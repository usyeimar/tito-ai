<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use App\Models\Tenant\Agent\Agent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * HTTP client for the Tito AI Runners FastAPI microservice.
 *
 * Responsible for creating, listing and terminating voice agent sessions.
 * The runner is the only component that talks to LiveKit / Daily directly.
 *
 * When use_registry is enabled, uses RunnerRegistry for load balancing.
 *
 * @see services/runners/app/api/v1/sessions.py
 */
final class RunnerClient
{
    public function __construct(
        private readonly AgentConfigBuilder $configBuilder,
        private readonly ?RunnerRegistry $runnerRegistry = null,
    ) {}

    /**
     * Create a new voice session for the given agent and return the
     * connection payload that the browser SDK needs.
     *
     * Generates a unique channel_id that the frontend will use to listen
     * for session events (ended, transcript, etc.) via WebSocket.
     *
     * @return array{
     *     session_id: string,
     *     room_name: string,
     *     provider: string,
     *     url: string,
     *     access_token: string,
     *     channel_id: string,
     *     context: array<string, mixed>,
     * }
     */
    public function createSession(Agent $agent): array
    {
        // Generate a unique channel ID for this session
        $channelId = (string) Str::uuid();

        // Build config with session-specific callback URL
        $config = $this->configBuilder->build($agent, $channelId);

        try {
            $response = $this->request()
                ->post('/api/v1/sessions/', $config);
        } catch (ConnectionException $e) {
            throw new RuntimeException(
                'No se pudo contactar el servicio de runners: '.$e->getMessage(),
                previous: $e,
            );
        }

        if ($response->failed()) {
            Log::warning('Tito runners session create failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $message = (string) ($response->json('detail.message')
                ?? $response->json('message')
                ?? $response->json('detail')
                ?? 'Failed to create runner session');

            throw new RuntimeException($message);
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return [
            'session_id' => (string) ($payload['session_id'] ?? ''),
            'room_name' => (string) ($payload['room_name'] ?? ''),
            'provider' => (string) ($payload['provider'] ?? ''),
            'url' => (string) ($payload['ws_url'] ?? $payload['url'] ?? ''),
            'access_token' => (string) ($payload['access_token'] ?? ''),
            'channel_id' => $channelId,
            'context' => (array) ($payload['context'] ?? []),
        ];
    }

    /**
     * Terminate a session on the correct runner.
     *
     * If the session was on a specific runner (identified by host_id),
     * sends the delete to that runner. Otherwise, uses the registry.
     */
    public function terminateSession(string $sessionId, ?string $hostId = null): bool
    {
        try {
            // If we know which runner has the session, use it directly
            if ($hostId) {
                $runner = $this->runnerRegistry?->getRunner($hostId);
                if ($runner && ($runner['url'] ?? '')) {
                    return $this->terminateOnRunner($runner['url'], $sessionId);
                }
            }

            // Fallback: try base_url or any available runner
            $url = $this->getRunnerUrl();
            if (! $url) {
                return false;
            }

            return $this->terminateOnRunner($url, $sessionId);
        } catch (ConnectionException) {
            return false;
        }
    }

    /**
     * Get the runner URL to use for requests.
     *
     * Uses registry for load balancing if enabled, otherwise falls back to base_url.
     */
    private function getRunnerUrl(): ?string
    {
        if (config('runners.use_registry', false) && $this->runnerRegistry) {
            $runner = $this->runnerRegistry->getAvailableRunner();
            if ($runner && ($runner['url'] ?? '')) {
                Log::debug('Using runner from registry', [
                    'host_id' => $runner['host_id'] ?? 'unknown',
                    'url' => $runner['url'],
                    'active_sessions' => $runner['active_sessions'] ?? 0,
                ]);

                return $runner['url'];
            }
        }

        // Fallback to configured base URL
        return rtrim((string) config('runners.base_url', 'http://localhost:8000'), '/');
    }

    /**
     * Send terminate request to a specific runner URL.
     */
    private function terminateOnRunner(string $baseUrl, string $sessionId): bool
    {
        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout((int) config('runners.timeout', 15))
                ->acceptJson()
                ->asJson()
                ->delete('/api/v1/sessions/'.urlencode($sessionId));

            return $response->successful();
        } catch (ConnectionException) {
            return false;
        }
    }

    private function request(): PendingRequest
    {
        $url = $this->getRunnerUrl();

        $request = Http::baseUrl($url)
            ->timeout((int) config('runners.timeout', 15))
            ->acceptJson()
            ->asJson();

        $apiKey = config('runners.api_key');
        if (is_string($apiKey) && $apiKey !== '') {
            $request = $request->withToken($apiKey);
        }

        return $request;
    }
}
