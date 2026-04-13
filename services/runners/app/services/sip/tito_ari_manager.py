"""Tito ARI Manager - Adaptado de Dograh para sistema Tito.

Standalone process that:
1. Connects to Asterisk ARI via WebSocket
2. Listens for StasisStart events on extensions
3. Resolves trunk and agent_id from Redis
4. Creates ExternalMedia channels (WebSocket to Tito API)
5. Manages bridges and call lifecycle

Based on patterns from Dograh ARI implementation.
"""

import asyncio
import json
import logging
import signal
from typing import Dict, Optional, Any
from urllib.parse import urlparse

import aiohttp
import websockets
from fastapi import WebSocket
from loguru import logger

from app.services.session_manager import session_manager
from app.services.trunk_service import trunk_service

# Redis key patterns for channel tracking
_ARI_CHANNEL_PREFIX = "ari:channel:"
_ARI_EXT_CHANNEL_PREFIX = "ari:ext:"
_ARI_BRIDGE_PREFIX = "ari:bridge:"
_ARI_PENDING_AUDIO_PREFIX = "ari:pending_audio:"
_CHANNEL_KEY_TTL = 3600


class TitoARIConnection:
    """Manages a single ARI WebSocket connection for a trunk."""

    def __init__(
        self,
        trunk_id: str,
        ari_endpoint: str,
        app_name: str,
        app_password: str,
        api_host: str = "localhost",
        api_port: int = 8000,
    ):
        self.trunk_id = trunk_id
        self.ari_endpoint = ari_endpoint.rstrip("/")
        self.app_name = app_name
        self.app_password = app_password
        self.api_host = api_host
        self.api_port = api_port

        self._ws: Optional[websockets.WebSocketClientProtocol] = None
        self._task: Optional[asyncio.Task] = None
        self._running = False
        self._reconnect_delay = 1
        self._max_reconnect_delay = 300
        self._ping_interval = 30

        # Lazy init Redis
        self._redis = None

    async def _get_redis(self):
        """Get Redis client from session_manager."""
        if not self._redis:
            self._redis = session_manager._redis
        return self._redis

    @property
    def ws_url(self) -> str:
        """Build the ARI WebSocket URL."""
        parsed = urlparse(self.ari_endpoint)
        ws_scheme = "wss" if parsed.scheme == "https" else "ws"
        return (
            f"{ws_scheme}://{parsed.netloc}/ari/events"
            f"?api_key={self.app_name}:{self.app_password}"
            f"&app={self.app_name}"
            f"&subscribeAll=true"
        )

    async def start(self):
        """Start the WebSocket connection."""
        if self._running:
            return
        self._running = True
        self._task = asyncio.create_task(self._connection_loop())
        logger.info(
            f"[ARI trunk={self.trunk_id}] Started connection to {self.ari_endpoint}"
        )

    async def stop(self):
        """Stop the WebSocket connection."""
        self._running = False
        if self._ws:
            await self._ws.close()
        if self._task and not self._task.done():
            self._task.cancel()
            try:
                await self._task
            except asyncio.CancelledError:
                pass
        logger.info(f"[ARI trunk={self.trunk_id}] Stopped connection")

    async def _connection_loop(self):
        """Main connection loop with reconnection logic."""
        while self._running:
            try:
                await self._connect_and_listen()
            except asyncio.CancelledError:
                break
            except Exception as e:
                if not self._running:
                    break
                logger.warning(
                    f"[ARI trunk={self.trunk_id}] Connection error: {e}. "
                    f"Reconnecting in {self._reconnect_delay}s..."
                )
                await asyncio.sleep(self._reconnect_delay)
                self._reconnect_delay = min(
                    self._reconnect_delay * 2, self._max_reconnect_delay
                )

    async def _connect_and_listen(self):
        """Connect to ARI and listen for events."""
        logger.info(f"[ARI trunk={self.trunk_id}] Connecting to {self.ari_endpoint}...")

        async for ws in websockets.connect(
            self.ws_url,
            ping_interval=self._ping_interval,
            ping_timeout=10,
            close_timeout=5,
        ):
            try:
                self._ws = ws
                self._reconnect_delay = 1
                logger.info(f"[ARI trunk={self.trunk_id}] WebSocket connected")

                async for message in ws:
                    if not self._running:
                        return
                    if isinstance(message, str):
                        await self._handle_event(message)

            except websockets.ConnectionClosed as e:
                if not self._running:
                    return
                logger.warning(
                    f"[ARI trunk={self.trunk_id}] WebSocket closed: code={e.code}"
                )
                continue
            finally:
                self._ws = None

    async def _handle_event(self, raw_data: str):
        """Handle ARI events."""
        try:
            event = json.loads(raw_data)
        except json.JSONDecodeError:
            logger.warning(
                f"[ARI trunk={self.trunk_id}] Invalid JSON: {raw_data[:200]}"
            )
            return

        event_type = event.get("type", "unknown")
        channel = event.get("channel", {})
        channel_id = channel.get("id", "unknown")
        channel_state = channel.get("state", "unknown")

        logger.debug(
            f"[ARI EVENT trunk={self.trunk_id}] {event_type}: channel={channel_id}, state={channel_state}"
        )

        if event_type == "StasisStart":
            # Skip external media channels we created
            if await self._is_ext_channel(channel_id):
                logger.debug(
                    f"[ARI trunk={self.trunk_id}] Skipping our externalMedia channel {channel_id}"
                )
                return

            app_args = event.get("args", [])
            caller = channel.get("caller", {})
            logger.info(
                f"[ARI trunk={self.trunk_id}] StasisStart: channel={channel_id}, "
                f"state={channel_state}, caller={caller.get('number', 'unknown')}, args={app_args}"
            )

            extension = app_args[0] if len(app_args) > 0 else None

            if channel_state == "Ring":
                # Inbound call
                asyncio.create_task(
                    self._handle_inbound_call(channel_id, extension, event)
                )
            else:
                # Outbound call
                asyncio.create_task(
                    self._handle_outbound_call(channel_id, extension, event)
                )

        elif event_type == "StasisEnd":
            logger.info(f"[ARI trunk={self.trunk_id}] StasisEnd: channel={channel_id}")
            asyncio.create_task(self._handle_stasis_end(channel_id))

        elif event_type == "ChannelDestroyed":
            cause = event.get("cause_txt", "unknown")
            logger.info(
                f"[ARI trunk={self.trunk_id}] ChannelDestroyed: channel={channel_id}, cause={cause}"
            )

    async def _handle_inbound_call(self, channel_id: str, extension: str, event: dict):
        """Handle inbound call: resolve trunk/agent, create external media, bridge."""
        channel = event.get("channel", {})
        caller_number = channel.get("caller", {}).get("number", "unknown")

        try:
            # 1. Get trunk data from Redis
            trunk_data = await self._get_trunk_data()
            if not trunk_data:
                logger.error(f"[ARI trunk={self.trunk_id}] Trunk data not found")
                await self._hangup_channel(channel_id)
                return

            # 2. Get agent_id from trunk routes
            agent_id = self._get_agent_id_from_trunk(trunk_data, extension)
            if not agent_id:
                logger.error(
                    f"[ARI trunk={self.trunk_id}] No agent_id for extension {extension}"
                )
                await self._hangup_channel(channel_id)
                return

            tenant_id = trunk_data.get("tenant_id", "central")

            logger.info(
                f"[ARI trunk={self.trunk_id}] Inbound call: extension={extension}, "
                f"agent_id={agent_id}, caller={caller_number}"
            )

            # 3. Create external media channel (WebSocket to Tito API)
            ext_channel_id = await self._create_external_media(
                channel_id=channel_id,
                agent_id=agent_id,
                tenant_id=tenant_id,
                caller_id=caller_number,
            )

            if not ext_channel_id:
                logger.error(
                    f"[ARI trunk={self.trunk_id}] Failed to create external media"
                )
                await self._hangup_channel(channel_id)
                return

            # 4. Answer the original channel
            await self._answer_channel(channel_id)

            # 5. Create bridge and add channels
            bridge_id = await self._create_bridge([channel_id, ext_channel_id])
            if not bridge_id:
                logger.error(f"[ARI trunk={self.trunk_id}] Failed to create bridge")
                await self._hangup_channel(channel_id)
                await self._hangup_channel(ext_channel_id)
                return

            # 6. Store session info in Redis
            session_data = {
                "trunk_id": self.trunk_id,
                "agent_id": agent_id,
                "tenant_id": tenant_id,
                "extension": extension,
                "caller_id": caller_number,
                "channel_id": channel_id,
                "ext_channel_id": ext_channel_id,
                "bridge_id": bridge_id,
                "direction": "inbound",
            }
            await self._set_channel_session(channel_id, session_data)
            await self._set_channel_session(ext_channel_id, session_data)

            logger.info(
                f"[ARI trunk={self.trunk_id}] Call setup complete: bridge={bridge_id}, "
                f"agent={agent_id}"
            )

        except Exception as e:
            logger.exception(
                f"[ARI trunk={self.trunk_id}] Error handling inbound call: {e}"
            )
            await self._hangup_channel(channel_id)

    async def _handle_outbound_call(self, channel_id: str, extension: str, event: dict):
        """Handle outbound call."""
        # TODO: Implement outbound call handling
        logger.info(f"[ARI trunk={self.trunk_id}] Outbound call: channel={channel_id}")

    async def _handle_stasis_end(self, channel_id: str):
        """Handle StasisEnd - cleanup resources."""
        try:
            session_data = await self._get_channel_session(channel_id)
            if not session_data:
                return

            bridge_id = session_data.get("bridge_id")
            ext_channel_id = session_data.get("ext_channel_id")
            original_channel_id = session_data.get("channel_id")

            # Destroy bridge
            if bridge_id:
                await self._delete_bridge(bridge_id)

            # Hangup other channel if still active
            other_channel = (
                ext_channel_id
                if channel_id == original_channel_id
                else original_channel_id
            )
            if other_channel and other_channel != channel_id:
                await self._hangup_channel(other_channel)

            # Cleanup Redis
            await self._delete_channel_session(channel_id)
            await self._delete_channel_session(other_channel)

            logger.info(
                f"[ARI trunk={self.trunk_id}] Cleanup complete for {channel_id}"
            )

        except Exception as e:
            logger.error(f"[ARI trunk={self.trunk_id}] Error in StasisEnd cleanup: {e}")

    async def _get_trunk_data(self) -> Optional[dict]:
        """Get trunk data from Redis."""
        try:
            data = await trunk_service._redis.get(f"trunk:{self.trunk_id}")
            if data:
                return json.loads(data)
        except Exception as e:
            logger.error(f"[ARI trunk={self.trunk_id}] Error getting trunk data: {e}")
        return None

    def _get_agent_id_from_trunk(
        self, trunk_data: dict, extension: str
    ) -> Optional[str]:
        """Extract agent_id from trunk routes."""
        routes = trunk_data.get("routes", [])
        for route in routes:
            pattern = route.get("pattern", "")
            if pattern == extension or pattern == "*":
                return route.get("agent_id")
        return None

    async def _create_external_media(
        self,
        channel_id: str,
        agent_id: str,
        tenant_id: str,
        caller_id: str,
    ) -> Optional[str]:
        """Create ExternalMedia channel via ARI with WebSocket transport.

        Uses the 'tito-ari' websocket_client profile (websocket_client.conf).
        That profile has connection_type=per_call_config, so we can override
        the base URI per-call via the ARI 'data' field.
        """
        # Build the full WebSocket URI with call-specific query params.
        # Asterisk will connect to this URI when the ExternalMedia channel is created.
        ws_uri = (
            f"ws://{self.api_host}:{self.api_port}/api/v1/sip/ari/audio"
            f"?channel_id={channel_id}"
            f"&agent_id={agent_id}"
            f"&tenant_id={tenant_id}"
            f"&caller_id={caller_id or ''}"
            f"&trunk_id={self.trunk_id}"
        )

        result = await self._ari_request(
            "POST",
            "/channels/externalMedia",
            data={
                "app": self.app_name,
                "external_host": "tito-ari",
                "format": "slin",
                "direction": "both",
                "transport": "websocket",
                "encapsulation": "none",
                "data": f"uri={ws_uri}",
            },
        )

        ext_channel_id = result.get("id")
        if ext_channel_id:
            await self._mark_ext_channel(ext_channel_id)

            # Store full call metadata so the WebSocket handler (sip.py)
            # can resolve agent/tenant without query params or registration messages.
            redis = await self._get_redis()
            pending_data = json.dumps({
                "channel_id": channel_id,
                "agent_id": agent_id,
                "tenant_id": tenant_id,
                "caller_id": caller_id,
                "trunk_id": self.trunk_id,
            })
            await redis.setex(
                f"{_ARI_PENDING_AUDIO_PREFIX}{ext_channel_id}",
                _CHANNEL_KEY_TTL,
                pending_data,
            )

            logger.info(
                f"[ARI trunk={self.trunk_id}] Created external media: "
                f"{ext_channel_id} -> {self.api_host}:{self.api_port}"
            )
        else:
            logger.error(
                f"[ARI trunk={self.trunk_id}] ExternalMedia creation failed: {result}"
            )
        return ext_channel_id

    async def _answer_channel(self, channel_id: str):
        """Answer a channel via ARI."""
        await self._ari_request("POST", f"/channels/{channel_id}/answer")
        logger.info(f"[ARI trunk={self.trunk_id}] Answered channel {channel_id}")

    async def _create_bridge(self, channel_ids: list) -> Optional[str]:
        """Create bridge and add channels."""
        # Create bridge
        result = await self._ari_request(
            "POST",
            "/bridges",
            params={
                "type": "mixing",
                "name": f"tito_{channel_ids[0][:8]}",
            },
        )
        bridge_id = result.get("id")
        if not bridge_id:
            return None

        # Add channels
        await self._ari_request(
            "POST",
            f"/bridges/{bridge_id}/addChannel",
            params={"channel": ",".join(channel_ids)},
        )

        logger.info(
            f"[ARI trunk={self.trunk_id}] Created bridge {bridge_id} with {channel_ids}"
        )
        return bridge_id

    async def _hangup_channel(self, channel_id: str):
        """Hangup a channel."""
        await self._ari_request(
            "DELETE", f"/channels/{channel_id}", tolerate_statuses=[404]
        )

    async def _delete_bridge(self, bridge_id: str):
        """Delete a bridge."""
        await self._ari_request(
            "DELETE", f"/bridges/{bridge_id}", tolerate_statuses=[404]
        )

    async def _ari_request(
        self,
        method: str,
        path: str,
        params: dict = None,
        data: dict = None,
        tolerate_statuses: list = None,
    ) -> dict:
        """Make ARI REST request."""
        url = f"{self.ari_endpoint}/ari{path}"
        auth = aiohttp.BasicAuth(self.app_name, self.app_password)

        async with aiohttp.ClientSession() as session:
            kwargs = {"auth": auth}
            if params:
                kwargs["params"] = params
            if data:
                kwargs["data"] = data

            async with session.request(method, url, **kwargs) as resp:
                if resp.status >= 400:
                    if tolerate_statuses and resp.status in tolerate_statuses:
                        return {}
                    text = await resp.text()
                    logger.error(
                        f"[ARI trunk={self.trunk_id}] {method} {path}: {resp.status} - {text}"
                    )
                    return {}
                if resp.status == 204:
                    return {}
                return await resp.json()

    # Redis helper methods
    async def _set_channel_session(self, channel_id: str, data: dict):
        """Store channel session in Redis."""
        redis = await self._get_redis()
        await redis.setex(
            f"{_ARI_CHANNEL_PREFIX}{channel_id}",
            _CHANNEL_KEY_TTL,
            json.dumps(data),
        )

    async def _get_channel_session(self, channel_id: str) -> Optional[dict]:
        """Get channel session from Redis."""
        redis = await self._get_redis()
        data = await redis.get(f"{_ARI_CHANNEL_PREFIX}{channel_id}")
        if data:
            return json.loads(data)
        return None

    async def _delete_channel_session(self, channel_id: str):
        """Delete channel session from Redis."""
        redis = await self._get_redis()
        await redis.delete(f"{_ARI_CHANNEL_PREFIX}{channel_id}")

    async def _mark_ext_channel(self, channel_id: str):
        """Mark channel as external media we created."""
        redis = await self._get_redis()
        await redis.setex(
            f"{_ARI_EXT_CHANNEL_PREFIX}{channel_id}",
            _CHANNEL_KEY_TTL,
            "1",
        )

    async def _is_ext_channel(self, channel_id: str) -> bool:
        """Check if channel is our external media."""
        redis = await self._get_redis()
        return await redis.exists(f"{_ARI_EXT_CHANNEL_PREFIX}{channel_id}") > 0


