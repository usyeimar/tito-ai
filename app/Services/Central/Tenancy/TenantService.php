<?php

namespace App\Services\Central\Tenancy;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\Auth\Role\Role;
use App\Models\Central\System\SystemProfilePicture;
use App\Models\Central\Tenancy\Tenant;
use App\Models\Tenant\Auth\Authentication\User;
use App\Services\Concerns\EnsuresVerifiedEmail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class TenantService
{
    use EnsuresVerifiedEmail;

    /**
     * @return LengthAwarePaginator<Tenant>
     */
    public function listForUser(CentralUser $user, array $filters = []): LengthAwarePaginator
    {
        $tenantIds = $user->tenants()->pluck('tenants.id')->all();

        if ($tenantIds === []) {
            $page = max((int) data_get($filters, 'page.number', 1), 1);
            $perPage = max((int) data_get($filters, 'page.size', 15), 1);

            return new LengthAwarePaginator([], 0, $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]);
        }

        return QueryBuilder::for(Tenant::class)
            ->whereIn('id', $tenantIds)
            ->allowedFilters('name', 'slug')
            ->defaultSort('name')
            ->paginate();
    }

    public function getForUser(CentralUser $user, Tenant $tenant): Tenant
    {
        $this->assertUserCanAccessTenant($user, $tenant);

        return $tenant;
    }

    public function createForUser(CentralUser $user, array $data): Tenant
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required to manage workspaces.');
        $this->ensureUserHasGlobalId($user);

        $tenantName = Arr::get($data, 'name');
        
        $tenant = Tenant::create([
            'slug' => Arr::get($data, 'slug'),
            'name' => $tenantName,
            'data' => [
                'name' => $tenantName,
            ],
        ]);

        $user->tenants()->syncWithoutDetaching([$tenant->getKey()]);

        $profilePicture = SystemProfilePicture::query()
            ->where('user_global_id', $user->global_id)
            ->first();

        if ($profilePicture) {
            $profilePicture->tenants()->syncWithoutDetaching([$tenant->getKey()]);
        }

        $tenant->run(function () use ($user): void {
            $tenantUser = User::query()->where('global_id', $user->global_id)->first();
            if (! $tenantUser) {
                return;
            }

            $hasSuperAdminRole = Role::query()
                ->where('name', 'super_admin')
                ->where('guard_name', 'tenant')
                ->exists();

            if ($hasSuperAdminRole && ! $tenantUser->hasRole('super_admin')) {
                $tenantUser->assignRole('super_admin');
            }
        });

        return $tenant->refresh();
    }

    public function updateForUser(CentralUser $user, Tenant $tenant, array $data): Tenant
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required to manage workspaces.');
        $this->assertUserCanAccessTenant($user, $tenant);

        if (array_key_exists('name', $data)) {
            $tenant->name = $data['name'];
            $tenant->data = array_merge((array) ($tenant->data ?? []), [
                'name' => $data['name'],
            ]);
        }

        if (array_key_exists('slug', $data)) {
            $tenant->slug = $data['slug'];
        }

        $tenant->save();

        return $tenant->refresh();
    }

    public function deleteForUser(CentralUser $user, Tenant $tenant): void
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required to manage workspaces.');
        $this->assertUserCanAccessTenant($user, $tenant);

        $tenant->delete();
    }

    private function assertUserCanAccessTenant(CentralUser $user, Tenant $tenant): void
    {
        $allowed = $user->tenants()
            ->whereKey($tenant->getKey())
            ->exists();

        if (! $allowed) {
            throw (new ModelNotFoundException)->setModel(Tenant::class, [$tenant->getKey()]);
        }
    }

    private function ensureUserHasGlobalId(CentralUser $user): void
    {
        if ($user->global_id) {
            return;
        }

        $user->forceFill([
            'global_id' => (string) Str::uuid(),
        ])->save();
    }
}
