<?php

namespace App\Services\Tenant\Auth\Role;

use App\Models\Central\Auth\Role\Permission;
use App\Models\Central\Auth\Role\Role;
use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RoleService
{
    public const array SYSTEM_ROLES = ['super_admin', 'admin', 'user'];

    /**
     * @return LengthAwarePaginator<Role>
     */
    public function listRoles(User $user, array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Role::class)
            ->where('guard_name', 'tenant')
            ->allowedFilters(AllowedFilter::partial('name'))
            ->allowedIncludes('permissions')
            ->defaultSort('name')
            ->paginate();
    }

    public function getRole(User $user, Role $role): Role
    {
        $this->ensureTenantRole($role);

        return $role->load('permissions');
    }

    public function createRole(User $user, array $data): Role
    {
        $role = Role::query()->create([
            'name' => $data['name'],
            'guard_name' => 'tenant',
        ]);

        $this->syncPermissions($role, $data);

        return $role->load('permissions');
    }

    public function updateRole(User $user, Role $role, array $data): Role
    {
        $this->ensureTenantRole($role);
        $this->guardSystemRole($role, $data);

        if (array_key_exists('name', $data)) {
            $role->name = $data['name'];
        }

        $role->save();

        $this->syncPermissions($role, $data);

        return $role->load('permissions');
    }

    public function deleteRole(User $user, Role $role): void
    {
        $this->ensureTenantRole($role);

        if ($this->isSystemRole($role)) {
            throw ValidationException::withMessages([
                'role' => ['System roles cannot be deleted.'],
            ]);
        }

        $role->delete();
    }

    private function syncPermissions(Role $role, array $data): void
    {
        if (! array_key_exists('permissions', $data)) {
            return;
        }

        $permissions = Permission::query()
            ->where('guard_name', 'tenant')
            ->whereIn('name', $data['permissions'])
            ->get();

        $role->syncPermissions($permissions);
    }

    private function guardSystemRole(Role $role, array $data): void
    {
        if (! $this->isSystemRole($role)) {
            return;
        }

        if (array_key_exists('name', $data) || array_key_exists('permissions', $data)) {
            throw ValidationException::withMessages([
                'role' => ['System roles cannot be modified.'],
            ]);
        }
    }

    private function isSystemRole(Role $role): bool
    {
        return in_array($role->name, self::SYSTEM_ROLES, true);
    }

    private function ensureTenantRole(Role $role): void
    {
        if ($role->guard_name === 'tenant') {
            return;
        }

        throw (new ModelNotFoundException)->setModel(Role::class, [$role->getKey()]);
    }
}
