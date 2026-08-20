<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use FlashMessages;

    public function show(Request $request): View
    {
        $user = Auth::user();
        $initials = $this->extractInitials($user->name);

        return view('admin.profile.show', compact(
            'user',
            'initials'
        ));
    }

    public function edit(Request $request): View
    {
        $user = Auth::user();

        return view('admin.profile.edit', compact(
            'user'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $member = $user->member;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        if ($member) {
            $rules['email'] = ['required', 'email', 'max:255', 'unique:members,email,' . $member->id];
            $rules['phone'] = ['nullable', 'string', 'max:20'];
            $rules['address'] = ['nullable', 'string', 'max:500'];
            $rules['occupation'] = ['nullable', 'string', 'max:255'];
            $rules['employer'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                $this->error('Current password is incorrect.');
                return redirect()->back();
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('profile-photos', $fileName, 'public');

            // Delete old photo
            if ($member) {
                if ($member->profile_photo && Storage::disk('public')->exists($member->profile_photo)) {
                    Storage::disk('public')->delete($member->profile_photo);
                }
                $member->profile_photo = $filePath;
                $member->save();
            } else {
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                $user->photo = $filePath;
            }
        }

        // Handle photo removal
        if ($request->input('remove_photo')) {
            if ($member) {
                if ($member->profile_photo && Storage::disk('public')->exists($member->profile_photo)) {
                    Storage::disk('public')->delete($member->profile_photo);
                }
                $member->profile_photo = null;
                $member->save();
            } else {
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                $user->photo = null;
            }
        }

        $user->save();

        if ($member) {
            $member->email = $validated['email'] ?? $member->email;
            $member->phone = $validated['phone'] ?? $member->phone;
            $member->residential_address = $validated['address'] ?? $member->residential_address;
            $member->occupation = $validated['occupation'] ?? $member->occupation;
            $member->employer = $validated['employer'] ?? $member->employer;
            $member->save();
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'description' => 'Admin updated their profile',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'name' => $user->name,
                'email' => $member?->email,
                'photo_updated' => $request->hasFile('photo'),
            ],
        ]);

        $this->success('Profile updated successfully.');

        return redirect()->route('admin.profile.show');
    }

    protected function extractInitials(string $name): string
    {
        $parts = array_values(array_filter(explode(' ', trim($name))));
        if (count($parts) === 0) {
            return 'A';
        }
        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 1));
        }

        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    }
}
