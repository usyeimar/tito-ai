<?php

namespace App\Services\Tenant\Commons\Files\Uploads;

use App\Enums\FileUploadSessionStatus;
use App\Models\Tenant\Commons\Files\FileUploadSession;
use Illuminate\Support\Facades\Log;

class FileUploadCleanupService
{
    private const array NON_TERMINAL_STATUSES = [
        FileUploadSessionStatus::INITIATED->value,
        FileUploadSessionStatus::UPLOADING->value,
        FileUploadSessionStatus::COMPLETING->value,
    ];

    public function __construct(
        private readonly MultipartGateway $multipartGateway,
        private readonly FileUploadIdempotencyService $idempotencyService,
    ) {}

    /**
     * @return array{expired_sessions:int,expired_idempotency_keys:int}
     */
    public function cleanupExpiredSessions(int $chunkSize = 200): array
    {
        $expiredSessions = $this->expireSessions($chunkSize);
        $expiredKeys = $this->idempotencyService->cleanupExpiredRecords($chunkSize);

        return [
            'expired_sessions' => $expiredSessions,
            'expired_idempotency_keys' => $expiredKeys,
        ];
    }

    private function expireSessions(int $chunkSize): int
    {
        $chunkSize = max(1, $chunkSize);
        $count = 0;

        while (true) {
            $sessions = FileUploadSession::query()
                ->whereIn('status', self::NON_TERMINAL_STATUSES)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->orderBy('expires_at')
                ->limit($chunkSize)
                ->get();

            if ($sessions->isEmpty()) {
                break;
            }

            foreach ($sessions as $session) {
                try {
                    $this->multipartGateway->abort(
                        (string) $session->disk,
                        (string) $session->object_key,
                        (string) $session->provider_upload_id,
                    );
                } catch (\Throwable $exception) {
                    Log::warning('Failed to abort expired multipart upload session during cleanup.', [
                        'session_id' => (string) $session->getKey(),
                        'provider_upload_id' => (string) $session->provider_upload_id,
                        'object_key' => (string) $session->object_key,
                        'error' => $exception->getMessage(),
                    ]);

                    $session->forceFill([
                        'status' => FileUploadSessionStatus::FAILED,
                        'failed_at' => now(),
                        'failure_code' => 'UPLOAD_ABORT_FAILED',
                        'failure_reason' => $exception->getMessage(),
                        'expires_at' => null,
                        'updated_at' => now(),
                    ])->save();

                    continue;
                }

                $session->forceFill([
                    'status' => FileUploadSessionStatus::EXPIRED,
                    'aborted_at' => now(),
                    'updated_at' => now(),
                ])->save();

                $count++;
            }
        }

        return $count;
    }
}
