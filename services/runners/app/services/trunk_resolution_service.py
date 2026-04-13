"""Trunk resolution service with Redis cache and Laravel API fallback.

This service resolves trunk configurations for incoming SIP calls,
first checking Redis cache, then falling back to Laravel backend API if needed.
"""

import json
import logging
import os
from typing import Optional

import httpx
from app.services.session_manager import session_manager

logger = logging.getLogger(__name__)


class TrunkResolutionService:
    """Service for resolving trunk configurations with caching and fallback."""

    REDIS_KEY_PREFIX = "trunk:"
    DEFAULT_TTL_SECONDS = 86400  # 24 hours

    def __init__(self):
        self._redis = session_manager._redis
        self._backend_url = os.getenv("BACKEND_URL", "http://localhost:8000")
        self._api_key = os.getenv("BACKEND_API_KEY")

    async def resolve_trunk(
        self,
        trunk_id: str,
        force_refresh: bool = False,
    ) -> Optional[dict]:
        """Resolve trunk configuration with caching and API fallback.

        Resolution strategy:
        1. Check Redis cache (if not force_refresh)
        2. If not in cache, call Laravel backend API
        3. Cache the result from API
        4. Return None if both cache and API fail

        Args:
            trunk_id: The unique trunk identifier
            force_refresh: Skip cache and fetch from API

        Returns:
            Trunk data dict if found, None otherwise
        """
        cache_key = f"{self.REDIS_KEY_PREFIX}{trunk_id}"

        # 1. Try Redis cache (unless force refresh)
        if not force_refresh:
            cached = await self._get_from_cache(cache_key)
            if cached:
                logger.debug(f"Trunk resolved from cache | trunk_id={trunk_id}")
                return cached

        # 2. Fallback to Laravel API
        trunk_data = await self._fetch_from_api(trunk_id)
        if trunk_data:
            # 3. Cache the result
            await self._cache_trunk(cache_key, trunk_data)
            logger.info(f"Trunk resolved from API and cached | trunk_id={trunk_id}")
            return trunk_data

        logger.warning(f"Trunk not found | trunk_id={trunk_id}")
        return None

    async def resolve_trunk_by_extension(
        self,
        workspace_slug: str,
        extension: str,
    ) -> Optional[dict]:
        """Resolve trunk and agent by extension pattern.

        Iterates through trunks in the workspace, checking routes for a match.

        Args:
            workspace_slug: The workspace identifier
            extension: The dialed extension

        Returns:
            Dict with trunk_id, agent_id, trunk_data if found, None otherwise
        """
        trunk_ids = await self._redis.smembers(f"trunk:index:{workspace_slug}")

        for tid in trunk_ids:
            trunk_data = await self.resolve_trunk(tid)
            if not trunk_data:
                continue

            if trunk_data.get("mode") != "inbound":
                continue
            if trunk_data.get("status") != "active":
                continue

            routes = trunk_data.get("routes", [])
            for route in routes:
                if not route.get("enabled", True):
                    continue

                pattern = route.get("pattern", "")

                # Exact match
                if pattern == extension:
                    return {
                        "trunk_id": tid,
                        "agent_id": route.get("agent_id"),
                        "trunk_data": trunk_data,
                    }

                # Wildcard match
                if pattern == "*":
                    return {
                        "trunk_id": tid,
                        "agent_id": route.get("agent_id"),
                        "trunk_data": trunk_data,
                    }

                # Dialplan-style pattern matching (e.g., _X., _XX.)
                if pattern.startswith("_"):
                    if self._pattern_matches(pattern, extension):
                        return {
                            "trunk_id": tid,
                            "agent_id": route.get("agent_id"),
                            "trunk_data": trunk_data,
                        }

        return None

    def _pattern_matches(self, pattern: str, extension: str) -> bool:
        """Check if a dialplan-style pattern matches an extension.

        Supports:
            _X.  - Any digits
            _Z.  - Any digits 2-9
            _N.  - Any digits 2-9
            _.   - Any character
            _X   - Single digit (at end)
        """
        if not pattern.startswith("_") or not extension:
            return pattern == extension

        # Convert pattern to regex
        regex_pattern = pattern[1:]  # Remove leading underscore
        regex_pattern = regex_pattern.replace("X", "[0-9]")
        regex_pattern = regex_pattern.replace("Z", "[2-9]")
        regex_pattern = regex_pattern.replace("N", "[2-9]")
        regex_pattern = regex_pattern.replace(".", ".")

        try:
            return bool(__import__("re").match(f"^{regex_pattern}$", extension))
        except Exception:
            return False

    async def _get_from_cache(self, cache_key: str) -> Optional[dict]:
        """Get trunk config from Redis cache."""
        try:
            raw = await self._redis.get(cache_key)
            if not raw:
                return None

            data = json.loads(raw)
            return data
        except Exception as e:
            logger.warning(f"Failed to parse cached trunk config: {e}")
            return None

    async def _fetch_from_api(self, trunk_id: str) -> Optional[dict]:
        """Fetch trunk config from Laravel backend API."""
        try:
            headers = {"Accept": "application/json"}
            if self._api_key:
                headers["Authorization"] = f"Bearer {self._api_key}"

            url = f"{self._backend_url}/api/v1/ai/trunks/{trunk_id}"

            async with httpx.AsyncClient(timeout=10.0) as client:
                response = await client.get(url, headers=headers)

                if response.status_code == 200:
                    data = response.json()
                    return data.get("data")

                if response.status_code == 404:
                    logger.warning(f"Trunk not found in API | trunk_id={trunk_id}")
                    return None

                logger.error(
                    f"API error fetching trunk config | "
                    f"trunk_id={trunk_id} status={response.status_code}"
                )
                return None

        except httpx.TimeoutException:
            logger.error(f"Timeout fetching trunk config | trunk_id={trunk_id}")
            return None
        except Exception as e:
            logger.error(f"Error fetching trunk config from API: {e}")
            return None

    async def _cache_trunk(self, cache_key: str, trunk_data: dict) -> None:
        """Cache trunk config to Redis."""
        try:
            await self._redis.setex(
                cache_key,
                self.DEFAULT_TTL_SECONDS,
                json.dumps(trunk_data),
            )
        except Exception as e:
            logger.warning(f"Failed to cache trunk config: {e}")

    async def invalidate_cache(self, trunk_id: str) -> None:
        """Invalidate cached trunk config."""
        cache_key = f"{self.REDIS_KEY_PREFIX}{trunk_id}"
        try:
            await self._redis.delete(cache_key)
            logger.info(f"Trunk cache invalidated | trunk_id={trunk_id}")
        except Exception as e:
            logger.warning(f"Failed to invalidate trunk cache: {e}")

    async def touch(self, trunk_id: str) -> None:
        """Refresh TTL for cached trunk config."""
        cache_key = f"{self.REDIS_KEY_PREFIX}{trunk_id}"
        try:
            await self._redis.expire(cache_key, self.DEFAULT_TTL_SECONDS)
        except Exception as e:
            logger.warning(f"Failed to touch trunk cache: {e}")


# Singleton instance
trunk_resolution_service = TrunkResolutionService()
