<?php

namespace App\Services\Registration;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\MailConfigService;
use App\Services\MessagingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationService
{
    public function __construct(
        protected MessagingService $messagingService,
        protected MailConfigService $mailConfigService,
    ) {}

    public function sendVerificationCodes(User $user, string $email, string $phone): array
    {
        $existingVerification = $user->verificationCode()->latest()->first();

        if ($existingVerification && !$existingVerification->isFullyVerified()) {
            $existingVerification->resendEmailCode();
            $existingVerification->resendPhoneCode();
            $verification = $existingVerification;
        } else {
            $verification = VerificationCode::createForUser($user, $email, $phone);
        }

        $emailSent = $this->sendEmailCode($email, $verification->email_code);
        $smsSent = $this->sendPhoneCode($phone, $verification->phone_code);

        return [
            'verification' => $verification,
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
        ];
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

        $emailSent = false;
        $smsSent = false;

        if (!$verification->isEmailVerified()) {
            $verification->resendEmailCode();
            $emailSent = $this->sendEmailCode($verification->email, $verification->email_code);
        }

        if (!$verification->isPhoneVerified()) {
            $verification->resendPhoneCode();
            $smsSent = $this->sendPhoneCode($verification->phone, $verification->phone_code);
        }

        $messages = [];
        if ($emailSent) $messages[] = 'Email code sent';
        if ($smsSent) $messages[] = 'SMS code sent';
        if (!$emailSent && !$smsSent) {
            return ['success' => false, 'message' => 'Failed to send verification codes.'];
        }

        return ['success' => true, 'message' => implode('. ', $messages) . '.'];
    }

    public function sendEmailCode(string $email, string $code): bool
    {
        try {
            $this->mailConfigService->configureFromDatabase();

            $senderId = config('mail.from.name', 'FEEDTAN');
            $body = "Dear Member,\n\nYour verification code is: {$code}\n\nThis code expires in 10 minutes.\n\n Regards,\n{$senderId}";

            Mail::raw($body, function ($message) use ($email, $senderId) {
                $message->to($email)
                    ->subject('Email Verification Code')
                    ->from(config('mail.from.address'), $senderId);
            });

            Log::info('Email verification code sent', ['email' => $email]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email verification code', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendPhoneCode(string $phone, string $code): bool
    {
        try {
            $senderId = $this->messagingService->getSenderId();
            $smsText = "Dear Member,\n\nYour verification code is: {$code}\n\nThis code expires in 10 minutes.\n\nRegards,\n{$senderId}";

            $result = $this->messagingService->sendSms($phone, $smsText);

            if ($result['success']) {
                Log::info('Phone verification code sent', ['phone' => $phone]);
                return true;
            }

            Log::error('Failed to send phone verification code', [
                'phone' => $phone,
                'response' => $result['response'] ?? null,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send phone verification code', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
