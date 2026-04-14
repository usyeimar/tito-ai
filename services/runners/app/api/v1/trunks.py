"""Read-only SIP Trunk API endpoints.

Trunk configuration is managed by Laravel via TrunkRedisSyncService.
This API provides read access to trunk data and call management (ephemeral records).
"""

import logging

from fastapi import APIRouter, HTTPException, Query, Path, Request, status

from app.schemas.sessions import ActionResponse
from app.schemas.trunks import (
    OutboundCallRequest,
    TrunkLink,
    TrunkResponse,
    TrunkListResponse,
    OutboundCallResponse,
    OutboundCallListResponse,
)
from app.services.trunk_service import trunk_service

logger = logging.getLogger(__name__)

router = APIRouter()


# ── Helpers HATEOAS ───────────────────────────────────────────────────────────


def get_trunk_links(request: Request, trunk_id: str, mode: str) -> dict:
    base_url = str(request.base_url).rstrip("/")
    trunk_path = f"{base_url}/api/v1/trunks/{trunk_id}"

    links = {
        "self": TrunkLink(href=trunk_path, method="GET"),
    }

    if mode == "outbound":
        links["calls"] = TrunkLink(href=f"{trunk_path}/calls", method="POST")
        links["list_calls"] = TrunkLink(href=f"{trunk_path}/calls", method="GET")

    return links


def get_call_links(request: Request, trunk_id: str, call_id: str) -> dict:
    base_url = str(request.base_url).rstrip("/")
    call_path = f"{base_url}/api/v1/trunks/{trunk_id}/calls/{call_id}"

    return {
        "self": TrunkLink(href=call_path, method="GET"),
        "cancel": TrunkLink(href=call_path, method="DELETE"),
        "trunk": TrunkLink(href=f"{base_url}/api/v1/trunks/{trunk_id}", method="GET"),
    }


# ── Read Trunks ───────────────────────────────────────────────────────────────


@router.get(
    "/",
    response_model=TrunkListResponse,
    summary="Listar SIP Trunks",
    response_description="Lista de trunks del workspace.",
)
async def list_trunks(
    request: Request,
    workspace_slug: str = Query(
        ..., description="Slug del workspace.", examples=["alloy-finance"]
    ),
):
    """
    Lista todos los SIP Trunks de un workspace.

    Incluye trunks de todos los modos (inbound, register, outbound).
    Los passwords están enmascarados.
    """
    trunks = await trunk_service.list_trunks(workspace_slug)
    for t in trunks:
        t["_links"] = get_trunk_links(request, t["trunk_id"], t["mode"])

    base_url = str(request.base_url).rstrip("/")
    return TrunkListResponse(
        trunks=trunks,
        count=len(trunks),
        _links={
            "self": TrunkLink(
                href=f"{base_url}/api/v1/trunks?workspace_slug={workspace_slug}",
                method="GET",
            ),
        },
    )


@router.get(
    "/{trunk_id}",
    response_model=TrunkResponse,
    summary="Obtener SIP Trunk",
    response_description="Detalle del trunk (passwords enmascarados).",
    responses={404: {"description": "Trunk no encontrado."}},
)
async def get_trunk(
    request: Request,
    trunk_id: str = Path(
        ..., description="ID del trunk.", examples=["trk_a1b2c3d4e5f6"]
    ),
):
    """Obtiene los datos de un SIP Trunk por ID. Los passwords se enmascaran."""
    trunk = await trunk_service.get_trunk(trunk_id)
    if not trunk:
        raise HTTPException(status_code=404, detail="Trunk no encontrado.")

    active = await trunk_service._get_active_calls(trunk_id)
    trunk["active_calls"] = active
    trunk["_links"] = get_trunk_links(request, trunk_id, trunk["mode"])
    return trunk


# ── Call Management (ephemeral - not trunk config writes) ─────────────────────


