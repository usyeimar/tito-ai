"""ARI WebSocket endpoint for Asterisk ExternalMedia connections."""

import asyncio
import json
import logging
from typing import Optional

from fastapi import APIRouter, Query, WebSocket, WebSocketDisconnect

from app.schemas.agent import AgentConfig
from app.services.agent_resolution_service import agent_resolution_service
from app.services.session_manager import session_manager
from app.services.task_manager import task_manager
from app.services.webhook_service import WebhookService

logger = logging.getLogger(__name__)

router = APIRouter()


@router.websocket("/ari/audio")
async def ari_audio_websocket(
    ws: WebSocket,
    channel_id: str = Query(..., description="Asterisk channel ID"),
    agent_id: str = Query(..., description="Agent ID to handle the call"),
    tenant_id: str = Query("central", description="Tenant ID"),
    caller_id: str = Query(None, description="Caller phone number"),
    trunk_id: str = Query(None, description="Trunk ID"),
):
    """WebSocket endpoint for Asterisk ARI ExternalMedia audio.

    This endpoint receives WebSocket connections from Asterisk ExternalMedia channels.
    It creates a Pipecat pipeline with the specified agent to handle the call.

    Query Parameters:
    - channel_id: Asterisk channel ID (required)
    - agent_id: Agent ID to handle the call (required)
    - tenant_id: Tenant ID (default: central)
    - caller_id: Caller phone number (optional)
    - trunk_id: Trunk ID (optional)
    """
    logger.info(
        f"[ARI WS] Connection request | channel={channel_id} agent={agent_id} "
        f"tenant={tenant_id} caller={caller_id}"
    )

    await ws.accept()

    # Create session
    session_id = f"ari_{channel_id}"

    try:
        # 1. Resolve agent configuration
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
        stt=stst,
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
