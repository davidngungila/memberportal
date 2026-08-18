@extends('layouts.app')

@section('page_title', 'Create Account')

@section('layout_content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fade-in">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-primary-900">Create Account</h1>
            <p class="text-primary-600 text-sm mt-1">Start your membership registration</p>
        </div>

        <div class="card p-6">
            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-input" required autofocus placeholder="you@example.com">
                </div>

                <div class="mb-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-input" required placeholder="+255 7XX XXX XXX">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    Send Verification Code
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-primary-500 mt-4">
            Already have an account? <a href="{{ route('login') }}" class="text-primary-700 font-semibold hover:underline">Login</a>
        </p>
    </div>
</div>
@endsection
