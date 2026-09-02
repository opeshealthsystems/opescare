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

> **Checked against the running code on 2026-09-02.** The payload previously shown here
> (`generic_name`, `strength`, `quantity_available`) was never accepted by the API. The
> authoritative field-by-field reference is
> [`OPESCARE_CONNECT_PLATFORM_API_SDK_BRIDGE_WIDGET_WEBHOOKS.md`](OPESCARE_CONNECT_PLATFORM_API_SDK_BRIDGE_WIDGET_WEBHOOKS.md)
> §13.2 (pharmacy) and §14.2 (blood).

### Pharmacy Stock Sync
*   **Endpoint**: `POST /api/v1/connect/inventory/pharmacy-stock/sync`
*   **Facility scope**: taken from the bearer token only. `facility_reference` is informational.
*   **Required per item**: `drug_code` (catalogue medicine UUID or WHO ATC code) and `quantity` (packs).
*   **Optional per item**: `expiry_date`, `stock_status` (`in_stock` | `low_stock` | `out_of_stock` | `unknown`), `pack_size`, `unit_price`.

```json
{
  "facility_reference": "PHARM-001",
  "items": [
    {
      "drug_code": "C01AA05",
      "quantity": 120,
      "expiry_date": "2027-01-31",
      "pack_size": "box of 20",
      "unit_price": 2500
    }
  ]
}
```

Items are resolved against the OpesCare medicine catalogue one by one. An unresolvable
code comes back in `rejected_items` as `unknown_drug_code`; an ATC code matching more
than one catalogue medicine comes back as `ambiguous_drug_code` (18 of the 419 catalogue
medicines share an ATC code with another row, so a feed keyed on ATC should expect this
and map those products to catalogue UUIDs). **A batch in which nothing could be stored
returns `422`, not a success.**

### Blood Stock Sync
*   **Endpoint**: `POST /api/v1/connect/inventory/blood-stock/sync`
*   **Required per item**: `blood_group`, `component_code`, `units`, `screening_status`.
*   Only `screening_status: "screened_safe"` is accepted; any other value refuses the whole batch with `422 UNSAFE_BLOOD_STATUS`.

```json
{
  "facility_reference": "BLOOD-001",
  "items": [
    {
      "blood_group": "O+",
      "component_code": "packed_red_cells",
      "units": 4,
      "screening_status": "screened_safe"
    }
  ]
}
```

> [!IMPORTANT]
> Quarantined, recalled, or expired stock items must be excluded from sync dispatches.
> The API enforces only the expiry gate (pharmacy) and the screening gate (blood) — one
> offending item refuses the entire batch. It has no recall or quarantine field and cannot
> detect either, so withholding that stock remains the partner's obligation.
