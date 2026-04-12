---
title: Pipecat + Asterisk ARI Implementation Guide
description: Complete guide to integrating Pipecat voice pipeline with Asterisk ARI
---

# Pipecat + Asterisk ARI Implementation Guide

Esta guía detalla cómo Dograh integra Pipecat con Asterisk usando el Asterisk REST Interface (ARI). Cubriremos cada componente, cómo se comunican, y el flujo completo de llamadas.

## 1. Arquitectura General del Sistema

```
+---------------------------------------------------------------------------------------------------------+
|                              SERVIDOR ASTERISK                                                           |
|                                                                                                 |
|  +--------------------------------------------------------------------------------------------------+   |
|  |                    Dialplan (extensions.conf)                                                  |   |
|  |  inbound call --> Stasis(app) --> ARI Events --> WebSocket handler                               |   |
|  +--------------------------------------------------------------------------------------------------+   |
|                                                                                                 |
|  +--------------------------------------------------------------------------------------------------+   |
|  |                    Módulos de Asterisk                                                           |   |
|  |  +---------+    +-------------+    +--------------+    +-------------+                        |   |
|  |  | chan_   |    |   ARI      |    |  Stasis    |    |  PJSIP    |                        |   |
|  |  |websocket|    | REST API  |    | Application|    |  (SIP)   |                        |   |
|  |  +---------+    +-------------+    +--------------+    +-------------+                        |   |
|  +--------------------------------------------------------------------------------------------------+   |
|                                                                                                 |
|  asterisk:5080 -----> WebSocket (PCM audio)                                                             |
+---------------------------------------------------------------------------------------------------------+
                                              |
                               raw PCM 16-bit / 16kHz / mono
                                              |
                                              v
+---------------------------------------------------------------------------------------------------------+
|                           DOGRAH BACKEND                                                              |
|                                                                                                 |
|  +------------------------------------------------------------------------------------------------+ |
|  |                    ARI Manager Process                                           |            |
|  |  - WebSocket listener para eventos ARI                                       |            |
|  |  - Procesa StasisStart/StasisEnd/ChannelStateChange                       |            |
|  |  - Crea external media channels                                          |            |
|  +------------------------------------------------------------------------------------------------+ |
|                                    |                                                             |
|                                    v                                                             |
|  +------------------------------------------------------------------------------------------------+ |
|  |                    Pipecat Pipeline                                      |            |
|  |                                                                       |            |
|  |   [WebSocket Transport] --> [STT] --> [LLM] --> [TTS] --> [WebSocket] |            |
|  |                                                                       |            |
|  |   - FastAPIWebsocketTransport (maneja WebSocket)                        |            |
|  |   - Serializers (AsteriskFrameSerializer)                              |            |
|  |   - Audio buffers y mixers                                             |            |
|  +------------------------------------------------------------------------------------------------+ |
+---------------------------------------------------------------------------------------------------------+
```

## 2. Componentes de Asterisk

Asterisk necesita varios componentes para comunicarse con Dograh:

### 2.1 ari.conf - Configuración de ARI

El archivo `ari.conf` define el usuario y contraseña para la API REST de Asterisk:

```ini
[general]
enabled = yes
; Habilita el servidor WebSocket para eventos ARI
bindaddr = 0.0.0.0
bindport = 8088

[dograh]
type = user
password = dograh_ari_password_2024
; Permisos de lectura
read = system,endpoint,channel,bridge,device,volume
; Permisos de escritura
write = system,endpoint,channel,bridge,device,volume
```

**Qué hace cada sección:**

- `[general]`: Configuración global del servidor ARI
- `[dograh]`: Usuario específico para Dograh con permisos sobre canales, bridges, etc.

### 2.2 http.conf - Servidor HTTP

```ini
[general]
enabled = yes
bindaddr = 0.0.0.0
bindport = 8088

[asterisk]
; Habilita WebSocket para streaming de audio
enabled = yes
```

### 2.3 extensions.conf - Plan de Llamadas

