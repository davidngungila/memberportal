@extends('layouts.app')

@section('page_title', 'Verify Phone Number')

@section('layout_content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fade-in">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-primary-900">Verify Your Phone</h1>
            <p class="text-primary-600 text-sm mt-1">Enter the 6-digit code sent to your phone</p>
        </div>

        <div class="card p-6 {{ $verification && $verification->isPhoneVerified() ? 'border-green-300 bg-green-50' : '' }}">
            @if($verification && $verification->isPhoneVerified())
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-green-800 mb-1">Phone Verified</h3>
                    @if($verification->phone_verified_at)
                        <p class="text-xs text-green-600">{{ $verification->phone_verified_at->format('d M Y, H:i') }}</p>
                    @endif

                    <a href="{{ route('register.password') }}" class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition">
                        <i class="fa-solid fa-arrow-right"></i>
                        Create Password
                    </a>
                </div>
            @else
                @if($verification && $verification->phone)
                    <p class="text-sm text-primary-600 mb-4 text-center">Code sent to <strong>{{ $verification->phone }}</strong></p>
                @endif

                <form method="POST" action="{{ route('register.verify.phone') }}">
                    @csrf
                    <div class="mb-4">
                        <input type="text" name="phone_code" class="form-input text-center text-lg tracking-[0.5em]" maxlength="6" required placeholder="_ _ _ _ _ _" autocomplete="one-time-code">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        Verify Phone
                    </button>

                    @if($verification && $verification->phone_attempts > 0)
                        <p class="text-xs text-primary-400 mt-2 text-center">{{ 5 - $verification->phone_attempts }} attempts remaining</p>
                    @endif
                </form>

                <form method="POST" action="{{ route('register.resend.phone') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl border border-primary-200 text-primary-600 text-xs font-semibold hover:bg-primary-50 transition">
                        <i class="fa-solid fa-rotate-right mr-1"></i> Resend Code
                    </button>
                </form>
            @endif
        </div>

        <p class="text-center text-xs text-primary-500 mt-4">
            <a href="{{ route('register.create') }}" class="text-primary-700 font-semibold hover:underline">Back to Registration</a>
        </p>
    </div>
</div>
@endsection
