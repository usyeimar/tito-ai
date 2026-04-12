<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use App\Services\Tenant\Agent\Runner\AgentRedisSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job to sync an agent's configuration to Redis.
 *
 * This job is dispatched when an agent is created or updated,
 * ensuring the SIP bridge can resolve the agent config quickly
 * when calls arrive from Asterisk.
 */
final class SyncAgentToRedisJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int>
     */
    public array $backoff = [1, 5, 10];

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $agentId,
    ) {
        $this->onQueue('agent-sync');
    }

    /**
     * Execute the job.
     */
    public function handle(AgentRedisSyncService $syncService): void
    {
        $agent = Agent::with(['settings', 'tools'])->find($this->agentId);

        if (! $agent) {
            Log::warning('Agent not found for Redis sync', [
                'agent_id' => $this->agentId,
            ]);

            return;
        }

        $syncService->sync($agent);

        Log::info('Agent synced to Redis via job', [
            'agent_id' => $this->agentId,
            'slug' => $agent->slug,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Failed to sync agent to Redis', [
            'agent_id' => $this->agentId,
            'error' => $exception->getMessage(),
        ]);
    }
}