```ini
[inbound-global]
; Llamadasentrantes desde troncal SIP
exten => _X.,1,Dial(PJSIP/${EXTEN},20)
same => n,Hangup()

[dograh-stasis]
; Extensión especial que envía llamadas a la aplicación Stasis
exten => _X.,1,Stasis(dograh,${EXTEN},${CALLERID(num)})
same => n,Hangup()

[from-sip-trunk]
; Llamadas desde proveedor externo
exten => _X.,1,Set(CALLERID(num)=${CALLERID(num)})
same => n,Goto(dograh-stasis,_X.,1)
```

**Concepto clave - Stasis:**
Stasis es una aplicación incorporada en Asterisk que permite controlar llamadas vía API. Cuando una llamada entra en Stasis:

1. El control se transfiere a la aplicación externa (Dograh)
2. Asterisk envía eventos via WebSocket
3. La aplicación externa puede manipular el canal

### 2.4 websocket_client.conf - Streaming de Audio

Este archivo es CRÍTICO. Define cómo Asterisk se conecta a nuestro servidor para enviar/recibir audio:

```ini
[general]
enabled = yes
; URL donde Dograh acepta conexiones WebSocket
url = wss://api.dograh.com/ws/ari
; Parámetros que se añaden a la URL como query string
params = workflow_id,user_id,workflow_run_id

[dograh-endpoint]
; Debe coincidir con el transportsp_name usado en create_external_media
context = default
```

## 3. Flujo Completo de una Llamada Saliente

Explicaremos paso a paso cómo funciona una llamada saliente (outbound):

### Diagrama de Secuencia

```
+-----------+   +-----------+   +-----------+   +-----------+   +-----------+
|   UI/    |    |   API   |    | Asterisk |    |  ARI    |    |Pipeline |
|  Admin   |    | Backend |    |  Server |    |Manager  |    | Pipecat |
+----+-----+   +----+-----+   +----+-----+   +----+-----+   +----+-----+
     |               |               |               |               |
     | POST /initiate-call           |               |               |
     |---------->|                   |               |               |
     |           |                   |               |               |
     |           | POST /ari/channels|               |               |
     |           |------------------>|               |               |
     |           |                   |               |               |
     |           |    201 Created    |               |               |
     |           |<------------------|               |               |
     |           |                   |               |               |
     |           |         Channel State: Ring       |               |
     |           |           ----------------------->|               |
     |           |                   |               |               |
     |           |                   |         StasisStart (state=Up)
     |           |                   |----------------->|               |
     |           |                   |               |               |
     |           |                   |   Crea externalMedia    |               |
     |           |                   |---------------|------->|               |
     |           |                   |               |               |
     |           |                   |         << WebSocket connect >>
     |           |                   |               |<------|--->|
     |           |                   |               |               |
     |           |                   |        Bridge channels  |               |
     |           |                   |---------------|------->|               |
     |           |                   |               |               |
     |           |                   |  Inicia stream            |               |
     |           |                   |---------------|------->|               |
     |           |                   |               |               |
```

### 3.1 Paso 1: Iniciar Llamada (API)

El flujo comienza cuando la UI o API llama a `initiate-call`:

```python
# api/services/telephony/providers/ari_provider.py
async def initiate_call(
    self,
    to_number: str,
    webhook_url: str,
    workflow_run_id: Optional[int] = None,
    from_number: Optional[str] = None,
    **kwargs
) -> CallInitiationResult:
    # Construir el endpoint SIP
    # PJSIP es el módulo moderno de canales SIP en Asterisk
    sip_endpoint = f"PJSIP/{to_number}"

    # Parámetros para crear el canal
    params = {
        "endpoint": sip_endpoint,
        "app": self.app_name,  # "dograh"
        # appArgs pasan contexto al canal para recuperarlo después
        "appArgs": ",".join([
            f"workflow_run_id={workflow_run_id}",
            f"workflow_id={kwargs.get('workflow_id')}",
            f"user_id={kwargs.get('user_id')}",
        ]),
    }

    if from_number:
        params["callerId"] = from_number

    # Llamar a la API REST de Asterisk
    response = await session.post(
        f"{self.base_url}/channels",
        params=params,
        auth=self._get_auth()
    )
```

