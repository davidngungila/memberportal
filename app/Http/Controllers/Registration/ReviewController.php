<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('register.create');
        }

        $application->load([
            'personalDetail',
            'documents',
            'bankAccounts',
            'nextOfKin',
            'referral',
            'savingPlan',
            'payments' => fn ($q) => $q->where('status', 'successful'),
            'membershipType',
        ]);

        $this->registrationService->readyForReview($user);

        return view('registration.review', [
            'application' => $application,
        ]);
    }
}
