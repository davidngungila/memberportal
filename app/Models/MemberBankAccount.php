<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberBankAccount extends Model
{
    protected $fillable = [
        'membercode',
        'bank_name',
        'account_name',
        'account_number',
        'branch',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
