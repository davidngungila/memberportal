@extends('layouts.registration')

@section('page_title', 'Registration Dashboard')

@section('content')
<div class="animate-fade-in">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Registration Dashboard</h1>
        <p class="text-primary-600 text-sm">Complete your membership registration</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-4">Registration Progress</h2>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-1 progress-bar">
                        <div class="progress-fill" style="width: {{ $progress['percentage'] }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-primary-700">{{ $progress['percentage'] }}%</span>
                </div>

                <div class="space-y-2">
                    @php
                        $stages = [
                            'membership_selected' => 'Membership Type',
                            'payment_completed' => 'Payment',
                            'personal_details_completed' => 'Personal Details',
                            'profile_completed' => 'Profile Photo',
                            'bank_details_completed' => 'Bank Details',
                            'next_of_kin_completed' => 'Next of Kin',
                            'referral_completed' => 'Referral',
                            'saving_plan_completed' => 'Saving Plan',
                            'ready_for_review' => 'Review Application',
                            'submitted' => 'Submit Application',
                        ];
                        $stageOrder = app(\App\Services\Registration\RegistrationService::class)::STAGE_ORDER;
                        $currentOrder = $stageOrder[$application->current_stage] ?? -1;
                    @endphp

                    @foreach($stages as $stage => $label)
                        @php
                            $stageIdx = $stageOrder[$stage];
                            $isCompleted = $currentOrder > $stageIdx;
                            $isCurrent = $currentOrder === $stageIdx;
                        @endphp
                        <div class="flex items-center gap-3 text-sm {{ $isCompleted ? 'text-primary-600' : ($isCurrent ? 'text-primary-800 font-semibold' : 'text-primary-400') }}">
                            @if($isCompleted)
                                <i class="fa-solid fa-check-circle text-primary-500 w-5 text-center"></i>
                            @elseif($isCurrent)
                                <i class="fa-solid fa-arrow-right text-yellow-500 w-5 text-center"></i>
                            @else
                                <i class="fa-regular fa-circle text-primary-300 w-5 text-center"></i>
                            @endif
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($progress['next_stage'])
                <div class="card p-6">
                    <h2 class="text-sm font-bold text-primary-800 mb-3">Next Step</h2>
                    <p class="text-primary-600 text-sm mb-4">Continue with your registration by completing the next step.</p>
                    @php
                        $nextRoutes = [
                            'membership_selected' => 'register.membership-type',
                            'payment_completed' => 'register.payment',
                            'personal_details_completed' => 'register.personal-details',
                            'profile_completed' => 'register.profile-photo',
                            'bank_details_completed' => 'register.bank-details',
                            'next_of_kin_completed' => 'register.next-of-kin',
                            'referral_completed' => 'register.referral',
                            'saving_plan_completed' => 'register.saving-plan',
                            'ready_for_review' => 'register.review',
                            'submitted' => 'register.submit',
                        ];
                        $nextRoute = $nextRoutes[$progress['next_stage']] ?? 'register.dashboard';
                    @endphp
                    <a href="{{ route($nextRoute) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition">
                        <i class="fa-solid fa-arrow-right"></i>
                        Continue Registration
                    </a>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-4">Application Status</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Status</span>
                        <span class="badge {{ $application->application_status === 'draft' ? 'badge-yellow' : 'badge-blue' }}">
                            {{ ucfirst(str_replace('_', ' ', $application->application_status)) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Application Number</span>
                        <span class="text-xs font-bold text-primary-800">{{ $application->application_number }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Progress</span>
                        <span class="text-xs font-bold text-primary-800">{{ $progress['percentage'] }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Payment</span>
                        <span class="badge {{ $application->payment_status === 'successful' ? 'badge-green' : 'badge-yellow' }}">
                            {{ ucfirst($application->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>

            @if($application->membershipType)
                <div class="card p-6">
                    <h2 class="text-sm font-bold text-primary-800 mb-3">Membership Type</h2>
                    <div class="p-3 rounded-lg bg-primary-50 border border-primary-100">
                        <p class="text-sm font-semibold text-primary-800">{{ $application->membershipType->name }}</p>
                        <p class="text-xs text-primary-600 mt-1">Registration Fee: TZS {{ number_format($application->membershipType->registration_fee) }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
