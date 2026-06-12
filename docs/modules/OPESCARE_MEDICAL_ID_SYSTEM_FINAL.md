# OpesCare Medical ID System — Final Gap-Free Technical PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Document Type:** Final Product + Technical Architecture + Security PRD  
**Build Direction:** Build from scratch  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Mobile App:** Flutter recommended  
**Specialist Services:** Python/FastAPI later for duplicate detection, identity risk scoring, OCR, anomaly detection, and analytics  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, database structure, UI, ID format, QR logic, module structure, or assumptions.

---

# 1. Purpose

This document defines the final OpesCare Medical ID system.

The Medical ID is the patient identity foundation for OpesCare. It must allow a patient to be identified safely across hospitals, clinics, laboratories, pharmacies, insurers, blood banks, public health reporting workflows, the mobile app, and approved external systems.

The Medical ID system must support:

- neutral country-based Health ID format
- secure patient identification
- physical card and digital card
- QR code lookup
- rotating mobile QR
- temporary consent QR
- safe verification
- duplicate prevention
- patient merge/unmerge workflow
- consent-based access
- emergency access
- prescription linkage
- lab result linkage
- EMR/encounter linkage
- pharmacy dispensing linkage
- insurance linkage
- public health reporting linkage
- external system interoperability
- audit logs
- patient access logs
- offline-safe behavior
- privacy protection
- country-level scalability
- bilingual English/French support

The Medical ID must help healthcare providers find the correct patient. It must not expose the patient’s full medical record by itself.

---

# 2. Core Decision

The visible Medical ID must not start with `OPES`.

Reason:

A health identity should feel neutral, national/regional, and healthcare-infrastructure-grade. If the ID starts with the company name, it can look like a company membership number.

OpesCare is the platform brand.

The visible Health ID itself should be neutral and country-based.

Correct display:

```text
OpesCare Health ID
CM-HID-7KQ9-MP42-X8D1
```

Incorrect:

```text
OPES-CM-000001
OPESCARE-HID-0001
```

---

# 3. Final Medical ID Format

## 3.1 Preferred Visible Format

```text
CM-HID-7KQ9-MP42-X8D1
```

## 3.2 Format Meaning

```text
CM      = Country code of issue
HID     = Health ID prefix
7KQ9    = random secure block
MP42    = random secure block
X8D1    = checksum/verification block
```

## 3.3 Format Pattern

```text
{COUNTRY_CODE}-HID-{BLOCK1}-{BLOCK2}-{CHECK_BLOCK}
```

Regex:

```text
^[A-Z]{2}-HID-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$
```

## 3.4 Safe Alphabet

Use uppercase letters and numbers, excluding confusing characters.

Allowed:

```text
ABCDEFGHJKLMNPQRSTUVWXYZ23456789
```

Avoid:

```text
O
0
I
1
L
```

## 3.5 Example IDs

```text
CM-HID-7KQ9-MP42-X8D1
NG-HID-8R2A-QK93-ZP6T
GH-HID-5T7B-KD20-RA4M
KE-HID-9M3C-PW84-ZQ7N
ZA-HID-2F6K-HR93-BT3X
```

## 3.6 ID Must Not Encode

The Health ID must not encode:

```text
patient date of birth
sex/gender
hospital code
region code
diagnosis
insurance status
sequential number
registration date
facility owner
company name
tribe/ethnicity
religion
phone number
national ID
```

## 3.7 No Sequential IDs

Bad:

```text
CM-HID-0000-0000-0001
```

Good:

```text
CM-HID-7KQ9-MP42-X8D1
```

---

# 4. ID Type Separation

The Medical ID system must separate:

1. visible Health ID
2. internal patient UUID
3. QR token
4. external facility identifiers
5. temporary access token
6. prescription/lab/document IDs

These must not be treated as the same thing.

## 4.1 Visible Health ID

Patient-facing ID:

```text
CM-HID-7KQ9-MP42-X8D1
```

Used for:

```text
patient card
patient portal
mobile app
printed summaries
prescriptions
lab reports
facility lookup
insurance reference where allowed
external system search
```

## 4.2 Internal Patient UUID

Database-level identifier:

```text
patient_uuid = 01HXZ7F9R8E2N5P4Q6M7A9B0CD
```

Used internally only.

Never use internal UUID as the visible Health ID.

## 4.3 QR Token

A token used for QR scanning.

Example:

```text
qrx_7Kq9Mp42X8d1Tz
```

Stored hashed in database.

QR token maps to patient only after backend verification.

## 4.4 External Facility Identifier

External facility-specific ID.

Example:

```text
Demo Central Hospital MRN: MRN-1002
```

