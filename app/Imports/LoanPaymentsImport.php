<?php

namespace App\Imports;

use App\Models\LoanPayment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class LoanPaymentsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private $importedCount = 0;
    private $skippedCount = 0;

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip completely empty rows early to save memory
        if (empty(array_filter($row, function($value) {
            return $value !== null && $value !== '';
        }))) {
            return null;
        }
        
        // Map Excel column names to database field names
        $loanId = $row['loan_id'] ?? $row['loanid'] ?? null;
        $customerId = $row['customer_id'] ?? $row['customerid'] ?? null;
        $paymentAmount = $row['payment_amount'] ?? $row['paymentamount'] ?? null;
        $paymentDate = $row['payment_date'] ?? $row['paymentdate'] ?? null;
        $paymentMethod = $row['payment_method'] ?? $row['paymentmethod'] ?? null;
        $referenceNumber = $row['reference_number'] ?? $row['referencenumber'] ?? null;
        $principalAmount = $row['principal_amount'] ?? $row['principalamount'] ?? null;
        
        // Skip rows with missing required fields
        if (empty($loanId) || empty($customerId) || empty($paymentAmount) || empty($paymentDate)) {
            return null;
        }

        // Skip rows with formula values (starting with '=')
        if (is_string($paymentDate) && strpos($paymentDate, '=') === 0) {
            return null;
        }

        try {
            // Handle Excel serial date format (numbers like 46229)
            if (is_numeric($paymentDate)) {
                $paymentDate = \Carbon\Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($paymentDate)->format('Y-m-d'));
            } else {
                $paymentDate = \Carbon\Carbon::parse($paymentDate);
            }
        } catch (\Exception $e) {
            // Skip rows with unparseable dates
            return null;
        }

        // Look up user_id from customer_id (member_number)
        $userId = null;
        if (!empty($customerId)) {
            $user = \App\Models\User::where('membercode', $customerId)->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        $this->importedCount++;
        
        return new LoanPayment([
            'loan_id' => $loanId,
            'user_id' => $userId,
            'customer_id' => $customerId,
            'payment_amount' => $paymentAmount,
            'payment_date' => $paymentDate,
            'payment_method' => $paymentMethod,
            'reference_number' => $referenceNumber,
            'principal_amount' => $principalAmount,
        ]);
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}
