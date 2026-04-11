# Tito AI — Roadmap de Desarrollo

Plataforma SaaS de IA Conversacional. Cubre el **Core/Control (Laravel)** y el **Motor de Ejecución en Tiempo Real (Pipecat/Python)**.

> **Estado:** Este documento es un **roadmap/TODO**, no una especificación técnica implementada. Representa la visión de producto y las historias de usuario planned. Para la especificación técnica actual, ver [`agent-schema.md`](./agent-schema.md) y los manifiestos en `docs/resources/`.

---

## 🛠 Definition of Done

Para considerar una Historia de Usuario (HU) como **Finalizada**:

1. **Código:** PSR-12 (PHP) y PEP 8 (Python). Formateado con Pint/Ruff.
2. **Pruebas:** Cobertura unitaria/feature (Pest para Laravel, Pytest para Pipecat).
3. **Documentación:** Contratos de API actualizados y comentarios en métodos complejos.
4. **Seguridad:** Validación de Multi-tenancy y saneamiento de entradas.

---

## Fase 1: Agents API & Core (Laravel)

_Persistencia, configuración, facturación y panel de control._

### Épica 1: Arquitectura de Agentes y Multi-tenancy

- **[ ] HU-1.1: Aprovisionamiento de Identidad del Agente**
    - Registrar agentes con UUIDs únicos y metadatos regionales (idioma, zona horaria).
    - CA1: Validación de `locale` contra ISO 639-1.
    - CA2: Aislamiento estricto de datos (Scope Global de Tenant).

- **[ ] HU-1.2: Configuración del Cerebro (Brain) y Voz (Voice)**
    - Definir proveedores de LLM (OpenAI, Anthropic) y TTS (Cartesia, ElevenLabs) por agente.
    - CA: Validación de esquemas JSON para `brain_config` y soporte de `templating` en el System Prompt.

- **[ ] HU-1.3: Control de Ciclo de Vida (Runtime Config)**
    - Establecer límites de `max_duration` e `idle_timeout`.
    - CA: El sistema debe forzar el cierre de sesiones que excedan el límite para evitar fugas de costes.

### Épica 2: Integraciones y Webhooks (Tool Calling)

- **[ ] HU-2.1: Registro de Herramientas Dinámicas**
    - Registrar esquemas de herramientas (JSON Schema) que el LLM pueda invocar.
    - CA: Validación de firma de Webhooks y persistencia segura de Headers de autenticación.

### Épica 3: SaaS, Seguridad y Playground

- **[ ] HU-3.1: Gestión de API Keys del Tenant**
    - Generar llaves de API con scopes limitados para integrar Tito en sistemas externos.
    - CA: Implementación de rotación de llaves y middleware de validación.

- **[ ] HU-3.2: Simulador WebRTC (Playground)**
    - Probar al agente en una interfaz web antes de pasarlo a producción.
    - CA: Generación de tokens efímeros para la conexión WebRTC con Pipecat.

---

## Fase 2: Pipecat Engine (Runners & Workers)

_Procesamiento de audio, latencia y flujos de IA._

### Épica 4: Pipeline de Audio en Tiempo Real

- **[ ] HU-4.1: Orquestación de Pipeline (STT → LLM → TTS)**
    - Inicializar una pipeline de Pipecat validando el JWT enviado por Laravel.
    - CA: Soporte para cambio dinámico de modelos sin reiniciar el socket.
    - **Saludo Anti-Duplicate:** El pipeline no envía un mensaje de texto "Hola" forzado al inicio. En su lugar, dispara un turno de LLM natural para que el agente salude siguiendo su System Prompt de forma única, evitando duplicación de saludos.

### Épica 5: Naturalidad y Gestión de Interrupciones (Barge-In)

- **[ ] HU-5.1: Detección de Actividad de Voz (VAD) Inteligente**
    - El bot debe dejar de hablar inmediatamente si el usuario dice algo importante.
    - CA (BDD): Dado que el bot reproduce audio, cuando se detecta voz continua > 400ms, entonces se detiene el TTS en < 100ms y se trunca el contexto del LLM.
    - Parámetros actuales: `start_secs: 0.4s` (ignora ruidos breves), `stop_secs: 0.2s` (fin de turno casi instantáneo con Smart Turn V3).

