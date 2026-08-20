@extends('layouts.admin')

@section('breadcrumb', 'Members › Edit Member')
@section('page_title', 'Edit Member: ' . $user->name)

@section('content')
<div x-data="memberEditForm()" class="space-y-6">
  @php
    $encryptedId = app(\App\Services\EncryptedIdService::class)->encrypt($user->id);
  @endphp
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.users.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div class="flex items-center gap-3 flex-1">
      <div class="relative">
        @if($user->member && $user->member->photo)
          <img src="{{ asset('storage/' . $user->member->photo) }}" 
               alt="{{ $user->name }}" 
               class="w-12 h-12 rounded-2xl object-cover shadow-md"
               onerror="this.src='{{ asset('images/default-avatar.png') }}'">
        @else
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center text-base font-bold shadow-md">
            {{ strtoupper(substr($user->name, 0, 1) ?? 'U') }}
          </div>
        @endif
      </div>
      <div class="flex-1 min-w-0">
        <h2 class="font-bold text-lg truncate" :class="darkMode ? 'text-white' : 'text-primary-900'">{{ $user->name }}</h2>
        <p class="text-xs mt-0.5 truncate" :class="darkMode ? 'text-primary-400' : 'text-primary-600'">{{ $user->email }}</p>
      </div>
      @if($user->status)
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
          {{ ucfirst($user->status) }}
        </span>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Form Area -->
    <div class="lg:col-span-3">
      <div class="glass p-6 rounded-2xl">
        <!-- Tabs Navigation -->
        <div class="flex flex-wrap gap-2 mb-6 border-b border-primary-100 dark:border-primary-900/50 pb-4">
          <button @click="currentTab = 1" :class="currentTab === 1 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-user text-[10px]"></i> Basic Info
          </button>
          <button @click="currentTab = 2" :class="currentTab === 2 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-address-book text-[10px]"></i> Contact
          </button>
          <button @click="currentTab = 3" :class="currentTab === 3 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-id-card text-[10px]"></i> Membership
          </button>
          <button @click="currentTab = 4" :class="currentTab === 4 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-lock text-[10px]"></i> Account
          </button>
          <button @click="currentTab = 5" :class="currentTab === 5 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-users text-[10px]"></i> Next of Kin
          </button>
          <button @click="currentTab = 6" :class="currentTab === 6 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-building-columns text-[10px]"></i> Banking
          </button>
          <button @click="currentTab = 7" :class="currentTab === 7 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-file text-[10px]"></i> Documents
          </button>
          <button @click="currentTab = 8" :class="currentTab === 8 ? 'bg-primary-600 text-white' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-colors">
            <i class="fa-solid fa-note-sticky text-[10px]"></i> Additional
          </button>
        </div>

        <!-- Tab 1: Basic Information -->
        <div x-show="currentTab === 1" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-user text-primary-500 text-xs"></i> Basic Information
          </h3>
          <form id="basicInfoForm" @submit.prevent="saveBasicInfo" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
              @if($user->member)
                <div class="md:col-span-3">
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Full Name</label>
                  <input type="text" value="{{ $user->name }}" readonly class="form-input bg-primary-50 dark:bg-primary-900/30 cursor-not-allowed">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Gender *</label>
                  <select name="gender" class="form-input">
                    <option value="">Select gender...</option>
                    <option value="male" {{ $user->member->gender === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $user->member->gender === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $user->member->gender === 'other' ? 'selected' : '' }}>Other</option>
                  </select>
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Date of Birth</label>
                  <input type="date" name="date_of_birth" value="{{ $user->member->date_of_birth ? $user->member->date_of_birth->format('Y-m-d') : '' }}" class="form-input">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">National ID (NIDA)</label>
                  <input type="text" name="national_id" value="{{ $user->member->national_id ?? '' }}" class="form-input font-mono">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Registration Date</label>
                  <input type="date" name="registration_date" value="{{ $user->member->registration_date ? $user->member->registration_date->format('Y-m-d') : '' }}" class="form-input">
                </div>
              @else
                <div class="md:col-span-3">
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Full Name *</label>
                  <input type="text" name="name" value="{{ $user->name }}" required class="form-input">
                </div>
                <div class="md:col-span-3">
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Email Address *</label>
                  <input type="email" name="email" value="{{ $user->email }}" required class="form-input">
                </div>
              @endif
            </div>
            <div class="flex justify-end">
              <button type="submit" :disabled="loading" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 disabled:bg-primary-400 disabled:cursor-not-allowed text-white text-sm font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-save" :class="loading ? 'fa-spin' : ''"></i>
                <span x-text="loading ? 'Saving...' : 'Save Changes'"></span>
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 2: Contact Information -->
        <div x-show="currentTab === 2" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-address-book text-primary-500 text-xs"></i> Contact Information
          </h3>
          <form id="contactInfoForm" @submit.prevent="saveContactInfo" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Phone Number</label>
                <input type="text" name="phone" value="{{ $user->member->phone ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Email Address</label>
                <input type="email" name="email" value="{{ $user->member->email ?? $user->email }}" class="form-input">
              </div>
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Residential Address</label>
                <textarea name="residential_address" rows="2" class="form-input">{{ $user->member->residential_address ?? '' }}</textarea>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" :disabled="loading" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 disabled:bg-primary-400 disabled:cursor-not-allowed text-white text-sm font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-save" :class="loading ? 'fa-spin' : ''"></i>
                <span x-text="loading ? 'Saving...' : 'Save Changes'"></span>
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 3: Membership Details -->
        <div x-show="currentTab === 3" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-id-card text-primary-500 text-xs"></i> Membership Details
          </h3>
          <form id="membershipDetailsForm" @submit.prevent="saveMembershipDetails" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Occupation</label>
                <input type="text" name="occupation" value="{{ $user->member->occupation ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Employer/Business</label>
                <input type="text" name="employer" value="{{ $user->member->employer ?? '' }}" class="form-input">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 4: Account Information -->
        <div x-show="currentTab === 4" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-lock text-primary-500 text-xs"></i> Account Information
          </h3>
          <form id="accountInfoForm" @submit.prevent="saveAccountInfo" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Member Number</label>
                <input type="text" value="{{ $user->membercode }}" readonly class="form-input font-mono bg-primary-50 dark:bg-primary-900/30">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">New Password</label>
                <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Re-enter new password">
              </div>
              <div class="flex items-center gap-3">
                <input type="checkbox" name="email_verified" id="email_verified" {{ $user->email_verified_at ? 'checked' : '' }} class="w-4 h-4 rounded">
                <label for="email_verified" class="text-sm text-primary-700 dark:text-primary-300">Email Verified</label>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 5: Next of Kin -->
        <div x-show="currentTab === 5" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-users text-primary-500 text-xs"></i> Next of Kin
          </h3>
          <form id="nextOfKinForm" @submit.prevent="saveNextOfKin" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Full Name</label>
                <input type="text" name="emergency_contact_name" value="{{ $user->member->emergency_contact_name ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Relationship</label>
                <input type="text" name="emergency_contact_relationship" value="{{ $user->member->emergency_contact_relationship ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Phone Number</label>
                <input type="text" name="emergency_contact_phone" value="{{ $user->member->emergency_contact_phone ?? '' }}" class="form-input">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 6: Banking & Mobile Money -->
        <div x-show="currentTab === 6" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-building-columns text-primary-500 text-xs"></i> Banking & Mobile Money
          </h3>
          <form id="bankingInfoForm" @submit.prevent="saveBankingInfo" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Bank Name</label>
                <input type="text" name="bank_name" value="{{ $user->member->bank_name ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Bank Account Number</label>
                <input type="text" name="account_number" value="{{ $user->member->account_number ?? '' }}" class="form-input font-mono">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Account Name</label>
                <input type="text" name="account_name" value="{{ $user->member->account_name ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Mobile Money Provider</label>
                <select name="mobile_money_provider" class="form-input">
                  <option value="">Select provider...</option>
                  <option value="mtn" {{ ($user->member->mobile_money_provider ?? '') === 'mtn' ? 'selected' : '' }}>MTN Mobile Money</option>
                  <option value="airtel" {{ ($user->member->mobile_money_provider ?? '') === 'airtel' ? 'selected' : '' }}>Airtel Money</option>
                  <option value="vodacom" {{ ($user->member->mobile_money_provider ?? '') === 'vodacom' ? 'selected' : '' }}>Vodacom M-Pesa</option>
                  <option value="tigopesa" {{ ($user->member->mobile_money_provider ?? '') === 'tigopesa' ? 'selected' : '' }}>Tigo Pesa</option>
                </select>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Mobile Money Number</label>
                <input type="text" name="mobile_money_number" value="{{ $user->member->mobile_money_number ?? '' }}" class="form-input font-mono">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 7: Documents -->
        <div x-show="currentTab === 7" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-file text-primary-500 text-xs"></i> Documents
          </h3>
          <form id="documentsInfoForm" @submit.prevent="saveDocumentsInfo" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Profile Photo</label>
                <input type="file" name="photo" accept="image/*" class="form-input">
                @if($user->member && $user->member->photo)
                  <p class="mt-2 text-xs text-primary-600 dark:text-primary-400">Current: {{ $user->member->photo }}</p>
                @endif
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 8: Additional Information -->
        <div x-show="currentTab === 8" x-transition class="space-y-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-note-sticky text-primary-500 text-xs"></i> Additional Information
          </h3>
          <form id="additionalInfoForm" @submit.prevent="saveAdditionalInfo" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Notes</label>
                <textarea name="notes" rows="4" class="form-input">{{ $user->member->notes ?? '' }}</textarea>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Right Sidebar Summary -->
    <div class="lg:col-span-1">
      <div class="glass p-6 rounded-2xl sticky top-6">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-4 flex items-center gap-2">
          <i class="fa-solid fa-user-circle text-primary-500"></i> Member Summary
        </h3>
        <div class="space-y-4">
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400 uppercase tracking-wider">Member Number</p>
            <p class="font-mono text-sm font-bold text-primary-900 dark:text-white">{{ $user->membercode }}</p>
          </div>
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400 uppercase tracking-wider">Name</p>
            <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $user->name }}</p>
          </div>
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400 uppercase tracking-wider">Status</p>
            <span class="inline-block px-2 py-1 rounded text-xs font-bold 
              {{ $user->status === 'active' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' : 
                 ($user->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300' : 
                 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300') }}">
              {{ ucfirst($user->status) }}
            </span>
          </div>
          @if($user->memberType)
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400 uppercase tracking-wider">Member Type</p>
            <p class="text-sm text-primary-700 dark:text-primary-300">{{ $user->memberType->name }}</p>
          </div>
          @endif
        </div>
        <div class="mt-6 pt-4 border-t border-primary-100 dark:border-primary-900/50">
          <a href="{{ route('admin.users.index') }}" class="w-full block text-center px-4 py-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-sm font-bold transition-colors">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to List
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function memberEditForm() {
  return {
    currentTab: 1,
    userId: '{{ $encryptedId }}',
    loading: false,
    
    async saveBasicInfo() {
      const form = document.getElementById('basicInfoForm');
      const formData = new FormData(form);
      this.loading = true;
      
      try {
        const response = await fetch(`{{ route('admin.users.update-basic-info', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Basic information updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          setTimeout(() => window.location.reload(), 1600);
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save basic information.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save basic information.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save basic information.'
        });
      } finally {
        this.loading = false;
      }
    },

    async saveContactInfo() {
      const form = document.getElementById('contactInfoForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch(`{{ route('admin.users.update-contact-info', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Contact information updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save contact information.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save contact information.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save contact information.'
        });
      }
    },

    async saveMembershipDetails() {
      const form = document.getElementById('membershipDetailsForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch(`{{ route('admin.users.update-membership-details', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Membership details updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save membership details.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save membership details.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save membership details.'
        });
      }
    },

    async saveAccountInfo() {
      const form = document.getElementById('accountInfoForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch(`{{ route('admin.users.update-account-info', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Account information updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save account information.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save account information.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save account information.'
        });
      }
    },

    async saveNextOfKin() {
      const form = document.getElementById('nextOfKinForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch(`{{ route('admin.users.update-next-of-kin', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Next of kin information updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save next of kin information.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save next of kin information.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save next of kin information.'
        });
      }
    },

    async saveBankingInfo() {
      const form = document.getElementById('bankingInfoForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch(`{{ route('admin.users.update-banking-info', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Banking information updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save banking information.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save banking information.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save banking information.'
        });
      }
    },

    async saveDocumentsInfo() {
      const form = document.getElementById('documentsInfoForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch(`{{ route('admin.users.update-documents-info', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Documents updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save documents.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save documents.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save documents.'
        });
      }
    },

    async saveAdditionalInfo() {
      const form = document.getElementById('additionalInfoForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch(`{{ route('admin.users.update-additional-info', $encryptedId) }}`, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Additional information updated successfully.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n');
            Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              text: errorMessages || data.message || 'Failed to save additional information.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to save additional information.'
            });
          }
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save additional information.'
        });
      }
    }
  };
}
</script>

@endsection
