"""Agent resolution service with Redis cache and Laravel API fallback.

This service resolves agent configurations for incoming SIP calls,
first checking Redis cache, then falling back to Laravel backend API if needed.
"""

import json
import logging
import os
from typing import Optional

import httpx
from app.schemas.agent import AgentConfig
from app.services.session_manager import session_manager

logger = logging.getLogger(__name__)


class AgentResolutionService:
    """Service for resolving agent configurations with caching and fallback."""

    REDIS_KEY_PREFIX = "agent_config:"
    DEFAULT_TTL_SECONDS = 86400  # 24 hours

    def __init__(self):
        self._redis = session_manager._redis
        self._backend_url = os.getenv("BACKEND_URL", "http://localhost:8000")
        self._api_key = os.getenv("BACKEND_API_KEY")

    async def resolve_agent(
        self,
        agent_id: str,
        tenant_id: Optional[str] = None,
        force_refresh: bool = False,
    ) -> Optional[AgentConfig]:
        """Resolve agent configuration with caching and API fallback.

        Resolution strategy:
        1. Check Redis cache (if not force_refresh)
        2. If not in cache, call Laravel backend API
        3. Cache the result from API
        4. Return None if both cache and API fail

        Args:
            agent_id: The unique agent identifier
            tenant_id: Optional tenant for multi-tenant lookups
            force_refresh: Skip cache and fetch from API

        Returns:
            AgentConfig if found, None otherwise
        """
        cache_key = f"{self.REDIS_KEY_PREFIX}{agent_id}"

        # 1. Try Redis cache (unless force refresh)
        if not force_refresh:
            cached = await self._get_from_cache(cache_key)
            if cached:
                logger.debug(f"Agent resolved from cache | agent_id={agent_id}")
                return cached

        # 2. Fallback to Laravel API
        agent_config = await self._fetch_from_api(agent_id, tenant_id)
        if agent_config:
            # 3. Cache the result
            await self._cache_agent(cache_key, agent_config)
            logger.info(f"Agent resolved from API and cached | agent_id={agent_id}")
            return agent_config

        logger.warning(f"Agent not found | agent_id={agent_id}")
        return None

    async def resolve_agent_by_slug(
        self,
        slug: str,
        tenant_id: str,
        workspace_slug: Optional[str] = None,
    ) -> Optional[AgentConfig]:
        """Resolve agent by slug using Laravel API.

        Args:
            slug: The agent slug (URL-friendly identifier)
            tenant_id: The tenant identifier
            workspace_slug: Optional workspace for lookup

        Returns:
            AgentConfig if found, None otherwise
        """
        # Try to get agent_id from Redis slug mapping first
        agent_id = await self._redis.hget(f"agent:slugs:{tenant_id}", slug)

        if agent_id:
            return await self.resolve_agent(agent_id, tenant_id)

        # Fallback to API lookup by slug
        agent_config = await self._fetch_from_api_by_slug(
            slug, tenant_id, workspace_slug
        )
        if agent_config:
            # Cache the result
            cache_key = f"{self.REDIS_KEY_PREFIX}{agent_config.agent_id}"
            await self._cache_agent(cache_key, agent_config)
            return agent_config

        return None

    async def _get_from_cache(self, cache_key: str) -> Optional[AgentConfig]:
        """Get agent config from Redis cache."""
        try:
            raw = await self._redis.get(cache_key)
            if not raw:
                return None

            data = json.loads(raw)

            # Handle wrapped format from Laravel sync
            if "config" in data:
                config_data = data["config"]
            else:
                config_data = data

            return AgentConfig(**config_data)
        except Exception as e:
            logger.warning(f"Failed to parse cached agent config: {e}")
            return None

    async def _fetch_from_api(
        self, agent_id: str, tenant_id: Optional[str] = None
    ) -> Optional[AgentConfig]:
        """Fetch agent config from Laravel backend API."""
        try:
            headers = {"Accept": "application/json"}
            if self._api_key:
                headers["Authorization"] = f"Bearer {self._api_key}"

            # Build URL with tenant subdomain (e.g., tenant.app.test)
            base_url = self._backend_url
            if tenant_id and tenant_id != "central":
                # Parse URL and inject tenant as subdomain
                from urllib.parse import urlparse, urlunparse

                parsed = urlparse(base_url)
                # tenant.app.localhost:8000 -> tenant.localhost:8000
                new_netloc = f"{tenant_id}.{parsed.netloc}"
                base_url = urlunparse(parsed._replace(netloc=new_netloc))

            url = f"{base_url}/api/agents/{agent_id}/config"

            async with httpx.AsyncClient(timeout=10.0) as client:
                response = await client.get(url, headers=headers)

                if response.status_code == 200:
                    data = response.json()
                    return AgentConfig(**data)

                if response.status_code == 404:
                    logger.warning(f"Agent not found in API | agent_id={agent_id}")
                    return None

                logger.error(
                    f"API error fetching agent config | "
                    f"agent_id={agent_id} status={response.status_code}"
                )
                return None

        except httpx.TimeoutException:
            logger.error(f"Timeout fetching agent config | agent_id={agent_id}")
            return None
        except Exception as e:
            logger.error(f"Error fetching agent config from API: {e}")
            return None

    async def _fetch_from_api_by_slug(
        self,
        slug: str,
        tenant_id: str,
        workspace_slug: Optional[str] = None,
    ) -> Optional[AgentConfig]:
        """Fetch agent config from Laravel API by slug."""
        try:
            headers = {"Accept": "application/json"}
            if self._api_key:
                headers["Authorization"] = f"Bearer {self._api_key}"

            # Build URL with tenant subdomain
            base_url = self._backend_url
            if tenant_id and tenant_id != "central":
                from urllib.parse import urlparse, urlunparse

                parsed = urlparse(base_url)
                new_netloc = f"{tenant_id}.{parsed.netloc}"
                base_url = urlunparse(parsed._replace(netloc=new_netloc))

            # Use the dedicated agent config by slug endpoint
            url = f"{base_url}/api/agents/by-slug/{slug}/config"

            async with httpx.AsyncClient(timeout=10.0) as client:
                response = await client.get(url, headers=headers)

                if response.status_code == 200:
                    data = response.json()
                    return AgentConfig(**data)

                return None

        except Exception as e:
            logger.error(f"Error fetching agent by slug from API: {e}")
            return None

    async def _cache_agent(self, cache_key: str, config: AgentConfig) -> None:
        """Cache agent config to Redis."""
        try:
            payload = {
                "agent_id": config.agent_id,
                "tenant_id": config.tenant_id,
                "slug": config.metadata.slug,
                "config": json.loads(config.model_dump_json()),
                "synced_at": None,  # Will be set by caller
                "version": config.version,
            }

            await self._redis.setex(
                cache_key,
                self.DEFAULT_TTL_SECONDS,
                json.dumps(payload),
            )
        except Exception as e:
            logger.warning(f"Failed to cache agent config: {e}")

    async def invalidate_cache(self, agent_id: str) -> None:
        """Invalidate cached agent config."""
        cache_key = f"{self.REDIS_KEY_PREFIX}{agent_id}"
        try:
            await self._redis.delete(cache_key)
            logger.info(f"Agent cache invalidated | agent_id={agent_id}")
        except Exception as e:
            logger.warning(f"Failed to invalidate agent cache: {e}")

    async def touch(self, agent_id: str) -> None:
        """Refresh TTL for cached agent config."""
        cache_key = f"{self.REDIS_KEY_PREFIX}{agent_id}"
        try:
            await self._redis.expire(cache_key, self.DEFAULT_TTL_SECONDS)
        except Exception as e:
            logger.warning(f"Failed to touch agent cache: {e}")


# Singleton instance
agent_resolution_service = AgentResolutionService()
