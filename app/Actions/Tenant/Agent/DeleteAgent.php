<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;

final class DeleteAgent
{
    public function __invoke(Agent $agent): void
    {
        $agent->delete();
    }
}
