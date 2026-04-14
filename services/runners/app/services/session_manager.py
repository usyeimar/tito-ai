"""Redis-backed session manager for tracking active agent sessions across pods."""

import asyncio
import logging
import json
import time
from typing import Optional, Dict, Any, List
from fastapi import WebSocket, WebSocketDisconnect
import redis.asyncio as aioredis
from app.schemas.agent import AgentConfig
from app.core.config import settings

logger = logging.getLogger(__name__)

RUNNER_FRAME_QUEUE_MAXSIZE = 150
MAX_SESSION_DURATION = 3600  # 1 hora máxima

SESSION_INDEX_GLOBAL = "session:index:global"
SESSION_INDEX_HOST_PREFIX = "session:index:host:"
BROADCAST_CHANNEL = "session:broadcast:global"


class SessionManager:
    def __init__(self, redis_url: str):
        self._redis = aioredis.from_url(redis_url, decode_responses=True)
        self._sockets: Dict[str, List[WebSocket]] = {}
        self._transcript_sockets: Dict[str, List[WebSocket]] = {}
        self._global_listener_task: Optional[asyncio.Task] = None
        self._global_pubsub = None

    async def save_session(
        self,
        session_id: str,
        config: AgentConfig,
        room_name: Optional[str] = None,
        provider: Optional[str] = None,
    ) -> None:
        """Persiste metadatos en Redis."""
        key = f"session:{session_id}"

        # Obtener datos existentes si los hay
        existing_raw = await self._redis.get(key)
        existing = json.loads(existing_raw) if existing_raw else {}

        value = {
            **existing,
            "session_id": session_id,
            "agent_id": config.agent_id,
            "tenant_id": config.tenant_id,
            "host_id": settings.HOST_ID,
            "room_name": room_name or existing.get("room_name"),
            "provider": provider or existing.get("provider"),
            "config": config.model_dump_json(),
            "status": "running",
            "created_at": existing.get("created_at", time.time()),
            "updated_at": time.time(),
        }

        await self._redis.setex(key, MAX_SESSION_DURATION, json.dumps(value))

        # Add to global and host-specific indexes
        await self._redis.sadd(SESSION_INDEX_GLOBAL, session_id)
        await self._redis.sadd(
            f"{SESSION_INDEX_HOST_PREFIX}{settings.HOST_ID}", session_id
        )

    async def get_session(self, session_id: str) -> Optional[Dict[str, Any]]:
        """Recupera metadatos de Redis."""
        data = await self._redis.get(f"session:{session_id}")
        return json.loads(data) if data else None

    async def list_sessions(
        self, host_id: Optional[str] = None
    ) -> List[Dict[str, Any]]:
        """Lista todas las sesiones activas en Redis.

        Args:
            host_id: Optional filter by specific host instance.
                     If None, returns all sessions from global index.
        """
        # Get session IDs from index
        if host_id:
            session_ids = await self._redis.smembers(
                f"{SESSION_INDEX_HOST_PREFIX}{host_id}"
            )
        else:
            session_ids = await self._redis.smembers(SESSION_INDEX_GLOBAL)

        sessions = []
        stale_ids = []

        for session_id in session_ids:
            key = f"session:{session_id}"
            data = await self._redis.get(key)
            if data:
                try:
                    sessions.append(json.loads(data))
                except Exception:
                    continue
            else:
                # Session expired, mark for cleanup
                stale_ids.append(session_id)

        # Lazy cleanup of stale entries from index
        if stale_ids:
            for sid in stale_ids:
                await self._redis.srem(SESSION_INDEX_GLOBAL, sid)
                await self._redis.srem(
                    f"{SESSION_INDEX_HOST_PREFIX}{host_id or settings.HOST_ID}", sid
                )

        return sessions

    async def delete_session(self, session_id: str) -> bool:
        """Elimina metadatos de Redis."""
        result = await self._redis.delete(f"session:{session_id}")

        # Remove from indexes
        await self._redis.srem(SESSION_INDEX_GLOBAL, session_id)
        await self._redis.srem(
            f"{SESSION_INDEX_HOST_PREFIX}{settings.HOST_ID}", session_id
        )

        return result > 0

    async def update_session_status(self, session_id: str, status: str) -> bool:
        """Actualiza el estado de la sesión en Redis."""
        key = f"session:{session_id}"
        data = await self._redis.get(key)
        if data:
            session = json.loads(data)
            session["status"] = status
            session["updated_at"] = time.time()
            await self._redis.setex(key, MAX_SESSION_DURATION, json.dumps(session))
            return True
        return False

    # ── Transcript Management ────────────────────────────────────────────

    async def broadcast_transcript(
        self, session_id: str, role: str, content: str, timestamp: str
    ):
        """Envia transcripción a Redis para clientes subscriptos."""
        import json

        payload = json.dumps(
            {
                "type": "transcript",
                "role": role,
                "content": content,
                "timestamp": timestamp,
            }
        )
        await self._redis.publish(f"session:{session_id}:transcripts", payload)

    async def subscribe_to_transcripts(self, session_id: str, ws: WebSocket):
        """Suscribe un WebSocket a transcripciones de la sesión."""
        if session_id not in self._transcript_sockets:
            self._transcript_sockets[session_id] = []
            asyncio.create_task(self._listen_transcripts(session_id))

        self._transcript_sockets[session_id].append(ws)

    async def unsubscribe_from_transcripts(self, session_id: str, ws: WebSocket):
        """Desuscribe un WebSocket de transcripciones."""
        sockets = self._transcript_sockets.get(session_id, [])
        if ws in sockets:
            sockets.remove(ws)

    async def _listen_transcripts(self, session_id: str):
        """Escucha transcripciones de Redis y las envĂ­a a WebSockets."""
        import json

        pubsub = self._redis.pubsub()
        await pubsub.subscribe(f"session:{session_id}:transcripts")

        try:
            async for message in pubsub.listen():
                if message["type"] == "message":
                    sockets = list(self._transcript_sockets.get(session_id, []))
                    if not sockets:
                        break

                    for ws in sockets:
                        try:
                            await asyncio.wait_for(
                                ws.send_text(message["data"]), timeout=0.050
                            )
                        except Exception:
                            pass
        finally:
            self._transcript_sockets.pop(session_id, None)

    # ── WebSocket Management ──────────────────────────────────────────────

    async def connect_ws(self, session_id: str, ws: WebSocket) -> None:
        """Registra un WebSocket localmente y suscribe a Redis Pub/Sub si es el primero."""
        await ws.accept()
        if session_id not in self._sockets:
            self._sockets[session_id] = []
            # Iniciar tarea de suscripción para esta sesión
            asyncio.create_task(self._subscribe_to_session_events(session_id))

        self._sockets[session_id].append(ws)
        logger.info(
            f"[{session_id}] WS local connected ({len(self._sockets[session_id])} listeners)"
        )

    def disconnect_ws(self, session_id: str, ws: WebSocket) -> None:
        """Elimina un WebSocket local."""
        listeners = self._sockets.get(session_id, [])
        if ws in listeners:
            listeners.remove(ws)
        if not listeners:
            self._sockets.pop(session_id, None)
        logger.info(
            f"[{session_id}] WS local disconnected ({len(listeners)} listeners left)"
        )

    async def _subscribe_to_session_events(self, session_id: str):
        """Tarea que escucha eventos de Redis y los envía a los WebSockets locales."""
        pubsub = self._redis.pubsub()
        await pubsub.subscribe(f"session:{session_id}:events")

        try:
            async for message in pubsub.listen():
                if message["type"] == "message":
                    event_data = message["data"]
                    # Enviar a todos los sockets locales para esta sesión
                    sockets = list(self._sockets.get(session_id, []))
                    if not sockets:
                        break  # No hay más listeners locales, cerrar suscripción

                    for ws in sockets:
                        try:
                            # Timeout de 50ms como en Fase 0.2
                            await asyncio.wait_for(
                                ws.send_text(event_data), timeout=0.050
                            )
                        except Exception:
                            self.disconnect_ws(session_id, ws)
        except Exception as e:
            logger.error(f"[{session_id}] Pub/Sub error: {e}")
        finally:
            await pubsub.unsubscribe(f"session:{session_id}:events")
            await pubsub.close()

    async def emit(self, session_id: str, event: Dict[str, Any]) -> None:
        """Publica un evento en Redis Pub/Sub para que todas las instancias lo reciban."""
        await self._redis.publish(f"session:{session_id}:events", json.dumps(event))

    async def start_global_listener(self) -> None:
        """Start listening to global broadcast channel on this instance."""
        if self._global_listener_task is not None:
            return

        self._global_pubsub = self._redis.pubsub()
        await self._global_pubsub.subscribe(BROADCAST_CHANNEL)
        self._global_listener_task = asyncio.create_task(self._listen_broadcast())
        logger.info("Global broadcast listener started")

    async def stop_global_listener(self) -> None:
        """Stop the global broadcast listener."""
        if self._global_listener_task:
            self._global_listener_task.cancel()
            try:
                await self._global_listener_task
            except asyncio.CancelledError:
                pass
            self._global_listener_task = None

        if self._global_pubsub:
            await self._global_pubsub.unsubscribe(BROADCAST_CHANNEL)
            await self._global_pubsub.close()
            self._global_pubsub = None

        logger.info("Global broadcast listener stopped")

    async def _listen_broadcast(self) -> None:
        """Listen for global broadcast events and forward to all local sockets."""
        try:
            async for message in self._global_pubsub.listen():
                if message["type"] == "message":
                    event_data = message["data"]
                    # Forward to all sessions' sockets
                    for session_id, sockets in list(self._sockets.items()):
                        for ws in list(sockets):
                            try:
                                await asyncio.wait_for(
                                    ws.send_text(event_data), timeout=0.050
                                )
                            except Exception:
                                self.disconnect_ws(session_id, ws)
        except asyncio.CancelledError:
            pass
        except Exception as e:
            logger.error(f"Global broadcast listener error: {e}")

    async def broadcast(self, event: Dict[str, Any]) -> None:
        """Publish an event to the global broadcast channel.

        This reaches all runners via Redis pub/sub, regardless of which
        runner the session is on. Each runner forwards to its local sockets.
        """
        await self._redis.publish(BROADCAST_CHANNEL, json.dumps(event))


session_manager = SessionManager(settings.REDIS_URL)
