"""SIP Endpoint APIs for Asterisk integration.

Provides ARI + ExternalMedia integration based on AVA/Dograph patterns:
- ARI Stasis receives call control events
- ExternalMedia creates WebSocket channel for bidirectional audio
- Audio flows WebSocket <-> Pipeline (STT/LLM/TTS)
"""

import asyncio
import json
import uuid
import socket
import audioop
from typing import Any, Optional
from dataclasses import dataclass

from fastapi import (
    APIRouter,
    HTTPException,
    Request,
    Query,
    WebSocket,
    WebSocketDisconnect,
)
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
from app.services.agent_resolution_service import agent_resolution_service
from app.services.session_manager import session_manager
from app.services.task_manager import task_manager

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


# ============================================================================
# ARI WebSocket Endpoint (ExternalMedia via WebSocket)
# ============================================================================


@router.websocket("/test")
async def test_websocket(ws: WebSocket):
    """Test WebSocket endpoint."""
    logger.info("[TEST WS] Connection request")
    await ws.accept()
    logger.info("[TEST WS] Connection accepted")
    await ws.send_text(json.dumps({"type": "connected"}))
    await ws.close()


@router.websocket("/ari/audio")
@router.websocket("/ari/audio/{audio_key:path}")
async def ari_audio_websocket(
    ws: WebSocket,
    audio_key: str = None,
):
    """WebSocket endpoint for Asterisk ARI ExternalMedia audio.

    Accepts connections from Asterisk ExternalMedia channels.
    Flow:
    1. Receives MEDIA_START event with channel_id
    2. Resolves call metadata (agent_id, tenant_id, etc.) from Redis
       (stored by tito_ari_manager before creating ExternalMedia)
    3. Starts Pipecat pipeline for bidirectional audio
    """
    logger.info(f"[ARI WS] Connection request | audio_key={audio_key}")

    await ws.accept()
    logger.info(f"[ARI WS] Connection accepted, waiting for MEDIA_START...")

    channel_id = None
    agent_id = None
    tenant_id = "central"
    caller_id = None
    trunk_id = None
    asterisk_channel = None

    try:
        first_msg = await asyncio.wait_for(ws.receive_text(), timeout=5.0)
        first_data = json.loads(first_msg)
        logger.info(f"[ARI WS] Received: {first_data}")

        if first_data.get("event") == "MEDIA_START":
            asterisk_channel = first_data.get("channel")
            channel_id = first_data.get("channel_id")
            connection_id = first_data.get("connection_id")
            logger.info(
                f"[ARI WS] MEDIA_START | channel={channel_id} connection={connection_id}"
            )
    except asyncio.TimeoutError:
        logger.warning("[ARI WS] No MEDIA_START, closing")
        await ws.close(code=4002)
        return
    except Exception as e:
        logger.error(f"[ARI WS] Failed to parse first message: {e}")
        await ws.close(code=4002)
        return

    # Resolve call metadata from Redis.
    # tito_ari_manager stores it at ari:pending_audio:{ext_channel_id}
    # before the ExternalMedia channel is created, so it's available by now.
    if channel_id:
        try:
            redis = session_manager._redis
            if redis:
                pending_key = f"ari:pending_audio:{channel_id}"
                pending_raw = await redis.get(pending_key)
                if pending_raw:
                    data = json.loads(pending_raw)
                    agent_id = data.get("agent_id")
                    tenant_id = data.get("tenant_id", tenant_id)
                    caller_id = data.get("caller_id")
                    trunk_id = data.get("trunk_id")
                    # Clean up the pending key
                    await redis.delete(pending_key)
                    logger.info(
                        f"[ARI WS] Resolved from Redis | agent={agent_id} tenant={tenant_id}"
                    )
                else:
                    logger.warning(
                        f"[ARI WS] No pending audio data for channel={channel_id}"
                    )
        except Exception as e:
            logger.error(f"[ARI WS] Redis lookup failed: {e}")

    if not channel_id or not agent_id:
        await ws.send_text(
            json.dumps(
                {
                    "type": "error",
                    "message": f"Missing channel_id={channel_id} or agent_id={agent_id}",
                }
            )
        )
        await ws.close(code=4002)
        return

    session_id = f"ari_{channel_id}"

    try:
        agent_config = await agent_resolution_service.resolve_agent(
            agent_id=agent_id,
            tenant_id=tenant_id,
        )

        if not agent_config:
            logger.error(f"[ARI WS] Agent not found: {agent_id}")
            await ws.send_text(
                json.dumps({"type": "error", "message": f"Agent {agent_id} not found"})
            )
            await ws.close(code=4001)
            return

        logger.info(f"[ARI WS] Agent resolved | agent={agent_id} session={session_id}")

        # 2. Save session
        await session_manager.save_session(
            session_id=session_id,
            config=agent_config,
            room_name=channel_id,  # Use channel_id as room identifier
            provider="ari",
        )

        # 3. Track task
        await task_manager.add(session_id, asyncio.current_task())

        # 4. Setup transport and pipeline
        await _run_ari_pipeline(
            ws=ws,
            session_id=session_id,
            agent_config=agent_config,
            channel_id=channel_id,
            caller_id=caller_id,
            trunk_id=trunk_id,
        )

    except WebSocketDisconnect:
        logger.info(f"[ARI WS] Client disconnected | session={session_id}")
    except Exception as e:
        logger.exception(f"[ARI WS] Error | session={session_id}: {e}")
        try:
            await ws.send_text(json.dumps({"type": "error", "message": str(e)}))
        except:
            pass
    finally:
        # Cleanup
        await task_manager.remove(session_id)
        await session_manager.delete_session(session_id)
        try:
            await ws.close()
        except:
            pass


