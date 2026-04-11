## 3. Modelo Multi-Tenant

### 3.1 Estructura de Datos en Redis

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                    ESTRUCTURA DE KEYS EN REDIS                                       │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                      │
│  # KEYS GLOBALES                                                                     │
│  ├── domain:{domain}:tenant_id              → tenant_id (lookup por dominio)      │
│  ├── tenant:{tenant_id}:config              → JSON de configuración               │
│  ├── tenant:{tenant_id}:networks            → SET de network_ids                  │
│  ├── tenant:{tenant_id}:quotas              → JSON de límites y uso               │
│  └── tenant:index                            → SET de todos los tenant_ids       │
│                                                                                      │
│  # KEYS POR TENANT                                                                   │
│  ├── tenant:{tenant_id}:network:{network_id}:peers     → SET de peer_ids          │
│  ├── tenant:{tenant_id}:network:{network_id}:agents    → SET de agent_ids        │
│  ├── tenant:{tenant_id}:network:{network_id}:queues    → SET de queue_ids         │
│  ├── tenant:{tenant_id}:network:{network_id}:trunks    → SET de trunk_ids         │
│  └── tenant:{tenant_id}:network:{network_id}:dialplan → JSON del dialplan         │
│                                                                                      │
│  # KEYS POR NETWORK                                                                 │
│  ├── network:{network_id}:config            → JSON de SIP Network                 │
│  ├── network:{network_id}:peers             → SET de peer_ids                     │
│  ├── network:{network_id}:agents            → SET de agent_ids                     │
│  ├── network:{network_id}:queues            → SET de queue_ids                    │
│  ├── network:{network_id}:trunks            → SET de trunk_ids                    │
│  ├── network:{network_id}:dialplan          → JSON completo                        │
│  └── network:{network_id}:routes            → SORTED SET por prioridad            │
│                                                                                      │
│  # KEYS POR PEER                                                                     │
│  ├── peer:{peer_id}                         → JSON completo del peer               │
│  ├── peer:{peer_id}:register                → JSON de estado de registro          │
│  └── peer:by_extension:{network_id}:{ext}  → peer_id (lookup rápido)            │
│                                                                                      │
│  # KEYS POR AGENTE                                                                    │
│  ├── agent:{agent_id}                       → JSON del agente                     │
│  ├── agent:{agent_id}:session               → Sesión activa (si existe)          │
│  └── agent:by_queue:{queue_id}              → SET de agent_ids en cola           │
│                                                                                      │
│  # KEYS POR COLA                                                                     │
│  ├── queue:{queue_id}                       → JSON de la cola                    │
│  ├── queue:{queue_id}:members               → SET de agent_ids con penalty        │
│  ├── queue:{queue_id}:calls                 → LIST de call_ids en cola            │
│  └── queue:{queue_id}:waiting               → SORTED SET (wait_time, call_id)     │
│                                                                                      │
│  # KEYS POR LLAMADA                                                                   │
│  ├── call:{call_id}                         → JSON de estado de llamada           │
│  ├── call:by_network:{network_id}           → SET de call_ids activos            │
│  └── call:by_agent:{agent_id}               → SET de call_ids del agente          │
│                                                                                      │
│  # KEYS DE SESIÓN DE RUNNER                                                         │
│  ├── session:{session_id}                  → JSON de sesión Runner               │
│  ├── session:by_runner:{runner_id}         → SET de session_ids                  │
│  └── runner:{runner_id}:heartbeat           → Timestamp (TTL 30s)                │
│                                                                                      │
│  # KEYS DE LIVEKIT                                                                   │
│  ├── livekit:room:{room_id}:config          → JSON de config de sala              │
│  ├── livekit:tenant:{tenant_id}:credentials → API key/secret                      │
│  └── livekit:room:{room_id}:participants    → SET de participantes               │
│                                                                                      │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Aislamiento entre Tenants

```python
# app/services/tenant_isolation.py

class TenantIsolation:
    """
    Garantiza aislamiento completo entre tenants.
    """
    
    async def validate_tenant_access(
        self, 
        requesting_tenant_id: str, 
        resource_tenant_id: str
    ) -> bool:
        """
        Un tenant no puede acceder a recursos de otro tenant.
        """
        return requesting_tenant_id == resource_tenant_id
    
    async def validate_network_access(
        self,
        requesting_tenant_id: str,
        network: SIPNetwork
    ) -> bool:
        """
        Valida que la red pertenezca al tenant solicitante.
        """
        return network.tenant_id == requesting_tenant_id
    
    async def get_tenant_quota(self, tenant_id: str) -> QuotaInfo:
        """
        Obtiene información de cuota del tenant.
        """
        # Redis: tenant:{tenant_id}:quotas
        # Retorna: max_concurrent_calls, max_call_per_second, etc.
    
    async def check_quota(
        self, 
        tenant_id: str, 
        resource: str, 
        amount: int = 1
    ) -> bool:
        """
        Verifica si el tenant puede usar el recurso.
        Incrementa el uso si hay espacio.
        """
        quota = await self.get_tenant_quotas(tenant_id)
        
        if resource == "concurrent_calls":
            current = await self._redis.get(f"quota:{tenant_id}:calls")
            return int(current or 0) < quota.max_concurrent_calls
        
        if resource == "calls_per_second":
            # Usar sliding window counter
            pass
    
    async def release_quota(
        self, 
        tenant_id: str, 
        resource: str, 
        amount: int = 1
    ):
        """Libera recursos usados."""
```

### 3.3 Sharding de Tenants

```python
# app/services/tenant_sharding.py

class TenantSharding:
    """
    Agrupa tenants en shards para distribuir carga.
    """
    
    def __init__(self, num_shards: int = 4):
        self._num_shards = num_shards
    
    def get_shard(self, tenant_id: str) -> int:
        """Determina el shard de un tenant."""
        # Hash consistente
        return hash(tenant_id) % self._num_shards
    
    def get_shard_config(self, shard: int) -> ShardConfig:
        """
        Obtiene la configuración de un shard:
        - Redis endpoints
        - Asterisk endpoints
        - Runner replicas
        """
        # Redis: shard:{shard}:config
    
    async def migrate_tenant(self, tenant_id: str, target_shard: int):
        """
        Migra un tenant a otro shard.
        Usado para rebalanceo de carga.
        """
```

---

