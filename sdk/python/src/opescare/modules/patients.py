from __future__ import annotations

from ..http.api_client import ApiClient


class Patients:
    """Patient record reads — requires valid consent grant."""

    def __init__(self, client: ApiClient) -> None:
        self._client = client

    def get_summary(self, health_id: str) -> dict:
        """
        Consented patient summary: demographics, allergies, medications, labs, visits.
        Requires consent scope: patients:read
        """
        return self._client.get(f"api/v1/connect/patients/{health_id}/summary")

    def get_emergency_profile(self, health_id: str) -> dict:
        """Emergency profile — requires emergency:access scope, fully audited."""
        return self._client.get(f"api/v1/connect/patients/{health_id}/emergency-profile")

    def search(self, **params: object) -> dict:
        """Search patients by health_id, demographics, national_id, or phone."""
        return self._client.post("api/v1/connect/patients/search", dict(params))
