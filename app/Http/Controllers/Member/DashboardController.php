<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\LoanCompletionCertificate;
use App\Models\ShareCertificate;
use App\Services\AdminDashboardService;
use App\Traits\FlashMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected AdminDashboardService $dashboardService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $memberNumber = $user->membercode;

        if (empty($memberNumber)) {
            $this->error('Your account is missing a Member Number. Please contact the administrator to update your profile.');
            return redirect()->route('member.profile.index')->with('hint', 'missing_member_number');
        }

        $member = $user; // Use user object directly since we're not using Google Sheets
        
        // Use database loans instead of Google Sheets
        $dbLoans = \App\Models\Loan::where('member_number', $memberNumber)
            ->whereIn('status', ['active', 'approved', 'disbursed'])
            ->get();
        
        // Convert database loans to the format expected
        $loans = $dbLoans->map(function ($loan) {
            return [
                'loan_number' => $loan->loan_number,
                'loan_product' => ucfirst($loan->purpose),
                'loan_amount' => $loan->principal_amount,
                'paid_amount' => $loan->amount_paid ?? 0,
                'outstanding_balance' => $loan->balance ?? 0,
                'installment' => $loan->monthly_payment ?? 0,
                'interest_rate' => $loan->interest_rate ?? 0,
                'status' => $loan->status,
                'disbursement_date' => $loan->disbursement_date ? $loan->disbursement_date->format('Y-m-d') : null,
                'maturity_date' => $loan->maturity_date ? $loan->maturity_date->format('Y-m-d') : null,
            ];
        })->toArray();
        
        // Use database investments instead of Google Sheets
        $dbInvestments = Investment::with(['investmentProduct'])
            ->where('member_number', $memberNumber)
            ->orderBy('investment_date', 'desc')
            ->get();
        
        $investments = $dbInvestments->map(function ($inv) {
            $actualReturn = $inv->actual_return ?? 0;
            $expectedReturn = $inv->expected_return ?? 0;
            $amount = $inv->amount ?? 0;
            
            // Use expected_return for profit calculation if actual_return equals amount (new investment)
            $returnValue = ($actualReturn == $amount) ? $expectedReturn : $actualReturn;
            $profit = $returnValue - $amount;
            $profitPct = $amount > 0 ? (($profit / $amount) * 100) : 0;
            
            return [
                'investment_number' => $inv->investment_number,
                'product' => $inv->investmentProduct ? $inv->investmentProduct->name : 'Unknown Product',
                'amount_invested' => $amount,
                'current_value' => $returnValue,
                'profit_earned' => $profit,
                'return_rate' => $profitPct,
                'start_date' => $inv->investment_date ? $inv->investment_date->format('Y-m-d') : null,
                'status' => $inv->status,
            ];
        })->toArray();
        
        $savings = ['transactions' => [], 'balance' => 0, 'running_balance' => 0]; // Placeholder for savings
        $deposits = []; // Placeholder for deposits
        
        // Get SWF data from database
        $swfMember = $user->swfMember;
        if ($swfMember) {
            $swfMember->load(['contributions']);
            $swfBalance = $swfMember->total_contributions - $swfMember->total_benefits_received;
            $swfContributionHistory = $swfMember->contributions->map(function ($contribution) {
                return [
                    'date' => $contribution->contribution_date->format('Y-m-d'),
                    'amount' => $contribution->amount,
                    'payment_method' => $contribution->payment_method,
                    'reference_number' => $contribution->reference_number,
                    'description' => 'SWF Contribution',
                ];
            })->toArray();
            $swf = [
                'current_balance' => $swfBalance,
                'contribution_history' => $swfContributionHistory,
                'total_contributions' => $swfMember->total_contributions,
                'total_benefits' => $swfMember->total_benefits_received,
            ];
        } else {
            $swf = ['current_balance' => 0, 'contribution_history' => [], 'total_contributions' => 0, 'total_benefits' => 0];
            $swfBalance = 0;
        }

        // Get certificates for the member
        $loanCertificates = LoanCompletionCertificate::whereHas('loan', function($query) use ($memberNumber) {
            $query->where('member_number', $memberNumber);
        })->with('loan')->orderBy('completion_date', 'desc')->get();

        $shareCertificates = ShareCertificate::whereHas('sharePurchase', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['sharePurchase.shareProduct'])->orderBy('issue_date', 'desc')->get();

        // Get database transactions
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

        // Use database transactions for savings
        $allTransactions = $dbTransactions;

        // Sort by date ascending for balance calculation
        usort($allTransactions, static fn($a, $b): int => strtotime($a['date'] ?? '') <=> strtotime($b['date'] ?? ''));

        // Calculate running balance from all transactions
        $currentBalance = 0;
        foreach ($allTransactions as &$transaction) {
            $type = strtolower($transaction['type'] ?? '');
            $isCredit = $type === 'deposit' || $type === 'flexi-deposit' || $type === 'rda-deposit' || $type === 'opening balance' || $type === 'interest';
            
            if ($isCredit) {
                $currentBalance += (float) ($transaction['amount'] ?? 0);
            } else {
                $currentBalance -= (float) ($transaction['amount'] ?? 0);
            }
            
            $transaction['balance_after'] = $currentBalance;
        }

        // Update savings with calculated balance
        $savings['transactions'] = $allTransactions;
        $savings['running_balance'] = $currentBalance;
        $savings['balance'] = $currentBalance;

        $loanBalance = collect($loans)->sum('outstanding_balance');
        $savingsBalance = $currentBalance;
        $depositBalance = 0; // Placeholder for deposits
        $investmentBalance = collect($investments)->sum('current_value');

        // Filter active loans for dashboard display
        $activeLoans = array_filter($loans, function(array $loan): bool {
            $status = strtolower($loan['status'] ?? '');
            return in_array($status, ['active', 'approved', 'disbursed']);
        });

        $recentTransactions = $this->consolidateRecentTransactions($loans, $savings, $deposits, $swf, $investments);

        $savingsGrowth = $this->buildSavingsGrowthData($savings);
        $investmentDistribution = $this->buildInvestmentDistribution($dbInvestments);
        $investmentPerformance = $this->buildInvestmentPerformance($dbInvestments);

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'dashboard',
            'subject_id' => null,
            'description' => 'Member viewed dashboard',
            'properties' => ['member_number' => $memberNumber],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.dashboard.index', compact(
            'user',
            'member',
            'loans',
            'activeLoans',
            'savings',
            'deposits',
            'swf',
            'investments',
            'loanBalance',
            'savingsBalance',
            'depositBalance',
            'swfBalance',
            'investmentBalance',
            'recentTransactions',
            'savingsGrowth',
            'investmentDistribution',
            'investmentPerformance',
            'loanCertificates',
            'shareCertificates'
        ));
    }

    protected function consolidateRecentTransactions(array $loans, array $savings, array $deposits, array $swf, array $investments): array
    {
        $transactions = [];

        if (!empty($loans)) {
            foreach ($loans as $loan) {
                if (!empty($loan['paid_amount']) && $loan['paid_amount'] > 0) {
                    $transactions[] = [
                        'date' => $loan['maturity_date'] ?? date('Y-m-d'),
                        'type' => 'Loan Repayment',
                        'description' => "Payment towards {$loan['loan_product']} ({$loan['loan_number']})",
                        'amount' => (float) $loan['paid_amount'],
                        'balance_after' => (float) ($loan['outstanding_balance'] ?? 0),
                        'sort_date' => strtotime($loan['maturity_date'] ?? date('Y-m-d')),
                    ];
                }
                if (!empty($loan['disbursement_date'])) {
                    $transactions[] = [
                        'date' => $loan['disbursement_date'],
                        'type' => 'Loan Disbursement',
                        'description' => "{$loan['loan_product']} disbursed ({$loan['loan_number']})",
                        'amount' => (float) $loan['loan_amount'],
                        'balance_after' => (float) ($loan['outstanding_balance'] ?? $loan['loan_amount']),
                        'sort_date' => strtotime($loan['disbursement_date']),
                    ];
                }
            }
        }

        // Include database transactions in recent transactions
        if (!empty($savings['transactions']) && is_array($savings['transactions'])) {
            foreach ($savings['transactions'] as $txn) {
                $typeLabel = match (strtolower($txn['type'] ?? '')) {
                    'deposit' => 'Saving Deposit',
                    'withdrawal' => 'Saving Withdrawal',
                    'interest' => 'Savings Interest',
                    'flexi-deposit' => 'Flexi Deposit',
                    'rda-deposit' => 'RDA Deposit',
                    'opening balance' => 'Opening Balance',
                    default => 'Saving ' . ($txn['type'] ?? 'Transaction'),
                };
                $transactions[] = [
                    'date' => $txn['date'],
                    'type' => $typeLabel,
                    'description' => $txn['reference'] ?? ($txn['description'] ?? 'Savings transaction'),
                    'amount' => (float) $txn['amount'],
                    'balance_after' => (float) ($txn['balance_after'] ?? 0),
                    'sort_date' => strtotime($txn['date']),
                ];
            }
        }

        if (!empty($deposits)) {
            foreach ($deposits as $dep) {
                if (!empty($dep['maturity_date'])) {
                    $transactions[] = [
                        'date' => $dep['maturity_date'],
                        'type' => 'Deposit Maturity',
                        'description' => "{$dep['product']} matures ({$dep['certificate_number']})",
                        'amount' => (float) ($dep['current_value'] ?? $dep['amount']),
                        'balance_after' => (float) ($dep['current_value'] ?? 0),
                        'sort_date' => strtotime($dep['maturity_date']),
                    ];
                }
                if (!empty($dep['start_date'])) {
                    $transactions[] = [
                        'date' => $dep['start_date'],
                        'type' => 'Deposit Placement',
                        'description' => "Opened {$dep['product']} ({$dep['certificate_number']})",
                        'amount' => (float) $dep['amount'],
                        'balance_after' => (float) ($dep['current_value'] ?? $dep['amount']),
                        'sort_date' => strtotime($dep['start_date']),
                    ];
                }
            }
        }

        if (!empty($swf['contribution_history']) && is_array($swf['contribution_history'])) {
            foreach ($swf['contribution_history'] as $c) {
                $transactions[] = [
                    'date' => $c['date'],
                    'type' => 'SWF Contribution',
                    'description' => $c['description'] ?? 'Social Welfare Fund',
                    'amount' => (float) $c['amount'],
                    'balance_after' => (float) ($swf['current_balance'] ?? 0),
                    'sort_date' => strtotime($c['date']),
                ];
            }
        }

        usort($transactions, static fn($a, $b): int => ($b['sort_date'] ?? 0) <=> ($a['sort_date'] ?? 0));

        return array_slice($transactions, 0, 5);
    }

    protected function buildSavingsGrowthData(array $savings): array
    {
        $labels = [];
        $values = [];
        $today = new \DateTime();

        for ($i = 5; $i >= 0; $i--) {
            $d = (clone $today)->modify("-{$i} month");
            $labels[] = $d->format('M Y');
        }

        $currentBalance = (float) ($savings['running_balance'] ?? $savings['balance'] ?? 0);
        
        // Calculate actual growth from transaction history
        if (!empty($savings['transactions']) && is_array($savings['transactions'])) {
            $sorted = $savings['transactions'];
            usort($sorted, static fn($a, $b): int => strtotime($a['date'] ?? '') <=> strtotime($b['date'] ?? ''));
            
            // Get balance at 6 months ago
            $sixMonthsAgo = (clone $today)->modify('-6 months')->format('Y-m-d');
            $startBalance = 0;
            
            foreach ($sorted as $txn) {
                if (strtotime($txn['date'] ?? '') >= strtotime($sixMonthsAgo)) {
                    $startBalance = (float) ($txn['balance_after'] ?? 0);
                    break;
                }
            }
            
            // If no transaction found in 6 months, use first transaction balance
            if ($startBalance === 0 && !empty($sorted)) {
                $startBalance = (float) ($sorted[0]['balance_after'] ?? 0);
            }
        } else {
            $startBalance = max(0, $currentBalance - 50000);
        }

        if ($currentBalance < $startBalance) {
            $startBalance = $currentBalance;
        }

        $step = $currentBalance > $startBalance ? ($currentBalance - $startBalance) / 5 : 0;
        for ($i = 0; $i < 6; $i++) {
            $values[] = round($startBalance + ($step * $i), 2);
        }
        if (!empty($values)) {
            $values[count($values) - 1] = $currentBalance;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function buildInvestmentDistribution($investments): array
    {
        $distribution = [];
        
        foreach ($investments as $inv) {
            $productName = $inv->investmentProduct ? $inv->investmentProduct->name : 'Unknown';
            $value = ($inv->actual_return == $inv->amount) ? ($inv->expected_return ?? 0) : ($inv->actual_return ?? 0);
            
            if (!isset($distribution[$productName])) {
                $distribution[$productName] = 0;
            }
            $distribution[$productName] += $value;
        }

        return [
            'labels' => array_keys($distribution),
            'values' => array_values($distribution),
        ];
    }

    protected function buildInvestmentPerformance($investments): array
    {
        $labels = [];
        $values = [];
        $today = new \DateTime();

        for ($i = 5; $i >= 0; $i--) {
            $d = (clone $today)->modify("-{$i} month");
            $labels[] = $d->format('M Y');
        }

        $totalValue = 0;
        foreach ($investments as $inv) {
            $value = ($inv->actual_return == $inv->amount) ? ($inv->expected_return ?? 0) : ($inv->actual_return ?? 0);
            $totalValue += $value;
        }

        // Simulate growth over 6 months
        $startValue = max(0, $totalValue * 0.85);
        $step = $totalValue > $startValue ? ($totalValue - $startValue) / 5 : 0;
        
        for ($i = 0; $i < 6; $i++) {
            $values[] = round($startValue + ($step * $i), 2);
        }
        if (!empty($values)) {
            $values[count($values) - 1] = $totalValue;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
