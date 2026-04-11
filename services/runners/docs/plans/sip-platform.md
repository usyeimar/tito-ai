# Plan: Plataforma SIP Multi-Tenant

Diseño unificado del subsistema SIP de Tito AI: redes virtuales por tenant, peers/agentes/colas/dialplan y **SIP Trunks** (inbound / register / outbound) como puente con el mundo externo.

Este documento consolida y reemplaza a:
- `sip-network-multi-tenant.md` (visión de alto nivel de la red SIP).
- `sip-trunks-api.md` (API detallada de Customer-Owned Trunks).

---

## 1. Visión General

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        TITO.AI SIP NETWORK PLATFORM                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    TENANT: empresa-1                                  │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                 │   │
│  │  │ SIP Network │  │   Peers     │  │   Agents    │                 │   │
│  │  │ 192.168.1.0 │  │ Ext 100-200 │  │  IA Agents  │                 │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘                 │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                 │   │
│  │  │   Queues    │  │   Trunks    │  │  Dialplan   │                 │   │
│  │  │ sales, supp │  │ in/reg/out  │  │   routing   │                 │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘                 │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    TENANT: empresa-2                                  │   │
│  │  (misma estructura, datos aislados)                                   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Contexto y motivación

Actualmente el modelo SIP en `deployment_service.py` provisiona **1 SIP URI por agente** (`sip:agent-id@workspace.sip.tito.ai`). Esto fuerza al cliente a crear un trunk por cada agente en su PBX.

El nuevo modelo introduce una **red SIP virtual por tenant** con las siguientes piezas:

- **SIP Network** — red virtual aislada con su dominio (`{slug}.sip.tito.ai`), codecs, límites y CIDR interno.
- **Peers** — extensiones/dispositivos SIP (usuarios humanos, softphones, trunks, DECT, etc.).
- **Agents** — IA o humanos asociados a la red.
- **Queues** — colas de atención con estrategias de distribución.
- **Trunks** — conexiones SIP con PBXes externas o carriers (inbound / register / outbound).
- **Dialplan** — reglas de enrutamiento para números marcados.

---

## 2. Modelo Conceptual

### 2.1 SIP Network

Red virtual de un tenant. Encapsula todo: peers, agents, queues, trunks y dialplan pertenecen a un `network_id`.

### 2.2 Peer

Extensión o dispositivo SIP (usuario humano, softphone, trunk, analógico, DECT).

### 2.3 Agent

Entidad que atiende llamadas. Puede ser:

- `ai_voice` — pipeline Pipecat con LLM/STT/TTS.
- `human` — un peer humano asociado.
- `hybrid` — combinación (escalado IA → humano).

### 2.4 Queue

Cola de llamadas con estrategia de distribución (`ring_all`, `round_robin`, `skills_based`, etc.), colas de overflow, prioridades y métricas en vivo.

### 2.5 Trunk (3 modos)

La pieza que conecta la red SIP con el mundo exterior. Tres modos de operación:

#### Modo `inbound` (el cliente nos llama)

```
Cliente configura en su PBX:
  Trunk: alloy-finance.sip.tito.ai (1 trunk, N extensiones)
  Ext 100 → agente "luna-soporte"
  Ext 200 → agente "ventas-bot"
  Ext 300 → agente "cobranzas-ai"

Flujo:
  PBX Cliente ──SIP INVITE (ext 100)──► Tu Asterisk → Pipeline "luna-soporte"
```

- **Quién configura:** admin de la PBX del cliente (trunk + rutas salientes).
- **Caso de uso:** call centers, PBX enterprise, integraciones avanzadas.
- **Ventaja:** 1 trunk → N agentes por extensión.

#### Modo `register` (nos registramos en la PBX del cliente)

```
Tu Asterisk se registra en la PBX del cliente:
  Register → pbx.alloy.com:5060 como extensión 100

Flujo:
  Tu Asterisk ──SIP REGISTER──► PBX Cliente (ext 100 = tu agente)
  Alguien marca ext 100 → PBX Cliente ──INVITE──► Tu Asterisk → Pipeline
```

- **Quién configura:** solo tú (el cliente da credenciales de extensión).
- **Caso de uso:** PyMEs, PBX cloud (3CX, Grandstream, FreePBX), setup rápido.
- **Ventaja:** el cliente no necesita saber configurar trunks SIP.
- **Nota:** 1 registro = 1 agente. Para N agentes, N registros.

#### Modo `outbound` (nosotros originamos la llamada)

```
Tu sistema inicia la llamada al usuario:
  Asterisk ──SIP INVITE──► Carrier (Twilio/VoIP) ──► PSTN ──► Teléfono usuario

Flujo:
  POST /api/v1/trunks/{trunk_id}/calls
  → Tu Asterisk origina llamada via trunk outbound
  → Usuario contesta → AudioSocket → Pipeline agente IA
```

- **Quién inicia:** tu sistema (vía API).
- **Caso de uso:** campañas de cobranza, recordatorios de citas, encuestas, verificación de identidad.
- **Requisito:** un carrier SIP con capacidad de originar llamadas (Twilio SIP Trunk, VoIP.ms, etc.).
- **Nota:** el `caller_id` lo define el trunk (o se sobrescribe por llamada).

#### Tabla comparativa de modos

