@extends('layouts.admin')

@section('breadcrumb', 'Members \u203A View Member')
@section('page_title', 'Member Profile')

@php
  $fmt = fn($n) => number_format((float)$n, 2) . ' TSh';
  $fmtInt = fn($n) => number_format((int)$n);

  $memberName = $member['name'] ?? ($member['Name'] ?? 'Unknown Member');
  $memberNo = $member['membercode'] ?? ($member['MemberCode'] ?? $memberNumber);
  $memberGender = $member['gender'] ?? ($member['Gender'] ?? '-');
  $memberPhone = $member['phone'] ?? ($member['Phone'] ?? '-');
  $memberEmail = $member['email'] ?? ($member['Email'] ?? '-');
  $memberAddress = $member['address'] ?? ($member['Address'] ?? '-');
  $memberOccupation = $member['occupation'] ?? ($member['Occupation'] ?? '-');
  $memberEmployer = $member['employer'] ?? ($member['Employer'] ?? '-');
  $memberBranch = $member['branch'] ?? ($member['Branch'] ?? '-');
  $memberRegDate = $member['registration_date'] ?? ($member['RegistrationDate'] ?? '-');
  $memberStatus = $member['status'] ?? null;
  $memberPhoto = $member['photo'] ?? null;

  $statusBadge = $dashboardService->memberStatusBadge($memberStatus);

  $loans = $loans ?? [];
  $savings = $savings ?? [];
  $deposits = $deposits ?? [];
  $swf = $swf ?? [];
  $investments = $investments ?? [];
  $shares = $shares ?? [];

  $savingsBalance = $savings['balance'] ?? ($savings[0]['balance'] ?? 0);
  $savingsInterest = $savings['interest_earned'] ?? ($savings[0]['interest_earned'] ?? 0);
  $savingsRunning = $savings['running_balance'] ?? ($savings[0]['running_balance'] ?? 0);
  $savingsTransactions = $savings['transactions'] ?? ($savings[0]['transactions'] ?? []);

  $swfTotal = $swf['total_contribution'] ?? ($swf[0]['total_contribution'] ?? 0);
  $swfBenefits = $swf['benefits'] ?? ($swf[0]['benefits'] ?? 0);
  $swfBalance = $swf['current_balance'] ?? ($swf[0]['current_balance'] ?? 0);
  $swfHistory = $swf['contribution_history'] ?? ($swf[0]['contribution_history'] ?? []);
@endphp

@section('content')

