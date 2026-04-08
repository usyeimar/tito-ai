<?php

namespace App\Jobs\Tenant\ThirdParty\RingCentral;

use App\Services\Tenant\ThirdParty\RingCentral\RingCentralVoicemailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DownloadRingCentralVoicemailMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    public int $timeout = 180;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly string $voicemailId,
    ) {
        $this->onQueue((string) config('services.ringcentral.queues.media', 'ringcentral-media'));
    }

    public function handle(RingCentralVoicemailService $voicemailService): void
    {
        $voicemailService->downloadMediaById($this->voicemailId);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [15, 45, 120, 240, 420, 600];
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('ringcentral:voicemail-download:'.$this->voicemailId))
                ->releaseAfter(20)
                ->expireAfter(240),
        ];
    }

    public function retryUntil(): Carbon
    {
        return now()->addHours(2);
    }
}
