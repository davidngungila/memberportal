<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class PersonalDetailsController extends Controller
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

        $personalDetail = $application->personalDetail;

        return view('registration.personal-details', [
            'application' => $application,
            'personalDetail' => $personalDetail,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'nationality' => 'nullable|string|max:255',
            'national_id_number' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:single,married,divorced,widowed,other',
            'occupation' => 'nullable|string|max:255',
            'employer' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = auth()->user();
        $this->registrationService->savePersonalDetails($user, $validated);

        return redirect()->route('register.profile-photo')
            ->with('success', 'Personal details saved.');
    }
}
