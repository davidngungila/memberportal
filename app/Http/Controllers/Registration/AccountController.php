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
            'phone' => 'required|string|min:10|max:15|unique:users,phone',
        ]);

        $result = $this->registrationService->createAccount([
            'phone' => $validated['phone'],
        ]);

        $sendResult = $this->verificationService->sendVerificationCodes(
            $result['user'],
            $validated['phone']
        );

        Auth::login($result['user']);

        $messages = ['Account created.'];
        if ($sendResult['sms_sent']) {
            $messages[] = 'Verification code sent to your phone.';
        } else {
            $messages[] = 'Could not send verification code. Please use the resend button.';
        }

        return redirect()->route('register.verify')
            ->with('success', implode(' ', $messages));
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

    public function verifyPhone(Request $request)
    {
        $validated = $request->validate([
            'phone_code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification) {
            return back()->with('error', 'No verification session found. Please resend the code.');
        }

        $phoneVerified = $verification->verifyPhone($validated['phone_code']);

        if ($phoneVerified) {
            $application = $user->membershipApplications()
                ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
                ->latest()
                ->first();

            if ($application) {
                $currentOrder = RegistrationService::STAGE_ORDER[$application->current_stage] ?? -1;
                if ($currentOrder < RegistrationService::STAGE_ORDER['phone_verified']) {
                    $application->update(['current_stage' => 'phone_verified']);
                }
            }

            if ($verification->isFullyVerified()) {
                $verification->markComplete();
            }

            return back()->with('success', 'Phone verified successfully.');
        }

        $reason = '';
        if ($verification->isPhoneExpired()) {
            $reason = 'Code has expired.';
        } elseif ($verification->phone_attempts >= 5) {
            $reason = 'Too many attempts.';
        } else {
            $reason = 'Invalid code.';
        }

        return back()->with('error', "Phone verification failed. {$reason}");
    }

    public function resendPhoneCode(Request $request)
    {
        $user = auth()->user();
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification || $verification->isPhoneVerified()) {
            return back()->with('error', 'No pending phone verification found.');
        }

        $verification->resendPhoneCode();
        $sent = $this->verificationService->sendPhoneCode($verification->phone, $verification->phone_code);

        if ($sent) {
            return back()->with('success', 'Phone verification code resent.');
        }

        return back()->with('error', 'Failed to resend verification code. Please try again.');
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
                ->with('error', 'Please verify your phone number first.');
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
