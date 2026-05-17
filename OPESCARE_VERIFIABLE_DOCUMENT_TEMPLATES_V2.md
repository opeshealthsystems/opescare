# OpesCare Verifiable Clinical, Financial, and Administrative Document Template System — V2 Production PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Document Type:** Final Product Requirements + Technical Architecture + Standards Alignment + UI/UX + Template Blueprint  
**Build Direction:** Build from scratch or safely extend existing document modules  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**PDF Generation:** Server-side PDF rendering required  
**Mobile App:** Flutter recommended  
**Verification:** QR code + secure verification code + signed/tamper-evident document hash  
**Interoperability Direction:** HL7 FHIR-aligned document metadata and clinical resource mapping  
**Coding Direction:** LOINC/SNOMED/ICD/ATC/RxNorm/local codes where applicable  
**Supply Chain Direction:** GS1-ready fields for medicines, devices, batches, lots, and expiry traceability  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS document templates, code, database structure, numbering format, UI, or assumptions.  
**Privacy Rule:** Public verification must never expose full sensitive patient information. Verification pages must show only the minimum safe details required to confirm authenticity.

---

# 1. Purpose

This V2 document defines the complete OpesCare system for creating, styling, securing, issuing, verifying, printing, downloading, sharing, amending, revoking, and auditing healthcare, financial, administrative, and interoperability-ready documents.

OpesCare must generate professional, beautiful, industry-standard documents for:

1. lab test requests
2. lab result reports
3. imaging/radiology requests
4. imaging/radiology reports
5. prescriptions
6. pharmacy dispensing receipts
7. medicine reservation slips
8. invoices
9. payment receipts
10. refund receipts
11. insurance claim summaries
12. insurance preauthorization summaries
13. referral letters
14. discharge summaries
15. consultation summaries
16. medical certificates
17. sick leave certificates
18. vaccination certificates
19. Health ID cards
20. temporary access QR slips
21. dependent/guardian authorization slips
22. consent receipts
23. emergency access reports
24. public health submission receipts
25. blood request forms
26. blood issue forms
27. partner/facility verification certificates
28. API/integration certificates
29. audit export cover sheets

Every official document must be:

```text
professional
print-ready
mobile-readable
bilingual-ready
tamper-evident
verifiable through QR code
verifiable through human-readable verification code
linked to patient Health ID where applicable
linked to source facility and issuer
mapped to interoperability resources where applicable
coded with recognized clinical terminologies where applicable
audited
versioned
privacy-safe
revocable
amendable without silent overwrite
```

The system must make OpesCare documents trusted across hospitals, clinics, pharmacies, laboratories, imaging centers, insurers, patients, public health partners, and connected external systems.

---

# 2. QR / Verification Code Decision

The user referred to “AR CODE.” In production language and implementation, use:

```text
Verification QR Code
Verification Code
Scan to verify this document
```

The QR code must not contain the full document or full medical record.

The QR code should point to:

```text
https://opescare.com/verify/document/{verification_token}
```

The verification token must be random, revocable, and stored hashed in the database.

---

# 3. Standards Alignment Direction

This module must be designed for modern healthcare interoperability.

## 3.1 Required Standards-Aware Approach

OpesCare documents must not be “PDF-only.” Each issued document should have:

```text
human-readable PDF
structured JSON payload
FHIR-aligned metadata/resource mapping where applicable
document hash
verification token
document status
audit trail
version history
```

## 3.2 FHIR-Aligned Mapping Matrix

| OpesCare Document | FHIR-Aligned Resource Direction |
|---|---|
| Lab Test Request | ServiceRequest |
| Lab Result Report | DiagnosticReport + Observation + Specimen + DocumentReference |
| Imaging/Radiology Request | ServiceRequest |
| Imaging/Radiology Report | DiagnosticReport + ImagingStudy + DocumentReference |
| Prescription | MedicationRequest |
| Dispensing Receipt | MedicationDispense + DocumentReference |
| Invoice | Invoice |
| Payment Receipt | PaymentNotice / Invoice / DocumentReference depending implementation |
| Insurance Claim Summary | Claim + ExplanationOfBenefit where applicable |
| Insurance Preauthorization Summary | Claim / CoverageEligibilityRequest / CoverageEligibilityResponse |
| Referral Letter | ServiceRequest + Composition + DocumentReference |
| Discharge Summary | Composition + Encounter + DocumentReference |
| Consultation Summary | Composition + Encounter + DocumentReference |
| Vaccination Certificate | Immunization + DocumentReference |
| Medical Certificate | Composition + DocumentReference |
| Sick Leave Certificate | Composition + DocumentReference |
| Health ID Card | Patient + Identifier + DocumentReference |
| Consent Receipt | Consent + DocumentReference |
| Emergency Access Report | AuditEvent + DocumentReference |
| Public Health Submission Receipt | Communication / DocumentReference / MeasureReport where applicable |
| Blood Request Form | ServiceRequest + Specimen/BiologicallyDerivedProduct direction where applicable |
| Blood Issue Form | BiologicallyDerivedProduct / SupplyDelivery direction where applicable |
| Partner/Facility Verification Certificate | Organization + VerificationResult direction where applicable |

## 3.3 Coding Systems

Where available and appropriate, support:

```text
LOINC for lab tests and clinical observations
SNOMED CT for clinical findings/procedures where licensed/available
ICD-10 or ICD-11 for diagnoses where policy allows
ATC/RxNorm/local medication code for medicines where available
local lab test code mapping
local pharmacy product code mapping
facility service code mapping
GS1 GTIN/batch/lot fields for medicines/devices where available
```

## 3.4 Local Code Mapping

Because many hospitals and labs use local codes, OpesCare must support:

```text
local_code
standard_code
code_system
mapping_status
mapped_by
mapped_at
mapping_confidence
review_required
```

Mapping statuses:

```text
unmapped
mapped
needs_review
rejected
deprecated
```

---

# 4. Core Principle

Every official OpesCare document must answer:

1. Who issued this document?
2. Which facility or organization issued it?
3. Which professional signed or validated it?
4. Which patient or party is it linked to?
5. Which Health ID is linked, where applicable?
6. What type of document is it?
7. What template version generated it?
8. Which structured resource generated it?
9. When was it issued?
10. Has it been amended, cancelled, expired, revoked, superseded, or entered in error?
11. Is it authentic?
12. Can it be verified safely?
13. What can be shown publicly?
14. What requires login/authorization?
15. Who accessed or downloaded it?
16. Who verified it?
17. Was it printed, downloaded, shared, amended, or revoked?
18. Is the document hash still valid?
19. Is the QR token still valid?
20. Does the document match its stored official version?

If these questions cannot be answered, the document is not production-ready.

---

# 5. Document Trust Model

Official documents must use controlled trust states.

```text
draft
pending_review
approved
issued
released
amended
superseded
cancelled
revoked
expired
entered_in_error
archived
legal_hold
```

## 5.1 Status Definitions

### draft

Document is being prepared. Not official. Not externally verifiable.

### pending_review

Document requires validation, signature, review, or approval.

### approved

Document approved but not yet issued or released.

### issued

