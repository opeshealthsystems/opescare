# OpesCare Data Dictionary & Master Data Catalog

**Version:** 1.0  
**Status:** Active  
**Last Updated:** 2026-05-19  
**Owner:** Data Governance Team

---

## Purpose

This data dictionary is the canonical source of truth for all OpesCare database field names, types, controlled values, and naming conventions. Every developer and agent **must** consult this document before creating migrations, API payloads, forms, import templates, and analytics fields.

**Non-negotiable rule:** Do not create duplicate or inconsistent field names. If a master field exists in this dictionary, use it.

---

## 1. Naming Conventions

| Rule | Pattern | Example |
|------|---------|---------|
| Database fields | snake_case | `first_name`, `facility_id` |
| Primary keys | UUID (HasUuids trait) | `id uuid PK` |
| Foreign keys | `{table_singular}_id` | `patient_id`, `facility_id` |
| Timestamps | `{action}_at` | `created_at`, `issued_at`, `revoked_at` |
| Status fields | Controlled enum string | `status: 'active'|'inactive'` |
| Boolean flags | `is_{state}` or `{thing}_enabled` | `is_demo`, `push_active` |
| JSON payloads | `{thing}_json` | `checklist_json`, `payload_json` |
| External refs | `source_system`, `source_reference` | HIS import tracking |
| Human actor | `created_by`, `updated_by` | UUID of acting user |
| Soft delete | `deleted_at` | Standard Laravel SoftDeletes |

---

## 2. Universal Fields (apply where relevant)

```
id                   uuid PK (HasUuids)
facility_id          uuid FK → facilities
patient_id           uuid FK → patients
created_by           uuid FK → users (nullable)
updated_by           uuid FK → users (nullable)
status               varchar(30) — controlled values
source_system        varchar(50) — e.g. 'opescare', 'hl7', 'import'
source_reference     varchar(100) — external record ID
metadata_json        jsonb — flexible non-critical metadata
created_at           timestamp
updated_at           timestamp
deleted_at           timestamp (nullable, SoftDeletes)
```

---

## 3. Patient Entity

### Table: `patients`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | HasUuids |
| health_id | varchar(50) | OpesCare Health ID, unique, e.g. OC-CMR-XXXX |
| first_name | varchar(100) | Required |
| middle_name | varchar(100) | Nullable |
| last_name | varchar(100) | Required |
| date_of_birth | date | Required (or estimated) |
| is_dob_estimated | boolean | Default false |
| sex | varchar(10) | male\|female\|other\|unknown |
| phone_number | varchar(30) | E.164 format preferred |
| address | jsonb | Structured or text address |
| emergency_contact | jsonb | {name, phone, relationship} |
| identity_status | varchar(20) | active\|suspended\|deceased |
| verification_status | varchar(20) | unverified\|pending\|verified |
| verified_by_facility_id | uuid FK | Facility that verified |
| verified_at | timestamp | |
| country_code | varchar(5) | ISO 3166-1 alpha-2, e.g. CM |
| is_demo | boolean | Demo record flag |

**Controlled values — `identity_status`:** `active`, `suspended`, `deceased`, `unknown`  
**Controlled values — `verification_status`:** `unverified`, `pending`, `verified`

---

## 4. Clinical Entity

### Table: `visits`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| patient_id | uuid FK | |
| facility_id | uuid FK | |
| provider_id | uuid FK → users | Nullable |
| visit_type | varchar(30) | outpatient\|inpatient\|emergency\|observation\|telemedicine |
| status | varchar(30) | planned\|active\|in_progress\|completed\|discharged\|cancelled |
| created_at | timestamp | Visit start |
| updated_at | timestamp | Last modification |

### Table: `clinical_notes`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| visit_id | uuid FK | |
| patient_id | uuid FK | |
| author_id | uuid FK → users | |
| note_type | varchar(30) | soap\|progress\|discharge\|referral\|consultation |
| status | varchar(20) | draft\|signed\|amended\|voided |
| history_of_present_illness | text | SOAP-H |
| examination_findings | text | SOAP-O/A |
| treatment_plan | text | SOAP-P |
| created_at | timestamp | |
| updated_at | timestamp | |

### Table: `diagnoses`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| visit_id | uuid FK | |
| patient_id | uuid FK | |
| icd10_code | varchar(20) | ICD-10 diagnosis code |
| diagnosis_type | varchar(20) | primary\|secondary\|differential |
| description | text | |

### Table: `vital_signs`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| triage_record_id | uuid FK | |
| temperature | decimal(4,1) | Celsius |
| pulse | smallint | beats/min |
| weight | decimal(5,2) | kilograms |
| height | decimal(5,2) | centimetres |

### Table: `lab_orders`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| patient_id | uuid FK | |
| facility_id | uuid FK | |
| visit_id | uuid FK nullable | |
| ordered_by | uuid FK → users nullable | |
| test_name | varchar(200) | |
| test_code | varchar(50) | Facility/LOINC code |
| urgency | varchar(20) | routine\|urgent\|stat |
| status | varchar(30) | pending\|collected\|processing\|resulted\|cancelled |
| ordered_at | timestamp | |
| collected_at | timestamp nullable | |
| resulted_at | timestamp nullable | |