- **[ ] HU-5.2: Gestión de Silencios y Abandono (Idle Logic)**
    - El bot debe detectar periodos de silencio prolongado para retomar la charla o colgar.
    - Fase 1 (Re-engagement): Al alcanzar `user_idle_timeout` (ej. 15s), el bot inyecta un mensaje proactivo ("¿Sigues ahí?"). Implementar debounce de 2s para evitar duplicación de frases.
    - Fase 2 (Cierre Forzoso): Si tras el mensaje de la Fase 1 pasan 10s adicionales sin audio, el bot ejecuta un "Cierre Clean" avisando al usuario y notificando a Laravel para liberar el worker.
    - CA3: Registro del motivo de fin de llamada como `TIMEOUT_SILENCE` en el dashboard.

- **[ ] HU-5.3: Análisis de Sentimiento en Tiempo Real**
    - Detectar la carga emocional del usuario durante la llamada.
    - Implementar procesador de análisis de sentimiento (VADER o modelo de HuggingFace) en el pipeline.
    - CA: Si el cliente está muy frustrado, activar automáticamente la herramienta `transferir_a_agente`.

- **[ ] HU-5.4: Gestión de Estados Visuales (RTVI Events)**
    - Notificar activamente al frontend sobre lo que está haciendo el bot.
    - Emitir eventos RTVI (`bot-thinking`, `bot-listening`, `tool-executing`) para que el widget web actualice su interfaz.
    - Interceptar mensajes custom del frontend (como `request-chat-history`) que no cumplen estrictamente el protocolo RTVI para evitar warnings en logs.

### Épica 6: Post-procesamiento Asíncrono (ARQ Workers)

- **[ ] HU-6.1: Resumen de Sesión y Extracción de Variables**
    - Al finalizar la llamada, un worker offline genera un resumen y extrae datos (ej. email, nombre).
    - CA: Envío de webhook de retorno a Laravel con el JSON estructurado de la sesión.

- **[ ] HU-6.2: Manejo de Errores Avanzado & Resiliencia**
    - Lógica de reintento y mensajes empáticos ante fallos de API.
    - Capturar excepciones en `AgentTools` y devolver un mensaje estructurado al LLM para que el agente explique el problema (ej: _"El sistema está lento, deme un momento adicional..."_).
    - CA: Evita silencios o respuestas genéricas cuando un servicio externo falla.

---

## Fase 3: Operaciones SaaS Pro y Calidad de Servicio

_Características premium y herramientas de observabilidad._

### Épica 7: Telefonía Avanzada y Cumplimiento

- **[ ] HU-7.1: Transferencia SIP (SIP REFER)**
    - El bot transfiere la llamada a un humano si detecta frustración.
    - CA: Comando de transferencia exitoso hacia el PBX externo y cierre de sesión en Pipecat.

- **[ ] HU-7.2: Grabación de Audio Dual (S3)**
    - Descargar el audio de la llamada con tracks separados (Bot/Humano).
    - CA: Almacenamiento cifrado en S3 y enlace temporal en el dashboard.

### Épica 8: Calidad de Servicio (QoS) y Métricas

- **[ ] HU-8.1: Dashboard de Fluidez y Latencia (Métricas Cliente)**
    - Ver KPIs de "Tiempo de Respuesta" y "Tasa de Interrupción".
    - CA: Gráficas de latencia media por agente y reporte de cumplimiento de SLA de voz (< 1s).

- **[ ] HU-8.2: Visualizador Waterfall de Latencia (Modo Debug)**
    - Ver el desglose en ms de cada fase: STT → LLM (TTFT) → TTS.
    - CA: Gráfica de telemetría detallada por cada turno de palabra en el historial de sesión.

