@extends('layouts.registration')

@section('page_title', 'Registration Payment')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Registration Payment</h1>
        <p class="text-primary-600 text-sm">Pay your registration fee to continue</p>
    </div>

    <div class="card p-6 mb-6">
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Membership Type</span>
                <span class="text-sm font-bold text-primary-800">{{ $membershipType->name }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Registration Fee</span>
                <span class="text-lg font-bold text-primary-800">TZS {{ number_format($membershipType->registration_fee) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Payment Status</span>
                @if($payment)
                    <span class="badge {{ $payment->status === 'successful' ? 'badge-green' : 'badge-yellow' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                @else
                    <span class="badge badge-yellow">Pending</span>
                @endif
            </div>
        </div>
    </div>

    @if($payment && $payment->status === 'successful')
        <div class="card p-6 border-primary-300 bg-primary-50">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-check-circle text-primary-500 text-lg"></i>
                <div>
                    <p class="text-sm font-bold text-primary-800">Payment Confirmed</p>
                    <p class="text-xs text-primary-600">You can now continue with your registration.</p>
                </div>
            </div>
            <a href="{{ route('register.personal-details') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition">
                <i class="fa-solid fa-arrow-right"></i>
                Continue Registration
            </a>
        </div>
    @else
        <div class="card p-6">
            <form method="POST" action="{{ route('register.payment.process') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Payment Method</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 rounded-lg border border-primary-200 cursor-pointer hover:border-primary-400 transition {{ old('payment_method') === 'mobile_money' ? 'border-primary-500 bg-primary-50' : '' }}">
                            <input type="radio" name="payment_method" value="mobile_money" class="hidden" {{ old('payment_method') === 'mobile_money' ? 'checked' : '' }}>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-mobile-screen text-primary-500"></i>
                                <span class="text-sm font-semibold text-primary-800">Mobile Money</span>
                            </div>
                        </label>
                        <label class="p-3 rounded-lg border border-primary-200 cursor-pointer hover:border-primary-400 transition {{ old('payment_method') === 'bank_transfer' ? 'border-primary-500 bg-primary-50' : '' }}">
                            <input type="radio" name="payment_method" value="bank_transfer" class="hidden" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-building-columns text-primary-500"></i>
                                <span class="text-sm font-semibold text-primary-800">Bank Transfer</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="transaction_reference" class="form-label">Transaction Reference (Optional)</label>
                    <input type="text" name="transaction_reference" id="transaction_reference" value="{{ old('transaction_reference') }}" class="form-input" placeholder="Enter transaction reference">
                </div>

                <button type="submit" class="px-6 py-3 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-credit-card"></i>
                    Pay Now
                </button>
            </form>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('input[name="payment_method"]').forEach(r => r.closest('label').classList.remove('border-primary-500', 'bg-primary-50'));
            this.closest('label').classList.add('border-primary-500', 'bg-primary-50');
        });
    });
</script>
@endpush
@endsection
