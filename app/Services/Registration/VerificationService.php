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

    public function sendVerificationCodes(User $user, string $phone): array
    {
        $existingVerification = $user->verificationCode()->latest()->first();

        if ($existingVerification && !$existingVerification->isPhoneVerified()) {
            $existingVerification->resendPhoneCode();
            $verification = $existingVerification;
        } else {
            $verification = VerificationCode::createForUser($user, null, $phone);
        }

        $smsSent = $this->sendPhoneCode($phone, $verification->phone_code);

        return [
            'verification' => $verification,
            'sms_sent' => $smsSent,
        ];
    }

    public function verifyPhone(User $user, string $phoneCode): bool
    {
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification) {
            return false;
        }

        $phoneVerified = $verification->verifyPhone($phoneCode);

        if ($phoneVerified && $verification->isFullyVerified()) {
            $verification->markComplete();
            return true;
        }

        return false;
    }

    public function resendPhoneCodeOnly(User $user): array
    {
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification) {
            return ['success' => false, 'message' => 'No verification session found.'];
        }

        if (!$verification->isPhoneVerified()) {
            $verification->resendPhoneCode();
            $smsSent = $this->sendPhoneCode($verification->phone, $verification->phone_code);

            if ($smsSent) {
                return ['success' => true, 'message' => 'Verification code resent.'];
            }

            return ['success' => false, 'message' => 'Failed to send verification code. Please try again.'];
        }

        return ['success' => false, 'message' => 'Phone already verified.'];
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
