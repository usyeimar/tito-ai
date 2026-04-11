<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use App\Models\Tenant\Agent\Agent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * HTTP client for the Tito AI Runners FastAPI microservice.
 *
 * Responsible for creating, listing and terminating voice agent sessions.
 * The runner is the only component that talks to LiveKit / Daily directly.
 *
 * @see services/runners/app/api/v1/sessions.py
 */
final class RunnerClient
{
    public function __construct(private readonly AgentConfigBuilder $configBuilder) {}

    /**
     * Create a new voice session for the given agent and return the
     * connection payload that the browser SDK needs.
     *
     * @return array{
     *     session_id: string,
     *     room_name: string,
     *     provider: string,
     *     url: string,
     *     access_token: string,
     *     context: array<string, mixed>,
     * }
     */
    public function createSession(Agent $agent): array
    {
        $config = $this->configBuilder->build($agent);

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
            'url' => (string) ($payload['url'] ?? ''),
            'access_token' => (string) ($payload['access_token'] ?? ''),
            'context' => (array) ($payload['context'] ?? []),
        ];
    }

    public function terminateSession(string $sessionId): bool
    {
        try {
            $response = $this->request()
                ->delete('/api/v1/sessions/'.urlencode($sessionId));
        } catch (ConnectionException) {
            return false;
        }

        return $response->successful();
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl((string) config('runners.base_url'))
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
