<?php

namespace App\Http\Controllers\Tenant\API\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\Auth\Role\AssignUserRolesRequest;
use App\Http\Requests\Tenant\API\Auth\User\IndexUserRequest;
use App\Http\Requests\Tenant\API\Auth\User\UpdateUserPasswordRequest;
use App\Http\Requests\Tenant\API\Auth\User\UpdateUserRequest;
use App\Http\Resources\Tenant\API\Auth\Authentication\TenantUserResource;
use App\Models\Central\Auth\Role\Role;
use App\Models\Tenant\Auth\Authentication\User;
use App\Services\Tenant\Auth\User\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(IndexUserRequest $request): JsonResponse
    {
        $users = $this->userManagementService->listUsers($request->user(), $request->validated());

        return response()->json([
            'users' => TenantUserResource::collection(collect($users->items())),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ],
        ]);
    }

    public function show(Request $request, User $user): TenantUserResource
    {
        return TenantUserResource::make($user->load(['roles', 'type.resourceTypeProfile', 'profilePicture']));
    }

    public function update(UpdateUserRequest $request, User $user): TenantUserResource
    {
        $user = $this->userManagementService->updateUser(
            $request->user(),
            $user,
            $request->validated(),
        );

        return TenantUserResource::make($user);
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): TenantUserResource
    {
        $this->authorize('updatePassword', $user);
        $user = $this->userManagementService->updatePassword(
            $request->user(),
            $user,
            $request->validated('password'),
        );

        return TenantUserResource::make($user);
    }

    public function destroy(Request $request, User $user): TenantUserResource
    {
        $user = $this->userManagementService->setActiveStatus(
            $request->user(),
            $user,
            false,
        );

        return TenantUserResource::make($user);
    }

    public function assignRoles(AssignUserRolesRequest $request, User $user): TenantUserResource
    {
        $this->authorize('assignRoles', $user);

        $user = $this->userManagementService->assignRoles(
            $request->user(),
            $user,
            $request->validated('roles', []),
        );

        return TenantUserResource::make($user);
    }

    public function revokeRole(Request $request, User $user, Role $role): TenantUserResource
    {
        $this->authorize('revokeRole', $user);

        $user = $this->userManagementService->revokeRole(
            $request->user(),
            $user,
            $role,
        );

        return TenantUserResource::make($user);
    }
}
