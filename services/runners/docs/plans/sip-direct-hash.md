# Plan: SIP Direct Hash

URIs SIP directas tipo `sip:direct.<hash>@sip.tito.ai` que permiten llegar a un recurso (agente, peer, cola, test) **sin provisionar trunks ni rutas por adelantado**. Complementa la plataforma SIP descrita en [`sip-platform.md`](./sip-platform.md): el flujo normal es `Trunk → Route → Agent`, y éste es un **short-circuit** para QA, demos y pruebas rápidas.

> **Componente análogo en voipbin:** `bin-direct-manager` — maneja `sip:direct.<hash>@sip.voipbin.net`.

---

## 1. Motivación

En el modelo de [`sip-platform.md`](./sip-platform.md), para llegar a un agente desde fuera hay que:

1. Crear una `SIPNetwork`.
2. Crear un `Trunk` (inbound/register).
3. Definir `TrunkRouteConfig` mapeando `extension → agent_id`.
4. Configurar la PBX del cliente.

Esto es correcto para producción, pero demasiado para probar un agente recién creado. El *direct hash* permite:

```
POST /api/v1/directs { network_id, resource_type: "agent", resource_id: "luna-soporte" }
→ { hash: "abc123def456" }

# Desde cualquier PBX o softphone:
sip:direct.abc123def456@sip.tito.ai
  └─► directamente al pipeline del agente "luna-soporte"
```

Sin trunk, sin ruta, sin configuración del lado del cliente.

---

## 2. Alineación con `sip-platform.md`

| Aspecto | Plataforma SIP | Direct Hash |
|---------|----------------|-------------|
| Scope | `network_id` | `network_id` (hereda) |
| Resolución | `TrunkService.resolve_inbound_call(workspace, ext)` | **Short-circuit previo** al resolver de trunks |
| Entidades destino | `peer`, `agent`, `queue`, `trunk` | Mismas: `agent`, `peer`, `queue`, `trunk`, `test` |
| Redis keys | `trunk:*`, `peer:*`, `agent:*`, `queue:*` | `direct:{hash}`, `direct:index:network:{network_id}` |
| Webhooks | `session.*`, `call.*` | `direct.created`, `direct.updated`, `direct.deleted` + los `session.*` del recurso destino |

**Regla de resolución (documentada en `sip-platform.md §4.5`):**

```python
# TrunkService.resolve_inbound_call
async def resolve_inbound_call(self, workspace_slug: str, extension: str) -> Optional[dict]:
    # 1. Short-circuit: direct hash
    if extension.startswith(DirectService.DIRECT_PREFIX):  # "direct."
        hash_value = extension[len(DirectService.DIRECT_PREFIX):]
        direct = await direct_service.get_by_hash(hash_value)
        if direct and direct["enabled"]:
            return _build_direct_resolution(direct)

    # 2. Flujo normal: buscar trunk + route
    # ... código existente
```

El direct hash **no necesita un trunk**: el AudioSocket del SIP Bridge cae en `resolve_inbound_call`, ve el prefijo y salta directo al recurso.

---

## 3. Esquema

### 3.1 `DirectResourceType`

Mapea 1:1 a las entidades de `sip-platform.md`, más `test` para QA.

```python
# app/schemas/direct.py

from enum import Enum
from typing import Optional
from pydantic import BaseModel, Field
import time


class DirectResourceType(str, Enum):
    AGENT = "agent"      # → AgentConfig pipeline
    PEER = "peer"        # → Ring peer SIP (extensión humana)
    QUEUE = "queue"      # → Enqueue y distribución
    TRUNK = "trunk"      # → Bridging a trunk externo
    TEST = "test"        # → Echo / tone test sin recurso real


class CreateDirectRequest(BaseModel):
    resource_type: DirectResourceType
    resource_id: Optional[str] = Field(
        None,
        description="ID del recurso destino. Requerido salvo para resource_type=test."
    )
    network_id: str = Field(..., description="Red SIP a la que pertenece el direct.")
    tenant_id: str
    workspace_slug: str
    enabled: bool = True
    ttl_seconds: Optional[int] = Field(
        None,
        description="Expiración opcional (útil para QA / demos temporales)."
    )


class DirectData(BaseModel):
    hash: str = Field(..., description="Hash de 12 caracteres.", examples=["abc123def456"])
    resource_type: DirectResourceType
    resource_id: Optional[str]
    network_id: str
    tenant_id: str
    workspace_slug: str
    enabled: bool = True
    expires_at: Optional[float] = None
    created_at: float = Field(default_factory=time.time)
    updated_at: float = Field(default_factory=time.time)
```

### 3.2 `DirectService`

