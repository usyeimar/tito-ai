<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Gestiona el estado de las sesiones de agentes para notificar al frontend
 * cuando el agente finaliza la llamada.
 */
class SessionStateService
{
    private const CACHE_PREFIX = 'agent_session:';

    private const DEFAULT_TTL = 3600; // 1 hora

    /**
     * Crea un nuevo registro de sesión.
     */
    public function createSession(
        string $channelId,
        string $tenantId,
        string $agentId,
        string $sessionId,
        string $roomName
    ): void {
        $key = self::CACHE_PREFIX.$channelId;

        Cache::put($key, [
            'channel_id' => $channelId,
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'session_id' => $sessionId,
            'room_name' => $roomName,
            'status' => 'active',
            'started_at' => now()->toISOString(),
            'ended_at' => null,
            'ended_by' => null,
            'data' => [],
        ], self::DEFAULT_TTL);

        Log::debug('Sesión de agente registrada', [
            'channel_id' => $channelId,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Marca una sesión como finalizada.
     */
    public function endSession(
        string $channelId,
        string $endedBy = 'agent',
        ?array $data = null
    ): void {
        $key = self::CACHE_PREFIX.$channelId;
        $session = Cache::get($key);

        if (! $session) {
            Log::warning('Intento de finalizar sesión no encontrada', [
                'channel_id' => $channelId,
            ]);

            return;
        }

        $session['status'] = 'ended';
        $session['ended_at'] = now()->toISOString();
        $session['ended_by'] = $endedBy;
        if ($data) {
            $session['data'] = array_merge($session['data'], $data);
        }

        Cache::put($key, $session, self::DEFAULT_TTL);

        Log::info('Sesión de agente marcada como finalizada', [
            'channel_id' => $channelId,
            'session_id' => $session['session_id'],
            'ended_by' => $endedBy,
        ]);
    }

    /**
     * Obtiene el estado de una sesión.
     */
    public function getSession(string $channelId): ?array
    {
        $key = self::CACHE_PREFIX.$channelId;

        return Cache::get($key);
    }

    /**
     * Verifica si una sesión está activa.
     */
    public function isActive(string $channelId): bool
    {
        $session = $this->getSession($channelId);

        return $session !== null && $session['status'] === 'active';
    }

    /**
     * Elimina una sesión del cache.
     */
    public function deleteSession(string $channelId): void
    {
        $key = self::CACHE_PREFIX.$channelId;
        Cache::forget($key);
    }
}
