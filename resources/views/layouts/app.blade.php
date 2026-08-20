<!DOCTYPE html>
<html lang="en" x-data="appState()" class="h-full" :class="{ 'dark': darkMode }">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>FEEDTAN DIGITAL - @yield('page_title', 'Dashboard')</title>

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          primary: {
            50:'#ecfdf5',
            100:'#d1fae5',
            200:'#a7f3d0',
            300:'#6ee7b7',
            400:'#34d399',
            500:'#10b981',
            600:'#059669',
            700:'#047857',
            800:'#065f46',
            900:'#064e3b',
            950:'#022c22'
          },
          dark: {
            800:'#0f1a14',
            850:'#111f17',
            900:'#0a140e',
            card:'#0d1f16',
            border:'#1a3328'
          }
        },
        fontFamily: {
          sans: ['Plus Jakarta Sans', 'sans-serif']
        },
        animation: {
          'fade-in':'fadeIn 0.4s ease',
          'slide-in':'slideIn 0.3s ease',
          'pulse-slow':'pulse 3s infinite',
          'spin-slow':'spin 4s linear infinite'
        },
        keyframes: {
          fadeIn:{from:{opacity:0,transform:'translateY(10px)'},to:{opacity:1,transform:'translateY(0)'}},
          slideIn:{from:{opacity:0,transform:'translateX(-20px)'},to:{opacity:1,transform:'translateX(0)'}}
        }
      }
    }
  }
</script>

<style>
*, *::before, *::after { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }

::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #10b981; border-radius: 10px; }