- **[ ] HU-8.3: Monitor de Eficiencia Coste-Calidad**
    - Ver el coste estimado de la sesión basado en tokens y minutos de audio.
    - CA: Cálculo preciso basado en precios de proveedores configurados.

---

## Fase 4: Enterprise, Guardrails y Omnicanalidad

_Despliegues corporativos, banca y alta disponibilidad._

### Épica 9: Base de Conocimiento (RAG)

- **[ ] HU-9.1: Ingesta y Consulta de Base de Conocimiento**
    - Asociar un ID de Knowledge Base a un agente para buscar respuestas en documentos internos (FAQs, T&C).
    - CA: Implementación de un vector store y un retriever en Pipecat previo al LLM.

### Épica 10: Resiliencia y Alta Disponibilidad

- **[ ] HU-10.1: Mecanismo de Fallback de Modelos LLM**
    - Detectar caídas o timeouts en el proveedor principal y cambiar automáticamente al fallback (ej. Anthropic → OpenAI).
    - CA: Tolerancia a fallos sin desconectar la llamada del cliente, con logueo de "Fallback Triggereado".

### Épica 11: Seguridad y Cumplimiento Financiero (Guardrails)

- **[ ] HU-11.1: Implementación de Guardrails Financieros y PII**
    - Censurar en tiempo real datos sensibles (PII) antes de enviarlos al LLM y bloquear tópicos prohibidos.
    - Integrar Microsoft Presidio o un procesador basado en Regex antes de enviar datos al `WebhookService`.
    - CA: Cumplimiento con normativas de privacidad financiera (GDPR / Habeas Data).

- **[ ] HU-11.2: Trabajos de Retención y Borrado de Datos (GDPR)**
    - Cron job (Celery/Horizon) que elimine audios de S3 y transcripts pasada la ventana de retención (ej. 30 días).
    - CA: Eliminación física confirmada mediante logs de auditoría sin intervención humana.

### Épica 12: Omnicanalidad y Audio UX

- **[ ] HU-12.1: Soporte para Orquestación Omnicanal (WhatsApp/SIP Trunking)**
    - El mismo agente cognitivo recibe mensajes de texto/voz por WhatsApp y llamadas entrantes SIP (PSTN).
    - CA: Webhooks bidireccionales integrados en Laravel mapeando el contexto a la misma Worker Pipeline.

- **[ ] HU-12.2: Ambient Noise (Ruido de Fondo)**
    - Reproducir ruidos de ambiente en bucle durante toda la llamada para aumentar el realismo.
    - Configuración: `runtime_profiles.behavior.ambient_sound`.

- **[ ] HU-12.3: Thinking Sounds (Sonidos de Pensamiento)**
    - Eliminar silencios incómodos mientras el agente consulta herramientas o el LLM genera la respuesta.
    - Activación: `on_user_turn_stopped`. Desactivación: `on_bot_started_speaking`.
    - Configuración: `runtime_profiles.behavior.thinking_sound`.

---

## Fase 5: Estandarización de Plataforma y Arquitectura Core

_Unificación de los tipos de agentes (MVP, Pipeline, Flow) bajo un mismo estándar arquitectónico._

> El esquema base unificado ya fue consolidado en `docs/resources/agent-unified-manifest.json`.

### Épica 13: Gestión de Entornos y Despliegues

- **[ ] HU-13.1: Ciclo de Vida y Estados (Environments)**
    - Etiquetar manifiestos con su estado (`draft`, `staging`, `production`) y no permitir tráfico real a versiones no publicadas.
    - CA: El orquestador telefónico/web solo iniciará sesiones a agentes con estatus de producción.

### Épica 14: Manejo de Estado e Inyección de Contexto (Memory Strategies)

