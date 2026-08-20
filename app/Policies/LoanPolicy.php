<?php

namespace App\Policies;

use App\Models\User;

class LoanPolicy
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

    public function view(User $user, $loan): bool
    {
        if (is_string($loan)) {
            return $user->membercode === $loan;
        }

        return $user->membercode === ($loan->member_number ?? $loan->memberNumber ?? null);
    }
}
