<?php

use App\Http\Middleware\HydrateTenantAuth;
use Stancl\Tenancy\Features\UserImpersonation;

describe('Tenant Impersonation → Dashboard flow', function () {
    it('includes HydrateTenantAuth middleware on tenant web routes', function () {
        $route = app('router')->getRoutes()->getByName('tenant.dashboard');

        expect($route)->not->toBeNull();
        expect($route->gatherMiddleware())->toContain(HydrateTenantAuth::class);
    });

    it('includes HydrateTenantAuth middleware on impersonate route', function () {
        $route = app('router')->getRoutes()->getByName('tenant.impersonate');

        expect($route)->not->toBeNull();
        expect($route->gatherMiddleware())->toContain(HydrateTenantAuth::class);
    });

    it('executes HydrateTenantAuth before Authenticate in middleware priority', function () {
        $route = app('router')->getRoutes()->getByName('tenant.dashboard');
        $resolved = app('router')->gatherRouteMiddleware($route);

        $reloadIndex = null;
        $authIndex = null;

        foreach ($resolved as $i => $m) {
            $name = is_string($m) ? $m : get_class($m);

            if (str_contains($name, 'HydrateTenantAuth')) {
                $reloadIndex = $i;
            }

            if (str_contains($name, 'Authenticate:tenant')) {
                $authIndex = $i;
            }
        }

        expect($reloadIndex)->not->toBeNull();
        expect($authIndex)->not->toBeNull();
        expect($reloadIndex)->toBeLessThan($authIndex);
    });

    it('authenticates via impersonation and accesses dashboard', function () {
        $token = tenancy()->impersonate(
            $this->tenant,
            (string) $this->user->id,
            route('tenant.dashboard', ['tenant' => $this->tenant->slug]),
            'tenant',
        );

        $response = $this->get('/'.$this->tenant->slug.'/impersonate/'.$token->token);

        $response->assertRedirect(route('tenant.dashboard', ['tenant' => $this->tenant->slug]));
        expect(UserImpersonation::modelClass()::find($token->id))->toBeNull();

        $this->app['auth']->guard('web')->setUser($this->centralUser);

        $response = $this->get('/'.$this->tenant->slug.'/dashboard');
        $response->assertOk();
    });

    it('restores tenant auth from central user when tenant session is missing', function () {
        // Simulate: central user authenticated (via HydrateCentralAuth cookie),
        // but tenant guard has no session data (e.g. after workspace switch).
        $this->app['auth']->guard('web')->setUser($this->centralUser);

        $response = $this->get('/'.$this->tenant->slug.'/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('dashboard'));
    });
});
