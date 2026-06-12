"""
Heartbeat loop — sends a periodic ping to OpesCare to signal the agent is alive.
OpesCare records last_seen_at on the bridge_agents table.
"""
import logging

import requests

from ..auth import build_auth_headers
from ..config import BridgeConfig

logger = logging.getLogger(__name__)

HEARTBEAT_ENDPOINT = "/api/v1/bridge/heartbeat"


class Heartbeat:
    def __init__(self, config: BridgeConfig):
        self.config  = config
        self.session = requests.Session()
        self.session.headers.update({
            "Content-Type": "application/json",
            "User-Agent":   "opescare-bridge-agent/1.0",
        })

    def ping(self) -> bool:
        """Send a heartbeat. Returns True on success."""
        url     = self.config.server_url.rstrip("/") + HEARTBEAT_ENDPOINT
        headers = build_auth_headers(self.config.agent_id, self.config.agent_key)

        try:
            response = self.session.post(
                url,
                json={
                    "agent_id":    self.config.agent_id,
                    "facility_id": self.config.facility_id,
                    "environment": self.config.environment,
                    "version":     "1.0.0",
                },
                headers=headers,
                timeout=10,
            )

            if response.status_code in (200, 204):
                logger.debug("Heartbeat OK")
                return True

            logger.warning("Heartbeat returned HTTP %d", response.status_code)
            return False

        except requests.exceptions.RequestException as exc:
            logger.warning("Heartbeat failed: %s", exc)
            return False
