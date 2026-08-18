<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class SavingPlanController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
    ) {}

    public function showForm()
    {
        $user = auth()->user();
        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('register.create');
        }

        $savingPlan = $application->savingPlan;

        return view('registration.saving-plan', [
            'application' => $application,
            'savingPlan' => $savingPlan ?? (object) ['plan_name' => '', 'frequency' => '', 'target_amount' => '', 'periodic_amount' => '', 'expected_saving_date' => null],
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'plan_name' => 'required|string|max:255',
            'frequency' => 'required|in:monthly,weekly,quarterly',
            'target_amount' => 'required|numeric|min:0',
            'periodic_amount' => 'nullable|numeric|min:0',
            'expected_saving_date' => 'nullable|date',
        ]);

        $user = auth()->user();
        $this->registrationService->saveSavingPlan($user, $validated);

        return redirect()->route('register.review')
            ->with('success', 'Saving plan saved.');
    }
}
