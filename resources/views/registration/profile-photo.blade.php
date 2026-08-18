@extends('layouts.registration')

@section('page_title', 'Profile Photo')

@section('content')
<div class="animate-fade-in max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary-900">Profile Photo</h1>
        <p class="text-primary-600 text-sm">Upload a passport-style photo</p>
    </div>

    <form method="POST" action="{{ route('register.profile-photo.upload') }}" enctype="multipart/form-data" x-data="photoUpload()">
        @csrf

        <div class="card p-6">
            <div class="border-2 border-dashed border-primary-200 rounded-xl p-8 text-center hover:border-primary-400 transition" @click="$refs.fileInput.click()" @dragover.prevent="dragging = true" @dragleave="dragging = false" @drop.prevent="dragging = false; handleDrop($event)" :class="{ 'border-primary-500 bg-primary-50': dragging }">
                @if($documents->where('document_type', 'passport_photo')->first())
                    @php $photo = $documents->where('document_type', 'passport_photo')->first(); @endphp
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $photo->file_path) }}" alt="Passport Photo" class="w-32 h-32 rounded-xl object-cover mx-auto border-2 border-primary-200">
                    </div>
                    <p class="text-sm text-primary-700 font-semibold">Current photo uploaded</p>
                    <p class="text-xs text-primary-500 mt-1">Click to upload a new photo</p>
                @else
                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-primary-300 mb-3"></i>
                    <p class="text-sm text-primary-700 font-semibold">Click to upload or drag and drop</p>
                    <p class="text-xs text-primary-500 mt-1">JPG, JPEG, PNG up to 2MB</p>
                @endif
            </div>
            <input type="file" name="passport_photo" x-ref="fileInput" accept="image/jpeg,image/png" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0])">

            <div x-show="preview" class="mt-4 text-center">
                <img :src="preview" class="w-32 h-32 rounded-xl object-cover mx-auto border-2 border-primary-200">
                <p class="text-xs text-primary-500 mt-2">New photo preview</p>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-4">
            <a href="{{ route('register.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50 transition">
                Back
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i>
                Upload & Continue
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function photoUpload() {
        return {
            preview: null,
            dragging: false,
            handleDrop(e) {
                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    this.preview = URL.createObjectURL(file);
                }
            }
        }
    }
</script>
@endpush
@endsection
