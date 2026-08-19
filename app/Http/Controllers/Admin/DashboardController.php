<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\AdminDashboardService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected GoogleSheetRepositoryInterface $googleSheetRepository,
        protected AdminDashboardService $dashboardService,
    ) {
    }

    public function index(Request $request)
    {
        $totals = $this->googleSheetRepository->getDashboardTotals();
        $lastSync = $this->googleSheetRepository->getLastSyncInfo();

        $searchResults = null;
        if ($request->filled('q')) {
            $searchResults = $this->googleSheetRepository->searchMembers($request->input('q'));
        }

        $allMembers = $this->googleSheetRepository->getAllMembers();
        
        // Get database members and merge with Google Sheets members
        $dbMembers = \App\Models\Member::all()->map(function($member) {
            return [
                'membercode' => $member->membercode,
                'name' => $member->full_name,
                'gender' => $member->gender,
                'phone' => $member->phone,
                'email' => $member->email,
                'branch' => $member->branch ?? '-',
                'status' => $member->status,
                'photo' => $member->photo,
            ];
        })->toArray();

        // Merge members, prioritizing database members
        $membersMap = [];
        foreach ($dbMembers as $member) {
            $membersMap[$member['membercode']] = $member;
        }
        foreach ($allMembers as $member) {
            $memberNo = $member['membercode'] ?? $member['MemberNumber'] ?? null;
            if ($memberNo && !isset($membersMap[$memberNo])) {
                $membersMap[$memberNo] = $member;
            }
        }
        $allMembers = array_values($membersMap);
        
        $recentMembers = array_slice($allMembers, 0, 5);

        // Get recent transactions from database
        $recentTransactions = \App\Models\Transaction::orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => $transaction->date->format('Y-m-d'),
                    'membercode' => $transaction->membercode,
                    'transaction_type' => $transaction->transaction_type,
                    'reference_no' => $transaction->reference_no,
                    'amount' => (float) $transaction->amount,
                ];
            })
            ->toArray();

        // Get transaction statistics
        $transactionStats = [
            'total_transactions' => \App\Models\Transaction::count(),
            'total_deposits' => \App\Models\Transaction::where('transaction_type', 'deposit')->sum('amount'),
            'total_withdrawals' => \App\Models\Transaction::where('transaction_type', 'withdrawal')->sum('amount'),
            'total_transfers' => \App\Models\Transaction::where('transaction_type', 'transfer')->sum('amount'),
        ];

        // Update totals to include database members
        $dbMemberCount = \App\Models\Member::count();
        $totals['total_members'] = ($totals['total_members'] ?? 0) + $dbMemberCount;

        $formattedTotals = $this->dashboardService->formatTotals(array_merge(
            $totals,
            [
                'last_sync' => $lastSync['last_sync_at'] ?? 'Never',
                'google_sheet_status' => $lastSync['status'] ?? 'unknown',
                'db_member_count' => $dbMemberCount,
                'db_transaction_count' => $transactionStats['total_transactions'],
                'total_deposits' => $transactionStats['total_deposits'],
                'total_withdrawals' => $transactionStats['total_withdrawals'],
            ]
        ));

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed dashboard',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'has_search' => $request->filled('q'),
                'search_query' => $request->input('q'),
            ],
        ]);

        return view('admin.dashboard.index', [
            'totals' => $formattedTotals,
            'rawTotals' => $totals,
            'recentMembers' => $recentMembers,
            'recentTransactions' => $recentTransactions,
            'transactionStats' => $transactionStats,
            'searchResults' => $searchResults,
            'searchQuery' => $request->input('q'),
            'lastSync' => $lastSync,
            'dashboardService' => $this->dashboardService,
        ]);
    }
}
