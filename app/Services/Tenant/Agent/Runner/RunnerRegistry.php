<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Service for discovering and selecting available runners from Redis registry.
 *
 * Runners register themselves with a heartbeat in Redis, and this service
 * helps Laravel find the best runner for new sessions based on load.
 */
final class RunnerRegistry
{
    private const RUNNER_INDEX_KEY = 'runner:index';

    private const RUNNER_KEY_PREFIX = 'runner:';

    /**
     * Get the runner with the lowest active sessions (load balancing).
     */
    public function getAvailableRunner(): ?array
    {
        try {
            $runnerIds = $this->redis()->smembers(self::RUNNER_INDEX_KEY);

            if (empty($runnerIds)) {
                return null;
            }

            $runners = [];
            foreach ($runnerIds as $runnerId) {
                $data = $this->redis()->get(self::RUNNER_KEY_PREFIX.$runnerId);
                if ($data) {
                    $runner = json_decode($data, true);
                    if ($runner && $this->isRunnerHealthy($runner)) {
                        $runners[] = $runner;
                    }
                }
            }

            if (empty($runners)) {
                return null;
            }

            // Filter runners that are at capacity
            $available = array_filter(
                $runners,
                fn (array $r) => ($r['active_sessions'] ?? 0) < ($r['max_sessions'] ?? 10)
            );

            if (empty($available)) {
                return null;
            }

            // Sort by active sessions (ascending) and return the one with lowest load
            usort($available, fn (array $a, array $b) => ($a['active_sessions'] ?? 0) <=> ($b['active_sessions'] ?? 0)
            );

            return $available[0];
        } catch (Throwable $e) {
            Log::error('Failed to get available runner from registry', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get a specific runner by host_id.
     */
    public function getRunner(string $hostId): ?array
    {
        try {
            $data = $this->redis()->get(self::RUNNER_KEY_PREFIX.$hostId);

            if (! $data) {
                return null;
            }

            $runner = json_decode($data, true);

            return $this->isRunnerHealthy($runner) ? $runner : null;
        } catch (Throwable $e) {
            Log::error('Failed to get runner from registry', [
                'host_id' => $hostId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get all healthy runners.
     *
     * @return array<array>
     */
    public function getAllRunners(): array
    {
        try {
            $runnerIds = $this->redis()->smembers(self::RUNNER_INDEX_KEY);

            if (empty($runnerIds)) {
                return [];
            }

            $runners = [];
            foreach ($runnerIds as $runnerId) {
                $data = $this->redis()->get(self::RUNNER_KEY_PREFIX.$runnerId);
                if ($data) {
                    $runner = json_decode($data, true);
                    if ($runner && $this->isRunnerHealthy($runner)) {
                        $runners[] = $runner;
                    }
                }
            }

            return $runners;
        } catch (Throwable $e) {
            Log::error('Failed to get all runners from registry', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Remove a runner from the registry (e.g., when it shuts down).
     */
    public function removeRunner(string $hostId): void
    {
        try {
            $this->redis()->srem(self::RUNNER_INDEX_KEY, $hostId);
            $this->redis()->del(self::RUNNER_KEY_PREFIX.$hostId);

            Log::info('Runner removed from registry', ['host_id' => $hostId]);
        } catch (Throwable $e) {
            Log::error('Failed to remove runner from registry', [
                'host_id' => $hostId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if a runner is healthy (has recent heartbeat).
     */
    private function isRunnerHealthy(array $runner): bool
    {
        $lastHeartbeat = $runner['last_heartbeat'] ?? 0;
        $now = now()->timestamp;

        // Runner is healthy if heartbeat is within 60 seconds
        return ($now - $lastHeartbeat) < 60;
    }

    private function redis(): Connection
    {
        return Redis::connection(config('database.redis.default', 'default'));
    }
}
