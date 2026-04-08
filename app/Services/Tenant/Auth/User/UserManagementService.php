<?php

namespace App\Services\Tenant\Auth\User;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\Auth\Role\Role;
use App\Models\Tenant\Auth\Authentication\User;
use App\Services\Concerns\EnsuresVerifiedEmail;
use App\Services\Shared\Auth\SuperAdminValidationService;
use App\Services\Shared\Auth\UserTokenService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

class UserManagementService
{
    use EnsuresVerifiedEmail;

    public function __construct(
        private readonly UserTokenService $userTokenService,
        private readonly SuperAdminValidationService $superAdminValidationService,
    ) {}

    /**
     * @return LengthAwarePaginator<User>
     */
    public function listUsers(User $actor, array $filters = []): LengthAwarePaginator
    {
        $this->ensureCanViewUsers($actor);

        return QueryBuilder::for(User::class)
            ->allowedFilters(['name', 'email'])
            ->allowedIncludes(['roles', 'profilePicture'])
            ->defaultSort('name')
            ->paginate();
    }

    public function assignRoles(User $actor, User $user, array $roles): User
    {
        $user->assignRole($roles);

        return $user->load(['roles', 'profilePicture']);
    }

    public function revokeRole(User $actor, User $user, Role $role): User
    {
        if ($role->name === 'super_admin') {
            $this->superAdminValidationService->ensureNotLastActiveSuperAdmin($user);
        }

        $user->removeRole($role);

        return $user->load(['roles',  'profilePicture']);
    }

    public function updateUser(User $actor, User $user, array $data): User
    {
        $this->ensureCanManageUsers($actor);

        $data = Arr::only($data, ['name', 'email', 'type_id']);
        $email = array_key_exists('email', $data) ? Str::lower((string) $data['email']) : null;

        $emailChanged = $email !== null && $email !== $user->email;
        if ($emailChanged) {
            $this->ensureEmailAvailable($user, $email);
            $data['email'] = $email;
            $data['email_verified_at'] = null;
        }

        $user->forceFill($data)->save();

        if ($emailChanged) {
            $this->revokeAllTokens($user);
            $this->sendCentralEmailVerification($user);
        }

        return $user->load(['roles',  'profilePicture']);
    }

    public function updatePassword(User $actor, User $user, string $password): User
    {
        $this->ensureCanManageUsers($actor);

        $user->forceFill([
            'password' => $password,
        ])->save();
        $this->revokeAllTokens($user);

        return $user->load(['roles',  'profilePicture']);
    }

    public function setActiveStatus(User $actor, User $user, bool $isActive): User
    {
        $this->ensureCanManageUsers($actor, $isActive);

        if (! $isActive) {
            $this->superAdminValidationService->ensureNotLastActiveSuperAdmin($user);
        }

        $user->forceFill(['is_active' => $isActive])->save();

        if (! $isActive) {
            $this->revokeAllTokens($user);
        }

        return $user->load(['roles',  'profilePicture']);
    }

    private function ensureCanManageUsers(User $user, ?bool $isActive = null): void
    {
        $permission = $isActive === false ? 'user.delete' : 'user.manage';

        $this->ensureVerifiedEmail($user, 'Email verification is required to manage users.');

        if (! $user->can($permission)) {
            abort(403, 'You do not have permission to manage users.');
        }
    }

    private function ensureCanViewUsers(User $user): void
    {
        if (! $user->can('user.view')) {
            abort(403, 'You do not have permission to view users.');
        }
    }

    private function ensureEmailAvailable(User $user, string $email): void
    {
        if (! $user->global_id) {
            return;
        }

        $exists = CentralUser::query()
            ->where('email', $email)
            ->where('global_id', '!=', $user->global_id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => ['An account with this email already exists. Please use a different email.'],
            ])->status(422);
        }
    }

    private function sendCentralEmailVerification(User $user): void
    {
        if (! $user->global_id) {
            return;
        }

        $centralUser = CentralUser::query()->where('global_id', $user->global_id)->first();
        if (! $centralUser) {
            return;
        }

        $centralUser->resendEmailVerificationNotification('update');
    }

    private function revokeAllTokens(User $user): void
    {
        if (! $user->global_id) {
            return;
        }

        $centralUser = CentralUser::query()->where('global_id', $user->global_id)->first();
        if (! $centralUser) {
            return;
        }

        $this->userTokenService->revokeAllTokens($centralUser);
    }
}
