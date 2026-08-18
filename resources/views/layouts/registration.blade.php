@extends('layouts.app')

@section('layout_content')
@php
    $regHelper = app(\App\Services\Registration\RegistrationViewHelper::class);
    if ($application) {
        $regHelper->setApplication($application);
    }
@endphp
<div class="min-h-screen flex" x-data="registrationState()">
    <div class="hidden lg:flex lg:w-72 xl:w-80 sidebar-bg flex-col fixed h-full z-30">
        <div class="p-5 border-b border-white/10">
            <a href="{{ route('register.dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-white font-bold text-sm tracking-wide">REGISTRATION</h1>
                    <p class="text-primary-300 text-[10px] tracking-widest">MEMBERSHIP PORTAL</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 py-4 px-3 overflow-y-auto">
            <div class="mb-4">
                <p class="text-primary-400 text-[10px] font-bold tracking-widest px-3 mb-2">NAVIGATION</p>
                <a href="{{ route('register.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('register.dashboard') ? 'active' : 'text-primary-100' }}">
                    <i class="fa-solid fa-gauge-high w-5 text-center text-xs"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="mb-4">
                <p class="text-primary-400 text-[10px] font-bold tracking-widest px-3 mb-2">APPLICATION</p>
                <a href="{{ route('register.membership-type') }}" class="{{ $regHelper->getStageStatus('membership_selected', 'register.membership-type') }}">
                    <i class="fa-solid fa-layer-group w-5 text-center text-xs"></i>
                    <span>Membership Type</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('membership_selected') !!}</span>
                </a>
                <a href="{{ route('register.payment') }}" class="{{ $regHelper->getStageStatus('payment_completed', 'register.payment') }}">
                    <i class="fa-solid fa-credit-card w-5 text-center text-xs"></i>
                    <span>Payment</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('payment_completed') !!}</span>
                </a>
            </div>

            <div class="mb-4">
                <p class="text-primary-400 text-[10px] font-bold tracking-widest px-3 mb-2">PERSONAL INFORMATION</p>
                <a href="{{ route('register.personal-details') }}" class="{{ $regHelper->getStageStatus('personal_details_completed', 'register.personal-details') }}">
                    <i class="fa-solid fa-user w-5 text-center text-xs"></i>
                    <span>Personal Details</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('personal_details_completed') !!}</span>
                </a>
                <a href="{{ route('register.profile-photo') }}" class="{{ $regHelper->getStageStatus('profile_completed', 'register.profile-photo') }}">
                    <i class="fa-solid fa-camera w-5 text-center text-xs"></i>
                    <span>Profile Photo</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('profile_completed') !!}</span>
                </a>
            </div>

            <div class="mb-4">
                <p class="text-primary-400 text-[10px] font-bold tracking-widest px-3 mb-2">FINANCIAL INFORMATION</p>
                <a href="{{ route('register.bank-details') }}" class="{{ $regHelper->getStageStatus('bank_details_completed', 'register.bank-details') }}">
                    <i class="fa-solid fa-building-columns w-5 text-center text-xs"></i>
                    <span>Bank Details</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('bank_details_completed') !!}</span>
                </a>
                <a href="{{ route('register.saving-plan') }}" class="{{ $regHelper->getStageStatus('saving_plan_completed', 'register.saving-plan') }}">
                    <i class="fa-solid fa-piggy-bank w-5 text-center text-xs"></i>
                    <span>Saving Plan</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('saving_plan_completed') !!}</span>
                </a>
            </div>

            <div class="mb-4">
                <p class="text-primary-400 text-[10px] font-bold tracking-widest px-3 mb-2">OTHER INFORMATION</p>
                <a href="{{ route('register.next-of-kin') }}" class="{{ $regHelper->getStageStatus('next_of_kin_completed', 'register.next-of-kin') }}">
                    <i class="fa-solid fa-people-roof w-5 text-center text-xs"></i>
                    <span>Next of Kin</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('next_of_kin_completed') !!}</span>
                </a>
                <a href="{{ route('register.referral') }}" class="{{ $regHelper->getStageStatus('referral_completed', 'register.referral') }}">
                    <i class="fa-solid fa-handshake w-5 text-center text-xs"></i>
                    <span>Referral</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('referral_completed') !!}</span>
                </a>
            </div>

            <div class="mb-4">
                <p class="text-primary-400 text-[10px] font-bold tracking-widest px-3 mb-2">SUBMISSION</p>
                <a href="{{ route('register.review') }}" class="{{ $regHelper->getStageStatus('ready_for_review', 'register.review') }}">
                    <i class="fa-solid fa-clipboard-check w-5 text-center text-xs"></i>
                    <span>Review Application</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('ready_for_review') !!}</span>
                </a>
                <a href="{{ route('register.submit') }}" class="{{ $regHelper->getStageStatus('submitted', 'register.submit') }}">
                    <i class="fa-solid fa-paper-plane w-5 text-center text-xs"></i>
                    <span>Submit Application</span>
                    <span class="ml-auto">{!! $regHelper->getStageIcon('submitted') !!}</span>
                </a>
            </div>

            <div class="mb-4">
                <p class="text-primary-400 text-[10px] font-bold tracking-widest px-3 mb-2">STATUS</p>
                <a href="{{ route('register.status') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('register.status') ? 'active' : 'text-primary-100' }}">
                    <i class="fa-solid fa-circle-info w-5 text-center text-xs"></i>
                    <span>Application Status</span>
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-300 text-sm font-bold">
                    {{ substr(auth()->user()->email, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">{{ auth()->user()->email }}</p>
                    <p class="text-primary-400 text-[10px]">{{ $application->application_number ?? 'New Application' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-primary-400 hover:text-white transition">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="lg:hidden fixed top-0 left-0 right-0 z-40 navbar-bg px-4 py-3 flex items-center justify-between">
        <button @click="sidebarOpen = !sidebarOpen" class="text-primary-700">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <h1 class="text-sm font-bold text-primary-800">REGISTRATION</h1>
        <div class="w-6"></div>
    </div>

    <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-200" @click="sidebarOpen = false" class="lg:hidden fixed inset-0 bg-black/50 z-40 backdrop-blur-sm" style="display:none"></div>

    <div x-show="sidebarOpen" x-transition:enter="transition-transform duration-200" x-transition:leave="transition-transform duration-200" class="lg:hidden fixed left-0 top-0 bottom-0 w-72 sidebar-bg z-50 transform -translate-x-full" :class="{ 'translate-x-0': sidebarOpen }" style="display:none">
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h1 class="text-white font-bold text-sm">REGISTRATION</h1>
            <button @click="sidebarOpen = false" class="text-primary-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <nav class="py-4 px-3 overflow-y-auto h-[calc(100%-140px)]">
            <a href="{{ route('register.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-100">
                <i class="fa-solid fa-gauge-high w-5 text-center text-xs"></i>
                <span>Dashboard</span>
            </a>
        </nav>
    </div>

    <div class="flex-1 lg:ml-72 xl:lg:ml-80 pt-16 lg:pt-0">
        <div class="p-4 md:p-6 lg:p-8">
            @if($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm animate-fade-in">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-exclamation-triangle text-red-500"></i>
                        <span class="font-semibold">Please correct the following:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

@push('scripts')
<script>
    function registrationState() {
        return {
            sidebarOpen: false,
        }
    }
</script>
@endpush
@endsection
