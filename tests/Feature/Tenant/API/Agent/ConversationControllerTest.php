<?php

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentSession;
use App\Models\Tenant\Agent\AgentSessionTranscript;

describe('Conversation API', function () {
    describe('Authentication', function () {
        it('requires authentication to list conversations', function () {
            $response = $this->getJson($this->tenantApiUrl('ai/conversations'));
            $response->assertUnauthorized();
        });

        it('requires authentication to view a conversation', function () {
            $session = AgentSession::factory()->create();

            $response = $this->getJson($this->tenantApiUrl("ai/conversations/{$session->id}"));
            $response->assertUnauthorized();
        });

        it('requires authentication to view transcripts', function () {
            $session = AgentSession::factory()->create();

            $response = $this->getJson($this->tenantApiUrl("ai/conversations/{$session->id}/transcripts"));
            $response->assertUnauthorized();
        });

        it('requires authentication to delete a conversation', function () {
            $session = AgentSession::factory()->create();

            $response = $this->deleteJson($this->tenantApiUrl("ai/conversations/{$session->id}"));
            $response->assertUnauthorized();
        });
    });

    describe('Conversation Management', function () {
        describe('List', function () {
            it('lists conversations with pagination', function () {
                $agent = Agent::factory()->create();
                AgentSession::factory()->count(3)->forAgent($agent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl('ai/conversations'));

                $response->assertOk();
                $response->assertJsonCount(3, 'data');
                $response->assertJsonStructure([
                    'data',
                    'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                ]);
            });

            it('filters conversations by agent_id', function () {
                $agent = Agent::factory()->create();
                $otherAgent = Agent::factory()->create();
                AgentSession::factory()->count(2)->forAgent($agent)->create();
                AgentSession::factory()->count(3)->forAgent($otherAgent)->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/conversations?filter[agent_id]={$agent->id}"));

                $response->assertOk();
                $response->assertJsonCount(2, 'data');
            });

            it('filters conversations by status', function () {
                $agent = Agent::factory()->create();
                AgentSession::factory()->count(2)->forAgent($agent)->create(['status' => 'completed']);
                AgentSession::factory()->forAgent($agent)->active()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl('ai/conversations?filter[status]=active'));

                $response->assertOk();
                $response->assertJsonCount(1, 'data');
            });
        });

        describe('Show', function () {
            it('shows a single conversation', function () {
                $agent = Agent::factory()->create(['name' => 'Test Agent']);
                $session = AgentSession::factory()->forAgent($agent)->create([
                    'status' => 'completed',
                    'channel' => 'web-widget',
                ]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/conversations/{$session->id}"));

                $response->assertOk();
                $response->assertJsonPath('data.id', $session->id);
                $response->assertJsonPath('data.status', 'completed');
            });

            it('returns 404 for non-existent conversation', function () {
                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl('ai/conversations/01HX99999999999999999999999'));

                $response->assertNotFound();
            });
        });

        describe('Transcripts', function () {
            it('returns transcripts for a conversation', function () {
                $session = AgentSession::factory()->create();
                AgentSessionTranscript::create([
                    'agent_session_id' => $session->id,
                    'role' => 'agent',
                    'content' => 'Hello, how can I help?',
                    'timestamp' => now(),
                ]);
                AgentSessionTranscript::create([
                    'agent_session_id' => $session->id,
                    'role' => 'user',
                    'content' => 'I need help with billing.',
                    'timestamp' => now(),
                ]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/conversations/{$session->id}/transcripts"));

                $response->assertOk();
                $response->assertJsonCount(2, 'data');
            });

            it('returns empty array when no transcripts exist', function () {
                $session = AgentSession::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/conversations/{$session->id}/transcripts"));

                $response->assertOk();
                $response->assertJsonCount(0, 'data');
            });
        });

        describe('Delete', function () {
            it('deletes a conversation and returns 204', function () {
                $session = AgentSession::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->deleteJson($this->tenantApiUrl("ai/conversations/{$session->id}"));

                $response->assertNoContent();
                expect(AgentSession::query()->whereKey($session->id)->exists())->toBeFalse();
            });
        });
    });
});
