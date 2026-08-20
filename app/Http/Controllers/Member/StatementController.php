<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatementController extends Controller
{
    use FlashMessages;

    protected const VALID_TYPES = ['loans', 'savings', 'deposits', 'swf', 'investments'];

    protected const TYPE_LABELS = [
        'loans' => 'Loan Statement',
        'savings' => 'Savings Statement',
        'deposits' => 'Deposit Statement',
        'swf' => 'SWF Statement',
        'investments' => 'Investment Statement',
    ];

    protected const TYPE_HEADERS = [
        'loans' => ['Date', 'Type', 'Reference', 'Debit (TSh)', 'Credit (TSh)', 'Balance (TSh)', 'Description'],
        'savings' => ['Date', 'Type', 'Description', 'Amount (TSh)', 'Balance (TSh)'],
        'deposits' => ['Certificate No.', 'Product', 'Amount (TSh)', 'Interest (TSh)', 'Start Date', 'Maturity Date', 'Current Value (TSh)', 'Status'],
        'swf' => ['Date', 'Description', 'Contribution (TSh)'],
        'investments' => ['Date', 'Product', 'Type', 'Value (TSh)'],
    ];

    public function __construct(
        protected GoogleSheetRepositoryInterface $repository,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        if (!$memberNumber) {
            return view('member.statements.index', [
                'previews' => [],
                'statementTypes' => self::VALID_TYPES,
                'typeLabels' => self::TYPE_LABELS,
                'fromDate' => $request->input('from', date('Y-m-01', strtotime('-3 months'))),
                'toDate' => $request->input('to', date('Y-m-d')),
            ]);
        }

        $fromDate = $request->input('from', date('Y-m-01', strtotime('-3 months')));
        $toDate = $request->input('to', date('Y-m-d'));

        $previews = [];
        foreach (self::VALID_TYPES as $type) {
            $raw = $this->repository->getMemberStatements($memberNumber, $type);
            $filtered = $this->filterByDateRange($raw, $fromDate, $toDate, $type);
            $previews[$type] = array_slice($filtered, 0, 3);
        }

        $statementTypes = self::VALID_TYPES;
        $typeLabels = self::TYPE_LABELS;

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'statement',
            'subject_id' => null,
            'description' => 'Member viewed statements center',
            'properties' => ['member_number' => $memberNumber, 'from' => $fromDate, 'to' => $toDate],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.statements.index', compact(
            'previews',
            'statementTypes',
            'typeLabels',
            'fromDate',
            'toDate'
        ));
    }

    public function download(Request $request, string $type): StreamedResponse
    {
        Gate::authorize('member-only');

        $type = strtolower(trim($type));
        if (! in_array($type, self::VALID_TYPES, true)) {
            abort(400, 'Invalid statement type requested.');
        }

        $user = Auth::user();
        $memberNumber = $user->membercode;

        if (!$memberNumber) {
            abort(404, 'Member not found');
        }

        $fromDate = $request->input('from', date('Y-m-01', strtotime('-3 months')));
        $toDate = $request->input('to', date('Y-m-d'));

        $raw = $this->repository->getMemberStatements($memberNumber, $type);

        // Add database transactions for savings statements
        if ($type === 'savings') {
            $dbTransactions = Transaction::byMemberCode($memberNumber)
                ->whereBetween('date', [$fromDate, $toDate])
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'date' => $transaction->date->format('Y-m-d'),
                        'type' => $transaction->transaction_type,
                        'description' => $transaction->reference_no ?? 'Imported Transaction',
                        'amount' => (float) $transaction->amount,
                        'balance_after' => null,
                        'source' => 'database'
                    ];
                })
                ->toArray();
            
            $raw = array_merge($raw, $dbTransactions);
        }

        $rows = $this->filterByDateRange($raw, $fromDate, $toDate, $type);
        $headers = self::TYPE_HEADERS[$type];

        $formattedRows = array_map(function (array $row) use ($type): array {
            return $this->mapRowForType($row, $type);
        }, $rows);

        $filename = sprintf(
            'statement_%s_%s_%s_%s.csv',
            $type,
            $memberNumber,
            str_replace('-', '', $fromDate),
            str_replace('-', '', $toDate)
        );

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'statement',
            'subject_id' => null,
            'description' => "Member downloaded {$type} statement",
            'properties' => [
                'member_number' => $memberNumber,
                'from' => $fromDate,
                'to' => $toDate,
                'rows' => count($formattedRows),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->stream(function () use ($headers, $formattedRows, $type, $memberNumber, $fromDate, $toDate): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [strtoupper(self::TYPE_LABELS[$type])]);
            fputcsv($handle, [
                "Member: {$memberNumber}",
                "Period: {$fromDate} to {$toDate}",
                "Generated: " . date('Y-m-d H:i:s'),
            ]);
            fputcsv($handle, []);
            fputcsv($handle, $headers);

            foreach ($formattedRows as $row) {
                fputcsv($handle, $row);
            }

            if (count($formattedRows) === 0) {
                fputcsv($handle, ['No records found for the selected period.']);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
        ]);
    }

    protected function filterByDateRange(array $rows, string $from, string $to, string $type): array
    {
        $fromTs = strtotime($from);
        $toTs = strtotime($to . ' 23:59:59');

        return array_values(array_filter($rows, static function (array $row) use ($fromTs, $toTs, $type): bool {
            $dateField = match ($type) {
                'loans' => $row['date'] ?? null,
                'savings' => $row['date'] ?? null,
                'swf' => $row['date'] ?? null,
                'investments' => $row['date'] ?? null,
                'deposits' => $row['start_date'] ?? $row['maturity_date'] ?? null,
                default => $row['date'] ?? null,
            };

            if ($dateField === null) {
                return true;
            }

            $rowTs = strtotime($dateField);
            return $rowTs >= $fromTs && $rowTs <= $toTs;
        }));
    }

    protected function mapRowForType(array $row, string $type): array
    {
        return match ($type) {
            'loans' => [
                $row['date'] ?? '',
                $row['type'] ?? '',
                $row['reference'] ?? '',
                number_format((float) ($row['debit'] ?? 0), 2),
                number_format((float) ($row['credit'] ?? 0), 2),
                number_format((float) ($row['balance'] ?? 0), 2),
                $row['description'] ?? '',
            ],
            'savings' => [
                $row['date'] ?? '',
                $row['type'] ?? '',
                $row['description'] ?? '',
                number_format((float) ($row['amount'] ?? 0), 2),
                number_format((float) ($row['balance_after'] ?? 0), 2),
            ],
            'deposits' => [
                $row['certificate_number'] ?? '',
                $row['product'] ?? '',
                number_format((float) ($row['amount'] ?? 0), 2),
                number_format((float) ($row['interest'] ?? 0), 2),
                $row['start_date'] ?? '',
                $row['maturity_date'] ?? '',
                number_format((float) ($row['current_value'] ?? 0), 2),
                $row['status'] ?? '',
            ],
            'swf' => [
                $row['date'] ?? '',
                $row['description'] ?? '',
                number_format((float) ($row['amount'] ?? 0), 2),
            ],
            'investments' => [
                $row['date'] ?? '',
                $row['product'] ?? '',
                $row['type'] ?? '',
                number_format((float) ($row['value'] ?? 0), 2),
            ],
            default => array_values($row),
        };
    }
}
