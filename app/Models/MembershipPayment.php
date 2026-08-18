<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipPayment extends Model
{
    protected $fillable = [
        'application_id',
        'membercode',
        'amount',
        'payment_method',
        'transaction_reference',
        'gateway_reference',
        'status',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'application_id');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
