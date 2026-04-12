<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Agent;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Agent\Agent;
use App\Services\Tenant\Agent\Runner\AgentConfigBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller for exposing agent configuration to the runners service.
 *
 * This controller provides endpoints for the SIP bridge (Python/FastAPI)
 * to fetch agent configurations when they are not cached in Redis.
 */
final class AgentConfigController extends Controller
{
    public function __construct(
        private readonly AgentConfigBuilder $configBuilder,
    ) {}

    /**
     * Get the full AgentConfig for an agent by ID.
     *
     * This endpoint is used by the runners service as a fallback
     * when the agent config is not found in Redis cache.
     */
    public function getConfigById(string $agentId): JsonResponse
    {
        try {
            $agent = Agent::with(['settings', 'tools'])->findOrFail($agentId);

            $config = $this->configBuilder->build($agent);

            return response()->json($config);
        } catch (ModelNotFoundException $e) {
            Log::warning('Agent not found for config request', [
                'agent_id' => $agentId,
            ]);

            return response()->json([
                'error' => 'Agent not found',
                'agent_id' => $agentId,
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Failed to build agent config', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to build agent configuration',
            ], 500);
        }
    }

    /**
     * Get the full AgentConfig for an agent by slug.
     *
     * This endpoint is used by the runners service to resolve
     * agent configurations by slug.
     */
    public function getConfigBySlug(string $agentSlug): JsonResponse
    {
        try {
            $agent = Agent::with(['settings', 'tools'])
                ->where('slug', $agentSlug)
                ->firstOrFail();

            $config = $this->configBuilder->build($agent);

            return response()->json($config);
        } catch (ModelNotFoundException $e) {
            Log::warning('Agent not found for config request by slug', [
                'slug' => $agentSlug,
            ]);

            return response()->json([
                'error' => 'Agent not found',
                'slug' => $agentSlug,
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Failed to build agent config by slug', [
                'slug' => $agentSlug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to build agent configuration',
            ], 500);
        }
    }
}
