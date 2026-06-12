# OpesCare Complete Master Prompt V3 - Full Module Flows, Upgrade Existing Code + Build Missing Flows

**Project:** OpesCare  
**Company:** Opesware  
**Domain:** opescare.com  
**Stack:** Laravel + PostgreSQL, Redis, PostGIS where needed, selected Python services only where justified  
**Purpose:** This is the corrected master prompt for Claude Code, Jules, Gemini, Codex, or any engineering agent. It explicitly includes module-by-module flows. Every module must have working flows, not just models or mentions in the PRD.  

## Critical Instruction

This is an upgrade-and-complete prompt. A lot of OpesCare already exists. Do not rebuild working modules. Audit first, preserve working code, patch partial modules, and implement missing modules cleanly.

## Non-Negotiable Rule

A module is not complete because it is mentioned in product knowledge or PRD. A module is complete only when each required flow has:

```text
model/data persistence
migration or existing schema mapping
service/business logic
controller/API route or UI action
request validation
permissions/policies
audit logs where sensitive
notifications where needed
bilingual user-facing labels
tests for success, failure, permissions, and edge cases
```

## Do Not Do

```text
do not use OpesHIS OS
do not copy OpesHIS OS
do not duplicate existing models/tables/routes
do not rewrite working modules blindly
do not expose patient data publicly
do not mark any module complete without tests and evidence
do not let certification look like professional licensing
do not let CDSS act like automated diagnosis
do not let map/medicine/blood/insurance modules make guarantees
```

## Required Audit Before Coding

Read existing audit reports and docs first. Then classify each module as IMPLEMENTED, PARTIAL, NOT_STARTED, IMPLEMENTED_WITH_BUGS, NEEDS_TESTS, NEEDS_SECURITY_REVIEW, or DEFERRED.

```text
docs/audit/OPESCARE_IMPLEMENTATION_AUDIT_RESULT.md
docs/audit/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md
docs/PROJECT_KNOWLEDGE.md
docs/PRD.md
docs/UIUX_PRODUCT_INTERFACE_PRD.md
```

## Required Build Priority If Missing

```text
1. Appointments & Booking
2. Queue & Patient Flow
3. Billing, Payments & Wallet
4. Insurance Claims & Preauthorization
5. Appointment-to-Billing-to-Document End-to-End Visit Flow
6. Support / Helpdesk
7. Data Import / Migration
8. Master Admin Control Center
9. Facility Go-Live Readiness
10. Global Search
```

---


## Module 01 - Identity, Authentication & User Accounts

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Control secure access for every patient, staff member, partner, developer, public health user, and administrator.

**Actors:** Guest, Patient, Guardian, Staff User, Developer, Partner Admin, Super Admin

**Core Models / Tables:**
```text
User
UserProfile
UserSession
OtpCode
LoginAttempt
PasswordResetToken
DeviceSession
AccountStatusHistory
```

### Required Flow Implementations

#### Patient Signup Flow

**Steps:**

1. User opens signup
2. Selects patient account
3. Submits name, email/phone, password, language
4. System validates uniqueness and password rules
5. System creates pending user
6. OTP/email verification is sent
7. User verifies
8. Patient profile shell is created
9. Health ID generation is triggered or queued
10. Audit event account_created is recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Staff Invitation Flow

**Steps:**

1. Facility admin opens staff invitations
2. Inputs staff name, email/phone, role, department, facility
3. System checks admin permission
4. Invitation token is created
5. Staff receives invite
6. Staff accepts and sets password
7. Role/facility assignment is activated
8. Training/certification requirements are checked
9. Audit event staff_invited and staff_joined recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Organization/Partner Signup Flow

**Steps:**

1. Representative submits organization profile
2. System creates pending organization account
3. Representative uploads verification documents
4. Admin review case is created
5. Account is limited until approval
6. If approved, organization admin role is granted
7. If rejected, reason is sent
8. Audit event organization_signup_reviewed recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Developer Signup Flow

**Steps:**

1. Developer registers
2. Email verification required
3. Developer profile created
4. Sandbox-only access granted
5. Developer must complete API onboarding before production request
6. API security terms accepted
7. Audit event developer_registered recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Login Flow

**Steps:**

1. User submits identifier and password
2. System rate-limits attempts
3. Credentials are verified
4. MFA/OTP challenge is triggered when required
5. Facility context is loaded for staff
6. Session is created
7. Last login/device recorded
8. Suspicious login detection runs
9. Audit event login_success or login_failed recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Password Reset Flow

**Steps:**

1. User requests reset
2. System sends reset token without exposing account existence
3. User submits token and new password
4. Sessions are optionally revoked
5. Audit event password_reset_completed recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Account Suspension/Reactivation Flow

**Steps:**

1. Authorized admin selects user
2. Reason is required
3. System suspends access
4. Active sessions are revoked
5. User is notified where appropriate
6. Reactivation requires permission and reason
7. Audit events account_suspended/account_reactivated recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Device and Session Management Flow

**Steps:**

1. User views active sessions
2. User revokes selected device
3. System invalidates token/session
4. Admin can revoke compromised sessions
5. Audit event session_revoked recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 02 - Roles, Permissions, Organizations & Facilities

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Ensure every user acts only inside the correct organization, facility, department, and role context.

**Actors:** Super Admin, Organization Admin, Facility Admin, Department Admin, Staff User

**Core Models / Tables:**
```text
Organization
Facility
Department
Role
Permission
UserOrganization
UserFacility
UserDepartment
ProfessionalLicense
FacilityLicense
FacilityContextLog
```

### Required Flow Implementations

#### Create Organization Flow

**Steps:**

1. Super admin creates organization or approves application
2. Organization type selected
3. Legal/verification status set
4. Initial admin assigned
5. Audit event organization_created recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Create Facility Flow

**Steps:**

1. Organization admin creates facility
2. Facility type, address, license, service scope captured
3. Verification status starts pending
4. Facility context and departments initialized
5. Audit event facility_created recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Department Setup Flow

**Steps:**

1. Facility admin creates departments
2. Department type and service scope selected
3. Staff can be assigned
4. Department appears in routing, queue, appointment, and EMR contexts

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Role Creation Flow

**Steps:**

1. Admin creates role
2. Permissions are selected from known permission catalog
3. Role scope is defined as platform/org/facility/department
4. Role is saved
5. Audit event role_created recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Permission Assignment Flow

**Steps:**

1. Admin selects user and role
2. System checks admin scope
3. Assignment effective date and facility context set
4. User receives notification
5. Audit event permission_assigned recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Multi-Facility Staff Flow

**Steps:**

1. Staff is assigned to multiple facilities
2. Default facility context is selected at login
3. Every action uses active facility context
4. Switching facility is audited
5. Data leakage across facilities is blocked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### License Verification Flow

