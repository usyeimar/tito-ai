import os
from arq import create_pool
from arq.connections import RedisSettings
import logging

logger = logging.getLogger(__name__)

# This is a placeholder worker configuration
# The actual worker functionality is handled by the ARQ worker in the API service
# For Celery compatibility, we'll create a minimal app

try:
    from celery import Celery

    # Create Celery app
    app = Celery("pipecat-runner")

    # Configure Celery using environment variables
    redis_host = os.getenv("REDIS_HOST", "redis")
    redis_port = os.getenv("REDIS_PORT", "6379")
    redis_password = os.getenv("REDIS_PASSWORD", "")
    redis_db = os.getenv("CELERY_REDIS_DB", "0")

    if redis_password:
        broker_url = f"redis://:{redis_password}@{redis_host}:{redis_port}/{redis_db}"
    else:
        broker_url = f"redis://{redis_host}:{redis_port}/{redis_db}"

    app.conf.update(
        broker_url=broker_url,
        result_backend=broker_url,
        task_serializer="json",
        accept_content=["json"],
        result_serializer="json",
        timezone="UTC",
        enable_utc=True,
    )

    logger.info(f"Celery app created successfully with broker: {broker_url}")

except Exception as e:
    logger.warning(f"Could not create Celery app: {e}")

    # Create a dummy app for compatibility
    class DummyApp:
        def task(self, *args, **kwargs):
            def decorator(func):
                return func

            return decorator

    app = DummyApp()
    logger.info("Using dummy Celery app")

if __name__ == "__main__":
    app.start()
