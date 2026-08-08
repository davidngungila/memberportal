<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_number',
        'full_name',
        'gender',
        'phone',
        'email',
        'status',
        'registration_date',
        'date_of_birth',
        'national_id',
        'occupation',
        'employer',
        'residential_address',
        'member_type',
        'marital_status',
        'bank_name',
        'bank_branch',
        'account_name',
        'account_number',
        'bank_account_status',
        'mobile_money_provider',
        'mobile_money_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'registration_fee',
        'notes',
        'photo',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'date_of_birth' => 'date',
        'registration_fee' => 'decimal:2',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class, 'member_number', 'member_number');
    }
}
