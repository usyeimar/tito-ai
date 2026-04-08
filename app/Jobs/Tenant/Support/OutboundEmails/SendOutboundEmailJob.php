<?php

namespace App\Jobs\Tenant\Support\OutboundEmails;

use App\Models\Central\Tenancy\Tenant;
use App\Services\Tenant\Support\OutboundEmails\OutboundEmailSendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOutboundEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $outboundEmailId,
        public readonly string $tenantId,
    ) {}

    public function handle(OutboundEmailSendService $sendService): void
    {
        $tenant = Tenant::query()->find($this->tenantId);

        if (! $tenant) {
            return;
        }

        $tenant->run(function () use ($sendService): void {
            $sendService->sendQueued($this->outboundEmailId);
        });
    }
}
