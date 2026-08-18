<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationProfile extends Model
{
    protected $fillable = [
        'type',
        'name',
        'sms_api_key',
        'messaging_sender_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
