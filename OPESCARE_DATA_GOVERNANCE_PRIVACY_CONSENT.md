# OpesCare Data Governance, Privacy, Consent, Compliance, and Patient Rights PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Domain:** opescare.com  
**Build Direction:** Build from scratch  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Specialist Services:** Python/FastAPI later for risk scoring, anomaly detection, de-identification assistance, and audit intelligence  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, database structure, privacy model, consent model, access model, UI, or assumptions.

---

# 1. Purpose of This Document

This document defines the complete data governance, privacy, consent, compliance, and patient rights model for OpesCare.

OpesCare is a digital Health ID and healthcare interoperability platform. It will process sensitive health information. Therefore, the platform must be designed with strict privacy, consent, role-based access, auditability, source attribution, governance review, and patient rights from the foundation.

This document covers:

1. Data governance principles
2. Patient data categories
3. Legal and compliance posture
4. Consent model
5. Emergency access model
6. Patient rights model
7. Data access rules
8. Data sharing rules
9. Data minimization
10. Data retention
11. Data correction and amendment
12. Audit and access logs
13. Public health and government reporting boundaries
14. Research data access
15. Insurer access limits
16. Guardian and dependent access
17. Data export and portability
18. Breach and incident workflow
19. Data localization and country policy
20. Roles and permissions
21. APIs and UI requirements
22. Testing and acceptance criteria

This document is not final legal advice. Before launch, OpesCare must be reviewed by qualified legal, medical, privacy, and health regulatory professionals in each country where it operates.

---

# 2. Core Governance Principle

OpesCare must never treat patient health data like ordinary application data.

Every data action must answer:

1. Who is requesting access?
2. Which patient is affected?
3. Which facility is involved?
4. What data is requested?
5. Why is the data needed?
6. Is patient consent required?
7. Is the user authorized?
8. Is the facility verified?
9. Is the data sensitive?
10. Is the data minimum necessary?
11. Is the action audited?
12. Can the patient see this access later?
13. Can the data be corrected without erasing history?
14. Can this data be shared externally?
15. Is there a legal or policy basis?

If these questions cannot be answered, the data action must be blocked or sent for review.

---

# 3. Product Position

OpesCare must communicate clearly:

**Patients are not products. Patient data is not public. OpesCare exists to help approved healthcare providers access the right information at the right time, under clear rules, consent, purpose, and audit.**

---

# 4. Data Governance Objectives

OpesCare data governance must:

1. Protect patient privacy.
2. Support safe medical care.
3. Enable consent-based sharing.
4. Prevent unauthorized access.
5. Preserve audit trails.
6. Maintain record provenance.
7. Support patient rights.
8. Allow public health reporting only under approved rules.
9. Support research only under governance review.
10. Prevent uncontrolled data export.
11. Ensure every record has a source.
12. Prevent silent overwrites of clinical records.
13. Support country-specific health policy.
14. Keep the platform trusted by patients, facilities, and regulators.

---

# 5. Patient Data Categories

OpesCare may process these categories of patient data.

## 5.1 Identity Data

Examples:

```text
Health ID
name
date of birth
sex
phone number
email
address
photo
national ID where used
insurance number
facility patient number
guardian/dependent relationship
```

## 5.2 Clinical Data

Examples:

```text
consultations
diagnoses
allergies
vital signs
triage records
prescriptions
dispense records
lab results
imaging reports
referrals
admission records
discharge summaries
nursing notes
surgical notes
care plans
death records
medical documents
```

## 5.3 Administrative Data

Examples:

```text
appointments
queues
billing
invoices
receipts
insurance eligibility
claims
facility information
staff records
role assignments
```

## 5.4 Consent and Access Data

Examples:

```text
consent requests
consent grants
consent denials
consent revocations
access logs
emergency access records
purpose of use
data scope
expiry time
```

## 5.5 Integration Data

Examples:

