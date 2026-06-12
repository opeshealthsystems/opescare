from __future__ import annotations

from ..http.api_client import ApiClient


class Consents:
    """
    Patient consent request and verification.

    Consent is MANDATORY before accessing clinical data.
    Always call verify() before reading patient records or pushing clinical data.
    """

    def __init__(self, client: ApiClient) -> None:
        self._client = client

    def request(
        self,
        health_id: str,
        purpose: str,
        requested_scopes: list[str],
        validity_period_days: int = 30,
        system_name: str = "",
    ) -> dict:
        """Request consent from a patient for the specified scopes."""
        return self._client.post("api/v1/connect/consents/request", {
            "health_id":           health_id,
            "purpose":             purpose,
            "requested_scopes":    requested_scopes,
            "validity_period_days": validity_period_days,
            "system_name":         system_name,
        })

    def verify(self, health_id: str, scope: str) -> dict:
        """Verify whether a specific scope has been granted for a patient."""
        return self._client.post("api/v1/connect/consents/verify", {
            "health_id": health_id,
            "scope":     scope,
        })

    def request_emergency_access(
        self,
        health_id: str,
        reason: str,
        emergency_type: str = "clinical_emergency",
    ) -> dict:
        """
        Emergency access override — bypasses consent, fully audited.
        Use only in genuine clinical emergencies.
        """
        return self._client.post("api/v1/connect/emergency-access/request", {
            "health_id":      health_id,
            "reason":         reason,
            "emergency_type": emergency_type,
        })