**Qué sucede en Asterisk:**

1. Asterisk crea un nuevo canal PJSIP hacia `to_number`
2. El canal entra a la aplicación Stasis "dograh"
3. Asterisk comienza a timbrar el teléfono destino

### 3.2 Paso 2: Evento StasisStart (ARI Manager)

El `ARIManager` escucha eventos via WebSocket. Cuando recibe `StasisStart`:

```python
# ari_manager.py - ARIConnection._handle_event()

async def _handle_event(self, raw_data: str):
    event = json.loads(raw_data)

    if event["type"] == "StasisStart":
        channel = event["channel"]
        channel_id = channel["id"]
        channel_state = channel["state"]  # "Up" para salientes

        app_args = event.get("args", [])

        # Extraer argumentos de appArgs
        args_dict = {}
        for arg in app_args:
            for pair in arg.split(","):
                if "=" in pair:
                    key, value = pair.split("=", 1)
                    args_dict[key.strip()] = value.strip()

        workflow_run_id = args_dict.get("workflow_run_id")

        # Llamar al handler
        await self._handle_stasis_start(
            channel_id, channel_state, workflow_run_id,
            workflow_id, user_id
        )
```

**Estados del canal:**

- `Ring`: La llamada está timbrando (entrante)
- `Up`: La llamada fue contestada (saliente)
- `Down`: La llamada terminó

### 3.3 Paso 3: Crear External Media Channel

Para transmitir audio, necesitamos un "external media channel":

```python
# ari_manager.py - _create_external_media()

async def _create_external_media(
    self,
    workflow_id: str,
    user_id: str,
    workflow_run_id: str,
) -> str:
    """
    Crea un canal de媒体 externo.
    Asterisk se conectará a nuestro servidor via WebSocket.
    """
    # transport_data con v() añade query params a la URL
    # Resultado: wss://api.dograh.com/ws/ari?workflow_id=1&user_id=2&workflow_run_id=3
    transport_data = (
        f"v(workflow_id={workflow_id},"
        f"user_id={user_id},"
        f"workflow_run_id={workflow_run_id})"
    )

    # Llamar a ARI para crear el canal externo
    result = await self._ari_request(
        "POST",
        "/channels/externalMedia",
        params={
            "app": self.app_name,
            "external_host": self.ws_client_name,  # "dograh-endpoint"
            "format": "ulaw",           # Formato de audio
            "transport": "websocket",    # Usar WebSocket para audio
            "connection_type": "client", # Asterisk es cliente, nosotros servidor
        },
    )

    return result.get("id")
```

### 3.4 Paso 4: Bridge (Mezclar Canales)

Conectar el canal de llamada con el canal de media externa:

```python
# ari_manager.py - _create_bridge_and_add_channels()

async def _create_bridge_and_add_channels(self, channel_ids: list) -> str:
    # Crear un bridge "mixing" (permite audio bidireccional)
    bridge_result = await self._ari_request(
        "POST",
        "/bridges",
        params={"type": "mixing", "name": f"bridge-{channel_ids[0]}"},
    )
    bridge_id = bridge_result.get("id")

    # Añadir ambos canales al bridge
    await self._ari_request(
        "POST",
        f"/bridges/{bridge_id}/addChannel",
        params={"channel": ",".join(channel_ids)},
    )

    return bridge_id
```

### 3.5 Paso 5: Iniciar Pipeline Pipecat

Una vez establecida la conexión WebSocket, se inicia el pipeline:

