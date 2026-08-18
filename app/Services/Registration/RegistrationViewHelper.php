<?php

namespace App\Services\Registration;

use App\Models\MembershipApplication;
use App\Models\User;

class RegistrationViewHelper
{
    protected ?MembershipApplication $application;
    protected array $stageOrder;
    protected array $stageNames;

    public function __construct()
    {
        $this->stageOrder = RegistrationService::STAGE_ORDER;
        $this->stageNames = [
            'account_created' => 'Account',
            'email_verified' => 'Email Verified',
            'phone_verified' => 'Phone Verified',
            'password_created' => 'Password',
            'membership_selected' => 'Membership Type',
            'payment_completed' => 'Payment',
            'personal_details_completed' => 'Personal Details',
            'profile_completed' => 'Profile Photo',
            'bank_details_completed' => 'Bank Details',
            'next_of_kin_completed' => 'Next of Kin',
            'referral_completed' => 'Referral',
            'saving_plan_completed' => 'Saving Plan',
            'ready_for_review' => 'Review',
            'submitted' => 'Submit',
        ];
    }

    public function setApplication(?MembershipApplication $application): void
    {
        $this->application = $application;
    }

    public function getApplication(): ?MembershipApplication
    {
        return $this->application;
    }

    public function getStageStatus(string $stage, string $route): string
    {
        if (!$this->application) {
            return 'sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-primary-400 opacity-50';
        }

        $currentOrder = $this->stageOrder[$this->application->current_stage] ?? -1;
        $requiredOrder = $this->stageOrder[$stage] ?? 0;

        $isCurrent = request()->routeIs($route);
        $isCompleted = $currentOrder > $requiredOrder;
        $isAvailable = $currentOrder >= $requiredOrder;

        $classes = ['sidebar-item', 'flex', 'items-center', 'gap-3', 'px-3', 'py-2.5', 'rounded-lg', 'text-sm'];

        if ($isCurrent) {
            $classes[] = 'active';
        } elseif ($isCompleted) {
            $classes[] = 'text-primary-100';
        } elseif ($isAvailable) {
            $classes[] = 'text-primary-200';
        } else {
            $classes[] = 'text-primary-400';
            $classes[] = 'opacity-50';
        }

        return implode(' ', $classes);
    }

    public function getStageIcon(string $stage): string
    {
        if (!$this->application) {
            return '<span class="text-primary-500 text-xs"><i class="fa-regular fa-circle"></i></span>';
        }

        $currentOrder = $this->stageOrder[$this->application->current_stage] ?? -1;
        $requiredOrder = $this->stageOrder[$stage] ?? 0;

        if ($currentOrder > $requiredOrder) {
            return '<span class="text-primary-300 text-xs"><i class="fa-solid fa-check"></i></span>';
        } elseif ($currentOrder === $requiredOrder) {
            return '<span class="text-yellow-300 text-xs"><i class="fa-solid fa-arrow-right"></i></span>';
        } else {
            return '<span class="text-primary-500 text-xs"><i class="fa-regular fa-circle"></i></span>';
        }
    }

    public function getProgress(): array
    {
        if (!$this->application) {
            return ['completed' => [], 'current' => 'account_created', 'percentage' => 0, 'next_stage' => null];
        }

        $service = app(RegistrationService::class);
        return $service->getApplicationProgress($this->application);
    }
}
