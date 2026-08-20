<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\Staff;
use App\Services\EncryptedIdService;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StaffController extends Controller
{
    use FlashMessages;

    public function __construct(
        protected EncryptedIdService $encryptedIdService,
    ) {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('q', '');
        $status = $request->input('status', '');
        $department = $request->input('department', '');

        $query = Staff::with(['member', 'staffRoles']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('staff_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($department)) {
            $query->where('department', $department);
        }

        $staff = $query->latest()->paginate($perPage);

        if ($staff instanceof LengthAwarePaginator) {
            $staff->appends([
                'q' => $search,
                'per_page' => $perPage,
                'status' => $status,
                'department' => $department,
            ]);
        }

        $departments = Staff::distinct()->whereNotNull('department')->pluck('department');

        $searchQuery = $search;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed staff list',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('admin.staff.index', compact(
            'staff',
            'searchQuery',
            'perPage',
            'departments',
        ), [
            'statusFilter' => $status,
            'departmentFilter' => $department,
        ]);
    }

    public function create(): View
    {
        $members = Member::active()->whereDoesntHave('staff')->orderBy('full_name')->get();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Admin viewed create staff form',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('admin.staff.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:20',
            'residential_address' => 'nullable|string|max:500',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|in:' . implode(',', Staff::EMPLOYMENT_TYPES),
            'hire_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:hire_date',
            'salary' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:100',
            'highest_qualification' => 'nullable|string|max:100',
            'field_of_study' => 'nullable|string|max:200',
            'institution' => 'nullable|string|max:200',
            'year_of_graduation' => 'nullable|digits:4|integer|min:1950|max:' . (date('Y') + 5),
            'professional_license' => 'nullable|string|max:200',
            'license_expiry' => 'nullable|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:' . implode(',', Staff::STATUSES),
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'member_id' => 'nullable|exists:members,id',
            'user_id' => 'nullable|exists:users,id',
            'staff_roles' => 'nullable|array',
            'staff_roles.*' => 'string|in:' . implode(',', array_keys(Staff::ROLES)),
        ]);

        $roles = $validated['staff_roles'] ?? [];
        unset($validated['staff_roles']);

        $validated['status'] = $validated['status'] ?? 'active';

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('staff-photos', 'public');
        }

        $staff = Staff::create($validated);

        foreach ($roles as $role) {
            $staff->staffRoles()->attach($role);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'staff',
            'subject_id' => $staff->id,
            'description' => "Admin created staff: {$staff->full_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'staff_number' => $staff->staff_number,
                'department' => $staff->department,
            ],
        ]);

        $this->success("Staff {$staff->full_name} created successfully.");

        return redirect()->route('admin.staff.index');
    }

    public function show(Request $request, string $encryptedId): View
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $staff = Staff::with(['member', 'user', 'staffRoles'])->findOrFail($id);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'staff',
            'subject_id' => $staff->id,
            'description' => "Admin viewed staff details: {$staff->full_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('admin.staff.show', compact('staff'));
    }

    public function edit(Request $request, string $encryptedId): View
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $staff = Staff::with('staffRoles')->findOrFail($id);
        $members = Member::active()->whereDoesntHave('staff')->orWhere('id', $staff->member_id)->orderBy('full_name')->get();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'staff',
            'subject_id' => $staff->id,
            'description' => "Admin viewed edit staff form: {$staff->full_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('admin.staff.edit', compact('staff', 'members'));
    }

    public function update(Request $request, string $encryptedId)
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:20',
            'residential_address' => 'nullable|string|max:500',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|in:' . implode(',', Staff::EMPLOYMENT_TYPES),
            'hire_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:hire_date',
            'salary' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:100',
            'highest_qualification' => 'nullable|string|max:100',
            'field_of_study' => 'nullable|string|max:200',
            'institution' => 'nullable|string|max:200',
            'year_of_graduation' => 'nullable|digits:4|integer|min:1950|max:' . (date('Y') + 5),
            'professional_license' => 'nullable|string|max:200',
            'license_expiry' => 'nullable|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:' . implode(',', Staff::STATUSES),
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_photo' => 'nullable|boolean',
            'member_id' => 'nullable|exists:members,id',
            'user_id' => 'nullable|exists:users,id',
            'staff_roles' => 'nullable|array',
            'staff_roles.*' => 'string|in:' . implode(',', array_keys(Staff::ROLES)),
        ]);

        $roles = $validated['staff_roles'] ?? [];
        unset($validated['staff_roles']);

        if ($request->hasFile('photo')) {
            if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                Storage::disk('public')->delete($staff->photo);
            }
            $validated['photo'] = $request->file('photo')->store('staff-photos', 'public');
        } elseif ($request->input('remove_photo')) {
            if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                Storage::disk('public')->delete($staff->photo);
            }
            $validated['photo'] = null;
        } else {
            unset($validated['photo']);
        }

        $staff->update($validated);

        $staff->staffRoles()->sync($roles);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'staff',
            'subject_id' => $staff->id,
            'description' => "Admin updated staff: {$staff->full_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'updated_fields' => array_keys($validated),
            ],
        ]);

        $this->success("Staff {$staff->full_name} updated successfully.");

        return redirect()->route('admin.staff.show', $this->encryptedIdService->encrypt($staff->id));
    }

    public function destroy(Request $request, string $encryptedId)
    {
        $id = $this->encryptedIdService->decrypt($encryptedId);
        $staff = Staff::findOrFail($id);
        $staffName = $staff->full_name;

        if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
            Storage::disk('public')->delete($staff->photo);
        }

        $staff->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => 'staff',
            'subject_id' => $id,
            'description' => "Admin deleted staff: {$staffName}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success("Staff {$staffName} deleted successfully.");

        return redirect()->route('admin.staff.index');
    }
}
