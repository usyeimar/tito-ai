## 1. Arquitectura SIP (PSTN)

### 1.1 Componentes del SBC (Session Border Controller)

#### Transport Layer

```python
# sbc/transport/manager.py

class TransportManager:
    """
    Maneja múltiples transportes SIP (UDP, TCP, TLS) para múltiples tenants.
    Cada tenant puede tener diferentes configuraciones de transporte.
    """
    
    def __init__(self):
        self._listeners: Dict[str, SIPListener] = {}
        self._tls_contexts: Dict[str, ssl.SSLContext] = {}
        self._rtp_ports: Dict[str, PortPool] = {}
    
    async def start_listener(self, config: TransportConfig):
        """
        Inicia listener SIP para un tenant específico.
        
        Configuración por tenant:
        - Tenant A: udp:0.0.0.0:5060, tls:0.0.0.0:5061
        - Tenant B: udp:0.0.0.0:15060, tls:0.0.0.0:15061
        """
        
    async def stop_listener(self, network_id: str):
        """Detiene un listener específico."""
    
    def get_rtp_session(self, call_id: str) -> RTPSession:
        """Obtiene sesión RTP activa."""
```

#### SIP Parser

```python
# sbc/parser/sip_parser.py

class SIPParser:
    """
    Parsea mensajes SIP y extrae información relevante para el routing.
    Maneja SIP normalize (RFC 3261) y headers custom.
    """
    
    def parse_request(self, raw_data: bytes) -> SIPRequest:
        """Parsea SIP INVITE, ACK, BYE, CANCEL, OPTIONS, REGISTER."""
    
    def parse_response(self, raw_data: bytes) -> SIPResponse:
        """Parsea respuestas 100-699."""
    
    def extract_identity(self, request: SIPRequest) -> SIPIdentity:
        """Extrae From, To, Contact, Via."""
    
    def extract_sdp(self, request: SIPRequest) -> SDPInfo:
        """Extrae sesión de descripción de protocolo (SDP)."""
    
    def normalize_headers(self, request: SIPRequest) -> SIPRequest:
        """Normaliza headers para routing interno."""
    
    def rewrite_headers(self, request: SIPRequest, config: HeaderConfig) -> SIPRequest:
        """Reescribe headers para reenvío a Asterisk."""
    
    def extract_destination(self, request: SIPRequest) -> Destination:
        """
        Extrae destino de la solicitud:
        - Dominio (para tenant lookup)
        - Usuario/Extensión (para dialplan)
        """
```

#### Router Engine

```python
# sbc/router/engine.py

class RouterEngine:
    """
    Motor de enrutamiento SIP basado en dominio/tenant.
    Es el corazón del SBC que decide el destino de cada llamada.
    """
    
    def __init__(self, redis: Redis):
        self._redis = redis
        self._cache: Dict[str, TenantConfig] = {}
    
    async def route(self, request: SIPRequest) -> RoutingDecision:
        """
        Decision de routing basada en:
        1. Host header (dominio SIP) → tenant
        2. Destination number → dialplan
        3. Tenant config → políticas
        4. Auth rules → permitir/bloquear
        """
        # 1. Extraer dominio del Host header
        domain = request.headers.get("Host", "")
        
        # 2. Lookup tenant
        tenant = await self._lookup_tenant(domain)
        if not tenant:
            return RoutingDecision(status=404, message="Domain not found")
        
        # 3. Validar autenticación
        auth_result = await self._authenticate(request, tenant)
        if not auth_result.success:
            return RoutingDecision(status=auth_result.status, 
                                   message=auth_result.message)
        
        # 4. Resolver destino (extensión/cola/agente/trunk)
        destination = await self._resolve_destination(
            tenant_id=tenant.id,
            number=request.uri.user
        )
        
        # 5. Aplicar políticas (rate limiting, recording, etc)
        policy_result = await self._apply_policies(request, tenant)
        if not policy_result.allowed:
            return RoutingDecision(status=policy_result.status,
                                   message=policy_result.message)
        
        # 6. Preparar forward
        return RoutingDecision(
            status=200,
            destination=destination,
            asterisk_endpoint=tenant.asterisk_endpoint,
            headers_to_add=self._build_headers(destination, tenant)
        )
    
    async def _lookup_tenant(self, domain: str) -> Optional[TenantConfig]:
        """Busca configuración del tenant por dominio SIP."""
        # Redis lookup: domain:{domain}:tenant_id → tenant_id
        # Redis lookup: tenant:{tenant_id}:config → JSON
        # Cache en memoria para performance
    
    async def _resolve_destination(self, tenant_id: str, number: str) -> Destination:
        """
        Resuelve número marcado a destino:
        - Extensión interna → peer
        - Cola → queue
        - Agente → agent
        - Número externo → trunk
        """
        # Consultar dialplan del tenant
        # Redis: tenant:{tenant_id}:dialplan:{number} → destination
    
    async def _apply_policies(self, request: SIPRequest, tenant: TenantConfig) -> PolicyResult:
        """Aplica políticas del tenant."""
        # Rate limiting
        # Call recording
        # CLID manipulation
        # Time-based restrictions
```

