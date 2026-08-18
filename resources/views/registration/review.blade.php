@extends('layouts.registration')

@section('page_title', 'Review Application')

@section('content')
<div class="animate-fade-in max-w-3xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Review Application</h1>
        <p class="text-primary-600 text-sm">Review your application before submission</p>
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-primary-800">ACCOUNT</h2>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-primary-600">Email:</span></div>
                <div class="font-semibold text-primary-800">{{ auth()->user()->email }}</div>
                <div><span class="text-primary-600">Phone:</span></div>
                <div class="font-semibold text-primary-800">{{ auth()->user()->phone }}</div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-primary-800">MEMBERSHIP</h2>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-primary-600">Type:</span></div>
                <div class="font-semibold text-primary-800">{{ $application->membershipType->name ?? 'Not selected' }}</div>
                <div><span class="text-primary-600">Payment:</span></div>
                <div>
                    <span class="badge {{ $application->payment_status === 'successful' ? 'badge-green' : 'badge-yellow' }}">
                        {{ ucfirst($application->payment_status) }}
                    </span>
                </div>
                <div><span class="text-primary-600">Amount:</span></div>
                <div class="font-semibold text-primary-800">TZS {{ number_format($application->membershipType->registration_fee ?? 0) }}</div>
            </div>
        </div>

        @if($application->personalDetail)
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-primary-800">PERSONAL DETAILS</h2>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-primary-600">Name:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->personalDetail->full_name }}</div>
                    <div><span class="text-primary-600">DOB:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->personalDetail->date_of_birth?->format('d M Y') ?? '-' }}</div>
                    <div><span class="text-primary-600">Gender:</span></div>
                    <div class="font-semibold text-primary-800">{{ ucfirst($application->personalDetail->gender ?? '-') }}</div>
                    <div><span class="text-primary-600">Nationality:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->personalDetail->nationality ?? '-' }}</div>
                    <div><span class="text-primary-600">Occupation:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->personalDetail->occupation ?? '-' }}</div>
                </div>
            </div>
        @endif

        @if($application->bankAccounts->count())
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-primary-800">BANK DETAILS</h2>
                </div>
                @foreach($application->bankAccounts as $bank)
                    <div class="p-3 rounded-lg bg-primary-50 border border-primary-100 {{ !$loop->first ? 'mt-2' : '' }}">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div><span class="text-primary-600">Bank:</span></div>
                            <div class="font-semibold text-primary-800">{{ $bank->bank_name }}</div>
                            <div><span class="text-primary-600">Account:</span></div>
                            <div class="font-semibold text-primary-800">****{{ substr($bank->account_number, -4) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($application->nextOfKin->count())
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-primary-800">NEXT OF KIN</h2>
                </div>
                @foreach($application->nextOfKin as $kin)
                    <div class="p-3 rounded-lg bg-primary-50 border border-primary-100 {{ !$loop->first ? 'mt-2' : '' }}">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div><span class="text-primary-600">Name:</span></div>
                            <div class="font-semibold text-primary-800">{{ $kin->full_name }}</div>
                            <div><span class="text-primary-600">Relationship:</span></div>
                            <div class="font-semibold text-primary-800">{{ ucfirst($kin->relationship) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($application->referral && $application->referral->was_referred)
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-primary-800">REFERRAL</h2>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-primary-600">Member Code:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->referral->referee_membercode }}</div>
                    <div><span class="text-primary-600">Name:</span></div>
                    <div class="font-semibold text-primary-800">{{ $application->referral->referee_name }}</div>
                </div>
            </div>
        @endif

        @if($application->savingPlan)
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-primary-800">SAVING PLAN</h2>
                </div>
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

    <div class="mt-6 flex items-center gap-4">
        <a href="{{ route('register.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <a href="{{ route('register.submit') }}" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2">
            <i class="fa-solid fa-paper-plane"></i>
            Submit Application
        </a>
    </div>
</div>
@endsection
