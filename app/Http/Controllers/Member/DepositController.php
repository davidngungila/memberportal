<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\EncryptedIdService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DepositController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected GoogleSheetRepositoryInterface $repository,
        protected EncryptedIdService $encryptedIdService,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        if (!$memberNumber) {
            return view('member.deposits.index', [
                'deposits' => [],
                'processedDeposits' => [],
                'totalInvested' => 0,
                'totalValue' => 0,
                'totalInterest' => 0,
                'maturingSoon' => 0,
            ]);
        }

        $deposits = $this->repository->getMemberDeposits($memberNumber);

        $processedDeposits = array_map(function (array $dep): array {
            $startDate = $dep['start_date'] ?? date('Y-m-d');
            $maturityDate = $dep['maturity_date'] ?? date('Y-m-d', strtotime('+1 year'));
            $amount = (float) ($dep['amount'] ?? 0);
            $currentValue = (float) ($dep['current_value'] ?? $amount);
            $interest = (float) ($dep['interest'] ?? 0);

            $startTs = strtotime($startDate);
            $maturityTs = strtotime($maturityDate);
            $now = time();

            $totalDuration = $maturityTs - $startTs;
            $elapsed = $now - $startTs;
            $progressPercent = $totalDuration > 0 ? min(100, max(0, round(($elapsed / $totalDuration) * 100, 1))) : 100;

            $daysRemaining = $maturityTs > $now ? (int) ceil(($maturityTs - $now) / 86400) : 0;
            $daysTotal = $totalDuration > 0 ? (int) ceil($totalDuration / 86400) : 0;
            $daysElapsed = $daysTotal - $daysRemaining;

            return array_merge($dep, [
                'amount_float' => $amount,
                'current_value_float' => $currentValue,
                'interest_float' => $interest,
                'progress_percent' => $progressPercent,
                'days_remaining' => $daysRemaining,
                'days_elapsed' => $daysElapsed,
                'days_total' => $daysTotal,
                'start_ts' => $startTs,
                'maturity_ts' => $maturityTs,
            ]);
        }, $deposits);

        usort($processedDeposits, static fn($a, $b): int => $a['maturity_ts'] <=> $b['maturity_ts']);

        $totalInvested = array_sum(array_column($processedDeposits, 'amount_float'));
        $totalValue = array_sum(array_column($processedDeposits, 'current_value_float'));
        $totalInterest = $totalValue - $totalInvested;
        $maturingSoon = count(array_filter($processedDeposits, static fn(array $d): bool => $d['days_remaining'] > 0 && $d['days_remaining'] <= 60));

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'deposit',
            'subject_id' => null,
            'description' => 'Member viewed deposits',
            'properties' => ['member_number' => $memberNumber, 'deposit_count' => count($processedDeposits)],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.deposits.index', compact(
            'processedDeposits',
            'deposits',
            'totalInvested',
            'totalValue',
            'totalInterest',
            'maturingSoon'
        ));
    }

    public function show(Request $request, string $encryptedCertificateNumber): View
    {
        $certificateNumber = $this->encryptedIdService->decrypt($encryptedCertificateNumber);
        
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        if (!$memberNumber) {
            abort(404, 'Deposit not found');
        }

        $deposits = $this->repository->getMemberDeposits($memberNumber);
        $deposit = collect($deposits)->firstWhere('certificate_number', $certificateNumber);

        if (! $deposit) {
            $this->error("Deposit certificate {$certificateNumber} not found or access denied.");
            abort(404, 'Deposit not found');
        }

        $startDate = $deposit['start_date'] ?? date('Y-m-d');
        $maturityDate = $deposit['maturity_date'] ?? date('Y-m-d', strtotime('+1 year'));
        $amount = (float) ($deposit['amount'] ?? 0);
        $currentValue = (float) ($deposit['current_value'] ?? $amount);
        $interest = (float) ($deposit['interest'] ?? 0);

        $startTs = strtotime($startDate);
        $maturityTs = strtotime($maturityDate);
        $now = time();

        $totalDuration = $maturityTs - $startTs;
        $elapsed = $now - $startTs;
        $progressPercent = $totalDuration > 0 ? min(100, max(0, round(($elapsed / $totalDuration) * 100, 1))) : 100;
        $daysRemaining = $maturityTs > $now ? (int) ceil(($maturityTs - $now) / 86400) : 0;

        $timeline = $this->buildTimeline($deposit, $startDate, $maturityDate, $progressPercent);
        $projectedMaturityValue = $amount + $interest;

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'deposit',
            'subject_id' => null,
            'description' => "Member viewed deposit: {$certificateNumber}",
            'properties' => ['member_number' => $memberNumber, 'product' => $deposit['product'] ?? null],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.deposits.show', compact(
            'deposit',
            'certificateNumber',
            'amount',
            'currentValue',
            'interest',
            'startDate',
            'maturityDate',
            'progressPercent',
            'daysRemaining',
            'timeline',
            'projectedMaturityValue'
        ));
    }

    protected function buildTimeline(array $deposit, string $startDate, string $maturityDate, float $progressPercent): array
    {
        $amount = (float) ($deposit['amount'] ?? 0);
        $interestTotal = (float) ($deposit['interest'] ?? 0);
        $currentValue = (float) ($deposit['current_value'] ?? $amount);

        $events = [];

        $events[] = [
            'date' => $startDate,
            'title' => 'Deposit Placed',
            'description' => "Fixed deposit opened with principal amount.",
            'amount' => $amount,
            'icon' => 'fa-circle-dollar-to-slot',
            'color' => 'green',
            'done' => true,
        ];

        $start = new \DateTime($startDate);
        $maturity = new \DateTime($maturityDate);
        $interval = \DateInterval::createFromDateString('3 months');
        $periods = new \DatePeriod($start, $interval, $maturity);

        $interestSoFar = $currentValue - $amount;
        $accruedEstimate = max(0, min($interestTotal, $interestSoFar));

        $quarterCount = 0;
        foreach ($periods as $idx => $dt) {
            if ($idx === 0) {
                continue;
            }
            $quarterCount++;
            $portion = $interestTotal > 0 ? ($quarterCount / 4) : 0;
            $estimatedInterest = round($interestTotal * min(1, $portion), 2);
            $isDone = $dt->getTimestamp() < time();

            $events[] = [
                'date' => $dt->format('Y-m-d'),
                'title' => 'Interest Applied',
                'description' => "Quarterly interest posting for this period.",
                'amount' => $isDone ? min($estimatedInterest, $accruedEstimate) : $estimatedInterest,
                'icon' => 'fa-percent',
                'color' => 'blue',
                'done' => $isDone,
            ];
        }

        $events[] = [
            'date' => $maturityDate,
            'title' => 'Maturity Date',
            'description' => "Deposit matures. Principal + total interest available for withdrawal.",
            'amount' => $amount + $interestTotal,
            'icon' => 'fa-flag-checkered',
            'color' => 'yellow',
            'done' => $progressPercent >= 100,
        ];

        return $events;
    }
}
