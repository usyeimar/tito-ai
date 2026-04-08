<?php

namespace App\Http\Controllers\Central\API\Auth\Impersonation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Auth\Impersonation\CreateTenantImpersonationRequest;
use App\Http\Resources\Central\API\Auth\Impersonation\ImpersonationUrlResource;
use App\Services\Central\Auth\Impersonation\TenantImpersonationService;
use Illuminate\Support\Facades\Log;

class TenantImpersonationController extends Controller
{
    public function __construct(
        private readonly TenantImpersonationService $tenantImpersonationService,
    ) {}

    public function create(CreateTenantImpersonationRequest $request): ImpersonationUrlResource
    {
        $result = $this->tenantImpersonationService->createImpersonationUrl(
            $request->user(),
            $request->validated('tenant'),
            $request->validated('redirect_url', '/'),
        );

        Log::info('Tenant impersonation issued.', [
            'user_id' => $request->user()?->id,
            'tenant' => $request->validated('tenant'),
            'redirect_url' => $request->validated('redirect_url', '/'),
            'ip' => $request->ip(),
        ]);

        return ImpersonationUrlResource::make($result);
    }
}
