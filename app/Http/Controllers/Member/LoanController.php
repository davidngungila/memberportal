<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Loan;
use App\Services\EncryptedIdService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LoanController extends Controller
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

        // Use database loans instead of Google Sheets - same as dashboard
        $dbLoans = Loan::where('member_number', $memberNumber)->get();

        // Convert database loans to the format expected by the view - same as dashboard
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

        // Process all loans (not just active) to show complete history
        $processedLoans = array_map(function (array $loan): array {
            $totalAmount = (float) ($loan['loan_amount'] ?? 0);
            $paid = (float) ($loan['paid_amount'] ?? 0);
            $outstanding = (float) ($loan['outstanding_balance'] ?? 0);
            $progress = $totalAmount > 0 ? min(100, round(($paid / $totalAmount) * 100, 1)) : 0;

            return array_merge($loan, [
                'progress_percent' => $progress,
                'total_amount' => $totalAmount,
                'paid_amount_float' => $paid,
                'outstanding_float' => $outstanding,
            ]);
        }, $loans);

        // Calculate totals from all loans
        $totalOutstanding = array_sum(array_column($processedLoans, 'outstanding_float'));
        $totalBorrowed = array_sum(array_column($processedLoans, 'total_amount'));
        
        // Count active loans separately for display
        $activeLoans = array_filter($processedLoans, function(array $loan): bool {
            return strtolower($loan['status'] ?? '') === 'active';
        });
        $activeCount = count($activeLoans);

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'loan',
            'subject_id' => null,
            'description' => 'Member viewed loans',
            'properties' => ['member_number' => $memberNumber, 'loan_count' => count($processedLoans), 'active_count' => $activeCount],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.loans.index', compact(
            'processedLoans',
            'loans',
            'totalOutstanding',
            'totalBorrowed',
            'activeCount'
        ));
    }

    public function create(Request $request): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'loan',
            'subject_id' => null,
            'description' => 'Member viewed loan application form',
            'properties' => ['member_number' => $memberNumber],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.loans.create', [
            'user' => $user,
            'memberNumber' => $memberNumber,
        ]);
    }

    public function storeBasicInfo(Request $request)
    {
        Gate::authorize('member-only');

        try {
            $validated = $request->validate([
                'loan_product_id' => 'nullable|exists:loan_products,id',
                'member_number' => 'required|string|max:50',
                'application_date' => 'required|date',
                'purpose' => 'required|in:business,education,agriculture,personal,emergency,other',
                'purpose_description' => 'required|string',
                'employment_status' => 'required|in:employed,self-employed,business-owner,retired,unemployed',
                'employer_name' => 'nullable|string|max:255',
                'monthly_income' => 'required|numeric|min:0',
                'other_income' => 'nullable|numeric|min:0',
                'work_experience' => 'nullable|integer|min:0',
            ]);

            // Handle empty string values for nullable numeric fields
            if ($request->has('other_income') && $request->input('other_income') === '') {
                $validated['other_income'] = null;
            }
            if ($request->has('work_experience') && $request->input('work_experience') === '') {
                $validated['work_experience'] = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Basic information saved successfully.',
                'loan_data' => $validated,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeLoanDetails(Request $request)
    {
        Gate::authorize('member-only');

        try {
            $validated = $request->validate([
                'principal_amount' => 'required|numeric|min:0',
                'interest_rate' => 'required|numeric|min:0|max:100',
                'term_months' => 'required|integer|min:1',
                'repayment_frequency' => 'nullable|in:monthly,biweekly,weekly',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loan details saved successfully.',
                'loan_data' => $validated,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeCollateral(Request $request)
    {
        Gate::authorize('member-only');

        try {
            $validated = $request->validate([
                'collateral' => 'nullable|string',
                'guarantor' => 'nullable|string',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Collateral information saved successfully.',
                'loan_data' => $validated,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Gate::authorize('member-only');

        try {
            $user = Auth::user();
            $memberNumber = $user->membercode;

            $validated = $request->validate([
                'loan_product_id' => 'nullable|exists:loan_products,id',
                'principal_amount' => 'required|numeric|min:0',
                'interest_rate' => 'required|numeric|min:0|max:100',
                'term_months' => 'required|integer|min:1',
                'application_date' => 'required|date',
                'purpose' => 'required|in:business,education,agriculture,personal,emergency,other',
                'purpose_description' => 'required|string',
                'employment_status' => 'required|in:employed,self-employed,business-owner,retired,unemployed',
                'employer_name' => 'nullable|string|max:255',
                'monthly_income' => 'required|numeric|min:0',
                'other_income' => 'nullable|numeric|min:0',
                'work_experience' => 'nullable|integer|min:0',
                'collateral' => 'nullable|string',
                'guarantor' => 'nullable|string',
                'notes' => 'nullable|string',
                'repayment_frequency' => 'nullable|in:monthly,biweekly,weekly',
                'preferred_repayment_date' => 'nullable|integer|in:1,5,10,15,20,25,30',
                'collateral_value' => 'nullable|numeric|min:0',
            ]);

            // Handle empty string values for nullable numeric fields
            if ($request->has('other_income') && $request->input('other_income') === '') {
                $validated['other_income'] = null;
            }
            if ($request->has('work_experience') && $request->input('work_experience') === '') {
                $validated['work_experience'] = null;
            }
            if ($request->has('collateral_value') && $request->input('collateral_value') === '') {
                $validated['collateral_value'] = null;
            }

            // Generate sequential loan number
            $today = date('Ymd');
            $loanCountToday = Loan::where('loan_number', 'like', 'LN' . $today . '%')->count();
            $sequentialNumber = str_pad((string) ($loanCountToday + 1), 4, '0', STR_PAD_LEFT);
            
            $validated['loan_number'] = 'LN' . $today . $sequentialNumber;
            $validated['user_id'] = $user->id;
            $validated['member_number'] = $memberNumber;
            $validated['status'] = 'pending';

            $loan = Loan::create($validated);

            // Create repayment schedule
            $this->createRepaymentSchedule($loan);

            ActivityLog::create([
                'user_id' => $user->id,
                'subject_type' => 'loan',
                'subject_id' => $loan->id,
                'description' => "Member submitted loan application: {$loan->loan_number}",
                'properties' => [
                    'member_number' => $memberNumber,
                    'loan_amount' => $validated['principal_amount'],
                    'purpose' => $validated['purpose'],
                    'monthly_income' => $validated['monthly_income'],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loan application submitted successfully.',
                'loan_number' => $loan->loan_number,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function createRepaymentSchedule(Loan $loan)
    {
        $principal = (float) $loan->principal_amount;
        $interestRate = (float) $loan->interest_rate;
        $termMonths = (int) $loan->term_months;
        
        // Calculate monthly payment using amortization formula
        if ($interestRate > 0) {
            $monthlyRate = $interestRate / 100 / 12;
            $monthlyPayment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / (pow(1 + $monthlyRate, $termMonths) - 1);
        } else {
            $monthlyPayment = $principal / $termMonths;
        }

        $balance = $principal;
        $startDate = $loan->application_date ? $loan->application_date->format('Y-m-d') : date('Y-m-d');

        for ($i = 1; $i <= $termMonths; $i++) {
            $dueDate = date('Y-m-d', strtotime("+{$i} month", strtotime($startDate)));
            
            $interestPortion = $balance * ($interestRate / 100 / 12);
            $principalPortion = $monthlyPayment - $interestPortion;
            $balance = max(0, $balance - $principalPortion);

            \App\Models\LoanRepaymentSchedule::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => $dueDate,
                'principal_amount' => $principalPortion,
                'interest_amount' => $interestPortion,
                'total_amount' => $monthlyPayment,
                'balance_after' => $balance,
                'status' => 'pending',
                'amount_paid' => 0,
            ]);
        }

        // Update loan with calculated monthly payment
        $loan->update([
            'monthly_payment' => $monthlyPayment,
            'total_amount_due' => $monthlyPayment * $termMonths,
            'balance' => $principal,
        ]);
    }

    public function show(Request $request, string $encryptedLoanNumber): View
    {
        $loanNumber = $this->encryptedIdService->decrypt($encryptedLoanNumber);
        
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        // Use database loan instead of Google Sheets
        $dbLoan = Loan::where('loan_number', $loanNumber)->where('member_number', $memberNumber)->first();

        if (! $dbLoan) {
            $this->error("Loan {$loanNumber} not found or access denied.");
            abort(404, 'Loan not found');
        }

        // Convert database loan to the format expected by the view
        $loan = [
            'loan_number' => $dbLoan->loan_number,
            'loan_product' => ucfirst($dbLoan->purpose),
            'loan_amount' => $dbLoan->principal_amount,
            'paid_amount' => $dbLoan->amount_paid ?? 0,
            'outstanding_balance' => $dbLoan->balance ?? 0,
            'installment' => $dbLoan->monthly_payment ?? 0,
            'interest_rate' => $dbLoan->interest_rate ?? 0,
            'status' => $dbLoan->status,
            'disbursement_date' => $dbLoan->disbursement_date ? $dbLoan->disbursement_date->format('Y-m-d') : null,
            'maturity_date' => $dbLoan->maturity_date ? $dbLoan->maturity_date->format('Y-m-d') : null,
        ];

        $totalAmount = (float) ($loan['loan_amount'] ?? 0);
        $paid = (float) ($loan['paid_amount'] ?? 0);
        $outstanding = (float) ($loan['outstanding_balance'] ?? 0);
        $progress = $totalAmount > 0 ? min(100, round(($paid / $totalAmount) * 100, 1)) : 0;

        $repaymentSchedule = $this->buildRepaymentSchedule($loan);
        $repaymentHistory = $this->buildRepaymentHistory($loan);

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'loan',
            'subject_id' => $dbLoan->id,
            'description' => "Member viewed loan: {$loanNumber}",
            'properties' => ['member_number' => $memberNumber, 'loan_product' => $loan['loan_product'] ?? null],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.loans.show', compact(
            'loan',
            'loanNumber',
            'totalAmount',
            'paid',
            'outstanding',
            'progress',
            'repaymentSchedule',
            'repaymentHistory'
        ));
    }

    protected function buildRepaymentSchedule(array $loan): array
    {
        $installment = (float) ($loan['installment'] ?? 0);
        $totalAmount = (float) ($loan['loan_amount'] ?? 0);
        $interestRate = (float) ($loan['interest_rate'] ?? 0) / 100;
        $disbursementDate = $loan['disbursement_date'] ?? date('Y-m-d');

        if ($installment <= 0 || $totalAmount <= 0) {
            return [];
        }

        $months = (int) ceil($totalAmount / $installment);
        if ($months > 60) {
            $months = (int) round($totalAmount / $installment);
        }
        $months = max(1, min($months, 60));

        $schedule = [];
        $remaining = $totalAmount;
        $currentDate = new \DateTime($disbursementDate);

        for ($i = 1; $i <= $months; $i++) {
            $currentDate->modify('+1 month');
            $interestPortion = round($remaining * ($interestRate / 12), 2);
            $principalPortion = round($installment - $interestPortion, 2);
            $remaining = round(max(0, $remaining - $principalPortion), 2);

            if ($i === $months && $remaining > 0) {
                $principalPortion += $remaining;
                $remaining = 0;
            }

            $schedule[] = [
                'installment_no' => $i,
                'due_date' => $currentDate->format('Y-m-d'),
                'installment' => round($principalPortion + $interestPortion, 2),
                'principal' => $principalPortion,
                'interest' => $interestPortion,
                'remaining' => $remaining,
                'status' => $remaining === 0 ? 'Paid' : (strtotime($currentDate->format('Y-m-d')) < time() ? 'Overdue' : 'Pending'),
            ];
        }

        return $schedule;
    }

    protected function buildRepaymentHistory(array $loan): array
    {
        $paid = (float) ($loan['paid_amount'] ?? 0);
        $installment = (float) ($loan['installment'] ?? 0);
        $disbursementDate = $loan['disbursement_date'] ?? date('Y-m-d');

        if ($paid <= 0 || $installment <= 0) {
            return [];
        }

        $fullPayments = (int) floor($paid / $installment);
        $history = [];
        $currentDate = new \DateTime($disbursementDate);

        for ($i = 1; $i <= $fullPayments; $i++) {
            $currentDate->modify('+1 month');
            $history[] = [
                'payment_no' => $i,
                'date' => $currentDate->format('Y-m-d'),
                'amount' => $installment,
                'method' => 'Payroll Deduction',
                'reference' => 'PY-' . strtoupper(substr(md5($loan['loan_number'] . $i), 0, 8)),
                'status' => 'Completed',
            ];
        }

        $partial = round($paid - ($fullPayments * $installment), 2);
        if ($partial > 0) {
            $currentDate->modify('+1 month');
            $history[] = [
                'payment_no' => $fullPayments + 1,
                'date' => $currentDate->format('Y-m-d'),
                'amount' => $partial,
                'method' => 'Partial Payment',
                'reference' => 'PP-' . strtoupper(substr(md5($loan['loan_number'] . 'partial'), 0, 8)),
                'status' => 'Completed',
            ];
        }

        return array_reverse($history);
    }
}
