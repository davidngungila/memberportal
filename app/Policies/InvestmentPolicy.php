<?php

namespace App\Policies;

use App\Models\User;

class InvestmentPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, $investment): bool
    {
        if (is_string($investment)) {
            return $user->membercode === $investment;
        }

        return $user->membercode === ($investment->member_number ?? $investment->memberNumber ?? null);
    }
}