Document is official and verifiable.

### released

Document has been released to patient or authorized recipient.

### amended

Document was corrected after release. Original remains preserved.

### superseded

An older version has been replaced by a newer version.

### cancelled

Document was cancelled before final use or before final release.

### revoked

Document was previously issued but later invalidated.

### expired

Document passed its validity period.

### entered_in_error

Document was created incorrectly and must not be relied on.

### archived

Historical record retained.

### legal_hold

Document cannot be deleted or destroyed because of legal/regulatory/admin hold.

## 5.2 Cancellation vs Revocation vs Entered-in-Error

```text
cancelled = stopped before final use or final release
revoked = issued/released document later invalidated
entered_in_error = created incorrectly and should never have existed as a valid document
superseded = replaced by a newer amended version
```

Verification pages must show these differences clearly.

---

# 6. Official Document Categories

## 6.1 Clinical Documents

```text
lab test request
lab result report
imaging/radiology request
imaging/radiology report
prescription
consultation summary
referral letter
discharge summary
medical certificate
sick leave certificate
vaccination certificate
emergency care summary
care plan
```

## 6.2 Pharmacy Documents

```text
dispensing receipt
medicine reservation slip
pharmacy stock confirmation
medicine substitution note
controlled medication log where allowed
```

## 6.3 Financial Documents

```text
invoice
payment receipt
deposit receipt
refund receipt
insurance claim summary
insurance preauthorization summary
statement of account
```

## 6.4 Administrative and Governance Documents

```text
consent receipt
emergency access report
public health submission receipt
partner approval certificate
facility verification certificate
API integration certificate
audit export cover sheet
```

## 6.5 Patient Identity Documents

```text
Health ID card
temporary access QR slip
dependent/guardian authorization slip
```

---

# 7. Universal Document Requirements

Every official document must include:

```text
OpesCare branding where appropriate
issuing facility/organization name
facility license/verification status where applicable
document title
document type
document number
document version
issue date/time
issuer name and role
issuer license number where applicable
issuer licensing body where applicable
facility license number where applicable
signature block
signature type
patient Health ID where applicable
patient name where applicable
safe patient identifier
verification QR code
alphanumeric verification code
document status
page number
privacy notice
template version
document hash reference
```

## 7.1 Required Footer

English:

```text
This document can be verified through OpesCare. Scan the verification code or enter the verification number at the official verification page.
```

French:

```text
Ce document peut être vérifié via OpesCare. Scannez le code de vérification ou saisissez le numéro de vérification sur la page officielle de vérification.
```

## 7.2 Required Verification Block

```text
Verification QR Code
Verification Code
Document Number
Issued At
Document Status
Version
```

Example:

```text
Scan to verify
Verification Code: VFY-CM-LAB-2026-8KQ2-MN7P
Document No: LAB-CM-2026-8KQ2-MN7P
Issued: 17 May 2026, 14:30
Status: Issued
Version: 1.0
```

## 7.3 Versioning

```text
Version: 1.0
```

If amended:

```text
Version: 1.1
Amended on: 18 May 2026
Reason: Corrected patient age display
```

## 7.4 Page Numbering

All multi-page documents must show:

```text
Page 1 of 3
```

## 7.5 Watermarks

Use subtle state watermarks:

```text
DRAFT — NOT VALID FOR USE
REVOKED
SUPERSEDED
ENTERED IN ERROR
CANCELLED
EXPIRED
OpesCare Verified
```

---

# 8. Universal Document Design System

## 8.1 Colors

```text
Primary Blue: #0F4C81
Primary Blue Dark: #0A355C
Primary Blue Light: #E8F2FA
Clinical Teal: #0F766E
Clinical Teal Light: #E6F7F5
Background: #F7FAFC
Surface White: #FFFFFF
Text Primary: #0F172A
Text Secondary: #475569
Border: #E2E8F0
Success: #15803D
Warning: #B45309
Danger: #B91C1C
Critical: #7F1D1D
Financial Green: #166534
```

## 8.2 Typography

```text
document title: 20–24px bold
section title: 13–15px uppercase/semi-bold
body: 10.5–12px
small footer: 8.5–10px
line-height: 1.35–1.5
```

## 8.3 Layout Grid

```text
page size: A4
margin: 16mm top/bottom, 14mm left/right
header height: 28–40mm
footer height: 18–24mm
content sections: card/table blocks
QR block: top-right or footer-right depending document
```

## 8.4 Header Layout

Header must show:

```text
facility logo left
facility name/address/license center or left
document title clearly visible
verification badge/QR section right
```

## 8.5 Clean Design Rule

Avoid:

```text
heavy gradients
unnecessary icons
large background images
low contrast
crowded sections
too many colors
emoji icons
decorative medical graphics that reduce readability
```

Use:

```text
clean borders
clear section spacing
consistent tables
high-contrast text
professional medical layout
```

---

# 9. Verification QR Code System

## 9.1 QR Code Purpose

The QR code verifies document authenticity and current status.

It does not grant access to full clinical content.

## 9.2 QR Code Payload

```text
https://opescare.com/verify/document/{verification_token}
```

Example:

```text
https://opescare.com/verify/document/vdt_8KQ2MN7PX9
```

## 9.3 QR Code Must Not Contain

```text
full lab result values
full prescription content
patient diagnosis
patient phone number
patient address
national ID
full Health ID where not needed
insurance details
API keys
passwords
access tokens
full PDF content
```

## 9.4 Verification Token

Use secure random token.

Store only hash.

Fields:

```text
token_hash
document_id
document_type
status
expires_at nullable
revoked_at nullable
last_used_at nullable
created_at
```

## 9.5 QR Expiry by Document Type

| Document Type | QR Expiry Rule |
|---|---|
| Lab result report | no expiry unless revoked/amended; verification remains status-limited |
| Imaging report | no expiry unless revoked/amended |
| Prescription | expires with prescription validity |
| Dispensing receipt | no expiry; verification remains privacy-limited |
| Invoice | no expiry; status may change |
| Receipt | no expiry; status may show void/refund |
| Referral | expires with referral validity or policy |
| Discharge summary | no expiry unless revoked/amended |
| Medical certificate | expires with certificate validity |
| Sick leave certificate | expires with certificate date range/validity |
| Vaccination certificate | based on vaccine/country policy |
| Temporary access QR slip | short expiry, e.g. 5–15 minutes |
| Health ID card QR | revocable, no fixed expiry unless rotating mobile QR |
| Consent receipt | expires with consent where applicable but remains auditable |
| Emergency access report | no public detail; status verification only |
| Public health receipt | no expiry unless revoked |
| Facility certificate | expires with verification/license validity |

## 9.6 Alphanumeric Verification Code

Format:

```text
VFY-{COUNTRY}-{TYPE}-{YEAR}-{RANDOM}-{CHECK}
```

Examples:

```text
VFY-CM-LAB-2026-8KQ2-MN7P
VFY-CM-RX-2026-P7AA-Q92D
VFY-CM-RCT-2026-KD88-RT6M
VFY-CM-REF-2026-MP42-X8D1
```

## 9.7 Verification Routes

