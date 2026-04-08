<?php

namespace App\Services\Central\Auth\Invitation;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\Auth\Role\Role;
use App\Models\Central\System\SystemProfilePicture;
use App\Models\Central\Tenancy\Tenant;
use App\Models\Central\Tenancy\TenantInvitation;
use App\Models\Tenant\Auth\Authentication\User as TenantUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function listForEmail(string $email, ?string $term = null): Collection
    {
        $email = Str::lower($email);

        $query = TenantInvitation::query()
            ->where('email', $email)
            ->where('status', TenantInvitation::STATUS_PENDING)
            ->orderByDesc('created_at');

        $term = trim((string) $term);

        if ($term !== '') {
            $like = '%'.$term.'%';

            $query->where(function (Builder $query) use ($like): void {
                $query->where('status', 'like', $like)
                    ->orWhereHas('tenant', function (Builder $tenantQuery) use ($like): void {
                        $tenantQuery->where('name', 'like', $like)
                            ->orWhere('slug', 'like', $like);
                    });
            });
        }

        $invitations = $query->get();

        $invitations->each->markExpiredIfNeeded();

        return $invitations->where('status', TenantInvitation::STATUS_PENDING)->values();
    }

    public function resolveInvitation(string $token): TenantInvitation
    {
        $invitation = TenantInvitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        $invitation->markExpiredIfNeeded();

        return $invitation;
    }

    public function acceptInvitation(TenantInvitation $invitation, CentralUser $user): TenantInvitation
    {
        $this->ensureInvitee($invitation, $user->email);

        return DB::transaction(function () use ($invitation, $user): TenantInvitation {
            $invitation->markExpiredIfNeeded();

            if ($invitation->status !== TenantInvitation::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'invitation' => ['This invitation is no longer valid.'],
                ]);
            }

            $user->tenants()->syncWithoutDetaching([$invitation->tenant_id]);

            if ($user->global_id) {
                $profilePicture = SystemProfilePicture::query()
                    ->where('user_global_id', $user->global_id)
                    ->first();

                if ($profilePicture) {
                    $profilePicture->tenants()->syncWithoutDetaching([$invitation->tenant_id]);
                }
            }

            $this->assignDefaultTenantRole($invitation, $user);

            $invitation->forceFill([
                'status' => TenantInvitation::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ])->save();

            return $invitation;
        });
    }

    public function declineInvitation(TenantInvitation $invitation, CentralUser $user): TenantInvitation
    {
        $this->ensureInvitee($invitation, $user->email);

        $invitation->markExpiredIfNeeded();

        if ($invitation->status !== TenantInvitation::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'invitation' => ['This invitation is no longer valid.'],
            ]);
        }

        $invitation->forceFill([
            'status' => TenantInvitation::STATUS_DECLINED,
            'declined_at' => now(),
        ])->save();

        return $invitation;
    }

    private function ensureInvitee(TenantInvitation $invitation, string $email): void
    {
        if (strcasecmp($invitation->email, $email) !== 0) {
            abort(403, 'This invitation does not belong to your account.');
        }
    }

    private function assignDefaultTenantRole(TenantInvitation $invitation, CentralUser $user): void
    {
        $tenant = Tenant::query()->whereKey($invitation->tenant_id)->first();
        if (! $tenant) {
            throw ValidationException::withMessages([
                'invitation' => ['The target workspace no longer exists.'],
            ]);
        }

        $tenant->run(function () use ($user): void {
            $tenantUser = TenantUser::query()->where('global_id', $user->global_id)->first();
            if (! $tenantUser) {
                throw ValidationException::withMessages([
                    'invitation' => ['Unable to assign workspace role because tenant user profile is missing.'],
                ]);
            }

            $hasTenantRoles = $tenantUser->roles()
                ->where('guard_name', 'tenant')
                ->exists();

            if ($hasTenantRoles) {
                return;
            }

            $defaultRole = Role::query()
                ->where('name', 'user')
                ->where('guard_name', 'tenant')
                ->first();

            if ($defaultRole) {
                $tenantUser->assignRole($defaultRole);
            } else {
                throw ValidationException::withMessages([
                    'invitation' => ['Unable to assign workspace role because default role is missing.'],
                ]);
            }
        });
    }
}