```python
# app/services/direct_service.py

class DirectService:
    DIRECT_PREFIX = "direct."
    HASH_LENGTH = 12

    def __init__(self):
        self._redis = session_manager._redis

    async def create_direct(self, request: CreateDirectRequest) -> DirectData:
        # 1. Validar network_id existe y el resource_id pertenece a la red
        #    (excepto resource_type=test)
        # 2. Validar resource_id:
        #       agent  → agent_service.get_agent(resource_id)
        #       peer   → peer_service.get_peer(resource_id)
        #       queue  → queue_service.get_queue(resource_id)
        #       trunk  → trunk_service.get_trunk(resource_id)
        #       test   → resource_id puede ser None
        # 3. Generar hash único: secrets.token_urlsafe(9)[:12]
        # 4. Persistir:
        #       SET "direct:{hash}" → JSON (con PX si ttl_seconds)
        #       SADD "direct:index:network:{network_id}" → hash
        # 5. Emit webhook direct.created

    async def get_by_hash(self, hash: str) -> Optional[DirectData]: ...
    async def list_directs(self, network_id: str) -> list[DirectData]: ...
    async def update_direct(self, hash: str, updates: dict) -> Optional[DirectData]: ...
    async def delete_direct(self, hash: str) -> bool: ...
    async def regenerate_hash(self, old_hash: str) -> DirectData: ...

direct_service = DirectService()
```

**Redis keys:**

| Key | Tipo | Descripción |
|---|---|---|
| `direct:{hash}` | STRING (JSON) | Datos del direct. Con `PX` si tiene `ttl_seconds`. |
| `direct:index:network:{network_id}` | SET | Hashes asociados a la red. |

---

## 4. Integración con el resolver

### 4.1 `TrunkService.resolve_inbound_call` (modificado)

El método ya definido en `sip-platform.md §4.5` gana un short-circuit al inicio:

```python
async def resolve_inbound_call(
    self, workspace_slug: str, extension: str
) -> Optional[dict]:
    # Short-circuit: direct hash
    if extension.startswith(DirectService.DIRECT_PREFIX):
        hash_value = extension[len(DirectService.DIRECT_PREFIX):]
        direct = await direct_service.get_by_hash(hash_value)
        if direct and direct.enabled:
            return {
                "trunk_id": None,
                "direct": direct.model_dump(),
                "agent_id": direct.resource_id if direct.resource_type == "agent" else None,
                "resource_type": direct.resource_type,
                "resource_id": direct.resource_id,
                "network_id": direct.network_id,
            }

    # Flujo normal: buscar trunk + route
    # ... (definido en sip-platform.md)
```

### 4.2 `SIPCallHandler._resolve_call` (modificado)

Cuando la extensión es un direct hash, el handler salta directamente al dispatch por `resource_type`:

```python
async def _resolve_call(self, channel_uuid, called_extension, conn):
    if not called_extension:
        return None

    # Direct hash (cross-workspace: se resuelve por hash global)
    if called_extension.startswith(DirectService.DIRECT_PREFIX):
        hash_value = called_extension[len(DirectService.DIRECT_PREFIX):]
        direct = await direct_service.get_by_hash(hash_value)
        if direct and direct.enabled:
            return await self._dispatch_direct(direct, conn)

    # Flujo normal por workspace
    return await self._dispatch_by_trunk(called_extension, conn)

async def _dispatch_direct(self, direct, conn):
    match direct.resource_type:
        case "agent":
            return {"agent_id": direct.resource_id, "network_id": direct.network_id}
        case "peer":
            await peer_service.ring_peer(direct.resource_id)
            return {"peer_id": direct.resource_id, "network_id": direct.network_id}
        case "queue":
            return await queue_service.add_call_to_queue(direct.resource_id, {...})
        case "trunk":
            return {"bridge_to_trunk": direct.resource_id}
        case "test":
            return {"mode": "echo_test"}
```

---

## 5. API REST

```
POST   /api/v1/directs                                    → create_direct
GET    /api/v1/directs?network_id=net_...                 → list_directs
GET    /api/v1/directs/{hash}                             → get_direct
PATCH  /api/v1/directs/{hash}                             → update_direct  (enable/disable, TTL)
DELETE /api/v1/directs/{hash}                             → delete_direct
POST   /api/v1/directs/{hash}/regenerate                  → regenerate_hash
```

Errores:
- `404` — hash no existe o expirado.
- `422` — `resource_id` inválido para el `resource_type`.
- `409` — hash duplicado (muy improbable con 12 chars, pero se retry).

Registrar router en `app/api/v1/__init__.py` con tag `"SIP Directs"` y agregarlo a `openapi_tags` en `main.py`.

---

## 6. Flujo de Llamada