@router.post(
    "/{trunk_id}/calls",
    status_code=status.HTTP_201_CREATED,
    response_model=OutboundCallResponse,
    summary="Originar Llamada Saliente",
    responses={
        400: {"description": "El trunk no es mode=outbound o no está activo."},
        404: {"description": "Trunk no encontrado."},
        429: {"description": "Límite de llamadas concurrentes alcanzado."},
    },
)
async def originate_call(
    call_request: OutboundCallRequest,
    request: Request,
    trunk_id: str = Path(...),
):
    """
    Inicia una llamada saliente via un trunk outbound.

    El agente IA llamará al número indicado. El campo `metadata` se inyecta
    al contexto del agente para que sepa con quién habla y por qué.

    **Estados de la llamada:**
    - `queued` → En cola para originar
    - `ringing` → El teléfono está sonando
    - `answered` → El usuario contestó, pipeline activo
    - `completed` → Llamada terminada normalmente
    - `failed` → Error de conexión
    - `no_answer` → Timeout, nadie contestó
    - `busy` → Línea ocupada
    - `cancelled` → Cancelada via API
    """
    try:
        call = await trunk_service.originate_call(trunk_id, call_request)
    except ValueError as e:
        msg = str(e)
        if "Límite" in msg:
            raise HTTPException(
                status_code=429, detail=msg, headers={"Retry-After": "10"}
            )
        if "no está activo" in msg or "solo es válido" in msg:
            raise HTTPException(status_code=400, detail=msg)
        raise HTTPException(status_code=400, detail=msg)

    if not call:
        raise HTTPException(status_code=404, detail="Trunk no encontrado.")

    call["_links"] = get_call_links(request, trunk_id, call["call_id"])
    return call


@router.get(
    "/{trunk_id}/calls",
    response_model=OutboundCallListResponse,
    summary="Listar Llamadas Activas del Trunk",
    responses={404: {"description": "Trunk no encontrado."}},
)
async def list_calls(
    request: Request,
    trunk_id: str = Path(...),
):
    """Lista las llamadas activas (no terminadas) de un trunk outbound."""
    trunk = await trunk_service.get_trunk(trunk_id)
    if not trunk:
        raise HTTPException(status_code=404, detail="Trunk no encontrado.")

    calls = await trunk_service.list_calls(trunk_id)
    for c in calls:
        c["_links"] = get_call_links(request, trunk_id, c["call_id"])

    base_url = str(request.base_url).rstrip("/")
    return OutboundCallListResponse(
        calls=calls,
        count=len(calls),
        trunk_id=trunk_id,
        _links={
            "self": TrunkLink(
                href=f"{base_url}/api/v1/trunks/{trunk_id}/calls", method="GET"
            ),
            "create": TrunkLink(
                href=f"{base_url}/api/v1/trunks/{trunk_id}/calls", method="POST"
            ),
            "trunk": TrunkLink(
                href=f"{base_url}/api/v1/trunks/{trunk_id}", method="GET"
            ),
        },
    )


@router.get(
    "/{trunk_id}/calls/{call_id}",
    response_model=OutboundCallResponse,
    summary="Obtener Estado de Llamada",
    responses={404: {"description": "Llamada no encontrada."}},
)
async def get_call(
    request: Request,
    trunk_id: str = Path(...),
    call_id: str = Path(...),
):
    """Obtiene el estado actual de una llamada saliente."""
    call = await trunk_service.get_call(call_id)
    if not call or call.get("trunk_id") != trunk_id:
        raise HTTPException(status_code=404, detail="Llamada no encontrada.")

    call["_links"] = get_call_links(request, trunk_id, call_id)
    return call


@router.delete(
    "/{trunk_id}/calls/{call_id}",
    response_model=OutboundCallResponse,
    summary="Cancelar Llamada Saliente",
    responses={
        400: {"description": "La llamada no se puede cancelar en su estado actual."},
        404: {"description": "Llamada no encontrada."},
    },
)
async def cancel_call(
    request: Request,
    trunk_id: str = Path(...),
    call_id: str = Path(...),
):
    """
    Cancela una llamada saliente.

    Solo se pueden cancelar llamadas con status `queued` o `ringing`.
    """
    try:
        call = await trunk_service.cancel_call(call_id)
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

    if not call:
        raise HTTPException(status_code=404, detail="Llamada no encontrada.")

    call["_links"] = get_call_links(request, trunk_id, call_id)
    return call
