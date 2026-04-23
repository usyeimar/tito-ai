<?php

namespace Tests;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\Auth\Role\Role;
use App\Models\Central\Tenancy\Tenant;
use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    protected ?Tenant $tenant = null;

    protected ?User $user = null;

    protected ?CentralUser $centralUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Initialize Tenancy
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        // 2. Clear Permission Cache for the tenant
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 3. Ensure Passport keys exist
        $this->ensurePassportKeys();

        // 4. Create a default Super Admin for the test
        $this->setupDefaultUser();
    }

    /**
     * Mirrors production auth: tenant web routes require both the tenant
     * session guard and a CentralUser on the web guard with workspace access.
     * Acting on the tenant guard alone hits HasAccesToWorkSpace (401).
     */
    public function actingAs(Authenticatable $user, $guard = null)
    {
        parent::actingAs($user, $guard);

        if ($guard === 'tenant' && $this->centralUser !== null) {
            $this->app['auth']->guard('web')->setUser($this->centralUser);
        }

        return $this;
    }

    protected function createTestTenant(): Tenant
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-'.Str::random(6),
        ]);

        return $tenant;
    }

    protected function setupDefaultUser(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'tenant',
        ]);

        $globalId = (string) Str::ulid();

        $this->centralUser = tenancy()->central(function () use ($globalId): CentralUser {
            $centralUser = CentralUser::firstOrCreate(
                ['email' => 'admin@test.com'],
                [
                    'global_id' => $globalId,
                    'name' => 'Test Admin',
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                ]
            );

            $centralUser->tenants()->syncWithoutDetaching([$this->tenant->getKey()]);

            return $centralUser;
        });

        $this->user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'global_id' => $this->centralUser->global_id,
                'name' => 'Test Admin',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $this->user->assignRole($role);
    }

    protected function ensurePassportKeys(): void
    {
        $privateKey = base_path('storage/oauth/oauth-private.key');
        $publicKey = base_path('storage/oauth/oauth-public.key');

        if (file_exists($privateKey) && file_exists($publicKey)) {
            return;
        }

        Artisan::call('passport:keys', ['--force' => false]);
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    protected function tenantApiUrl(string $path): string
    {
        return "/{$this->tenant->slug}/api/{$path}";
    }

    /**
     * Create a tenant user with the given roles.
     *
     * @param  list<string>  $roles
     */
    protected function createTenantUser(array $roles = ['user']): User
    {
        $user = User::factory()->create(['is_active' => true]);

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'tenant',
            ]);

            $user->assignRole($role);
        }

        return $user;
    }
}