Stored as alias or external identifier.

Never replace the Health ID.

## 4.5 Temporary Access Token

Short-lived access token created by patient consent.

Example:

```text
tmp_consent_abc123
```

Used for temporary scoped access only.

## 4.6 Prescription/Lab/Document IDs

Prescription, lab result, document, claim, referral, and encounter IDs must be separate.

Examples:

```text
RX-CM-2026-8KQ2-MN7P
LAB-CM-2026-44RQ-K9ZT
DOC-CM-2026-P2AB-Q88M
```

Do not reuse Health ID as record ID.

---

# 5. What the Medical ID Carries

## 5.1 Visible ID Carries Only

The visible ID itself carries only:

```text
country code
HID prefix
random secure identifier
checksum/verification block
```

## 5.2 QR Code Carries Only

The QR code carries only:

```text
secure verification URL or secure token
token version
optional country
optional issuer
```

The QR must not carry clinical data.

## 5.3 Backend Profile Linked to ID May Contain

The backend patient profile may contain:

```text
identity summary
verification status
emergency profile
clinical timeline
prescriptions
lab results
EMR encounters
referrals
pharmacy dispense events
insurance links
documents
consent records
access logs
external identifiers
```

Backend access is controlled by authorization, consent, purpose, facility, role, and audit.

---

# 6. QR Code System

## 6.1 QR Code Purpose

The QR code is a lookup starter.

It helps locate or verify a patient identity.

The QR code is not:

```text
a medical record
a password
a login token
an API key
permission to view full history
proof of consent by itself
```

## 6.2 Recommended Static QR Payload

Use secure URL:

```text
https://opescare.com/verify/qr/qrx_7Kq9Mp42X8d1Tz
```

## 6.3 Alternative Machine Payload

```json
{
  "type": "opescare.health_id.qr",
  "version": "1",
  "token": "qrx_7Kq9Mp42X8d1Tz",
  "country": "CM"
}
```

## 6.4 QR Must Not Contain

```text
full medical history
diagnoses
lab results
prescriptions
allergies
phone number
date of birth
address
insurance details
national ID
full emergency profile
access token
API key
password
unhashed patient UUID
```

## 6.5 QR Token Storage

Store QR token as hash.

Table stores:

```text
token_hash
token_type
patient_id
status
expires_at
revoked_at
last_used_at
created_at
```

Never store or log raw token after creation except where unavoidable for immediate display.

## 6.6 QR Token Types

```text
static_identity_qr
rotating_mobile_qr
emergency_card_qr
temporary_consent_qr
facility_printed_qr
revoked_qr
```

## 6.7 Static Identity QR

Used on physical card.

Behavior:

- identifies patient safely
- shows only safe identity preview
- requires consent/auth for records
- can be revoked and reissued if compromised

## 6.8 Rotating Mobile QR

Used inside mobile app.

Rules:

```text
rotates every 60 seconds or configured interval
expires automatically
requires logged-in patient session
more secure than printed QR
```

## 6.9 Temporary Consent QR

Patient-created QR granting limited access.

Rules:

```text
short expiry: recommended 5 to 15 minutes
scope-limited
purpose-limited
single-use or limited-use
revocable
audit required
```

Example temporary access:

```text
scope: patient.summary + allergies + active medications
purpose: consultation
valid: 15 minutes
```

## 6.10 Emergency Card QR

Emergency QR can start emergency profile workflow.

Rules:

```text
authorized provider required
facility context required
emergency reason required
limited profile only
audit and review required
```

---

# 7. Medical ID Card Design

## 7.1 Card Types

Support:

```text
minimal card
standard card
emergency card
digital card
printed facility card
mobile wallet card later
```

## 7.2 Minimal Card

Shows:

```text
OpesCare Health ID
Health ID
QR code
verification note
```

Does not show patient name.

## 7.3 Standard Card

Shows:

```text
OpesCare Health ID
patient display name
Health ID
QR code
verification status
optional photo
```

## 7.4 Emergency Card

Shows:

```text
patient display name
Health ID
QR code
blood group where patient approved/policy allows
critical allergy indicator
emergency contact optional
emergency instruction
```

## 7.5 Card Must Include Warning

English:

```text
This card does not expose full medical records. Authorized providers must verify access through OpesCare.
```

French:

```text
Cette carte ne révèle pas le dossier médical complet. Les prestataires autorisés doivent vérifier l’accès via OpesCare.
```

## 7.6 Card Privacy Controls

Patient should be able to choose display level where policy allows:

```text
minimal
standard
emergency
```

Facility-issued cards may follow facility/country policy.

