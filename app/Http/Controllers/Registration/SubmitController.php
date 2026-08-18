<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class SubmitController extends Controller
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

        if ($application->current_stage !== 'ready_for_review') {
            return redirect()->route('register.review')
                ->with('error', 'Please complete the review first.');
        }

        return view('registration.submit', [
            'application' => $application,
        ]);
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'confirm_correct' => 'accepted',
            'agree_terms' => 'accepted',
        ]);

        $user = auth()->user();
        $application = $this->registrationService->submitApplication($user);

        return redirect()->route('register.status')
            ->with('success', 'Application submitted successfully.');
    }
}