- **[ ] HU-14.1: Implementación de Estrategias de Memoria (Contextual Persistence)**
    - Permitir que el agente recuerde conversaciones anteriores con el mismo cliente. Impacto: el agente podrá decir _"Hola de nuevo, ¿seguimos con la consulta sobre el saldo de ayer?"_.
    - Integración con Redis/PostgreSQL para almacenar y recuperar el historial por `tenant_id` y `user_id`.
    - **Contexto de Sesión (Short-Term):** Redis (`hash: session:{uuid}:history`) como buffer rápido con truncamiento FIFO si se excede el límite de tokens del LLM.
    - **Memoria Persistente (Long-Term):** Al detonar el webhook `on_call_ended`, Laravel encola un Job de Horizon que extrae "Datos Clave y Acuerdos" y los almacena en PostgreSQL vinculado al `phone_number` o `user_id`.
    - **Inyección de Variables (Zero-shot Hydration):** Antes de empujar el prompt hacia Pipecat, el orquestador Laravel inspecciona `"injected_variables"`, realiza fetch dinámico a recursos externos y ejecuta un render de Blade/String sustituyendo tags como `{{account_balance}}` por sus valores en tiempo real.
    - CA: Retomar el estado de contexto de una llamada pasada dentro de la ventana del TTL configurada.

### Épica 16: Seguridad y Eventos (Events & Secrets)

- **[ ] HU-16.1: Protocolo Unificado de Webhooks (Lifecycle Hooks)**
    - Suscribirse a un bus de eventos estandarizado (`on_session_start`, `on_call_ended`) para todos los canales (Web, SIP, WhatsApp).
    - CA: Despacho de eventos centralizado en Laravel hacia APIs de clientes.

- **[ ] HU-16.2: Referenciación Segura de Secretos (Vault Key Management)**
    - Reemplazar los tokens planos en los JSON por referencias seguras (ej. `vault:tenant_123_twilio`) que Laravel resuelva en memoria.
    - CA: Mecanismo seguro de hidratación de credenciales antes de inyectar configuración a Pipecat.

---

## Fase 6: Experiencia B2B, Monetización y Personalización

_Capa comercial, integraciones de cara al cliente y herramientas orientadas a potenciar la adopción SaaS._

### Épica 17: Gestión y Clonación de Voces (Custom Voices)

- **[ ] HU-17.1: Motor de Clonación Multi-Proveedor (Voice Cloning Sync)**
    - Subir un clip de audio para clonar una voz y usarla como TTS en los bots.
    - **Strategy Pattern:** `VoiceCloningManager` en Laravel con interfaz base `VoiceProviderContract` para aislar la lógica de ElevenLabs, Cartesia, PlayHT, etc.
    - **Sync Asíncrono Concurrente:** `POST /api/voices/clone` dispara un Job que realiza el API Request en paralelo a todos los proveedores configurados.
    - **Vendor Mapping (tabla `tenant_custom_voices`):** Un registro lógico de voz con columna JSON `vendor_ids` que guarda el ID resultante por proveedor: `{"elevenlabs": "ID_Xi8J...", "cartesia": "ID_cart_99..."}`.
    - **Resolución en Runtime:** Al compilar el manifiesto final para Pipecat, el Core de Laravel busca en la BD el hash map e inyecta exclusivamente el ID del proveedor activo. Pipecat no sabe nada del mapeo.
    - CA: Un usuario debe poder subir un solo archivo de audio e intercambiar el motor de TTS sin perder la identidad de la voz ni volver a subir el archivo.

---

## Referencia: Agent Configuration Schema

Ver [`agent-schema.md`](./agent-schema.md) para la referencia completa del esquema `AgentConfig` (Pydantic v2).

### Resumen de módulos principales

| Módulo                | Descripción                                                                  |
| :-------------------- | :--------------------------------------------------------------------------- |
| `AgentMetadata`       | Nombre, slug, idioma, tags.                                                  |
| `Architecture`        | Tipo de ejecución: `pipeline` o `node`.                                      |
| `Brain`               | LLM (provider, model, instructions), RAG, Localización.                      |
| `BrainContext`        | Gestión de ventana de contexto: `summarize`, `truncate`, `none`.             |
| `RuntimeProfiles`     | STT, TTS, VAD, Behavior, Session Limits.                                     |
| `RuntimeBehavior`     | Interruptibilidad, turn detection, mute strategies, ambient/thinking sounds. |
| `AgentCapabilities`   | Definición de tools/function calling.                                        |
| `ComplianceConfig`    | PII redaction, privacidad.                                                   |
| `ObservabilityConfig` | Logging y monitoreo.                                                         |