#### Auth Layer

```python
# sbc/auth/layer.py

class SBCAuthLayer:
    """
    Autenticación y autorización de peers SIP.
    Implementa múltiples métodos de autenticación.
    """
    
    async def authenticate(self, request: SIPRequest, tenant: TenantConfig) -> AuthResult:
        """
        Autentica basado en configuración del tenant:
        - digest: WWW-Authenticate challenge
        - ip: Whitelist de IPs permitidas
        - api_key: API Key en header
        - none: Sin autenticación
        """
        if tenant.auth_mode == "none":
            return AuthResult(success=True)
        
        if tenant.auth_mode == "ip":
            return await self._authenticate_ip(request, tenant)
        
        if tenant.auth_mode == "digest":
            return await self._authenticate_digest(request, tenant)
        
        if tenant.auth_mode == "api_key":
            return await self._authenticate_api_key(request, tenant)
    
    async def _authenticate_digest(self, request: SIPRequest, tenant: TenantConfig) -> AuthResult:
        """Autenticación Digest MD5/SHA256."""
        # 1. Verificar si tiene Authorization header
        # 2. Si no, retornar 401 con WWW-Authenticate
        # 3. Si tiene, verificar contra credentials del peer
        
    async def _authenticate_ip(self, request: SIPRequest, tenant: TenantConfig) -> AuthResult:
        """Autenticación por IP origen."""
        # Verificar IP origen en whitelist del tenant
    
    async def authorize(self, identity: SIPIdentity, action: str, tenant: TenantConfig) -> bool:
        """Autoriza acciones específicas (INVITE, REGISTER, etc)."""
```

#### Media Relay (RTP)

```python
# sbc/media/relay.py

class MediaRelay:
    """
    Relay de RTP entre caller y Asterisk.
    El SBC reenvía RTP sin procesamiento (B2BUA solo si es necesario).
    """
    
    def __init__(self, rtp_port_start: int = 10000, rtp_port_end: int = 20000):
        self._port_pool = PortPool(rtp_port_start, rtp_port_end)
        self._sessions: Dict[str, RTPSession] = {}
    
    async def create_session(self, call_id: str, local_sdp: SDPInfo, 
                             remote_endpoint: str) -> RTPSession:
        """
        Crea sesión RTP:
        1. Allocate ports desde el pool
        2. Rewrites SDP (reemplaza IP con IP del relay)
        3. Crea NAT mappings
        4. Inicia receiver de RTP
        """
        local_port = self._port_pool.allocate()
        session = RTPSession(
            call_id=call_id,
            local_port=local_port,
            remote_endpoint=remote_endpoint
        )
        self._sessions[call_id] = session
        return session
    
    async def forward_rtp(self, call_id: str, rtp_packet: RTPPacket):
        """Reenvía paquetes RTP al destino correcto."""
    
    async def close_session(self, call_id: str):
        """Libera puertos y limpia sesión."""
    
    def get_stats(self, call_id: str) -> RTPStats:
        """Estadísticas de RTP: jitter, packet loss, latency."""
```

### 1.2 Modelo de Datos SIP