| Aspecto | `inbound` | `register` | `outbound` |
|---------|-----------|------------|------------|
| Dirección SIP | Cliente → Tu Asterisk | Tu Asterisk → PBX cliente (registro) | Tu Asterisk → Carrier → Teléfono |
| Quién inicia la llamada | Usuario externo | Usuario externo | **Tu sistema** (vía API) |
| Config del cliente | Crear trunk + rutas | Solo credenciales de ext | Credenciales del carrier |
| Complejidad cliente | Media-alta | **Baja** | Baja |
| N agentes | 1 trunk → N extensiones | 1 registro = 1 agente | Se especifica por llamada |
| NAT/Firewall | Cliente debe alcanzar tu IP | Tú inicias la conexión | Tú inicias la conexión |
| Monitoreo extra | — | `registration_status` | `call_status` |
| Caller ID | Lo define el que llama | — | Configurable en el trunk |

### 2.6 Dialplan / Routes

Reglas de enrutamiento (`peer` | `queue` | `agent` | `trunk` | `ivr` | `external`) con prioridad, matching por regex, restricciones de horario y caller ID.

---

## 3. Esquemas Pydantic

### 3.1 `SIPNetwork`

```python
# app/schemas/sip_network.py

from pydantic import BaseModel, Field
from typing import Optional, List
from enum import Enum
import time

class NetworkStatus(str, Enum):
    ACTIVE = "active"
    SUSPENDED = "suspended"
    TERMINATED = "terminated"

class SIPNetwork(BaseModel):
    network_id: str = Field(..., description="ID único de la red")
    tenant_id: str = Field(..., description="ID del tenant/organización")
    name: str
    slug: str = Field(..., description="Slug único para URLs")

    cidr: str = Field(..., example="192.168.1.0/24")
    gateway: str = Field(..., example="192.168.1.1")

    domain: str = Field(..., example="empresa1.sip.tito.ai")
    outbound_proxy: Optional[str] = None

    max_peers: int = 100
    max_concurrent_calls: int = 50

    codec_preference: List[str] = Field(default_factory=lambda: ["opus", "ulaw", "alaw"])
    transport: str = "udp"

    status: NetworkStatus = NetworkStatus.ACTIVE
    created_at: float = Field(default_factory=time.time)
    updated_at: float = Field(default_factory=time.time)
```

### 3.2 `Peer`

```python
# app/schemas/peer.py

class PeerType(str, Enum):
    SIP = "sip"
    IAX = "iax"
    TRUNK = "trunk"
    ANALOG = "analog"
    DECT = "dect"
    SOFTPHONE = "softphone"

class PeerStatus(str, Enum):
    ONLINE = "online"
    OFFLINE = "offline"
    BUSY = "busy"
    UNAVAILABLE = "unavailable"

class Peer(BaseModel):
    peer_id: str
    network_id: str
    extension: str = Field(..., example="100")

    peer_type: PeerType = PeerType.SIP
    name: str

    username: str
    secret: str = Field(..., description="Password (enmascarado en responses)")

    caller_id: Optional[str] = None
    context: str = "default"

    codec_enabled: List[str] = Field(default_factory=list)
    voicemail_enabled: bool = True
    call_forward_enabled: bool = False

    status: PeerStatus = PeerStatus.OFFLINE
    last_register: Optional[float] = None
    current_channel: Optional[str] = None

    tags: List[str] = Field(default_factory=list)
    metadata: dict = Field(default_factory=dict)
```

### 3.3 `Agent`

```python
# app/schemas/agent.py (extender existente)

class AgentType(str, Enum):
    AI_VOICE = "ai_voice"
    HUMAN = "human"
    HYBRID = "hybrid"

class AgentMode(str, Enum):
    INBOUND = "inbound"
    OUTBOUND = "outbound"
    BOTH = "both"

class Agent(BaseModel):
    agent_id: str
    network_id: str
    name: str
    agent_type: AgentType = AgentType.AI_VOICE
    mode: AgentMode = AgentMode.BOTH

    ai_config_id: Optional[str] = None
    greeting_audio: Optional[str] = None
    language: str = "es-CO"

    associated_peer_id: Optional[str] = None

    max_concurrent_calls: int = 1
    timeout_seconds: int = 30
    wrap_up_time_seconds: int = 10

    skills: List[str] = Field(default_factory=list)
    queues: List[str] = Field(default_factory=list)

    status: str = "available"  # available, on_call, away, offline
    current_calls: int = 0
```

### 3.4 `Queue`

```python
# app/schemas/queue.py

class QueueStrategy(str, Enum):
    RING_ALL = "ring_all"
    LEAST_RECENT = "least_recent"
    FEWEST_CALLS = "fewest_calls"
    ROUND_ROBIN = "round_robin"
    WEIGHTED = "weighted"
    SKILLS_BASED = "skills_based"

class QueueMember(BaseModel):
    agent_id: str
    penalty: int = 0
    paused: bool = False

class Queue(BaseModel):
    queue_id: str
    network_id: str
    name: str

    strategy: QueueStrategy = QueueStrategy.RING_ALL
    priority: int = 0

    timeout: int = 30
    retry_time: int = 5
    max_wait_time: int = 300

    announce_position: bool = True
    announce_hold_time: bool = True
    music_on_hold: str = "default"

    members: List[QueueMember] = Field(default_factory=list)

    overflow_queue_id: Optional[str] = None
    overflow_threshold: int = 10

    status: str = "active"
    calls_waiting: int = 0
    longest_wait: int = 0
```

### 3.5 `Trunk` (todos los modos)