```text
source system
external patient ID
external record ID
API client ID
sync status
webhook status
reconciliation cases
Bridge Agent records
SDK request logs without PHI
```

## 5.6 Public Health Data

Examples:

```text
notifiable disease reports
aggregate facility reports
lab surveillance reports
medicine stock-out reports
blood shortage reports
vaccination reports
public health signals
```

## 5.7 Technical and Security Data

Examples:

```text
login events
device registrations
IP address
user agent
session data
failed login attempts
API access logs
rate-limit events
security alerts
```

---

# 6. Data Sensitivity Classification

Every data object must have a sensitivity class.

## 6.1 Public

Information that can appear on public pages.

Example:

```text
OpesCare product description
public contact information
public API documentation without secrets
```

## 6.2 Internal

Operational data for OpesCare and facility staff.

Example:

```text
dashboard configuration
non-sensitive facility setup
general workflow status
```

## 6.3 Confidential

Sensitive but not necessarily clinical.

Example:

```text
staff account details
organization applications
billing records
facility credentials
API client settings
```

## 6.4 Patient Health Information

Sensitive patient medical data.

Example:

```text
diagnoses
lab results
prescriptions
allergies
consultations
medical documents
```

## 6.5 Highly Sensitive Health Information

Health data requiring extra restrictions.

Examples may include:

```text
HIV/STI data
mental health records
sexual and reproductive health records
genetic information
substance use records
domestic violence-related records
high-risk infectious disease records
minors' sensitive care records
```

The exact list must be configurable by country policy and facility policy.

## 6.6 Security-Critical

Data that could compromise the platform.

Example:

```text
API secrets
encryption keys
session tokens
webhook secrets
Bridge Agent credentials
admin credentials
```

Security-critical data must never appear in normal logs, exports, or UI.

---

# 7. Legal and Compliance Posture

OpesCare must be designed to adapt to country-specific legal requirements.

The platform should support configuration for:

```text
country policy
data retention period
age of consent
guardian rules
emergency access rules
public health reporting rules
research access rules
data localization rules
patient access rights
data correction rights
data export rules
breach notification rules
```

## Important Legal Review Rule

Before production launch in any country, OpesCare must undergo legal and regulatory review for:

- health data processing
- patient consent
- facility onboarding
- public health reporting
- research access
- data sharing with insurers
- data exports
- data hosting
- breach response
- terms and privacy policies

---

# 8. Lawful Basis / Processing Basis Model

Every sensitive data action should have a processing basis.

Supported basis values:

```text
patient_consent
treatment
emergency_care
legal_obligation
public_health_reporting
insurance_processing
research_governance_approval
facility_operations
patient_request
system_security
```

Rules:

- Consent is required where policy says consent is required.
- Treatment access still requires role, purpose, and audit.
- Emergency access requires reason and review.
- Public health reporting must be minimum necessary.
- Insurance access must be scoped to necessary data.
- Research access requires governance approval.
- System security access must be logged and limited.

---

# 9. Consent Model

## 9.1 What Consent Means

Consent means a patient, guardian, or authorized representative approves access to specific health information for a specific purpose and time period.

Consent must not be vague or unlimited by default.

Every consent grant must define:

```text
patient
requesting facility
requesting user or role
purpose
data scope
start time
expiry time
approval method
status
audit trail
```

## 9.2 Consent Statuses

```text
requested
pending
granted
denied
expired
revoked
cancelled
superseded
emergency_override
```

## 9.3 Consent Request Flow

1. Facility requests access.
2. System identifies requested patient.
3. System checks facility verification.
4. System checks user role.
5. System checks requested purpose.
6. System checks requested data scope.
7. Consent request is sent to patient/guardian where required.
8. Patient views request in clear language.
9. Patient approves or denies.
10. Consent grant or denial is stored.
11. Facility is notified.
12. Audit event is created.

## 9.4 Consent Request Must Show

Patient-facing request must show:

