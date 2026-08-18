<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationBankAccount extends Model
{
    protected $fillable = [
        'application_id',
        'bank_name',
        'account_name',
        'account_number',
        'branch',
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
