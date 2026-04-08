<?php

declare(strict_types=1);

namespace App\Services\Tenant\Activity\DTOs;

final readonly class ActivityContext
{
    /**
     * @param  array<string, mixed>  $originMetadata
     */
    public function __construct(
        public ?string $actorType = null,
        public ?string $actorId = null,
        public ?string $actorLabel = null,
        public string $origin = 'system',
        public ?string $requestId = null,
        public ?string $workflowActorType = null,
        public ?string $workflowActorId = null,
        public ?string $workflowActorLabel = null,
        public ?string $workflowRunId = null,
        public array $originMetadata = [],
    ) {}
}
