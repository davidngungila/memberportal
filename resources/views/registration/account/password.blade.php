@extends('layouts.app')

@section('page_title', 'Create Password')

@section('layout_content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fade-in">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-lock text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-primary-900">Create Password</h1>
            <p class="text-primary-600 text-sm mt-1">Set a secure password for your account</p>
        </div>

        <div class="card p-6">
            <form method="POST" action="{{ route('register.password.store') }}" x-data="passwordForm()">
                @csrf

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password" class="form-input pr-10" required x-model="password" placeholder="Enter password">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-400 hover:text-primary-600">
                            <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="form-label">Repeat Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required placeholder="Repeat password">
                </div>

                <div class="mb-6 p-3 rounded-lg bg-primary-50 border border-primary-100">
                    <p class="text-xs font-semibold text-primary-700 mb-2">Password requirements:</p>
                    <div class="grid grid-cols-1 gap-1 text-xs">
                        <div class="flex items-center gap-2" :class="password.length >= 8 ? 'text-primary-600' : 'text-primary-400'">
                            <i :class="password.length >= 8 ? 'fa-solid fa-check-circle text-primary-500' : 'fa-regular fa-circle'"></i>
                            At least 8 characters
                        </div>
                        <div class="flex items-center gap-2" :class="/[A-Z]/.test(password) ? 'text-primary-600' : 'text-primary-400'">
                            <i :class="/[A-Z]/.test(password) ? 'fa-solid fa-check-circle text-primary-500' : 'fa-regular fa-circle'"></i>
                            Uppercase letter
                        </div>
                        <div class="flex items-center gap-2" :class="/[a-z]/.test(password) ? 'text-primary-600' : 'text-primary-400'">
                            <i :class="/[a-z]/.test(password) ? 'fa-solid fa-check-circle text-primary-500' : 'fa-regular fa-circle'"></i>
                            Lowercase letter
                        </div>
                        <div class="flex items-center gap-2" :class="/[0-9]/.test(password) ? 'text-primary-600' : 'text-primary-400'">
                            <i :class="/[0-9]/.test(password) ? 'fa-solid fa-check-circle text-primary-500' : 'fa-regular fa-circle'"></i>
                            Number
                        </div>
                        <div class="flex items-center gap-2" :class="/[^A-Za-z0-9]/.test(password) ? 'text-primary-600' : 'text-primary-400'">
                            <i :class="/[^A-Za-z0-9]/.test(password) ? 'fa-solid fa-check-circle text-primary-500' : 'fa-regular fa-circle'"></i>
                            Special character
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right"></i>
                    Continue
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function passwordForm() {
        return {
            password: '',
            showPassword: false,
        }
    }
</script>
@endpush
@endsection
