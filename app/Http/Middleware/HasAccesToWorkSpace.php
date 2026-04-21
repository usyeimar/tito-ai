<?php

namespace App\Http\Middleware;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Tenant\Auth\Authentication\User;
use App\Services\Central\Tenancy\TenantService;
use Closure;
use Illuminate\Http\Request;

class HasAccesToWorkSpace
{
    public function handle(Request $request, Closure $next)
    {
        // $tenant = tenant();

        // if (! $tenant) {
        //     return $next($request);
        // }

        // /** @var CentralUser|null $centralUser */
        $centralUser = auth('web')->user();

        // dd($centralUser);
        $tenants = app(TenantService::class)->listForUser($centralUser);

        $currentTenantSlug = tenant()?->slug;
        $hasAccessToTenant = collect($tenants->toArray()['data'])
            ->pluck('slug')
            ->contains($currentTenantSlug);

        if (! $hasAccessToTenant) {
            abort(403, 'Your do not have access to this workspace. Please contact your workspace administrator.');
        }

        return $next($request);
    }
}