### Mute Strategies disponibles

| Strategy                   | Descripción                                                                               |
| :------------------------- | :---------------------------------------------------------------------------------------- |
| `until_first_bot_complete` | Silencia al usuario hasta que el agente termina su primer respuesta (ideal para saludos). |
| `function_call`            | Silencia al usuario mientras el agente ejecuta una herramienta.                           |
| `first_speech`             | Silencia al usuario solo durante la primera locución del bot.                             |
| `always`                   | Silencia al usuario siempre que el bot esté hablando (turno estricto).                    |

### VAD Providers disponibles

| Provider | Descripción                                                                 |
| :------- | :-------------------------------------------------------------------------- |
| `silero` | (Default) ONNX-based, alta precisión. Recomendado para la mayoría de casos. |
| `aic`    | AI-Coustics, especializado en entornos con supresión de ruido.              |

---

## Referencia: Deployments API

### Endpoints

| Método   | Endpoint                                               | Descripción                                |
| :------- | :----------------------------------------------------- | :----------------------------------------- |
| `GET`    | `/api/tenant/agents/{id}/deployments`                  | Listar todos los deployments de un agente. |
| `GET`    | `/api/tenant/agents/{id}/deployments/{depId}`          | Obtener un deployment específico.          |
| `POST`   | `/api/tenant/agents/{id}/deployments`                  | Crear un nuevo deployment.                 |
| `PATCH`  | `/api/tenant/agents/{id}/deployments/{depId}`          | Actualizar un deployment existente.        |
| `DELETE` | `/api/tenant/agents/{id}/deployments/{depId}`          | Eliminar un deployment.                    |
| `GET`    | `/api/tenant/agents/{id}/deployments/active/{channel}` | Obtener el deployment activo por canal.    |
| `GET`    | `/api/public/widget-config/web/{slug}`                 | Config pública para Web Widget.            |
| `GET`    | `/api/public/widget-config/sip/{slug}`                 | Config pública para SIP.                   |

### Ejemplo: GET /api/tenant/agents/{agentId}/deployments

```json
[
    {
        "id": "01HXZ7K9MNPQRSTUVWXYZ12345",
        "channel": "web-widget",
        "enabled": true,
        "config": {
            "widget": {
                "position": "bottom-right",
                "theme": "light",
                "primaryColor": "#3182ce",
                "avatarUrl": "/avatars/julia.png",
                "welcomeMessage": "¡Hola! Soy Julia, tu asistente de Alloy Finance. ¿En qué puedo ayudarte?",
                "placeholderText": "Escribe tu mensaje aquí...",
                "showBranding": true,
                "minimizedByDefault": false
            },
            "livekit": {
                "roomPrefix": "widget-",
                "maxParticipants": 2,
                "enableTranscription": true,
                "audioBitrate": 64
            },
            "privacy": {
                "dataRetentionDays": 30,
                "allowRecording": false,
                "gdprCompliant": true
            }
        },
        "version": "1.0.0",
        "status": "active",
        "metadata": {
            "environment": "production",
            "domain": "app.tito.ai",
            "sslEnabled": true,
            "rateLimit": { "requestsPerMinute": 30, "burst": 5 },
            "analytics": {
                "trackEvents": true,
                "googleAnalyticsId": "G-XXXXXXXXXX"
            }
        },
        "agent": {
            "id": "01HXZ7K9MNPQRSTUVWXYZ12345",
            "name": "Julia (Alloy Finance)",
            "slug": "julia-alloy-finance"
        }
    },
    {
        "id": "01HXZ7K9MNPQRSTUVWXYZ67890",
        "channel": "sip",
        "enabled": true,
        "config": {
            "sip": {
                "server": "sip.provider.com",
                "port": 5060,
                "transport": "udp",
                "username": "julia_bot",
                "auth_realm": "provider.com",
                "codecs": ["PCMU", "PCMA", "opus"],
                "dtmf_mode": "rfc2833",
                "register_expires": 3600
            },
            "media": {
                "ice_servers": [
                    { "urls": "stun:stun.l.google.com:19302" },
                    {
                        "urls": "turn:turn.example.com:3478",
                        "username": "user",
                        "credential": "pass"
                    }
                ]
            },
            "call_flow": {
                "greeting": "Gracias por llamar a Alloy Finance. Soy Julia, su asistente virtual.",
                "menu_options": [
                    {
                        "key": "1",
                        "description": "Consultar saldo",
                        "action": "check_balance"
                    },
                    {
                        "key": "2",
                        "description": "Hacer un pago",
                        "action": "make_payment"
                    },
                    {
                        "key": "0",
                        "description": "Hablar con un agente",
                        "action": "transfer_to_human"
                    }
                ],
                "max_duration": 1800,
                "enable_recording": true,
                "voicemail_enabled": true
            }
        },
        "version": "2.1.0",
        "status": "active",
        "metadata": {
            "environment": "production",
            "provider": "twilio",
            "trunk_sid": "TRXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
            "phone_number": "+1234567890"
        },
        "agent": {
            "id": "01HXZ7K9MNPQRSTUVWXYZ12345",
            "name": "Julia (Alloy Finance)",
            "slug": "julia-alloy-finance"
        }
    }
]
```

