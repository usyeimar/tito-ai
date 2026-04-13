<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\Agent;

use App\Models\Tenant\Agent\Trunk;
use App\Services\Tenant\Agent\Runner\TrunkRedisSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job to sync a trunk's configuration to Redis.
 *
 * This job is dispatched when a trunk is created or updated,
 * ensuring the SIP bridge can resolve the trunk config quickly
 * when calls arrive from Asterisk.
 */
final class SyncTrunkToRedisJob implements ShouldQueue
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
        private readonly string $trunkId,
    ) {
        $this->onQueue('trunk-sync');
    }

    /**
     * Execute the job.
     */
    public function handle(TrunkRedisSyncService $syncService): void
    {
        $trunk = Trunk::find($this->trunkId);

        if (! $trunk) {
            Log::warning('Trunk not found for Redis sync', [
                'trunk_id' => $this->trunkId,
            ]);

            return;
        }

        $syncService->sync($trunk);

        Log::info('Trunk synced to Redis via job', [
            'trunk_id' => $this->trunkId,
            'name' => $trunk->name,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Failed to sync trunk to Redis', [
            'trunk_id' => $this->trunkId,
            'error' => $exception->getMessage(),
        ]);
    }
}
