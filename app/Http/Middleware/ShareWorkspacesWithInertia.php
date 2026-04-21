<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\Central\API\Tenancy\TenantResource;
use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Tenancy\TenantService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

readonly class ShareWorkspacesWithInertia
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Get the central user based on global_id
            $centralUser = CentralUser::query()
                ->where('global_id', $user->global_id)
                ->first();

            if ($centralUser) {
                $tenants = $this->tenantService->listForUser($centralUser);

                Inertia::share([
                    'workspaces' => collect($tenants->items())
                        ->map(fn ($tenant) => TenantResource::make($tenant)->resolve()),
                ]);
            }
        }

        return $next($request);
    }
}
