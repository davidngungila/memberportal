<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Investment;
use App\Services\AdminDashboardService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected AdminDashboardService $dashboardService,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        $investments = Investment::with(['investmentProduct'])
            ->where('member_number', $memberNumber)
            ->orderBy('investment_date', 'desc')
            ->get();

        $enrichedInvestments = $investments->map(function ($inv) {
            $productName = $inv->investmentProduct ? $inv->investmentProduct->name : 'Unknown Product';
            $productCode = $inv->investmentProduct ? $inv->investmentProduct->code : 'Unknown';
            $duration = '';
            if ($inv->investment_date && $inv->maturity_date) {
                $duration = $inv->investment_date->diffInMonths($inv->maturity_date) . ' months';
            }
            $actualReturn = $inv->actual_return ?? 0;
            $expectedReturn = $inv->expected_return ?? 0;
            $amount = $inv->amount ?? 0;
            
            // Use expected_return for profit calculation if actual_return equals amount (new investment)
            $returnValue = ($actualReturn == $amount) ? $expectedReturn : $actualReturn;
            $profit = $returnValue - $amount;
            $profitPct = $amount > 0 ? (($profit / $amount) * 100) : 0;
            $status = $this->dashboardService->depositStatusBadge($inv->status ?? null);

            return (object) [
                'investment' => $inv,
                'product_name' => $productName,
                'product_code' => $productCode,
                'duration' => $duration,
                'profit' => $profit,
                'profit_pct' => $profitPct,
                'status' => $status,
            ];
        });

        $totalInvested = $investments->sum('amount');
        $totalCurrentValue = $investments->sum(function ($inv) {
            $returnValue = ($inv->actual_return == $inv->amount) ? ($inv->expected_return ?? 0) : ($inv->actual_return ?? 0);
            return $returnValue;
        });
        $totalProfit = $totalCurrentValue - $totalInvested;
        $overallReturn = $totalInvested > 0 ? (($totalCurrentValue - $totalInvested) / $totalInvested) * 100 : 0;
        $productsCount = $investments->count();

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'investment',
            'subject_id' => null,
            'description' => 'Member viewed investments',
            'properties' => [
                'member_number' => $memberNumber,
                'investment_count' => $productsCount,
                'total_value' => $totalCurrentValue,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.investments.index', [
            'investments' => $enrichedInvestments,
            'totalInvested' => $totalInvested,
            'totalCurrentValue' => $totalCurrentValue,
            'totalProfit' => $totalProfit,
            'overallReturn' => $overallReturn,
            'productsCount' => $productsCount,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function show(Request $request, string $encryptedId): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        $investmentId = app('App\Services\EncryptedIdService')->decrypt($encryptedId);

        $investment = Investment::with(['investmentProduct'])
            ->where('id', $investmentId)
            ->where('member_number', $memberNumber)
            ->first();

        if (!$investment) {
            $this->error('Investment not found');
            return redirect()->route('member.investments.index');
        }

        $productName = $investment->investmentProduct ? $investment->investmentProduct->name : 'Unknown Product';
        $duration = '';
        if ($investment->investment_date && $investment->maturity_date) {
            $duration = $investment->investment_date->diffInMonths($investment->maturity_date) . ' months';
        }
        $actualReturn = $investment->actual_return ?? 0;
        $expectedReturn = $investment->expected_return ?? 0;
        $amount = $investment->amount ?? 0;
        
        // Use expected_return for profit calculation if actual_return equals amount (new investment)
        $returnValue = ($actualReturn == $amount) ? $expectedReturn : $actualReturn;
        $profit = $returnValue - $amount;
        $profitPct = $amount > 0 ? (($profit / $amount) * 100) : 0;
        $status = $this->dashboardService->depositStatusBadge($investment->status ?? null);

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'investment',
            'subject_id' => $investment->id,
            'description' => 'Member viewed investment details',
            'properties' => [
                'member_number' => $memberNumber,
                'investment_number' => $investment->investment_number,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.investments.show', [
            'investment' => $investment,
            'product_name' => $productName,
            'duration' => $duration,
            'profit' => $profit,
            'profit_pct' => $profitPct,
            'status' => $status,
        ]);
    }
}
