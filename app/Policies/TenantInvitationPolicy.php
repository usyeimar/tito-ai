<?php

namespace App\Policies;

use App\Models\Central\Tenancy\TenantInvitation;
use App\Models\Tenant\Auth\Authentication\User;

class TenantInvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invitation.manage') || $user->can('invitation.view');
    }

    public function view(User $user, TenantInvitation $invitation): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function resend(User $user, TenantInvitation $invitation): bool
    {
        return $this->canManage($user);
    }

    public function revoke(User $user, TenantInvitation $invitation): bool
    {
        return $this->canManage($user);
    }

    public function reinvite(User $user, TenantInvitation $invitation): bool
    {
        return $this->canManage($user);
    }

    private function canManage(User $user): bool
    {
        return $this->isVerified($user) && $user->can('invitation.manage');
    }

    private function isVerified(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }
}
