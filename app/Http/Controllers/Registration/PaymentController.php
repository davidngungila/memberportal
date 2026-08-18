<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\PaymentService;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
        protected PaymentService $paymentService,
    ) {}

    public function showForm()
    {
        $user = auth()->user();
        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('register.create');
        }

        if (!$application->membership_type_id) {
            return redirect()->route('register.membership-type')
                ->with('error', 'Please select a membership type first.');
        }

        $payment = $this->paymentService->getApplicationPayment($application);

        return view('registration.payment', [
            'application' => $application,
            'payment' => $payment,
            'membershipType' => $application->membershipType,
        ]);
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:mobile_money,bank_transfer,cash,card',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $application = $this->registrationService->getActiveApplication($user);

        $payment = $this->paymentService->createPayment($application, [
            'amount' => $application->membershipType->registration_fee,
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
        ]);

        return redirect()->route('register.payment.pending', $payment->id)
            ->with('success', 'Payment initiated. Please complete the payment.');
    }

    public function pending($paymentId)
    {
        $user = auth()->user();
        $application = $this->registrationService->getActiveApplication($user);

        $payment = $application->payments()->findOrFail($paymentId);

        return view('registration.payment-pending', [
            'application' => $application,
            'payment' => $payment,
            'membershipType' => $application->membershipType,
        ]);
    }

    public function confirm($paymentId)
    {
        $user = auth()->user();
        $application = $this->registrationService->getActiveApplication($user);

        $payment = $application->payments()->findOrFail($paymentId);

        $this->paymentService->markSuccessful($payment);
        $this->registrationService->completePayment($user, $payment->id);

        return redirect()->route('register.personal-details')
            ->with('success', 'Payment confirmed. Please complete your personal details.');
    }

    public function callback()
    {
        $reference = request('reference');
        $status = request('status');

        if ($reference && $status === 'successful') {
            $payment = \App\Models\MembershipPayment::where('transaction_reference', $reference)->first();

            if ($payment && $payment->status !== 'successful') {
                $this->paymentService->markSuccessful($payment);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
