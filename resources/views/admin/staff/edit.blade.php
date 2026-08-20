@extends('layouts.admin')

@section('breadcrumb', 'Staff Management » Edit Staff')
@section('page_title', 'Edit Staff')

@section('content')

<div class="max-w-4xl mx-auto">
  <form action="{{ route('admin.staff.update', app(\App\Services\EncryptedIdService::class)->encrypt($staff->id)) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

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
            <option value="{{ $m->id }}" {{ old('member_id', $staff->member_id) == $m->id ? 'selected' : '' }}>
              {{ $m->full_name }} ({{ $m->membercode }})
            </option>
          @endforeach
        </select>
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
          <input type="text" name="full_name" value="{{ old('full_name', $staff->full_name) }}" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Gender</label>
          <select name="gender" class="form-input">
            <option value="">Select...</option>
            <option value="male" {{ old('gender', $staff->gender) === 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ old('gender', $staff->gender) === 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ old('gender', $staff->gender) === 'other' ? 'selected' : '' }}>Other</option>
          </select>
        </div>
        <div>
          <label class="form-label">Date of Birth</label>
          <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $staff->date_of_birth?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Phone</label>
          <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">National ID</label>
          <input type="text" name="national_id" value="{{ old('national_id', $staff->national_id) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Marital Status</label>
          <select name="marital_status" class="form-input">
            <option value="">Select...</option>
            <option value="single" {{ old('marital_status', $staff->marital_status) === 'single' ? 'selected' : '' }}>Single</option>
            <option value="married" {{ old('marital_status', $staff->marital_status) === 'married' ? 'selected' : '' }}>Married</option>
            <option value="divorced" {{ old('marital_status', $staff->marital_status) === 'divorced' ? 'selected' : '' }}>Divorced</option>
            <option value="widowed" {{ old('marital_status', $staff->marital_status) === 'widowed' ? 'selected' : '' }}>Widowed</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="form-label">Residential Address</label>
          <input type="text" name="residential_address" value="{{ old('residential_address', $staff->residential_address) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Photo</label>
          @if($staff->photo)
            <div class="mb-2 flex items-center gap-3">
              <img src="{{ asset('storage/' . $staff->photo) }}" alt="" class="w-12 h-12 rounded-full object-cover">
              <label class="flex items-center gap-2 text-xs text-red-600 cursor-pointer">
                <input type="checkbox" name="remove_photo" value="1" class="rounded border-gray-300 text-red-600">
                Remove photo
              </label>
            </div>
          @endif
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
          <input type="text" name="department" value="{{ old('department', $staff->department) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Position</label>
          <input type="text" name="position" value="{{ old('position', $staff->position) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Employment Type</label>
          <select name="employment_type" class="form-input">
            <option value="">Select...</option>
            @foreach(\App\Models\Staff::EMPLOYMENT_TYPES as $type)
              <option value="{{ $type }}" {{ old('employment_type', $staff->employment_type) === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">Branch</label>
          <input type="text" name="branch" value="{{ old('branch', $staff->branch) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Hire Date</label>
          <input type="date" name="hire_date" value="{{ old('hire_date', $staff->hire_date?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">End Date</label>
          <input type="date" name="end_date" value="{{ old('end_date', $staff->end_date?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Salary (TZS)</label>
          <input type="number" name="salary" value="{{ old('salary', $staff->salary) }}" class="form-input" min="0" step="1000">
        </div>
        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            @foreach(\App\Models\Staff::STATUSES as $s)
              <option value="{{ $s }}" {{ old('status', $staff->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-input" rows="2">{{ old('notes', $staff->notes) }}</textarea>
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
            @foreach(['Certificate', 'Diploma', 'Advanced Diploma', 'Bachelor', 'Postgraduate Diploma', 'Master', 'PhD'] as $q)
              <option value="{{ $q }}" {{ old('highest_qualification', $staff->highest_qualification) === $q ? 'selected' : '' }}>{{ $q === 'Bachelor' ? "Bachelor's Degree" : ($q === 'Master' ? "Master's Degree" : $q) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">Field of Study</label>
          <input type="text" name="field_of_study" value="{{ old('field_of_study', $staff->field_of_study) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Institution</label>
          <input type="text" name="institution" value="{{ old('institution', $staff->institution) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Year of Graduation</label>
          <input type="number" name="year_of_graduation" value="{{ old('year_of_graduation', $staff->year_of_graduation) }}" class="form-input" min="1950" max="{{ date('Y') + 5 }}">
        </div>
        <div>
          <label class="form-label">Professional License</label>
          <input type="text" name="professional_license" value="{{ old('professional_license', $staff->professional_license) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">License Expiry</label>
          <input type="date" name="license_expiry" value="{{ old('license_expiry', $staff->license_expiry?->format('Y-m-d')) }}" class="form-input">
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
          <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $staff->emergency_contact_name) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Contact Phone</label>
          <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $staff->emergency_contact_phone) }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Relationship</label>
          <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $staff->emergency_contact_relationship) }}" class="form-input">
        </div>
      </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('admin.staff.show', app(\App\Services\EncryptedIdService::class)->encrypt($staff->id)) }}" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-bold transition-colors">
        Cancel
      </a>
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all shadow-sm hover:shadow-md active:scale-95">
        <i class="fa-solid fa-save mr-2"></i> Save Changes
      </button>
    </div>
  </form>
</div>

@endsection
