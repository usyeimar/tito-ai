<?php

namespace App\Services\Shared\Auth;

use App\Models\Tenant\Auth\Authentication\User;

class SuperAdminValidationService
{
    /**
     * Ensures that the user being modified is not the last active super admin.
     * This check is used when:
     * - Revoking the super_admin role from a user
     * - Deactivating a user who has the super_admin role
     *
     * @param  User  $user  The user being modified
     */
    public function ensureNotLastActiveSuperAdmin(User $user): void
    {
        if (! $user->hasRole('super_admin')) {
            return;
        }

        $query = User::query()
            ->active()
            ->whereHas('roles', fn ($builder) => $builder->where('name', 'super_admin')->where('guard_name', 'tenant'));

        // Exclude the user from the count since we're checking the state after the operation
        if ($user->is_active) {
            $query->whereKeyNot($user->getKey());
        }

        if ($query->count() === 0) {
            abort(422, 'At least one active super admin is required.');
        }
    }
}
