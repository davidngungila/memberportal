@extends('layouts.admin')

@section('breadcrumb', 'Staff Management » Staff Details')
@section('page_title', 'Staff Details')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="glass rounded-2xl p-6 lg:p-8 border border-primary-100 dark:border-dark-border">
    <div class="flex flex-col md:flex-row md:items-center gap-6">
      <div class="flex-shrink-0 mx-auto md:mx-0">
        @if($staff->photo)
          <img src="{{ asset('storage/' . $staff->photo) }}" alt="" class="w-24 h-24 rounded-full object-cover shadow-xl shadow-primary-500/20">
        @else
          <div class="w-24 h-24 rounded-full flex items-center justify-center shadow-xl shadow-primary-500/20"
               style="background: linear-gradient(135deg, #818cf8 0%, #6366f1 55%, #3730a3 100%);">
            <span class="text-4xl font-extrabold text-white tracking-wide">{{ strtoupper(substr($staff->full_name, 0, 1)) }}</span>
          </div>
        @endif
      </div>

      <div class="flex-1 text-center md:text-left">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-primary-900 dark:text-white leading-tight">
              {{ $staff->full_name }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center justify-center md:justify-start gap-3">
              <span class="inline-flex items-center px-3 py-1.5 rounded-lg font-mono text-sm font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800/60">
                <i class="fa-solid fa-id-badge mr-2 text-indigo-500 text-xs"></i>
                {{ $staff->staff_number }}
              </span>
              @if($staff->member)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold border border-primary-100 dark:border-primary-800/50">
                  <i class="fa-solid fa-link"></i>
                  Member: {{ $staff->member->membercode }}
                </span>
              @endif
              @if($staff->status === 'active')
                <span class="badge badge-green"><i class="fa-solid fa-circle-check text-[9px] mr-1"></i> Active</span>
              @elseif($staff->status === 'inactive')
                <span class="badge badge-gray"><i class="fa-solid fa-circle-xmark text-[9px] mr-1"></i> Inactive</span>
              @elseif($staff->status === 'suspended')
                <span class="badge badge-yellow"><i class="fa-solid fa-clock text-[9px] mr-1"></i> Suspended</span>
              @elseif($staff->status === 'terminated')
                <span class="badge badge-red"><i class="fa-solid fa-ban text-[9px] mr-1"></i> Terminated</span>
              @endif
            </div>
            @if($staff->staffRoles->count())
              <div class="mt-2 flex flex-wrap items-center justify-center md:justify-start gap-1.5">
                @foreach($staff->staffRoles as $role)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-[11px] font-bold border border-amber-200 dark:border-amber-800/50">
                    <i class="fa-solid fa-shield-halved text-[9px]"></i>
                    {{ \App\Models\Staff::ROLES[$role->role] ?? $role->role }}
                  </span>
                @endforeach
              </div>
            @endif
          </div>
          <div class="flex items-center gap-2">
            <a href="{{ route('admin.staff.edit', app(\App\Services\EncryptedIdService::class)->encrypt($staff->id)) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all shadow-sm hover:shadow-md active:scale-95 whitespace-nowrap">
              <i class="fa-solid fa-pen text-[11px]"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.staff.destroy', app(\App\Services\EncryptedIdService::class)->encrypt($staff->id)) }}" class="inline" x-data
                  @submit.prevent="if(confirm('Delete this staff member?')) $el.submit()">
              @csrf
              @method('DELETE')
              <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-all shadow-sm hover:shadow-md active:scale-95 whitespace-nowrap">
                <i class="fa-solid fa-trash text-[11px]"></i> Delete
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Personal Info --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-id-card text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">PERSONAL INFO</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Staff details</p>
        </div>
      </div>
      <div class="divide-y divide-primary-50 dark:divide-primary-800/50">
        @foreach([
          ['Full Name', $staff->full_name],
          ['Gender', $staff->gender ? ucfirst($staff->gender) : null],
          ['Date of Birth', $staff->date_of_birth ? $staff->date_of_birth->format('d M Y') : null],
          ['Phone', $staff->phone],
          ['Email', $staff->email],
          ['National ID', $staff->national_id],
          ['Marital Status', $staff->marital_status ? ucfirst($staff->marital_status) : null],
          ['Address', $staff->residential_address],
        ] as [$label, $value])
          <div class="flex items-start justify-between px-5 py-3.5 gap-3">
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 dark:text-primary-500 mb-0.5">{{ $label }}</p>
              <p class="text-sm font-bold text-primary-900 dark:text-white break-words">{{ $value ?? '—' }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Employment Info --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-briefcase text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">EMPLOYMENT</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Job and department</p>
        </div>
      </div>
      <div class="divide-y divide-primary-50 dark:divide-primary-800/50">
        @foreach([
          ['Department', $staff->department],
          ['Position', $staff->position],
          ['Employment Type', $staff->employment_type ? ucfirst(str_replace('_', ' ', $staff->employment_type)) : null],
          ['Branch', $staff->branch],
          ['Hire Date', $staff->hire_date ? $staff->hire_date->format('d M Y') : null],
          ['End Date', $staff->end_date ? $staff->end_date->format('d M Y') : null],
          ['Salary', $staff->salary ? number_format($staff->salary, 0) . ' TZS' : null],
        ] as [$label, $value])
          <div class="flex items-start justify-between px-5 py-3.5 gap-3">
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 dark:text-primary-500 mb-0.5">{{ $label }}</p>
              <p class="text-sm font-bold text-primary-900 dark:text-white break-words">{{ $value ?? '—' }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Qualifications --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-400 to-violet-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-graduation-cap text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">QUALIFICATIONS</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Education and licenses</p>
        </div>
      </div>
      <div class="divide-y divide-primary-50 dark:divide-primary-800/50">
        @foreach([
          ['Highest Qualification', $staff->highest_qualification],
          ['Field of Study', $staff->field_of_study],
          ['Institution', $staff->institution],
          ['Year of Graduation', $staff->year_of_graduation],
          ['Professional License', $staff->professional_license],
          ['License Expiry', $staff->license_expiry ? $staff->license_expiry->format('d M Y') : null],
        ] as [$label, $value])
          <div class="flex items-start justify-between px-5 py-3.5 gap-3">
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 dark:text-primary-500 mb-0.5">{{ $label }}</p>
              <p class="text-sm font-bold text-primary-900 dark:text-white break-words">{{ $value ?? '—' }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Emergency Contact --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-phone-volume text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">EMERGENCY CONTACT</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">In case of emergency</p>
        </div>
      </div>
      <div class="divide-y divide-primary-50 dark:divide-primary-800/50">
        @foreach([
          ['Contact Name', $staff->emergency_contact_name],
          ['Contact Phone', $staff->emergency_contact_phone],
          ['Relationship', $staff->emergency_contact_relationship],
        ] as [$label, $value])
          <div class="flex items-start justify-between px-5 py-3.5 gap-3">
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 dark:text-primary-500 mb-0.5">{{ $label }}</p>
              <p class="text-sm font-bold text-primary-900 dark:text-white break-words">{{ $value ?? '—' }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>

  </div>

  @if($staff->notes)
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-sticky-note text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">NOTES</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Additional information</p>
        </div>
      </div>
      <div class="p-5">
        <p class="text-sm text-primary-900 dark:text-white whitespace-pre-wrap">{{ $staff->notes }}</p>
      </div>
    </div>
  @endif

</div>

@endsection
