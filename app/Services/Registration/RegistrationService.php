<?php

namespace App\Services\Registration;

use App\Models\MembershipApplication;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationService
{
    public const STAGES = [
        'account_created',
        'phone_verified',
        'password_created',
        'membership_selected',
        'payment_completed',
        'personal_details_completed',
        'profile_completed',
        'bank_details_completed',
        'next_of_kin_completed',
        'referral_completed',
        'saving_plan_completed',
        'ready_for_review',
        'submitted',
    ];

    public const STAGE_ORDER = [
        'account_created' => 0,
        'phone_verified' => 1,
        'password_created' => 2,
        'membership_selected' => 3,
        'payment_completed' => 4,
        'personal_details_completed' => 5,
        'profile_completed' => 6,
        'bank_details_completed' => 7,
        'next_of_kin_completed' => 8,
        'referral_completed' => 9,
        'saving_plan_completed' => 10,
        'ready_for_review' => 11,
        'submitted' => 12,
    ];

    public function createAccount(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $phone = $data['phone'];

            $user = User::create([
                'name' => $phone,
                'email' => null,
                'phone' => $phone,
                'password' => Hash::make(Str::random(12)),
                'role' => 'member',
                'status' => 'active',
            ]);

            $verification = VerificationCode::createForUser($user, null, $phone);

            $application = MembershipApplication::create([
                'application_number' => MembershipApplication::generateApplicationNumber(),
                'user_id' => $user->id,
                'application_status' => 'draft',
                'current_stage' => 'account_created',
            ]);

            return [
                'user' => $user,
                'verification' => $verification,
                'application' => $application,
            ];
        });
    }

    public function verifyAccount(User $user, string $phoneCode): bool
    {
        $verification = $user->verificationCode()->latest()->first();

        if (!$verification) {
            return false;
        }

        $phoneVerified = $verification->verifyPhone($phoneCode);

        if ($phoneVerified) {
            $verification->markComplete();
            $this->advanceStage($user, 'phone_verified');
            return true;
        }

        return false;
    }

    public function createPassword(User $user, string $password): bool
    {
        $user->update(['password' => Hash::make($password)]);
        $this->advanceStage($user, 'password_created');
        return true;
    }

    public function selectMembershipType(User $user, int $membershipTypeId): MembershipApplication
    {
        $application = $this->getActiveApplication($user);

        $application->update([
            'membership_type_id' => $membershipTypeId,
        ]);

        $this->advanceStage($user, 'membership_selected');

        return $application;
    }

    public function recordPayment(User $user, array $paymentData): MembershipPayment
    {
        $application = $this->getActiveApplication($user);

        $payment = \App\Models\MembershipPayment::create([
            'application_id' => $application->id,
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'] ?? null,
            'transaction_reference' => $paymentData['transaction_reference'] ?? null,
            'status' => 'pending',
        ]);

        $application->update(['payment_status' => 'pending']);

        return $payment;
    }

    public function completePayment(User $user, int $paymentId): bool
    {
        $application = $this->getActiveApplication($user);

        $payment = \App\Models\MembershipPayment::where('application_id', $application->id)
            ->where('id', $paymentId)
            ->firstOrFail();

        $payment->update([
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        $application->update(['payment_status' => 'successful']);
        $this->advanceStage($user, 'payment_completed');

        return true;
    }

    public function savePersonalDetails(User $user, array $data): void
    {
        $application = $this->getActiveApplication($user);

        \App\Models\ApplicationPersonalDetail::updateOrCreate(
            ['application_id' => $application->id],
            $data
        );

        $this->advanceStage($user, 'personal_details_completed');
    }

    public function saveProfilePhoto(User $user, string $filePath, string $fileName, ?string $fileType = null, ?int $fileSize = null): void
    {
        $application = $this->getActiveApplication($user);

        \App\Models\ApplicationDocument::updateOrCreate(
            [
                'application_id' => $application->id,
                'document_type' => 'passport_photo',
            ],
            [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
            ]
        );

        $this->advanceStage($user, 'profile_completed');
    }

    public function saveBankDetails(User $user, array $bankAccounts): void
    {
        $application = $this->getActiveApplication($user);

        \App\Models\ApplicationBankAccount::where('application_id', $application->id)->delete();

        foreach ($bankAccounts as $index => $account) {
            \App\Models\ApplicationBankAccount::create([
                'application_id' => $application->id,
                'bank_name' => $account['bank_name'],
                'account_name' => $account['account_name'],
                'account_number' => $account['account_number'],
                'branch' => $account['branch'] ?? null,
                'is_primary' => $index === 0,
            ]);
        }

        $this->advanceStage($user, 'bank_details_completed');
    }

    public function saveNextOfKin(User $user, array $nextOfKinList): void
    {
        $application = $this->getActiveApplication($user);

        \App\Models\ApplicationNextOfKin::where('application_id', $application->id)->delete();

        foreach ($nextOfKinList as $index => $kin) {
            \App\Models\ApplicationNextOfKin::create([
                'application_id' => $application->id,
                'full_name' => $kin['full_name'],
                'relationship' => $kin['relationship'],
                'phone' => $kin['phone'],
                'alternative_phone' => $kin['alternative_phone'] ?? null,
                'address' => $kin['address'] ?? null,
                'is_primary' => $index === 0,
            ]);
        }

        $this->advanceStage($user, 'next_of_kin_completed');
    }

    public function saveReferral(User $user, array $data): void
    {
        $application = $this->getActiveApplication($user);

        \App\Models\ApplicationReferral::updateOrCreate(
            ['application_id' => $application->id],
            [
                'was_referred' => $data['was_referred'] ?? false,
                'referee_membercode' => $data['referee_membercode'] ?? null,
                'referee_name' => $data['referee_name'] ?? null,
            ]
        );

        $this->advanceStage($user, 'referral_completed');
    }

    public function saveSavingPlan(User $user, array $data): void
    {
        $application = $this->getActiveApplication($user);

        \App\Models\ApplicationSavingPlan::updateOrCreate(
            ['application_id' => $application->id],
            $data
        );

        $this->advanceStage($user, 'saving_plan_completed');
    }

    public function readyForReview(User $user): void
    {
        $this->advanceStage($user, 'ready_for_review');
    }

    public function submitApplication(User $user): MembershipApplication
    {
        $application = $this->getActiveApplication($user);

        $application->update([
            'application_status' => 'submitted',
            'submitted_at' => now(),
            'current_stage' => 'submitted',
        ]);

        return $application;
    }

    public function getActiveApplication(User $user): MembershipApplication
    {
        return $user->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
            ->latest()
            ->firstOrFail();
    }

    public function getApplicationProgress(MembershipApplication $application): array
    {
        $completedStages = [];
        $currentStageIndex = self::STAGE_ORDER[$application->current_stage] ?? -1;

        foreach (self::STAGES as $index => $stage) {
            if ($index < $currentStageIndex) {
                $completedStages[] = $stage;
            }
        }

        return [
            'completed' => $completedStages,
            'current' => $application->current_stage,
            'percentage' => $application->getProgressPercentage(),
            'next_stage' => self::STAGES[$currentStageIndex + 1] ?? null,
        ];
    }

    protected function advanceStage(User $user, string $stage): void
    {
        $application = $this->getActiveApplication($user);

        $currentOrder = self::STAGE_ORDER[$application->current_stage] ?? -1;
        $newOrder = self::STAGE_ORDER[$stage] ?? 0;

        if ($newOrder > $currentOrder) {
            $application->update([
                'current_stage' => $stage,
                'application_status' => 'in_progress',
            ]);
        }
    }
}