```python
# app/schemas/sip_network.py

from pydantic import BaseModel, Field
from typing import Optional, List, Dict
from enum import Enum
import time

class NetworkStatus(str, Enum):
    ACTIVE = "active"
    SUSPENDED = "suspended"
    TERMINATED = "terminated"

class SIPNetwork(BaseModel):
    """Red SIP virtual de un tenant."""
    
    # Identificación
    network_id: str = Field(..., description="ID único de la red")
    tenant_id: str = Field(..., description="ID del tenant/organización")
    name: str = Field(..., description="Nombre de la red")
    slug: str = Field(..., description="Slug único para URLs")
    
    # Red IP virtual (CIDR)
    cidr: str = Field(..., description="Rango CIDR de la red", example="192.168.1.0/24")
    gateway: str = Field(..., description="IP del gateway", example="192.168.1.1")
    
    # Configuración SIP
    domain: str = Field(..., description="Dominio SIP", example="empresa1.sip.tito.ai")
    outbound_proxy: Optional[str] = Field(None, description="Proxy saliente")
    
    # TLS
    tls_enabled: bool = Field(False)
    tls_cert_path: Optional[str] = Field(None)
    tls_key_path: Optional[str] = Field(None)
    
    # Límites
    max_peers: int = Field(100, description="Máximo de peers")
    max_concurrent_calls: int = Field(50, description="Máximo llamadas simultáneas")
    
    # Audio
    codec_preference: List[str] = Field(
        default_factory=lambda: ["opus", "ulaw", "alaw"],
        description="Codecs preferidos"
    )
    transport: str = Field("udp", description="Transporte SIP (udp/tcp/tls)")
    
    # Asterisk de destino
    asterisk_endpoint: str = Field(..., description="IP del Asterisk del tenant")
    asterisk_port: int = Field(5060, description="Puerto SIP del Asterisk")
    
    # Estado
    status: NetworkStatus = Field(NetworkStatus.ACTIVE)
    created_at: float = Field(default_factory=time.time)
    updated_at: float = Field(default_factory=time.time)
```

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
    """Extensión/dispositivo SIP dentro de una red."""
    
    # Identificación
    peer_id: str
    network_id: str
    extension: str = Field(..., description="Número de extensión", example="100")
    
    # Tipo y nombre
    peer_type: PeerType = Field(PeerType.SIP)
    name: str
    
    # Credenciales SIP
    username: str
    secret: str  # Se enmascara en responses
    
    # Configuración
    caller_id: Optional[str] = Field(None, description="Caller ID externo")
    context: str = Field("default", description="Contexto del dialplan")
    codec_enabled: List[str] = Field(default_factory=list)
    voicemail_enabled: bool = Field(True)
    call_forward_enabled: bool = Field(False)
    
    # Estado runtime
    status: PeerStatus = Field(PeerStatus.OFFLINE)
    last_register: Optional[float] = Field(None)
    current_channel: Optional[str] = Field(None)
    
    # Metadata
    tags: List[str] = Field(default_factory=list)
    metadata: Dict[str, str] = Field(default_factory=dict)
```

```python
# app/schemas/trunk.py

class TrunkMode(str, Enum):
    INBOUND = "inbound"     # Cliente conecta a nosotros
    REGISTER = "register"   # Nos registramos en PBX del cliente
    OUTBOUND = "outbound"   # Llamadas salientes

class TrunkStatus(str, Enum):
    ACTIVE = "active"
    INACTIVE = "inactive"
    SUSPENDED = "suspended"

class Trunk(BaseModel):
    """Trunk SIP asociado a una red."""
    
    trunk_id: str
    network_id: str
    
    name: str
    mode: TrunkMode
    
    # Inbound
    sip_host: Optional[str] = Field(None, description="Host para inbound")
    inbound_auth: Optional[InboundAuthConfig] = None
    
    # Register
    register_config: Optional[RegisterConfig] = None
    registration_status: Optional[str] = Field(None)
    
    # Outbound
    outbound_config: Optional[OutboundConfig] = None
    
    # Configuración
    max_concurrent_calls: int = Field(5)
    codecs: List[str] = Field(default_factory=lambda: ["ulaw", "alaw", "opus"])
    status: TrunkStatus = Field(TrunkStatus.ACTIVE)
    
    # Métricas
    total_calls: int = Field(0)
    active_calls: int = Field(0)
