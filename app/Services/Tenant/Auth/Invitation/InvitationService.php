<?php

namespace App\Services\Tenant\Auth\Invitation;

use App\Exceptions\InvitationPendingConflictException;
use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\Tenancy\Tenant;
use App\Models\Central\Tenancy\TenantInvitation;
use App\Models\Tenant\Auth\Authentication\User as TenantUser;
use App\Services\Concerns\EnsuresVerifiedEmail;
use App\Services\Concerns\SendsInvitationEmails;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    use EnsuresVerifiedEmail;
    use SendsInvitationEmails;

    public function listForTenant(Tenant $tenant, TenantUser $actor, array $filters = []): Collection
    {
        $this->ensureCanRead($actor);

        $query = TenantInvitation::query()
            ->where('tenant_id', $tenant->getKey())
            ->orderByDesc('created_at');

        $term = trim((string) data_get($filters, 'filter.search', ''));

        if ($term !== '') {
            $like = '%'.$term.'%';

            $query->where(function ($query) use ($like): void {
                $query->where('email', 'like', $like)
                    ->orWhere('status', 'like', $like);
            });
        }

        $invitations = $query->get();

        $invitations->each->markExpiredIfNeeded();

        return $invitations;
    }

    public function createSingleInvitation(Tenant $tenant, TenantUser $actor, string $email): TenantInvitation
    {
        $this->ensureCanManage($actor);

        $email = Str::lower($email);

        $this->validateEmailForInvitation($tenant, $email);

        return $this->createAndSend($tenant, $actor, $email);
    }

    /**
     * @return array{successful: Collection, failed: Collection}
     */
    public function createBatchInvitations(Tenant $tenant, TenantUser $actor, array $emails): array
    {
        $this->ensureCanManage($actor);

        $successful = collect();
        $failed = collect();

        DB::transaction(function () use ($tenant, $actor, $emails, &$successful, &$failed) {
            foreach ($emails as $email) {
                $email = Str::lower($email);

                try {
                    $this->validateEmailForInvitation($tenant, $email);
                    $invitation = $this->createAndSend($tenant, $actor, $email);
                    $successful->push($invitation);
                } catch (ValidationException $e) {
                    $messages = $e->validator->errors()->all();
                    $failed->push([
                        'email' => $email,
                        'reason' => $messages[0] ?? 'Validation failed.',
                    ]);
                } catch (InvitationPendingConflictException $e) {
                    $failed->push([
                        'email' => $email,
                        'reason' => $e->getMessage(),
                    ]);
                }
            }
        });

        return ['successful' => $successful, 'failed' => $failed];
    }

    public function reinviteFromPrevious(TenantInvitation $invitation, TenantUser $actor): TenantInvitation
    {
        $this->ensureCanManage($actor);

        $reinvitableStatuses = [
            TenantInvitation::STATUS_DECLINED,
            TenantInvitation::STATUS_EXPIRED,
            TenantInvitation::STATUS_REVOKED,
        ];

        $invitation->markExpiredIfNeeded();

        if (! in_array($invitation->status, $reinvitableStatuses, true)) {
            throw ValidationException::withMessages([
                'invitation' => ['Only declined, expired, or revoked invitations can be re-invited.'],
            ]);
        }

        // Check if user already belongs to the tenant
        $centralUser = CentralUser::query()->where('email', $invitation->email)->first();
        if ($centralUser && $centralUser->tenants()->whereKey($invitation->tenant_id)->exists()) {
            throw ValidationException::withMessages([
                'invitation' => ['This user already belongs to the workspace.'],
            ]);
        }

        return $this->resetAndSend($invitation, $actor);
    }

    public function resendInvitation(TenantInvitation $invitation, TenantUser $actor): TenantInvitation
    {
        $this->ensureCanManage($actor);

        if ($invitation->status !== TenantInvitation::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'invitation' => ['Only pending invitations can be resent.'],
            ]);
        }

        $invitation->markExpiredIfNeeded();
        if ($invitation->status !== TenantInvitation::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'invitation' => ['This invitation has expired.'],
            ]);
        }

        return $this->refreshAndSend($invitation);
    }

    public function revokeInvitation(TenantInvitation $invitation, TenantUser $actor): TenantInvitation
    {
        $this->ensureCanManage($actor);

        if ($invitation->status !== TenantInvitation::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'invitation' => ['Only pending invitations can be revoked.'],
            ]);
        }

        $invitation->forceFill([
            'status' => TenantInvitation::STATUS_REVOKED,
            'revoked_at' => now(),
        ])->save();

        return $invitation;
    }

    private function validateEmailForInvitation(Tenant $tenant, string $email): void
    {
        $centralUser = CentralUser::query()->where('email', $email)->first();
        if ($centralUser && $centralUser->tenants()->whereKey($tenant->getKey())->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This user already belongs to the workspace.'],
            ]);
        }

        $pending = TenantInvitation::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('email', $email)
            ->where('status', TenantInvitation::STATUS_PENDING)
            ->first();

        if ($pending) {
            $pending->markExpiredIfNeeded();
        }

        if ($pending && $pending->status === TenantInvitation::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'email' => ['An invitation is already pending for this user.'],
            ]);
        }
    }

    private function createAndSend(Tenant $tenant, TenantUser $actor, string $email): TenantInvitation
    {
        $token = $this->generateToken();
        $expiresAt = $this->expiresAt();

        try {
            $invitation = TenantInvitation::query()->create([
                'tenant_id' => $tenant->getKey(),
                'email' => $email,
                'token_hash' => hash('sha256', $token),
                'invited_by_central_user_id' => $this->centralUserId($actor),
                'status' => TenantInvitation::STATUS_PENDING,
                'expires_at' => $expiresAt,
                'last_sent_at' => now(),
            ]);
        } catch (QueryException $e) {
            if ($this->isPendingInvitationConstraintViolation($e)) {
                throw new InvitationPendingConflictException;
            }

            throw $e;
        }

        $invitation->load(['tenant', 'invitedBy']);

        $this->queueInvitationEmail($invitation->getKey(), $token);

        return $invitation;
    }

    private function isPendingInvitationConstraintViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $detail = $e->errorInfo[2] ?? '';

        return $sqlState === '23505'
            && str_contains($detail, 'tenant_invitations_pending_tenant_email_unique');
    }

    private function refreshAndSend(TenantInvitation $invitation): TenantInvitation
    {
        $token = $this->generateToken();
        $expiresAt = $this->expiresAt();

        $invitation->forceFill([
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
            'last_sent_at' => now(),
        ])->save();

        $invitation->load(['tenant', 'invitedBy']);

        $this->queueInvitationEmail($invitation->getKey(), $token);

        return $invitation;
    }

    private function resetAndSend(TenantInvitation $invitation, TenantUser $actor): TenantInvitation
    {
        $token = $this->generateToken();
        $expiresAt = $this->expiresAt();

        $invitation->forceFill([
            'token_hash' => hash('sha256', $token),
            'invited_by_central_user_id' => $this->centralUserId($actor),
            'status' => TenantInvitation::STATUS_PENDING,
            'expires_at' => $expiresAt,
            'last_sent_at' => now(),
            'accepted_at' => null,
            'declined_at' => null,
            'revoked_at' => null,
        ])->save();

        $invitation->load(['tenant', 'invitedBy']);

        $this->queueInvitationEmail($invitation->getKey(), $token);

        return $invitation;
    }

    private function queueInvitationEmail(string $invitationId, string $token): void
    {
        DB::afterCommit(function () use ($invitationId, $token): void {
            $invitation = TenantInvitation::query()
                ->with(['tenant', 'invitedBy'])
                ->find($invitationId);

            if (! $invitation) {
                return;
            }

            $this->sendInvitationEmail($invitation, $token);
        });
    }

    private function centralUserId(TenantUser $actor): ?string
    {
        if (! $actor->global_id) {
            return null;
        }

        return CentralUser::query()
            ->where('global_id', $actor->global_id)
            ->value('id');
    }

    private function ensureCanManage(TenantUser $actor): void
    {
        $this->ensureVerifiedEmail($actor, 'Email verification is required to manage invitations.');

        if (! $actor->can('invitation.manage')) {
            abort(403, 'You do not have permission to manage invitations.');
        }
    }

    private function ensureCanRead(TenantUser $actor): void
    {
        if (! $actor->can('invitation.manage') && ! $actor->can('invitation.view')) {
            abort(403, 'You do not have permission to view invitations.');
        }
    }
}