---

# 8. Medical ID Verification

## 8.1 Verification Methods

Medical ID can be verified through:

```text
manual ID entry
QR scan
mobile rotating QR
temporary consent QR
patient OTP confirmation
Connect API
Connect Widget
Bridge Agent lookup
OpesCare Lite lookup
facility patient search
```

## 8.2 Manual Verification Flow

1. Staff opens patient search.
2. Staff enters Medical ID.
3. System validates format.
4. System validates checksum.
5. System checks rate limit.
6. System checks if ID exists.
7. System shows safe identity preview only.
8. Staff confirms patient identity.
9. Staff selects purpose of use.
10. System starts consent workflow or approved workflow.
11. Audit event is created.

## 8.3 QR Verification Flow

1. Staff scans QR.
2. QR opens verification URL or widget.
3. System validates token format.
4. System checks token hash.
5. System checks token status.
6. System checks expiry/revocation.
7. System rate-limits repeated scans.
8. System maps token to patient.
9. System shows safe identity preview only.
10. Staff selects purpose.
11. Consent/emergency/allowed workflow starts.
12. Audit event is created.

## 8.4 API Verification Flow

1. External system authenticates.
2. External system sends Health ID or QR token.
3. OpesCare checks client, facility, scope, and purpose.
4. OpesCare verifies ID/token.
5. OpesCare returns safe identity preview.
6. External system requests consent or performs allowed workflow.
7. Audit event is created.

---

# 9. Safe Identity Preview

Before consent or authorized access, show only:

```text
Health ID
masked/display name
sex
year of birth or masked DOB
verification status
patient photo where allowed
duplicate warning where applicable
deceased/suspended warning where applicable
```

Do not show before consent:

```text
diagnoses
allergies
lab results
prescriptions
full timeline
address
phone number
insurance details
documents
emergency contacts
```

## 9.1 Masking Example

Full name:

```text
Marie Nfor T.
```

or:

```text
Marie N.
```

Date:

```text
Born: 1992
```

not full DOB unless policy allows.

---

# 10. Verification Statuses

Medical ID verification status:

```text
provisional
self_registered
facility_verified
government_id_verified
guardian_verified
duplicate_suspected
merged
suspended
deceased
entered_in_error
retired
```

## 10.1 Status Meaning

### provisional

Created but not fully verified.

### self_registered

Created by patient.

### facility_verified

Verified by approved healthcare facility.

### government_id_verified

Verified through approved government identity process.

### guardian_verified

Dependent/guardian relationship verified.

### duplicate_suspected

Possible duplicate found; some workflows restricted.

### merged

Old ID merged into primary record and retained as alias.

### suspended

Access restricted.

### deceased

Patient marked deceased. Records preserved.

### entered_in_error

Created wrongly; not used for care.

### retired

No longer primary, retained for history.

---

# 11. Medical ID Lifecycle

## 11.1 Create

Created by:

```text
patient self-registration
facility registration
guardian registration for dependent
approved bulk import
approved government/project import
```

## 11.2 Before Creation

System must run duplicate check.

Inputs:

```text
name
date of birth
sex
phone
national ID where available
facility patient ID
insurance number
guardian relationship
existing Health ID
```

Outcomes:

```text
no_match
possible_match
strong_match
confirmed_duplicate
```

Rules:

- `no_match`: allow creation.
- `possible_match`: create provisional or send review depending on policy.
- `strong_match`: block automatic creation and require review.
- `confirmed_duplicate`: do not create new primary ID.

## 11.3 Verify

Verification may happen through:

```text
facility identity check
government ID verification
guardian verification
manual admin review
existing hospital record matching
```

## 11.4 Update

Identity details can be updated with audit.

Rules:

- Health ID remains stable.
- changes to name/DOB/sex require permission and audit.
- high-risk identity changes may require review.

## 11.5 Merge

Duplicate records can be merged.

Rules:

```text
never auto-merge high-risk matches
show side-by-side comparison
require authorized reviewer
preserve old Health IDs as aliases
preserve source records
audit merge
notify affected parties where policy requires
allow unmerge where possible
```

## 11.6 Unmerge

Unmerge required when wrong merge occurs.

Rules:

```text
requires high privilege
requires reason
preserves audit
restores aliases/statuses carefully
creates safety notice
```

## 11.7 Suspend

Suspension reasons:

```text
fraud suspected
duplicate investigation
security risk
entered in error
legal restriction
identity dispute
```

## 11.8 Deceased

Rules:

```text
mark deceased
preserve records
restrict normal workflows
allow authorized/legal access
block routine new visits unless correction/reversal
```

