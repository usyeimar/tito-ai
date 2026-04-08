<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaignPolicy extends ModulePolicy
{
    protected string $module = 'marketing_campaign';

    public function preview(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function estimate(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function review(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function draft(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function testSend(User $user, Model $model): bool
    {
        return $this->canManage($user);
    }

    public function execute(User $user, Model $model): bool
    {
        return $this->isVerified($user) && $user->can("{$this->module}.execute");
    }

    public function sendNow(User $user, Model $model): bool
    {
        return $this->execute($user, $model);
    }

    public function schedule(User $user, Model $model): bool
    {
        return $this->execute($user, $model);
    }

    public function activateRecurring(User $user, Model $model): bool
    {
        return $this->execute($user, $model);
    }

    public function pause(User $user, Model $model): bool
    {
        return $this->execute($user, $model);
    }

    public function cancel(User $user, Model $model): bool
    {
        return $this->execute($user, $model);
    }
}