```text
GET  /verify/document/{token}
GET  /verify/document
POST /api/v1/document-verification/verify-code
```

Manual verification accepts:

```text
verification_code
document_number optional
```

## 9.8 Public Verification Response

Public verification may show:

```text
authentic / not authentic
document type
document number masked where needed
issuing facility
issue date
current status
version
patient initials or masked name where policy allows
last 4 characters of Health ID where allowed
```

Public verification must not show:

```text
full lab result values
diagnosis
full prescription
full patient name where restricted
phone number
address
insurance details
full document PDF download
```

## 9.9 Authenticated Verification

Authorized users may log in to view more details only if:

```text
role allowed
facility context allowed
patient consent or policy basis exists
document type allows access
audit is created
```

## 9.10 Verification Status Output

```text
valid
valid_but_amended
valid_but_expired
revoked
cancelled
entered_in_error
superseded
not_found
token_expired
verification_limited
```

## 9.11 Anti-Fraud Checks

Detect:

```text
too many failed verification attempts
many scans of same document from unusual locations
expired/revoked document scan
mismatched document number/code
suspected forged copy
document code enumeration
```

Risk events:

```text
document_verification_abuse_suspected
revoked_document_scanned
document_code_enumeration_suspected
document_hash_mismatch_detected
```

---

# 10. Document Numbering System

## 10.1 Recommended Format

```text
{TYPE}-{COUNTRY}-{YEAR}-{RANDOM}-{CHECK}
```

Examples:

```text
LAB-CM-2026-8KQ2-MN7P
RX-CM-2026-P7AA-Q92D
RCT-CM-2026-KD88-RT6M
INV-CM-2026-Z4P2-HT9B
REF-CM-2026-MP42-X8D1
DSC-CM-2026-RA7M-JJ82
```

## 10.2 Type Codes

```text
LAB = lab result report
LREQ = lab request
IMGREQ = imaging/radiology request
IMG = imaging/radiology report
RX = prescription
DSP = dispensing receipt
MRES = medicine reservation slip
RCT = receipt
INV = invoice
REFUND = refund receipt
CLM = insurance claim summary
AUTH = insurance preauthorization summary
REF = referral
DSC = discharge summary
CONS = consultation summary
MCERT = medical certificate
SICK = sick leave certificate
VAC = vaccination certificate
EA = emergency access report
CNST = consent receipt
BLOODREQ = blood request form
BLOODISS = blood issue form
HID = Health ID card
PHR = public health receipt
FACV = facility verification certificate
APIC = API integration certificate
```

## 10.3 Collision Rule

If generated number exists:

```text
discard generated number
generate new random block
retry with limit
raise critical generation error if repeated collision
```

---

# 11. Digital Signature and Tamper Evidence

## 11.1 Document Hash

For every issued PDF, compute:

```text
sha256_hash
```

Store:

```text
document_hash
rendered_pdf_hash
issued_at
issued_by
template_version
payload_hash
```

## 11.2 Hash Verification

When verifying:

```text
compare stored hash with rendered/served PDF hash
show hash mismatch if altered
block reliance on tampered copy
create audit event
```

## 11.3 Signature Types

Support signature types:

```text
manual_signature
uploaded_signature_image
electronic_signature
system_issued_signature
digital_certificate_signature
```

## 11.4 Signature Rules

| Document Type | Minimum Signature Requirement |
|---|---|
| Lab result | lab validator electronic/system signature |
| Radiology report | radiologist signature |
| Prescription | authorized prescriber signature |
| Medical certificate | provider signature |
| Sick leave certificate | provider signature |
| Discharge summary | responsible clinician signature |
| Referral | referring provider signature |
| Receipt/invoice | cashier/system signature |
| Public health receipt | system/governance signature |
| Facility certificate | OpesCare authorized verifier signature |

## 11.5 Digital Certificate Phase

Phase 2 may add:

```text
cryptographic document signing
facility digital certificate
OpesCare platform certificate
timestamp authority
```

---

# 12. Document Template Management

## 12.1 Template Statuses

```text
draft
in_review
approved
published
archived
rejected
```

## 12.2 Template Versioning

Track:

```text
template_code
document_type
language
version
status
created_by
approved_by
published_at
archived_at
change_summary
```

## 12.3 Template Approval Workflow

1. Designer/developer creates draft.
2. Clinical/operations reviewer checks content.
3. Compliance reviewer checks privacy.
4. Admin approves.
5. Template is published.
6. Old template is archived.
7. Documents keep the template version used at issuance.

## 12.4 Template Override Rule

Facilities may customize headers/branding but must not remove:

```text
verification QR
verification code
document status
OpesCare verification footer
audit link
privacy notice
document number
version number
issuer/signature block where required
```

---

# 13. Coding and Terminology Requirements

## 13.1 Lab Tests

Each lab test should support:

```text
local_test_code
LOINC code where mapped
test_name
specimen_type
method
unit
reference_range
critical_range
result_flag
```

## 13.2 Diagnoses

Diagnosis fields should support:

```text
local diagnosis text
ICD-10/ICD-11 code where policy allows
SNOMED CT code where licensed/available
diagnosis certainty
diagnosis date
```

## 13.3 Medicines

Medication fields should support:

```text
local medicine name
generic name
brand name
strength
form
route
ATC code where available
RxNorm code where available
local product code
GTIN where available
batch/lot number where applicable
expiry date where applicable
manufacturer where applicable
```

## 13.4 Facility Services

Service fields should support:

```text
local service code
service name
standard mapping where available
payer code where applicable
```

---

# 14. Specimen Chain-of-Custody Requirements

Lab and pathology documents must support specimen lifecycle.

## 14.1 Required Specimen Fields

```text
sample_id
specimen_type
collection_date_time
collector_user
collection_location
received_by_lab_user
received_date_time
sample_condition
storage_condition
rejection_reason nullable
chain_of_custody_events
```

## 14.2 Sample Condition Values

```text
acceptable
hemolyzed
clotted
insufficient_quantity
leaking_container
wrong_container
delayed_transport
unlabeled
mislabeled
rejected
```

## 14.3 Chain-of-Custody Event Fields

```text
event_type
performed_by
location
timestamp
notes
```

Event types:

```text
collected
received
transferred
processed
stored
rejected
disposed
```

---

# 15. Lab Test Request Template

## 15.1 FHIR Direction

```text
ServiceRequest + Patient + Practitioner + Organization + Specimen if pre-created
```

## 15.2 Required Fields

```text
document title: Laboratory Test Request
document number
verification code
QR code
patient name
Health ID
age/sex
requesting facility
requesting provider
provider license/role
requested tests
local test code
LOINC code where available
clinical notes/reason for test where appropriate
priority: routine/urgent/stat
sample type
collection instructions
requested date/time
signature block
status
```

## 15.3 Layout

```text
Header
Patient Information
Requesting Provider
Requested Tests Table
Clinical Notes / Indication
Sample Instructions
QR Verification Block
Signature / Authorization
Footer
```

## 15.4 Requested Tests Table

Columns:

```text
test code
standard code
test name
sample type
priority
notes
```

## 15.5 Disclaimer

English:

