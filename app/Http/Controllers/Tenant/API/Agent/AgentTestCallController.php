<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Agent;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Agent\Agent;
use App\Services\Tenant\Agent\Runner\RunnerClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class AgentTestCallController extends Controller
{
    public function __construct(private readonly RunnerClient $runner) {}

    /**
     * Provision a new voice session against the runners microservice and
     * return the connection payload to the browser.
     */
    public function start(Request $request, Agent $agent): JsonResponse
    {
        Gate::authorize('view', $agent);

        try {
            $session = $this->runner->createSession($agent);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        }

        if (($session['provider'] ?? null) !== 'livekit') {
            // The runner ignored our transport pin (or is misconfigured).
            // Tear the session down and surface a clear error.
            if (! empty($session['session_id'])) {
                $this->runner->terminateSession((string) $session['session_id']);
            }

            return response()->json([
                'message' => 'El runner devolvió un transporte no soportado: '
                    .($session['provider'] ?? 'unknown').'. Esta UI solo soporta LiveKit.',
            ], 503);
        }

        return response()->json([
            'data' => $session,
        ], 201);
    }

    public function stop(Request $request, Agent $agent, string $session): JsonResponse
    {
        Gate::authorize('view', $agent);

        $ok = $this->runner->terminateSession($session);

        return response()->json([
            'data' => ['terminated' => $ok],
        ]);
    }
}
