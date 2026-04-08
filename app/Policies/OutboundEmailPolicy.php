<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Model;

class OutboundEmailPolicy extends ModulePolicy
{
    protected string $module = 'outbound_email';

    public function send(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->canView($user);
    }

    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }
}
