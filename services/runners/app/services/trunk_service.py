"""Read-only service for SIP Trunk resolution (calls are ephemeral, trunks are managed by Laravel).

This service provides read access to trunk configurations from Redis,
with fallback to the Laravel backend API via TrunkResolutionService.
Call management (originate, update status) is still supported since calls are ephemeral.
"""

import json
import logging
import time
import uuid
from copy import deepcopy
from typing import Optional, Dict, Any, List

import redis.asyncio as aioredis
from app.core.config import settings
from app.schemas.trunks import (
    UpdateTrunkRequest,
    OutboundCallRequest,
)

logger = logging.getLogger(__name__)


class TrunkService:
    """Read-only trunk service - writes are handled by Laravel via TrunkRedisSyncService."""

    DOMAIN_SUFFIX = "sip.tito.ai"
    CALL_TTL = 3600  # 1 hora

    def __init__(self):
        self._redis = aioredis.from_url(settings.REDIS_URL, decode_responses=True)
        self._ami = None  # Set via set_ami_controller()

    def set_ami_controller(self, ami):
        """Inject the AMI controller for outbound call origination."""
        self._ami = ami

    # ── Read Operations ────────────────────────────────────────────────────────

    async def get_trunk(self, trunk_id: str) -> Optional[dict]:
        """Get trunk by ID with masked passwords."""
        data = await self._get_trunk_raw(trunk_id)
        if not data:
            return None
        return self._mask_passwords(data)

    async def list_trunks(self, workspace_slug: str) -> List[dict]:
        """List all trunks for a workspace."""
        trunk_ids = await self._redis.smembers(f"trunk:index:{workspace_slug}")
        trunks = []
        for tid in trunk_ids:
            trunk = await self.get_trunk(tid)
            if trunk:
                active = await self._get_active_calls(tid)
                trunk["active_calls"] = active
                trunks.append(trunk)
        return trunks

    # ── Resolution (used by SIP Bridge) ──────────────────────────────────────

    async def resolve_inbound_call(
        self, workspace_slug: str, extension: str
    ) -> Optional[dict]:
        """Resolve inbound call to trunk and agent by extension pattern."""
        trunk_ids = await self._redis.smembers(f"trunk:index:{workspace_slug}")

        for tid in trunk_ids:
            data = await self._get_trunk_raw(tid)
            if not data or data["mode"] != "inbound" or data["status"] != "active":
                continue

            for route in data.get("routes", []):
                pattern = route.get("pattern", "")
                if pattern == extension and route.get("enabled", True):
                    return {
                        "trunk_id": tid,
                        "agent_id": route.get("agent_id"),
                        "trunk_data": data,
                    }
        return None

    async def resolve_register_call(self, trunk_id: str) -> Optional[dict]:
        """Resolve register mode trunk."""
        data = await self._get_trunk_raw(trunk_id)
        if not data or data["mode"] != "register" or data["status"] != "active":
            return None

        return {
            "trunk_id": trunk_id,
            "agent_id": data.get("agent_id"),
            "trunk_data": data,
        }

    # ── Call Management (ephemeral - these are NOT trunk config writes) ─────────

    async def originate_call(
        self, trunk_id: str, request: OutboundCallRequest
    ) -> Optional[dict]:
        """Originate an outbound call (call records are ephemeral)."""
        data = await self._get_trunk_raw(trunk_id)
        if not data:
            return None
        if data["mode"] != "outbound":
            raise ValueError("originate_call solo es válido para trunks mode=outbound")
        if data["status"] != "active":
            raise ValueError("El trunk no está activo")

        # Validar concurrencia
        active = await self.increment_active_calls(trunk_id)
        if active > data["max_concurrent_calls"]:
            await self.decrement_active_calls(trunk_id)
            raise ValueError(
                f"Límite de llamadas concurrentes alcanzado ({data['max_concurrent_calls']})"
            )

        call_id = f"call_{uuid.uuid4().hex[:12]}"
        outbound_cfg = data.get("outbound", {})
        caller_id = request.caller_id or outbound_cfg.get("caller_id")

        call_data = {
            "call_id": call_id,
            "trunk_id": trunk_id,
            "agent_id": request.agent_id,
            "to": request.to,
            "caller_id": caller_id,
            "call_status": "queued",
            "session_id": None,
            "timeout_seconds": request.timeout_seconds,
            "callback_url": request.callback_url,
            "metadata": request.metadata,
            "created_at": time.time(),
        }

        await self._redis.setex(f"call:{call_id}", self.CALL_TTL, json.dumps(call_data))
        await self._redis.sadd(f"call:index:{trunk_id}", call_id)

        # Execute Originate via AMI if available
        if self._ami and self._ami.connected:
            trunk_name = outbound_cfg.get("trunk_name", data.get("name", "default"))
            dial_string = f"PJSIP/{request.to}@{trunk_name}"

            try:
                await self._ami.originate(
                    channel=dial_string,
                    context="tito-outbound",
                    exten="s",
                    priority="1",
                    caller_id=caller_id,
                    variables={
                        "CALL_ID": call_id,
                        "AGENT_ID": request.agent_id,
                        "TRUNK_ID": trunk_id,
                    },
                    timeout=request.timeout_seconds * 1000,
                )
                call_data["call_status"] = "ringing"
                await self._redis.setex(
                    f"call:{call_id}", self.CALL_TTL, json.dumps(call_data)
                )
            except Exception as e:
                logger.error(f"AMI originate failed: {e}")
                call_data["call_status"] = "failed"
                await self._redis.setex(
                    f"call:{call_id}", self.CALL_TTL, json.dumps(call_data)
                )
                await self.decrement_active_calls(trunk_id)
                raise ValueError(f"No se pudo originar la llamada: {e}")
        else:
            logger.warning(
                f"AMI not available — call {call_id} queued but not originated. "
                "Set SIP_ENABLED=true and configure AMI credentials."
            )

        logger.info(
            f"Call originated | call_id={call_id} trunk_id={trunk_id} to={request.to} agent={request.agent_id}"
        )
        return call_data

    async def get_call(self, call_id: str) -> Optional[dict]:
        """Get call by ID."""
        raw = await self._redis.get(f"call:{call_id}")
        return json.loads(raw) if raw else None

    async def list_calls(self, trunk_id: str) -> List[dict]:
        """List all calls for a trunk."""
        call_ids = await self._redis.smembers(f"call:index:{trunk_id}")
        calls = []
        for cid in call_ids:
            call = await self.get_call(cid)
            if call:
                calls.append(call)
            else:
                # Call TTL expired, cleanup from index
                await self._redis.srem(f"call:index:{trunk_id}", cid)
        return calls

    async def cancel_call(self, call_id: str) -> Optional[dict]:
        """Cancel a queued or ringing call."""
        call = await self.get_call(call_id)
        if not call:
            return None

        if call["call_status"] not in ("queued", "ringing"):
            raise ValueError(
                f"No se puede cancelar una llamada con status={call['call_status']}"
            )

        call["call_status"] = "cancelled"
        await self._redis.setex(f"call:{call_id}", self.CALL_TTL, json.dumps(call))
        await self._redis.srem(f"call:index:{call['trunk_id']}", call_id)
        await self.decrement_active_calls(call["trunk_id"])

        logger.info(f"Call cancelled | call_id={call_id}")
        return call

    async def update_call_status(
        self, call_id: str, new_status: str, session_id: Optional[str] = None
    ) -> Optional[dict]:
        """Update call status (including terminal states)."""
        call = await self.get_call(call_id)
        if not call:
            return None

        call["call_status"] = new_status
        if session_id:
            call["session_id"] = session_id

        await self._redis.setex(f"call:{call_id}", self.CALL_TTL, json.dumps(call))

        # If terminal state, cleanup counters
        terminal_statuses = ("completed", "failed", "no_answer", "busy", "cancelled")
        if new_status in terminal_statuses:
            await self._redis.srem(f"call:index:{call['trunk_id']}", call_id)
            await self.decrement_active_calls(call["trunk_id"])

        logger.info(f"Call status updated | call_id={call_id} status={new_status}")
        return call

    # ── Helpers ───────────────────────────────────────────────────────────────

    async def _get_trunk_raw(self, trunk_id: str) -> Optional[dict]:
        """Get raw trunk data from Redis."""
        raw = await self._redis.get(f"trunk:{trunk_id}")
        return json.loads(raw) if raw else None

    def _mask_passwords(self, data: dict) -> dict:
        """Mask passwords in trunk data."""
        masked = deepcopy(data)
        if masked.get("inbound_auth", {}).get("password"):
            masked["inbound_auth"]["password"] = "********"
        if masked.get("register_config", {}).get("password"):
            masked["register_config"]["password"] = "********"
        if masked.get("outbound", {}).get("password"):
            masked["outbound"]["password"] = "********"
        return masked

    async def _get_active_calls(self, trunk_id: str) -> int:
        """Get active call count for a trunk."""
        val = await self._redis.get(f"trunk:calls:{trunk_id}")
        return int(val) if val else 0

    async def increment_active_calls(self, trunk_id: str) -> int:
        """Increment active calls counter."""
        return await self._redis.incr(f"trunk:calls:{trunk_id}")

    async def decrement_active_calls(self, trunk_id: str) -> int:
        """Decrement active calls counter."""
        val = await self._redis.decr(f"trunk:calls:{trunk_id}")
        if val < 0:
            await self._redis.set(f"trunk:calls:{trunk_id}", 0)
            return 0
        return val


trunk_service = TrunkService()
