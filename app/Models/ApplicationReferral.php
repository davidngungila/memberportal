<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationReferral extends Model
{
    protected $fillable = [
        'application_id',
        'was_referred',
        'referee_membercode',
        'referee_name',
    ];

    protected $casts = [
        'was_referred' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'application_id');
    }
}
