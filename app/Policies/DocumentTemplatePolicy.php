<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplatePolicy extends ModulePolicy
{
    protected string $module = 'document_template';

    public function render(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function preview(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function download(User $user, Model $model): bool
    {
        return $this->canView($user);
    }

    public function variables(User $user): bool
    {
        return $this->canView($user);
    }

    public function formats(User $user): bool
    {
        return $this->canView($user);
    }
}
