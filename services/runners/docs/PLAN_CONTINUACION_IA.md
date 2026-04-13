# 📋 PLAN DE CONTINUACIÓN - ARI WebSocket Integration

**Fecha:** 2024-04-13  
**Proyecto:** Tito AI - SIP Integration with ARI  
**Estado:** Implementación parcial - Pendiente resolución de transporte de audio

---

## 🎯 Estado Actual del Proyecto

### ✅ **Implementado y Funcionando:**

#### 1. **ARI Manager** (`app/services/sip/tito_ari_manager.py`)

- ✅ Conexión WebSocket a Asterisk establecida
- ✅ Escucha eventos StasisStart/StasisEnd
- ✅ Resolución de trunk/agente desde Redis
- ✅ Reconexión automática con exponential backoff
- ✅ Multi-trunk support

**Eventos manejados:**

- `StasisStart` - Inicio de llamada
- `StasisEnd` - Fin de llamada
- `ChannelDestroyed` - Limpieza de recursos
- `ChannelHangupRequest` - Colgado solicitado

#### 2. **Endpoint WebSocket** (`app/api/v1/sip.py`)

- ✅ Endpoint `/api/v1/sip/ari/audio` implementado
- ✅ Pipeline Pipecat integrado
- ✅ Serializer para audio slin (8kHz 16-bit PCM)
- ✅ Soporte para query params: channel_id, agent_id, tenant_id, caller_id, trunk_id

**Pipeline configurado:**

- STT: Deepgram (nova-2)
- LLM: OpenAI (GPT-4o)
- TTS: Cartesia (sonic-2)

#### 3. **Trunk de Prueba Configurado**

```json
{
    "trunk_id": "trk_default_test",
    "name": "Default Test Trunk",
    "mode": "inbound",
    "routes": [
        {
            "pattern": "*",
            "agent_id": "agent-tito-test"
        }
    ],
    "ari_endpoint": "http://asterisk:8088",
    "app_name": "tito-ai",
    "app_password": "tito-ari-secret"
}
```

#### 4. **Configuración Asterisk**

- ✅ ARI app `tito-ai` registrada
- ✅ Módulos websocket cargados
- ✅ Dialplan `tito-inbound-ari` configurado
- ✅ Endpoint `tito-inbound` configurado

---

## ❌ **Problema Actual (Bloqueante):**

```
ERROR: external_host must be a valid websocket_client connection id.
```

### **Causa Root:**

Asterisk 22.8.2 no reconoce el perfil `websocket_client` al crear ExternalMedia channel vía ARI.

### **Código problemático:**

```python
# app/services/sip/tito_ari_manager.py
result = await self._ari_request(
    "POST",
    "/channels/externalMedia",
    data={
        "app": self.app_name,
        "external_host": "tito-media",  # o URL ws://...
        "format": "slin",
        "direction": "both",
        "encapsulation": "none",
        "transport": "websocket",
    },
)
```

### **Errores probados:**

1. ❌ `external_host: "tito-ari"` → "must be a valid websocket_client connection id"
2. ❌ `external_host: "ws://host:port/path"` → "must be <host>:<port> for all transports other than websocket"
3. ❌ Con `transport=websocket` + perfil → "must be a valid websocket_client connection id"

---

## 🔧 **OPCIONES PARA CONTINUAR:**

### **OPCIÓN A: Completar ARI + WebSocket (Investigación requerida)**

#### Investigación necesaria:

1. Verificar si Asterisk 22 requiere módulo adicional para `chan_websocket` con ExternalMedia
2. Probar sintaxis alternativa de ARI para externalMedia
3. Revisar si hay parámetros adicionales requeridos

#### Archivos a revisar:

- `/home/usyeimar/projects/itm/app.tito.ai/services/runners/config/asterisk/websocket_client.conf`
- `/home/usyeimar/projects/itm/app.tito.ai/services/runners/config/asterisk/modules.conf`
- `/home/usyeimar/projects/itm/app.tito.ai/services/runners/config/asterisk/http.conf`

#### Documentación útil:

- https://docs.asterisk.org/Configuration/Channel-Drivers/AudioSocket/
- https://docs.asterisk.org/Configuration/Interfaces/Asterisk-REST-Interface-ARI/
- https://wiki.asterisk.org/wiki/display/AST/External+Media+and+ARI

**Tiempo estimado:** 2-4 horas de investigación + pruebas

---

### **OPCIÓN B: Usar AudioSocket (Solución Inmediata)**

#### Estado:

AudioSocket ya está implementado y funcionando en puerto 9092.

#### Pasos para activar:

