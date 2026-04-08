<?php

namespace App\Http\Controllers\Tenant\API\Auth\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\Auth\Role\IndexPermissionRequest;
use App\Models\Central\Auth\Role\Role;
use App\Support\Permissions\TenantPermissionRegistry;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(IndexPermissionRequest $request): JsonResponse
    {
        $this->authorize('manage', Role::class);

        $modules = TenantPermissionRegistry::modules();
        $term = trim((string) data_get($request->validated(), 'filter.search', ''));

        if ($term !== '') {
            $needle = mb_strtolower($term);

            $modules = array_values(array_filter($modules, static function (array $module) use ($needle): bool {
                $moduleText = mb_strtolower((string) ($module['key'] ?? '').' '.(string) ($module['label'] ?? ''));

                if (str_contains($moduleText, $needle)) {
                    return true;
                }

                foreach ((array) ($module['permissions'] ?? []) as $permission) {
                    $permissionText = mb_strtolower(
                        (string) ($permission['name'] ?? '').' '.
                        (string) ($permission['label'] ?? '').' '.
                        (string) ($permission['action'] ?? '')
                    );

                    if (str_contains($permissionText, $needle)) {
                        return true;
                    }
                }

                return false;
            }));
        }

        return response()->json([
            'modules' => $modules,
        ]);
    }
}