```text
who is asking
which facility is asking
why they are asking
what information they want to see
how long access will last
what happens if patient denies
approve button
deny button
privacy note
```

Example:

```text
Central Clinic is requesting access to your recent medical summary, allergies, active medications, and recent lab results for consultation. This access will expire in 4 hours.
```

French:

```text
La Clinique Centrale demande l’accès à votre résumé médical récent, vos allergies, vos médicaments actifs et vos résultats de laboratoire récents pour une consultation. Cet accès expirera dans 4 heures.
```

## 9.5 Consent Scope Examples

```text
patient.summary
allergies.read
active_medications.read
lab_results.recent.read
prescriptions.recent.read
documents.selected.read
referral.package.read
insurance.minimum_necessary.read
```

## 9.6 Consent Expiry

Consent must expire automatically.

Default expiry examples:

```text
consultation access: 4 to 24 hours
referral access: configurable, e.g. 7 to 30 days
insurance claim access: claim-specific
guardian access: policy-based
emergency override: short and review-triggered
```

Actual expiry should be configurable by country and facility policy.

## 9.7 Consent Revocation

Patients should be able to revoke consent where policy allows.

Revocation rules:

- Revocation stops future access.
- Revocation does not erase legally retained past records.
- Revocation does not remove audit logs.
- Revocation does not undo access that already happened.
- Facility should receive consent.revoked event where integrated.

---

# 10. Emergency Access Model

Emergency access allows approved providers to view a limited emergency profile when normal consent cannot be obtained.

## 10.1 Emergency Access Requirements

Required:

```text
verified facility
authorized user
patient selection
emergency reason
purpose = emergency
limited emergency profile
audit event
post-access review
patient notification where safe and policy allows
```

## 10.2 Emergency Profile May Include

```text
patient identity
Health ID
blood group
critical allergies
chronic conditions
high-risk medications
emergency contacts
critical warnings
```

## 10.3 Emergency Profile Must Not Include by Default

```text
full consultation history
full lab history
full prescriptions history
full billing information
insurance claims
sensitive notes
full documents
```

## 10.4 Emergency Access Review

Every emergency access creates a review case.

Review statuses:

```text
pending_review
approved_as_valid
requires_explanation
suspected_abuse
confirmed_abuse
closed
```

## 10.5 Emergency Access Abuse

If abuse is suspected:

- create governance case
- suspend access if needed
- notify facility admin
- notify OpesCare security team
- preserve audit logs
- follow legal/policy notification requirements

---

# 11. Patient Rights

OpesCare must support patient rights where applicable.

## 11.1 Right to Access

Patients should be able to view:

```text
Health ID
medical summary
timeline entries where allowed
lab results where released
prescriptions
consent requests
access logs
documents
dependents
```

## 11.2 Right to Know Who Accessed Records

Patients should see access logs showing:

```text
date/time
facility
role/provider type
purpose
data category accessed
emergency access flag
```

Do not expose staff private data beyond policy.

## 11.3 Right to Correct Information

Patients should be able to request correction of inaccurate information.

Correction process:

1. Patient submits correction request.
2. Facility or authorized reviewer receives request.
3. Reviewer approves, rejects, or asks for evidence.
4. If approved, correction is applied as amendment.
5. Original record is preserved.
6. Audit event is logged.

## 11.4 Right to Download or Export

Patients may request export of their records where policy allows.

Export types:

```text
patient summary PDF
selected timeline export
lab results export
prescription history export
complete structured export later
```

Exports must be audited.

Sensitive exports may require OTP/PIN confirmation.

## 11.5 Right to Revoke Access

Patients may revoke active consent grants where policy allows.

## 11.6 Right to Manage Dependents

Patients/guardians may manage dependents according to legal and policy rules.

## 11.7 Right to Report Suspicious Access

Patient portal must include:

```text
Report suspicious access
```

Flow:

1. Patient opens access log.
2. Patient selects suspicious entry.
3. Patient submits concern.
4. Compliance case is created.
5. OpesCare/facility reviewer investigates.
6. Patient receives status update where allowed.

