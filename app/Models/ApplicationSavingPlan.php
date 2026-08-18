<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationSavingPlan extends Model
{
    protected $fillable = [
        'application_id',
        'plan_name',
        'frequency',
        'target_amount',
        'periodic_amount',
        'expected_saving_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'periodic_amount' => 'decimal:2',
        'expected_saving_date' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'application_id');
    }
}
