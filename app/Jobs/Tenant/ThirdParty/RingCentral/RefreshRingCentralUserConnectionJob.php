<?php

namespace App\Jobs\Tenant\ThirdParty\RingCentral;

use App\Models\Tenant\ThirdParty\RingCentral\RingCentralUserConnection;
use App\Services\Tenant\ThirdParty\RingCentral\RingCentralTokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RefreshRingCentralUserConnectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public int $timeout = 120;

    public int $maxExceptions = 2;

    public function __construct(
        public readonly string $connectionId,
    ) {
        $this->onQueue((string) config('services.ringcentral.queues.maintenance', 'ringcentral-maintenance'));
    }

    public function handle(RingCentralTokenService $tokenService): void
    {
        $connection = RingCentralUserConnection::query()->find($this->connectionId);

        if (! $connection || $connection->unlinked_at !== null) {
            return;
        }

        try {
            $tokenService->refreshIfNeeded($connection);
        } catch (\Throwable $exception) {
            $connection->forceFill([
                'last_error' => $exception->getMessage(),
            ])->save();
        }
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [15, 60, 180, 420];
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('ringcentral:connection-refresh:'.$this->connectionId))
                ->releaseAfter(20)
                ->expireAfter(180),
        ];
    }

    public function retryUntil(): Carbon
    {
        return now()->addHours(2);
    }
}
