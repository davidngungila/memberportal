<?php

namespace App\Policies;

use App\Models\User;

class SavingsPolicy
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

    public function view(User $user, $savings): bool
    {
        if (is_string($savings)) {
            return $user->membercode === $savings;
        }

        return $user->membercode === ($savings->member_number ?? $savings->memberNumber ?? null);
    }
}
