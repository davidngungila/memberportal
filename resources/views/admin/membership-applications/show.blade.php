@extends('layouts.admin')

@section('page_title', 'Application ' . $application->application_number)

@section('content')
<div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-primary-900">Application {{ $application->application_number }}</h1>
            <p class="text-primary-600 text-sm">{{ $application->personalDetail->full_name ?? $application->user?->name ?? 'Unknown Applicant' }}</p>
        </div>
        <a href="{{ route('admin.membership-applications.index') }}" class="px-4 py-2 rounded-lg border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    @if(!$application->user && !$application->personalDetail)
        <div class="card p-8 text-center mb-6 border-yellow-200 bg-yellow-50">
            <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-triangle-exclamation text-yellow-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-primary-800 mb-2">Incomplete Application Data</h3>
            <p class="text-sm text-primary-600 mb-2">This application has no linked user or personal details.</p>
            <p class="text-xs text-primary-400">Application #{{ $application->application_number }} | Status: {{ ucfirst($application->application_status ?? 'draft') }} | Stage: {{ ucfirst(str_replace('_', ' ', $application->current_stage ?? 'unknown')) }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            {{-- APPLICANT ACCOUNT --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user text-primary-500"></i> APPLICANT ACCOUNT
                </h2>
                @if($application->user)
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">Name:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->user->name ?? '-' }}</div>
                        <div><span class="text-primary-600">Phone (Login):</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->user->phone ?? '-' }}</div>
                        <div><span class="text-primary-600">Email:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->user->email ?? 'Not provided' }}</div>
                        <div><span class="text-primary-600">Registered:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->user->created_at?->format('d M Y, H:i') ?? '-' }}</div>
                    </div>
                @else
                    <p class="text-sm text-primary-400 italic">No user account linked to this application.</p>
                @endif
            </div>

            {{-- MEMBERSHIP TYPE --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-primary-500"></i> MEMBERSHIP TYPE
                </h2>
                @if($application->membershipType)
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">Type:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->membershipType->name }}</div>
                        <div><span class="text-primary-600">Registration Fee:</span></div>
                        <div class="font-semibold text-primary-800">TZS {{ number_format($application->membershipType->registration_fee ?? 0) }}</div>
                        <div><span class="text-primary-600">Payment Status:</span></div>
                        <div>
                            <span class="badge {{ ($application->payment_status ?? '') === 'successful' ? 'badge-green' : (($application->payment_status ?? '') === 'pending' ? 'badge-yellow' : 'badge-gray') }}">
                                {{ ucfirst($application->payment_status ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">Type:</span></div>
                        <div class="font-semibold text-primary-400 italic">Not selected</div>
                        <div><span class="text-primary-600">Payment Status:</span></div>
                        <div>
                            <span class="badge badge-gray">{{ ucfirst($application->payment_status ?? 'N/A') }}</span>
                        </div>
                    </div>
                @endif
            </div>
                </div>
            </div>

            {{-- PERSONAL DETAILS --}}
            @if($application->personalDetail)
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-user-tie text-primary-500"></i> PERSONAL DETAILS
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">First Name:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->first_name ?? '-' }}</div>
                        <div><span class="text-primary-600">Middle Name:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->middle_name ?? '-' }}</div>
                        <div><span class="text-primary-600">Last Name:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->last_name ?? '-' }}</div>
                        <div><span class="text-primary-600">Full Name:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->full_name }}</div>
                        <div><span class="text-primary-600">Date of Birth:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->date_of_birth?->format('d M Y') ?? '-' }}</div>
                        <div><span class="text-primary-600">Gender:</span></div>
                        <div class="font-semibold text-primary-800">{{ ucfirst($application->personalDetail->gender ?? '-') }}</div>
                        <div><span class="text-primary-600">Nationality:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->nationality ?? '-' }}</div>
                        <div><span class="text-primary-600">National ID:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->national_id_number ?? '-' }}</div>
                        <div><span class="text-primary-600">Marital Status:</span></div>
                        <div class="font-semibold text-primary-800">{{ ucfirst($application->personalDetail->marital_status ?? '-') }}</div>
                        <div><span class="text-primary-600">Occupation:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->occupation ?? '-' }}</div>
                        <div><span class="text-primary-600">Employer:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->employer ?? '-' }}</div>
                        <div><span class="text-primary-600">Phone:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->phone ?? '-' }}</div>
                        <div><span class="text-primary-600">Email:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->email ?? '-' }}</div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-primary-100">
                        <h3 class="text-xs font-bold text-primary-600 uppercase mb-3">Address</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><span class="text-primary-600">Region:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->personalDetail->region ?? '-' }}</div>
                            <div><span class="text-primary-600">District:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->personalDetail->district ?? '-' }}</div>
                            <div><span class="text-primary-600">Ward:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->personalDetail->ward ?? '-' }}</div>
                            <div><span class="text-primary-600">Street:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->personalDetail->street ?? '-' }}</div>
                            <div><span class="text-primary-600">Full Address:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->personalDetail->address ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- PROFILE PHOTO --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-camera text-primary-500"></i> PROFILE PHOTO / DOCUMENTS
                </h2>
                @if($application->documents && $application->documents->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($application->documents as $doc)
                            <div class="p-3 rounded-lg bg-primary-50 border border-primary-100">
                                <div class="flex items-center gap-3">
                                    @if(in_array(strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $doc->file_name }}" class="w-16 h-16 rounded-lg object-cover border border-primary-200">
                                    @else
                                        <div class="w-16 h-16 rounded-lg bg-primary-100 flex items-center justify-center">
                                            <i class="fa-solid fa-file text-primary-400 text-xl"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-primary-800 truncate">{{ $doc->document_type ?? 'Document' }}</p>
                                        <p class="text-xs text-primary-500 truncate">{{ $doc->file_name ?? '-' }}</p>
                                        @if($doc->file_size)
                                            <p class="text-xs text-primary-400">{{ round($doc->file_size / 1024, 1) }} KB</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-primary-400 italic">No documents uploaded yet.</p>
                @endif
            </div>

            {{-- PAYMENT DETAILS --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-primary-500"></i> PAYMENT DETAILS
                </h2>
                @if($application->payments && $application->payments->count())
                    @foreach($application->payments as $payment)
                        <div class="p-3 rounded-lg bg-primary-50 border border-primary-100 {{ !$loop->first ? 'mt-2' : '' }}">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="text-primary-600">Amount:</span></div>
                                <div class="font-semibold text-primary-800">TZS {{ number_format($payment->amount ?? 0) }}</div>
                                <div><span class="text-primary-600">Method:</span></div>
                                <div class="font-semibold text-primary-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</div>
                                <div><span class="text-primary-600">Reference:</span></div>
                                <div class="font-semibold text-primary-800">{{ $payment->transaction_reference ?? '-' }}</div>
                                <div><span class="text-primary-600">Status:</span></div>
                                <div>
                                    <span class="badge {{ ($payment->status ?? '') === 'successful' ? 'badge-green' : (($payment->status ?? '') === 'pending' ? 'badge-yellow' : 'badge-red') }}">
                                        {{ ucfirst($payment->status ?? 'unknown') }}
                                    </span>
                                </div>
                                @if($payment->paid_at)
                                    <div><span class="text-primary-600">Paid At:</span></div>
                                    <div class="font-semibold text-primary-800">{{ $payment->paid_at->format('d M Y, H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-primary-400 italic">No payment records found.</p>
                @endif
            </div>

            {{-- BANK DETAILS --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-primary-500"></i> BANK DETAILS
                </h2>
                @if($application->bankAccounts && $application->bankAccounts->count())
                    @foreach($application->bankAccounts as $bank)
                        <div class="p-3 rounded-lg bg-primary-50 border border-primary-100 {{ !$loop->first ? 'mt-2' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-primary-700 uppercase">{{ $bank->bank_name }}</span>
                                @if($bank->is_primary)
                                    <span class="badge badge-green text-[10px]">Primary</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="text-primary-600">Account Name:</span></div>
                                <div class="font-semibold text-primary-800">{{ $bank->account_name }}</div>
                                <div><span class="text-primary-600">Account Number:</span></div>
                                <div class="font-semibold text-primary-800">{{ $bank->account_number }}</div>
                                <div><span class="text-primary-600">Branch:</span></div>
                                <div class="font-semibold text-primary-800">{{ $bank->branch ?? '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-primary-400 italic">No bank details provided.</p>
                @endif
            </div>

            {{-- NEXT OF KIN --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-people-roof text-primary-500"></i> NEXT OF KIN
                </h2>
                @if($application->nextOfKin && $application->nextOfKin->count())
                    @foreach($application->nextOfKin as $kin)
                        <div class="p-3 rounded-lg bg-primary-50 border border-primary-100 {{ !$loop->first ? 'mt-2' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-primary-700 uppercase">{{ $kin->full_name }}</span>
                                @if($kin->is_primary)
                                    <span class="badge badge-green text-[10px]">Primary</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="text-primary-600">Relationship:</span></div>
                                <div class="font-semibold text-primary-800">{{ ucfirst($kin->relationship) }}</div>
                                <div><span class="text-primary-600">Phone:</span></div>
                                <div class="font-semibold text-primary-800">{{ $kin->phone }}</div>
                                @if($kin->alternative_phone)
                                    <div><span class="text-primary-600">Alt Phone:</span></div>
                                    <div class="font-semibold text-primary-800">{{ $kin->alternative_phone }}</div>
                                @endif
                                @if($kin->address)
                                    <div><span class="text-primary-600">Address:</span></div>
                                    <div class="font-semibold text-primary-800">{{ $kin->address }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-primary-400 italic">No next of kin details provided.</p>
                @endif
            </div>

            {{-- REFERRAL --}}
            @if($application->referral)
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-handshake text-primary-500"></i> REFERRAL
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">Referred:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->referral->was_referred ? 'Yes' : 'No' }}</div>
                        @if($application->referral->was_referred)
                            <div><span class="text-primary-600">Referee Code:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->referral->referee_membercode ?? '-' }}</div>
                            <div><span class="text-primary-600">Referee Name:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->referral->referee_name ?? '-' }}</div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- SAVING PLAN --}}
            @if($application->savingPlan)
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-piggy-bank text-primary-500"></i> SAVING PLAN
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">Plan Name:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->savingPlan->plan_name ?? '-' }}</div>
                        <div><span class="text-primary-600">Frequency:</span></div>
                        <div class="font-semibold text-primary-800">{{ ucfirst($application->savingPlan->frequency ?? '-') }}</div>
                        <div><span class="text-primary-600">Target Amount:</span></div>
                        <div class="font-semibold text-primary-800">TZS {{ number_format($application->savingPlan->target_amount ?? 0) }}</div>
                        <div><span class="text-primary-600">Periodic Amount:</span></div>
                        <div class="font-semibold text-primary-800">TZS {{ number_format($application->savingPlan->periodic_amount ?? 0) }}</div>
                        @if($application->savingPlan->expected_saving_date)
                            <div><span class="text-primary-600">Expected Saving Date:</span></div>
                            <div class="font-semibold text-primary-800">{{ $application->savingPlan->expected_saving_date->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- SIDEBAR --}}
        <div class="space-y-6">
            {{-- APPLICATION STATUS --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-primary-500"></i> APPLICATION STATUS
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Status</span>
                        <span class="badge {{ match($application->application_status ?? '') {
                            'submitted' => 'badge-blue',
                            'under_review' => 'badge-blue',
                            'approved' => 'badge-green',
                            'rejected' => 'badge-red',
                            'correction_required' => 'badge-yellow',
                            default => 'badge-gray',
                        } }}">
                            {{ ucfirst(str_replace('_', ' ', $application->application_status ?? 'unknown')) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Progress</span>
                        <span class="text-xs font-bold text-primary-800">{{ $application->getProgressPercentage() }}%</span>
                    </div>
                    <div class="w-full bg-primary-100 rounded-full h-2">
                        <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: {{ $application->getProgressPercentage() }}%"></div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Current Stage</span>
                        <span class="text-xs font-bold text-primary-800">{{ ucfirst(str_replace('_', ' ', $application->current_stage ?? 'unknown')) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Submitted</span>
                        <span class="text-xs text-primary-800">{{ $application->submitted_at?->format('d M Y, H:i') ?? '-' }}</span>
                    </div>
                    @if($application->reviewed_at)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-primary-600">Reviewed</span>
                            <span class="text-xs text-primary-800">{{ $application->reviewed_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                    @if($application->approved_at)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-primary-600">Approved</span>
                            <span class="text-xs text-primary-800">{{ $application->approved_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                    @if($application->membercode)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-primary-600">Member Code</span>
                            <span class="badge badge-green">{{ $application->membercode }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ACTIONS --}}
            @if(in_array($application->application_status, ['submitted', 'under_review', 'correction_required']))
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-gavel text-primary-500"></i> ACTIONS
                    </h2>

                    <div x-data="{ confirmApprove: false }">
                        <button type="button" x-show="!confirmApprove" @click="confirmApprove = true" class="w-full px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check"></i> Approve Application
                        </button>
                        <div x-show="confirmApprove" x-transition class="p-3 rounded-lg bg-green-50 border border-green-200">
                            <p class="text-sm text-green-800 font-semibold mb-2">Approve this application?</p>
                            <p class="text-xs text-green-600 mb-3">A member account will be created with a unique member code.</p>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.membership-applications.approve', $application) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition">
                                        Yes, Approve
                                    </button>
                                </form>
                                <button type="button" @click="confirmApprove = false" class="px-3 py-2 rounded-lg border border-primary-200 text-primary-600 text-xs font-semibold hover:bg-primary-50 transition">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.membership-applications.request-correction', $application) }}" class="mb-3" x-data="{ showCorrection: false }">
                        @csrf
                        <button type="button" @click="showCorrection = !showCorrection" class="w-full px-4 py-2.5 rounded-lg bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen"></i> Request Correction
                        </button>
                        <div x-show="showCorrection" x-transition class="mt-3 p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                            <textarea name="correction_notes" class="form-input text-xs" rows="3" placeholder="What needs to be corrected?" required></textarea>
                            <button type="submit" class="mt-2 w-full px-4 py-2 rounded-lg bg-yellow-500 text-white text-xs font-semibold hover:bg-yellow-600 transition">
                                Send Correction Request
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.membership-applications.reject', $application) }}" x-data="{ showReject: false }">
                        @csrf
                        <button type="button" @click="showReject = !showReject" class="w-full px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-times"></i> Reject Application
                        </button>
                        <div x-show="showReject" x-transition class="mt-3 p-3 rounded-lg bg-red-50 border border-red-200">
                            <textarea name="rejection_reason" class="form-input text-xs" rows="3" placeholder="Reason for rejection..." required></textarea>
                            <button type="submit" class="mt-2 w-full px-4 py-2 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">
                                Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($application->application_status === 'approved')
                <div class="card p-5 border-green-200 bg-green-50">
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-check-double text-green-500 text-xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-green-800 mb-1">Application Approved</h3>
                        <p class="text-xs text-green-600">Member created with code: <strong>{{ $application->membercode }}</strong></p>
                    </div>
                </div>
            @endif

            @if($application->rejection_reason)
                <div class="card p-5 border-red-200 bg-red-50">
                    <h2 class="text-sm font-bold text-red-800 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-ban"></i> Rejection Reason
                    </h2>
                    <p class="text-sm text-red-700">{{ $application->rejection_reason }}</p>
                </div>
            @endif

            @if($application->correction_notes)
                <div class="card p-5 border-yellow-200 bg-yellow-50">
                    <h2 class="text-sm font-bold text-yellow-800 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Correction Notes
                    </h2>
                    <p class="text-sm text-yellow-700">{{ $application->correction_notes }}</p>
                </div>
            @endif

            {{-- PROGRESS TIMELINE --}}
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-primary-500"></i> REGISTRATION STAGES
                </h2>
                @php
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
                    $currentOrder = $stages[$application->current_stage] ?? -1;
                    $stageKeys = array_keys($stages);
                    $currentIdx = array_search($application->current_stage, $stageKeys);
                    if ($currentIdx === false) $currentIdx = -1;
                @endphp
                <div class="space-y-1">
                    @foreach($stages as $key => $label)
                        @php
                            $idx = array_search($key, $stageKeys);
                            $isComplete = $application->application_status === 'approved' || $application->application_status === 'submitted' || $idx < $currentIdx;
                            $isCurrent = $idx === $currentIdx;
                        @endphp
                        <div class="flex items-center gap-2 py-1">
                            @if($isComplete || $application->application_status === 'approved')
                                <i class="fa-solid fa-check-circle text-green-500 text-xs"></i>
                            @elseif($isCurrent)
                                <i class="fa-solid fa-circle-dot text-primary-500 text-xs"></i>
                            @else
                                <i class="fa-regular fa-circle text-primary-300 text-xs"></i>
                            @endif
                            <span class="text-xs {{ $isCurrent ? 'font-bold text-primary-800' : ($isComplete ? 'text-green-700' : 'text-primary-400') }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
