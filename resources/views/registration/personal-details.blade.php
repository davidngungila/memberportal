@extends('layouts.registration')

@section('page_title', 'Personal Details')

@section('content')
<div class="animate-fade-in">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                <i class="fa-solid fa-user text-primary-600"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-primary-900">Personal Details</h1>
                <p class="text-primary-500 text-sm">Enter your personal information to continue</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('register.personal-details.store') }}">
        @csrf

        {{-- Section: Basic Info --}}
        <div class="card p-6 mb-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg bg-primary-500 text-white flex items-center justify-center text-xs font-bold">1</div>
                <h2 class="text-sm font-bold text-primary-800 uppercase tracking-wider">Basic Information</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $personalDetail->first_name ?? '') }}" class="form-input" required>
                </div>
                <div>
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name', $personalDetail->middle_name ?? '') }}" class="form-input">
                </div>
                <div>
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $personalDetail->last_name ?? '') }}" class="form-input" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', isset($personalDetail->date_of_birth) ? $personalDetail->date_of_birth->format('Y-m-d') : '') }}" class="form-input" required>
                </div>
                <div>
                    <label for="gender" class="form-label">Gender *</label>
                    <select name="gender" id="gender" class="form-input" required>
                        <option value="">Select</option>
                        <option value="male" {{ old('gender', $personalDetail->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $personalDetail->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $personalDetail->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="nationality" class="form-label">Nationality</label>
                    <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $personalDetail->nationality ?? '') }}" class="form-input" placeholder="e.g. Tanzanian">
                </div>
                <div>
                    <label for="national_id_number" class="form-label">National ID / NIDA Number</label>
                    <input type="text" name="national_id_number" id="national_id_number" value="{{ old('national_id_number', $personalDetail->national_id_number ?? '') }}" class="form-input" placeholder="e.g. 1234567890123456">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="marital_status" class="form-label">Marital Status</label>
                    <select name="marital_status" id="marital_status" class="form-input">
                        <option value="">Select</option>
                        <option value="single" {{ old('marital_status', $personalDetail->marital_status ?? '') === 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('marital_status', $personalDetail->marital_status ?? '') === 'married' ? 'selected' : '' }}>Married</option>
                        <option value="divorced" {{ old('marital_status', $personalDetail->marital_status ?? '') === 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed" {{ old('marital_status', $personalDetail->marital_status ?? '') === 'widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                </div>
                <div>
                    <label for="occupation" class="form-label">Occupation</label>
                    <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $personalDetail->occupation ?? '') }}" class="form-input" placeholder="e.g. Teacher">
                </div>
            </div>
            <div class="mt-4">
                <label for="employer" class="form-label">Employer</label>
                <input type="text" name="employer" id="employer" value="{{ old('employer', $personalDetail->employer ?? '') }}" class="form-input" placeholder="e.g. FEEDTAN Cooperative">
            </div>
        </div>

        {{-- Section: Address --}}
        <div class="card p-6 mb-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg bg-primary-500 text-white flex items-center justify-center text-xs font-bold">2</div>
                <h2 class="text-sm font-bold text-primary-800 uppercase tracking-wider">Address</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="region" class="form-label">Region</label>
                    <input type="text" name="region" id="region" value="{{ old('region', $personalDetail->region ?? '') }}" class="form-input" placeholder="e.g. Dar es Salaam">
                </div>
                <div>
                    <label for="district" class="form-label">District</label>
                    <input type="text" name="district" id="district" value="{{ old('district', $personalDetail->district ?? '') }}" class="form-input" placeholder="e.g. Kinondoni">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="ward" class="form-label">Ward</label>
                    <input type="text" name="ward" id="ward" value="{{ old('ward', $personalDetail->ward ?? '') }}" class="form-input" placeholder="e.g. Mwananyamala">
                </div>
                <div>
                    <label for="street" class="form-label">Street</label>
                    <input type="text" name="street" id="street" value="{{ old('street', $personalDetail->street ?? '') }}" class="form-input" placeholder="e.g. Mwananyamala B">
                </div>
            </div>
            <div class="mt-4">
                <label for="address" class="form-label">Full Address</label>
                <input type="text" name="address" id="address" value="{{ old('address', $personalDetail->address ?? '') }}" class="form-input" placeholder="P.O. Box or street address">
            </div>
        </div>

        {{-- Section: Contact --}}
        <div class="card p-6 mb-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg bg-primary-500 text-white flex items-center justify-center text-xs font-bold">3</div>
                <h2 class="text-sm font-bold text-primary-800 uppercase tracking-wider">Contact Information</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $personalDetail->email ?? auth()->user()->email) }}" class="form-input">
                </div>
                <div>
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $personalDetail->phone ?? auth()->user()->phone) }}" class="form-input">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('register.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2 shadow-sm hover:shadow-md">
                <i class="fa-solid fa-arrow-right"></i>
                Save & Continue
            </button>
        </div>
    </form>
</div>
@endsection
