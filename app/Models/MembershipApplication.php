<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MembershipApplication extends Model
{
    protected $fillable = [
        'application_number',
        'user_id',
        'membership_type_id',
        'membercode',
        'payment_status',
        'application_status',
        'current_stage',
        'rejection_reason',
        'correction_notes',
        'submitted_at',
        'reviewed_at',
        'approved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MemberType::class, 'membership_type_id');
    }

    public function personalDetail(): HasOne
    {
        return $this->hasOne(ApplicationPersonalDetail::class, 'application_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(ApplicationBankAccount::class, 'application_id');
    }

    public function nextOfKin(): HasMany
    {
        return $this->hasMany(ApplicationNextOfKin::class, 'application_id');
    }

    public function referral(): HasOne
    {
        return $this->hasOne(ApplicationReferral::class, 'application_id');
    }

    public function savingPlan(): HasOne
    {
        return $this->hasOne(ApplicationSavingPlan::class, 'application_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MembershipPayment::class, 'application_id');
    }

    public function successfulPayment(): HasOne
    {
        return $this->hasOne(MembershipPayment::class, 'application_id')
            ->where('status', 'successful')
            ->latest('paid_at');
    }

    public function isDraft(): bool
    {
        return $this->application_status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->application_status === 'in_progress';
    }

    public function isSubmitted(): bool
    {
        return $this->application_status === 'submitted';
    }

    public function isUnderReview(): bool
    {
        return $this->application_status === 'under_review';
    }

    public function isApproved(): bool
    {
        return $this->application_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->application_status === 'rejected';
    }

    public function needsCorrection(): bool
    {
        return $this->application_status === 'correction_required';
    }

    public function getProgressPercentage(): int
    {
        $stages = [
            'membership_selected',
            'payment_completed',
            'personal_details_completed',
            'profile_completed',
            'bank_details_completed',
            'next_of_kin_completed',
            'referral_completed',
            'saving_plan_completed',
            'ready_for_review',
        ];

        $stageOrder = array_flip($stages);
        $currentStageOrder = $stageOrder[$this->current_stage] ?? -1;

        return (int) round((($currentStageOrder + 1) / count($stages)) * 100);
    }

    public static function generateApplicationNumber(): string
    {
        $year = date('Y');
        $lastApplication = static::where('application_number', 'like', "APP-{$year}-%")
            ->orderByDesc('application_number')
            ->first();

        if ($lastApplication) {
            $lastNumber = (int) substr($lastApplication->application_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('APP-%s-%06d', $year, $newNumber);
    }
}
