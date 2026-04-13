#!/usr/bin/env python3
"""
Script para ejecutar el Tito ARI Manager como proceso standalone.

Uso:
    python run_ari_manager.py

Variables de entorno:
    REDIS_URL=redis://localhost:6379/0
    ARI_HOST=asterisk
    ARI_PORT=8088
    ARI_USERNAME=tito-ai
    ARI_PASSWORD=tito-ari-secret
"""

import asyncio
import sys
import os

# Add app to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Load env
from dotenv import load_dotenv

load_dotenv()

# Configure logging
import logging

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)-8s | %(name)s:%(lineno)d - %(message)s",
    handlers=[logging.StreamHandler(sys.stdout)],
)


async def main():
    """Run ARI Manager standalone."""
    from app.services.sip.tito_ari_manager import TitoARIManager
    from app.services.session_manager import session_manager

    # Connect to Redis first
    logger = logging.getLogger(__name__)
    logger.info("Connecting to Redis...")
    await session_manager.connect()
    logger.info("✓ Redis connected")

    # Start ARI Manager
    manager = TitoARIManager()

    try:
        await manager.start()
    except KeyboardInterrupt:
        logger.info("Received shutdown signal")
    finally:
        await manager.stop()
        await session_manager.disconnect()
        logger.info("Shutdown complete")


if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\nShutdown by user")
        sys.exit(0)
