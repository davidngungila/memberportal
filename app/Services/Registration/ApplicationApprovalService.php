<?php

namespace App\Services\Registration;

use App\Models\Member;
use App\Models\MembershipApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationApprovalService
{
    public function approve(MembershipApplication $application): Member
    {
        return DB::transaction(function () use ($application) {
            $application->update([
                'application_status' => 'approved',
                'approved_at' => now(),
            ]);

            $personalDetail = $application->personalDetail;
            $membershipType = $application->membershipType;

            $membercode = $this->generateMembercode();

            $member = Member::create([
                'member_number' => $membercode,
                'membercode' => $membercode,
                'user_id' => $application->user_id,
                'membership_type_id' => $application->membership_type_id,
                'first_name' => $personalDetail->first_name,
                'middle_name' => $personalDetail->middle_name,
                'last_name' => $personalDetail->last_name,
                'full_name' => $personalDetail->full_name,
                'gender' => $personalDetail->gender,
                'phone' => $personalDetail->phone,
                'email' => $personalDetail->email,
                'date_of_birth' => $personalDetail->date_of_birth,
                'national_id' => $personalDetail->national_id_number,
                'occupation' => $personalDetail->occupation,
                'employer' => $personalDetail->employer,
                'residential_address' => collect([
                    $personalDetail->street,
                    $personalDetail->ward,
                    $personalDetail->district,
                    $personalDetail->region,
                ])->filter()->implode(', '),
                'marital_status' => $personalDetail->marital_status,
                'status' => 'Active',
                'registration_status' => 'registered',
                'registration_date' => now(),
                'joined_at' => now(),
                'registration_fee' => $membershipType?->registration_fee ?? 0,
            ]);

            $this->transferBankAccounts($application, $membercode);
            $this->transferNextOfKin($application, $membercode);
            $this->transferDocuments($application, $member);
            $this->transferSavingPlan($application, $membercode);

            $application->update(['membercode' => $membercode]);

            $user = $application->user;
            $user->update([
                'member_number' => $membercode,
                'name' => $personalDetail->full_name,
            ]);

            if (!$user->hasRole('member')) {
                $user->assignRole('member');
            }

            return $member;
        });
    }

    public function reject(MembershipApplication $application, ?string $reason = null): void
    {
        $application->update([
            'application_status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
        ]);
    }

    public function requestCorrection(MembershipApplication $application, string $notes): void
    {
        $application->update([
            'application_status' => 'correction_required',
            'correction_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }

    protected function generateMembercode(): string
    {
        $prefix = 'SCH';
        $lastMember = Member::where('membercode', 'like', "{$prefix}%")
            ->orderByDesc('membercode')
            ->first();

        if ($lastMember) {
            $lastNumber = (int) substr($lastMember->membercode, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $newNumber;
    }

    protected function transferBankAccounts(MembershipApplication $application, string $membercode): void
    {
        foreach ($application->bankAccounts as $bankAccount) {
            \App\Models\MemberBankAccount::create([
                'membercode' => $membercode,
                'bank_name' => $bankAccount->bank_name,
                'account_name' => $bankAccount->account_name,
                'account_number' => $bankAccount->account_number,
                'branch' => $bankAccount->branch,
                'is_primary' => $bankAccount->is_primary,
            ]);
        }
    }

    protected function transferNextOfKin(MembershipApplication $application, string $membercode): void
    {
        foreach ($application->nextOfKin as $kin) {
            \App\Models\MemberNextOfKin::create([
                'membercode' => $membercode,
                'full_name' => $kin->full_name,
                'relationship' => $kin->relationship,
                'phone' => $kin->phone,
                'alternative_phone' => $kin->alternative_phone,
                'address' => $kin->address,
                'is_primary' => $kin->is_primary,
            ]);
        }
    }

    protected function transferDocuments(MembershipApplication $application, Member $member): void
    {
        foreach ($application->documents as $document) {
            if ($document->document_type === 'passport_photo') {
                $member->update(['profile_photo' => $document->file_path]);
            }
        }
    }

    protected function transferSavingPlan(MembershipApplication $application, string $membercode): void
    {
        $plan = $application->savingPlan;
        if ($plan) {
            \App\Models\SavingPlan::create([
                'name' => $plan->plan_name,
                'member_number' => $membercode,
                'membercode' => $membercode,
                'membership' => $application->membershipType?->name ?? 'Regular',
                'goal' => $plan->target_amount,
                'monthly_goal' => $plan->periodic_amount,
                'period_type' => $plan->frequency,
                'start_date' => now(),
                'status' => 'active',
            ]);
        }
    }
}
