<?php

namespace App\Services\Tenant\Commons\Bulk;

use App\Enums\BulkTaskItemStatus;
use App\Enums\BulkTaskStatus;
use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\Commons\Bulk\BulkTask;
use App\Models\Tenant\Commons\Bulk\BulkTaskItem;
use Illuminate\Database\UniqueConstraintViolationException;

class BulkTaskService
{
    public function createOrReuseTask(
        User $actor,
        string $module,
        string $resource,
        string $action,
        array $ids,
        ?string $clientRequestId = null,
        ?array $context = null,
    ): BulkTask {
        $normalizedIds = array_values(array_map('strval', $ids));

        if ($clientRequestId === null || trim($clientRequestId) === '') {
            return $this->createTask($actor, $module, $resource, $action, $normalizedIds, null, $context);
        }

        $clientRequestId = trim($clientRequestId);

        $existing = BulkTask::query()
            ->where('requested_by', $actor->getKey())
            ->where('module', $module)
            ->where('resource', $resource)
            ->where('action', $action)
            ->where('client_request_id', $clientRequestId)
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            return $this->createTask($actor, $module, $resource, $action, $normalizedIds, $clientRequestId, $context);
        } catch (UniqueConstraintViolationException) {
            return BulkTask::query()
                ->where('requested_by', $actor->getKey())
                ->where('module', $module)
                ->where('resource', $resource)
                ->where('action', $action)
                ->where('client_request_id', $clientRequestId)
                ->firstOrFail();
        }
    }

    public function forUserAndResource(string $taskId, User $actor, string $module, string $resource): BulkTask
    {
        return BulkTask::query()
            ->with(['items' => fn ($query) => $query->orderBy('position')])
            ->whereKey($taskId)
            ->where('requested_by', $actor->getKey())
            ->where('module', $module)
            ->where('resource', $resource)
            ->firstOrFail();
    }

    public function markRunning(BulkTask $task): BulkTask
    {
        if ($task->status === BulkTaskStatus::QUEUED) {
            $task->forceFill([
                'status' => BulkTaskStatus::RUNNING,
                'started_at' => now(),
            ])->save();
        }

        return $task;
    }

    public function recordItemResult(BulkTask $task, int $position, string $itemId, array $result): BulkTaskItem
    {
        $item = BulkTaskItem::query()->create([
            'task_id' => $task->getKey(),
            'position' => $position,
            'item_id' => $itemId,
            'status' => $result['status'] ?? BulkTaskItemStatus::FAILED_EXCEPTION,
            'code' => $result['code'] ?? null,
            'detail' => $result['detail'] ?? null,
            'http_status' => $result['http_status'] ?? null,
            'result' => $result['result'] ?? null,
        ]);

        $task->forceFill([
            'processed_count' => $task->processed_count + 1,
            'success_count' => $task->success_count + ($item->status === BulkTaskItemStatus::SUCCESS ? 1 : 0),
            'skipped_count' => $task->skipped_count + ($item->status->isSkipped() ? 1 : 0),
            'failed_count' => $task->failed_count + ($item->status->isFailed() ? 1 : 0),
        ])->save();

        return $item;
    }

    public function complete(BulkTask $task): BulkTask
    {
        $status = ($task->failed_count > 0 || $task->skipped_count > 0)
            ? BulkTaskStatus::COMPLETED_WITH_ISSUES
            : BulkTaskStatus::COMPLETED;

        $task->forceFill([
            'status' => $status,
            'finished_at' => now(),
        ])->save();

        return $task->fresh(['items']);
    }

    public function fail(BulkTask $task, string $detail): BulkTask
    {
        BulkTaskItem::query()->create([
            'task_id' => $task->getKey(),
            'position' => max(1, $task->processed_count + 1),
            'item_id' => '__task__',
            'status' => BulkTaskItemStatus::FAILED_EXCEPTION,
            'code' => 'INTERNAL_ERROR',
            'detail' => $detail,
            'http_status' => 500,
            'result' => null,
        ]);

        $task->forceFill([
            'processed_count' => $task->processed_count + 1,
            'failed_count' => $task->failed_count + 1,
            'status' => BulkTaskStatus::FAILED,
            'finished_at' => now(),
        ])->save();

        return $task->fresh(['items']);
    }

    public function cleanupExpired(int $retentionDays): int
    {
        $cutoff = now()->subDays(max(1, $retentionDays));

        return BulkTask::query()
            ->whereIn('status', [BulkTaskStatus::COMPLETED, BulkTaskStatus::COMPLETED_WITH_ISSUES, BulkTaskStatus::FAILED])
            ->where('created_at', '<', $cutoff)
            ->delete();
    }

    private function createTask(
        User $actor,
        string $module,
        string $resource,
        string $action,
        array $ids,
        ?string $clientRequestId,
        ?array $context,
    ): BulkTask {
        return BulkTask::query()->create([
            'module' => $module,
            'resource' => $resource,
            'action' => $action,
            'status' => BulkTaskStatus::QUEUED,
            'requested_by' => $actor->getKey(),
            'client_request_id' => $clientRequestId,
            'context' => $context,
            'requested_ids' => $ids,
            'submitted_count' => count($ids),
        ]);
    }
}
