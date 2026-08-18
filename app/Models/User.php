<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\DatabaseNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'member_number',
        'phone',
        'gender',
        'address',
        'occupation',
        'employer',
        'branch',
        'photo',
        'status',
        'member_type_id',
        'registration_date',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'registration_date' => 'date',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable');
    }

    public function memberType()
    {
        return $this->belongsTo(MemberType::class);
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function swfMember()
    {
        return $this->hasOne(SwfMember::class);
    }

    public function sharePurchases()
    {
        return $this->hasMany(SharePurchase::class);
    }

    public function shareCertificates()
    {
        return $this->hasMany(ShareCertificate::class);
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }

    public function membershipApplications(): HasMany
    {
        return $this->hasMany(MembershipApplication::class);
    }

    public function activeApplication()
    {
        return $this->hasOne(MembershipApplication::class)
            ->whereIn('application_status', ['draft', 'in_progress', 'submitted', 'under_review', 'correction_required'])
            ->latest();
    }

    public function latestApplication()
    {
        return $this->hasOne(MembershipApplication::class)->latest();
    }

    public function verificationCode()
    {
        return $this->hasOne(VerificationCode::class)->latest();
    }

    public function isApprovedMember(): bool
    {
        return $this->member !== null && $this->member->status === 'Active';
    }

    public function hasActiveApplication(): bool
    {
        return $this->membershipApplications()
            ->whereIn('application_status', ['draft', 'in_progress', 'submitted', 'under_review', 'correction_required'])
            ->exists();
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        if (is_array($role)) {
            foreach ($role as $r) {
                if ($this->hasRole($r)) {
                    return true;
                }
            }
            return false;
        }

        return $role->intersect($this->roles)->isNotEmpty();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->hasRole('member') || $this->role === 'member';
    }

    public function assignRole($role): self
    {
        if (is_string($role)) {
            $roleModel = Role::where('name', $role)->firstOrFail();
            $this->roles()->syncWithoutDetaching([$roleModel->id]);
        } elseif (is_int($role)) {
            $this->roles()->syncWithoutDetaching([$role]);
        } else {
            $this->roles()->syncWithoutDetaching([$role->id]);
        }

        return $this;
    }
}
