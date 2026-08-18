@extends('layouts.registration')

@section('page_title', 'Submit Application')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Submit Application</h1>
        <p class="text-primary-600 text-sm">Review and submit your membership application</p>
    </div>

    <form method="POST" action="{{ route('register.submit.store') }}">
        @csrf

        <div class="card p-6 space-y-4">
            <div class="p-4 rounded-xl bg-primary-50 border border-primary-200">
                <h2 class="text-sm font-bold text-primary-800 mb-2">Declaration</h2>
                <div class="space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="confirm_correct" value="1" class="mt-0.5 text-primary-500" required>
                        <span class="text-sm text-primary-700">I confirm that the information provided is correct and complete to the best of my knowledge.</span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="agree_terms" value="1" class="mt-0.5 text-primary-500" required>
                        <span class="text-sm text-primary-700">I agree to the membership terms and conditions of the cooperative.</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-4">
            <a href="{{ route('register.review') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
                <i class="fa-solid fa-arrow-left"></i> Back to Review
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                Submit Application
            </button>
        </div>
    </form>
</div>
@endsection
