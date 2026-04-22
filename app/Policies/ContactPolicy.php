<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\CRM\Contacts\Contact;
use Illuminate\Database\Eloquent\Model;

class ContactPolicy extends ModulePolicy
{
    protected string $module = 'contact';

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

    public function assign(User $user, Contact $contact): bool
    {
        return $this->canManage($user);
    }
}
