<?php

namespace App\Services\Tenant\Commons\Files\Uploads;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\Commons\Files\FileUploadIdempotencyKey;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FileUploadIdempotencyService
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  \Closure():array{status:int,body:array<string, mixed>,upload_session_id?:?string,file_id?:?string}  $operation
     * @return array{status:int,body:array<string, mixed>,replayed:bool}
     */
    public function execute(User $actor, string $action, string $idempotencyKey, array $payload, \Closure $operation): array
    {
        $normalizedKey = $this->normalizeKey($idempotencyKey);
        $requestHash = $this->hashPayload($payload);

        $cached = DB::transaction(function () use ($actor, $action, $normalizedKey, $requestHash): ?array {
            $record = FileUploadIdempotencyKey::query()
                ->where('action', $action)
                ->where('idempotency_key', $normalizedKey)
                ->where('actor_id', $actor->getKey())
                ->lockForUpdate()
                ->first();

            if ($record instanceof FileUploadIdempotencyKey) {
                if ((string) $record->request_hash !== $requestHash) {
                    throw new HttpException(409, 'Idempotency key was already used with a different payload.');
                }

                if ($record->response_status !== null && is_array($record->response_body)) {
                    return [
                        'status' => (int) $record->response_status,
                        'body' => $record->response_body,
                        'replayed' => true,
                    ];
                }

                throw new HttpException(409, 'A request with this idempotency key is already being processed.');
            }

            FileUploadIdempotencyKey::query()->create([
                'action' => $action,
                'idempotency_key' => $normalizedKey,
                'request_hash' => $requestHash,
                'expires_at' => now()->addHours($this->ttlHours()),
                'actor_id' => $actor->getKey(),
            ]);

            return null;
        });

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = $operation();
        } catch (\Throwable $exception) {
            FileUploadIdempotencyKey::query()
                ->where('action', $action)
                ->where('idempotency_key', $normalizedKey)
                ->where('actor_id', $actor->getKey())
                ->whereNull('response_status')
                ->delete();

            throw $exception;
        }

        $status = (int) ($response['status'] ?? 200);
        $body = $this->normalizeBody((array) ($response['body'] ?? []));
        $uploadSessionId = isset($response['upload_session_id']) && is_string($response['upload_session_id']) && trim($response['upload_session_id']) !== ''
            ? trim($response['upload_session_id'])
            : null;
        $fileId = isset($response['file_id']) && is_string($response['file_id']) && trim($response['file_id']) !== ''
            ? trim($response['file_id'])
            : null;

        DB::transaction(function () use ($actor, $action, $normalizedKey, $status, $body, $uploadSessionId, $fileId): void {
            $record = FileUploadIdempotencyKey::query()
                ->where('action', $action)
                ->where('idempotency_key', $normalizedKey)
                ->where('actor_id', $actor->getKey())
                ->lockForUpdate()
                ->first();

            if (! $record instanceof FileUploadIdempotencyKey) {
                throw new HttpException(409, 'Idempotency state was lost before response persisted.');
            }

            $record->forceFill([
                'response_status' => $status,
                'response_body' => $body,
                'upload_session_id' => $uploadSessionId,
                'file_id' => $fileId,
            ])->save();
        });

        return [
            'status' => $status,
            'body' => $body,
            'replayed' => false,
        ];
    }

    public function cleanupExpiredRecords(int $chunkSize = 200): int
    {
        $chunkSize = max(1, $chunkSize);
        $deleted = 0;

        while (true) {
            $ids = FileUploadIdempotencyKey::query()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->orderBy('expires_at')
                ->limit($chunkSize)
                ->pluck('id')
                ->all();

            if ($ids === []) {
                break;
            }

            $deleted += FileUploadIdempotencyKey::query()->whereIn('id', $ids)->delete();
        }

        return $deleted;
    }

    private function normalizeKey(string $key): string
    {
        return trim($key);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hashPayload(array $payload): string
    {
        $normalized = $this->sortPayload($payload);

        return hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR));
    }

    private function sortPayload(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->sortPayload($item), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->sortPayload($item);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function normalizeBody(array $body): array
    {
        return json_decode(json_encode($body, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    private function ttlHours(): int
    {
        return max(1, (int) config('files_module.upload.multipart.idempotency_ttl_hours', 24));
    }
}
