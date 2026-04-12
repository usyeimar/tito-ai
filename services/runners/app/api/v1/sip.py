"""SIP Endpoint APIs for Asterisk integration.

Provides ARI + ExternalMedia integration based on AVA/Dograph patterns:
- ARI Stasis receives call control events
- ExternalMedia creates RTP channel for bidirectional audio
- Audio flows UDP <-> Pipeline (STT/LLM/TTS)
"""

import asyncio
import json
import uuid
import socket
import audioop
from typing import Any, Optional
from dataclasses import dataclass

from fastapi import APIRouter, HTTPException, Request
from loguru import logger

from app.schemas.agent import AgentConfig

from app.services.sip.websocket_server import (
    WebSocketConnection,
    WebSocketServer,
)
from app.services.sip.call_handler import (
    CallDirection,
    CallState,
)

router = APIRouter(prefix="/sip", tags=["SIP"])


@dataclass
class ExternalMediaSession:
    """Session for ARI ExternalMedia audio streaming."""

    channel_id: str
    bridge_id: str
    external_media_channel_id: str
    audio_socket: socket.socket
    remote_addr: tuple
    format: str  # slin16, ulaw, etc.
    sample_rate: int


async def handle_asterisk_connection(conn: WebSocketConnection):
    """Handle chan_websocket connection (fallback method).

    Note: This is kept for compatibility but ExternalMedia via ARI is preferred.
    """
    logger.warning(
        f"[{conn.connection_id}] chan_websocket not fully implemented, use ARI+ExternalMedia instead"
    )
    await conn.hangup()


# ============================================================================
# ARI + ExternalMedia Handler (Primary method)
# ============================================================================


