# Plan de Desarrollo - Índice Maestro

Este documento es el índice maestro del plan de desarrollo de TITO.AI Platform.

## Estructura

```
plans/
├── v1-sections/                    # Plan original dividido por secciones
│   ├── 00-overview/              # Visión y arquitectura de alto nivel
│   ├── 01-laravel-multi-tenant/  # Laravel con Stancl Tenancy
│   ├── 02-sip-architecture/    # Arquitectura SIP/PSTN
│   ├── 03-webrtc/              # WebRTC con LiveKit
│   ├── 04-multi-tenant-model/   # Modelo multi-tenant en Redis
│   ├── 05-high-availability/  # Alta disponibilidad
│   └── 06-api-unified/         # API unificada
├── sip-platform.md              # Plataforma SIP multi-tenant (redes + trunks)
├── sip-direct-hash.md           # URIs direct.<hash> (short-circuit de resolución)
└── README.md                    # Este índice
```

---

## Secciones

### 00. Visión General

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [00-vision](./v1-sections/00-overview/00-vision.md) | Visión general del proyecto | 4 |
| [01-architecture](./v1-sections/00-overview/01-architecture.md) | Arquitectura de alto nivel | 42 |

### 01. Laravel Multi-Tenant

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [00-stancl-tenancy](./v1-sections/01-laravel-multi-tenant/00-stancl-tenancy.md) | Implementación con Stancl Tenancy | ⚠️ 979 |

**Sub-secciones (dentro del archivo grande):**
- 0.1 ¿Qué es Stancl Tenancy?
- 0.2 Arquitectura Multi-Tenant
- 0.3 Instalación y Configuración
- 0.4 Estructura de Base de Datos
- 0.5 Migraciones
- 0.6 Rutas y Controllers
- 0.7 Integración con Microservicios
- 0.8 Comandos de Gestión
- 0.9 Middleware y Rate Limiting
- 0.10 Consideraciones de Seguridad
- 0.11 Modelos Eloquent

### 02. Arquitectura SIP

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [sip-components](./v1-sections/02-sip-architecture/sip-components.md) | SBC, transport, router, auth, RTP | ⚠️ 487 |

**Sub-secciones:**
- 1.1 Componentes del SBC
- 1.2 Modelo de Datos SIP
- 1.3 Flujo de Llamada SIP

### 03. WebRTC

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [livekit-webrtc](./v1-sections/03-webrtc/livekit-webrtc.md) | LiveKit y WebRTC | ⚠️ 268 |

### 04. Modelo Multi-Tenant

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [tenant-model](./v1-sections/04-multi-tenant-model/tenant-model.md) | Modelo en Redis | 168 |

### 05. Alta Disponibilidad

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [high-availability](./v1-sections/05-high-availability/high-availability.md) | Cluster y réplicas | ⚠️ 452 |

### 06. API Unificada

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [api-unified](./v1-sections/06-api-unified/api-unified.md) | Endpoints unificados | 124 |

### 07. SIP Platform

| Documento | Descripción | Líneas |
|-----------|-------------|--------|
| [sip-platform](./sip-platform.md) | Plataforma SIP multi-tenant: redes, peers, agentes, colas y trunks (inbound / register / outbound) | 1165 |
| [sip-direct-hash](./sip-direct-hash.md) | URIs `sip:direct.<hash>@sip.tito.ai` — short-circuit de resolución para QA/demos | 334 |

`sip-platform.md` unifica y reemplaza a los antiguos `sip-network-multi-tenant.md` y `sip-trunks-api.md`.
`sip-direct-hash.md` es un plan complementario que se integra vía short-circuit en `TrunkService.resolve_inbound_call`.

### 08. Backlog Técnico (no-SIP)

| Documento | Descripción |
|-----------|-------------|
| [backlog](./backlog.md) | Funcionalidades pendientes fuera del dominio SIP: widget web, seguridad, grabaciones, KB/RAG, observabilidad, DB, auto-scaling, billing, flows, deuda técnica |

---

## Archivos Muy Grandes (>500 líneas)

Los siguientes archivos necesitan subdividirse:

| Archivo | Líneas | Estado |
|---------|-------|--------|
| `01-laravel-multi-tenant/00-stancl-tenancy.md` | 979 | ⚠️ needs-split |
| `02-sip-architecture/sip-components.md` | 487 | ⚠️ needs-split |
| `05-high-availability/high-availability.md` | 452 | ⚠️ needs-split |
| `03-webrtc/livekit-webrtc.md` | 268 | ✅ OK |
| `04-multi-tenant-model/tenant-model.md` | 168 | ✅ OK |
| `06-api-unified/api-unified.md` | 124 | ✅ OK |

---

## Roadmap de Implementación (Resumen)

| Fase | Componente | Semanas |
|------|------------|---------|
| 1 | Fundamentos (Schema, Redis, API) | 1-4 |
| 2 | SIP/PSTN (SBC, Kamailio) | 5-10 |
| 3 | WebRTC (LiveKit, Runner) | 11-14 |
| 4 | Alta Disponibilidad | 15-18 |
| 5 | Observabilidad | 19-20 |
| 6 | Producción | 21-24 |

---

## Notas

- Los archivos con más de 500 líneas están marcados con ⚠️
- Se recomienda subdividirlos por sub-secciones para facilitar la revisión
- Mantener el plan original enarchive para referencia