```text
This request contains health information and must be handled only by authorized personnel.
```

French:

```text
Cette demande contient des informations de santé et doit être traitée uniquement par le personnel autorisé.
```

---

# 16. Lab Result Report Template

## 16.1 FHIR Direction

```text
DiagnosticReport + Observation + Specimen + Patient + Practitioner + Organization + DocumentReference
```

## 16.2 Required Fields

```text
document title: Laboratory Result Report
document number
verification code
QR code
patient name
Health ID
age/sex
ordering provider
ordering facility
performing lab
lab accreditation/license field where applicable
sample ID
specimen type
collection date/time
received date/time
sample condition
reported date/time
result validation status
validated by
released by
test results
LOINC/local codes
reference ranges
critical ranges
units
method/device where applicable
flags
critical result indicator
interpretive notes where allowed
amendment status
signature block
```

## 16.3 Layout

```text
Header
Verification Block
Patient Information
Order Information
Sample / Specimen Information
Results Table
Interpretation / Notes
Critical Result Notice if applicable
Validator / Signature
Amendment History
Footer
```

## 16.4 Results Table

Columns:

```text
test
local code
standard code
result
unit
reference range
critical range
flag
method
status
```

Flags:

```text
normal
high
low
critical
abnormal
pending
not_applicable
```

## 16.5 Reference Range Governance

Each result should support:

```text
reference range source
age-adjusted range
sex-adjusted range
method-specific range
critical threshold
unit system
```

## 16.6 Critical Result Notice

```text
Critical result: This result requires prompt review by an authorized healthcare professional.
```

## 16.7 Amendment Rules

Released lab reports must not be silently edited.

If amended:

```text
show amended badge
show amendment date
show reason summary
preserve original version
new QR verification points to latest status
old QR shows superseded/amended status
```

## 16.8 Public Verification Rule

Public verification may show:

```text
document type
issuing lab
issue date
status
version
document authentic yes/no
```

Do not show:

```text
result values
test names if sensitive
diagnosis
full patient identity
```

---

# 17. Imaging/Radiology Request Template

## 17.1 FHIR Direction

```text
ServiceRequest + Patient + Practitioner + Organization
```

## 17.2 Required Fields

```text
request number
verification code
QR code
patient name
Health ID
requesting provider
facility
study requested
body part
clinical indication
priority
requested date/time
signature
```

Public verification must not expose clinical indication.

---

# 18. Imaging/Radiology Report Template

## 18.1 FHIR Direction

```text
DiagnosticReport + ImagingStudy + DocumentReference + Patient + Practitioner + Organization
```

## 18.2 Required Fields

```text
document title
document number
verification code
QR code
patient name
Health ID
requesting provider
imaging center/facility
study type
body part
study date
report date
radiologist name
radiologist license
findings
impression
recommendation
signature
status
```

## 18.3 Layout

```text
Header
Patient Information
Study Information
Findings
Impression
Recommendations
Radiologist Signature
Verification Block
Footer
```

## 18.4 Public Verification Rule

Do not expose findings or impression publicly.

---

# 19. Prescription Template

## 19.1 FHIR Direction

```text
MedicationRequest + Patient + Practitioner + Organization + Medication
```

## 19.2 Required Fields

```text
document title: Prescription
prescription number
verification code
QR code
patient name
Health ID
age/sex
prescriber name
prescriber role/license
licensing body
prescribing facility
facility license where applicable
date issued
medication list
generic name
brand name where applicable
strength
form
route
dose
frequency
duration
quantity
refills if allowed
substitution allowed yes/no
allergy warning if applicable
dispense status
validity/expiry date
signature
```

## 19.3 Layout

```text
Header
Patient Information
Prescriber Information
Medication Table
Clinical Warnings
Dispense Instructions
Verification Block
Signature
Footer
```

## 19.4 Medication Table

Columns:

```text
medicine
generic/brand
strength
form
dose
route
frequency
duration
quantity
instructions
substitution
```

## 19.5 Controlled Medicines

If controlled medicines are supported later:

```text
controlled medicine flag
extra authorization
stricter audit
restricted printing
controlled medication log
special prescription numbering
```

## 19.6 Prescription Statuses

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

## 19.7 Public Verification Rule

May show:

```text
authentic yes/no
prescription status
issuing facility
prescriber
issue date
expiry status
dispense status
```

Must not show publicly:

```text
full medication list
diagnosis
patient address/phone
sensitive condition
```

Pharmacy authenticated view may show medication details if authorized.

## 19.8 Disclaimer

```text
Use medicines only as directed by the prescribing healthcare professional.
```

---

# 20. Pharmacy Dispensing Receipt Template

## 20.1 FHIR Direction

```text
MedicationDispense + MedicationRequest + DocumentReference
```

## 20.2 Required Fields

```text
document title: Dispensing Receipt
receipt number
verification code
QR code
pharmacy name
pharmacy license
pharmacist name
pharmacist license where applicable
patient name
Health ID
prescription number
medications dispensed
quantity dispensed
dispense date/time
batch/lot number optional
expiry date optional
GTIN optional
amount paid if applicable
balance if applicable
substitution note if applicable
pharmacist signature
```

## 20.3 Layout

```text
Header
Patient/Prescription Reference
Dispensed Medicines Table
Payment Summary if applicable
Pharmacist Confirmation
Verification Block
Footer
```

## 20.4 Dispensed Medicines Table

Columns:

```text
medicine
strength
quantity
batch/lot number
expiry date
GTIN optional
price optional
```

## 20.5 Public Verification Rule

May show:

```text
receipt authenticity
pharmacy
date
status
```

Must not show full medicine list publicly unless authenticated and policy allows.

---

# 21. Invoice Template

## 21.1 FHIR Direction

```text
Invoice + Patient + Organization + Coverage where applicable
```

## 21.2 Required Fields

```text
document title: Invoice
invoice number
verification code
QR code
facility name
patient/customer name
Health ID where applicable
payer/insurance where applicable
invoice date
due date
services/items table
subtotal
discount
tax if applicable
insurance covered amount
patient responsibility
total due
payment status
terms
```

## 21.3 Services Table

Columns:

```text
item/service
description
service code optional
quantity
unit price
amount
payer responsibility
patient responsibility
```

## 21.4 Statuses

```text
draft
issued
partially_paid
paid
cancelled
voided
refunded
overdue
```

## 21.5 Public Verification Rule

Public verification should not expose medical service details.

---

# 22. Receipt Template

## 22.1 FHIR Direction

```text
PaymentNotice / Invoice / DocumentReference depending implementation
```

## 22.2 Required Fields

```text
document title: Payment Receipt
receipt number
verification code
QR code
facility/organization
patient/customer
Health ID where applicable
invoice reference
payment date/time
payment method
amount paid
received by
balance
transaction reference
status
```

## 22.3 Payment Methods

```text
cash
mobile_money
card
bank_transfer
insurance
wallet
other
```

## 22.4 Statuses

```text
issued
voided
refunded
partially_refunded
entered_in_error
```

## 22.5 Public Verification Rule

May show:

```text
receipt authentic yes/no
issuing organization
receipt number
date
payment status
document status
```

