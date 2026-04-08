<?php

namespace App\Http\Requests\Tenant\API\Auth\Role;

use App\Support\Permissions\TenantPermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function rules(): array
    {
        $permissionNames = TenantPermissionRegistry::permissionNames();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'tenant'),
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in($permissionNames)],
        ];
    }
}