```python
# run_pipeline.py - run_pipeline_ari()

async def run_pipeline_ari(
    websocket_client: WebSocket,
    channel_id: str,
    workflow_id: int,
    workflow_run_id: int,
    user_id: int,
) -> None:
    # 1. Crear configuración de audio
    # ARI usa 8kHz MULAW (same as Twilio)
    audio_config = create_audio_config(WorkflowRunMode.ARI.value)
    # AudioConfig:
    # - transport_in_sample_rate: 8000
    # - transport_out_sample_rate: 8000
    # - pipeline_sample_rate: 8000

    # 2. Crear transporte con serializer
    transport = await create_ari_transport(
        websocket_client,
        channel_id,
        workflow_run_id,
        audio_config,
        organization_id,
        vad_config,
        ambient_noise_config,
    )

    # 3. Ejecutar pipeline
    await _run_pipeline(
        transport,
        workflow_id,
        workflow_run_id,
        user_id,
        audio_config=audio_config,
    )
```

## 4. Componentes del Pipeline Pipecat

### 4.1 Transport y Serializers

Eltransport maneja la conexión WebSocket y los serializers manejan el formato de audio:

```python
# transport_setup.py - create_ari_transport()

async def create_ari_transport(...):
    # Serializer convierte frames de Asterisk
    serializer = AsteriskFrameSerializer(
        channel_id=channel_id,
        ari_endpoint=ari_endpoint,
        app_name=app_name,
        app_password=app_password,
        transfer_strategy=ARIBridgeSwapStrategy(),
        hangup_strategy=ARIHangupStrategy(),
    )

    # FastAPIWebsocketTransport maneja el WebSocket
    return FastAPIWebsocketTransport(
        websocket=websocket_client,
        params=FastAPIWebsocketParams(
            audio_in_enabled=True,
            audio_out_enabled=True,
            audio_in_sample_rate=audio_config.transport_in_sample_rate,
            audio_out_sample_rate=audio_config.transport_out_sample_rate,
            serializer=serializer,  # Convierte audio ARI <-> Pipecat
        ),
    )
```

### 4.2 AudioFlow Completo

```
+-----------------------------------------------------------------------------+
|                    FLUJO DE AUDIO                                            |
|                                                                             |
|  Asterisk                      Pipecat Pipeline              |
|  -------                      ----------------            |
|                                                                             |
|  [Mikrofono]--->[PJSIP]--->[Bridge]--->[ExternalMedia]     |
|                                             |               |
|                                             v               |
|                                    [WebSocket]              |
|                                             |               |
|                                             v               |
|  [Audio Input Buffer] <------- (raw PCM 8kHz)               |
|          |                                                    |
|          v (resample to 8kHz)                               |
|  [Speech-to-Text: Deepgram]                                 |
|          |                                                    |
|          v (text)                                          |
|  [LLM Service: OpenAI/Anthropic/Google]                    |
|          |                                                    |
|          v (text response)                                 |
|  [Text-to-Speech: Cartesia]                                |
|          |                                                    |
|          v (audio 8kHz PCM)                                |
|  [Audio Output Buffer]                                     |
|          |                                                    |
|          v                                                 |
|  [WebSocket] ---> [Asterisk] ---> [Bridge] ---> [Speaker]   |
|                                                                             |
+-----------------------------------------------------------------------------+
```

## 5. Llamada Entrante (Inbound)

### 5.1 Flujo Completo

```
+-----------+   +-----------+   +-----------+   +-----------+
|   Caller |    | Asterisk |    |   ARI   |    |  Dograh  |
| (Phone)  |    |  Server |    | Manager |    | Backend |
+----+-----+   +----+-----+   +----+-----+   +----+-----+
     |               |               |               |
     |  INVITE (SIP) |               |               |
     |-------------->|               |               |
     |               |               |               |
     |    180 Ringing|               |               |
     |<--------------|               |               |
     |               |               |               |
     |    200 OK    |               |               |
     |<--------------|               |               |
     |               |               |               |
     |     ACK      |               |               |
     |-------------->|               |               |
     |               |               |               |
     |        StasisStart(state=Ring)|               |
     |               |-------------->|               |
     |               |               |               |
     |               |         Valida quota           |
     |               |         Crea workflow_run   |
     |               |         Answer channel    |
     |               |               |               |
     |               |             POST /channels/{id}/answer
     |               |<--------------|               |
     |               |               |               |
     |               |         Crea externalMedia  |
     |               |         Bridge channels     |
     |               |         Inicia WebSocket   |
     |               |---------------|------->   |
     |               |               |               |
     |               |         << Audio Stream >>  |
     |<------------------------------------------->|
     |               |               |               |
```