```
INVITE sip:direct.abc123def456@sip.tito.ai
    ↓
Asterisk (dialplan: sip.tito.ai)
    ↓
AudioSocket → SIPCallHandler._on_audiosocket_connection()
    ↓
SIPCallHandler._resolve_call()
    ↓
Prefijo "direct." detectado → direct_service.get_by_hash("abc123def456")
    ↓
DirectData { resource_type, resource_id, network_id, ... }
    ↓
_dispatch_direct():
    resource_type == "agent"  → fetch AgentConfig → Pipeline (STT→LLM→TTS)
    resource_type == "peer"   → peer_service.ring_peer
    resource_type == "queue"  → queue_service.add_call_to_queue
    resource_type == "test"   → echo loop / tone
```

---

## 7. Ejemplos de Uso

### 7.1 Exponer un agente sin trunk (QA)

```bash
POST /api/v1/directs
{
  "resource_type": "agent",
  "resource_id": "luna-soporte",
  "network_id": "net_alloy_main",
  "tenant_id": "tenant-abc",
  "workspace_slug": "alloy-finance"
}

# → 201
{
  "hash": "abc123def456",
  "resource_type": "agent",
  "resource_id": "luna-soporte",
  "network_id": "net_alloy_main",
  "enabled": true,
  "expires_at": null
}

# Desde cualquier softphone:
sip:direct.abc123def456@sip.tito.ai
# → Conecta directo con el pipeline de "luna-soporte"
```

### 7.2 Demo temporal (24h)

```bash
POST /api/v1/directs
{
  "resource_type": "agent",
  "resource_id": "ventas-bot",
  "network_id": "net_alloy_main",
  "tenant_id": "tenant-abc",
  "workspace_slug": "alloy-finance",
  "ttl_seconds": 86400
}
# → el hash expira automáticamente tras 24h
```

### 7.3 Test sin recurso (echo)

```bash
POST /api/v1/directs
{
  "resource_type": "test",
  "network_id": "net_alloy_main",
  "tenant_id": "tenant-abc",
  "workspace_slug": "alloy-finance"
}

# Llamar → bot devuelve el audio recibido (loopback), útil para validar el SIP Bridge sin tocar LLM/TTS.
```

---

## 8. Webhooks

| Evento | Trigger |
|--------|---------|
| `direct.created`  | `POST /directs` |
| `direct.updated`  | `PATCH /directs/{hash}` |
| `direct.deleted`  | `DELETE /directs/{hash}` o expiración por TTL |
| `session.started` | La llamada al hash dispara un pipeline (reusa el webhook de `sip-platform.md`) |
| `session.ended`   | Igual que arriba |

---

## 9. Implementación por Pasos

| Paso | Archivo | Acción | Dependencias |
|------|---------|--------|--------------|
| 1 | `app/schemas/direct.py` | Crear | — |
| 2 | `app/services/direct_service.py` | Crear | Paso 1 |
| 3 | `app/services/trunk_service.py` | Añadir short-circuit en `resolve_inbound_call` | Paso 2 + `sip-platform.md §4.5` |
| 4 | `app/services/sip/call_handler.py` | Añadir `_dispatch_direct` | Paso 2 |
| 5 | `app/api/v1/directs.py` | Crear endpoints REST | Pasos 1-2 |
| 6 | `app/api/v1/__init__.py` + `main.py` | Registrar router + tag OpenAPI | Paso 5 |
| 7 | `tests/test_direct_service.py` | Unit + integration | Pasos 1-6 |

> **Dependencia dura:** este plan requiere que la **Fase 4 (Trunks)** de `sip-platform.md` esté al menos con `TrunkService` + `SIPCallHandler` operativos. Si el SIP Bridge (Fase 6) aún no está, el resolver funciona en pruebas pero no llega audio real.

---

## 10. Consideraciones

- **Seguridad:** rate limiting por `network_id` en `POST /directs` para evitar abuso. Permiso `directs.manage` a nivel de tenant.
- **Auth cross-workspace:** el resolver es global (el hash se busca sin workspace). El `network_id` del direct se usa para scopear quién puede crearlo/borrarlo, pero la llamada entrante no valida workspace — por diseño, para que un QA pueda llamar desde cualquier PBX.
- **Entropía del hash:** 12 chars base64url ≈ 72 bits. Suficiente contra enumeration bruto para la capa pública; aun así, combinarlo con rate-limiting del dominio SIP.
- **TTL:** el TTL se respeta vía `PX` de Redis; el cleanup de `direct:index:network:{network_id}` se hace lazy cuando `get_by_hash` devuelve `None`.
- **Observabilidad:** registrar `direct_id` y `resource_type` en las métricas de sesión para distinguir tráfico de demos vs producción.
