# Tito AI Runners — Documentación

Índice de la documentación del servicio **runners** (FastAPI + Pipecat).

## Índice

### Agentes

| Documento | Descripción |
|-----------|-------------|
| [Agent Schema](./agents/agent-schema.md) | Referencia completa del esquema `AgentConfig` (Pydantic v2) |
| [Agent Design](./agents/agent-design.md) | Roadmap y diseño funcional de agentes |

### Planes de Desarrollo

| Documento | Descripción |
|-----------|-------------|
| [Índice de Planes](./plans/README.md) | Índice maestro del plan de desarrollo |
| [SIP Platform](./plans/sip-platform.md) | Plataforma SIP multi-tenant: redes, peers, agentes, colas y trunks (inbound / register / outbound) |
| [SIP Direct Hash](./plans/sip-direct-hash.md) | URIs `sip:direct.<hash>@sip.tito.ai` — short-circuit de resolución para QA/demos |
| [Backlog técnico](./plans/backlog.md) | Funcionalidades pendientes no-SIP (widget, observabilidad, billing, etc.) |
| [v1-sections](./plans/v1-sections/) | Plan v1 original dividido por secciones |

### Operaciones

| Documento | Descripción |
|-----------|-------------|
| [Running / Runbooks](./running/README.md) | Runbooks operacionales (deployment, health, incident response) |

### Recursos

| Archivo | Descripción |
|---------|-------------|
| [`agent-unified-manifest.json`](./resources/agent-unified-manifest.json) | Manifiesto unificado de agente (referencia) |
| [`agent-mvp-manifest.json`](./resources/agent-mvp-manifest.json) | Manifiesto mínimo (MVP) |
| [`agent-pipeline-manifest.json`](./resources/agent-pipeline-manifest.json) | Ejemplo de arquitectura *pipeline* |
| [`agent-flow-manifest.json`](./resources/agent-flow-manifest.json) | Ejemplo de arquitectura *flow* |

---

## Estructura

```
docs/
├── README.md                     # Este índice
├── agents/                       # Esquema y diseño de agentes
│   ├── agent-schema.md
│   └── agent-design.md
├── plans/                        # Planes de desarrollo
│   ├── README.md
│   ├── sip-platform.md
│   ├── sip-direct-hash.md
│   ├── backlog.md
│   └── v1-sections/
│       ├── 00-overview/
│       ├── 01-laravel-multi-tenant/
│       ├── 02-sip-architecture/
│       ├── 03-webrtc/
│       ├── 04-multi-tenant-model/
│       ├── 05-high-availability/
│       └── 06-api-unified/
├── running/                      # Runbooks operacionales
│   └── README.md
└── resources/                    # Manifiestos JSON de ejemplo
    ├── agent-unified-manifest.json
    ├── agent-mvp-manifest.json
    ├── agent-pipeline-manifest.json
    └── agent-flow-manifest.json
```

---

## Convenciones

- Archivos en **kebab-case** (`agent-schema.md`).
- Planes versionados como `runner-refactor-vN.md`.

---

## Documentación del monorepo (Tito AI Core)

| Documento | Ubicación |
|-----------|-----------|
| SIP Trunks | [`../../../docs/architecture/telephony-sip-trunks.md`](../../../docs/architecture/telephony-sip-trunks.md) |
| Autenticación | [`../../../docs/architecture/authentication.md`](../../../docs/architecture/authentication.md) |
| Arquitectura de Agentes | [`../../../docs/agents/architecture.md`](../../../docs/agents/architecture.md) |
| Roadmap de Agentes | [`../../../docs/agents/roadmap.md`](../../../docs/agents/roadmap.md) |

---

## Contacto

- **Repo:** `app.tito.ai` (`services/runners`)
- **Stack:** FastAPI, Redis, Pipecat, WebRTC (Daily / LiveKit)
- **Slack:** `#tito-ai-runners`