---

# 12. Guardian and Dependent Access

Guardian access must be scoped and verified.

## 12.1 Guardian Access Types

```text
parent_guardian
legal_guardian
caregiver
temporary_caregiver
healthcare_proxy
```

## 12.2 Guardian Statuses

```text
requested
pending_verification
active
denied
expired
revoked
suspended
```

## 12.3 Guardian Rules

- Guardian access must have legal or approved basis.
- Caregiver is not automatically legal guardian.
- Guardian access must be scoped.
- Guardian access must expire or be reviewed.
- Minor-to-adult transition must trigger access review.
- Guardian actions must be audited.

## 12.4 Minor-to-Adult Transition

When dependent reaches configured age:

1. System flags account for transition.
2. Patient is notified where contact exists.
3. Guardian access is reviewed.
4. Patient may claim own account.
5. Access is adjusted by policy.
6. Audit event is logged.

---

# 13. Role-Based Access Control

Access must be role-based and facility-context based.

## 13.1 Example Roles

```text
patient
guardian
doctor
nurse
lab_staff
lab_validator
pharmacist
cashier
insurance_officer
facility_admin
public_health_officer
research_reviewer
opesware_support
security_admin
system_admin
```

## 13.2 Access Rules

- Doctors can view clinical data only with proper purpose and consent/policy.
- Nurses can access patient care information relevant to their workflow.
- Lab staff can access lab orders and sample data, not full history by default.
- Pharmacists can access prescription and allergy warnings relevant to dispensing.
- Cashiers cannot view full clinical details.
- Insurers receive minimum necessary data.
- Public health users receive approved reports, not full records by default.
- Opesware support access must be limited, audited, and justified.

---

# 14. Purpose-of-Use Control

Every sensitive access must include purpose.

Allowed purposes:

```text
treatment
emergency
referral
lab_processing
pharmacy_dispensing
insurance_eligibility
insurance_claim
billing
public_health_reporting
research_approved
patient_request
system_security
support
```

Purpose must be audited.

Some purposes require consent.

Some purposes require governance approval.

Some purposes are facility-only.

---

# 15. Data Minimization

Every module must use minimum necessary data.

Examples:

## Pharmacy

Pharmacist may need:

```text
prescription
allergy warnings
dispense history
patient identity confirmation
```

Pharmacist usually does not need:

```text
full consultation notes
full imaging reports
insurance claim details
```

## Cashier

Cashier may need:

```text
invoice
service names
payment status
patient billing identity
```

Cashier usually does not need:

```text
diagnosis details
lab result values
sensitive notes
```

## Insurance

Insurer may need:

```text
eligibility
claim-related service information
minimum supporting documentation
```

Insurer should not receive:

```text
full patient history
unrelated diagnoses
unrelated prescriptions
full timeline
```

---

# 16. Data Sharing Rules

Data may be shared only with:

```text
verified healthcare facilities
verified pharmacies
verified labs
verified insurers
approved public health systems
approved research governance users
patient/guardian
approved technology integrations
```

Data sharing requires:

```text
authentication
authorization
facility verification
purpose
scope
consent or legal basis
audit
source attribution
```

---

# 17. Public Health and Government Reporting Boundaries

Government reporting must follow the Public Health Reporting Module rules.

Default:

- aggregate reports
- de-identified data
- minimum necessary fields
- review before submission where required

Do not send:

- full patient history
- full timeline
- unrestricted patient identity
- unreviewed suspected signals as confirmed
- sensitive patient data without policy

Identifiable reporting only when legally required and configured.

---

# 18. Research Data Access

Research access requires governance approval.

## 18.1 Research Workflow

1. Researcher submits request.
2. Ethics/governance documents uploaded.
3. Governance review occurs.
4. Data minimization plan is approved.
5. De-identification profile is selected.
6. Small-cell suppression applied where needed.
7. Dataset preview generated.
8. Access granted with expiry.
9. All access audited.
10. Access revoked at expiry or violation.