Avoid exposing patient medical services publicly.

---

# 23. Refund Receipt Template

## 23.1 Required Fields

```text
refund receipt number
verification code
QR code
original receipt/invoice
refund amount
refund method
refund reason
approved by
processed by
date/time
status
```

Public verification should show authenticity/status, not medical service details.

---

# 24. Insurance Claim Summary Template

## 24.1 FHIR Direction

```text
Claim + ExplanationOfBenefit + Coverage where applicable
```

## 24.2 Required Fields

```text
claim number
verification code
QR code
patient reference
Health ID masked where needed
facility
insurer
policy number masked
claim date
claim amount
approved amount
status
reason summary
minimum necessary supporting references
```

Public verification must not show full clinical details.

---

# 25. Referral Letter Template

## 25.1 FHIR Direction

```text
ServiceRequest + Composition + DocumentReference
```

## 25.2 Required Fields

```text
document title: Referral Letter
referral number
verification code
QR code
patient name
Health ID
referring facility
referring provider
receiving facility/provider
reason for referral
clinical summary
urgency
attachments/relevant documents
date issued
signature
status
```

## 25.3 Layout

```text
Header
Patient Information
Referral Information
Clinical Summary
Reason for Referral
Relevant Findings
Requested Action
Verification Block
Signature
Footer
```

## 25.4 Public Verification Rule

Public verification must not show clinical summary.

---

# 26. Discharge Summary Template

## 26.1 FHIR Direction

```text
Composition + Encounter + DocumentReference
```

## 26.2 Required Fields

```text
document title: Discharge Summary
document number
verification code
QR code
patient name
Health ID
admission date
discharge date
admitting diagnosis
discharge diagnosis
procedures
treatment given
medications at discharge
follow-up instructions
warning signs
provider/team
signature
```

## 26.3 Public Verification Rule

Do not show diagnosis or treatment publicly.

---

# 27. Consultation Summary Template

## 27.1 FHIR Direction

```text
Composition + Encounter + DocumentReference
```

## 27.2 Required Fields

```text
document title: Consultation Summary
document number
verification code
QR code
patient name
Health ID
facility
provider
consultation date
chief complaint
assessment
plan
prescriptions linked
lab requests linked
follow-up instructions
signature
```

## 27.3 Public Verification Rule

Public verification only confirms authenticity/status.

---

# 28. Medical Certificate Template

## 28.1 FHIR Direction

```text
Composition + DocumentReference
```

## 28.2 Required Fields

```text
document title: Medical Certificate
certificate number
verification code
QR code
patient name
Health ID where applicable
provider
provider license
facility
statement/certification
date issued
validity period
signature
status
```

## 28.3 Public Verification Rule

May show:

```text
certificate authentic yes/no
issuer
issue date
validity status
```

Must not expose diagnosis unless policy/consent allows.

---

# 29. Sick Leave Certificate Template

## 29.1 FHIR Direction

```text
Composition + DocumentReference
```

## 29.2 Required Fields

```text
document title: Sick Leave Certificate
certificate number
verification code
QR code
patient name
Health ID optional/masked
provider
provider license
facility
start date
end date
number of days
fitness status
signature
```

## 29.3 Public Verification Rule

Public verification can show:

```text
authenticity
issuer
date range
validity status
```

Do not disclose diagnosis by default.

---

# 30. Vaccination Certificate Template

## 30.1 FHIR Direction

```text
Immunization + DocumentReference
```

## 30.2 Required Fields

```text
document title: Vaccination Certificate
certificate number
verification code
QR code
patient name
Health ID
vaccine name
vaccine code where available
dose number
date administered
facility
administered by
batch/lot number
expiry date where applicable
manufacturer where applicable
next dose date where applicable
signature
```

## 30.3 Public Verification Rule

May show vaccine validity only if policy allows.

---

# 31. Blood Request and Blood Issue Forms

## 31.1 Blood Request FHIR Direction

```text
ServiceRequest + Patient + Organization
```

## 31.2 Blood Request Required Fields

```text
document title: Blood Request
request number
verification code
QR code
requesting facility
patient Health ID masked
blood group
component requested
units requested
urgency
requesting provider
date/time
status
```

## 31.3 Blood Issue Direction

```text
BiologicallyDerivedProduct / SupplyDelivery direction where applicable
```

## 31.4 Blood Issue Required Fields

```text
document title: Blood Issue Form
issue number
verification code
QR code
issuing blood bank
receiving facility
blood group
component
units issued
issue date/time
authorized by
status
```

## 31.5 Public Verification Rule

Public verification must not expose patient identity.

---

# 32. Consent Receipt Template

## 32.1 FHIR Direction

```text
Consent + DocumentReference
```

## 32.2 Required Fields

```text
document title: Consent Receipt
receipt number
verification code
QR code
patient name
Health ID
requesting facility
purpose
data scope
consent decision
start time
expiry time
revocation status
```

## 32.3 Public Verification Rule

Show only authenticity/status unless authenticated.

---

# 33. Emergency Access Report Template

## 33.1 FHIR Direction

```text
AuditEvent + DocumentReference
```

## 33.2 Required Fields

```text
document title: Emergency Access Report
report number
verification code
QR code
patient Health ID masked
facility
authorized user
reason
access time
data categories accessed
review status
reviewed by
```

## 33.3 Public Verification Rule

Only confirms document authenticity/status.

---

# 34. Health ID Card Template

## 34.1 FHIR Direction

```text
Patient + Identifier + DocumentReference
```

## 34.2 Required Fields

```text
OpesCare Health ID
Health ID
patient display name depending card type
QR code
verification status
issue date
card type
privacy note
```

## 34.3 Card Types

```text
minimal
standard
emergency
```

## 34.4 Emergency Card Optional Fields

```text
blood group
critical allergy indicator
emergency contact
```

Only show where patient/policy allows.

---

# 35. Public Health Submission Receipt Template

## 35.1 FHIR Direction

```text
Communication / MeasureReport / DocumentReference depending report type
```

## 35.2 Required Fields

```text
document title: Public Health Submission Receipt
receipt number
verification code
QR code
report type
reporting facility
reporting period
submission status
submitted by
submitted at
external reference if available
```

## 35.3 Public Verification Rule

Must not expose patient identity by default.

---

# 36. Partner/Facility Verification Certificate Template

## 36.1 FHIR Direction

```text
Organization + VerificationResult direction where applicable
```

## 36.2 Required Fields

```text
document title: Partner/Facility Verification Certificate
certificate number
verification code
QR code
partner/facility name
partner type
verification status
verified services
license reference
issue date
expiry date
OpesCare verifier
status
```

## 36.3 Public Verification Rule

May show partner/facility authenticity and current verification status.

---

# 37. API/Integration Certificate Template

## 37.1 Required Fields

```text
certificate number
verification code
QR code
partner/developer name
integration name
environment
certification scope
certified endpoints/scopes
issue date
expiry date
status
OpesCare verifier
```

Public verification may show certification status and scope, not secrets.

---

# 38. Document Sharing Controls

Patients and authorized users may share documents only through controlled methods.

## 38.1 Share Types

