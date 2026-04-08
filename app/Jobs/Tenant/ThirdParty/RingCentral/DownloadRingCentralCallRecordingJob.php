<?php

namespace App\Jobs\Tenant\ThirdParty\RingCentral;

use App\Services\Tenant\ThirdParty\RingCentral\RingCentralCallRecordingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DownloadRingCentralCallRecordingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    public int $timeout = 300;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly string $recordingId,
    ) {
        $this->onQueue((string) config('services.ringcentral.queues.media', 'ringcentral-media'));
    }

    public function handle(RingCentralCallRecordingService $recordingService): void
    {
        $recordingService->downloadRecordingMediaById($this->recordingId);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 60, 120, 240, 480, 900];
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('ringcentral:recording-download:'.$this->recordingId))
                ->releaseAfter(30)
                ->expireAfter(300),
        ];
    }

    public function retryUntil(): Carbon
    {
        return now()->addHours(4);
    }
}
