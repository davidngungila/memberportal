@extends('layouts.registration')

@section('page_title', 'Application Status')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Application Status</h1>
        <p class="text-primary-600 text-sm">Track your membership application</p>
    </div>

    <div class="card p-6">
        <div class="text-center mb-6">
            @if($application->application_status === 'submitted' || $application->application_status === 'under_review')
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-clock text-blue-500 text-2xl"></i>
                </div>
                <h2 class="text-lg font-bold text-primary-800">Under Review</h2>
                <p class="text-sm text-primary-600 mt-1">Your application is being reviewed by our team.</p>
            @elseif($application->application_status === 'correction_required')
                <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                </div>
                <h2 class="text-lg font-bold text-primary-800">Correction Required</h2>
                <p class="text-sm text-primary-600 mt-1">Please review the notes below and update your application.</p>
            @elseif($application->application_status === 'rejected')
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-times-circle text-red-500 text-2xl"></i>
                </div>
                <h2 class="text-lg font-bold text-primary-800">Application Rejected</h2>
                <p class="text-sm text-primary-600 mt-1">Unfortunately, your application has been rejected.</p>
            @endif
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Application Number</span>
                <span class="text-sm font-bold text-primary-800">{{ $application->application_number }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-primary-600">Status</span>
                <span class="badge {{ match($application->application_status) {
                    'submitted' => 'badge-blue',
                    'under_review' => 'badge-blue',
                    'correction_required' => 'badge-yellow',
                    'approved' => 'badge-green',
                    'rejected' => 'badge-red',
                    default => 'badge-gray',
                } }}">
                    {{ ucfirst(str_replace('_', ' ', $application->application_status)) }}
                </span>
            </div>
            @if($application->submitted_at)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-primary-600">Submitted</span>
                    <span class="text-sm text-primary-800">{{ $application->submitted_at->format('d M Y, H:i') }}</span>
                </div>
            @endif
        </div>

        @if($application->correction_notes)
            <div class="mt-4 p-4 rounded-xl bg-yellow-50 border border-yellow-200">
                <p class="text-xs font-bold text-yellow-800 mb-1">Correction Notes:</p>
                <p class="text-sm text-yellow-700">{{ $application->correction_notes }}</p>
            </div>
        @endif

        @if($application->rejection_reason)
            <div class="mt-4 p-4 rounded-xl bg-red-50 border border-red-200">
                <p class="text-xs font-bold text-red-800 mb-1">Rejection Reason:</p>
                <p class="text-sm text-red-700">{{ $application->rejection_reason }}</p>
            </div>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('register.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