**Steps:**

1. Professional/facility license captured
2. Document uploaded
3. Verification status assigned
4. Expiry tracked
5. Expiry alerts created
6. Invalid/expired license can restrict actions

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Staff Removal Flow

**Steps:**

1. Admin removes user from facility/department
2. Open sessions for that context invalidated
3. Pending tasks reassigned if needed
4. Audit event staff_removed recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 03 - Patient Health ID / Medical ID

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Create and verify a secure patient Health ID that links medical records without exposing sensitive data through the ID or QR code.

**Actors:** Patient, Guardian, Provider, Receptionist, Emergency Staff, System

**Core Models / Tables:**
```text
Patient
HealthId
HealthIdToken
HealthIdVerificationEvent
EmergencyProfile
PatientDuplicateCandidate
PatientMergeRecord
HealthIdStatusHistory
```

### Required Flow Implementations

#### Health ID Generation Flow

**Steps:**

1. Patient profile is created
2. System checks duplicate candidates
3. Country-code-based Health ID is generated
4. Secure QR token is generated
5. Token hash stored
6. Patient receives digital card
7. Audit event health_id_created recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Manual Health ID Verification Flow

**Steps:**

1. User enters Health ID
2. System checks status
3. Public view returns safe metadata only
4. Authenticated provider can request access
5. Verification event is recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### QR Verification Flow

**Steps:**

1. Scanner opens QR URL/token
2. System resolves hashed token
3. If public, show limited verification
4. If authenticated, apply access policy
5. Audit event health_id_qr_scanned recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Duplicate Detection Flow

**Steps:**

1. New patient data entered
2. System compares name/date/contact/biometrics if available
3. Duplicate candidates shown to authorized user
4. User confirms new patient or merge review
5. Audit event duplicate_check_performed recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Patient Merge Flow

**Steps:**

1. Authorized data steward opens duplicate case
2. Records compared
3. Merge plan previewed
4. Patient identity safety checks run
5. Merge confirmed
6. Old IDs are preserved as aliases
7. Audit event patient_merged recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Unmerge Flow

**Steps:**

1. Authorized user opens merge history
2. System displays reversible merge components
3. Unmerge reason required
4. Records separated
5. Audit event patient_unmerged recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Profile Flow

**Steps:**

1. Patient configures emergency data or facility policy enables minimal emergency profile
2. Blood group/allergy/critical conditions/emergency contact shown only as allowed
3. Emergency scan requires reason when authenticated
4. Audit event emergency_profile_accessed recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Health ID Revocation/Reissue Flow

**Steps:**

1. Patient/admin reports compromised QR/card
2. Old token revoked
3. New token generated
4. Public verification shows revoked for old QR
5. Audit event health_id_reissued recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 04 - Patient Profile & EMR

**Action:** UPGRADE  
**Purpose:** Complete patient record and clinical timeline.

**Actors:** Patient, Provider, Nurse, Facility Admin

**Core Models / Tables:**
```text
PatientProfile
Guardian
Allergy
Condition
Encounter
ConsultationNote
VitalSign
ClinicalTimelineEvent
RecordSource
```

### Required Flow Implementations

#### Patient Profile Creation

**Steps:**

1. Register patient
2. Capture demographics/contact/guardian
3. Validate duplicates
4. Create profile
5. Link Health ID
6. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Allergy and Condition Entry

**Steps:**

1. Provider opens EMR
2. Adds allergy/condition
3. Severity/status captured
4. Source recorded
5. Patient timeline updates
6. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consultation Documentation

**Steps:**

1. Open authorized record
2. Create encounter
3. Enter complaint/history/exam/assessment/plan
4. Link prescriptions/labs/referrals
5. Save draft or sign
6. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Vital Signs Flow

**Steps:**

1. Nurse selects patient
2. Records vitals
3. Abnormal flags created
4. Timeline updates
5. Provider notified if critical

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Patient Timeline Flow

**Steps:**

1. User opens timeline
2. System filters by permission
3. Displays encounters/labs/rx/docs
4. Sensitive entries masked when needed
5. Access audited

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Patient Own Record Flow

**Steps:**

1. Patient logs in
2. Views released records
3. Downloads allowed documents
4. Requests correction/access report
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### External Record Attribution

**Steps:**

1. API pushes record
2. Source system/facility captured
3. Record marked external
4. Reconciliation if conflict
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 05 - Consent, Privacy & Access Control

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Enforce patient consent, purpose-of-use, emergency access, and privacy.

**Actors:** Patient, Provider, Privacy Officer, System

**Core Models / Tables:**
```text
ConsentRequest
ConsentGrant
AccessLog
EmergencyAccessEvent
DataScope
PrivacyIncident
```

### Required Flow Implementations

#### Consent Request

**Steps:**

1. Provider requests access
2. Purpose/data scope/duration set
3. Patient notified
4. Request pending
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consent Approval

**Steps:**

1. Patient reviews request
2. Approves scope/duration
3. Access grant created
4. Provider notified
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consent Denial

**Steps:**

1. Patient denies
2. Reason optional
3. Provider notified with limited info
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consent Revocation

**Steps:**

1. Patient revokes active grant
2. Access stops immediately
3. Provider notified
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consent Expiry

**Steps:**

1. Scheduled job expires grant
2. Access blocked
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Access

**Steps:**

1. Provider selects emergency access
2. Reason required
3. Limited dataset opened
4. Review task created
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Access Review

**Steps:**

1. Privacy officer reviews logs
2. Approves or flags misuse
3. Incident created if needed
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 06 - Partner Contribution & Governance

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Verify partners and control what they can contribute or access.

**Actors:** Partner Admin, Reviewer, Super Admin

**Core Models / Tables:**
```text
Partner
PartnerApplication
PartnerDocument
PartnerAgreement
PartnerPermission
PartnerQualityScore
PartnerRiskCase
```

### Required Flow Implementations

#### Partner Application

**Steps:**

1. Partner submits profile
2. Uploads documents
3. Selects contribution types
4. Application created
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Review and Approval

**Steps:**

1. Reviewer checks docs
2. Requests more info/approves/rejects
3. Trust level assigned
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Agreement Flow

**Steps:**

1. Agreement generated
2. Partner signs
3. OpesCare countersigns
4. Permissions become eligible
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Contribution Permission

**Steps:**

1. Admin grants scopes
2. Facility/data boundaries set
3. API/app access updated
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Quality Monitoring

**Steps:**

1. System checks freshness/errors
2. Quality score updated
3. Warnings sent
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Suspension

**Steps:**

1. Risk detected
2. Admin suspends partner
3. API/listings disabled
4. Partner notified
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 07 - API / SDK / Bridge Agent / Connect Widget

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Allow external systems to push and pull authorized data securely.

**Actors:** Developer, Partner IT, External HIS/LIS/PMS, Admin

