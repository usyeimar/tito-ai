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
        Passport::cookie(config('passport_tokens.access_cookie.tenant_name', 'tenant_access_token'));
    }

    public function revert(): void
    {
        Passport::cookie(config('passport_tokens.access_cookie.central_name', 'central_access_token'));
    }
}
