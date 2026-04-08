<?php

declare(strict_types=1);

namespace App\Services\Tenant\Activity;

use App\Models\Tenant\Activity\ActivityEvent;
use App\Models\Tenant\Activity\ActivityEventRelation;
use App\Services\Tenant\Activity\DTOs\ActivityContext;
use App\Services\Tenant\Activity\Support\ActivityContextStore;
use App\Services\Tenant\Activity\Support\ChangesMapBuilder;
use App\Services\Tenant\Activity\Support\KnownMorphTypes;
use App\Services\Tenant\Activity\Support\SubjectLabelResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ActivityEventService
{
    public function __construct(
        private readonly KnownMorphTypes $knownTypes,
        private readonly SubjectLabelResolver $labelResolver,
        private readonly ChangesMapBuilder $changes,
    ) {}

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<int, string>|null  $allowedFields
     * @return array<string, array{from:mixed,to:mixed}>
     */
    public function buildChanges(array $before, array $after, ?array $allowedFields = null): array
    {
        return $this->changes->build($before, $after, $allowedFields);
    }

    /**
     * @param  array<int, Model|array{type:string,id:string,label?:string,relation?:string}>  $related
     * @param  array<string, mixed>  $metadata
     */
    public function recordMutation(
        string $eventType,
        Model $subject,
        array $changes = [],
        array $related = [],
        array $metadata = [],
        ?ActivityContext $context = null,
    ): ActivityEvent {
        $subjectType = $this->knownTypes->typeForModel($subject);

        if ($subjectType === null) {
            throw new InvalidArgumentException('Unknown activity subject model: '.$subject::class);
        }

        $subjectId = (string) $subject->getKey();
        $subjectLabel = $this->labelResolver->resolve($subject);
        $context = $context ?? $this->resolveContext();
        $occurredAt = now();

        $metadata = [
            ...$metadata,
            'origin_context' => $context->originMetadata,
        ];

        $event = ActivityEvent::query()->create([
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
            'event_type' => $eventType,
            'actor_type' => $context->actorType,
            'actor_id' => $context->actorId,
            'actor_label' => $context->actorLabel,
            'origin' => $context->origin,
            'request_id' => $context->requestId,
            'workflow_actor_type' => $context->workflowActorType,
            'workflow_actor_id' => $context->workflowActorId,
            'workflow_actor_label' => $context->workflowActorLabel,
            'workflow_run_id' => $context->workflowRunId,
            'changes' => $changes,
            'metadata' => $metadata,
            'occurred_at' => $occurredAt,
            'created_at' => $occurredAt,
        ]);

        $resolvedRelations = $this->resolveRelations($related);

        foreach ($resolvedRelations as $relation) {
            ActivityEventRelation::query()->create([
                'activity_event_id' => (string) $event->getKey(),
                'related_type' => $relation['type'],
                'related_id' => $relation['id'],
                'related_label' => $relation['label'],
                'relation' => $relation['relation'],
                'occurred_at' => $occurredAt,
                'created_at' => $occurredAt,
            ]);
        }

        return $event->load('relations');
    }

    private function resolveContext(): ActivityContext
    {
        $stored = ActivityContextStore::current();

        if ($stored !== null) {
            return $stored;
        }

        $request = request();

        if (! $request instanceof Request) {
            return new ActivityContext;
        }

        $requestId = trim((string) ($request->headers->get('X-Request-Id') ?? $request->headers->get('X-Request-ID') ?? ''));

        if ($requestId === '') {
            $requestId = 'req_'.Str::ulid()->toBase32();
        }

        $user = $request->user();
        $actorType = null;
        $actorId = null;
        $actorLabel = null;

        if ($user instanceof Model) {
            $actorType = $this->knownTypes->typeForModel($user) ?? 'tenant_user';
            $actorId = (string) $user->getKey();
            $actorLabel = trim((string) (data_get($user, 'name') ?? data_get($user, 'email') ?? ''));
            $actorLabel = $actorLabel !== '' ? $actorLabel : $actorId;
        }

        return new ActivityContext(
            actorType: $actorType,
            actorId: $actorId,
            actorLabel: $actorLabel,
            origin: 'api',
            requestId: $requestId,
            originMetadata: [
                'method' => $request->method(),
                'path' => $request->path(),
            ],
        );
    }

    /**
     * @param  array<int, Model|array{type:string,id:string,label?:string,relation?:string}>  $related
     * @return array<int, array{type:string,id:string,label:?string,relation:?string}>
     */
    private function resolveRelations(array $related): array
    {
        $resolved = [];
        $seen = [];

        foreach ($related as $entry) {
            $type = null;
            $id = null;
            $label = null;
            $relation = null;

            if ($entry instanceof Model) {
                $type = $this->knownTypes->typeForModel($entry);
                $id = (string) $entry->getKey();
                $label = $this->labelResolver->resolve($entry);
            } elseif (is_array($entry)) {
                $type = trim((string) ($entry['type'] ?? ''));
                $id = trim((string) ($entry['id'] ?? ''));
                $label = isset($entry['label']) ? trim((string) $entry['label']) : null;
                $relation = isset($entry['relation']) ? trim((string) $entry['relation']) : null;
            }

            if ($type === null || $type === '' || $id === null || $id === '') {
                continue;
            }

            $key = $type.'|'.$id.'|'.($relation ?? '');

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $resolved[] = [
                'type' => $type,
                'id' => $id,
                'label' => $label,
                'relation' => $relation,
            ];
        }

        return $resolved;
    }
}
