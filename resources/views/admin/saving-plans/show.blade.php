@extends('layouts.admin')

@section('breadcrumb', 'Saving Plans \u203A View')
@section('page_title', 'View Saving Plan')

@section('content')

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.saving-plans.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-sm text-primary-600 dark:text-primary-400">
        View saving plan details
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
      <!-- Plan Details -->
      <div class="glass p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-bold text-primary-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-piggy-bank text-teal-500"></i>
            {{ $savingPlan->name }}
          </h3>
          <span class="badge {{ $savingPlan->status === 'active' ? 'badge-green' : ($savingPlan->status === 'completed' ? 'badge-blue' : 'badge-gray') }}">
            {{ ucfirst($savingPlan->status) }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Member</p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $savingPlan->member ? $savingPlan->member->full_name : ($savingPlan->user ? $savingPlan->user->name : 'Unknown') }}</p>
            <p class="text-xs text-primary-500 dark:text-primary-400">{{ $savingPlan->member_number }}</p>
          </div>
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Total Goal</p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ number_format($savingPlan->goal, 2) }} TSh</p>
          </div>
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Period Type</p>
            <p class="text-sm font-bold text-primary-900 dark:text-white capitalize">{{ $savingPlan->period_type }}</p>
          </div>
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Number of Periods</p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $savingPlan->period_value }}</p>
          </div>
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Amount per Period</p>
            <p class="text-sm font-bold text-green-600 dark:text-green-400">{{ number_format($savingPlan->periodic_amount, 2) }} TSh</p>
          </div>
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Start Date</p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $savingPlan->start_date ? $savingPlan->start_date->format('M j, Y') : '—' }}</p>
          </div>
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Target Date</p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $savingPlan->target_date ? $savingPlan->target_date->format('M j, Y') : '—' }}</p>
          </div>
          <div class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50">
            <p class="text-xs text-primary-600 dark:text-primary-400 mb-1">Created</p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $savingPlan->created_at ? $savingPlan->created_at->format('M j, Y') : '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Payment Schedule -->
      <div class="glass p-6 rounded-2xl">
        <h3 class="text-lg font-bold text-primary-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fa-solid fa-calendar-days text-teal-500"></i>
          Payment Schedule
        </h3>
        
        @if($savingPlan->payment_schedule && is_array($savingPlan->payment_schedule) && count($savingPlan->payment_schedule) > 0)
          <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl">
            <table class="data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Due Date</th>
                  <th class="text-right">Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($savingPlan->payment_schedule as $payment)
                  <tr>
                    <td class="text-xs text-primary-400 dark:text-primary-500 font-mono">{{ $payment['period_number'] }}</td>
                    <td class="text-sm font-semibold text-primary-900 dark:text-white">
                      {{ \Carbon\Carbon::parse($payment['due_date'])->format('M j, Y') }}
                    </td>
                    <td class="text-right">
                      <span class="font-bold text-sm text-primary-900 dark:text-white">
                        {{ number_format($payment['amount'], 2) }} TSh
                      </span>
                    </td>
                    <td>
                      <span class="badge {{ $payment['status'] === 'completed' ? 'badge-green' : ($payment['status'] === 'pending' ? 'badge-yellow' : 'badge-gray') }}">
                        {{ ucfirst($payment['status']) }}
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-8 text-primary-500 dark:text-primary-400">
            <i class="fa-solid fa-calendar-xmark text-3xl mb-3 block opacity-30"></i>
            <p class="text-sm">No payment schedule available</p>
          </div>
        @endif
      </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
      <!-- Progress Summary -->
      <div class="glass p-5 rounded-2xl">
        <h4 class="text-sm font-bold text-primary-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fa-solid fa-chart-pie text-teal-500"></i> Progress Summary
        </h4>
        
        @if($savingPlan->payment_schedule && is_array($savingPlan->payment_schedule))
          @php
            $totalPayments = count($savingPlan->payment_schedule);
            $completedPayments = collect($savingPlan->payment_schedule)->where('status', 'completed')->count();
            $progressPercentage = $totalPayments > 0 ? round(($completedPayments / $totalPayments) * 100, 1) : 0;
            $totalSaved = collect($savingPlan->payment_schedule)->where('status', 'completed')->sum('amount');
          @endphp
          
          <div class="space-y-4">
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-primary-600 dark:text-primary-400">Progress</span>
                <span class="text-xs font-bold text-primary-900 dark:text-white">{{ $progressPercentage }}%</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-teal-500 h-2 rounded-full transition-all" style="width: {{ $progressPercentage }}%"></div>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <div class="p-3 rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800/40">
                <p class="text-[10px] text-teal-600 dark:text-teal-400 mb-1">Completed</p>
                <p class="text-sm font-bold text-teal-900 dark:text-teal-100">{{ $completedPayments }} / {{ $totalPayments }}</p>
              </div>
              <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800/40">
                <p class="text-[10px] text-primary-600 dark:text-primary-400 mb-1">Total Saved</p>
                <p class="text-sm font-bold text-primary-900 dark:text-white">{{ number_format($totalSaved, 2) }}</p>
              </div>
            </div>
          </div>
        @else
          <div class="text-center py-4 text-primary-500 dark:text-primary-400">
            <p class="text-xs">No progress data available</p>
          </div>
        @endif
      </div>

      <!-- Actions -->
      <div class="glass p-5 rounded-2xl">
        <h4 class="text-sm font-bold text-primary-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fa-solid fa-gear text-teal-500"></i> Actions
        </h4>
        
        <div class="space-y-3">
          <a href="{{ route('admin.saving-plans.edit', $savingPlan->id) }}"
             class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-sm font-bold transition-all w-full">
            <i class="fa-solid fa-pen text-xs"></i> Edit Plan
          </a>
          <form method="POST" action="{{ route('admin.saving-plans.destroy', $savingPlan->id) }}" onsubmit="return confirm('Are you sure you want to delete this saving plan?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 text-sm font-bold transition-all w-full">
              <i class="fa-solid fa-trash text-xs"></i> Delete Plan
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
