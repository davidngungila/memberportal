@extends('layouts.registration')

@section('page_title', 'Referral')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Referral Information</h1>
        <p class="text-primary-600 text-sm">Were you referred by an existing member?</p>
    </div>

    <form method="POST" action="{{ route('register.referral.store') }}" x-data="referralForm()">
        @csrf

        <div class="card p-6 space-y-4">
            <div>
                <label class="form-label">Were you referred by an existing member?</label>
                <div class="flex items-center gap-4 mt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="was_referred" value="1" x-model="wasReferred" class="text-primary-500">
                        <span class="text-sm text-primary-700">Yes</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="was_referred" value="0" x-model="wasReferred" class="text-primary-500" checked>
                        <span class="text-sm text-primary-700">No</span>
                    </label>
                </div>
            </div>

            <div x-show="wasReferred === '1' || wasReferred === 1" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="referee_membercode" class="form-label">Member Code</label>
                        <input type="text" name="referee_membercode" id="referee_membercode" x-model="membercode" @input.debounce.500ms="validateMembercode()" class="form-input" placeholder="e.g. SCH8">
                    </div>
                    <div>
                        <label class="form-label">Member Name</label>
                        <div class="form-input bg-gray-50 flex items-center gap-2">
                            <template x-if="loading">
                                <i class="fa-solid fa-spinner fa-spin text-primary-400"></i>
                            </template>
                            <template x-if="!loading && refereeName">
                                <span class="text-primary-700" x-text="refereeName"></span>
                            </template>
                            <template x-if="!loading && !refereeName">
                                <span class="text-primary-400 text-xs">Enter member code to search</span>
                            </template>
                        </div>
                    </div>
                </div>
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

@push('scripts')
<script>
    function referralForm() {
        return {
            wasReferred: @json($referral->was_referred ?? '0'),
            membercode: '{{ $referral->referee_membercode ?? '' }}',
            refereeName: '{{ $referral->referee_name ?? '' }}',
            loading: false,
            async validateMembercode() {
                if (!this.membercode) {
                    this.refereeName = '';
                    return;
                }
                this.loading = true;
                try {
                    const response = await fetch('{{ route("register.validate-membercode") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ membercode: this.membercode }),
                    });
                    const data = await response.json();
                    this.refereeName = data.valid ? data.name : '';
                } catch (e) {
                    this.refereeName = '';
                }
                this.loading = false;
            }
        }
    }
</script>
@endpush
@endsection
