<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\Activity\IndexActivityRequest;
use App\Http\Resources\Tenant\Activity\ActivityEventResource;
use App\Models\Tenant\Activity\ActivityEvent;
use App\Services\Tenant\Activity\ActivityQueryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityQueryService $activityQueryService,
    ) {}

    public function index(IndexActivityRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ActivityEvent::class);

        $events = $this->activityQueryService->paginate($request->user(), $request->validated());

        return ActivityEventResource::collection($events);
    }
}
