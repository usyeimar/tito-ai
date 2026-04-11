<?php

declare(strict_types=1);

namespace App\Services\Central\Tenancy\Bootstrappers;

use Laravel\Passport\Passport;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class PassportCookieBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant): void
    {
        Passport::cookie('tenant_token');
    }

    public function revert(): void
    {
        Passport::cookie('laravel_token');
    }
}
