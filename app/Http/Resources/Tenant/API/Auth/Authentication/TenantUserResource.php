<?php

namespace App\Http\Resources\Tenant\API\Auth\Authentication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $profilePictureUrl = when($this->profilePicture?->id, function ($profilePictureId) {
            return route('tenant.profile-picture.show', [
                'tenant' => tenant()->slug,
                'profilePicture' => $profilePictureId,
            ]);
        }, null);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_picture_url' => $profilePictureUrl,
            'is_active' => $this->is_active,
            //            'type' => $this->whenLoaded('type', fn (): array => MetadataRelationshipData::fromModel($this->type)->toArray()),
            'roles' => $this->getRoleNames()->values(),
            'permissions' => $this->groupPermissionsByModule(
                $this->getAllPermissions()->pluck('name')->all()
            ),
        ];
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array<string, array<int, string>>
     */
    private function groupPermissionsByModule(array $permissions): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission);
            $module = array_shift($parts);
            $action = $parts === [] ? 'access' : implode('.', $parts);

            if (! is_string($module) || $module === '') {
                continue;
            }

            $grouped[$module] ??= [];
            $grouped[$module][] = $action;
        }

        ksort($grouped);
        foreach ($grouped as &$actions) {
            $actions = array_values(array_unique($actions));
            sort($actions);
        }

        return $grouped;
    }
}
