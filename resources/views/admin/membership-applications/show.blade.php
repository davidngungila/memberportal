@extends('layouts.admin')

@section('page_title', 'Application ' . ($application->application_number ?? ''))

@section('content')

@php
    $appStatus = $application->application_status ?? '';
    $canAct = in_array($appStatus, ['submitted', 'under_review', 'correction_required']);
    $applicantName = $application->personalDetail?->full_name ?? $application->user?->name ?? 'Unknown Applicant';
    $statusBadge = match($appStatus) {
        'submitted' => 'badge-blue',
        'under_review' => 'badge-blue',
        'approved' => 'badge-green',
        'rejected' => 'badge-red',
        'correction_required' => 'badge-yellow',
        default => 'badge-gray',
    };
    $progress = method_exists($application, 'getProgressPercentage') ? $application->getProgressPercentage() : 0;
    $stages = [
        'membership_selected' => 'Membership Selected',
        'payment_completed' => 'Payment Completed',
        'personal_details_completed' => 'Personal Details',
        'profile_completed' => 'Profile Photo',
        'bank_details_completed' => 'Bank Details',
        'next_of_kin_completed' => 'Next of Kin',
        'referral_completed' => 'Referral',
        'saving_plan_completed' => 'Saving Plan',
        'ready_for_review' => 'Ready for Review',
        'submitted' => 'Submitted',
    ];
    $stageKeys = array_keys($stages);
    $currentIdx = array_search($application->current_stage ?? '', $stageKeys);
    if ($currentIdx === false) {
        $currentIdx = -1;
    }
@endphp

