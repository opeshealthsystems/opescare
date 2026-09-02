# OpesCare Connect Platform API, SDK, Bridge Agent, Widget, Webhooks, Alerts, and Synchronization Blueprint

**Document Type:** Technical Product + Architecture + API Design  
**Project:** OpesCare  
**Parent Company:** Opesware  
**Build Direction:** Build from scratch  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Specialist Services:** Python/FastAPI later for duplicate matching, OCR, AI summaries, analytics, anomaly detection, and public health intelligence  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, database structure, module structure, API structure, UI patterns, or integration assumptions.

---

## 1. Purpose of This Document

This document defines the complete OpesCare connectivity platform.

It explains how OpesCare connects with hospitals, clinics, laboratories, pharmacies, insurers, blood banks, public health systems, developer systems, and the official OpesCare mobile app.

This document covers:

1. OpesCare Connect API
2. Mobile API separation
3. API authentication and authorization
4. Push and pull data flows
5. Consent and access checks
6. Patient matching and reconciliation
7. Idempotency and duplicate protection
8. API versioning
9. SDK design
10. Connect Widget design
11. Bridge Agent design
12. Webhooks
13. Alerts and notification events
14. Sync engine
15. Offline and retry behavior
16. Error handling
17. OpenAPI requirements
18. Security rules
19. Testing requirements
20. Certification and sandbox flow
21. Full architecture review and alignment check

The goal is to ensure that OpesCare is not just a digital health ID platform, but a connected healthcare interoperability platform where approved systems can safely push and pull patient information.

---

## 2. Core Product Vision

OpesCare must support this vision:

**One patient. One secure Health ID. One trusted medical history. Connected across healthcare providers with consent, audit, source attribution, and safety.**

The platform must allow:

- patients to carry one Health ID
- hospitals to push new medical records
- hospitals to pull approved patient summaries
- labs to push verified lab results
- pharmacies to push dispense events and stock availability
- insurers to access only minimum necessary information
- blood banks and hospitals to share controlled blood availability
- external systems to integrate without replacing all existing software
- patients to see who accessed their records
- emergency providers to access limited critical information when needed
- OpesCare to maintain clean audit logs, provenance, and reconciliation

---

## 3. Core Architecture Decision

OpesCare must have separate API surfaces.

Do not expose one generic API for every actor.

Use these API groups:

```text
/api/mobile/...             Official OpesCare mobile app
/api/web/...                First-party web portals if needed
/api/v1/connect/...         External hospitals, labs, pharmacies, insurers, blood banks, vendors
/api/v1/admin/...           OpesCare internal administration
/api/v1/webhooks/...        Webhook management and delivery callbacks where needed
```

Recommended architecture:

```text
External Systems / Mobile Apps
        ↓
API Gateway / Laravel Middleware Layer
        ↓
Authentication
        ↓
Facility / User / Client Verification
        ↓
Authorization + Consent + Purpose Check
        ↓
Validation + Idempotency + Rate Limit
        ↓
Patient Matching / MPI
        ↓
Domain Services
        ↓
PostgreSQL
        ↓
Outbox Events + Redis Queues
        ↓
Webhooks / Alerts / Sync Dashboard / Reconciliation
```

---

## 4. API Boundary Rules

### 4.1 Mobile API

The Mobile API is for the official OpesCare patient mobile app.

It supports:

- patient login
- OTP verification
- Health ID display
- QR code
- consent approval/denial
- access logs
- timeline summary
- lab results
- prescriptions
- appointments
- document uploads
- Find Medication
- Find Blood Help
- notifications
- dependents

Mobile API must not be used by hospitals or third-party systems.

### 4.2 Connect API

The Connect API is for external organizations and systems.

It supports:

- patient search
- consent request
- consent verification
- pull approved patient summary
- push encounters
- push lab results
- push prescriptions
- push dispense events
- push documents
- pharmacy stock sync
- blood stock sync
- blood need requests
- sync status
- reconciliation
- webhooks

Connect API must not expose patient self-service functions such as changing mobile PIN, managing personal devices, or patient app settings.

### 4.3 Admin API

Admin APIs are for internal OpesCare platform operations.

They must not be exposed to external facilities unless explicitly required.

---

## 5. Integration Actors

The Connect Platform must support these actors:

```text
hospital
clinic
laboratory
pharmacy
insurance_company
blood_bank
public_health_system
technology_vendor
mobile_app_backend
opescare_lite_portal
opescare_bridge_agent
opescare_connect_widget
opescare_sdk_client
```

Each actor must have:

- verified organization
- active integration client
- approved scopes
- environment assignment
- audit trail
- rate limits
- API credential status
- integration owner contact
- support contact
- sandbox approval before production where required

---

## 6. Integration Products

OpesCare Connect Platform includes:

1. **OpesCare Connect API**  
   Secure REST API for system-to-system integration.

2. **OpesCare Connect SDK**  
   Developer libraries that make API integration easier.

3. **OpesCare Connect Widget**  
   Embeddable secure widget for patient search, consent, pull, and push.

4. **OpesCare Bridge Agent**  
   Connector for legacy systems, CSV/Excel exports, local databases, and offline environments.

5. **OpesCare Lite Portal**  
   Browser-based portal for facilities without their own software.

6. **Webhooks and Alerts Engine**  
   Event delivery and notifications for integrated systems.

7. **Sync and Reconciliation Dashboard**  
   Visibility into pushed records, failed syncs, duplicate conflicts, and data quality issues.

---

## 7. Authentication Model

### 7.1 External Connect API Authentication

Use OAuth2 Client Credentials or signed client credential token flow for system-to-system API access.

Each external system must have:

```text
client_id
client_secret
organization_id
facility_id
environment
allowed_scopes
status
rate_limit_policy
credential_expiry
last_rotated_at
```

Access token rules:

```text
short-lived access token
scoped
audience-bound
environment-bound
revocable
logged
```

Recommended token endpoint:

```text
POST /api/v1/connect/auth/token
```

Required request:

```json
{
  "client_id": "client_xxx",
  "client_secret": "secret_xxx",
  "grant_type": "client_credentials"
}
```

Success response:

```json
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "patients.search records.push consent.request"
}
```

### 7.2 Mobile API Authentication

Mobile app authentication should use:

- phone/email login
- OTP verification
- device registration
- short-lived access token
- refresh token rotation
- secure device storage
- optional PIN/biometric unlock
- remote logout/device revocation

Mobile login endpoint examples:

```text
POST /api/mobile/auth/login
POST /api/mobile/auth/otp/verify
POST /api/mobile/auth/refresh
POST /api/mobile/auth/logout
POST /api/mobile/devices/register
POST /api/mobile/devices/{id}/revoke
```

### 7.3 Widget Authentication

The Connect Widget must never expose permanent API credentials in browser code.

Widget flow:

1. Hospital backend authenticates with OpesCare.
2. Hospital backend requests a short-lived widget session token.
3. OpesCare returns widget token.
4. Browser loads widget with short-lived token.
5. Widget performs only permitted actions.
6. Widget session expires quickly.

Endpoint:

```text
POST /api/v1/connect/widget/sessions
```

### 7.4 Bridge Agent Authentication

Bridge Agent must authenticate as an integration client tied to a specific facility and device.

Bridge credentials must be:

- device-bound
- revocable
- rotatable
- environment-bound
- encrypted locally
- never shown in plain text after setup

---

## 8. Authorization and Access Control

Every API request must pass through:

```text
1. authentication
2. client status check
3. facility status check
4. organization status check
5. scope check
6. purpose-of-use check
7. patient consent check where required
8. record sensitivity check
9. rate limit check
10. audit event creation
```

External systems must send purpose of use for sensitive pull operations.

Recommended header:

```text
X-Purpose-Of-Use: treatment
```

Allowed purpose values:

```text
treatment
emergency
referral
lab_processing
pharmacy_dispensing
insurance_eligibility
insurance_claim
public_health_reporting
patient_authorized_access
system_sync
```

