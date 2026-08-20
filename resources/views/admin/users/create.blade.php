@extends('layouts.admin')

@section('breadcrumb', 'Members \u203A Create Member')
@section('page_title', 'Create New Member')

@section('content')
<div x-data="memberCreateForm()" class="space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.users.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-sm text-primary-600 dark:text-primary-400">
        Create a new member with comprehensive information
      </p>
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
              <div class="md:col-span-3">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Member Type *</label>
                <select name="member_type_id" required class="form-input">
                  <option value="">Select member type...</option>
                  @foreach($memberTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }} - {{ $type->code }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">First Name *</label>
                <input type="text" name="first_name" required class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Middle Name</label>
                <input type="text" name="middle_name" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Last Name *</label>
                <input type="text" name="last_name" required class="form-input">
              </div>
              <div class="md:col-span-3">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Email Address *</label>
                <input type="email" name="email_address" required class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Gender *</label>
                <select name="gender" required class="form-input">
                  <option value="">Select gender...</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">National ID (NIDA)</label>
                <input type="text" name="national_id" class="form-input font-mono">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Passport/Driving License</label>
                <input type="text" name="passport_driving_license" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Registration Date *</label>
                <input type="date" name="registration_date" required class="form-input" value="{{ now()->format('Y-m-d') }}">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Status *</label>
                <select name="status" required class="form-input">
                  <option value="pending">Pending</option>
                  <option value="active">Active</option>
                  <option value="suspended">Suspended</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Continue
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Phone Number</label>
                <input type="text" name="phone_number" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Alternative Phone</label>
                <input type="text" name="alternative_phone" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Region</label>
                <input type="text" name="region" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">District</label>
                <input type="text" name="district" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Ward</label>
                <input type="text" name="ward" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Street/Village</label>
                <input type="text" name="street_village" class="form-input">
              </div>
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Physical Address</label>
                <textarea name="physical_address" rows="2" class="form-input"></textarea>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Continue
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Branch</label>
                <select name="branch_id" class="form-input">
                  <option value="">Select branch...</option>
                  <option value="1">Main Branch</option>
                  <option value="2">Branch A</option>
                  <option value="3">Branch B</option>
                </select>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Membership Category</label>
                <input type="text" name="membership_category" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Occupation</label>
                <input type="text" name="occupation" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Employer/Business</label>
                <input type="text" name="employer_business" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Monthly Income (TSh)</label>
                <input type="number" name="monthly_income" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Introduced By</label>
                <input type="text" name="introduced_by" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Joining Fee (TSh) *</label>
                <input type="number" name="joining_fee" required class="form-input" value="0">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Shares Purchased (TSh) *</label>
                <input type="number" name="shares_purchased" required class="form-input" value="0">
              </div>
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Initial Savings Deposit (TSh) *</label>
                <input type="number" name="initial_savings_deposit" required class="form-input" value="0">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Continue
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Leave blank to use member number">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">New Password</label>
                <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current password">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Re-enter new password">
              </div>
              <div class="flex items-center gap-3">
                <input type="checkbox" name="email_verified" id="email_verified" class="w-4 h-4 rounded">
                <label for="email_verified" class="text-sm text-primary-700 dark:text-primary-300">Email Verified</label>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Continue
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Full Name</label>
                <input type="text" name="kin_full_name" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Relationship</label>
                <input type="text" name="kin_relationship" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Phone Number</label>
                <input type="text" name="kin_phone_number" class="form-input">
              </div>
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Address</label>
                <textarea name="kin_address" rows="2" class="form-input"></textarea>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Continue
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Bank Name</label>
                <input type="text" name="bank_name" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Bank Account Number</label>
                <input type="text" name="bank_account_number" class="form-input font-mono">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Account Name</label>
                <input type="text" name="account_name" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Mobile Money Network</label>
                <select name="mobile_money_network" class="form-input">
                  <option value="">Select network...</option>
                  <option value="mtn">MTN Mobile Money</option>
                  <option value="airtel">Airtel Money</option>
                  <option value="vodacom">Vodacom M-Pesa</option>
                  <option value="tigopesa">Tigo Pesa</option>
                </select>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Mobile Wallet Number</label>
                <input type="text" name="mobile_wallet_number" class="form-input font-mono">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Continue
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Passport Photo</label>
                <input type="file" name="passport_photo" accept="image/*" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">National ID Copy</label>
                <input type="file" name="national_id_copy" class="form-input">
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Signature</label>
                <input type="file" name="signature" accept="image/*" class="form-input">
              </div>
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Other Attachments</label>
                <input type="file" name="other_attachments[]" multiple class="form-input">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Continue
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="md:col-span-2">
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Notes</label>
                <textarea name="notes" rows="4" class="form-input"></textarea>
              </div>
              <div>
                <label class="form-label uppercase tracking-wider text-primary-700 dark:text-primary-300">Tags (comma separated)</label>
                <input type="text" name="tags" class="form-input" placeholder="e.g. VIP, Corporate">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all">
                <i class="fa-solid fa-save mr-1.5"></i> Save & Complete
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
          <i class="fa-solid fa-clipboard-list text-primary-500 text-xs"></i> Summary
        </h3>
        <div class="space-y-4">
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400">Member Number</p>
            <p x-text="summary.membercode || 'Auto-generated'" class="text-sm font-mono font-semibold text-primary-900 dark:text-white">Auto-generated</p>
          </div>
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400">Registration Date</p>
            <p x-text="summary.registration_date || '—'" class="text-sm font-semibold text-primary-900 dark:text-white">—</p>
          </div>
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400">Member Type</p>
            <p x-text="summary.member_type || '—'" class="text-sm font-semibold text-primary-900 dark:text-white">—</p>
          </div>
          <div>
            <p class="text-xs text-primary-600 dark:text-primary-400">Status</p>
            <p x-text="summary.status || '—'" class="text-sm font-semibold text-primary-900 dark:text-white">—</p>
          </div>
          <div class="pt-4 border-t border-primary-100 dark:border-primary-900/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-2">Progress</p>
            <div class="w-full bg-primary-100 dark:bg-primary-900/40 rounded-full h-2">
              <div class="bg-primary-600 h-2 rounded-full transition-all" :style="'width: ' + progress + '%'"></div>
            </div>
            <p class="text-xs text-primary-600 dark:text-primary-400 mt-1" x-text="progress + '% completed'"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function memberCreateForm() {
  return {
    currentTab: 1,
    userId: null,
    summary: {
      membercode: null,
      registration_date: null,
      member_type: null,
      status: null
    },
    progress: 0,
    
    async saveBasicInfo() {
      const form = document.getElementById('basicInfoForm');
      const formData = new FormData(form);
      
      try {
        const response = await fetch('{{ route('admin.users.store-basic-info') }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const contentType = response.headers.get('content-type');
        
        if (!response.ok) {
          if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            let errorMessage = 'Failed to save basic information.';
            if (data.errors) {
              const errorMessages = Object.values(data.errors).flat();
              errorMessage = errorMessages.join('\n');
            } else if (data.message) {
              errorMessage = data.message;
            }
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorMessage
            });
          } else {
            const text = await response.text();
            console.error('Server returned HTML instead of JSON:', text);
            Swal.fire({
              icon: 'error',
              title: 'Server Error',
              text: 'Please check the form for validation errors.'
            });
          }
          return;
        }
        
        const data = await response.json();
        
        if (data.success) {
          this.userId = data.user_id;
          this.summary.membercode = 'MB' + new Date().toISOString().slice(2,10).replace(/-/g,'') + Math.floor(Math.random() * 10000).toString().padStart(4, '0');
          this.summary.registration_date = formData.get('registration_date');
          const memberTypeSelect = form.querySelector('[name="member_type_id"]');
          this.summary.member_type = memberTypeSelect.options[memberTypeSelect.selectedIndex].text;
          this.summary.status = formData.get('status');
          this.progress = 12.5;
          
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Basic information saved successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          
          this.currentTab = 2;
        } else {
          let errorMessage = 'Failed to save basic information.';
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat();
            errorMessage = errorMessages.join('\n');
          } else if (data.message) {
            errorMessage = data.message;
          }
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage
          });
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to save basic information.'
        });
      }
    },
    
    async saveContactInfo() {
      const form = document.getElementById('contactInfoForm');
      const formData = new FormData(form);
      formData.append('_method', 'PUT');
      
      console.log('Saving contact info for user ID:', this.userId);
      console.log('Form data:', Object.fromEntries(formData));
      
      try {
        const response = await fetch(`{{ route('admin.users.store-contact-info', ':userId') }}`.replace(':userId', this.userId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        const contentType = response.headers.get('content-type');
        console.log('Content type:', contentType);
        
        if (!response.ok) {
          if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            console.log('Error data:', data);
            let errorMessage = 'Failed to save contact information.';
            if (data.errors) {
              const errorMessages = Object.values(data.errors).flat();
              errorMessage = errorMessages.join('\n');
            } else if (data.message) {
              errorMessage = data.message;
            }
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorMessage
            });
          } else {
            const text = await response.text();
            console.error('Server returned HTML instead of JSON:', text);
            Swal.fire({
              icon: 'error',
              title: 'Server Error',
              text: 'Please check the form for validation errors.'
            });
          }
          return;
        }
        
        const data = await response.json();
        console.log('Success data:', data);
        
        if (data.success) {
          this.progress = 25;
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Contact information saved successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          this.currentTab = 3;
        } else {
          let errorMessage = 'Failed to save contact information.';
          if (data.errors) {
            const errorMessages = Object.values(data.errors).flat();
            errorMessage = errorMessages.join('\n');
          } else if (data.message) {
            errorMessage = data.message;
          }
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage
          });
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
      formData.append('_method', 'PUT');
      
      try {
        const response = await fetch(`{{ route('admin.users.store-membership-details', ':userId') }}`.replace(':userId', this.userId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.progress = 37.5;
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Membership details saved successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          this.currentTab = 4;
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save membership details.'
        });
      }
    },
    
    async saveAccountInfo() {
      const form = document.getElementById('accountInfoForm');
      const formData = new FormData(form);
      formData.append('_method', 'PUT');
      
      try {
        const response = await fetch(`{{ route('admin.users.store-account-info', ':userId') }}`.replace(':userId', this.userId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.progress = 50;
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Account information saved successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          this.currentTab = 5;
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save account information.'
        });
      }
    },
    
    async saveNextOfKin() {
      const form = document.getElementById('nextOfKinForm');
      const formData = new FormData(form);
      formData.append('_method', 'PUT');
      
      try {
        const response = await fetch(`{{ route('admin.users.store-next-of-kin', ':userId') }}`.replace(':userId', this.userId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.progress = 62.5;
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Next of kin information saved successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          this.currentTab = 6;
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save next of kin information.'
        });
      }
    },
    
    async saveBankingInfo() {
      const form = document.getElementById('bankingInfoForm');
      const formData = new FormData(form);
      formData.append('_method', 'PUT');
      
      try {
        const response = await fetch(`{{ route('admin.users.store-banking-info', ':userId') }}`.replace(':userId', this.userId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.progress = 75;
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Banking information saved successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          this.currentTab = 7;
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save banking information.'
        });
      }
    },
    
    async saveDocumentsInfo() {
      const form = document.getElementById('documentsInfoForm');
      const formData = new FormData(form);
      formData.append('_method', 'PUT');
      
      try {
        const response = await fetch(`{{ route('admin.users.store-documents-info', ':userId') }}`.replace(':userId', this.userId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.progress = 87.5;
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Documents saved successfully.',
            timer: 1500,
            showConfirmButton: false
          });
          this.currentTab = 8;
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save documents.'
        });
      }
    },
    
    async saveAdditionalInfo() {
      const form = document.getElementById('additionalInfoForm');
      const formData = new FormData(form);
      formData.append('_method', 'PUT');
      
      try {
        const response = await fetch(`{{ route('admin.users.store-additional-info', ':userId') }}`.replace(':userId', this.userId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.progress = 100;
          Swal.fire({
            icon: 'success',
            title: 'Completed!',
            text: 'Member created successfully!',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '{{ route('admin.users.index') }}';
          });
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save additional information.'
        });
      }
    }
  };
}
</script>
@endpush
@endsection
