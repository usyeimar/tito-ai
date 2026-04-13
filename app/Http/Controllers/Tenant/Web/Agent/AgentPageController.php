<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Web\Agent;

use App\Actions\Tenant\Agent\ListAgents;
use App\Actions\Tenant\Agent\ShowAgent;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Stancl\Tenancy\Contracts\Tenant;

class AgentPageController extends Controller
{
    public function index(Request $request, ListAgents $action): Response
    {
        Gate::authorize('viewAny', Agent::class);

        return Inertia::render('tenant/agents/show', [
            'tenant' => $this->tenantPayload(),
            'agent' => null,
            'agents' => $action(['search' => $request->query('search')])
                ->map->toArray()
                ->values(),
        ]);
    }

    public function show(Request $request, Agent $agent, ShowAgent $action, ListAgents $listAction): Response
    {
        Gate::authorize('view', $agent);

        return Inertia::render('tenant/agents/show', [
            'tenant' => $this->tenantPayload(),
            'agent' => $action($agent)->toArray(),
            'agents' => $listAction(['search' => $request->query('search')])
                ->map->toArray()
                ->values(),
        ]);
    }

    /**
     * @return array{id: string, name: string, slug: string}|null
     */
    private function tenantPayload(): ?array
    {
        /** @var Tenant|null $tenant */
        $tenant = tenant();

        if ($tenant === null) {
            return null;
        }

        return [
            'id' => (string) $tenant->getTenantKey(),
            'name' => (string) ($tenant->name ?? $tenant->getTenantKey()),
            'slug' => (string) ($tenant->slug ?? $tenant->getTenantKey()),
        ];
    }
}