```text
temporary share link
downloaded PDF
printed copy
provider-to-provider share
insurance claim package share
```

## 38.2 Temporary Share Link

Fields:

```text
share_token_hash
document_id
created_by
recipient_email_or_phone optional
expires_at
revoked_at nullable
access_count
max_access_count nullable
created_at
```

## 38.3 Share Rules

```text
share links expire
share links can be revoked
downloads are audited
shared PDFs may include watermark
sensitive documents require OTP/PIN before sharing
public verification remains privacy-safe
```

## 38.4 Shared Copy Watermark

Example:

```text
Shared copy generated by OpesCare on 17 May 2026
```

---

# 39. Offline Verification Rules

Healthcare environments may have poor connectivity.

## 39.1 Offline Allowed

```text
visual inspection of document
read document number
read verification code
check visible issuer/signature
queue verification for later
```

## 39.2 Offline Not Allowed

```text
grant sensitive access from QR alone
assume document is authentic without later online verification
download full record from QR
bypass consent
```

## 39.3 Offline Status

If offline verification is used:

```text
mark as pending_online_verification
sync verification event later
show caution banner
```

Caution text:

```text
This document has not been verified online yet. Confirm authenticity through OpesCare when connectivity is available.
```

---

# 40. Accessibility, Print, and Rendering QA

## 40.1 Accessibility

Documents should support:

```text
clear text hierarchy
high contrast
readable font sizes
plain text fallback where possible
screen-reader-friendly text extraction where possible
not relying on color alone
```

## 40.2 Print QA

Test:

```text
A4 black-and-white print
A4 color print
mobile PDF preview
desktop PDF preview
thermal print for receipts
page break control
header/footer on multipage
QR scannability after print
```

## 40.3 QR Scannability

QR must remain scannable:

```text
minimum size
quiet zone
high contrast
not distorted
not placed on busy background
```

---

# 41. Document Verification Pages

## 41.1 Public Verification Page UI

Show:

```text
OpesCare logo
verification result
document status
document type
issuing organization
issue date
version
safe patient reference if allowed
verification code entry
privacy notice
```

## 41.2 Status Messages

Valid:

```text
This document is authentic and currently valid.
```

Amended:

```text
This document is authentic, but a newer amended version exists.
```

Superseded:

```text
This version has been superseded by a newer version.
```

Revoked:

```text
This document was issued but has been revoked. Do not rely on it.
```

Cancelled:

```text
This document was cancelled and is not valid for use.
```

Expired:

```text
This document is authentic but has expired.
```

Entered in error:

```text
This document was entered in error and should not be relied on.
```

Not found:

```text
This document could not be verified. Check the code and try again.
```

## 41.3 Authenticated Detail View

If authorized, show:

```text
full document preview
download PDF
audit access
version history
amendment history
source facility
issuer
```

---

# 42. Document Security Rules

## 42.1 Required

```text
server-side PDF generation
unique document number
verification QR code
verification code
token hash
document hash
payload hash
status tracking
version tracking
amendment history
issuer tracking
signature block
audit logs
access permissions
watermarks
privacy-safe public verification
```

## 42.2 Blocked

Do not allow:

```text
editing issued PDF silently
removing verification QR
public QR exposing full document
using predictable tokens
downloading sensitive document without authorization
issuing document without issuer/facility
issuing lab result without validation
issuing prescription without authorized prescriber
issuing receipt without transaction/payment reference
issuing document with unpublished template
deleting issued document without legal retention logic
```

---

# 43. Document Access and Permissions

Access depends on:

```text
role
facility
organization
patient consent
document type
document sensitivity
source facility
country policy
legal basis
```

## 43.1 Patient

Can view own released documents where policy allows.

## 43.2 Provider

Can view documents for patients under care relationship or consent/policy.

## 43.3 Laboratory

Can view lab-related documents it issued or was authorized to process.

## 43.4 Pharmacy

Can view prescriptions and dispensing documents relevant to its workflow.

## 43.5 Insurance

Can view financial/claim documents within minimum necessary scope.

## 43.6 Public Health

Can view approved public health documents, mostly aggregate/de-identified.

## 43.7 Admin

Can manage templates and documents based on permission, but sensitive access is audited.

---

# 44. Document Lifecycle Flow

## 44.1 Create Document

1. Source event occurs.
2. System selects published template.
3. Data is assembled.
4. Standard code mappings are attached where available.
5. Privacy rules are applied.
6. Draft is generated.
7. Validation checks run.
8. Review/signature required if applicable.
9. Document is issued.
10. QR/token/code generated.
11. PDF hash and payload hash stored.
12. Document becomes verifiable.
13. Notification is sent where appropriate.
14. Audit event is created.

## 44.2 Amend Document

1. Authorized user requests amendment.
2. Reason is required.
3. New version is generated.
4. Old version marked superseded/amended.
5. Verification page reflects amendment.
6. Patient/authorized parties notified where applicable.
7. Audit event created.

## 44.3 Revoke Document

1. Authorized user selects revoke.
2. Reason required.
3. Document status changes to revoked.
4. Verification page shows revoked.
5. Relevant users notified where required.
6. Audit event created.

## 44.4 Entered in Error

1. Authorized user marks document entered in error.
2. Reason required.
3. Document blocked from normal use.
4. Verification page shows entered-in-error.
5. Audit event created.

---

# 45. Document Data Models

## 45.1 document_templates

```text
id
uuid
template_code
document_type
language
version
status
html_template
css_styles
plain_text_template nullable
created_by
approved_by nullable
published_at nullable
archived_at nullable
created_at
updated_at
```

## 45.2 official_documents

```text
id
uuid
document_type
document_number
verification_code
patient_id nullable
health_id nullable
facility_id nullable
organization_id nullable
issuer_user_id nullable
template_id
template_version
status
version
sensitivity_level
title
payload_json
standard_mapping_json nullable
pdf_path nullable
document_hash nullable
payload_hash nullable
issued_at nullable
released_at nullable
expires_at nullable
revoked_at nullable
revocation_reason nullable
created_at
updated_at
```

## 45.3 document_verification_tokens

```text
id
uuid
official_document_id
token_hash
status
expires_at nullable
revoked_at nullable
last_used_at nullable
created_at
updated_at
```

## 45.4 document_versions

```text
id
official_document_id
version
payload_json
standard_mapping_json nullable
pdf_path
document_hash
payload_hash
change_reason
created_by
created_at
```

## 45.5 document_signatures

```text
id
official_document_id
signer_user_id
signer_name
signer_role
signer_license_number nullable
signer_license_body nullable
signature_type
signed_at
signature_metadata_json
created_at
updated_at
```

## 45.6 document_verification_events

```text
id
uuid
official_document_id nullable
verification_code nullable
token_hash nullable
result
ip_address
user_agent
verified_by_user_id nullable
public_verification
created_at
```

## 45.7 document_access_logs

```text
id
uuid
official_document_id
actor_id
actor_type
action
ip_address
user_agent
created_at
```

## 45.8 document_share_links

```text
id
uuid
official_document_id
share_token_hash
created_by
recipient_contact nullable
expires_at
revoked_at nullable
access_count
max_access_count nullable
created_at
updated_at
```

