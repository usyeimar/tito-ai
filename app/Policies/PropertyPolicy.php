<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\CRM\Properties\Property;
use Illuminate\Database\Eloquent\Model;

class PropertyPolicy extends ModulePolicy
{
    protected string $module = 'property';

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

    public function assign(User $user, Property $property): bool
    {
        return $this->canManage($user);
    }
}
