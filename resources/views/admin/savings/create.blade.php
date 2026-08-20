@extends('layouts.admin')

@section('breadcrumb', 'Savings \u203A Add Transaction')
@section('page_title', 'Add Savings Transaction')

@section('content')

<div x-data="savingsForm()" class="space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.savings.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-sm text-primary-600 dark:text-primary-400">
        Add a new savings transaction for a member
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form Area -->
    <div class="lg:col-span-2">
      <div class="glass p-6 rounded-2xl">
        <form method="POST" action="{{ route('admin.savings.store') }}" @submit.prevent="submitForm">
          @csrf

          <!-- Member -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Member <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input type="text" 
                     placeholder="Search member by name or member number..."
                     class="form-input w-full"
                     x-model="memberSearch"
                     @input="filterMembers"
                     @focus="showMemberDropdown = true"
                     @blur="setTimeout(() => showMemberDropdown = false, 200)"/>
              <input type="hidden" name="member_number" x-model="form.member_number" required/>
              <button x-show="form.member_number" @click="clearMember" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-400 hover:text-primary-600">
                <i class="fa-solid fa-xmark text-xs"></i>
              </button>
            </div>
            
            <!-- Dropdown Results -->
            <div x-show="showMemberDropdown && filteredMembers.length > 0" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto">
               <template x-for="member in filteredMembers" :key="member.membercode">
                <div @click="selectMember(member)" 
                     class="px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0">
                  <div class="font-semibold text-sm text-primary-900 dark:text-white" x-text="member.name"></div>
                   <div class="text-xs text-primary-500 dark:text-primary-400" x-text="member.membercode"></div>
                </div>
              </template>
            </div>
            
            @error('member_number')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Transaction Type -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Transaction Type <span class="text-red-500">*</span>
            </label>
            <select name="transaction_type" required class="form-input w-full" x-model="form.transaction_type">
              <option value="">Select transaction type</option>
              <option value="deposit">Deposit</option>
              <option value="withdrawal">Withdrawal</option>
              <option value="interest">Interest</option>
              <option value="flexi-deposit">Flexi Deposit</option>
              <option value="rda-deposit">RDA Deposit</option>
              <option value="opening balance">Opening Balance</option>
            </select>
            @error('transaction_type')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Savings Product -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Savings Product (Optional)
            </label>
            <select name="product_id" class="form-input w-full" x-model="form.product_id" @change="onProductChange">
              <option value="">No product</option>
              @foreach($products as $product)
                <option value="{{ $product->id }}" 
                        data-interest-rate="{{ $product->interest_rate }}"
                        data-min-deposit="{{ $product->min_deposit }}"
                        data-max-deposit="{{ $product->max_deposit }}">
                  {{ $product->name }} - {{ $product->interest_rate }}% Interest
                </option>
              @endforeach
            </select>
            @error('product_id')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Saving Plan -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Saving Plan (Optional)
            </label>
            <select name="saving_plan_id" class="form-input w-full" x-model="form.saving_plan_id">
              <option value="">No saving plan</option>
              @foreach($savingPlans as $plan)
                <option value="{{ $plan->id }}" data-member="{{ $plan->member_number }}">
                  {{ $plan->name }} - {{ $plan->user ? $plan->user->name : 'Unknown' }} ({{ $plan->member_number }})
                </option>
              @endforeach
            </select>
            @error('saving_plan_id')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Amount -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Amount (TSh) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="amount" required min="0" step="0.01"
                   class="form-input w-full"
                   x-model="form.amount"
                   @input="calculateInterest"
                   placeholder="Enter amount"/>
            @error('amount')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Date -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Transaction Date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="date" required
                   class="form-input w-full"
                   x-model="form.date"
                   value="{{ now()->format('Y-m-d') }}"/>
            @error('date')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Reference Number -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Reference Number (Optional)
            </label>
            <input type="text" name="reference_no"
                   class="form-input w-full"
                   x-model="form.reference_no"
                   placeholder="Enter reference number"/>
            @error('reference_no')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-primary-100 dark:border-primary-900/50">
            <a href="{{ route('admin.savings.index') }}"
               class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-bold transition-all">
              Cancel
            </a>
            <button type="submit"
                    :disabled="submitting"
                    class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 disabled:bg-teal-400 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 flex items-center gap-2">
              <i x-show="!submitting" class="fa-solid fa-plus text-[13px]"></i>
              <i x-show="submitting" class="fa-solid fa-spinner fa-spin text-[13px]"></i>
              <span x-text="submitting ? 'Adding...' : 'Add Transaction'"></span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="lg:col-span-1">
      <div class="glass p-5 rounded-2xl sticky top-6 space-y-6">
        <!-- Product Details -->
        <div x-show="selectedProduct" class="p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800/40">
          <h4 class="text-sm font-bold text-teal-700 dark:text-teal-300 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-piggy-bank text-xs"></i> Product Details
          </h4>
          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Interest Rate:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="selectedProduct.interest_rate + '%'"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Min Deposit:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="formatCurrency(selectedProduct.min_deposit)"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Max Deposit:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="formatCurrency(selectedProduct.max_deposit)"></span>
            </div>
          </div>
        </div>

        <!-- Transaction Summary -->
        <div x-show="form.amount" class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800/40">
          <h4 class="text-sm font-bold text-primary-700 dark:text-primary-300 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-calculator text-xs"></i> Transaction Summary
          </h4>
          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Transaction Type:</span>
              <span class="font-bold text-primary-900 dark:text-white capitalize" x-text="form.transaction_type"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Amount:</span>
              <span class="font-bold text-primary-900 dark:text-white" x-text="formatCurrency(form.amount)"></span>
            </div>
            <div x-show="selectedProduct && form.transaction_type === 'deposit'" class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Expected Interest:</span>
              <span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(calculateExpectedInterest())"></span>
            </div>
            <div x-show="selectedProduct && form.transaction_type === 'deposit'" class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Expected Return:</span>
              <span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(parseFloat(form.amount) + calculateExpectedInterest())"></span>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div x-show="!selectedProduct && !form.amount" class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800/40 text-center">
          <i class="fa-solid fa-coins text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
          <p class="text-xs text-gray-500 dark:text-gray-400">Select a product and enter amount to see calculations</p>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function savingsForm() {
    return {
      form: {
        member_number: '',
        transaction_type: '',
        product_id: '',
        saving_plan_id: '',
        amount: '',
        date: '{{ now()->format('Y-m-d') }}',
        reference_no: ''
      },
      selectedProduct: null,
      submitting: false,
      memberSearch: '',
      showMemberDropdown: false,
      filteredMembers: [],
      allMembers: @json($members),
      
      init() {
        this.filteredMembers = this.allMembers;
      },
      
      filterMembers() {
        const search = this.memberSearch.toLowerCase();
        if (!search) {
          this.filteredMembers = this.allMembers;
        } else {
          this.filteredMembers = this.allMembers.filter(member => 
            member.name.toLowerCase().includes(search) ||
             member.membercode.toLowerCase().includes(search)
          );
        }
        this.showMemberDropdown = true;
      },
      
      selectMember(member) {
         this.form.member_number = member.membercode;
         this.memberSearch = member.name + ' (' + member.membercode + ')';
        this.showMemberDropdown = false;
      },
      
      clearMember() {
        this.form.member_number = '';
        this.memberSearch = '';
        this.filteredMembers = this.allMembers;
      },
      
      onProductChange() {
        const select = document.querySelector('select[name="product_id"]');
        const option = select.options[select.selectedIndex];
        
        if (option && option.value) {
          this.selectedProduct = {
            interest_rate: parseFloat(option.dataset.interestRate) || 0,
            min_deposit: parseFloat(option.dataset.minDeposit) || 0,
            max_deposit: parseFloat(option.dataset.maxDeposit) || 0
          };
        } else {
          this.selectedProduct = null;
        }
      },
      
      calculateInterest() {
        // Interest calculation happens in real-time when viewing summary
      },
      
      calculateExpectedInterest() {
        if (!this.form.amount || !this.selectedProduct) return 0;
        const amount = parseFloat(this.form.amount);
        const rate = this.selectedProduct.interest_rate / 100;
        return amount * rate;
      },
      
      formatCurrency(value) {
        if (!value && value !== 0) return '0.00 TSh';
        return new Intl.NumberFormat('en-TZ', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(value) + ' TSh';
      },
      
      submitForm() {
        this.submitting = true;
        this.$el.submit();
      }
    };
  }
</script>
@endpush
