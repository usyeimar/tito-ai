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
 * Includes circuit breaker and retry with exponential backoff.
 *
 * @see services/runners/app/api/v1/sessions.py
 */
final class RunnerClient
{
    private const MAX_RETRIES = 3;

    private const BASE_DELAY_MS = 200;

    public function __construct(
        private readonly AgentConfigBuilder $configBuilder,
        private readonly CircuitBreaker $circuitBreaker,
        private readonly ?RunnerRegistry $runnerRegistry = null,
    ) {}

    /**
     * Create a new voice session for the given agent.
     *
     * The runner decides which transport (livekit/daily) to use based on
     * its own configuration — Laravel never sends transport preference.
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
        $this->ensureAvailable();

        $config = $this->configBuilder->build($agent);

        return $this->withRetry(function () use ($config): array {
            $response = $this->request()
                ->post('/api/v1/sessions/', $config);

            if ($response->failed()) {
                Log::warning('Runner session create failed', [
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
                'context' => (array) ($payload['context'] ?? []),
            ];
        });
    }

    /**
     * Terminate a session on the correct runner.
     */
    public function terminateSession(string $sessionId, ?string $hostId = null): bool
    {
        try {
            if ($hostId) {
                $runner = $this->runnerRegistry?->getRunner($hostId);
                if ($runner && ($runner['url'] ?? '')) {
                    return $this->terminateOnRunner($runner['url'], $sessionId);
                }
            }

            $url = $this->getRunnerUrl();

            return $url ? $this->terminateOnRunner($url, $sessionId) : false;
        } catch (ConnectionException) {
            return false;
        }
    }

    /**
     * Execute a callable with retry + exponential backoff + circuit breaker.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function withRetry(callable $callback): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $result = $callback();
                $this->circuitBreaker->recordSuccess();

                return $result;
            } catch (ConnectionException $e) {
                $lastException = $e;
                $this->circuitBreaker->recordFailure();
                Log::warning('Runner connection failed', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
            } catch (RuntimeException $e) {
                // Don't retry business logic errors (4xx)
                $this->circuitBreaker->recordFailure();
                throw $e;
            }

            if ($attempt < self::MAX_RETRIES) {
                $delayMs = self::BASE_DELAY_MS * (2 ** ($attempt - 1));
                usleep($delayMs * 1000);
            }
        }

        throw new RuntimeException(
            'Runner unavailable after '.self::MAX_RETRIES.' attempts: '.$lastException?->getMessage(),
            previous: $lastException,
        );
    }

    private function ensureAvailable(): void
    {
        if (! $this->circuitBreaker->isAvailable()) {
            throw new RuntimeException(
                'Runner service circuit breaker is open. Service temporarily unavailable.'
            );
        }
    }

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

        return rtrim((string) config('runners.base_url', 'http://localhost:8000'), '/');
    }

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