```python
# app/schemas/trunks.py

from typing import Optional, Literal, Dict, Any, List
from pydantic import BaseModel, Field, ConfigDict
import time


# ── Sub-configs por modo ──

class TrunkRouteConfig(BaseModel):
    """Mapeo de extensión/DID a un agente (solo mode=inbound)."""
    extension: str = Field(..., examples=["100", "+573001234567"])
    agent_id: str = Field(..., examples=["luna-soporte"])
    priority: int = 0
    enabled: bool = True


class TrunkInboundAuthConfig(BaseModel):
    """Autenticación para trunks inbound."""
    auth_type: Literal["digest", "ip"]
    username: Optional[str] = Field(None, description="Auto-generado si no se provee.")
    password: Optional[str] = Field(None, description="Auto-generado si no se provee.")
    allowed_ips: List[str] = Field(default_factory=list, examples=[["203.0.113.10"]])


class TrunkRegisterConfig(BaseModel):
    """Configuración para registrarse en una PBX remota (mode=register)."""
    remote_host: str = Field(..., examples=["pbx.alloy.com"])
    remote_port: int = 5060
    username: str = Field(..., examples=["100"])
    password: str
    domain: Optional[str] = None
    transport: Literal["udp", "tcp", "tls"] = "udp"
    register_interval: int = Field(120, description="Segundos entre re-registros.")


class TrunkOutboundConfig(BaseModel):
    """Configuración del carrier SIP para llamadas salientes (mode=outbound)."""
    carrier_host: str = Field(..., examples=["sip.twilio.com", "trunk.voipms.com"])
    carrier_port: int = 5060
    username: str = Field(..., examples=["ACxxxxx"])
    password: str
    domain: Optional[str] = None
    transport: Literal["udp", "tcp", "tls"] = "udp"
    caller_id: Optional[str] = Field(None, examples=["+573001234567"])
    prefix: Optional[str] = Field(None, description="Prefijo de marcación.")
    headers: Dict[str, str] = Field(default_factory=dict)


# ── Llamadas outbound ──

class OutboundCallRequest(BaseModel):
    to: str = Field(..., examples=["+573001234567"], description="Número E.164.")
    agent_id: str = Field(..., examples=["luna-cobranzas"])
    caller_id: Optional[str] = Field(None, description="Override del caller ID.")
    timeout_seconds: int = 30
    callback_url: Optional[str] = None
    metadata: Dict[str, Any] = Field(default_factory=dict, description="Se inyecta al contexto del agente.")


class OutboundCallResponse(BaseModel):
    call_id: str = Field(..., examples=["call_a1b2c3d4e5f6"])
    trunk_id: str
    agent_id: str
    to: str
    caller_id: Optional[str]
    call_status: Literal["queued", "ringing", "answered", "completed", "failed", "no_answer", "busy"] = "queued"
    session_id: Optional[str] = Field(None, description="Disponible cuando call_status=answered.")
    created_at: float = Field(default_factory=time.time)
    links: Dict[str, Any] = Field(default_factory=dict, alias="_links")

    model_config = ConfigDict(populate_by_name=True)


# ── Requests CRUD ──

class CreateTrunkRequest(BaseModel):
    name: str = Field(..., examples=["Trunk Principal Alloy"])
    tenant_id: str = Field(..., examples=["tenant-abc"])
    workspace_slug: str = Field(..., examples=["alloy-finance"])
    network_id: str = Field(..., description="Red SIP a la que pertenece el trunk.")
    mode: Literal["inbound", "register", "outbound"]

    # mode=inbound
    inbound_auth: Optional[TrunkInboundAuthConfig] = None
    routes: List[TrunkRouteConfig] = Field(default_factory=list)

    # mode=register
    register: Optional[TrunkRegisterConfig] = None
    agent_id: Optional[str] = Field(None, description="Agente asociado (requerido si mode=register).")

    # mode=outbound
    outbound: Optional[TrunkOutboundConfig] = None

    # Compartidos
    max_concurrent_calls: int = 5
    codecs: List[str] = Field(default_factory=lambda: ["ulaw", "alaw", "opus"])


class UpdateTrunkRequest(BaseModel):
    name: Optional[str] = None
    inbound_auth: Optional[TrunkInboundAuthConfig] = None
    register: Optional[TrunkRegisterConfig] = None
    outbound: Optional[TrunkOutboundConfig] = None
    agent_id: Optional[str] = None
    max_concurrent_calls: Optional[int] = None
    codecs: Optional[List[str]] = None
    enabled: Optional[bool] = None


# ── Responses ──

class TrunkLink(BaseModel):
    href: str
    method: str


class TrunkResponse(BaseModel):
    trunk_id: str = Field(..., examples=["trk_a1b2c3d4e5f6"])
    name: str
    tenant_id: str
    workspace_slug: str
    network_id: str
    mode: Literal["inbound", "register", "outbound"]

    # Inbound
    sip_host: Optional[str] = Field(None, examples=["alloy-finance.sip.tito.ai"])
    sip_port: int = 5060
    inbound_auth: Optional[TrunkInboundAuthConfig] = None
    routes: List[TrunkRouteConfig] = Field(default_factory=list)

    # Register
    register: Optional[TrunkRegisterConfig] = None
    registration_status: Optional[Literal["registered", "unregistered", "rejected", "retrying"]] = None
    agent_id: Optional[str] = None

    # Outbound
    outbound: Optional[TrunkOutboundConfig] = None
    total_calls_made: int = 0

    # Compartidos
    max_concurrent_calls: int = 5
    active_calls: int = 0
    codecs: List[str] = Field(default_factory=list)
    status: Literal["active", "inactive", "suspended"] = "active"
    created_at: float = Field(default_factory=time.time)
    updated_at: float = Field(default_factory=time.time)
    links: Dict[str, TrunkLink] = Field(default_factory=dict, alias="_links")

    model_config = ConfigDict(populate_by_name=True)


class TrunkListResponse(BaseModel):
    trunks: List[TrunkResponse] = Field(default_factory=list)
    count: int
    links: Dict[str, TrunkLink] = Field(default_factory=dict, alias="_links")

    model_config = ConfigDict(populate_by_name=True)


class TrunkCredentialsResponse(BaseModel):
    """Respuesta con credenciales regeneradas (password en claro solo aquí)."""
    trunk_id: str
    mode: Literal["inbound", "register", "outbound"]
    inbound_auth: Optional[TrunkInboundAuthConfig] = None
    register: Optional[TrunkRegisterConfig] = None
    outbound: Optional[TrunkOutboundConfig] = None
    status: str = "CREDENTIALS_ROTATED"
    links: Dict[str, TrunkLink] = Field(default_factory=dict, alias="_links")

    model_config = ConfigDict(populate_by_name=True)
```

