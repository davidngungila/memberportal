@extends('layouts.member')

@section('breadcrumb', 'My Profile › Edit')
@section('page_title', 'Edit Profile')

@php
    $displayName = $member?->full_name ?? $personalDetails?->full_name ?? $user->name ?? '';
    $displayEmail = $member?->email ?? $personalDetails?->email ?? '';
    $displayPhone = $verification?->phone ?? $member?->phone ?? $personalDetails?->phone ?? '';
    $displayAddress = $member?->residential_address ?? $personalDetails?->address ?? '';
    $displayOccupation = $member?->occupation ?? $personalDetails?->occupation ?? '';
    $displayEmployer = $member?->employer ?? $personalDetails?->employer ?? '';
@endphp

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

  <!-- Header Card -->
  <div class="glass rounded-2xl p-6 lg:p-8 border border-primary-100 dark:border-dark-border bg-gradient-to-br from-primary-50/50 to-white dark:from-primary-900/20 dark:to-transparent">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center shadow-lg">
        <i class="fa-solid fa-user-pen text-xl"></i>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-primary-900 dark:text-white">Edit Your Profile</h1>
        <p class="text-sm text-primary-600 dark:text-primary-400">Update your personal information and preferences</p>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left Column - Profile Photo -->
      <div class="lg:col-span-1">
        <div class="glass rounded-2xl p-6 border border-primary-100 dark:border-dark-border sticky top-6">
          <div class="flex flex-col items-center text-center">
            <div class="relative mb-4">
              <div class="w-32 h-32 rounded-full overflow-hidden shadow-2xl ring-4 ring-primary-100 dark:ring-primary-800/50">
                @if($user->photo)
                  <img src="{{ asset('storage/' . $user->photo) }}" alt="Profile Photo" class="w-full h-full object-cover">
                @else
                  <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-400 to-indigo-600">
                    <span class="text-5xl font-extrabold text-white">{{ strtoupper(substr($user->name ?? 'M', 0, 1)) }}</span>
                  </div>
                @endif
              </div>
              <label class="absolute bottom-2 right-2 w-10 h-10 bg-primary-600 hover:bg-primary-500 text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg transition-all hover:scale-110">
                <i class="fa-solid fa-camera text-sm"></i>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif" class="hidden">
              </label>
            </div>
            <h3 class="font-bold text-primary-900 dark:text-white">{{ $displayName ?: 'Member' }}</h3>
            <p class="text-xs text-primary-500 dark:text-primary-400 mb-4">{{ $displayEmail ?: 'No email' }}</p>

            <div class="w-full pt-4 border-t border-primary-100 dark:border-dark-border">
              <p class="text-[10px] font-semibold text-primary-500 dark:text-primary-400 mb-2">PHOTO GUIDELINES</p>
              <ul class="text-[11px] text-primary-600 dark:text-primary-400 space-y-1">
                <li>• JPG, PNG or GIF format</li>
                <li>• Maximum size: 2MB</li>
                <li>• Recommended: 400x400px</li>
              </ul>
            </div>

            @if($user->photo)
              <button type="button" onclick="document.getElementById('removePhoto').click()" class="mt-4 w-full px-4 py-2 rounded-lg border border-red-200 hover:border-red-300 dark:border-red-800/30 dark:hover:border-red-800/50 text-red-600 dark:text-red-400 text-xs font-bold transition-colors">
                <i class="fa-solid fa-trash mr-1.5"></i> Remove Photo
              </button>
              <input type="checkbox" name="remove_photo" value="1" id="removePhoto" class="hidden">
            @endif
          </div>
        </div>
      </div>

      <!-- Right Column - Form Fields -->
      <div class="lg:col-span-2">

        <!-- Personal Information Card -->
        <div class="glass rounded-2xl p-6 lg:p-8 border border-primary-100 dark:border-dark-border">
          <div class="flex items-center gap-3 mb-6 pb-4 border-b border-primary-100 dark:border-dark-border">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center">
              <i class="fa-solid fa-user"></i>
            </div>
            <div>
              <h2 class="font-bold text-primary-900 dark:text-white text-base">Personal Information</h2>
              <p class="text-xs text-primary-500 dark:text-primary-400">Basic contact details</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Full Name <span class="text-red-500">*</span></label>
              <div class="relative">
                <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                <input type="text" name="name" required value="{{ old('name', $displayName) }}"
                       class="form-input py-2.5 pl-9 pr-4 text-sm"
                       placeholder="Enter your full name">
              </div>
              @error('name')
                <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Email <span class="text-red-500">*</span></label>
              <div class="relative">
                <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                <input type="email" name="email" required value="{{ old('email', $displayEmail) }}"
                       class="form-input py-2.5 pl-9 pr-4 text-sm"
                       placeholder="your@email.com">
              </div>
              @error('email')
                <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Phone</label>
              <div class="relative">
                <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                <input type="text" name="phone" value="{{ old('phone', $displayPhone) }}"
                       class="form-input py-2.5 pl-9 pr-4 text-sm"
                       placeholder="+255 123 456 789">
              </div>
              @error('phone')
                <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Address</label>
              <div class="relative">
                <i class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                <input type="text" name="address" value="{{ old('address', $displayAddress) }}"
                       class="form-input py-2.5 pl-9 pr-4 text-sm"
                       placeholder="Your address">
              </div>
              @error('address')
                <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Occupation</label>
              <div class="relative">
                <i class="fa-solid fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                <input type="text" name="occupation" value="{{ old('occupation', $displayOccupation) }}"
                       class="form-input py-2.5 pl-9 pr-4 text-sm"
                       placeholder="Your occupation">
              </div>
              @error('occupation')
                <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Employer</label>
              <div class="relative">
                <i class="fa-solid fa-building absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                <input type="text" name="employer" value="{{ old('employer', $displayEmployer) }}"
                       class="form-input py-2.5 pl-9 pr-4 text-sm"
                       placeholder="Your employer">
              </div>
              @error('employer')
                <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        <!-- Password Change Card -->
        <div class="glass rounded-2xl p-6 lg:p-8 border border-primary-100 dark:border-dark-border">
          <div class="flex items-center gap-3 mb-6 pb-4 border-b border-primary-100 dark:border-dark-border">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center">
              <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
              <h2 class="font-bold text-primary-900 dark:text-white text-base">Security Settings</h2>
              <p class="text-xs text-primary-500 dark:text-primary-400">Change your password</p>
            </div>
          </div>

          <div class="space-y-5">
            <div>
              <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Current Password</label>
              <div class="relative">
                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                <input type="password" name="current_password"
                       class="form-input py-2.5 pl-9 pr-4 text-sm"
                       placeholder="Enter current password">
              </div>
              @error('current_password')
                <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
              @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">New Password</label>
                <div class="relative">
                  <i class="fa-solid fa-key absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                  <input type="password" name="new_password"
                         class="form-input py-2.5 pl-9 pr-4 text-sm"
                         placeholder="Min 8 characters">
                </div>
                @error('new_password')
                  <p class="mt-1.5 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                @enderror
              </div>
              <div>
                <label class="block text-xs font-bold text-primary-700 dark:text-primary-300 mb-2">Confirm New Password</label>
                <div class="relative">
                  <i class="fa-solid fa-check absolute left-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                  <input type="password" name="new_password_confirmation"
                         class="form-input py-2.5 pl-9 pr-4 text-sm"
                         placeholder="Confirm new password">
                </div>
              </div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800/30">
              <p class="text-[11px] text-blue-700 dark:text-blue-300">
                <i class="fa-solid fa-circle-info mr-1.5"></i>
                Leave password fields empty if you don't want to change your password.
              </p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <a href="{{ route('member.profile.show') }}" class="px-6 py-3 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-xs font-bold transition-all hover:scale-105">
            <i class="fa-solid fa-xmark mr-1.5"></i> Cancel
          </a>
          <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white text-xs font-bold transition-all shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40 hover:scale-105">
            <i class="fa-solid fa-check mr-1.5"></i> Save Changes
          </button>
        </div>
      </div>
    </div>
  </form>

</div>

@endsection
