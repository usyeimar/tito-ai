<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Trunk;

final class ShowTrunk
{
    public function __invoke(Trunk $trunk): Trunk
    {
        return $trunk->load(['agent']);
    }
}