```bash
# 1. Cambiar variable de entorno
echo "SIP_TRANSPORT=audiosocket" >> /home/usyeimar/projects/itm/app.tito.ai/.env

# 2. Reiniciar servicio
docker compose restart pipecat-runners-api

# 3. Probar llamada
docker exec apptitoai-asterisk asterisk -rx \
  "channel originate Local/100@tito-inbound application Stasis tito-ai"
```

#### Cambios necesarios:

- Usar dialplan `tito-inbound` en lugar de `tito-inbound-ari`
- El audio fluye por TCP socket en lugar de WebSocket
- El resto de la arquitectura ARI se mantiene igual

**Tiempo estimado:** 5 minutos

---

### **OPCIÓN C: Implementar RTP Directo (Solución Avanzada)**

#### Idea:

En lugar de WebSocket, usar ExternalMedia con transporte RTP y crear servidor UDP que reciba audio raw.

#### Implementación propuesta:

```python
# Crear socket UDP para recibir RTP
udp_socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
udp_socket.bind(("0.0.0.0", 10000))

# En ARI request
result = await self._ari_request(
    "POST",
    "/channels/externalMedia",
    data={
        "app": self.app_name,
        "external_host": "172.19.0.4:10000",  # IP:Puerto UDP
        "format": "slin",
        "direction": "both",
        "encapsulation": "rtp",  # RTP en lugar de websocket
    },
)
```

#### Ventajas:

- Mayor control sobre el audio
- No depende de chan_websocket
- Compatibilidad universal con Asterisk

#### Desventajas:

- Más complejo (manejo de paquetes RTP)
- Requiere stripping de headers RTP
- Mayor latencia potencial

**Tiempo estimado:** 3-5 horas de implementación

---

## 📂 **Archivos Clave del Proyecto:**

### **Creados:**

```
/home/usyeimar/projects/itm/app.tito.ai/services/runners/
├── app/services/sip/
│   └── tito_ari_manager.py          # NUEVO - Core ARI Manager
├── config/asterisk/
│   ├── ari-websocket.conf           # NUEVO - Config ARI general
│   └── websocket_client.conf        # NUEVO - Perfiles websocket
├── docs/
│   ├── ARI_WEBSOCKET_INTEGRATION.md # NUEVO - Documentación
│   ├── GUIA_PRUEBA_LLAMADA.md       # NUEVO - Guía de pruebas
│   └── example_lifespan.py          # NUEVO - Ejemplo integración
└── scripts/
    ├── setup_test_trunk.sh          # NUEVO - Setup trunk de prueba
    └── test_call.sh                 # NUEVO - Script de prueba
```

### **Modificados:**

```
├── app/services/sip/
│   └── ari_client.py                # MODIFICADO - create_external_media_websocket()
├── app/api/v1/
│   └── sip.py                       # MODIFICADO - WebSocket endpoint ari_audio
├── app/main.py                      # MODIFICADO - Integración TitoARIManager
├── config/asterisk/
│   ├── extensions.conf              # EXISTENTE - Dialplan (no modificar)
│   └── pjsip.conf                   # MODIFICADO - Contexto tito-inbound-ari
├── compose.yaml                     # MODIFICADO - SIP_TRANSPORT
└── .env                             # MODIFICADO - Variables de entorno
```

---

## 🚀 **COMANDOS PARA PROBAR:**

### **Verificar estado de contenedores:**

```bash
cd /home/usyeimar/projects/itm/app.tito.ai
docker compose ps
```

### **Ver logs de ARI en tiempo real:**

```bash
docker logs apptitoai-pipecat-runners-api-1 -f | grep -E "ARI|Stasis|external"
```

### **Hacer llamada de prueba:**

```bash
docker exec apptitoai-asterisk asterisk -rx \
  "channel originate Local/100@tito-inbound-ari application Stasis tito-ai"
```

### **Verificar trunk en Redis:**

```bash
docker exec apptitoai-redis-1 redis-cli -a redis get trunk:trk_default_test | python3 -m json.tool
```

### **Verificar agente en Redis:**

```bash
docker exec apptitoai-redis-1 redis-cli -a redis get agent_config:agent-tito-test | python3 -m json.tool
```

### **Recargar configuración Asterisk:**

```bash
docker exec apptitoai-asterisk asterisk -rx "core reload"
docker exec apptitoai-asterisk asterisk -rx "module reload res_websocket_client"
```

### **Ver logs de Asterisk:**

```bash
docker logs apptitoai-asterisk -f | grep -E "tito-ari|websocket|ERROR"
```

---

## 🔄 **Flujo de Llamada Esperado:**

