## 2. Arquitectura WebRTC

### 2.1 Componentes de LiveKit

```python
# app/schemas/livekit_config.py

class TenantLiveKitConfig(BaseModel):
    """Configuración de LiveKit por tenant."""
    
    network_id: str
    tenant_id: str
    
    # URLs (pueden ser diferentes por tenant o shareadas)
    livekit_url: str = "wss://livekit.tito.ai"
    livekit_api_key: str
    livekit_api_secret: str
    
    # Configuración de sala
    room_config: RoomConfig = Field(default_factory=RoomConfig)
    
    # Audio
    audio_codec: str = "opus"
    sample_rate: int = 16000
    
    # Recording
    recording_enabled: bool = False
    recording_storage_backend: Optional[str] = None  # s3, gcs, local
    
    # Video (opcional)
    video_enabled: bool = False
    video_codec: str = "vp8"


class RoomConfig(BaseModel):
    """Configuración de sala LiveKit."""
    
    max_participants: int = Field(2, description="Max participants per room")
    empty_timeout: int = Field(300, description="Seconds before empty room is deleted")
    departure_timeout: int = Field(60, description="Seconds before disconnected is removed")
    enable_recording: bool = Field(False)
    enable_transcription: bool = Field(False)
    
    # Audio settings
    audio: AudioConfig = Field(default_factory=AudioConfig)
    
    # Video settings
    video: Optional[VideoConfig] = None


class AudioConfig(BaseModel):
    """Configuración de audio de la sala."""
    
    quality: str = Field("high", description="low, medium, high")
    echo_cancellation: bool = Field(True)
    noise_suppression: bool = Field(True)
    auto_gain_control: bool = Field(True)
```

### 2.2 Flujo WebRTC

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                         FLUJO LLAMADA WEBRTC                                        │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                      │
│  1. INICIO                                                                           │
│     ┌───────────────────────────────────────────────────────────────────────────┐   │
│     │ Usuario abre aplicación web/móvil                                        │   │
│     │ Solicita unirse a sala: room_{network_id}_{extension}                   │   │
│     └───────────────────────────────────────────────────────────────────────────┘   │
│                                                                                      │
│  2. AUTHENTICATION                                                                   │
│     └── API Backend genera JWT Token                                              │
│          └── Payload:                                                              │
│               ├── iss: api_key                                                    │
│               ├── sub: user_identity                                             │
│               ├── room: room_name                                                │
│               ├── exp: expiration                                                │
│               └── jti: unique token id                                           │
│                                                                                      │
│     └── Usuario conecta a LiveKit: wss://livekit.tito.ai                         │
│          └── Envía JWT en conexión                                               │
│               └── LiveKit valida token                                           │
│                                                                                      │
│  3. ROOM CREATION                                                                    │
│     └── Si la sala no existe, LiveKit la crea                                    │
│          └── Notifica a Backend via Webhook/RTC                                    │
│               └── Event: room_started                                             │
│                                                                                      │
│  4. PARTICIPANT JOINED                                                              │
│     └── LiveKit envía evento: participant_joined                                 │
│          └── Backend recibe via Webhook                                           │
│               └── Redis Pub/Sub: "room:{room_id}:join"                           │
│                                                                                      │
│  5. RUNNER NOTIFICATION                                                            │
│     └── Runner suscribe a Redis channel "room:+"                                │
│          └── Recibe: {room_id, participant_id, ...}                              │
│               └── Runner ejecuta: spawn_bot(room_url, token, config, room_name) │
│                                                                                      │
│  6. RUNNER JOINS LA SALA                                                           │
│     └── Runner conecta a LiveKit con JWT de bot                                  │
│          └── Se une a la sala como participante "agent-{agent_id}"              │
│               └── Habilita microphone                                            │
│                                                                                      │
│  7. MEDIA FLOW                                                                       │
│     ┌───────────────────────────────────────────────────────────────────────────┐   │
│     │ Browser ──WebRTC (SRTP)──► LiveKit ──WebRTC (SRTP)──► Runner (Bot)      │   │
│     │   audio ───────────────────────────── audio ─────────────────────────    │   │
│     │   video ───────────────────────────── video ─────────────────────────    │   │
│     │   (opcional)                                  (opcional)                │   │
│     └───────────────────────────────────────────────────────────────────────────┘   │
│                                                                                      │
│  8. CONVERSACIÓN                                                                    │
│     └── Pipeline de agente procesa audio:                                         │
│          ├── STT (speech to text)                                                │
│          ├── LLM (procesa y genera respuesta)                                    │
│          └── TTS (text to speech)                                                │
│               └── Runner envía audio a LiveKit                                    │
│                    └── LiveKit reenvía al browser                                 │
│                                                                                      │
│  9. TERMINACIÓN                                                                       │
│     └── Usuario se desconecta                                                     │
│          └── LiveKit envía: participant_left                                     │
│               └── Redis Pub/Sub: "room:{room_id}:leave"                          │
│                    └── Runner deja la sala                                       │
│                         └── Pipeline termina                                      │
│                              └── Webhook: call.ended                              │
│                                                                                      │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

