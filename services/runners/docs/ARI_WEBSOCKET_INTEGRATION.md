# Integración ARI + WebSocket para Tito

Esta implementación reemplaza AudioSocket por una arquitectura ARI (Asterisk REST Interface) + ExternalMedia WebSocket, similar a como funciona Dograh.

## Arquitectura

```
┌─────────────┐     ┌─────────────┐     ┌─────────────────────────────┐
│   Cliente   │────▶│  Asterisk   │────▶│  ARI Manager (Python)       │
│    SIP      │     │   (PJSIP)   │     │  - Escucha StasisStart      │
└─────────────┘     └──────┬──────┘     │  - Resuelve trunk/agent     │
                           │            │  - Crea ExternalMedia       │
                           │ WebSocket  └─────────────────────────────┘
                           │                           │
                           ▼                           ▼
                  ┌─────────────────┐     ┌─────────────────────────┐
                  │  Stasis App     │     │  ExternalMedia Channel  │
                  │  (tito-ai)      │     │  (WebSocket)            │
                  └─────────────────┘     └───────────┬─────────────┘
                                                      │
                                                      ▼ WebSocket
                                           ┌──────────────────────┐
                                           │  /api/v1/sip/ari/    │
                                           │  audio (FastAPI)     │
                                           └──────────┬───────────┘
                                                      │
                                                      ▼
                                           ┌──────────────────────┐
                                           │  Pipecat Pipeline    │
                                           │  STT → LLM → TTS     │
                                           └──────────────────────┘
```

## Archivos Creados/Modificados

### Nuevos Archivos

1. **`app/services/sip/tito_ari_manager.py`**
   - ARI Manager adaptado de Dograh
   - Escucha eventos StasisStart/StasisEnd
   - Gestiona conexiones por trunk
   - Usa Redis para tracking de canales

2. **`config/asterisk/ari-websocket.conf`**
   - Configuración de Asterisk para ARI + WebSocket
   - Reemplaza la configuración de AudioSocket

### Archivos Modificados

3. **`app/services/sip/ari_client.py`**
   - Agregado `create_external_media_websocket()` para crear canales WebSocket

4. **`app/api/v1/sip.py`**
   - Agregado endpoint WebSocket `/ari/audio`
   - Handler de pipeline para llamadas ARI

## Configuración

### 1. Asterisk

Copiar la configuración:

```bash
cp config/asterisk/ari-websocket.conf /etc/asterisk/
```

O integrar en tu docker-compose:

```yaml
services:
  asterisk:
    volumes:
      - ./config/asterisk/ari-websocket.conf:/etc/asterisk/ari-websocket.conf:ro
```

### 2. Variables de Entorno

Agregar al `.env`:

```env
# ARI Configuration
ARI_HOST=asterisk
ARI_PORT=8088
ARI_USERNAME=tito-ai
ARI_PASSWORD=tito-ari-secret
ARI_APP_NAME=tito-ai

# API Host (para WebSocket connections)
API_HOST=pipecat-runners-api
API_PORT=8000
```

### 3. Trunk Configuration

Los trunks ahora necesitan configuración ARI en Redis:

```json
{
  "trunk_id": "trk_abc123",
  "name": "Mi Trunk",
  "mode": "inbound",
  "ari_endpoint": "http://asterisk:8088",
  "app_name": "tito-ai",
  "app_password": "tito-ari-secret",
  "api_host": "pipecat-runners-api",
  "api_port": 8000,
  "routes": [
    {
      "pattern": "1000",
      "agent_id": "agent-ventas"
    },
    {
      "pattern": "*",
      "agent_id": "agent-default"
    }
  ]
}
```

## Uso

### Iniciar ARI Manager

```python
# En tu main.py o como proceso separado
from app.services.sip.tito_ari_manager import TitoARIManager

async def start_ari_manager():
    manager = TitoARIManager()
    await manager.start()
```

### Flujo de Llamada

