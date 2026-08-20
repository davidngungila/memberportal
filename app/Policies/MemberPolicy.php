<?php

namespace App\Policies;

use App\Models\User;

class MemberPolicy
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

    public function view(User $user, string $memberNumber): bool
    {
        return $user->membercode === $memberNumber;
    }
}