### 5.2 Manejo de Llamada Entrante

```python
# ari_manager.py - _handle_inbound_stasis_start()

async def _handle_inbound_stasis_start(
    self, channel_id: str, channel_state: str, event: dict
):
    """Maneja llamada entrante (state=Ring)"""

    # 1. Verificar workflow configurado
    if not self.inbound_workflow_id:
        await self._delete_channel(channel_id)
        return

    # 2. Cargar workflow
    workflow = await db_client.get_workflow(
        self.inbound_workflow_id,
        organization_id=self.organization_id
    )

    # 3. Verificar quota
    quota_result = await check_dograh_quota_by_user_id(user_id)
    if not quota_result.has_quota:
        await self._delete_channel(channel_id)
        return

    # 4. Crear workflow run
    workflow_run = await db_client.create_workflow_run(
        name=f"ARI Inbound {caller_number}",
        workflow_id=self.inbound_workflow_id,
        mode=WorkflowRunMode.ARI,
        initial_context={"caller_number": caller_number, ...},
    )

    # 5. Contestar la llamada
    await self._answer_channel(channel_id)

    # 6. Continuar como saliente (crear external media, bridge, etc.)
    await self._handle_stasis_start(...)
```

## 6. Integración con Call Transfer

### 6.1 Flujo de Transfer

```
+-----------+   +-----------+   +-----------+   +-----------+
|  Caller  |    | Asterisk |    |   ARI   |    |Destination|
+----+-----+   +----+-----+   +----+-----+   +----+-----+
     |               |               |               |
     |     << Audio Bidireccional >> |               |
     |<------------------------------------------->   |
     |               |               |               |
     |      Herramienta transfer_call               |
     |-------------->|               |               |
     |               |               |               |
     |               |   Crear canal destino        |
     |               |-------------->|               |
     |               |               |               |
     |               |     << Audio >>|               |
     |<-------------|               |               |
     |               |               |               |
     |     Bridge swap (swap old --> new channel)    |
     |               |               |               |
     |<-------------------------------------------> (new)
     |               |               |               |
     |    Hangup canal original                   |
     |               |-------------->|               |
```

### 6.2 Implementación

```python
# ari_provider.py - transfer_call()

async def transfer_call(
    self,
    destination: str,
    transfer_id: str,
    conference_name: str,
    timeout: int = 30,
) -> Dict[str, Any]:
    """Inicia transferencia creando canal hacia destino"""

    # Crear canal hacia el destino
    params = {
        "endpoint": f"PJSIP/{destination}",
        "app": self.app_name,
        "appArgs": f"transfer,{transfer_id}",  # Indica que es transferencia
    }

    response = await session.post(
        f"{self.base_url}/channels",
        params=params,
        auth=self._get_auth(),
    )

    # El manager maneja eventos de respuesta
    # Y hace bridge swap cuando el destino contesta
```

## 7. Formato de Audio

### 7.1 Especificaciones ARI

| Propiedad   | Valor                                    |
| ----------- | ---------------------------------------- |
| Encoding    | micro-law (mulaw)                        |
| Sample Rate | 8kHz                                     |
| Bits        | 8-bit                                    |
| Channels    | Mono                                     |
| Transport   | Raw binary WebSocket frames              |
| Framing     | No hay wrapper JSON (streaming continuo) |

### 7.2 Comparación entre Proveedores

| Aspecto        | Twilio           | Vonage         | Asterisk ARI  |
| -------------- | ---------------- | -------------- | ------------- |
| Audio encoding | micro-law base64 | Raw PCM 16-bit | micro-law raw |
| Sample rate    | 8kHz             | 16kHz          | 8kHz          |
| Eventos        | HTTP webhooks    | WebSocket      | WebSocket     |
| Control        | TwiML (XML)      | NCCO (JSON)    | REST API      |
| Inbound        | Phone number     | Vonage number  | SIP trunk     |

