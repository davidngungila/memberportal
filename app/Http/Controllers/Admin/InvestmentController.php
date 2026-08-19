<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Investment;
use App\Models\InvestmentProduct;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\AdminDashboardService;
use App\Services\EncryptedIdService;
use App\Services\MemberService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvestmentController extends Controller
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
        $sortColumn = $request->input('sort', 'investment_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $searchQuery = $request->input('q', '');
        $statusFilter = $request->input('status', '');

        $query = Investment::with(['investmentProduct']);

        // Search
        if (!empty($searchQuery)) {
            $query->where('investment_number', 'like', '%' . $searchQuery . '%')
                  ->orWhere('member_number', 'like', '%' . $searchQuery . '%');
        }

        // Filter by status
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        // Sort
        $query->orderBy($sortColumn, $sortDirection);

        $investments = $query->paginate($perPage);

        // Load all users by member_number for efficient lookup
        $memberNumbers = $investments->pluck('member_number')->filter()->unique();
        $usersByMemberNumber = User::whereIn('member_number', $memberNumbers)
            ->pluck('name', 'member_number')
            ->toArray();

        // Enrich investments with calculated values while preserving pagination
        $investments->through(function ($inv) use ($usersByMemberNumber) {
            $memberNo = $inv->member_number ?? '-';
            
            // Try to get member name from cache first
            $memberName = $usersByMemberNumber[$memberNo] ?? null;
            
            // If still not found, use member_number as fallback
            if (empty($memberName)) {
                $memberName = $memberNo;
            }
            
            $product = $inv->investmentProduct ? $inv->investmentProduct->code : 'Unknown';
            $amountInvested = $inv->amount ?? 0;
            $currentValue = $inv->actual_return ?? 0;
            $profit = ($inv->actual_return ?? 0) - ($inv->amount ?? 0);
            $returnPct = $inv->amount > 0 ? (($profit / $inv->amount) * 100) : 0;
            $startDate = $inv->investment_date ? $inv->investment_date->format('Y-m-d') : '-';
            $status = $this->dashboardService->depositStatusBadge($inv->status ?? 'pending');
            $profitClass = $profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
            $profitIcon = $profit >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';

            return (object) [
                'investment' => $inv,
                'member_no' => $memberNo,
                'member_name' => $memberName,
                'product' => $product,
                'amount_invested' => $amountInvested,
                'current_value' => $currentValue,
                'profit' => $profit,
                'return_pct' => $returnPct,
                'start_date' => $startDate,
                'status' => $status,
                'profit_class' => $profitClass,
                'profit_icon' => $profitIcon,
            ];
        });

        $investments->appends([
            'q' => $searchQuery,
            'status' => $statusFilter,
            'per_page' => $perPage,
            'sort' => $sortColumn,
            'sort_direction' => $sortDirection,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed investments list',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'search_query' => $searchQuery,
                'status_filter' => $statusFilter,
                'per_page' => $perPage,
                'sort' => $sortColumn,
                'sort_direction' => $sortDirection,
                'total_count' => $investments->total(),
            ],
        ]);

        return view('admin.investments.index', [
            'investments' => $investments,
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

        $members = \App\Models\Member::all(['id', 'membercode', 'full_name']);
        $products = InvestmentProduct::active()->get();

        return view('admin.investments.create', [
            'members' => $members,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');

        $validated = $request->validate([
            'member_number' => ['required', 'exists:members,membercode'],
            'investment_product_id' => ['required', 'exists:investment_products,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'investment_date' => ['required', 'date'],
            'maturity_date' => ['nullable', 'date', 'after:investment_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $member = \App\Models\Member::where('membercode', $validated['member_number'])->first();
        $product = InvestmentProduct::find($validated['investment_product_id']);

        if (!$member) {
            $this->error('Member not found');
            return redirect()->back()->withInput();
        }

        if (!$product) {
            $this->error('Investment product not found');
            return redirect()->back()->withInput();
        }

        // Validate amount against product limits
        if ($product->min_investment && $validated['amount'] < $product->min_investment) {
            $this->error("Minimum investment for this product is {$product->min_investment}");
            return redirect()->back()->withInput();
        }

        if ($product->max_investment && $validated['amount'] > $product->max_investment) {
            $this->error("Maximum investment for this product is {$product->max_investment}");
            return redirect()->back()->withInput();
        }

        // Calculate maturity date if not provided
        if (empty($validated['maturity_date']) && $product->duration_months) {
            $maturityDate = \Carbon\Carbon::parse($validated['investment_date'])->addMonths($product->duration_months);
            $validated['maturity_date'] = $maturityDate->format('Y-m-d');
        }

        // Generate investment number
        $investmentNumber = 'INV-' . strtoupper(uniqid());

        // Calculate expected return
        $interestRate = $product->interest_rate ?? 0;
        $expectedReturn = $validated['amount'] * (1 + ($interestRate / 100));

        $investment = Investment::create([
            'user_id' => $member->user_id,
            'investment_product_id' => $validated['investment_product_id'],
            'member_number' => $validated['member_number'],
            'investment_number' => $investmentNumber,
            'amount' => $validated['amount'],
            'investment_date' => $validated['investment_date'],
            'maturity_date' => $validated['maturity_date'] ?? null,
            'interest_rate' => $interestRate,
            'expected_return' => $expectedReturn,
            'actual_return' => $validated['amount'], // Initially same as invested amount
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create journal entry for investment (double-entry)
        $this->createInvestmentJournalEntry($investment, $validated['amount'], $validated['investment_date']);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'investment',
            'subject_id' => $investment->id,
            'description' => "Admin created investment for member {$validated['member_number']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'member_number' => $validated['member_number'],
                'investment_number' => $investmentNumber,
                'amount' => $validated['amount'],
                'product' => $product->name,
            ],
        ]);

        $this->success('Investment created successfully');
        return redirect()->route('admin.investments.index');
    }

    private function createInvestmentJournalEntry(Investment $investment, float $amount, string $investmentDate)
    {
        // Get investment account (asset) and cash/bank account
        $investmentAccount = Account::where('account_type', 'asset')
            ->where('account_subtype', 'investment')
            ->where('is_active', true)
            ->first();

        $cashAccount = Account::where('account_type', 'asset')
            ->where('account_subtype', 'current_asset')
            ->where('is_active', true)
            ->first();

        if (!$investmentAccount || !$cashAccount) {
            \Log::error('Required accounts not found for investment journal entry', [
                'investment_number' => $investment->investment_number,
            ]);
            return;
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'entry_number' => 'INV-' . date('Ymd') . '-' . str_pad((string) ($investment->id), 4, '0', STR_PAD_LEFT),
            'entry_date' => $investmentDate,
            'entry_type' => 'investment',
            'description' => "Investment by member {$investment->member_number} ({$investment->investment_number})",
            'reference' => $investment->investment_number,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
            'created_by' => Auth::id(),
        ]);

        // Create journal entry lines (double-entry)
        // Debit: Investment Account (Asset increases)
        $journalEntry->lines()->create([
            'account_id' => $investmentAccount->id,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'description' => "Investment by member {$investment->member_number}",
        ]);

        // Credit: Cash/Bank (Asset decreases)
        $journalEntry->lines()->create([
            'account_id' => $cashAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'description' => "Cash payment for investment {$investment->investment_number}",
        ]);

        // Post the journal entry to update account balances
        $journalEntry->post();
    }

    public function show(Request $request, string $encryptedMemberNumber)
    {
        Gate::authorize('admin-only');

        $memberNumber = $this->encryptedIdService->decrypt($encryptedMemberNumber);

        $investments = Investment::with(['investmentProduct'])
            ->where('member_number', $memberNumber)
            ->orderBy('investment_date', 'desc')
            ->get();

        if ($investments->isEmpty()) {
            $this->error("No investments found for member {$memberNumber}");
            return redirect()->route('admin.investments.index');
        }

        // Load user by member_number for member name display
        $user = User::where('member_number', $memberNumber)->first();
        $memberName = $user ? $user->name : $memberNumber;
        $memberEmail = $user ? $user->email : null;

        $totalInvested = $investments->sum('amount');
        $totalCurrentValue = $investments->sum(function ($inv) {
            return $inv->actual_return ?? $inv->expected_return ?? 0;
        });
        $totalProfit = $totalCurrentValue - $totalInvested;
        $overallReturn = $totalInvested > 0 ? (($totalCurrentValue - $totalInvested) / $totalInvested) * 100 : 0;

        $enrichedInvestments = $investments->map(function ($inv) {
            $productName = $inv->investmentProduct ? $inv->investmentProduct->name : 'Unknown Product';
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
                'duration' => $duration,
                'profit' => $profit,
                'profit_pct' => $profitPct,
                'status' => $status,
            ];
        });

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'investment',
            'subject_id' => null,
            'description' => "Admin viewed member investments: {$memberNumber}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'member_name' => $memberName,
                'total_invested' => $totalInvested,
                'investment_count' => $investments->count(),
            ],
        ]);

        return view('admin.investments.show', [
            'member' => [
                'name' => $memberName,
                'member_number' => $memberNumber,
                'email' => $memberEmail,
            ],
            'memberNumber' => $memberNumber,
            'investments' => $enrichedInvestments,
            'totalInvested' => $totalInvested,
            'totalCurrentValue' => $totalCurrentValue,
            'totalProfit' => $totalProfit,
            'overallReturn' => $overallReturn,
            'allHistory' => [],
            'dashboardService' => $this->dashboardService,
        ]);
    }
}
