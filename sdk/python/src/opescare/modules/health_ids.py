from __future__ import annotations

from ..http.api_client import ApiClient


class HealthIds:
    """Health ID resolution and verification."""

    def __init__(self, client: ApiClient) -> None:
        self._client = client

    def resolve(self, **params: object) -> dict:
        """
        Resolve a patient to their canonical Health ID.

        Pass health_id= for direct lookup, or first_name=, last_name=,
        date_of_birth= for demographic resolution.

        Returns dict with status: 'found' | 'created' | 'not_found'
        """
        return self._client.post("api/v1/connect/patients/resolve", dict(params))

    def verify(self, health_id: str) -> dict:
        """Verify a Health ID format and existence without full demographics."""
        return self._client.get(f"api/v1/connect/patients/verify/{health_id}")
