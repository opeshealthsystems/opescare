from __future__ import annotations

import hashlib
import hmac
import json
import time
from typing import Any

from ..exceptions import WebhookSignatureError
from ..http.api_client import ApiClient

_TIMESTAMP_TOLERANCE = 300  # 5 minutes


class Webhooks:
    """Webhook subscription management and incoming payload verification."""

    def __init__(self, client: ApiClient) -> None:
        self._client = client

    # ── Subscription management ───────────────────────────────────────────

    def subscribe(
        self,
        callback_url: str,
        events: list[str],
        description: str = "",
    ) -> dict:
        """
        Create a webhook subscription.
        Store the returned webhook_secret immediately — it is shown once only.
        """
        return self._client.post("api/v1/connect/webhooks/subscriptions", {
            "callback_url":      callback_url,
            "subscribed_events": events,
            "description":       description,
        })

    def replay(self, event_id: str) -> dict:
        """Manually replay a persisted webhook event to all matching subscriptions."""
        return self._client.post(f"api/v1/connect/webhooks/events/{event_id}/replay")

    # ── Signature verification ─────────────────────────────────────────────

    def verify_signature(
        self,
        raw_payload: str | bytes,
        signature_header: str,
        secret: str,
    ) -> None:
        """
        Verify the HMAC-SHA256 signature of an incoming webhook delivery.

        Call this BEFORE processing any webhook payload.

        Args:
            raw_payload:       Raw request body — do NOT json.loads() before calling.
            signature_header:  Value of X-OpesCare-Signature header ("t=...,v1=...").
            secret:            Your webhook_secret (whsec_...).

        Raises:
            WebhookSignatureError: If signature is invalid, malformed, or a replay.
        """
        parts: dict[str, str] = {}
        for segment in signature_header.split(","):
            if "=" in segment:
                k, v = segment.split("=", 1)
                parts[k.strip()] = v.strip()

        if "t" not in parts or "v1" not in parts:
            raise WebhookSignatureError(
                "Webhook signature header is malformed or missing t/v1 fields."
            )

        timestamp = int(parts["t"])
        received  = parts["v1"]

        age = abs(int(time.time()) - timestamp)
        if age > _TIMESTAMP_TOLERANCE:
            raise WebhookSignatureError(
                f"Webhook timestamp is {age}s old (tolerance: {_TIMESTAMP_TOLERANCE}s). "
                "Possible replay attack."
            )

        if isinstance(raw_payload, str):
            raw_payload = raw_payload.encode()

        signed_payload = f"{timestamp}.".encode() + raw_payload
        expected = hmac.new(secret.encode(), signed_payload, hashlib.sha256).hexdigest()

        # Constant-time comparison to prevent timing attacks
        if not hmac.compare_digest(expected, received):
            raise WebhookSignatureError(
                "Webhook HMAC signature does not match. Payload may have been tampered."
            )

    def parse_event(self, raw_payload: str | bytes) -> dict[str, Any]:
        """
        Parse a verified webhook payload into an event dict.
        Always call verify_signature() before this method.

        Raises:
            ValueError: If payload is not valid JSON or missing 'type' field.
        """
        if isinstance(raw_payload, bytes):
            raw_payload = raw_payload.decode()

        try:
            event = json.loads(raw_payload)
        except json.JSONDecodeError as exc:
            raise ValueError(f"Webhook payload is not valid JSON: {exc}") from exc

        if not isinstance(event, dict) or "type" not in event:
            raise ValueError("Webhook payload missing required 'type' field.")

        return event
