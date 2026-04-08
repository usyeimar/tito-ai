<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Model;

final class FormulaPolicy extends ModulePolicy
{
    protected string $module = 'formula';

    public function evaluate(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }
}
