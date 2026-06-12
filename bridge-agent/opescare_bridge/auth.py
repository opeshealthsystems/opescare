"""
HMAC-SHA256 request authentication for the OpesCare Bridge Agent.

The server validates requests via the VerifyBridgeAgent middleware which:
  1. Reads X-Bridge-Agent-Key header
  2. SHA-256 hashes the incoming key
  3. Looks up the agent by key hash
  4. Verifies agent.status == 'active'
"""
import hashlib
import hmac
import time
from typing import Dict


def build_auth_headers(agent_id: str, agent_key: str) -> Dict[str, str]:
    """
    Build authentication headers for a Bridge Agent API request.

    The server stores SHA-256(agent_key) — we send the plain key in the header
    and the middleware hashes it for comparison.
    """
    return {
        "X-Bridge-Agent-ID":  agent_id,
        "X-Bridge-Agent-Key": agent_key,
        "X-Bridge-Timestamp": str(int(time.time())),
    }


def sign_payload(payload: bytes, secret: str) -> str:
    """
    HMAC-SHA256 signature for a request payload.
    Used for optional payload integrity verification.
    """
    return hmac.new(secret.encode(), payload, hashlib.sha256).hexdigest()