## 18.2 Research Rules

- No identifiable export by default.
- Ethics approval required.
- Access must expire.
- Researcher activity must be logged.
- Dataset must be reproducible and versioned.
- Small groups must be protected from re-identification.

---

# 19. Insurance Data Access

Insurance access must be limited.

Insurers may access data for:

```text
eligibility
preauthorization
claim review
payment posting
policy-related workflows
```

Rules:

- Minimum necessary only.
- No full timeline by default.
- Sensitive unrelated history must be hidden.
- Claim data access must be audited.
- Patient consent may be required depending on policy.
- Facility and insurer contracts must define allowed access.

---

# 20. Data Retention

Retention must be configurable by country and data type.

Data types:

```text
clinical records
audit logs
consent logs
billing records
insurance records
integration logs
public health reports
research datasets
security logs
uploaded documents
```

Rules:

- Clinical records usually require long retention.
- Audit logs should be append-only and retained according to policy.
- Tokens and temporary sessions should expire quickly.
- Export files should expire or be access-controlled.
- Deletion must not break legal medical record requirements.
- Legal hold must pause deletion.

---

# 21. Deletion, Archiving, and Legal Hold

Healthcare records should usually not be hard-deleted.

Use:

```text
archive
inactive
entered_in_error
amended
revoked
expired
deceased
legal_hold
```

## Hard Delete

Hard delete should be restricted to:

- temporary technical artifacts
- expired session tokens
- failed incomplete uploads
- data allowed by policy to be deleted
- duplicates after safe merge/unmerge procedures where lineage remains

Clinical records should be amended or marked entered-in-error, not silently deleted.

---

# 22. Correction and Amendment

Clinical data must not be silently overwritten.

Correction rules:

- Original remains preserved.
- Amendment reason required.
- Amended by stored.
- Amendment time stored.
- Patient/facility can see corrected state where authorized.
- Audit event created.

Use amendment for:

```text
signed notes
released lab results
issued prescriptions
discharge summaries
public health reports
billing reversals
```

---

# 23. Audit Logs

Audit logs must be append-only.

## 23.1 Audit Events

Audit:

```text
login
logout
patient_search
patient_profile_view
consent_requested
consent_granted
consent_denied
consent_revoked
emergency_access
summary_pulled
record_pushed
lab_result_released
prescription_issued
document_downloaded
record_exported
access_log_viewed
guardian_access_created
guardian_access_revoked
report_submitted
research_dataset_exported
insurance_claim_data_accessed
support_access
role_changed
facility_suspended
api_token_created
api_token_revoked
```

## 23.2 Audit Fields

```text
actor_id
actor_type
role
facility_id
organization_id
patient_id
health_id_reference
action
resource_type
resource_id
purpose
scope
ip_address
user_agent
source_system
timestamp
result
reason
correlation_id
```

## 23.3 Audit UI

Audit screens must support:

```text
search by patient
search by actor
search by facility
search by date
filter emergency access
filter exports
filter suspicious access
view event detail
export only with permission
```

---

# 24. Access Logs for Patients

Patient-facing access logs must be clear.

Show:

```text
date/time
facility
role/provider type
purpose
data category
emergency access flag
```

Do not show technical jargon.

Example:

```text
Central Clinic viewed your medical summary for consultation on May 17, 2026.
```

French:

```text
La Clinique Centrale a consulté votre résumé médical pour une consultation le 17 mai 2026.
```

---

# 25. Support Access

Opesware support/admin access must be limited.

Rules:

- support access requires reason
- access is time-limited
- support users cannot view clinical details unless specially approved
- all support access audited
- break-glass support access requires review
- support impersonation must be avoided or heavily controlled
- support should use metadata-only views where possible

---

# 26. Data Export

Export types:

```text
patient requested export
facility export
public health report export
research dataset export
insurance claim package export
audit export
```

