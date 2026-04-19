<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Agent;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Agent\Agent;
use App\Services\Tenant\Agent\Runner\RunnerClient;
use App\Services\Tenant\Agent\Runner\SessionStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AgentTestCallController extends Controller
{
    public function __construct(
        private readonly RunnerClient $runner,
        private readonly SessionStateService $sessionState,
    ) {}

    /**
     * Provision a new voice session against the runners microservice and
     * return the connection payload to the browser.
     */
    public function start(Request $request, Agent $agent): JsonResponse
    {
        Gate::authorize('view', $agent);

        $session = $this->runner->createSession($agent);

        if (($session['provider'] ?? null) !== 'livekit') {
            // The runner ignored our transport pin (or is misconfigured).
            if (! empty($session['session_id'])) {
                $this->runner->terminateSession((string) $session['session_id']);
            }

            abort(503, 'El runner devolvió un transporte no soportado: '
                .($session['provider'] ?? 'unknown').'. Esta UI solo soporta LiveKit.');
        }

        // Register session state for frontend polling
        $this->sessionState->createSession(
            channelId: $session['channel_id'],
            tenantId: (string) (tenant('id') ?? 'central'),
            agentId: (string) $agent->id,
            sessionId: $session['session_id'],
            roomName: $session['room_name'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Sesión de prueba creada exitosamente.',
            'data' => $session,
        ], 201);
    }

    /**
     * Get the current state of a session.
     * Used by frontend to detect when the agent ends the call.
     */
    public function status(Request $request, string $channelId): JsonResponse
    {
        $session = $this->sessionState->getSession($channelId);

        if (! $session) {
            return response()->json([
                'error' => 'Session not found',
            ], 404);
        }

        return response()->json([
            'data' => $session,
        ]);
    }

    /**
     * Mark a session as ended by the user.
     */
    public function userEnded(Request $request, string $channelId): JsonResponse
    {
        $session = $this->sessionState->getSession($channelId);

        if (! $session) {
            return response()->json([
                'error' => 'Session not found',
            ], 404);
        }

        $this->sessionState->endSession($channelId, 'user');

        return response()->json([
            'message' => 'Session marked as ended by user',
        ]);
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