> **Nota:** todas las passwords se enmascaran (`"********"`) en responses `GET`. El password en claro solo se devuelve en la creación y en `POST /rotate-credentials`.

---

## 4. Servicios

Todos los servicios siguen el patrón de `deployment_service.py`: singleton, `session_manager._redis`, async.

### 4.1 `SIPNetworkService`

```python
# app/services/sip_network_service.py

class SIPNetworkService:
    DOMAIN_SUFFIX = "sip.tito.ai"

    async def create_network(self, tenant_id: str, request: CreateNetworkRequest) -> SIPNetwork: ...
    async def get_network(self, network_id: str) -> Optional[SIPNetwork]: ...
    async def list_networks(self, tenant_id: str) -> List[SIPNetwork]: ...
    async def update_network(self, network_id: str, updates: dict) -> SIPNetwork: ...
    async def delete_network(self, network_id: str) -> bool: ...

    async def get_network_stats(self, network_id: str) -> dict: ...
    async def check_limits(self, network_id: str) -> dict: ...
```

### 4.2 `PeerService`

```python
class PeerService:
    async def create_peer(self, network_id: str, request: CreatePeerRequest) -> Peer: ...
    async def register_peer(self, username: str, network_id: str, contact: str): ...
    async def unregister_peer(self, username: str, network_id: str): ...
    async def get_peer_status(self, peer_id: str) -> PeerStatus: ...
    async def update_peer(self, peer_id: str, updates: dict) -> Peer: ...
    async def delete_peer(self, peer_id: str) -> bool: ...

    async def import_peers(self, network_id: str, peers: List[dict]) -> ImportResult: ...
    async def export_peers(self, network_id: str) -> List[Peer]: ...
```

### 4.3 `AgentService` (extender existente)

```python
class AgentService:
    async def create_agent(self, network_id: str, request: CreateAgentRequest) -> Agent: ...
    async def assign_to_queue(self, agent_id: str, queue_id: str, penalty: int = 0): ...
    async def remove_from_queue(self, agent_id: str, queue_id: str): ...
    async def set_status(self, agent_id: str, status: str): ...
    async def get_available_agent(self, queue_id: str, required_skills: List[str] = None) -> Optional[Agent]: ...
    async def distribute_call(self, queue_id: str, call_context: dict) -> Agent: ...
```

### 4.4 `QueueService`

```python
class QueueService:
    async def create_queue(self, network_id: str, request: CreateQueueRequest) -> Queue: ...
    async def add_member(self, queue_id: str, agent_id: str, penalty: int = 0): ...
    async def remove_member(self, queue_id: str, agent_id: str): ...
    async def pause_member(self, queue_id: str, agent_id: str, reason: str = None): ...
    async def get_queue_status(self, queue_id: str) -> QueueStatus: ...
    async def get_queue_metrics(self, queue_id: str, time_range: str = "1h") -> dict: ...

    async def add_call_to_queue(self, queue_id: str, call_data: dict) -> str: ...
    async def remove_call_from_queue(self, queue_id: str, call_id: str, reason: str): ...
    async def get_wait_position(self, queue_id: str, call_id: str) -> int: ...
```

### 4.5 `TrunkService`

