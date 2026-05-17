# OpesCare Connect: Clinical Interoperability Workflows

This document defines standard push, pull, search, and inventory sync operations for external medical actors.

---

## 1. Privacy-Safe Patient Search

To maintain total patient confidentiality, searching for a patient does not return raw clinical diagnoses or summaries.

*   **Endpoint**: `POST /api/v1/connect/patients/search`
*   **Required Scope**: `patients.search`

### Search Request (by Health ID)
```json
{
  "search_type": "health_id",
  "query": "OC-CMR-7KQ9-MP42-X8D1",
  "purpose": "treatment",
  "requesting_user": {
    "external_user_id": "DR-1002",
    "name": "Dr. Elizabeth Blackwell",
    "role": "doctor"
  }
}
```

### Exact Match Response
```json
{
  "status": "matched",
  "match_type": "exact",
  "patient": {
    "health_id": "OC-CMR-7KQ9-MP42-X8D1",
    "display_name": "John D.",
    "sex": "male",
    "year_of_birth": 1990,
    "verification_status": "verified_by_facility"
  },
  "next_action": "request_consent"
}
```

---

## 2. Scoped Patient Consent Requests

Before retrieving patient clinical summaries, you must obtain a scoped patient consent grant.

*   **Endpoint**: `POST /api/v1/connect/consents/request`
*   **Required Scope**: `consent.request`

```json
{
  "health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "purpose": "treatment",
  "requested_scopes": ["patient.summary", "allergies.read", "medications.read"],
  "duration_minutes": 240,
  "callback_url": "https://your-hospital.org/opescare/consent-callback"
}
```

### Consent Verification Check
*   **Endpoint**: `POST /api/v1/connect/consents/verify`
```json
{
  "consent_request_id": "crq_8921_abc",
  "health_id": "OC-CMR-7KQ9-MP42-X8D1"
}
```

---

## 3. Pull Patient Records

Once consent is granted, retrieve the scoped clinical file.

### Pull Patient Summary
*   **Endpoint**: `GET /api/v1/connect/patients/{health_id}/summary`
*   **Required Headers**:
    *   `X-Purpose-Of-Use: treatment`
    *   `X-Consent-Grant-Id: cgt_1002_xyz`

### Emergency Pull Bypass
*   **Endpoint**: `GET /api/v1/connect/patients/{health_id}/emergency-profile`
*   **Required Headers**:
    *   `X-Purpose-Of-Use: emergency`
    *   `X-Emergency-Reason: Patient is unconscious in ICU`

> [!WARNING]
> Emergency pulls bypass the active consent check but immediately register a high-risk security audit log and trigger alerts to OpesCare records regulators.

---

## 4. Push Clinical Records

Integrated systems use the push endpoints to record clinical encounters, lab releases, and prescriptions.

*   **Endpoint**: `POST /api/v1/connect/records/encounters`
*   **Required Headers**:
    *   `Idempotency-Key: idm_unique_key_uuid`

```json
{
  "health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "external_encounter_id": "ENC-9001",
  "facility_reference": "FAC-001",
  "encounter": {
    "type": "outpatient",
    "started_at": "2026-05-17T09:00:00Z",
    "chief_complaint": "Fever and headaches",
    "diagnoses": [
      {
        "code": "R50.9",
        "system": "ICD-10",
        "display": "Fever, unspecified"
      }
    ]
  }
}
```

---

## 5. Inventory Stock Sync (Pharmacy & Blood Banks)

To populate verified medicine and blood availability locator maps, partners must push stock indices.

### Pharmacy Stock Sync
*   **Endpoint**: `POST /api/v1/connect/inventory/pharmacy-stock/sync`
```json
{
  "facility_reference": "PHARM-001",
  "items": [
    {
      "generic_name": "Amoxicillin",
      "strength": "500mg",
      "quantity_available": 120,
      "expiry_date": "2027-01-31"
    }
  ]
}
```
> [!IMPORTANT]
> Quarantined, recalled, or expired stock items must be excluded from sync dispatches.
