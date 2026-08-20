@extends('layouts.app')

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
               x-text="user ? user.name.charAt(0).toUpperCase() : 'M'"></div>
        @endif
        <div class="min-w-0 flex-1">
          <p class="text-white text-xs font-semibold truncate" x-text="user ? user.name : 'Member'"></p>
          <span class="role-tag role-member mt-1 inline-block">Member</span>
        </div>
      </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

      <a href="{{ route('member.dashboard') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge-high w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Dashboard</span>
      </a>

      <a href="{{ route('member.profile.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.profile.*') ? 'active' : '' }}">
        <i class="fa-solid fa-user w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Profile</span>
      </a>

      <div x-show="!sidebarCollapsed">
        <p class="text-primary-500 text-[10px] font-bold uppercase tracking-widest px-3 pt-4 pb-1">My Accounts</p>
      </div>

      <a href="{{ route('member.loans.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.loans.*') ? 'active' : '' }}">
        <i class="fa-solid fa-hand-holding-dollar w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">My Loans</span>
      </a>

      <a href="{{ route('member.savings.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.savings.*') ? 'active' : '' }}">
        <i class="fa-solid fa-piggy-bank w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">My Savings</span>
      </a>

      <a href="{{ route('member.saving-plan.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.saving-plan.*') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-line w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Saving Plan</span>
      </a>

      <a href="{{ route('member.swf.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.swf.*') ? 'active' : '' }}">
        <i class="fa-solid fa-shield-halved w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">My SWF</span>
      </a>

      <a href="{{ route('member.investments.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.investments.*') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-line w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">My Investments</span>
      </a>

      @if(auth()->check() && auth()->user()->sharePurchases()->exists())
      <a href="{{ route('member.shares.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.shares.*') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-pie w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">My Shares</span>
      </a>
      @endif

      <div x-show="!sidebarCollapsed">
        <p class="text-primary-500 text-[10px] font-bold uppercase tracking-widest px-3 pt-4 pb-1">More</p>
      </div>

      <a href="{{ route('member.statements.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.statements.*') ? 'active' : '' }}">
        <i class="fa-solid fa-file-invoice-dollar w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Statements</span>
      </a>

      <a href="{{ route('member.notifications.index') }}"
         class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-200 hover:text-white transition-all duration-150
                {{ request()->routeIs('member.notifications.*') ? 'active' : '' }}">
        <i class="fa-solid fa-bell w-4 text-center flex-shrink-0"></i>
        <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap">Notifications</span>
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
          <a href="{{ route('member.dashboard') }}" class="font-medium transition-colors"
             :class="darkMode ? 'text-primary-400 hover:text-primary-300' : 'text-primary-500 hover:text-primary-600'">FEEDTAN DIGITAL</a>
          <i class="fa-solid fa-chevron-right text-[10px]" :class="darkMode ? 'text-primary-700' : 'text-primary-300'"></i>
          <span class="font-semibold" :class="darkMode ? 'text-primary-200' : 'text-primary-800'">@yield('breadcrumb', 'Member Dashboard')</span>
        </div>
      </div>

      <div class="flex items-center gap-2">

        <button @click="toggleDarkMode()" class="p-2 rounded-lg transition-colors"
                :class="darkMode ? 'text-primary-300 hover:bg-primary-900' : 'text-primary-700 hover:bg-primary-50'"
                :title="darkMode ? 'Light Mode' : 'Dark Mode'">
          <i :class="darkMode ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="text-sm"></i>
        </button>

        <div class="relative" x-data="{ open: false, notifications: [], unreadCount: 0 }" x-init="fetchNotifications()">
          <button @click="open = !open" class="relative p-2 rounded-lg transition-colors"
                  :class="darkMode ? 'text-primary-300 hover:bg-primary-900/50' : 'text-primary-700 hover:bg-primary-100'">
            <i class="fa-solid fa-bell text-sm"></i>
            <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" 
                  class="notif-dot flex items-center justify-center text-[10px] font-bold text-white" 
                  style="top:6px;right:6px;"></span>
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
              <h3 class="font-bold text-sm" :class="darkMode ? 'text-white' : 'text-primary-900'">My Notifications</h3>
              <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" 
                    class="badge badge-green text-[10px]">New</span>
            </div>
            <div class="max-h-72 overflow-y-auto">
              <template x-if="notifications.length === 0">
                <div class="p-6 text-center">
                  <div class="w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-400 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-bell-slash text-lg"></i>
                  </div>
                  <p class="text-sm" :class="darkMode ? 'text-primary-400' : 'text-gray-500'">No notifications</p>
                </div>
              </template>
              <template x-for="notif in notifications.slice(0, 5)" :key="notif.id">
                <a @click="markAsRead(notif.id)" :href="'{{ route('member.notifications.index') }}'" 
                   class="p-3 border-b transition-colors cursor-pointer block"
                   :class="darkMode ? 'border-[#1a3328] hover:bg-primary-900/30' : 'border-primary-50 hover:bg-primary-50'">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs"
                         :class="getNotificationIconClass(notif.category)">
                      <i :class="getNotificationIcon(notif.category)"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <p class="text-xs font-semibold truncate" :class="darkMode ? 'text-white' : 'text-primary-900'" x-text="notif.title"></p>
                        <span x-show="notif.is_unread" class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"></span>
                      </div>
                      <p class="text-[11px] mt-0.5 line-clamp-2" :class="darkMode ? 'text-primary-400' : 'text-gray-500'" x-text="notif.message"></p>
                      <p class="text-[10px] mt-1 text-primary-500" x-text="formatDate(notif.date)"></p>
                    </div>
                  </div>
                </a>
              </template>
            </div>
            <div class="p-3">
              <a href="{{ route('member.notifications.index') }}" class="w-full text-center text-xs text-primary-500 hover:text-primary-400 font-semibold py-1 block">View all notifications →</a>
            </div>
          </div>
        </div>

        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl transition-colors"
                  :class="darkMode ? 'hover:bg-primary-900/50' : 'hover:bg-primary-100'">
            @if(auth()->check() && auth()->user()->photo)
              <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="Profile" class="w-9 h-9 rounded-full object-cover shadow-sm">
            @else
              <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm"
                   x-text="user ? user.name.charAt(0).toUpperCase() : 'M'"></div>
            @endif
            <div class="hidden lg:block text-left">
              <p class="text-xs font-semibold leading-tight" :class="darkMode ? 'text-white' : 'text-primary-900'"
                 x-text="user ? user.name : 'Member'"></p>
              <p class="text-[10px] font-mono" :class="darkMode ? 'text-primary-400' : 'text-primary-500'"
                 x-text="user && user.membercode ? user.membercode : 'FTN-00001'"></p>
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
                 x-text="user ? user.name : 'Member User'"></p>
              <p class="text-[11px] mt-0.5 font-mono" :class="darkMode ? 'text-primary-400' : 'text-gray-500'"
                 x-text="user && user.membercode ? 'Member No: ' + user.membercode : 'Member No: FTN-00001'"></p>
              <p class="text-[11px] mt-0.5" :class="darkMode ? 'text-primary-400' : 'text-gray-500'"
                 x-text="user ? user.email : 'member@feedtan.co.tz'"></p>
              <span class="role-tag role-member mt-2 inline-block">Member</span>
            </div>
            <a href="{{ route('member.profile.index') }}" @click="open=false"
               class="w-full flex items-center gap-3 px-4 py-2.5 text-xs transition-colors text-left"
               :class="darkMode ? 'text-primary-300 hover:bg-primary-900/30' : 'text-gray-700 hover:bg-primary-50'">
              <i class="fa-solid fa-user w-4"></i> My Profile
            </a>
            <a href="{{ route('member.statements.index') }}" @click="open=false"
               class="w-full flex items-center gap-3 px-4 py-2.5 text-xs transition-colors text-left"
               :class="darkMode ? 'text-primary-300 hover:bg-primary-900/30' : 'text-gray-700 hover:bg-primary-50'">
              <i class="fa-solid fa-file-invoice w-4"></i> My Statements
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
            <p class="text-sm mt-1" :class="darkMode ? 'text-primary-400' : 'text-primary-600'"
               x-text="user ? 'Welcome back, ' + user.name.split(' ')[0] : 'Welcome back'">
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