### Table: `lab_results`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| lab_order_id | uuid FK | |
| patient_id | uuid FK | |
| parameter_name | varchar(200) | |
| value | varchar(100) | |
| unit | varchar(50) nullable | |
| reference_range | varchar(100) nullable | |
| flag | varchar(20) nullable | H\|L\|HH\|LL\|normal\|abnormal |
| resulted_at | timestamp | |

### Table: `prescriptions`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| patient_id | uuid FK | |
| facility_id | uuid FK | |
| visit_id | uuid FK nullable | |
| prescribed_by | uuid FK → users nullable | |
| status | varchar(30) | active\|dispensed\|partially_dispensed\|cancelled\|expired |
| prescribed_at | timestamp | |
| dispensed_at | timestamp nullable | |
| expires_at | timestamp nullable | |

### Table: `prescription_items`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| prescription_id | uuid FK | |
| drug_name | varchar(200) | |
| drug_code | varchar(50) nullable | Formulary code |
| dose | varchar(100) nullable | e.g. "500mg" |
| frequency | varchar(100) nullable | e.g. "3× daily" |
| route | varchar(50) nullable | oral\|IV\|IM\|topical\|sublingual |
| duration_days | smallint nullable | |
| quantity | smallint nullable | |
| status | varchar(30) | pending\|dispensed\|cancelled |

---

## 5. Facility Entity

### Table: `facilities`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| name | varchar(200) | |
| type | varchar(50) | hospital\|clinic\|laboratory\|pharmacy\|health_centre |
| status | varchar(20) | active\|inactive\|suspended |
| country_code | varchar(5) | ISO 3166-1 alpha-2 |
| is_demo | boolean | |

---

## 6. Legal & Compliance

### Table: `legal_documents`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| slug | varchar(100) unique | e.g. terms-of-use |
| title | varchar(200) | |
| document_type | varchar(50) | terms\|privacy\|consent\|dpa\|facility_agreement\|api_terms |
| language | varchar(10) | ISO language code, default 'en' |
| is_active | boolean | |

### Table: `legal_document_versions`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| legal_document_id | uuid FK | |
| version | varchar(20) | e.g. "1.0", "2.1" |
| content_html | longtext | Published HTML content |
| content_hash | varchar(64) | SHA-256 of content_html |
| is_current | boolean | Only one true per document |
| requires_reacceptance | boolean | |
| change_summary | text nullable | |
| published_by | varchar(200) | |
| published_at | timestamp nullable | |
| effective_at | timestamp nullable | |

---

## 7. Mobile Entities

### Table: `mobile_sessions` / `provider_mobile_sessions`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| patient_id / user_id | uuid FK | Patient or Provider |
| device_fingerprint | varchar(128) | Device unique identifier |
| platform | varchar(20) | ios\|android\|web |
| access_token_hash | varchar(128) nullable | SHA-256 of token |
| expires_at | timestamp nullable | |
| revoked_at | timestamp nullable | |
| revoke_reason | varchar(100) nullable | |

### Table: `push_device_tokens`

| Field | Type | Notes |
|-------|------|-------|
| id | uuid PK | |
| patient_id | uuid FK | |
| device_fingerprint | varchar(128) | |
| platform | varchar(20) | ios\|android\|web |
| push_token | text | FCM/APNs token |
| is_active | boolean | |
| revoked_at | timestamp nullable | |

---

## 8. Status Controlled Values Reference

| Entity | Field | Allowed Values |
|--------|-------|----------------|
| Patient | identity_status | active, suspended, deceased, unknown |
| Patient | verification_status | unverified, pending, verified |
| Visit | status | planned, active, in_progress, completed, discharged, cancelled |
| Visit | visit_type | outpatient, inpatient, emergency, observation, telemedicine |
| Clinical note | status | draft, signed, amended, voided |
| Lab order | status | pending, collected, processing, resulted, cancelled |
| Lab order | urgency | routine, urgent, stat |
| Lab result | flag | H, L, HH, LL, normal, abnormal |
| Prescription | status | active, dispensed, partially_dispensed, cancelled, expired |
| Prescription item | status | pending, dispensed, cancelled |
| Appointment | status | booked, confirmed, checked_in, in_progress, completed, no_show, cancelled |
| Legal document | document_type | terms, privacy, consent, dpa, facility_agreement, api_terms |
| Mobile session | platform | ios, android, web |
| Lite device | status | pending, active, suspended, revoked |

---

## 9. FHIR R4 Mapping Summary

| OpesCare Model | FHIR R4 Resource | Mapper Class |
|----------------|-----------------|--------------|
| Patient | Patient | FhirPatientMapper |
| Visit | Encounter | FhirEncounterMapper |
| VitalSign | Observation | FhirObservationMapper |
| LabOrder + LabResult | DiagnosticReport | FhirDiagnosticReportMapper |
| Prescription + PrescriptionItem | MedicationRequest | FhirMedicationRequestMapper |

FHIR endpoint base: `GET /api/fhir/R4/{Resource}[/{id}]`

---

## 10. Data Dictionary Governance

1. Any new table or field must be reviewed against this dictionary before migration is created.
2. Status controlled values must use only the listed values — no freestyle strings.
3. Foreign key naming must follow `{table_singular}_id` pattern.
4. UUID primary keys on all domain tables via Laravel `HasUuids` trait.
5. Changes to this document require a code review and version bump.
6. Violations detected in code review must be corrected before merge.