**Core Models / Tables:**
```text
ApiClient
ApiToken
WebhookEndpoint
WebhookDelivery
SyncJob
SyncConflict
BridgeAgent
SdkKey
```

### Required Flow Implementations

#### Sandbox App

**Steps:**

1. Developer creates app
2. Sandbox scopes assigned
3. Keys generated
4. Docs shown
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Production Approval

**Steps:**

1. Developer requests production
2. Security checklist reviewed
3. Scopes approved
4. Production keys generated
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Push Data

**Steps:**

1. External system sends payload
2. Auth/scope/idempotency checked
3. Validation runs
4. Patient matched
5. Data stored or reconciliation case created
6. Webhook/audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Pull Data

**Steps:**

1. External system requests data
2. Consent/policy checked
3. Minimum dataset returned
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Webhook Delivery

**Steps:**

1. Event occurs
2. Webhook queued
3. Delivery attempted
4. Retry on failure
5. Log result

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Bridge Agent Sync

**Steps:**

1. Local agent authenticates
2. Pulls/pushes queued changes
3. Conflicts detected
4. Reconciliation case created
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Key Rotation

**Steps:**

1. Admin/developer rotates key
2. Old secret revoked after grace period
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 08 - Notifications, Alerts, Tasks, Voice & Messaging

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Deliver safe communication through dashboards, email, WhatsApp, SMS, voice, and internal messaging.

**Actors:** Patient, Doctor, Nurse, Lab, Pharmacy, Admin, System

**Core Models / Tables:**
```text
NotificationEvent
NotificationTemplate
NotificationDelivery
ActionTask
EscalationChain
MessageThread
Message
Broadcast
```

### Required Flow Implementations

#### Notification Dispatch

**Steps:**

1. Event triggers notification
2. Template selected by language/channel
3. Privacy filter applied
4. Queued
5. Delivery logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Critical Alert

**Steps:**

1. Critical event occurs
2. Task created
3. Assignee notified
4. Acknowledgement required
5. Escalates if not acknowledged
6. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Internal Messaging

**Steps:**

1. User opens authorized thread
2. Sends message
3. Attachments checked
4. Recipient notified
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Broadcast

**Steps:**

1. Admin creates broadcast
2. Audience selected
3. Approval if needed
4. Sent
5. Delivery tracked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Preference Handling

**Steps:**

1. User sets channel preferences
2. System respects preferences unless critical
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Voice Notification

**Steps:**

1. Critical voice alert queued
2. Approved message generated
3. Call provider sends voice
4. Delivery logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 09 - Verifiable Document Templates

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Generate tamper-evident QR-verifiable medical, financial, and administrative documents.

**Actors:** Provider, Lab, Pharmacy, Cashier, Patient, Public Verifier

**Core Models / Tables:**
```text
DocumentTemplate
OfficialDocument
DocumentVerificationToken
DocumentVersion
DocumentSignature
DocumentShareLink
```

### Required Flow Implementations

#### Document Generation

**Steps:**

1. Source event occurs
2. Template selected
3. Payload assembled
4. PDF generated
5. QR/code/hash created
6. Document issued
7. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Public Verification

**Steps:**

1. User scans QR
2. Token resolved
3. Status/authenticity shown
4. Sensitive data hidden
5. Verification logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Authenticated Download

**Steps:**

1. Authorized user opens document
2. Permission checked
3. PDF served by signed route
4. Download logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Amendment

**Steps:**

1. Authorized user requests amendment
2. Reason required
3. New version created
4. Old version superseded
5. Notifications/audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Revocation

**Steps:**

1. Authorized user revokes
2. Reason required
3. Public verification shows revoked
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Entered-in-Error

**Steps:**

1. Authorized role marks document entered in error
2. Use blocked
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Document Sharing

**Steps:**

1. User creates time-limited share link
2. Recipient accesses with token/OTP if needed
3. Access logged
4. Link can be revoked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 10 - Demo Access & Demo Data

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Provide isolated demo accounts and demo data for every dashboard and flow.

**Actors:** Guest, Demo User, Admin

**Core Models / Tables:**
```text
DemoAccount
DemoScenario
DemoResetJob
DemoIsolationFlag
```

### Required Flow Implementations

#### Demo Page

**Steps:**

1. Visitor opens demo access
2. Roles listed
3. Clicks demo login
4. Session created as demo
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Demo Dataset

**Steps:**

1. Seeder creates demo patient/facilities/staff/lab/rx/invoice/map/etc
2. Data flagged demo
3. Dashboards show realistic flows

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Demo Reset

**Steps:**

1. Scheduled or admin reset runs
2. Demo data restored
3. No production data touched
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Isolation

**Steps:**

1. Demo user attempts production record
2. Policy blocks
3. Security event logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 11 - Bilingual English/French Platform

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Make public and user-facing platform clear in English and French.

**Actors:** All Users, Translator/Admin

**Core Models / Tables:**
```text
TranslationKey
LanguagePreference
TranslationAudit
```

### Required Flow Implementations

#### Language Switch

**Steps:**

1. User selects language
2. Preference saved
3. UI reloads translated
4. Fallback used if missing

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Translation Coverage

**Steps:**

1. System scans keys
2. Missing translations reported
3. Admin updates translations
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Medical Language Safety

**Steps:**

1. Patient-facing copy reviewed
2. Jargon removed
3. Clear labels used
4. French layout checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Notification Translation

**Steps:**

1. Notification uses user language
2. Template fallback if missing
3. Delivery logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 12 - UI/UX, Layout, Colors & Icons

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Enforce a hospital-grade interface across all modules.

**Actors:** All Users, Designer/Admin

**Core Models / Tables:**
```text
DesignToken
UiSetting
ComponentAudit
```

### Required Flow Implementations

#### Layout Standardization

**Steps:**

1. Apply dashboard shell
2. Apply responsive grids
3. Check mobile/tablet/desktop
4. Fix overflows

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Icon Standard

**Steps:**

1. Use Lucide icons
2. Remove emoji icons
3. Map icon names per feature
4. Audit UI

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### State Design

**Steps:**

1. Implement empty/loading/error/success states
2. Add status badges
3. Test long French labels

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Accessibility

**Steps:**

1. Check contrast
2. Keyboard states
3. Readable forms/tables
4. ARIA labels where needed

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 13 - Pharmacy Stock & Medicine Availability

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Let patients and providers find pharmacies with recently reported medicine stock.

**Actors:** Patient, Pharmacy, Provider, Public Health

**Core Models / Tables:**
```text
Medicine
PharmacyStock
MedicineReservation
StockUpdateLog
MedicineCodeMapping
```

### Required Flow Implementations

#### Stock Update

**Steps:**

1. Pharmacy updates medicine/quantity/status
2. Batch/expiry optional
3. Freshness timestamp set
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Stock Sync

