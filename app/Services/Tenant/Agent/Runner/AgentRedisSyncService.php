<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use App\Models\Tenant\Agent\Agent;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Service for syncing agent configurations to Redis for the runners service.
 *
 * This allows the SIP bridge (Python/FastAPI) to quickly resolve agent configs
 * when calls arrive from Asterisk, without needing to query the Laravel backend.
 */
final class AgentRedisSyncService
{
    private const AGENT_CONFIG_KEY_PREFIX = 'agent_config:';

    private const AGENT_INDEX_KEY = 'agent:index';

    private const DEFAULT_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private readonly AgentConfigBuilder $configBuilder,
    ) {}

    /**
     * Sync an agent's configuration to Redis.
     *
     * @throws Throwable
     */
    public function sync(Agent $agent): void
    {
        $config = $this->configBuilder->build($agent);
        $agentId = (string) $agent->id;
        $tenantId = (string) (tenant('id') ?? 'central');

        $payload = [
            'agent_id' => $agentId,
            'tenant_id' => $tenantId,
            'slug' => $agent->slug,
            'config' => $config,
            'synced_at' => now()->toIso8601String(),
            'version' => $config['version'] ?? '1.0.0',
        ];

        try {
            $redis = $this->redis();

            // Store agent config with TTL
            $redis->setex(
                self::AGENT_CONFIG_KEY_PREFIX.$agentId,
                self::DEFAULT_TTL_SECONDS,
                json_encode($payload)
            );

            // Add to tenant-agent index for lookups
            $redis->sadd("agent:index:{$tenantId}", $agentId);

            // Add slug mapping for slug-based lookups
            $redis->hset("agent:slugs:{$tenantId}", $agent->slug, $agentId);

            Log::info('Agent synced to Redis', [
                'agent_id' => $agentId,
                'tenant_id' => $tenantId,
                'slug' => $agent->slug,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to sync agent to Redis', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Remove an agent's configuration from Redis.
     */
    public function remove(Agent $agent): void
    {
        $agentId = (string) $agent->id;
        $tenantId = (string) (tenant('id') ?? 'central');

        try {
            $redis = $this->redis();

            $redis->del(self::AGENT_CONFIG_KEY_PREFIX.$agentId);
            $redis->srem("agent:index:{$tenantId}", $agentId);
            $redis->hdel("agent:slugs:{$tenantId}", $agent->slug);

            Log::info('Agent removed from Redis', [
                'agent_id' => $agentId,
                'tenant_id' => $tenantId,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to remove agent from Redis', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Find an agent ID by slug for a tenant.
     */
    public function findAgentIdBySlug(string $tenantId, string $slug): ?string
    {
        try {
            $agentId = $this->redis()->hget("agent:slugs:{$tenantId}", $slug);

            return $agentId ?: null;
        } catch (Throwable $e) {
            Log::warning('Failed to lookup agent by slug in Redis', [
                'tenant_id' => $tenantId,
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if an agent is synced to Redis.
     */
    public function isSynced(string $agentId): bool
    {
        try {
            return (bool) $this->redis()->exists(self::AGENT_CONFIG_KEY_PREFIX.$agentId);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Refresh TTL for an agent config to prevent expiration.
     */
    public function touch(string $agentId): void
    {
        try {
            $redis = $this->redis();
            $key = self::AGENT_CONFIG_KEY_PREFIX.$agentId;

            if ($redis->exists($key)) {
                $redis->expire($key, self::DEFAULT_TTL_SECONDS);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to touch agent in Redis', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all synced agent IDs for a tenant.
     *
     * @return array<string>
     */
    public function getSyncedAgentIds(string $tenantId): array
    {
        try {
            return $this->redis()->smembers("agent:index:{$tenantId}");
        } catch (Throwable $e) {
            Log::warning('Failed to get synced agents from Redis', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function redis(): Connection
    {
        return Redis::connection(config('database.redis.default', 'default'));
    }
}
