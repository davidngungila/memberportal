<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'membercode',
        'user_id',
        'membership_type_id',
        'full_name',
        'gender',
        'phone',
        'email',
        'status',
        'registration_status',
        'joined_at',
        'registration_date',
        'date_of_birth',
        'national_id',
        'occupation',
        'employer',
        'residential_address',
        'member_type',
        'marital_status',
        'bank_name',
        'bank_branch',
        'account_name',
        'account_number',
        'bank_account_status',
        'mobile_money_provider',
        'mobile_money_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'registration_fee',
        'notes',
        'photo',
        'profile_photo',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'date_of_birth' => 'date',
        'joined_at' => 'datetime',
        'registration_fee' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MemberType::class, 'membership_type_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'membercode', 'membercode');
    }

    public function savings(): HasMany
    {
        return $this->hasMany(SavingPlan::class, 'membercode', 'membercode');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'membercode', 'membercode');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'membercode', 'membercode');
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(MemberBankAccount::class, 'membercode', 'membercode');
    }

    public function nextOfKin(): HasMany
    {
        return $this->hasMany(MemberNextOfKin::class, 'membercode', 'membercode');
    }

    public function savingBalances(): HasMany
    {
        return $this->hasMany(SavingBalance::class, 'membercode', 'membercode');
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class, 'membercode', 'membercode');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(MembershipApplication::class, 'membercode', 'membercode');
    }

    public function scopeByMembercode($query, string $membercode)
    {
        return $query->where('membercode', $membercode);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?? 'Unknown';
    }
}
