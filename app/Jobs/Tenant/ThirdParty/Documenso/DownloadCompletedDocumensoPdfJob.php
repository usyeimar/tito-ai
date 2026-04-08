<?php

namespace App\Jobs\Tenant\ThirdParty\Documenso;

use App\Models\Tenant\Signing\SigningRequest;
use App\Services\Tenant\Commons\Files\TenantAssetPathBuilder;
use App\Services\Tenant\Signing\SigningRequestService;
use App\Services\Tenant\ThirdParty\Documenso\DocumensoApiClient;
use App\Services\Tenant\ThirdParty\Documenso\DocumensoConfigurationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DownloadCompletedDocumensoPdfJob implements ShouldQueue
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
        TenantAssetPathBuilder $assetPathBuilder,
    ): void {
        $request = SigningRequest::query()->find($this->signingRequestId);

        if (! $request || $request->provider_document_id === null) {
            return;
        }

        $config = $configurationManager->requireData();
        $content = $client->downloadCompletedDocument($config, $request->provider_document_id);

        $disk = $assetPathBuilder->defaultDisk();
        $fileName = sprintf('%s-signed.pdf', pathinfo($request->source_file_name ?? 'document.pdf', PATHINFO_FILENAME));
        $path = $assetPathBuilder->buildPath(
            module: 'signing-requests',
            ids: $request->id,
            type: 'completed',
            file: $fileName,
        );

        Storage::disk($disk)->put($path, $content);

        $signingRequestService->storeCompletedArtifact(
            request: $request,
            disk: $disk,
            path: $path,
            fileName: $fileName,
            checksum: hash('sha256', $content),
        );

        $signingRequestService->recordEvent(
            request: $request->fresh(),
            eventType: 'documenso.completed_pdf_downloaded',
            source: 'documenso_api',
            payload: [
                'disk' => $disk,
                'path' => $path,
            ],
        );
    }
}
