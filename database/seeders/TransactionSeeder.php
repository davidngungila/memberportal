<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing transactions
        DB::table('transactions')->truncate();

        $transactionsData = [
            [
                'date' => '2025-03-01',
                'membercode' => 'FND1',
                'transaction_type' => 'Opening Balance',
                'reference_no' => 'Opening balance',
                'amount' => 24158.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'ASC75',
                'transaction_type' => 'RDA-Withdrawal',
                'reference_no' => 'IB79268101032611',
                'amount' => -400000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'GRP3',
                'transaction_type' => 'RDA-Withdrawal',
                'reference_no' => 'IB79239201032610 -Charity',
                'amount' => -200000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'SCH55',
                'transaction_type' => 'RDA-Withdrawal',
                'reference_no' => '403IBFT260600009',
                'amount' => -3000000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'SCH55',
                'transaction_type' => 'Flexi-Withdrawal',
                'reference_no' => '403IBFT260600009',
                'amount' => -100000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'SCH41',
                'transaction_type' => 'RDA-Withdrawal',
                'reference_no' => '403OPIB260600501',
                'amount' => -1630000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'SCH41',
                'transaction_type' => 'Flexi-Withdrawal',
                'reference_no' => '403OPIB260600501',
                'amount' => -250000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'ASC129',
                'transaction_type' => 'Flexi-Deposit',
                'reference_no' => '19ca9cdada b52b8f',
                'amount' => 100000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'FND2',
                'transaction_type' => 'RDA-Deposit',
                'reference_no' => 'IB79677801 032616',
                'amount' => 250000.00,
            ],
            [
                'date' => '2026-03-01',
                'membercode' => 'ASC64',
                'transaction_type' => 'RDA-Deposit',
                'reference_no' => 'IB79677801 032616',
                'amount' => 200000.00,
            ],
        ];

        foreach ($transactionsData as $transaction) {
            DB::table('transactions')->insert($transaction);
        }

        // Re-enable foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Transaction seeder completed successfully. ' . count($transactionsData) . ' transactions seeded.');
    }
}
