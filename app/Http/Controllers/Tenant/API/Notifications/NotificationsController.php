<?php

namespace App\Http\Controllers\Tenant\API\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\Notifications\BatchNotificationIdsRequest;
use App\Http\Requests\Tenant\API\Notifications\IndexTenantNotificationRequest;
use App\Http\Resources\Tenant\API\Notifications\TenantNotificationResource;
use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    public function index(IndexTenantNotificationRequest $request)
    {
        $page = (int) $request->validated('page.number', 1);
        $perPage = (int) $request->validated('page.size', 25);
        $unread = $request->validated('filter.unread');

        $query = $this->ownedQuery($request->user())
            ->orderByDesc('created_at');

        if ($unread === true) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate($perPage, ['*'], 'page', $page);

        return TenantNotificationResource::collection($notifications);
    }

    public function markRead(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $this->assertOwned($request->user(), $notification);

        if (! $notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function batchMarkRead(BatchNotificationIdsRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');

        $updatedCount = $this->ownedQuery($request->user())
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'updated_count' => $updatedCount,
            ],
        ]);
    }

    public function destroy(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $this->assertOwned($request->user(), $notification);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }

    public function batchDestroy(BatchNotificationIdsRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');

        $deletedCount = $this->ownedQuery($request->user())
            ->whereIn('id', $ids)
            ->delete();

        return response()->json([
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }

    private function assertOwned(User $user, DatabaseNotification $notification): void
    {
        $isOwned = $notification->notifiable_type === $user->getMorphClass()
            && (string) $notification->notifiable_id === (string) $user->getKey();

        if (! $isOwned) {
            abort(404);
        }
    }

    private function ownedQuery(User $user): Builder
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', (string) $user->getKey());
    }
}
