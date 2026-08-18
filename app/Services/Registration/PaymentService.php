<?php

namespace App\Services\Registration;

use App\Models\MembershipApplication;
use App\Models\MembershipPayment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createPayment(MembershipApplication $application, array $data): MembershipPayment
    {
        return MembershipPayment::create([
            'application_id' => $application->id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function processPayment(MembershipPayment $payment, array $gatewayResponse): bool
    {
        return DB::transaction(function () use ($payment, $gatewayResponse) {
            $payment->update([
                'gateway_reference' => $gatewayResponse['reference'] ?? null,
                'metadata' => $gatewayResponse,
            ]);

            if (($gatewayResponse['status'] ?? '') === 'successful') {
                $payment->update([
                    'status' => 'successful',
                    'paid_at' => now(),
                ]);

                $payment->application->update([
                    'payment_status' => 'successful',
                ]);

                return true;
            }

            $payment->update(['status' => 'failed']);
            $payment->application->update(['payment_status' => 'failed']);

            return false;
        });
    }

    public function markSuccessful(MembershipPayment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'successful',
                'paid_at' => now(),
            ]);

            $payment->application->update([
                'payment_status' => 'successful',
            ]);

            return true;
        });
    }

    public function markFailed(MembershipPayment $payment, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge($payment->metadata ?? [], ['failure_reason' => $reason]),
            ]);

            $payment->application->update([
                'payment_status' => 'failed',
            ]);

            return true;
        });
    }

    public function refund(MembershipPayment $payment, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment->update([
                'status' => 'refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refund_reason' => $reason,
                    'refunded_at' => now()->toIso8601String(),
                ]),
            ]);

            $payment->application->update([
                'payment_status' => 'refunded',
            ]);

            return true;
        });
    }

    public function getApplicationPayment(MembershipApplication $application): ?MembershipPayment
    {
        return $application->payments()
            ->where('status', 'successful')
            ->latest('paid_at')
            ->first();
    }

    public function isPaymentComplete(MembershipApplication $application): bool
    {
        return $application->payments()
            ->where('status', 'successful')
            ->exists();
    }
}