## 11.9 Entered in Error

If created wrongly:

```text
mark entered_in_error
do not silently delete
retain audit trail
prevent future care use
```

---

# 12. Linking Medical ID to EMR and Clinical Records

Every clinical record must link to:

```text
patient_id
health_id
facility_id
encounter_id where applicable
source_system
external_record_id where applicable
created_by
created_at
verification_status
provenance
```

## 12.1 Timeline Entry Types

```text
registration
consultation
diagnosis
vitals
lab_order
lab_result
prescription
dispense_event
referral
admission
discharge
document
vaccination
insurance_claim
emergency_access
public_health_reference where allowed
```

## 12.2 Source Attribution

Every record must show:

```text
source facility
source user/role
source system
created time
verified status
amendment status
```

## 12.3 Record Trust Levels

```text
patient_uploaded_unverified
facility_recorded
lab_validated
provider_signed
amended
entered_in_error
external_pending_reconciliation
```

Unverified patient uploads must not become official clinical facts automatically.

---

# 13. Linking to Prescriptions

## 13.1 Prescription Creation

Prescription links to:

```text
Health ID
patient_id
encounter_id
prescriber_id
facility_id
prescription_id
medication details
status
issued_at
source system
```

## 13.2 Prescription ID

Use separate prescription ID.

Example:

```text
RX-CM-2026-8KQ2-MN7P
```

## 13.3 Prescription QR

Prescription QR verifies prescription only.

It must not expose full patient history.

## 13.4 Pharmacy Verification Flow

1. Patient presents prescription QR or Health ID.
2. Pharmacy verifies prescription.
3. System checks prescription status.
4. System checks pharmacy authorization.
5. System shows relevant prescription details.
6. System shows relevant allergy warning if allowed/needed.
7. Pharmacist dispenses.
8. Dispense event links back to Health ID.
9. Prescription status updates.
10. Timeline updates.
11. Audit event is logged.

## 13.5 Pharmacy Access Boundaries

Pharmacy may see:

```text
prescription details
medication
dose
duration
prescriber
facility
relevant allergy warning
dispense history for that prescription
```

Pharmacy must not automatically see:

```text
full consultation notes
full diagnosis history
unrelated lab results
full timeline
insurance details not needed
```

## 13.6 Prescription Statuses

```text
draft
issued
partially_dispensed
fully_dispensed
cancelled
expired
suspended_pending_review
entered_in_error
```

---

# 14. Linking to Lab Results

## 14.1 Lab Order Link

Lab order links to:

```text
Health ID
patient_id
encounter_id
ordering_provider
ordering_facility
lab_facility
lab_order_id
sample_id
```

## 14.2 Lab Result Link

Lab result links to:

```text
Health ID
patient_id
lab_order_id
sample_id
test_code
result_id
validated_by
released_at
source_system
amendment_status
```

## 14.3 Lab Report QR

Lab report QR verifies:

```text
report authenticity
report ID
issuing lab
release status
amendment status
```

It must not expose full medical history.

## 14.4 Lab Result Statuses

```text
ordered
sample_collected
in_progress
pending_validation
released
amended
cancelled
rejected
entered_in_error
```

## 14.5 Amendment Rule

Released lab results must not be silently edited.

Correction must:

```text
create amendment
preserve original
show amendment reason
audit event
notify authorized parties where needed
```

---

# 15. Linking to Insurance

Insurance links to Health ID but must use minimum necessary access.

## 15.1 Insurance Linkage

```text
Health ID
patient_id
policy_id
payer_id
claim_id
facility_id
service_id
authorization_id
```

## 15.2 Insurer May See

```text
eligibility
covered services
claim-related service information
minimum necessary clinical support
authorization status
invoice/payment status
```

## 15.3 Insurer Must Not Automatically See

```text
full timeline
unrelated diagnoses
unrelated prescriptions
unrelated lab results
sensitive conditions not related to claim
```

---

# 16. Linking to Public Health Reporting

Public health reporting may be generated from records linked to Health ID, but reports should not expose Health ID by default.

Default:

```text
aggregate
de-identified
pseudonymized where needed
```

Identifiable reporting only if:

```text
legally required
country policy enables it
minimum necessary
audited
approved
```

---

# 17. Linking to Documents

Documents link to:

```text
Health ID
document_id
document_type
source_facility
uploaded_by
verification_status
created_at
```

Document statuses:

```text
uploaded
pending_review
verified
rejected
archived
entered_in_error
```

Unverified documents must not be treated as official clinical truth.

---

# 18. Interoperability With External Systems

External systems connect through:

