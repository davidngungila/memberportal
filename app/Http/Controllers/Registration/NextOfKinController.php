<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class NextOfKinController extends Controller
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

        $nextOfKin = $application->nextOfKin;

        return view('registration.next-of-kin', [
            'application' => $application,
            'nextOfKin' => $nextOfKin,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'kin' => 'required|array|min:1',
            'kin.*.full_name' => 'required|string|max:255',
            'kin.*.relationship' => 'required|string|max:100',
            'kin.*.phone' => 'required|string|min:10|max:20',
            'kin.*.alternative_phone' => 'nullable|string|max:20',
            'kin.*.address' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $this->registrationService->saveNextOfKin($user, $validated['kin']);

        return redirect()->route('register.referral')
            ->with('success', 'Next of kin details saved.');
    }
}
