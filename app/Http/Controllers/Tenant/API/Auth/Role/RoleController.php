<?php

namespace App\Http\Controllers\Tenant\API\Auth\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\Auth\Role\IndexRoleRequest;
use App\Http\Requests\Tenant\API\Auth\Role\StoreRoleRequest;
use App\Http\Requests\Tenant\API\Auth\Role\UpdateRoleRequest;
use App\Http\Resources\Tenant\API\Auth\Role\RoleResource;
use App\Models\Central\Auth\Role\Role;
use App\Services\Tenant\Auth\Role\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index(IndexRoleRequest $request): JsonResponse
    {
        $roles = $this->roleService->listRoles($request->user(), $request->validated());

        return response()->json([
            'roles' => RoleResource::collection(collect($roles->items())),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
            'links' => [
                'first' => $roles->url(1),
                'last' => $roles->url($roles->lastPage()),
                'prev' => $roles->previousPageUrl(),
                'next' => $roles->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreRoleRequest $request): RoleResource
    {
        $role = $this->roleService->createRole(
            $request->user(),
            $request->validated(),
        );

        return RoleResource::make($role);
    }

    public function show(Request $request, Role $role): RoleResource
    {
        $role = $this->roleService->getRole($request->user(), $role);

        return RoleResource::make($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $role = $this->roleService->updateRole(
            $request->user(),
            $role,
            $request->validated(),
        );

        return RoleResource::make($role);
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->roleService->deleteRole($request->user(), $role);

        return response()->json(['message' => 'Role deleted.']);
    }
}