<div x-data="memberShow()" class="space-y-6">

  <div class="glass p-6 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-80 h-80 rounded-full bg-gradient-to-br from-primary-200/30 to-transparent dark:from-primary-800/20 -mr-40 -mt-40"></div>

    <div class="flex flex-col md:flex-row md:items-start gap-6 relative z-10">
      <div class="flex-shrink-0">
        <div class="w-28 h-28 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center text-4xl font-bold shadow-xl ring-4 ring-white dark:ring-primary-900/40 overflow-hidden">
          @if($memberPhoto)
            @php
              // Use the same approach as users table - direct path
              if (str_starts_with($memberPhoto, 'http://') || str_starts_with($memberPhoto, 'https://')) {
                  $photoPath = $memberPhoto;
              } else {
                  $photoPath = asset('storage/' . $memberPhoto);
              }
              // Debug: Log the photo path
              \Log::info('Member photo display', ['member' => $memberNumber, 'photo' => $memberPhoto, 'path' => $photoPath]);
            @endphp
            <img src="{{ $photoPath }}" alt="{{ $memberName }}" class="w-full h-full object-cover rounded-2xl" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'; console.log('Image failed to load: {{ $photoPath }}');"/>
            <svg class="w-14 h-14 opacity-80 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          @else
            @php
              \Log::info('Member photo is null or empty', ['member' => $memberNumber]);
            @endphp
            <svg class="w-14 h-14 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          @endif
        </div>
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <h1 class="text-2xl font-bold text-primary-900 dark:text-white">{{ $memberName }}</h1>
              <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
              @if(isset($member['member_type_id']) && $member['member_type_id'])
                @php
                  $memberType = \App\Models\MemberType::find($member['member_type_id']);
                @endphp
                @if($memberType)
                  <span class="inline-flex items-center px-3 py-1 rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 text-xs font-bold">
                    <i class="fa-solid fa-user-tag mr-1.5 text-[10px]"></i>
                    {{ $memberType->name }}
                  </span>
                @endif
              @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-primary-100 dark:bg-primary-900/50 font-mono text-xs font-bold text-primary-700 dark:text-primary-300">
                <i class="fa-solid fa-id-card text-[10px]"></i>
                {{ $memberNo }}
              </span>
              <span class="inline-flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400">
                <i class="fa-solid fa-calendar text-[10px]"></i>
                Registered: {{ $memberRegDate }}
              </span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" onclick="previewMembershipCertificate()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-100 hover:bg-purple-200 dark:bg-purple-900/40 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-xs font-bold transition-colors">
              <i class="fa-solid fa-certificate text-[11px]"></i> Certificate
            </button>
            <a href="tel:{{ $memberPhone }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-xs font-bold transition-colors">
              <i class="fa-solid fa-phone text-[11px]"></i> Call
            </a>
            <a href="mailto:{{ $memberEmail }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-xs font-bold transition-colors">
              <i class="fa-solid fa-envelope text-[11px]"></i> Email
            </a>
            <a href="{{ route('admin.members.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800/50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold transition-colors">
              <i class="fa-solid fa-arrow-left text-[11px]"></i> Back
            </a>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
          <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30">
            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 dark:text-primary-400 mb-1">
              <i class="fa-solid fa-venus-mars mr-1"></i> Gender
            </p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberGender }}</p>
          </div>
          <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30">
            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 dark:text-primary-400 mb-1">
              <i class="fa-solid fa-location-dot mr-1"></i> Branch
            </p>
            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberBranch }}</p>
          </div>
          <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30">
            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 dark:text-primary-400 mb-1">
              <i class="fa-solid fa-phone mr-1"></i> Phone
            </p>
            <p class="text-sm font-bold text-primary-900 dark:text-white font-mono">{{ $memberPhone }}</p>
          </div>
          <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30">
            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 dark:text-primary-400 mb-1">
              <i class="fa-solid fa-envelope mr-1"></i> Email
            </p>
            <p class="text-sm font-bold text-primary-900 dark:text-white truncate max-w-[150px]">{{ $memberEmail }}</p>
          </div>
          <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30">
            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 dark:text-primary-400 mb-1">
              <i class="fa-solid fa-briefcase mr-1"></i> Occupation
            </p>
            <p class="text-sm font-bold text-primary-900 dark:text-white truncate max-w-[150px]">{{ $memberOccupation }}</p>
          </div>
          <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30">
            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 dark:text-primary-400 mb-1">
              <i class="fa-solid fa-building mr-1"></i> Employer
            </p>
            <p class="text-sm font-bold text-primary-900 dark:text-white truncate max-w-[150px]">{{ $memberEmployer }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="glass p-1.5 rounded-2xl">
    <nav class="flex items-center gap-1 p-1 overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden">
      <template x-for="tab in tabs" :key="tab.id">
        <button @click="activeTab = tab.id; updateHash(tab.id)"
                :class="[
                  activeTab === tab.id
                    ? 'bg-primary-600 text-white shadow-md'
                    : 'text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40',
                  'flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap'
                ]">
          <i :class="tab.icon" class="text-[11px]"></i>
          <span x-text="tab.label"></span>
          <span x-show="tab.badge !== null"
                :class="[
                  activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-primary-200 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300',
                  'ml-1 px-1.5 py-0.5 rounded-lg text-[10px] font-bold'
                ]" x-text="tab.badge"></span>
        </button>
      </template>
    </nav>
  </div>

  <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="glass p-6">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-5 flex items-center gap-2">
          <i class="fa-solid fa-user text-primary-500 text-xs"></i> Personal Information
        </h3>
        <div class="space-y-4">
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Full Name</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white text-right">{{ $memberName }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Member Code</span>
            <span class="text-sm font-mono font-bold text-primary-900 dark:text-white">{{ $memberNo }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Gender</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberGender }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Phone</span>
            <span class="text-sm font-mono font-bold text-primary-900 dark:text-white">{{ $memberPhone }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Email</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberEmail }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Registration Date</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberRegDate }}</span>
          </div>
        </div>
      </div>

      <div class="glass p-6">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-5 flex items-center gap-2">
          <i class="fa-solid fa-building text-primary-500 text-xs"></i> Employment & Address
        </h3>
        <div class="space-y-4">
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Branch</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberBranch }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Occupation</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberOccupation }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Employer</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white">{{ $memberEmployer }}</span>
          </div>
          <div class="flex items-start justify-between pb-4 border-b border-primary-100 dark:border-primary-900/50">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Residential Address</span>
            <span class="text-sm font-bold text-primary-900 dark:text-white text-right max-w-[60%]">{{ $memberAddress }}</span>
          </div>
          <div class="flex items-start justify-between">
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400">Account Status</span>
            <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div x-show="activeTab === 'loans'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="glass p-6">
      <div class="flex items-center justify-between mb-5">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
          <i class="fa-solid fa-hand-holding-dollar text-orange-500 text-xs"></i>
          Loan Accounts
          <span class="badge badge-yellow ml-2">{{ count($loans) }} Active</span>
        </h3>
        <a href="{{ route('admin.loans.index') }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-500">
          Manage All Loans <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
        </a>
      </div>

      <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl">
        <table class="data-table">
          <thead>
            <tr>
              <th>Loan #</th>
              <th>Product</th>
              <th class="text-right">Amount</th>
              <th class="text-right">Outstanding</th>
              <th class="text-right">Paid</th>
              <th class="text-right">Installment</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($loans as $loan)
              @php
                $loanNo = $loan['loan_number'] ?? ($loan['LoanNumber'] ?? '-');
                $loanProduct = $loan['loan_product'] ?? ($loan['LoanProduct'] ?? '-');
                $loanAmount = $loan['loan_amount'] ?? ($loan['LoanAmount'] ?? 0);
                $loanOutstanding = $loan['outstanding_balance'] ?? ($loan['OutstandingBalance'] ?? 0);
                $loanPaid = $loan['paid_amount'] ?? ($loan['PaidAmount'] ?? 0);
                $loanInstallment = $loan['installment'] ?? ($loan['Installment'] ?? 0);
                $loanStatus = $dashboardService->loanStatusBadge($loan['status'] ?? ($loan['Status'] ?? null));
                $progress = $loanAmount > 0 ? min(($loanPaid / $loanAmount) * 100, 100) : 0;
              @endphp
              <tr>
                <td class="font-mono text-xs font-bold text-primary-700 dark:text-primary-300">{{ $loanNo }}</td>
                <td>
                  <div>
                    <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $loanProduct }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                      <div class="flex-1 max-w-[100px] h-1.5 rounded-full bg-primary-100 dark:bg-primary-900/40 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-primary-400 to-primary-600 rounded-full" style="width: {{ $progress }}%"></div>
                      </div>
                      <span class="text-[10px] font-bold text-primary-500 dark:text-primary-400">{{ number_format($progress, 0) }}%</span>
                    </div>
                  </div>
                </td>
                <td class="text-right font-bold text-primary-900 dark:text-white text-xs">{{ $fmt($loanAmount) }}</td>
                <td class="text-right font-bold text-orange-600 dark:text-orange-400 text-xs">{{ $fmt($loanOutstanding) }}</td>
                <td class="text-right font-bold text-green-600 dark:text-green-400 text-xs">{{ $fmt($loanPaid) }}</td>
                <td class="text-right font-semibold text-primary-900 dark:text-white text-xs">{{ $fmt($loanInstallment) }}/mo</td>
                <td><span class="badge {{ $loanStatus['class'] }}">{{ $loanStatus['label'] }}</span></td>
                <td class="text-right whitespace-nowrap">
                  <a href="#" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-[11px] font-bold transition-colors">
                    <i class="fa-solid fa-eye text-[10px]"></i> Details
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-12 text-primary-500 dark:text-primary-400">
                  <i class="fa-solid fa-hand-holding-dollar text-3xl mb-3 block opacity-30"></i>
                  <p class="text-sm font-semibold mb-1">No loans found</p>
                  <p class="text-xs">This member has no active loan accounts</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div x-show="activeTab === 'savings'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(59,130,246,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
              <i class="fa-solid fa-wallet"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Current Balance</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($savingsBalance) }}</p>
        </div>
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(34,197,94,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 flex items-center justify-center">
              <i class="fa-solid fa-percent"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-green-600 dark:text-green-400">Interest Earned</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($savingsInterest) }}</p>
        </div>
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(139,92,246,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
              <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Running Balance</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($savingsRunning) }}</p>
        </div>
      </div>

      <div class="glass p-6">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-5 flex items-center gap-2">
          <i class="fa-solid fa-clock-rotate-left text-primary-500 text-xs"></i>
          Transaction History
        </h3>
        <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Running Balance</th>
              </tr>
            </thead>
            <tbody>
              @forelse($savingsTransactions as $tx)
                @php
                  $txDate = $tx['date'] ?? ($tx['Date'] ?? '-');
                  $txType = strtolower((string)($tx['type'] ?? ($tx['Type'] ?? 'deposit')));
                  $txAmount = $tx['amount'] ?? ($tx['Amount'] ?? 0);
                  $txBalance = $tx['running_balance'] ?? ($tx['RunningBalance'] ?? 0);
                  $isDeposit = str_contains($txType, 'deposit') || str_contains($txType, 'credit');
                @endphp
                <tr>
                  <td class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $txDate }}</td>
                  <td>
                    <span :class="[
                      '{{ $isDeposit }}' === '1'
                        ? 'badge badge-green'
                        : 'badge badge-red'
                    ]">
                      <i :class="'{{ $isDeposit }}' === '1' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'" class="mr-1 text-[9px]"></i>
                      {{ ucfirst($txType) }}
                    </span>
                  </td>
                  <td class="text-right">
                    <span class="text-xs font-bold {{ $isDeposit ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                      {{ $isDeposit ? '+' : '-' }}{{ $fmt($txAmount) }}
                    </span>
                  </td>
                  <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($txBalance) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-12 text-primary-500 dark:text-primary-400">
                    <i class="fa-solid fa-receipt text-3xl mb-3 block opacity-30"></i>
                    <p class="text-sm font-semibold mb-1">No transactions yet</p>
                    <p class="text-xs">Savings transaction history will appear here</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="glass p-6">
        <div class="flex items-center justify-between mb-5">
          <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-money-bill-trend-up text-purple-500 text-xs"></i>
            Fixed Deposits / Certificates
            <span class="badge badge-purple ml-2" style="background: #f3e8ff; color: #6b21a8;">{{ count($deposits) }} Active</span>
          </h3>
        </div>

        <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl">
          <table class="data-table">
            <thead>
              <tr>
                <th>Certificate #</th>
                <th>Product</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Interest %</th>
                <th>Start Date</th>
                <th>Maturity</th>
                <th>Status</th>
                <th class="text-right">Current Value</th>
              </tr>
            </thead>
            <tbody>
              @forelse($deposits as $dep)
                @php
                  $certNo = $dep['certificate_number'] ?? ($dep['CertificateNumber'] ?? '-');
                  $depProduct = $dep['product'] ?? ($dep['Product'] ?? '-');
                  $depAmount = $dep['amount'] ?? ($dep['Amount'] ?? 0);
                  $depInterest = $dep['interest'] ?? ($dep['Interest'] ?? 0);
                  $depStart = $dep['start_date'] ?? ($dep['StartDate'] ?? '-');
                  $depMaturity = $dep['maturity_date'] ?? ($dep['MaturityDate'] ?? '-');
                  $depCurrentValue = $dep['current_value'] ?? ($dep['CurrentValue'] ?? $depAmount);
                  $depStatus = $dashboardService->depositStatusBadge($dep['status'] ?? ($dep['Status'] ?? null));
                @endphp
                <tr>
                  <td class="font-mono text-xs font-bold text-purple-700 dark:text-purple-300">{{ $certNo }}</td>
                  <td class="text-sm font-semibold text-primary-900 dark:text-white">{{ $depProduct }}</td>
                  <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($depAmount) }}</td>
                  <td class="text-right">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 text-xs font-bold">
                      <i class="fa-solid fa-percent text-[10px]"></i>
                      {{ number_format((float)$depInterest, 2) }}%
                    </span>
                  </td>
                  <td class="text-xs font-mono text-primary-700 dark:text-primary-300">{{ $depStart }}</td>
                  <td class="text-xs font-mono text-primary-700 dark:text-primary-300">{{ $depMaturity }}</td>
                  <td><span class="badge {{ $depStatus['class'] }}">{{ $depStatus['label'] }}</span></td>
                  <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($depCurrentValue) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-12 text-primary-500 dark:text-primary-400">
                    <i class="fa-solid fa-money-bill-trend-up text-3xl mb-3 block opacity-30"></i>
                    <p class="text-sm font-semibold mb-1">No deposits found</p>
                    <p class="text-xs">This member has no fixed deposit certificates</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div x-show="activeTab === 'swf'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(236,72,153,0.1), rgba(236,72,153,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400 flex items-center justify-center">
              <i class="fa-solid fa-coins"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-pink-600 dark:text-pink-400">Total Contribution</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($swfTotal) }}</p>
        </div>
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(20,184,166,0.1), rgba(20,184,166,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 flex items-center justify-center">
              <i class="fa-solid fa-gift"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">Benefits Accrued</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($swfBenefits) }}</p>
        </div>
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(14,165,233,0.1), rgba(14,165,233,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center">
              <i class="fa-solid fa-chart-pie"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">Current Balance</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($swfBalance) }}</p>
        </div>
      </div>

      <div class="glass p-6">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-5 flex items-center gap-2">
          <i class="fa-solid fa-history text-pink-500 text-xs"></i>
          Contribution History
        </h3>
        <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Reference</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Balance</th>
              </tr>
            </thead>
            <tbody>
              @forelse($swfHistory as $contrib)
                @php
                  $cDate = $contrib['date'] ?? ($contrib['Date'] ?? '-');
                  $cType = $contrib['type'] ?? ($contrib['Type'] ?? 'Contribution');
                  $cDesc = $contrib['description'] ?? ($contrib['Description'] ?? 'SWF Contribution');
                  $cRef = $contrib['reference'] ?? ($contrib['Reference'] ?? '-');
                  $cAmount = $contrib['amount'] ?? ($contrib['Amount'] ?? 0);
                  $cBalance = $contrib['balance'] ?? ($contrib['Balance'] ?? 0);
                @endphp
                <tr>
                  <td class="text-xs font-mono text-primary-700 dark:text-primary-300">{{ $cDate }}</td>
                  <td><span class="badge badge-blue">{{ ucfirst((string)$cType) }}</span></td>
                  <td class="text-xs text-primary-700 dark:text-primary-300">{{ $cDesc }}</td>
                  <td class="text-xs font-mono text-primary-700 dark:text-primary-300">{{ $cRef }}</td>
                  <td class="text-right text-xs font-bold text-green-600 dark:text-green-400">+{{ $fmt($cAmount) }}</td>
                  <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($cBalance) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-12 text-primary-500 dark:text-primary-400">
                    <i class="fa-solid fa-shield-halved text-3xl mb-3 block opacity-30"></i>
                    <p class="text-sm font-semibold mb-1">No SWF contributions yet</p>
                    <p class="text-xs">Social Welfare Fund contribution history</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div x-show="activeTab === 'investments'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="glass p-6">
      <div class="flex items-center justify-between mb-5">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm flex items-center gap-2">
          <i class="fa-solid fa-chart-line text-lime-500 text-xs"></i>
          Investment Portfolio
          <span class="badge badge-green ml-2">{{ count($investments) }} Positions</span>
        </h3>
      </div>

      <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl mb-6">
        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th class="text-right">Amount Invested</th>
              <th class="text-right">Units</th>
              <th class="text-right">Current Value</th>
              <th class="text-right">Profit %</th>
              <th class="text-right">Return %</th>
            </tr>
          </thead>
          <tbody>
            @forelse($investments as $inv)
              @php
                $invProduct = $inv['product'] ?? ($inv['Product'] ?? '-');
                $invInvested = $inv['amount_invested'] ?? ($inv['AmountInvested'] ?? 0);
                $invUnits = $inv['units'] ?? ($inv['Units'] ?? 0);
                $invCurrent = $inv['current_value'] ?? ($inv['CurrentValue'] ?? 0);
                $invProfit = $inv['profit_earned'] ?? ($inv['ProfitEarned'] ?? 0);
                $invReturn = $inv['return_rate'] ?? ($inv['ReturnRate'] ?? 0);
                $invHistory = $inv['history'] ?? ($inv['History'] ?? []);
                $profitPct = $invInvested > 0 ? (($invCurrent - $invInvested) / $invInvested) * 100 : 0;
                $isProfit = $profitPct >= 0;
              @endphp
              <tr>
                <td class="text-sm font-semibold text-primary-900 dark:text-white">{{ $invProduct }}</td>
                <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($invInvested) }}</td>
                <td class="text-right text-xs font-mono font-bold text-primary-700 dark:text-primary-300">{{ $fmtInt($invUnits) }}</td>
                <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($invCurrent) }}</td>
                <td class="text-right">
                  <span class="text-xs font-bold {{ $isProfit ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    <i class="fa-solid {{ $isProfit ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} mr-1 text-[10px]"></i>
                    {{ $isProfit ? '+' : '' }}{{ number_format($profitPct, 2) }}%
                  </span>
                </td>
                <td class="text-right">
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg {{ $isProfit ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' }} text-xs font-bold">
                    {{ $isProfit ? '+' : '' }}{{ number_format((float)$invReturn, 2) }}%
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-12 text-primary-500 dark:text-primary-400">
                  <i class="fa-solid fa-chart-line text-3xl mb-3 block opacity-30"></i>
                  <p class="text-sm font-semibold mb-1">No investments found</p>
                  <p class="text-xs">This member has no active investment positions</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(!empty($investments) && !empty($investments[0]['history'] ?? $investments[0]['History'] ?? []))
        <div class="pt-5 border-t border-primary-100 dark:border-primary-900/50">
          <h4 class="font-bold text-primary-900 dark:text-white text-xs mb-4 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-primary-500"></i>
            Investment Transaction History
          </h4>
          <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Product</th>
                  <th>Type</th>
                  <th class="text-right">Units</th>
                  <th class="text-right">Value</th>
                </tr>
              </thead>
              <tbody>
                @foreach(($investments[0]['history'] ?? $investments[0]['History'] ?? []) as $hist)
                  @php
                    $hDate = $hist['date'] ?? ($hist['Date'] ?? '-');
                    $hProduct = $hist['product'] ?? ($hist['Product'] ?? '-');
                    $hType = $hist['type'] ?? ($hist['Type'] ?? '-');
                    $hUnits = $hist['units'] ?? ($hist['Units'] ?? 0);
                    $hValue = $hist['value'] ?? ($hist['Value'] ?? 0);
                  @endphp
                  <tr>
                    <td class="text-xs font-mono text-primary-700 dark:text-primary-300">{{ $hDate }}</td>
                    <td class="text-xs font-semibold text-primary-900 dark:text-white">{{ $hProduct }}</td>
                    <td><span class="badge badge-blue">{{ ucfirst((string)$hType) }}</span></td>
                    <td class="text-right text-xs font-mono font-bold text-primary-700 dark:text-primary-300">{{ $fmtInt($hUnits) }}</td>
                    <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($hValue) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div x-show="activeTab === 'shares'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(59,130,246,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
              <i class="fa-solid fa-certificate"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Total Shares</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmtInt($shares['total_shares'] ?? ($shares[0]['total_shares'] ?? 0)) }}</p>
        </div>
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(34,197,94,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 flex items-center justify-center">
              <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-green-600 dark:text-green-400">Share Value</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($shares['share_value'] ?? ($shares[0]['share_value'] ?? 0)) }}</p>
        </div>
        <div class="glass p-5" style="background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(139,92,246,0.02));">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
              <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Total Value</p>
          </div>
          <p class="text-2xl font-bold text-primary-900 dark:text-white">{{ $fmt($shares['total_value'] ?? ($shares[0]['total_value'] ?? 0)) }}</p>
        </div>
      </div>

      <div class="glass p-6">
        <h3 class="font-bold text-primary-900 dark:text-white text-sm mb-5 flex items-center gap-2">
          <i class="fa-solid fa-history text-blue-500 text-xs"></i>
          Share Transaction History
        </h3>
        <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-xl">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Share Type</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Price per Share</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Balance</th>
              </tr>
            </thead>
            <tbody>
              @forelse($shares['transactions'] ?? ($shares[0]['transactions'] ?? []) as $tx)
                @php
                  $txDate = $tx['date'] ?? ($tx['Date'] ?? '-');
                  $txType = $tx['type'] ?? ($tx['Type'] ?? 'Purchase');
                  $txShareType = $tx['share_type'] ?? ($tx['ShareType'] ?? 'Ordinary');
                  $txQty = $tx['quantity'] ?? ($tx['Quantity'] ?? 0);
                  $txPrice = $tx['price_per_share'] ?? ($tx['PricePerShare'] ?? 0);
                  $txAmount = $tx['total_amount'] ?? ($tx['TotalAmount'] ?? 0);
                  $txBalance = $tx['balance'] ?? ($tx['Balance'] ?? 0);
                  $isPurchase = str_contains(strtolower((string)$txType), 'purchase') || str_contains(strtolower((string)$txType), 'buy');
                @endphp
                <tr>
                  <td class="text-xs font-mono text-primary-700 dark:text-primary-300">{{ $txDate }}</td>
                  <td>
                    <span :class="[
                      '{{ $isPurchase }}' === '1'
                        ? 'badge badge-green'
                        : 'badge badge-red'
                    ]">
                      <i :class="'{{ $isPurchase }}' === '1' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'" class="mr-1 text-[9px]"></i>
                      {{ ucfirst((string)$txType) }}
                    </span>
                  </td>
                  <td class="text-xs text-primary-700 dark:text-primary-300">{{ $txShareType }}</td>
                  <td class="text-right text-xs font-mono font-bold text-primary-700 dark:text-primary-300">{{ $fmtInt($txQty) }}</td>
                  <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmt($txPrice) }}</td>
                  <td class="text-right">
                    <span class="text-xs font-bold {{ $isPurchase ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                      {{ $isPurchase ? '+' : '-' }}{{ $fmt($txAmount) }}
                    </span>
                  </td>
                  <td class="text-right text-xs font-bold text-primary-900 dark:text-white">{{ $fmtInt($txBalance) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-12 text-primary-500 dark:text-primary-400">
                    <i class="fa-solid fa-certificate text-3xl mb-3 block opacity-30"></i>
                    <p class="text-sm font-semibold mb-1">No share transactions yet</p>
                    <p class="text-xs">Share purchase and sale history will appear here</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Membership Certificate Preview Modal -->
<div id="membershipCertificateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-auto">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Membership Certificate Preview</h3>
        <button onclick="closeMembershipModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>
      <div class="flex gap-3 mb-4">
        <button onclick="printMembershipCertificate()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold transition-all">
          <i class="fa-solid fa-print"></i> Print
        </button>
        <button onclick="downloadMembershipCertificatePDF()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-semibold transition-all">
          <i class="fa-solid fa-file-pdf"></i> Download PDF
        </button>
      </div>
      <div id="membershipCertificatePreview" class="border border-gray-200 dark:border-gray-700 rounded-lg p-8">
        <!-- Certificate content will be loaded here -->
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function memberShow() {
    return {
      activeTab: 'profile',
      tabs: [
        { id: 'profile', label: 'Profile', icon: 'fa-solid fa-user', badge: null },
        { id: 'loans', label: 'Loans', icon: 'fa-solid fa-hand-holding-dollar', badge: {{ count($loans) }} },
        { id: 'savings', label: 'Savings & Deposits', icon: 'fa-solid fa-piggy-bank', badge: null },
        { id: 'swf', label: 'SWF', icon: 'fa-solid fa-shield-halved', badge: null },
        { id: 'investments', label: 'Investments', icon: 'fa-solid fa-chart-line', badge: {{ count($investments) }} },
        { id: 'shares', label: 'Shares', icon: 'fa-solid fa-certificate', badge: null },
      ],
      init() {
        const hash = window.location.hash.replace('#tab-', '');
        const validTabs = this.tabs.map(t => t.id);
        if (hash && validTabs.includes(hash)) {
          this.activeTab = hash;
        }
      },
      updateHash(tabId) {
        if (history.pushState) {
          history.pushState(null, null, '#tab-' + tabId);
        } else {
          window.location.hash = 'tab-' + tabId;
        }
      }
    };
  }

  // Membership Certificate Functions
  @php
  $settings = \Illuminate\Support\Facades\Cache::get('share_settings', []);
  $certificateBackgroundPath = $settings['certificate_background'] ?? '';
  $certificateBackgroundUrl = $certificateBackgroundPath ? asset('storage/' . $certificateBackgroundPath) : '';
  @endphp

  const memberData = {
    membercode: '{{ $memberNo }}',
    member_name: '{{ $memberName }}',
    registration_date: '{{ $memberRegDate }}',
    branch: '{{ $memberBranch }}',
    status: '{{ $memberStatus ?? 'Active' }}'
  };

  // Generate unique verification code
  const verificationCode = 'CERT-' + memberData.membercode.toUpperCase() + '-' + Math.random().toString(36).substring(2, 10).toUpperCase();
  const verificationUrl = window.location.origin + '/verify-certificate/' + verificationCode;

  const certificateBackgroundUrl = '{{ $certificateBackgroundUrl }}';

  function previewMembershipCertificate() {
    const modal = document.getElementById('membershipCertificateModal');
    const preview = document.getElementById('membershipCertificatePreview');
    
    let backgroundStyle = '';
    if (certificateBackgroundUrl) {
      backgroundStyle = `background-image: url('${certificateBackgroundUrl}'); background-size: cover; background-position: center; background-repeat: no-repeat;`;
    }
    
    // Generate QR code using API
    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(verificationUrl);

    preview.innerHTML = `
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
      <style>
        .great-vibes-regular {
          font-family: "Great Vibes", cursive;
          font-weight: 400;
          font-style: normal;
        }
      </style>
      <div style="${backgroundStyle} min-height: 500px; padding: 40px; position: relative;">
        <div style="padding: 40px; position: relative; z-index: 1;">
          <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="font-size: 32px; font-weight: bold; color: #1e40af; margin-bottom: 5px; font-family: 'Times New Roman', serif; text-shadow: 2px 2px 4px rgba(255,255,255,0.8);">CERTIFICATE OF MEMBERSHIP</h1>
          </div>
          
          <div style="text-align: center; margin-bottom: 30px;">
            <p style="color: #1f2937; font-size: 16px; margin-bottom: 10px; text-shadow: 1px 1px 2px rgba(255,255,255,0.8);">This is to certify that</p>
            <h2 class="great-vibes-regular" style="font-size: 36px; color: #1e40af; margin: 10px 0; text-shadow: 2px 2px 4px rgba(255,255,255,0.8);">${memberData.member_name}</h2>
            <div style="width: 350px; height: 2px; background: linear-gradient(to right, transparent, #1e40af, transparent); margin: 8px auto;"></div>
            <p style="color: #1f2937; font-size: 16px; text-shadow: 1px 1px 2px rgba(255,255,255,0.8);">is a registered and active member, holding <strong>Membership Number ${memberData.member_number}</strong>, with a registration date of <strong>${memberData.registration_date}</strong>. This certificate serves as official proof of membership and entitles the holder to the rights, privileges, and responsibilities of membership in accordance with the Constitution, By-laws, and Policies.</p>
          </div>
          
          <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid rgba(255,255,255,0.5);">
            <img src="${qrCodeUrl}" alt="QR Code" style="width: 60px; height: 60px; border: 2px solid rgba(255,255,255,0.5); border-radius: 8px; margin: 0 auto 15px;">
          </div>
          
          <div style="text-align: center; margin-top: 20px;">
            <p style="color: #1f2937; font-size: 14px; text-shadow: 1px 1px 2px rgba(255,255,255,0.8);">Issued by FEED TAN CMG SACCO on ${new Date().toLocaleDateString()}.</p>
          </div>
        </div>
      </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeMembershipModal() {
    const modal = document.getElementById('membershipCertificateModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  function printMembershipCertificate() {
    let backgroundStyle = '';
    if (certificateBackgroundUrl) {
      backgroundStyle = `background-image: url('${certificateBackgroundUrl}'); background-size: cover; background-position: center; background-repeat: no-repeat;`;
    }
    
    const content = document.getElementById('membershipCertificatePreview').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <html>
      <head>
        <title>Membership Certificate - ${memberData.member_number}</title>
        <style>
          body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
          @media print { body { margin: 0; } }
        </style>
      </head>
      <body>
        <div style="${backgroundStyle} min-height: 500px; padding: 40px; position: relative;">
          ${content}
        </div>
      </body>
      </html>
    `);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 500);
  }

  function downloadMembershipCertificatePDF() {
    let backgroundStyle = '';
    if (certificateBackgroundUrl) {
      backgroundStyle = `background-image: url('${certificateBackgroundUrl}'); background-size: cover; background-position: center; background-repeat: no-repeat;`;
    }
    
    const content = document.getElementById('membershipCertificatePreview').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <html>
      <head>
        <title>Membership Certificate - ${memberData.member_number}</title>
        <style>
          body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
          @media print { body { margin: 0; } }
        </style>
      </head>
      <body>
        <div style="${backgroundStyle} min-height: 500px; padding: 40px; position: relative;">
          ${content}
        </div>
      </body>
      </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
      printWindow.print();
      // Note: The user will need to select 'Save as PDF' in the print dialog
    }, 500);
  }
</script>
@endpush