```python
# app/services/trunk_service.py

class TrunkService:
    DOMAIN_SUFFIX = "sip.tito.ai"

    def __init__(self):
        self._redis = session_manager._redis

    # ── CRUD ──
    async def create_trunk(self, request: CreateTrunkRequest) -> dict:
        # 1. Validar según mode:
        #    - inbound: requiere inbound_auth
        #    - register: requiere register + agent_id
        #    - outbound: requiere outbound
        # 2. Generar trunk_id: f"trk_{uuid.uuid4().hex[:12]}"
        # 3. Para inbound: auto-generar username/password si no se proveen
        # 4. Construir dict con timestamps
        # 5. Para inbound: sip_host = f"{workspace_slug}.{DOMAIN_SUFFIX}"
        # 6. Para register: registration_status = "unregistered" (se actualiza por AMI)
        # 7. Persistir: Redis SET "trunk:{trunk_id}" → JSON
        # 8. Índice: Redis SADD "trunk:index:{workspace_slug}" → trunk_id
        # 9. Índice por red: Redis SADD "trunk:index:network:{network_id}" → trunk_id

    async def get_trunk(self, trunk_id: str) -> Optional[dict]: ...   # enmascara passwords
    async def list_trunks(self, workspace_slug: str) -> list[dict]: ...
    async def update_trunk(self, trunk_id: str, request: UpdateTrunkRequest) -> dict: ...
    async def delete_trunk(self, trunk_id: str) -> bool: ...

    # ── Rutas (solo mode=inbound) ──
    async def add_route(self, trunk_id: str, route: TrunkRouteConfig) -> dict: ...
    async def remove_route(self, trunk_id: str, extension: str) -> bool: ...

    # ── Credenciales ──
    async def rotate_credentials(self, trunk_id: str) -> dict: ...

    # ── Resolución (usado por SIP Bridge) ──
    async def resolve_inbound_call(self, workspace_slug: str, extension: str) -> Optional[dict]:
        """Busca en trunks del workspace la ruta que matchea extension.

        Incluye un short-circuit para URIs `direct.<hash>` — ver
        `sip-direct-hash.md` para el detalle del mecanismo complementario.
        """

    async def resolve_register_call(self, trunk_id: str) -> Optional[dict]:
        """Para mode=register: el trunk_id se conoce por la registración."""

    # ── Llamadas salientes (mode=outbound) ──
    async def originate_call(self, trunk_id: str, request: OutboundCallRequest) -> dict:
        # 1. Leer trunk, validar mode=outbound y status=active
        # 2. Validar max_concurrent_calls (INCR trunk:calls:{trunk_id}, si > max → DECR y 429)
        # 3. Generar call_id
        # 4. Determinar caller_id: request → trunk.outbound.caller_id → None
        # 5. Persistir "call:{call_id}" → JSON con TTL 1h
        # 6. (SIP Bridge futuro) AMI Originate:
        #       Channel: PJSIP/{to}@{trunk_endpoint}
        #       Context: tito-outbound
        #       CallerID: {caller_id}
        #       Variable: CALL_ID={call_id},AGENT_ID={agent_id}

    async def get_call(self, call_id: str) -> Optional[dict]: ...
    async def list_calls(self, trunk_id: str) -> list[dict]: ...
    async def cancel_call(self, call_id: str) -> bool: ...
    async def update_call_status(self, call_id: str, new_status: str, session_id: str = None) -> dict: ...

    # ── Helpers ──
    async def _get_trunk_raw(self, trunk_id: str) -> Optional[dict]: ...
    def _mask_password(self, data: dict) -> dict: ...
    async def increment_active_calls(self, trunk_id: str) -> int: ...
    async def decrement_active_calls(self, trunk_id: str) -> int: ...

trunk_service = TrunkService()
```

### 4.6 `DialplanService`

```python
class RouteConfig(BaseModel):
    name: str
    priority: int
    match_pattern: str  # regex o wildcard
    destination_type: Literal["peer", "queue", "trunk", "ivr", "external"]
    destination_id: str
    time_restriction: Optional[str] = None  # cron expression
    caller_id_restriction: Optional[str] = None

class DialplanService:
    async def create_route(self, network_id: str, route: RouteConfig) -> Route: ...
    async def match_route(self, network_id: str, dialed_number: str) -> RouteMatch: ...
```

---

## 5. API REST

### 5.1 Networks

```
POST   /api/v1/networks                                   → create_network
GET    /api/v1/networks?tenant_id=xxx                     → list_networks
GET    /api/v1/networks/{network_id}                      → get_network
PATCH  /api/v1/networks/{network_id}                      → update_network
DELETE /api/v1/networks/{network_id}                      → delete_network
GET    /api/v1/networks/{network_id}/stats                → network_stats
GET    /api/v1/networks/{network_id}/topology             → network_topology
```

### 5.2 Peers

```
POST   /api/v1/networks/{network_id}/peers                → create_peer
GET    /api/v1/networks/{network_id}/peers                → list_peers
GET    /api/v1/networks/{network_id}/peers/{peer_id}      → get_peer
PATCH  /api/v1/networks/{network_id}/peers/{peer_id}      → update_peer
DELETE /api/v1/networks/{network_id}/peers/{peer_id}      → delete_peer
POST   /api/v1/networks/{network_id}/peers/bulk           → import_peers
GET    /api/v1/networks/{network_id}/peers/export         → export_peers
GET    /api/v1/networks/{network_id}/peers/{peer_id}/status → peer_status
```

### 5.3 Agents

```
POST   /api/v1/networks/{network_id}/agents               → create_agent
GET    /api/v1/networks/{network_id}/agents               → list_agents
GET    /api/v1/networks/{network_id}/agents/{agent_id}    → get_agent
PATCH  /api/v1/networks/{network_id}/agents/{agent_id}    → update_agent
DELETE /api/v1/networks/{network_id}/agents/{agent_id}    → delete_agent
POST   /api/v1/networks/{network_id}/agents/{agent_id}/queue → assign_to_queue
DELETE /api/v1/networks/{network_id}/agents/{agent_id}/queue/{queue_id} → remove_from_queue
POST   /api/v1/networks/{network_id}/agents/{agent_id}/status → set_status
```

### 5.4 Queues

```
POST   /api/v1/networks/{network_id}/queues               → create_queue
GET    /api/v1/networks/{network_id}/queues               → list_queues
GET    /api/v1/networks/{network_id}/queues/{queue_id}    → get_queue
PATCH  /api/v1/networks/{network_id}/queues/{queue_id}    → update_queue
DELETE /api/v1/networks/{network_id}/queues/{queue_id}    → delete_queue
POST   /api/v1/networks/{network_id}/queues/{queue_id}/members → add_member
DELETE /api/v1/networks/{network_id}/queues/{queue_id}/members/{agent_id} → remove_member
GET    /api/v1/networks/{network_id}/queues/{queue_id}/status  → queue_status
GET    /api/v1/networks/{network_id}/queues/{queue_id}/metrics → queue_metrics
GET    /api/v1/networks/{network_id}/queues/{queue_id}/calls   → list_calls_in_queue
```

### 5.5 Trunks

