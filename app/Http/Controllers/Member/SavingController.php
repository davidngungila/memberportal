<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SavingController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected GoogleSheetRepositoryInterface $repository,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        $savings = $this->repository->getMemberSavings($memberNumber);

        $balance = (float) ($savings['balance'] ?? 0);
        $interestEarned = (float) ($savings['interest_earned'] ?? 0);
        $runningBalance = (float) ($savings['running_balance'] ?? 0);

        // Get transactions from Google Sheets
        $googleTransactions = $savings['transactions'] ?? [];

        // Get transactions from database with running balance
        $dbTransactions = Transaction::byMemberCode($memberNumber)
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->date->format('Y-m-d'),
                    'type' => $transaction->transaction_type,
                    'amount' => (float) $transaction->amount,
                    'reference' => $transaction->reference_no ?? '',
                    'balance_after' => null, // Will be calculated
                    'source' => 'database'
                ];
            })
            ->toArray();

        // Merge transactions from both sources
        $allTransactions = array_merge($googleTransactions, $dbTransactions);

        // Sort by date ascending for balance calculation
        usort($allTransactions, static fn($a, $b): int => strtotime($a['date'] ?? '') <=> strtotime($b['date'] ?? ''));

        // Calculate running balance
        $currentBalance = 0;
        foreach ($allTransactions as &$transaction) {
            // Determine if this is a credit or debit
            $type = strtolower($transaction['type'] ?? '');
            $isCredit = $type === 'deposit' || $type === 'flexi-deposit' || $type === 'rda-deposit' || $type === 'opening balance' || $type === 'interest';
            
            if ($isCredit) {
                $currentBalance += (float) ($transaction['amount'] ?? 0);
            } else {
                $currentBalance -= (float) ($transaction['amount'] ?? 0);
            }
            
            $transaction['balance_after'] = $currentBalance;
        }

        // Sort by date descending for display
        usort($allTransactions, static fn($a, $b): int => strtotime($b['date'] ?? '') <=> strtotime($a['date'] ?? ''));

        $transactions = $allTransactions;

        $deposits = array_values(array_filter($transactions, static function (array $t): bool {
            $type = strtolower($t['type'] ?? '');
            return $type === 'deposit' || ($type !== 'withdrawal' && $type !== 'interest' && ($t['amount'] ?? 0) > 0);
        }));

        $withdrawals = array_values(array_filter($transactions, static function (array $t): bool {
            $type = strtolower($t['type'] ?? '');
            return $type === 'withdrawal' || ($t['amount'] ?? 0) < 0;
        }));

        $ledger = array_map(static function (array $t): array {
            $amount = (float) ($t['amount'] ?? 0);
            $type = strtolower($t['type'] ?? '');
            $isCredit = $type === 'deposit' || $type === 'interest' || $amount > 0;

            return array_merge($t, [
                'amount_float' => $amount,
                'is_credit' => $isCredit,
                'credit' => $isCredit ? abs($amount) : 0,
                'debit' => ! $isCredit ? abs($amount) : 0,
                'balance_after' => (float) ($t['balance_after'] ?? 0),
            ]);
        }, $transactions);

        $totalDeposited = array_sum(array_column($deposits, 'amount'));
        $totalWithdrawn = abs(array_sum(array_column($withdrawals, 'amount')));

        // Update running balance to the latest calculated balance
        $runningBalance = $currentBalance;

        // Paginate ledger (20 per page)
        $currentPage = $request->input('page', 1);
        $perPage = 20;
        $totalLedger = count($ledger);
        $ledgerPaginated = new LengthAwarePaginator(
            array_slice($ledger, ($currentPage - 1) * $perPage, $perPage),
            $totalLedger,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Paginate deposits (20 per page)
        $depositsPage = $request->input('deposits_page', 1);
        $totalDepositsCount = count($deposits);
        $depositsPaginated = new LengthAwarePaginator(
            array_slice($deposits, ($depositsPage - 1) * $perPage, $perPage),
            $totalDepositsCount,
            $perPage,
            $depositsPage,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'deposits_page']
        );

        // Paginate withdrawals (20 per page)
        $withdrawalsPage = $request->input('withdrawals_page', 1);
        $totalWithdrawalsCount = count($withdrawals);
        $withdrawalsPaginated = new LengthAwarePaginator(
            array_slice($withdrawals, ($withdrawalsPage - 1) * $perPage, $perPage),
            $totalWithdrawalsCount,
            $perPage,
            $withdrawalsPage,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'withdrawals_page']
        );

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'savings',
            'subject_id' => null,
            'description' => 'Member viewed savings',
            'properties' => [
                'member_number' => $memberNumber,
                'balance' => $balance,
                'running_balance' => $runningBalance,
                'transaction_count' => count($transactions),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.savings.index', compact(
            'savings',
            'balance',
            'interestEarned',
            'runningBalance',
            'deposits',
            'withdrawals',
            'ledger',
            'ledgerPaginated',
            'depositsPaginated',
            'withdrawalsPaginated',
            'totalDeposited',
            'totalWithdrawn'
        ));
    }
}
