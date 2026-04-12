# Sistema de Resolución de Agentes para Llamadas SIP

Este documento describe el sistema de resolución de configuraciones de agentes cuando llegan llamadas desde Asterisk.

## Arquitectura

```
┌──────────────┐     ┌──────────────────────────────────────┐     ┌─────────────────┐
│   Asterisk   │────→│         Runners (Python)             │────→│  Laravel (PHP)  │
│  (Stasis/    │     │                                      │     │                 │
│   AudioSocket│     │  ┌───────────────────────────────┐   │     │  ┌─────────────┐│
└──────────────┘     │  │   AgentResolutionService      │   │     │  │   Agent     ││
                     │  │                               │   │     │  │   Config    ││
                     │  │  1. Check Redis Cache ────────┼───┼────→│  │   Builder   ││
                     │  │     agent_config:{agent_id}   │   │     │  └─────────────┘│
                     │  │                               │   │     │       ↑         │
                     │  │  2. If not found ─────────────┼───┼────→│  ┌─────────────┐│
                     │  │     Fetch from Laravel API    │   │     │  │ SyncAgent   ││
                     │  │                               │   │     │  │ ToRedisJob  ││
                     │  │  3. Cache result ─────────────┼───┼────→│  └─────────────┘│
                     │  │     Store in Redis            │   │     │       ↑         │
                     │  └───────────────────────────────┘   │     │  ┌─────────────┐│
                     │                   │                    │     │  │CreateAgent  ││
                     │                   ↓                    │     │  │UpdateAgent  ││
                     │          ┌─────────────┐               │     │  └─────────────┘│
                     │          │   Pipecat   │               │     └─────────────────┘
                     │          │   Pipeline  │               │
                     │          │  STT→LLM→TTS│               │
                     │          └─────────────┘               │
                     └────────────────────────────────────────┘
```

## Flujo de Resolución

### Cuando llega una llamada desde Asterisk:

1. **AudioSocket Server** recibe la conexión TCP desde Asterisk
2. **SIPCallHandler** (`call_handler.py`) recibe el evento de nueva conexión
3. **\_resolve_call** busca el trunk y agent_id por extensión en Redis
4. **\_get_agent_config** usa `AgentResolutionService` para obtener la configuración:
    - **Paso 1**: Verificar Redis cache (`agent_config:{agent_id}`)
    - **Paso 2**: Si no está en cache, llamar API Laravel (`GET /api/agents/{agent_id}/config`)
    - **Paso 3**: Almacenar en cache para futuras llamadas
5. Con la configuración, se construye y ejecuta el pipeline Pipecat

## Componentes

### Laravel (PHP)

#### 1. AgentRedisSyncService

**Archivo**: `app/Services/Tenant/Agent/Runner/AgentRedisSyncService.php`

Servicio responsable de sincronizar configuraciones de agentes a Redis.

**Métodos principales:**

- `sync(Agent $agent)`: Sincroniza un agente a Redis
- `remove(Agent $agent)`: Elimina un agente de Redis
- `findAgentIdBySlug(string $tenantId, string $slug)`: Busca agente por slug
- `isSynced(string $agentId)`: Verifica si un agente está sincronizado
- `touch(string $agentId)`: Refresca el TTL del cache

**Estructura en Redis:**

```
agent_config:{agent_id}  →  {agent_id, tenant_id, slug, config, synced_at, version}
agent:index:{tenant_id}  →  Set de agent_ids
agent:slugs:{tenant_id}  →  Hash {slug → agent_id}
```

#### 2. SyncAgentToRedisJob

**Archivo**: `app/Jobs/Tenant/Agent/SyncAgentToRedisJob.php`

Job de cola que sincroniza un agente a Redis de forma asíncrona.

**Cola**: `agent-sync`
**Reintentos**: 3 intentos con backoff [1, 5, 10] segundos

#### 3. CreateAgent / UpdateAgent Actions

**Archivos**:

- `app/Actions/Tenant/Agent/CreateAgent.php`
- `app/Actions/Tenant/Agent/UpdateAgent.php`

Estas actions ahora dispatchan `SyncAgentToRedisJob` automáticamente después de crear/actualizar un agente, asegurando que los cambios se reflejen inmediatamente en Redis.

#### 4. AgentConfigController

**Archivo**: `app/Http/Controllers/Tenant/API/Agent/AgentConfigController.php`

Controlador API que expone la configuración completa del agente para el servicio de runners.

**Endpoints:**

```
GET /api/agents/{agentId}/config         → Config por ID
GET /api/agents/by-slug/{agentSlug}/config  → Config por slug
```

#### 5. SyncAgentsToRedisCommand

**Archivo**: `app/Console/Commands/Tenant/Agent/SyncAgentsToRedisCommand.php`

Comando Artisan para sincronizar agentes existentes a Redis.

**Uso:**

```bash
# Sincronizar todos los agentes (async)
php artisan tenant:agents:sync-to-redis

# Sincronizar todos sincrónicamente
php artisan tenant:agents:sync-to-redis --sync

# Sincronizar agente específico
php artisan tenant:agents:sync-to-redis --agent=agent-id
php artisan tenant:agents:sync-to-redis --slug=agent-slug

# Usar cola específica
php artisan tenant:agents:sync-to-redis --queue=high
```

### Runners (Python)

