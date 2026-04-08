<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Model;

abstract class ModulePolicy
{
    protected string $module;

    protected function canView(User $user): bool
    {
        return $user->can("{$this->module}.view");
    }

    protected function canManage(User $user): bool
    {
        return $this->isVerified($user) && $user->can("{$this->module}.manage");
    }

    protected function canDelete(User $user): bool
    {
        return $this->isVerified($user) && $user->can("{$this->module}.delete");
    }

    protected function isVerified(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function requestAccess(User $user): bool
    {
        return $this->canView($user);
    }

    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->canDelete($user);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->canDelete($user);
    }

    public function clone(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function batchDelete(User $user): bool
    {
        return $this->canDelete($user);
    }

    public function batchRestore(User $user): bool
    {
        return $this->canManage($user);
    }

    public function batchForceDelete(User $user): bool
    {
        return $this->canDelete($user);
    }

    public function batchClone(User $user): bool
    {
        return $this->canManage($user);
    }

    public function batchStatus(User $user): bool
    {
        return $this->canManage($user);
    }

    public function convert(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }
}