Rules:

- `treatment` may require patient consent depending on policy.
- `emergency` must create emergency access audit and review.
- `insurance_claim` must return minimum necessary information only.
- `public_health_reporting` must follow governance and country policy.
- `system_sync` must never be used to bypass consent for record viewing.

---

## 9. Consent Model in Connect API

### 9.1 Consent Required for Pull

External systems cannot pull full patient information by default.

They must request consent unless an allowed legal/policy exception applies.

Consent request endpoint:

```text
POST /api/v1/connect/consents/request
```

Example request:

```json
{
  "health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "facility_reference": "FAC-001",
  "requesting_user": {
    "external_user_id": "DR-1002",
    "name": "Dr. Example",
    "role": "doctor"
  },
  "purpose": "treatment",
  "requested_scopes": [
    "patient.summary",
    "allergies.read",
    "medications.read",
    "lab_results.recent.read"
  ],
  "duration_minutes": 240,
  "callback_url": "https://example-hospital.com/opescare/consent-callback"
}
```

Example response:

```json
{
  "status": "pending",
  "consent_request_id": "crq_01HF...",
  "expires_at": "2026-05-17T14:30:00Z",
  "message": "Consent request sent to patient or guardian."
}
```

### 9.2 Consent Verification

Endpoint:

```text
POST /api/v1/connect/consents/verify
```

Request:

```json
{
  "consent_request_id": "crq_01HF...",
  "health_id": "OC-CMR-7KQ9-MP42-X8D1"
}
```

Response:

```json
{
  "status": "granted",
  "consent_grant_id": "cgt_01HF...",
  "scopes": [
    "patient.summary",
    "allergies.read",
    "medications.read"
  ],
  "expires_at": "2026-05-17T18:30:00Z"
}
```

### 9.3 Consent Revocation

Mobile API endpoint:

```text
POST /api/mobile/consents/{id}/revoke
```

Connect API should receive webhook notification:

```text
consent.revoked
```

Once revoked, future pulls must fail unless emergency policy applies.

---

## 10. Patient Search API

Patient search must be privacy-safe.

Endpoint:

```text
POST /api/v1/connect/patients/search
```

Supported search methods:

```text
health_id
qr_reference
phone
name_date_of_birth
local_facility_patient_id
insurance_number
```

Request:

```json
{
  "search_type": "health_id",
  "query": "OC-CMR-7KQ9-MP42-X8D1",
  "purpose": "treatment",
  "requesting_user": {
    "external_user_id": "DR-1002",
    "name": "Dr. Example",
    "role": "doctor"
  }
}
```

Response for exact match:

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

Response for possible match:

```json
{
  "status": "possible_matches",
  "message": "Multiple possible patients found. Confirm identity before continuing.",
  "candidates": [
    {
      "candidate_id": "cand_01HF...",
      "display_name": "John D.",
      "sex": "male",
      "year_of_birth": 1990,
      "verification_status": "verified_by_facility"
    }
  ],
  "next_action": "confirm_patient_identity"
}
```

Privacy rule:

Search results must not show:

- diagnoses
- lab results
- prescriptions
- full address
- full phone number unless policy allows
- full medical history
- sensitive flags

---

## 11. Pull API

Pull APIs allow approved systems to retrieve scoped data.

### 11.1 Pull Patient Summary

Endpoint:

```text
GET /api/v1/connect/patients/{health_id}/summary
```

Required headers:

```text
Authorization: Bearer <token>
X-Purpose-Of-Use: treatment
X-Consent-Grant-Id: cgt_01HF...
X-Correlation-Id: req_001
```

Response:

```json
{
  "health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "summary_generated_at": "2026-05-17T10:30:00Z",
  "source": "OpesCare",
  "verification_status": "verified_by_facility",
  "sections": {
    "demographics": {
      "display_name": "John D.",
      "sex": "male",
      "date_of_birth": "1990-04-12"
    },
    "allergies": [
      {
        "substance": "Penicillin",
        "severity": "severe",
        "status": "active",
        "source_facility": "Facility A"
      }
    ],
    "active_medications": [],
    "recent_lab_results": [],
    "recent_visits": []
  },
  "access": {
    "consent_grant_id": "cgt_01HF...",
    "purpose": "treatment",
    "expires_at": "2026-05-17T18:30:00Z"
  }
}
```

### 11.2 Pull Emergency Profile

Endpoint:

```text
GET /api/v1/connect/patients/{health_id}/emergency-profile
```

Required headers:

```text
X-Purpose-Of-Use: emergency
X-Emergency-Reason: unconscious patient requiring urgent care
```

Emergency profile returns only:

- patient identity
- blood group
- critical allergies
- chronic conditions
- high-risk medications
- emergency contacts
- safety warnings

It must not return full medical history by default.

### 11.3 Pull Lab Results

Endpoint:

```text
GET /api/v1/connect/patients/{health_id}/lab-results
```

Filters:

```text
date_from
date_to
test_code
facility_id
recent_only
```

### 11.4 Pull Medication History

Endpoint:

```text
GET /api/v1/connect/patients/{health_id}/medications
```

Returns active/recent medications only according to scope.

### 11.5 Pull Referral Package

Endpoint:

```text
GET /api/v1/connect/referrals/{referral_id}/package
```

Requires referral access grant.

---

## 12. Push API

Push APIs allow approved systems to send new records to OpesCare.

Every write request must include:

```text
Authorization
Idempotency-Key
X-Correlation-Id
X-Source-System
X-Purpose-Of-Use
```

### 12.1 Push Encounter

Endpoint:

```text
POST /api/v1/connect/records/encounters
```

Request:

```json
{
  "health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "external_patient_reference": "HOSP-12345",
  "external_encounter_id": "ENC-9001",
  "facility_reference": "FAC-001",
  "provider": {
    "external_user_id": "DR-1002",
    "name": "Dr. Example",
    "role": "doctor"
  },
  "encounter": {
    "type": "outpatient",
    "started_at": "2026-05-17T09:00:00Z",
    "ended_at": "2026-05-17T09:45:00Z",
    "chief_complaint": "Fever and headache",
    "diagnoses": [
      {
        "code": "R50",
        "system": "ICD-10",
        "display": "Fever",
        "status": "active"
      }
    ],
    "notes": "Patient examined. Follow-up recommended."
  }
}
```

Response:

```json
{
  "status": "accepted",
  "opescare_record_id": "enc_01HF...",
  "sync_status": "synced",
  "timeline_event_id": "tle_01HF..."
}
```

If patient match is uncertain:

```json
{
  "status": "pending_reconciliation",
  "reconciliation_case_id": "rec_01HF...",
  "message": "Patient match requires review before this record can appear on the timeline."
}
```

### 12.2 Push Lab Result

Endpoint:

```text
POST /api/v1/connect/records/lab-results
```

Request must include:

- health_id or patient reference
- external_lab_order_id
- specimen ID where available
- test name/code
- result value
- unit
- reference range
- abnormal flag
- validation status
- released_at
- validator information
- source facility
- idempotency key

Released lab results must not be silently overwritten.

Corrections must use amendment endpoint:

```text
POST /api/v1/connect/records/lab-results/{id}/amendments
```

### 12.3 Push Prescription

Endpoint:

```text
POST /api/v1/connect/records/prescriptions
```

Request includes:

- medication
- dose
- route
- frequency
- duration
- prescriber
- status
- issued_at
- source facility

### 12.4 Push Dispense Event

Endpoint:

```text
POST /api/v1/connect/records/dispense-events
```

Request includes:

- prescription reference
- medication
- quantity dispensed
- batch reference where available
- pharmacist
- pharmacy facility
- dispensed_at

### 12.5 Push Document

Endpoint:

```text
POST /api/v1/connect/documents
```

Use multipart upload or pre-signed upload flow.

Documents are unverified by default unless trusted facility and workflow allow verification.

### 12.6 Push Referral

Endpoint:

