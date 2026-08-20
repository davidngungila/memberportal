@extends('layouts.admin')

@section('breadcrumb', 'System \u203A Users \u203A Details')
@section('page_title', 'User Details')

@section('content')
<div class="space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('admin.users.index') }}"
       class="p-2.5 rounded-xl bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-primary-900 dark:text-white">{{ $user->name }}</h1>
      <p class="text-sm text-primary-600 dark:text-primary-400">{{ $user->email }}</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
      <div class="glass p-6 rounded-2xl">
        <h2 class="font-bold text-primary-900 dark:text-white text-sm mb-4">User Information</h2>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Name</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $user->name }}</p>
            </div>
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Email</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $user->email }}</p>
            </div>
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Role</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ ucfirst($user->role) }}</p>
            </div>
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Status</p>
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $user->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                {{ ucfirst($user->status) }}
              </span>
            </div>
            @if($user->membercode)
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Member Number</p>
              <p class="text-sm font-mono font-semibold text-primary-900 dark:text-white">{{ $user->membercode }}</p>
            </div>
            @endif
            @if($user->memberType)
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Member Type</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $user->memberType->name }}</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      @if($user->memberProfile)
      <div class="glass p-6 rounded-2xl mt-6">
        <h2 class="font-bold text-primary-900 dark:text-white text-sm mb-4">Member Profile</h2>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Full Name</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $user->memberProfile->full_name }}</p>
            </div>
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Gender</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ ucfirst($user->memberProfile->gender) }}</p>
            </div>
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Phone</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $user->memberProfile->phone_number }}</p>
            </div>
            <div>
              <p class="text-xs text-primary-600 dark:text-primary-400">Registration Date</p>
              <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $user->memberProfile->registration_date ? $user->memberProfile->registration_date->format('M d, Y') : '—' }}</p>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>

    <div class="lg:col-span-1">
      <div class="glass p-6 rounded-2xl">
        <h2 class="font-bold text-primary-900 dark:text-white text-sm mb-4">Actions</h2>
        <div class="space-y-3">
          <a href="{{ route('admin.users.edit', $encryptedId) }}"
             class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors">
            <i class="fa-solid fa-pen"></i> Edit User
          </a>
          <form method="POST" action="{{ route('admin.users.destroy', $encryptedId) }}"
                x-data
                @submit.prevent="confirmDelete($el, '{{ addslashes($user->name) }}')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition-colors">
              <i class="fa-solid fa-trash"></i> Delete User
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
