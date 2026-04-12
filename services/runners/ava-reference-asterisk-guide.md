# Guía de Implementación: Integración de Asterisk en AVA AI Voice Agent

## Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Arquitectura de Integración](#arquitectura-de-integración)
3. [Asterisk REST Interface (ARI)](#asterisk-rest-interface-ari)
4. [Cliente ARI - Código Completo](#cliente-ari---código-completo)
5. [Motor Principal - Código Completo](#motor-principal---código-completo)
6. [Transporte de Audio](#transporte-de-audio)
7. [Configuración del Dialplan](#configuración-del-dialplan)
8. [Eventos de Asterisk](#eventos-de-asterisk)
9. [Herramientas de Telefonía](#herramientas-de-telefonía)
10. [Ejemplos de Integración](#ejemplos-de-integración)

---

## Visión General

AVA AI Voice Agent se integra con Asterisk a través de **Asterisk REST Interface (ARI)**, una API RESTful y WebSocket que permite:

1. **Control de llamadas** - Recibir, responder, transferir, colgar
2. **Audio streaming** - Recibir audio del llamante y enviar audio sintetizado
3. **Estado de extensiones** - Consultar disponibilidad de agentes
4. **Manipulación de canales** - Crear bridges, reproducir audio, establecer variables

---

## Arquitectura de Integración

```
┌──────────────────────────────────────────────────────────────────────────┐
│                              ASTERISK                                    │
│                                                                          │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────────────────────┐ │
│  │  Dialplan  │───▶│  Stasis App │───▶│  ARI (HTTP + WebSocket)      │ │
│  │ extensions │    │ asterisk-ai │    │  Puerto: 8088               │ │
│  └─────────────┘    │ -voice-agent│    └─────────────────────────────┘ │
│         │            └─────────────┘                  │                  │
│         │                     │                         │                  │
│         │                     │          ┌──────────────┴───────────────┐│
│         │                     │          │                              ││
│         ▼                     ▼          ▼                              ││
│  ┌─────────────┐    ┌─────────────────────┐                          ││
│  │ AudioSocket │    │  ExternalMedia (RTP) │                          ││
│  │  TCP :8090  │    │  UDP  :18080         │                          ││
│  └─────────────┘    └─────────────────────┘                          ││
└─────────┼─────────────────────────┼────────────────────────────────────┘
          │                         │
          │  WebSocket              │  Audio
          ▼                         ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                        AVA AI VOICE AGENT                               │
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────────────┐ │
│  │                       ARIClient (ari_client.py)                    │ │
│  └─────────────────────────────────────────────────────────────────────┘ │
│                                    │                                     │
│                                    ▼                                     │
│  ┌─────────────────────────────────────────────────────────────────────┐ │
│  │                       Engine (engine.py)                            │ │
│  └─────────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## Asterisk REST Interface (ARI)

### Conceptos Fundamentales

ARI tiene dos componentes principales:

1. **API REST (HTTP)** - Comandos para controlar Asterisk
2. **WebSocket** - Eventos en tiempo real

### Habilitar ARI en Asterisk

```bash
# Verificar estado de ARI
asterisk -rx "ari show status"

# Cargar módulos necesarios
asterisk -rx "module load res_ari"
asterisk -rx "module load res_ari_stasis"
asterisk -rx "module load res_ari_channels"
asterisk -rx "module load res_ari_bridges"
asterisk -rx "module load res_ari_playbacks"
asterisk -rx "module load res_ari_device_states"
```

### Configuración de ari.conf

```ini
[general]
enabled = yes
port = 8088
bindaddr = 0.0.0.0

[asterisk-ai-voice-agent]
type = user
password = your_secure_password
read = all
write = all
```

### Configuración de http.conf

```ini
[general]
enabled = yes
port = 8088
bindaddr = 0.0.0.0
```

---

## Cliente ARI - Código Completo

El archivo `src/ari_client.py` contiene la clase `ARIClient` que maneja toda la comunicación con Asterisk. A continuación el código completo:

### Clase Principal ARIClient

```python
# src/ari_client.py (líneas 29-118)
import asyncio
import json
import os
import time
import uuid
import audioop
import wave
from typing import Dict, Any, Optional, Callable, List
import aiohttp
import websockets
import structlog
from urllib.parse import quote
import ssl
from websockets.exceptions import ConnectionClosed
from websockets.asyncio.client import ClientConnection


class ARIClient:
    """A client for interacting with the Asterisk REST Interface (ARI)."""

    def __init__(self, username: str, password: str, base_url: str, app_name: str, ssl_verify: bool = True):
        self.username = username
        self.password = password
        self.app_name = app_name
        self.http_url = base_url
        self.ssl_verify = ssl_verify
        
        # Determinar esquema WebSocket basado en HTTP
        if base_url.startswith("https://"):
            ws_scheme = "wss"
            ws_host = base_url.replace("https://", "").split('/')[0]
        else:
            ws_scheme = "ws"
            ws_host = base_url.replace("http://", "").split('/')[0]
        
        safe_username = quote(username)
        safe_password = quote(password)
        
        # URL de WebSocket para recibir eventos
        self.ws_url = f"{ws_scheme}://{ws_host}/ari/events?api_key={safe_username}:{safe_password}&app={app_name}&subscribeAll=true&subscribe=ChannelAudioFrame"
        
        self.websocket: Optional[ClientConnection] = None
        self.http_session: Optional[aiohttp.ClientSession] = None
        self.running = False
        self._should_reconnect = True
        self._reconnect_attempt = 0
        self._max_reconnect_backoff = 60
        self._connected = False
        self.event_handlers: Dict[str, List[Callable]] = {}
        self.active_playbacks: Dict[str, str] = {}
        self.audio_frame_handler: Optional[Callable] = None

    def on_event(self, event_type: str, handler: Callable):
        """Alias for add_event_handler for backward compatibility."""
        self.add_event_handler(event_type, handler)

    @property
    def is_connected(self) -> bool:
        """Return true ARI connection state for readiness checks."""
        return self._connected and self.running and self.websocket is not None
```

### Método de Conexión

```python
# src/ari_client.py (líneas 68-118)
async def connect(self):
    """Connect to the ARI WebSocket and establish an HTTP session."""
    ws_scheme = "wss" if self.ws_url.startswith("wss://") else "ws"
    http_scheme = "https" if self.http_url.startswith("https://") else "http"
    
    logger.info(
        "Connecting to ARI...",
        attempt=self._reconnect_attempt + 1,
        http_scheme=http_scheme,
        ws_scheme=ws_scheme,
        http_url=self.http_url,
    )
    self._connected = False
    
    try:
        # Configurar SSL
        ssl_context = None
        if http_scheme == "https":
            if self.ssl_verify:
                ssl_context = ssl.create_default_context()
            else:
                ssl_context = ssl.create_default_context()
                ssl_context.check_hostname = False
                ssl_context.verify_mode = ssl.CERT_NONE
                logger.warning("SSL certificate verification disabled for ARI connection")

        # Crear sesión HTTP
        if self.http_session is None or self.http_session.closed:
            connector = aiohttp.TCPConnector(ssl=ssl_context) if ssl_context else None
            self.http_session = aiohttp.ClientSession(
                auth=aiohttp.BasicAuth(self.username, self.password),
                connector=connector
            )
        
        # Probar conexión HTTP a ARI
        async with self.http_session.get(f"{self.http_url}/asterisk/info") as response:
            if response.status != 200:
                raise ConnectionError(f"Failed to connect to ARI HTTP endpoint. Status: {response.status}")
            logger.info("Successfully connected to ARI HTTP endpoint.", scheme=http_scheme, ssl_verify=self.ssl_verify)

        # Conectar WebSocket
        self.websocket = await websockets.connect(self.ws_url, ssl=ssl_context)
        self.running = True
        self._connected = True
        self._reconnect_attempt = 0
        logger.info("Successfully connected to ARI WebSocket.", scheme=ws_scheme)
        
    except Exception as e:
        self._connected = False
        logger.error("Failed to connect to ARI", error=str(e), attempt=self._reconnect_attempt + 1)
        if self.http_session and not self.http_session.closed:
            await self.http_session.close()
            self.http_session = None
        raise
```

### Envío de Comandos REST

```python
# src/ari_client.py (líneas 264-325)
async def send_command(
    self,
    method: str,
    resource: str,
    data: Optional[Dict[str, Any]] = None,
    params: Optional[Dict[str, Any]] = None,
    tolerate_statuses: Optional[List[int]] = None,
) -> Dict[str, Any]:
    """Send a command to the ARI HTTP endpoint."""
    
    url = f"{self.http_url}/{resource}"
    
    # Manejar channelVars especialmente
    if params and "channelVars" in params:
        channel_vars = params.pop("channelVars")
        if data is None:
            data = {}
        data["channelVars"] = channel_vars
    
    try:
        async with self.http_session.request(method, url, json=data, params=params) as response:
            if response.status >= 400:
                reason = await response.text()
                # Caso común: leer variable de canal que no existe
                if (
                    int(response.status) == 404
                    and str(method).upper() == "GET"
                    and "/channels/" in f"/{resource}"
                    and str(resource).endswith("/variable")
                    and "Provided variable was not found" in reason
                ):
                    logger.debug("ARI channel variable not found (benign)", method=method, url=url, status=response.status, reason=reason)
                    return {"status": response.status, "reason": reason}
                
                if tolerate_statuses and response.status in tolerate_statuses:
                    logger.debug("ARI command tolerated non-2xx", method=method, url=url, status=response.status, reason=reason)
                else:
                    logger.error("ARI command failed", method=method, url=url, status=response.status, reason=reason)
                
                return {"status": response.status, "reason": reason}
            
            if response.status == 204:
                return {"status": response.status}
            
            return await response.json()
    
    except aiohttp.ClientError as e:
        logger.error("ARI HTTP request failed", exc_info=True)
        return {"status": 500, "reason": str(e)}
```

### Responder una Llamada

```python
# src/ari_client.py (líneas 380-383)
async def answer_channel(self, channel_id: str):
    """Answer a channel."""
    logger.info("Answering channel", channel_id=channel_id)
    await self.send_command("POST", f"channels/{channel_id}/answer")
```

### Colgar una Llamada

```python
# src/ari_client.py (líneas 385-393)
async def hangup_channel(self, channel_id: str):
    """Hang up a channel."""
    logger.info("Hanging up channel", channel_id=channel_id)
    # 404 es okay - puede que ya esté colgada
    response = await self.send_command("DELETE", f"channels/{channel_id}", tolerate_statuses=[404])
    if response and response.get("status") == 404:
        logger.debug("Channel hangup failed (404), likely already hung up.", channel_id=channel_id)
```

### Reproducir Audio

```python
# src/ari_client.py (líneas 427-441)
async def play_media(self, channel_id: str, media_uri: str) -> Optional[Dict[str, Any]]:
    """Play media on a channel."""
    logger.info("Playing media on channel", channel_id=channel_id, media_uri=media_uri)
    return await self.send_command("POST", f"channels/{channel_id}/play", data={"media": media_uri})


async def play_sound(self, channel_id: str, sound_file: str) -> Optional[Dict[str, Any]]:
    """
    Convenience wrapper to play an Asterisk sound file (e.g. "custom/please-wait").
    """
    media_uri = (sound_file or "").strip()
    if not media_uri:
        return None
    if not any(media_uri.startswith(prefix) for prefix in ("sound:", "file:", "recording:")):
        media_uri = f"sound:{media_uri}"
    return await self.play_media(channel_id, media_uri)
```

### Establecer Variable de Canal

```python
# src/ari_client.py (líneas 474-490)
async def set_channel_var(self, channel_id: str, variable: str, value: str = "") -> bool:
    """Set a channel variable via ARI."""
    try:
        resp = await self.send_command(
            "POST",
            f"channels/{channel_id}/variable",
            data={"variable": variable, "value": value},
        )
        # Algunas implementaciones retornan {} en éxito
        return resp is not None
    except Exception:
        logger.error("Failed to set channel variable", channel_id=channel_id, variable=variable, exc_info=True)
        return False
```

### Crear Canal ExternalMedia

```python
# src/ari_client.py (líneas 968-1009)
async def create_external_media_channel(
    self,
    app: str,
    external_host: str,
    format: str = "ulaw",
    direction: str = "both",
    encapsulation: str = "rtp"
) -> Optional[Dict[str, Any]]:
    """Create an External Media channel for RTP communication."""
    try:
        response = await self.send_command(
            "POST",
            "channels/externalMedia",
            data={
                "app": app,
                "external_host": external_host,  # "192.168.1.100:18080"
                "format": format,
                "direction": direction,
                "encapsulation": encapsulation
            }
        )
        
        if response and response.get("id"):
            logger.info("External Media channel created", 
                       channel_id=response["id"], 
                       external_host=external_host,
                       format=format)
            return response
        else:
            logger.error("Failed to create External Media channel", response=response)
            return None
            
    except Exception as e:
        logger.error("Error creating External Media channel", 
                    external_host=external_host, 
                    error=str(e))
        return None
```

### Originar Llamada Saliente

```python
# src/ari_client.py (líneas 327-355)
async def originate_channel(
    self,
    *,
    endpoint: str,
    app: str,
    app_args: str = "",
    timeout: int = 60,
    caller_id: str = "",
    channel_vars: Optional[Dict[str, Any]] = None,
) -> Dict[str, Any]:
    """Originate an outbound channel via ARI (POST /channels)."""
    
    params: Dict[str, Any] = {
        "endpoint": str(endpoint),      # "SIP/6001" o "Local/1000@from-internal"
        "app": str(app),                 # "asterisk-ai-voice-agent"
        "timeout": str(int(timeout)),
    }
    if app_args:
        params["appArgs"] = str(app_args)
    if caller_id:
        params["callerId"] = str(caller_id)
    if channel_vars:
        params["channelVars"] = channel_vars
    
    return await self.send_command("POST", "channels", params=params)
```

### Crear y Manejar Bridge

```python
# src/ari_client.py (líneas 493-513)
async def create_bridge(self, bridge_type: str = "mixing") -> Optional[str]:
    """Create a new bridge for channel mixing."""
    try:
        response = await self.send_command(
            "POST",
            "bridges",
            data={
                "type": bridge_type,
                "name": f"bridge_{uuid.uuid4().hex[:8]}"
            }
        )
        
        if response.get("id"):
            logger.info("Bridge created", bridge_id=response["id"], bridge_type=bridge_type)
            return response["id"]
        else:
            logger.error("Failed to create bridge", response=response)
            return None
    except Exception as e:
        logger.error("Error creating bridge", error=str(e))
        return None


# src/ari_client.py (líneas 587-626)
async def add_channel_to_bridge(self, bridge_id: str, channel_id: str) -> bool:
    """Add a channel to a bridge."""
    try:
        response = await self.send_command(
            "POST",
            f"bridges/{bridge_id}/addChannel",
            data={"channel": channel_id}
        )

        status = response.get("status") if isinstance(response, dict) else None
        if status is not None:
            if 200 <= int(status) < 300:
                logger.info("Channel added to bridge", bridge_id=bridge_id, channel_id=channel_id, status=status)
                return True
            # Idempotencia: Asterisk puede retornar conflicto si el canal ya está en el bridge
            reason = str(response.get("reason", "") or "")
            if int(status) in (409, 422) and ("already" in reason.lower()) and ("bridge" in reason.lower()):
                logger.info("Channel already in bridge (treated as success)", bridge_id=bridge_id, channel_id=channel_id)
                return True
            else:
                logger.error("Failed to add channel to bridge", bridge_id=bridge_id, channel_id=channel_id, status=status)
                return False

        logger.info("Channel add-to-bridge response without status; assuming success", bridge_id=bridge_id, channel_id=channel_id)
        return True

    except Exception as e:
        logger.error("Error adding channel to bridge", bridge_id=bridge_id, channel_id=channel_id, error=str(e))
        return False
```

### Continuar en Dialplan

```python
# src/ari_client.py (líneas 357-378)
async def continue_in_dialplan(
    self,
    channel_id: str,
    *,
    context: str,
    extension: str = "s",
    priority: int = 1,
    label: Optional[str] = None,
) -> bool:
    """Return a Stasis channel back to the dialplan (POST /channels/{id}/continue)."""
    params: Dict[str, Any] = {
        "context": str(context),
        "extension": str(extension),
        "priority": str(int(priority)),
    }
    if label:
        params["label"] = str(label)
    
    resp = await self.send_command("POST", f"channels/{channel_id}/continue", params=params)
    status = resp.get("status") if isinstance(resp, dict) else None
    if status is not None and int(status) >= 400:
        return False
    return True
```

### Listener de Eventos WebSocket

```python
# src/ari_client.py (líneas 120-212)
async def start_listening(self):
    """Start listening for events from the ARI WebSocket with automatic reconnection."""
    await self._listen_with_reconnect()


async def _listen_with_reconnect(self):
    """Supervised listener loop with automatic reconnection."""
    
    while self._should_reconnect:
        # Asegurar conexión antes de escuchar
        if not self.running or not self.websocket:
            try:
                await self.connect()
            except Exception as e:
                self._reconnect_attempt += 1
                backoff = min(2 ** self._reconnect_attempt, self._max_reconnect_backoff)
                logger.warning("ARI connection failed, will retry", attempt=self._reconnect_attempt, backoff_seconds=backoff, error=str(e))
                await asyncio.sleep(backoff)
                continue

        logger.info("Starting ARI event listener.")
        try:
            async for message in self.websocket:
                try:
                    event_data = json.loads(message)
                    event_type = event_data.get("type")
                    
                    # Manejar audio frames de ExternalMedia
                    if event_type == "ChannelAudioFrame":
                        channel = event_data.get('channel', {})
                        channel_id = channel.get('id')
                        logger.debug("ChannelAudioFrame received", channel_id=channel_id)
                        asyncio.create_task(self._on_audio_frame(channel, event_data))
                    
                    #分发 eventos a handlers registrados
                    if event_type and event_type in self.event_handlers:
                        for handler in self.event_handlers[event_type]:
                            asyncio.create_task(handler(event_data))
                            
                except json.JSONDecodeError:
                    logger.warning("Failed to decode ARI event JSON", message=message)
                    
        except ConnectionClosed:
            self._connected = False
            self.running = False
            self.websocket = None
            if self._should_reconnect:
                self._reconnect_attempt += 1
                backoff = min(2 ** self._reconnect_attempt, self._max_reconnect_backoff)
                logger.warning("ARI WebSocket connection closed, will reconnect", attempt=self._reconnect_attempt, backoff_seconds=backoff)
                await asyncio.sleep(backoff)
            else:
                logger.info("ARI WebSocket closed (shutdown requested).")
                break
                
        except Exception as e:
            self._connected = False
            self.running = False
            self.websocket = None
            if self._should_reconnect:
                self._reconnect_attempt += 1
                backoff = min(2 ** self._reconnect_attempt, self._max_reconnect_backoff)
                logger.error("ARI listener error, will reconnect", attempt=self._reconnect_attempt, backoff_seconds=backoff, error=str(e), exc_info=True)
                await asyncio.sleep(backoff)
            else:
                logger.error("ARI listener error (shutdown requested).", exc_info=True)
                break
    
    logger.info("ARI reconnect supervisor stopped.")
```

### Registro de Handlers de Eventos

```python
# src/ari_client.py (líneas 230-235)
def add_event_handler(self, event_type: str, handler: Callable):
    """Register a handler for a specific ARI event type."""
    if event_type not in self.event_handlers:
        self.event_handlers[event_type] = []
    self.event_handlers[event_type].append(handler)
    logger.debug("Added event handler", event_type=event_type, handler=handler.__name__)
```

---

## Motor Principal - Código Completo

El archivo `src/engine.py` contiene la lógica principal:

### Inicialización del Engine

```python
# src/engine.py (líneas 603-868)
async def start(self):
    """Start the engine and ARI reconnect supervisor."""
    
    # 1) Cargar providers primero (bajo riesgo)
    await self._load_providers()
    
    # 2) Inicializar sistema de herramientas
    try:
        from src.tools.registry import tool_registry
        tool_registry.initialize_default_tools()
        tools_config = getattr(self.config, 'tools', None)
        if tools_config:
            tool_registry.initialize_http_tools_from_config(tools_config)
        in_call_tools_config = getattr(self.config, 'in_call_tools', None)
        if in_call_tools_config:
            tool_registry.initialize_in_call_http_tools_from_config(in_call_tools_config, cache_key="global")
        logger.info("Tool calling system initialized", tool_count=len(tool_registry.list_tools()))
    except Exception as e:
        logger.warning(f"Failed to initialize tool calling system: {e}", exc_info=True)

    # 3) Iniciar pipeline orchestrator
    try:
        await self.pipeline_orchestrator.start()
    except PipelineOrchestratorError as exc:
        logger.info("Pipeline orchestrator not configured - using full agent provider mode.", detail=str(exc))
    except Exception as exc:
        logger.warning("Unexpected error starting pipeline orchestrator - falling back to direct provider mode", error=str(exc))

    # 4) Iniciar health server
    try:
        asyncio.create_task(self._start_health_server())
    except Exception:
        logger.debug("Health server failed to start", exc_info=True)

    logger.info("Runtime modes", audio_transport=self.config.audio_transport, downstream_mode=self.config.downstream_mode)

    # 5) Preparar AudioSocket transport
    if self.config.audio_transport == "audiosocket":
        try:
            if not self.config.audiosocket:
                raise ValueError("AudioSocket configuration not found")

            host = self.config.audiosocket.host
            port = self.config.audiosocket.port
            self.audio_socket_server = AudioSocketServer(
                host=host,
                port=port,
                on_uuid=self._audiosocket_handle_uuid,
                on_audio=self._audiosocket_handle_audio,
                on_disconnect=self._audiosocket_handle_disconnect,
                on_dtmf=self._audiosocket_handle_dtmf,
            )
            await self.audio_socket_server.start()
            logger.info("AudioSocket server listening", host=host, port=port)
            
            # Configurar streaming manager
            as_format = None
            try:
                if self.config.audiosocket and hasattr(self.config.audiosocket, 'format'):
                    as_format = self.config.audiosocket.format
            except Exception:
                as_format = None
            self.streaming_playback_manager.set_transport(
                audio_transport=self.config.audio_transport,
                audiosocket_server=self.audio_socket_server,
                audiosocket_format=as_format,
            )
        except Exception as exc:
            logger.error("Failed to start AudioSocket transport", error=str(exc), exc_info=True)
            self.audio_socket_server = None

    # 6) Preparar RTP server para ExternalMedia
    if self.config.audio_transport == "externalmedia":
        try:
            if not self.config.external_media:
                raise ValueError("ExternalMedia configuration not found")
            
            rtp_host = self.config.external_media.rtp_host
            rtp_port = int(getattr(self.config.external_media, "rtp_port", 0) or 18080)
            codec = getattr(self.config.external_media, "codec", "ulaw")
            format = getattr(self.config.external_media, "format", "slin16")
            sample_rate = getattr(self.config.external_media, "sample_rate", None)
            
            # Inferir sample_rate del formato
            if not sample_rate:
                if format in ("slin16", "linear16", "pcm16"):
                    sample_rate = 16000
                elif format in ("slin", "linear"):
                    sample_rate = 8000
                else:
                    sample_rate = 8000
            
            port_range = self._parse_port_range(
                getattr(self.config.external_media, "port_range", None),
                rtp_port,
            )
            allowed_remote_hosts = self._resolve_allowed_remote_hosts(
                getattr(self.config.external_media, "allowed_remote_hosts", None),
                getattr(self.config.asterisk, "host", None),
            )
            lock_remote_endpoint = bool(getattr(self.config.external_media, "lock_remote_endpoint", True))
            
            # Crear RTP server
            self.rtp_server = RTPServer(
                host=rtp_host,
                port=rtp_port,
                engine_callback=self._on_rtp_audio,
                codec=codec,
                format=format,
                sample_rate=sample_rate,
                port_range=port_range,
                allowed_remote_hosts=allowed_remote_hosts,
                lock_remote_endpoint=lock_remote_endpoint,
            )
            
            await self.rtp_server.start()
            logger.info("RTP server started for ExternalMedia transport", 
                       host=rtp_host, port=rtp_port, codec=codec, format=format, sample_rate=sample_rate)
            
            self.streaming_playback_manager.set_transport(
                rtp_server=self.rtp_server,
                audio_transport=self.config.audio_transport,
            )
        except Exception as exc:
            logger.error("Failed to start ExternalMedia RTP transport", error=str(exc), exc_info=True)
            self.rtp_server = None

    # 7) Iniciar ARI listener
    self.ari_client.add_event_handler("PlaybackFinished", self._on_playback_finished)
    if not self._ari_listener_task or self._ari_listener_task.done():
        self._ari_listener_task = asyncio.create_task(self.ari_client.start_listening())
        self._ari_listener_task.add_done_callback(self._on_ari_listener_task_done)
    
    # 8) Outbound scheduler
    try:
        if not self._outbound_scheduler_task:
            self._outbound_scheduler_task = asyncio.create_task(self._outbound_scheduler_loop())
    except Exception:
        logger.debug("Failed to start outbound scheduler task", exc_info=True)
    
    logger.info("Engine started and listening for calls.")
```

### Manejo de StasisStart

```python
# src/engine.py (líneas 2373-2438)
async def _handle_stasis_start(self, event: dict):
    """Handle StasisStart events - Hybrid ARI approach with single handler."""
    logger.info("StasisStart event received", event_data=event)
    channel = event.get('channel', {})
    channel_id = channel.get('id')
    channel_name = channel.get('name', '')
    args = event.get('args', [])
    
    # Remover de pre-stasis tracking
    self._pre_stasis_channels.discard(channel_id)
    
    logger.info("Channel analysis", 
               channel_id=channel_id,
               channel_name=channel_name,
               args=args,
               is_caller=self._is_caller_channel(channel),
               is_local=self._is_local_channel(channel))
    
    # Stasis args reservados para flujos de control interno
    if args and len(args) > 0:
        action_type = str(args[0] or "").strip().lower()
        if action_type in ("outbound", "outbound_amd"):
            await self._handle_outbound_stasis(channel_id, channel, args)
            return

        # Agent action (transfer, voicemail, queue, etc.)
        logger.info(f"AGENT ACTION - Stasis entry with action: {action_type}", channel_id=channel_id, action_type=action_type)
        await self._handle_agent_action_stasis(channel_id, channel, args)
        return
    
    if self._is_caller_channel(channel):
        # Este es el canal del llamante - FLUJO PRINCIPAL
        logger.info("Processing caller channel", channel_id=channel_id)
        await self._handle_caller_stasis_start_hybrid(channel_id, channel)
    elif self._is_local_channel(channel):
        # Canales Local son piernas auxiliares (ej. transferencias)
        logger.info("Local channel entered Stasis", channel_id=channel_id, channel_name=channel_name)
        await self._handle_local_stasis_start_hybrid(channel_id, channel)
    elif self._is_audiosocket_channel(channel):
        logger.info("AudioSocket channel entered Stasis", channel_id=channel_id, channel_name=channel_name)
        await self._handle_audiosocket_channel_stasis_start(channel_id, channel)
    elif self._is_external_media_channel(channel):
        logger.info("ExternalMedia channel entered Stasis", channel_id=channel_id, channel_name=channel_name)
        await self._handle_external_media_stasis_start(channel_id, channel)
    else:
        logger.warning("Unknown channel type in StasisStart", channel_id=channel_id, channel_name=channel_name)
```

### Iniciar Canal ExternalMedia

```python
# src/engine.py (líneas 2440-2489)
async def _start_external_media_channel(self, caller_channel_id: str) -> Optional[str]:
    """Allocate RTP resources y origina el ExternalMedia channel via ARI."""
    if not self.config.external_media:
        logger.error("ExternalMedia configuration missing", caller_channel_id=caller_channel_id)
        return None
    if not self.rtp_server:
        logger.error("RTP server unavailable", caller_channel_id=caller_channel_id)
        return None

    try:
        port = await self.rtp_server.allocate_session(caller_channel_id)
    except Exception as exc:
        logger.error("RTP session allocation failed", caller_channel_id=caller_channel_id, error=str(exc), exc_info=True)
        return None

    bind_host = self.config.external_media.rtp_host
    advertise_host = getattr(self.config.external_media, 'advertise_host', None) or bind_host
    
    if advertise_host in ("0.0.0.0", "::"):
        advertise_host = "127.0.0.1"
    
    codec = getattr(self.config.external_media, "codec", "ulaw")
    direction = getattr(self.config.external_media, "direction", "both")
    external_host = f"{advertise_host}:{port}"

    try:
        response = await self.ari_client.create_external_media_channel(
            app=self.config.asterisk.app_name,
            external_host=external_host,
            format=codec,
            direction=direction,
            encapsulation="rtp",
        )
    except Exception as exc:
        logger.error("ARI create_external_media_channel failed", caller_channel_id=caller_channel_id, external_host=external_host, error=str(exc), exc_info=True)
        await self.rtp_server.cleanup_session(caller_channel_id)
        return None

    if response and response.get("id"):
        logger.info("ExternalMedia channel originated", external_media_channel_id=response["id"], caller_channel_id=caller_channel_id, external_host=external_host)
        return response.get("id")
    else:
        logger.error("Failed to originate ExternalMedia channel", caller_channel_id=caller_channel_id, response=response)
        await self.rtp_server.cleanup_session(caller_channel_id)
        return None
```

---

## Transporte de Audio

### Modo 1: AudioSocket

**Dialplan:**
```asterisk
[from-ai-agent]
exten => _X!,1,NoOp(AI Agent - AudioSocket)
same => n,Set(CHANNEL(audiosocket)=on)
same => n,Set(CHANNEL(audiosocket_host)=127.0.0.1:8090)
same => n,Set(CHANNEL(audiosocket_format)=ulaw)
same => n,Stasis(asterisk-ai-voice-agent)
same => n,Hangup()
```

**Configuración ai-agent.yaml:**
```yaml
audio_transport: audiosocket

audiosocket:
  host: 127.0.0.1
  port: 8090
  format: ulaw
```

### Modo 2: ExternalMedia (RTP)

**Dialplan:**
```asterisk
[from-ai-agent]
exten => _X!,1,NoOp(AI Agent - ExternalMedia)
same => n,Stasis(asterisk-ai-voice-agent,start)
same => n,Hangup()
```

**Configuración ai-agent.yaml:**
```yaml
audio_transport: externalmedia

external_media:
  rtp_host: 127.0.0.1
  rtp_port: 18080
  port_range: 18080:18099
  codec: ulaw
  format: slin16
```

---

## Configuración del Dialplan

### Configuración Básica

```asterisk
; ============================================================
; Contexto principal para el AI Voice Agent
; ============================================================

[from-ai-agent]
; Extensión catch-all
exten => _X!,1,NoOp(===== AI Voice Agent - Llamada entrante =====)
same => n,Set(AI_CONTEXT=default)
same => n,Set(CDR(userfield)=ai-agent)
same => n,Stasis(asterisk-ai-voice-agent)
same => n,Hangup()

; ============================================================
; Configuración con múltiples DID
; ============================================================

[from-ai-agent-custom]
; DID principal - ventas
exten => 1234567890,1,NoOp(Ventas - DID principal)
same => n,Set(AI_CONTEXT=sales)
same => n,Stasis(asterisk-ai-voice-agent)
same => n,Hangup()

; Soporte técnico
exten => 1234567891,1,NoOp(Soporte técnico)
same => n,Set(AI_CONTEXT=support)
same => n,Stasis(asterisk-ai-voice-agent)
same => n,Hangup()

; Extensiones de prueba
exten => 1000,1,NoOp(Prueba - Contexto default)
same => n,Set(AI_CONTEXT=default)
same => n,Stasis(asterisk-ai-voice-agent)
same => n,Hangup()

exten => 1001,1,NoOp(Prueba - Contexto demo_hybrid)
same => n,Set(AI_CONTEXT=demo_hybrid)
same => n,Stasis(asterisk-ai-voice-agent)
same => n,Hangup()
```

---

## Eventos de Asterisk

### StasisStart

```python
{
    "type": "StasisStart",
    "channel": {
        "id": "1647223423.0",
        "name": "SIP/6001-00000001",
        "state": "Ring",
        "caller": {"number": "1001", "name": "John Doe"},
        "connected": {"number": "1234567890"}
    },
    "args": ["default"],
    "timestamp": "2024-03-12T10:30:00.000000Z"
}
```

### StasisEnd

```python
{
    "type": "StasisEnd",
    "channel": {"id": "1647223423.0", "name": "SIP/6001-00000001"},
    "timestamp": "2024-03-12T10:35:00.000000Z"
}
```

### ChannelHangupRequest

```python
{
    "type": "ChannelHangupRequest",
    "channel": {"id": "1647223423.0"},
    "timestamp": "2024-03-12T10:35:00.000000Z"
}
```

### PlaybackFinished

```python
{
    "type": "PlaybackFinished",
    "playback": {"id": "1647223423.1", "media_uri": "sound:ai-generated/greeting-123"},
    "timestamp": "2024-03-12T10:30:05.000000Z"
}
```

### ChannelDtmfReceived

```python
{
    "type": "ChannelDtmfReceived",
    "channel": {"id": "1647223423.0"},
    "digit": "5",
    "duration_ms": 100,
    "timestamp": "2024-03-12T10:30:02.000000Z"
}
```

---

## Herramientas de Telefonía

### TransferTool

```python
# src/tools/telephony/transfer.py (líneas 348-510)
# Transferencia ciega
result = await context.ari_client.send_command(
    "POST",
    "channels",
    params={
        "endpoint": f"Local/{destination}@from-internal",
        "app": "asterisk-ai-voice-agent"
    }
)
```

### CheckExtensionStatusTool

```python
# src/tools/telephony/check_extension_status.py (líneas 224-260)
# Verificar estado de extensión
resp = await context.ari_client.send_command("GET", f"ari/deviceStates/{device_name}")
```

### HangupTool

```python
# src/tools/telephony/hangup.py
await context.ari_client.hangup_channel(channel_id)
```

---

## Ejemplos de Integración

### Ejemplo 1: Inicializar ARIClient Completo

```python
import asyncio
from src.ari_client import ARIClient

async def main():
    # Crear cliente ARI
    ari_client = ARIClient(
        username="asterisk",
        password="your_password",
        base_url="http://127.0.0.1:8088",
        app_name="asterisk-ai-voice-agent",
        ssl_verify=True
    )
    
    # Conectar
    await ari_client.connect()
    
    # Registrar handlers
    def handle_stasis_start(event):
        print(f"StasisStart: {event}")
    
    def handle_stasis_end(event):
        print(f"StasisEnd: {event}")
    
    ari_client.add_event_handler("StasisStart", handle_stasis_start)
    ari_client.add_event_handler("StasisEnd", handle_stasis_end)
    
    # Iniciar listener
    await ari_client.start_listening()

asyncio.run(main())
```

### Ejemplo 2: Responder y Reproducir Audio

```python
# Responder llamada
channel_id = "1647223423.0"
await ari_client.answer_channel(channel_id)

# Reproducir mensaje
await ari_client.play_sound(channel_id, "welcome-message")

# O reproducir audio TTS
audio_data = tts_engine.generate("Hola, bienvenido")
with open("/mnt/asterisk_media/ai-generated/response.ulaw", "wb") as f:
    f.write(audio_data)

await ari_client.play_media(channel_id, "sound:ai-generated/response")
```

### Ejemplo 3: Transferencia Ciega

```python
# Transferencia ciega
destination = "6001"

result = await ari_client.originate_channel(
    endpoint=f"Local/{destination}@from-internal/n",
    app="asterisk-ai-voice-agent",
    caller_id="AI Agent <6789>"
)
```

### Ejemplo 4: Verificar Estado de Extensión

```python
# Device State API
device_name = "SIP/6001"
resp = await ari_client.send_command("GET", f"ari/deviceStates/{device_name}")
if resp.get("state") == "NOT_INUSE":
    print("Disponible")
```

### Ejemplo 5: Crear Bridge y Mezclar

```python
# Crear bridge
bridge_id = await ari_client.create_bridge(bridge_type="mixing")

# Añadir canales
await ari_client.add_channel_to_bridge(bridge_id, caller_channel_id)
await ari_client.add_channel_to_bridge(bridge_id, agent_channel_id)

# Reproducir música en espera
await ari_client.play_audio_via_bridge(bridge_id, "sound:hold-music")

# Destruir bridge al terminar
await ari_client.destroy_bridge(bridge_id)
```

### Ejemplo 6: Establecer Variables de Canal

```python
# Establecer contexto AI
await ari_client.set_channel_var(channel_id, "AI_CONTEXT", "sales")
await ari_client.set_channel_var(channel_id, "CALLER_NAME", "John Doe")

# Leer variable
resp = await ari_client.send_command(
    "GET", 
    f"channels/{channel_id}/variable",
    params={"variable": "AI_CONTEXT"}
)
context = resp.get("value")
```

---

## Verificación

```bash
# Probar ARI
curl -u asterisk:password http://127.0.0.1:8088/asterisk/info

# Ver canales
asterisk -rx "core show channels"

# Ver bridges
asterisk -rx "bridge show"

# Logs
asterisk -rvvvv
```
