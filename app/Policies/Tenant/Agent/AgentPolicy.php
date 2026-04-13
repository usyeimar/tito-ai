<?php

namespace App\Policies\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Auth\Authentication\User;

class AgentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Agent $agent): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isVerified($user);
    }

    public function update(User $user, Agent $agent): bool
    {
        return $this->isVerified($user);
    }

    public function delete(User $user, Agent $agent): bool
    {
        return $this->isVerified($user);
    }

    private function isVerified(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }
}
