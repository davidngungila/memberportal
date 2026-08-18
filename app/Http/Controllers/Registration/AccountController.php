<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use App\Services\Registration\RegistrationService;
use App\Services\Registration\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
        protected VerificationService $verificationService,
    ) {}

    public function showCreateForm()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isApprovedMember()) {
                return redirect()->route('member.dashboard');
            }
            if ($user->hasActiveApplication()) {
                return redirect()->route('register.dashboard');
            }
        }

        return view('registration.account.create');
    }

    public function createAccount(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10|max:15|unique:users,phone',
        ]);

        $result = $this->registrationService->createAccount($validated);

        $this->verificationService->sendVerificationCodes(
            $result['user'],
            $validated['email'],
            $validated['phone']
        );

        Auth::login($result['user']);

        return redirect()->route('register.verify')
            ->with('success', 'Account created. Please verify your email and phone.');
    }

    public function showVerificationForm()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('register.create');
        }

        $verification = $user->verificationCode()->latest()->first();

        return view('registration.account.verify', [
            'verification' => $verification,
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email_code' => 'required|string|size:6',
            'phone_code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        $result = $this->verificationService->verify(
            $user,
            $validated['email_code'],
            $validated['phone_code']
        );

        if ($result['success']) {
            $application = $user->membershipApplications()
                ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
                ->latest()
                ->first();

            if ($application) {
                $application->update(['current_stage' => 'email_verified']);
            }

            return redirect()->route('register.password')
                ->with('success', 'Verification successful.');
        }

        return back()->with('error', $result['message']);
    }

    public function resendCodes()
    {
        $user = auth()->user();
        $result = $this->verificationService->resendCodes($user);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function showPasswordForm()
    {
        $user = auth()->user();
        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('register.create');
        }

        $currentOrder = RegistrationService::STAGE_ORDER[$application->current_stage] ?? -1;
        if ($currentOrder < RegistrationService::STAGE_ORDER['phone_verified']) {
            return redirect()->route('register.verify')
                ->with('error', 'Please verify your account first.');
        }

        return view('registration.account.password');
    }

    public function createPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $this->registrationService->createPassword($user, $validated['password']);

        return redirect()->route('register.dashboard')
            ->with('success', 'Password created. Continue with your registration.');
    }
}
