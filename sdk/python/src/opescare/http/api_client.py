"""
Low-level HTTP client wrapping httpx.

Handles:
  - Bearer token injection on every request
  - Idempotency-Key header on write operations
  - Exponential backoff retry for 429 / 5xx
  - Typed exception mapping
"""
from __future__ import annotations

import time
from typing import Any

import httpx

from ..exceptions import (
    AuthenticationError,
    AuthorizationError,
    ConsentRequiredError,
    IdempotencyConflictError,
    NotFoundException,
    OpesCareError,
    RateLimitError,
    ServerError,
    ValidationError,
)

_MAX_RETRIES  = 4
_RETRY_DELAYS = [1, 2, 4, 8]  # seconds, exponential
_WRITE_METHODS = {"POST", "PUT", "PATCH"}


class ApiClient:
    def __init__(self, base_url: str, access_token: str, timeout: float = 30.0) -> None:
        self._base_url     = base_url.rstrip("/")
        self._access_token = access_token
        self._timeout      = timeout
        self._http         = httpx.Client(
            base_url=self._base_url + "/",
            timeout=timeout,
            headers={
                "Accept":     "application/json",
                "User-Agent": "opescare-python-sdk/1.0",
            },
        )

    # ── Public HTTP methods ────────────────────────────────────────────────

    def get(self, path: str, params: dict[str, Any] | None = None) -> dict[str, Any]:
        return self._request("GET", path, params=params)

    def post(
        self,
        path: str,
        body: dict[str, Any] | None = None,
        idempotency_key: str | None = None,
    ) -> dict[str, Any]:
        return self._request("POST", path, body=body, idempotency_key=idempotency_key)

    def delete(self, path: str) -> dict[str, Any]:
        return self._request("DELETE", path)

    # ── Internal ──────────────────────────────────────────────────────────

    def _request(
        self,
        method: str,
        path: str,
        params: dict[str, Any] | None = None,
        body: dict[str, Any] | None = None,
        idempotency_key: str | None = None,
    ) -> dict[str, Any]:
        headers: dict[str, str] = {
            "Authorization": f"Bearer {self._access_token}",
        }
        if body is not None:
            headers["Content-Type"] = "application/json"
        if idempotency_key and method.upper() in _WRITE_METHODS:
            headers["Idempotency-Key"] = idempotency_key

        attempt = 0
        while True:
            try:
                response = self._http.request(
                    method,
                    path.lstrip("/"),
                    params=params,
                    json=body,
                    headers=headers,
                )
            except httpx.RequestError as exc:
                if attempt < _MAX_RETRIES:
                    time.sleep(_RETRY_DELAYS[attempt])
                    attempt += 1
                    continue
                raise ServerError(f"Request failed after {_MAX_RETRIES} retries: {exc}") from exc

            status = response.status_code

            if status < 400:
                return _decode(response)

            resp_body  = _safe_json(response)
            error_code = resp_body.get("error_code") or resp_body.get("error") or ""
            message    = resp_body.get("message") or f"HTTP {status}"

            if status == 429:
                retry_after = int(response.headers.get("Retry-After", 60))
                if attempt < _MAX_RETRIES:
                    time.sleep(max(retry_after, _RETRY_DELAYS[attempt]))
                    attempt += 1
                    continue
                raise RateLimitError(message, retry_after, resp_body)

            if status >= 500 and attempt < _MAX_RETRIES:
                time.sleep(_RETRY_DELAYS[attempt])
                attempt += 1
                continue

            raise _map_error(status, error_code, message, resp_body)

    def close(self) -> None:
        self._http.close()

    def __enter__(self) -> "ApiClient":
        return self

    def __exit__(self, *_: object) -> None:
        self.close()


def _decode(response: httpx.Response) -> dict[str, Any]:
    if not response.content:
        return {}
    try:
        return response.json()
    except Exception:
        return {"raw": response.text}


def _safe_json(response: httpx.Response) -> dict[str, Any]:
    try:
        return response.json()
    except Exception:
        return {}


def _map_error(
    status: int, error_code: str, message: str, body: dict[str, Any]
) -> OpesCareError:
    if status == 401:
        return AuthenticationError(message, status, body)
    if status == 403 and error_code == "CONSENT_REQUIRED":
        return ConsentRequiredError(message, status, body)
    if status == 403:
        return AuthorizationError(message, status, body)
    if status == 404:
        return NotFoundException(message, status, body)
    if status == 409:
        return IdempotencyConflictError(message, status, body)
    if status == 422:
        return ValidationError(message, status, body)
    if status >= 500:
        return ServerError(message, status, body)
    return OpesCareError(message, status, body)