@endsection

@push('scripts')
<script>
function fetchNotifications() {
  fetch('{{ route('member.notifications.index') }}')
    .then(response => response.json())
    .then(data => {
      this.notifications = data.notifications || [];
      this.unreadCount = data.unread_count || 0;
    })
    .catch(error => {
      console.error('Error fetching notifications:', error);
    });
}

function markAsRead(notificationId) {
  fetch(`{{ route('member.notifications.read', ':id') }}`.replace(':id', notificationId), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    // Update local state
    const notif = this.notifications.find(n => n.id === notificationId);
    if (notif) {
      notif.is_read = true;
      notif.is_unread = false;
      this.unreadCount = Math.max(0, this.unreadCount - 1);
    }
  })
  .catch(error => {
    console.error('Error marking notification as read:', error);
  });
}

function getNotificationIcon(category) {
  const icons = {
    'announcement': 'fa-bullhorn',
    'loan': 'fa-hand-holding-dollar',
    'general': 'fa-circle-info',
  };
  return icons[category] || 'fa-circle-info';
}

function getNotificationIconClass(category) {
  const classes = {
    'announcement': 'bg-blue-900/40 text-blue-400',
    'loan': 'bg-orange-900/40 text-orange-400',
    'general': 'bg-green-900/40 text-green-400',
  };
  return classes[category] || 'bg-blue-900/40 text-blue-400';
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
</script>
@endpush