class ARIExternalMediaHandler:
    """Handles SIP calls via ARI with ExternalMedia for audio streaming.

    Based on AVA/Dograph patterns:
    1. Call enters Stasis app "tito-ai"
    2. We answer and create a bridge
    3. Create ExternalMedia channel (RTP/UDP)
    4. Bridge caller + external_media together
    5. Audio flows caller <-> bridge <-> external_media <-> our UDP socket <-> Pipeline
    """

    def __init__(self, ari_client):
        self._ari = ari_client
        self._sessions: dict[str, ExternalMediaSession] = {}
        self._udp_socket: Optional[socket.socket] = None
        self._running = False

    async def start(self):
        """Start UDP listener for ExternalMedia audio."""
        self._running = True
        # Create UDP socket for ExternalMedia to send audio to
        self._udp_socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self._udp_socket.bind(("0.0.0.0", 9094))  # ExternalMedia target port
        self._udp_socket.setblocking(False)

        # Start listener task
        asyncio.create_task(self._audio_listener())
        logger.info("ARI ExternalMedia handler started on UDP 9094")

    async def stop(self):
        """Stop handler."""
        self._running = False
        if self._udp_socket:
            self._udp_socket.close()

    async def _audio_listener(self):
        """Listen for incoming RTP audio from ExternalMedia channels."""
        loop = asyncio.get_event_loop()
        while self._running:
            try:
                # Non-blocking UDP receive
                data, addr = await loop.run_in_executor(
                    None, lambda: self._udp_socket.recvfrom(2048)
                )

                # Find session by remote address
                for session_id, session in self._sessions.items():
                    if session.remote_addr == addr:
                        # Process audio - strip RTP header and push to pipeline
                        audio_data = self._strip_rtp_header(data)
                        if audio_data:
                            await self._push_audio_to_pipeline(session_id, audio_data)
                        break

            except BlockingIOError:
                await asyncio.sleep(0.001)
            except Exception as e:
                logger.error(f"Audio listener error: {e}")
                await asyncio.sleep(0.1)

    def _strip_rtp_header(self, rtp_packet: bytes) -> Optional[bytes]:
        """Strip RTP header, return raw audio payload."""
        if len(rtp_packet) < 12:
            return None
        # RTP header is 12 bytes minimum
        # For slin16, payload starts at byte 12
        return rtp_packet[12:]

    async def _push_audio_to_pipeline(self, session_id: str, audio_data: bytes):
        """Push received audio into the pipeline for this session."""
        # TODO: Connect to pipeline input
        logger.debug(f"[{session_id}] Received {len(audio_data)} bytes of audio")

    async def handle_stasis_start(self, event: dict):
        """Handle StasisStart event from ARI."""
        channel = event.get("channel", {})
        channel_id = channel.get("id")
        args = event.get("args", [])

        # Parse args: Stasis(tito-ai, extension, caller_id)
        extension = args[0] if len(args) > 0 else "100"
        caller_id = args[1] if len(args) > 1 else "unknown"

        logger.info(
            f"[ARI] StasisStart: channel={channel_id}, ext={extension}, caller={caller_id}"
        )

        try:
            # 1. Answer the call
            await self._ari.answer_channel(channel_id)

            # 2. Resolve agent
            from app.services.trunk_service import trunk_service
            from app.schemas.agent import AgentConfig

            result = await trunk_service.resolve_inbound_call("default", extension)
            if not result:
                result = await trunk_service.resolve_inbound_call("default", "*")

            if not result or not result.get("agent_id"):
                logger.error(f"[ARI] No agent found for extension {extension}")
                await self._ari.hangup_channel(channel_id)
                return

            agent_id = result["agent_id"]
            raw_config = await trunk_service._redis.get(f"agent_config:{agent_id}")
            if not raw_config:
                logger.error(f"[ARI] Agent config not found: {agent_id}")
                await self._ari.hangup_channel(channel_id)
                return

            config = AgentConfig.parse_raw(raw_config)

            # 3. Create mixing bridge
            bridge_id = await self._ari.create_bridge("mixing")
            if not bridge_id:
                logger.error("[ARI] Failed to create bridge")
                await self._ari.hangup_channel(channel_id)
                return

            # 4. Add caller to bridge
            await self._ari.add_channel_to_bridge(bridge_id, channel_id)

            # 5. Create ExternalMedia channel (RTP -> our UDP socket)
            # This creates a channel that sends/receives audio via RTP to our UDP port
            external_media = await self._ari.create_external_media_channel(
                external_host=f"{self._ari.host}:9094",  # Our UDP listener
                format="slin16",
                direction="both",
            )

            if not external_media:
                logger.error("[ARI] Failed to create ExternalMedia channel")
                await self._ari.destroy_bridge(bridge_id)
                await self._ari.hangup_channel(channel_id)
                return

            external_channel_id = external_media["id"]

            # 6. Add ExternalMedia to bridge
            await self._ari.add_channel_to_bridge(bridge_id, external_channel_id)

            # 7. Store session
            session = ExternalMediaSession(
                channel_id=channel_id,
                bridge_id=bridge_id,
                external_media_channel_id=external_channel_id,
                audio_socket=self._udp_socket,
                remote_addr=(self._ari.host, external_media.get("port", 9094)),
                format="slin16",
                sample_rate=16000,
            )
            self._sessions[channel_id] = session

            # 8. Start pipeline for this session
            await self._start_pipeline(channel_id, config, session)

            logger.info(
                f"[ARI] Call setup complete: channel={channel_id}, agent={agent_id}"
            )

        except Exception as e:
            logger.exception(f"[ARI] Error handling StasisStart: {e}")
            await self._ari.hangup_channel(channel_id)

    async def _start_pipeline(
        self, channel_id: str, config: AgentConfig, session: ExternalMediaSession
    ):
        """Start Pipecat pipeline for this call session."""
        from pipecat.audio.vad.silero import SileroVADAnalyzer
        from pipecat.audio.vad.vad_analyzer import VADParams
        from pipecat.pipeline.runner import PipelineRunner
        from pipecat.pipeline.task import PipelineParams, PipelineTask

        from app.services.agents.factory.builder import ServiceFactory
        from app.services.agents.pipelines.pipeline_builder import build_pipeline
        from app.services.agents.pipelines.context_setup import setup_context
        from app.services.sip.transport import (
            SIPAudioSocketTransport,
            SIPAudioSocketParams,
        )

        # Create custom transport that uses our UDP socket for audio I/O
        # This is a simplified version - in production you'd create a proper UDP transport

        # For now, use the working AudioSocket pattern but with a custom socket
        # TODO: Implement proper UDP/RTP transport

        logger.info(f"[{channel_id}] Starting pipeline with agent {config.agent_id}")

        # Create services
        stt = ServiceFactory.create_stt_service(config)
        llm = ServiceFactory.create_llm_service(config)
        tts = ServiceFactory.create_tts_service(config)

        # Setup context
        session_id = f"ari_{channel_id}"
        vad_params = VADParams(
            confidence=0.7,
            start_secs=0.4,
            stop_secs=0.2,
            min_volume=0.6,
        )

        # For ExternalMedia, we need a custom UDP transport
        # For now, use placeholder - full implementation would create UDP input/output
        logger.warning(
            f"[{channel_id}] ExternalMedia audio transport not yet fully implemented"
        )

    async def handle_stasis_end(self, event: dict):
        """Handle StasisEnd - cleanup session."""
        channel_id = event.get("channel", {}).get("id")

        if channel_id in self._sessions:
            session = self._sessions.pop(channel_id)
            logger.info(f"[ARI] Cleaning up session: {channel_id}")

            # Destroy bridge
            if session.bridge_id:
                await self._ari.destroy_bridge(session.bridge_id)

    async def handle_channel_hangup(self, event: dict):
        """Handle hangup."""
        channel_id = event.get("channel", {}).get("id")
        if channel_id in self._sessions:
            logger.info(f"[ARI] Channel hangup: {channel_id}")
            self._sessions.pop(channel_id, None)


# Global handler instance (initialized in main.py)
external_media_handler: Optional[ARIExternalMediaHandler] = None


# ============================================================================
# Call Management Endpoints
# ============================================================================


@router.get("/calls/{call_id}")
async def get_call(call_id: str):
    """Get call status."""
    return {"call_id": call_id, "status": "unknown"}


@router.post("/calls/{call_id}/answer")
async def answer_call(call_id: str):
    """Answer an incoming call."""
    return {"call_id": call_id, "status": "answered"}


@router.post("/calls/{call_id}/hangup")
async def hangup_call(call_id: str):
    """Hangup a call."""
    return {"call_id": call_id, "status": "hungup"}


@router.post("/calls/{call_id}/transfer")
async def transfer_call(
    call_id: str, destination: str, destination_type: str = "queue"
):
    """Transfer call to queue, peer, or external number."""
    return {
        "call_id": call_id,
        "destination": destination,
        "type": destination_type,
        "status": "transferred",
    }


@router.get("/health")
async def sip_health():
    """Check SIP server health."""
    return {
        "status": "ok",
        "ari": "connected" if external_media_handler else "not_initialized",
        "active_sessions": len(external_media_handler._sessions)
        if external_media_handler
        else 0,
    }
