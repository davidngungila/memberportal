@extends('layouts.admin')

@section('breadcrumb', 'Staff Management')
@section('page_title', 'Staff Management')

@section('content')

<div class="space-y-6">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1 max-w-2xl">
      <form method="GET" action="{{ route('admin.staff.index') }}" class="flex-1">
        <div class="relative">
          <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-primary-400"></i>
          <input type="text" name="q" value="{{ $searchQuery ?? '' }}"
                 placeholder="Search by name, staff number, phone, department..."
                 class="form-input pl-9 py-2.5 text-sm"/>
          @if($searchQuery)
            <a href="{{ route('admin.staff.index') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-400 hover:text-primary-600">
              <i class="fa-solid fa-xmark text-xs"></i>
            </a>
          @endif
        </div>
      </form>
      <select name="status" class="form-input py-2.5 text-sm" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        @foreach(\App\Models\Staff::STATUSES as $s)
          <option value="{{ $s }}" {{ ($statusFilter ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
      <select name="department" class="form-input py-2.5 text-sm" onchange="this.form.submit()">
        <option value="">All Departments</option>
        @foreach($departments as $dept)
          <option value="{{ $dept }}" {{ ($departmentFilter ?? '') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
        @endforeach
      </select>
    </div>

    <a href="{{ route('admin.staff.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95 whitespace-nowrap">
      <i class="fa-solid fa-user-plus text-[13px]"></i> Add Staff
    </a>
  </div>

  <div class="glass p-5">
    <div class="flex items-center gap-3 mb-5">
      <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
        <i class="fa-solid fa-id-badge mr-1.5"></i> {{ $staff->total() }} Staff Found
      </span>
      @if($searchQuery)
        <span class="badge badge-blue text-[10px]">Search: {{ $searchQuery }}</span>
      @endif
    </div>

    <div class="overflow-x-auto rounded-2xl">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-12">#</th>
            <th>Staff #</th>
            <th>Name</th>
            <th>Department</th>
            <th>Position</th>
            <th>Phone</th>
            <th>Member Link</th>
            <th>Status</th>
            <th>Hired</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($staff as $index => $s)
            @php
              $rowNum = ($staff->currentPage() - 1) * $staff->perPage() + $index + 1;
              $encryptedId = app(\App\Services\EncryptedIdService::class)->encrypt($s->id);
            @endphp
            <tr class="group">
              <td class="text-xs text-primary-400 font-mono">{{ $rowNum }}.</td>
              <td>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 font-mono text-xs font-bold text-indigo-700 dark:text-indigo-300">
                  {{ $s->staff_number }}
                </span>
              </td>
              <td>
                <div class="flex items-center gap-3">
                  @if($s->photo)
                    <img src="{{ asset('storage/' . $s->photo) }}" alt="" class="w-9 h-9 rounded-full object-cover flex-shrink-0 shadow-sm border-2 border-white dark:border-gray-700">
                  @else
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">
                      {{ strtoupper(substr($s->full_name, 0, 1)) }}
                    </div>
                  @endif
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary-900 dark:text-white truncate max-w-[200px]">{{ $s->full_name }}</p>
                    @if($s->email)
                      <p class="text-[11px] text-primary-500 dark:text-primary-400 truncate max-w-[200px]">
                        <i class="fa-solid fa-envelope text-[9px] mr-1"></i>{{ $s->email }}
                      </p>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                <span class="text-xs text-primary-700 dark:text-primary-300">{{ $s->department ?? '—' }}</span>
              </td>
              <td>
                <span class="text-xs text-primary-700 dark:text-primary-300">{{ $s->position ?? '—' }}</span>
              </td>
              <td>
                <span class="text-xs text-primary-700 dark:text-primary-300">{{ $s->phone ?? '—' }}</span>
              </td>
              <td>
                @if($s->member)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-primary-50 dark:bg-primary-900/40 font-mono text-xs font-bold text-primary-700 dark:text-primary-300">
                    <i class="fa-solid fa-link text-[9px] opacity-60"></i>
                    {{ $s->member->membercode }}
                  </span>
                @else
                  <span class="text-xs text-primary-300 dark:text-primary-600 italic">Not linked</span>
                @endif
              </td>
              <td>
                @if($s->status === 'active')
                  <span class="badge badge-green"><i class="fa-solid fa-circle-check text-[9px] mr-1"></i> Active</span>
                @elseif($s->status === 'inactive')
                  <span class="badge badge-gray"><i class="fa-solid fa-circle-xmark text-[9px] mr-1"></i> Inactive</span>
                @elseif($s->status === 'suspended')
                  <span class="badge badge-yellow"><i class="fa-solid fa-clock text-[9px] mr-1"></i> Suspended</span>
                @elseif($s->status === 'terminated')
                  <span class="badge badge-red"><i class="fa-solid fa-ban text-[9px] mr-1"></i> Terminated</span>
                @else
                  <span class="badge badge-blue">{{ ucfirst($s->status) }}</span>
                @endif
              </td>
              <td>
                <span class="text-xs text-primary-600 dark:text-primary-400">{{ $s->hire_date ? $s->hire_date->format('d M Y') : '—' }}</span>
              </td>
              <td class="text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  <a href="{{ route('admin.staff.show', $encryptedId) }}"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 text-xs transition-colors"
                     title="View">
                    <i class="fa-solid fa-eye text-[10px]"></i>
                  </a>
                  <a href="{{ route('admin.staff.edit', $encryptedId) }}"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 text-xs transition-colors border border-amber-200 dark:border-amber-800/40"
                     title="Edit">
                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                  </a>
                  <form method="POST" action="{{ route('admin.staff.destroy', $encryptedId) }}" class="inline" x-data
                        @submit.prevent="if(confirm('Delete staff {{ addslashes($s->full_name) }}?')) $el.submit()">
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
              <td colspan="10" class="text-center py-16 text-primary-500 dark:text-primary-400">
                <i class="fa-solid fa-id-badge text-4xl mb-4 block opacity-30"></i>
                <p class="text-sm font-semibold mb-1">No staff found</p>
                <p class="text-xs">
                  @if($searchQuery)
                    Try adjusting your search or
                  @endif
                  <a href="{{ route('admin.staff.create') }}" class="text-primary-600 dark:text-primary-400 underline hover:no-underline">add a new staff member</a>
                </p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($staff->hasPages())
      <div class="mt-6 pt-5 border-t border-primary-100 dark:border-primary-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="text-xs text-primary-600 dark:text-primary-400">
          Showing <span class="font-bold text-primary-900 dark:text-white">{{ $staff->firstItem() ?? 0 }}</span> to
          <span class="font-bold text-primary-900 dark:text-white">{{ $staff->lastItem() ?? 0 }}</span> of
          <span class="font-bold text-primary-900 dark:text-white">{{ $staff->total() }}</span> staff
        </p>
        <nav class="flex items-center justify-center gap-1">
          @if($staff->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-300 dark:text-primary-700 bg-primary-50 dark:bg-primary-900/20 cursor-not-allowed"><i class="fa-solid fa-chevron-left text-[10px]"></i></span>
          @else
            <a href="{{ $staff->appends(request()->query())->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
          @endif
          @for($i = max($staff->currentPage() - 2, 1); $i <= min($staff->currentPage() + 2, $staff->lastPage()); $i++)
            @if($i == $staff->currentPage())
              <span class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-primary-600 shadow-sm">{{ $i }}</span>
            @else
              <a href="{{ $staff->appends(request()->query())->url($i) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-primary-700 dark:text-primary-300 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 transition-colors">{{ $i }}</a>
            @endif
          @endfor
          @if($staff->hasMorePages())
            <a href="{{ $staff->appends(request()->query())->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/40 dark:hover:bg-primary-900/60 transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
          @else
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-primary-300 dark:text-primary-700 bg-primary-50 dark:bg-primary-900/20 cursor-not-allowed"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
          @endif
        </nav>
      </div>
    @endif
  </div>
</div>

@endsection
