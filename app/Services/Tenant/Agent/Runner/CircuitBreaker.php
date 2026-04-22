<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use Illuminate\Support\Facades\Cache;

/**
 * Simple circuit breaker to prevent cascading failures when runners are down.
 *
 * States: closed (normal) → open (failing) → half-open (testing recovery).
 */
final class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';

    private const STATE_OPEN = 'open';

    private const STATE_HALF_OPEN = 'half-open';

    public function __construct(
        private readonly string $service = 'runner',
        private readonly int $failureThreshold = 5,
        private readonly int $recoveryTimeoutSeconds = 30,
        private readonly int $successThreshold = 2,
    ) {}

    public function isAvailable(): bool
    {
        $state = $this->getState();

        if ($state === self::STATE_CLOSED) {
            return true;
        }

        if ($state === self::STATE_OPEN) {
            $openedAt = (int) Cache::get($this->key('opened_at'), 0);

            if ((time() - $openedAt) >= $this->recoveryTimeoutSeconds) {
                $this->setState(self::STATE_HALF_OPEN);

                return true;
            }

            return false;
        }

        // half-open: allow one request through
        return true;
    }

    public function recordSuccess(): void
    {
        $state = $this->getState();

        if ($state === self::STATE_HALF_OPEN) {
            $successes = (int) Cache::increment($this->key('successes'));

            if ($successes >= $this->successThreshold) {
                $this->reset();
            }

            return;
        }

        // In closed state, reset failure count
        Cache::forget($this->key('failures'));
    }

    public function recordFailure(): void
    {
        $state = $this->getState();

        if ($state === self::STATE_HALF_OPEN) {
            $this->trip();

            return;
        }

        $failures = (int) Cache::increment($this->key('failures'));

        if ($failures >= $this->failureThreshold) {
            $this->trip();
        }
    }

    public function getState(): string
    {
        return (string) Cache::get($this->key('state'), self::STATE_CLOSED);
    }

    public function getFailureCount(): int
    {
        return (int) Cache::get($this->key('failures'), 0);
    }

    private function trip(): void
    {
        $this->setState(self::STATE_OPEN);
        Cache::put($this->key('opened_at'), time(), 3600);
        Cache::forget($this->key('successes'));
    }

    private function reset(): void
    {
        $this->setState(self::STATE_CLOSED);
        Cache::forget($this->key('failures'));
        Cache::forget($this->key('successes'));
        Cache::forget($this->key('opened_at'));
    }

    private function setState(string $state): void
    {
        Cache::put($this->key('state'), $state, 3600);
    }

    private function key(string $suffix): string
    {
        return "circuit_breaker:{$this->service}:{$suffix}";
    }
}
