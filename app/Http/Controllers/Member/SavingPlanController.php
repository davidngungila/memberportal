<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SavingPlan;
use App\Models\Transaction;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SavingPlanController extends Controller
{
    use FlashMessages;

    public function index(Request $request): View
    {
        $user = Auth::user();
        $memberNumber = $user->membercode;

        // Get the member's saving plan
        $savingPlan = SavingPlan::where('member_number', $memberNumber)->first();

        if (!$savingPlan) {
            return view('member.saving-plan.empty');
        }

        // Calculate current savings from transactions
        $currentSavings = Transaction::byMemberCode($memberNumber)
            ->where('transaction_type', 'deposit')
            ->sum('amount');

        // Calculate current month's savings for monthly progress
        $currentMonthStart = now()->startOfMonth();
        $currentMonthSavings = Transaction::byMemberCode($memberNumber)
            ->where('transaction_type', 'deposit')
            ->where('date', '>=', $currentMonthStart)
            ->sum('amount');

        // Calculate progress percentage
        $goalAmount = (float) $savingPlan->goal;
        $monthlyGoal = (float) $savingPlan->monthly_goal;
        $progress = $goalAmount > 0 ? ($currentSavings / $goalAmount) * 100 : 0;
        $monthlyProgress = $monthlyGoal > 0 ? ($currentMonthSavings / $monthlyGoal) * 100 : 0;

        // Calculate remaining amount
        $remaining = max(0, $goalAmount - $currentSavings);

        // Get recent transactions for this member
        $recentTransactions = Transaction::byMemberCode($memberNumber)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Calculate monthly contributions
        $monthlyContributions = Transaction::byMemberCode($memberNumber)
            ->where('transaction_type', 'deposit')
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'saving_plan',
            'subject_id' => $savingPlan->id,
            'description' => 'Member viewed saving plan',
            'properties' => [
                'member_number' => $memberNumber,
                'plan_name' => $savingPlan->name,
                'current_savings' => $currentSavings,
                'goal' => $goalAmount,
                'progress' => $progress,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.saving-plan.index', compact(
            'savingPlan',
            'currentSavings',
            'goalAmount',
            'monthlyGoal',
            'progress',
            'monthlyProgress',
            'remaining',
            'recentTransactions',
            'monthlyContributions'
        ));
    }
}
