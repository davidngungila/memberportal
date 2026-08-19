<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersTemplateExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([
            [
                'M001',
                'John Doe',
                'Male',
                '+255712345678',
                'john.doe@example.com',
                'Active',
                '2024-01-15',
                '1990-05-20',
                '1234567890123456',
                'Accountant',
                'ABC Company Ltd',
                'P.O. Box 1234, Dar es Salaam',
                'Regular',
                'Married',
                'CRDB Bank',
                'Dar es Salaam Branch',
                'John Doe',
                '0123456789',
                'Active',
                'M-Pesa',
                '+255712345678',
                'Jane Doe',
                '+255798765432',
                'Spouse',
                '50000',
                'Sample member record',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'membercode',
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
        ];
    }
}
