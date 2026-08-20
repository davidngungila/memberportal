<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Services\GoogleSheetService;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class GoogleSheetRepository implements GoogleSheetRepositoryInterface
{
    protected const CACHE_TTL_SECONDS = 300;

    protected const CACHE_PREFIX = 'google_sheets:';

    protected const ALLOWED_STATEMENT_TYPES = [
        'savings',
        'loans',
        'deposits',
        'swf',
        'investments',
    ];

    protected const SHEET_RANGES = [
        'members' => 'Members!A:K',
        'loans' => 'Loans!A:K',
        'savings' => 'Savings!A:F',
        'deposits' => 'Deposits!A:I',
        'swf' => 'SWF!A:E',
        'investments' => 'Investments!A:H',
        'shares' => 'Shares!A:F',
    ];

    protected ?string $spreadsheetId = null;

    protected bool $googleApiConfigured = false;

    public function __construct(
        protected GoogleSheetService $googleSheetService,
        protected MockGoogleSheetRepository $fallback,
        protected CacheRepository $cache,
        protected LoggerInterface $logger,
    ) {
        $this->initializeFromConfig();
    }

    public function getSheetData(string $sheetName, ?string $range = null): array
    {
        $sheetName = trim($sheetName);
        if ($sheetName === '') {
            throw new InvalidArgumentException('Sheet name cannot be empty.');
        }

        $normalizedName = strtolower($sheetName);
        $resolvedRange = $range ?? (self::SHEET_RANGES[$normalizedName] ?? "{$sheetName}!A:Z");

        $cacheKey = $this->cacheKey('sheet', $normalizedName, md5($resolvedRange));

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($normalizedName, $resolvedRange): array {
                if ($this->spreadsheetId === null) {
                    throw new RuntimeException('Spreadsheet ID is not configured.');
                }

                $rawRows = $this->googleSheetService->fetchSheet($this->spreadsheetId, $resolvedRange);

                return $this->googleSheetService->parseSheetRows($rawRows);
            },
            fallbackCallable: fn (): array => $this->fallback->getSheetData($sheetName, $range),
            operation: "getSheetData({$sheetName})",
        );
    }

    public function getMemberByNumber(string $memberNumber): ?array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $cacheKey = $this->cacheKey('member', $memberNumber);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($memberNumber): ?array {
                $allMembers = $this->getAllMembersLive();
                foreach ($allMembers as $member) {
                    $currentNumber = strtoupper(trim((string) ($member['member_number'] ?? $member['MemberNumber'] ?? '')));
                    if ($currentNumber === $memberNumber) {
                        return $member;
                    }
                }

                return null;
            },
            fallbackCallable: fn (): ?array => $this->fallback->getMemberByNumber($memberNumber),
            operation: "getMemberByNumber({$memberNumber})",
        );
    }

    public function getAllMembers(): array
    {
        $cacheKey = $this->cacheKey('all_members');

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: fn (): array => $this->getAllMembersLive(),
            fallbackCallable: fn (): array => $this->fallback->getAllMembers(),
            operation: 'getAllMembers()',
        );
    }

    public function getMemberLoans(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $cacheKey = $this->cacheKey('loans', $memberNumber);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($memberNumber): array {
                $allLoans = $this->getSheetData('loans');

                return array_values(array_filter($allLoans, static function (array $loan) use ($memberNumber): bool {
                    $current = strtoupper(trim((string) ($loan['member_number'] ?? $loan['MemberNumber'] ?? '')));

                    return $current === $memberNumber;
                }));
            },
            fallbackCallable: fn (): array => $this->fallback->getMemberLoans($memberNumber),
            operation: "getMemberLoans({$memberNumber})",
        );
    }

    public function getMemberSavings(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $cacheKey = $this->cacheKey('savings', $memberNumber);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($memberNumber): array {
                $allRows = $this->getSheetData('savings');

                foreach ($allRows as $row) {
                    $current = strtoupper(trim((string) ($row['member_number'] ?? $row['MemberNumber'] ?? '')));
                    if ($current === $memberNumber) {
                        $transactions = [];
                        if (! empty($row['transactions']) && is_array($row['transactions'])) {
                            $transactions = $row['transactions'];
                        }

                        return [
                            'balance' => isset($row['balance']) ? (float) $row['balance'] : 0.0,
                            'interest_earned' => isset($row['interest_earned']) ? (float) $row['interest_earned'] : 0.0,
                            'running_balance' => isset($row['running_balance']) ? (float) $row['running_balance'] : 0.0,
                            'transactions' => $transactions,
                            'member_number' => $memberNumber,
                        ];
                    }
                }

                return [
                    'balance' => 0.0,
                    'interest_earned' => 0.0,
                    'running_balance' => 0.0,
                    'transactions' => [],
                    'member_number' => $memberNumber,
                ];
            },
            fallbackCallable: fn (): array => $this->fallback->getMemberSavings($memberNumber),
            operation: "getMemberSavings({$memberNumber})",
        );
    }

    public function getMemberDeposits(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $cacheKey = $this->cacheKey('deposits', $memberNumber);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($memberNumber): array {
                $allRows = $this->getSheetData('deposits');

                return array_values(array_filter($allRows, static function (array $row) use ($memberNumber): bool {
                    $current = strtoupper(trim((string) ($row['member_number'] ?? $row['MemberNumber'] ?? '')));

                    return $current === $memberNumber;
                }));
            },
            fallbackCallable: fn (): array => $this->fallback->getMemberDeposits($memberNumber),
            operation: "getMemberDeposits({$memberNumber})",
        );
    }

    public function getMemberSwf(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $cacheKey = $this->cacheKey('swf', $memberNumber);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($memberNumber): array {
                $allRows = $this->getSheetData('swf');

                foreach ($allRows as $row) {
                    $current = strtoupper(trim((string) ($row['member_number'] ?? $row['MemberNumber'] ?? '')));
                    if ($current === $memberNumber) {
                        return [
                            'total_contribution' => isset($row['total_contribution']) ? (float) $row['total_contribution'] : 0.0,
                            'benefits' => isset($row['benefits']) ? (float) $row['benefits'] : 0.0,
                            'current_balance' => isset($row['current_balance']) ? (float) $row['current_balance'] : 0.0,
                            'contribution_history' => $row['contribution_history'] ?? [],
                            'member_number' => $memberNumber,
                        ];
                    }
                }

                return [
                    'total_contribution' => 0.0,
                    'benefits' => 0.0,
                    'current_balance' => 0.0,
                    'contribution_history' => [],
                    'member_number' => $memberNumber,
                ];
            },
            fallbackCallable: fn (): array => $this->fallback->getMemberSwf($memberNumber),
            operation: "getMemberSwf({$memberNumber})",
        );
    }

    public function getMemberInvestments(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $cacheKey = $this->cacheKey('investments', $memberNumber);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($memberNumber): array {
                $allRows = $this->getSheetData('investments');

                return array_values(array_filter($allRows, static function (array $row) use ($memberNumber): bool {
                    $current = strtoupper(trim((string) ($row['member_number'] ?? $row['MemberNumber'] ?? '')));

                    return $current === $memberNumber;
                }));
            },
            fallbackCallable: fn (): array => $this->fallback->getMemberInvestments($memberNumber),
            operation: "getMemberInvestments({$memberNumber})",
        );
    }

    public function getMemberShares(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $cacheKey = $this->cacheKey('shares', $memberNumber);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($memberNumber): array {
                $allRows = $this->getSheetData('shares');

                return array_values(array_filter($allRows, static function (array $row) use ($memberNumber): bool {
                    $current = strtoupper(trim((string) ($row['member_number'] ?? $row['MemberNumber'] ?? '')));

                    return $current === $memberNumber;
                }));
            },
            fallbackCallable: fn (): array => $this->fallback->getMemberShares($memberNumber),
            operation: "getMemberShares({$memberNumber})",
        );
    }

    public function getMemberStatements(string $memberNumber, string $type): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        $type = strtolower(trim($type));

        if (! in_array($type, self::ALLOWED_STATEMENT_TYPES, true)) {
            throw new InvalidArgumentException(
                "Invalid statement type: {$type}. Allowed types: ".implode(', ', self::ALLOWED_STATEMENT_TYPES)
            );
        }

        $cacheKey = $this->cacheKey('statements', $memberNumber, $type);

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: fn (): array => match ($type) {
                'savings' => $this->getMemberSavings($memberNumber)['transactions'] ?? [],
                'loans' => $this->buildLoanStatement($this->getMemberLoans($memberNumber)),
                'deposits' => $this->getMemberDeposits($memberNumber),
                'swf' => $this->getMemberSwf($memberNumber)['contribution_history'] ?? [],
                'investments' => $this->flattenInvestmentHistory($this->getMemberInvestments($memberNumber)),
                default => [],
            },
            fallbackCallable: fn (): array => $this->fallback->getMemberStatements($memberNumber, $type),
            operation: "getMemberStatements({$memberNumber}, {$type})",
        );
    }

    public function getDashboardTotals(): array
    {
        $cacheKey = $this->cacheKey('dashboard_totals');

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function (): array {
                $members = $this->getAllMembers();
                $savingsRows = $this->getSheetData('savings');
                $loansRows = $this->getSheetData('loans');
                $depositsRows = $this->getSheetData('deposits');
                $investmentsRows = $this->getSheetData('investments');
                $swfRows = $this->getSheetData('swf');

                $totalSavings = 0;
                foreach ($savingsRows as $row) {
                    $totalSavings += (float) ($row['balance'] ?? 0);
                }

                $totalLoans = 0;
                foreach ($loansRows as $row) {
                    $totalLoans += (float) ($row['outstanding_balance'] ?? 0);
                }

                $totalDeposits = 0;
                foreach ($depositsRows as $row) {
                    $totalDeposits += (float) ($row['current_value'] ?? $row['amount'] ?? 0);
                }

                $totalInvestments = 0;
                foreach ($investmentsRows as $row) {
                    $totalInvestments += (float) ($row['current_value'] ?? 0);
                }

                $totalSwf = 0;
                foreach ($swfRows as $row) {
                    $totalSwf += (float) ($row['current_balance'] ?? 0);
                }

                return [
                    'total_members' => count($members),
                    'total_savings' => $totalSavings,
                    'total_loans' => $totalLoans,
                    'total_deposits' => $totalDeposits,
                    'total_investments' => $totalInvestments,
                    'total_swf' => $totalSwf,
                ];
            },
            fallbackCallable: fn (): array => $this->fallback->getDashboardTotals(),
            operation: 'getDashboardTotals()',
        );
    }

    public function searchMembers(string $query): array
    {
        $query = trim($query);
        $cacheKey = $this->cacheKey('search_members', md5($query));

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function () use ($query): array {
                $allMembers = $this->getAllMembers();
                if ($query === '') {
                    return $allMembers;
                }

                $lowerQuery = strtolower($query);

                return array_values(array_filter($allMembers, static function (array $member) use ($lowerQuery): bool {
                    $haystack = strtolower(implode(' ', [
                        $member['name'] ?? '',
                        $member['member_number'] ?? '',
                        $member['phone'] ?? '',
                        $member['email'] ?? '',
                        $member['branch'] ?? '',
                        $member['occupation'] ?? '',
                        $member['employer'] ?? '',
                    ]));

                    return str_contains($haystack, $lowerQuery);
                }));
            },
            fallbackCallable: fn (): array => $this->fallback->searchMembers($query),
            operation: "searchMembers(\"{$query}\")",
        );
    }

    public function addMember(array $memberData): bool
    {
        try {
            if ($this->googleApiConfigured && $this->googleSheetService->isReady()) {
                $this->googleSheetService->appendRow($this->spreadsheetId, 'Members!A:K', $memberData);
                $this->clearAllCache();
                return true;
            }

            return $this->fallback->addMember($memberData);
        } catch (Throwable $e) {
            $this->logger->error('GoogleSheetRepository: Failed to add member', [
                'error' => $e->getMessage(),
                'member_number' => $memberData['member_number'] ?? null,
            ]);
            return false;
        }
    }

    public function getLastSyncInfo(): array
    {
        $cacheKey = $this->cacheKey('last_sync_info');

        return $this->executeWithCacheAndFallback(
            cacheKey: $cacheKey,
            liveCallable: function (): array {
                return [
                    'last_synced_at' => Carbon::now()->toDateTimeString(),
                    'source' => 'Google Sheets API',
                    'status' => $this->googleApiConfigured ? 'success' : 'fallback',
                    'spreadsheet_id' => $this->spreadsheetId,
                    'service_ready' => $this->googleSheetService->isReady(),
                    'next_sync_at' => Carbon::now()->addSeconds(self::CACHE_TTL_SECONDS)->toDateTimeString(),
                    'records_synced' => [
                        'members' => count($this->getAllMembers()),
                        'loans' => count($this->getSheetData('loans')),
                    ],
                    'duration_seconds' => 0,
                ];
            },
            fallbackCallable: fn (): array => $this->fallback->getLastSyncInfo(),
            operation: 'getLastSyncInfo()',
            cacheTtl: 30,
        );
    }

    public function isUsingFallback(): bool
    {
        return ! $this->googleApiConfigured || ! $this->googleSheetService->isReady();
    }

    public function clearAllCache(): void
    {
        $keys = [
            $this->cacheKey('all_members'),
            $this->cacheKey('dashboard_totals'),
            $this->cacheKey('last_sync_info'),
        ];
        foreach (self::SHEET_RANGES as $name => $range) {
            $keys[] = $this->cacheKey('sheet', $name, md5($range));
        }
        foreach ($keys as $key) {
            $this->cache->forget($key);
        }
        $this->logger->info('GoogleSheetRepository: Cleared all cached entries.', ['keys_flushed' => count($keys)]);
    }

    protected function executeWithCacheAndFallback(
        string $cacheKey,
        Closure $liveCallable,
        Closure $fallbackCallable,
        string $operation,
        ?int $cacheTtl = null,
    ): mixed {
        $ttl = $cacheTtl ?? self::CACHE_TTL_SECONDS;

        try {
            return $this->cache->remember($cacheKey, $ttl, function () use ($liveCallable, $fallbackCallable, $operation): mixed {
                if (! $this->googleApiConfigured || ! $this->googleSheetService->isReady()) {
                    $this->logger->notice("GoogleSheetRepository: Google API not configured for {$operation}. Using mock fallback data.");

                    return $fallbackCallable();
                }

                try {
                    $result = $liveCallable();
                    $this->logger->debug("GoogleSheetRepository: {$operation} completed from live Google Sheets API.");

                    return $result;
                } catch (Throwable $e) {
                    $this->logger->error("GoogleSheetRepository: {$operation} failed. Falling back to mock data.", [
                        'error' => $e->getMessage(),
                        'class' => get_class($e),
                    ]);

                    return $fallbackCallable();
                }
            });
        } catch (Throwable $e) {
            $this->logger->critical("GoogleSheetRepository: {$operation} encountered critical error. Attempting emergency fallback.", [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            try {
                return $fallbackCallable();
            } catch (Throwable $fallbackError) {
                $this->logger->emergency('GoogleSheetRepository: Both live data and fallback failed.', [
                    'operation' => $operation,
                    'live_error' => $e->getMessage(),
                    'fallback_error' => $fallbackError->getMessage(),
                ]);

                throw new RuntimeException(
                    "Repository operation {$operation} failed: live error ({$e->getMessage()}) and fallback failed ({$fallbackError->getMessage()})",
                    0,
                    $e
                );
            }
        }
    }

    protected function getAllMembersLive(): array
    {
        return $this->getSheetData('members');
    }

    protected function validateMemberNumber(string $memberNumber): string
    {
        $memberNumber = strtoupper(trim($memberNumber));
        if ($memberNumber === '') {
            throw new InvalidArgumentException('Member number cannot be empty.');
        }

        // Allow longer strings for encrypted IDs (up to 500 chars for encrypted data)
        if (strlen($memberNumber) > 500) {
            throw new InvalidArgumentException('Member number cannot exceed 500 characters.');
        }

        // For regular member numbers (shorter), be more permissive
        // For encrypted IDs (longer), allow base64-like characters
        if (strlen($memberNumber) <= 10) {
            // Regular member number - allow alphanumeric and common separators
            if (! preg_match('/^[A-Za-z0-9\-]+$/', $memberNumber)) {
                throw new InvalidArgumentException('Member number contains invalid characters. Only alphanumeric and hyphen are allowed for member numbers.');
            }
        } else {
            // Encrypted ID - allow base64-like characters
            if (! preg_match('/^[A-Za-z0-9_\-+\/=]+$/', $memberNumber)) {
                throw new InvalidArgumentException('Member number contains invalid characters. Only alphanumeric, underscore, hyphen, plus, slash, and equals are allowed.');
            }
        }

        return $memberNumber;
    }

    protected function initializeFromConfig(): void
    {
        try {
            $this->spreadsheetId = config('services.google.sheets.spreadsheet_id')
                ?? config('google.spreadsheet_id')
                ?? env('GOOGLE_SPREADSHEET_ID');

            $serviceAccountConfig = config('services.google.sheets.service_account')
                ?? config('google.service_account')
                ?? null;

            if (is_array($serviceAccountConfig) && ! empty($serviceAccountConfig) && $this->spreadsheetId !== null) {
                $configWithSpreadsheet = $serviceAccountConfig;
                $configWithSpreadsheet['spreadsheet_id'] = $this->spreadsheetId;
                $this->googleSheetService->authenticateUsingServiceAccount($configWithSpreadsheet);
                $this->googleApiConfigured = true;
                $this->logger->info('GoogleSheetRepository: Google Sheets API configured successfully.', [
                    'spreadsheet_id' => $this->spreadsheetId,
                ]);
            } else {
                $this->googleApiConfigured = false;
                $this->logger->notice('GoogleSheetRepository: Google service account config or spreadsheet ID not found. Repository will use mock fallback data for all calls.');
            }
        } catch (Throwable $e) {
            $this->googleApiConfigured = false;
            $this->logger->error('GoogleSheetRepository: Failed to initialize Google Sheets configuration. Using fallback.', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);
        }
    }

    protected function cacheKey(string ...$parts): string
    {
        return self::CACHE_PREFIX.implode(':', array_map(static fn (string $p): string => empty($p) ? '_' : $p, $parts));
    }

    protected function buildLoanStatement(array $loans): array
    {
        $statement = [];
        foreach ($loans as $loan) {
            $disbursementDate = $loan['disbursement_date'] ?? null;
            if (! empty($disbursementDate)) {
                $statement[] = [
                    'date' => $disbursementDate,
                    'type' => 'Disbursement',
                    'reference' => $loan['loan_number'] ?? null,
                    'debit' => 0,
                    'credit' => (float) ($loan['loan_amount'] ?? 0),
                    'balance' => (float) ($loan['loan_amount'] ?? 0),
                    'description' => 'Loan disbursed - ' . ($loan['loan_product'] ?? 'N/A'),
                ];
            }
            $paid = (float) ($loan['paid_amount'] ?? 0);
            if ($paid > 0) {
                $lastDate = $loan['maturity_date'] ?? date('Y-m-d');
                $statement[] = [
                    'date' => $lastDate,
                    'type' => 'Repayment',
                    'reference' => $loan['loan_number'] ?? null,
                    'debit' => $paid,
                    'credit' => 0,
                    'balance' => (float) ($loan['outstanding_balance'] ?? 0),
                    'description' => 'Loan repayment - ' . ($loan['status'] ?? 'Active'),
                ];
            }
        }

        return $statement;
    }

    protected function flattenInvestmentHistory(array $investments): array
    {
        $result = [];
        foreach ($investments as $investment) {
            $history = $investment['history'] ?? [];
            if (! is_array($history)) {
                continue;
            }
            foreach ($history as $event) {
                if (is_array($event)) {
                    $event['product'] = $investment['product'] ?? null;
                    $result[] = $event;
                }
            }
        }

        return $result;
    }

    public function getLoanPayments(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        
        try {
            // Get user_id from member_number
            $user = \App\Models\User::where('membercode', $memberNumber)->first();
            if (!$user) {
                return [];
            }
            
            // Get from database by user_id
            $payments = \App\Models\LoanPayment::byUserId($user->id)
                ->orderBy('payment_date', 'desc')
                ->get()
                ->map(function ($payment) {
                    return [
                        'loan_id' => $payment->loan_id,
                        'user_id' => $payment->user_id,
                        'customer_id' => $payment->customer_id,
                        'payment_amount' => (float) $payment->payment_amount,
                        'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                        'payment_method' => $payment->payment_method,
                        'reference_number' => $payment->reference_number,
                        'principal_amount' => (float) $payment->principal_amount,
                    ];
                })
                ->toArray();

            return $payments;
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist yet, return empty array
            return [];
        }
    }

    public function getLoansInformation(string $memberNumber): array
    {
        $memberNumber = $this->validateMemberNumber($memberNumber);
        
        try {
            // Get user_id from member_number
            $user = \App\Models\User::where('membercode', $memberNumber)->first();
            if (!$user) {
                return [];
            }
            
            // Get from database by user_id
            $loans = \App\Models\LoanInformation::byUserId($user->id)
            ->orderBy('loan_start_date', 'desc')
            ->get()
            ->map(function ($loan) {
                return [
                    'loan_id' => $loan->loan_id,
                    'customer_id' => $loan->customer_id,
                    'loan_type' => $loan->loan_type,
                    'loan_amount' => (float) $loan->loan_amount,
                    'nature' => $loan->nature,
                    'interest_rate_pm' => (float) $loan->interest_rate_pm,
                    'duration_months' => $loan->duration_months,
                    'loan_start_date' => $loan->loan_start_date ? $loan->loan_start_date->format('Y-m-d') : null,
                    'loan_maturity_date' => $loan->loan_maturity_date ? $loan->loan_maturity_date->format('Y-m-d') : null,
                    'total_payable' => (float) $loan->total_payable,
                    'monthly_installment' => (float) $loan->monthly_installment,
                    'monthly_principal' => (float) $loan->monthly_principal,
                    'principal_paid_to_date' => (float) $loan->principal_paid_to_date,
                    'termination_fee' => (float) $loan->termination_fee,
                    'total_paid' => (float) $loan->total_paid,
                    'outstanding_balance' => (float) $loan->outstanding_balance,
                    'loan_status' => $loan->loan_status,
                    'loan_guarantor' => $loan->loan_guarantor,
                    'number_of_paid_installments' => $loan->number_of_paid_installments,
                    'number_of_unpaid_installments' => $loan->number_of_unpaid_installments,
                    'this_month_loan_status' => $loan->this_month_loan_status,
                    'balance_after_payment' => (float) $loan->balance_after_payment,
                    'loan_agreement_ref_no' => $loan->loan_agreement_ref_no,
                ];
            })
            ->toArray();

            return $loans;
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist yet, return empty array
            return [];
        }
    }
}
