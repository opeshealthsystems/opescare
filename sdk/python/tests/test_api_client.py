"""
Tests for ApiClient retry logic and exception mapping.
Uses pytest-httpx to mock HTTP responses without a real server.
"""
import pytest
import httpx
from pytest_httpx import HTTPXMock

from opescare.exceptions import (
    AuthenticationError,
    AuthorizationError,
    ConsentRequiredError,
    IdempotencyConflictError,
    NotFoundException,
    RateLimitError,
    ServerError,
    ValidationError,
)
from opescare.http.api_client import ApiClient


BASE = "http://opescare-test.local"


@pytest.fixture
def client():
    return ApiClient(BASE, "test_bearer_token", timeout=5.0)


def test_successful_get_returns_dict(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="GET",
        url=f"{BASE}/api/v1/connect/patients/verify/CM-HID-0001",
        json={"valid": True, "health_id": "CM-HID-0001"},
    )
    result = client.get("api/v1/connect/patients/verify/CM-HID-0001")
    assert result["valid"] is True


def test_401_raises_authentication_error(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="POST",
        url=f"{BASE}/api/v1/connect/patients/resolve",
        status_code=401,
        json={"message": "Token expired.", "error_code": "TOKEN_EXPIRED"},
    )
    with pytest.raises(AuthenticationError) as exc_info:
        client.post("api/v1/connect/patients/resolve", {"health_id": "CM-HID-0001"})
    assert exc_info.value.status_code == 401


def test_403_consent_required_raises_consent_error(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="GET",
        url=f"{BASE}/api/v1/connect/patients/CM-HID-0001/summary",
        status_code=403,
        json={"message": "Consent required.", "error_code": "CONSENT_REQUIRED"},
    )
    with pytest.raises(ConsentRequiredError):
        client.get("api/v1/connect/patients/CM-HID-0001/summary")


def test_403_generic_raises_authorization_error(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="GET",
        url=f"{BASE}/api/v1/connect/patients/CM-HID-0001/summary",
        status_code=403,
        json={"message": "Forbidden.", "error_code": "FORBIDDEN"},
    )
    with pytest.raises(AuthorizationError):
        client.get("api/v1/connect/patients/CM-HID-0001/summary")


def test_404_raises_not_found(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="GET",
        url=f"{BASE}/api/fhir/R4/Patient/CM-HID-XXXX",
        status_code=404,
        json={"message": "Patient not found."},
    )
    with pytest.raises(NotFoundException):
        client.get("api/fhir/R4/Patient/CM-HID-XXXX")


def test_409_raises_idempotency_conflict(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="POST",
        url=f"{BASE}/api/v1/connect/records/encounters",
        status_code=409,
        json={"message": "Idempotency key already used."},
    )
    with pytest.raises(IdempotencyConflictError):
        client.post("api/v1/connect/records/encounters", {"health_id": "CM-HID-0001"}, "idem-key-1")


def test_422_raises_validation_error(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="POST",
        url=f"{BASE}/api/v1/connect/patients/resolve",
        status_code=422,
        json={"message": "Validation failed.", "errors": {"health_id": ["required"]}},
    )
    with pytest.raises(ValidationError):
        client.post("api/v1/connect/patients/resolve", {})


def test_idempotency_key_sent_on_post(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="POST",
        url=f"{BASE}/api/v1/connect/records/lab-results",
        json={"status": "created"},
    )
    client.post("api/v1/connect/records/lab-results", {"test": "CBC"}, "my-idem-key")
    request = httpx_mock.get_requests()[0]
    assert request.headers.get("Idempotency-Key") == "my-idem-key"


def test_rate_limit_error_carries_server_retry_after(httpx_mock: HTTPXMock, client: ApiClient):
    # Regression: max() must be used so Retry-After: 120 is not overridden by 1s backoff.
    for _ in range(5):  # exhaust all retries
        httpx_mock.add_response(
            method="GET",
            url=f"{BASE}/api/fhir/R4/Patient",
            status_code=429,
            headers={"Retry-After": "120"},
            json={"message": "Rate limited."},
        )
    with pytest.raises(RateLimitError) as exc_info:
        client.get("api/fhir/R4/Patient")
    assert exc_info.value.retry_after == 120
    assert exc_info.value.retry_after > 8  # must be server value, not capped at backoff max


def test_bearer_token_sent_on_every_request(httpx_mock: HTTPXMock, client: ApiClient):
    httpx_mock.add_response(
        method="GET",
        url=f"{BASE}/api/fhir/R4/metadata",
        json={"resourceType": "CapabilityStatement"},
    )
    client.get("api/fhir/R4/metadata")
    request = httpx_mock.get_requests()[0]
    assert request.headers.get("Authorization") == "Bearer test_bearer_token"
