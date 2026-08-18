@extends('layouts.registration')

@section('page_title', 'Payment Pending')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Payment Pending</h1>
        <p class="text-primary-600 text-sm">Complete your payment to continue</p>
    </div>

    <div class="card p-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-hourglass-half text-yellow-500 text-2xl"></i>
            </div>
            <h2 class="text-lg font-bold text-primary-800">Awaiting Payment</h2>
            <p class="text-sm text-primary-600 mt-1">Please complete the payment to proceed.</p>
        </div>

        <div class="space-y-3 mb-6">
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Amount</span>
                <span class="text-lg font-bold text-primary-800">TZS {{ number_format($payment->amount) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Payment Method</span>
                <span class="text-sm font-semibold text-primary-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @if($payment->transaction_reference)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-primary-600">Reference</span>
                    <span class="text-sm font-semibold text-primary-800">{{ $payment->transaction_reference }}</span>
                </div>
            @endif
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Status</span>
                <span class="badge badge-yellow">{{ ucfirst($payment->status) }}</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('register.payment') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <form method="POST" action="{{ route('register.payment.confirm', $payment->id) }}" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    I Have Paid
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
