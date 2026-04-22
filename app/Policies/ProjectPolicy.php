<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\CRM\Projects\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectPolicy extends ModulePolicy
{
    protected string $module = 'project';

    public function view(User $user, Model $model): bool
    {
        return $this->canView($user);
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

    public function assign(User $user, Project $project): bool
    {
        return $this->canManage($user);
    }
}
