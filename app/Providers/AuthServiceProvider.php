<?php

namespace App\Providers;

use App\Models\Central\Auth\Role\Role;
use App\Models\Central\Tenancy\TenantInvitation;
use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\System\ColumnConfiguration\SystemColumnConfiguration;
use App\Models\Tenant\System\ColumnConfiguration\SystemUserColumnConfiguration;
use App\Policies\RolePolicy;
use App\Policies\SystemConfigurationPolicy;
use App\Policies\SystemUserColumnConfigurationPolicy;
use App\Policies\Tenant\Agent\AgentPolicy;
use App\Policies\TenantInvitationPolicy;
use App\Policies\UserPolicy;
use App\Support\Passport\ImpersonationTokenGrant;
use DateInterval;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        SystemColumnConfiguration::class => SystemConfigurationPolicy::class,
        SystemUserColumnConfiguration::class => SystemUserColumnConfigurationPolicy::class,
        Role::class => RolePolicy::class,
        TenantInvitation::class => TenantInvitationPolicy::class,
        Agent::class => AgentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Passport::loadKeysFrom(base_path('storage/oauth'));
        Passport::enablePasswordGrant();
        Passport::$clientUuids = false;

        $accessMinutes = max(5, (int) config('passport_tokens.access_ttl_minutes', 60));
        $refreshDays = max(1, (int) config('passport_tokens.refresh_ttl_days', 30));

        Passport::tokensExpireIn(now()->addMinutes($accessMinutes));
        Passport::refreshTokensExpireIn(now()->addDays($refreshDays));

        $this->registerImpersonationGrant($accessMinutes, $refreshDays);
    }

    private function registerImpersonationGrant(int $accessMinutes, int $refreshDays): void
    {
        if (! $this->passportKeysExist()) {
            return;
        }

        $server = $this->app->make(AuthorizationServer::class);

        $grant = new ImpersonationTokenGrant(
            $this->app->make(RefreshTokenRepository::class),
        );

        $grant->setRefreshTokenTTL(new DateInterval('P'.$refreshDays.'D'));
        $server->enableGrantType($grant, new DateInterval('PT'.$accessMinutes.'M'));
    }

    private function passportKeysExist(): bool
    {
        $privateKey = Passport::keyPath('oauth-private.key');
        $publicKey = Passport::keyPath('oauth-public.key');

        return file_exists($privateKey) && file_exists($publicKey);
    }
}
