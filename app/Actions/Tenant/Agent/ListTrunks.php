<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Trunk;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTrunks
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Trunk>
     */
    public function __invoke(array $filters = []): LengthAwarePaginator
    {
        $query = Trunk::query();

        if (isset($filters['workspace_slug'])) {
            $query->where('workspace_slug', $filters['workspace_slug']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['mode'])) {
            $query->where('mode', $filters['mode']);
        }

        if (isset($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }

        return $query->orderBy('name')->paginateFromFilters($filters);
    }
}
