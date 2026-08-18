<?php

namespace App\Http\Middleware;

use App\Models\MembershipApplication;
use App\Services\Registration\RegistrationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationStage
{
    protected array $stageRequirements = [
        'membership-type' => ['password_created'],
        'payment' => ['membership_selected'],
        'personal-details' => ['payment_completed'],
        'profile-photo' => ['personal_details_completed'],
        'bank-details' => ['profile_completed'],
        'next-of-kin' => ['bank_details_completed'],
        'referral' => ['next_of_kin_completed'],
        'saving-plan' => ['referral_completed'],
        'review' => ['saving_plan_completed'],
        'submit' => ['ready_for_review'],
    ];

    public function handle(Request $request, Closure $next, ?string $requiredStage = null): Response
    {
        if (!auth()->check()) {
            return redirect()->route('register.create');
        }

        $user = auth()->user();
        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->first();

        if (!$application) {
            if ($user->isApprovedMember()) {
                return redirect()->route('member.dashboard');
            }
            return redirect()->route('register.create');
        }

        if ($application->application_status === 'submitted' || $application->application_status === 'under_review') {
            if ($request->routeIs('register.status') || $request->routeIs('member.dashboard')) {
                return $next($request);
            }
            return redirect()->route('register.status');
        }

        if ($application->application_status === 'approved') {
            if ($request->routeIs('member.*')) {
                return $next($request);
            }
            return redirect()->route('member.dashboard');
        }

        if ($requiredStage && isset($this->stageRequirements[$requiredStage])) {
            $requiredStages = $this->stageRequirements[$requiredStage];
            $currentStageOrder = RegistrationService::STAGE_ORDER[$application->current_stage] ?? -1;

            foreach ($requiredStages as $required) {
                $requiredOrder = RegistrationService::STAGE_ORDER[$required] ?? 0;
                if ($currentStageOrder < $requiredOrder) {
                    return redirect()->route('register.dashboard')
                        ->with('error', 'Please complete the previous steps first.');
                }
            }
        }

        $request->attributes->set('application', $application);

        return $next($request);
    }
}
