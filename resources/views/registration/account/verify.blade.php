@extends('layouts.app')

@section('page_title', 'Verify Account')

@section('layout_content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fade-in">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-primary-900">Verify Your Account</h1>
            <p class="text-primary-600 text-sm mt-1">Verify your email and phone individually to continue</p>
        </div>

        {{-- EMAIL VERIFICATION --}}
        <div class="card p-6 mb-4 {{ $verification && $verification->isEmailVerified() ? 'border-green-300 bg-green-50' : '' }}">
            <div class="flex items-center gap-3 mb-4">
                @if($verification && $verification->isEmailVerified())
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-sm font-bold text-green-800">Email Verified</h2>
                        @if($verification->email_verified_at)
                            <p class="text-xs text-green-600">{{ $verification->email_verified_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                        <i class="fa-solid fa-envelope text-primary-500 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-sm font-bold text-primary-800">Email Verification</h2>
                        @if($verification && $verification->email)
                            <p class="text-xs text-primary-500">Sent to {{ $verification->email }}</p>
                        @endif
                    </div>
                @endif
            </div>

            @if(!$verification || !$verification->isEmailVerified())
                <form method="POST" action="{{ route('register.verify.email') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="email_code" class="form-input text-center text-lg tracking-[0.5em]" maxlength="6" required placeholder="_ _ _ _ _ _" autocomplete="one-time-code">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            Verify Email
                        </button>
                    </div>

                    @if($verification && $verification->email_attempts > 0)
                        <p class="text-xs text-primary-400 mt-2 text-center">{{ 5 - $verification->email_attempts }} attempts remaining</p>
                    @endif
                </form>

                <form method="POST" action="{{ route('register.resend.email') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-lg border border-primary-200 text-primary-600 text-xs font-semibold hover:bg-primary-50 transition">
                        <i class="fa-solid fa-rotate-right mr-1"></i> Resend Email Code
                    </button>
                </form>
            @endif
        </div>

        {{-- PHONE VERIFICATION --}}
        <div class="card p-6 mb-4 {{ $verification && $verification->isPhoneVerified() ? 'border-green-300 bg-green-50' : '' }}">
            <div class="flex items-center gap-3 mb-4">
                @if($verification && $verification->isPhoneVerified())
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-sm font-bold text-green-800">Phone Verified</h2>
                        @if($verification->phone_verified_at)
                            <p class="text-xs text-green-600">{{ $verification->phone_verified_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                        <i class="fa-solid fa-mobile-screen text-primary-500 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-sm font-bold text-primary-800">Phone Verification</h2>
                        @if($verification && $verification->phone)
                            <p class="text-xs text-primary-500">Sent to {{ $verification->phone }}</p>
                        @endif
                    </div>
                @endif
            </div>

            @if(!$verification || !$verification->isPhoneVerified())
                <form method="POST" action="{{ route('register.verify.phone') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="phone_code" class="form-input text-center text-lg tracking-[0.5em]" maxlength="6" required placeholder="_ _ _ _ _ _" autocomplete="one-time-code">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            Verify Phone
                        </button>
                    </div>

                    @if($verification && $verification->phone_attempts > 0)
                        <p class="text-xs text-primary-400 mt-2 text-center">{{ 5 - $verification->phone_attempts }} attempts remaining</p>
                    @endif
                </form>

                <form method="POST" action="{{ route('register.resend.phone') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-lg border border-primary-200 text-primary-600 text-xs font-semibold hover:bg-primary-50 transition">
                        <i class="fa-solid fa-rotate-right mr-1"></i> Resend Phone Code
                    </button>
                </form>
            @endif
        </div>

        {{-- CONTINUE BUTTON --}}
        @if($verification && $verification->isFullyVerified())
            <div class="card p-6 border-green-300 bg-green-50 animate-fade-in">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-check-double text-green-500 text-xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-green-800 mb-1">Both Verified!</h3>
                    <p class="text-xs text-green-600 mb-4">Your email and phone have been verified successfully.</p>
                    <a href="{{ route('register.password') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition">
                        <i class="fa-solid fa-arrow-right"></i>
                        Create Password
                    </a>
                </div>
            </div>
        @else
            <div class="text-center">
                <p class="text-xs text-primary-400">
                    Both email and phone must be verified before continuing.
                </p>
            </div>
        @endif

        <p class="text-center text-xs text-primary-500 mt-4">
            <a href="{{ route('register.create') }}" class="text-primary-700 font-semibold hover:underline">Back to Registration</a>
        </p>
    </div>
</div>
@endsection
