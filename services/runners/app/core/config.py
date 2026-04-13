import os
import uuid
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    PROJECT_NAME: str = "Tito AI Runners"
    LIVEKIT_URL: str = ""
    LIVEKIT_API_KEY: str = ""
    LIVEKIT_API_SECRET: str = ""
    DAILY_API_KEY: str = ""
    DAILY_API_URL: str = "https://api.daily.co/v1"

    DEFAULT_TRANSPORT_PROVIDER: str = "daily"

    # Integración con Laravel
    BACKEND_URL: str = "http://localhost:8000"
    BACKEND_API_KEY: str = ""

    OPENAI_API_KEY: str = ""
    REDIS_URL: str = "redis://localhost:6379/0"

    # Runner Identification & Scaling
    HOST_ID: str = os.getenv("POD_NAME", f"runner-{uuid.uuid4().hex[:8]}")
    RUNNER_ADVERTISE_URL: str = os.getenv("RUNNER_ADVERTISE_URL", "")
    MAX_CONCURRENT_SESSIONS: int = int(os.getenv("MAX_CONCURRENT_SESSIONS", "10"))

    # SIP Bridge — Asterisk AMI
    ASTERISK_AMI_HOST: str = "localhost"
    ASTERISK_AMI_PORT: int = 5038
    ASTERISK_AMI_USER: str = "tito"
    ASTERISK_AMI_SECRET: str = ""

    # SIP Bridge — AudioSocket server
    SIP_AUDIOSOCKET_HOST: str = "0.0.0.0"
    SIP_AUDIOSOCKET_PORT: int = 9092

    # SIP Bridge — enable/disable
    SIP_ENABLED: bool = False

    # SIP Transport mode: "audiosocket", "websocket", or "ari"
    # - audiosocket: Direct TCP via app_audiosocket (simplest, default)
    # - websocket: Asterisk chan_websocket (Asterisk 20.18+)
    # - ari: Full call control via ARI + Stasis (most flexible)
    # All three servers start regardless; this controls the Asterisk dialplan context.
    SIP_TRANSPORT: str = "audiosocket"

    # SIP Bridge — ARI (Asterisk REST Interface)
    ASTERISK_ARI_HOST: str = "asterisk"
    ASTERISK_ARI_PORT: int = 8088
    ASTERISK_ARI_USER: str = "tito-ai"
    ASTERISK_ARI_PASSWORD: str = "tito-ari-secret"
    ASTERISK_ARI_APP: str = "tito-ai"

    model_config = SettingsConfigDict(env_file=".env", extra="ignore")


settings = Settings()
