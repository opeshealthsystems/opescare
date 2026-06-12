"""
Tests for Webhooks.verify_signature() and parse_event().
These are stateless and need no running server.
"""
import hashlib
import hmac
import json
import time

import pytest

from opescare.exceptions import WebhookSignatureError
from opescare.modules.webhooks import Webhooks

# Minimal stub — webhook methods don't need a real ApiClient
webhooks = Webhooks(client=None)  # type: ignore[arg-type]
SECRET   = "whsec_test_python_secret_123"


def _make_header(payload: str, secret: str, timestamp: int) -> str:
    signed = f"{timestamp}.{payload}".encode()
    sig    = hmac.new(secret.encode(), signed, hashlib.sha256).hexdigest()
    return f"t={timestamp},v1={sig}"


# ── verify_signature ──────────────────────────────────────────────────────────

def test_valid_signature_does_not_raise():
    payload = '{"type":"lab_result.released","data":{"health_id":"CM-HID-0001"}}'
    ts      = int(time.time())
    header  = _make_header(payload, SECRET, ts)
    webhooks.verify_signature(payload, header, SECRET)  # no exception = pass


def test_wrong_secret_raises():
    payload = '{"type":"lab_result.released"}'
    ts      = int(time.time())
    header  = _make_header(payload, SECRET, ts)
    with pytest.raises(WebhookSignatureError):
        webhooks.verify_signature(payload, header, "wrong_secret")


def test_tampered_payload_raises():
    payload = '{"type":"lab_result.released"}'
    ts      = int(time.time())
    header  = _make_header(payload, SECRET, ts)
    with pytest.raises(WebhookSignatureError):
        webhooks.verify_signature('{"type":"tampered"}', header, SECRET)


def test_old_timestamp_raises_replay_protection():
    payload = '{"type":"prescription.issued"}'
    ts      = int(time.time()) - 400  # 400s ago, beyond 300s tolerance
    header  = _make_header(payload, SECRET, ts)
    with pytest.raises(WebhookSignatureError, match="replay"):
        webhooks.verify_signature(payload, header, SECRET)


def test_malformed_header_raises():
    with pytest.raises(WebhookSignatureError):
        webhooks.verify_signature("{}", "not_a_valid_header", SECRET)


def test_missing_v1_field_raises():
    with pytest.raises(WebhookSignatureError):
        webhooks.verify_signature("{}", f"t={int(time.time())}", SECRET)


def test_bytes_payload_accepted():
    payload = b'{"type":"consent.granted"}'
    ts      = int(time.time())
    header  = _make_header(payload.decode(), SECRET, ts)
    webhooks.verify_signature(payload, header, SECRET)  # should not raise


# ── parse_event ───────────────────────────────────────────────────────────────

def test_parse_event_returns_dict():
    payload = json.dumps({
        "id": "evt_001", "type": "lab_result.released",
        "version": "1.0", "created_at": "2026-06-01T00:00:00Z",
        "data": {"health_id": "CM-HID-0001"}, "meta": {},
    })
    event = webhooks.parse_event(payload)
    assert event["type"] == "lab_result.released"
    assert event["id"]   == "evt_001"


def test_parse_event_accepts_bytes():
    payload = b'{"type":"consent.revoked","id":"evt_002","data":{},"meta":{}}'
    event   = webhooks.parse_event(payload)
    assert event["type"] == "consent.revoked"


def test_parse_event_raises_on_invalid_json():
    with pytest.raises(ValueError, match="JSON"):
        webhooks.parse_event("not valid json {{")


def test_parse_event_raises_when_type_missing():
    with pytest.raises(ValueError, match="type"):
        webhooks.parse_event('{"id":"evt_003","data":{}}')
