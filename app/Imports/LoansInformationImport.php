<?php

namespace App\Imports;

use App\Models\LoanInformation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class LoansInformationImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
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
        $loanType = $row['loan_type'] ?? $row['loantype'] ?? null;
        $loanAmount = $row['loan_amount'] ?? $row['loanamount'] ?? null;
        $nature = $row['nature'] ?? null;
        $interestRatePm = $row['interest_rate_pm'] ?? $row['interestratepm'] ?? null;
        $durationMonths = $row['duration_months'] ?? $row['durationmonths'] ?? null;
        $loanStartDate = $row['loan_start_date'] ?? $row['loanstartdate'] ?? null;
        $loanMaturityDate = $row['loan_maturity_date'] ?? $row['loanmaturitydate'] ?? null;
        $totalPayable = $row['total_payable'] ?? $row['totalpayable'] ?? null;
        $monthlyInstallment = $row['monthly_installment'] ?? $row['monthlyinstallment'] ?? null;
        $monthlyPrincipal = $row['monthly_principal'] ?? $row['monthlyprincipal'] ?? null;
        $principalPaidToDate = $row['principal_paid_to_date'] ?? $row['principalpaidtodate'] ?? null;
        $terminationFee = $row['termination_fee'] ?? $row['terminationfee'] ?? null;
        $totalPaid = $row['total_paid'] ?? $row['totalpaid'] ?? null;
        $outstandingBalance = $row['outstanding_balance'] ?? $row['outstandingbalance'] ?? null;
        $loanStatus = $row['loan_status'] ?? $row['loanstatus'] ?? null;
        $loanGuarantor = $row['loan_guarantor'] ?? $row['loanguarantor'] ?? null;
        $numberOfPaidInstallments = $row['number_of_paid_installments'] ?? $row['numberofpaidinstallments'] ?? null;
        $numberOfUnpaidInstallments = $row['number_of_unpaid_installments'] ?? $row['numberofunpaidinstallments'] ?? null;
        $thisMonthLoanStatus = $row['this_month_loan_status'] ?? $row['thismonthloanstatus'] ?? null;
        $balanceAfterPayment = $row['balance_after_payment'] ?? $row['balanceafterpayment'] ?? null;
        $loanAgreementRefNo = $row['loan_agreement_ref_no'] ?? $row['loanagreementrefno'] ?? null;
        
        // Skip rows with missing required fields
        if (empty($loanId) || empty($customerId) || empty($loanType) || empty($loanAmount)) {
            return null;
        }

        // Handle dates
        try {
            if ($loanStartDate) {
                if (is_numeric($loanStartDate)) {
                    $loanStartDate = \Carbon\Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($loanStartDate)->format('Y-m-d'));
                } else {
                    $loanStartDate = \Carbon\Carbon::parse($loanStartDate);
                }
            }
            
            if ($loanMaturityDate) {
                if (is_numeric($loanMaturityDate)) {
                    $loanMaturityDate = \Carbon\Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($loanMaturityDate)->format('Y-m-d'));
                } else {
                    $loanMaturityDate = \Carbon\Carbon::parse($loanMaturityDate);
                }
            }
        } catch (\Exception $e) {
            // Continue with null dates if parsing fails
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
        
        return new LoanInformation([
            'loan_id' => $loanId,
            'user_id' => $userId,
            'customer_id' => $customerId,
            'loan_type' => $loanType,
            'loan_amount' => $loanAmount,
            'nature' => $nature,
            'interest_rate_pm' => $interestRatePm,
            'duration_months' => $durationMonths,
            'loan_start_date' => $loanStartDate,
            'loan_maturity_date' => $loanMaturityDate,
            'total_payable' => $totalPayable,
            'monthly_installment' => $monthlyInstallment,
            'monthly_principal' => $monthlyPrincipal,
            'principal_paid_to_date' => $principalPaidToDate,
            'termination_fee' => $terminationFee,
            'total_paid' => $totalPaid,
            'outstanding_balance' => $outstandingBalance,
            'loan_status' => $loanStatus,
            'loan_guarantor' => $loanGuarantor,
            'number_of_paid_installments' => $numberOfPaidInstallments,
            'number_of_unpaid_installments' => $numberOfUnpaidInstallments,
            'this_month_loan_status' => $thisMonthLoanStatus,
            'balance_after_payment' => $balanceAfterPayment,
            'loan_agreement_ref_no' => $loanAgreementRefNo,
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
