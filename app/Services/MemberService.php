<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class MemberService
{
    public function paginateArray(array $data, int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        $page = $page ?: Paginator::resolveCurrentPage('page');
        $total = count($data);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($data, $offset, $perPage);

        return new LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function sort(array $data, string $column, string $direction = 'asc'): array
    {
        if (empty($data)) {
            return $data;
        }

        $direction = strtolower($direction) === 'desc' ? -1 : 1;

        usort($data, static function ($a, $b) use ($column, $direction): int {
            $valA = $a[$column] ?? null;
            $valB = $b[$column] ?? null;

            if ($valA === $valB) {
                return 0;
            }

            if (is_numeric($valA) && is_numeric($valB)) {
                return ($valA < $valB ? -1 : 1) * $direction;
            }

            return strcasecmp((string) $valA, (string) $valB) * $direction;
        });

        return $data;
    }

    public function filterByMemberNumber(array $data, string $memberNumber): array
    {
        if (empty($memberNumber)) {
            return $data;
        }

        $memberNumber = strtolower(trim($memberNumber));

        return array_values(array_filter(
            $data,
            static fn ($item): bool => isset($item['membercode'])
                && str_contains(strtolower((string) $item['membercode']), $memberNumber)
        ));
    }

    public function filterByStatus(array $data, string $status): array
    {
        if (empty($status)) {
            return $data;
        }

        $status = strtolower(trim($status));

        return array_values(array_filter(
            $data,
            static fn ($item): bool => isset($item['status'])
                && strtolower((string) $item['status']) === $status
        ));
    }

    public function filterByBranch(array $data, string $branch): array
    {
        if (empty($branch)) {
            return $data;
        }

        $branch = strtolower(trim($branch));

        return array_values(array_filter(
            $data,
            static fn ($item): bool => isset($item['branch'])
                && str_contains(strtolower((string) $item['branch']), $branch)
        ));
    }

    public function search(array $data, string $query): array
    {
        if (empty($query)) {
            return $data;
        }

        $query = strtolower(trim($query));
        $searchFields = [
            'membercode',
            'name',
            'email',
            'phone',
            'branch',
            'occupation',
            'employer',
        ];

        return array_values(array_filter(
            $data,
            static function ($item) use ($query, $searchFields): bool {
                foreach ($searchFields as $field) {
                    if (isset($item[$field]) && str_contains(strtolower((string) $item[$field]), $query)) {
                        return true;
                    }
                }
                return false;
            }
        ));
    }

    public function chunkArray(array $data, int $size): array
    {
        return array_chunk($data, $size, true);
    }

    public function getSortDirectionIcon(string $currentColumn, string $column, string $direction): string
    {
        if ($currentColumn !== $column) {
            return 'fa-sort text-gray-300';
        }

        return $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    }
}