**Steps:**

1. External pharmacy system pushes stock
2. Validation and mapping
3. Update accepted/rejected
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Medicine Search

**Steps:**

1. User searches medicine
2. Generic/brand mapping runs
3. Nearby pharmacies shown
4. Freshness warning shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Reservation

**Steps:**

1. Patient requests reservation
2. Pharmacy confirms/rejects
3. Expiry timer set
4. Patient notified

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Staleness

**Steps:**

1. Scheduled job marks stale stock
2. Search displays stale warning
3. Partner notified

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Stock-Out Report

**Steps:**

1. Repeated out-of-stock captured
2. Aggregate signal sent to public health dashboard

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 14 - Blood Availability

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Show reported blood availability and urgent request pathways safely.

**Actors:** Patient, Provider, Blood Bank, Hospital, Public Health

**Core Models / Tables:**
```text
BloodAvailability
BloodRequest
BloodComponent
BloodUpdate
```

### Required Flow Implementations

#### Update Availability

**Steps:**

1. Facility updates blood group/component/unit range
2. Freshness timestamp set
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Blood Search

**Steps:**

1. User selects group/component/location
2. Verified fresh facilities shown
3. Call/directions displayed
4. Warning shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Urgent Request

**Steps:**

1. Provider creates request
2. Patient identity protected
3. Facility alerted
4. Status tracked
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Staleness

**Steps:**

1. Job marks stale data
2. Search warns user
3. Facility reminded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Shortage Signal

**Steps:**

1. Low availability aggregated
2. Public health signal created
3. No patient identity exposed

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 15 - Public Health Reporting

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Create aggregate, de-identified, reviewable reports for public health partners.

**Actors:** Facility Reporter, Public Health Officer, Reviewer, Admin

**Core Models / Tables:**
```text
PublicHealthReport
ReportMetric
ReportSubmission
ReportApproval
DiseaseSignal
ShortageSignal
```

### Required Flow Implementations

#### Report Draft

**Steps:**

1. System aggregates data
2. Facility reporter reviews
3. Identifiable data removed by default
4. Draft created

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Review

**Steps:**

1. Reviewer checks completeness
2. Requests correction or approves
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Submission

**Steps:**

1. Approved report submitted/exported
2. Receipt generated
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Rejection/Correction

**Steps:**

1. Report rejected with reason
2. Facility corrects
3. Version history preserved

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Shortage Reporting

**Steps:**

1. Medicine/blood shortage signals aggregated
2. Dashboards updated
3. Public health notified

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Completeness Dashboard

**Steps:**

1. Facility reporting score calculated
2. Missing reports highlighted

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 16 - Certification / OpesCare Academy

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Train and certify digital health workflow competency, not professional medical licensing.

**Actors:** Learner, Trainer, Facility Admin, Admin

**Core Models / Tables:**
```text
Course
Lesson
Quiz
QuizAttempt
SimulationAttempt
Certificate
CompetencyRequirement
```

### Required Flow Implementations

#### Course Creation

**Steps:**

1. Admin creates course/modules/lessons/quizzes
2. Disclaimer added
3. Course reviewed/published
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Enrollment

**Steps:**

1. Learner enrolls or assigned
2. Progress record created
3. Notifications sent

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Learning and Quiz

**Steps:**

1. Learner completes lessons
2. Takes quiz
3. Score recorded
4. Retry rules applied

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Simulation

**Steps:**

1. Learner completes practical workflow
2. Mistakes scored
3. Supervisor signoff if needed

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Certificate

**Steps:**

1. Requirements met
2. Certificate number/QR generated
3. Expiry set
4. Public verification enabled

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Renewal/Revocation

**Steps:**

1. Expiry reminders sent
2. Renewal quiz/simulation completed or certificate revoked with reason

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Competency Gate

**Steps:**

1. User requests sensitive permission
2. System checks required certificate plus role/license
3. Access allowed/blocked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 17 - Verified Care Access Map

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Provide verified, freshness-aware care access navigation.

**Actors:** Patient, Facility, Pharmacy, Lab, Admin, Public Health

**Core Models / Tables:**
```text
CareFacility
CareFacilityService
FacilityClaim
FacilityReport
PharmacyStockAvailability
LabTestAvailability
BloodAvailability
```

### Required Flow Implementations

#### Facility Listing

**Steps:**

1. Facility created/imported
2. Location geocoded
3. Verification status assigned
4. Public visibility determined

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Search

**Steps:**

1. User searches by location/service/medicine/lab/blood/insurance
2. Results filtered by verification/freshness
3. Warnings shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Facility Claim

**Steps:**

1. Representative claims facility
2. Documents uploaded
3. Admin approves/rejects
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Report Wrong Info

**Steps:**

1. User submits issue
2. Moderation case created
3. Partner/admin resolves
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Mode

**Steps:**

1. User selects emergency
2. Nearest emergency-capable facilities shown
3. Call/directions prioritized
4. Disclaimer shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Freshness Jobs

**Steps:**

1. Stock/blood/lab/profile freshness recalculated
2. Stale warnings displayed

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 18 - Appointments & Booking

**Action:** BUILD_IF_MISSING  
**Purpose:** Schedule care and connect appointment to visit, queue, encounter, and billing.

**Actors:** Patient, Receptionist, Provider, Facility Admin

**Core Models / Tables:**
```text
Appointment
AppointmentSlot
ProviderAvailability
FacilitySchedule
AppointmentCheckIn
```

### Required Flow Implementations

#### Provider Availability Setup

**Steps:**

1. Provider/facility admin defines working days/hours
2. Service types and slot durations set
3. Blocked times captured
4. Calendar available for booking

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Patient Booking

**Steps:**

1. Patient selects facility/provider/service/date
2. System checks slot availability
3. Patient confirms
4. Appointment scheduled
5. Reminder queued

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Staff Booking

**Steps:**

1. Receptionist searches patient or creates profile
2. Selects slot/service
3. Books appointment on behalf of patient
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Reschedule

**Steps:**

1. User selects new slot
2. Old slot released
3. New slot reserved
4. Notifications sent
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Cancellation

**Steps:**

1. Patient/staff cancels with reason
2. Slot released
3. Policy/no-fee logic applied
4. Notification sent

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Check-In

**Steps:**

1. Patient arrives
2. Appointment verified
3. Check-in event created
4. Queue ticket generated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### No-Show

**Steps:**

1. Scheduled job marks missed appointments
2. No-show notice/audit recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Encounter/Billing Link

**Steps:**

1. Checked-in appointment creates visit/encounter shell
2. Billing service receives charge trigger

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 19 - Queue & Patient Flow

**Action:** BUILD_IF_MISSING  
**Purpose:** Move patients through facility stations without losing context.

**Actors:** Receptionist, Nurse, Doctor, Lab, Cashier, Pharmacy

