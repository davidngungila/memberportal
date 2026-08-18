<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\MembershipType;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class MembershipTypeController extends Controller
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

        $membershipTypes = MembershipType::active()->get();

        return view('registration.membership-type', [
            'application' => $application,
            'membershipTypes' => $membershipTypes,
        ]);
    }

    public function select(Request $request)
    {
        $validated = $request->validate([
            'membership_type_id' => 'required|exists:member_types,id',
        ]);

        $user = auth()->user();
        $this->registrationService->selectMembershipType($user, $validated['membership_type_id']);

        return redirect()->route('register.payment')
            ->with('success', 'Membership type selected.');
    }
}
