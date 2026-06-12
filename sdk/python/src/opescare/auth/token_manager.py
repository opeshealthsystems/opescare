"""
OAuth2 client-credentials token manager.

Fetches a Bearer token from /api/v1/connect/auth/token,
caches it in memory, and refreshes 60 seconds before expiry.
"""
from __future__ import annotations

import time

import httpx

from ..exceptions import AuthenticationError


class TokenManager:
    _EXPIRY_BUFFER = 60  # refresh 60s before actual expiry

    def __init__(self, base_url: str, client_id: str, client_secret: str) -> None:
        self._base_url     = base_url.rstrip("/")
        self._client_id    = client_id
        self._client_secret = client_secret
        self._token: str | None = None
        self._expires_at: float = 0.0

    def get_token(self) -> str:
        """Return a valid token, refreshing if expired or near expiry."""
        if self._token and time.time() < (self._expires_at - self._EXPIRY_BUFFER):
            return self._token
        return self.refresh()

    def refresh(self) -> str:
        """Force a new token request."""
        url = f"{self._base_url}/api/v1/connect/auth/token"
        try:
            response = httpx.post(
                url,
                json={
                    "client_id":     self._client_id,
                    "client_secret": self._client_secret,
                    "grant_type":    "client_credentials",
                },
                headers={"Content-Type": "application/json"},
                timeout=15,
            )
            response.raise_for_status()
            data = response.json()
        except httpx.HTTPStatusError as exc:
            body    = _safe_json(exc.response)
            message = body.get("message", exc.response.text)
            raise AuthenticationError(
                f"Failed to obtain access token: {message}",
                exc.response.status_code,
                body,
            ) from exc
        except httpx.RequestError as exc:
            raise AuthenticationError(
                f"Failed to reach token endpoint: {exc}"
            ) from exc

        token = data.get("access_token")
        if not token:
            raise AuthenticationError(
                "Token response is missing 'access_token' field."
            )

        self._token      = token
        self._expires_at = time.time() + int(data.get("expires_in", 3600))
        return self._token

    def revoke(self) -> None:
        """Clear the cached token (forces a fresh fetch on next call)."""
        self._token      = None
        self._expires_at = 0.0


def _safe_json(response: httpx.Response) -> dict:
    try:
        return response.json()
    except Exception:
        return {}
