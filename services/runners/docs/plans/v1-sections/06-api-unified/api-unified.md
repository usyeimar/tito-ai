## 5. API Unificada

> **Nota de Arquitectura:** La gestión de redes SIP, peers, trunks y agentes (secciones 1-4) se administra desde **Laravel (Admin UI)**, no desde el runner Python. El runner recibe la configuración vía `POST /api/v1/sessions` y ejecuta el pipeline de voz. Esta API define los endpoints que Laravel usaría para gestionar la configuración SIP, pero la implementación real vive en el backend Laravel.

### 5.1 Endpoints de Red

```
# Networks
POST   /api/v1/networks                              → Crear red SIP
GET    /api/v1/networks                              → Listar redes
GET    /api/v1/networks/{network_id}                → Obtener red
PATCH  /api/v1/networks/{network_id}                → Actualizar red
DELETE /api/v1/networks/{network_id}                → Eliminar red

# Peers
POST   /api/v1/networks/{network_id}/peers          → Crear peer
GET    /api/v1/networks/{network_id}/peers          → Listar peers
GET    /api/v1/networks/{network_id}/peers/{peer_id}→ Obtener peer
PATCH  /api/v1/networks/{network_id}/peers/{peer_id}→ Actualizar peer
DELETE /api/v1/networks/{network_id}/peers/{peer_id}→ Eliminar peer

# Trunks
POST   /api/v1/networks/{network_id}/trunks         → Crear trunk
GET    /api/v1/networks/{network_id}/trunks         → Listar trunks
GET    /api/v1/networks/{network_id}/trunks/{trunk_id} → Obtener trunk
PATCH  /api/v1/networks/{network_id}/trunks/{trunk_id} → Actualizar trunk
DELETE /api/v1/networks/{network_id}/trunks/{trunk_id} → Eliminar trunk

# Routes
POST   /api/v1/networks/{network_id}/routes         → Crear ruta
GET    /api/v1/networks/{network_id}/routes         → Listar rutas
DELETE /api/v1/networks/{network_id}/routes/{route_id} → Eliminar ruta
```

### 5.2 Endpoints de Llamadas

```
# Llamadas SIP (PSTN)
POST   /api/v1/networks/{network_id}/calls          → Iniciar llamada saliente
GET    /api/v1/networks/{network_id}/calls          → Listar llamadas
GET    /api/v1/networks/{network_id}/calls/{call_id}→ Obtener llamada
DELETE /api/v1/networks/{network_id}/calls/{call_id}→ Cancelar llamada

# Rooms WebRTC
POST   /api/v1/networks/{network_id}/rooms          → Crear sala
GET    /api/v1/networks/{network_id}/rooms          → Listar salas
GET    /api/v1/networks/{network_id}/rooms/{room_id}→ Obtener sala
DELETE /api/v1/networks/{network_id}/rooms/{room_id}→ Eliminar sala
POST   /api/v1/networks/{network_id}/rooms/{room_id}/token → Generar token

# Agentes
POST   /api/v1/networks/{network_id}/agents         → Crear agente
GET    /api/v1/networks/{network_id}/agents         → Listar agentes
GET    /api/v1/networks/{network_id}/agents/{agent_id} → Obtener agente
PATCH  /api/v1/networks/{network_id}/agents/{agent_id} → Actualizar agente
DELETE /api/v1/networks/{network_id}/agents/{agent_id} → Eliminar agente
```

### 5.3 Request/Response Unificado

```python
# app/schemas/call.py

from pydantic import BaseModel, Field
from typing import Optional, Dict, Any, Literal
import time

class CallType(str, Enum):
    SIP = "sip"       # Telefónico tradicional
    WEBRTC = "webrtc" # Navegador/App

class CallDirection(str, Enum):
    INBOUND = "inbound"
    OUTBOUND = "outbound"

class CreateCallRequest(BaseModel):
    """Request unificado para crear llamadas."""

    # Común
    network_id: str
    agent_id: Optional[str] = None
    context: Optional[Dict[str, Any]] = Field(None, description="Contexto para el agente")
    metadata: Optional[Dict[str, Any]] = Field(None, description="Metadata adicional")

    # Para SIP (PSTN) - OUTBOUND
    call_type: Literal["sip", "webrtc"] = Field(..., description="Tipo de llamada")

    # Para SIP
    to: Optional[str] = Field(None, description="Número destino (E.164)")
    trunk_id: Optional[str] = Field(None, description="Trunk para llamadas salientes")
    caller_id: Optional[str] = Field(None, description="Caller ID override")

    # Para WebRTC
    room_name: Optional[str] = Field(None, description="Nombre de sala")
    participant_identity: Optional[str] = Field(None, description="Identidad del participante")
    participant_name: Optional[str] = Field(None, description="Nombre del participante")

class CallResponse(BaseModel):
    """Response unificado de llamada."""

    call_id: str
    call_type: CallType
    direction: CallDirection
    network_id: str
    agent_id: Optional[str]

    # Estado
    status: str = Field(..., description="queued, ringing, active, completed, failed")
    session_id: Optional[str] = Field(None, description="ID de sesión Runner")

    # Timestamps
    created_at: float
    answered_at: Optional[float] = None
    ended_at: Optional[float] = None

    # Detalles
    from_number: Optional[str] = None
    to_number: Optional[str] = None
    room_name: Optional[str] = None

    # Links HATEOAS
    links: Dict[str, Any] = Field(default_factory=dict)
```

---
