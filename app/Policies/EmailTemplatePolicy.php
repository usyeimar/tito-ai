<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Model;

class EmailTemplatePolicy extends ModulePolicy
{
    protected string $module = 'email_template';

    public function render(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function preview(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function generate(User $user): bool
    {
        return $this->canManage($user);
    }

    public function variables(User $user): bool
    {
        return $this->canView($user);
    }

    public function attachDocumentTemplate(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function detachDocumentTemplate(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function reorderDocumentTemplates(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }
}
