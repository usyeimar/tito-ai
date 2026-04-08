<?php

namespace App\Jobs\Tenant\ThirdParty\RingCentral;

use App\Services\Tenant\ThirdParty\RingCentral\RingCentralWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessRingCentralWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly string $deliveryId,
    ) {
        $this->onQueue((string) config('services.ringcentral.queues.webhooks', 'ringcentral-webhooks'));
    }

    public function handle(RingCentralWebhookService $webhookService): void
    {
        $webhookService->processById($this->deliveryId);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 30, 90, 180, 300];
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('ringcentral:webhook-process:'.$this->deliveryId))
                ->releaseAfter(15)
                ->expireAfter(180),
        ];
    }

    public function retryUntil(): Carbon
    {
        return now()->addMinutes(20);
    }
}
