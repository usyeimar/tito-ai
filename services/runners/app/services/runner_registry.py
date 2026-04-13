"""Runner Registry Service for multi-instance coordination.

This service manages runner heartbeat and registration in Redis,
enabling load balancing and runner discovery from Laravel.
"""

import asyncio
import json
import logging
import time
from typing import Optional

import redis.asyncio as redis

from app.core.config import settings
from app.services.session_manager import session_manager
from app.services.task_manager import task_manager

logger = logging.getLogger(__name__)

RUNNER_KEY_PREFIX = "runner:"
RUNNER_INDEX_KEY = "runner:index"
RUNNER_TTL_SECONDS = 30
HEARTBEAT_INTERVAL_SECONDS = 15


class RunnerRegistryService:
    """Manages runner registration and heartbeat in Redis."""

    def __init__(self):
        self._redis: Optional[redis.Redis] = None
        self._heartbeat_task: Optional[asyncio.Task] = None
        self._running = False

    @property
    def redis(self) -> redis.Redis:
        """Get or create Redis connection."""
        if self._redis is None:
            self._redis = redis.from_url(settings.REDIS_URL, decode_responses=True)
        return self._redis

    async def start(self) -> None:
        """Register this runner and start heartbeat."""
        if self._running:
            logger.warning("Runner registry already running")
            return

        self._running = True

        # Register immediately
        await self._register()

        # Start heartbeat background task
        self._heartbeat_task = asyncio.create_task(self._heartbeat_loop())
        logger.info(
            f"Runner registry started | host_id={settings.HOST_ID} "
            f"advertise_url={settings.RUNNER_ADVERTISE_URL}"
        )

    async def stop(self) -> None:
        """Unregister this runner."""
        self._running = False

        if self._heartbeat_task:
            self._heartbeat_task.cancel()
            try:
                await self._heartbeat_task
            except asyncio.CancelledError:
                pass

        await self._unregister()
        logger.info("Runner registry stopped")

    async def _register(self) -> None:
        """Register this runner in Redis."""
        runner_key = f"{RUNNER_KEY_PREFIX}{settings.HOST_ID}"
        payload = {
            "host_id": settings.HOST_ID,
            "url": settings.RUNNER_ADVERTISE_URL,
            "active_sessions": task_manager.count(),
            "max_sessions": settings.MAX_CONCURRENT_SESSIONS,
            "sip_enabled": settings.SIP_ENABLED,
            "last_heartbeat": time.time(),
        }

        try:
            pipe = self.redis.pipeline()
            pipe.setex(runner_key, RUNNER_TTL_SECONDS, json.dumps(payload))
            pipe.sadd(RUNNER_INDEX_KEY, settings.HOST_ID)
            await pipe.execute()
        except Exception as e:
            logger.error(f"Failed to register runner: {e}")

    async def _unregister(self) -> None:
        """Remove this runner from Redis."""
        runner_key = f"{RUNNER_KEY_PREFIX}{settings.HOST_ID}"
        try:
            pipe = self.redis.pipeline()
            pipe.delete(runner_key)
            pipe.srem(RUNNER_INDEX_KEY, settings.HOST_ID)
            await pipe.execute()
        except Exception as e:
            logger.error(f"Failed to unregister runner: {e}")

    async def _heartbeat_loop(self) -> None:
        """Background task to refresh runner TTL."""
        while self._running:
            try:
                await asyncio.sleep(HEARTBEAT_INTERVAL_SECONDS)
                await self._refresh()
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error(f"Heartbeat error: {e}")

    async def _refresh(self) -> None:
        """Refresh runner TTL and update session count."""
        runner_key = f"{RUNNER_KEY_PREFIX}{settings.HOST_ID}"
        payload = {
            "host_id": settings.HOST_ID,
            "url": settings.RUNNER_ADVERTISE_URL,
            "active_sessions": task_manager.count(),
            "max_sessions": settings.MAX_CONCURRENT_SESSIONS,
            "sip_enabled": settings.SIP_ENABLED,
            "last_heartbeat": time.time(),
        }

        try:
            pipe = self.redis.pipeline()
            pipe.setex(runner_key, RUNNER_TTL_SECONDS, json.dumps(payload))
            pipe.sadd(RUNNER_INDEX_KEY, settings.HOST_ID)  # Ensure still in index
            await pipe.execute()
            logger.debug(
                f"Heartbeat | host_id={settings.HOST_ID} "
                f"sessions={payload['active_sessions']}"
            )
        except Exception as e:
            logger.error(f"Failed to refresh runner heartbeat: {e}")

    async def get_all_runners(self) -> list[dict]:
        """Get all registered runners."""
        try:
            runner_ids = await self.redis.smembers(RUNNER_INDEX_KEY)
            if not runner_ids:
                return []

            runners = []
            pipe = self.redis.pipeline()
            for rid in runner_ids:
                pipe.get(f"{RUNNER_KEY_PREFIX}{rid}")
            results = await pipe.execute()

            for i, rid in enumerate(runner_ids):
                raw = results[i]
                if raw:
                    try:
                        runners.append(json.loads(raw))
                    except json.JSONDecodeError:
                        pass

            return runners
        except Exception as e:
            logger.error(f"Failed to get runners: {e}")
            return []

    async def get_runner(self, host_id: str) -> Optional[dict]:
        """Get a specific runner by host_id."""
        try:
            raw = await self.redis.get(f"{RUNNER_KEY_PREFIX}{host_id}")
            if raw:
                return json.loads(raw)
            return None
        except Exception as e:
            logger.error(f"Failed to get runner {host_id}: {e}")
            return None

    async def get_available_runner(self) -> Optional[dict]:
        """Get the runner with the lowest load (fewest active sessions)."""
        runners = await self.get_all_runners()
        if not runners:
            return None

        available = [
            r
            for r in runners
            if r.get("active_sessions", 0) < r.get("max_sessions", 10)
        ]
        if not available:
            return None

        return min(available, key=lambda r: r.get("active_sessions", 0))


# Singleton instance
runner_registry_service = RunnerRegistryService()
