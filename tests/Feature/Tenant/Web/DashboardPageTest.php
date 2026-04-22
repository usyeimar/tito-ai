<?php

describe('Tenant Dashboard Page', function () {
    describe('Authentication', function () {
        it('redirects guests to login', function () {
            $response = $this->get('/'.$this->tenant->slug.'/dashboard');
            $response->assertRedirect();
        });
    });

    describe('Render', function () {
        it('renders the dashboard page', function () {
            $response = $this->actingAs($this->user, 'tenant')
                ->get('/'.$this->tenant->slug.'/dashboard');

            $response->assertOk();
            $response->assertInertia(
                fn ($page) => $page->component('dashboard')
            );
        });
    });
});
