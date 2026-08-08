<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'member_number',
        'loan_number',
        'loan_product_id',
        'principal_amount',
        'interest_rate',
        'term_months',
        'application_date',
        'approval_date',
        'disbursement_date',
        'maturity_date',
        'monthly_payment',
        'total_amount_due',
        'amount_paid',
        'balance',
        'status',
        'purpose',
        'purpose_description',
        'collateral',
        'guarantor',
        'notes',
        'repayment_frequency',
        'preferred_repayment_date',
        'collateral_value',
        'employment_status',
        'employer_name',
        'monthly_income',
        'other_income',
        'work_experience',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'application_date' => 'date',
        'approval_date' => 'date',
        'disbursement_date' => 'date',
        'maturity_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_number', 'member_number');
    }

    public function loanPayments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function loanInformation()
    {
        return $this->hasOne(LoanInformation::class);
    }

    public function repaymentSchedules()
    {
        return $this->hasMany(LoanRepaymentSchedule::class);
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function disbursement()
    {
        return $this->hasOne(LoanDisbursement::class);
    }

    public function scopeByMemberNumber($query, $memberNumber)
    {
        return $query->where('member_number', $memberNumber);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeDefaulted($query)
    {
        return $query->where('status', 'defaulted');
    }
}
