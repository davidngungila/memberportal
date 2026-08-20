@extends('layouts.admin')

@section('breadcrumb', 'Staff Management » Add Staff')
@section('page_title', 'Add Staff')

@section('content')

<div class="max-w-4xl mx-auto">
  <form action="{{ route('admin.staff.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    {{-- Link to Member --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-link text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">MEMBER LINK</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Link to an existing member (optional)</p>
        </div>
      </div>
      <div class="p-5">
        <label class="form-label">Linked Member</label>
        <select name="member_id" class="form-input">
          <option value="">— Not linked —</option>
          @foreach($members as $m)
            <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
              {{ $m->full_name }} ({{ $m->membercode }})
            </option>
          @endforeach
        </select>
        <p class="text-[10px] text-gray-500 mt-1">Link if this staff member is also a cooperative member</p>
      </div>
    </div>

    {{-- Personal Information --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-id-card text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">PERSONAL INFORMATION</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Basic staff details</p>
        </div>
      </div>
      <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="form-label">Full Name <span class="text-red-500">*</span></label>
          <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Gender</label>
          <select name="gender" class="form-input">
            <option value="">Select...</option>
            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
          </select>
        </div>
        <div>
          <label class="form-label">Date of Birth</label>
          <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Phone</label>
          <input type="text" name="phone" value="{{ old('phone') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">National ID</label>
          <input type="text" name="national_id" value="{{ old('national_id') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Marital Status</label>
          <select name="marital_status" class="form-input">
            <option value="">Select...</option>
            <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>Single</option>
            <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>Married</option>
            <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Divorced</option>
            <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>Widowed</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="form-label">Residential Address</label>
          <input type="text" name="residential_address" value="{{ old('residential_address') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Photo</label>
          <input type="file" name="photo" accept="image/*" class="form-input">
        </div>
      </div>
    </div>

    {{-- Employment Information --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-briefcase text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">EMPLOYMENT INFORMATION</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Job details and status</p>
        </div>
      </div>
      <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="form-label">Department</label>
          <input type="text" name="department" value="{{ old('department') }}" class="form-input" placeholder="e.g. Finance, Operations">
        </div>
        <div>
          <label class="form-label">Position</label>
          <input type="text" name="position" value="{{ old('position') }}" class="form-input" placeholder="e.g. Manager, Accountant">
        </div>
        <div>
          <label class="form-label">Employment Type</label>
          <select name="employment_type" class="form-input">
            <option value="">Select...</option>
            @foreach(\App\Models\Staff::EMPLOYMENT_TYPES as $type)
              <option value="{{ $type }}" {{ old('employment_type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">Branch</label>
          <input type="text" name="branch" value="{{ old('branch') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Hire Date</label>
          <input type="date" name="hire_date" value="{{ old('hire_date') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">End Date (Contract/Intern)</label>
          <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Salary (TZS)</label>
          <input type="number" name="salary" value="{{ old('salary') }}" class="form-input" min="0" step="1000">
        </div>
        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            @foreach(\App\Models\Staff::STATUSES as $s)
              <option value="{{ $s }}" {{ old('status', 'active') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
        </div>
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
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Education and professional credentials</p>
        </div>
      </div>
      <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="form-label">Highest Qualification</label>
          <select name="highest_qualification" class="form-input">
            <option value="">Select...</option>
            <option value="Certificate" {{ old('highest_qualification') === 'Certificate' ? 'selected' : '' }}>Certificate</option>
            <option value="Diploma" {{ old('highest_qualification') === 'Diploma' ? 'selected' : '' }}>Diploma</option>
            <option value="Advanced Diploma" {{ old('highest_qualification') === 'Advanced Diploma' ? 'selected' : '' }}>Advanced Diploma</option>
            <option value="Bachelor" {{ old('highest_qualification') === 'Bachelor' ? 'selected' : '' }}>Bachelor's Degree</option>
            <option value="Postgraduate Diploma" {{ old('highest_qualification') === 'Postgraduate Diploma' ? 'selected' : '' }}>Postgraduate Diploma</option>
            <option value="Master" {{ old('highest_qualification') === 'Master' ? 'selected' : '' }}>Master's Degree</option>
            <option value="PhD" {{ old('highest_qualification') === 'PhD' ? 'selected' : '' }}>PhD</option>
          </select>
        </div>
        <div>
          <label class="form-label">Field of Study</label>
          <input type="text" name="field_of_study" value="{{ old('field_of_study') }}" class="form-input" placeholder="e.g. Accounting, Computer Science">
        </div>
        <div>
          <label class="form-label">Institution</label>
          <input type="text" name="institution" value="{{ old('institution') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Year of Graduation</label>
          <input type="number" name="year_of_graduation" value="{{ old('year_of_graduation') }}" class="form-input" min="1950" max="{{ date('Y') + 5 }}">
        </div>
        <div>
          <label class="form-label">Professional License</label>
          <input type="text" name="professional_license" value="{{ old('professional_license') }}" class="form-input" placeholder="e.g. CPA, ACCA">
        </div>
        <div>
          <label class="form-label">License Expiry</label>
          <input type="date" name="license_expiry" value="{{ old('license_expiry') }}" class="form-input">
        </div>
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
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Who to contact in case of emergency</p>
        </div>
      </div>
      <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="form-label">Contact Name</label>
          <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Contact Phone</label>
          <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Relationship</label>
          <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" class="form-input" placeholder="e.g. Spouse, Parent">
        </div>
      </div>
    </div>

    {{-- Staff Roles --}}
    <div class="glass rounded-2xl overflow-hidden border border-primary-100 dark:border-dark-border">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/40 dark:bg-primary-900/20">
        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center flex-shrink-0 shadow-sm">
          <i class="fa-solid fa-user-shield text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-primary-900 dark:text-white text-sm">STAFF ROLES</h3>
          <p class="text-[11px] text-primary-500 dark:text-primary-400">Select modules this staff can access (one staff can have multiple roles)</p>
        </div>
      </div>
      <div class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          @foreach(\App\Models\Staff::ROLES as $key => $label)
            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all
                          {{ in_array($key, old('staff_roles', [])) ? 'border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700' : 'border-primary-100 dark:border-dark-border hover:border-primary-300 dark:hover:border-primary-700' }}">
              <input type="checkbox" name="staff_roles[]" value="{{ $key }}"
                     {{ in_array($key, old('staff_roles', [])) ? 'checked' : '' }}
                     class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $label }}</p>
                <p class="text-[10px] text-primary-500 dark:text-primary-400 mt-0.5">
                  @if($key === 'deposit_officer')
                    Savings, Deposits, Saving Plans
                  @elseif($key === 'investment_officer')
                    Investments
                  @elseif($key === 'loan_officer')
                    Loans
                  @elseif($key === 'swf_officer')
                    SWF
                  @elseif($key === 'system_administrator')
                    Users, Staff, Settings, Reports
                  @elseif($key === 'secretary')
                    Applications, Members, Notifications
                  @elseif($key === 'chairperson')
                    Approvals, Reports, Applications
                  @endif
                </p>
              </div>
            </label>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('admin.staff.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-bold transition-colors">
        Cancel
      </a>
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95">
        <i class="fa-solid fa-user-plus mr-2"></i> Create Staff
      </button>
    </div>
  </form>
</div>

@endsection
