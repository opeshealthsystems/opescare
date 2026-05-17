# OpesCare Connect: Webhooks & Reconciliation Cases

Learn how to subscribe to live B2B events and handle clinical matching reconciliation.

---

## 1. Event Webhooks

Webhooks notify external client platforms in real-time when patient timelines, consent statuses, or inventory syncing thresholds update.

### Core Interoperability Webhook Events
*   `patient.created` / `patient.updated`: Patient index updates.
*   `consent.granted` / `consent.revoked`: Consent status shifts.
*   `emergency_access.used`: Emergency bypass was triggered (immediate regulatory notice).
*   `lab_result.released`: Laboratory results validated.

---

## 2. Webhook Signature Security

Every dispatch is signed with a cryptographic signature sent in the request headers to prevent replay attacks and spoofing.

### Dispatch Headers
```text
X-OpesCare-Signature: t=1778946753,v1=a1b2c3d4e5f6g7h8...
X-OpesCare-Timestamp: 1778946753
X-OpesCare-Event-Id: evt_dispatch_1002_xyz
```

### Signature Computation
Compute HMAC-SHA256 of the timestamp joined with the raw JSON request body utilizing your unique Webhook Secret:

```text
HMAC-SHA256(timestamp + "." + raw_body, webhook_secret)
```

---

## 3. Webhook Payload Minimization Rule

To comply with high-level patient privacy guidelines, webhook payloads do not send raw patient data. They dispatch resource IDs which must be pulled using active, authorized API requests.

### Example Webhook Payload
```json
{
  "event_id": "evt_dispatch_1002_xyz",
  "event_type": "lab_result.released",
  "occurred_at": "2026-05-17T10:00:00Z",
  "resource": {
    "type": "lab_result",
    "id": "lab_result_unique_registry_id_999"
  },
  "patient": {
    "health_id_reference": "OC-CMR-****-X8D1"
  },
  "facility_id": "FAC-001"
}
```

---

## 4. Patient Matching & Reconciliation Cases

If a hospital pushes a clinical record referencing a patient that has uncertain MPI matching, OpesCare does not discard the record. Instead, it places the sync record under a **Reconciliation Case** for clinical review.

*   **Endpoint**: `GET /api/v1/connect/reconciliation/cases`
*   **Resolve Case**: `POST /api/v1/connect/reconciliation/cases/{case_id}/resolve`

### Resolution Payload
```json
{
  "resolution": "attach_to_confirmed_patient",
  "confirmed_health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "notes": "Verified patient identity manually via ID CARD match."
}
```
*   Unresolved reconciliation records **will not appear** on active patient timelines.
