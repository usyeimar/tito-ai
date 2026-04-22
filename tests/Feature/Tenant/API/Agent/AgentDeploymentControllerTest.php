<?php

use App\Enums\DeploymentChannel;
use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentDeployment;

describe('Agent Deployment API', function () {
    describe('Authentication', function () {
        it('requires authentication to list deployments', function () {
            $agent = Agent::factory()->create();

            $response = $this->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments"));
            $response->assertUnauthorized();
        });

        it('requires authentication to create a deployment', function () {
            $agent = Agent::factory()->create();

            $response = $this->postJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments"), [
                'channel' => DeploymentChannel::WebWidget->value,
            ]);
            $response->assertUnauthorized();
        });

        it('requires authentication to view a deployment', function () {
            $agent = Agent::factory()->create();
            $deployment = AgentDeployment::factory()->forAgent($agent)->create();

            $response = $this->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments/{$deployment->id}"));
            $response->assertUnauthorized();
        });

        it('requires authentication to update a deployment', function () {
            $agent = Agent::factory()->create();
            $deployment = AgentDeployment::factory()->forAgent($agent)->create();

            $response = $this->patchJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments/{$deployment->id}"), [
                'enabled' => false,
            ]);
            $response->assertUnauthorized();
        });

        it('requires authentication to delete a deployment', function () {
            $agent = Agent::factory()->create();
            $deployment = AgentDeployment::factory()->forAgent($agent)->create();

            $response = $this->deleteJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments/{$deployment->id}"));
            $response->assertUnauthorized();
        });
    });

    describe('Deployment Management', function () {
        describe('List', function () {
            it('lists deployments for an agent', function () {
                $agent = Agent::factory()->create();
                AgentDeployment::factory()->count(3)->forAgent($agent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments"));

                $response->assertOk();
                $response->assertJsonCount(3, 'data');
            });

            it('does not list deployments from other agents', function () {
                $agent = Agent::factory()->create();
                $otherAgent = Agent::factory()->create();
                AgentDeployment::factory()->count(2)->forAgent($agent)->create();
                AgentDeployment::factory()->count(3)->forAgent($otherAgent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments"));

                $response->assertOk();
                $response->assertJsonCount(2, 'data');
            });
        });

        describe('Create', function () {
            it('creates a deployment and returns 201', function () {
                $agent = Agent::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments"), [
                        'channel' => DeploymentChannel::WebWidget->value,
                        'enabled' => true,
                        'config' => ['theme' => 'dark'],
                        'version' => '1.0.0',
                    ]);

                $response->assertCreated();
                $response->assertJsonPath('data.channel', DeploymentChannel::WebWidget->value);
                $response->assertJsonPath('data.enabled', true);
                $response->assertJsonPath('data.config.theme', 'dark');
                $response->assertJsonPath('message', 'Deployment created.');
            });

            it('requires channel to create a deployment', function () {
                $agent = Agent::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments"), [
                        'enabled' => true,
                    ]);

                assertHasValidationError($response, 'channel');
            });

            it('rejects invalid channel value', function () {
                $agent = Agent::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments"), [
                        'channel' => 'invalid-channel',
                    ]);

                assertHasValidationError($response, 'channel');
            });
        });

        describe('Show', function () {
            it('shows a single deployment', function () {
                $agent = Agent::factory()->create();
                $deployment = AgentDeployment::factory()->forAgent($agent)->webWidget()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments/{$deployment->id}"));

                $response->assertOk();
                $response->assertJsonPath('data.id', $deployment->id);
                $response->assertJsonPath('data.channel', DeploymentChannel::WebWidget->value);
            });

            it('returns 404 for non-existent deployment', function () {
                $agent = Agent::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments/01HX99999999999999999999999"));

                $response->assertNotFound();
            });
        });

        describe('Update', function () {
            it('updates a deployment', function () {
                $agent = Agent::factory()->create();
                $deployment = AgentDeployment::factory()->forAgent($agent)->create([
                    'enabled' => true,
                    'version' => '1.0.0',
                ]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->patchJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments/{$deployment->id}"), [
                        'enabled' => false,
                        'version' => '2.0.0',
                        'config' => ['position' => 'top-left'],
                    ]);

                $response->assertOk();
                $response->assertJsonPath('data.enabled', false);
                $response->assertJsonPath('data.version', '2.0.0');
                $response->assertJsonPath('message', 'Deployment updated.');

                expect($deployment->fresh()->enabled)->toBeFalse();
                expect($deployment->fresh()->version)->toBe('2.0.0');
            });
        });

        describe('Delete', function () {
            it('deletes a deployment and returns 204', function () {
                $agent = Agent::factory()->create();
                $deployment = AgentDeployment::factory()->forAgent($agent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->deleteJson($this->tenantApiUrl("ai/agents/{$agent->id}/deployments/{$deployment->id}"));

                $response->assertNoContent();
                expect(AgentDeployment::query()->whereKey($deployment->id)->exists())->toBeFalse();
            });
        });
    });
});