**Core Models / Tables:**
```text
Queue
QueueTicket
PatientCheckIn
QueueStation
PatientFlowEvent
```

### Required Flow Implementations

#### Check-In to Queue

**Steps:**

1. Patient checks in
2. Queue station selected
3. Ticket generated
4. Patient flow event recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Triage Queue

**Steps:**

1. Nurse calls patient
2. Status changes called/in_service
3. Triage completed
4. Patient routed to consultation or emergency

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consultation Queue

**Steps:**

1. Doctor calls next patient
2. Encounter opens
3. Consultation completed
4. Next station chosen

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Lab/Billing/Pharmacy Routing

**Steps:**

1. Provider/cashier/lab routes patient
2. Queue ticket transferred
3. Status updated
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Priority Bypass

**Steps:**

1. Emergency priority assigned
2. Patient moved ahead
3. Reason required
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Public Queue Display

**Steps:**

1. Masked ticket numbers displayed
2. No full names/sensitive details
3. Status updates live

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Visit Completion

**Steps:**

1. All stations completed
2. Queue ticket closed
3. Visit timeline updated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 20 - Billing, Payments & Wallet

**Action:** BUILD_IF_MISSING  
**Purpose:** Handle medical billing, payments, receipts, refunds, and patient wallet.

**Actors:** Cashier, Patient, Facility Admin, Insurance User

**Core Models / Tables:**
```text
Invoice
InvoiceItem
Payment
Receipt
Refund
Wallet
WalletTransaction
PriceList
```

### Required Flow Implementations

#### Invoice Creation

**Steps:**

1. Service/lab/pharmacy/consultation event triggers invoice draft
2. Items priced
3. Discount/insurance rules applied
4. Invoice issued

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Patient Responsibility

**Steps:**

1. Insurance coverage calculated
2. Patient portion shown
3. Approval required if override

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Payment Recording

**Steps:**

1. Cashier selects method
2. Amount validated
3. Payment record created
4. Invoice balance updated
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Receipt Generation

**Steps:**

1. Successful payment triggers receipt
2. QR-verifiable receipt generated
3. Patient notified

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Refund

**Steps:**

1. Authorized user selects payment
2. Reason required
3. Refund amount validated
4. Receipt adjusted
5. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Wallet Deposit

**Steps:**

1. Patient deposits/prepays
2. Wallet transaction created
3. Balance updated
4. Receipt generated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Payment Reconciliation

**Steps:**

1. Cashier/admin reviews payments
2. External transaction IDs matched
3. Exceptions flagged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Financial Reports

**Steps:**

1. Daily cashier report generated
2. Facility/admin access only

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 21 - Insurance Claims & Preauthorization

**Action:** COMPLETE_OR_BUILD  
**Purpose:** Manage eligibility, preauthorization, claims, payer review, and claim payments.

**Actors:** Patient, Billing Staff, Insurance User, Provider

**Core Models / Tables:**
```text
InsuranceProvider
InsurancePlan
PatientInsurancePolicy
EligibilityCheck
PreauthorizationRequest
InsuranceClaim
ClaimItem
ClaimDecision
```

### Required Flow Implementations

#### Policy Registration

**Steps:**

1. Patient/facility enters insurance details
2. Policy verified or pending
3. Coverage limits captured

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Eligibility Check

**Steps:**

1. Facility requests eligibility
2. Insurer/API/manual review returns status
3. Result stored

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Preauthorization

**Steps:**

1. Facility submits service request/docs
2. Insurer reviews minimum data
3. Approve/reject/request info
4. Notification sent

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Claim Creation

**Steps:**

1. Invoice/encounter creates claim draft
2. Claim items added
3. Required docs attached

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Claim Submission

**Steps:**

1. Claim validated
2. Submitted to payer
3. Status pending review

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Payer Review

**Steps:**

1. Insurance user sees minimum necessary data
2. Approves/rejects/requests info
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Claim Payment

**Steps:**

1. Approved amount posted
2. Invoice balance updated
3. Receipt/payment record if applicable

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Patient Claim Status

**Steps:**

1. Patient sees safe claim status without internal payer notes

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 22 - Telemedicine

**Action:** PHASE_2_BUILD_IF_ASSIGNED  
**Purpose:** Remote consultation with consent and clinical limitations.

**Actors:** Patient, Provider, Cashier/Admin

**Core Models / Tables:**
```text
Teleconsultation
TelemedicineConsent
CallSession
VirtualWaitingRoom
TelemedicineNote
```

### Required Flow Implementations

#### Teleconsult Booking

**Steps:**

1. Patient selects telemedicine service
2. Provider availability checked
3. Appointment scheduled

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Telemedicine Consent

**Steps:**

1. Patient reads limitation/privacy notice
2. Consent captured before session

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Virtual Waiting Room

**Steps:**

1. Patient joins waiting room
2. Provider sees queue
3. Identity checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Call Session

**Steps:**

1. Provider starts video/audio through provider abstraction
2. Session metadata logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Teleconsult Note

**Steps:**

1. Provider documents consultation
2. E-prescription/lab/referral possible if allowed

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Telemedicine Billing

**Steps:**

1. Fee invoice/payment linked
2. Receipt generated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Recording Policy

**Steps:**

1. Recording disabled by default
2. If enabled, explicit consent and audit required

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 23 - Triage & Emergency Workflow

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Classify urgency and escalate critical cases safely.

**Actors:** Nurse, Doctor, Emergency Staff

**Core Models / Tables:**
```text
TriageAssessment
TriageScore
EmergencyCase
ChiefComplaint
VitalSign
```

### Required Flow Implementations

#### Triage Start

**Steps:**

1. Patient arrives/checks in
2. Nurse opens triage form
3. Chief complaint captured

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Vitals Capture

**Steps:**

1. Vitals entered
2. Abnormal values flagged
3. Timeline updated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Priority Assignment

**Steps:**

1. Triage category assigned
2. Reason recorded
3. Queue priority updated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Escalation

**Steps:**

1. Critical category triggers alert
2. Doctor/emergency team notified
3. Acknowledgement required

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Reassessment

**Steps:**

1. Nurse updates status/vitals
2. Priority changes audited

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Access

**Steps:**

1. Emergency access opened if needed
2. Reason and dataset limited
3. Review task created

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 24 - Ward, Admission & Bed Management

**Action:** PHASE_2_OR_BUILD_IF_NEEDED  
**Purpose:** Manage inpatient admissions, beds, transfers, rounds, and discharge.

**Actors:** Admissions Staff, Nurse, Doctor, Ward Admin

**Core Models / Tables:**
```text
Admission
Ward
Bed
BedAssignment
WardTransfer
InpatientNote
NursingRound
```

### Required Flow Implementations

#### Admission

**Steps:**

