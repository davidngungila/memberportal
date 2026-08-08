<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingPlan;
use App\Models\User;
use App\Services\EncryptedIdService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SavingPlanController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected EncryptedIdService $encryptedIdService,
    ) {
    }

    public function index(Request $request)
    {
        $query = SavingPlan::with('user', 'member');

        if ($request->filled('member_number')) {
            $query->byMemberNumber($request->member_number);
        }

        if ($request->filled('membership')) {
            $query->byMembership($request->membership);
        }

        $savingPlans = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('admin.saving-plans.index', compact('savingPlans'));
    }

    public function create()
    {
        $members = User::where('role', 'member')->get();
        return view('admin.saving-plans.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'goal' => 'required|numeric|min:0',
            'period_type' => 'required|in:daily,weekly,monthly',
            'period_value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'status' => 'nullable|string|in:active,completed,paused',
        ]);

        $user = User::findOrFail($validated['user_id']);
        
        // Calculate periodic amount
        $periodicAmount = $validated['goal'] / (int) $validated['period_value'];
        
        // Calculate target date based on period type and value
        $startDate = Carbon::parse($validated['start_date']);
        $periods = (int) $validated['period_value'];
        $targetDate = match($validated['period_type']) {
            'daily' => $startDate->copy()->addDays($periods),
            'weekly' => $startDate->copy()->addWeeks($periods),
            'monthly' => $startDate->copy()->addMonths($periods),
        };
        
        // Generate payment schedule
        $paymentSchedule = [];
        $currentDate = $startDate->copy();
        
        for ($i = 1; $i <= $periods; $i++) {
            $paymentSchedule[] = [
                'period_number' => $i,
                'due_date' => $currentDate->format('Y-m-d'),
                'amount' => round($periodicAmount, 2),
                'status' => 'pending',
            ];
            
            $currentDate = match($validated['period_type']) {
                'daily' => $currentDate->addDay(),
                'weekly' => $currentDate->addWeek(),
                'monthly' => $currentDate->addMonth(),
            };
        }
        
        SavingPlan::create([
            'name' => $validated['name'],
            'user_id' => $validated['user_id'],
            'member_number' => $user->member_number,
            'membership' => 'individual',
            'goal' => $validated['goal'],
            'period_type' => $validated['period_type'],
            'period_value' => $validated['period_value'],
            'start_date' => $validated['start_date'],
            'target_date' => $targetDate,
            'periodic_amount' => $periodicAmount,
            'payment_schedule' => $paymentSchedule,
            'status' => $validated['status'] ?? 'active',
        ]);

        $this->success('Saving plan created successfully.');
        return redirect()->route('admin.saving-plans.index');
    }

    public function show(string $encryptedId)
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $savingPlan = SavingPlan::with('user', 'member')->findOrFail($id);
        return view('admin.saving-plans.show', compact('savingPlan'));
    }

    public function edit(string $encryptedId)
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $savingPlan = SavingPlan::findOrFail($id);
        $members = User::where('role', 'member')->get();
        return view('admin.saving-plans.edit', compact('savingPlan', 'members'));
    }

    public function update(Request $request, string $encryptedId)
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $savingPlan = SavingPlan::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'goal' => 'required|numeric|min:0',
            'period_type' => 'required|in:daily,weekly,monthly',
            'period_value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'status' => 'nullable|string|in:active,completed,paused',
        ]);

        $user = User::findOrFail($validated['user_id']);
        
        // Recalculate periodic amount
        $periodicAmount = $validated['goal'] / (int) $validated['period_value'];
        
        // Recalculate target date
        $startDate = Carbon::parse($validated['start_date']);
        $periods = (int) $validated['period_value'];
        $targetDate = match($validated['period_type']) {
            'daily' => $startDate->copy()->addDays($periods),
            'weekly' => $startDate->copy()->addWeeks($periods),
            'monthly' => $startDate->copy()->addMonths($periods),
        };
        
        // Regenerate payment schedule
        $paymentSchedule = [];
        $currentDate = $startDate->copy();
        
        for ($i = 1; $i <= $periods; $i++) {
            $paymentSchedule[] = [
                'period_number' => $i,
                'due_date' => $currentDate->format('Y-m-d'),
                'amount' => round($periodicAmount, 2),
                'status' => 'pending',
            ];
            
            $currentDate = match($validated['period_type']) {
                'daily' => $currentDate->addDay(),
                'weekly' => $currentDate->addWeek(),
                'monthly' => $currentDate->addMonth(),
            };
        }
        
        $savingPlan->update([
            'name' => $validated['name'],
            'user_id' => $validated['user_id'],
            'member_number' => $user->member_number,
            'membership' => 'individual',
            'goal' => $validated['goal'],
            'period_type' => $validated['period_type'],
            'period_value' => $validated['period_value'],
            'start_date' => $validated['start_date'],
            'target_date' => $targetDate,
            'periodic_amount' => $periodicAmount,
            'payment_schedule' => $paymentSchedule,
            'status' => $validated['status'] ?? 'active',
        ]);

        $this->success('Saving plan updated successfully.');
        return redirect()->route('admin.saving-plans.index');
    }

    public function destroy(string $encryptedId)
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $savingPlan = SavingPlan::findOrFail($id);
        $savingPlan->delete();

        $this->success('Saving plan deleted successfully.');
        return redirect()->route('admin.saving-plans.index');
    }
}
