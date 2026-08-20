<?php

namespace App\Policies;

use App\Models\User;

class DepositPolicy
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

    public function view(User $user, $deposit): bool
    {
        if (is_string($deposit)) {
            return $user->membercode === $deposit;
        }

        return $user->membercode === ($deposit->member_number ?? $deposit->memberNumber ?? null);
    }
}
