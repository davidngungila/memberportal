@extends('layouts.registration')

@section('page_title', 'Select Membership Type')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Select Membership Type</h1>
        <p class="text-primary-600 text-sm">Choose the membership type that suits you</p>
    </div>

    <form method="POST" action="{{ route('register.membership-type.store') }}">
        @csrf

        <div class="space-y-4 mb-6">
            @foreach($membershipTypes as $type)
                <label class="card p-5 cursor-pointer hover:border-primary-400 transition {{ old('membership_type_id') == $type->id ? 'border-primary-500 bg-primary-50' : '' }}">
                    <div class="flex items-start gap-4">
                        <input type="radio" name="membership_type_id" value="{{ $type->id }}" class="mt-1" {{ old('membership_type_id') == $type->id ? 'checked' : '' }} required>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-primary-800">{{ $type->name }}</h3>
                                <span class="text-sm font-bold text-primary-600">TZS {{ number_format($type->registration_fee) }}</span>
                            </div>
                            @if($type->description)
                                <p class="text-xs text-primary-600 mt-1">{{ $type->description }}</p>
                            @endif
                            <div class="flex items-center gap-4 mt-2 text-xs text-primary-500">
                                <span>Code: {{ $type->code }}</span>
                                @if($type->monthly_contribution > 0)
                                    <span>Monthly: TZS {{ number_format($type->monthly_contribution) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        <button type="submit" class="px-6 py-3 rounded-xl bg-primary-600 text-white font-semibold text-sm hover:bg-primary-700 transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-right"></i>
            Continue
        </button>
    </form>
</div>
@endsection