#### 1. AgentResolutionService

**Archivo**: `app/services/agent_resolution_service.py`

Servicio central para resolver configuraciones de agentes.

**Métodos principales:**

- `resolve_agent(agent_id, tenant_id, force_refresh)`: Resuelve agente por ID
- `resolve_agent_by_slug(slug, tenant_id, workspace_slug)`: Resuelve por slug
- `invalidate_cache(agent_id)`: Invalida el cache de un agente
- `touch(agent_id)`: Refresca el TTL del cache

**Estrategia de resolución:**

1. Verificar Redis cache
2. Si no está en cache o `force_refresh=True`, llamar API Laravel
3. Cachear el resultado
4. Retornar la configuración

**Variables de entorno:**

```bash
BACKEND_URL=http://app.test  # URL base de Laravel
BACKEND_API_KEY=secret       # API key para autenticación (opcional)
```

#### 2. SIPCallHandler

**Archivo**: `app/services/sip/call_handler.py`

Manejador de llamadas SIP que usa `AgentResolutionService`.

**Método `_get_agent_config`:**

```python
async def _get_agent_config(self, agent_id: str, trunk_data: dict) -> Optional[AgentConfig]:
    tenant_id = trunk_data.get("tenant_id")
    return await agent_resolution_service.resolve_agent(
        agent_id=agent_id,
        tenant_id=tenant_id,
    )
```

## Configuración

### 1. Configurar Redis en Laravel

Asegúrate de que Redis esté configurado en `config/database.php`:

```php
'redis' => [
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
],
```

### 2. Configurar Variables de Entorno en Runners

En `services/runners/.env`:

```bash
# Backend Laravel URL
BACKEND_URL=http://app.test
BACKEND_API_KEY=your-secret-api-key  # Opcional, para autenticación

# Redis (compartido con Laravel)
REDIS_URL=redis://localhost:6379/0
```

### 3. Configurar Cola en Laravel

En `config/queue.php`, asegúrate de tener configurada la cola `agent-sync`:

```php
'queues' => [
    'agent-sync' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'agent-sync',
        'retry_after' => 90,
    ],
],
```

## Uso

### Sincronización Inicial

Para agentes existentes antes de implementar este sistema:

```bash
# Sincronizar todos los agentes
php artisan tenant:agents:sync-to-redis

# Procesar la cola
php artisan queue:work --queue=agent-sync
```

### Verificar Sincronización

```bash
# Conectar a Redis
redis-cli

# Verificar agente específico
GET agent_config:agent-id

# Ver todos los agentes indexados
SMEMBERS agent:index:tenant-id

# Ver mapeo de slugs
HGETALL agent:slugs:tenant-id
```

### Flujo de Trabajo Normal

1. **Crear agente**: El agente se sincroniza automáticamente a Redis
2. **Actualizar agente**: Los cambios se sincronizan automáticamente
3. **Llamada entrante**: El runner resuelve el agente desde cache o API
4. **Cache expira**: El runner vuelve a consultar la API automáticamente

## Monitoreo

### Logs en Laravel

```bash
# Ver logs de sincronización
tail -f storage/logs/laravel.log | grep "Agent synced"

# Ver logs de errores
tail -f storage/logs/laravel.log | grep "Failed to sync agent"
```

### Logs en Runners

```bash
# Ver logs de resolución de agentes
tail -f logs/runners.log | grep "Agent resolved"

# Ver logs de fallback a API
tail -f logs/runners.log | grep "resolved from API"
```

### Métricas

El sistema expone las siguientes métricas (si Prometheus está habilitado):

- `agent_resolution_cache_hits_total`: Cache hits
- `agent_resolution_cache_misses_total`: Cache misses
- `agent_resolution_api_calls_total`: Llamadas a API Laravel
- `agent_resolution_duration_seconds`: Tiempo de resolución

## Solución de Problemas

### Problema: "AgentConfig not found for agent_id"

**Causas posibles:**

1. El agente no existe en Laravel
2. El agente no está sincronizado a Redis
3. La API de Laravel no responde

**Solución:**

```bash
# 1. Verificar que el agente existe
php artisan tinker --execute 'echo App\Models\Tenant\Agent\Agent::find("agent-id")?->name;'

# 2. Sincronizar manualmente
php artisan tenant:agents:sync-to-redis --agent=agent-id --sync

# 3. Verificar Redis
redis-cli GET agent_config:agent-id
```

### Problema: "Timeout fetching agent config from API"

**Causas posibles:**

1. Laravel no responde
2. URL incorrecta en `BACKEND_URL`
3. Problemas de red entre runners y Laravel

**Solución:**

```bash
# Verificar conectividad
curl http://app.test/api/agents/agent-id/config

# Verificar URL en runners
grep BACKEND_URL services/runners/.env
```

### Problema: Cache no se actualiza después de modificar agente

**Solución:**

```bash
# Invalidar cache manualmente
redis-cli DEL agent_config:agent-id

# O sincronizar forzosamente
php artisan tenant:agents:sync-to-redis --agent=agent-id --sync
```

## Próximos Pasos

- [ ] Implementar webhook para invalidación de cache
- [ ] Agregar métricas de resolución a Prometheus
- [ ] Implementar circuit breaker para API fallback
- [ ] Agregar soporte para múltiples regiones/cache distribuido
