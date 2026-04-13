# TODO: SIP Integration (Asterisk + Pipecat ARI + WebSocket)

## Estado actual

**NUEVO**: Flujo con ARI + WebSocket (reemplaza AudioSocket)
Flujo: Teléfono → Asterisk (SIP/RTP) → Stasis → ARI Manager → ExternalMedia WebSocket → Pipecat Pipeline → STT → LLM → TTS → Asterisk → Teléfono

## Completado ✅

### ARI + WebSocket Implementation (NEW)

- [x] **ARI Manager** (`app/services/sip/tito_ari_manager.py`)
    - Adaptado de Dograh ARI implementation
    - Conexión WebSocket a Asterisk ARI
    - Manejo de eventos StasisStart/StasisEnd
    - Reconexión automática con exponential backoff
    - Soporte multi-trunk

- [x] **ExternalMedia WebSocket** (`app/services/sip/ari_client.py`)
    - `create_external_media_websocket()`: Crea canal WebSocket hacia API
    - Reemplaza AudioSocket TCP
    - Audio 8kHz slin (signed linear)

- [x] **WebSocket Endpoint** (`app/api/v1/sip.py`)
    - `/api/v1/sip/ari/audio`: Endpoint WebSocket para llamadas ARI
    - Resolución de agente via `agent_resolution_service`
    - Pipeline Pipecat completo (STT → LLM → TTS)
    - Serializer para slin (signed linear 16-bit PCM)

- [x] **Documentación**
    - `docs/ARI_WEBSOCKET_INTEGRATION.md`: Guía completa de migración
    - `config/asterisk/ari-websocket.conf`: Configuración de Asterisk
    - `docs/example_lifespan.py`: Ejemplo de integración en main.py

### AudioSocket (DEPRECATED - kept for reference)

- [x] ~~AudioSocket Server~~ (`app/services/sip/audiosocket_server.py`)
    - ~~Servidor TCP en puerto 9092~~
    - **DEPRECATED**: Reemplazado por ARI + WebSocket

- [x] ~~SIP Transport~~ (`app/services/sip/transport.py`)
    - ~~`SIPAudioSocketInputTransport` / `SIPAudioSocketOutputTransport`~~
    - **DEPRECATED**: Usar ARI WebSocket en su lugar

- [x] **AMI Controller** (`app/services/sip/ami_controller.py`)
    - Conexión a Asterisk Manager Interface via panoramisk
    - Eventos DTMF y Hangup
    - GetVar para metadata de canal

- [x] **Configuración Asterisk** (`config/asterisk/`)
    - ~~`extensions.conf`: Dial(AudioSocket/...)~~ **DEPRECATED**
    - `ari-websocket.conf`: Nueva config ARI + WebSocket
    - `pjsip.conf`: endpoints SIP
    - `manager.conf`: AMI access

## Pendiente de verificar

- [ ] **Test llamada completa end-to-end**
    - Llamar al número SIP → Asterisk contesta → AudioSocket conecta → Tito habla primero (SPEAK_FIRST) → Usuario habla → Deepgram transcribe → GPT-4o responde → Cartesia genera audio → Audio llega al teléfono
    - Verificar que el resampling bidireccional funciona (8kHz↔16kHz)
    - Verificar que STT transcribe correctamente (el usuario reportó "pregunté varias veces por el nombre pero no me respondió")

## Pendiente de implementar

- [x] **Fetch AgentConfig desde backend API** ✅ IMPLEMENTADO
    - Servicio: `app/services/agent_resolution_service.py`
    - Resolución por cache (Redis) → Fallback a API Laravel
    - Endpoints Laravel: `GET /api/agents/{agent_id}/config` y `GET /api/agents/by-slug/{slug}/config`
    - Sincronización automática desde Laravel al crear/actualizar agentes
    - Ver documentación: `docs/agent-resolution.md`

- [ ] **Resolución de trunk por IP/caller**
    - Actualmente itera todas las keys `trunk:index:*` — no escala
    - Implementar lookup directo por IP origen o por DID/extension

- [ ] **Llamadas outbound (click-to-call)**
    - Endpoint API: POST `/api/v1/sip/calls` con destino + agent_id
    - Usar AMI Originate para iniciar llamada desde Asterisk
    - El contexto `tito-outbound` en extensions.conf ya existe pero necesita integración

- [ ] **Grabación de llamadas**
    - Audio buffer ya existe en el pipeline (`build_pipeline` retorna `audio_buffer`)
    - Implementar guardado a S3/storage al finalizar llamada
    - Agregar URL de grabación al webhook session.ended

- [ ] **DTMF interactivo**
    - Los eventos DTMF se capturan (via AMI y AudioSocket type 0x03)
    - Falta: lógica de IVR/menú basada en DTMF dentro del pipeline

- [ ] **Transferencia de llamada**
    - Usar AMI Redirect para transferir canal a otro extension/agente
    - Endpoint API: POST `/api/v1/sip/calls/{id}/transfer`

- [ ] **Múltiples trunks/proveedores**
    - Soporte para Twilio SIP trunk, Vonage, etc.
    - Cada proveedor puede tener codecs/rates diferentes
    - Registro dinámico de endpoints PJSIP

- [ ] **Monitoreo y métricas**
    - Dashboard de llamadas activas
    - Latencia STT/TTS por llamada
    - Calidad de audio (jitter, packet loss)

- [ ] **Seguridad**
    - Cambiar password de `pjsip.conf` (`tito123` es solo para desarrollo)
    - TLS para SIP (transport-tls en pjsip.conf)
    - ACL por IP en identify section
    - Rate limiting en trunk

## Arquitectura de archivos

```
services/runners/
├── app/
│   ├── main.py                          # Lifespan: inicia AudioSocket + AMI
│   ├── api/v1/
│   │   ├── sip.py                       # Endpoints REST para SIP (trunks CRUD)
│   │   └── sessions.py                  # Endpoints de sesiones
│   └── services/
│       ├── sip/
│       │   ├── audiosocket_server.py    # TCP server AudioSocket protocol
│       │   ├── transport.py             # Pipecat transport (Input/Output/Main)
│       │   ├── call_handler.py          # Orquestador de llamadas SIP
│       │   ├── ami_controller.py        # Asterisk Manager Interface
│       │   └── websocket_server.py      # WebSocket transport (chan_websocket)
│       ├── trunk_service.py             # CRUD trunks en Redis
│       └── agents/
│           └── pipelines/
│               ├── pipeline_builder.py  # Construye pipeline Pipecat
│               └── context_setup.py     # Setup contexto LLM
├── config/asterisk/
│   ├── extensions.conf                  # Dialplan
│   ├── pjsip.conf                       # SIP endpoints/trunks
│   ├── manager.conf                     # AMI access
│   └── ...
└── compose.yaml                         # Docker compose del runner
```

## Notas técnicas

- **Audio**: Asterisk envía slin8 (8kHz, 16-bit signed linear, mono, 160 bytes = 20ms). Pipeline usa 16kHz. Resampling con `audioop.ratecv()`.
- **UUID**: Asterisk chan_audiosocket envía UUID como 16 bytes binarios, NO como string. Se parsea con `uuid.UUID(bytes=payload)`.
- **TaskManager**: Pipecat inicializa TaskManager en `PipelineRunner.run()`. No se puede llamar `create_task()` antes de eso. Se usa patrón deferred con `_pending_conn`.
- **Docker network**: asterisk y pipecat-runners-api están en la misma red bridge `apptitoai_sail`. AudioSocket se conecta por hostname `pipecat-runners-api:9092`.
