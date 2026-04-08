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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class SendDocumensoSigningRequestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $signingRequestId,
    ) {}

    public function handle(
        DocumensoApiClient $client,
        DocumensoConfigurationManager $configurationManager,
        SigningRequestService $signingRequestService,
    ): void {
        $request = SigningRequest::query()->with('recipients')->find($this->signingRequestId);

        if (! $request) {
            return;
        }

        $config = $configurationManager->requireActiveData();
        $fileContent = Storage::disk($request->source_disk)->get($request->source_path);

        try {
            if (in_array($request->status, [
                SigningRequest::STATUS_SENT,
                SigningRequest::STATUS_OPENED,
                SigningRequest::STATUS_PARTIALLY_COMPLETED,
                SigningRequest::STATUS_COMPLETED,
                SigningRequest::STATUS_REJECTED,
                SigningRequest::STATUS_CANCELLED,
            ], true)) {
                return;
            }

            if ($request->provider_envelope_id === null) {
                $createResponse = $client->createEnvelope(
                    config: $config,
                    fileName: $request->source_file_name ?? basename($request->source_path),
                    fileContent: $fileContent,
                    payload: [
                        'type' => 'DOCUMENT',
                        'title' => $request->title,
                        'externalId' => $request->external_id,
                        'recipients' => $request->recipients->map(function ($recipient) use ($request): array {
                            return [
                                'email' => $recipient->email,
                                'name' => $recipient->name,
                                'role' => $recipient->role,
                                'signingOrder' => (int) $recipient->routing_order,
                                'fields' => $this->buildRecipientFields($request->meta ?? [], $recipient->email),
                            ];
                        })->values()->all(),
                        'meta' => [
                            'subject' => $request->meta['subject'] ?? null,
                            'message' => $request->meta['message'] ?? null,
                            'redirectUrl' => $request->meta['redirect_url'] ?? null,
                        ],
                    ],
                );

                $request = $signingRequestService->markProviderDraft(
                    request: $request,
                    envelopeId: (string) ($createResponse['id'] ?? ''),
                    payload: $createResponse,
                );

                $signingRequestService->recordEvent(
                    request: $request,
                    eventType: 'documenso.envelope_created',
                    source: 'documenso_api',
                    payload: $createResponse,
                );
            }

            $distributeResponse = $client->distributeEnvelope(
                config: $config,
                envelopeId: (string) $request->provider_envelope_id,
            );

            $request = $signingRequestService->markSent($request, $distributeResponse);
            $signingRequestService->recordEvent(
                request: $request,
                eventType: 'documenso.envelope_distributed',
                source: 'documenso_api',
                payload: $distributeResponse,
            );
        } catch (\Throwable $exception) {
            $signingRequestService->markFailed($request, $exception->getMessage());
            $signingRequestService->recordEvent(
                request: $request,
                eventType: 'documenso.send_failed',
                source: 'documenso_api',
                payload: ['error' => $exception->getMessage()],
            );

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<int, array<string, int|string|float>>
     */
    private function buildRecipientFields(array $meta, string $recipientEmail): array
    {
        $fieldDefinitions = collect($meta['documenso_fields'] ?? [])
            ->filter(fn (mixed $field): bool => is_array($field))
            ->filter(function (array $field) use ($recipientEmail): bool {
                return strcasecmp((string) Arr::get($field, 'recipient_email', ''), $recipientEmail) === 0;
            })
            ->values();

        return $fieldDefinitions
            ->map(fn (array $field): array => $this->normalizeFieldDefinition($field))
            ->filter(fn (array $field): bool => $field !== [])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, int|string|float>
     */
    private function normalizeFieldDefinition(array $field): array
    {
        $type = trim((string) Arr::get($field, 'type', ''));

        if ($type === '') {
            return [];
        }

        $normalized = [
            'type' => $type,
            'identifier' => is_numeric(Arr::get($field, 'identifier')) ? (int) Arr::get($field, 'identifier') : (string) Arr::get($field, 'identifier', 0),
            'page' => (int) Arr::get($field, 'page', 1),
            'positionX' => (float) Arr::get($field, 'positionX', 0),
            'positionY' => (float) Arr::get($field, 'positionY', 0),
            'width' => (float) Arr::get($field, 'width', 0),
            'height' => (float) Arr::get($field, 'height', 0),
        ];

        if (($label = Arr::get($field, 'label')) !== null) {
            $normalized['fieldMeta'] = [
                'label' => (string) $label,
            ];
        }

        return $normalized;
    }
}
