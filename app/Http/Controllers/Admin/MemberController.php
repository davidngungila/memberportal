<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Exports\MembersTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\MembersImport;
use App\Jobs\ImportMembersJob;
use App\Models\ActivityLog;
use App\Services\AdminDashboardService;
use App\Services\EncryptedIdService;
use App\Services\MemberService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected MemberService $memberService,
        protected AdminDashboardService $dashboardService,
        protected EncryptedIdService $encryptedIdService,
    ) {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $sortColumn = $request->input('sort', 'member_number');
        $sortDirection = $request->input('sort_direction', 'asc');

        // Get all members from database for client-side filtering
        $dbMembersQuery = \App\Models\Member::query();
        
        // Apply search filter to database members (for initial load with search query)
        if ($request->filled('q')) {
            $searchTerm = $request->input('q');
            $dbMembersQuery->where(function($query) use ($searchTerm) {
                $query->where('member_number', 'like', '%' . $searchTerm . '%')
                      ->orWhere('full_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Get all members (without pagination for client-side filtering)
        $allDbMembers = \App\Models\Member::query()->get()->map(function($member) {
            $statusBadge = $this->dashboardService->memberStatusBadge($member->status);
            return [
                'id' => $member->id,
                'member_number' => $member->member_number,
                'encrypted_id' => $this->encryptedIdService->encrypt($member->member_number),
                'name' => $member->full_name,
                'gender' => $member->gender,
                'phone' => $member->phone,
                'email' => $member->email,
                'branch' => $member->branch ?? '-',
                'status' => $member->status,
                'status_badge_class' => $statusBadge['class'],
                'status_badge_label' => $statusBadge['label'],
                'photo' => $member->photo,
            ];
        })->toArray();

        // Use the filtered or all members for pagination
        $dbMembers = $dbMembersQuery->get()->map(function($member) {
            return [
                'id' => $member->id,
                'member_number' => $member->member_number,
                'name' => $member->full_name,
                'gender' => $member->gender,
                'phone' => $member->phone,
                'email' => $member->email,
                'branch' => $member->branch ?? '-',
                'status' => $member->status,
                'photo' => $member->photo,
            ];
        })->toArray();

        // Use only database members
        $membersMap = [];
        foreach ($dbMembers as $member) {
            $membersMap[$member['member_number']] = $member;
        }
        $members = array_values($membersMap);

        $members = $this->memberService->sort($members, $sortColumn, $sortDirection);
        $chunked = $this->memberService->chunkArray($members, $perPage);
        $paginated = $this->memberService->paginateArray($members, $perPage);

        $paginated->appends([
            'q' => $request->input('q'),
            'per_page' => $perPage,
            'sort' => $sortColumn,
            'sort_direction' => $sortDirection,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed members list',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'search_query' => $request->input('q'),
                'per_page' => $perPage,
                'sort' => $sortColumn,
                'sort_direction' => $sortDirection,
                'total_count' => count($members),
            ],
        ]);

        return view('admin.members.index', [
            'members' => $paginated,
            'allMembers' => $allDbMembers,
            'searchQuery' => $request->input('q'),
            'perPage' => $perPage,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
            'memberService' => $this->memberService,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function show(Request $request, string $memberNumber)
    {
        // Check if the member number is encrypted or plain
        $isEncrypted = strlen($memberNumber) > 10; // Encrypted IDs are typically longer
        
        if ($isEncrypted) {
            try {
                $memberNumber = $this->encryptedIdService->decrypt($memberNumber);
            } catch (\Exception $e) {
                // If decryption fails, treat as plain member number
                $memberNumber = $request->route('memberNumber');
            }
        }
        
        Gate::authorize('view-member-data', $memberNumber);

        // First check database for imported members
        $dbMember = \App\Models\Member::where('member_number', $memberNumber)->first();
        
        if ($dbMember) {
            $member = [
                'member_number' => $dbMember->member_number,
                'name' => $dbMember->full_name,
                'gender' => $dbMember->gender,
                'phone' => $dbMember->phone,
                'email' => $dbMember->email,
                'branch' => $dbMember->branch ?? '-',
                'status' => $dbMember->status,
                'registration_date' => $dbMember->registration_date,
                'date_of_birth' => $dbMember->date_of_birth,
                'national_id' => $dbMember->national_id,
                'occupation' => $dbMember->occupation,
                'employer' => $dbMember->employer,
                'residential_address' => $dbMember->residential_address,
                'photo' => $dbMember->photo,
                'member_type' => $dbMember->member_type,
                'marital_status' => $dbMember->marital_status,
                'bank_name' => $dbMember->bank_name,
                'bank_branch' => $dbMember->bank_branch,
                'account_name' => $dbMember->account_name,
                'account_number' => $dbMember->account_number,
                'bank_account_status' => $dbMember->bank_account_status,
                'mobile_money_provider' => $dbMember->mobile_money_provider,
                'mobile_money_number' => $dbMember->mobile_money_number,
                'emergency_contact_name' => $dbMember->emergency_contact_name,
                'emergency_contact_phone' => $dbMember->emergency_contact_phone,
                'emergency_contact_relationship' => $dbMember->emergency_contact_relationship,
                'registration_fee' => $dbMember->registration_fee,
                'notes' => $dbMember->notes,
            ];
            
            // Try to get photo from User table if not in Member table
            if (empty($member['photo'])) {
                $user = \App\Models\User::where('member_number', $memberNumber)->first();
                if ($user && $user->photo) {
                    $member['photo'] = $user->photo;
                }
            }
            
            // Get loans from database
            $loans = [];
            try {
                $dbLoans = \App\Models\LoanInformation::where('customer_id', $memberNumber)
                    ->orWhere('user_id', function($query) use ($memberNumber) {
                        $query->select('id')->from('users')->where('member_number', $memberNumber);
                    })
                    ->get();
                
                foreach ($dbLoans as $dbLoan) {
                    $user = \App\Models\User::find($dbLoan->user_id);
                    $loans[] = [
                        'loan_number' => $dbLoan->loan_number,
                        'loan_product' => $dbLoan->loan_product,
                        'loan_amount' => (float) $dbLoan->loan_amount,
                        'status' => $dbLoan->status,
                        'member_name' => $user ? $user->name : 'Unknown',
                        'member_number' => $memberNumber,
                        'source' => 'database'
                    ];
                }
            } catch (\Exception $e) {
                // Table might not exist, skip loans
            }
            
            // Get savings from database
            $savings = [
                'balance' => 0,
                'running_balance' => 0,
                'transactions' => []
            ];
            
            try {
                $dbTransactions = \App\Models\Transaction::byMemberCode($memberNumber)
                    ->orderBy('date', 'asc')
                    ->get()
                    ->map(function ($transaction) {
                        return [
                            'date' => $transaction->date->format('Y-m-d'),
                            'type' => $transaction->transaction_type,
                            'amount' => (float) $transaction->amount,
                            'reference' => $transaction->reference_no ?? '',
                            'balance_after' => null,
                            'source' => 'database'
                        ];
                    })
                    ->toArray();

                // Calculate running balance
                $currentBalance = 0;
                foreach ($dbTransactions as &$transaction) {
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
                usort($dbTransactions, static fn($a, $b): int => strtotime($b['date'] ?? '') <=> strtotime($a['date'] ?? ''));

                $savings['transactions'] = $dbTransactions;
                $savings['running_balance'] = $currentBalance;
                $savings['balance'] = $currentBalance;
            } catch (\Exception $e) {
                // Table might not exist, skip transactions
            }
            
            // Get deposits from database (model may not exist yet)
            $deposits = [];
            try {
                if (class_exists('\App\Models\Deposit')) {
                    $dbDeposits = \App\Models\Deposit::where('member_number', $memberNumber)
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    foreach ($dbDeposits as $dbDeposit) {
                        $deposits[] = [
                            'certificate_number' => $dbDeposit->certificate_number,
                            'deposit_amount' => (float) $dbDeposit->deposit_amount,
                            'deposit_date' => $dbDeposit->deposit_date ? $dbDeposit->deposit_date->format('Y-m-d') : null,
                            'maturity_date' => $dbDeposit->maturity_date ? $dbDeposit->maturity_date->format('Y-m-d') : null,
                            'interest_rate' => (float) $dbDeposit->interest_rate,
                            'status' => $dbDeposit->status,
                            'source' => 'database'
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Table might not exist, skip deposits
            }
            
            // Get SWF from database (model may not exist yet)
            $swf = [];
            try {
                if (class_exists('\App\Models\Swf')) {
                    $dbSwf = \App\Models\Swf::where('member_number', $memberNumber)
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    foreach ($dbSwf as $dbSwfItem) {
                        $swf[] = [
                            'amount' => (float) $dbSwfItem->amount,
                            'date' => $dbSwfItem->date ? $dbSwfItem->date->format('Y-m-d') : null,
                            'status' => $dbSwfItem->status,
                            'source' => 'database'
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Table might not exist, skip SWF
            }
            
            // Get investments from database (model may not exist yet)
            $investments = [];
            try {
                if (class_exists('\App\Models\Investment')) {
                    $dbInvestments = \App\Models\Investment::where('member_number', $memberNumber)
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    foreach ($dbInvestments as $dbInvestment) {
                        $investments[] = [
                            'amount' => (float) $dbInvestment->amount,
                            'date' => $dbInvestment->date ? $dbInvestment->date->format('Y-m-d') : null,
                            'status' => $dbInvestment->status,
                            'source' => 'database'
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Table might not exist, skip investments
            }
            
            // Get loan payments and loans information from database
            $loanPayments = [];
            $loansInformation = [];
            try {
                $user = \App\Models\User::where('member_number', $memberNumber)->first();
                if ($user) {
                    $loanPayments = \App\Models\LoanPayment::byUserId($user->id)
                        ->orderBy('payment_date', 'desc')
                        ->get()
                        ->map(function ($payment) {
                            return [
                                'loan_id' => $payment->loan_id,
                                'customer_id' => $payment->customer_id,
                                'payment_amount' => (float) $payment->payment_amount,
                                'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                                'payment_method' => $payment->payment_method,
                                'reference_number' => $payment->reference_number,
                                'principal_amount' => (float) $payment->principal_amount,
                            ];
                        })
                        ->toArray();
                    
                    $loansInformation = \App\Models\LoanInformation::byUserId($user->id)
                        ->orderBy('loan_start_date', 'desc')
                        ->get()
                        ->map(function ($loan) {
                            return [
                                'loan_id' => $loan->loan_id,
                                'customer_id' => $loan->customer_id,
                                'loan_type' => $loan->loan_type,
                                'loan_amount' => (float) $loan->loan_amount,
                                'nature' => $loan->nature,
                                'interest_rate_pm' => (float) $loan->interest_rate_pm,
                                'duration_months' => $loan->duration_months,
                                'loan_start_date' => $loan->loan_start_date ? $loan->loan_start_date->format('Y-m-d') : null,
                                'loan_maturity_date' => $loan->loan_maturity_date ? $loan->loan_maturity_date->format('Y-m-d') : null,
                                'total_payable' => (float) $loan->total_payable,
                                'monthly_installment' => (float) $loan->monthly_installment,
                                'monthly_principal' => (float) $loan->monthly_principal,
                                'principal_paid_to_date' => (float) $loan->principal_paid_to_date,
                                'termination_fee' => (float) $loan->termination_fee,
                                'total_paid' => (float) $loan->total_paid,
                                'outstanding_balance' => (float) $loan->outstanding_balance,
                                'loan_status' => $loan->loan_status,
                                'loan_guarantor' => $loan->loan_guarantor,
                                'number_of_paid_installments' => $loan->number_of_paid_installments,
                                'number_of_unpaid_installments' => $loan->number_of_unpaid_installments,
                                'this_month_loan_status' => $loan->this_month_loan_status,
                                'balance_after_payment' => (float) $loan->balance_after_payment,
                                'loan_agreement_ref_no' => $loan->loan_agreement_ref_no,
                            ];
                        })
                        ->toArray();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                // Table doesn't exist yet
            }
            
            $loans = $loansInformation;
        } else {
            // Member not in database - return error
            $this->error("Member {$memberNumber} not found in database.");
            return redirect()->route('admin.members.index');
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'member',
            'subject_id' => null,
            'description' => "Admin viewed member profile: {$memberNumber}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'member_name' => $member['name'] ?? null,
            ],
        ]);

        return view('admin.members.show', [
            'member' => $member,
            'memberNumber' => $memberNumber,
            'encryptedMemberNumber' => $this->encryptedIdService->encrypt($memberNumber),
            'loans' => $loans,
            'savings' => $savings,
            'deposits' => $deposits,
            'swf' => $swf,
            'investments' => $investments,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function loans(Request $request, string $encryptedMemberNumber)
    {
        $memberNumber = $this->encryptedIdService->decrypt($encryptedMemberNumber);
        
        Gate::authorize('view-member-data', $memberNumber);

        // Get member from database
        $member = \App\Models\Member::where('member_number', $memberNumber)->first();
        
        if (!$member) {
            $this->error("Member {$memberNumber} not found in database.");
            return redirect()->route('admin.members.index');
        }

        // Get loans from database
        $loans = [];
        try {
            $user = \App\Models\User::where('member_number', $memberNumber)->first();
            if ($user) {
                $loans = \App\Models\LoanInformation::byUserId($user->id)
                    ->orderBy('loan_start_date', 'desc')
                    ->get()
                    ->map(function ($loan) {
                        return [
                            'loan_id' => $loan->loan_id,
                            'customer_id' => $loan->customer_id,
                            'loan_type' => $loan->loan_type,
                            'loan_amount' => (float) $loan->loan_amount,
                            'nature' => $loan->nature,
                            'interest_rate_pm' => (float) $loan->interest_rate_pm,
                            'duration_months' => $loan->duration_months,
                            'loan_start_date' => $loan->loan_start_date ? $loan->loan_start_date->format('Y-m-d') : null,
                            'loan_maturity_date' => $loan->loan_maturity_date ? $loan->loan_maturity_date->format('Y-m-d') : null,
                            'total_payable' => (float) $loan->total_payable,
                            'monthly_installment' => (float) $loan->monthly_installment,
                            'monthly_principal' => (float) $loan->monthly_principal,
                            'principal_paid_to_date' => (float) $loan->principal_paid_to_date,
                            'termination_fee' => (float) $loan->termination_fee,
                            'total_paid' => (float) $loan->total_paid,
                            'outstanding_balance' => (float) $loan->outstanding_balance,
                            'loan_status' => $loan->loan_status,
                            'loan_guarantor' => $loan->loan_guarantor,
                            'number_of_paid_installments' => $loan->number_of_paid_installments,
                            'number_of_unpaid_installments' => $loan->number_of_unpaid_installments,
                            'this_month_loan_status' => $loan->this_month_loan_status,
                            'balance_after_payment' => (float) $loan->balance_after_payment,
                            'loan_agreement_ref_no' => $loan->loan_agreement_ref_no,
                        ];
                    })
                    ->toArray();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist yet
            $loans = [];
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'member',
            'subject_id' => null,
            'description' => "Admin viewed member loans: {$memberNumber}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'member' => [
                    'member_number' => $member->member_number,
                    'name' => $member->full_name,
                ],
                'loans' => $loans,
            ]);
        }

        return view('admin.members.partials.loans', [
            'member' => [
                'member_number' => $member->member_number,
                'name' => $member->full_name,
            ],
            'loans' => $loans,
            'memberNumber' => $memberNumber,
            'encryptedMemberNumber' => $encryptedMemberNumber,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function savings(Request $request, string $encryptedMemberNumber)
    {
        $memberNumber = $this->encryptedIdService->decrypt($encryptedMemberNumber);
        
        Gate::authorize('view-member-data', $memberNumber);

        $member = $this->googleSheetRepository->getMemberByNumber($memberNumber);
        $savings = $this->googleSheetRepository->getMemberSavings($memberNumber);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'member',
            'subject_id' => null,
            'description' => "Admin viewed member savings: {$memberNumber}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'member' => $member,
                'savings' => $savings,
            ]);
        }

        return view('admin.members.partials.savings', [
            'member' => $member,
            'savings' => $savings,
            'memberNumber' => $memberNumber,
            'encryptedMemberNumber' => $encryptedMemberNumber,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function import(Request $request)
    {
        Gate::authorize('admin-only');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            // Increase execution time for large files
            set_time_limit(300);
            
            $jobId = Str::uuid()->toString();
            $file = $request->file('file');
            
            // Process import directly from uploaded file without storing
            $googleSheetRepository = $this->googleSheetRepository;
            $import = new \App\Imports\MembersImport($googleSheetRepository);
            
            Excel::import($import, $file);

            $importedCount = $import->getImportedCount();
            $errors = $import->getErrors();
            $createdUsers = $import->getCreatedUsers();

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'description' => 'Admin imported members from Excel',
                'properties' => [
                    'imported_count' => $importedCount,
                    'created_users_count' => count($createdUsers),
                    'created_users' => $createdUsers,
                    'errors_count' => count($errors),
                    'errors' => $errors,
                ],
            ]);

            return response()->json([
                'success' => true,
                'job_id' => $jobId,
                'message' => 'Import completed successfully',
                'imported' => $importedCount,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('Import failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to import: ' . $e->getMessage(),
                'error_type' => get_class($e),
            ], 500);
        }
    }

    public function importProgress($jobId)
    {
        Gate::authorize('admin-only');

        $progress = Cache::get("import_{$jobId}", [
            'status' => 'pending',
            'progress' => 0,
            'message' => 'Waiting to start...',
            'imported' => 0,
            'total' => 0,
        ]);

        return response()->json($progress);
    }

    public function downloadTemplate()
    {
        Gate::authorize('admin-only');

        return Excel::download(new MembersTemplateExport, 'members_import_template.xlsx');
    }
}
