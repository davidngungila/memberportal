@extends('layouts.registration')

@section('page_title', 'Saving Plan')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Saving Plan</h1>
        <p class="text-primary-600 text-sm">Select your preferred saving plan</p>
    </div>

    <form method="POST" action="{{ route('register.saving-plan.store') }}">
        @csrf

        <div class="card p-6 space-y-4">
            <div>
                <label for="plan_name" class="form-label">Select Saving Plan *</label>
                <select name="plan_name" id="plan_name" class="form-input" required>
                    <option value="">Choose a plan</option>
                    <option value="Basic Savings" {{ old('plan_name', $savingPlan->plan_name ?? '') === 'Basic Savings' ? 'selected' : '' }}>Basic Savings - Minimum Monthly: TZS 20,000</option>
                    <option value="Standard Savings" {{ old('plan_name', $savingPlan->plan_name ?? '') === 'Standard Savings' ? 'selected' : '' }}>Standard Savings - Minimum Monthly: TZS 50,000</option>
                    <option value="Premium Savings" {{ old('plan_name', $savingPlan->plan_name ?? '') === 'Premium Savings' ? 'selected' : '' }}>Premium Savings - Minimum Monthly: TZS 100,000</option>
                </select>
            </div>

            <div>
                <label for="frequency" class="form-label">Saving Frequency *</label>
                <select name="frequency" id="frequency" class="form-input" required>
                    <option value="">Select frequency</option>
                    <option value="monthly" {{ old('frequency', $savingPlan->frequency ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="weekly" {{ old('frequency', $savingPlan->frequency ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="quarterly" {{ old('frequency', $savingPlan->frequency ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                </select>
            </div>

            <div>
                <label for="target_amount" class="form-label">Target Amount (TZS) *</label>
                <input type="number" name="target_amount" id="target_amount" value="{{ old('target_amount', $savingPlan->target_amount ?? '') }}" class="form-input" required min="0" step="1000" placeholder="e.g. 500000">
            </div>

            <div>
                <label for="periodic_amount" class="form-label">Periodic Amount (TZS)</label>
                <input type="number" name="periodic_amount" id="periodic_amount" value="{{ old('periodic_amount', $savingPlan->periodic_amount ?? '') }}" class="form-input" min="0" step="1000" placeholder="e.g. 50000">
            </div>

            <div>
                <label for="expected_saving_date" class="form-label">Expected Saving Date</label>
                <input type="date" name="expected_saving_date" id="expected_saving_date" value="{{ old('expected_saving_date', $savingPlan->expected_saving_date?->format('Y-m-d') ?? '') }}" class="form-input">
            </div>
        </div>

        <div class="mt-6 flex items-center gap-4">
            <a href="{{ route('register.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
                Back
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i>
                Save & Continue
            </button>
        </div>
    </form>
</div>
@endsection
