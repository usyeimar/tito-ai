<?php

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentSession;

describe('Agent Session Webhook API', function () {
    describe('Authentication', function () {
        it('rejects requests without valid signature when api key is set', function () {
            config(['runners.api_key' => 'test-secret']);

            $response = $this->postJson(
                $this->tenantApiUrl('ai/runner/webhook'),
                ['event' => 'session.started', 'agent_id' => 'abc', 'data' => []],
            );

            $response->assertUnauthorized();
        });

        it('accepts requests with valid api key header', function () {
            config(['runners.api_key' => 'test-secret']);
            $agent = Agent::factory()->create();

            $response = $this->postJson(
                $this->tenantApiUrl('ai/runner/webhook'),
                [
                    'event' => 'session.started',
                    'agent_id' => $agent->id,
                    'data' => ['session_id' => 'sess_auth_test'],
                ],
                ['X-Tito-Agent-Key' => 'test-secret'],
            );

            $response->assertOk();
        });

        it('accepts requests with valid HMAC signature', function () {
            $secret = 'hmac-test-secret';
            config(['runners.api_key' => $secret]);
            $agent = Agent::factory()->create();

            $body = json_encode([
                'event' => 'session.started',
                'agent_id' => $agent->id,
                'data' => ['session_id' => 'sess_hmac_test'],
            ]);
            $timestamp = (string) time();
            $signature = 'sha256='.hash_hmac('sha256', $timestamp.$body, $secret);

            $response = $this->call('POST', $this->tenantApiUrl('ai/runner/webhook'), [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_TITO_SIGNATURE' => $signature,
                'HTTP_X_TITO_TIMESTAMP' => $timestamp,
            ], $body);

            $response->assertOk();
        });

        it('allows requests when no api key is configured', function () {
            config(['runners.api_key' => null]);
            $agent = Agent::factory()->create();

            $response = $this->postJson(
                $this->tenantApiUrl('ai/runner/webhook'),
                [
                    'event' => 'session.started',
                    'agent_id' => $agent->id,
                    'data' => ['session_id' => 'sess_no_key'],
                ],
            );

            $response->assertOk();
        });
    });

    describe('Validation', function () {
        it('rejects missing event field', function () {
            config(['runners.api_key' => null]);

            $response = $this->postJson(
                $this->tenantApiUrl('ai/runner/webhook'),
                ['agent_id' => 'abc', 'data' => []],
            );

            $response->assertUnprocessable();
        });

        it('rejects invalid event type', function () {
            config(['runners.api_key' => null]);

            $response = $this->postJson(
                $this->tenantApiUrl('ai/runner/webhook'),
                ['event' => 'invalid.event', 'agent_id' => 'abc', 'data' => []],
            );

            $response->assertUnprocessable();
        });
    });

    describe('Event Handling', function () {
        beforeEach(function () {
            config(['runners.api_key' => null]);
        });

        describe('session.started', function () {
            it('creates an agent session in the database', function () {
                $agent = Agent::factory()->create();

                $response = $this->postJson(
                    $this->tenantApiUrl('ai/runner/webhook'),
                    [
                        'event' => 'session.started',
                        'agent_id' => $agent->id,
                        'data' => [
                            'session_id' => 'sess_start_001',
                            'channel' => 'web-widget',
                        ],
                    ],
                );

                $response->assertOk();
                $response->assertJsonPath('status', 'received');

                $session = AgentSession::where('external_session_id', 'sess_start_001')->first();
                expect($session)->not->toBeNull();
                expect($session->status)->toBe('active');
                expect($session->agent_id)->toBe($agent->id);
                expect($session->channel)->toBe('web-widget');
            });
        });

        describe('session.ended', function () {
            it('marks session as completed with metadata', function () {
                $agent = Agent::factory()->create();
                $session = AgentSession::factory()->forAgent($agent)->active()->create([
                    'external_session_id' => 'sess_end_001',
                ]);

                $response = $this->postJson(
                    $this->tenantApiUrl('ai/runner/webhook'),
                    [
                        'event' => 'session.ended',
                        'agent_id' => $agent->id,
                        'data' => [
                            'session_id' => 'sess_end_001',
                            'status' => 'completed',
                            'duration_seconds' => 120,
                            'reason' => 'user_hangup',
                        ],
                    ],
                );

                $response->assertOk();

                $session->refresh();
                expect($session->status)->toBe('completed');
                expect($session->ended_at)->not->toBeNull();
                expect($session->metadata['duration_seconds'])->toBe(120);
                expect($session->metadata['termination_reason'])->toBe('user_hangup');
            });

            it('stores transcription entries from ended event', function () {
                $agent = Agent::factory()->create();
                AgentSession::factory()->forAgent($agent)->active()->create([
                    'external_session_id' => 'sess_transcript_end',
                ]);

                $this->postJson(
                    $this->tenantApiUrl('ai/runner/webhook'),
                    [
                        'event' => 'session.ended',
                        'agent_id' => $agent->id,
                        'data' => [
                            'session_id' => 'sess_transcript_end',
                            'status' => 'completed',
                            'transcription' => [
                                ['role' => 'agent', 'content' => 'Hello!'],
                                ['role' => 'user', 'content' => 'Hi there'],
                                ['role' => 'system', 'content' => 'ignored'],
                            ],
                        ],
                    ],
                );

                $session = AgentSession::where('external_session_id', 'sess_transcript_end')->first();
                expect($session->transcripts)->toHaveCount(2);
            });
        });

        describe('session.transcript', function () {
            it('stores a real-time transcript entry', function () {
                $agent = Agent::factory()->create();
                AgentSession::factory()->forAgent($agent)->active()->create([
                    'external_session_id' => 'sess_rt_001',
                ]);

                $response = $this->postJson(
                    $this->tenantApiUrl('ai/runner/webhook'),
                    [
                        'event' => 'session.transcript',
                        'agent_id' => $agent->id,
                        'data' => [
                            'session_id' => 'sess_rt_001',
                            'role' => 'user',
                            'content' => 'I need help with my account',
                        ],
                    ],
                );

                $response->assertOk();

                $session = AgentSession::where('external_session_id', 'sess_rt_001')->first();
                expect($session->transcripts)->toHaveCount(1);
                expect($session->transcripts->first()->content)->toBe('I need help with my account');
            });
        });

        describe('session.error', function () {
            it('marks session as failed with error metadata', function () {
                $agent = Agent::factory()->create();
                AgentSession::factory()->forAgent($agent)->active()->create([
                    'external_session_id' => 'sess_err_001',
                ]);

                $response = $this->postJson(
                    $this->tenantApiUrl('ai/runner/webhook'),
                    [
                        'event' => 'session.error',
                        'agent_id' => $agent->id,
                        'data' => [
                            'session_id' => 'sess_err_001',
                            'error' => 'LLM provider timeout',
                        ],
                    ],
                );

                $response->assertOk();

                $session = AgentSession::where('external_session_id', 'sess_err_001')->first();
                expect($session->status)->toBe('failed');
                expect($session->ended_at)->not->toBeNull();
                expect($session->metadata['error'])->toBe('LLM provider timeout');
            });
        });
    });
});
