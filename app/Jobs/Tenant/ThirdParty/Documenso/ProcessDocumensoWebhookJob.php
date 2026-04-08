<?php

namespace App\Jobs\Tenant\ThirdParty\Documenso;

use App\Services\Tenant\ThirdParty\Documenso\DocumensoWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessDocumensoWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(
        public string $deliveryId,
    ) {}

    public function handle(DocumensoWebhookService $webhookService): void
    {
        $webhookService->processById($this->deliveryId);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('documenso-webhook:'.$this->deliveryId))->expireAfter(120),
        ];
    }
}
