@extends('layouts.admin')

@section('breadcrumb', 'System \u203A Users')
@section('page_title', 'User Management')

@section('content')

<div x-data="usersList()" class="space-y-6">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1 max-w-2xl">
      <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1" x-ref="searchForm">
        <div class="relative">
          <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-primary-400"></i>
          <input type="text" name="q" value="{{ $searchQuery ?? '' }}"
                 placeholder="Search by name, email, member number, phone..."
                 class="form-input pl-9 py-2.5 text-sm"
                 x-model="searchQuery"
                 @input.debounce.400ms="$refs.searchForm.submit()"/>
          @if($searchQuery)
            <a href="{{ route('admin.users.index') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-400 hover:text-primary-600">
              <i class="fa-solid fa-xmark text-xs"></i>
            </a>
          @endif
        </div>
      </form>
    </div>

    <div class="flex items-center gap-3">
      <button @click="openBulkResetModal()"
              :disabled="selectedUsers.length === 0"
              :class="selectedUsers.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
        <i class="fa-solid fa-key text-[13px]"></i> Bulk Reset Password
      </button>
      <a href="{{ route('admin.users.create') }}"
         class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 whitespace-nowrap">
        <i class="fa-solid fa-user-plus text-[13px]"></i> Create User
      </a>
    </div>
  </div>

  <div class="glass p-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
      <div class="flex items-center gap-3">
        <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
          <i class="fa-solid fa-users mr-1.5"></i> {{ $users->total() }} Users Found
        </span>
        @if($searchQuery)
          <span class="badge badge-blue text-[10px]">Search: {{ $searchQuery }}</span>
        @endif
      </div>
      <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 text-xs text-primary-600 dark:text-primary-400">
          Per page:
          <select name="per_page" class="form-input py-1.5 px-2 w-20 text-xs" @change="changePerPage($el.value)">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
          </select>
        </label>
      </div>
    </div>

    <div class="overflow-x-auto -webkit-scrollbar [&::-webkit-scrollbar]:hidden rounded-2xl">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-12">
              <input type="checkbox" 
                     @change="toggleSelectAll($el.checked)"
                     :checked="allSelected"
                     class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            </th>
            <th class="w-12">#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Member #</th>
            <th>Status</th>
            <th>Created At</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $index => $user)
            @php
              $rowNum = ($users->currentPage() - 1) * $users->perPage() + $index + 1;
              $userRole = $user->role ?? ($user->roles->first()->name ?? 'member');
              $userStatus = $user->status ?? 'active';
              $encryptedId = app(\App\Services\EncryptedIdService::class)->encrypt($user->id);
            @endphp
            <tr class="group">
              <td class="text-xs text-primary-400 dark:text-primary-500 font-mono">
                <input type="checkbox" 
                       :value="'{{ $encryptedId }}'"
                       x-model="selectedUsers"
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
              </td>
              <td class="text-xs text-primary-400 dark:text-primary-500 font-mono">{{ $rowNum }}.</td>
              <td>
                <div class="flex items-center gap-3">
                  @if($user->photo)
                    <img src="{{ asset('storage/' . $user->photo) }}" 
                         alt="{{ $user->name }}" 
                         class="w-9 h-9 rounded-full object-cover flex-shrink-0 shadow-sm border-2 border-white dark:border-gray-700">
                  @else
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">
                      {{ strtoupper(substr($user->name, 0, 1) ?? 'U') }}
                    </div>
                  @endif
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary-900 dark:text-white truncate max-w-[200px]">{{ $user->name }}</p>
                    @if($user->phone)
                      <p class="text-[11px] text-primary-500 dark:text-primary-400 truncate max-w-[200px]">
                        <i class="fa-solid fa-phone text-[9px] mr-1"></i>{{ $user->phone }}
                      </p>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                <span class="text-xs text-primary-700 dark:text-primary-300 max-w-[200px] truncate block">{{ $user->email }}</span>
              </td>
              <td>
                @if($userRole === 'admin')
                  <span class="role-tag role-admin">Admin</span>
                @elseif($userRole === 'manager')
                  <span class="role-tag role-manager">Manager</span>
                @elseif($userRole === 'teller')
                  <span class="role-tag role-teller">Teller</span>
                @elseif($userRole === 'auditor')
                  <span class="role-tag role-auditor">Auditor</span>
                @else
                  <span class="role-tag role-member">Member</span>
                @endif
              </td>
              <td>
                @if($user->membercode)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-primary-50 dark:bg-primary-900/40 font-mono text-xs font-bold text-primary-700 dark:text-primary-300">
                    <i class="fa-solid fa-id-card text-[9px] opacity-60"></i>
                    {{ $user->membercode }}
                  </span>
                @else
                  <span class="text-xs text-primary-300 dark:text-primary-600 italic">-</span>
                @endif
              </td>
              <td>
                @if(strtolower((string)$userStatus) === 'active')
                  <span class="badge badge-green"><i class="fa-solid fa-circle-check text-[9px] mr-1"></i> Active</span>
                @elseif(strtolower((string)$userStatus) === 'inactive' || strtolower((string)$userStatus) === 'disabled')
                  <span class="badge badge-gray"><i class="fa-solid fa-circle-xmark text-[9px] mr-1"></i> Inactive</span>
                @elseif(strtolower((string)$userStatus) === 'pending')
                  <span class="badge badge-yellow"><i class="fa-solid fa-clock text-[9px] mr-1"></i> Pending</span>
                @else
                  <span class="badge badge-blue">{{ ucfirst($userStatus) }}</span>
                @endif
              </td>
              <td>
                <span class="text-xs text-primary-600 dark:text-primary-400 block">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                <span class="text-[10px] text-primary-400 dark:text-primary-600">{{ $user->created_at ? $user->created_at->format('H:i') : '' }}</span>
              </td>
              <td class="text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  @php
                    $encryptedId = app(\App\Services\EncryptedIdService::class)->encrypt($user->id);
                  @endphp
                  <button @click="openConfirmModal('{{ $encryptedId }}', '{{ addslashes($user->name) }}')"
                          class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 text-xs transition-colors border border-amber-200 dark:border-amber-800/40"
                          title="Reset Password">
                    <i class="fa-solid fa-key text-[10px]"></i>
                  </button>
                  <a href="{{ route('admin.users.edit', $encryptedId) }}"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-xs transition-colors"
                     title="Edit">
                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                  </a>
                  <form method="POST" action="{{ route('admin.users.destroy', $encryptedId) }}"
                        class="inline"
                        x-data
                        @submit.prevent="confirmDelete($el, '{{ addslashes($user->name) }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 text-xs transition-colors border border-red-200 dark:border-red-800/40"
                            title="Delete">
                      <i class="fa-solid fa-trash text-[10px]"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-16 text-primary-500 dark:text-primary-400">
                <i class="fa-solid fa-users-slash text-4xl mb-4 block opacity-30"></i>
                <p class="text-sm font-semibold mb-1">No users found</p>
                <p class="text-xs">
                  @if($searchQuery)
                    Try adjusting your search terms or
                  @endif
                  <a href="{{ route('admin.users.create') }}" class="text-primary-600 dark:text-primary-400 underline hover:no-underline">create a new user</a>
                </p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($users->hasPages())
      <div class="mt-6 pt-5 border-t border-primary-100 dark:border-primary-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="text-xs text-primary-600 dark:text-primary-400">
          Showing <span class="font-bold text-primary-900 dark:text-white">{{ $users->firstItem() ?? 0 }}</span> to
          <span class="font-bold text-primary-900 dark:text-white">{{ $users->lastItem() ?? 0 }}</span> of
          <span class="font-bold text-primary-900 dark:text-white">{{ $users->total() }}</span> users
        </p>

        <nav class="flex items-center justify-center gap-1" role="navigation" aria-label="Pagination Navigation">
          @if($users->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-300 dark:text-primary-700 bg-primary-50 dark:bg-primary-900/20 cursor-not-allowed">
              <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </span>
          @else
            <a href="{{ $users->appends(request()->query())->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 transition-colors">
              <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </a>
          @endif

          @php
            $start = max($users->currentPage() - 2, 1);
            $end = min($start + 4, $users->lastPage());
            if ($end - $start < 4) {
                $start = max($end - 4, 1);
            }
          @endphp

          @for($i = $start; $i <= $end; $i++)
            @if($i == $users->currentPage())
              <span class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-primary-600 shadow-sm">
                {{ $i }}
              </span>
            @else
              <a href="{{ $users->appends(request()->query())->url($i) }}"
                 class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-primary-700 dark:text-primary-300 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 transition-colors">
                {{ $i }}
              </a>
            @endif
          @endfor

          @if($users->hasMorePages())
            <a href="{{ $users->appends(request()->query())->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 transition-colors">
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
          @else
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-300 dark:text-primary-700 bg-primary-50 dark:bg-primary-900/20 cursor-not-allowed">
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </span>
          @endif
        </nav>
      </div>
    @endif
  </div>

  <!-- Bulk Password Reset Confirmation Modal -->
  <div x-show="showBulkResetModal" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
       style="display: none;">
    <div x-show="showBulkResetModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 max-w-lg w-full mx-4">
      <div class="text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
          <i class="fa-solid fa-key text-2xl text-amber-600 dark:text-amber-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Bulk Reset Password?</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Are you sure you want to reset passwords for <span class="font-bold text-amber-600 dark:text-amber-400" x-text="selectedUsers.length"></span> selected users?
        </p>
        <div class="flex gap-3">
          <button @click="closeBulkResetModal()"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-bold transition-colors">
            Cancel
          </button>
          <button @click="confirmBulkReset()"
                  :disabled="isBulkResetting"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fa-solid fa-key mr-2"></i> 
            <span x-show="!isBulkResetting">Reset Passwords</span>
            <span x-show="isBulkResetting">Resetting...</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bulk Password Reset Results Modal -->
  <div x-show="showBulkResultsModal" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
       style="display: none;">
    <div x-show="showBulkResultsModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
      <div>
        <div class="text-center mb-6">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
            <i class="fa-solid fa-check text-2xl text-green-600 dark:text-green-400"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Password Reset Results</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            <span class="font-bold text-green-600 dark:text-green-400" x-text="bulkResults.success_count"></span> successful, 
            <span class="font-bold text-red-600 dark:text-red-400" x-text="bulkResults.failure_count"></span> failed
          </p>
        </div>
        <div class="space-y-3 max-h-96 overflow-y-auto">
          <template x-for="result in bulkResults.results" :key="result.user_id">
            <div class="p-3 rounded-lg border" 
                 :class="result.success ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'">
              <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-sm text-gray-900 dark:text-white" x-text="result.user_name || 'Unknown'"></span>
                <span class="text-xs px-2 py-1 rounded-full"
                      :class="result.success ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'">
                  <i :class="result.success ? 'fa-solid fa-check' : 'fa-solid fa-xmark'" class="mr-1"></i>
                  <span x-text="result.success ? 'Success' : 'Failed'"></span>
                </span>
              </div>
              <div x-show="result.success" class="flex items-center justify-between">
                <span class="text-xs text-gray-600 dark:text-gray-400">New Password:</span>
                <div class="flex items-center gap-2">
                  <span class="font-mono font-bold text-primary-600 dark:text-primary-400" x-text="result.new_password"></span>
                  <button @click="copyPassword(result.new_password)" 
                          class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                    <i class="fa-solid fa-copy"></i>
                  </button>
                </div>
              </div>
              <div x-show="!result.success" class="text-xs text-red-600 dark:text-red-400" x-text="result.error"></div>
            </div>
          </template>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="closeBulkResultsModal()"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-bold transition-colors">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Password Reset Confirmation Modal -->
  <div x-show="showConfirmModal" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
       style="display: none;">
    <div x-show="showConfirmModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4">
      <div class="text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
          <i class="fa-solid fa-key text-2xl text-amber-600 dark:text-amber-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Reset Password?</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
          Are you sure you want to reset the password for <span class="font-semibold text-gray-900 dark:text-white" x-text="userName"></span>?
        </p>
        <div class="flex gap-3">
          <button @click="closeConfirmModal()"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-bold transition-colors">
            Cancel
          </button>
          <button @click="confirmReset()"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-colors">
            <i class="fa-solid fa-key mr-2"></i> Reset Password
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Password Reset Modal -->
  <div x-show="showPasswordModal" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
       style="display: none;">
    <div x-show="showPasswordModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4">
      <div class="text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
          <i class="fa-solid fa-check text-2xl text-green-600 dark:text-green-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Password Reset Successful</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          New password for <span class="font-semibold text-gray-900 dark:text-white" x-text="userName"></span>
        </p>
        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 mb-4">
          <p class="text-2xl font-mono font-bold text-primary-600 dark:text-primary-400 tracking-wider" x-text="newPassword"></p>
        </div>
        <div class="flex gap-3">
          <button @click="copyPassword()"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-colors">
            <i class="fa-solid fa-copy mr-2"></i> Copy Password
          </button>
          <button @click="closePasswordModal()"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-bold transition-colors">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function usersList() {
    return {
      searchQuery: @json($searchQuery ?? ''),
      showPasswordModal: false,
      showConfirmModal: false,
      showBulkResetModal: false,
      showBulkResultsModal: false,
      isBulkResetting: false,
      newPassword: '',
      userName: '',
      userIdToReset: null,
      selectedUsers: [],
      allSelected: false,
      bulkResults: { results: [], success_count: 0, failure_count: 0 },
      changePerPage(value) {
        const params = new URLSearchParams(window.location.search);
        params.set('per_page', value);
        params.delete('page');
        window.location.href = window.location.pathname + '?' + params.toString();
      },
      confirmDelete(form, userName) {
        if (confirm('Are you sure you want to delete user "' + userName + '"? This action cannot be undone.')) {
          form.submit();
        }
      },
      openConfirmModal(userId, userName) {
        this.userIdToReset = userId;
        this.userName = userName;
        this.showConfirmModal = true;
      },
      closeConfirmModal() {
        this.showConfirmModal = false;
        this.userIdToReset = null;
        this.userName = '';
      },
      async confirmReset() {
        this.showConfirmModal = false;
        
        try {
          const response = await fetch('/admin/users/' + this.userIdToReset + '/reset-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            }
          });
          
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Server returned an unexpected response. Please check the logs.'
            });
            this.userIdToReset = null;
            return;
          }
          
          const data = await response.json();
          
          if (data.success) {
            this.newPassword = data.new_password;
            this.userName = data.user_name;
            this.showPasswordModal = true;
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: 'Password reset successfully and sent to email.',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to reset password'
            });
          }
        } catch (error) {
          console.error('Password reset error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error resetting password: ' + error.message
          });
        }
        
        this.userIdToReset = null;
      },
      copyPassword(password) {
        navigator.clipboard.writeText(password);
        alert('Password copied to clipboard!');
      },
      closePasswordModal() {
        this.showPasswordModal = false;
        this.newPassword = '';
        this.userName = '';
      },
      toggleSelectAll(checked) {
        this.allSelected = checked;
        if (checked) {
          const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
          this.selectedUsers = Array.from(checkboxes).map(cb => cb.value);
        } else {
          this.selectedUsers = [];
        }
      },
      openBulkResetModal() {
        if (this.selectedUsers.length === 0) {
          alert('Please select at least one user to reset passwords.');
          return;
        }
        this.showBulkResetModal = true;
      },
      closeBulkResetModal() {
        this.showBulkResetModal = false;
      },
      async confirmBulkReset() {
        this.isBulkResetting = true;
        
        try {
          const response = await fetch('/admin/users/bulk-reset-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              user_ids: this.selectedUsers
            })
          });
          
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Server returned an unexpected response. Please check the logs.'
            });
            this.isBulkResetting = false;
            return;
          }
          
          const data = await response.json();
          
          if (data.success) {
            this.bulkResults = {
              results: data.results,
              success_count: data.success_count,
              failure_count: data.failure_count
            };
            this.showBulkResetModal = false;
            this.showBulkResultsModal = true;
            this.selectedUsers = [];
            this.allSelected = false;
            
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: `Password reset completed. ${data.success_count} successful, ${data.failure_count} failed. Emails sent to all successful resets.`,
              timer: 3000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Failed to reset passwords'
            });
          }
        } catch (error) {
          console.error('Bulk password reset error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error resetting passwords: ' + error.message
          });
        }
        
        this.isBulkResetting = false;
      },
      closeBulkResultsModal() {
        this.showBulkResultsModal = false;
        this.bulkResults = { results: [], success_count: 0, failure_count: 0 };
      }
    }
  }
</script>
@endpush

@endsection