```text
Connect API
Connect SDK
Connect Widget
Bridge Agent
Webhooks
OpesCare Lite
Mobile API for official mobile app only
```

## 18.1 External System Push

External systems can push:

```text
encounters
lab results
prescriptions
dispense events
referrals
admissions
discharges
documents
pharmacy stock
blood stock
```

Push must include:

```text
Health ID or external patient reference
source system
facility
external record ID
idempotency key
purpose
timestamp
```

If patient match is uncertain, create reconciliation case.

## 18.2 External System Pull

External systems can pull only approved data.

Pull requires:

```text
auth
facility verification
purpose
scope
consent or policy basis
audit
```

Pull examples:

```text
patient summary
allergies
active medications
recent lab results
prescription history
emergency profile
referral package
```

## 18.3 External System Lookup

Lookup supports:

```text
Health ID
QR token
local facility patient ID
phone where allowed
name + date of birth
insurance number
```

Lookup returns safe preview only before consent.

---

# 19. Medical ID API Endpoints

## 19.1 Verify Health ID

```text
POST /api/v1/connect/medical-ids/verify
```

Request:

```json
{
  "health_id": "CM-HID-7KQ9-MP42-X8D1",
  "purpose": "treatment",
  "requesting_user": {
    "external_user_id": "DR-1002",
    "role": "doctor"
  }
}
```

Response:

```json
{
  "status": "valid",
  "verification_status": "facility_verified",
  "patient_preview": {
    "display_name": "Demo P.",
    "sex": "female",
    "year_of_birth": 1992,
    "health_id": "CM-HID-7KQ9-MP42-X8D1"
  },
  "next_action": "request_consent"
}
```

## 19.2 Verify QR

```text
POST /api/v1/connect/medical-ids/verify-qr
```

Request:

```json
{
  "qr_token": "qrx_7Kq9Mp42X8d1Tz",
  "purpose": "treatment"
}
```

## 19.3 Patient Search

```text
POST /api/v1/connect/patients/search
```

## 19.4 Request Consent

```text
POST /api/v1/connect/consents/request
```

## 19.5 Pull Summary

```text
GET /api/v1/connect/patients/{health_id}/summary
```

## 19.6 Pull Emergency Profile

```text
GET /api/v1/connect/patients/{health_id}/emergency-profile
```

Requires:

```text
emergency reason
authorized facility
authorized provider
audit
```

## 19.7 Push Clinical Records

```text
POST /api/v1/connect/records/encounters
POST /api/v1/connect/records/lab-results
POST /api/v1/connect/records/prescriptions
POST /api/v1/connect/records/dispense-events
POST /api/v1/connect/documents
```

---

# 20. Public Verification Pages

Routes:

```text
/verify/health-id
/verify/qr/{token}
```

## 20.1 Public Page May Show

```text
ID format status
valid/invalid status
safe identity preview where policy allows
verification status
next action
```

## 20.2 Public Page Must Not Show

```text
diagnoses
allergies
lab results
prescriptions
full patient name where privacy restricts
full date of birth
phone
address
insurance details
full medical history
```

## 20.3 Invalid ID Response

Use safe language:

```text
This Health ID could not be verified. Check the ID and try again.
```

Do not reveal unnecessary internal details.

---

# 21. Mobile App Medical ID Features

The mobile app must include:

```text
My Health ID
QR code
rotating QR
temporary consent QR
verification status
medical summary
emergency profile
consent requests
access logs
download/share card
report suspicious access
language switch
```

## 21.1 Mobile Security Rules

```text
secure token storage
PIN/biometric for sensitive actions
no full records in notifications
QR rotation
device revocation
session timeout
offline cache encryption if used
```

## 21.2 Offline Mode

Mobile app may show cached card offline, but:

```text
offline card should show cached timestamp
offline QR should be static or expired-safe
temporary consent QR requires online validation
full records should not be cached unless encrypted and policy allows
```

---

# 22. Offline and Low-Connectivity Rules

Healthcare environments may have poor internet.

## 22.1 Offline Verification

If offline:

```text
static card can be visually inspected
QR cannot grant access without backend validation
facility may record pending encounter locally
sync later through Bridge Agent or local queue
```

## 22.2 Offline Data Capture

Allowed:

```text
capture visit locally
capture local patient reference
queue sync
mark as pending_verification
```

Blocked:

```text
grant full access from QR alone
assume consent without validation
mark external records verified without sync
submit public health report externally
```

## 22.3 Sync Later

When connectivity returns:

```text
verify Health ID
match patient
request consent if needed
push queued records
create reconciliation if uncertain
audit sync
```

---

# 23. Database Model Suggestions

## 23.1 patients

