<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
    ) {}

    public function showForm()
    {
        $user = auth()->user();
        $application = $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('register.create');
        }

        $documents = $application->documents;

        return view('registration.profile-photo', [
            'application' => $application,
            'documents' => $documents,
        ]);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'passport_photo' => 'required|file|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('passport_photo');
        $path = $file->store('registration/passports', 'public');

        $user = auth()->user();
        $this->registrationService->saveProfilePhoto(
            $user,
            $path,
            $file->getClientOriginalName(),
            $file->getMimeType(),
            $file->getSize()
        );

        return redirect()->route('register.bank-details')
            ->with('success', 'Profile photo uploaded.');
    }
}
