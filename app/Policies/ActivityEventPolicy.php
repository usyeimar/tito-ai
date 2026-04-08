<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Model;

class ActivityEventPolicy extends ModulePolicy
{
    protected string $module = 'activity';

    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->canView($user);
    }
}
