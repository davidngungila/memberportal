@extends('layouts.admin')

@section('page_title', 'Application ' . $application->application_number)

@section('content')
<div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-primary-900">Application {{ $application->application_number }}</h1>
            <p class="text-primary-600 text-sm">{{ $application->personalDetail->full_name ?? $application->user->name ?? 'Unknown Applicant' }}</p>
        </div>
        <a href="{{ route('admin.membership-applications.index') }}" class="px-4 py-2 rounded-lg border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4">APPLICANT</h2>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-primary-600">Name:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->personalDetail->full_name ?? '-' }}</div>
                    <div><span class="text-primary-600">Email:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->user->email ?? '-' }}</div>
                    <div><span class="text-primary-600">Phone:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->personalDetail->phone ?? '-' }}</div>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4">MEMBERSHIP</h2>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-primary-600">Type:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->membershipType->name ?? '-' }}</div>
                    <div><span class="text-primary-600">Payment:</span></div>
                    <div>
                        <span class="badge {{ $application->payment_status === 'successful' ? 'badge-green' : 'badge-yellow' }}">
                            {{ ucfirst($application->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>

            @if($application->personalDetail)
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4">PERSONAL DETAILS</h2>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">Full Name:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->full_name }}</div>
                        <div><span class="text-primary-600">DOB:</span></div>
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
                        <div><span class="text-primary-600">Region:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->region ?? '-' }}</div>
                        <div><span class="text-primary-600">District:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->personalDetail->district ?? '-' }}</div>
                    </div>
                </div>
            @endif

            @if($application->bankAccounts->count())
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4">BANK DETAILS</h2>
                    @foreach($application->bankAccounts as $bank)
                        <div class="p-3 rounded-lg bg-primary-50 border border-primary-100 {{ !$loop->first ? 'mt-2' : '' }}">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="text-primary-600">Bank:</span></div>
                                <div class="font-semibold text-primary-800">{{ $bank->bank_name }}</div>
                                <div><span class="text-primary-600">Account Name:</span></div>
                                <div class="font-semibold text-primary-800">{{ $bank->account_name }}</div>
                                <div><span class="text-primary-600">Account Number:</span></div>
                                <div class="font-semibold text-primary-800">{{ $bank->account_number }}</div>
                                <div><span class="text-primary-600">Branch:</span></div>
                                <div class="font-semibold text-primary-800">{{ $bank->branch ?? '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($application->nextOfKin->count())
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4">NEXT OF KIN</h2>
                    @foreach($application->nextOfKin as $kin)
                        <div class="p-3 rounded-lg bg-primary-50 border border-primary-100 {{ !$loop->first ? 'mt-2' : '' }}">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="text-primary-600">Name:</span></div>
                                <div class="font-semibold text-primary-800">{{ $kin->full_name }}</div>
                                <div><span class="text-primary-600">Relationship:</span></div>
                                <div class="font-semibold text-primary-800">{{ ucfirst($kin->relationship) }}</div>
                                <div><span class="text-primary-600">Phone:</span></div>
                                <div class="font-semibold text-primary-800">{{ $kin->phone }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($application->savingPlan)
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4">SAVING PLAN</h2>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-primary-600">Plan:</span></div>
                        <div class="font-semibold text-primary-800">{{ $application->savingPlan->plan_name }}</div>
                        <div><span class="text-primary-600">Frequency:</span></div>
                        <div class="font-semibold text-primary-800">{{ ucfirst($application->savingPlan->frequency) }}</div>
                        <div><span class="text-primary-600">Target Amount:</span></div>
                        <div class="font-semibold text-primary-800">TZS {{ number_format($application->savingPlan->target_amount) }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-5">
                <h2 class="text-sm font-bold text-primary-800 mb-4">APPLICATION STATUS</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Status</span>
                        <span class="badge {{ match($application->application_status) {
                            'submitted' => 'badge-blue',
                            'under_review' => 'badge-blue',
                            'approved' => 'badge-green',
                            'rejected' => 'badge-red',
                            'correction_required' => 'badge-yellow',
                            default => 'badge-gray',
                        } }}">
                            {{ ucfirst(str_replace('_', ' ', $application->application_status)) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Progress</span>
                        <span class="text-xs font-bold text-primary-800">{{ $application->getProgressPercentage() }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-primary-600">Submitted</span>
                        <span class="text-xs text-primary-800">{{ $application->submitted_at?->format('d M Y') ?? '-' }}</span>
                    </div>
                </div>
            </div>

            @if(in_array($application->application_status, ['submitted', 'under_review', 'correction_required']))
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-primary-800 mb-4">ACTIONS</h2>

                    <form method="POST" action="{{ route('admin.membership-applications.approve', $application) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check"></i> Approve
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.membership-applications.request-correction', $application) }}" class="mb-3" x-data="{ showCorrection: false }">
                        @csrf
                        <button type="button" @click="showCorrection = !showCorrection" class="w-full px-4 py-2.5 rounded-lg bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen"></i> Request Correction
                        </button>
                        <div x-show="showCorrection" x-transition class="mt-3">
                            <textarea name="correction_notes" class="form-input text-xs" rows="3" placeholder="Enter correction notes..." required></textarea>
                            <button type="submit" class="mt-2 w-full px-4 py-2 rounded-lg bg-yellow-500 text-white text-xs font-semibold hover:bg-yellow-600 transition">
                                Send Correction Request
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.membership-applications.reject', $application) }}" x-data="{ showReject: false }">
                        @csrf
                        <button type="button" @click="showReject = !showReject" class="w-full px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-times"></i> Reject
                        </button>
                        <div x-show="showReject" x-transition class="mt-3">
                            <textarea name="rejection_reason" class="form-input text-xs" rows="3" placeholder="Enter rejection reason..." required></textarea>
                            <button type="submit" class="mt-2 w-full px-4 py-2 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">
                                Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($application->rejection_reason)
                <div class="card p-5 border-red-200">
                    <h2 class="text-sm font-bold text-red-800 mb-2">Rejection Reason</h2>
                    <p class="text-sm text-red-700">{{ $application->rejection_reason }}</p>
                </div>
            @endif

            @if($application->correction_notes)
                <div class="card p-5 border-yellow-200">
                    <h2 class="text-sm font-bold text-yellow-800 mb-2">Correction Notes</h2>
                    <p class="text-sm text-yellow-700">{{ $application->correction_notes }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
