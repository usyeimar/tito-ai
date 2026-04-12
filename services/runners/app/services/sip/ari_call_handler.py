"""ARI Call Handler - Manages SIP calls via Asterisk REST Interface + Stasis.

When a call enters the Stasis application "tito-ai":
1. StasisStart event is received via ARI WebSocket
2. Call metadata is extracted (extension, caller ID)
3. The call is answered and redirected to the AudioSocket dialplan
4. Audio flows to the Pipecat pipeline via AudioSocket

This gives ARI-level visibility into calls (events, metadata, DTMF)
while reusing the AudioSocket pipeline for actual audio processing.

For full ARI audio (ExternalMedia/RTP), a future iteration can replace
the continue_in_dialplan step with bridge + ExternalMedia channel.
"""

import asyncio
import logging
from typing import Dict, Any, Optional

from app.services.sip.ari_client import ARIClient

logger = logging.getLogger(__name__)


class ARICallHandler:
    """Handles SIP calls via ARI Stasis events."""

    def __init__(
        self,
        ari_client: ARIClient,
        audiosocket_host: str = "pipecat-runners-api",
        audiosocket_port: int = 9092,
    ):
        self._ari = ari_client
        self._audiosocket_host = audiosocket_host
        self._audiosocket_port = audiosocket_port
        self._active_calls: Dict[str, Dict[str, Any]] = {}

        # Register ARI event handlers
        self._ari.on_event("StasisStart", self._on_stasis_start)
        self._ari.on_event("StasisEnd", self._on_stasis_end)
        self._ari.on_event("ChannelHangupRequest", self._on_hangup_request)
        self._ari.on_event("ChannelDtmfReceived", self._on_dtmf)

    async def _on_stasis_start(self, event: Dict[str, Any]):
        """Handle a new call entering the Stasis application."""
        channel = event.get("channel", {})
        channel_id = channel.get("id", "")
        channel_name = channel.get("name", "")
        channel_state = channel.get("state", "")
        args = event.get("args", [])

        caller_num = channel.get("caller", {}).get("number", "unknown")

        logger.info(
            f"ARI StasisStart | channel={channel_id} name={channel_name} "
            f"state={channel_state} caller={caller_num} args={args}"
        )

        # Parse Stasis args: Stasis(tito-ai,<extension>,<caller_id>)
        extension = args[0] if len(args) > 0 else None
        caller_from_args = args[1] if len(args) > 1 else None

        # Skip non-caller channels (ExternalMedia, Local, AudioSocket)
        if not self._is_caller_channel(channel):
            logger.debug(f"ARI: Skipping non-caller channel {channel_name}")
            return

        # Handle outbound calls differently
        if extension and extension.lower() == "outbound":
            await self._handle_outbound(channel_id, channel, args)
            return

        # Inbound call flow
        await self._handle_inbound(
            channel_id, channel, extension, caller_from_args or caller_num
        )

    async def _handle_inbound(
        self,
        channel_id: str,
        channel: Dict[str, Any],
        extension: Optional[str],
        caller_id: str,
    ):
        """Handle an inbound call via ARI.

        Strategy: Answer the call, set metadata variables, then redirect
        to the AudioSocket dialplan context. The AudioSocket handler takes
        over audio processing.  ARI retains visibility via events.
        """
        try:
            logger.info(
                f"ARI: Handling inbound call | channel={channel_id} "
                f"extension={extension} caller={caller_id}"
            )

            # 1. Store call state (for tracking / future features)
            self._active_calls[channel_id] = {
                "channel": channel,
                "extension": extension,
                "caller_id": caller_id,
            }

            # 2. Set channel variables before redirecting to dialplan
            #    These will be available to the AudioSocket handler via AMI GetVar
            if extension:
                await self._ari.set_channel_var(channel_id, "AGENT_ID", extension)
            if caller_id:
                await self._ari.set_channel_var(channel_id, "ARI_CALLER_ID", caller_id)

            # 3. Redirect to AudioSocket dialplan context
            #    The channel exits Stasis and enters the AudioSocket flow:
            #    Answer() -> AudioSocket(UUID, host:port) -> pipeline
            success = await self._ari.continue_in_dialplan(
                channel_id,
                context="tito-inbound",
                extension=extension or "100",
                priority=1,
            )

            if success:
                logger.info(
                    f"ARI: Call redirected to AudioSocket | channel={channel_id} "
                    f"extension={extension}"
                )
            else:
                logger.error(f"ARI: Failed to redirect call | channel={channel_id}")
                await self._ari.hangup_channel(channel_id)

        except Exception as e:
            logger.exception(f"ARI: Error handling inbound call {channel_id}: {e}")
            await self._ari.hangup_channel(channel_id)
            self._active_calls.pop(channel_id, None)

    async def _handle_outbound(
        self,
        channel_id: str,
        channel: Dict[str, Any],
        args: list,
    ):
        """Handle an outbound call that entered Stasis."""
        call_id = args[1] if len(args) > 1 else None
        agent_id = args[2] if len(args) > 2 else None

        logger.info(
            f"ARI: Outbound call | channel={channel_id} "
            f"call_id={call_id} agent_id={agent_id}"
        )

        self._active_calls[channel_id] = {
            "channel": channel,
            "call_id": call_id,
            "agent_id": agent_id,
            "outbound": True,
        }

        # Set variables and redirect to outbound AudioSocket context
        if call_id:
            await self._ari.set_channel_var(channel_id, "CALL_ID", call_id)
        if agent_id:
            await self._ari.set_channel_var(channel_id, "AGENT_ID", agent_id)

        await self._ari.continue_in_dialplan(
            channel_id,
            context="tito-outbound",
            extension="s",
            priority=1,
        )

    async def _on_stasis_end(self, event: Dict[str, Any]):
        """Handle a call leaving the Stasis application."""
        channel_id = event.get("channel", {}).get("id", "")
        call = self._active_calls.pop(channel_id, None)
        if call:
            logger.info(
                f"ARI StasisEnd | channel={channel_id} "
                f"extension={call.get('extension')}"
            )
        else:
            logger.debug(f"ARI StasisEnd | channel={channel_id} (not tracked)")

    async def _on_hangup_request(self, event: Dict[str, Any]):
        """Handle a hangup request."""
        channel_id = event.get("channel", {}).get("id", "")
        logger.info(f"ARI ChannelHangupRequest | channel={channel_id}")

    async def _on_dtmf(self, event: Dict[str, Any]):
        """Handle DTMF received via ARI."""
        channel_id = event.get("channel", {}).get("id", "")
        digit = event.get("digit", "")
        logger.info(f"ARI DTMF | channel={channel_id} digit={digit}")

    def _is_caller_channel(self, channel: Dict[str, Any]) -> bool:
        """Check if this is a caller channel (not Local/ExternalMedia)."""
        name = channel.get("name", "")
        return not any(
            name.startswith(prefix)
            for prefix in ("Local/", "ExternalMedia/", "AudioSocket/")
        )

    @property
    def active_calls(self) -> int:
        return len(self._active_calls)