1. **Llamada Entrante**:
   - Cliente SIP llama a `sip:1000@tito.ai`
   - Asterisk entra en `Stasis(tito-ai,1000,+1234567890)`
   - ARI Manager recibe evento `StasisStart`

2. **Resolución**:
   - Busca trunk en Redis
   - Obtiene `agent_id` de las rutas
   - Valida que el agente exista

3. **Setup Audio**:
   - Crea ExternalMedia channel (WebSocket)
   - URL: `ws://pipecat-runners-api:8000/api/v1/sip/ari/audio?agent_id=xxx&...`
   - Contesta llamada original
   - Crea bridge y conecta canales

4. **Pipeline**:
   - Asterisk se conecta al WebSocket
   - FastAPI acepta conexión
   - Resuelve agente y crea pipeline
   - Audio fluye bidireccional

## Diferencias con AudioSocket

| Aspecto | AudioSocket (Antes) | ARI + WebSocket (Ahora) |
|---------|---------------------|-------------------------|
| Protocolo | TCP propietario | WebSocket estándar |
| Audio | 8kHz μ-law | 8kHz slin (16-bit) |
| Control | Limitado (AMI) | Total (ARI REST API) |
| Estabilidad | Se cuelga/timeout | WebSocket nativo, más estable |
| Flexibilidad | Baja | Alta (bridges, transfers) |
| Reconexión | Manual | Automática con backoff |

## Ventajas

1. **Sin AudioSocket**: No más servidor TCP en puerto 9092
2. **WebSocket Nativo**: Mejor integración con FastAPI/Pipecat
3. **Control Total**: Creas/destruyes canales dinámicamente
4. **Reconexión Automática**: ARI Manager reconecta automáticamente
5. **Multi-trunk**: Soporta múltiples trunks con diferentes configs

## Troubleshooting

### Error: "ExternalMedia creation failed"

Verificar:
- Asterisk tiene módulo `chan_websocket.so` cargado
- Configuración ARI es correcta (username/password)
- URL WebSocket es accesible desde Asterisk

### Error: "WebSocket connection refused"

Verificar:
- FastAPI está corriendo y escuchando en el puerto correcto
- No hay firewall bloqueando conexiones
- URL en `create_external_media_websocket` es correcta

### Error: "Agent not found"

Verificar:
- `agent_id` en trunk routes es correcto
- Agente existe en Redis/API Laravel
- `agent_resolution_service` funciona correctamente

## Migración desde AudioSocket

1. **Backup** tu configuración actual:
   ```bash
   cp /etc/asterisk/extensions.conf /etc/asterisk/extensions.conf.bak
   ```

2. **Actualizar** configuración Asterisk con `ari-websocket.conf`

3. **Detener** AudioSocket server (remover de main.py)

4. **Iniciar** ARI Manager en lugar de AudioSocket:
   ```python
   # En main.py lifespan
   from app.services.sip.tito_ari_manager import TitoARIManager
   
   ari_manager = TitoARIManager()
   await ari_manager.start()
   ```

5. **Testear** llamada:
   ```bash
   # Desde Asterisk CLI
   asterisk -rx "core show channels"
   asterisk -rx "ari show apps"
   ```

## TODO / Mejoras Futuras

- [ ] Implementar outbound calls (click-to-call)
- [ ] Agregar soporte para transfers
- [ ] Implementar recording
- [ ] Agregar métricas y monitoreo
- [ ] Soporte para múltiples codecs (opus, g722)
- [ ] Implementar rate limiting por trunk

## Referencias

- [Dograh ARI Implementation](https://github.com/dograh-ai/dograh/blob/main/api/services/telephony/ari_manager.py)
- [Asterisk ARI Documentation](https://docs.asterisk.org/Configuration/Interfaces/Asterisk-REST-Interface-ARI/)
- [Pipecat FastAPI WebSocket Transport](https://docs.pipecat.ai/server/transports/fastapi-websocket)
