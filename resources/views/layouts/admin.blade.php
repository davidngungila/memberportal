@extends('layouts.app')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('layout_content')

<div class="flex h-screen overflow-hidden" x-data="{ loading: false }">

  <!-- Loading Overlay -->
  <div x-show="loading" x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
       class="fixed inset-0 bg-black/30 backdrop-blur-sm z-[100] flex items-center justify-center">
    <div class="bg-white dark:bg-dark-bg rounded-2xl shadow-2xl p-8 flex flex-col items-center gap-4">
      <div class="w-12 h-12 border-4 border-primary-200 dark:border-primary-800 border-t-primary-600 rounded-full animate-spin"></div>
      <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Loading data...</p>
    </div>
  </div>

  <div x-show="sidebarOpen" @click="sidebarOpen=false"
       class="mobile-overlay lg:hidden" x-transition:enter="transition-opacity duration-200"
       x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity duration-200"
       x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

  <aside :class="[
    sidebarOpen ? 'mobile-open translate-x-0' : 'lg:translate-x-0 -translate-x-full',
    'sidebar sidebar-bg h-screen z-50 flex flex-col flex-shrink-0',
    sidebarCollapsed && window.innerWidth >= 1024 ? 'sidebar-collapsed' : 'sidebar-expanded'
  ]">

    <div class="flex items-center justify-between p-4 border-b border-primary-800/50 flex-shrink-0">
      <div class="flex items-center gap-3" x-show="!sidebarCollapsed || window.innerWidth < 1024">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0 shadow-lg">
          <i class="fa-solid fa-leaf text-primary-900 text-sm"></i>
        </div>
        <div x-show="!sidebarCollapsed">
          <p class="text-white font-bold text-sm leading-tight">FEEDTAN</p>
          <p class="text-primary-300 text-[10px] font-semibold tracking-wide">DIGITAL</p>
        </div>
      </div>
      <div x-show="sidebarCollapsed && window.innerWidth >= 1024" class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center mx-auto">
        <i class="fa-solid fa-leaf text-primary-900 text-sm"></i>
      </div>
      <button @click="toggleSidebar()" class="text-primary-300 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-primary-800/40 hidden lg:block">
        <i :class="sidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'" class="text-xs"></i>
      </button>
    </div>

    <div class="p-3 border-b border-primary-800/50 flex-shrink-0" x-show="!sidebarCollapsed" x-transition>
      <div class="flex items-center gap-3 p-2.5 rounded-xl bg-primary-800/30">
        @if(auth()->check() && auth()->user()->photo)
          <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="Profile" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
        @else
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
               x-text="user ? user.name.charAt(0).toUpperCase() : 'A'"></div>
        @endif
        <div class="min-w-0 flex-1">
          <p class="text-white text-xs font-semibold truncate" x-text="user ? user.name : 'Admin User'"></p>
          <span class="role-tag role-admin mt-1 inline-block" x-text="roleLabel(user ? user.role : 'admin')"></span>
        </div>
      </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5" x-ref="adminNav">

      <a href="{{ route('admin.dashboard') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150 group
                {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge-high w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Dashboard</span>
      </a>

      <a href="{{ route('admin.notifications.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150 group
                {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
        <i class="fa-solid fa-bell w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Notifications</span>
      </a>

      <div x-show="!sidebarCollapsed">
        <p class="text-primary-500 text-[10px] font-bold uppercase tracking-widest px-3 pt-4 pb-1">Members Management</p>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.members.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
          <i class="fa-solid fa-users w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Members</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.members.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.members.index') ? 'active' : '' }}">
            <i class="fa-solid fa-list w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">All Members</span>
          </a>
          <a href="{{ route('admin.members.index') }}?status=active"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150">
            <i class="fa-solid fa-user-check w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Active Members</span>
          </a>
          <a href="{{ route('admin.members.index') }}?status=inactive"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150">
            <i class="fa-solid fa-user-xmark w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Inactive Members</span>
          </a>
          <a href="{{ route('admin.member-types.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.member-types.*') ? 'active' : '' }}">
            <i class="fa-solid fa-user-tag w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Member Types</span>
          </a>
          <a href="{{ route('admin.membership-applications.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.membership-applications.*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-circle-check w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Member Applications</span>
          </a>
        </div>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.loans.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.loans.*') ? 'active' : '' }}">
          <i class="fa-solid fa-hand-holding-dollar w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Loans</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.loans.applications') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.loans.applications') ? 'active' : '' }}">
            <i class="fa-solid fa-file-signature w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Loan Applications</span>
          </a>
          <a href="{{ route('admin.loans.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.loans.index') && request('status') !== 'pending' ? 'active' : '' }}">
            <i class="fa-solid fa-list-check w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Active Loans</span>
          </a>
          <a href="{{ route('admin.loans.index', ['status' => 'pending']) }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.loans.index') && request('status') === 'pending' ? 'active' : '' }}">
            <i class="fa-solid fa-spinner w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Processing Loans</span>
          </a>
          <a href="{{ route('admin.loan-products.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.loan-products.*') ? 'active' : '' }}">
            <i class="fa-solid fa-box w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Loan Products</span>
          </a>
        </div>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.savings.*') || request()->routeIs('admin.deposits.*') || request()->routeIs('admin.saving-plans.*') || request()->routeIs('admin.transactions.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.savings.*') || request()->routeIs('admin.deposits.*') || request()->routeIs('admin.saving-plans.*') || request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
          <i class="fa-solid fa-piggy-bank w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Savings & Deposits</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.products.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="fa-solid fa-box-open w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Product Available</span>
          </a>
          <a href="{{ route('admin.savings.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.savings.*') ? 'active' : '' }}">
            <i class="fa-solid fa-wallet w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Savings Accounts</span>
          </a>
          <a href="{{ route('admin.saving-plans.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.saving-plans.*') ? 'active' : '' }}">
            <i class="fa-solid fa-bullseye w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Saving Plan</span>
          </a>
          <a href="{{ route('admin.transactions.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
            <i class="fa-solid fa-receipt w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Transactions</span>
          </a>
          <a href="{{ route('admin.statements.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.statements.*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-invoice w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Statement</span>
          </a>
        </div>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.swf.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.swf.*') ? 'active' : '' }}">
          <i class="fa-solid fa-shield-halved w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">SWF</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.swf.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.swf.index') ? 'active' : '' }}">
            <i class="fa-solid fa-list w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">All SWF</span>
          </a>
          <a href="{{ route('admin.swf.index') }}?status=active"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150">
            <i class="fa-solid fa-user-check w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Active SWF</span>
          </a>
          <a href="{{ route('admin.swf.index') }}?status=matured"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150">
            <i class="fa-solid fa-calendar-check w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Matured SWF</span>
          </a>
          <a href="{{ route('admin.swf.benefits.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.swf.benefits.*') ? 'active' : '' }}">
            <i class="fa-solid fa-gift w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Benefits</span>
          </a>
        </div>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.investments.*') || request()->routeIs('admin.investment-products.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.investments.*') || request()->routeIs('admin.investment-products.*') ? 'active' : '' }}">
          <i class="fa-solid fa-chart-line w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Investments</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.investment-products.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.investment-products.*') ? 'active' : '' }}">
            <i class="fa-solid fa-box-open w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Investment Products</span>
          </a>
          <a href="{{ route('admin.investments.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.investments.index') ? 'active' : '' }}">
            <i class="fa-solid fa-list w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">All Investments</span>
          </a>
          <a href="{{ route('admin.investments.index') }}?status=active"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150">
            <i class="fa-solid fa-chart-line w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Active Investments</span>
          </a>
          <a href="{{ route('admin.investments.index') }}?status=matured"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150">
            <i class="fa-solid fa-calendar-check w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Matured Investments</span>
          </a>
        </div>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.shares.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.shares.*') ? 'active' : '' }}">
          <i class="fa-solid fa-building-columns w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Shares</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.share-products.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-products.*') ? 'active' : '' }}">
            <i class="fa-solid fa-box w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Share Products</span>
          </a>
          <a href="{{ route('admin.share-purchases.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-purchases.*') ? 'active' : '' }}">
            <i class="fa-solid fa-cart-shopping w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Share Purchases</span>
          </a>
          <a href="{{ route('admin.share-certificates.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-certificates.*') ? 'active' : '' }}">
            <i class="fa-solid fa-certificate w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Share Certificates</span>
          </a>
          <a href="{{ route('admin.share-transfers.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-transfers.*') ? 'active' : '' }}">
            <i class="fa-solid fa-arrow-right-arrow-left w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Share Transfers</span>
          </a>
          <a href="{{ route('admin.share-dividends.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-dividends.*') ? 'active' : '' }}">
            <i class="fa-solid fa-money-bill-trend-up w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Share Dividends</span>
          </a>
          <a href="{{ route('admin.share-transactions.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-transactions.*') ? 'active' : '' }}">
            <i class="fa-solid fa-exchange-alt w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Share Transactions</span>
          </a>
          <a href="{{ route('admin.share-reports.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-reports.*') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-bar w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Share Reports</span>
          </a>
          <a href="{{ route('admin.share-settings.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.share-settings.*') ? 'active' : '' }}">
            <i class="fa-solid fa-gear w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Settings</span>
          </a>
        </div>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.accounting.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.accounting.*') ? 'active' : '' }}">
          <i class="fa-solid fa-calculator w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Accounting</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.accounts.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-sitemap w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Chart of Accounts</span>
          </a>
          <a href="{{ route('admin.journal-entries.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.journal-entries.*') ? 'active' : '' }}">
            <i class="fa-solid fa-book w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Journal Entries</span>
          </a>
          <a href="{{ route('admin.ledger.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.ledger.*') ? 'active' : '' }}">
            <i class="fa-solid fa-list w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">General Ledger</span>
          </a>
          <a href="{{ route('admin.trial-balance.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.trial-balance.*') ? 'active' : '' }}">
            <i class="fa-solid fa-balance-scale w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Trial Balance</span>
          </a>
          <a href="{{ route('admin.balance-sheet.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.balance-sheet.*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-invoice-dollar w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Balance Sheet</span>
          </a>
          <a href="{{ route('admin.income-statement.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.income-statement.*') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-line w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Income Statement</span>
          </a>
          <a href="{{ route('admin.cash-flow.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.cash-flow.*') ? 'active' : '' }}">
            <i class="fa-solid fa-money-bill-transfer w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Cash Flow</span>
          </a>
          <a href="{{ route('admin.bank-accounts.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.bank-accounts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building-columns w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Bank Accounts</span>
          </a>
          <a href="{{ route('admin.fixed-assets.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.fixed-assets.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Fixed Assets</span>
          </a>
          <a href="{{ route('admin.receipts.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.receipts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-receipt w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Receipts</span>
          </a>
          <a href="{{ route('admin.payments.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="fa-solid fa-money-bill-wave w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Payments</span>
          </a>
          <a href="{{ route('admin.expenses.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-invoice w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Expenses</span>
          </a>
          <a href="{{ route('admin.revenues.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.revenues.*') ? 'active' : '' }}">
            <i class="fa-solid fa-coins w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Revenues</span>
          </a>
        </div>
      </div>

      <div x-show="!sidebarCollapsed">
        <p class="text-primary-500 text-[10px] font-bold uppercase tracking-widest px-3 pt-4 pb-1">Reports</p>
      </div>

      <a href="{{ route('admin.reports.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <i class="fa-solid fa-file-lines w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Reports</span>
      </a>

      <div x-data="{ open: {{ request()->routeIs('admin.google-sheets.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.google-sheets.*') ? 'active' : '' }}">
          <i class="fa-brands fa-google w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Google Sheets</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.google-sheets.index') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.google-sheets.index') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Dashboard</span>
          </a>
          <a href="{{ route('admin.google-sheets.customers') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.google-sheets.customers') ? 'active' : '' }}">
            <i class="fa-solid fa-users w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Customers</span>
          </a>
          <a href="{{ route('admin.google-sheets.logs') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.google-sheets.logs') ? 'active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Sync Logs</span>
          </a>
        </div>
      </div>

      <div x-show="!sidebarCollapsed">
        <p class="text-primary-500 text-[10px] font-bold uppercase tracking-widest px-3 pt-4 pb-1">Communication</p>
      </div>

      <div x-data="{ open: {{ request()->routeIs('admin.communication.*') ? 'true' : 'false' }} }" class="sidebar-dropdown">
        <button @click="open = !open"
                class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                       {{ request()->routeIs('admin.communication.*') ? 'active' : '' }}">
          <i class="fa-solid fa-comments w-4 text-center flex-shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap flex-1 text-left">Communication</span>
          <i x-show="!sidebarCollapsed" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" class="fa-solid text-[10px] transition-transform"></i>
        </button>
        <div x-show="open" x-transition class="mt-1 space-y-0.5 pl-6">
          <a href="{{ route('admin.communication.sms') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.communication.sms') ? 'active' : '' }}">
            <i class="fa-solid fa-message w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">SMS</span>
          </a>
          <a href="{{ route('admin.communication.email') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.communication.email') ? 'active' : '' }}">
            <i class="fa-solid fa-envelope w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Email</span>
          </a>
          <a href="{{ route('admin.communication.whatsapp') }}"
             class="sidebar-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary-300 hover:text-white transition-all duration-150
                    {{ request()->routeIs('admin.communication.whatsapp') ? 'active' : '' }}">
            <i class="fa-brands fa-whatsapp w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">WhatsApp</span>
          </a>
        </div>
      </div>

      <div x-show="!sidebarCollapsed">
        <p class="text-primary-500 text-[10px] font-bold uppercase tracking-widest px-3 pt-4 pb-1">System</p>
      </div>

      <a href="{{ route('admin.users.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <i class="fa-solid fa-user-gear w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Users</span>
      </a>

      <a href="{{ route('admin.staff.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
        <i class="fa-solid fa-id-badge w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Staff</span>
      </a>

      <a href="{{ route('admin.roles.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
        <i class="fa-solid fa-user-shield w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Roles</span>
      </a>

      <a href="{{ route('admin.permissions.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
        <i class="fa-solid fa-key w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Permissions</span>
      </a>

      <a href="{{ route('admin.settings.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <i class="fa-solid fa-gear w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Settings</span>
      </a>

      <a href="{{ route('admin.activity-logs.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
        <i class="fa-solid fa-list-check w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Activity Logs</span>
      </a>

      <div class="pt-3 mt-3 border-t border-primary-800/40">
        <form method="POST" action="{{ route('logout') }}" class="w-full" x-data>
          @csrf
          <button type="submit"
                  class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-300 hover:bg-red-900/40 hover:text-red-400 transition-all duration-150">
            <i class="fa-solid fa-right-from-bracket w-4 text-center flex-shrink-0"></i>
            <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Logout</span>
          </button>
        </form>
      </div>
    </nav>
  </aside>

  <div class="flex-1 flex flex-col overflow-hidden main-content">

    <header class="navbar-bg flex items-center justify-between px-4 lg:px-6 h-16 flex-shrink-0 relative z-30">

      <div class="flex items-center gap-3">
        <button @click="toggleSidebar()" class="p-2 rounded-lg transition-colors"
                :class="darkMode ? 'text-primary-300 hover:bg-primary-900' : 'text-primary-700 hover:bg-primary-50'">
          <i class="fa-solid fa-bars text-sm"></i>
        </button>
        <div class="hidden sm:flex items-center gap-2 text-xs">
          <a href="{{ route('admin.dashboard') }}" class="font-medium transition-colors"
             :class="darkMode ? 'text-primary-400 hover:text-primary-300' : 'text-primary-500 hover:text-primary-600'">FEEDTAN DIGITAL</a>
          <i class="fa-solid fa-chevron-right text-[10px]" :class="darkMode ? 'text-primary-700' : 'text-primary-300'"></i>
          <span class="font-semibold" :class="darkMode ? 'text-primary-200' : 'text-primary-800'">@yield('breadcrumb', 'Dashboard')</span>
        </div>
      </div>

      <div class="flex items-center gap-2">

        <button @click="toggleDarkMode()" class="p-2 rounded-lg transition-colors"
                :class="darkMode ? 'text-primary-300 hover:bg-primary-900' : 'text-primary-700 hover:bg-primary-50'"
                :title="darkMode ? 'Light Mode' : 'Dark Mode'">
          <i :class="darkMode ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="text-sm"></i>
        </button>

        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="relative p-2 rounded-lg transition-colors"
                  :class="darkMode ? 'text-primary-300 hover:bg-primary-900/50' : 'text-primary-700 hover:bg-primary-100'">
            <i class="fa-solid fa-bell text-sm"></i>
            <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 rounded-full border-2 border-white dark:border-primary-900 text-[9px] font-bold text-white flex items-center justify-center">3</span>
          </button>
          <div x-show="open" @click.away="open=false" x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 scale-95 translate-y-1"
               x-transition:enter-end="opacity-100 scale-100 translate-y-0"
               x-transition:leave="transition ease-in duration-100"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="absolute right-0 top-12 w-80 rounded-2xl shadow-2xl z-50 overflow-hidden"
               :class="darkMode ? 'bg-[#0d1f16] border border-[#1a3328]' : 'bg-white border border-primary-100'">
            <div class="p-4 border-b flex justify-between items-center"
                 :class="darkMode ? 'border-[#1a3328]' : 'border-primary-100'">
              <h3 class="font-bold text-sm" :class="darkMode ? 'text-white' : 'text-primary-900'">Notifications</h3>
              <span class="badge badge-green text-[10px]">3 Unread</span>
            </div>
            <div class="max-h-72 overflow-y-auto">
              <div class="p-3 border-b transition-colors cursor-pointer"
                   :class="darkMode ? 'border-[#1a3328] hover:bg-primary-900/30' : 'border-primary-50 hover:bg-primary-50'">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-green-900/40 text-green-400 flex items-center justify-center flex-shrink-0 text-xs">
                    <i class="fa-solid fa-arrow-down"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold truncate" :class="darkMode ? 'text-white' : 'text-primary-900'">New Deposit Received</p>
                    <p class="text-[11px] mt-0.5" :class="darkMode ? 'text-primary-400' : 'text-gray-500'">TZS 250,000 from John M.</p>
                    <p class="text-[10px] mt-1 text-primary-500">2 minutes ago</p>
                  </div>
                  <div class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0 mt-1"></div>
                </div>
              </div>
              <div class="p-3 border-b transition-colors cursor-pointer"
                   :class="darkMode ? 'border-[#1a3328] hover:bg-primary-900/30' : 'border-primary-50 hover:bg-primary-50'">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-yellow-900/40 text-yellow-400 flex items-center justify-center flex-shrink-0 text-xs">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold truncate" :class="darkMode ? 'text-white' : 'text-primary-900'">Loan Application Pending</p>
                    <p class="text-[11px] mt-0.5" :class="darkMode ? 'text-primary-400' : 'text-gray-500'">Sarah K. applied for TZS 5M</p>
                    <p class="text-[10px] mt-1 text-primary-500">15 minutes ago</p>
                  </div>
                  <div class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0 mt-1"></div>
                </div>
              </div>
              <div class="p-3 border-b transition-colors cursor-pointer"
                   :class="darkMode ? 'border-[#1a3328] hover:bg-primary-900/30' : 'border-primary-50 hover:bg-primary-50'">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-blue-900/40 text-blue-400 flex items-center justify-center flex-shrink-0 text-xs">
                    <i class="fa-solid fa-user-plus"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold truncate" :class="darkMode ? 'text-white' : 'text-primary-900'">New Member Registered</p>
                    <p class="text-[11px] mt-0.5" :class="darkMode ? 'text-primary-400' : 'text-gray-500'">David L. joined from Arusha</p>
                    <p class="text-[10px] mt-1 text-primary-500">1 hour ago</p>
                  </div>
                  <div class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0 mt-1"></div>
                </div>
              </div>
              <div class="p-3 border-b transition-colors cursor-pointer opacity-60"
                   :class="darkMode ? 'border-[#1a3328] hover:bg-primary-900/30' : 'border-primary-50 hover:bg-primary-50'">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-purple-900/40 text-purple-400 flex items-center justify-center flex-shrink-0 text-xs">
                    <i class="fa-solid fa-chart-line"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold truncate" :class="darkMode ? 'text-white' : 'text-primary-900'">Monthly Report Ready</p>
                    <p class="text-[11px] mt-0.5" :class="darkMode ? 'text-primary-400' : 'text-gray-500'">June financial summary generated</p>
                    <p class="text-[10px] mt-1 text-primary-500">3 hours ago</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="p-3">
              <a href="#" class="w-full text-center text-xs text-primary-500 hover:text-primary-400 font-semibold py-1 block">View all notifications →</a>
            </div>
          </div>
        </div>

        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl transition-colors"
                  :class="darkMode ? 'hover:bg-primary-900/50' : 'hover:bg-primary-100'">
            @if(auth()->check() && auth()->user()->photo)
              <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="Profile" class="w-9 h-9 rounded-full object-cover shadow-sm">
            @else
              <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-700 flex items-center justify-center text-white font-bold text-sm shadow-sm"
                   x-text="user ? user.name.charAt(0).toUpperCase() : 'A'"></div>
            @endif
            <div class="hidden lg:block text-left">
              <p class="text-xs font-semibold leading-tight" :class="darkMode ? 'text-white' : 'text-primary-900'"
                 x-text="user ? user.name : 'Admin User'"></p>
              <p class="text-[10px]" :class="darkMode ? 'text-primary-400' : 'text-primary-500'"
                 x-text="roleLabel(user ? user.role : 'admin')"></p>
            </div>
            <i class="fa-solid fa-chevron-down text-[10px] hidden lg:block" :class="darkMode ? 'text-primary-400' : 'text-primary-400'"></i>
          </button>
          <div x-show="open" @click.away="open=false" x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 scale-95 translate-y-1"
               x-transition:enter-end="opacity-100 scale-100 translate-y-0"
               x-transition:leave="transition ease-in duration-100"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="absolute right-0 top-12 w-60 rounded-2xl shadow-2xl z-50 py-2"
               :class="darkMode ? 'bg-[#0d1f16] border border-[#1a3328]' : 'bg-white border border-primary-100'">
            <div class="px-4 py-3 border-b" :class="darkMode ? 'border-[#1a3328]' : 'border-primary-100'">
              <p class="text-xs font-bold" :class="darkMode ? 'text-white' : 'text-primary-900'"
                 x-text="user ? user.name : 'Admin User'"></p>
              <p class="text-[11px] mt-0.5" :class="darkMode ? 'text-primary-400' : 'text-gray-500'"
                 x-text="user ? user.email : 'admin@feedtan.co.tz'"></p>
              <span class="role-tag role-admin mt-2 inline-block" x-text="roleLabel(user ? user.role : 'admin')"></span>
            </div>
            <a href="{{ route('admin.settings.index') }}" @click="open=false"
               class="w-full flex items-center gap-3 px-4 py-2.5 text-xs transition-colors text-left"
               :class="darkMode ? 'text-primary-300 hover:bg-primary-900/30' : 'text-gray-700 hover:bg-primary-50'">
              <i class="fa-solid fa-gear w-4"></i> Settings
            </a>
            <a href="{{ route('admin.profile.show') }}" @click="open=false"
               class="w-full flex items-center gap-3 px-4 py-2.5 text-xs transition-colors text-left"
               :class="darkMode ? 'text-primary-300 hover:bg-primary-900/30' : 'text-gray-700 hover:bg-primary-50'">
              <i class="fa-solid fa-user w-4"></i> My Profile
            </a>
            <div class="border-t my-1" :class="darkMode ? 'border-[#1a3328]' : 'border-primary-100'"></div>
            <form method="POST" action="{{ route('logout') }}" class="w-full" x-data>
              @csrf
              <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-left">
                <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
              </button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 overflow-y-auto p-4 lg:p-6" :class="darkMode ? 'bg-[#0a140e]' : 'bg-[#f0fdf4]'">

      @hasSection('page-header')
        <div class="mb-6">
          @yield('page-header')
        </div>
      @else
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
          <div>
            <h1 class="text-xl lg:text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-primary-900'">
              @yield('page_title', 'Dashboard')
            </h1>
            <p class="text-sm mt-1" :class="darkMode ? 'text-primary-400' : 'text-primary-600'">
              Manage and oversee all cooperative operations
            </p>
          </div>
        </div>
      @endif

      <div class="animate-[fadeIn_0.4s_ease]">
        @yield('content')
      </div>
    </main>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    @if(session()->has('flash'))
      @php
        $flash = session()->get('flash');
        $level = $flash['level'] ?? 'info';
        $message = $flash['message'] ?? '';
      @endphp
      @if($level === 'success')
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '{{ $message }}',
          confirmButtonColor: '#059669',
          timer: 3000,
          timerProgressBar: true
        });
      @elseif($level === 'error')
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: '{{ $message }}',
          confirmButtonColor: '#dc2626',
          timer: 5000,
          timerProgressBar: true
        });
      @elseif($level === 'warning')
        Swal.fire({
          icon: 'warning',
          title: 'Warning',
          text: '{{ $message }}',
          confirmButtonColor: '#d97706',
          timer: 4000,
          timerProgressBar: true
        });
      @elseif($level === 'info')
        Swal.fire({
          icon: 'info',
          title: 'Information',
          text: '{{ $message }}',
          confirmButtonColor: '#2563eb',
          timer: 3000,
          timerProgressBar: true
        });
      @endif
    @endif

    @if(session()->has('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session()->get('success') }}',
        confirmButtonColor: '#059669',
        timer: 3000,
        timerProgressBar: true
      });
    @endif

    @if(session()->has('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ session()->get('error') }}',
        confirmButtonColor: '#dc2626',
        timer: 5000,
        timerProgressBar: true
      });
    @endif

    @if(session()->has('warning'))
      Swal.fire({
        icon: 'warning',
        title: 'Warning',
        text: '{{ session()->get('warning') }}',
        confirmButtonColor: '#d97706',
        timer: 4000,
        timerProgressBar: true
      });
    @endif

    @if(session()->has('info'))
      Swal.fire({
        icon: 'info',
        title: 'Information',
        text: '{{ session()->get('info') }}',
        confirmButtonColor: '#2563eb',
        timer: 3000,
        timerProgressBar: true
      });
    @endif

    @if($errors->any())
      Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: '<ul class="text-left">' + @foreach($errors->all() as $error) '<li>{{ $error }}</li>' @endforeach + '</ul>',
        confirmButtonColor: '#dc2626'
      });
    @endif
  });
</script>

@endsection
