<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationNextOfKin extends Model
{
    protected $fillable = [
        'application_id',
        'full_name',
        'relationship',
        'phone',
        'alternative_phone',
        'address',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'application_id');
    }
}
