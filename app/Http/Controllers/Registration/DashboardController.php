<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\MemberType;
use App\Services\Registration\RegistrationService;

class DashboardController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
    ) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->isApprovedMember()) {
            return redirect()->route('member.dashboard');
        }

        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->first();

        if (!$application) {
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('register.create');
        }

        $progress = $this->registrationService->getApplicationProgress($application);
        $membershipTypes = MemberType::active()->get();

        return view('registration.dashboard', [
            'application' => $application,
            'progress' => $progress,
            'membershipTypes' => $membershipTypes,
        ]);
    }

    public function status()
    {
        $user = auth()->user();

        $application = $user->membershipApplications()
            ->whereIn('application_status', ['submitted', 'under_review', 'approved', 'rejected'])
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('register.dashboard');
        }

        if ($application->application_status === 'approved') {
            return redirect()->route('member.dashboard');
        }

        return view('registration.status', [
            'application' => $application,
        ]);
    }
}
