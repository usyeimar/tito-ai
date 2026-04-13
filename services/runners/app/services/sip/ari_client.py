"""Async Asterisk REST Interface (ARI) client.

Provides WebSocket event listening and HTTP command sending for full call
control via Stasis applications.  Used when SIP_TRANSPORT=ari.

Based on patterns from AVA and Dograph reference implementations.
"""

import asyncio
import json
import logging
import uuid
from typing import Callable, Dict, List, Optional, Any, Awaitable
from urllib.parse import quote

import aiohttp
from aiohttp import BasicAuth

logger = logging.getLogger(__name__)

EventHandler = Callable[[Dict[str, Any]], Awaitable[None]]


class ARIClient:
    """Async client for the Asterisk REST Interface."""

    def __init__(
        self,
        host: str = "asterisk",
        port: int = 8088,
        username: str = "tito-ai",
        password: str = "tito-ari-secret",
        app_name: str = "tito-ai",
        scheme: str = "http",
    ):
        self.host = host
        self.port = port
        self.username = username
        self.password = password
        self.app_name = app_name
        self.scheme = scheme

        self.http_url = f"{scheme}://{host}:{port}/ari"
        ws_scheme = "wss" if scheme == "https" else "ws"
        safe_user = quote(username)
        safe_pass = quote(password)
        self.ws_url = (
            f"{ws_scheme}://{host}:{port}/ari/events"
            f"?api_key={safe_user}:{safe_pass}"
            f"&app={app_name}&subscribeAll=true"
        )

        self._session: Optional[aiohttp.ClientSession] = None
        self._ws: Optional[aiohttp.ClientWebSocketResponse] = None
        self._running = False
        self._connected = False
        self._event_handlers: Dict[str, List[EventHandler]] = {}
        self._reconnect_attempt = 0
        self._max_backoff = 60

    # ── Properties ────────────────────────────────────────────────────────────

    @property
    def is_connected(self) -> bool:
        return self._connected and self._running

    # ── Event Registration ────────────────────────────────────────────────────

    def on_event(self, event_type: str, handler: EventHandler):
        """Register a handler for an ARI event type."""
        if event_type not in self._event_handlers:
            self._event_handlers[event_type] = []
        self._event_handlers[event_type].append(handler)

    # ── Connection ────────────────────────────────────────────────────────────

    async def connect(self):
        """Establish HTTP session and verify ARI is reachable."""
        if self._session is None or self._session.closed:
            self._session = aiohttp.ClientSession(
                auth=BasicAuth(self.username, self.password)
            )

        # Verify ARI is available
        try:
            async with self._session.get(f"{self.http_url}/asterisk/info") as resp:
                if resp.status != 200:
                    raise ConnectionError(f"ARI HTTP returned {resp.status}")
            logger.info(f"ARI HTTP connected to {self.http_url}")
        except aiohttp.ClientError as e:
            logger.error(f"ARI HTTP connection failed: {e}")
            raise

    async def start_listening(self):
        """Start the WebSocket event listener with automatic reconnection."""
        self._running = True
        while self._running:
            try:
                await self._listen()
            except asyncio.CancelledError:
                break
            except Exception as e:
                self._connected = False
                if not self._running:
                    break
                self._reconnect_attempt += 1
                backoff = min(2**self._reconnect_attempt, self._max_backoff)
                logger.warning(f"ARI WebSocket error, reconnecting in {backoff}s: {e}")
                await asyncio.sleep(backoff)

        logger.info("ARI event listener stopped")

    async def _listen(self):
        """Connect to ARI WebSocket and dispatch events."""
        if not self._session or self._session.closed:
            await self.connect()

        logger.info("Connecting to ARI WebSocket...")
        self._ws = await self._session.ws_connect(self.ws_url, heartbeat=30)
        self._connected = True
        self._reconnect_attempt = 0
        logger.info("ARI WebSocket connected")

        try:
            async for msg in self._ws:
                if msg.type == aiohttp.WSMsgType.TEXT:
                    try:
                        event = json.loads(msg.data)
                        event_type = event.get("type", "")
                        handlers = self._event_handlers.get(event_type, [])
                        for handler in handlers:
                            asyncio.create_task(handler(event))
                    except json.JSONDecodeError:
                        logger.warning(f"Invalid ARI event JSON: {msg.data[:200]}")
                elif msg.type in (
                    aiohttp.WSMsgType.CLOSED,
                    aiohttp.WSMsgType.ERROR,
                ):
                    break
        finally:
            self._connected = False
            if self._ws and not self._ws.closed:
                await self._ws.close()

    async def disconnect(self):
        """Gracefully disconnect."""
        self._running = False
        if self._ws and not self._ws.closed:
            await self._ws.close()
        if self._session and not self._session.closed:
            await self._session.close()
        self._connected = False
        logger.info("ARI client disconnected")

    # ── REST Commands ─────────────────────────────────────────────────────────

    async def send_command(
        self,
        method: str,
        resource: str,
        data: Optional[Dict[str, Any]] = None,
        params: Optional[Dict[str, Any]] = None,
        tolerate_statuses: Optional[List[int]] = None,
    ) -> Dict[str, Any]:
        """Send a command to the ARI REST API."""
        if not self._session or self._session.closed:
            await self.connect()

        url = f"{self.http_url}/{resource}"
        try:
            async with self._session.request(
                method, url, json=data, params=params
            ) as resp:
                if resp.status >= 400:
                    reason = await resp.text()
                    if tolerate_statuses and resp.status in tolerate_statuses:
                        logger.debug(
                            f"ARI {method} {resource}: {resp.status} (tolerated)"
                        )
                    else:
                        logger.error(
                            f"ARI {method} {resource}: {resp.status} - {reason}"
                        )
                    return {"status": resp.status, "reason": reason}

                if resp.status == 204:
                    return {"status": 204}

                return await resp.json()

        except aiohttp.ClientError as e:
            logger.error(f"ARI request error: {method} {resource}: {e}")
            return {"status": 500, "reason": str(e)}

    # ── Channel Operations ────────────────────────────────────────────────────

    async def answer_channel(self, channel_id: str):
        """Answer a channel."""
        logger.info(f"ARI: Answering channel {channel_id}")
        return await self.send_command("POST", f"channels/{channel_id}/answer")

    async def hangup_channel(self, channel_id: str):
        """Hang up a channel."""
        logger.info(f"ARI: Hanging up channel {channel_id}")
        return await self.send_command(
            "DELETE", f"channels/{channel_id}", tolerate_statuses=[404]
        )

    async def get_channel_var(self, channel_id: str, variable: str) -> Optional[str]:
        """Get a channel variable."""
        resp = await self.send_command(
            "GET",
            f"channels/{channel_id}/variable",
            params={"variable": variable},
            tolerate_statuses=[404],
        )
        return resp.get("value")

    async def set_channel_var(self, channel_id: str, variable: str, value: str) -> bool:
        """Set a channel variable."""
        resp = await self.send_command(
            "POST",
            f"channels/{channel_id}/variable",
            data={"variable": variable, "value": value},
        )
        return resp.get("status", 200) < 400

    # ── Bridge Operations ─────────────────────────────────────────────────────

    async def create_bridge(self, bridge_type: str = "mixing") -> Optional[str]:
        """Create a new bridge and return its ID."""
        resp = await self.send_command(
            "POST",
            "bridges",
            data={
                "type": bridge_type,
                "name": f"tito_{uuid.uuid4().hex[:8]}",
            },
        )
        bridge_id = resp.get("id")
        if bridge_id:
            logger.info(f"ARI: Bridge created {bridge_id}")
        return bridge_id

    async def add_channel_to_bridge(self, bridge_id: str, channel_id: str) -> bool:
        """Add a channel to a bridge."""
        resp = await self.send_command(
            "POST",
            f"bridges/{bridge_id}/addChannel",
            data={"channel": channel_id},
        )
        status = resp.get("status", 200)
        return status < 400 or status in (409, 422)

    async def destroy_bridge(self, bridge_id: str):
        """Destroy a bridge."""
        await self.send_command(
            "DELETE", f"bridges/{bridge_id}", tolerate_statuses=[404]
        )

    # ── ExternalMedia ─────────────────────────────────────────────────────────

    async def create_external_media_channel(
        self,
        external_host: str,
        format: str = "slin16",
        direction: str = "both",
    ) -> Optional[Dict[str, Any]]:
        """Create an ExternalMedia channel for RTP audio streaming."""
        resp = await self.send_command(
            "POST",
            "channels/externalMedia",
            data={
                "app": self.app_name,
                "external_host": external_host,
                "format": format,
                "direction": direction,
                "encapsulation": "rtp",
            },
        )
        if resp.get("id"):
            logger.info(
                f"ARI: ExternalMedia channel created {resp['id']} -> {external_host}"
            )
            return resp
        logger.error(f"ARI: Failed to create ExternalMedia: {resp}")
        return None

    async def create_external_media_websocket(
        self,
        profile: str = "tito-ari",
        ws_uri: str = "",
        format: str = "slin",
        direction: str = "both",
    ) -> Optional[Dict[str, Any]]:
        """Create an ExternalMedia channel for WebSocket audio streaming.

        Uses a websocket_client profile defined in websocket_client.conf.
        The 'data' field can override the profile's base URI per-call.

        Args:
            profile: websocket_client profile name from websocket_client.conf
            ws_uri: Optional URI override (passed via data field)
            format: Audio format ("slin" for 8kHz 16-bit signed linear)
            direction: Audio direction ("both", "in", "out")

        Returns:
            Dict with channel info including "id" if successful, None otherwise
        """
        payload = {
            "app": self.app_name,
            "external_host": profile,
            "format": format,
            "direction": direction,
            "transport": "websocket",
        }
        if ws_uri:
            payload["data"] = f"uri={ws_uri}"

        resp = await self.send_command(
            "POST",
            "channels/externalMedia",
            data=payload,
        )
        if resp.get("id"):
            logger.info(
                f"ARI: ExternalMedia WebSocket channel created {resp['id']} "
                f"via profile={profile}"
            )
            return resp
        logger.error(f"ARI: Failed to create ExternalMedia WebSocket: {resp}")
        return None

    # ── Originate ─────────────────────────────────────────────────────────────

    async def originate_channel(
        self,
        endpoint: str,
        app_args: str = "",
        caller_id: str = "",
        timeout: int = 60,
    ) -> Optional[Dict[str, Any]]:
        """Originate an outbound call via ARI."""
        params: Dict[str, Any] = {
            "endpoint": endpoint,
            "app": self.app_name,
            "timeout": str(timeout),
        }
        if app_args:
            params["appArgs"] = app_args
        if caller_id:
            params["callerId"] = caller_id

        return await self.send_command("POST", "channels", params=params)

    # ── Dialplan Continuation ─────────────────────────────────────────────────

    async def continue_in_dialplan(
        self,
        channel_id: str,
        context: str,
        extension: str = "s",
        priority: int = 1,
    ) -> bool:
        """Return a Stasis channel to the dialplan."""
        resp = await self.send_command(
            "POST",
            f"channels/{channel_id}/continue",
            params={
                "context": context,
                "extension": extension,
                "priority": str(priority),
            },
        )
        return resp.get("status", 200) < 400
