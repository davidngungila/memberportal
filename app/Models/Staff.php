<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'member_id',
        'staff_number',
        'full_name',
        'gender',
        'phone',
        'email',
        'date_of_birth',
        'national_id',
        'marital_status',
        'residential_address',
        'department',
        'position',
        'employment_type',
        'hire_date',
        'end_date',
        'salary',
        'branch',
        'highest_qualification',
        'field_of_study',
        'institution',
        'year_of_graduation',
        'professional_license',
        'license_expiry',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'status',
        'notes',
        'photo',
    ];

    const STATUSES = ['active', 'inactive', 'suspended', 'terminated'];

    const EMPLOYMENT_TYPES = ['full_time', 'part_time', 'contract', 'intern'];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'end_date' => 'date',
        'license_expiry' => 'date',
        'salary' => 'decimal:2',
        'year_of_graduation' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Staff $staff) {
            if (empty($staff->staff_number)) {
                $staff->staff_number = 'STF' . date('ymd') . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (Staff $staff) {
            if ($staff->status && !in_array($staff->status, self::STATUSES)) {
                throw new \InvalidArgumentException("Invalid status: {$staff->status}. Must be one of: " . implode(', ', self::STATUSES));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?? 'Unknown';
    }
}
