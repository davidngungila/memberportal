@extends('layouts.registration')

@section('page_title', 'Select Membership Type')

@section('content')
<div class="animate-fade-in max-w-3xl">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                <i class="fa-solid fa-layer-group text-primary-600"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-primary-900">Choose Your Membership</h1>
                <p class="text-primary-500 text-sm">Select the plan that best fits your needs</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('register.membership-type.store') }}" x-data="{ selected: '{{ old('membership_type_id') }}' }">
        @csrf

        <div class="grid grid-cols-1 gap-5 mb-8">
            @foreach($membershipTypes as $type)
                <label class="group relative cursor-pointer">
                    <input type="radio" name="membership_type_id" value="{{ $type->id }}" class="sr-only peer" {{ old('membership_type_id') == $type->id ? 'checked' : '' }} required x-model="selected">
                    <div class="card p-0 overflow-hidden transition-all duration-200 peer-checked:border-primary-500 peer-checked:ring-2 peer-checked:ring-primary-500/20 hover:shadow-lg hover:-translate-y-0.5">
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-primary-50 to-primary-100/50 border-b border-primary-200/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr($type->code, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-primary-900">{{ $type->name }}</h3>
                                    <span class="text-xs font-semibold text-primary-500 uppercase tracking-wider">{{ $type->code }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-extrabold text-primary-700">TZS {{ number_format($type->registration_fee) }}</p>
                                <p class="text-[10px] text-primary-500 font-semibold uppercase">Registration Fee</p>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-4">
                            @if($type->description)
                                <p class="text-sm text-primary-600 mb-4 leading-relaxed">{{ $type->description }}</p>
                            @endif

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @if($type->monthly_contribution > 0)
                                    <div class="bg-primary-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-primary-500 mb-1">Monthly</p>
                                        <p class="text-sm font-bold text-primary-800">TZS {{ number_format($type->monthly_contribution) }}</p>
                                    </div>
                                @endif

                                @if($type->min_savings > 0)
                                    <div class="bg-primary-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-primary-500 mb-1">Min Savings</p>
                                        <p class="text-sm font-bold text-primary-800">TZS {{ number_format($type->min_savings) }}</p>
                                    </div>
                                @endif

                                @if($type->max_loan_multiplier > 0)
                                    <div class="bg-primary-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-primary-500 mb-1">Loan Multiplier</p>
                                        <p class="text-sm font-bold text-primary-800">{{ $type->max_loan_multiplier }}x</p>
                                    </div>
                                @endif

                                @if($type->interest_rate_discount > 0)
                                    <div class="bg-primary-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-primary-500 mb-1">Rate Discount</p>
                                        <p class="text-sm font-bold text-primary-800">{{ $type->interest_rate_discount }}%</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Benefits --}}
                            <div class="flex flex-wrap gap-2 mt-4">
                                @if($type->can_vote)
                                    <span class="badge badge-green">
                                        <i class="fa-solid fa-check mr-1"></i> Can Vote
                                    </span>
                                @endif
                                @if($type->can_hold_office)
                                    <span class="badge badge-blue">
                                        <i class="fa-solid fa-check mr-1"></i> Can Hold Office
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Selected indicator --}}
                        <div class="px-6 py-3 bg-primary-500/0 peer-checked:bg-primary-500/5 border-t border-primary-100 flex items-center justify-between transition-all">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full border-2 border-primary-300 peer-checked:border-primary-500 peer-checked:bg-primary-500 flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                                <span class="text-xs font-semibold text-primary-600">Select this plan</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-primary-400 text-xs"></i>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('register.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2 shadow-sm hover:shadow-md">
                <i class="fa-solid fa-arrow-right"></i>
                Continue
            </button>
        </div>
    </form>
</div>
@endsection
