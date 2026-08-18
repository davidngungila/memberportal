<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationPersonalDetail extends Model
{
    protected $fillable = [
        'application_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'nationality',
        'national_id_number',
        'marital_status',
        'occupation',
        'employer',
        'region',
        'district',
        'ward',
        'street',
        'address',
        'email',
        'phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'application_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->middle_name ?? '') . ' ' . $this->last_name);
    }
}
