"""Typed exceptions for the OpesCare Python SDK."""
from __future__ import annotations

from typing import Any


class OpesCareError(Exception):
    """Base exception for all OpesCare SDK errors."""

    def __init__(
        self,
        message: str,
        status_code: int = 0,
        response_body: dict[str, Any] | None = None,
    ) -> None:
        super().__init__(message)
        self.status_code   = status_code
        self.response_body = response_body or {}


class AuthenticationError(OpesCareError):
    """Invalid or expired credentials / token."""


class AuthorizationError(OpesCareError):
    """Authenticated but not permitted to perform the action."""


class ConsentRequiredError(OpesCareError):
    """Patient consent has not been granted for the requested scope."""


class ValidationError(OpesCareError):
    """Request payload failed server-side validation (HTTP 422)."""


class NotFoundException(OpesCareError):
    """Resource not found (HTTP 404)."""


class IdempotencyConflictError(OpesCareError):
    """Idempotency key was reused with a different payload (HTTP 409)."""


class RateLimitError(OpesCareError):
    """Too many requests — check retry_after for when to retry."""

    def __init__(
        self,
        message: str,
        retry_after: int = 60,
        response_body: dict[str, Any] | None = None,
    ) -> None:
        super().__init__(message, 429, response_body)
        self.retry_after = retry_after


class ServerError(OpesCareError):
    """OpesCare server returned 5xx after all retries were exhausted."""


class WebhookSignatureError(OpesCareError):
    """Incoming webhook signature is invalid, malformed, or a replay."""