```text
POST /api/v1/connect/referrals
```

Creates or updates referral package according to consent and facility policy.

---

## 13. Medication Availability API

### 13.1 Search Medication Availability

> **Not implemented as of 2026-09-02.** No route matches `GET /api/v1/connect/availability/medications/search` anywhere in `apps/api-laravel/routes/`. The medicine search that exists today is `GET /api/v1/care-map/pharmacies/medicine-search` (`CareMapController::searchMedicine`), which takes `medicine` (required), `latitude`, `longitude`, `radius` and answers `{ status, data, meta }` — not the parameters or response shape below. The section is retained as design intent, not as a contract a partner can call.


Endpoint:

```text
GET /api/v1/connect/availability/medications/search
```

Query parameters:

```text
q
generic_name
brand_name
strength
form
latitude
longitude
city
radius_km
verified_only
available_only
open_now
prescription_required
```

Response:

```json
{
  "query": "amoxicillin",
  "results": [
    {
      "pharmacy_id": "fac_123",
      "pharmacy_name": "Verified Pharmacy",
      "distance_km": 2.4,
      "availability_status": "available",
      "last_updated_at": "2026-05-17T10:20:00Z",
      "verified": true,
      "prescription_required": true,
      "opening_status": "open",
      "reservation_available": true,
      "price": {
        "amount": 2500,
        "currency": "XAF"
      }
    }
  ],
  "safety_note": "Medicine availability can change. Contact the pharmacy or reserve before travelling."
}
```

### 13.2 Pharmacy Stock Sync

> **Checked against the running code on 2026-09-02.**
> Source of truth: `apps/api-laravel/app/Http/Controllers/Api/V1/Connect/InventoryController.php::syncPharmacyStock()`
> and `apps/api-laravel/app/Modules/Connect/Services/PartnerStockIngestService.php`.
> Every field below is one the validator enforces. The body published in this
> section before that date (`external_item_id`, `generic_name`, `brand_name`,
> `strength`, `form`, `quantity_available`, `batch_number`, `status`,
> `prescription_required`, `updated_at`) was never accepted by the API — see
> §13.2.8. Until this revision the endpoint also validated a request and then
> wrote nothing; it now persists real stock that reaches the public Medicine
> Finder, so a wrong body is a wrong shelf, not a no-op.

Endpoint:

```text
POST /api/v1/connect/inventory/pharmacy-stock/sync
```

#### 13.2.1 Authentication and headers

```text
Authorization: Bearer <RS256 JWT>      required
Content-Type: application/json         required
Idempotency-Key: <opaque string>       optional
X-Correlation-Id: <string>             optional
```

The facility whose shelf is written is taken **only** from the bearer token
(`facility_id` resolved by the auth middleware). A token that carries no
facility scope is refused with `403 FACILITY_UNRESOLVABLE`; there is no way to
sync stock for another facility.

Unlike the `/records/*` writes described in §15, `Idempotency-Key` is
**optional** on this endpoint — it is honoured when sent (replay, and `409` on
key reuse with a different body) but its absence is not an error. The writes are
natural-key upserts on `(medicine_id, care_facility_id)` taken under a row lock,
so a repeat sync updates in place with or without the header.

When `X-Correlation-Id` is absent the API generates one (`req_` + 16 hex
characters) and returns it in every response body.

#### 13.2.2 Request fields

| Field | Type | Required | Validator rule | Notes |
|---|---|---|---|---|
| `facility_reference` | string | **required** | `required, string, max:100` | The partner's own reference for the pharmacy. Informational only — it is echoed back and recorded in the audit event, and is **never** used to select or authorise a facility. |
| `items` | array | **required** | `required, array, min:1` | At least one item. |
| `items[].drug_code` | string | **required** | `required, string` | Resolved against the OpesCare `medicines` catalogue: either the catalogue medicine **UUID** or a **WHO ATC code** (matched case-insensitively). See §13.2.5. |
| `items[].quantity` | integer | **required** | `required, integer, min:0` | Packs currently on the shelf. `0` is meaningful and is stored as `out_of_stock`. |
| `items[].expiry_date` | date | optional | `nullable, date` | Safety gate only (§13.2.4). It is **not** stored on the stock row. |
| `items[].stock_status` | string | optional | `nullable, string, in: in_stock, low_stock, out_of_stock, unknown` | Overrides the quantity-derived status when the partner keeps its own availability state. |
| `items[].pack_size` | string | optional | `nullable, string, max:100` | Free text, e.g. `"box of 20"`. |
| `items[].unit_price` | number | optional | `nullable, numeric, min:0` | Currency is **not** part of the payload: it is taken from the catalogue medicine (XAF). |

Any other key inside an item is ignored — only the validated keys are read, so
an unknown field is dropped silently rather than rejected.

Request:

```json
{
  "facility_reference": "PHARM-001",
  "items": [
    {
      "drug_code": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
      "quantity": 120,
      "expiry_date": "2027-01-31",
      "pack_size": "box of 20",
      "unit_price": 2500
    },
    {
      "drug_code": "C01AA05",
      "quantity": 4,
      "stock_status": "low_stock"
    }
  ]
}
```

```bash
curl -X POST https://api.opescare.com/api/v1/connect/inventory/pharmacy-stock/sync \
  -H "Authorization: Bearer $OPESCARE_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: 4f5b2fd8-b16e-4c66-9fa0-620af5b6c901" \
  -d '{
        "facility_reference": "PHARM-001",
        "items": [
          { "drug_code": "C01AA05", "quantity": 120, "expiry_date": "2027-01-31" }
        ]
      }'
```

#### 13.2.3 How a reported quantity becomes a published status

When `stock_status` is omitted, the pack count decides — the same thresholds the
pharmacy portal uses, so the dispensing ledger and the public finder never
disagree about what "low" means:

```text
quantity = 0        -> out_of_stock
quantity 1 .. 10    -> low_stock
quantity >= 11      -> in_stock
```

An explicit `stock_status` always wins.

Two further rules apply to every row a partner writes:

- `source_system` is stamped `partner_api:<integration_client_id>` (or
  `partner_api` when the middleware could not name the client) and returned as
  `source_system`. The public finders withhold seeded and unattributed stock, so
  this provenance is what makes a partner's report reachable by a patient.
- `reservation_enabled` is always `false`. Counter reservation is a workflow a
  pharmacy opts into through the portal; a partner feed cannot honour it.

#### 13.2.4 Expired stock — the batch is refused whole

Every item carrying an `expiry_date` in the past is collected, and the **entire
batch is rejected without writing anything**. A batch carrying expired stock is
not partially trustworthy.

```json
{
  "status": "rejected",
  "error_code": "UNSAFE_STOCK_STATUS",
  "message": "Expired stock synchronisation blocked. Remove the listed items and retry.",
  "expired_items": [
    { "index": 0, "drug_code": "C01AA05", "expiry_date": "2024-01-31" }
  ],
  "correlation_id": "req_9f2c1d4b6a8e0c37"
}
```

HTTP status: `422`.

#### 13.2.5 Per-item outcomes

Items are resolved against the catalogue individually; nothing is ever dropped
in silence. Every submitted line comes back either counted (created or updated)
or listed in `rejected_items` with a machine-readable `reason`:

| `reason` | When | What the partner must change |
|---|---|---|
| `unknown_drug_code` | The code matches no active medicine in the OpesCare catalogue. | Send the catalogue medicine UUID, or a WHO ATC code that exists in the catalogue. |
| `ambiguous_drug_code` | The ATC code matches **more than one** active catalogue medicine. | Send the catalogue medicine UUID instead of the ATC code. |
| `pharmacy_listing_unlinked` | No public pharmacy listing is linked to the authenticated facility. Applied to **every** item in the batch. | Contact OpesCare support to link the listing; reported stock would otherwise reach no patient. |

