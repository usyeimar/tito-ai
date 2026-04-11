<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\API\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'occurred_at' => $this->occurred_at,
            'event_type' => $this->event_type,
            'origin' => $this->origin,
            'request_id' => $this->request_id,
            'subject' => [
                'type' => $this->subject_type,
                'id' => $this->subject_id,
                'label' => $this->subject_label,
            ],
            'related' => $this->whenLoaded('relations', function (): array {
                return $this->relations->map(static fn ($relation): array => [
                    'type' => $relation->related_type,
                    'id' => $relation->related_id,
                    'label' => $relation->related_label,
                    'relation' => $relation->relation,
                ])->all();
            }, []),
            'actor' => $this->actor_id === null ? null : [
                'type' => $this->actor_type,
                'id' => $this->actor_id,
                'label' => $this->actor_label,
            ],
            'workflow_actor' => $this->workflow_actor_id === null ? null : [
                'type' => $this->workflow_actor_type,
                'id' => $this->workflow_actor_id,
                'label' => $this->workflow_actor_label,
            ],
            'workflow_run_id' => $this->workflow_run_id,
            'is_redacted' => (bool) data_get($this->metadata, 'redaction_applied', false),
            'changes' => $this->changes ?? [],
            'metadata' => $this->metadata ?? [],
        ];
    }
}