1. Provider admits patient
2. Admission reason/ward requested
3. Bed availability checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Bed Assignment

**Steps:**

1. Free bed selected
2. Bed marked occupied
3. Patient assigned
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Ward Transfer

**Steps:**

1. Transfer requested
2. Target bed checked
3. Transfer confirmed
4. Old bed released

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Inpatient Notes

**Steps:**

1. Doctor/nurse records daily notes
2. Timeline updates

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Nursing Rounds

**Steps:**

1. Round tasks generated
2. Nurse completes checks
3. Escalations created if abnormal

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Medication Administration

**Steps:**

1. Medication schedule shown
2. Nurse records administration/omission reason

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Discharge

**Steps:**

1. Discharge plan completed
2. Summary generated
3. Bed released

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 25 - Inventory & Supply Chain

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Track medical stock, consumables, equipment, procurement, and shortages.

**Actors:** Inventory Staff, Pharmacy, Lab, Facility Admin

**Core Models / Tables:**
```text
InventoryItem
StockLocation
StockBatch
StockMovement
Supplier
PurchaseOrder
GoodsReceipt
```

### Required Flow Implementations

#### Item Creation

**Steps:**

1. Admin creates item
2. Category/unit/reorder level set

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Receive Stock

**Steps:**

1. Goods received
2. Batch/lot/expiry captured
3. Stock increased
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Move Stock

**Steps:**

1. Source/destination selected
2. Quantity validated
3. Movement recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Adjustment

**Steps:**

1. Authorized adjustment with reason
2. Stock changed
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Expiry Tracking

**Steps:**

1. Scheduled job flags expiring/expired stock
2. Alerts sent

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Low Stock Alert

**Steps:**

1. Stock falls below reorder level
2. Task/notification created

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Purchase Order

**Steps:**

1. PO created
2. Supplier selected
3. Approval workflow
4. Goods receipt closes PO

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Stock Audit

**Steps:**

1. Count entered
2. Variance calculated
3. Adjustment approval if needed

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 26 - Staff / HR / Shift Management

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Manage facility staff operationally.

**Actors:** HR Admin, Facility Admin, Staff

**Core Models / Tables:**
```text
StaffProfile
ProfessionalLicense
StaffShift
DutyRoster
LeaveRequest
DepartmentAssignment
```

### Required Flow Implementations

#### Staff Profile

**Steps:**

1. Create staff profile
2. Attach user account
3. Assign department/facility

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### License Tracking

**Steps:**

1. License entered
2. Expiry date tracked
3. Alerts sent

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Shift Scheduling

**Steps:**

1. Admin creates shifts
2. Assigns staff
3. Conflicts checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Duty Roster

**Steps:**

1. Roster published
2. Staff notified
3. Changes audited

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Leave Request

**Steps:**

1. Staff requests leave
2. Admin approves/rejects
3. Roster updated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Training Link

**Steps:**

1. Certification status shown in profile
2. Missing required training blocks sensitive actions

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Multi-Facility Assignment

**Steps:**

1. Doctor/staff assigned to multiple facilities
2. Context and schedule separated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 27 - Clinical Decision Support / Clinical Alerts

**Action:** BUILD_CAREFULLY_ADVISORY  
**Purpose:** Advisory clinical warnings and reminders.

**Actors:** Provider, Clinical Admin

**Core Models / Tables:**
```text
ClinicalRule
ClinicalAlert
DrugInteractionRule
AllergyAlertRule
DoseWarningRule
AlertOverride
```

### Required Flow Implementations

#### Allergy Alert

**Steps:**

1. Prescription/order entered
2. Allergy match checked
3. Alert shown
4. Override reason required if proceeding

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Drug Interaction

**Steps:**

1. Medication list checked
2. Interaction severity shown
3. Provider decides
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Duplicate Prescription

**Steps:**

1. Same/related active medication detected
2. Warning shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Critical Lab Alert

**Steps:**

1. Lab result critical
2. Provider task created
3. Acknowledgement required

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Dose Warning

**Steps:**

1. Age/weight/pregnancy/renal rules checked if data available
2. Advisory shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Reminder

**Steps:**

1. Chronic/vaccine/follow-up reminder generated
2. Provider can act/dismiss

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Rule Management

**Steps:**

1. Clinical admin adds rule source/version
2. Rules reviewed before active

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 28 - Patient Mobile App API Readiness

**Action:** COMPLETE_API_CONTRACT  
**Purpose:** Prepare patient mobile app endpoints.

**Actors:** Patient, Guardian

**Core Models / Tables:**
```text
MobileSession
PushDeviceToken
```

### Required Flow Implementations

#### Mobile Auth

**Steps:**

1. Patient logs in from app
2. Device token saved
3. Session audited

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Health ID Card

**Steps:**

1. API returns Health ID QR/display data safely

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Records View

**Steps:**

1. API returns released records only
2. Permissions enforced

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consent Inbox

**Steps:**

1. Patient views/approves/denies/revokes consent

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Notifications

**Steps:**

1. Push notification sent with privacy-safe content

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Documents

**Steps:**

1. Patient views/downloads authorized documents

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Care Tools

**Steps:**

1. Medicine/blood/care map endpoints available

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Profile

**Steps:**

1. Patient manages emergency profile fields

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 29 - Provider Mobile App API Readiness

**Action:** COMPLETE_API_CONTRACT  
**Purpose:** Prepare mobile workflows for providers.

**Actors:** Doctor, Nurse, Pharmacist, Lab Staff

**Core Models / Tables:**
```text
MobileSession
ProviderDevice
PushDeviceToken
```

### Required Flow Implementations

#### Provider Mobile Auth

**Steps:**

1. Provider logs in
2. MFA if required
3. Facility context selected

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Scan Health ID

**Steps:**

1. Provider scans QR
2. Access policy checked
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Tasks and Alerts

**Steps:**

1. Provider receives assigned tasks/critical alerts
2. Acknowledgement logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Secure Messages

**Steps:**

1. Provider reads/sends authorized messages

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Prescription Verify

**Steps:**

1. Pharmacist scans prescription QR
2. Medication details shown only if authorized

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Access

**Steps:**

1. Provider requests emergency access from mobile
2. Reason required
3. Review task created

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Facility Switch

**Steps:**

1. Provider switches facility context
2. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 30 - Offline Mode & Sync

**Action:** PHASE_2_HIGH_SECURITY  
**Purpose:** Limited encrypted offline workflow and sync.

**Actors:** Facility, Provider, Bridge Agent

**Core Models / Tables:**
```text
OfflineQueue
SyncJob
SyncConflict
LocalCachePolicy
OfflineAuditEvent
```

### Required Flow Implementations

#### Offline Capture

**Steps:**

1. Authorized offline mode enabled
2. Allowed forms cached
3. Data captured locally encrypted

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Sync Retry

