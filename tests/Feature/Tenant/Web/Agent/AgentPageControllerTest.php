<?php

use App\Models\Tenant\Agent\Agent;

describe('Agent Pages (Inertia)', function () {
    describe('Authentication', function () {
        it('redirects guests to login on index', function () {
            $response = $this->get('/'.$this->tenant->slug.'/agents');
            $response->assertRedirect();
        });

        it('redirects guests to login on show', function () {
            $agent = Agent::factory()->create();

            $response = $this->get('/'.$this->tenant->slug.'/agents/'.$agent->id);
            $response->assertRedirect();
        });
    });

    describe('Index', function () {
        it('renders the agents index page', function () {
            $response = $this->actingAs($this->user, 'tenant')
                ->get('/'.$this->tenant->slug.'/agents');

            $response->assertOk();
            $response->assertInertia(
                fn ($page) => $page
                    ->component('tenant/agents/show')
                    ->where('agent', null)
                    ->has('agents')
                    ->has('tenant.id')
            );
        });

        it('lists existing agents', function () {
            Agent::factory()->count(3)->create();

            $response = $this->actingAs($this->user, 'tenant')
                ->get('/'.$this->tenant->slug.'/agents');

            $response->assertOk();
            $response->assertInertia(
                fn ($page) => $page->has('agents', 3)
            );
        });

        it('supports search query parameter', function () {
            Agent::factory()->create(['name' => 'Sales Bot']);
            Agent::factory()->create(['name' => 'Support Bot']);

            $response = $this->actingAs($this->user, 'tenant')
                ->get('/'.$this->tenant->slug.'/agents?search=Sales');

            $response->assertOk();
        });
    });

    describe('Show', function () {
        it('renders the agent detail page', function () {
            $agent = Agent::factory()->create();

            $response = $this->actingAs($this->user, 'tenant')
                ->get('/'.$this->tenant->slug.'/agents/'.$agent->id);

            $response->assertOk();
            $response->assertInertia(
                fn ($page) => $page
                    ->component('tenant/agents/show')
                    ->where('agent.id', (string) $agent->id)
                    ->has('agents')
            );
        });

        it('returns 404 for an unknown agent', function () {
            $response = $this->actingAs($this->user, 'tenant')
                ->get('/'.$this->tenant->slug.'/agents/01HX99999999999999999999999');

            $response->assertNotFound();
        });
    });
});
