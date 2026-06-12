from __future__ import annotations

from typing import Any

from ..http.api_client import ApiClient


class Fhir:
    """
    FHIR R4 resource reads.

    All responses are FHIR R4 compliant JSON.
    Patient-specific resources require patients:read consent scope.
    """

    def __init__(self, client: ApiClient) -> None:
        self._client = client

    def metadata(self) -> dict:
        """CapabilityStatement — public, no auth required."""
        return self._client.get("api/fhir/R4/metadata")

    def patient(self, health_id: str) -> dict:
        """GET /fhir/R4/Patient/{id}"""
        return self._client.get(f"api/fhir/R4/Patient/{health_id}")

    def search_patients(self, **params: Any) -> dict:
        """GET /fhir/R4/Patient?param=value"""
        return self._client.get("api/fhir/R4/Patient", params or None)

    def patient_everything(self, health_id: str) -> dict:
        """GET /fhir/R4/Patient/{id}/$everything — full patient bundle."""
        return self._client.get(f"api/fhir/R4/Patient/{health_id}/$everything")

    def allergy_intolerances(self, health_id: str, **params: Any) -> dict:
        """GET /fhir/R4/AllergyIntolerance?patient={health_id}"""
        return self._client.get("api/fhir/R4/AllergyIntolerance", {"patient": health_id, **params})

    def medication_requests(self, health_id: str, **params: Any) -> dict:
        """GET /fhir/R4/MedicationRequest?patient={health_id}"""
        return self._client.get("api/fhir/R4/MedicationRequest", {"patient": health_id, **params})

    def diagnostic_reports(self, health_id: str, **params: Any) -> dict:
        """GET /fhir/R4/DiagnosticReport?patient={health_id}"""
        return self._client.get("api/fhir/R4/DiagnosticReport", {"patient": health_id, **params})

    def conditions(self, health_id: str, **params: Any) -> dict:
        """GET /fhir/R4/Condition?patient={health_id}"""
        return self._client.get("api/fhir/R4/Condition", {"patient": health_id, **params})

    def immunizations(self, health_id: str, **params: Any) -> dict:
        """GET /fhir/R4/Immunization?patient={health_id}"""
        return self._client.get("api/fhir/R4/Immunization", {"patient": health_id, **params})

    def encounters(self, health_id: str, **params: Any) -> dict:
        """GET /fhir/R4/Encounter?patient={health_id}"""
        return self._client.get("api/fhir/R4/Encounter", {"patient": health_id, **params})
