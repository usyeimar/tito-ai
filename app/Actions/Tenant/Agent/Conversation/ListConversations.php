<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent\Conversation;

use App\Models\Tenant\Agent\AgentSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListConversations
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<AgentSession>
     */
    public function __invoke(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(
            AgentSession::query()->with('agent')->withCount('transcripts')
        )
            ->allowedFilters(
                AllowedFilter::exact('agent_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('channel'),
                AllowedFilter::callback('started_after', function (Builder $query, mixed $value): void {
                    $query->where('started_at', '>=', $value);
                }),
                AllowedFilter::callback('started_before', function (Builder $query, mixed $value): void {
                    $query->where('started_at', '<=', $value);
                }),
            )
            ->allowedSorts('started_at', 'ended_at', 'status', 'created_at')
            ->defaultSort('-started_at')
            ->paginateFromFilters($filters);
    }
}
