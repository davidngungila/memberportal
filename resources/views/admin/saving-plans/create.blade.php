@extends('layouts.admin')

@section('breadcrumb', 'Saving Plans \u203A New')
@section('page_title', 'Create Saving Plan')

@section('content')

<div x-data="savingPlanForm()" class="space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.saving-plans.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-sm text-primary-600 dark:text-primary-400">
        Create a new saving plan for a member
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form Area -->
    <div class="lg:col-span-2">
      <div class="glass p-6 rounded-2xl">
        <form method="POST" action="{{ route('admin.saving-plans.store') }}" @submit.prevent="submitForm">
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
              <input type="hidden" name="user_id" x-model="form.user_id" required/>
              <button x-show="form.user_id" @click="clearMember" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-400 hover:text-primary-600">
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
              <template x-for="member in filteredMembers" :key="member.id">
                <div @click="selectMember(member)" 
                     class="px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0">
                  <div class="font-semibold text-sm text-primary-900 dark:text-white" x-text="member.name"></div>
                   <div class="text-xs text-primary-500 dark:text-primary-400" x-text="member.membercode"></div>
                </div>
              </template>
            </div>
            
            @error('user_id')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Plan Name -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Plan Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" required
                   class="form-input w-full"
                   x-model="form.name"
                   placeholder="Enter plan name (e.g., Emergency Fund, Vacation Savings)"/>
            @error('name')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Goal Amount -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Total Goal Amount (TSh) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="goal" required min="0" step="0.01"
                   class="form-input w-full"
                   x-model="form.goal"
                   @input="calculateSchedule"
                   placeholder="Enter total amount you want to save"/>
            @error('goal')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Period Type and Value -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
                Saving Period <span class="text-red-500">*</span>
              </label>
              <select name="period_type" required class="form-input w-full" x-model="form.period_type" @change="calculateSchedule">
                <option value="">Select period</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
              @error('period_type')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
                Number of Periods <span class="text-red-500">*</span>
              </label>
              <input type="number" name="period_value" required min="1"
                     class="form-input w-full"
                     x-model="form.period_value"
                     @input="calculateSchedule"
                     placeholder="e.g., 12 for 12 months"/>
              @error('period_value')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- Start Date -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Start Date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="start_date" required
                   class="form-input w-full"
                   x-model="form.start_date"
                   @change="calculateSchedule"/>
            @error('start_date')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Status -->
          <div class="mb-6">
            <label class="block text-sm font-bold text-primary-900 dark:text-white mb-2">
              Status
            </label>
            <select name="status" class="form-input w-full" x-model="form.status">
              <option value="active" selected>Active</option>
              <option value="paused">Paused</option>
              <option value="completed">Completed</option>
            </select>
            @error('status')
              <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-primary-100 dark:border-primary-900/50">
            <a href="{{ route('admin.saving-plans.index') }}"
               class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-bold transition-all">
              Cancel
            </a>
            <button type="submit"
                    :disabled="submitting"
                    class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 disabled:bg-teal-400 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 flex items-center gap-2">
              <i x-show="!submitting" class="fa-solid fa-plus text-[13px]"></i>
              <i x-show="submitting" class="fa-solid fa-spinner fa-spin text-[13px]"></i>
              <span x-text="submitting ? 'Creating...' : 'Create Saving Plan'"></span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="lg:col-span-1">
      <div class="glass p-5 rounded-2xl sticky top-6 space-y-6">
        <!-- Plan Summary -->
        <div x-show="form.goal && form.period_type && form.period_value" class="p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800/40">
          <h4 class="text-sm font-bold text-teal-700 dark:text-teal-300 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-calculator text-xs"></i> Plan Summary
          </h4>
          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Total Goal:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="formatCurrency(form.goal)"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Period Type:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100 capitalize" x-text="form.period_type"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Number of Periods:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="form.period_value"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Amount per Period:</span>
              <span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(periodicAmount)"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-teal-600 dark:text-teal-400">Target Date:</span>
              <span class="font-bold text-teal-900 dark:text-teal-100" x-text="targetDate || '—'"></span>
            </div>
          </div>
        </div>

        <!-- Payment Schedule Preview -->
        <div x-show="paymentSchedule.length > 0" class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800/40">
          <h4 class="text-sm font-bold text-primary-700 dark:text-primary-300 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-calendar-days text-xs"></i> Payment Schedule
          </h4>
          <div class="space-y-2 text-xs max-h-64 overflow-y-auto">
            <template x-for="payment in paymentSchedule.slice(0, 10)" :key="payment.period_number">
              <div class="flex items-center justify-between py-1 border-b border-primary-100 dark:border-primary-800/50 last:border-0">
                <span class="text-primary-600 dark:text-primary-400">
                  <span x-text="'#' + payment.period_number"></span>
                  <span class="ml-2" x-text="payment.due_date"></span>
                </span>
                <span class="font-bold text-primary-900 dark:text-white" x-text="formatCurrency(payment.amount)"></span>
              </div>
            </template>
            <div x-show="paymentSchedule.length > 10" class="text-center text-primary-500 dark:text-primary-400 py-2">
              ... and <span x-text="paymentSchedule.length - 10"></span> more payments
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div x-show="!form.goal || !form.period_type || !form.period_value" class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800/40 text-center">
          <i class="fa-solid fa-piggy-bank text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
          <p class="text-xs text-gray-500 dark:text-gray-400">Enter goal, period, and start date to see calculations</p>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function savingPlanForm() {
    return {
      form: {
        user_id: '',
        name: '',
        goal: '',
        period_type: '',
        period_value: '',
        start_date: '',
        status: 'active'
      },
      periodicAmount: 0,
      targetDate: '',
      paymentSchedule: [],
      submitting: false,
      memberSearch: '',
      showMemberDropdown: false,
      filteredMembers: [],
      allMembers: @json($members),
      
      init() {
        this.filteredMembers = this.allMembers;
        this.form.start_date = new Date().toISOString().split('T')[0];
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
        this.form.user_id = member.id;
         this.memberSearch = member.name + ' (' + member.membercode + ')';
        this.showMemberDropdown = false;
      },
      
      clearMember() {
        this.form.user_id = '';
        this.memberSearch = '';
        this.filteredMembers = this.allMembers;
      },
      
      calculateSchedule() {
        if (!this.form.goal || !this.form.period_type || !this.form.period_value || !this.form.start_date) {
          this.periodicAmount = 0;
          this.targetDate = '';
          this.paymentSchedule = [];
          return;
        }
        
        const goal = parseFloat(this.form.goal);
        const periods = parseInt(this.form.period_value);
        this.periodicAmount = goal / periods;
        
        const startDate = new Date(this.form.start_date);
        const targetDate = new Date(startDate);
        
        switch(this.form.period_type) {
          case 'daily':
            targetDate.setDate(targetDate.getDate() + periods);
            break;
          case 'weekly':
            targetDate.setDate(targetDate.getDate() + (periods * 7));
            break;
          case 'monthly':
            targetDate.setMonth(targetDate.getMonth() + periods);
            break;
        }
        
        this.targetDate = targetDate.toISOString().split('T')[0];
        
        // Generate payment schedule
        this.paymentSchedule = [];
        const currentDate = new Date(startDate);
        
        for (let i = 1; i <= periods; i++) {
          this.paymentSchedule.push({
            period_number: i,
            due_date: currentDate.toISOString().split('T')[0],
            amount: this.periodicAmount.toFixed(2),
            status: 'pending'
          });
          
          switch(this.form.period_type) {
            case 'daily':
              currentDate.setDate(currentDate.getDate() + 1);
              break;
            case 'weekly':
              currentDate.setDate(currentDate.getDate() + 7);
              break;
            case 'monthly':
              currentDate.setMonth(currentDate.getMonth() + 1);
              break;
          }
        }
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