## 8. Configuración en Dograh

### 8.1 Configuración de Organización

En la base de datos, cada organización tiene:

```json
{
    "provider": "ari",
    "ari_endpoint": "http://asterisk:8088",
    "app_name": "dograh",
    "app_password": "dograh_ari_password",
    "ws_client_name": "dograh-endpoint",
    "inbound_workflow_id": 123
}
```

### 8.2 Variables de Entorno

```bash
# api/.env
REDIS_URL=redis://:password@redis:6379
DATABASE_URL=postgresql+asyncpg://...
```

## 9. Proceso del ARI Manager

### 9.1 Inicio

```python
# ari_manager.py - main()

async def main():
    manager = ARIManager()
    await manager.start()

class ARIManager:
    async def start(self):
        # Cargar configuraciones de la base de datos
        await self._refresh_connections()

        # Loop principal
        while self._running:
            await asyncio.sleep(60)  # Refresh cada minuto
            await self._refresh_connections()
```

### 9.2 Reconnection con Backoff

```python
# ari_manager.py - ARIConnection._connection_loop()

async def _connection_loop(self):
    while self._running:
        try:
            await self._connect_and_listen()
        except Exception:
            # Exponential backoff: 1s, 2s, 4s, 8s... max 300s
            await asyncio.sleep(self._reconnect_delay)
            self._reconnect_delay = min(self._reconnect_delay * 2, 300)
```

## 10. Endpoints

### 10.1 WebSocket Pipeline

```
GET /api/v1/telephony/ws/ari?workflow_id=1&user_id=2&workflow_run_id=3
```

Los query params vienen del `transport_data` en externalMedia.

### 10.2 API REST

| Endpoint                       | Metodo | Descripcion   |
| ------------------------------ | ------ | ------------- |
| `/ari/channels`                | POST   | Crear canal   |
| `/ari/channels/{id}`           | GET    | Obtener canal |
| `/ari/channels/{id}/answer`    | POST   | Contestar     |
| `/ari/channels/{id}`           | DELETE | Colgar        |
| `/ari/bridges`                 | POST   | Crear bridge  |
| `/ari/bridges/{id}/addChannel` | POST   | Anadir canal  |

## 11. Errores Comunes y Soluciones

### 11.1 StasisStart No Llega

```
Sintoma: No se recibe evento StasisStart en el manager
Causa: app_name no coincide entre ari.conf y la config
Solucion: Verificar que app_name en ari.conf = config["app_name"]
```

### 11.2 External Media Falla

```
Sintoma: No se puede crear externalMedia channel
Causa: websocket_client.conf no configurado o mal escrito
Solucion: Verificar endpoint en websocket_client.conf
```

### 11.3 Audio No Suena

```
Sintoma: Llamada conectada pero sin audio
Causa: Sample rate mismatch entre Asterisk y pipeline
Solucion: Verificar audio_config transport_in_sample_rate = 8000
```

### 11.4 Transfer Falla

```
Sintoma: transferencia no funciona
Causa: El bridge fue destruido antes del swap
Solucion: Verificar estado del bridge antes de operar
```

## 12. Ejecutando el Sistema

### 12.1 Iniciar ARI Manager

```bash
# Como proceso separado
python -m api.services.telephony.ari_manager
```

### 12.2 Verificar Conexion

```bash
# Probar WebSocket ARI
wscat -c "ws://asterisk:8088/ari/events?api_key=dograh:password&app=dograh"

# Ver canales activos en Asterisk
asterisk -rx "core show channels"
```

### 12.3 Logs del Manager

```
# En CloudWatch/logs:
# [ARI org=123] StasisStart: channel=xxx, state=Ring
# [ARI org=123] Created external media channel: xxx
# [ARI org=123] Bridge created with channels: [xxx, yyy]
```
