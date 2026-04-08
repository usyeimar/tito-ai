<?php

declare(strict_types=1);

namespace App\Jobs\Voice;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class ProcessThreadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public string $threadId,
        public string $tenantId,
        public array $config = [],
    ) {}

    public function handle(): void
    {
        $jobData = [
            'thread_id' => $this->threadId,
            'tenant_id' => $this->tenantId,
            'config' => $this->config,
            'enqueued_at' => now()->toIso8601String(),
        ];

        Redis::connection('queue')->rpush(
            'arq:default',
            json_encode([
                'task' => 'process_thread',
                'args' => [$this->threadId, $this->config['audio_data'] ?? null],
                'kwargs' => [],
                'job_id' => $this->job?->uuid() ?? uniqid('thread_'),
                'enqueue_time_ms' => (int) (microtime(true) * 1000),
            ])
        );

        Redis::connection()->setex(
            "thread:{$this->threadId}",
            3600,
            json_encode($jobData)
        );
    }

    public function failed(\Throwable $exception): void
    {
        Redis::connection()->setex(
            "thread:{$this->threadId}:error",
            3600,
            json_encode([
                'error' => $exception->getMessage(),
                'failed_at' => now()->toIso8601String(),
            ])
        );
    }
}