```text
id
uuid
health_id
country_code
verification_status
status
is_demo
created_at
updated_at
```

## 23.2 patient_identity_profiles

```text
id
patient_id
first_name
middle_name
last_name
date_of_birth
sex
phone
email
photo_path
preferred_language
created_at
updated_at
```

## 23.3 health_id_aliases

```text
id
patient_id
alias_type
alias_value
source_facility_id
status
created_at
updated_at
```

Alias types:

```text
old_health_id
merged_health_id
facility_mrn
external_patient_id
insurance_number
legacy_card_number
```

## 23.4 health_id_qr_tokens

```text
id
uuid
patient_id
token_hash
token_type
status
expires_at
revoked_at
last_used_at
created_at
updated_at
```

## 23.5 health_id_verification_events

```text
id
uuid
patient_id
verification_type
verified_by_user_id
verified_by_facility_id
verification_status
evidence_reference
created_at
updated_at
```

## 23.6 patient_external_identifiers

```text
id
patient_id
facility_id
source_system
external_patient_id
identifier_type
status
created_at
updated_at
```

## 23.7 medical_id_access_events

```text
id
uuid
patient_id
health_id
actor_id
actor_type
facility_id
access_type
purpose
result
ip_address
user_agent
created_at
```

## 23.8 identity_merge_cases

```text
id
uuid
primary_patient_id
secondary_patient_id
status
match_score
reviewed_by
review_reason
created_at
updated_at
```

## 23.9 identity_risk_flags

```text
id
patient_id
flag_type
severity
status
created_at
resolved_at
```

---

# 24. Medical ID Generation Algorithm

## 24.1 Inputs

```text
country_code
secure random generator
safe alphabet
checksum function
reserved prefix list
uniqueness check
```

## 24.2 Steps

1. Validate country code.
2. Generate random block 1.
3. Generate random block 2.
4. Generate check block using checksum/random verification method.
5. Format ID.
6. Check uniqueness.
7. Store Health ID.
8. Create default QR token.
9. Audit creation.
10. Return Health ID.

## 24.3 Pseudocode

```text
alphabet = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789"
country = "CM"
prefix = "HID"
block1 = secure_random(4, alphabet)
block2 = secure_random(4, alphabet)
check_block = checksum_or_secure_random(country + prefix + block1 + block2)
health_id = country + "-HID-" + block1 + "-" + block2 + "-" + check_block
ensure_unique(health_id)
store(health_id)
```

## 24.4 Collision Handling

If generated ID exists:

```text
discard generated ID
generate new blocks
retry with limit
raise critical error if repeated collision
```

---

# 25. Rate Limiting and Abuse Prevention

Protect verification endpoints from enumeration.

Rules:

```text
rate-limit public verification by IP
rate-limit API verification by client/facility
detect repeated invalid Health IDs
detect repeated QR scans
block suspicious enumeration
log abuse signals
never reveal too much on invalid ID
```

Abuse events:

```text
health_id_enumeration_suspected
qr_scan_abuse_suspected
api_lookup_abuse_suspected
```

---

# 26. Audit Requirements

Audit events:

```text
health_id_created
health_id_verified
health_id_lookup
health_id_qr_scanned
health_id_qr_token_created
health_id_qr_token_revoked
health_id_status_changed
health_id_duplicate_suspected
health_id_merged
health_id_unmerged
temporary_consent_qr_created
temporary_consent_qr_used
temporary_consent_qr_expired
emergency_profile_accessed
external_system_verified_health_id
medical_id_access_denied
identity_profile_updated
```

Audit fields:

```text
patient_id
health_id_reference
actor_id
actor_type
facility_id
purpose
source_system
ip_address
user_agent
result
timestamp
reason where applicable
correlation_id
```

---

# 27. Security Requirements

## 27.1 Required

```text
Health ID not sequential
QR does not expose records
QR token stored hashed
QR token revocable
rotating mobile QR supported
temporary consent QR expires
API requires auth
sensitive pull requires consent/policy
every lookup audited
rate-limit lookup attempts
detect enumeration attempts
mask patient preview before consent
support duplicate review
support suspended/deceased/entered_in_error states
```

## 27.2 Block

```text
unlimited public lookup
full record from QR alone
sequential ID guessing
patient data in QR payload
API key in QR
password in QR
unlogged emergency access
unrestricted insurer access
unverified external system pulls
direct database access by external systems
silent clinical overwrites
```

---

# 28. Bilingual Labels

English/French labels:

