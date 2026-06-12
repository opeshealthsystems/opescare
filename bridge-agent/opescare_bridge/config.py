"""
Bridge Agent configuration loader.

Config is read from bridge_config.json in the working directory.
Sensitive values (agent_key) must never be committed — use environment
variables (OPESCARE_AGENT_KEY) as an override.
"""
import json
import os
from dataclasses import dataclass, field
from pathlib import Path
from typing import Optional


@dataclass
class ConnectorConfig:
    type: str                        # "csv_folder" | "rest_api" | "postgresql" | "mysql"
    watch_folder: Optional[str] = None
    file_pattern: str = "*.csv"
    rest_endpoint: Optional[str] = None
    db_connection_string: Optional[str] = None
    poll_interval_seconds: int = 60


@dataclass
class BridgeConfig:
    server_url: str
    agent_id: str
    agent_key: str                   # SHA-256 hashed in DB; plain here — loaded from env or file
    facility_id: str
    connector: ConnectorConfig
    heartbeat_interval_seconds: int = 60
    sync_interval_seconds: int = 300
    queue_db_path: str = "bridge_queue.db"
    log_level: str = "INFO"
    environment: str = "sandbox"


def load_config(path: str = "bridge_config.json") -> BridgeConfig:
    """Load config from file, with environment variable overrides."""
    config_path = Path(path)

    if not config_path.exists():
        raise FileNotFoundError(
            f"Bridge Agent config not found at '{path}'. "
            "Copy bridge_config.example.json and fill in your credentials."
        )

    with open(config_path) as f:
        raw = json.load(f)

    # Environment variable overrides (for production deployments)
    raw["agent_key"] = os.getenv("OPESCARE_AGENT_KEY", raw.get("agent_key", ""))
    raw["server_url"] = os.getenv("OPESCARE_SERVER_URL", raw.get("server_url", ""))

    connector_raw = raw.pop("connector", {})
    connector = ConnectorConfig(**{
        k: v for k, v in connector_raw.items()
        if k in ConnectorConfig.__dataclass_fields__
    })

    return BridgeConfig(
        **{k: v for k, v in raw.items() if k in BridgeConfig.__dataclass_fields__},
        connector=connector,
    )
