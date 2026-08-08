<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\SmsSettings;
use App\Services\AdminDashboardService;
use App\Services\EncryptedIdService;
use App\Services\MemberService;
use App\Services\SmsService;
use App\Traits\FlashMessages;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class LoanController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected MemberService $memberService,
        protected AdminDashboardService $dashboardService,
        protected EncryptedIdService $encryptedIdService,
    ) {
    }

    public function applications(Request $request)
    {
        Gate::authorize('admin-only');

        $query = Loan::with('user')->pending();

        $loans = $query->orderBy('application_date', 'desc')->paginate(15);

        return view('admin.loans.applications', [
            'loans' => $loans,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function repayments(Request $request)
    {
        Gate::authorize('admin-only');

        $query = Loan::with('user')->active();

        $loans = $query->orderBy('disbursement_date', 'desc')->paginate(15);

        return view('admin.loans.repayments', [
            'loans' => $loans,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function index(Request $request)
    {
        Gate::authorize('admin-only');

        $perPage = (int) $request->input('per_page', 15);
        $sortColumn = $request->input('sort', 'application_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $statusFilter = $request->input('status', '');
        $searchQuery = $request->input('q', '');

        $query = Loan::with('user');

        // Search
        if (!empty($searchQuery)) {
            $query->where('loan_number', 'like', '%' . $searchQuery . '%')
                  ->orWhere('member_number', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('user', function ($q) use ($searchQuery) {
                      $q->where('name', 'like', '%' . $searchQuery . '%');
                  });
        }

        // Filter by status
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        // Sort
        $query->orderBy($sortColumn, $sortDirection);

        $loans = $query->paginate($perPage);

        $loans->appends([
            'q' => $searchQuery,
            'status' => $statusFilter,
            'per_page' => $perPage,
            'sort' => $sortColumn,
            'sort_direction' => $sortDirection,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed loans list',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'search_query' => $searchQuery,
                'status_filter' => $statusFilter,
                'per_page' => $perPage,
                'sort' => $sortColumn,
                'sort_direction' => $sortDirection,
                'total_count' => $loans->total(),
            ],
        ]);

        return view('admin.loans.index', [
            'loans' => $loans,
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
            'perPage' => $perPage,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
            'memberService' => $this->memberService,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('admin-only');

        return view('admin.loans.create', [
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function storeBasicInfo(Request $request)
    {
        Gate::authorize('admin-only');

        try {
            $validated = $request->validate([
                'loan_product_id' => 'nullable|exists:loan_products,id',
                'user_id' => 'required|exists:users,id',
                'member_number' => 'required|string|max:50',
                'application_date' => 'required|date',
                'purpose' => 'required|in:business,education,agriculture,personal,emergency,other',
                'purpose_description' => 'nullable|string',
            ]);

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
        Gate::authorize('admin-only');

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
        Gate::authorize('admin-only');

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
        Gate::authorize('admin-only');

        $validated = $request->validate([
            'loan_product_id' => 'nullable|exists:loan_products,id',
            'user_id' => 'required|exists:users,id',
            'member_number' => 'required|string|max:50',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1',
            'application_date' => 'required|date',
            'purpose' => 'required|in:business,education,agriculture,personal,emergency,other',
            'purpose_description' => 'nullable|string',
            'collateral' => 'nullable|string',
            'guarantor' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Generate sequential loan number
        $today = date('Ymd');
        $loanCountToday = Loan::where('loan_number', 'like', 'LN' . $today . '%')->count();
        $sequentialNumber = str_pad((string) ($loanCountToday + 1), 4, '0', STR_PAD_LEFT);
        $validated['loan_number'] = 'LN' . $today . $sequentialNumber;
        $validated['status'] = 'pending';

        $loan = Loan::create($validated);

        // Create repayment schedule
        $this->createRepaymentSchedule($loan);

        $this->success('Loan application created successfully.');

        return redirect()->route('admin.loans.index');
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

    public function show(Request $request, string $encryptedLoanNumber)
    {
        try {
            $loanNumber = $this->encryptedIdService->decrypt($encryptedLoanNumber);
        } catch (\Exception $e) {
            $this->error('Invalid loan number.');
            return redirect()->route('admin.loans.index');
        }
        
        Gate::authorize('admin-only');

        $loan = Loan::with(['user', 'repaymentSchedules'])->where('loan_number', $loanNumber)->first();

        if (!$loan) {
            $this->error("Loan {$loanNumber} not found.");
            return redirect()->route('admin.loans.index');
        }

        $loanAmount = (float) $loan->principal_amount;
        $paidAmount = (float) $loan->amount_paid;
        $outstanding = (float) $loan->balance;
        $progress = $loanAmount > 0 ? min(($paidAmount / $loanAmount) * 100, 100) : 0;

        $installment = (float) $loan->monthly_payment;
        $interestRate = (float) $loan->interest_rate;
        $disbursementDate = $loan->disbursement_date ? $loan->disbursement_date->format('Y-m-d') : '-';
        $maturityDate = $loan->maturity_date ? $loan->maturity_date->format('Y-m-d') : '-';

        // Load repayment schedule from database
        $repaymentSchedule = $loan->repaymentSchedules->map(function ($schedule) {
            return [
                'installment_no' => $schedule->installment_number,
                'due_date' => $schedule->due_date->format('Y-m-d'),
                'amount' => (float) $schedule->total_amount,
                'principal' => (float) $schedule->principal_amount,
                'interest' => (float) $schedule->interest_amount,
                'balance_after' => (float) $schedule->balance_after,
                'status' => ucfirst($schedule->status),
            ];
        })->toArray();

        $repaymentHistory = [];
        // Fetch actual loan payments from database
        $actualPayments = \App\Models\LoanPayment::where('loan_id', $loan->id)
            ->orderBy('payment_date', 'desc')
            ->get();
        
        if ($actualPayments->isNotEmpty()) {
            $repaymentHistory = $actualPayments->map(function ($payment) {
                return [
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                    'transaction_ref' => $payment->reference_number ?? 'N/A',
                    'method' => $payment->payment_method ?? 'N/A',
                    'amount' => (float) $payment->payment_amount,
                    'principal' => (float) $payment->principal_amount ?? 0,
                    'balance_after' => null, // Balance after not stored in loan_payments table
                ];
            })->toArray();
        } elseif ($paidAmount > 0 && !empty($repaymentSchedule)) {
            // Fallback to generated history if no actual payments exist
            $paidCount = (int) floor($paidAmount / $installment);
            $paidCount = min($paidCount, count($repaymentSchedule));
            for ($i = 0; $i < $paidCount; $i++) {
                $repaymentHistory[] = array_merge($repaymentSchedule[$i], [
                    'payment_date' => $repaymentSchedule[$i]['due_date'],
                    'transaction_ref' => 'PAY-' . str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                    'method' => 'Bank Transfer',
                ]);
            }
            $remaining = $paidAmount - ($paidCount * $installment);
            if ($remaining > 0 && $paidCount < count($repaymentSchedule)) {
                $repaymentHistory[] = array_merge($repaymentSchedule[$paidCount], [
                    'amount' => $remaining,
                    'payment_date' => $repaymentSchedule[$paidCount]['due_date'],
                    'transaction_ref' => 'PAY-' . str_pad((string) ($paidCount + 1), 6, '0', STR_PAD_LEFT),
                    'method' => 'Partial Payment',
                ]);
            }
        }

        $loanStatement = array_merge(
            [
                [
                    'date' => $disbursementDate,
                    'type' => 'Disbursement',
                    'reference' => $loanNumber,
                    'debit' => 0,
                    'credit' => $loanAmount,
                    'balance' => $loanAmount,
                    'description' => "Loan disbursed",
                ],
            ],
            array_map(static fn ($h) => [
                'date' => $h['payment_date'] ?? $h['due_date'],
                'type' => 'Repayment',
                'reference' => $h['transaction_ref'] ?? 'PAY-000000',
                'debit' => $h['amount'],
                'credit' => 0,
                'balance' => $h['balance_after'] ?? 0,
                'description' => $h['method'] ?? 'Loan Repayment',
            ], $repaymentHistory)
        );

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'loan',
            'subject_id' => $loan->id,
            'description' => "Admin viewed loan {$loanNumber}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'member_number' => $loan->member_number,
                'member_name' => $loan->user->name ?? 'Unknown',
                'loan_amount' => $loanAmount,
            ],
        ]);

        return view('admin.loans.show', [
            'loan' => [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'loan_product' => ucfirst($loan->purpose),
                'loan_amount' => $loanAmount,
                'outstanding_balance' => $outstanding,
                'paid_amount' => $paidAmount,
                'installment' => $installment,
                'status' => $loan->status,
                'maturity_date' => $maturityDate,
                'disbursement_date' => $disbursementDate,
            ],
            'loanNumber' => $loanNumber,
            'member' => [
                'name' => $loan->user->name ?? 'Unknown',
                'member_number' => $loan->member_number,
                'phone' => $loan->user->phone ?? '-',
                'branch' => '-',
            ],
            'progress' => $progress,
            'loanAmount' => $loanAmount,
            'paidAmount' => $paidAmount,
            'outstanding' => $outstanding,
            'installment' => $installment,
            'interestRate' => $interestRate,
            'disbursementDate' => $disbursementDate,
            'maturityDate' => $maturityDate,
            'repaymentSchedule' => $repaymentSchedule,
            'repaymentHistory' => $repaymentHistory,
            'loanStatement' => $loanStatement,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function edit(Request $request, $id)
    {
        Gate::authorize('admin-only');

        $loan = Loan::findOrFail($id);

        return view('admin.loans.edit', [
            'loan' => $loan,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('admin-only');

        $loan = Loan::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'member_number' => 'required|string|max:50',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1',
            'application_date' => 'required|date',
            'approval_date' => 'nullable|date',
            'disbursement_date' => 'nullable|date',
            'maturity_date' => 'nullable|date',
            'monthly_payment' => 'nullable|numeric|min:0',
            'total_amount_due' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,approved,disbursed,active,paid,defaulted,rejected',
            'purpose' => 'required|in:business,education,agriculture,personal,emergency,other',
            'purpose_description' => 'nullable|string',
            'collateral' => 'nullable|string',
            'guarantor' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $loan->update($validated);

        $this->success('Loan updated successfully.');

        return redirect()->route('admin.loans.index');
    }

    public function destroy(Request $request, $id)
    {
        Gate::authorize('admin-only');

        $loan = Loan::findOrFail($id);
        $loan->delete();

        $this->success('Loan deleted successfully.');

        return redirect()->route('admin.loans.index');
    }

    public function approve(Request $request, $encryptedId)
    {
        Gate::authorize('admin-only');

        try {
            $id = $this->encryptedIdService->decrypt($encryptedId);
        } catch (\Exception $e) {
            $this->error('Invalid loan ID.');
            return redirect()->route('admin.loans.index');
        }

        $loan = Loan::findOrFail($id);
        $loan->update([
            'status' => 'approved',
            'approval_date' => now(),
        ]);

        $this->success('Loan approved successfully.');

        return redirect()->back();
    }

    public function disburse(Request $request, $encryptedId)
    {
        Gate::authorize('admin-only');

        try {
            $id = $this->encryptedIdService->decrypt($encryptedId);
        } catch (\Exception $e) {
            $this->error('Invalid loan ID.');
            return redirect()->route('admin.loans.index');
        }

        $loan = Loan::findOrFail($id);
        
        $validated = $request->validate([
            'disbursement_date' => 'required|date',
            'disbursement_method' => 'required|in:bank_transfer,mobile_money,cash,cheque',
            'account_wallet' => 'required|string',
            'maturity_date' => 'required|date',
            'first_repayment_date' => 'nullable|date',
            'monthly_payment' => 'required|numeric|min:0',
            'total_amount_due' => 'required|numeric|min:0',
            'processing_fee' => 'nullable|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $processingFee = $validated['processing_fee'] ?? 0;
        $insuranceFee = $validated['insurance_fee'] ?? 0;
        $otherDeductions = $validated['other_deductions'] ?? 0;
        $totalDeductions = $processingFee + $insuranceFee + $otherDeductions;
        $netAmountPaid = $loan->principal_amount - $totalDeductions;

        // Create disbursement record with sequential number
        $today = date('Ymd');
        $disbursementCountToday = \App\Models\LoanDisbursement::where('disbursement_number', 'like', 'LND' . $today . '%')->count();
        $sequentialNumber = str_pad((string) ($disbursementCountToday + 1), 4, '0', STR_PAD_LEFT);
        \App\Models\LoanDisbursement::create([
            'disbursement_number' => 'LND' . $today . $sequentialNumber,
            'loan_id' => $loan->id,
            'loan_number' => $loan->loan_number,
            'member_number' => $loan->member_number,
            'member_name' => $loan->user->name ?? 'Unknown',
            'loan_product' => $loan->loanProduct->name ?? 'Unknown',
            'approved_amount' => $loan->principal_amount,
            'disbursed_amount' => $loan->principal_amount,
            'disbursement_date' => $validated['disbursement_date'],
            'disbursement_method' => $validated['disbursement_method'],
            'account_wallet' => $validated['account_wallet'],
            'interest_rate' => $loan->interest_rate,
            'repayment_period' => $loan->term_months,
            'first_repayment_date' => $validated['first_repayment_date'] ?? null,
            'maturity_date' => $validated['maturity_date'],
            'processing_fee' => $processingFee,
            'insurance_fee' => $insuranceFee,
            'other_deductions' => $otherDeductions,
            'net_amount_paid' => $netAmountPaid,
            'disbursed_by' => Auth::id(),
            'approved_by' => $loan->approval_date ? Auth::id() : null,
            'status' => 'disbursed',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $loan->update([
            'status' => 'disbursed',
            'disbursement_date' => $validated['disbursement_date'],
            'maturity_date' => $validated['maturity_date'],
            'monthly_payment' => $validated['monthly_payment'],
            'total_amount_due' => $validated['total_amount_due'],
            'balance' => $validated['total_amount_due'],
        ]);

        // Create journal entry for loan disbursement (double-entry)
        $this->createDisbursementJournalEntry($loan, $netAmountPaid, $validated['disbursement_date']);

        // Send SMS notification if enabled
        $this->sendDisbursementSms($loan, $validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin disbursed loan: ' . $loan->loan_number,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success('Loan disbursed successfully.');

        return redirect()->back();
    }

    private function sendDisbursementSms(Loan $loan, array $disbursementData)
    {
        // Check if SMS notifications are enabled in settings
        $settings = Cache::get('admin_settings', []);
        $smsNotificationsEnabled = $settings['sms_notifications'] ?? false;
        
        // Also check if SMS service is active
        $smsSettings = SmsSettings::first();
        if (!$smsSettings || !$smsSettings->is_active) {
            return;
        }

        if (!$smsNotificationsEnabled) {
            return;
        }

        // Check if member has a phone number
        $memberPhone = $loan->user->phone ?? null;
        if (empty($memberPhone)) {
            return;
        }

        $smsService = new SmsService();
        
        // Format phone number (ensure it starts with country code if needed)
        $phone = $this->formatPhoneNumber($memberPhone);
        
        // Create SMS message
        $message = $this->createDisbursementSmsMessage($loan, $disbursementData);
        
        // Send SMS
        try {
            $smsService->sendSingle($phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the disbursement process
            \Log::error('Failed to send disbursement SMS', [
                'loan_number' => $loan->loan_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If number starts with 0, replace with Tanzania country code
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }
        
        // If number doesn't start with country code, add it
        if (!str_starts_with($phone, '255')) {
            $phone = '255' . $phone;
        }
        
        return $phone;
    }

    private function createDisbursementSmsMessage(Loan $loan, array $disbursementData): string
    {
        $memberName = $loan->user->name ?? 'Dear Member';
        $loanNumber = $loan->loan_number;
        $amount = number_format($loan->principal_amount, 2);
        $disbursementDate = $disbursementData['disbursement_date']->format('d/m/Y');
        $maturityDate = $disbursementData['maturity_date']->format('d/m/Y');
        $monthlyPayment = number_format($disbursementData['monthly_payment'], 2);
        
        return "Dear {$memberName}, Your loan {$loanNumber} of TSh {$amount} has been successfully disbursed on {$disbursementDate}. Monthly payment: TSh {$monthlyPayment}. Maturity date: {$maturityDate}. Start making repayments from next month. Thank you, FEEDTAN DIGITAL.";
    }

    private function createDisbursementJournalEntry(Loan $loan, float $amount, string $disbursementDate)
    {
        // Get loan receivable account (asset) and cash/bank account
        $loanReceivableAccount = Account::where('account_type', 'asset')
            ->where('account_subtype', 'loan_receivable')
            ->where('is_active', true)
            ->first();

        $cashAccount = Account::where('account_type', 'asset')
            ->where('account_subtype', 'current_asset')
            ->where('is_active', true)
            ->first();

        if (!$loanReceivableAccount || !$cashAccount) {
            \Log::error('Required accounts not found for loan disbursement journal entry', [
                'loan_number' => $loan->loan_number,
            ]);
            return;
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'entry_number' => 'LOAN-DIS-' . date('Ymd') . '-' . str_pad((string) ($loan->id), 4, '0', STR_PAD_LEFT),
            'entry_date' => $disbursementDate,
            'entry_type' => 'loan_disbursement',
            'description' => "Loan disbursement to {$loan->user->name} ({$loan->loan_number})",
            'reference' => $loan->loan_number,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
            'created_by' => Auth::id(),
        ]);

        // Create journal entry lines (double-entry)
        // Debit: Loan Receivable (Asset increases)
        $journalEntry->lines()->create([
            'account_id' => $loanReceivableAccount->id,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'description' => "Loan disbursement to {$loan->user->name}",
            'member_id' => $loan->user->id,
        ]);

        // Credit: Cash/Bank (Asset decreases)
        $journalEntry->lines()->create([
            'account_id' => $cashAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'description' => "Cash disbursement for loan {$loan->loan_number}",
            'member_id' => $loan->user->id,
        ]);

        // Post the journal entry to update account balances
        $journalEntry->post();
    }

    private function createRepaymentJournalEntry(Loan $loan, float $amount, string $paymentDate)
    {
        // Get loan receivable account (asset) and cash/bank account
        $loanReceivableAccount = Account::where('account_type', 'asset')
            ->where('account_subtype', 'loan_receivable')
            ->where('is_active', true)
            ->first();

        $cashAccount = Account::where('account_type', 'asset')
            ->where('account_subtype', 'current_asset')
            ->where('is_active', true)
            ->first();

        $interestIncomeAccount = Account::where('account_type', 'revenue')
            ->where('account_subtype', 'interest_income')
            ->where('is_active', true)
            ->first();

        if (!$loanReceivableAccount || !$cashAccount) {
            \Log::error('Required accounts not found for loan repayment journal entry', [
                'loan_number' => $loan->loan_number,
            ]);
            return;
        }

        // Calculate interest portion (simplified - could be calculated from schedule)
        $interestRate = (float) $loan->interest_rate;
        $monthlyInterest = $amount * ($interestRate / 100 / 12);
        $principalPortion = $amount - $monthlyInterest;

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'entry_number' => 'LOAN-PAY-' . date('Ymd') . '-' . str_pad((string) ($loan->id), 4, '0', STR_PAD_LEFT),
            'entry_date' => $paymentDate,
            'entry_type' => 'loan_repayment',
            'description' => "Loan repayment from {$loan->user->name} ({$loan->loan_number})",
            'reference' => $loan->loan_number,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
            'created_by' => Auth::id(),
        ]);

        // Create journal entry lines (double-entry)
        // Debit: Cash/Bank (Asset increases)
        $journalEntry->lines()->create([
            'account_id' => $cashAccount->id,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'description' => "Loan repayment from {$loan->user->name}",
            'member_id' => $loan->user->id,
        ]);

        // Credit: Loan Receivable (Asset decreases - principal portion)
        $journalEntry->lines()->create([
            'account_id' => $loanReceivableAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $principalPortion,
            'description' => "Principal repayment for loan {$loan->loan_number}",
            'member_id' => $loan->user->id,
        ]);

        // Credit: Interest Income (Revenue increases - interest portion)
        if ($interestIncomeAccount && $monthlyInterest > 0) {
            $journalEntry->lines()->create([
                'account_id' => $interestIncomeAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $monthlyInterest,
                'description' => "Interest income from loan {$loan->loan_number}",
                'member_id' => $loan->user->id,
            ]);
        }

        // Post the journal entry to update account balances
        $journalEntry->post();
    }

    public function recordPayment(Request $request, string $encryptedLoanNumber)
    {
        try {
            $loanNumber = $this->encryptedIdService->decrypt($encryptedLoanNumber);
        } catch (\Exception $e) {
            $this->error('Invalid loan number.');
            return redirect()->route('admin.loans.index');
        }

        Gate::authorize('admin-only');

        $loan = Loan::where('loan_number', $loanNumber)->first();

        if (!$loan) {
            $this->error("Loan {$loanNumber} not found.");
            return redirect()->route('admin.loans.index');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', "gte:{$loan->monthly_payment}"],
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'notes' => 'nullable|string',
            'allocate_excess_to' => 'nullable|in:savings,investment,refund',
        ], [
            'amount.gte' => "Payment amount must be at least the monthly installment of " . number_format((float) $loan->monthly_payment, 2) . " TSh",
        ]);

        $paymentAmount = (float) $validated['amount'];
        $outstandingBalance = (float) $loan->balance;
        
        // Calculate excess payment
        $excessAmount = max(0, $paymentAmount - $outstandingBalance);
        $loanPaymentAmount = min($paymentAmount, $outstandingBalance);
        
        // Update loan balance and amount paid
        $loan->update([
            'amount_paid' => $loan->amount_paid + $loanPaymentAmount,
            'balance' => max(0, $loan->balance - $loanPaymentAmount),
        ]);

        // Update repayment schedule status
        $this->updateRepaymentScheduleStatus($loan, $loanPaymentAmount, $validated['payment_date']);

        // Create journal entry for loan repayment (double-entry)
        $this->createRepaymentJournalEntry($loan, $loanPaymentAmount, $validated['payment_date']);

        // Create loan payment record with sequential reference number
        $today = date('Ymd');
        $paymentCountToday = \App\Models\LoanPayment::where('reference_number', 'like', 'PAY' . $today . '%')->count();
        $sequentialNumber = str_pad((string) ($paymentCountToday + 1), 4, '0', STR_PAD_LEFT);
        \App\Models\LoanPayment::create([
            'loan_id' => $loan->id,
            'customer_id' => $loan->member_number,
            'payment_amount' => $loanPaymentAmount,
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => 'PAY' . $today . $sequentialNumber,
            'principal_amount' => $loanPaymentAmount,
        ]);

        // Handle excess payment allocation
        if ($excessAmount > 0) {
            $allocationType = $validated['allocate_excess_to'] ?? 'refund';
            
            if ($allocationType === 'savings') {
                // Add to member's savings account
                \App\Models\Transaction::create([
                    'member_code' => $loan->member_number,
                    'date' => $validated['payment_date'],
                    'type' => 'Deposit',
                    'description' => "Excess loan payment from {$loanNumber}",
                    'amount' => $excessAmount,
                    'balance_after' => 0, // Will be calculated
                    'reference' => 'EXC-' . $today . $sequentialNumber,
                ]);
            } elseif ($allocationType === 'investment') {
                // Add to member's investment account
                \App\Models\Transaction::create([
                    'member_code' => $loan->member_number,
                    'date' => $validated['payment_date'],
                    'type' => 'Investment',
                    'description' => "Excess loan payment from {$loanNumber}",
                    'amount' => $excessAmount,
                    'balance_after' => 0, // Will be calculated
                    'reference' => 'EXC-' . $today . $sequentialNumber,
                ]);
            }
            // If 'refund', just record the excess without allocation
        }

        // Check if loan is fully paid
        if ($loan->balance <= 0) {
            $loan->update(['status' => 'paid']);
        }

        $message = 'Payment recorded successfully.';
        if ($excessAmount > 0) {
            $message .= " Excess amount: " . number_format($excessAmount, 2) . ' TSh';
            if (isset($allocationType) && $allocationType !== 'refund') {
                $message .= " allocated to {$allocationType}.";
            } else {
                $message .= " to be refunded.";
            }
        }
        
        $this->success($message);

        return redirect()->back();
    }

    private function updateRepaymentScheduleStatus(Loan $loan, float $paymentAmount, string $paymentDate)
    {
        $schedules = $loan->repaymentSchedules()->where('status', 'pending')->orderBy('installment_number')->get();
        
        $remainingAmount = $paymentAmount;
        
        foreach ($schedules as $schedule) {
            if ($remainingAmount <= 0) break;
            
            $scheduleAmount = (float) $schedule->total_amount;
            
            if ($remainingAmount >= $scheduleAmount) {
                // Full payment for this installment
                $schedule->update([
                    'status' => 'paid',
                    'amount_paid' => $scheduleAmount,
                    'paid_date' => $paymentDate,
                ]);
                $remainingAmount -= $scheduleAmount;
            } else {
                // Partial payment
                $schedule->update([
                    'status' => 'partial',
                    'amount_paid' => $remainingAmount,
                    'paid_date' => $paymentDate,
                ]);
                $remainingAmount = 0;
            }
        }
    }

    public function exportPdf(Request $request, string $encryptedLoanNumber)
    {
        try {
            $loanNumber = $this->encryptedIdService->decrypt($encryptedLoanNumber);
        } catch (\Exception $e) {
            $this->error('Invalid loan number.');
            return redirect()->route('admin.loans.index');
        }
        
        Gate::authorize('admin-only');

        $loan = Loan::with(['user', 'repaymentSchedules'])->where('loan_number', $loanNumber)->first();

        if (!$loan) {
            $this->error("Loan {$loanNumber} not found.");
            return redirect()->route('admin.loans.index');
        }

        $loanAmount = (float) $loan->principal_amount;
        $paidAmount = (float) $loan->amount_paid;
        $outstanding = (float) $loan->balance;
        $progress = $loanAmount > 0 ? min(($paidAmount / $loanAmount) * 100, 100) : 0;

        $installment = (float) $loan->monthly_payment;
        $interestRate = (float) $loan->interest_rate;
        $disbursementDate = $loan->disbursement_date ? $loan->disbursement_date->format('Y-m-d') : '-';
        $maturityDate = $loan->maturity_date ? $loan->maturity_date->format('Y-m-d') : '-';

        $repaymentSchedule = $loan->repaymentSchedules->map(function ($schedule) {
            return [
                'installment_no' => $schedule->installment_number,
                'due_date' => $schedule->due_date->format('Y-m-d'),
                'amount' => (float) $schedule->total_amount,
                'principal' => (float) $schedule->principal_amount,
                'interest' => (float) $schedule->interest_amount,
                'balance_after' => (float) $schedule->balance_after,
                'status' => ucfirst($schedule->status),
            ];
        })->toArray();

        $repaymentHistory = [];
        if ($paidAmount > 0 && !empty($repaymentSchedule)) {
            $paidCount = (int) floor($paidAmount / $installment);
            $paidCount = min($paidCount, count($repaymentSchedule));
            for ($i = 0; $i < $paidCount; $i++) {
                $repaymentHistory[] = array_merge($repaymentSchedule[$i], [
                    'payment_date' => $repaymentSchedule[$i]['due_date'],
                    'transaction_ref' => 'PAY-' . str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                    'method' => 'Bank Transfer',
                ]);
            }
            $remaining = $paidAmount - ($paidCount * $installment);
            if ($remaining > 0 && $paidCount < count($repaymentSchedule)) {
                $repaymentHistory[] = array_merge($repaymentSchedule[$paidCount], [
                    'amount' => $remaining,
                    'payment_date' => $repaymentSchedule[$paidCount]['due_date'],
                    'transaction_ref' => 'PAY-' . str_pad((string) ($paidCount + 1), 6, '0', STR_PAD_LEFT),
                    'method' => 'Partial Payment',
                ]);
            }
        }

        $loanStatement = array_merge(
            [
                [
                    'date' => $disbursementDate,
                    'type' => 'Disbursement',
                    'reference' => $loanNumber,
                    'debit' => 0,
                    'credit' => $loanAmount,
                    'balance' => $loanAmount,
                    'description' => "Loan disbursed",
                ],
            ],
            array_map(static fn ($h) => [
                'date' => $h['payment_date'] ?? $h['due_date'],
                'type' => 'Repayment',
                'reference' => $h['transaction_ref'] ?? 'PAY-000000',
                'debit' => $h['amount'],
                'credit' => 0,
                'balance' => $h['balance_after'] ?? 0,
                'description' => $h['method'] ?? 'Loan Repayment',
            ], $repaymentHistory)
        );

        $member = [
            'name' => $loan->user->name ?? 'Unknown',
            'member_number' => $loan->member_number,
            'phone' => $loan->user->phone ?? '-',
            'branch' => '-',
        ];

        $pdf = Pdf::loadView('admin.loans.pdf', compact(
            'loan',
            'loanNumber',
            'member',
            'loanAmount',
            'paidAmount',
            'outstanding',
            'progress',
            'installment',
            'interestRate',
            'disbursementDate',
            'maturityDate',
            'repaymentSchedule',
            'repaymentHistory',
            'loanStatement'
        ));

        return $pdf->download("loan_statement_{$loanNumber}.pdf");
    }

    public function exportCsv(Request $request, string $encryptedLoanNumber)
    {
        try {
            $loanNumber = $this->encryptedIdService->decrypt($encryptedLoanNumber);
        } catch (\Exception $e) {
            $this->error('Invalid loan number.');
            return redirect()->route('admin.loans.index');
        }
        
        Gate::authorize('admin-only');

        $loan = Loan::with(['user', 'repaymentSchedules'])->where('loan_number', $loanNumber)->first();

        if (!$loan) {
            $this->error("Loan {$loanNumber} not found.");
            return redirect()->route('admin.loans.index');
        }

        $loanAmount = (float) $loan->principal_amount;
        $paidAmount = (float) $loan->amount_paid;
        $outstanding = (float) $loan->balance;
        $progress = $loanAmount > 0 ? min(($paidAmount / $loanAmount) * 100, 100) : 0;

        $installment = (float) $loan->monthly_payment;
        $interestRate = (float) $loan->interest_rate;
        $disbursementDate = $loan->disbursement_date ? $loan->disbursement_date->format('Y-m-d') : '-';
        $maturityDate = $loan->maturity_date ? $loan->maturity_date->format('Y-m-d') : '-';

        $repaymentSchedule = $loan->repaymentSchedules->map(function ($schedule) {
            return [
                'installment_no' => $schedule->installment_number,
                'due_date' => $schedule->due_date->format('Y-m-d'),
                'amount' => (float) $schedule->total_amount,
                'principal' => (float) $schedule->principal_amount,
                'interest' => (float) $schedule->interest_amount,
                'balance_after' => (float) $schedule->balance_after,
                'status' => ucfirst($schedule->status),
            ];
        })->toArray();

        $repaymentHistory = [];
        if ($paidAmount > 0 && !empty($repaymentSchedule)) {
            $paidCount = (int) floor($paidAmount / $installment);
            $paidCount = min($paidCount, count($repaymentSchedule));
            for ($i = 0; $i < $paidCount; $i++) {
                $repaymentHistory[] = array_merge($repaymentSchedule[$i], [
                    'payment_date' => $repaymentSchedule[$i]['due_date'],
                    'transaction_ref' => 'PAY-' . str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                    'method' => 'Bank Transfer',
                ]);
            }
            $remaining = $paidAmount - ($paidCount * $installment);
            if ($remaining > 0 && $paidCount < count($repaymentSchedule)) {
                $repaymentHistory[] = array_merge($repaymentSchedule[$paidCount], [
                    'amount' => $remaining,
                    'payment_date' => $repaymentSchedule[$paidCount]['due_date'],
                    'transaction_ref' => 'PAY-' . str_pad((string) ($paidCount + 1), 6, '0', STR_PAD_LEFT),
                    'method' => 'Partial Payment',
                ]);
            }
        }

        $loanStatement = array_merge(
            [
                [
                    'date' => $disbursementDate,
                    'type' => 'Disbursement',
                    'reference' => $loanNumber,
                    'debit' => 0,
                    'credit' => $loanAmount,
                    'balance' => $loanAmount,
                    'description' => "Loan disbursed",
                ],
            ],
            array_map(static fn ($h) => [
                'date' => $h['payment_date'] ?? $h['due_date'],
                'type' => 'Repayment',
                'reference' => $h['transaction_ref'] ?? 'PAY-000000',
                'debit' => $h['amount'],
                'credit' => 0,
                'balance' => $h['balance_after'] ?? 0,
                'description' => $h['method'] ?? 'Loan Repayment',
            ], $repaymentHistory)
        );

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=loan_statement_{$loanNumber}.csv",
        ];

        $callback = function () use ($loanStatement) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Reference', 'Description', 'Debit', 'Credit', 'Balance']);
            
            foreach ($loanStatement as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['type'],
                    $row['reference'],
                    $row['description'],
                    $row['debit'],
                    $row['credit'],
                    $row['balance'],
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importLoanPayments(Request $request)
    {
        Gate::authorize('admin-only');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $import = new \App\Imports\LoanPaymentsImport();

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
            
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();

            $this->success("Loan payments imported successfully. Imported: {$importedCount} records.");

            ActivityLog::create([
                'user_id' => Auth::id(),
                'subject_type' => 'loan_payment',
                'subject_id' => null,
                'description' => 'Admin imported loan payments',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => [
                    'imported_count' => $importedCount,
                    'skipped_count' => $skippedCount,
                ],
            ]);

            return redirect()->back();
        } catch (\Exception $e) {
            $this->error('Error importing loan payments: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function importLoansInformation(Request $request)
    {
        Gate::authorize('admin-only');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $import = new \App\Imports\LoansInformationImport();

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
            
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();

            $this->success("Loans information imported successfully. Imported: {$importedCount} records.");

            ActivityLog::create([
                'user_id' => Auth::id(),
                'subject_type' => 'loans_information',
                'subject_id' => null,
                'description' => 'Admin imported loans information',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => [
                    'imported_count' => $importedCount,
                    'skipped_count' => $skippedCount,
                ],
            ]);

            return redirect()->back();
        } catch (\Exception $e) {
            $this->error('Error importing loans information: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function appreciationCertificate($encryptedLoanNumber)
    {
        Gate::authorize('admin-only');

        $loanNumber = $this->encryptedIdService->decrypt($encryptedLoanNumber);
        $loan = Loan::where('loan_number', $loanNumber)->with('user')->firstOrFail();

        $settings = Cache::get('share_settings', []);
        $certificateBackgroundPath = $settings['certificate_background'] ?? '';
        $certificateBackgroundUrl = $certificateBackgroundPath ? asset('storage/' . $certificateBackgroundPath) : '';

        return view('admin.loans.appreciation-certificate', [
            'loan' => $loan,
            'certificateBackgroundUrl' => $certificateBackgroundUrl,
        ]);
    }
}
