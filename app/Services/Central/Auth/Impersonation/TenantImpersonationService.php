<?php

namespace App\Services\Central\Auth\Impersonation;

use App\Exceptions\TenantImpersonationUnavailableException;
use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\Tenancy\Tenant;
use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Auth\Access\AuthorizationException;

class TenantImpersonationService
{
    public function createImpersonationUrl(CentralUser $centralUser, string $tenantSlug, string $redirectUrl): array
    {
        $tenant = Tenant::query()->where('slug', $tenantSlug)->firstOrFail();

        $hasAccess = $centralUser->tenants()->whereKey($tenant->getKey())->exists();
        $isSupport = $centralUser->hasAnyRole(['support', 'super_admin']);
        if (! $hasAccess && ! $isSupport) {
            throw new AuthorizationException;
        }

        $tenantUserId = $tenant->run(fn () => User::query()
            ->where('global_id', $centralUser->global_id)
            ->where('is_active', true)
            ->value('id'));

        if (! $tenantUserId) {
            throw new TenantImpersonationUnavailableException('Your tenant account is inactive or unavailable. Please contact your workspace administrator.');
        }

        $token = tenancy()->impersonate($tenant, (string) $tenantUserId, $redirectUrl, 'tenant');
        if ($isSupport) {
            $token->impersonator_central_user_id = $centralUser->id;
            $token->save();
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        return [
            'url' => "{$baseUrl}/{$tenant->slug}/api/impersonate/{$token->token}",
            'token' => $token->token,
        ];
    }
}
