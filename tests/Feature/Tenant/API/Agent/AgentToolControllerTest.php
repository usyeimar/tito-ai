<?php

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentTool;

describe('Agent Tool API', function () {
    describe('Authentication', function () {
        it('requires authentication to list tools', function () {
            $agent = Agent::factory()->create();

            $response = $this->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools"));
            $response->assertUnauthorized();
        });

        it('requires authentication to create a tool', function () {
            $agent = Agent::factory()->create();

            $response = $this->postJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools"), [
                'name' => 'Test Tool',
            ]);
            $response->assertUnauthorized();
        });

        it('requires authentication to view a tool', function () {
            $agent = Agent::factory()->create();
            $tool = AgentTool::factory()->forAgent($agent)->create();

            $response = $this->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools/{$tool->id}"));
            $response->assertUnauthorized();
        });

        it('requires authentication to update a tool', function () {
            $agent = Agent::factory()->create();
            $tool = AgentTool::factory()->forAgent($agent)->create();

            $response = $this->patchJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools/{$tool->id}"), [
                'name' => 'Updated',
            ]);
            $response->assertUnauthorized();
        });

        it('requires authentication to delete a tool', function () {
            $agent = Agent::factory()->create();
            $tool = AgentTool::factory()->forAgent($agent)->create();

            $response = $this->deleteJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools/{$tool->id}"));
            $response->assertUnauthorized();
        });
    });

    describe('Tool Management', function () {
        describe('List', function () {
            it('lists tools for an agent', function () {
                $agent = Agent::factory()->create();
                AgentTool::factory()->count(3)->forAgent($agent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools"));

                $response->assertOk();
                $response->assertJsonCount(3, 'data');
            });

            it('does not list tools from other agents', function () {
                $agent = Agent::factory()->create();
                $otherAgent = Agent::factory()->create();
                AgentTool::factory()->count(2)->forAgent($agent)->create();
                AgentTool::factory()->count(3)->forAgent($otherAgent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools"));

                $response->assertOk();
                $response->assertJsonCount(2, 'data');
            });
        });

        describe('Create', function () {
            it('creates a tool and returns 201', function () {
                $agent = Agent::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools"), [
                        'name' => 'search_knowledge',
                        'description' => 'Search the knowledge base',
                        'parameters' => ['query' => ['type' => 'string']],
                        'is_active' => true,
                    ]);

                $response->assertCreated();
                $response->assertJsonPath('data.name', 'search_knowledge');
                $response->assertJsonPath('data.description', 'Search the knowledge base');
                $response->assertJsonPath('data.is_active', true);
                $response->assertJsonPath('message', 'Tool created.');
            });

            it('requires name to create a tool', function () {
                $agent = Agent::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools"), [
                        'description' => 'No name',
                    ]);

                assertHasValidationError($response, 'name');
            });
        });

        describe('Show', function () {
            it('shows a single tool', function () {
                $agent = Agent::factory()->create();
                $tool = AgentTool::factory()->forAgent($agent)->create(['name' => 'lookup_crm']);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools/{$tool->id}"));

                $response->assertOk();
                $response->assertJsonPath('data.id', $tool->id);
                $response->assertJsonPath('data.name', 'lookup_crm');
            });

            it('returns 404 for non-existent tool', function () {
                $agent = Agent::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools/01HX99999999999999999999999"));

                $response->assertNotFound();
            });
        });

        describe('Update', function () {
            it('updates a tool', function () {
                $agent = Agent::factory()->create();
                $tool = AgentTool::factory()->forAgent($agent)->create([
                    'name' => 'original_tool',
                    'is_active' => true,
                ]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->patchJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools/{$tool->id}"), [
                        'name' => 'updated_tool',
                        'is_active' => false,
                    ]);

                $response->assertOk();
                $response->assertJsonPath('data.name', 'updated_tool');
                $response->assertJsonPath('data.is_active', false);
                $response->assertJsonPath('message', 'Tool updated.');

                expect($tool->fresh()->name)->toBe('updated_tool');
                expect($tool->fresh()->is_active)->toBeFalse();
            });
        });

        describe('Delete', function () {
            it('deletes a tool and returns 204', function () {
                $agent = Agent::factory()->create();
                $tool = AgentTool::factory()->forAgent($agent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->deleteJson($this->tenantApiUrl("ai/agents/{$agent->id}/tools/{$tool->id}"));

                $response->assertNoContent();
                expect(AgentTool::query()->whereKey($tool->id)->exists())->toBeFalse();
            });
        });
    });
});
