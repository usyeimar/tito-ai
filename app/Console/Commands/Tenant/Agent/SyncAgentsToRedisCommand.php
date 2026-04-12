<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant\Agent;

use App\Jobs\Tenant\Agent\SyncAgentToRedisJob;
use App\Models\Tenant\Agent\Agent;
use App\Services\Tenant\Agent\Runner\AgentRedisSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sync all agents to Redis for SIP bridge resolution.
 *
 * This command dispatches jobs to sync all existing agents to Redis,
 * ensuring the SIP bridge can resolve agent configs when calls arrive from Asterisk.
 *
 * Usage:
 *   php artisan tenant:agents:sync-to-redis
 *   php artisan tenant:agents:sync-to-redis --agent=agent-id
 *   php artisan tenant:agents:sync-to-redis --chunk=50
 */
final class SyncAgentsToRedisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:agents:sync-to-redis
                            {--agent= : Sync a specific agent by ID}
                            {--slug= : Sync a specific agent by slug}
                            {--chunk=100 : Number of agents to process per chunk}
                            {--queue= : Queue to dispatch jobs to (default: agent-sync)}
                            {--sync : Run synchronously instead of dispatching jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all agents to Redis for SIP bridge resolution';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $agentId = $this->option('agent');
        $slug = $this->option('slug');
        $chunkSize = (int) $this->option('chunk');
        $queue = $this->option('queue') ?? 'agent-sync';
        $sync = $this->option('sync');

        // Sync specific agent by ID
        if ($agentId) {
            return $this->syncSingleAgent($agentId, $sync, $queue);
        }

        // Sync specific agent by slug
        if ($slug) {
            return $this->syncAgentBySlug($slug, $sync, $queue);
        }

        // Sync all agents
        return $this->syncAllAgents($chunkSize, $sync, $queue);
    }

    /**
     * Sync a single agent by ID.
     */
    private function syncSingleAgent(string $agentId, bool $sync, string $queue): int
    {
        $this->info("Syncing agent: {$agentId}");

        $agent = Agent::with(['settings', 'tools'])->find($agentId);

        if (! $agent) {
            $this->error("Agent not found: {$agentId}");

            return self::FAILURE;
        }

        if ($sync) {
            return $this->syncSynchronously($agent);
        }

        SyncAgentToRedisJob::dispatch($agentId)->onQueue($queue);
        $this->info("Dispatched sync job for agent: {$agent->name} ({$agentId})");

        return self::SUCCESS;
    }

    /**
     * Sync a single agent by slug.
     */
    private function syncAgentBySlug(string $slug, bool $sync, string $queue): int
    {
        $this->info("Syncing agent by slug: {$slug}");

        $agent = Agent::with(['settings', 'tools'])->where('slug', $slug)->first();

        if (! $agent) {
            $this->error("Agent not found with slug: {$slug}");

            return self::FAILURE;
        }

        if ($sync) {
            return $this->syncSynchronously($agent);
        }

        SyncAgentToRedisJob::dispatch((string) $agent->id)->onQueue($queue);
        $this->info("Dispatched sync job for agent: {$agent->name} ({$agent->id})");

        return self::SUCCESS;
    }

    /**
     * Sync all agents in chunks.
     */
    private function syncAllAgents(int $chunkSize, bool $sync, string $queue): int
    {
        $count = Agent::count();

        if ($count === 0) {
            $this->warn('No agents found to sync.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} agents to sync.");

        if ($sync) {
            $this->info('Running synchronously...');
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            Agent::with(['settings', 'tools'])
                ->chunkById($chunkSize, function ($agents) use ($bar): void {
                    foreach ($agents as $agent) {
                        try {
                            $syncService = app(AgentRedisSyncService::class);
                            $syncService->sync($agent);
                            $bar->advance();
                        } catch (Throwable $e) {
                            $bar->clear();
                            $this->error("Failed to sync agent {$agent->id}: {$e->getMessage()}");
                            $bar->display();
                        }
                    }
                });

            $bar->finish();
            $this->newLine();
            $this->info('All agents synced successfully!');

            return self::SUCCESS;
        }

        // Dispatch jobs asynchronously
        $dispatched = 0;
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Agent::select('id')->chunkById($chunkSize, function ($agents) use ($queue, &$dispatched, $bar): void {
            foreach ($agents as $agent) {
                SyncAgentToRedisJob::dispatch((string) $agent->id)->onQueue($queue);
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
     * Sync a single agent synchronously.
     */
    private function syncSynchronously(Agent $agent): int
    {
        try {
            $syncService = app(AgentRedisSyncService::class);
            $syncService->sync($agent);
            $this->info("Successfully synced agent: {$agent->name} ({$agent->id})");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Failed to sync agent {$agent->id}: {$e->getMessage()}");
            Log::error('Failed to sync agent to Redis via command', [
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
