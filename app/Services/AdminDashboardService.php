<?php

declare(strict_types=1);

namespace App\Services;

class AdminDashboardService
{
    public function formatTotals(array $totals): array
    {
        $moneyKeys = [
            'total_savings',
            'total_loans',
            'total_deposits',
            'total_investments',
            'total_swf',
        ];

        $formatted = [];
        foreach ($totals as $key => $value) {
            if (in_array($key, $moneyKeys, true)) {
                $formatted[$key] = $this->formatMoney($value);
                $formatted["{$key}_raw"] = $value;
            } elseif ($key === 'total_members') {
                $formatted[$key] = number_format((int) $value);
                $formatted["{$key}_raw"] = (int) $value;
            } else {
                $formatted[$key] = $value;
            }
        }

        if (isset($totals['total_savings'], $totals['total_loans']) && (float) $totals['total_savings'] > 0) {
            $formatted['loan_to_savings_ratio'] = number_format(((float) $totals['total_loans'] / (float) $totals['total_savings']) * 100, 1).'%';
        }

        $formatted['google_sheet_status_badge'] = $this->statusBadge($totals['google_sheet_status'] ?? 'unknown');
        $formatted['last_sync_formatted'] = $totals['last_sync'] ?? 'Never';

        return $formatted;
    }

    public function formatMoney(mixed $amount): string
    {
        return number_format((float) $amount, 2).' TSh';
    }

    public function statusBadge(string $status): array
    {
        $status = strtolower($status);

        return match ($status) {
            'connected', 'active', 'synced' => [
                'label' => 'Connected',
                'class' => 'badge-green',
                'icon' => 'fa-check-circle',
            ],
            'disconnected', 'error', 'failed' => [
                'label' => 'Disconnected',
                'class' => 'badge-red',
                'icon' => 'fa-times-circle',
            ],
            'syncing', 'pending' => [
                'label' => 'Syncing',
                'class' => 'badge-yellow',
                'icon' => 'fa-spinner fa-spin',
            ],
            default => [
                'label' => ucfirst($status),
                'class' => 'badge-gray',
                'icon' => 'fa-question-circle',
            ],
        };
    }

    public function memberStatusBadge(?string $status): array
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'active' => [
                'label' => 'Active',
                'class' => 'badge-green',
            ],
            'inactive', 'dormant' => [
                'label' => 'Inactive',
                'class' => 'badge-gray',
            ],
            'suspended' => [
                'label' => 'Suspended',
                'class' => 'badge-red',
            ],
            'pending' => [
                'label' => 'Pending',
                'class' => 'badge-yellow',
            ],
            'expired' => [
                'label' => 'Expired',
                'class' => 'badge-orange',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'class' => 'badge-red',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'class' => 'badge-gray',
            ],
            default => [
                'label' => $status ? ucfirst($status) : 'Unknown',
                'class' => 'badge-gray',
            ],
        };
    }

    public function loanStatusBadge(?string $status): array
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'active', 'disbursed', 'current' => [
                'label' => 'Active',
                'class' => 'badge-green',
            ],
            'paid', 'settled', 'closed' => [
                'label' => 'Completed',
                'class' => 'badge-blue',
            ],
            'defaulted', 'overdue' => [
                'label' => 'Defaulted',
                'class' => 'badge-red',
            ],
            'pending', 'processing', 'approved' => [
                'label' => 'Pending',
                'class' => 'badge-yellow',
            ],
            default => [
                'label' => $status ? ucfirst($status) : 'Unknown',
                'class' => 'badge-gray',
            ],
        };
    }

    public function depositStatusBadge(?string $status): array
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'active', 'current' => [
                'label' => 'Active',
                'class' => 'badge-green',
            ],
            'matured', 'closed' => [
                'label' => 'Matured',
                'class' => 'badge-blue',
            ],
            'withdrawn' => [
                'label' => 'Withdrawn',
                'class' => 'badge-gray',
            ],
            default => [
                'label' => $status ? ucfirst($status) : 'Unknown',
                'class' => 'badge-gray',
            ],
        };
    }
}
