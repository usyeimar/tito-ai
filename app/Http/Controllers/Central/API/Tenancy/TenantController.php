<?php

namespace App\Http\Controllers\Central\API\Tenancy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Tenancy\IndexTenantRequest;
use App\Http\Requests\Central\API\Tenancy\StoreTenantRequest;
use App\Http\Requests\Central\API\Tenancy\UpdateTenantRequest;
use App\Http\Resources\Central\API\Tenancy\TenantResource;
use App\Models\Central\Tenancy\Tenant;
use App\Services\Central\Auth\Password\PasswordConfirmationService;
use App\Services\Central\Tenancy\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService,
        private readonly PasswordConfirmationService $passwordConfirmationService,
    ) {}

    public function index(IndexTenantRequest $request): JsonResponse
    {
        $tenants = $this->tenantService->listForUser($request->user(), $request->validated());

        return response()->json([
            'tenants' => TenantResource::collection(collect($tenants->items())),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ],
            'links' => [
                'first' => $tenants->url(1),
                'last' => $tenants->url($tenants->lastPage()),
                'prev' => $tenants->previousPageUrl(),
                'next' => $tenants->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->createForUser(
            $request->user(),
            $request->validated(),
        );

        return TenantResource::make($tenant)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Tenant $tenant): TenantResource
    {
        $tenant = $this->tenantService->getForUser($request->user(), $tenant);

        return TenantResource::make($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): TenantResource
    {
        $tenant = $this->tenantService->updateForUser(
            $request->user(),
            $tenant,
            $request->validated(),
        );

        return TenantResource::make($tenant);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $this->passwordConfirmationService->ensureRecentPasswordConfirmation($request->user());

        $this->tenantService->deleteForUser($request->user(), $tenant);

        return response()->json(['message' => 'Tenant deleted.']);
    }
}
