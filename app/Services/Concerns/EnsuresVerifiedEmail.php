<?php

namespace App\Services\Concerns;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Tenant\Auth\Authentication\User;

trait EnsuresVerifiedEmail
{
    public function ensureVerifiedEmail(CentralUser|User $user, string $message): void
    {
        // We allow the action to continue if the user has already verified their email
        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return;
        }

        abort(403, $message);
    }
}
