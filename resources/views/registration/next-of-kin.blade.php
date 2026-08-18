@extends('layouts.registration')

@section('page_title', 'Next of Kin')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Next of Kin</h1>
        <p class="text-primary-600 text-sm">Provide your next of kin details</p>
    </div>

    <form method="POST" action="{{ route('register.next-of-kin.store') }}" x-data="kinForm()">
        @csrf

        <div class="space-y-4 mb-6">
            <template x-for="(kin, index) in kinList" :key="index">
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-primary-800" x-text="'Next of Kin ' + (index + 1)"></h3>
                        <button type="button" @click="removeKin(index)" x-show="kinList.length > 1" class="text-red-500 hover:text-red-700 text-xs">
                            <i class="fa-solid fa-trash"></i> Remove
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Full Name *</label>
                            <input type="text" :name="'kin[' + index + '][full_name]'" x-model="kin.full_name" class="form-input" required placeholder="Full name">
                        </div>
                        <div>
                            <label class="form-label">Relationship *</label>
                            <select :name="'kin[' + index + '][relationship]'" x-model="kin.relationship" class="form-input" required>
                                <option value="">Select</option>
                                <option value="spouse">Spouse</option>
                                <option value="parent">Parent</option>
                                <option value="child">Child</option>
                                <option value="sibling">Sibling</option>
                                <option value="friend">Friend</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="form-label">Phone Number *</label>
                            <input type="text" :name="'kin[' + index + '][phone]'" x-model="kin.phone" class="form-input" required placeholder="+255 7XX XXX XXX">
                        </div>
                        <div>
                            <label class="form-label">Alternative Phone</label>
                            <input type="text" :name="'kin[' + index + '][alternative_phone]'" x-model="kin.alternative_phone" class="form-input" placeholder="+255 7XX XXX XXX">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Address</label>
                        <input type="text" :name="'kin[' + index + '][address]'" x-model="kin.address" class="form-input" placeholder="Address">
                    </div>
                </div>
            </template>
        </div>

        <button type="button" @click="addKin()" class="mb-6 inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-primary-300 text-primary-600 text-sm font-semibold hover:border-primary-500 hover:bg-primary-50 transition">
            <i class="fa-solid fa-plus"></i>
            Add Another Next of Kin
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
    function kinForm() {
        return {
            kinList: @json($nextOfKin->count() > 0 ? $nextOfKin->toArray() : [['full_name' => '', 'relationship' => '', 'phone' => '', 'alternative_phone' => '', 'address' => '']]),
            addKin() {
                this.kinList.push({ full_name: '', relationship: '', phone: '', alternative_phone: '', address: '' });
            },
            removeKin(index) {
                this.kinList.splice(index, 1);
            }
        }
    }
</script>
@endpush
@endsection
