<?php

namespace App\Jobs\Tenant\ThirdParty\Documenso;

use App\Models\Tenant\Signing\SigningRequest;
use App\Models\Tenant\Signing\SigningRequestRecipient;
use App\Services\Tenant\Signing\SigningRequestService;
use App\Services\Tenant\ThirdParty\Documenso\DocumensoApiClient;
use App\Services\Tenant\ThirdParty\Documenso\DocumensoConfigurationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class RefreshDocumensoSigningRequestJob implements ShouldQueue
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

        if (! $request || $request->provider_envelope_id === null) {
            return;
        }

        $config = $configurationManager->requireActiveData();
        $envelope = $client->getEnvelope($config, $request->provider_envelope_id);
        $eventName = $this->mapEventName($envelope, $request);
        $providerEventId = implode(':', array_filter([
            'refresh',
            $envelope['id'] ?? null,
            $envelope['status'] ?? null,
            Arr::get($envelope, 'updatedAt'),
        ]));

        if ($eventName === 'DOCUMENT_DRAFT') {
            $request = $signingRequestService->markProviderDraft(
                request: $request,
                envelopeId: (string) ($envelope['id'] ?? $request->provider_envelope_id),
                payload: $envelope,
            );

            $signingRequestService->recordEvent(
                request: $request,
                eventType: 'document_draft',
                source: 'documenso_refresh',
                payload: $envelope,
                providerEventId: $providerEventId,
            );

            return;
        }

        $request = $signingRequestService->applyWebhook(
            request: $request,
            eventName: $eventName,
            payload: $envelope,
            providerEventId: $providerEventId,
            source: 'documenso_refresh',
        );

        if ($request->status === SigningRequest::STATUS_COMPLETED && $request->completed_path === null && $request->provider_document_id !== null) {
            DownloadCompletedDocumensoPdfJob::dispatch((string) $request->getKey());
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapEventName(array $payload, SigningRequest $request): string
    {
        return match (strtoupper((string) ($payload['status'] ?? ''))) {
            'DRAFT' => 'DOCUMENT_DRAFT',
            'COMPLETED' => 'DOCUMENT_COMPLETED',
            'REJECTED' => 'DOCUMENT_REJECTED',
            'CANCELLED' => 'DOCUMENT_CANCELLED',
            default => $this->pendingEventName($payload, $request),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function pendingEventName(array $payload, SigningRequest $request): string
    {
        $recipients = collect((array) ($payload['recipients'] ?? []));
        $hasRejectedRecipient = $recipients->contains(fn (mixed $recipient): bool => is_array($recipient) && Arr::get($recipient, 'signingStatus') === 'REJECTED');

        if ($hasRejectedRecipient) {
            return 'DOCUMENT_REJECTED';
        }

        $completedRecipientCount = $request->recipients->where('status', SigningRequestRecipient::STATUS_COMPLETED)->count();
        $hasSignedRecipient = $recipients->contains(fn (mixed $recipient): bool => is_array($recipient) && Arr::get($recipient, 'signingStatus') === 'SIGNED');

        if ($hasSignedRecipient || $completedRecipientCount > 0) {
            return 'DOCUMENT_RECIPIENT_COMPLETED';
        }

        $hasOpenedRecipient = $recipients->contains(fn (mixed $recipient): bool => is_array($recipient) && Arr::get($recipient, 'readStatus') === 'OPENED');

        return $hasOpenedRecipient ? 'DOCUMENT_OPENED' : 'DOCUMENT_SENT';
    }
}
