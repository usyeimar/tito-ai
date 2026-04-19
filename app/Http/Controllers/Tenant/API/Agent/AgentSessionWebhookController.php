<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Agent;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Agent\Runner\SessionStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recibe webhooks del runner cuando ocurren eventos de sesión.
 *
 * Eventos soportados:
 * - session.started: Sesión iniciada
 * - session.ended: Sesión finalizada (por el agente o desconexión)
 * - session.transcript: Transcripción disponible
 * - session.error: Error en la sesión
 */
class AgentSessionWebhookController extends Controller
{
    public function __construct(
        private readonly SessionStateService $sessionState,
    ) {}

    /**
     * Procesa eventos webhook del runner para un canal específico.
     */
    public function handle(Request $request, string $channelId): JsonResponse
    {
        // Validar API key del runner
        $apiKey = $request->header('X-Tito-Agent-Key');
        if (! $this->isValidApiKey($apiKey)) {
            Log::warning('Webhook del runner rechazado: API key inválida', [
                'ip' => $request->ip(),
                'channel_id' => $channelId,
                'event' => $request->input('event'),
            ]);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $tenantId = $request->input('tenant_id');
        $agentId = $request->input('agent_id');
        $roomName = $request->input('room_name');
        $data = $request->input('data', []);

        Log::info('Webhook del runner recibido', [
            'event' => $event,
            'channel_id' => $channelId,
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'room_name' => $roomName,
        ]);

        // Manejar eventos específicos
        match ($event) {
            'session.started' => $this->handleSessionStarted($channelId, $tenantId, $agentId, $roomName, $data),
            'session.ended' => $this->handleSessionEnded($channelId, $tenantId, $agentId, $roomName, $data),
            'session.transcript' => $this->handleSessionTranscript($channelId, $tenantId, $agentId, $roomName, $data),
            'session.error' => $this->handleSessionError($channelId, $tenantId, $agentId, $roomName, $data),
            default => Log::debug("Evento de runner no manejado: {$event}"),
        };

        return response()->json(['status' => 'received']);
    }

    /**
     * Maneja el evento de inicio de sesión.
     */
    private function handleSessionStarted(
        string $channelId,
        string $tenantId,
        string $agentId,
        string $roomName,
        array $data
    ): void {
        broadcast([
            'event' => 'session.started',
            'channel_id' => $channelId,
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'room_name' => $roomName,
            'data' => $data,
        ])->toOthers();
    }

    /**
     * Maneja el evento de fin de sesión.
     * Notifica al frontend para cerrar la modal y generar análisis.
     */
    private function handleSessionEnded(
        string $channelId,
        string $tenantId,
        string $agentId,
        string $roomName,
        array $data
    ): void {
        Log::info('Sesión finalizada por el agente', [
            'channel_id' => $channelId,
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'room_name' => $roomName,
            'reason' => $data['reason'] ?? 'unknown',
            'duration' => $data['duration_seconds'] ?? null,
        ]);

        // Update session state so frontend polling detects it
        $this->sessionState->endSession($channelId, 'agent', $data);
    }

    /**
     * Maneja el evento de transcript disponible.
     */
    private function handleSessionTranscript(
        string $channelId,
        string $tenantId,
        string $agentId,
        string $roomName,
        array $data
    ): void {
        broadcast([
            'event' => 'session.transcript',
            'channel_id' => $channelId,
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'room_name' => $roomName,
            'data' => $data,
        ])->toOthers();
    }

    /**
     * Maneja errores de sesión.
     */
    private function handleSessionError(
        string $channelId,
        string $tenantId,
        string $agentId,
        string $roomName,
        array $data
    ): void {
        Log::error('Error en sesión del runner', [
            'channel_id' => $channelId,
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'room_name' => $roomName,
            'error' => $data['error'] ?? 'Unknown error',
        ]);

        broadcast([
            'event' => 'session.error',
            'channel_id' => $channelId,
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'room_name' => $roomName,
            'data' => $data,
        ])->toOthers();
    }

    /**
     * Valida la API key del runner.
     * Si no hay key configurada, acepta el webhook (para desarrollo).
     */
    private function isValidApiKey(?string $apiKey): bool
    {
        $expectedKey = config('runners.api_key');

        // Si no hay key configurada, aceptar webhook (desarrollo)
        if (empty($expectedKey)) {
            Log::debug('Webhook del runner aceptado: no hay API key configurada');

            return true;
        }

        return $apiKey === $expectedKey;
    }
}
