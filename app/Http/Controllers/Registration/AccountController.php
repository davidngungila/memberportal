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

        $sendResult = $this->verificationService->sendVerificationCodes(
            $result['user'],
            $validated['email'],
            $validated['phone']
        );

        Auth::login($result['user']);

        $messages = ['Account created.'];
        if ($sendResult['email_sent'] && $sendResult['sms_sent']) {
            $messages[] = 'Verification codes sent to your email and phone.';
        } elseif ($sendResult['email_sent']) {
            $messages[] = 'Email verification code sent. SMS could not be sent — please check your phone number or request a resend.';
        } elseif ($sendResult['sms_sent']) {
            $messages[] = 'SMS verification code sent. Email could not be sent — please check your email or request a resend.';
        } else {
            $messages[] = 'Could not send verification codes. Please use the resend buttons on the verification page.';
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

    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email_code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification) {
            return back()->with('error', 'No verification session found. Please resend the code.');
        }

        $emailVerified = $verification->verifyEmail($validated['email_code']);

        if ($emailVerified) {
            $application = $user->membershipApplications()
                ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
                ->latest()
                ->first();

            if ($application) {
                $currentOrder = RegistrationService::STAGE_ORDER[$application->current_stage] ?? -1;
                if ($currentOrder < RegistrationService::STAGE_ORDER['email_verified']) {
                    $application->update(['current_stage' => 'email_verified']);
                }
            }

            return back()->with('success', 'Email verified successfully.');
        }

        $reason = '';
        if ($verification->isEmailExpired()) {
            $reason = 'Code has expired.';
        } elseif ($verification->email_attempts >= 5) {
            $reason = 'Too many attempts.';
        } else {
            $reason = 'Invalid code.';
        }

        return back()->with('error', "Email verification failed. {$reason}");
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

    public function resendEmailCode(Request $request)
    {
        $user = auth()->user();
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification || $verification->isEmailVerified()) {
            return back()->with('error', 'No pending email verification found.');
        }

        $verification->resendEmailCode();
        $sent = $this->verificationService->sendEmailCode($verification->email, $verification->email_code);

        if ($sent) {
            return back()->with('success', 'Email verification code resent.');
        }

        return back()->with('error', 'Failed to resend email verification code. Please try again.');
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

        return back()->with('error', 'Failed to resend phone verification code. Please try again.');
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
                ->with('error', 'Please verify both your email and phone first.');
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
