<?php

namespace App\Listeners\Permissions;

use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Events\TenancyInitialized;

class SetPermissionCacheKey
{
    public function handle(TenancyInitialized $event): void
    {
        $tenant = $event->tenancy->tenant;
        if (! $tenant) {
            return;
        }

        $tenantKey = (string) $tenant->getKey();
        if ($tenantKey === '') {
            return;
        }

        config(['permission.cache.key' => "spatie.permission.cache.tenant.{$tenantKey}"]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
