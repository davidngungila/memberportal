<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use App\Services\Registration\ApplicationApprovalService;
use Illuminate\Http\Request;

class MembershipApplicationController extends Controller
{
    public function __construct(
        protected ApplicationApprovalService $approvalService,
    ) {}

    public function index(Request $request)
    {
        $query = MembershipApplication::with(['user', 'membershipType', 'personalDetail']);

        if ($request->filled('status')) {
            $query->where('application_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('personalDetail', function ($q3) use ($search) {
                      $q3->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $applications = $query->latest()->paginate(15);
        $stats = [
            'total' => MembershipApplication::count(),
            'pending' => MembershipApplication::whereIn('application_status', ['draft', 'in_progress'])->count(),
            'submitted' => MembershipApplication::where('application_status', 'submitted')->count(),
            'under_review' => MembershipApplication::where('application_status', 'under_review')->count(),
            'approved' => MembershipApplication::where('application_status', 'approved')->count(),
            'rejected' => MembershipApplication::where('application_status', 'rejected')->count(),
        ];

        return view('admin.membership-applications.index', compact('applications', 'stats'));
    }

    public function show(MembershipApplication $application)
    {
        $application->load([
            'user',
            'membershipType',
            'personalDetail',
            'documents',
            'bankAccounts',
            'nextOfKin',
            'referral',
            'savingPlan',
            'payments',
        ]);

        return view('admin.membership-applications.show', compact('application'));
    }

    public function approve(MembershipApplication $application)
    {
        $member = $this->approvalService->approve($application);

        return redirect()->route('admin.membership-applications.show', $application)
            ->with('success', "Application approved. Member created with code: {$member->membercode}");
    }

    public function reject(Request $request, MembershipApplication $application)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $this->approvalService->reject($application, $validated['rejection_reason']);

        return redirect()->route('admin.membership-applications.show', $application)
            ->with('success', 'Application rejected.');
    }

    public function requestCorrection(Request $request, MembershipApplication $application)
    {
        $validated = $request->validate([
            'correction_notes' => 'required|string|max:1000',
        ]);

        $this->approvalService->requestCorrection($application, $validated['correction_notes']);

        return redirect()->route('admin.membership-applications.show', $application)
            ->with('success', 'Correction requested.');
    }
}
