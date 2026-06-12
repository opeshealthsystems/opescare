"""Tests for typed exception hierarchy."""
import pytest

from opescare.exceptions import (
    AuthenticationError,
    AuthorizationError,
    ConsentRequiredError,
    IdempotencyConflictError,
    OpesCareError,
    RateLimitError,
    ServerError,
    WebhookSignatureError,
)


def test_all_errors_inherit_from_opescare_error():
    for cls in (
        AuthenticationError, AuthorizationError, ConsentRequiredError,
        IdempotencyConflictError, RateLimitError, ServerError, WebhookSignatureError,
    ):
        err = cls("test message")
        assert isinstance(err, OpesCareError), f"{cls.__name__} must inherit OpesCareError"


def test_authentication_error_carries_status_code():
    err = AuthenticationError("Invalid token", status_code=401)
    assert err.status_code == 401
    assert str(err) == "Invalid token"


def test_rate_limit_error_carries_retry_after():
    err = RateLimitError("Too many requests", retry_after=45)
    assert err.retry_after == 45
    assert err.status_code == 429


def test_response_body_stored_on_error():
    body = {"error_code": "AUTH_FAILED", "message": "Bad creds"}
    err  = AuthenticationError("Bad creds", status_code=401, response_body=body)
    assert err.response_body == body


def test_consent_required_is_not_authorization_error():
    consent = ConsentRequiredError("Consent required")
    assert not isinstance(consent, AuthorizationError)


def test_errors_are_catchable_as_exception():
    with pytest.raises(Exception):
        raise ServerError("Boom", 500)


def test_webhook_signature_error_is_opescare_error():
    err = WebhookSignatureError("Signature mismatch")
    assert isinstance(err, OpesCareError)
    assert isinstance(err, WebhookSignatureError)