<div x-data="{ approveModal: false, correctionModal: false, rejectModal: false }" class="animate-fade-in">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.membership-applications.index') }}" class="px-3 py-2 rounded-lg border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition shrink-0">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-primary-900">{{ $application->application_number ?? 'N/A' }}</h1>
                    <span class="badge {{ $statusBadge }}">{{ ucfirst(str_replace('_', ' ', $appStatus ?: 'unknown')) }}</span>
                </div>
                <p class="text-sm text-primary-600">{{ $applicantName }}</p>
            </div>
        </div>
        @if($canAct)
            <div class="flex items-center gap-2">
                <button type="button" @click="approveModal = true" class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
                <button type="button" @click="correctionModal = true" class="px-4 py-2 rounded-lg bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600 transition flex items-center gap-2">
                    <i class="fa-solid fa-pen"></i> Correction
                </button>
                <button type="button" @click="rejectModal = true" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-times"></i> Reject
                </button>
            </div>
        @endif
    </div>

    @if(!$application->user && !$application->personalDetail)
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-5 mb-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-yellow-500"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-yellow-800">Incomplete Application Data</h3>
                <p class="text-xs text-yellow-600 mt-1">This application has no linked user account or personal details record. Some sections below may be empty.</p>
            </div>
        </div>
    @endif

    @if($application->rejection_reason)
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 mb-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-ban text-red-500"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-red-800">Rejection Reason</h3>
                <p class="text-sm text-red-700 mt-1">{{ $application->rejection_reason }}</p>
            </div>
        </div>
    @endif

    @if($application->correction_notes)
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-5 mb-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-pen-to-square text-yellow-500"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-yellow-800">Correction Notes</h3>
                <p class="text-sm text-yellow-700 mt-1">{{ $application->correction_notes }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user-tie text-indigo-500 text-xs"></i>
                    </span>
                    PERSONAL DETAILS
                </h2>
                @if($application->personalDetail)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">First Name</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->first_name ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Middle Name</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->middle_name ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Last Name</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->last_name ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Full Name</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->full_name ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Date of Birth</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->date_of_birth?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Gender</p>
                            <p class="text-sm font-semibold text-primary-800">{{ ucfirst($application->personalDetail?->gender ?? '') ?: '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Nationality</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->nationality ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">National ID Number</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->national_id_number ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Marital Status</p>
                            <p class="text-sm font-semibold text-primary-800">{{ ucfirst($application->personalDetail?->marital_status ?? '') ?: '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Occupation</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->occupation ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Employer</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->employer ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Phone</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->phone ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Email</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->email ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-primary-100">
                        <h3 class="text-xs font-bold text-primary-600 uppercase mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-primary-400"></i> Address
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-primary-500 mb-1">Region</p>
                                <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->region ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-primary-500 mb-1">District</p>
                                <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->district ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-primary-500 mb-1">Ward</p>
                                <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->ward ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-primary-500 mb-1">Street</p>
                                <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->street ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 sm:col-span-2 lg:col-span-3">
                                <p class="text-xs text-primary-500 mb-1">Full Address</p>
                                <p class="text-sm font-semibold text-primary-800">{{ $application->personalDetail?->address ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No personal details submitted yet.</p>
                @endif
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-camera text-pink-500 text-xs"></i>
                    </span>
                    PROFILE PHOTO & DOCUMENTS
                </h2>
                @if($application->documents && $application->documents->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($application->documents as $doc)
                            @php
                                $ext = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            <div class="rounded-xl bg-gray-50 border border-primary-100 p-3 flex items-center gap-3">
                                @if($isImage)
                                    <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $doc->file_name }}" class="w-16 h-16 rounded-lg object-cover border border-primary-200 shrink-0">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-primary-100 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-file text-primary-400 text-xl"></i>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-primary-800 truncate">{{ $doc->document_type ?? 'Document' }}</p>
                                    <p class="text-xs text-primary-500 truncate">{{ $doc->file_name ?? '-' }}</p>
                                    @if($doc->file_size)
                                        <p class="text-xs text-primary-400 mt-0.5">{{ round($doc->file_size / 1024, 1) }} KB</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No documents uploaded yet.</p>
                @endif
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-credit-card text-emerald-500 text-xs"></i>
                    </span>
                    PAYMENT DETAILS
                </h2>
                @if($application->payments && $application->payments->count())
                    <div class="space-y-3">
                        @foreach($application->payments as $payment)
                            @php
                                $payStatus = $payment->status ?? '';
                                $payBadge = match($payStatus) {
                                    'successful' => 'badge-green',
                                    'pending' => 'badge-yellow',
                                    'failed' => 'badge-red',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <div class="rounded-xl bg-gray-50 border border-primary-100 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-bold text-primary-800">TZS {{ number_format($payment->amount ?? 0) }}</span>
                                    <span class="badge {{ $payBadge }}">{{ ucfirst($payStatus ?: 'unknown') }}</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Method</p>
                                        <p class="font-semibold text-primary-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '')) ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Reference</p>
                                        <p class="font-semibold text-primary-800">{{ $payment->transaction_reference ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Paid At</p>
                                        <p class="font-semibold text-primary-800">{{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No payment records found.</p>
                @endif
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-building-columns text-sky-500 text-xs"></i>
                    </span>
                    BANK DETAILS
                </h2>
                @if($application->bankAccounts && $application->bankAccounts->count())
                    <div class="space-y-3">
                        @foreach($application->bankAccounts as $bank)
                            <div class="rounded-xl bg-gray-50 border border-primary-100 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-bold text-primary-800 uppercase">{{ $bank->bank_name ?? '-' }}</span>
                                    @if($bank->is_primary)
                                        <span class="badge badge-green text-[10px]">Primary</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Account Name</p>
                                        <p class="font-semibold text-primary-800">{{ $bank->account_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Account Number</p>
                                        <p class="font-semibold text-primary-800">{{ $bank->account_number ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Branch</p>
                                        <p class="font-semibold text-primary-800">{{ $bank->branch ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No bank details provided.</p>
                @endif
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-people-roof text-violet-500 text-xs"></i>
                    </span>
                    NEXT OF KIN
                </h2>
                @if($application->nextOfKin && $application->nextOfKin->count())
                    <div class="space-y-3">
                        @foreach($application->nextOfKin as $kin)
                            <div class="rounded-xl bg-gray-50 border border-primary-100 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-bold text-primary-800">{{ $kin->full_name ?? '-' }}</span>
                                    @if($kin->is_primary)
                                        <span class="badge badge-green text-[10px]">Primary</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Relationship</p>
                                        <p class="font-semibold text-primary-800">{{ ucfirst($kin->relationship ?? '') ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Phone</p>
                                        <p class="font-semibold text-primary-800">{{ $kin->phone ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-primary-500 mb-0.5">Alt Phone</p>
                                        <p class="font-semibold text-primary-800">{{ $kin->alternative_phone ?? '-' }}</p>
                                    </div>
                                    <div class="sm:col-span-2 lg:col-span-3">
                                        <p class="text-xs text-primary-500 mb-0.5">Address</p>
                                        <p class="font-semibold text-primary-800">{{ $kin->address ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No next of kin details provided.</p>
                @endif
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-handshake text-amber-500 text-xs"></i>
                    </span>
                    REFERRAL
                </h2>
                @if($application->referral)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Was Referred</p>
                            <p class="text-sm font-semibold text-primary-800">{{ ($application->referral?->was_referred ?? false) ? 'Yes' : 'No' }}</p>
                        </div>
                        @if($application->referral?->was_referred)
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-primary-500 mb-1">Referee Member Code</p>
                                <p class="text-sm font-semibold text-primary-800">{{ $application->referral?->referee_membercode ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-primary-500 mb-1">Referee Name</p>
                                <p class="text-sm font-semibold text-primary-800">{{ $application->referral?->referee_name ?? '-' }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No referral information submitted.</p>
                @endif
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-piggy-bank text-teal-500 text-xs"></i>
                    </span>
                    SAVING PLAN
                </h2>
                @if($application->savingPlan)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Plan Name</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->savingPlan?->plan_name ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Frequency</p>
                            <p class="text-sm font-semibold text-primary-800">{{ ucfirst($application->savingPlan?->frequency ?? '') ?: '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Target Amount</p>
                            <p class="text-sm font-semibold text-primary-800">TZS {{ number_format($application->savingPlan?->target_amount ?? 0) }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Periodic Amount</p>
                            <p class="text-sm font-semibold text-primary-800">TZS {{ number_format($application->savingPlan?->periodic_amount ?? 0) }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Expected Saving Date</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->savingPlan?->expected_saving_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No saving plan submitted.</p>
                @endif
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-id-card text-orange-500 text-xs"></i>
                    </span>
                    MEMBERSHIP TYPE
                </h2>
                @if($application->membershipType)
                    @php
                        $payStatus = $application->payment_status ?? '';
                        $payBadge = match($payStatus) {
                            'successful' => 'badge-green',
                            'pending' => 'badge-yellow',
                            'failed' => 'badge-red',
                            default => 'badge-gray',
                        };
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Type</p>
                            <p class="text-sm font-semibold text-primary-800">{{ $application->membershipType?->name ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Registration Fee</p>
                            <p class="text-sm font-semibold text-primary-800">TZS {{ number_format($application->membershipType?->registration_fee ?? 0) }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-primary-500 mb-1">Payment Status</p>
                            <span class="badge {{ $payBadge }}">{{ ucfirst($payStatus ?: 'N/A') }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No membership type selected.</p>
                @endif
            </div>

        </div>

        <div class="space-y-6">

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-chart-line text-blue-500 text-xs"></i>
                    </span>
                    APPLICATION STATUS
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-500">Status</span>
                        <span class="badge {{ $statusBadge }}">{{ ucfirst(str_replace('_', ' ', $appStatus ?: 'unknown')) }}</span>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs text-primary-500">Progress</span>
                            <span class="text-xs font-bold text-primary-800">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-primary-100 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-500">Current Stage</span>
                        <span class="text-xs font-bold text-primary-800">{{ ucfirst(str_replace('_', ' ', $application->current_stage ?? 'unknown')) }}</span>
                    </div>
                    <div class="border-t border-primary-100 pt-3 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-primary-500">Submitted</span>
                            <span class="text-xs text-primary-800">{{ $application->submitted_at?->format('d M Y, H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-primary-500">Reviewed</span>
                            <span class="text-xs text-primary-800">{{ $application->reviewed_at?->format('d M Y, H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-primary-500">Approved</span>
                            <span class="text-xs text-primary-800">{{ $application->approved_at?->format('d M Y, H:i') ?? '-' }}</span>
                        </div>
                    </div>
                    @if($application->membercode)
                        <div class="border-t border-primary-100 pt-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-primary-500">Member Code</span>
                                <span class="badge badge-green">{{ $application->membercode }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card rounded-xl p-6">
                <h2 class="text-sm font-bold text-primary-800 mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-list-check text-purple-500 text-xs"></i>
                    </span>
                    REGISTRATION STAGES
                </h2>
                <div class="space-y-0">
                    @foreach($stages as $key => $label)
                        @php
                            $idx = array_search($key, $stageKeys);
                            $isComplete = ($appStatus === 'approved') || ($idx !== false && $idx < $currentIdx);
                            $isCurrent = ($idx !== false && $idx === $currentIdx) && $appStatus !== 'approved';
                        @endphp
                        <div class="flex items-start gap-3 py-2 relative">
                            @if(!$loop->last)
                                <div class="absolute left-[9px] top-7 w-0.5 h-full {{ $isComplete ? 'bg-green-300' : 'bg-gray-200' }}"></div>
                            @endif
                            <div class="shrink-0 mt-0.5">
                                @if($isComplete)
                                    <i class="fa-solid fa-check-circle text-green-500 text-sm"></i>
                                @elseif($isCurrent)
                                    <i class="fa-solid fa-circle-dot text-primary-500 text-sm"></i>
                                @else
                                    <i class="fa-regular fa-circle text-gray-300 text-sm"></i>
                                @endif
                            </div>
                            <span class="text-xs leading-tight {{ $isCurrent ? 'font-bold text-primary-800' : ($isComplete ? 'text-green-700 font-medium' : 'text-gray-400') }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <div x-show="approveModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-black/50" @click="approveModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="text-center">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-check text-green-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-primary-800 mb-2">Approve Application</h3>
                <p class="text-sm text-primary-500 mb-1">Application: <strong>{{ $application->application_number ?? '' }}</strong></p>
                <p class="text-sm text-primary-500 mb-5">A member account will be created with a unique member code.</p>
                <div class="flex gap-3">
                    <button type="button" @click="approveModal = false" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-primary-600 text-sm font-semibold hover:bg-gray-50 transition">Cancel</button>
                    <form method="POST" action="{{ url('admin/membership-applications/' . $application->id . '/approve') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">Yes, Approve</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div x-show="correctionModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-black/50" @click="correctionModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-pen text-yellow-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-primary-800 mb-2">Request Correction</h3>
                <p class="text-sm text-primary-500 mb-1">Application: <strong>{{ $application->application_number ?? '' }}</strong></p>
                <p class="text-sm text-primary-500">Describe what needs to be corrected.</p>
            </div>
            <form method="POST" action="{{ url('admin/membership-applications/' . $application->id . '/correction') }}">
                @csrf
                <div class="mb-5">
                    <textarea name="correction_notes" class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-primary-800 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-yellow-400 transition" rows="4" placeholder="What needs to be corrected?" required></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="correctionModal = false" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-primary-600 text-sm font-semibold hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600 transition">Send Request</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="rejectModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-black/50" @click="rejectModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-times text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-primary-800 mb-2">Reject Application</h3>
                <p class="text-sm text-primary-500 mb-1">Application: <strong>{{ $application->application_number ?? '' }}</strong></p>
                <p class="text-sm text-primary-500">This action cannot be undone. Provide a reason.</p>
            </div>
            <form method="POST" action="{{ url('admin/membership-applications/' . $application->id . '/reject') }}">
                @csrf
                <div class="mb-5">
                    <textarea name="rejection_reason" class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-primary-800 focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400 transition" rows="4" placeholder="Reason for rejection..." required></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="rejectModal = false" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-primary-600 text-sm font-semibold hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection