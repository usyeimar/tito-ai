# Backlog Técnico - Tito AI Runners

Backlog de funcionalidades pendientes que **no** están cubiertas por los planes SIP
([`sip-platform.md`](./sip-platform.md), [`sip-direct-hash.md`](./sip-direct-hash.md)).

> Todo lo relativo a SIP (trunks, direct hash, outbound dialing, transferencia SIP REFER)
> vive en los planes SIP y **no** debe duplicarse aquí.

---

## 0. SBC / Kamailio (Pendiente de diseño)

La arquitectura SIP actual menciona "SIP Bridge (Asterisk/Kamailio)" en `sip-platform.md` Fase 6, pero **falta diseño detallado**:

- [ ] **SBC vs Kamailio**: Definir si usamos un SBC managed (Twilio, Bandwidth) o desplegamos Kamailio propio
- [ ] **Orquestación desde Laravel**: Cómo se provisiona/configura el SBC desde el Admin UI
- [ ] **Inbound**: DNS/SIP trunk → Kamailio → routing → Python runner
- [ ] **Outbound**: Python runner → API → Kamailio → Carrier (Twilio/VoIP)
- [ ] **Transcoding**: Codecs (OPUS, G.711, G.729)
- [ ] **TLS/SRTP**: Seguridad de transporte
- [ ] **Monitoring**: CDR, QoS, alertas

> **Nota:** El runner Python NO gestiona esto. Laravel es el control plane que provisiona y configura el SBC.

---

## 1. Canales de despliegue de agentes

Hoy los agentes solo se despliegan vía `POST /api/v1/sessions`. Falta soportar más canales
sin cambiar la configuración del agente.

### 1.1 Web Widget (Embeddable)

Widget JavaScript que se incrusta con un snippet `<script>` y permite hablar con el agente
desde el navegador.

- [ ] `POST /api/v1/agents/{agent_id}/widget` — config del widget (tema, posición, idioma)
- [ ] `GET /api/v1/widget/{agent_id}/embed.js` — sirve el script embeddable
- [ ] El widget llama internamente a `POST /api/v1/sessions`
- [ ] Manejo de permisos de micrófono (MediaDevices API)
- [ ] Integración con Daily.co Prebuilt o LiveKit Components para el frontend WebRTC
- [ ] Configuración visual por agente: color, logo, posición, mensaje de bienvenida, idioma
- [ ] CORS allowlist por tenant, rate limiting por widget/tenant
- [ ] Schema Pydantic `WidgetConfig`
- [ ] Preview en `/widget/{agent_id}/preview`

Ejemplo:

```html
<script src="https://runners.tito.ai/api/v1/widget/agent-001/embed.js"></script>
```

### 1.2 Modelo de canales unificado

Una vez implementados Widget y los canales SIP (ver `sip-platform.md`):

- [ ] Modelo `AgentChannel` con tipo (`api`, `widget`, `sip`, `whatsapp-future`)
- [ ] `GET /api/v1/agents/{agent_id}/channels` — listar canales activos
- [ ] Cada canal con su config pero compartiendo el `AgentConfig` base
- [ ] Dashboard de canales por agente

### 1.3 Canales futuros

- [ ] **WhatsApp** vía Twilio/Meta API (audio messages como input)
- [ ] **Telegram** (voice messages + voice calls)
- [ ] **WebRTC directo** P2P sin Daily/LiveKit (reducir costos)
- [ ] **Phone number provisioning** por tenant (Twilio)

---

## 2. Seguridad y Autenticación

Los endpoints están abiertos sin protección.

- [ ] **Auth por API Key / JWT** en todos los endpoints
    - `Authorization: Bearer <token>` o `X-Tito-Key: sk_live_xxx`
    - Middleware FastAPI que valida contra el backend Laravel
    - Scopes por tenant
- [ ] **Rate limiting por tenant** (max sesiones concurrentes, max sesiones/hora, Redis sliding window)
- [ ] **CORS por tenant/widget** (allowlist de dominios)
- [ ] **Sanitización de inputs**
    - Prompt injection en `instructions`
    - SSRF en `callback_url`

---

## 3. Grabación y Transcripciones

**Estado actual:** `compliance.record_audio` está en el schema, `AudioBufferProcessor` se inserta
en el pipeline, y `_save_recording()` en `agent_pipeline_engine.py` guarda WAV local
(`resources/data/recordings/session_{id}_{ts}.wav`). Las transcripciones van por webhook
`session.ended` pero no se persisten.

- [ ] **Storage backend configurable** para producción
    - `RECORDING_STORAGE=s3|minio|local`, `S3_BUCKET`, `S3_REGION`, credenciales
    - `RecordingStorageService` con interfaz común
    - Path: `/{tenant_id}/{agent_id}/{session_id}.wav`
- [ ] `GET /api/v1/sessions/{session_id}/recording` — signed URL de S3 o stream local
- [ ] Limpieza de grabaciones locales tras subir a S3 (TTL configurable)
- [ ] **Persistencia de transcripciones**
    - DB (PostgreSQL) o Redis con TTL largo
    - `GET /api/v1/sessions/{session_id}/transcript`
    - Formato: mensajes con timestamps, rol (user/agent), texto
- [ ] **PII Redaction** si `compliance.pii_redaction: true`

---

## 4. Knowledge Base / RAG

El schema tiene `brain.knowledge_base.id` pero no está implementado.

- [ ] `POST /api/v1/knowledge-bases` para crear KB
- [ ] Subir documentos (PDF, TXT, CSV, URLs) y procesarlos en chunks
- [ ] Vector store (Pinecone, Qdrant, pgvector, ChromaDB)
- [ ] Pipeline: antes del LLM, buscar contexto relevante y adjuntarlo al prompt

---

## 5. Transferencia a Humano (Escalation)

> La transferencia vía SIP REFER se documenta en `sip-platform.md` (Queue / Peer).
> Aquí se cubre solo la parte del LLM y WebRTC.

- [ ] Tool `transfer_to_human` disponible para el LLM (decide cuándo escalar)
- [ ] Webhook `session.transfer_requested`
- [ ] Si es WebRTC: notificar al frontend vía WebSocket
- [ ] Cola de espera con música/mensaje

---

## 6. Observabilidad y Monitoreo

**Estado actual:**

- Prometheus parcial en `app/api/v1/metrics.py`: `tito_active_sessions`, `tito_session_duration_seconds`,
  `tito_session_errors_total`, `tito_dropped_frames_total` (declarada pero nunca se incrementa).
- `GET /api/v1/metrics` para scraping.
- Logging stdlib básico; Loguru en dependencias pero no se usa consistentemente.
- **Sin tracing**, sin OpenTelemetry, sin latencia por componente.

### 6.1 Métricas Prometheus

- [ ] Arreglar `tito_dropped_frames_total` (dead code actualmente)
- [ ] **Latencia por componente** (crítico para optimizar voz):
    ```python
    tito_stt_latency_seconds        # buckets 0.1-5.0
    tito_llm_ttfb_seconds           # Time To First Byte del token
    tito_llm_total_seconds          # tiempo total de respuesta
    tito_tts_latency_seconds
    tito_e2e_turn_latency_seconds   # user stops speaking → agent starts speaking
    ```
    Labels: `provider`, `model`, `agent_id`. Instrumentar con `time.monotonic()` alrededor de cada procesador.
- [ ] **Uso por tenant**:
    ```python
    tito_sessions_total{tenant_id, agent_id, provider}
    tito_session_minutes_total{tenant_id}
    ```
- [ ] **Transporte**:
    ```python
    tito_transport_setup_seconds{provider}
    tito_transport_errors_total{provider, error_type}
    ```

### 6.2 Distributed Tracing (OpenTelemetry)

- [ ] Dependencias: `opentelemetry-api`, `-sdk`, `-instrumentation-fastapi`, `-exporter-otlp`
- [ ] `TracerProvider` en `app/main.py` lifespan
- [ ] Exportar a Jaeger / Tempo / Datadog
- [ ] `OTEL_EXPORTER_OTLP_ENDPOINT`, `OTEL_SERVICE_NAME=tito-runner`
- [ ] Spans manuales por etapa:
    ```
    create_session
    ├── transport_setup
    ├── pipeline_run
    │   ├── stt_process
    │   ├── llm_generate
    │   ├── tts_synthesize
    │   └── tool_call
    └── session_cleanup
    ```
- [ ] Propagar `trace_id` en webhooks y eventos WebSocket

### 6.3 Health Checks Granulares

- [ ] `GET /health/live` — liveness (siempre 200 si responde)
- [ ] `GET /health/ready` — readiness (verifica Redis, capacidad, Daily/LiveKit API)
    ```json
    {
        "status": "ready",
        "checks": {
            "redis": { "status": "up", "latency_ms": 2 },
            "daily_api": { "status": "up", "latency_ms": 45 },
            "capacity": { "status": "available", "active": 3, "max": 10 }
        }
    }
    ```
    HTTP 503 si alguna dependencia falla.

### 6.4 Structured Logging

- [ ] Migrar a Loguru consistentemente (o `python-json-logger`)
- [ ] Formato JSON con campos consistentes: `session_id`, `agent_id`, `tenant_id`, `event`, `trace_id`
- [ ] `contextvars` para propagar `session_id` sin pasarlo manualmente
- [ ] Integración con ELK / Loki / CloudWatch

### 6.5 Alertas

- [ ] Reglas Prometheus / Grafana:
    - `tito_active_sessions / MAX_CONCURRENT_SESSIONS > 0.8` → runner casi lleno
    - `rate(tito_session_errors_total[5m]) / rate(tito_sessions_total[5m]) > 0.05` → error > 5%
    - `histogram_quantile(0.95, tito_llm_ttfb_seconds) > 3` → LLM lento
    - `histogram_quantile(0.95, tito_e2e_turn_latency_seconds) > 5` → latencia E2E alta
    - `up{job="tito-runner"} == 0` → runner caído
- [ ] Canales: Slack, PagerDuty, email
- [ ] Dashboard Grafana: sesiones activas, latencias p50/p95/p99, errores, uso por tenant

---

## 7. Base de Datos Persistente

Todo vive en Redis (volátil). Para producción:

- [ ] PostgreSQL para:
    - Sesiones históricas
    - Transcripciones completas
    - Metadata de grabaciones (link a S3)
    - Cache de configuraciones de agentes (fuente de verdad sigue en Laravel)
    - Métricas de uso por tenant
- [ ] Migraciones con Alembic
- [ ] Redis sigue como cache de sesiones activas + pub/sub

---

## 8. Auto-Scaling y Orquestación

- [ ] **HPA**: custom metric `tito_active_sessions / MAX_CONCURRENT_SESSIONS`
    - Scale up > 0.7, scale down < 0.3
- [ ] **Session affinity**: sticky sessions por `session_id`
- [ ] **Graceful draining**: `/health` devuelve `at_capacity: true`, esperar sesiones activas
- [ ] **Queue de sesiones**: si todos los runners están llenos, encolar en Redis/RabbitMQ

---

## 9. Testing

- [ ] Unit tests de `SessionManager`, `TaskManager`
- [ ] Integration tests: crear sesión → verificar room → cleanup
- [ ] Tests de schemas Pydantic (AgentConfig con distintos payloads)
- [ ] Tests de endpoints con `httpx.AsyncClient`
- [ ] Load testing con Locust/k6
- [ ] Tests de WebSocket

---

## 10. CI/CD y DevOps

- [ ] Pipeline GitHub Actions / GitLab CI: lint (ruff), type check (mypy), tests, build, push
- [ ] Helm chart para Kubernetes
- [ ] Terraform/Pulumi para infra (Redis, PostgreSQL, DNS, certs)
- [ ] Secrets management (Vault / AWS Secrets Manager)

---

## 11. Billing y Uso por Tenant

- [ ] **Tracking de minutos**
    - Tabla `usage_records`: `tenant_id`, `agent_id`, `session_id`, `duration_seconds`, `created_at`
    - `GET /api/v1/tenants/{tenant_id}/usage?month=2026-04`
- [ ] **Límites por plan**
    - Max minutos/mes, max sesiones concurrentes, max agentes
    - Rechazar con 402/429 si se excede
    - Webhook `usage.limit_reached` al 80% y 100%
- [ ] **Reportes**: desglose por agente, exportar CSV/JSON

---

## 12. Reconexión y Resiliencia

- [ ] **Reconexión del usuario** (< 60s)
    - `POST /api/v1/sessions/{session_id}/rejoin` → nuevo token misma sala
- [ ] **Reconexión del runner**: otro runner retoma sesión desde Redis metadata
- [ ] **Webhook retry**: actualmente fire-and-forget con timeout 5s
    - Retry con backoff exponencial (1s, 5s, 30s)
    - Dead letter queue tras 3 fallos

---

## 13. Conversation Flows (pipecat-ai-flows)

`pipecat-ai-flows` está en `pyproject.toml` pero no se usa.

- [ ] Soporte para `architecture.type: "node-graph"` (existe en schema, solo se usa `pipeline`)
- [ ] Flujos con nodos: saludo → recopilar datos → confirmar → despedida
- [ ] Transiciones condicionales
- [ ] Editor visual en frontend admin
- [ ] Nodos especiales: `transfer_to_human`, `collect_input`, `confirm_action`, `end_call`

---

## 14. Contexto Externo e Inyección de Datos

- [ ] **Inyectar contexto CRM/backend en el prompt**
    - `orchestration.session_context` existe en schema pero no se usa
    - GET al backend antes de arrancar el LLM
- [ ] **Memoria entre sesiones**
    - Resumen de cada sesión adjuntado en la siguiente
    - Identificar usuario por teléfono/email/ID

---

## 15. Deuda técnica pendiente

- [ ] Import roto: `pipecat.processors.audio.audio_player_processor` en `pipeline_builder.py`
- [ ] `GET /api/v1/sessions` devuelve lista vacía (placeholder) — implementar listado real desde Redis
- [ ] Rotar API keys expuestas en `.env` (LiveKit, Daily, OpenAI, Cartesia, Deepgram, Google)
- [ ] Tests unitarios para endpoints nuevos
- [ ] Evaluar si el runner debería cachear `AgentConfig` y recibir solo `agent_id`
