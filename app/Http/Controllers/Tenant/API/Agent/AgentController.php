<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Agent;

use App\Actions\Tenant\Agent\CreateAgent;
use App\Actions\Tenant\Agent\DeleteAgent;
use App\Actions\Tenant\Agent\ListAgents;
use App\Actions\Tenant\Agent\ShowAgent;
use App\Actions\Tenant\Agent\UpdateAgent;
use App\Data\Tenant\Agent\CreateAgentData;
use App\Data\Tenant\Agent\UpdateAgentData;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Agent\Agent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AgentController extends Controller
{
    public function index(ListAgents $action, Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Agent::class);

        return response()->json([
            'data' => $action($request->all())->map->toArray()->values(),
        ]);
    }

    public function store(CreateAgentData $data, CreateAgent $action): JsonResponse
    {
        Gate::authorize('create', Agent::class);

        $agentData = $action($data);

        return response()->json([
            'data' => $agentData->toArray(),
            'message' => 'Agent created',
        ], 201);
    }

    public function show(Agent $agent, ShowAgent $action): JsonResponse
    {
        Gate::authorize('view', $agent);

        return response()->json([
            'data' => $action($agent)->toArray(),
        ]);
    }

    public function update(UpdateAgentData $data, Agent $agent, UpdateAgent $action): JsonResponse
    {
        Gate::authorize('update', $agent);

        $agentData = $action($agent, $data);

        return response()->json([
            'data' => $agentData->toArray(),
            'message' => 'Agent updated',
        ]);
    }

    public function destroy(Agent $agent, DeleteAgent $action): JsonResponse
    {
        Gate::authorize('delete', $agent);

        $action($agent);

        return response()->json([
            'message' => 'Agent deleted',
        ]);
    }
}