`ambiguous_drug_code` is not an edge case. ATC is not unique in this catalogue:
**18 ATC codes in the 419-medicine catalogue map to more than one active row**
(counted on 2026-09-02), because they are strength or form variants of the same
molecule — `N02AA01` is both *Morphine Sulfate 10mg Tablet* and *Morphine
Sulfate 10mg/mL Injection*, and `J01CA04` (the amoxicillin used in this
document's previous example) is both the *500mg Capsule* and the *250mg/5ml Oral
Suspension*. The API reports those items back rather than guessing: guessing
would file a pharmacy's 500mg capsules against the oral-suspension catalogue row
and publish it to patients as fact. **A partner keying its feed on ATC codes
should expect these rejections and map the affected products to catalogue UUIDs
once.**

Each entry of `rejected_items` has the shape:

```json
{
  "index": 2,
  "drug_code": "N02AA01",
  "reason": "ambiguous_drug_code",
  "message": "drug_code matches more than one catalogue medicine, so the right one cannot be chosen. Send the catalogue medicine UUID instead of the ATC code."
}
```

`index` is the zero-based position in the submitted `items` array. `message` is
localized (EN/FR); `reason` is the stable value to branch on.

#### 13.2.6 Warnings — stored, but still not visible to patients

`warnings` is a list of listing-level codes. A warning is **not** a rejection:
the stock was written correctly, but the public listing still prevents patients
from seeing it.

| Warning | Meaning |
|---|---|
| `not_listed` | The linked pharmacy listing is not `active`, so the Medicine Finder excludes it. |
| `no_coordinates` | The listing has no latitude/longitude; every finder query is geographic, so it can never appear in a result. |
| `unlinked` | No public listing is linked at all. Accompanies the batch-wide `pharmacy_listing_unlinked` rejection. |

#### 13.2.7 Responses

**`200 OK` — at least one item reached the database**

```json
{
  "status": "synced",
  "facility_id": "8c1f0c1e-2f5a-4a2b-9d3e-77b0a4c2e911",
  "facility_reference": "PHARM-001",
  "source_system": "partner_api:his_client_042",
  "submitted_count": 3,
  "accepted_count": 1,
  "updated_count": 1,
  "rejected_count": 1,
  "synced_items_count": 2,
  "rejected_items": [
    {
      "index": 2,
      "drug_code": "N02AA01",
      "reason": "ambiguous_drug_code",
      "message": "drug_code matches more than one catalogue medicine, so the right one cannot be chosen. Send the catalogue medicine UUID instead of the ATC code."
    }
  ],
  "warnings": ["no_coordinates"],
  "timestamp": 1788307200,
  "correlation_id": "req_9f2c1d4b6a8e0c37"
}
```

| Field | Meaning |
|---|---|
| `accepted_count` | Rows **created**. |
| `updated_count` | Rows **updated** in place. |
| `rejected_count` | Length of `rejected_items`. |
| `synced_items_count` | `accepted_count + updated_count` — the number of items that genuinely reached the database, not the batch size. |
| `source_system` | The provenance stamped on every row written by this call. |
| `timestamp` | Unix epoch seconds (integer). |

**`422 Unprocessable Entity` — nothing could be stored**

If every submitted item was rejected, the response carries the **same body** with
`status: "rejected"` plus an `error_code` and `message`, and the HTTP status is
`422`. A success response is never returned for a batch that wrote nothing.

```json
{
  "status": "rejected",
  "error_code": "VALIDATION_FAILED",
  "message": "No submitted item could be stored, so nothing was written. See rejected_items for the reason on each line.",
  "submitted_count": 2,
  "accepted_count": 0,
  "updated_count": 0,
  "rejected_count": 2,
  "synced_items_count": 0,
  "rejected_items": [ "..." ],
  "warnings": ["unlinked"],
  "correlation_id": "req_9f2c1d4b6a8e0c37"
}
```

**Every status code this endpoint returns**

| Status | `error_code` | Cause |
|---|---|---|
| `200` | — | At least one item created or updated. |
| `403` | `FACILITY_UNRESOLVABLE` | The bearer token carries no facility scope. |
| `409` | `IDEMPOTENCY_CONFLICT` | The same `Idempotency-Key` was reused with a different body. |
| `422` | `VALIDATION_FAILED` | Body failed validation (missing `drug_code`, missing `quantity`, empty `items`, …). Envelope: `{ "status": "error", "error_code": "VALIDATION_FAILED", "message": "The request data failed validation.", "errors": { … } }`. |
| `422` | `VALIDATION_FAILED` | Body was valid, but **no item could be stored** (see above — this variant carries the full count/rejection body). |
| `422` | `UNSAFE_STOCK_STATUS` | One or more items carried a past `expiry_date`; the whole batch was refused. |

A replayed `Idempotency-Key` returns the stored response verbatim with the
header `X-Cache-Idempotency: HIT`. Only `200` responses are stored for replay.

Each call emits the audit event `pharmacy_stock_synced` carrying
`items_count`, `created_rows`, `updated_rows`, `rejected_rows`, `source_system`
and `facility_reference`.

#### 13.2.8 Fields removed from this specification on 2026-09-02

These were published here but have never existed in the endpoint. They are not
aliases and they are not deprecated — sending them has no effect, and a body
built from the old specification fails validation because it carries no
`drug_code` and no `quantity`.

| Removed field | Status in the API | Closest supported field |
|---|---|---|
| `external_item_id` | Not accepted. | — (identify the product with `drug_code`) |
| `generic_name` | Not accepted. | `drug_code` (catalogue UUID or ATC code) |
| `brand_name` | Not accepted. | `drug_code` |
| `strength` | Not accepted. | `drug_code` — strength is a property of the catalogue medicine |
| `form` | Not accepted. | `drug_code` |
| `quantity_available` | Not accepted. | `quantity` |
| `batch_number` | Not accepted. | — (batch/lot is not modelled on reported stock) |
| `status` | Not accepted. | `stock_status`, with the enum values listed in §13.2.2 |
| `prescription_required` | Not accepted. | — (a catalogue property, not a per-facility report) |
| `updated_at` (top level) | Not accepted. | — (the server stamps `last_reported_at` itself) |

The previously published "Rules" list is restated against the behaviour that
exists:

- **expired stock excluded** — enforced, and stricter than "excluded": the whole
  batch is refused (§13.2.4).
- **recalled stock excluded / quarantined stock excluded** — **not implemented
  on this endpoint.** There is no recall or quarantine field in the payload and
  no such check in the controller. Partners remain contractually required to
  withhold recalled or quarantined stock, but the API cannot detect it.
- **stale update marked stale** — freshness is stamped server-side
  (`last_reported_at` per row, `last_availability_update_at` per listing) and
  the finder surfaces it; the sync endpoint itself returns no staleness field.
- **unverified pharmacy not shown publicly** — enforced downstream by the
  finder, and reported here through the `warnings` list (§13.2.6).

### 13.3 Medication Reservation

> **Not implemented as of 2026-09-02.** No route matches `POST /api/v1/connect/availability/medications/reservations` in `apps/api-laravel/routes/`. Design intent only.


Endpoint:

```text
POST /api/v1/connect/availability/medications/reservations
```

Reservation must expire.

---

## 14. Blood Availability API

### 14.1 Search Blood Availability

> **Not implemented as of 2026-09-02.** No route matches `GET /api/v1/connect/availability/blood/search` in `apps/api-laravel/routes/`. The blood search that exists today is `GET /api/v1/care-map/blood/search` (`CareMapController::searchBlood`), which requires `blood_group` and answers `{ status, data, meta }` — not the response shape below. Design intent only.


Endpoint:

```text
GET /api/v1/connect/availability/blood/search
```

Query parameters:

```text
blood_group
component
latitude
longitude
city
radius_km
verified_only
available_only
urgency
```

Response:

```json
{
  "blood_group": "O+",
  "component": "packed_red_cells",
  "results": [
    {
      "facility_id": "blood_fac_001",
      "facility_name": "Verified Blood Bank",
      "distance_km": 3.1,
      "availability_status": "available",
      "usable_units": 4,
      "last_updated_at": "2026-05-17T10:15:00Z",
      "verified": true,
      "contact_required": true
    }
  ],
  "safety_note": "Blood transfusion must be handled by qualified healthcare professionals."
}
```

### 14.2 Blood Stock Sync

> **Checked against the running code on 2026-09-02.**
> Source of truth: `apps/api-laravel/app/Http/Controllers/Api/V1/Connect/InventoryController.php::syncBloodStock()`
> and `apps/api-laravel/app/Modules/Connect/Services/PartnerStockIngestService.php`.
> `blood_group` is now **required** — a blood inventory row is keyed on
> (facility, blood group, component), so a payload without it could not be
> stored at all. `component` was renamed `component_code` and constrained to the
> operational vocabulary; `usable_units` was renamed `units`. See §14.2.7.

Endpoint:

```text
POST /api/v1/connect/inventory/blood-stock/sync
```

#### 14.2.1 Authentication and headers

Identical to §13.2.1: Bearer JWT, facility taken **only** from the token
(`403 FACILITY_UNRESOLVABLE` when the token carries no facility scope), optional
`Idempotency-Key`, optional `X-Correlation-Id` (generated when absent).

#### 14.2.2 Request fields

| Field | Type | Required | Validator rule | Notes |
|---|---|---|---|---|
| `facility_reference` | string | **required** | `required, string, max:100` | The partner's own reference for the blood bank. Informational only — never used to select or authorise a facility. |
| `items` | array | **required** | `required, array, min:1` | |
| `items[].blood_group` | string | **required** | `required, string, in: A+, A-, B+, B-, AB+, AB-, O+, O-` | Part of the row's natural key. |
| `items[].component_code` | string | **required** | `required, string, in:` the operational component list below | Part of the row's natural key. |
| `items[].units` | integer | **required** | `required, integer, min:0` | Units currently available. `0` is meaningful and publishes as "none held". |
| `items[].screening_status` | string | **required** | `required, string` | Any string validates, but only `screened_safe` is accepted downstream — see §14.2.4. |

Accepted `component_code` values (operational spellings; several are aliases of
the same published component):

```text
whole_blood
red_cells
packed_cells
packed_red_cells
prbc
plasma
fresh_frozen_plasma
ffp
platelets
cryoprecipitate
```

`cryoprecipitate` is storable but has no patient-facing component, so it is
recorded on the operational shelf and never published to the Blood Finder.

Any other key inside an item is ignored — only the validated keys are read.

Request:

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

```bash
curl -X POST https://api.opescare.com/api/v1/connect/inventory/blood-stock/sync \
  -H "Authorization: Bearer $OPESCARE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
        "facility_reference": "BLOOD-001",
        "items": [
          { "blood_group": "O+", "component_code": "packed_red_cells",
            "units": 4, "screening_status": "screened_safe" }
        ]
      }'
```

#### 14.2.3 How a sync is stored and published

Each item upserts one operational row keyed `(facility_id, blood_group,
component)`, then re-publishes the patient-facing availability band through the
blood availability projector, so the Blood Finder stays in step with what the
bank actually holds. Every row is stamped
`source_system = partner_api:<integration_client_id>` (or `partner_api` when the
client cannot be named) — the public finder withholds seeded and unattributed
rows, so this provenance is what makes the report reachable by a patient.

Repeat syncs update in place: the natural key makes duplication impossible with
or without an `Idempotency-Key`.

#### 14.2.4 Unscreened blood — the batch is refused whole

Every item whose `screening_status` is not exactly `screened_safe` is collected,
and the **entire batch is rejected without writing anything**.

```json
{
  "status": "rejected",
  "error_code": "UNSAFE_BLOOD_STATUS",
  "message": "Unscreened or unsafe blood component sync is forbidden. Remove the listed items and retry.",
  "unsafe_items": [
    { "index": 1, "component_code": "plasma", "screening_status": "pending" }
  ],
  "correlation_id": "req_9f2c1d4b6a8e0c37"
}
```

HTTP status: `422`.

Because the batch is all-or-nothing, everything that reaches storage has passed
the screening gate, and `is_unsafe` is written as `false` on every row.

#### 14.2.5 Warnings

| Warning | Meaning |
|---|---|
| `blood_listing_unlinked` | No public listing is linked to the authenticated facility. The operational record is stored and correct, but no patient can find it. Contact OpesCare support to link the listing. |

This is a warning, not a rejection: unlike pharmacy stock, blood units are
stored on the facility's own operational shelf whether or not a public listing
exists.

#### 14.2.6 Responses

**`200 OK`**

```json
{
  "status": "synced",
  "facility_id": "8c1f0c1e-2f5a-4a2b-9d3e-77b0a4c2e911",
  "facility_reference": "BLOOD-001",
  "source_system": "partner_api:his_client_042",
  "submitted_count": 2,
  "accepted_count": 1,
  "updated_count": 1,
  "rejected_count": 0,
  "synced_components_count": 2,
  "stored_rows": 2,
  "rejected_items": [],
  "warnings": [],
  "timestamp": 1788307200,
  "correlation_id": "req_9f2c1d4b6a8e0c37"
}
```

| Field | Meaning |
|---|---|
| `accepted_count` | Rows **created**. |
| `updated_count` | Rows **updated** in place. |
| `rejected_count` / `rejected_items` | Always `0` / `[]` on this endpoint today: an item that passes validation and the screening gate always resolves to a storable row. The fields exist so the two sync endpoints answer with the same shape. |
| `synced_components_count` | The number of items submitted. |
| `stored_rows` | `accepted_count + updated_count`. |
| `timestamp` | Unix epoch seconds (integer). |

**Every status code this endpoint returns**

| Status | `error_code` | Cause |
|---|---|---|
| `200` | — | The batch was stored. |
| `403` | `FACILITY_UNRESOLVABLE` | The bearer token carries no facility scope. |
| `409` | `IDEMPOTENCY_CONFLICT` | The same `Idempotency-Key` was reused with a different body. |
| `422` | `VALIDATION_FAILED` | Body failed validation (missing `blood_group`, unknown `component_code`, missing `units`, …). Envelope: `{ "status": "error", "error_code": "VALIDATION_FAILED", "message": "The request data failed validation.", "errors": { … } }`. |
| `422` | `UNSAFE_BLOOD_STATUS` | One or more items were not `screened_safe`; the whole batch was refused. |

There is no "nothing stored" 422 on this endpoint — that outcome cannot arise
here, because a validated, screened item always writes.

A replayed `Idempotency-Key` returns the stored response verbatim with the
header `X-Cache-Idempotency: HIT`. Only `200` responses are stored for replay.

Each call emits the audit event `blood_stock_synced` carrying
`components_count`, `stored_rows`, `created_rows`, `updated_rows`,
`source_system` and `facility_reference`.

#### 14.2.7 Fields changed or removed on 2026-09-02

| Previously published | Status in the API | Replacement |
|---|---|---|
| `component` | Not accepted. | `component_code`, restricted to the list in §14.2.2 |
| `usable_units` | Not accepted. | `units` |
| `reserved_units` | Not accepted. | — (reservation is not modelled on a partner sync) |
| `expiry_date` | Not accepted **on this endpoint**. | — (the pharmacy endpoint accepts one as a safety gate; the blood endpoint does not) |
| `status` | Not accepted. | — (availability is derived from `units`) |
| `updated_at` (top level) | Not accepted. | — (the server stamps `last_stock_update` itself) |
| *(absent)* | **`blood_group` is now required.** | Without it a row cannot be keyed, which is why this endpoint previously stored nothing. |

The previously published "Rules" list restated against the behaviour that
exists:

- **unsafe units excluded** — enforced, and stricter than "excluded": the whole
  batch is refused (§14.2.4).
- **expired units excluded / quarantined units excluded** — **not implemented on
  this endpoint.** There is no expiry or quarantine field in the payload and no
  such check in the controller. `is_expired` and `is_quarantined` exist on the
  operational record but are set elsewhere — `PATCH /api/v1/inventory/blood/{item}/flags`
  on the staff-facing inventory API, or the facility's inventory portal — never
  by a partner sync. Partners remain contractually required to withhold expired
  or quarantined units.
- **unverified sources excluded** — enforced downstream by the finder's
  provenance gate, and reported here through `warnings` (§14.2.5).
- **patient identity never shown publicly** — unchanged: this payload carries no
  patient identifier of any kind.

### 14.3 Create Blood Need Request

> **Not implemented as of 2026-09-02.** No route matches `POST /api/v1/connect/availability/blood/needs` in `apps/api-laravel/routes/`. Design intent only.


Endpoint:

```text
POST /api/v1/connect/availability/blood/needs
```

Request:

```json
{
  "facility_reference": "HOSP-001",
  "blood_group": "O+",
  "component": "packed_red_cells",
  "units_needed": 3,
  "urgency": "urgent",
  "expires_at": "2026-05-17T18:00:00Z",
  "contact_department": "Emergency Department"
}
```

No patient identity should be publicly exposed.

### 14.4 Blood Reservation and Transfer

> **Not implemented as of 2026-09-02.** None of the three routes below exist in `apps/api-laravel/routes/`. Design intent only.


Endpoints:

```text
POST /api/v1/connect/availability/blood/reservations
POST /api/v1/connect/availability/blood/transfers
POST /api/v1/connect/availability/blood/transfers/{id}/receipt
```

Transfer must log chain-of-custody.

---

## 15. Idempotency and Duplicate Protection

All write endpoints must require `Idempotency-Key`.

Required behavior:

```text
same key + same request body = return original response
same key + different request body = 409 IDEMPOTENCY_CONFLICT
missing key on write = 400 IDEMPOTENCY_KEY_REQUIRED
```

Recommended headers:

```text
Idempotency-Key: 4f5b2fd8-b16e-4c66-9fa0-620af5b6c901
X-Correlation-Id: req-2026-0001
```

Store:

```text
idempotency_key
client_id
endpoint
request_hash
response_status
response_body
created_at
expires_at
```

---

## 16. Reconciliation Model

Reconciliation is required when OpesCare cannot safely accept data.

Cases include:

```text
patient_not_found
multiple_patient_matches
identity_conflict
invalid_payload
facility_mismatch
duplicate_suspicion
idempotency_conflict
unmapped_code
document_mismatch
unsafe_blood_stock
unsafe_medication_stock
```

Endpoint:

```text
GET /api/v1/connect/reconciliation/cases
GET /api/v1/connect/reconciliation/cases/{id}
POST /api/v1/connect/reconciliation/cases/{id}/resolve
```

Resolution options:

```text
attach_to_confirmed_patient
create_provisional_patient
reject_record
request_correction
mark_duplicate
escalate
```

Rules:

- unresolved records must not appear on patient timeline
- every resolution audited
- reviewer cannot bypass Patient Identity Service
- patient safety cases get higher priority

---

## 17. Webhooks

Webhooks notify external systems about important events.

### 17.1 Webhook Events

Core events:

```text
patient.created
patient.updated
patient.merged
patient.deceased
consent.requested
consent.granted
consent.denied
consent.revoked
emergency_access.used
encounter.created
lab_result.released
lab_result.amended
prescription.issued
prescription.cancelled
medication.dispensed
referral.created
referral.accepted
claim.submitted
claim.approved
pharmacy_stock.synced
pharmacy_stock.stale
blood_stock.synced
blood_need.created
blood_need.fulfilled
sync.failed
reconciliation.created
reconciliation.resolved
security.suspicious_access
```

### 17.2 Webhook Payload Rule

Webhook payloads must be minimal.

Do not include full patient records by default.

Example:

```json
{
  "event_id": "evt_01HF...",
  "event_type": "lab_result.released",
  "occurred_at": "2026-05-17T10:00:00Z",
  "resource": {
    "type": "lab_result",
    "id": "lab_01HF..."
  },
  "patient": {
    "health_id_reference": "OC-CMR-****-X8D1"
  },
  "facility_id": "FAC-001"
}
```

External system must call API to retrieve details with authorization.

### 17.3 Webhook Security

Webhooks must include signature:

```text
X-OpesCare-Signature
X-OpesCare-Timestamp
X-OpesCare-Event-Id
```

Signature:

```text
HMAC-SHA256(timestamp + "." + raw_body, webhook_secret)
```

Rules:

- verify timestamp freshness
- prevent replay
- rotate webhook secrets
- retry failed delivery
- use dead-letter queue
- allow webhook pause/resume

### 17.4 Webhook Delivery States

```text
pending
delivered
failed
retrying
dead_letter
paused
```

---

## 18. Alerts and Notifications

Alerts are internal platform events that require attention.

Alert categories:

```text
clinical_safety
security
sync_failure
reconciliation
inventory
blood_availability
medication_availability
consent
emergency_access
billing
integration
```

Alert severity:

```text
info
warning
danger
critical
```

Examples:

```text
critical lab result released
emergency access used
blood stock stale
pharmacy stock sync failed
patient match uncertain
webhook delivery failed
facility API credential expiring
suspicious access detected
```

Alert rules:

- critical alerts require acknowledgement
- clinical alerts must be visible to appropriate users
- alerts must not expose sensitive information to unauthorized users
- alert delivery must be audited

---

## 19. SDK Design

The SDK must simplify integration but never hide important errors.

Initial SDKs:

```text
PHP SDK
TypeScript SDK
Python SDK
```

Future SDKs:

```text
Java SDK
C#/.NET SDK
```

### 19.1 SDK Core Features

Every SDK should provide:

```text
client initialization
token management
request signing where needed
idempotency helper
structured error handling
retry policy
webhook signature verification
pagination support
sandbox/production environment selection
typed request/response models
logging hooks without PHI
```

### 19.2 SDK Methods

Recommended SDK methods:

```text
authenticate()
searchPatient()
requestConsent()
verifyConsent()
pullPatientSummary()
pullEmergencyProfile()
pushEncounter()
pushLabResult()
amendLabResult()
pushPrescription()
pushDispenseEvent()
pushDocument()
syncPharmacyStock()
searchMedicationAvailability()
createMedicationReservation()
syncBloodStock()
searchBloodAvailability()
createBloodNeedRequest()
createBloodReservation()
createBloodTransfer()
getSyncStatus()
listReconciliationCases()
resolveReconciliationCase()
createWebhookSubscription()
verifyWebhookSignature()
```

### 19.3 PHP SDK Example

```php
$client = new OpesCare\Client([
    'client_id' => getenv('OPESCARE_CLIENT_ID'),
    'client_secret' => getenv('OPESCARE_CLIENT_SECRET'),
    'environment' => 'sandbox',
]);

$result = $client->patients()->search([
    'search_type' => 'health_id',
    'query' => 'OC-CMR-7KQ9-MP42-X8D1',
    'purpose' => 'treatment',
]);

$consent = $client->consents()->request([
    'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
    'purpose' => 'treatment',
    'requested_scopes' => ['patient.summary', 'allergies.read'],
]);
```

### 19.4 TypeScript SDK Example

```ts
const client = new OpesCareClient({
  clientId: process.env.OPESCARE_CLIENT_ID!,
  clientSecret: process.env.OPESCARE_CLIENT_SECRET!,
  environment: "sandbox",
});

const patient = await client.patients.search({
  searchType: "health_id",
  query: "OC-CMR-7KQ9-MP42-X8D1",
  purpose: "treatment",
});

const result = await client.records.pushEncounter({
  healthId: "OC-CMR-7KQ9-MP42-X8D1",
  externalEncounterId: "ENC-9001",
  encounter: {
    type: "outpatient",
    startedAt: "2026-05-17T09:00:00Z",
  },
});
```

### 19.5 Python SDK Example

```python
client = OpesCareClient(
    client_id=os.environ["OPESCARE_CLIENT_ID"],
    client_secret=os.environ["OPESCARE_CLIENT_SECRET"],
    environment="sandbox",
)

patient = client.patients.search(
    search_type="health_id",
    query="OC-CMR-7KQ9-MP42-X8D1",
    purpose="treatment",
)
```

### 19.6 SDK Error Handling

SDK must expose structured errors:

```text
AuthenticationError
AuthorizationError
ConsentRequiredError
ValidationError
IdempotencyConflictError
RateLimitError
ReconciliationRequiredError
FacilitySuspendedError
ServerError
```

SDK must not convert all errors into generic exceptions.

---

## 20. Connect Widget Design

The Connect Widget is for hospitals or systems that cannot deeply integrate quickly.

### 20.1 Widget Functions

Widget should support:

```text
patient search
QR scan
request consent
verify consent
pull patient summary
pull emergency profile
push current visit summary
push lab result
push prescription
upload document
view sync status
create reconciliation case
```

### 20.2 Widget Session Flow

1. Hospital backend requests widget session.
2. OpesCare validates integration client.
3. OpesCare returns short-lived widget token.
4. Widget loads in browser.
5. Staff performs permitted action.
6. Widget sends audit metadata.
7. Session expires.

### 20.3 Widget Security

Rules:

- no permanent API secrets in frontend
- short-lived token
- facility-bound
- user-bound where possible
- allowed actions scoped
- patient context scoped
- CSRF and origin checks
- iframe origin allowlist
- audit all actions

### 20.4 Widget UI Rules

Widget must use OpesCare clear language, colors, and Lucide icons.

Widget states:

```text
loading
ready
patient_selected
consent_required
consent_pending
access_granted
sync_success
sync_failed
reconciliation_required
session_expired
```

---

## 21. Bridge Agent Design

Bridge Agent connects older systems.

### 21.1 Supported Sources

Bridge Agent should support:

```text
CSV exports
Excel exports
JSON files
XML files
folder watch
SFTP pull/push
local database read-only connector where approved
manual upload
scheduled export import
```

### 21.2 Bridge Agent Components

```text
configuration UI
credential manager
field mapper
file watcher
local queue
validation engine
encryption layer
sync worker
retry engine
error dashboard
update mechanism
logs without PHI
```

### 21.3 Bridge Agent Flow

1. Facility installs Bridge Agent.
2. Agent is registered to OpesCare.
3. Credentials are issued.
4. Source system/export folder is configured.
5. Field mapping is completed.
6. Test sync runs in sandbox.
7. Facility reviews mapping.
8. Production sync is approved.
9. Agent watches for new data.
10. Agent validates data.
11. Agent queues data locally.
12. Agent sends data to Connect API.
13. Failed records are shown in dashboard.
14. Reconciliation cases are created where required.

### 21.4 Bridge Agent Security

Rules:

- local queue encrypted
- credentials encrypted
- no plaintext patient logs
- least privilege
- signed updates
- remote revocation
- audit sync events
- facility-specific credentials
- environment separation
- PHI redaction in technical logs

### 21.5 Bridge Agent Statuses

```text
installed
configured
sandbox_testing
active
offline
syncing
sync_failed
credential_expired
revoked
update_required
```

---

## 22. Sync Engine

Sync engine must coordinate all incoming and outgoing data.

### 22.1 Sync States

```text
received
validated
queued
processing
synced
pending_reconciliation
failed
retrying
dead_letter
cancelled
```

### 22.2 Sync Dashboard

Dashboard should show:

- incoming records
- outgoing webhooks
- failed syncs
- retrying events
- reconciliation required
- stale pharmacy stock
- stale blood stock
- Bridge Agent status
- API client health
- webhook health
- last successful sync
- sync latency

### 22.3 Sync Failure Reasons

```text
authentication_failed
authorization_failed
consent_required
schema_validation_failed
patient_not_found
multiple_patient_matches
facility_suspended
idempotency_conflict
rate_limited
external_system_timeout
webhook_delivery_failed
unsafe_inventory_status
unmapped_code
server_error
```

---

## 23. API Versioning

Use explicit versioning:

```text
/api/v1/connect/...
```

Versioning rules:

- never break v1 without new version
- deprecate old endpoints with notice
- maintain changelog
- include `Sunset` header where applicable
- SDKs must track API versions
- OpenAPI docs must be versioned

Headers:

```text
OpesCare-API-Version: 2026-05-17
```

---

## 24. Rate Limits

Rate limits must be per:

- client
- facility
- endpoint
- environment
- risk level

Example policy:

```text
patient search: strict
pull summary: strict
push records: moderate
stock sync: higher but controlled
webhook subscription changes: strict
auth token requests: strict
```

Rate limit response:

```json
{
  "status": "rejected",
  "error_code": "RATE_LIMIT_EXCEEDED",
  "message": "Too many requests. Please try again later.",
  "retry_after_seconds": 60
}
```

---

## 25. Standard Error Format

All APIs must return structured errors.

```json
{
  "status": "rejected",
  "error_code": "CONSENT_REQUIRED",
  "message": "Patient consent is required before viewing this record.",
  "required_action": "request_consent",
  "correlation_id": "req_2026_0001"
}
```

Error code rules:

- stable
- English code only
- not translated
- message can be localized
- include required_action where useful

Core error codes:

```text
AUTHENTICATION_FAILED
TOKEN_EXPIRED
TOKEN_REVOKED
INSUFFICIENT_SCOPE
FACILITY_SUSPENDED
ORGANIZATION_NOT_VERIFIED
CONSENT_REQUIRED
CONSENT_EXPIRED
CONSENT_REVOKED
PURPOSE_REQUIRED
PATIENT_NOT_FOUND
MULTIPLE_PATIENT_MATCHES
RECONCILIATION_REQUIRED
VALIDATION_FAILED
IDEMPOTENCY_KEY_REQUIRED
IDEMPOTENCY_CONFLICT
RATE_LIMIT_EXCEEDED
UNSAFE_STOCK_STATUS
UNSAFE_BLOOD_STATUS
WEBHOOK_SIGNATURE_INVALID
RESOURCE_NOT_FOUND
SERVER_ERROR
```

---

## 26. Audit and Provenance

### 26.1 Audit Events

Audit everything sensitive:

```text
api_token_issued
api_token_revoked
patient_search_performed
consent_requested
consent_verified
patient_summary_pulled
emergency_profile_pulled
encounter_pushed
lab_result_pushed
lab_result_amended
prescription_pushed
dispense_event_pushed
pharmacy_stock_synced
blood_stock_synced
blood_need_created
webhook_sent
webhook_failed
reconciliation_created
reconciliation_resolved
bridge_agent_registered
widget_session_created
sdk_client_used
```

Audit event fields:

```text
actor_type
actor_id
client_id
organization_id
facility_id
patient_id where applicable
health_id_reference where applicable
action
resource_type
resource_id
purpose
scope
ip_address
user_agent
source_system
correlation_id
timestamp
result
reason
```

### 26.2 Provenance

Every external record must store:

```text
source_system
source_facility
source_user
external_record_id
submitted_at
received_at
verification_status
payload_hash
idempotency_key
```

---

## 27. OpenAPI Documentation

All external API endpoints must be documented in OpenAPI 3.1.

OpenAPI files:

```text
/contracts/openapi/opescare-connect-v1.yaml
/contracts/openapi/opescare-mobile-v1.yaml
```

Each endpoint must include:

- summary
- description
- required auth
- scopes
- request schema
- response schema
- error responses
- idempotency rules
- rate-limit notes
- examples
- privacy notes where applicable

---

## 28. Environment Strategy

Environments:

```text
local
development
sandbox
staging
production
```

Rules:

- sandbox uses fake/synthetic data only
- production credentials cannot work in sandbox
- sandbox credentials cannot work in production
- real patient data forbidden in sandbox
- test patients clearly marked fake
- API docs must show environment URLs separately

---

## 29. Certification and Go-Live Process

Before a facility or vendor gets production access:

1. Organization verified.
2. Integration use case approved.
3. Sandbox credentials issued.
4. Developer tests patient search.
5. Developer tests consent request.
6. Developer tests push/pull flows.
7. Developer tests idempotency.
8. Developer tests error handling.
9. Developer tests webhook signature verification.
10. Developer tests reconciliation.
11. Security review completed.
12. Data mapping reviewed.
13. Production credentials issued.
14. First production sync monitored.
15. Facility marked active.

---

## 30. Mobile App API Contract

Official OpesCare mobile app endpoints:

```text
POST /api/mobile/auth/login
POST /api/mobile/auth/otp/verify
POST /api/mobile/auth/refresh
POST /api/mobile/auth/logout
POST /api/mobile/devices/register
POST /api/mobile/devices/{id}/revoke

GET  /api/mobile/me
GET  /api/mobile/health-id
GET  /api/mobile/timeline
GET  /api/mobile/lab-results
GET  /api/mobile/prescriptions
GET  /api/mobile/appointments
GET  /api/mobile/consent-requests
POST /api/mobile/consent-requests/{id}/approve
POST /api/mobile/consent-requests/{id}/deny
POST /api/mobile/consents/{id}/revoke
GET  /api/mobile/access-logs
POST /api/mobile/documents/upload
GET  /api/mobile/find-medication
GET  /api/mobile/find-blood-help
GET  /api/mobile/dependents
GET  /api/mobile/notifications
POST /api/mobile/notifications/{id}/read
```

Mobile API rules:

- no direct database access
- no full records in push notifications
- secure token storage
- device registration
- sensitive action confirmation
- English/French support
- clear medical language

---

## 31. Data Contracts

### 31.1 Patient Reference

```json
{
  "health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "external_patient_reference": "HOSP-12345",
  "local_facility_patient_id": "MRN-1002"
}
```

### 31.2 Requesting User

```json
{
  "external_user_id": "DR-1002",
  "name": "Dr. Example",
  "role": "doctor",
  "department": "Outpatient"
}
```

### 31.3 Source Metadata

```json
{
  "source_system": "Example HIS",
  "source_version": "1.2.0",
  "source_facility": "FAC-001",
  "external_record_id": "ENC-9001",
  "submitted_at": "2026-05-17T09:45:00Z"
}
```

### 31.4 Sync Result

```json
{
  "status": "synced",
  "opescare_record_id": "rec_01HF...",
  "timeline_event_id": "tle_01HF...",
  "correlation_id": "req_2026_0001"
}
```

---

## 32. Security Requirements

Mandatory:

- HTTPS only
- no API secrets in frontend
- short-lived access tokens
- credential rotation
- rate limiting
- audit logs
- request validation
- idempotency
- webhook signatures
- PHI redaction in logs
- least privilege scopes
- facility verification
- environment separation
- sandbox with fake data
- secret management
- vulnerability scanning
- backup and recovery
- incident response

Never allow:

- unauthenticated data push
- unrestricted patient pull
- patient record access without consent/policy
- stale stock displayed as verified availability
- expired/quarantined blood displayed as available
- full patient data in webhook payload by default
- production secrets in GitHub
- patient data in debug logs

---

## 33. Testing Requirements

Required tests:

```text
auth tests
scope tests
consent tests
patient search privacy tests
pull summary tests
emergency access tests
push encounter tests
push lab result tests
lab amendment tests
prescription tests
dispense event tests
idempotency tests
reconciliation tests
webhook signature tests
webhook retry tests
SDK tests
widget session tests
Bridge Agent sync tests
pharmacy stock sync tests
blood stock sync tests
mobile API auth tests
rate limit tests
audit logging tests
```

Critical negative tests:

1. Cannot pull record without consent.
2. Cannot use expired token.
3. Cannot use revoked token.
4. Cannot push without idempotency key.
5. Same idempotency key with different payload returns conflict.
6. Suspended facility cannot sync.
7. Uncertain patient match goes to reconciliation.
8. Expired medicine stock not shown available.
9. Quarantined blood not shown available.
10. Webhook signature invalid is rejected.
11. Widget cannot use expired session.
12. Bridge Agent cannot sync with revoked credential.
13. Patient search does not expose clinical details.
14. Emergency access requires reason.
15. Audit event is created for every sensitive action.

---

## 34. Developer Documentation Structure

Recommended docs:

```text
docs/integration/CONNECT_API_OVERVIEW.md
docs/integration/AUTHENTICATION.md
docs/integration/PATIENT_SEARCH.md
docs/integration/CONSENT.md
docs/integration/PULL_RECORDS.md
docs/integration/PUSH_RECORDS.md
docs/integration/PHARMACY_STOCK_SYNC.md
docs/integration/BLOOD_STOCK_SYNC.md
docs/integration/WEBHOOKS.md
docs/integration/ERROR_CODES.md
docs/integration/IDEMPOTENCY.md
docs/integration/RECONCILIATION.md
docs/integration/SDK_DESIGN.md
docs/integration/WIDGET_DESIGN.md
docs/integration/BRIDGE_AGENT_DESIGN.md
docs/integration/CERTIFICATION.md
```

---

## 35. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, and docs/integration/OPESCARE_CONNECT_PLATFORM.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS API, database, architecture, or module structure.

Task: Create the OpesCare Connect Platform foundation.

Scope:
1. Create docs/integration folder if missing.
2. Add API contract placeholder files.
3. Add Connect API route placeholders under /api/v1/connect.
4. Add Mobile API route placeholders under /api/mobile.
5. Add placeholder controllers/services for auth, patient search, consent, pull summary, push records, pharmacy stock sync, blood stock sync, webhooks, reconciliation, and sync status.
6. Add OpenAPI placeholder file.
7. Add SDK design placeholder folder.
8. Add Widget design placeholder folder.
9. Add Bridge Agent design placeholder folder.
10. Add error code enum placeholder.
11. Add idempotency middleware placeholder.
12. Add audit event placeholder.
13. Add tests proving route registration, auth guard requirement, and write endpoints requiring idempotency key.
14. Do not implement full clinical business logic yet.
15. Do not expose patient data in any placeholder response.
16. Open a PR with summary, files created, risks, and next recommended tasks.
```

---

## 36. Alignment Review

This design aligns with the OpesCare vision because:

- It keeps the patient Health ID at the center.
- It separates mobile app access from external system access.
- It allows hospitals to push and pull data safely.
- It supports existing hospital systems instead of forcing replacement.
- It includes APIs, SDKs, widget, Bridge Agent, and Lite Portal.
- It enforces consent and purpose checks before sensitive pulls.
- It protects patients through audit logs and access limits.
- It supports medicine availability through verified pharmacy stock sync.
- It supports blood availability through verified blood bank/hospital stock sync.
- It prevents stale or unsafe stock from appearing as available.
- It includes reconciliation for uncertain patient matching.
- It requires idempotency and duplicate protection.
- It includes webhooks and alerts.
- It supports sandbox, certification, and production approval.
- It prepares for bilingual mobile and web experiences.
- It protects OpesCare from becoming a messy generic API.

---

## 37. Final Technical Rule

OpesCare Connect must be built as healthcare infrastructure, not a casual API.

Every integration must answer:

1. Who is calling?
2. Which facility are they acting for?
3. What is the purpose?
4. What scope is allowed?
5. Is patient consent required?
6. Is the patient match safe?
7. Is the data valid?
8. Is the request a duplicate?
9. Where did the record come from?
10. What audit event was created?
11. What happens if sync fails?
12. What happens if the external system retries?
13. What is shown to the patient?
14. What is hidden from unauthorized users?
15. What goes to reconciliation?

If these questions cannot be answered, the integration is incomplete.

The platform must be connected, synchronized, safe, and auditable from the foundation.
