<?php

namespace App\Jobs\Tenant\ThirdParty\Documenso;

use App\Models\Tenant\Signing\SigningRequest;
use App\Services\Tenant\Signing\SigningRequestService;
use App\Services\Tenant\ThirdParty\Documenso\DocumensoApiClient;
use App\Services\Tenant\ThirdParty\Documenso\DocumensoConfigurationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelDocumensoSigningRequestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(
        public string $signingRequestId,
    ) {}

    public function handle(
        DocumensoApiClient $client,
        DocumensoConfigurationManager $configurationManager,
        SigningRequestService $signingRequestService,
    ): void {
        $request = SigningRequest::query()->with('recipients')->find($this->signingRequestId);

        if (! $request || $request->provider_envelope_id === null || $request->status === SigningRequest::STATUS_CANCELLED) {
            return;
        }

        $config = $configurationManager->requireActiveData();
        $payload = $client->deleteEnvelope($config, $request->provider_envelope_id);

        $signingRequestService->cancel(
            request: $request,
            eventType: 'documenso.envelope_cancelled',
            source: 'documenso_api',
            payload: $payload,
        );
    }
}
