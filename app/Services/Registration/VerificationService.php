<?php

namespace App\Services\Registration;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;

class VerificationService
{
    public function __construct(
        protected SmsService $smsService,
    ) {}

    public function sendVerificationCodes(User $user, string $email, string $phone): VerificationCode
    {
        $existingVerification = $user->verificationCode()->latest()->first();

        if ($existingVerification && !$existingVerification->isFullyVerified()) {
            $existingVerification->resendEmailCode();
            $existingVerification->resendPhoneCode();
            $verification = $existingVerification;
        } else {
            $verification = VerificationCode::createForUser($user, $email, $phone);
        }

        $this->sendEmailCode($email, $verification->email_code);
        $this->sendPhoneCode($phone, $verification->phone_code);

        return $verification;
    }

    public function verify(User $user, string $emailCode, string $phoneCode): array
    {
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification) {
            return ['success' => false, 'message' => 'No verification code found.'];
        }

        $emailVerified = $verification->verifyEmail($emailCode);
        $phoneVerified = $verification->verifyPhone($phoneCode);

        $errors = [];

        if (!$emailVerified && !$verification->isEmailVerified()) {
            if ($verification->isEmailExpired()) {
                $errors[] = 'Email verification code has expired.';
            } elseif ($verification->email_attempts >= 5) {
                $errors[] = 'Too many email verification attempts.';
            } else {
                $errors[] = 'Invalid email verification code.';
            }
        }

        if (!$phoneVerified && !$verification->isPhoneVerified()) {
            if ($verification->isPhoneExpired()) {
                $errors[] = 'Phone verification code has expired.';
            } elseif ($verification->phone_attempts >= 5) {
                $errors[] = 'Too many phone verification attempts.';
            } else {
                $errors[] = 'Invalid phone verification code.';
            }
        }

        if ($verification->isFullyVerified()) {
            $verification->markComplete();
            return ['success' => true, 'message' => 'Verification successful.'];
        }

        return ['success' => false, 'message' => implode(' ', $errors)];
    }

    public function resendCodes(User $user): array
    {
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification) {
            return ['success' => false, 'message' => 'No verification session found.'];
        }

        if (!$verification->isEmailVerified()) {
            $verification->resendEmailCode();
            $this->sendEmailCode($verification->email, $verification->email_code);
        }

        if (!$verification->isPhoneVerified()) {
            $verification->resendPhoneCode();
            $this->sendPhoneCode($verification->phone, $verification->phone_code);
        }

        return ['success' => true, 'message' => 'Verification codes resent.'];
    }

    public function sendEmailCode(string $email, string $code): void
    {
        try {
            Mail::raw("Your verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($email, $code) {
                $message->to($email)
                    ->subject('Email Verification Code')
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send email verification code', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendPhoneCode(string $phone, string $code): void
    {
        try {
            $this->smsService->sendSingle($phone, "Your verification code is: {$code}. It expires in 10 minutes.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send phone verification code', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