Rules:

- exports require permission
- sensitive exports require extra confirmation
- export file access expires
- downloads audited
- export includes data classification
- export does not include unnecessary data
- black-and-white printable documents remain understandable

---

# 27. Data Breach and Security Incident Workflow

## 27.1 Incident Types

```text
unauthorized_access
suspected_credential_compromise
data_export_abuse
api_key_leak
malware_upload
misdirected_report
wrong_patient_access
breach_confirmed
```

## 27.2 Incident Workflow

1. Incident detected.
2. Security case created.
3. Severity assigned.
4. Access contained if needed.
5. Impact analysis performed.
6. Affected patients/facilities identified.
7. Legal/privacy review occurs.
8. Notifications sent where required.
9. Root cause analysis completed.
10. Corrective actions tracked.
11. Case closed.
12. Audit preserved.

## 27.3 Incident Statuses

```text
new
triaging
contained
under_investigation
confirmed
false_alarm
notification_required
resolved
closed
```

---

# 28. De-identification and Pseudonymization

Use de-identification for research and some public health outputs.

Remove:

```text
name
phone
email
exact address
national ID
exact Health ID
free-text identifiers
exact birth date where not needed
```

Keep where needed:

```text
age group
sex
district/region
disease category
time period
facility type
```

Pseudonymization may use case reference codes.

Mapping must be protected.

---

# 29. Small-Cell Suppression

For aggregate public/research outputs, suppress small counts.

Default threshold:

```text
5
```

Example:

If count < 5, display:

```text
<5
```

or suppress based on policy.

---

# 30. Country Policy Configuration

Create country policy configuration for:

```text
country
languages
data localization rules
age of consent
guardian access rules
retention periods
notifiable disease rules
emergency access rules
public health reporting rules
research access rules
insurance access rules
sensitive data classes
small-cell threshold
breach notification requirements
```

Country policies must be versioned and effective-dated.

---

# 31. Data Localization

Data localization must be configurable.

Rules:

- hosting region must be recorded
- country policy may restrict cross-border transfer
- backups must follow policy
- analytics exports must follow policy
- research datasets must follow policy
- external integrations must respect country rules

---

# 32. API Governance

External APIs must enforce:

```text
authentication
authorization
scope
purpose
consent
facility verification
rate limits
idempotency
audit
source attribution
data minimization
```

Sensitive pull endpoints must never return full patient records without explicit allowed scope.

---

# 33. UI Requirements

## 33.1 Patient Portal

Must include:

```text
Consent Requests
Active Access
Access Logs
Report Suspicious Access
Download My Records
Request Correction
Manage Dependents
Privacy Settings
```

## 33.2 Staff Portal

Must include:

```text
Consent Panel
Emergency Access Warning
Purpose of Use Selector
Patient Banner
Audit Notice for Sensitive Actions
Restricted Data Warning
```

## 33.3 Admin Portal

Must include:

```text
Audit Logs
Consent Policy
Country Policy
Emergency Access Reviews
Suspicious Access Cases
Data Export Requests
Research Requests
Public Health Reporting Rules
Breach/Incident Cases
```

---

# 34. Consent UI Text

## English

```text
This provider is requesting access to your health information. Review who is asking, why they need access, what they want to see, and how long access will last.
```

## French

```text
Ce prestataire demande l’accès à vos informations de santé. Vérifiez qui fait la demande, pourquoi l’accès est nécessaire, quelles informations seront consultées et combien de temps l’accès durera.
```

---

# 35. Emergency Access UI Text

## English

```text
Emergency access is audited. You must enter a reason before viewing this patient’s emergency profile.
```

## French

```text
L’accès d’urgence est audité. Vous devez indiquer une raison avant de consulter le profil d’urgence de ce patient.
```

---

# 36. Data Export Warning Text

## English

```text
This export may contain sensitive health information. Only download it if you are authorized and have a valid reason.
```

## French

