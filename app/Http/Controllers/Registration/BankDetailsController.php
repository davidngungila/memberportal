<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\Request;

class BankDetailsController extends Controller
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

        $bankAccounts = $application->bankAccounts;

        return view('registration.bank-details', [
            'application' => $application,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'banks' => 'required|array|min:1',
            'banks.*.bank_name' => 'required|string|max:255',
            'banks.*.account_name' => 'required|string|max:255',
            'banks.*.account_number' => 'required|string|max:50',
            'banks.*.branch' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $this->registrationService->saveBankDetails($user, $validated['banks']);

        return redirect()->route('register.next-of-kin')
            ->with('success', 'Bank details saved.');
    }
}
