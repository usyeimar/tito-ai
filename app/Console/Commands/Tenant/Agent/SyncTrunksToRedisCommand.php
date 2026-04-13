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
 * This command dispatches jobs to sync all existing trunks to Redis,
 * ensuring the SIP bridge can resolve trunk configs when calls arrive from Asterisk.
 *
 * Usage:
 *   php artisan tenant:trunks:sync-to-redis
 *   php artisan tenant:trunks:sync-to-redis --trunk=trunk-id
 *   php artisan tenant:trunks:sync-to-redis --workspace=default
 *   php artisan tenant:trunks:sync-to-redis --chunk=50
 */
final class SyncTrunksToRedisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:trunks:sync-to-redis
                            {--trunk= : Sync a specific trunk by ID}
                            {--workspace= : Sync all trunks for a specific workspace}
                            {--chunk=100 : Number of trunks to process per chunk}
                            {--queue= : Queue to dispatch jobs to (default: trunk-sync)}
                            {--sync : Run synchronously instead of dispatching jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all trunks to Redis for SIP bridge resolution';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $trunkId = $this->option('trunk');
        $workspace = $this->option('workspace');
        $chunkSize = (int) $this->option('chunk');
        $queue = $this->option('queue') ?? 'trunk-sync';
        $sync = $this->option('sync');

        // Sync specific trunk by ID
        if ($trunkId) {
            return $this->syncSingleTrunk($trunkId, $sync, $queue);
        }

        // Sync all trunks for a workspace
        if ($workspace) {
            return $this->syncTrunksForWorkspace($workspace, $chunkSize, $sync, $queue);
        }

        // Sync all trunks
        return $this->syncAllTrunks($chunkSize, $sync, $queue);
    }

    /**
     * Sync a single trunk by ID.
     */
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

    /**
     * Sync all trunks for a specific workspace.
     */
    private function syncTrunksForWorkspace(string $workspace, int $chunkSize, bool $sync, string $queue): int
    {
        $this->info("Syncing trunks for workspace: {$workspace}");

        $count = Trunk::where('workspace_slug', $workspace)->count();

        if ($count === 0) {
            $this->warn("No trunks found for workspace: {$workspace}");

            return self::SUCCESS;
        }

        $this->info("Found {$count} trunks to sync.");

        if ($sync) {
            return $this->syncTrunksForWorkspaceSync($workspace, $chunkSize);
        }

        return $this->dispatchSyncJobsForWorkspace($workspace, $chunkSize, $queue);
    }

    /**
     * Sync all trunks in chunks.
     */
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
                        $syncService = app(TrunkRedisSyncService::class);
                        $syncService->sync($trunk);
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

        // Dispatch jobs asynchronously
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
        $this->info('Run `php artisan queue:work --queue='.$queue.'` to process them.');

        return self::SUCCESS;
    }

    /**
     * Sync trunks for workspace synchronously.
     */
    private function syncTrunksForWorkspaceSync(string $workspace, int $chunkSize): int
    {
        $this->info('Running synchronously...');

        $bar = $this->output->createProgressBar(Trunk::where('workspace_slug', $workspace)->count());
        $bar->start();

        Trunk::where('workspace_slug', $workspace)
            ->chunkById($chunkSize, function ($trunks) use ($bar): void {
                foreach ($trunks as $trunk) {
                    try {
                        $syncService = app(TrunkRedisSyncService::class);
                        $syncService->sync($trunk);
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

    /**
     * Dispatch sync jobs for workspace.
     */
    private function dispatchSyncJobsForWorkspace(string $workspace, int $chunkSize, string $queue): int
    {
        $dispatched = 0;
        $bar = $this->output->createProgressBar(Trunk::where('workspace_slug', $workspace)->count());
        $bar->start();

        Trunk::where('workspace_slug', $workspace)
            ->select('id')
            ->chunkById($chunkSize, function ($trunks) use ($queue, &$dispatched, $bar): void {
                foreach ($trunks as $trunk) {
                    SyncTrunkToRedisJob::dispatch((string) $trunk->id)->onQueue($queue);
                    $dispatched++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$dispatched} sync jobs to queue: {$queue}");
        $this->info('Run `php artisan queue:work --queue='.$queue.'` to process them.');

        return self::SUCCESS;
    }

    /**
     * Sync a single trunk synchronously.
     */
    private function syncSynchronously(Trunk $trunk): int
    {
        try {
            $syncService = app(TrunkRedisSyncService::class);
            $syncService->sync($trunk);
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
