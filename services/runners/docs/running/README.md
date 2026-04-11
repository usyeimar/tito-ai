# Running - Runbooks Operacionales

Esta carpeta contiene **runbooks y procedimientos operacionales** para el servicio
`tito-runners`: cómo levantarlo, cómo diagnosticarlo, cómo responder a incidentes.

> **Qué NO va aquí:**
> - Backlog de features → [`../plans/backlog.md`](../plans/backlog.md)
> - Diseño / arquitectura → [`../plans/`](../plans/)
> - Esquemas y diseño de agentes → [`../agents/`](../agents/)

---

## Runbooks previstos

| Documento | Descripción | Estado |
|-----------|-------------|--------|
| `deployment.md` | Cómo desplegar el runner (local, staging, prod) | 📝 pendiente |
| `health-checks.md` | Endpoints de health, semántica de `/health/live` y `/health/ready` | 📝 pendiente |
| `incident-response.md` | Playbook ante caídas, latencia alta, saturación | 📝 pendiente |
| `environment.md` | Variables de entorno requeridas y opcionales | 📝 pendiente |
| `local-dev.md` | Setup local (Redis, credenciales, `uv run`) | 📝 pendiente |

Por ahora el servicio se levanta con el `compose.yaml` del repo raíz; cuando existan
runbooks concretos se agregarán en archivos individuales y se enlazarán desde este índice.
