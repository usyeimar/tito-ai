<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant\Agent;

use App\Jobs\Tenant\Agent\SyncTrunkToRedisJob;
use App\Models\Tenant\Agent\Trunk;
use App\Services\Tenant\Agent\Runner\TrunkRedisSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sync all trunks to Redis for SIP bridge resolution.
 *
 * Usage:
 *   php artisan tenant:trunks:sync-to-redis
 *   php artisan tenant:trunks:sync-to-redis --trunk=trunk-id
 *   php artisan tenant:trunks:sync-to-redis --chunk=50
 */
final class SyncTrunksToRedisCommand extends Command
{
    protected $signature = 'tenant:trunks:sync-to-redis
                            {--trunk= : Sync a specific trunk by ID}
                            {--chunk=100 : Number of trunks to process per chunk}
                            {--queue= : Queue to dispatch jobs to (default: trunk-sync)}
                            {--sync : Run synchronously instead of dispatching jobs}';

    protected $description = 'Sync all trunks to Redis for SIP bridge resolution';

    public function handle(): int
    {
        $trunkId = $this->option('trunk');
        $chunkSize = (int) $this->option('chunk');
        $queue = $this->option('queue') ?? 'trunk-sync';
        $sync = $this->option('sync');

        if ($trunkId) {
            return $this->syncSingleTrunk($trunkId, $sync, $queue);
        }

        return $this->syncAllTrunks($chunkSize, $sync, $queue);
    }

    private function syncSingleTrunk(string $trunkId, bool $sync, string $queue): int
    {
        $this->info("Syncing trunk: {$trunkId}");

        $trunk = Trunk::find($trunkId);

        if (! $trunk) {
            $this->error("Trunk not found: {$trunkId}");

            return self::FAILURE;
        }

        if ($sync) {
            return $this->syncSynchronously($trunk);
        }

        SyncTrunkToRedisJob::dispatch($trunkId)->onQueue($queue);
        $this->info("Dispatched sync job for trunk: {$trunk->name} ({$trunkId})");

        return self::SUCCESS;
    }

    private function syncAllTrunks(int $chunkSize, bool $sync, string $queue): int
    {
        $count = Trunk::count();

        if ($count === 0) {
            $this->warn('No trunks found to sync.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} trunks to sync.");

        if ($sync) {
            $this->info('Running synchronously...');
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            Trunk::chunkById($chunkSize, function ($trunks) use ($bar): void {
                foreach ($trunks as $trunk) {
                    try {
                        app(TrunkRedisSyncService::class)->sync($trunk);
                        $bar->advance();
                    } catch (Throwable $e) {
                        $bar->clear();
                        $this->error("Failed to sync trunk {$trunk->id}: {$e->getMessage()}");
                        $bar->display();
                    }
                }
            });

            $bar->finish();
            $this->newLine();
            $this->info('All trunks synced successfully!');

            return self::SUCCESS;
        }

        $dispatched = 0;
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Trunk::select('id')->chunkById($chunkSize, function ($trunks) use ($queue, &$dispatched, $bar): void {
            foreach ($trunks as $trunk) {
                SyncTrunkToRedisJob::dispatch((string) $trunk->id)->onQueue($queue);
                $dispatched++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$dispatched} sync jobs to queue: {$queue}");

        return self::SUCCESS;
    }

    private function syncSynchronously(Trunk $trunk): int
    {
        try {
            app(TrunkRedisSyncService::class)->sync($trunk);
            $this->info("Successfully synced trunk: {$trunk->name} ({$trunk->id})");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Failed to sync trunk {$trunk->id}: {$e->getMessage()}");
            Log::error('Failed to sync trunk to Redis via command', [
                'trunk_id' => $trunk->id,
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