class TitoARIManager:
    """Manages ARI connections for all trunks."""

    def __init__(self):
        self._connections: Dict[str, TitoARIConnection] = {}
        self._running = False
        self._refresh_interval = 60

    async def start(self):
        """Start ARI manager."""
        self._running = True
        logger.info("Tito ARI Manager starting...")

        # Wait for Redis connection
        retry_count = 0
        max_retries = 30
        while retry_count < max_retries:
            try:
                if session_manager._redis:
                    await session_manager._redis.ping()
                    logger.info("✓ Redis connection established")
                    break
            except Exception:
                pass

            retry_count += 1
            if retry_count >= max_retries:
                raise RuntimeError("Failed to connect to Redis after 30 attempts")

            logger.debug(f"Waiting for Redis... (attempt {retry_count}/{max_retries})")
            await asyncio.sleep(1)

        await self._refresh_connections()

        while self._running:
            await asyncio.sleep(self._refresh_interval)
            if self._running:
                await self._refresh_connections()

    async def stop(self):
        """Stop all connections."""
        self._running = False
        logger.info("Tito ARI Manager stopping...")

        for conn in self._connections.values():
            await conn.stop()
        self._connections.clear()

    async def _refresh_connections(self):
        """Refresh connections based on trunk configurations."""
        try:
            # Get all inbound trunks from Redis
            # This is simplified - in production you'd scan or maintain an index
            active_trunks = await self._load_trunk_configs()
        except Exception as e:
            logger.error(f"Failed to load trunk configs: {e}")
            return

        active_ids = set()

        for trunk_data in active_trunks:
            trunk_id = trunk_data["trunk_id"]
            active_ids.add(trunk_id)

            if trunk_id not in self._connections:
                # New trunk - start connection
                conn = TitoARIConnection(
                    trunk_id=trunk_id,
                    ari_endpoint=trunk_data.get("ari_endpoint", "http://asterisk:8088"),
                    app_name=trunk_data.get("app_name", "tito-ai"),
                    app_password=trunk_data.get("app_password", "tito-secret"),
                    api_host=trunk_data.get("api_host", "pipecat-runners-api"),
                    api_port=trunk_data.get("api_port", 8000),
                )
                self._connections[trunk_id] = conn
                await conn.start()
                logger.info(f"[ARI Manager] Started connection for trunk {trunk_id}")

        # Stop removed trunks
        removed = set(self._connections.keys()) - active_ids
        for trunk_id in removed:
            conn = self._connections.pop(trunk_id)
            await conn.stop()
            logger.info(f"[ARI Manager] Stopped connection for trunk {trunk_id}")

    async def _load_trunk_configs(self) -> list:
        """Load trunk configurations from Redis.

        Scans all trunk:index:* keys and loads trunk data for inbound trunks.
        """
        configs = []

        try:
            redis = session_manager._redis

            # Get all workspace indices
            index_keys = []
            async for key in redis.scan_iter(match="trunk:index:*"):
                index_keys.append(key)

            # Get all trunk IDs from indices
            all_trunk_ids = set()
            for index_key in index_keys:
                trunk_ids = await redis.smembers(index_key)
                all_trunk_ids.update(trunk_ids)

            # Load trunk data for each trunk
            for trunk_id in all_trunk_ids:
                try:
                    trunk_data_raw = await redis.get(f"trunk:{trunk_id}")
                    if not trunk_data_raw:
                        continue

                    trunk_data = json.loads(trunk_data_raw)

                    # Only process inbound trunks with ARI configuration
                    if trunk_data.get("mode") != "inbound":
                        continue

                    # Build config with defaults
                    config = {
                        "trunk_id": trunk_id,
                        "ari_endpoint": trunk_data.get(
                            "ari_endpoint", "http://asterisk:8088"
                        ),
                        "app_name": trunk_data.get("app_name", "tito-ai"),
                        "app_password": trunk_data.get(
                            "app_password", "tito-ari-secret"
                        ),
                        "api_host": trunk_data.get("api_host", "localhost"),
                        "api_port": trunk_data.get("api_port", 8000),
                        "workspace_slug": trunk_data.get("workspace_slug", "default"),
                        "tenant_id": trunk_data.get("tenant_id", "central"),
                        "routes": trunk_data.get("routes", []),
                    }

                    configs.append(config)
                    logger.debug(f"[ARI Manager] Loaded trunk config: {trunk_id}")

                except json.JSONDecodeError as e:
                    logger.error(f"[ARI Manager] Failed to parse trunk {trunk_id}: {e}")
                except Exception as e:
                    logger.error(f"[ARI Manager] Error loading trunk {trunk_id}: {e}")

            if configs:
                logger.info(f"[ARI Manager] Loaded {len(configs)} inbound trunk(s)")

        except Exception as e:
            logger.error(f"[ARI Manager] Error loading trunk configs: {e}")

        return configs


async def main():
    """Entry point for ARI manager process."""
    manager = TitoARIManager()

    loop = asyncio.get_running_loop()
    shutdown_event = asyncio.Event()

    def signal_handler():
        logger.info("Received shutdown signal")
        shutdown_event.set()

    for sig in (signal.SIGTERM, signal.SIGINT):
        loop.add_signal_handler(sig, signal_handler)

    manager_task = asyncio.create_task(manager.start())
    await shutdown_event.wait()

    await manager.stop()
    manager_task.cancel()
    try:
        await manager_task
    except asyncio.CancelledError:
        pass

    logger.info("Tito ARI Manager exited cleanly")


if __name__ == "__main__":
    asyncio.run(main())
