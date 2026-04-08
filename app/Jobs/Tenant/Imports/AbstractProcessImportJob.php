<?php

namespace App\Jobs\Tenant\Imports;

use App\Models\Tenant\Auth\Authentication\User;
use App\Services\Tenant\Activity\DTOs\ActivityContext;
use App\Services\Tenant\Activity\Support\ActivityContextStore;
use App\Services\Tenant\Imports\AbstractImportProcessor;
use App\Services\Tenant\Notifications\TenantNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $maxExceptions = 1;

    public function __construct(
        public readonly string $importId,
    ) {
        $this->onQueue((string) config('imports.queue', 'imports'));
    }

    /**
     * Find the import model by ID with its requester relation loaded.
     */
    abstract protected function findImport(): Model;

    /**
     * Resolve the import processor from the container.
     */
    abstract protected function makeProcessor(): AbstractImportProcessor;

    /**
     * Return a human-readable entity label (e.g. 'Contact', 'Company').
     */
    abstract protected function entityLabel(): string;

    /**
     * Return the notification source key (e.g. 'contact_import').
     */
    abstract protected function notificationSource(): string;

    /**
     * Return the notification icon name (e.g. 'IconFileImport').
     */
    abstract protected function notificationIcon(): string;

    /**
     * Broadcast a progress update event to the requester.
     */
    abstract protected function broadcastProgress(Model $import, User $requester): void;

    /**
     * Send a result email to the requester (only called in execute mode).
     */
    abstract protected function sendResultEmail(Model $import, User $requester): void;

    public function handle(TenantNotificationService $notificationService): void
    {
        $import = $this->findImport();

        if ($import->isTerminal()) {
            return;
        }

        $requester = $import->requester;
        if (! $requester instanceof User) {
            $import->forceFill([
                'status' => 'failed',
                'error_message' => 'Import requester could not be resolved.',
                'finished_at' => now(),
            ])->save();

            return;
        }

        $import->forceFill([
            'status' => 'running',
            'started_at' => $import->started_at ?? now(),
        ])->save();

        $this->broadcastProgress($import, $requester);

        $context = new ActivityContext(
            actorType: 'tenant_user',
            actorId: (string) $requester->getKey(),
            actorLabel: $requester->name,
            origin: 'job',
            requestId: (string) $import->idempotency_key,
            originMetadata: [
                'job_class' => static::class,
                'queue' => $this->queue,
                'import_id' => (string) $import->getKey(),
            ],
        );

        $onProgress = fn () => $this->broadcastProgress($import, $requester);

        try {
            $processor = $this->makeProcessor();
            $result = ActivityContextStore::runWith($context, fn (): array => $processor->process($import, $requester, $onProgress));

            $fatalError = is_string($result['fatal_error'] ?? null)
                ? trim((string) $result['fatal_error'])
                : null;

            $status = $fatalError !== null && $fatalError !== ''
                ? 'failed'
                : (((int) ($result['failed_count'] ?? 0) > 0 || (int) ($result['skipped_count'] ?? 0) > 0)
                    ? 'completed_with_issues'
                    : 'completed');

            $import->forceFill([
                'status' => $status,
                'total_rows' => (int) ($result['total_rows'] ?? 0),
                'processed_rows' => (int) ($result['processed_rows'] ?? 0),
                'created_count' => (int) ($result['created_count'] ?? 0),
                'updated_count' => (int) ($result['updated_count'] ?? 0),
                'skipped_count' => (int) ($result['skipped_count'] ?? 0),
                'failed_count' => (int) ($result['failed_count'] ?? 0),
                'warnings_count' => (int) ($result['warnings_count'] ?? 0),
                'warnings_by_code' => $result['warnings_by_code'] ?? [],
                'errors_by_code' => $result['errors_by_code'] ?? [],
                'report_path' => $result['report_path'] ?? null,
                'failed_rows_path' => $result['failed_rows_path'] ?? null,
                'error_message' => $fatalError !== null && $fatalError !== ''
                    ? Str::limit($fatalError, 512)
                    : null,
                'finished_at' => now(),
                'expires_at' => $import->expires_at ?? now()->addDays(max(1, (int) config('imports.retention_days', 30))),
            ])->save();
        } catch (Throwable $exception) {
            $import->forceFill([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 512),
                'finished_at' => now(),
            ])->save();
        }

        $import->refresh();

        $this->broadcastProgress($import, $requester);
        $this->createCompletionNotification($import, $requester, $notificationService);

        if ((string) $import->mode === 'execute') {
            $this->sendResultEmail($import, $requester);
        }
    }

    /**
     * Build the standard progress/summary payload from an import model.
     *
     * @return array<string, mixed>
     */
    protected function buildProgressPayload(Model $import): array
    {
        return [
            'import_id' => (string) $import->getKey(),
            'status' => (string) $import->status,
            'mode' => (string) $import->mode,
            'source_filename' => (string) $import->source_filename,
            'summary' => [
                'total' => (int) $import->total_rows,
                'processed' => (int) $import->processed_rows,
                'created' => (int) $import->created_count,
                'updated' => (int) $import->updated_count,
                'skipped' => (int) $import->skipped_count,
                'failed' => (int) $import->failed_count,
                'warnings' => (int) $import->warnings_count,
            ],
        ];
    }

    private function createCompletionNotification(Model $import, User $requester, TenantNotificationService $notificationService): void
    {
        $label = $this->entityLabel();
        $status = (string) $import->status;
        $filename = (string) $import->source_filename;

        $title = match ($status) {
            'completed' => "{$label} import completed",
            'completed_with_issues' => "{$label} import completed with issues",
            'failed' => "{$label} import failed",
            default => "{$label} import finished",
        };

        $created = (int) $import->created_count;
        $updated = (int) $import->updated_count;
        $failed = (int) $import->failed_count;

        $message = match ($status) {
            'failed' => "Import of \"{$filename}\" failed: ".($import->error_message ?? 'Unknown error'),
            default => "Import of \"{$filename}\": {$created} created, {$updated} updated"
                .($failed > 0 ? ", {$failed} failed" : ''),
        };

        $notificationService->createForUsers([$requester], [
            'title' => $title,
            'message' => $message,
            'icon' => $this->notificationIcon(),
            'source' => $this->notificationSource(),
            'meta' => [
                'import_id' => (string) $import->getKey(),
                'status' => $status,
                'mode' => (string) $import->mode,
            ],
        ]);
    }
}
