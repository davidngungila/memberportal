<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanProduct;
use Illuminate\Support\Facades\DB;

class LoanProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('loan_products')->truncate();

        $loanProducts = [
            [
                'name' => 'Normal Loan',
                'code' => 'NL',
                'description' => 'Standard loan for general purposes',
                'min_amount' => 50000,
                'max_amount' => 50000000,
                'interest_rate' => 1.3, // 0.013 = 1.3%
                'min_term_months' => 3,
                'max_term_months' => 36,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'reducing',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => false,
                'requires_guarantor' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Guaranteed Loan',
                'code' => 'GL',
                'description' => 'Loan with guarantor requirement',
                'min_amount' => 100000,
                'max_amount' => 100000000,
                'interest_rate' => 1.4, // 0.014 = 1.4%
                'min_term_months' => 6,
                'max_term_months' => 48,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'reducing',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => false,
                'requires_guarantor' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Quick Loan',
                'code' => 'QL',
                'description' => 'Fast approval short-term loan',
                'min_amount' => 20000,
                'max_amount' => 5000000,
                'interest_rate' => 5.0, // 0.05 = 5%
                'min_term_months' => 1,
                'max_term_months' => 6,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'flat',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => false,
                'requires_guarantor' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Emergency Loan',
                'code' => 'EL',
                'description' => 'Loan for emergency situations',
                'min_amount' => 30000,
                'max_amount' => 10000000,
                'interest_rate' => 2.0, // 0.02 = 2%
                'min_term_months' => 1,
                'max_term_months' => 12,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'reducing',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => false,
                'requires_guarantor' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Staff Loan',
                'code' => 'SL',
                'description' => 'Special loan for staff members',
                'min_amount' => 100000,
                'max_amount' => 20000000,
                'interest_rate' => 0.6, // 0.006 = 0.6%
                'min_term_months' => 6,
                'max_term_months' => 60,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'reducing',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => false,
                'requires_guarantor' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Device Loan',
                'code' => 'DL',
                'description' => 'Loan for purchasing devices/equipment',
                'min_amount' => 50000,
                'max_amount' => 5000000,
                'interest_rate' => 0, // 0 = 0%
                'min_term_months' => 3,
                'max_term_months' => 24,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'flat',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => false,
                'requires_guarantor' => true,
                'status' => 'active',
            ],
            [
                'name' => 'FIA Loan',
                'code' => 'FIA',
                'description' => 'Fixed Investment Account backed loan',
                'min_amount' => 100000,
                'max_amount' => 50000000,
                'interest_rate' => 1.0, // 0.01 = 1%
                'min_term_months' => 12,
                'max_term_months' => 48,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'reducing',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => true,
                'requires_guarantor' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Business Loan',
                'code' => 'BL',
                'description' => 'Loan for business development and expansion',
                'min_amount' => 500000,
                'max_amount' => 200000000,
                'interest_rate' => 1.8, // 0.018 = 1.8%
                'min_term_months' => 6,
                'max_term_months' => 60,
                'processing_fee' => 0,
                'late_fee' => 0,
                'interest_type' => 'reducing',
                'repayment_frequency' => 'monthly',
                'requires_collateral' => true,
                'requires_guarantor' => true,
                'status' => 'active',
            ],
        ];

        foreach ($loanProducts as $product) {
            LoanProduct::create($product);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('Loan products seeded successfully.');
    }
}
