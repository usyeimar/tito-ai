<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Tenant\Auth\Authentication\User as TenantUser;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\Tenant;

class AuthenticateTenantUser
{
    public function handle(Request $request, Closure $next)
    {
        // Si ya está autenticado con el guard tenant, continuar
        if (auth('tenant')->check()) {
            return $next($request);
        }

        // Verificar si hay un usuario autenticado en el guard central (web)
        $centralUser = auth('web')->user();

        if (! $centralUser instanceof CentralUser) {
            return redirect()->route('login');
        }

        // Obtener el tenant actual
        /** @var Tenant|null $tenant */
        $tenant = tenant();

        if (! $tenant) {
            return redirect()->route('login');
        }

        // Buscar el usuario correspondiente en el tenant
        $tenantUser = $tenant->run(function () use ($centralUser): ?TenantUser {
            return TenantUser::query()
                ->where('global_id', $centralUser->global_id)
                ->where('is_active', true)
                ->first();
        });

        if (! $tenantUser) {
            abort(403, 'Your tenant account is inactive or unavailable. Please contact your workspace administrator.');
        }

        // Autenticar al usuario del tenant
        auth('tenant')->login($tenantUser);

        return $next($request);
    }
}
