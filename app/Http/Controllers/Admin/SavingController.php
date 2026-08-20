<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AdminDashboardService;
use App\Services\EncryptedIdService;
use App\Services\MemberService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SavingController extends Controller
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
        $sortColumn = $request->input('sort', 'member_number');
        $sortDirection = $request->input('sort_direction', 'asc');
        $searchQuery = $request->input('q', '');

        // Get all members with their transactions from database
        $users = User::where('role', 'member')->get();
        
        $savingsList = [];
        foreach ($users as $user) {
            $memberNo = $user->membercode;
            if (! $memberNo) {
                continue;
            }
            
            $transactions = Transaction::byMemberCode($memberNo)->orderBy('date', 'desc')->get();
            
            // Calculate balance from transactions
            $balance = 0;
            $interestEarned = 0;
            foreach ($transactions as $txn) {
                $type = strtolower($txn->transaction_type);
                if (in_array($type, ['deposit', 'flexi-deposit', 'rda-deposit', 'opening balance', 'interest'])) {
                    $balance += (float) $txn->amount;
                    if ($type === 'interest') {
                        $interestEarned += (float) $txn->amount;
                    }
                } elseif (in_array($type, ['withdrawal', 'withdrawal'])) {
                    $balance -= (float) $txn->amount;
                }
            }
            
            $runningBalance = $balance + $interestEarned;
            $lastTransaction = $transactions->first() ? $transactions->first()->date->format('Y-m-d') : '-';

            $savingsList[] = [
                'member_number' => $memberNo,
                'member_name' => $user->name,
                'member_status' => $user->status ?? 'active',
                'member_branch' => $user->branch ?? '-',
                'balance' => $balance,
                'interest_earned' => $interestEarned,
                'running_balance' => $runningBalance,
                'last_transaction' => $lastTransaction,
                'transactions_count' => $transactions->count(),
            ];
        }

        if (! empty($searchQuery)) {
            $savingsList = array_values(array_filter($savingsList, static function ($s) use ($searchQuery): bool {
                $query = strtolower(trim($searchQuery));
                $haystack = strtolower(implode(' ', [
                    $s['member_number'] ?? '',
                    $s['member_name'] ?? '',
                    $s['member_branch'] ?? '',
                ]));

                return str_contains($haystack, $query);
            }));
        }

        $savingsList = $this->memberService->sort($savingsList, $sortColumn, $sortDirection);
        $paginated = $this->memberService->paginateArray($savingsList, $perPage);

        $paginated->appends([
            'q' => $searchQuery,
            'per_page' => $perPage,
            'sort' => $sortColumn,
            'sort_direction' => $sortDirection,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed savings list',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'search_query' => $searchQuery,
                'per_page' => $perPage,
                'sort' => $sortColumn,
                'sort_direction' => $sortDirection,
                'total_count' => count($savingsList),
            ],
        ]);

        return view('admin.savings.index', [
            'savings' => $paginated,
            'searchQuery' => $searchQuery,
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
        $savingPlans = \App\Models\SavingPlan::with('user')->where('status', 'active')->get();
        $products = \App\Models\SavingsProduct::active()->get();
        
        return view('admin.savings.create', compact('members', 'savingPlans', 'products'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');

        $validated = $request->validate([
            'member_number' => 'required|string|exists:users,membercode',
            'transaction_type' => 'required|string|in:deposit,withdrawal,interest,flexi-deposit,rda-deposit,opening balance',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'saving_plan_id' => 'nullable|exists:saving_plans,id',
            'product_id' => 'nullable|exists:savings_products,id',
        ]);

        // Validate that saving plan belongs to the selected member
        if (!empty($validated['saving_plan_id'])) {
            $savingPlan = \App\Models\SavingPlan::find($validated['saving_plan_id']);
            if ($savingPlan && $savingPlan->member_number !== $validated['member_number']) {
                return back()->with('error', 'The selected saving plan does not belong to this member.');
            }
        }

        try {
            $transaction = Transaction::create([
                'date' => $validated['date'],
                'membercode' => $validated['member_number'],
                'transaction_type' => $validated['transaction_type'],
                'amount' => $validated['amount'],
                'reference_no' => $validated['reference_no'] ?? null,
                'saving_plan_id' => $validated['saving_plan_id'] ?? null,
                'product_id' => $validated['product_id'] ?? null,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'subject_type' => 'transaction',
                'subject_id' => $transaction->id,
                'description' => "Admin added transaction for member {$validated['member_number']}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => [
                    'member_number' => $validated['member_number'],
                    'transaction_type' => $validated['transaction_type'],
                    'amount' => $validated['amount'],
                    'saving_plan_id' => $validated['saving_plan_id'] ?? null,
                    'product_id' => $validated['product_id'] ?? null,
                ],
            ]);

            $this->success('Transaction added successfully!');
            return redirect()->route('admin.savings.index');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add transaction: ' . $e->getMessage());
        }
    }

    public function show(Request $request, string $encryptedMemberNumber)
    {
        $memberNumber = $this->encryptedIdService->decrypt($encryptedMemberNumber);
        
        Gate::authorize('admin-only');

        $user = User::where('membercode', $memberNumber)->first();

        if (! $user) {
            $this->error("Member {$memberNumber} not found.");
            return redirect()->route('admin.savings.index');
        }

        $transactions = Transaction::byMemberCode($memberNumber)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate balance from transactions
        $balance = 0;
        $interestEarned = 0;
        foreach ($transactions as $txn) {
            $type = strtolower($txn->transaction_type);
            if (in_array($type, ['deposit', 'flexi-deposit', 'rda-deposit', 'opening balance', 'interest'])) {
                $balance += (float) $txn->amount;
                if ($type === 'interest') {
                    $interestEarned += (float) $txn->amount;
                }
            } elseif (in_array($type, ['withdrawal', 'withdrawal'])) {
                $balance -= (float) $txn->amount;
            }
        }

        $runningBalance = $balance + $interestEarned;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'savings',
            'subject_id' => null,
            'description' => "Admin viewed member savings: {$memberNumber}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'member_name' => $user->name,
                'balance' => $balance,
                'transactions_count' => $transactions->count(),
            ],
        ]);

        return view('admin.savings.show', [
            'member' => $user,
            'memberNumber' => $memberNumber,
            'balance' => $balance,
            'interestEarned' => $interestEarned,
            'runningBalance' => $runningBalance,
            'transactions' => $transactions,
            'dashboardService' => $this->dashboardService,
        ]);
    }
}
