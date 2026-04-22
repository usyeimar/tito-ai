"""Redis command consumer for receiving commands from Laravel.

Protocol:
    1. Laravel LPUSHes a JSON command to `runner:commands` (or `runner:commands:{host_id}`).
    2. This consumer BRPOPs from that list and dispatches the command.
    3. For synchronous commands, it LPUSHes the response to `runner:responses:{request_id}`.
    4. Laravel BRPOPs from the response key with a timeout.

Commands:
    - session.create  → Creates a new voice session (synchronous, returns session data)
    - session.terminate → Terminates an existing session (async, no response expected)
"""

import asyncio
import json
import logging
import time
import uuid
from typing import Optional

import redis.asyncio as aioredis

from app.core.config import settings
from app.schemas.agent import AgentConfig
from app.services.session_manager import session_manager
from app.services.task_manager import task_manager

logger = logging.getLogger(__name__)

COMMANDS_KEY = "runner:commands"
RESPONSE_KEY_PREFIX = "runner:responses:"
RESPONSE_TTL_SECONDS = 30


class CommandConsumer:
    """Consumes commands from Laravel via Redis and dispatches them."""

    def __init__(self) -> None:
        self._redis: Optional[aioredis.Redis] = None
        self._running = False
        self._task: Optional[asyncio.Task] = None

    @property
    def redis(self) -> aioredis.Redis:
        if self._redis is None:
            self._redis = aioredis.from_url(settings.REDIS_URL, decode_responses=True)
        return self._redis

    async def start(self) -> None:
        """Start the command consumer loop."""
        self._running = True
        self._task = asyncio.create_task(self._consume_loop(), name="command-consumer")
        logger.info(
            f"Command consumer started | keys={self._listen_keys()} | host_id={settings.HOST_ID}"
        )

    async def stop(self) -> None:
        """Stop the command consumer gracefully."""
        self._running = False
        if self._task and not self._task.done():
            self._task.cancel()
            try:
                await self._task
            except asyncio.CancelledError:
                pass
        if self._redis:
            await self._redis.aclose()
            self._redis = None
        logger.info("Command consumer stopped")

    def _listen_keys(self) -> list[str]:
        """Keys to BRPOP from: host-specific first, then shared."""
        keys = []
        if settings.HOST_ID:
            keys.append(f"{COMMANDS_KEY}:{settings.HOST_ID}")
        keys.append(COMMANDS_KEY)
        return keys

    async def _consume_loop(self) -> None:
        """Main loop: BRPOP commands and dispatch them."""
        keys = self._listen_keys()

        while self._running:
            try:
                result = await self.redis.brpop(keys, timeout=5)
                if result is None:
                    continue

                _key, raw = result
                await self._handle_message(raw)

            except asyncio.CancelledError:
                break
            except aioredis.ConnectionError as e:
                logger.error(f"Redis connection lost: {e}, reconnecting in 2s...")
                self._redis = None
                await asyncio.sleep(2)
            except Exception as e:
                logger.error(f"Command consumer error: {e}", exc_info=True)
                await asyncio.sleep(1)

    async def _handle_message(self, raw: str) -> None:
        """Parse and dispatch a single command message."""
        try:
            message = json.loads(raw)
        except json.JSONDecodeError:
            logger.warning(f"Invalid JSON command: {raw[:100]}")
            return

        request_id = message.get("request_id", "")
        command = message.get("command", "")
        payload = message.get("payload", {})
        is_async = message.get("async", False)

        logger.info(f"Command received | command={command} | request_id={request_id}")

        try:
            result = await self._dispatch(command, payload)

            if not is_async and request_id:
                await self._send_response(request_id, command, result)

        except Exception as e:
            logger.error(f"Command failed | command={command} | error={e}")
            if not is_async and request_id:
                await self._send_response(
                    request_id, command, None, error=str(e)
                )

    async def _dispatch(self, command: str, payload: dict) -> Optional[dict]:
        """Route command to the appropriate handler."""
        match command:
            case "session.create":
                return await self._handle_create_session(payload)
            case "session.terminate":
                await self._handle_terminate_session(payload)
                return None
            case _:
                raise ValueError(f"Unknown command: {command}")

    async def _handle_create_session(self, payload: dict) -> dict:
        """Create a new voice session — mirrors the HTTP create_session logic."""
        if task_manager.count() >= settings.MAX_CONCURRENT_SESSIONS:
            raise RuntimeError(
                f"Runner at capacity: {task_manager.count()}/{settings.MAX_CONCURRENT_SESSIONS}"
            )

        config = AgentConfig.model_validate(payload)

        provider = settings.DEFAULT_TRANSPORT_PROVIDER.lower()
        if (
            hasattr(config, "runtime_profiles")
            and config.runtime_profiles
            and hasattr(config.runtime_profiles, "transport")
            and config.runtime_profiles.transport
        ):
            provider = config.runtime_profiles.transport.provider.lower()

        session_id = f"sess_{uuid.uuid4().hex[:12]}"
        room_name = f"tito_{config.agent_id}_{uuid.uuid4().hex[:8]}"
        participant_name = f"user_{uuid.uuid4().hex[:6]}"

        if provider == "websocket":
            await session_manager.save_session(
                session_id, config, room_name=session_id, provider=provider
            )
            return {
                "session_id": session_id,
                "room_name": session_id,
                "provider": "websocket",
                "url": "",
                "access_token": "",
                "context": {
                    "agent_id": config.agent_id,
                    "tenant_id": config.tenant_id,
                },
            }

        if provider == "daily":
            from app.services.daily_service import DailyService

            room_data = await DailyService.create_room_and_tokens(
                room_name, participant_name
            )
        else:
            from app.services.livekit_service import LiveKitService

            room_data = LiveKitService.create_room_and_tokens(
                room_name, participant_name
            )

        await session_manager.save_session(
            session_id, config, room_name=room_name, provider=provider
        )

        # Launch pipeline in background
        from app.api.v1.sessions import _run_session

        task = asyncio.create_task(
            _run_session(session_id, room_data, config, provider),
            name=f"session-{session_id}",
        )
        await task_manager.add(session_id, task)

        logger.info(f"Session created via Redis | session_id={session_id} | provider={provider}")

        return {
            "session_id": session_id,
            "room_name": room_data["room_name"],
            "provider": provider,
            "ws_url": room_data.get("ws_url", ""),
            "url": room_data.get("ws_url", ""),
            "access_token": room_data.get("user_token", ""),
            "context": {
                "agent_id": config.agent_id,
                "tenant_id": config.tenant_id,
                "created_at": time.time(),
            },
        }

    async def _handle_terminate_session(self, payload: dict) -> None:
        """Terminate an existing session."""
        session_id = payload.get("session_id", "")
        if not session_id:
            logger.warning("Terminate command missing session_id")
            return

        session = await session_manager.get_session(session_id)
        if not session:
            logger.warning(f"Session not found for terminate: {session_id}")
            return

        await task_manager.stop(session_id)

        # Clean up room
        try:
            room_name = session.get("room_name")
            provider = session.get("provider", "livekit")
            if provider == "daily":
                from app.services.daily_service import DailyService

                await DailyService.delete_room(room_name)
            elif provider == "livekit":
                from app.services.livekit_service import LiveKitService

                await LiveKitService.delete_room(room_name)
        except Exception as e:
            logger.warning(f"Room cleanup failed: {session_id} | {e}")

        logger.info(f"Session terminated via Redis | session_id={session_id}")

    async def _send_response(
        self,
        request_id: str,
        command: str,
        data: Optional[dict],
        error: Optional[str] = None,
    ) -> None:
        """Push response to the response key for Laravel to BRPOP."""
        response = {
            "request_id": request_id,
            "command": command,
            "data": data or {},
            "error": error,
            "host_id": settings.HOST_ID,
            "timestamp": time.time(),
        }

        key = f"{RESPONSE_KEY_PREFIX}{request_id}"
        await self.redis.lpush(key, json.dumps(response))
        await self.redis.expire(key, RESPONSE_TTL_SECONDS)

        logger.debug(f"Response sent | request_id={request_id} | error={error}")


# Singleton
command_consumer = CommandConsumer()
