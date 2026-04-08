<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\System\ColumnConfiguration\SystemUserColumnConfiguration;
use Illuminate\Database\Eloquent\Model;

class SystemUserColumnConfigurationPolicy extends ModulePolicy
{
    protected string $module = 'system_configurations';

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return $this->isOwner($user, $model);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isOwner($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->isOwner($user, $model);
    }

    private function isOwner(User $user, Model $model): bool
    {
        return $model instanceof SystemUserColumnConfiguration
            && $model->user_id === $user->id;
    }
}
