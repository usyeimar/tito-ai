## 4. Alta Disponibilidad y Réplicas

### 4.1 Runner Cluster

```yaml
# docker-compose.runners.yml

services:
  runner:
    image: titoai/runners:latest
    deploy:
      replicas: 4
      resources:
        limits:
          cpus: '2'
          memory: 4G
        reservations:
          cpus: '1'
          memory: 2G
    environment:
      - REDIS_URL=redis://redis-cluster:6379
      - LIVEKIT_URL=wss://livekit-cluster
      - RUNNER_ID=${HOSTNAME}
      - LOG_LEVEL=INFO
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8000/health"]
      interval: 30s
      timeout: 10s
      retries: 3
    volumes:
      - ./models:/app/models
      - ./config:/app/config
    networks:
      - runners-network

networks:
  runners-network:
    driver: bridge
```

```python
# app/services/session_distribution.py

class SessionDistributor:
    """
    Distribuye sesiones entre réplicas de runners.
    """
    
    def __init__(self, redis: Redis):
        self._redis = redis
    
    async def register_runner(self, runner_id: str):
        """Runner se registra al iniciar."""
        await self._redis.sadd("runners:active", runner_id)
        await self._redis.set(
            f"runner:{runner_id}:last_heartbeat", 
            time.time()
        )
        await self._redis.expire(f"runner:{runner_id}:last_heartbeat", 60)
    
    async def unregister_runner(self, runner_id: str):
        """Runner se desregistra al cerrar."""
        await self._redis.srem("runners:active", runner_id)
    
    async def heartbeat(self, runner_id: str, session_count: int):
        """Actualiza estado del runner."""
        await self._redis.set(
            f"runner:{runner_id}:session_count", 
            session_count
        )
        await self._redis.set(
            f"runner:{runner_id}:last_heartbeat",
            time.time()
        )
    
    async def get_available_runner(self) -> str:
        """
        Retorna el runner con menos carga.
        """
        runners = await self._redis.smembers("runners:active")
        
        if not runners:
            raise RuntimeError("No runners available")
        
        min_load = float('inf')
        best_runner = None
        
        for runner in runners:
            # Verificar que está vivo (heartbeat reciente)
            last_hb = await self._redis.get(f"runner:{runner}:last_heartbeat")
            if not last_hb:
                continue
            
            if time.time() - float(last_hb) > 30:
                continue  # Runner muerto
            
            # Obtener carga
            count = await self._redis.get(f"runner:{runner}:session_count")
            load = int(count or 0)
            
            if load < min_load:
                min_load = load
                best_runner = runner
        
        if not best_runner:
            raise RuntimeError("No healthy runners available")
        
        return best_runner
    
    async def migrate_session(self, session_id: str, from_runner: str):
        """
        Migra una sesión a otro runner (por failover).
        """
        # 1. Obtener datos de la sesión
        session_data = await self._redis.get(f"session:{session_id}")
        
        # 2. Obtener nuevo runner
        new_runner = await self.get_available_runner()
        
        # 3. Guardar en nuevo runner
        await self._redis.set(
            f"session:{new_runner}:{session_id}",
            session_data
        )
        
        # 4. Notificar al nuevo runner
        await self._redis.publish(
            f"runner:{new_runner}:session_migrate",
            session_id
        )
```

### 4.2 Kamailio Cluster

```ini
# kamailio.cfg - Configuración de clustering

# Habilitar clusterer module
loadmodule "clusterer.so"

modparam("clusterer", "cluster_id", 1)
modparam("clusterer", "db_url", "mysql://kamailio:password@localhost/kamailio")
modparam("clusterer", "sharing_tag", "node=1, priority=10")

# Sincronización de estado
modparam("clusterer", "sync", 1)

# Listen para cluster
listen = udp:10.0.0.10:5060
listen = tcp:10.0.0.10:5060

# Lua script para integración
loadmodule "lua.so"
modparam("lua", "lua_script", "/etc/kamailio/tenant_lookup.lua")

# RTPEngine distribuido
loadmodule "rtpengine.so"
modparam("rtpengine", "rtpengine_sock", "udp:rtpengine-1:12222 udp:rtpengine-2:12222")
```

```lua
-- kamailio/tenant_lookup.lua

-- Conexión a Redis
local redis = require "redis"
local red = nil

local function get_redis()
    if not red then
        red = redis.connect("redis://" .. REDIS_HOST .. ":6379")
    end
    return red
end

-- Lookup tenant por dominio
function lookup_tenant(domain)
    local r = get_redis()
    
    -- Buscar tenant por dominio
    local tenant_id = r:get("domain:" .. domain .. ":tenant_id")
    if not tenant_id then
        return nil
    end
    
    -- Obtener config del tenant
    local config_json = r:get("tenant:" .. tenant_id .. ":config")
    if not config_json then
        return nil
    end
    
    return cjson.decode(config_json)
end

-- Resolver extensión a agente
function resolve_extension(tenant_id, extension)
    local r = get_redis()
    
    local key = string.format("tenant:%s:extension:%s", tenant_id, extension)
    local result = r:get(key)
    
    if result then
        return cjson.decode(result)
    end
    return nil
end

-- Verificar rate limit
function check_rate_limit(tenant_id)
    local r = get_redis()
    
    local key = string.format("ratelimit:%s:%s", tenant_id, os.date("%Y%m%d%H%M"))
    local count = r:get(key)
    
    if count and tonumber(count) > 1000 then
        return false  -- Rate limit excedido
    end
    
    return true
end
```