async def _run_ari_pipeline(
    ws: WebSocket,
    session_id: str,
    agent_config: AgentConfig,
    channel_id: str,
    caller_id: Optional[str],
    trunk_id: Optional[str],
):
    """Run Pipecat pipeline for ARI call."""
    import pipecat
    from pipecat.audio.vad.silero import SileroVADAnalyzer
    from pipecat.audio.vad.vad_analyzer import VADParams
    from pipecat.frames.frames import (
        EndFrame,
        InputAudioRawFrame,
        OutputAudioRawFrame,
    )
    from pipecat.pipeline.task import PipelineParams, PipelineTask
    from pipecat.serializers.base_serializer import FrameSerializer
    from pipecat.transports.network.fastapi_websocket import (
        FastAPIWebsocketParams,
        FastAPIWebsocketTransport,
    )

    from app.services.agents.factory.builder import ServiceFactory
    from app.services.agents.pipelines.context_setup import setup_context
    from app.services.agents.pipelines.pipeline_builder import build_pipeline

    # Audio params - Asterisk sends slin (16-bit signed linear) at 8kHz
    sample_rate = 8000

    # VAD configuration
    vad_params = VADParams(
        confidence=0.7,
        start_secs=0.4,
        stop_secs=0.2,
        min_volume=0.6,
    )
    vad_analyzer = SileroVADAnalyzer(params=vad_params)

    # Serializer for slin (signed linear 16-bit PCM)
    class SlinPCMSerializer(FrameSerializer):
        """Serializer for signed linear 16-bit PCM (Asterisk slin format)."""

        def __init__(self, sr: int):
            super().__init__()
            self._sr = sr

        async def serialize(self, frame):
            if isinstance(frame, OutputAudioRawFrame):
                return frame.audio
            return None

        async def deserialize(self, data):
            if isinstance(data, (bytes, bytearray)):
                return InputAudioRawFrame(
                    audio=bytes(data),
                    sample_rate=self._sr,
                    num_channels=1,
                )
            return None

    # Transport params
    params = FastAPIWebsocketParams(
        audio_in_enabled=True,
        audio_out_enabled=True,
        audio_in_sample_rate=sample_rate,
        audio_out_sample_rate=sample_rate,
        vad_enabled=True,
        vad_analyzer=vad_analyzer,
        add_wav_header=False,
        serializer=SlinPCMSerializer(sample_rate),
    )

    transport = FastAPIWebsocketTransport(websocket=ws, params=params)

    # Services
    stt = ServiceFactory.create_stt_service(agent_config)
    llm = ServiceFactory.create_llm_service(agent_config)
    tts = ServiceFactory.create_tts_service(agent_config)

    # Context
    _llm_context, context_aggregator = setup_context(
        session_id, agent_config, vad_analyzer
    )

    # Add caller_id to context if available
    if caller_id:
        _llm_context.add_message(
            {
                "role": "system",
                "content": f"Caller phone number: {caller_id}",
            }
        )

    # Build pipeline
    pipeline, _ = build_pipeline(
        transport=transport,
        stt=stt,
        llm=llm,
        tts=tts,
        context_aggregator=context_aggregator,
        config=agent_config,
    )

    # Create task
    task = PipelineTask(
        pipeline,
        params=PipelineParams(allow_interruptions_by_all=True),
    )

    # Event handlers
    @transport.event_handler("on_client_connected")
    async def on_connected(transport, ws):
        logger.info(f"[ARI WS] Client connected | session={session_id}")
        await ws.send_text(json.dumps({"type": "started", "session_id": session_id}))

        # Trigger first bot message if configured
        from pipecat.frames.frames import LLMRunFrame

        if agent_config.behavior.initial_action == "SPEAK_FIRST":
            await task.queue_frames([LLMRunFrame()])

    @transport.event_handler("on_client_disconnected")
    async def on_disconnected(transport, ws):
        logger.info(f"[ARI WS] Client disconnected | session={session_id}")

    # Run pipeline
    runner = pipecat.pipeline.runner.PipelineRunner()
    await runner.run(task)

    logger.info(f"[ARI WS] Pipeline completed | session={session_id}")
