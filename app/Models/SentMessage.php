<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentMessage extends Model
{
    protected $fillable = [
        'type',
        'to',
        'from',
        'message',
        'status',
        'message_id',
        'api_response',
    ];

    protected $casts = [
        'api_response' => 'array',
    ];

    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'sent';
    }
}
