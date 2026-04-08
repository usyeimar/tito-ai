<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Thread;

use App\Jobs\Voice\ProcessThreadJob;
use App\Models\Assistant\Thread;
use App\Services\Tenant\Assistant\AssistantService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ThreadService
{
    public function __construct(
        private readonly AssistantService $assistantService,
    ) {}

    public function getByUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Thread::query()
            ->byUser($userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findByExternalId(string $externalId, ?int $userId = null): ?Thread
    {
        $query = Thread::where('external_thread_id', $externalId);

        if ($userId !== null) {
            $query->byUser($userId);
        }

        return $query->first();
    }

    public function create(
        int $userId,
        int $tenantId,
        int $assistantId,
        array $config = []
    ): Thread {
        return DB::transaction(function () use ($userId, $tenantId, $assistantId, $config) {
            $assistant = $this->assistantService->findById($assistantId, $tenantId);

            if (! $assistant) {
                throw new \InvalidArgumentException('Assistant not found');
            }

            $defaultConfig = $this->assistantService->getDefaultConfig($assistant);
            $mergedConfig = array_merge($defaultConfig, $config);

            $thread = Thread::create([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'assistant_id' => $assistantId,
                'external_thread_id' => Str::uuid()->toString(),
                'status' => 'pending',
                'config' => $mergedConfig,
                'started_at' => now(),
            ]);

            ProcessThreadJob::dispatch(
                $thread->external_thread_id,
                (string) $tenantId,
                $thread->config
            );

            return $thread;
        });
    }

    public function complete(Thread $thread): Thread
    {
        if (! $thread->isActive()) {
            throw new \InvalidArgumentException('Thread is not active');
        }

        $thread->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return $thread->fresh();
    }

    public function fail(Thread $thread, string $reason): Thread
    {
        $thread->update([
            'status' => 'failed',
            'ended_at' => now(),
            'metadata' => array_merge($thread->metadata ?? [], ['failure_reason' => $reason]),
        ]);

        return $thread->fresh();
    }

    public function delete(Thread $thread): void
    {
        $thread->delete();
    }

    public function getActiveThreads(int $userId): Collection
    {
        return Thread::query()
            ->byUser($userId)
            ->active()
            ->orderByDesc('created_at')
            ->get();
    }
}
