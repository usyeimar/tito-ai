<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Trunk;
use App\Services\Tenant\Agent\Runner\TrunkRedisSyncService;
use Illuminate\Support\Facades\Log;

final class DeleteTrunk
{
    public function __invoke(Trunk $trunk): void
    {
        $trunkId = (string) $trunk->id;

        // Remove from Redis first
        try {
            $syncService = app(TrunkRedisSyncService::class);
            $syncService->remove($trunk);
        } catch (\Throwable $e) {
            Log::warning('Failed to remove trunk from Redis during deletion', [
                'trunk_id' => $trunkId,
                'error' => $e->getMessage(),
            ]);
        }

        $trunk->delete();
    }
}
