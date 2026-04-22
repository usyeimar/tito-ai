"""Tests for the Redis command consumer."""

import asyncio
import json
import pytest
import pytest_asyncio
from unittest.mock import AsyncMock, patch, MagicMock

from app.services.command_consumer import CommandConsumer, COMMANDS_KEY, RESPONSE_KEY_PREFIX


@pytest_asyncio.fixture
async def consumer():
    c = CommandConsumer()
    c._redis = AsyncMock()
    yield c
    c._running = False


@pytest.mark.asyncio
async def test_handle_message_dispatches_create(consumer):
    """session.create command should call _handle_create_session and send response."""
    with patch.object(consumer, "_handle_create_session", new_callable=AsyncMock) as mock_create:
        mock_create.return_value = {
            "session_id": "sess_test123",
            "room_name": "room_test",
            "provider": "livekit",
            "url": "wss://example.com",
            "access_token": "token",
            "context": {},
        }

        message = json.dumps({
            "request_id": "req_001",
            "command": "session.create",
            "payload": {"agent_id": "agent_1", "tenant_id": "tenant_1"},
        })

        await consumer._handle_message(message)

        mock_create.assert_called_once()
        consumer.redis.lpush.assert_called_once()
        key = consumer.redis.lpush.call_args[0][0]
        assert key == f"{RESPONSE_KEY_PREFIX}req_001"


@pytest.mark.asyncio
async def test_handle_message_dispatches_terminate(consumer):
    """session.terminate should call _handle_terminate_session without response."""
    with patch.object(consumer, "_handle_terminate_session", new_callable=AsyncMock) as mock_term:
        message = json.dumps({
            "request_id": "req_002",
            "command": "session.terminate",
            "payload": {"session_id": "sess_kill"},
            "async": True,
        })

        await consumer._handle_message(message)

        mock_term.assert_called_once_with({"session_id": "sess_kill"})
        consumer.redis.lpush.assert_not_called()


@pytest.mark.asyncio
async def test_handle_message_sends_error_on_failure(consumer):
    """Failed commands should send error response."""
    with patch.object(consumer, "_dispatch", new_callable=AsyncMock) as mock_dispatch:
        mock_dispatch.side_effect = RuntimeError("Runner at capacity")

        message = json.dumps({
            "request_id": "req_003",
            "command": "session.create",
            "payload": {},
        })

        await consumer._handle_message(message)

        consumer.redis.lpush.assert_called_once()
        raw_response = consumer.redis.lpush.call_args[0][1]
        response = json.loads(raw_response)
        assert response["error"] == "Runner at capacity"


@pytest.mark.asyncio
async def test_handle_message_ignores_invalid_json(consumer):
    """Invalid JSON should be logged and skipped."""
    await consumer._handle_message("not valid json {{{")
    consumer.redis.lpush.assert_not_called()


@pytest.mark.asyncio
async def test_handle_message_unknown_command(consumer):
    """Unknown commands should send error response."""
    message = json.dumps({
        "request_id": "req_004",
        "command": "unknown.command",
        "payload": {},
    })

    await consumer._handle_message(message)

    consumer.redis.lpush.assert_called_once()
    raw_response = consumer.redis.lpush.call_args[0][1]
    response = json.loads(raw_response)
    assert "Unknown command" in response["error"]


@pytest.mark.asyncio
async def test_listen_keys_includes_host_specific(consumer):
    """Listen keys should include host-specific key first."""
    with patch("app.services.command_consumer.settings") as mock_settings:
        mock_settings.HOST_ID = "runner-abc123"
        keys = consumer._listen_keys()
        assert keys[0] == f"{COMMANDS_KEY}:runner-abc123"
        assert keys[1] == COMMANDS_KEY