### 2.3 Integración Runner con LiveKit

```python
# app/services/livekit_runner_service.py

import asyncio
from livekit import rtc
from livekit.art import AsyncArt

class LiveKitRunnerService:
    """
    Servicio que integra Runner con LiveKit para manejar sesiones WebRTC.
    """
    
    def __init__(self, config: LiveKitConfig):
        self.config = config
        self._room: Optional[rtc.Room] = None
        self._agent_pipeline = None
    
    async def connect(self, room_name: str, agent_id: str, token: str):
        """
        Conecta a una sala de LiveKit como agente.
        """
        # Crear room connection
        self._room = rtc.Room()
        
        # Configurar callbacks
        self._room.on("participant_joined", self._on_participant_joined)
        self._room.on("participant_left", self._on_participant_left)
        self._room.on("track_subscribed", self._on_track_subscribed)
        
        # Conectar a la sala
        url = self.config.url.replace("wss://", "http://")
        await self._room.connect(url, token)
        
        # Guardar referencia al agent pipeline
        self._agent_id = agent_id
        self._room_name = room_name
    
    async def _on_participant_joined(self, participant: rtc.Participant):
        """Maneja cuando un participante se une."""
        if participant.identity == self._agent_id:
            return  # Soy yo mismo
        
        # Iniciar pipeline de agente
        # El audio del participante se procesa a través del pipeline
    
    async def _on_participant_left(self, participant: rtc.Participant):
        """Maneja cuando un participante se va."""
        # Notificar al pipeline
    
    async def _on_track_subscribed(self, publication: rtc.TrackPublication, 
                                    participant: rtc.Participant):
        """Maneja cuando se subscribe a un track."""
        if publication.kind == rtc.TrackKind.KIND_AUDIO:
            # Attach track al pipeline de procesamiento
            audio_track = publication.track
            # Pasar audio al pipeline de STT
    
    async def play_audio(self, audio_data: bytes):
        """Envía audio al room."""
        if self._room:
            # Convertir audio a formato de LiveKit
            # Publicar como track de audio
    
    async def disconnect(self):
        """Desconecta del room."""
        if self._room:
            await self._room.disconnect()
```

### 2.4 Generación de Tokens JWT

```python
# app/services/token_service.py

import jwt
import time
from typing import Optional

class TokenService:
    """Servicio para generar tokens de acceso a LiveKit."""
    
    def __init__(self, api_key: str, api_secret: str):
        self._api_key = api_key
        self._api_secret = api_secret
    
    def create_room_token(self, room: str, identity: str, 
                         name: Optional[str] = None,
                         metadata: Optional[str] = None,
                         expires_at: Optional[int] = None) -> str:
        """
        Crea token para unirse a una sala de LiveKit.
        """
        now = int(time.time())
        exp = expires_at or (now + 3600)  # 1 hour default
        
        claims = {
            "iss": self._api_key,
            "sub": identity,
            "room": room,
            "exp": exp,
            "iat": now,
            "jti": f"token_{now}_{identity}"  # Unique token ID
        }
        
        if name:
            claims["name"] = name
        
        if metadata:
            claims["metadata"] = metadata
        
        return jwt.encode(claims, self._api_secret, algorithm="HS256")
    
    def create_agent_token(self, room: str, agent_id: str) -> str:
        """Crea token para el agente (bot)."""
        return self.create_room_token(
            room=room,
            identity=f"agent-{agent_id}",
            name=f"Agent {agent_id}",
            metadata=json.dumps({"type": "agent", "agent_id": agent_id})
        )
    
    def create_user_token(self, room: str, user_id: str, 
                         user_name: str, metadata: Optional[dict] = None) -> str:
        """Crea token para el usuario final."""
        return self.create_room_token(
            room=room,
            identity=user_id,
            name=user_name,
            metadata=json.dumps(metadata) if metadata else None
        )
```

---