```text
Cette exportation peut contenir des informations de santé sensibles. Téléchargez-la uniquement si vous êtes autorisé et si vous avez une raison valable.
```

---

# 37. Required Data Models

## consent_requests

```text
id
uuid
patient_id
requesting_organization_id
requesting_facility_id
requesting_user_id nullable
purpose
requested_scopes_json
status
expires_at
created_at
updated_at
```

## consent_grants

```text
id
uuid
consent_request_id
patient_id
granted_by_user_id
grant_method
scopes_json
purpose
starts_at
expires_at
revoked_at
status
created_at
updated_at
```

## access_logs

```text
id
uuid
patient_id
actor_id
actor_type
organization_id
facility_id
purpose
data_category
resource_type
resource_id nullable
access_type
emergency_access
ip_address
user_agent
created_at
```

## emergency_access_events

```text
id
uuid
patient_id
facility_id
actor_id
reason
status
review_status
reviewed_by nullable
reviewed_at nullable
created_at
updated_at
```

## correction_requests

```text
id
uuid
patient_id
requested_by_user_id
resource_type
resource_id
reason
supporting_document_id nullable
status
reviewed_by nullable
reviewed_at nullable
created_at
updated_at
```

## data_export_requests

```text
id
uuid
patient_id nullable
requested_by_user_id
export_type
scope_json
status
approved_by nullable
file_path nullable
expires_at nullable
created_at
updated_at
```

## security_incidents

```text
id
uuid
incident_type
severity
status
summary
detected_at
contained_at nullable
resolved_at nullable
created_by nullable
created_at
updated_at
```

## country_policies

```text
id
uuid
country_code
name
version
effective_from
effective_to nullable
settings_json
status
created_at
updated_at
```

## research_access_requests

```text
id
uuid
requesting_organization
principal_investigator
purpose
ethics_document_id nullable
requested_dataset_scope_json
status
reviewed_by nullable
approved_at nullable
expires_at nullable
created_at
updated_at
```

---

# 38. API Endpoints

## Consent

```text
GET  /api/mobile/consent-requests
POST /api/mobile/consent-requests/{id}/approve
POST /api/mobile/consent-requests/{id}/deny
POST /api/mobile/consents/{id}/revoke

POST /api/v1/connect/consents/request
POST /api/v1/connect/consents/verify
```

## Access Logs

```text
GET /api/mobile/access-logs
GET /api/v1/admin/access-logs
```

## Emergency Access

```text
POST /api/v1/connect/emergency-access/request
GET  /api/v1/connect/patients/{health_id}/emergency-profile
GET  /api/v1/admin/emergency-access/reviews
POST /api/v1/admin/emergency-access/{id}/review
```

## Correction Requests

```text
POST /api/mobile/correction-requests
GET  /api/v1/admin/correction-requests
POST /api/v1/admin/correction-requests/{id}/approve
POST /api/v1/admin/correction-requests/{id}/reject
```

## Data Export

```text
POST /api/mobile/data-export-requests
GET  /api/mobile/data-export-requests
GET  /api/v1/admin/data-export-requests
POST /api/v1/admin/data-export-requests/{id}/approve
POST /api/v1/admin/data-export-requests/{id}/reject
GET  /api/mobile/data-exports/{id}/download
```

## Security Incidents

```text
GET  /api/v1/admin/security-incidents
POST /api/v1/admin/security-incidents
POST /api/v1/admin/security-incidents/{id}/contain
POST /api/v1/admin/security-incidents/{id}/resolve
```

## Country Policies

```text
GET  /api/v1/admin/country-policies
POST /api/v1/admin/country-policies
PUT  /api/v1/admin/country-policies/{id}
POST /api/v1/admin/country-policies/{id}/publish
```

---

# 39. Audit Events

Required audit events:

