<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberNextOfKin extends Model
{
    protected $fillable = [
        'membercode',
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
}
