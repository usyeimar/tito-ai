<?php

declare(strict_types=1);

namespace App\Events\Tenant\Agent;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AgentSessionEvent implements ShouldBroadcastNow
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $event,
        public readonly array $payload = [],
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        $tenantKey = tenant()?->getTenantKey() ?? '';

        return [
            new Channel("tenant.{$tenantKey}.agent-sessions.{$this->sessionId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->event;
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
