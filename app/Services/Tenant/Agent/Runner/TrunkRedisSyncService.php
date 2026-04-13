<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use App\Models\Tenant\Agent\Trunk;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Service for syncing trunk configurations to Redis for the runners service.
 *
 * This allows the SIP bridge (Python/FastAPI) to quickly resolve trunk configs
 * when calls arrive from Asterisk, without needing to query the Laravel backend.
 */
final class TrunkRedisSyncService
{
    private const TRUNK_KEY_PREFIX = 'trunk:';

    private const DEFAULT_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private readonly ?string $runnerAripEndpoint = null,
        private readonly ?string $runnerApiHost = null,
        private readonly ?int $runnerApiPort = null,
    ) {}

    /**
     * Sync a trunk's configuration to Redis.
     *
     * @throws Throwable
     */
    public function sync(Trunk $trunk): void
    {
        $trunkId = (string) $trunk->id;
        $workspaceSlug = $trunk->workspace_slug;

        $payload = $this->buildPayload($trunk);

        try {
            $redis = $this->redis();

            // Store trunk config with TTL
            $redis->setex(
                self::TRUNK_KEY_PREFIX.$trunkId,
                self::DEFAULT_TTL_SECONDS,
                json_encode($payload)
            );

            // Add to workspace index for lookups
            $redis->sadd("trunk:index:{$workspaceSlug}", $trunkId);

            Log::info('Trunk synced to Redis', [
                'trunk_id' => $trunkId,
                'workspace_slug' => $workspaceSlug,
                'name' => $trunk->name,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to sync trunk to Redis', [
                'trunk_id' => $trunkId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Remove a trunk's configuration from Redis.
     */
    public function remove(Trunk $trunk): void
    {
        $trunkId = (string) $trunk->id;
        $workspaceSlug = $trunk->workspace_slug;

        try {
            $redis = $this->redis();

            $redis->del(self::TRUNK_KEY_PREFIX.$trunkId);
            $redis->srem("trunk:index:{$workspaceSlug}", $trunkId);

            Log::info('Trunk removed from Redis', [
                'trunk_id' => $trunkId,
                'workspace_slug' => $workspaceSlug,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to remove trunk from Redis', [
                'trunk_id' => $trunkId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if a trunk is synced to Redis.
     */
    public function isSynced(string $trunkId): bool
    {
        try {
            return (bool) $this->redis()->exists(self::TRUNK_KEY_PREFIX.$trunkId);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Refresh TTL for a trunk config to prevent expiration.
     */
    public function touch(string $trunkId): void
    {
        try {
            $redis = $this->redis();
            $key = self::TRUNK_KEY_PREFIX.$trunkId;

            if ($redis->exists($key)) {
                $redis->expire($key, self::DEFAULT_TTL_SECONDS);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to touch trunk in Redis', [
                'trunk_id' => $trunkId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all synced trunk IDs for a workspace.
     *
     * @return array<string>
     */
    public function getSyncedTrunkIds(string $workspaceSlug): array
    {
        try {
            return $this->redis()->smembers("trunk:index:{$workspaceSlug}");
        } catch (Throwable $e) {
            Log::warning('Failed to get synced trunks from Redis', [
                'workspace_slug' => $workspaceSlug,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Build the Redis payload from a Trunk model.
     */
    private function buildPayload(Trunk $trunk): array
    {
        $payload = [
            'trunk_id' => (string) $trunk->id,
            'name' => $trunk->name,
            'tenant_id' => $trunk->workspace_slug, // For compatibility with runner
            'workspace_slug' => $trunk->workspace_slug,
            'mode' => $trunk->mode,
            'max_concurrent_calls' => $trunk->max_concurrent_calls,
            'codecs' => $trunk->codecs ?? ['ulaw', 'alaw'],
            'status' => $trunk->status,
            'inbound_auth' => $trunk->inbound_auth,
            'routes' => $trunk->routes ?? [],
            'sip_host' => $trunk->sip_host,
            'sip_port' => $trunk->sip_port,
            'created_at' => $trunk->created_at?->timestamp ?? time(),
            'updated_at' => $trunk->updated_at?->timestamp ?? time(),
        ];

        // Add runner-specific configuration
        if ($this->runnerAripEndpoint) {
            $payload['ari_endpoint'] = $this->runnerAripEndpoint;
        }

        if ($this->runnerApiHost) {
            $payload['api_host'] = $this->runnerApiHost;
        }

        if ($this->runnerApiPort) {
            $payload['api_port'] = $this->runnerApiPort;
        }

        // For inbound trunks, add default ARI app config if not set
        if ($trunk->mode === Trunk::MODE_INBOUND) {
            $payload['app_name'] = $payload['app_name'] ?? 'tito-ai';
            $payload['app_password'] = $payload['app_password'] ?? config('services.asterisk.ari_password', 'tito-ari-secret');
        }

        return $payload;
    }

    private function redis(): Connection
    {
        return Redis::connection(config('database.redis.default', 'default'));
    }
}
