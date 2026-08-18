@extends('layouts.app')

@section('page_title', 'Verify Account')

@section('layout_content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fade-in">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-envelope-open-text text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-primary-900">Verify Your Account</h1>
            <p class="text-primary-600 text-sm mt-1">Enter the codes sent to your email and phone</p>
        </div>

        <div class="card p-6">
            <form method="POST" action="{{ route('register.verify') }}">
                @csrf

                <div class="mb-4">
                    <label for="email_code" class="form-label">Email Verification Code</label>
                    <input type="text" name="email_code" id="email_code" class="form-input text-center text-lg tracking-[0.5em]" maxlength="6" required placeholder="_ _ _ _ _ _">
                    @if($verification && $verification->email)
                        <p class="text-xs text-primary-500 mt-1">Sent to {{ $verification->email }}</p>
                    @endif
                </div>

                <div class="mb-6">
                    <label for="phone_code" class="form-label">Phone Verification Code</label>
                    <input type="text" name="phone_code" id="phone_code" class="form-input text-center text-lg tracking-[0.5em]" maxlength="6" required placeholder="_ _ _ _ _ _">
                    @if($verification && $verification->phone)
                        <p class="text-xs text-primary-500 mt-1">Sent to {{ $verification->phone }}</p>
                    @endif
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    Verify
                </button>
            </form>

            <div class="mt-4 text-center">
                <form method="POST" action="{{ route('register.resend') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-primary-600 hover:text-primary-800 font-semibold">
                        Didn't receive the code? Resend Code
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-xs text-primary-500 mt-4">
            <a href="{{ route('register.create') }}" class="text-primary-700 font-semibold hover:underline">Back to Registration</a>
        </p>
    </div>
</div>
@endsection