**Steps:**

1. Connectivity returns
2. Queue uploads events
3. Retries failures

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Conflict Detection

**Steps:**

1. Server detects conflicting updates
2. SyncConflict created

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Conflict Resolution

**Steps:**

1. Authorized user chooses resolution
2. No silent overwrite
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Offline Consent Limits

**Steps:**

1. Sensitive data unavailable offline unless policy allows
2. Emergency/offline access logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Bridge Agent Sync

**Steps:**

1. Local system syncs with central platform
2. Failures create reconciliation cases

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 31 - Analytics & Reporting

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Operational and aggregate dashboards.

**Actors:** Facility Admin, Public Health, Super Admin

**Core Models / Tables:**
```text
AnalyticsSnapshot
DashboardMetric
ReportDefinition
MetricSnapshot
```

### Required Flow Implementations

#### Metric Aggregation

**Steps:**

1. Scheduled job aggregates source data
2. Snapshots stored

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Facility Dashboard

**Steps:**

1. Visits/labs/prescriptions/billing/training shown for facility

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Shortage Dashboard

**Steps:**

1. Medicine/blood shortages aggregated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### API Health

**Steps:**

1. API errors/webhook failures/sync health shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Financial Reports

**Steps:**

1. Billing/payments/claims summarized

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Data Quality

**Steps:**

1. Completeness/duplicates/reconciliation metrics shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Export

**Steps:**

1. Authorized export with de-identification and audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 32 - Audit, Compliance & Security Operations Center

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Central security and compliance console.

**Actors:** Security Admin, Privacy Officer, Super Admin

**Core Models / Tables:**
```text
AuditEvent
SecurityIncident
AccessReview
SuspiciousAccessFlag
ComplianceCase
AuditExport
```

### Required Flow Implementations

#### Audit Explorer

**Steps:**

1. Admin filters by user/patient/facility/action/date
2. Sensitive access logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Suspicious Access Detection

**Steps:**

1. Rules flag abnormal activity
2. Case created

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Emergency Access Review

**Steps:**

1. Reviewer sees emergency access events
2. Approves/flags misuse

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Security Incident

**Steps:**

1. Incident created from report/alert
2. Severity assigned
3. Actions tracked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Breach Workflow

**Steps:**

1. Breach case documented
2. Notifications/tasks assigned
3. Resolution recorded

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Role Review

**Steps:**

1. Permissions reviewed periodically
2. Changes audited

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Compliance Export

**Steps:**

1. Authorized user exports audit package
2. Export event logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 33 - Support, Helpdesk & Incident Management

**Action:** BUILD_IF_MISSING  
**Purpose:** Manage support tickets without exposing data unnecessarily.

**Actors:** Patient, Facility User, Developer, Support Agent, Admin

**Core Models / Tables:**
```text
SupportTicket
TicketMessage
TicketAssignment
IncidentReport
KnowledgeBaseArticle
```

### Required Flow Implementations

#### Ticket Creation

**Steps:**

1. User creates ticket
2. Category/severity selected
3. No sensitive clinical data requested by default

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Assignment

**Steps:**

1. Support lead assigns ticket
2. SLA timer starts

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Messaging

**Steps:**

1. Agent/user exchange messages
2. Attachments scanned
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Escalation

**Steps:**

1. Ticket exceeds SLA or severity
2. Escalated to team/admin

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Incident Conversion

**Steps:**

1. Security/bug ticket converted to incident
2. Incident workflow starts

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Resolution

**Steps:**

1. Agent resolves
2. User notified
3. Feedback captured

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Knowledge Base

**Steps:**

1. Article searched/viewed
2. Bilingual content shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 34 - Data Import / Migration

**Action:** BUILD_IF_MISSING  
**Purpose:** Onboard legacy data safely.

**Actors:** Implementation Lead, Facility Admin, Data Steward

**Core Models / Tables:**
```text
ImportJob
ImportBatch
ImportRowError
ImportMapping
ImportRollback
```

### Required Flow Implementations

#### Upload Import File

**Steps:**

1. User uploads CSV/Excel
2. File scanned
3. Columns detected

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Mapping Review

**Steps:**

1. User maps columns to fields
2. Required fields validated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Preview

**Steps:**

1. System previews changes/errors/duplicates
2. No data written yet

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Run Import

**Steps:**

1. Authorized user confirms
2. Rows processed
3. Errors captured
4. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Duplicate Handling

**Steps:**

1. Potential duplicates create review queue

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Rollback

**Steps:**

1. If supported, import can be reversed
2. Rollback audited

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Import Report

**Steps:**

1. Success/errors summary generated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 35 - Master Admin Control Center

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Central platform administration.

**Actors:** Super Admin

**Core Models / Tables:**
```text
PlatformSetting
Country
Region
LanguageSetting
FeatureFlag
ModuleToggle
SystemHealthSnapshot
```

### Required Flow Implementations

#### Platform Settings

**Steps:**

1. Super admin edits settings
2. Validation runs
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Country/Region Setup

**Steps:**

1. Countries/regions configured
2. Used by Health ID/map/facilities

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Language Management

**Steps:**

1. Supported languages enabled
2. Fallback set

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Feature Flags

**Steps:**

1. Flag created/toggled
2. Target audience set
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Module Toggles

**Steps:**

1. Module enabled/disabled per plan/facility
2. Dependencies checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Maintenance Mode

**Steps:**

1. Admin enables maintenance
2. Public message shown
3. Admins bypass

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### System Health

**Steps:**

1. Queues/storage/API/database health displayed

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Partner Approvals

**Steps:**

1. Pending partner approvals visible

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 36 - Subscription / SaaS Billing

**Action:** PHASE_2_OR_BUSINESS_REQUIRED  
**Purpose:** Bill organizations for OpesCare SaaS usage, separate from patient bills.

**Actors:** Organization Admin, Super Admin, Billing Admin

**Core Models / Tables:**
```text
SubscriptionPlan
OrganizationSubscription
SubscriptionInvoice
UsageMetric
PlanFeature
```

### Required Flow Implementations

#### Plan Creation

**Steps:**

1. Admin defines plan/features/limits/pricing

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Organization Subscription

**Steps:**

1. Organization selects/assigned plan
2. Trial or active subscription created

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Trial Expiry

**Steps:**

1. Trial ending alerts sent
2. Access limits applied if expired

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Upgrade/Downgrade

**Steps:**

1. Plan change requested
2. Billing/proration handled if enabled
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Usage Tracking

**Steps:**

1. API/users/storage/modules usage counted

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Module Activation

**Steps:**

1. Plan features enable/disable modules

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Subscription Invoice

**Steps:**

1. Invoice generated for organization
2. Payment status tracked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 37 - Data Quality & Reconciliation

**Action:** UPGRADE_AND_PRESERVE  
**Purpose:** Keep health data clean across external systems and internal modules.