```text
Health ID → Identifiant de santé
Medical ID → Identifiant médical
Scan QR Code → Scanner le code QR
Verify Health ID → Vérifier l’identifiant de santé
Verification Status → Statut de vérification
Provisional → Provisoire
Verified → Vérifié
Consent Required → Consentement requis
Emergency Access → Accès d’urgence
Access Logs → Journal d’accès
Temporary Access QR → QR d’accès temporaire
Invalid Health ID → Identifiant de santé invalide
Suspended Health ID → Identifiant de santé suspendu
Duplicate Suspected → Doublon suspecté
```

Use clear medical language. Avoid jargon.

---

# 29. UI Requirements

## 29.1 Patient Portal

Add:

```text
My Health ID
Download Health ID Card
Show QR Code
Create Temporary Access QR
Verification Status
Who Accessed My ID
Report Suspicious Access
Card Privacy Settings
```

## 29.2 Staff Portal

Add:

```text
Search by Health ID
Scan QR
Verify Patient
Request Consent
Emergency Access
Link External Record
Duplicate Warning
```

## 29.3 Admin Portal

Add:

```text
Health ID Registry
Duplicate Review
Verification Events
QR Token Management
Suspended IDs
Merged IDs
Entered-in-Error IDs
Audit Logs
```

## 29.4 Developer Portal

Add:

```text
Medical ID Verification API
QR Verification API
Patient Search API
Consent API
Push/Pull Record API
Webhook Events
SDK Examples
Rate Limit Rules
Error Codes
```

---

# 30. Webhooks Related to Medical ID

Webhook events:

```text
health_id.created
health_id.verified
health_id.status_changed
health_id.merged
health_id.duplicate_suspected
consent.granted
consent.revoked
record.linked
prescription.issued
lab_result.released
dispense.completed
emergency_access.used
```

Webhook payload must be minimal.

Example:

```json
{
  "event_type": "health_id.verified",
  "event_id": "evt_01HX...",
  "occurred_at": "2026-05-17T10:00:00Z",
  "patient": {
    "health_id_reference": "CM-HID-****-X8D1"
  },
  "resource": {
    "type": "health_id",
    "id": "hid_01HX..."
  }
}
```

Do not include full medical history in webhook payload.

---

# 31. Error Handling

Use stable error codes.

```text
INVALID_HEALTH_ID_FORMAT
INVALID_HEALTH_ID_CHECKSUM
HEALTH_ID_NOT_FOUND
HEALTH_ID_SUSPENDED
HEALTH_ID_DECEASED
HEALTH_ID_ENTERED_IN_ERROR
HEALTH_ID_DUPLICATE_SUSPECTED
QR_TOKEN_INVALID
QR_TOKEN_EXPIRED
QR_TOKEN_REVOKED
CONSENT_REQUIRED
ACCESS_DENIED
EMERGENCY_REASON_REQUIRED
RATE_LIMIT_EXCEEDED
RECONCILIATION_REQUIRED
```

Example response:

```json
{
  "status": "rejected",
  "error_code": "CONSENT_REQUIRED",
  "message": "Patient consent is required before viewing this information.",
  "required_action": "request_consent"
}
```

---

# 32. Testing Requirements

Required tests:

1. Generated Health ID does not start with OPES.
2. Generated Health ID starts with country code.
3. Health ID matches required pattern.
4. Health ID uses safe alphabet.
5. Health ID is non-sequential.
6. Duplicate Health ID cannot be created.
7. Checksum validation works.
8. Invalid checksum is rejected.
9. QR payload does not contain clinical data.
10. QR token is stored hashed.
11. QR token maps to patient only after backend validation.
12. QR token can be revoked.
13. Revoked QR token fails.
14. Expired QR token fails.
15. Rotating QR expires.
16. Temporary consent QR expires.
17. Public lookup is rate-limited.
18. API lookup is rate-limited.
19. Patient preview is masked before consent.
20. Full summary requires consent.
21. Emergency access requires reason.
22. Prescription links to Health ID.
23. Lab result links to Health ID.
24. Dispense event links to Health ID.
25. External hospital can push encounter with Health ID.
26. Uncertain match creates reconciliation case.
27. Duplicate suspected status restricts workflow.
28. Merge preserves alias.
29. Wrong merge can be unmerged by authorized user.
30. Deceased status restricts normal workflow.
31. Entered-in-error status blocks care usage.
32. Audit event created for QR scan.
33. Audit event created for external verification.
34. Insurer cannot pull full timeline.
35. Pharmacy cannot pull full clinical notes.
36. Public health report does not expose Health ID by default.
37. Offline QR does not grant record access.
38. Temporary consent QR is scope-limited.
39. Webhook payload masks Health ID.
40. Invalid ID response does not leak internal details.