```text
consent_request_created
consent_granted
consent_denied
consent_revoked
patient_record_accessed
emergency_access_used
emergency_access_reviewed
patient_access_log_viewed
correction_request_created
correction_request_approved
correction_request_rejected
record_amended
data_export_requested
data_export_approved
data_export_downloaded
support_access_used
research_request_created
research_request_approved
research_dataset_exported
public_health_report_submitted
country_policy_published
security_incident_created
security_incident_resolved
```

---

# 40. Testing Requirements

Required tests:

1. Patient can approve consent request.
2. Patient can deny consent request.
3. Patient can revoke active consent.
4. Expired consent blocks future access.
5. Revoked consent blocks future access.
6. Facility cannot access record without authorization.
7. Patient can view access logs.
8. Emergency access requires reason.
9. Emergency access creates review case.
10. Emergency profile excludes full history by default.
11. Cashier cannot view clinical notes.
12. Pharmacist sees allergies relevant to dispensing.
13. Insurer cannot access full timeline by default.
14. Public health report excludes full patient record.
15. Research export requires approval.
16. Data export is audited.
17. Correction request creates amendment, not overwrite.
18. Country policy changes are versioned.
19. Support access is audited.
20. Sensitive data cannot appear in ordinary logs.

---

# 41. Acceptance Criteria

This governance module is acceptable when:

1. Consent requests are scoped, purpose-based, and expiring.
2. Patients can approve, deny, and revoke consent where policy allows.
3. Emergency access is limited, reason-based, audited, and reviewed.
4. Patients can see access logs.
5. Patient correction requests exist.
6. Clinical records are amended, not silently overwritten.
7. Data exports require permission and audit.
8. Public health reporting is minimum necessary.
9. Research access requires governance approval.
10. Insurer access is limited to minimum necessary information.
11. Guardian access is verified and scoped.
12. Country policies are configurable and versioned.
13. Audit logs are append-only.
14. Sensitive data is protected from ordinary logs.
15. Support access is controlled.
16. Breach/incident workflow exists.
17. APIs enforce purpose, scope, consent, and authorization.
18. UI uses clear English and French language.
19. No full patient data is exposed without proper basis.
20. All high-risk actions are tested.

---

# 42. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, docs/public-health/OPESCARE_PUBLIC_HEALTH_REPORTING_PHASES.md, and docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS code, database, module structure, consent model, access model, UI, or API design.

Task: Create the Data Governance, Privacy, Consent, and Patient Rights module foundation.

Scope:
1. Create module placeholder: app/Modules/Governance.
2. Create docs/governance folder if missing.
3. Add model placeholders for ConsentRequest, ConsentGrant, AccessLog, EmergencyAccessEvent, CorrectionRequest, DataExportRequest, SecurityIncident, CountryPolicy, ResearchAccessRequest.
4. Add route placeholders for consent, access logs, emergency access, correction requests, data export, country policies, and security incidents.
5. Add policy placeholders for consent, emergency access, data export, and sensitive data access.
6. Add audit event enum placeholders for all governance events.
7. Add service placeholders: ConsentService, AccessLogService, EmergencyAccessService, CorrectionRequestService, DataExportService, CountryPolicyService.
8. Add tests proving:
   - consent is required for sensitive pull
   - expired consent blocks access
   - revoked consent blocks access
   - emergency access requires reason
   - access logs are created
   - data export requires permission
9. Do not implement final legal policies yet.
10. Do not implement full clinical modules in this task.
11. Do not expose patient data in placeholder responses.
12. Open a PR with summary, files created, risks, and next recommended tasks.
```

---

# 43. Final Rule

OpesCare must be trusted before it can be powerful.

The platform must protect patient data, explain access clearly, enforce consent and purpose, keep audit logs, support patient rights, and prevent uncontrolled sharing.

The correct governance model is:

```text
verify identity
verify facility
verify role
verify purpose
check consent or legal basis
minimize data
log access
show patient where appropriate
allow correction
protect sensitive data
review high-risk actions
```

If a data action cannot be justified, minimized, and audited, it must not happen.
