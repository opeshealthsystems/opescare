"""
OpesCare Connect Suite Python SDK

Quick start::

    from opescare import OpesCareClient

    client = OpesCareClient(
        client_id="sandbox_xxxxxxxxxxxx",
        client_secret="sk_sandbox_xxxxxxxxxxxx",
        environment="sandbox",
    )

    # Resolve a patient's Health ID
    result = client.health_ids.resolve(health_id="CM-HID-7KQ9-MP42-X8D1")

    # Read active medications (FHIR R4)
    meds = client.fhir.medication_requests("CM-HID-7KQ9-MP42-X8D1", status="active")

    # Push a CDSS drug interaction alert
    client.records.push_encounter(
        health_id="CM-HID-7KQ9-MP42-X8D1",
        encounter_type="cdss_alert",
        clinical_note="Drug interaction: Warfarin + Aspirin — increased bleeding risk.",
        severity="high",
        alert_type="drug_interaction",
        cdss_rule_id="DDI-WARFARIN-ASPIRIN-001",
    )
"""
from __future__ import annotations

from .auth.token_manager import TokenManager
from .http.api_client    import ApiClient
from .modules.consents   import Consents
from .modules.fhir       import Fhir
from .modules.health_ids import HealthIds
from .modules.patients   import Patients
from .modules.records    import Records
from .modules.webhooks   import Webhooks

_BASE_URLS = {
    "sandbox":    "http://opescare.test",
    "production": "https://api.opescare.com",
}


class OpesCareClient:
    """
    Main entry point for the OpesCare Python SDK.

    Instantiation fetches an access token eagerly so credential errors
    surface immediately rather than on the first API call.
    """

    def __init__(
        self,
        client_id: str,
        client_secret: str,
        environment: str = "sandbox",
        base_url: str | None = None,
        timeout: float = 30.0,
    ) -> None:
        resolved_url = base_url or _BASE_URLS.get(environment)
        if not resolved_url:
            raise ValueError(
                f"Unknown environment '{environment}'. "
                "Use 'sandbox' or 'production', or pass base_url= explicitly."
            )

        self._client_id     = client_id
        self._client_secret = client_secret
        self._environment   = environment
        self._base_url      = resolved_url
        self._timeout       = timeout

        self._token_manager = TokenManager(resolved_url, client_id, client_secret)
        token               = self._token_manager.get_token()  # raises AuthenticationError on bad creds

        self._http = ApiClient(resolved_url, token, timeout)

        self.health_ids = HealthIds(self._http)
        self.patients   = Patients(self._http)
        self.consents   = Consents(self._http)
        self.records    = Records(self._http)
        self.fhir       = Fhir(self._http)
        self.webhooks   = Webhooks(self._http)

    def refresh_token(self) -> "OpesCareClient":
        """
        Return a new client with a freshly fetched access token.
        Call this if you receive an AuthenticationError mid-session.
        """
        return OpesCareClient(
            client_id=self._client_id,
            client_secret=self._client_secret,
            environment=self._environment,
            base_url=self._base_url,
            timeout=self._timeout,
        )

    def close(self) -> None:
        """Close the underlying HTTP connection pool."""
        self._http.close()

    def __enter__(self) -> "OpesCareClient":
        return self

    def __exit__(self, *_: object) -> None:
        self.close()
