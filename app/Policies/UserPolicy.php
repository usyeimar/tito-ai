<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('user.view');
    }

    public function update(User $user, User $model): bool
    {
        return $this->canManage($user);
    }

    public function updatePassword(User $user, User $model): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->canDelete($user);
    }

    public function assignRoles(User $user, User $model): bool
    {
        return $this->isVerified($user) && $user->hasRole('super_admin');
    }

    public function revokeRole(User $user, User $model): bool
    {
        return $this->isVerified($user) && $user->hasRole('super_admin');
    }

    private function canManage(User $user): bool
    {
        return $this->isVerified($user) && $user->can('user.manage');
    }

    private function canDelete(User $user): bool
    {
        return $this->isVerified($user) && $user->can('user.delete');
    }

    private function isVerified(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }
}
