<?php

namespace App\Jobs\Tenant\ThirdParty\RingCentral;

use App\Services\Tenant\ThirdParty\RingCentral\RingCentralCallInsightsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GenerateRingCentralCallInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public int $timeout = 240;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly string $transcriptionId,
    ) {
        $this->onQueue((string) config('services.ringcentral.queues.ai', 'ringcentral-ai'));
    }

    public function handle(RingCentralCallInsightsService $insightsService): void
    {
        $insightsService->generateByTranscriptionId($this->transcriptionId);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120, 300, 600];
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('ringcentral:transcription-insights:'.$this->transcriptionId))
                ->releaseAfter(60)
                ->expireAfter(420),
        ];
    }

    public function retryUntil(): Carbon
    {
        return now()->addHours(3);
    }
}
