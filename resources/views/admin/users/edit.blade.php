@extends('layouts.admin')

@section('breadcrumb', 'Members \u203A Edit Member')
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
        @if($user->memberProfile && $user->memberProfile->passport_photo)
          <img src="{{ asset('storage/' . $user->memberProfile->passport_photo) }}" 
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
      @if($user->memberProfile)
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
          {{ $user->memberProfile->status }}
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
              @if($user->memberProfile)
                <div class="md:col-span-3">
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Profile Photo</label>
                  <div class="flex items-center gap-4">
                    @if($user->memberProfile->passport_photo)
                      <img src="{{ asset('storage/' . $user->memberProfile->passport_photo) }}" 
                           alt="{{ $user->name }}" 
                           class="w-20 h-20 rounded-xl object-cover shadow-md"
                           onerror="this.style.display='none'">
                    @endif
                    <input type="file" name="profile_photo" accept="image/*" class="form-input flex-1">
                  </div>
                </div>
                <div class="md:col-span-3">
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Member Type *</label>
                  <select name="member_type_id" class="form-input">
                    <option value="">Select member type...</option>
                    @foreach($memberTypes as $type)
                      <option value="{{ $type->id }}" {{ $user->member_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }} - {{ $type->code }}</option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">First Name *</label>
                  <input type="text" name="first_name" value="{{ $user->memberProfile->first_name }}" class="form-input">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Middle Name</label>
                  <input type="text" name="middle_name" value="{{ $user->memberProfile->middle_name }}" class="form-input">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Last Name *</label>
                  <input type="text" name="last_name" value="{{ $user->memberProfile->last_name }}" class="form-input">
                </div>
                <div class="md:col-span-3">
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Email Address *</label>
                  <input type="email" name="email_address" value="{{ $user->email }}" class="form-input">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Gender *</label>
                  <select name="gender" class="form-input">
                    <option value="">Select gender...</option>
                    <option value="male" {{ $user->memberProfile->gender === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $user->memberProfile->gender === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $user->memberProfile->gender === 'other' ? 'selected' : '' }}>Other</option>
                  </select>
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Date of Birth</label>
                  <input type="date" name="date_of_birth" value="{{ $user->memberProfile->date_of_birth ? $user->memberProfile->date_of_birth->format('Y-m-d') : '' }}" class="form-input">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">National ID (NIDA)</label>
                  <input type="text" name="national_id" value="{{ $user->memberProfile->national_id }}" class="form-input font-mono">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Passport/Driving License</label>
                  <input type="text" name="passport_driving_license" value="{{ $user->memberProfile->passport_driving_license }}" class="form-input">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Registration Date</label>
                  <input type="date" name="registration_date" value="{{ $user->memberProfile->registration_date ? $user->memberProfile->registration_date->format('Y-m-d') : '' }}" class="form-input">
                </div>
                <div>
                  <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Status</label>
                  <select name="status" required class="form-input">
                    <option value="active" {{ $user->memberProfile->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ $user->memberProfile->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ $user->memberProfile->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                  </select>
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
                <input type="text" name="phone_number" value="{{ $user->memberProfile->phone_number ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Alternative Phone</label>
                <input type="text" name="alternative_phone" value="{{ $user->memberProfile->alternative_phone ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Region</label>
                <input type="text" name="region" value="{{ $user->memberProfile->region ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">District</label>
                <input type="text" name="district" value="{{ $user->memberProfile->district ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Ward</label>
                <input type="text" name="ward" value="{{ $user->memberProfile->ward ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Street/Village</label>
                <input type="text" name="street_village" value="{{ $user->memberProfile->street_village ?? '' }}" class="form-input">
              </div>
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Physical Address</label>
                <textarea name="physical_address" rows="2" class="form-input">{{ $user->memberProfile->physical_address ?? '' }}</textarea>
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
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Branch</label>
                <select name="branch_id" class="form-input">
                  <option value="">Select branch...</option>
                  <option value="1" {{ $user->memberProfile->branch_id == 1 ? 'selected' : '' }}>Main Branch</option>
                  <option value="2" {{ $user->memberProfile->branch_id == 2 ? 'selected' : '' }}>Branch A</option>
                  <option value="3" {{ $user->memberProfile->branch_id == 3 ? 'selected' : '' }}>Branch B</option>
                </select>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Membership Category</label>
                <input type="text" name="membership_category" value="{{ $user->memberProfile->membership_category ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Occupation</label>
                <input type="text" name="occupation" value="{{ $user->memberProfile->occupation ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Employer/Business</label>
                <input type="text" name="employer_business" value="{{ $user->memberProfile->employer_business ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Monthly Income</label>
                <input type="number" name="monthly_income" value="{{ $user->memberProfile->monthly_income ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Introduced By</label>
                <input type="text" name="introduced_by" value="{{ $user->memberProfile->introduced_by ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Joining Fee</label>
                <input type="number" name="joining_fee" value="{{ $user->memberProfile->joining_fee ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Shares Purchased</label>
                <input type="number" name="shares_purchased" value="{{ $user->memberProfile->shares_purchased ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Initial Savings Deposit</label>
                <input type="number" name="initial_savings_deposit" value="{{ $user->memberProfile->initial_savings_deposit ?? '' }}" class="form-input">
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
                <input type="text" name="kin_full_name" value="{{ $user->memberProfile->kin_full_name ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Relationship</label>
                <input type="text" name="kin_relationship" value="{{ $user->memberProfile->kin_relationship ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Phone Number</label>
                <input type="text" name="kin_phone_number" value="{{ $user->memberProfile->kin_phone_number ?? '' }}" class="form-input">
              </div>
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Address</label>
                <textarea name="kin_address" rows="2" class="form-input">{{ $user->memberProfile->kin_address ?? '' }}</textarea>
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
                <input type="text" name="bank_name" value="{{ $user->memberProfile->bank_name ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Bank Account Number</label>
                <input type="text" name="bank_account_number" value="{{ $user->memberProfile->bank_account_number ?? '' }}" class="form-input font-mono">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Account Name</label>
                <input type="text" name="account_name" value="{{ $user->memberProfile->account_name ?? '' }}" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Mobile Money Network</label>
                <select name="mobile_money_network" class="form-input">
                  <option value="">Select network...</option>
                  <option value="mtn" {{ $user->memberProfile->mobile_money_network === 'mtn' ? 'selected' : '' }}>MTN Mobile Money</option>
                  <option value="airtel" {{ $user->memberProfile->mobile_money_network === 'airtel' ? 'selected' : '' }}>Airtel Money</option>
                  <option value="vodacom" {{ $user->memberProfile->mobile_money_network === 'vodacom' ? 'selected' : '' }}>Vodacom M-Pesa</option>
                  <option value="tigopesa" {{ $user->memberProfile->mobile_money_network === 'tigopesa' ? 'selected' : '' }}>Tigo Pesa</option>
                </select>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Mobile Wallet Number</label>
                <input type="text" name="mobile_wallet_number" value="{{ $user->memberProfile->mobile_wallet_number ?? '' }}" class="form-input font-mono">
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
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Passport Photo</label>
                <input type="file" name="passport_photo" accept="image/*" class="form-input">
                @if($user->memberProfile && $user->memberProfile->passport_photo)
                  <p class="mt-2 text-xs text-primary-600 dark:text-primary-400">Current: {{ $user->memberProfile->passport_photo }}</p>
                @endif
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">National ID Copy</label>
                <input type="file" name="national_id_copy" class="form-input">
                @if($user->memberProfile && $user->memberProfile->national_id_copy)
                  <p class="mt-2 text-xs text-primary-600 dark:text-primary-400">Current: {{ $user->memberProfile->national_id_copy }}</p>
                @endif
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Signature</label>
                <input type="file" name="signature" accept="image/*" class="form-input">
                @if($user->memberProfile && $user->memberProfile->signature)
                  <p class="mt-2 text-xs text-primary-600 dark:text-primary-400">Current: {{ $user->memberProfile->signature }}</p>
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
                <textarea name="notes" rows="4" class="form-input">{{ $user->memberProfile->notes ?? '' }}</textarea>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Tags (comma separated)</label>
                <input type="text" name="tags" value="{{ $user->memberProfile->tags ? implode(', ', json_decode($user->memberProfile->tags, true)) : '' }}" class="form-input" placeholder="e.g. VIP, Corporate">
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
          @if($user->memberProfile)
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400 uppercase tracking-wider">Status</p>
            <span class="inline-block px-2 py-1 rounded text-xs font-bold 
              {{ $user->memberProfile->status === 'active' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' : 
                 ($user->memberProfile->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300' : 
                 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300') }}">
              {{ ucfirst($user->memberProfile->status) }}
            </span>
          </div>
          @if($user->memberType)
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400 uppercase tracking-wider">Member Type</p>
            <p class="text-sm text-primary-700 dark:text-primary-300">{{ $user->memberType->name }}</p>
          </div>
          @endif
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
          // Reload page to show updated profile photo
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