```
# ── CRUD ──
POST   /api/v1/trunks                                     → create_trunk
GET    /api/v1/trunks?workspace_slug=alloy-finance        → list_trunks
GET    /api/v1/trunks/{trunk_id}                          → get_trunk
PATCH  /api/v1/trunks/{trunk_id}                          → update_trunk
DELETE /api/v1/trunks/{trunk_id}                          → delete_trunk

# ── Rutas (solo mode=inbound) ──
POST   /api/v1/trunks/{trunk_id}/routes                   → add_route
DELETE /api/v1/trunks/{trunk_id}/routes/{extension}       → remove_route

# ── Credenciales ──
POST   /api/v1/trunks/{trunk_id}/rotate-credentials       → rotate_credentials

# ── Llamadas salientes (solo mode=outbound) ──
POST   /api/v1/trunks/{trunk_id}/calls                    → originate_call
GET    /api/v1/trunks/{trunk_id}/calls                    → list_calls
GET    /api/v1/trunks/{trunk_id}/calls/{call_id}          → get_call
DELETE /api/v1/trunks/{trunk_id}/calls/{call_id}          → cancel_call
```

Cada endpoint:
- Usa los schemas Pydantic definidos arriba como request/response models.
- Genera `_links` HATEOAS con helper `get_trunk_links(request, trunk_id)`.
- Errores: `404` si el trunk no existe, `422` en validación, `409` en extensión duplicada, `429` al alcanzar `max_concurrent_calls`, `400` si el endpoint no aplica al modo del trunk.
- Documentado para Swagger bajo el tag `"SIP Trunks"`.

### 5.6 Routes / Dialplan

```
POST   /api/v1/networks/{network_id}/routes               → create_route
GET    /api/v1/networks/{network_id}/routes               → list_routes
DELETE /api/v1/networks/{network_id}/routes/{route_id}    → delete_route

GET    /api/v1/networks/{network_id}/dialplan             → get_dialplan
POST   /api/v1/networks/{network_id}/dialplan/validate    → validate_dialplan
POST   /api/v1/networks/{network_id}/dialplan/export      → export_dialplan
```

---

## 6. Redis Keys

```python
# Networks
"network:{network_id}"                      → JSON completo de la red
"network:index:{tenant_id}"                 → SET de network_ids del tenant

# Peers
"peer:{peer_id}"                            → JSON del peer
"peer:index:{network_id}"                   → SET de peer_ids
"peer:by_extension:{network_id}:{ext}"      → peer_id (lookup rápido)
"peer:registration:{network_id}:{username}" → contact, expires (registro activo)

# Agents
"agent:{agent_id}"                          → JSON del agente
"agent:index:{network_id}"                  → SET de agent_ids
"agent:queue:{queue_id}"                    → SET de agent_ids en cola
"agent:status:{network_id}"                 → HSET status por agente

# Queues
"queue:{queue_id}"                          → JSON de la cola
"queue:index:{network_id}"                  → SET de queue_ids
"queue:calls:{queue_id}"                    → LIST de call_ids en cola
"queue:wait:{queue_id}"                     → SORTED SET con wait_time

# Trunks
"trunk:{trunk_id}"                          → JSON completo (passwords en claro)
"trunk:index:{workspace_slug}"              → SET de trunk_ids del workspace
"trunk:index:network:{network_id}"          → SET de trunk_ids de la red
"trunk:calls:{trunk_id}"                    → INT contador de llamadas activas

# Outbound calls
"call:{call_id}"                            → JSON del estado de llamada (TTL 1h)
"call:index:{trunk_id}"                     → SET de call_ids activos del trunk

# Dialplan
"dialplan:{network_id}"                     → JSON del dialplan completo
"route:{network_id}:{priority}"             → JSON de la regla

# Métricas
"metrics:network:{network_id}:calls"        → HSET de métricas
"metrics:queue:{queue_id}:{interval}"       → Métricas de cola
```

---

## 7. Flujos de Llamada

### 7.1 Llamada entrante (inbound trunk o register)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                      FLUJO LLAMADA ENTRANTE                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. TRUNK RECIBE LA LLAMADA                                                │
│     └─> trunk_service.resolve_inbound_call(workspace, extension)           │
│          o trunk_service.resolve_register_call(trunk_id)                   │
│          → retorna { trunk_id, agent_id, trunk_data }                      │
│                                                                             │
│  2. DIALPLAN MATCHING (si aplica)                                          │
│     └─> dialplan_service.match_route(network_id, dialed_number)            │
│          → destino: peer | queue | agent | trunk                            │
│                                                                             │
│  3. ROUTING                                                                │
│     queue    → queue_service.add_call_to_queue → distribute_call → agente  │
│     agent    → verificar disponibilidad → spawn pipeline                    │
│     peer     → peer_service.ring_peer                                       │
│                                                                             │
│  4. CALL ESTABLISHED                                                       │
│     └─> Update peer status (BUSY)                                          │
│     └─> session_manager.start_session                                      │
│     └─> Webhook session.started                                            │
│                                                                             │
│  5. CALL ENDED                                                             │
│     └─> Cleanup, release agent, update metrics                             │
│     └─> Webhook session.ended                                              │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 7.2 Llamada saliente (outbound trunk)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                     FLUJO LLAMADA SALIENTE                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. API REQUEST                                                            │
│     POST /api/v1/trunks/{trunk_id}/calls                                   │
│     { to, agent_id, caller_id?, timeout_seconds, callback_url, metadata }  │
│                                                                             │
│  2. VALIDACIÓN                                                             │
│     - trunk existe, mode=outbound, status=active                           │
│     - active_calls < max_concurrent_calls                                  │
│                                                                             │
│  3. ORIGINATE (SIP Bridge futuro)                                          │
│     asterisk_ami_service.originate_call(                                    │
│       channel=f"PJSIP/{to}@{trunk_outbound_endpoint}",                      │
│       context="tito-outbound",                                              │
│       variables={ CALL_ID, AGENT_ID, CALL_CONTEXT: metadata }               │
│     )                                                                       │
│                                                                             │
│  4. CALL PROGRESS                                                          │
│     - AMI events: Ring → Answer / Busy / Congestion / NoAnswer             │
│     - Redis: actualiza call_status                                         │
│     - Webhooks: call.ringing → call.answered | .busy | .no_answer | .failed│
│                                                                             │
│  5. ANSWERED                                                               │
│     - Spawn agent pipeline con metadata inyectada al contexto              │
│     - session_id persistido en call:{call_id}                              │
│                                                                             │
│  6. HANGUP                                                                 │
│     - Cleanup, decrement active_calls, update metrics                      │
│     - Webhook call.completed                                               │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 7.3 Diagrama por modo

