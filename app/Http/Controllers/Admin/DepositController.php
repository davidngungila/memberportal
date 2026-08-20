<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Deposit;
use App\Models\JournalEntry;
use App\Models\SavingsProduct;
use App\Models\User;
use App\Services\AdminDashboardService;
use App\Services\EncryptedIdService;
use App\Services\MemberService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DepositController extends Controller
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
        Gate::authorize('admin-only');

        $perPage = (int) $request->input('per_page', 15);
        $sortColumn = $request->input('sort', 'certificate_number');
        $sortDirection = $request->input('sort_direction', 'asc');
        $statusFilter = $request->input('status', '');
        $searchQuery = $request->input('q', '');

        $query = Deposit::with(['user', 'product']);

        if (!empty($statusFilter)) {
            $query->byStatus($statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('certificate_number', 'like', "%{$searchQuery}%")
                  ->orWhere('member_number', 'like', "%{$searchQuery}%")
                  ->orWhereHas('user', function ($userQuery) use ($searchQuery) {
                      $userQuery->where('name', 'like', "%{$searchQuery}%");
                  });
            });
        }

        $deposits = $query->orderBy($sortColumn, $sortDirection)->paginate($perPage);

        $deposits->appends([
            'q' => $searchQuery,
            'status' => $statusFilter,
            'per_page' => $perPage,
            'sort' => $sortColumn,
            'sort_direction' => $sortDirection,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed deposits list',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'search_query' => $searchQuery,
                'status_filter' => $statusFilter,
                'per_page' => $perPage,
                'sort' => $sortColumn,
                'sort_direction' => $sortDirection,
                'total_count' => $deposits->total(),
            ],
        ]);

        return view('admin.deposits.index', [
            'deposits' => $deposits,
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
            'perPage' => $perPage,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
            'memberService' => $this->memberService,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    public function create()
    {
        Gate::authorize('admin-only');

        $members = User::where('role', 'member')->get();
        $products = SavingsProduct::active()->get();

        return view('admin.deposits.create', compact('members', 'products'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'nullable|exists:savings_products,id',
            'amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'maturity_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        $user = User::findOrFail($validated['user_id']);
        
        // Generate certificate number
        $certificateNumber = 'DEP-' . date('Y') . '-' . str_pad(Deposit::count() + 1, 6, '0', STR_PAD_LEFT);

        try {
            $deposit = Deposit::create([
                'user_id' => $validated['user_id'],
                'member_number' => $user->membercode,
                'certificate_number' => $certificateNumber,
                'product_id' => $validated['product_id'] ?? null,
                'amount' => $validated['amount'],
                'interest_rate' => $validated['interest_rate'],
                'interest_earned' => 0,
                'current_value' => $validated['amount'],
                'start_date' => $validated['start_date'],
                'maturity_date' => $validated['maturity_date'],
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create journal entry for deposit (double-entry)
            $this->createDepositJournalEntry($deposit, $validated['amount'], $validated['start_date']);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'subject_type' => 'deposit',
                'subject_id' => $deposit->id,
                'description' =>                 "Admin created deposit {$certificateNumber} for member {$user->membercode}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => [
                'member_number' => $user->membercode,
                    'member_name' => $user->name,
                    'certificate_number' => $certificateNumber,
                    'amount' => $validated['amount'],
                    'product_id' => $validated['product_id'] ?? null,
                ],
            ]);

            $this->success('Deposit created successfully!');
            return redirect()->route('admin.deposits.index');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create deposit: ' . $e->getMessage());
        }
    }

    public function show(Request $request, string $encryptedCertificateNumber)
    {
        $certificateNumber = $this->encryptedIdService->decrypt($encryptedCertificateNumber);
        
        Gate::authorize('admin-only');

        $deposit = Deposit::with(['user', 'product'])->where('certificate_number', $certificateNumber)->firstOrFail();

        $progress = 0;
        if ($deposit->start_date && $deposit->maturity_date) {
            $startTs = $deposit->start_date->timestamp;
            $maturityTs = $deposit->maturity_date->timestamp;
            $now = now()->timestamp;
            if ($maturityTs > $startTs) {
                $progress = min(100, max(0, (($now - $startTs) / ($maturityTs - $startTs)) * 100));
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'deposit',
            'subject_id' => $deposit->id,
            'description' => "Admin viewed deposit: {$certificateNumber}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'member_number' => $deposit->member_number,
                'member_name' => $deposit->user->name,
                'amount' => $deposit->amount,
            ],
        ]);

        return view('admin.deposits.show', [
            'deposit' => $deposit,
            'certificateNumber' => $certificateNumber,
            'member' => $deposit->user,
            'amount' => $deposit->amount,
            'interest' => $deposit->interest_earned,
            'interestRate' => $deposit->interest_rate,
            'currentValue' => $deposit->current_value,
            'startDate' => $deposit->start_date->format('Y-m-d'),
            'maturityDate' => $deposit->maturity_date->format('Y-m-d'),
            'progress' => $progress,
            'dashboardService' => $this->dashboardService,
        ]);
    }

    private function createDepositJournalEntry(Deposit $deposit, float $amount, string $startDate)
    {
        // Get savings/deposit account (liability) and cash/bank account
        $savingsAccount = Account::where('account_type', 'liability')
            ->where('account_subtype', 'savings_deposit')
            ->where('is_active', true)
            ->first();

        $cashAccount = Account::where('account_type', 'asset')
            ->where('account_subtype', 'current_asset')
            ->where('is_active', true)
            ->first();

        if (!$savingsAccount || !$cashAccount) {
            \Log::error('Required accounts not found for deposit journal entry', [
                'certificate_number' => $deposit->certificate_number,
            ]);
            return;
        }

        $userName = $deposit->user ? $deposit->user->name : 'Unknown';

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'entry_number' => 'DEP-' . date('Ymd') . '-' . str_pad((string) ($deposit->id), 4, '0', STR_PAD_LEFT),
            'entry_date' => $startDate,
            'entry_type' => 'deposit',
            'description' => "Deposit from {$userName} ({$deposit->certificate_number})",
            'reference' => $deposit->certificate_number,
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
            'description' => "Deposit payment from {$userName}",
        ]);

        // Credit: Savings/Deposit (Liability increases)
        $journalEntry->lines()->create([
            'account_id' => $savingsAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'description' => "Savings deposit from {$userName}",
        ]);

        // Post the journal entry to update account balances
        $journalEntry->post();
    }
}
