<?php

namespace App\Policies;

use App\Models\Central\Auth\Role\Role;
use App\Models\Tenant\Auth\Authentication\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $this->isVerified($user) && $user->hasRole('super_admin');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->isVerified($user) && $user->hasRole('super_admin');
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->isVerified($user) && $user->hasRole('super_admin');
    }

    public function manage(User $user): bool
    {
        return $this->isVerified($user) && $user->hasRole('super_admin');
    }

    private function isVerified(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }
}