```
MODE INBOUND:
  Admin PBX ──configura trunk──► PBX Cliente ──SIP INVITE──► Tu Asterisk
                                trunk_service.resolve_inbound_call()
                                (workspace, ext="100" → agent="luna")
                                          │
                                          ▼
                                  AudioSocket → Pipeline


MODE REGISTER:
  POST /trunks (mode=register) → Tu Asterisk ──SIP REGISTER──► PBX Cliente
                                 (ext 100 queda apuntando a ti)
  Alguien marca 100 → PBX Cliente ──SIP INVITE──► Tu Asterisk
                                 trunk_service.resolve_register_call()
                                 (trunk_id → agent_id)
                                          │
                                          ▼
                                  AudioSocket → Pipeline


MODE OUTBOUND:
  POST /trunks/{id}/calls → trunk_service.originate_call()
                                          │
                                          ▼
                    Tu Asterisk ──SIP INVITE──► Carrier (Twilio / VoIP)
                                                      │
                                                      ▼
                                           PSTN → Teléfono usuario
                                                      │
                                           Usuario contesta
                                                      │
                                                      ▼
                                  AudioSocket → Pipeline (agente IA)

  Webhooks: call.ringing → call.answered → call.completed
```

---

## 8. Ejemplos de Uso (Trunks)

### Ejemplo 1: Trunk inbound (call center)

```bash
# 1. Crear trunk inbound
POST /api/v1/trunks
{
  "name": "Trunk Principal Alloy",
  "tenant_id": "tenant-abc",
  "workspace_slug": "alloy-finance",
  "network_id": "net_alloy_main",
  "mode": "inbound",
  "inbound_auth": {
    "auth_type": "digest",
    "username": "alloy-trunk"
  },
  "max_concurrent_calls": 10,
  "codecs": ["ulaw", "alaw"]
}

# → Response 201:
{
  "trunk_id": "trk_a1b2c3d4e5f6",
  "mode": "inbound",
  "sip_host": "alloy-finance.sip.tito.ai",
  "sip_port": 5060,
  "inbound_auth": {
    "auth_type": "digest",
    "username": "alloy-trunk",
    "password": "xK9mP2vL8nQ3"
  },
  "routes": [],
  "status": "active",
  "_links": {
    "self":   { "href": "/api/v1/trunks/trk_a1b2c3d4e5f6", "method": "GET" },
    "routes": { "href": "/api/v1/trunks/trk_a1b2c3d4e5f6/routes", "method": "POST" },
    "rotate": { "href": "/api/v1/trunks/trk_a1b2c3d4e5f6/rotate-credentials", "method": "POST" },
    "delete": { "href": "/api/v1/trunks/trk_a1b2c3d4e5f6", "method": "DELETE" }
  }
}

# 2. Agregar rutas
POST /api/v1/trunks/trk_a1b2c3d4e5f6/routes
{ "extension": "100", "agent_id": "luna-soporte" }

POST /api/v1/trunks/trk_a1b2c3d4e5f6/routes
{ "extension": "200", "agent_id": "ventas-bot" }

# 3. Admin configura su PBX:
#    Trunk → alloy-finance.sip.tito.ai:5060 (user: alloy-trunk, pass: xK9mP2vL8nQ3)
#    Ext 100 → Dial(PJSIP/tito-trunk/100)
#    Ext 200 → Dial(PJSIP/tito-trunk/200)
```

### Ejemplo 2: Register (PyME con 3CX)

```bash
POST /api/v1/trunks
{
  "name": "Ext 100 en 3CX Alloy",
  "tenant_id": "tenant-abc",
  "workspace_slug": "alloy-finance",
  "network_id": "net_alloy_main",
  "mode": "register",
  "register": {
    "remote_host": "pbx.alloy.com",
    "remote_port": 5060,
    "username": "100",
    "password": "ext100pass",
    "transport": "udp",
    "register_interval": 120
  },
  "agent_id": "luna-soporte",
  "max_concurrent_calls": 1
}

# → 201 con registration_status: "unregistered"
# Tu Asterisk se registra en pbx.alloy.com (SIP Bridge futuro)
# Consultar: GET /api/v1/trunks/trk_x7y8... → registration_status: "registered"
# Alguien marca ext 100 en 3CX → PBX envía INVITE → Pipeline luna-soporte
```

### Ejemplo 3: Outbound (campaña de cobranza)

