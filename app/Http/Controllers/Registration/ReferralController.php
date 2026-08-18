<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class ReferralController extends Controller
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

        $referral = $application->referral;

        return view('registration.referral', [
            'application' => $application,
            'referral' => $referral ?? (object) ['was_referred' => false, 'referee_membercode' => null, 'referee_name' => null],
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'was_referred' => 'required|boolean',
            'referee_membercode' => 'required_if:was_referred,true|nullable|string|max:50',
        ]);

        $data = [
            'was_referred' => $validated['was_referred'],
            'referee_membercode' => $validated['referee_membercode'] ?? null,
            'referee_name' => null,
        ];

        if ($data['was_referred'] && $data['referee_membercode']) {
            $referee = Member::where('membercode', $data['referee_membercode'])->first();
            if (!$referee) {
                return back()->with('error', 'Member code not found.');
            }
            $data['referee_name'] = $referee->full_name;
        }

        $user = auth()->user();
        $this->registrationService->saveReferral($user, $data);

        return redirect()->route('register.saving-plan')
            ->with('success', 'Referral information saved.');
    }

    public function validateMembercode(Request $request)
    {
        $request->validate([
            'membercode' => 'required|string',
        ]);

        $member = Member::where('membercode', $request->membercode)->first();

        if ($member) {
            return response()->json([
                'valid' => true,
                'name' => $member->full_name,
            ]);
        }

        return response()->json([
            'valid' => false,
            'name' => null,
        ]);
    }
}