body { color: #064e3b; background: #f0fdf4; }
.dark body { color: #d1fae5; background: #0a140e; }

.card { background: #ffffff; border: 1px solid #d1fae5; box-shadow: 0 2px 12px rgba(6,78,59,0.08); border-radius: 14px; }
.dark .card { background: #0d1f16; border: 1px solid #1a3328; box-shadow: 0 2px 12px rgba(0,0,0,0.3); }

.sidebar-bg { background: #064e3b; }
.navbar-bg { background: #ffffff; border-bottom: 1px solid #d1fae5; }
.dark .navbar-bg { background: #0d1f16; border-bottom: 1px solid #1a3328; }

.glass {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  background: rgba(255,255,255,0.85);
  border: 1px solid rgba(209,250,229,0.8);
  border-radius: 14px;
}
.dark .glass {
  background: rgba(13,31,22,0.85);
  border: 1px solid rgba(26,51,40,0.8);
}

.sidebar { transition: width 0.3s cubic-bezier(0.4,0,0.2,1), transform 0.3s cubic-bezier(0.4,0,0.2,1); }
.sidebar-collapsed { width: 64px !important; }
.sidebar-expanded { width: 260px !important; }

.sidebar-item { transition: background 0.15s, color 0.15s; }
.sidebar-item:hover { background: rgba(255,255,255,0.1); }
.sidebar-item.active { background: #059669; color: #fff; }

.badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:999px; font-size:11px; font-weight:600; letter-spacing:0.4px; }
.badge-green { background:#d1fae5; color:#065f46; }
.badge-red { background:#fee2e2; color:#991b1b; }
.badge-yellow { background:#fef9c3; color:#854d0e; }
.badge-blue { background:#dbeafe; color:#1e40af; }
.badge-gray { background:#f3f4f6; color:#4b5563; }
.dark .badge-green { background:#052e16; color:#6ee7b7; }
.dark .badge-red { background:#450a0a; color:#fca5a5; }
.dark .badge-yellow { background:#422006; color:#fde68a; }
.dark .badge-blue { background:#172554; color:#93c5fd; }
.dark .badge-gray { background:#1f2937; color:#9ca3af; }

.stat-card {
  border-radius:14px;
  padding:20px 22px;
  transition:transform 0.2s, box-shadow 0.2s;
  position:relative;
  overflow:hidden;
}
.stat-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(16,185,129,0.18); }
.stat-card .icon-wrap { width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px; }
.stat-card .bg-blob { position:absolute;right:-20px;top:-20px;width:100px;height:100px;border-radius:50%;opacity:0.08; }

.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; padding:10px 14px; text-align:left; }
.data-table td { padding:10px 14px; font-size:13px; }
.data-table tbody tr { transition:background 0.15s; border-bottom:1px solid transparent; }
.data-table th { color:#065f46; background:#ecfdf5; border-bottom:1px solid #d1fae5; }
.data-table tbody tr { border-bottom-color:#f0fdf4; }
.data-table tbody tr:hover { background:#f0fdf4; }
.dark .data-table th { color:#6ee7b7; background:#052e16; border-bottom:1px solid #1a3328; }
.dark .data-table tbody tr { border-bottom-color:#1a3328; }
.dark .data-table tbody tr:hover { background:#0d2318; }

.toast-container {
  position:fixed;
  bottom:24px;
  right:24px;
  z-index:9999;
  display:flex;
  flex-direction:column;
  gap:10px;
}
.toast {
  display:flex;
  align-items:center;
  gap:10px;
  padding:12px 18px;
  border-radius:12px;
  font-size:13px;
  font-weight:500;
  box-shadow:0 8px 24px rgba(0,0,0,0.2);
  animation:fadeIn 0.3s ease;
  min-width:240px;
}
.toast-success { background:#064e3b; color:#6ee7b7; border:1px solid #065f46; }
.toast-error { background:#450a0a; color:#fca5a5; border:1px solid #991b1b; }
.toast-info { background:#172554; color:#93c5fd; border:1px solid #1e40af; }

.progress-bar { height:6px; border-radius:99px; background:#d1fae5; overflow:hidden; }
.dark .progress-bar { background:#1a3328; }
.progress-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,#10b981,#34d399); transition:width 1s ease; }

.form-input {
  width:100%;
  padding:9px 14px;
  border-radius:8px;
  font-size:13px;
  outline:none;
  transition:border-color 0.2s, box-shadow 0.2s;
  font-family:'Plus Jakarta Sans',sans-serif;
  border:1px solid #d1fae5;
  background:#f9fafb;
  color:#064e3b;
}
.form-input:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,0.15); }
.dark .form-input { background:#0a140e; border-color:#1a3328; color:#fff; }
.form-label { font-size:12px; font-weight:600; margin-bottom:4px; display:block; }

.role-tag { padding:3px 10px; border-radius:999px; font-size:10px; font-weight:700; letter-spacing:0.5px; }
.role-admin { background:#059669; color:#fff; }
.role-manager { background:#3b82f6; color:#fff; }
.role-teller { background:#f59e0b; color:#fff; }
.role-member { background:#6366f1; color:#fff; }
.role-auditor { background:#ef4444; color:#fff; }

.notif-dot { width:8px;height:8px;border-radius:50%;background:#ef4444;position:absolute;top:2px;right:2px;animation:pulse 1.5s infinite; }

@keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

@media(max-width:1024px) {
  .sidebar { position:fixed!important; z-index:50; height:100vh; top:0; left:0; transform:translateX(-100%); }
  .sidebar.mobile-open { transform:translateX(0)!important; width:260px!important; }
  .main-content { margin-left:0!important; }
  .mobile-overlay { display:block; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:40; backdrop-filter:blur(2px); }
}
@media(min-width:1024px) {
  .mobile-overlay { display:none; }
}
</style>

@stack('styles')
</head>
<body class="h-full font-sans">

<div class="toast-container" x-ref="toastContainer">
  @if(session('flash') && is_array(session('flash')))
    @foreach(session('flash') as $msg)
      @if(is_array($msg))
        <div class="toast toast-{{ $msg['level'] ?? 'info' }}" x-init="setTimeout(() => $el.remove(), 5000)">
          <i class="fa-solid fa-{{ $msg['level'] === 'success' ? 'check-circle' : ($msg['level'] === 'error' ? 'exclamation-circle' : 'info-circle') }}"></i>
          <span>{{ $msg['message'] }}</span>
          <button @click="$el.remove()" class="ml-auto opacity-70 hover:opacity-100"><i class="fa-solid fa-xmark text-xs"></i></button>
        </div>
      @endif
    @endforeach
  @endif
</div>

@php
    $authUser = auth()->check() ? auth()->user() : null;
    $userData = $user ?? ($authUser ? array_merge($authUser->only(['id', 'name', 'role', 'membercode', 'branch']), ['email' => $authUser->email, 'phone' => $authUser->phone]) : null);
@endphp

<script>
  function appState() {
    return {
      sidebarOpen: false,
      sidebarCollapsed: false,
      darkMode: false,
      user: @json($userData),

      init() {
        this.sidebarCollapsed = window.innerWidth >= 1024 && localStorage.getItem('sidebarCollapsed') === 'true';
        this.darkMode = localStorage.getItem('darkMode') === 'true';
        if (this.darkMode) document.documentElement.classList.add('dark');

        window.addEventListener('resize', () => {
          if (window.innerWidth < 1024) {
            this.sidebarOpen = false;
          }
        });
      },

      toggleSidebar() {
        if (window.innerWidth < 1024) {
          this.sidebarOpen = !this.sidebarOpen;
        } else {
          this.sidebarCollapsed = !this.sidebarCollapsed;
          localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
        }
      },

      toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        document.documentElement.classList.toggle('dark', this.darkMode);
      },

      showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle');
        toast.innerHTML = '<i class="fa-solid fa-' + icon + '"></i><span>' + message + '</span><button class="ml-auto opacity-70 hover:opacity-100"><i class="fa-solid fa-xmark text-xs"></i></button>';
        toast.querySelector('button').addEventListener('click', () => toast.remove());
        this.$refs.toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
      },

      roleLabel(role) {
        const labels = { admin:'Admin', manager:'Manager', teller:'Teller', member:'Member', auditor:'Auditor' };
        return labels[role] || role;
      }
    }
  }
</script>

@yield('layout_content')

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: {!! json_encode(session('success')) !!},
            confirmButtonColor: '#059669',
            confirmButtonText: 'OK',
            timer: 5000,
            timerProgressBar: true,
        });
    @endif
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: {!! json_encode(session('error')) !!},
            confirmButtonColor: '#059669',
            confirmButtonText: 'OK',
        });
    @endif
    @if(session('info'))
        Swal.fire({
            icon: 'info',
            title: 'Info',
            text: {!! json_encode(session('info')) !!},
            confirmButtonColor: '#059669',
            confirmButtonText: 'OK',
        });
    @endif
    @if(session('warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: {!! json_encode(session('warning')) !!},
            confirmButtonColor: '#059669',
            confirmButtonText: 'OK',
        });
    @endif
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: '<ul style="text-align:left;font-size:13px;margin:0;padding-left:16px;">@foreach($errors->all() as $error)<li>{!! nl2br(e($error)) !!}</li>@endforeach</ul>',
            confirmButtonColor: '#059669',
            confirmButtonText: 'OK',
            width: '400px',
        });
    @endif
});
</script>

@stack('scripts')
</body>
</html>