## 45.9 document_code_mappings

```text
id
official_document_id
resource_type
local_code
standard_code
code_system
mapping_status
mapped_by nullable
mapped_at nullable
created_at
updated_at
```

## 45.10 document_specimen_events

```text
id
official_document_id
sample_id
event_type
performed_by nullable
location nullable
timestamp
notes nullable
created_at
updated_at
```

---

# 46. API Endpoints

## 46.1 Documents

```text
GET  /api/v1/documents
POST /api/v1/documents
GET  /api/v1/documents/{id}
POST /api/v1/documents/{id}/issue
POST /api/v1/documents/{id}/release
POST /api/v1/documents/{id}/amend
POST /api/v1/documents/{id}/revoke
POST /api/v1/documents/{id}/entered-in-error
GET  /api/v1/documents/{id}/download
GET  /api/v1/documents/{id}/versions
```

## 46.2 Templates

```text
GET  /api/v1/admin/document-templates
POST /api/v1/admin/document-templates
PUT  /api/v1/admin/document-templates/{id}
POST /api/v1/admin/document-templates/{id}/submit-review
POST /api/v1/admin/document-templates/{id}/approve
POST /api/v1/admin/document-templates/{id}/publish
POST /api/v1/admin/document-templates/{id}/archive
POST /api/v1/admin/document-templates/{id}/rollback
```

## 46.3 Verification

```text
GET  /verify/document/{token}
POST /api/v1/document-verification/verify-code
GET  /api/v1/documents/{id}/verification-events
```

## 46.4 Sharing

```text
POST /api/v1/documents/{id}/share-links
GET  /api/v1/documents/share/{token}
POST /api/v1/documents/share-links/{id}/revoke
```

## 46.5 Document Generation Shortcuts

```text
POST /api/v1/lab-requests/{id}/generate-document
POST /api/v1/lab-results/{id}/generate-report
POST /api/v1/prescriptions/{id}/generate-document
POST /api/v1/invoices/{id}/generate-document
POST /api/v1/receipts/{id}/generate-document
POST /api/v1/referrals/{id}/generate-document
POST /api/v1/discharges/{id}/generate-summary
```

---

# 47. Audit Events

```text
document_template_created
document_template_approved
document_template_published
document_template_rolled_back
document_created
document_draft_generated
document_issued
document_released
document_downloaded
document_printed
document_shared
document_share_link_revoked
document_amended
document_revoked
document_cancelled
document_entered_in_error
document_qr_generated
document_verified_publicly
document_verified_authenticated
document_verification_failed
document_access_denied
document_signature_applied
document_hash_mismatch_detected
document_code_mapping_created
document_specimen_event_recorded
```

Audit fields:

```text
actor_id
actor_role
document_id
document_type
patient_id nullable
facility_id nullable
organization_id nullable
action
old_status nullable
new_status nullable
reason nullable
ip_address
user_agent
timestamp
```

---

# 48. Error Codes

```text
DOCUMENT_NOT_FOUND
DOCUMENT_TEMPLATE_NOT_FOUND
DOCUMENT_TEMPLATE_NOT_PUBLISHED
DOCUMENT_ACCESS_DENIED
DOCUMENT_NOT_ISSUED
DOCUMENT_ALREADY_ISSUED
DOCUMENT_REVOKED
DOCUMENT_CANCELLED
DOCUMENT_EXPIRED
DOCUMENT_ENTERED_IN_ERROR
DOCUMENT_SUPERSEDED
DOCUMENT_VERIFICATION_FAILED
DOCUMENT_VERIFICATION_TOKEN_EXPIRED
DOCUMENT_VERIFICATION_TOKEN_REVOKED
DOCUMENT_HASH_MISMATCH
DOCUMENT_AMENDMENT_REASON_REQUIRED
DOCUMENT_REVOCATION_REASON_REQUIRED
DOCUMENT_ENTERED_IN_ERROR_REASON_REQUIRED
DOCUMENT_ISSUER_REQUIRED
DOCUMENT_SIGNATURE_REQUIRED
DOCUMENT_QR_REQUIRED
DOCUMENT_NUMBER_COLLISION
DOCUMENT_SHARE_LINK_EXPIRED
DOCUMENT_SHARE_LINK_REVOKED
DOCUMENT_PUBLIC_VERIFICATION_LIMITED
```

---

# 49. Bilingual Requirements

All templates must support English and French.

Each document template must have:

```text
English title
French title
English labels
French labels
English footer
French footer
English privacy notes
French privacy notes
```

Examples:

```text
Laboratory Result Report → Rapport de résultat de laboratoire
Prescription → Ordonnance
Payment Receipt → Reçu de paiement
Invoice → Facture
Referral Letter → Lettre de référence
Discharge Summary → Résumé de sortie
Scan to verify → Scanner pour vérifier
Verification Code → Code de vérification
Document Status → Statut du document
Reference Range → Intervalle de référence
Specimen → Échantillon
Signature → Signature
```

---

# 50. Print and Download Requirements

## 50.1 PDF

PDF must be:

```text
A4-ready
print-friendly
mobile-readable
small file size where possible
embedded QR code
consistent fonts
page numbers
header/footer repeated on multipage documents
extractable text where possible
```

## 50.2 Thermal Receipt Option

For pharmacy/receipt workflows, support optional narrow receipt layout:

```text
80mm thermal receipt
compact QR code
short verification code
itemized payment
```

Official full receipt PDF should still be available.

## 50.3 Watermarks

Use clear watermarks for:

```text
draft
revoked
cancelled
superseded
expired
entered in error
```

---

# 51. Template UI Requirements

## 51.1 Admin Template Manager

```text
template list
template preview
language switch
version history
approval workflow
test render with dummy data
publish/archive buttons
rollback
standard mapping preview
```

## 51.2 Document Viewer

```text
document preview
download PDF
print
share where allowed
verify
status badge
version history
audit trail where authorized
amendment history
code mapping details where authorized
```

## 51.3 Patient Document Center

```text
documents list
filter by type
download
share temporary link where allowed
revoke share link
view verification status
```

## 51.4 Staff Document Center

```text
documents by patient
documents by encounter
documents by facility
pending review
issued documents
revoked/amended documents
```

---

# 52. Testing Requirements

Required tests:

1. Lab request PDF renders.
2. Lab result PDF renders.
3. Prescription PDF renders.
4. Receipt PDF renders.
5. Invoice PDF renders.
6. Referral PDF renders.
7. Discharge summary PDF renders.
8. QR code appears on every official document.
9. Verification code appears on every official document.
10. QR payload does not contain clinical details.
11. Verification token is stored hashed.
12. Public verification does not show lab result values.
13. Public verification does not show full prescription details.
14. Public verification does not show referral clinical summary.
15. Public verification does not show discharge diagnosis.
16. Public verification does not show sick leave diagnosis.
17. Revoked document shows revoked status.
18. Amended document shows amended/superseded status.
19. Old version remains accessible to authorized users.
20. Document hash is stored.
21. Payload hash is stored.
22. Hash mismatch is detected.
23. Issued document cannot be silently edited.
24. Amendment requires reason.
25. Revocation requires reason.
26. Entered-in-error requires reason.
27. Draft watermark appears on draft.
28. Revoked watermark appears on revoked.
29. Superseded watermark appears on old amended version.
30. French template renders.
31. Multipage document has page numbers.
32. Patient can view own released documents.
33. Unauthorized user cannot download sensitive document.
34. Pharmacy can verify prescription with authorization.
35. Insurer cannot view full clinical document without scope.
36. Document verification abuse is rate-limited.
37. Thermal receipt layout renders where enabled.
38. Template versioning works.
39. Template rollback works.
40. LOINC/local code mapping fields exist for lab tests.
41. Specimen chain-of-custody can be recorded.
42. Prescription supports medication coding fields.
43. Dispensing receipt supports batch/lot/expiry/GTIN fields.
44. Temporary share link expires.
45. Revoked share link cannot be used.
46. Offline verification is marked pending online verification.
47. Audit events are created.
48. PDF renders correctly on desktop and mobile download.
49. QR remains scannable after A4 print.
50. QR remains scannable on thermal receipt where enabled.

---

# 53. Acceptance Criteria

This module is complete when:

1. All official document types are defined.
2. Every official document has QR verification.
3. Every official document has alphanumeric verification code.
4. QR does not contain full document content.
5. Verification tokens are secure and hashed.
6. Public verification is privacy-safe per document type.
7. Authenticated verification supports role-based detail.
8. Document numbering is non-predictable enough for public use.
9. Templates are versioned.
10. Templates require review/approval before publication.
11. PDF generation is server-side.
12. PDF hash is stored.
13. Payload hash is stored.
14. Issued documents cannot be silently edited.
15. Amendment workflow exists.
16. Revocation workflow exists.
17. Entered-in-error workflow exists.
18. Status watermarks exist.
19. Lab test request template exists.
20. Lab result report template exists.
21. Prescription template exists.
22. Pharmacy dispensing receipt template exists.
23. Invoice template exists.
24. Payment receipt template exists.
25. Referral template exists.
26. Discharge summary template exists.
27. Consultation summary template exists.
28. Medical certificate template exists.
29. Sick leave certificate template exists.
30. Vaccination certificate template exists.
31. Blood request/issue template exists.
32. Consent receipt template exists.
33. Emergency access report template exists.
34. Health ID card template exists.
35. Public health receipt template exists.
36. Partner/facility verification certificate exists.
37. API/integration certificate exists.
38. FHIR-aligned mapping matrix exists.
39. Lab coding fields support LOINC/local code mapping.
40. Diagnosis coding fields support ICD/SNOMED/local where available.
41. Medication coding supports local/ATC/RxNorm/GTIN where available.
42. Specimen chain-of-custody exists.
43. Issuer credentials and signature types exist.
44. QR expiry rules differ by document type.
45. Offline verification rules exist.
46. Document sharing controls exist.
47. Accessibility and print QA requirements exist.
48. English and French labels exist.
49. A4 and mobile readability are supported.
50. Thermal receipt option exists for receipts/pharmacy where enabled.
51. All sensitive document access is audited.
52. Tests cover rendering, verification, privacy, amendment, revocation, sharing, coding, specimen tracking, offline behavior, and access permissions.

---

# 54. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md, docs/identity/OPESCARE_MEDICAL_ID_SYSTEM_FINAL.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, docs/communications/OPESCARE_COMMUNICATION_ALERTS_TASKS_MESSAGING_SYSTEM.md, and docs/documents/OPESCARE_VERIFIABLE_DOCUMENT_TEMPLATES_V2.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS document templates, code, UI, database, numbering format, or assumptions.

Task: Create the OpesCare Verifiable Document Template System V2 foundation.

Scope:
1. Create module placeholder: app/Modules/Documents.
2. Create docs/documents folder if missing.
3. Add model placeholders:
   - DocumentTemplate
   - OfficialDocument
   - DocumentVerificationToken
   - DocumentVersion
   - DocumentSignature
   - DocumentVerificationEvent
   - DocumentAccessLog
   - DocumentShareLink
   - DocumentCodeMapping
   - DocumentSpecimenEvent

4. Add services:
   - DocumentNumberService
   - DocumentVerificationService
   - QrCodeGenerationService
   - DocumentPdfRenderService
   - DocumentTemplateService
   - DocumentAmendmentService
   - DocumentRevocationService
   - DocumentAccessPolicyService
   - DocumentShareService
   - DocumentHashService
   - DocumentCodeMappingService
   - SpecimenChainOfCustodyService

5. Add routes for:
   - documents
   - templates
   - verification
   - downloads
   - versions
   - sharing
   - issuing/releasing/amending/revoking/entered-in-error

6. Add base A4 document layout with:
   - header
   - facility branding area
   - facility license area
   - document title
   - verification QR block
   - verification code
   - document status
   - footer
   - watermark support
   - page numbering

7. Add first templates:
   - lab test request
   - lab result report
   - prescription
   - payment receipt
   - invoice
   - referral letter

8. Add QR verification:
   - QR points to /verify/document/{token}
   - token stored hashed
   - QR payload does not contain clinical details

9. Add alphanumeric verification code:
   - VFY-{COUNTRY}-{TYPE}-{YEAR}-{RANDOM}-{CHECK}

10. Add document hash and payload hash placeholders.

11. Add FHIR-aligned mapping metadata fields.

12. Add coding fields:
   - LOINC/local code for lab tests
   - medication code fields
   - diagnosis code fields
   - GTIN/batch/lot/expiry fields where applicable

13. Add specimen chain-of-custody placeholders.

14. Add public verification page placeholder:
   - show authenticity/status only
   - do not show clinical details publicly

15. Add document sharing placeholder:
   - temporary share link
   - expiry
   - revocation
   - audit

16. Add tests proving:
   - every issued document has QR
   - every issued document has verification code
   - QR payload does not contain clinical data
   - token is stored hashed
   - lab result public verification does not show result values
   - prescription public verification does not show medication list
   - issued document cannot be silently edited
   - amendment requires reason
   - revoked document shows revoked status
   - entered-in-error document cannot be relied on
   - French labels render
   - LOINC/local code fields exist
   - specimen chain-of-custody can be recorded
   - share link expires
   - audit events are created

17. Do not implement full clinical modules in this task.
18. Do not expose patient data in placeholder responses.
19. Open a PR with summary, files created, screenshots/PDF previews, tests, risks, and next recommended tasks.
```

---

# 55. Final Rule

OpesCare documents must be trusted, verifiable, standards-aware, and privacy-safe.

The correct model is:

```text
professional template
official document number
secure QR verification
human verification code
server-side PDF
document hash
payload hash
FHIR-aligned metadata
clinical code mappings
issuer credential block
signature type
status tracking
version history
amendment workflow
revocation workflow
entered-in-error workflow
privacy-safe public verification
role-based authenticated access
document sharing controls
offline verification caution
audit everything
```

If a document can be forged, silently edited, publicly expose sensitive health information, fail to verify, or cannot be mapped to structured clinical data where appropriate, it is not production-ready.
