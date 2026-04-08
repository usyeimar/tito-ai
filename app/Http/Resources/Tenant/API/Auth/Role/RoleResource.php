<?php

namespace App\Http\Resources\Tenant\API\Auth\Role;

use App\Services\Tenant\Auth\Role\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $permissions = $this->whenLoaded('permissions', function () {
            return $this->permissions->pluck('name')->values();
        });

        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'permissions' => $permissions ?? [],
            'is_system' => in_array($this->name, RoleService::SYSTEM_ROLES, true),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
