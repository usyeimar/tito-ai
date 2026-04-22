<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\CRM\Leads\Lead;
use Illuminate\Database\Eloquent\Model;

class LeadPolicy extends ModulePolicy
{
    protected string $module = 'lead';

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

    public function convert(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $this->canManage($user);
    }
}