**Actors:** Data Steward, Admin, Facility Admin

**Core Models / Tables:**
```text
DataQualityIssue
ReconciliationCase
DuplicatePatientCandidate
ExternalRecordMatch
DataCompletenessScore
```

### Required Flow Implementations

#### Duplicate Detection

**Steps:**

1. System finds candidates
2. Case created
3. Reviewer notified

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Merge Review

**Steps:**

1. Reviewer compares records
2. Merge plan previewed
3. Merge confirmed or rejected

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Unmerge

**Steps:**

1. Merge history opened
2. Unmerge reason required
3. Records separated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Unmatched External Record

**Steps:**

1. External push cannot match patient
2. Case created
3. Manual matching required

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Failed Sync Case

**Steps:**

1. API/Bridge failure creates reconciliation case

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Bad Mapping

**Steps:**

1. Lab/medicine code mapping conflict flagged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Completeness Score

**Steps:**

1. Patient/facility data completeness calculated
2. Gaps shown

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 38 - Global Search

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Permission-aware search across the platform.

**Actors:** Authorized Users

**Core Models / Tables:**
```text
SearchIndex
SearchLog
```

### Required Flow Implementations

#### Patient Search

**Steps:**

1. User searches Health ID/name
2. Permission filter applied
3. Sensitive search logged

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Document Search

**Steps:**

1. User searches verification code/document number
2. Access checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Facility Search

**Steps:**

1. Search facilities/services/map data

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Medicine/Lab Search

**Steps:**

1. Search medicine/test catalogs

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Partner Search

**Steps:**

1. Admin searches partners

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Message Search

**Steps:**

1. Only authorized thread messages returned

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Search Analytics

**Steps:**

1. Aggregate search trends without patient exposure

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 39 - File Storage & Medical Attachments

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Secure file handling for medical and admin documents.

**Actors:** All Authorized Users

**Core Models / Tables:**
```text
FileAsset
MedicalAttachment
AttachmentAccessLog
VirusScanResult
```

### Required Flow Implementations

#### Upload

**Steps:**

1. User uploads file
2. Type/size checked
3. Virus scan placeholder/job runs
4. Stored privately

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Classify

**Steps:**

1. File classified as clinical/insurance/admin/etc
2. Sensitivity set

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Attach

**Steps:**

1. File attached to patient/document/claim/message
2. Permission checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Download

**Steps:**

1. Signed URL generated
2. Expiry set
3. Download audited

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Archive/Delete

**Steps:**

1. Authorized user archives/deletes according to retention rules
2. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Public Block

**Steps:**

1. Direct public storage path access denied

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 40 - Facility Go-Live Readiness

**Action:** COMPLETE_IF_PARTIAL  
**Purpose:** Ensure a facility is safe to pilot or launch.

**Actors:** Implementation Lead, Facility Admin, Super Admin

**Core Models / Tables:**
```text
GoLiveChecklist
GoLiveApproval
FacilityReadinessScore
```

### Required Flow Implementations

#### Checklist Creation

**Steps:**

1. Facility enters onboarding
2. Checklist generated based on facility type

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Role/Training Check

**Steps:**

1. Staff roles assigned
2. Required certifications checked

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Configuration Check

**Steps:**

1. Departments/services/templates/notifications/audit verified

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Data Import Check

**Steps:**

1. Required imports completed or waived

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Support Check

**Steps:**

1. Support contact and escalation defined

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Readiness Score

**Steps:**

1. System calculates score and blockers

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Approval

**Steps:**

1. Authorized approver signs off
2. Go-live status set
3. Audit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


## Module 41 - Appointment-to-Billing-to-Document End-to-End Visit Flow

**Action:** BUILD_INTEGRATION_IF_MISSING  
**Purpose:** Tie operational modules into one patient visit journey.

**Actors:** Patient, Receptionist, Nurse, Doctor, Lab, Cashier, Pharmacy

**Core Models / Tables:**
```text
Visit
VisitStep
VisitTimeline
Encounter
Invoice
Receipt
OfficialDocument
```

### Required Flow Implementations

#### Visit Start

**Steps:**

1. Appointment or walk-in creates visit
2. Facility context set
3. Patient checked in

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Queue and Triage

**Steps:**

1. Queue ticket created
2. Triage optional/required based on facility policy

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Consultation

**Steps:**

1. Provider opens visit workspace
2. Consultation note created
3. Orders/prescriptions/referrals added

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Billing

**Steps:**

1. Billable services generate invoice
2. Insurance/patient responsibility calculated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Payment and Receipt

**Steps:**

1. Payment recorded
2. Receipt document generated

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Lab and Pharmacy Completion

**Steps:**

1. Lab results and/or dispensing linked to visit

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Visit Close

**Steps:**

1. All required steps complete
2. Visit summary generated
3. Patient notified

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.

#### Full Audit

**Steps:**

1. Every step recorded in VisitTimeline and AuditEvent

**Implementation Requirements:**

- Route/API endpoint or UI action must exist.
- Service/business logic must execute the flow.
- Request validation must reject invalid input.
- Permissions and facility/organization boundaries must be enforced.
- Sensitive actions must create audit events.
- Notifications must fire where operationally required.
- English and French labels/messages must exist for user-facing steps.
- Feature tests must cover success, failure, permission denial, and edge cases.


### Module Completion Tests
- Models and migrations exist or are intentionally mapped to existing equivalents.
- All listed flows are implemented, not merely documented.
- UI/API entry points are available.
- Permissions, audit logs, bilingual labels, and tests exist.
- No patient data is exposed outside authorized scope.


# Mandatory Cross-Module End-to-End Tests

1. Patient signup -> Health ID -> consent -> provider access -> EMR timeline.
2. Appointment -> check-in -> queue -> triage -> consultation -> invoice -> payment -> receipt -> verifiable document -> visit close.
3. Lab order -> sample -> result validation -> critical alert -> release -> patient notification -> verifiable lab report.
4. Prescription -> QR prescription -> pharmacy verification -> dispensing -> stock update -> dispensing receipt.
5. Insurance eligibility -> preauthorization -> claim -> payer review -> claim decision -> invoice update.
6. External API push -> validation -> patient matching -> reconciliation if needed -> webhook -> audit.
7. Emergency access -> reason -> limited view -> review task -> final compliance decision.
8. Public health report -> aggregation -> de-identification -> review -> submission -> receipt.
9. Care map search -> facility/medicine/lab/blood/insurance result -> freshness warning -> report correction.
10. Certification -> course -> quiz/simulation -> QR certificate -> access gate check.

# Final Developer Instruction

Do not say a module is complete because it is mentioned in the PRD. A module is complete only when all listed flows have working code, data persistence, services, routes/UI/API, permissions, audit logs, bilingual labels, and tests.