---

# 33. Acceptance Criteria

The Medical ID system is acceptable when:

1. Medical ID no longer starts with Opes.
2. ID starts with country code.
3. ID uses neutral `HID` prefix.
4. ID is non-sequential.
5. ID has checksum/error detection.
6. ID uses safe alphabet.
7. ID does not expose patient information.
8. QR code does not contain full medical records.
9. QR token is secure, hashed, expiring where needed, and revocable.
10. Static QR is supported.
11. Rotating mobile QR is supported.
12. Temporary consent QR is supported.
13. Emergency QR starts limited emergency workflow only.
14. Manual ID verification works.
15. QR verification works.
16. API verification works.
17. Safe identity preview works.
18. Consent is required before sensitive record access.
19. Emergency access is limited, reason-based, audited, and reviewed.
20. Prescriptions link to Health ID.
21. Lab results link to Health ID.
22. EMR encounters link to Health ID.
23. Dispense events link to Health ID.
24. Insurance records link with minimum necessary access.
25. Public health reports are aggregate/de-identified by default.
26. External systems can verify ID through Connect API.
27. External systems can push and pull approved data.
28. Duplicate prevention exists.
29. Merge workflow preserves aliases.
30. Unmerge workflow exists.
31. Patient can view Health ID and access logs.
32. Staff can scan and verify ID.
33. Admin can manage verification, duplicates, aliases, and QR tokens.
34. Developer documentation exists.
35. Offline mode does not bypass consent/security.
36. Enumeration protection exists.
37. All sensitive actions are audited.
38. English and French labels exist.
39. Error codes are stable.
40. Tests cover security, privacy, linkage, and edge cases.

---

# 34. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md, and docs/identity/OPESCARE_MEDICAL_ID_SYSTEM_FINAL.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS ID format, QR logic, database, API design, or UI.

Task: Implement the OpesCare Medical ID foundation.

Scope:
1. Create docs/identity folder if missing.
2. Add final Medical ID format: COUNTRY-HID-BLOCK-BLOCK-CHECKBLOCK.
3. Use example: CM-HID-7KQ9-MP42-X8D1.
4. Do not use OPES in the Health ID itself.
5. Add Health ID generator service.
6. Add checksum validation placeholder.
7. Add safe alphabet without confusing characters.
8. Add QR token service.
9. Store QR tokens hashed.
10. Ensure QR payload does not contain clinical data.
11. Add model placeholders:
   - Patient
   - PatientIdentityProfile
   - HealthIdAlias
   - HealthIdQrToken
   - HealthIdVerificationEvent
   - PatientExternalIdentifier
   - MedicalIdAccessEvent
   - IdentityMergeCase
   - IdentityRiskFlag
12. Add routes:
   - POST /api/v1/connect/medical-ids/verify
   - POST /api/v1/connect/medical-ids/verify-qr
   - GET /verify/health-id
   - GET /verify/qr/{token}
13. Add patient portal placeholders:
   - My Health ID
   - Show QR Code
   - Create Temporary Access QR
   - Access Logs
   - Card Privacy Settings
14. Add staff portal placeholders:
   - Search by Health ID
   - Scan QR
   - Request Consent
   - Emergency Access
   - Duplicate Warning
15. Add admin placeholders:
   - Health ID Registry
   - Duplicate Review
   - QR Token Management
   - Merged IDs
   - Entered-in-Error IDs
16. Add audit event enum placeholders.
17. Add error code enum placeholders.
18. Add rate-limit placeholders for verification endpoints.
19. Add tests proving:
   - generated ID does not start with OPES
   - generated ID starts with country code
   - ID is not sequential
   - safe alphabet is used
   - QR payload does not contain clinical data
   - QR token is stored hashed
   - QR token can be revoked
   - expired/revoked QR fails
   - sensitive access requires consent
   - emergency access requires reason
   - lookup is audited
   - public lookup is rate-limited
20. Do not implement full clinical modules in this task.
21. Do not expose patient medical data in placeholder responses.
22. Open a PR with summary, files created, risks, and next recommended tasks.
```

---

# 35. Final Rule

The Medical ID is the foundation of OpesCare.

It must be neutral, secure, country-based, non-sequential, private, auditable, interoperable, and safe under low-connectivity conditions.

The correct model is:

```text
country-based Health ID
secure QR token
safe identity preview
consent before sensitive access
emergency access only with reason
all records linked through patient identity
external systems connect through API/widget/SDK/Bridge Agent
offline mode does not bypass security
every sensitive action audited
```

The Medical ID should help care move safely with the patient without exposing the patient’s life to anyone who scans a code.
