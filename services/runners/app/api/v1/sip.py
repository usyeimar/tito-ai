"""SIP endpoints for Asterisk integration.

Provides the `/ari/audio` WebSocket endpoint that Asterisk connects to when
an ARI ExternalMedia channel is created. Call control is driven by
`TitoARIManager` (see app/services/sip/tito_ari_manager.py) which stores call
metadata in Redis at `ari:pending_audio:{channel_id}` before creating the
ExternalMedia channel; this endpoint picks up that metadata on connection.

Audio flow:
    Asterisk → WS /ari/audio → FastAPIWebsocketTransport → Pipecat pipeline
"""

import asyncio
import json
from typing import Optional

from fastapi import APIRouter, WebSocket, WebSocketDisconnect
from loguru import logger

from app.schemas.agent import AgentConfig
from app.services.agent_resolution_service import agent_resolution_service
from app.services.session_manager import session_manager
from app.services.task_manager import task_manager

router = APIRouter(prefix="/sip", tags=["SIP"])


# ============================================================================
# Health + stub call-management endpoints
# ============================================================================


@router.get("/health")
async def sip_health():
    """SIP bridge health check."""
    return {"status": "ok"}


@router.get("/calls/{call_id}")
async def get_call(call_id: str):
    """Get call status (placeholder — real state lives in Redis/ARI)."""
    return {"call_id": call_id, "status": "unknown"}


@router.post("/calls/{call_id}/hangup")
async def hangup_call(call_id: str):
    """Hang up a call (placeholder — use ARI for real control)."""
    return {"call_id": call_id, "status": "hungup"}


# ============================================================================
# ARI ExternalMedia WebSocket Endpoint
# ============================================================================


@router.websocket("/ari/audio")
@router.websocket("/ari/audio/{audio_key:path}")
async def ari_audio_websocket(ws: WebSocket, audio_key: str = None):
    """WebSocket endpoint for Asterisk ARI ExternalMedia audio.

    Flow:
        1. Asterisk sends MEDIA_START event with channel_id
        2. We look up call metadata (agent_id, tenant_id, caller_id, trunk_id)
           in Redis at `ari:pending_audio:{channel_id}` — stored there by
           TitoARIManager before creating the ExternalMedia channel.
        3. Start a Pipecat pipeline for bidirectional slin 8kHz audio.
    """
    logger.info(f"[ARI WS] Connection request | audio_key={audio_key}")
    await ws.accept()

    # ── 1. Wait for MEDIA_START ──────────────────────────────────────────────
    channel_id: Optional[str] = None
    try:
        first_msg = await asyncio.wait_for(ws.receive_text(), timeout=5.0)
        first_data = json.loads(first_msg)
        if first_data.get("event") == "MEDIA_START":
            channel_id = first_data.get("channel_id")
            logger.info(f"[ARI WS] MEDIA_START | channel={channel_id}")
        else:
            logger.warning(f"[ARI WS] Unexpected first message: {first_data}")
    except asyncio.TimeoutError:
        logger.warning("[ARI WS] No MEDIA_START, closing")
        await ws.close(code=4002)
        return
    except Exception as e:
        logger.error(f"[ARI WS] Failed to parse first message: {e}")
        await ws.close(code=4002)
        return

    if not channel_id:
        await ws.close(code=4002)
        return

    # ── 2. Resolve call metadata from Redis ──────────────────────────────────
    agent_id: Optional[str] = None
    tenant_id = "central"
    caller_id: Optional[str] = None
    trunk_id: Optional[str] = None

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
                await redis.delete(pending_key)
                logger.info(
                    f"[ARI WS] Resolved | channel={channel_id} agent={agent_id} tenant={tenant_id}"
                )
            else:
                logger.warning(f"[ARI WS] No metadata for channel={channel_id}")
    except Exception as e:
        logger.error(f"[ARI WS] Redis lookup failed: {e}")

    if not agent_id:
        await ws.send_text(
            json.dumps({"type": "error", "message": f"Missing agent_id for channel={channel_id}"})
        )
        await ws.close(code=4002)
        return

    # ── 3. Resolve agent config and run pipeline ─────────────────────────────
    session_id = f"ari_{channel_id}"
    try:
        agent_config = await agent_resolution_service.resolve_agent(
            agent_id=agent_id, tenant_id=tenant_id
        )
        if not agent_config:
            logger.error(f"[ARI WS] Agent not found: {agent_id}")
            await ws.send_text(
                json.dumps({"type": "error", "message": f"Agent {agent_id} not found"})
            )
            await ws.close(code=4001)
            return

        await session_manager.save_session(
            session_id=session_id,
            config=agent_config,
            room_name=channel_id,
            provider="ari",
        )
        await task_manager.add(session_id, asyncio.current_task())

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
        except Exception:
            pass
    finally:
        await task_manager.remove(session_id)
        await session_manager.delete_session(session_id)
        try:
            await ws.close()
        except Exception:
            pass


async def _run_ari_pipeline(
    ws: WebSocket,
    session_id: str,
    agent_config: AgentConfig,
    channel_id: str,
    caller_id: Optional[str],
    trunk_id: Optional[str],
):
    """Run Pipecat pipeline for an ARI ExternalMedia call (slin 8kHz)."""
    import pipecat
    from pipecat.audio.vad.silero import SileroVADAnalyzer
    from pipecat.audio.vad.vad_analyzer import VADParams
    from pipecat.frames.frames import (
        InputAudioRawFrame,
        LLMRunFrame,
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

    # Asterisk 'slin' format = signed linear 16-bit PCM at 8kHz
    sample_rate = 8000

    vad_analyzer = SileroVADAnalyzer(
        params=VADParams(confidence=0.7, start_secs=0.4, stop_secs=0.2, min_volume=0.6)
    )

    class SlinPCMSerializer(FrameSerializer):
        """Signed linear 16-bit PCM serializer (Asterisk slin)."""

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
                    audio=bytes(data), sample_rate=self._sr, num_channels=1
                )
            return None

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

    stt = ServiceFactory.create_stt_service(agent_config)
    llm = ServiceFactory.create_llm_service(agent_config)
    tts = ServiceFactory.create_tts_service(agent_config)

    llm_context, context_aggregator = setup_context(session_id, agent_config, vad_analyzer)

    if caller_id:
        llm_context.add_message(
            {"role": "system", "content": f"Caller phone number: {caller_id}"}
        )

    pipeline, _ = build_pipeline(
        transport=transport,
        stt=stt,
        llm=llm,
        tts=tts,
        context_aggregator=context_aggregator,
        config=agent_config,
    )

    task = PipelineTask(pipeline, params=PipelineParams(allow_interruptions_by_all=True))

    @transport.event_handler("on_client_connected")
    async def on_connected(transport, ws):
        logger.info(f"[ARI WS] Pipeline client connected | session={session_id}")
        await ws.send_text(json.dumps({"type": "started", "session_id": session_id}))
        if agent_config.behavior.initial_action == "SPEAK_FIRST":
            await task.queue_frames([LLMRunFrame()])

    @transport.event_handler("on_client_disconnected")
    async def on_disconnected(transport, ws):
        logger.info(f"[ARI WS] Pipeline client disconnected | session={session_id}")

    runner = pipecat.pipeline.runner.PipelineRunner()
    await runner.run(task)
    logger.info(f"[ARI WS] Pipeline completed | session={session_id}")
