<?php

namespace App\Http\Requests\Tenant\API\Auth\Role;

use App\Support\Permissions\TenantPermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function rules(): array
    {
        $permissionNames = TenantPermissionRegistry::permissionNames();

        $role = $this->route('role');
        $roleId = $role ? $role->getKey() : null;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'tenant')
                    ->ignore($roleId),
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in($permissionNames)],
        ];
    }
}
