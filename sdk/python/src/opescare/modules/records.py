from __future__ import annotations

import os
from typing import Any

from ..http.api_client import ApiClient


def _gen_key(prefix: str) -> str:
    return f"{prefix}-{os.urandom(16).hex()}"


class Records:
    """
    Clinical record writes — push encounters, lab results, prescriptions.

    All write operations require:
      1. A valid consent grant for the relevant scope
      2. An idempotency key (auto-generated if not provided)
    """

    def __init__(self, client: ApiClient) -> None:
        self._client = client

    def push_encounter(
        self,
        health_id: str,
        encounter_type: str,
        clinical_note: str,
        *,
        severity: str = "low",
        alert_type: str = "",
        cdss_rule_id: str = "",
        confidence_score: float | None = None,
        occurred_at: str = "",
        source_system: str = "",
        idempotency_key: str | None = None,
        **extra: Any,
    ) -> dict:
        """
        Push a clinical encounter, note, or CDSS recommendation.
        Requires consent scope: patients:write
        """
        payload: dict[str, Any] = {
            "health_id":      health_id,
            "encounter_type": encounter_type,
            "clinical_note":  clinical_note,
            "severity":       severity,
        }
        if alert_type:       payload["alert_type"]       = alert_type
        if cdss_rule_id:     payload["cdss_rule_id"]     = cdss_rule_id
        if confidence_score is not None: payload["confidence_score"] = confidence_score
        if occurred_at:      payload["occurred_at"]      = occurred_at
        if source_system:    payload["source_system"]    = source_system
        payload.update(extra)

        return self._client.post(
            "api/v1/connect/records/encounters",
            payload,
            idempotency_key or _gen_key("enc"),
        )

    def push_lab_result(
        self,
        health_id: str,
        test_name: str,
        result_value: str,
        *,
        reference_range: str = "",
        interpretation: str = "",
        flagged: bool = False,
        flag_level: str = "",
        occurred_at: str = "",
        source_system: str = "",
        idempotency_key: str | None = None,
        **extra: Any,
    ) -> dict:
        """
        Push a lab result or CDSS interpretation.
        Requires consent scope: labs:write
        """
        payload: dict[str, Any] = {
            "health_id":    health_id,
            "test_name":    test_name,
            "result_value": result_value,
            "flagged":      flagged,
        }
        if reference_range: payload["reference_range"] = reference_range
        if interpretation:  payload["interpretation"]  = interpretation
        if flag_level:      payload["flag_level"]      = flag_level
        if occurred_at:     payload["occurred_at"]     = occurred_at
        if source_system:   payload["source_system"]   = source_system
        payload.update(extra)

        return self._client.post(
            "api/v1/connect/records/lab-results",
            payload,
            idempotency_key or _gen_key("lab"),
        )

    def push_prescription(
        self,
        health_id: str,
        *,
        alert_type: str = "",
        medication_name: str = "",
        contraindication_reason: str = "",
        severity: str = "low",
        recommendation: str = "",
        occurred_at: str = "",
        source_system: str = "",
        idempotency_key: str | None = None,
        **extra: Any,
    ) -> dict:
        """
        Push a prescription or drug safety alert.
        Requires consent scope: prescriptions:write
        """
        payload: dict[str, Any] = {"health_id": health_id, "severity": severity}
        if alert_type:               payload["alert_type"]               = alert_type
        if medication_name:          payload["medication_name"]          = medication_name
        if contraindication_reason:  payload["contraindication_reason"]  = contraindication_reason
        if recommendation:           payload["recommendation"]           = recommendation
        if occurred_at:              payload["occurred_at"]              = occurred_at
        if source_system:            payload["source_system"]            = source_system
        payload.update(extra)

        return self._client.post(
            "api/v1/connect/records/prescriptions",
            payload,
            idempotency_key or _gen_key("rx"),
        )
