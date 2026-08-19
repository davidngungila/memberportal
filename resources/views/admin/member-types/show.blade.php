@extends('layouts.admin')

@section('breadcrumb', 'Members \u203A Member Types \u203A View')
@section('page_title', 'Member Type Details')

@php
  function fmtTsh($val): string {
      return 'TSh ' . number_format((float)$val, 0, '.', ',');
  }
@endphp

@section('content')
<div class="space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.member-types.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-sm text-primary-600 dark:text-primary-400">
        View member type details and configuration
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
      <!-- Type Info Card -->
      <div class="glass p-6 rounded-2xl">
        <div class="flex items-center gap-4 pb-6 border-b border-primary-100 dark:border-primary-900/50">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 to-purple-600 text-white flex items-center justify-center text-2xl shadow-md">
            <i class="fa-solid fa-user-tag"></i>
          </div>
          <div>
            <h2 class="font-bold text-lg text-primary-900 dark:text-white">{{ $memberType->name }}</h2>
            <p class="text-xs text-primary-600 dark:text-primary-400 font-mono">{{ $memberType->code }}</p>
          </div>
          <div class="ml-auto">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $memberType->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
              {{ ucfirst($memberType->status) }}
            </span>
          </div>
        </div>

        @if($memberType->description)
          <div class="mt-6">
            <h3 class="text-xs font-bold uppercase tracking-wider mb-2 text-primary-700 dark:text-primary-300">Description</h3>
            <p class="text-sm text-primary-700 dark:text-primary-300 leading-relaxed">{{ $memberType->description }}</p>
          </div>
        @endif

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3 text-primary-700 dark:text-primary-300">
              <i class="fa-solid fa-money-bill mr-1.5"></i> Financial Requirements
            </h3>
            <dl class="space-y-3">
              <div class="flex justify-between items-center">
                <dt class="text-xs text-primary-600 dark:text-primary-400">Registration Fee</dt>
                <dd class="text-sm font-semibold text-primary-900 dark:text-white">{{ fmtTsh($memberType->registration_fee) }}</dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-xs text-primary-600 dark:text-primary-400">Monthly Contribution</dt>
                <dd class="text-sm font-semibold text-primary-900 dark:text-white">{{ fmtTsh($memberType->monthly_contribution) }}</dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-xs text-primary-600 dark:text-primary-400">Minimum Savings</dt>
                <dd class="text-sm font-semibold text-primary-900 dark:text-white">{{ fmtTsh($memberType->min_savings) }}</dd>
              </div>
            </dl>
          </div>

          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3 text-primary-700 dark:text-primary-300">
              <i class="fa-solid fa-percent mr-1.5"></i> Loan Benefits
            </h3>
            <dl class="space-y-3">
              <div class="flex justify-between items-center">
                <dt class="text-xs text-primary-600 dark:text-primary-400">Loan Multiplier</dt>
                <dd class="text-sm font-semibold text-primary-900 dark:text-white">{{ $memberType->max_loan_multiplier }}x</dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-xs text-primary-600 dark:text-primary-400">Interest Discount</dt>
                <dd class="text-sm font-semibold text-primary-900 dark:text-white">{{ $memberType->interest_rate_discount }}%</dd>
              </div>
            </dl>
          </div>
        </div>

        <div class="mt-6">
          <h3 class="text-xs font-bold uppercase tracking-wider mb-3 text-primary-700 dark:text-primary-300">
            <i class="fa-solid fa-shield-halved mr-1.5"></i> Privileges
          </h3>
          <div class="flex flex-wrap gap-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $memberType->can_vote ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
              <i class="fa-solid {{ $memberType->can_vote ? 'fa-check' : 'fa-xmark' }} mr-1.5 text-[10px]"></i>
              Voting Rights
            </span>
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $memberType->can_hold_office ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
              <i class="fa-solid {{ $memberType->can_hold_office ? 'fa-check' : 'fa-xmark' }} mr-1.5 text-[10px]"></i>
              Can Hold Office
            </span>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t border-primary-100 dark:border-primary-900/50">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Priority Level</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $memberType->priority }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-primary-600 dark:text-primary-400">Associated Members</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $membersCount }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Members List -->
    @if($members->count() > 0)
      <div class="lg:col-span-2">
        <div class="glass p-6 rounded-2xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
              <i class="fa-solid fa-users text-primary-500 text-xs"></i>
              Members Using This Type
            </h3>
            <span class="text-xs text-primary-600 dark:text-primary-400">{{ $members->total() }} member{{ $members->total() !== 1 ? 's' : '' }}</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-primary-50 dark:bg-primary-900/20">
                  <th class="text-left px-4 py-3 text-xs font-semibold text-primary-600 dark:text-primary-400">Member Code</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-primary-600 dark:text-primary-400">Name</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-primary-600 dark:text-primary-400">Email</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-primary-600 dark:text-primary-400">Status</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-primary-600 dark:text-primary-400">Joined</th>
                </tr>
              </thead>
              <tbody>
                @foreach($members as $member)
                  <tr class="border-b border-primary-100 dark:border-primary-800 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2 py-1 rounded-lg bg-primary-100 dark:bg-primary-900/40 font-mono text-xs font-bold text-primary-700 dark:text-primary-300">
                        {{ $member->membercode ?? 'N/A' }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-primary-900 dark:text-white">{{ $member->name }}</td>
                    <td class="px-4 py-3 text-sm text-primary-700 dark:text-primary-300">{{ $member->email }}</td>
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $member->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                        {{ ucfirst($member->status) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-primary-700 dark:text-primary-300">{{ $member->registration_date ? $member->registration_date->format('M d, Y') : '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-4 flex items-center justify-between">
            <p class="text-xs text-primary-600 dark:text-primary-400">Showing {{ $members->firstItem() }}-{{ $members->lastItem() }} of {{ $members->total() }} members</p>
            {{ $members->links() }}
          </div>
        </div>
      </div>
    @endif

    <!-- Sidebar -->
    <div class="space-y-6">
      <!-- Quick Actions -->
      <div class="glass p-6 rounded-2xl">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-4 flex items-center gap-2">
          <i class="fa-solid fa-bolt text-primary-500 text-xs"></i>
          Quick Actions
        </h3>
        <div class="space-y-3">
          <a href="{{ route('admin.member-types.edit', $encryptedId) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors">
            <i class="fa-solid fa-pen text-xs"></i> Edit Type
          </a>
          <form method="POST" action="{{ route('admin.member-types.destroy', $encryptedId) }}" id="deleteForm" class="hidden">
            @csrf
            @method('DELETE')
          </form>
          <button type="button" onclick="confirmDelete()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition-colors">
            <i class="fa-solid fa-trash text-xs"></i> Delete Type
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="glass p-6 rounded-2xl">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-4 flex items-center gap-2">
          <i class="fa-solid fa-chart-pie text-primary-500 text-xs"></i>
          Statistics
        </h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-xs text-primary-600 dark:text-primary-400">Total Members</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white">{{ $membersCount }}</span>
          </div>
          <div class="w-full bg-primary-100 dark:bg-primary-900/40 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ min(100, $membersCount * 10) }}%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function confirmDelete() {
    Swal.fire({
      title: 'Are you sure?',
      text: 'Do you want to delete this member type?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('deleteForm').submit();
      }
    });
  }
</script>
@endpush
@endsection
