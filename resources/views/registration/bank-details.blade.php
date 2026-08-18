@extends('layouts.registration')

@section('page_title', 'Bank Details')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Bank Details</h1>
        <p class="text-primary-600 text-sm">Enter your bank account information</p>
    </div>

    <form method="POST" action="{{ route('register.bank-details.store') }}" x-data="bankForm()">
        @csrf

        <div class="space-y-4 mb-6">
            <template x-for="(bank, index) in banks" :key="index">
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-primary-800" x-text="'Bank Account ' + (index + 1)"></h3>
                        <button type="button" @click="removeBank(index)" x-show="banks.length > 1" class="text-red-500 hover:text-red-700 text-xs">
                            <i class="fa-solid fa-trash"></i> Remove
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Bank Name *</label>
                            <input type="text" :name="'banks[' + index + '][bank_name]'" x-model="bank.bank_name" class="form-input" required placeholder="e.g. CRDB Bank">
                        </div>
                        <div>
                            <label class="form-label">Branch</label>
                            <input type="text" :name="'banks[' + index + '][branch]'" x-model="bank.branch" class="form-input" placeholder="e.g. Main Branch">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="form-label">Account Name *</label>
                            <input type="text" :name="'banks[' + index + '][account_name]'" x-model="bank.account_name" class="form-input" required placeholder="Account holder name">
                        </div>
                        <div>
                            <label class="form-label">Account Number *</label>
                            <input type="text" :name="'banks[' + index + '][account_number]'" x-model="bank.account_number" class="form-input" required placeholder="Account number">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" @click="addBank()" class="mb-6 inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-primary-300 text-primary-600 text-sm font-semibold hover:border-primary-500 hover:bg-primary-50 transition">
            <i class="fa-solid fa-plus"></i>
            Add Another Bank Account
        </button>

        <div class="flex items-center gap-4">
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

@push('scripts')
<script>
    function bankForm() {
        return {
            banks: @json($bankAccounts->count() > 0 ? $bankAccounts->toArray() : [['bank_name' => '', 'account_name' => '', 'account_number' => '', 'branch' => '']]),
            addBank() {
                this.banks.push({ bank_name: '', account_name: '', account_number: '', branch: '' });
            },
            removeBank(index) {
                this.banks.splice(index, 1);
            }
        }
    }
</script>
@endpush
@endsection