### 4.3 Redis Cluster

```yaml
# docker-compose.redis-cluster.yml

services:
  redis-1:
    image: redis:7-alpine
    command: >
      redis-server 
      --cluster-enabled yes 
      --cluster-config-file nodes.conf 
      --cluster-node-timeout 5000
      --appendonly yes
    ports:
      - "6379:6379"
    volumes:
      - redis1-data:/data
    networks:
      - redis-cluster

  redis-2:
    image: redis:7-alpine
    command: >
      redis-server 
      --cluster-enabled yes 
      --cluster-config-file nodes.conf 
      --cluster-node-timeout 5000
      --appendonly yes
    ports:
      - "6380:6379"
    volumes:
      - redis2-data:/data
    networks:
      - redis-cluster

  redis-3:
    image: redis:7-alpine
    command: >
      redis-server 
      --cluster-enabled yes 
      --cluster-config-file nodes.conf 
      --cluster-node-timeout 5000
      --appendonly yes
    ports:
      - "6381:6379"
    volumes:
      - redis3-data:/data
    networks:
      - redis-cluster

  redis-4:
    image: redis:7-alpine
    command: >
      redis-server 
      --cluster-enabled yes 
      --cluster-config-file nodes.conf 
      --cluster-node-timeout 5000
      --appendonly yes
    ports:
      - "6382:6379"
    volumes:
      - redis4-data:/data
    networks:
      - redis-cluster

  redis-5:
    image: redis:7-alpine
    command: >
      redis-server 
      --cluster-enabled yes 
      --cluster-config-file nodes.conf 
      --cluster-node-timeout 5000
      --appendonly yes
    ports:
      - "6383:6379"
    volumes:
      - redis5-data:/data
    networks:
      - redis-cluster

  redis-6:
    image: redis:7-alpine
    command: >
      redis-server 
      --cluster-enabled yes 
      --cluster-config-file nodes.conf 
      --cluster-node-timeout 5000
      --appendonly yes
    ports:
      - "6384:6379"
    volumes:
      - redis6-data:/data
    networks:
      - redis-cluster

networks:
  redis-cluster:
    driver: bridge

volumes:
  redis1-data:
  redis2-data:
  redis3-data:
  redis4-data:
  redis5-data:
  redis6-data:
```

**Inicialización del cluster:**
```bash
redis-cli --cluster create \
  127.0.0.1:6379 \
  127.0.0.1:6380 \
  127.0.0.1:6381 \
  127.0.0.1:6382 \
  127.0.0.1:6383 \
  127.0.0.1:6384 \
  --cluster-replicas 1
```

### 4.4 LiveKit Cluster

```yaml
# docker-compose.livekit.yml

services:
  livekit:
    image: livekit/livekit-server
    command: --config /config/config.yaml
    volumes:
      - ./config:/config
      - ./keys:/keys
    ports:
      - "7880:7880"  # HTTP
      - "7881:7881"  # WebSocket
      - "7882:7882"  # RTMP
      - "7888:7888"  # Metrics
    deploy:
      replicas: 3
      placement:
        constraints:
          - node.role == worker
    environment:
      - LIVEKIT_KEYS=${LIVEKIT_API_KEY}:${LIVEKIT_API_SECRET}
    networks:
      - livekit-network

  livekit-turn:
    image: livekit/livekit-turn-server
    ports:
      - "3478:3478/udp"
      - "3478:3478/tcp"
    environment:
      - TURN_SECRET=${TURN_SECRET}
    networks:
      - livekit-network

networks:
  livekit-network:
    driver: bridge
```

```yaml
# config.yaml para LiveKit
---
port: 7880
rtc:
  port_range_start: 50000
  port_range_end: 60000
  use_external_ip: true
  tcp_port: 7881
  udp_port: 7882

keys:
  ${LIVEKIT_API_KEY}: ${LIVEKIT_API_SECRET}

room:
  auto_create: true
  empty_timeout: 300
  departure_timeout: 60

turn:
  enabled: true
  secret: ${TURN_SECRET}
  port: 3478

logging:
  level: info
  pion_level: warn

prometheus:
  port: 7888
  path: /metrics
```

### 4.5 Asterisk Pool

```yaml
# docker-compose.asterisk-pool.yml

services:
  asterisk-group-a:
    image: titoai/asterisk:latest
    environment:
      - TENANT_GROUP=group-a
      - ASTERISK_CONTEXT=tenant-group-a
      - MAX_CHANNELS=100
    networks:
      - asterisk-internal
    deploy:
      replicas: 2

  asterisk-group-b:
    image: titoai/asterisk:latest
    environment:
      - TENANT_GROUP=group-b
      - ASTERISK_CONTEXT=tenant-group-b
      - MAX_CHANNELS=100
    networks:
      - asterisk-internal
    deploy:
      replicas: 2

networks:
  asterisk-internal:
    driver: bridge
```

---

