"""
Example integration of TitoARIManager into main.py lifespan.

Add this to your app/main.py or similar lifespan handler.
"""

from contextlib import asynccontextmanager
from fastapi import FastAPI
from loguru import logger


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application lifespan handler."""

    # ========================================================================
    # STARTUP
    # ========================================================================
    logger.info("Starting Tito services...")

    # Start Session Manager (Redis)
    from app.services.session_manager import session_manager

    await session_manager.connect()
    logger.info("✓ Session Manager connected")

    # Start ARI Manager (replaces AudioSocket)
    from app.services.sip.tito_ari_manager import TitoARIManager

    ari_manager = TitoARIManager()
    await ari_manager.start()
    logger.info("✓ ARI Manager started")

    # Store in app state for access in endpoints
    app.state.ari_manager = ari_manager

    # Optionally: Start ARI Call Handler (if you want to keep it for compatibility)
    # from app.services.sip.ari_call_handler import ARICallHandler
    # from app.services.sip.ari_client import ARIClient
    # ari_client = ARIClient()
    # await ari_client.connect()
    # ari_call_handler = ARICallHandler(ari_client)
    # await ari_call_handler.start()
    # app.state.ari_call_handler = ari_call_handler

    logger.info("🚀 Tito services started successfully")

    yield

    # ========================================================================
    # SHUTDOWN
    # ========================================================================
    logger.info("Shutting down Tito services...")

    # Stop ARI Manager
    if hasattr(app.state, "ari_manager"):
        await app.state.ari_manager.stop()
        logger.info("✓ ARI Manager stopped")

    # Stop Session Manager
    await session_manager.disconnect()
    logger.info("✓ Session Manager disconnected")

    logger.info("👋 Tito services shut down complete")


# Usage in FastAPI app:
# app = FastAPI(lifespan=lifespan)


# Alternative: Run ARI Manager as separate process
"""
If you prefer to run ARI Manager as a separate process (like Dograh does),
create a new file run_ari_manager.py:

```python
#!/usr/bin/env python3
import asyncio
from app.services.sip.tito_ari_manager import main

if __name__ == "__main__":
    asyncio.run(main())
```

And add to your docker-compose:

```yaml
services:
  ari-manager:
    build: .
    command: python run_ari_manager.py
    environment:
      - REDIS_URL=redis://redis:6379
      - ARI_HOST=asterisk
      - ARI_PORT=8088
    depends_on:
      - redis
      - asterisk
```
"""
