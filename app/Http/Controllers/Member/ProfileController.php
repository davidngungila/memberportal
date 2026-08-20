<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SwfMember;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use FlashMessages;

    public function show(Request $request): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        
        $swfMember = $user->swfMember;
        if ($swfMember) {
            $swfBalance = $swfMember->total_contributions - $swfMember->total_benefits_received;
        } else {
            $swfBalance = 0;
        }

        $fullName = $user->name;
        $initials = $this->extractInitials($fullName);

        return view('member.profile.show', compact(
            'user',
            'initials',
            'fullName',
            'swfBalance'
        ));
    }

    public function index(Request $request): View
    {
        return $this->show($request);
    }

    public function edit(Request $request): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();

        $fullName = $user->name;

        return view('member.profile.edit', compact(
            'user',
            'fullName'
        ));
    }

    public function update(Request $request)
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            $this->error('No member profile found. Please contact support.');
            return redirect()->back();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email,' . $member->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'employer' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                $this->error('Current password is incorrect.');
                return redirect()->back();
            }
            $user->password = Hash::make($validated['new_password']);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('profile-photos', $fileName, 'public');

            if ($member->profile_photo && Storage::disk('public')->exists($member->profile_photo)) {
                Storage::disk('public')->delete($member->profile_photo);
            }

            $member->profile_photo = $filePath;
        }

        if ($request->input('remove_photo')) {
            if ($member->profile_photo && Storage::disk('public')->exists($member->profile_photo)) {
                Storage::disk('public')->delete($member->profile_photo);
            }
            $member->profile_photo = null;
        }

        $user->name = $validated['name'];
        $user->save();

        $member->email = $validated['email'];
        $member->phone = $validated['phone'] ?? $member->phone;
        $member->residential_address = $validated['address'] ?? $member->residential_address;
        $member->occupation = $validated['occupation'] ?? $member->occupation;
        $member->employer = $validated['employer'] ?? $member->employer;
        $member->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'description' => 'Member updated their profile',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'name' => $user->name,
                'email' => $member->email,
                'photo_updated' => $request->hasFile('photo'),
            ],
        ]);

        $this->success('Profile updated successfully.');

        return redirect()->route('member.profile.show');
    }

    protected function extractInitials(string $name): string
    {
        $parts = array_values(array_filter(explode(' ', trim($name))));
        if (count($parts) === 0) {
            return 'M';
        }
        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 1));
        }

        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    }
}
