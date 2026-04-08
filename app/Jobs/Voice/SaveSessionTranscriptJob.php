<?php

declare(strict_types=1);

namespace App\Jobs\Voice;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class SaveSessionTranscriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public string $sessionId,
        public array $transcript = [],
        public array $metadata = [],
    ) {}

    public function handle(): void
    {
        Redis::connection('queue')->rpush(
            'arq:default',
            json_encode([
                'task' => 'save_session_transcript',
                'args' => [$this->sessionId, $this->transcript],
                'kwargs' => ['metadata' => $this->metadata],
                'job_id' => $this->job?->uuid() ?? uniqid('transcript_'),
                'enqueue_time_ms' => (int) (microtime(true) * 1000),
            ])
        );
    }
}