```bash
# 1. Crear trunk outbound con carrier Twilio
POST /api/v1/trunks
{
  "name": "Twilio Outbound - Cobranzas",
  "tenant_id": "tenant-abc",
  "workspace_slug": "alloy-finance",
  "network_id": "net_alloy_main",
  "mode": "outbound",
  "outbound": {
    "carrier_host": "sip.twilio.com",
    "carrier_port": 5060,
    "username": "ACxxxxxxxxxxxxx",
    "password": "auth_token_here",
    "transport": "tls",
    "caller_id": "+573001234567"
  },
  "max_concurrent_calls": 5,
  "codecs": ["ulaw", "opus"]
}

# 2. Originar llamada
POST /api/v1/trunks/trk_out_.../calls
{
  "to": "+573109876543",
  "agent_id": "cobranzas-bot",
  "timeout_seconds": 25,
  "callback_url": "https://backend.alloy.com/webhooks/calls",
  "metadata": {
    "customer_name": "Juan Pérez",
    "debt_amount": 150000,
    "due_date": "2026-04-01",
    "account_id": "ACC-789"
  }
}

# → 201 { call_id, call_status: "queued", ... }

# 3. Consultar estado
GET /api/v1/trunks/trk_out_.../calls/call_m1n2o3p4q5r6
# → call_status: "answered", session_id: "sess_a1b2..."

# 4. Listar llamadas activas
GET /api/v1/trunks/trk_out_.../calls

# 5. Cancelar una llamada en curso
DELETE /api/v1/trunks/trk_out_.../calls/call_m1n2o3p4q5r6
```

**Inyección de `metadata` al contexto del agente:**

```
metadata: { customer_name: "Juan Pérez", debt_amount: 150000 }
                          ↓
System prompt del agente:
  "Estás llamando a Juan Pérez. Tiene una deuda de $150.000 COP vencida
   desde 2026-04-01. Tu objetivo es negociar un plan de pago."
                          ↓
Agente: "Hola Juan, te llamo de Alloy Finance respecto a tu cuenta..."
```

---

## 9. Webhooks por Modo

| Evento | `inbound` | `register` | `outbound` |
|--------|:---------:|:----------:|:----------:|
| `session.started`  | ✅ | ✅ | ✅ (cuando `call_status=answered`) |
| `session.ended`    | ✅ | ✅ | ✅ |
| `session.error`    | ✅ | ✅ | ✅ |
| `call.ringing`     | — | — | **✅** |
| `call.answered`    | — | — | **✅** |
| `call.completed`   | — | — | **✅** |
| `call.failed`      | — | — | **✅** |
| `call.no_answer`   | — | — | **✅** |
| `call.busy`        | — | — | **✅** |

---

## 10. Implementación por Fases

| Fase | Componente | Descripción | Dependencias |
|------|------------|-------------|--------------|
| 1 | SIP Network + Peers | Redes virtuales, peers, registro | — |
| 2 | Agents | Agentes IA asociados a la red | Fase 1 |
| 3 | Queues | Colas y estrategias de distribución | Fase 2 |
| 4 | **Trunks** (schemas + service + API) | Inbound / Register / Outbound (sin SIP Bridge aún) | Fase 1 |
| 5 | Dialplan / Routes | Enrutamiento unificado | Fases 1-4 |
| 6 | SIP Bridge (Asterisk/Kamailio + AMI) | Registro real, originate, AudioSocket | Fase 4 |
| 7 | Métricas | Observabilidad por network/trunk/queue | Fases 1-6 |

### Orden concreto para Trunks (Fase 4)

| Paso | Archivo | Acción |
|------|---------|--------|
| 1 | `app/schemas/trunks.py` | Crear |
| 2 | `app/services/trunk_service.py` | Crear (sin SIP Bridge; solo Redis + validación) |
| 3 | `app/api/v1/trunks.py` | Crear endpoints REST con HATEOAS |
| 4 | `app/api/v1/__init__.py` | `router.include_router(trunks_router, prefix="/trunks", tags=["SIP Trunks"])` |
| 5 | `app/main.py` | Agregar tag `"SIP Trunks"` en `openapi_tags` |

### Lo que NO se toca en Fase 4

- `deployment_service.py` / `deployments.py` — el modelo viejo SIP-por-agente sigue funcionando; se deprecará al terminar Fase 6.
- `agent_pipeline_engine.py` — no cambia. El SIP Bridge (Fase 6) usará `resolve_inbound_call` / `resolve_register_call`.
- `config.py` y `compose.yaml` — Asterisk + AMI se agregan en Fase 6.

---

## 11. Schema JSON de Ejemplo (network completa)

```json
{
  "network": {
    "network_id": "net_abc123",
    "tenant_id": "tenant_xyz",
    "name": "Empresa Demo",
    "slug": "empresa-demo",
    "cidr": "192.168.1.0/24",
    "gateway": "192.168.1.1",
    "domain": "empresa-demo.sip.tito.ai",
    "max_peers": 100,
    "max_concurrent_calls": 50,
    "status": "active"
  },
  "peers": [
    {
      "peer_id": "peer_001",
      "extension": "100",
      "name": "Juan Pérez",
      "peer_type": "sip",
      "username": "juan100",
      "status": "online"
    }
  ],
  "agents": [
    {
      "agent_id": "agent_ai_001",
      "name": "Asistente Ventas",
      "agent_type": "ai_voice",
      "mode": "both",
      "ai_config_id": "cfg_voice_001",
      "queues": ["sales", "support"]
    }
  ],
  "queues": [
    {
      "queue_id": "queue_sales",
      "name": "Ventas",
      "strategy": "ring_all",
      "members": [
        { "agent_id": "agent_001", "penalty": 0 },
        { "agent_id": "agent_002", "penalty": 5 }
      ]
    }
  ],
  "trunks": [
    {
      "trunk_id": "trk_a1b2c3d4e5f6",
      "mode": "inbound",
      "sip_host": "empresa-demo.sip.tito.ai",
      "routes": [
        { "extension": "100", "agent_id": "agent_ai_001" }
      ]
    }
  ],
  "routes": []
}
```