```

### 1.3 Flujo de Llamada SIP

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                         FLUJO LLAMADA ENTRANTE (SIP)                                │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                      │
│  1. RECEPCIÓN DEL INVITE                                                             │
│     ┌───────────────────────────────────────────────────────────────────────────┐   │
│     │ SIP INVITE desde Internet                                                 │   │
│     │ FROM: <sip:573001234567@empresa1.sip.tito.ai>                            │   │
│     │ TO: <sip:100@empresa1.sip.tito.ai>                                       │   │
│     │ CONTACT: <sip:10.0.0.1:5060>                                             │   │
│     │ Via: SIP/2.0/UDP 10.0.0.1:5060;branch=...                                │   │
│     └───────────────────────────────────────────────────────────────────────────┘   │
│                                                                                      │
│  2. TRANSPORT LAYER                                                                  │
│     └── Socket UDP/TCP recibe el paquete                                            │
│          └── Extrae headers y payload                                               │
│               └── Pasa a SIP Parser                                                 │
│                                                                                      │
│  3. SIP PARSER                                                                       │
│     └── Parsea headers: From, To, Via, Contact, SDP                                │
│          └── Extrae:                                                               │
│               ├── Domain: empresa1.sip.tito.ai                                     │
│               ├── User: 100                                                       │
│               ├── Source IP: 10.0.0.1                                             │
│               └── SDP (IP de audio, codecs, puertos)                              │
│                                                                                      │
│  4. ROUTER ENGINE                                                                   │
│     └── Lookup tenant por dominio                                                  │
│          └── REDIS GET domain:empresa1.sip.tito.ai:tenant_id                      │
│               └── REDIS GET tenant:{tenant_id}:config                             │
│                                                                                      │
│     └── Autenticación                                                              │
│          └── Verificar IP origen en allowed_ips                                    │
│               └── O: verificar Digest Auth                                        │
│                                                                                      │
│     └── Resolver destino                                                          │
│          └── REDIS GET tenant:{tenant_id}:extension:100                           │
│               └── Result: {type: "agent", agent_id: "agent_voice_001"}            │
│                                                                                      │
│     └── Aplicar políticas                                                          │
│          └── Verificar rate limit (calls_per_second)                              │
│               └── Verificar max_concurrent_calls                                  │
│                    └── Verificar call recording enabled                           │
│                                                                                      │
│  5. MEDIA RELAY                                                                      │
│     └── Allocate RTP ports: 10000-10001                                           │
│          └── Rewrite SDP:                                                          │
│               ├── Reemplaza IP de audio (10.0.0.1 → 10.20.30.40 SBC)            │
│               └── Cambia puerto RTP (10000 → 10000)                               │
│                                                                                      │
│  6. FORWARD TO ASTERISK                                                            │
│     └── Reescribe headers:                                                        │
│          ├── To: 100@asterisk-internal                                            │
│          ├── X-Network-ID: net_abc123                                             │
│          ├── X-Agent-ID: agent_voice_001                                          │
│          ├── X-Tenant-ID: tenant_xyz                                             │
│          └── Remove: Route, Record-Route                                          │
│                                                                                      │
│     └── Send to Asterisk: 10.100.1.50:5060                                       │
│                                                                                      │
│  7. PROGRESIÓN                                                                       │
│     └── Asterisk sends 180 Ringing                                                │
│          └── SBC forwards to caller                                               │
│                                                                                      │
│     └── Asterisk sends 200 OK with SDP                                            │
│          └── SBC:                                                                  │
│               ├── Media relay establece RTP stream                                │
│               └── Forwards 200 OK to caller                                       │
│                                                                                      │
│  8. MEDIA FLOW                                                                        │
│     ┌───────────────────────────────────────────────────────────────────────────┐   │
│     │ Caller ──RTP(p:10000)──► SBC ──RTP(p:10002)──► Asterisk ──RTP──► Agent IA │   │
│     │         │                            │                       │             │   │
│     │         └────────────────────────────┴───────────────────────┘             │   │
│     │                    (RTP Reenvío sin procesamiento)                         │   │
│     └───────────────────────────────────────────────────────────────────────────┘   │
│                                                                                      │
│  9. TERMINACIÓN                                                                       │
│     └── BYE from caller or Asterisk                                                │
│          └── SBC:                                                                  │
│               ├── Close RTP session (release ports)                               │
│               ├── Update call status in Redis                                     │
│               ├── Forward BYE to Asterisk                                         │
│               └── Emit webhook: call.ended                                        │
│                                                                                      │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