### Ejemplo: POST /api/tenant/agents/{agentId}/deployments (Web Widget)

```json
{
    "channel": "web-widget",
    "enabled": true,
    "version": "1.1.0-dark-theme",
    "status": "active",
    "config": {
        "widget": {
            "position": "bottom-left",
            "theme": "dark",
            "primaryColor": "#10b981",
            "avatarUrl": "/avatars/julia-dark.png",
            "welcomeMessage": "¡Hola! Soy Julia en modo oscuro. ¿En qué puedo ayudarte?",
            "placeholderText": "Escribe tu mensaje...",
            "showBranding": false,
            "minimizedByDefault": true
        },
        "livekit": {
            "roomPrefix": "widget-dark-",
            "maxParticipants": 2,
            "enableTranscription": true,
            "audioBitrate": 64
        },
        "privacy": {
            "dataRetentionDays": 30,
            "allowRecording": false,
            "gdprCompliant": true
        }
    }
}
```

### Ejemplo: PATCH /api/tenant/agents/{agentId}/deployments/{deploymentId}

```json
{
    "version": "1.1.1",
    "config": {
        "widget": {
            "position": "bottom-right",
            "theme": "light",
            "primaryColor": "#3b82f6",
            "avatarUrl": "/avatars/julia-new.png",
            "welcomeMessage": "¡Hola! Soy Julia con nueva imagen y color. ¿En qué puedo ayudarte?",
            "placeholderText": "Escribe tu mensaje...",
            "showBranding": true,
            "minimizedByDefault": false
        }
    }
}
```

### Flujo de trabajo típico

1. **Desarrollo/Testing:** Crear deployment `"1.0.0-dev"` → probar → si funciona, crear `"1.0.0"` y marcar activo.
2. **Actualización:** Crear deployment con versión incrementada → probar → si hay problemas, `PATCH` con `"enabled": false` y revertir.
3. **Mantenimiento temporal:** `PATCH` con `"enabled": false` para pausar, `"enabled": true` para reactivar.

### Reglas de negocio

- Solo puede haber **un deployment activo por canal por agente**. Al activar uno nuevo, los otros del mismo canal se desactivan automáticamente.
- Para deshabilitar sin eliminar: `PATCH` con `"enabled": false`. `DELETE` elimina físicamente.
- Usar versiones semánticas (`major.minor.patch`) para tracking de cambios.
- El campo `metadata` es para información operacional (entorno, proveedor, rate limits) que no afecta la funcionalidad del agente.
- Los endpoints públicos (`/api/public/widget-config/*`) solo exponen lo necesario para el widget, sin credenciales ni datos sensibles.
