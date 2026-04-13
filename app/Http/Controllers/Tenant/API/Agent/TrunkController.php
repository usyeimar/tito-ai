<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Agent;

use App\Actions\Tenant\Agent\CreateTrunk;
use App\Actions\Tenant\Agent\DeleteTrunk;
use App\Actions\Tenant\Agent\ListTrunks;
use App\Actions\Tenant\Agent\ShowTrunk;
use App\Actions\Tenant\Agent\UpdateTrunk;
use App\Data\Tenant\Agent\CreateTrunkData;
use App\Data\Tenant\Agent\UpdateTrunkData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Agent\StoreTrunkRequest;
use App\Http\Requests\Tenant\Agent\UpdateTrunkRequest;
use App\Models\Tenant\Agent\Trunk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TrunkController extends Controller
{
    public function __construct(
        private readonly ListTrunks $listTrunks,
        private readonly CreateTrunk $createTrunk,
        private readonly ShowTrunk $showTrunk,
        private readonly UpdateTrunk $updateTrunk,
        private readonly DeleteTrunk $deleteTrunk,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Trunk::class);

        $trunks = ($this->listTrunks)($request->all());

        return response()->json([
            'data' => $trunks->map(fn (Trunk $trunk) => $this->transformTrunk($trunk))->values(),
        ]);
    }

    public function store(StoreTrunkRequest $request, CreateTrunkData $data): JsonResponse
    {
        Gate::authorize('create', Trunk::class);

        $trunk = ($this->createTrunk)($data);

        return response()->json([
            'data' => $this->transformTrunk($trunk),
            'message' => 'Trunk created',
        ], 201);
    }

    public function show(Trunk $trunk): JsonResponse
    {
        Gate::authorize('view', $trunk);

        $trunk = ($this->showTrunk)($trunk);

        return response()->json([
            'data' => $this->transformTrunk($trunk),
        ]);
    }

    public function update(UpdateTrunkRequest $request, Trunk $trunk, UpdateTrunkData $data): JsonResponse
    {
        Gate::authorize('update', $trunk);

        $trunk = ($this->updateTrunk)($trunk, $data);

        return response()->json([
            'data' => $this->transformTrunk($trunk),
            'message' => 'Trunk updated',
        ]);
    }

    public function destroy(Trunk $trunk): JsonResponse
    {
        Gate::authorize('delete', $trunk);

        ($this->deleteTrunk)($trunk);

        return response()->json([
            'message' => 'Trunk deleted',
        ]);
    }

    /**
     * Transform a Trunk model to array for API response.
     *
     * @return array<string, mixed>
     */
    private function transformTrunk(Trunk $trunk): array
    {
        return [
            'id' => $trunk->id,
            'name' => $trunk->name,
            'agent_id' => $trunk->agent_id,
            'workspace_slug' => $trunk->workspace_slug,
            'mode' => $trunk->mode,
            'max_concurrent_calls' => $trunk->max_concurrent_calls,
            'codecs' => $trunk->codecs,
            'status' => $trunk->status,
            'inbound_auth' => $trunk->inbound_auth,
            'routes' => $trunk->routes,
            'sip_host' => $trunk->sip_host,
            'sip_port' => $trunk->sip_port,
            'register_config' => $trunk->register_config,
            'outbound' => $trunk->outbound,
            'created_at' => $trunk->created_at?->toIso8601String(),
            'updated_at' => $trunk->updated_at?->toIso8601String(),
        ];
    }
}
