<?php

namespace App\Jobs\Tenant\ThirdParty\RingCentral;

use App\Services\Tenant\ThirdParty\RingCentral\RingCentralCallTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class TranscribeRingCentralCallRecordingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 300;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly string $recordingId,
    ) {
        $this->onQueue((string) config('services.ringcentral.queues.ai', 'ringcentral-ai'));
    }

    public function handle(RingCentralCallTranscriptionService $transcriptionService): void
    {
        $transcriptionService->transcribeByRecordingId($this->recordingId);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 90, 180, 360, 720];
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('ringcentral:recording-transcription:'.$this->recordingId))
                ->releaseAfter(45)
                ->expireAfter(420),
        ];
    }

    public function retryUntil(): Carbon
    {
        return now()->addHours(4);
    }
}
