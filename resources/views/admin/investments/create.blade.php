@extends('layouts.admin')

@section('breadcrumb', 'Members › Investments › New Investment')
@section('page_title', 'Create New Investment')

@section('content')

<div x-data="investmentForm()" class="space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.investments.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-sm text-primary-600 dark:text-primary-400">
        Create a new investment for a member
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form Area -->
    <div class="lg:col-span-2">
      <div class="glass p-6 rounded-2xl">
        <form method="POST" action="{{ route('admin.investments.store') }}" @submit.prevent="submitForm">
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
                  <div class="font-semibold text-sm text-primary-900 dark:text-white" x-text="member.full_name"></div>
                  <div class="text-xs text-primary-500 dark:text-primary-400" x-text="member.member_number"></div>
                </div>
              </template>
            </div>
            
            @error('member_number')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Investment Product -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Investment Product <span class="text-red-500">*</span>
            </label>
            <select name="investment_product_id" required class="form-input w-full" x-model="form.investment_product_id" @change="onProductChange">
              <option value="">Select a product</option>
              @foreach($products as $product)
                <option value="{{ $product->id }}" 
                        data-interest-rate="{{ $product->interest_rate }}"
                        data-min-investment="{{ $product->min_investment }}"
                        data-max-investment="{{ $product->max_investment }}"
                        data-duration="{{ $product->duration_months }}">
                  {{ $product->name }} - {{ $product->interest_rate }}% ({{ $product->duration_months }} months)
                </option>
              @endforeach
            </select>
            @error('investment_product_id')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Amount -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Investment Amount (TSh) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="amount" required min="0" step="0.01"
                   class="form-input w-full"
                   x-model="form.amount"
                   placeholder="Enter investment amount"/>
            @error('amount')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Investment Date -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Investment Date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="investment_date" required
                   class="form-input w-full"
                   x-model="form.investment_date"
                   @change="calculateMaturityDate"/>
            @error('investment_date')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Maturity Date -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Maturity Date <span class="text-primary-500 text-xs font-normal">(Auto-calculated if left blank)</span>
            </label>
            <input type="date" name="maturity_date"
                   class="form-input w-full"
                   x-model="form.maturity_date"/>
            @error('maturity_date')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Notes -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Notes
            </label>
            <textarea name="notes" rows="3"
                      class="form-input w-full"
                      x-model="form.notes"
                      placeholder="Optional notes..."></textarea>
            @error('notes')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-primary-100 dark:border-primary-900/50">
            <a href="{{ route('admin.investments.index') }}"
               class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-bold transition-all">
              Cancel
            </a>
            <button type="submit"
                    :disabled="submitting"
                    class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 disabled:bg-teal-400 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 flex items-center gap-2">
              <i x-show="!submitting" class="fa-solid fa-plus text-[13px]"></i>
              <i x-show="submitting" class="fa-solid fa-spinner fa-spin text-[13px]"></i>
              <span x-text="submitting ? 'Creating...' : 'Create Investment'"></span>
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
            <i class="fa-solid fa-chart-line text-xs"></i> Product Details
          </h4>
          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Interest Rate:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="selectedProduct.interest_rate + '%'"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Duration:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="selectedProduct.duration + ' months'"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Min Investment:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="formatCurrency(selectedProduct.min_investment)"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Max Investment:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="formatCurrency(selectedProduct.max_investment)"></span>
            </div>
          </div>
        </div>

        <!-- Investment Summary -->
        <div x-show="form.amount && selectedProduct" class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800/40">
          <h4 class="text-sm font-bold text-primary-700 dark:text-primary-300 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-calculator text-xs"></i> Investment Summary
          </h4>
          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Invested Amount:</span>
              <span class="font-bold text-primary-900 dark:text-white" x-text="formatCurrency(form.amount)"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Expected Return:</span>
              <span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(calculateExpectedReturn())"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Expected Profit:</span>
              <span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(calculateExpectedReturn() - form.amount)"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-primary-600 dark:text-primary-400">Return Percentage:</span>
              <span class="font-bold text-primary-900 dark:text-white" x-text="selectedProduct.interest_rate + '%'"></span>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div x-show="!selectedProduct" class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800/40 text-center">
          <i class="fa-solid fa-chart-line text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
          <p class="text-xs text-gray-500 dark:text-gray-400">Select a product to view details</p>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function investmentForm() {
    return {
      form: {
        member_number: '',
        investment_product_id: '',
        amount: '',
        investment_date: '',
        maturity_date: '',
        notes: ''
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
            member.full_name.toLowerCase().includes(search) ||
            member.membercode.toLowerCase().includes(search)
          );
        }
        this.showMemberDropdown = true;
      },
      
      selectMember(member) {
        this.form.member_number = member.membercode;
        this.memberSearch = member.full_name + ' (' + member.membercode + ')';
        this.showMemberDropdown = false;
      },
      
      clearMember() {
        this.form.member_number = '';
        this.memberSearch = '';
        this.filteredMembers = this.allMembers;
      },
      
      onMemberChange() {
        // Handle member selection if needed
      },
      
      onProductChange() {
        const select = document.querySelector('select[name="investment_product_id"]');
        const option = select.options[select.selectedIndex];
        
        if (option && option.value) {
          this.selectedProduct = {
            interest_rate: parseFloat(option.dataset.interestRate) || 0,
            min_investment: parseFloat(option.dataset.minInvestment) || 0,
            max_investment: parseFloat(option.dataset.maxInvestment) || 0,
            duration: parseInt(option.dataset.duration) || 0
          };
          this.calculateMaturityDate();
        } else {
          this.selectedProduct = null;
        }
      },
      
      calculateMaturityDate() {
        if (this.form.investment_date && this.selectedProduct && this.selectedProduct.duration > 0) {
          const investmentDate = new Date(this.form.investment_date);
          const maturityDate = new Date(investmentDate);
          maturityDate.setMonth(maturityDate.getMonth() + this.selectedProduct.duration);
          this.form.maturity_date = maturityDate.toISOString().split('T')[0];
        }
      },
      
      calculateExpectedReturn() {
        if (!this.form.amount || !this.selectedProduct) return 0;
        const amount = parseFloat(this.form.amount);
        const rate = this.selectedProduct.interest_rate / 100;
        return amount * (1 + rate);
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
