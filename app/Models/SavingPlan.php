<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingPlan extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'member_number',
        'membership',
        'goal',
        'period_type', // 'daily', 'weekly', 'monthly'
        'period_value', // number of periods (e.g., 12 months, 52 weeks)
        'start_date',
        'target_date',
        'periodic_amount', // calculated amount to save each period
        'payment_schedule', // JSON array of scheduled payments
        'status',
    ];

    protected $casts = [
        'goal' => 'decimal:2',
        'periodic_amount' => 'decimal:2',
        'start_date' => 'date',
        'target_date' => 'date',
        'payment_schedule' => 'array',
    ];

    public function scopeByMemberNumber($query, $memberNumber)
    {
        return $query->where('member_number', $memberNumber);
    }

    public function scopeByMembership($query, $membership)
    {
        return $query->where('membership', $membership);
    }

    public function scopeByMemberId($query, $memberId)
    {
        return $query->where('member_number', $memberId);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_number', 'membercode');
    }
}
