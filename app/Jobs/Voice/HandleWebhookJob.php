<?php

declare(strict_types=1);

namespace App\Jobs\Voice;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class HandleWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $webhookType,
        public array $payload,
    ) {}

    public function handle(): void
    {
        Redis::connection('queue')->rpush(
            'arq:default',
            json_encode([
                'task' => 'handle_webhook',
                'args' => [],
                'kwargs' => [
                    'webhook_type' => $this->webhookType,
                    'payload' => $this->payload,
                ],
                'job_id' => $this->job?->uuid() ?? uniqid('webhook_'),
                'enqueue_time_ms' => (int) (microtime(true) * 1000),
            ])
        );
    }
}