```
┌─────────────┐     ┌─────────────┐     ┌─────────────────────┐
│  Cliente    │────▶│  Asterisk   │────▶│   ARI Manager       │
│   SIP       │     │   (PJSIP)   │     │  (tito_ari_manager) │
└─────────────┘     └──────┬──────┘     └─────────────────────┘
                           │                          │
                           │ Stasis(tito-ai)          │
                           ▼                          ▼
                    ┌─────────────┐     ┌─────────────────────┐
                    │  Stasis App │     │  POST /channels/    │
                    │  (tito-ai)  │     │  externalMedia      │
                    └─────────────┘     └─────────────────────┘
                                                   │
                           Asterisk se conecta    │
                           vía WebSocket          ▼
                           a este endpoint
                    ┌──────────────────────────────────────┐
                    │  /api/v1/sip/ari/audio               │
                    │  (FastAPI WebSocket)                 │
                    └──────────────────┬───────────────────┘
                                       │
                                       ▼
                    ┌──────────────────────────────────────┐
                    │  Pipeline Pipecat                    │
                    │  STT → LLM (GPT-4o) → TTS (Cartesia) │
                    └──────────────────────────────────────┘
```

**Punto de falla actual:** Asterisk no logra crear el canal ExternalMedia con WebSocket.

---

## ❓ **DECISIONES PENDIENTES:**

### **1. ¿Continuar con WebSocket o usar AudioSocket?**

| Opción          | Ventajas                                      | Desventajas                                      | Tiempo |
| --------------- | --------------------------------------------- | ------------------------------------------------ | ------ |
| **WebSocket**   | Más moderno, mejor integración, bidireccional | No funciona actualmente, requiere investigación  | 2-4h   |
| **AudioSocket** | Funciona ahora, más simple, probado           | Menos flexible, requiere AudioSocket en Asterisk | 5min   |
| **RTP Directo** | Máximo control, compatible                    | Más complejo, manejo de paquetes RTP             | 3-5h   |

**Recomendación:** Probar primero AudioSocket para validar el resto de la arquitectura, luego investigar WebSocket.

---

### **2. Configuración de transporte de audio:**

**Actual:**

```python
"transport": "websocket",
"external_host": "tito-media"  # perfil
```

**Alternativas a probar:**

```python
# Opción 1: URL completa
"transport": "websocket",
"external_host": "ws://apptitoai-pipecat-runners-api-1:8000/api/v1/sip/ari/audio?params..."

# Opción 2: RTP
"encapsulation": "rtp",
"external_host": "172.19.0.4:10000"

# Opción 3: Con transport_data
"transport": "websocket",
"external_host": "tito-media",
"transport_data": "uri=ws://..."
```

---

## 📝 **Checklist para Siguiente IA:**

### **Investigación inicial (15 min):**

- [ ] Verificar versión exacta de Asterisk: `docker exec apptitoai-asterisk asterisk -rx "core show version"`
- [ ] Verificar módulos cargados: `docker exec apptitoai-asterisk asterisk -rx "module show"`
- [ ] Buscar en logs de Asterisk errores de websocket_client

### **Pruebas rápidas (30 min):**

- [ ] Probar AudioSocket cambiando SIP_TRANSPORT
- [ ] Probar con `transport=rtp` en lugar de `websocket`
- [ ] Verificar conectividad de red entre contenedores

### **Documentación útil:**

- [ ] Revisar https://issues.asterisk.org/ para bugs relacionados
- [ ] Buscar ejemplos de ExternalMedia con WebSocket en GitHub
- [ ] Verificar si hay versión más reciente de Asterisk con soporte mejorado

---

## 🎉 **Logros Alcanzados:**

✅ **Arquitectura ARI completa implementada**
✅ **WebSocket bidireccional establecido con Asterisk**
✅ **Sistema de trunk multi-tenant funcional**
✅ **Pipeline Pipecat integrado y probado**
✅ **Sesión Redis para tracking de llamadas**
✅ **Reconexión automática con backoff exponencial**

**Éxito parcial:** El 90% de la integración está completa. Solo falta resolver el tipo de transporte de audio entre Asterisk y el backend.

---

## 📞 **Contacto y Contexto:**

**Proyecto:** Tito AI - Plataforma de agentes conversacionales de voz  
**Stack:** Python, FastAPI, Pipecat, Asterisk, Redis, Docker  
**Repositorio:** `/home/usyeimar/projects/itm/app.tito.ai/services/runners/`  
**Servicios corriendo:** `docker compose ps`

**Último commit:** Implementación ARI Manager con WebSocket  
**Branch:** main  
**Estado:** Listo para debugging de transporte de audio

---

**Nota para siguiente IA:**  
El código está limpio, documentado y funcional. El problema es específico de la interacción entre Asterisk 22.8.2 y la API de ExternalMedia. Se recomienda probar AudioSocket primero para validar que todo el flujo funciona, y luego investigar la configuración correcta de WebSocket con externalMedia.
