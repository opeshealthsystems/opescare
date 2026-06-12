# OpesCare Extended Modules Implementation Audit Checklist

**Project:** OpesCare  
**Parent Company:** Opesware  
**Document Type:** Extended/Missing Modules Audit Checklist  
**Purpose:** Audit all OpesCare modules that were not fully covered in the first 17-core-module audit report.  
**Audit Mode:** Read-only. Do not modify code.  
**Expected Output Report:** `docs/audit/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md`  
**Build Direction:** OpesCare is being built from scratch in Laravel, with selected Python services where required.  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, UI, database structure, flows, or assumptions.  
**Repository Scope:** Audit the current OpesCare repository only. Do not assume a module exists unless there is code, migrations, routes, UI, tests, or documentation evidence.

---

# 1. Purpose of This Audit

The first implementation audit verified the following 17 core modules:

```text
Identity / Authentication
Roles / Permissions / Facilities
Health ID / Medical ID
Patient Profile / EMR
Consent / Privacy / Access Control
Partner Governance
API / SDK / Connect Widget
Notifications / Alerts / Messaging
Verifiable Documents
Demo Access
Pharmacy Stock
Blood Availability
Public Health Reporting
Certification / OpesCare Academy
Verified Care Access Map
Bilingual Foundation
Core Security / Audit Foundation
```

This second audit focuses only on the **missing or extended modules** that may not yet be implemented as full standalone production modules.

The goal is to determine whether each extended module is:

```text
NOT_STARTED
PARTIAL
IMPLEMENTED
IMPLEMENTED_WITH_BUGS
NEEDS_REFACTOR
NEEDS_SECURITY_REVIEW
NEEDS_UI_REVIEW
NEEDS_TESTS
BLOCKED
DEFERRED
```

---

# 2. Audit Instructions for Agent

You must audit only. Do not edit code.

## 2.1 Do Not Do

```text
Do not create files except the final audit report.
Do not refactor code.
Do not add migrations.
Do not add models.
Do not add routes.
Do not add tests.
Do not rename files.
Do not install packages.
Do not delete files.
Do not assume implementation without evidence.
```

## 2.2 Must Do

For every module:

```text
search codebase
check docs
check models
check migrations
check routes
check controllers
check services
check policies/permissions
check UI pages/views/components
check API endpoints
check tests
check seeders/demo data
check audit logs
check bilingual support
check security/privacy rules
check known bugs/TODOs
```

## 2.3 Evidence Required

For every status, include exact evidence:

```text
file path
class/model name
migration name
route name/path
controller/service name
view/component path
test file path
documentation path
missing file/path if expected but absent
```

Do not mark a module `IMPLEMENTED` unless it has:

```text
data model or equivalent persistence
business logic/service/controller
routes or UI access
permissions/access control
audit where sensitive
tests
basic bilingual support where user-facing
```

If it only has a model but no flows, mark `PARTIAL`.

If it only appears in docs but not code, mark `NOT_STARTED` or `PLANNED_ONLY`.

---

# 3. Output Report Required

Create this file:

```text
docs/audit/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md
```

Use this structure:

```text
# OpesCare Extended Modules Implementation Audit Result

Audit Date:
Auditor/Agent:
Repository:
Branch:
Commit:
Test Command Run:
Test Result:

## Executive Summary
Total Extended Modules Audited:
Implemented:
Partial:
Not Started:
Implemented With Bugs:
Needs Security Review:
Needs Tests:
Deferred:

## P0 Critical Gaps
1.
2.
3.

## P1 Pilot-Blocking Gaps
1.
2.
3.

## P2 Production-Readiness Gaps
1.
2.
3.

## P3 Later Phase Modules
1.
2.
3.

## Module-by-Module Results
...
```

---

# 4. Status Definitions

## 4.1 NOT_STARTED

Use when:

```text
no model
no migration
no route
no service/controller
no UI
no tests
only maybe mentioned in docs
```

## 4.2 PLANNED_ONLY

Use when:

```text
module exists only in PRD/docs
no implementation evidence
```

## 4.3 PARTIAL

Use when:

```text
some models exist but flows missing
some routes exist but UI missing
some UI exists but backend missing
basic stub exists without full logic
tests missing
permissions missing
audit missing
```

## 4.4 IMPLEMENTED

Use only when:

```text
models/migrations exist
routes/controllers/services exist
UI or API access exists
permissions exist
audit exists where required
tests exist
demo data exists where useful
bilingual labels exist where user-facing
```

## 4.5 IMPLEMENTED_WITH_BUGS

Use when:

```text
module exists but tests fail
flow breaks
security/privacy bug exists
UI breaks
missing required validation causes incorrect behavior
```

## 4.6 NEEDS_SECURITY_REVIEW

Use when:

```text
module handles patient data
module handles payments
module handles insurance
module handles audit/compliance
module handles API access
module handles offline data
but security/privacy rules are not clearly implemented
```

## 4.7 NEEDS_TESTS

Use when:

```text
implementation exists but no tests or insufficient tests
```

---

# 5. Priority Definitions

## 5.1 P0 — Must Fix Before Any Pilot/Demo With Real Data

Examples:

```text
unauthorized patient data access
billing/payment data corruption
insurance exposing full records
offline sync leaking patient data
audit logs missing for sensitive operations
admin can bypass facility boundaries
patient identity duplication unsafe
```

## 5.2 P1 — Must Fix Before Controlled Pilot

Examples:

```text
appointments missing
queue flow missing for facility pilot
billing not reliable
claims not reliable
data reconciliation missing
support escalation missing
```

## 5.3 P2 — Must Fix Before Full Production

Examples:

```text
advanced analytics missing
subscription billing missing
full mobile app missing
advanced inventory missing
advanced HR/shift scheduling missing
```

## 5.4 P3 — Later Phase

Examples:

```text
telemedicine
advanced CDSS
AI-driven analytics
advanced national dashboards
```

---

# 6. Extended Module Index

Audit these extended modules:

```text
01. Appointments & Booking
02. Queue & Patient Flow
03. Billing, Payments & Wallet
04. Insurance Claims & Preauthorization
05. Telemedicine
06. Triage & Emergency Workflow
07. Ward, Admission & Bed Management
08. Inventory & Supply Chain
09. Staff / HR / Shift Management
10. Clinical Decision Support / Clinical Alerts
11. Patient Mobile App
12. Provider Mobile App
13. Offline Mode & Sync
14. Analytics & Reporting
15. Audit, Compliance & Security Operations Center
16. Support, Helpdesk & Incident Management
17. Data Import / Migration
18. Master Admin Control Center
19. Subscription / SaaS Billing
20. Data Quality & Reconciliation
21. Search / Global Search
22. File Storage & Medical Attachments
23. Appointment-to-Billing-to-Document End-to-End Flow
24. Facility Go-Live Readiness
```

---

# 7. Module 01 — Appointments & Booking

## 7.1 Purpose

Patients and facilities need appointment scheduling.

## 7.2 Required Components

Check for:

```text
Appointment model
AppointmentSlot model
ProviderAvailability model
FacilitySchedule model
AppointmentController
AppointmentService
AppointmentPolicy
appointment routes
patient booking UI
staff calendar UI
doctor calendar UI
appointment notifications
appointment reminders
appointment audit logs
appointment tests
```

## 7.3 Required Flows

Audit these flows:

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Patient books appointment |  |  |  |
| Staff books appointment for patient |  |  |  |
| Doctor/provider availability configured |  |  |  |
| Facility schedule configured |  |  |  |
| Patient reschedules appointment |  |  |  |
| Facility reschedules appointment |  |  |  |
| Patient cancels appointment |  |  |  |
| Facility cancels appointment |  |  |  |
| Appointment reminder sent |  |  |  |
| Appointment check-in |  |  |  |
| Appointment no-show tracking |  |  |  |
| Appointment linked to encounter |  |  |  |
| Appointment linked to billing |  |  |  |
| Appointment linked to telemedicine if enabled |  |  |  |

## 7.4 Must-Have Statuses

```text
requested
scheduled
confirmed
checked_in
in_progress
completed
cancelled
rescheduled
no_show
expired
```

## 7.5 Security/Privacy Checks

```text
patient cannot see other patients' appointments
provider only sees assigned appointments
facility admin only sees facility appointments
appointment details do not expose sensitive diagnosis publicly
```

---

# 8. Module 02 — Queue & Patient Flow

## 8.1 Purpose

Track patient movement inside facilities.

## 8.2 Required Components

Check for:

```text
Queue model
QueueTicket model
PatientCheckIn model
PatientFlowEvent model
QueueService
QueueController
queue display UI
staff queue dashboard
triage queue
consultation queue
lab queue
pharmacy queue
billing queue
discharge queue
queue tests
```

## 8.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Patient arrival/check-in |  |  |  |
| Queue ticket generated |  |  |  |
| Patient sent to triage |  |  |  |
| Patient sent to consultation |  |  |  |
| Patient sent to lab |  |  |  |
| Patient sent to pharmacy |  |  |  |
| Patient sent to billing |  |  |  |
| Patient discharged from queue |  |  |  |
| Priority/emergency bypass |  |  |  |
| Queue display works |  |  |  |
| Estimated wait time works/planned |  |  |  |
| Staff assignment works |  |  |  |

## 8.4 Queue Statuses

```text
waiting
called
in_service
paused
transferred
completed
skipped
cancelled
emergency_priority
```

## 8.5 Security Checks

```text
public queue display masks patient names
facility staff only see facility queue
queue events are audited
```

---

# 9. Module 03 — Billing, Payments & Wallet

## 9.1 Purpose

Manage facility billing, patient payments, receipts, refunds, and wallet/prepayment.

## 9.2 Required Components

Check for:

```text
BillingAccount model
Invoice model
InvoiceItem model
Payment model
Receipt model
Refund model
Wallet model
WalletTransaction model
PriceList model
BillingService
PaymentService
RefundService
billing routes
cashier UI
patient payment UI
financial reports
billing tests
```

## 9.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Create bill/invoice |  |  |  |
| Add consultation fee |  |  |  |
| Add lab fee |  |  |  |
| Add pharmacy item |  |  |  |
| Apply discount |  |  |  |
| Apply insurance covered amount |  |  |  |
| Calculate patient responsibility |  |  |  |
| Cash payment |  |  |  |
| Mobile money payment |  |  |  |
| Card/bank transfer payment |  |  |  |
| Generate receipt |  |  |  |
| Void payment |  |  |  |
| Refund payment |  |  |  |
| Wallet/prepayment deposit |  |  |  |
| Wallet used for payment |  |  |  |
| Payment reconciliation |  |  |  |
| Cashier report |  |  |  |

## 9.4 Required Statuses

Invoice statuses:

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

Payment statuses:

```text
pending
successful
failed
cancelled
refunded
partially_refunded
reconciled
disputed
```

## 9.5 Security/Finance Checks

```text
payment amounts cannot be negative
receipt cannot be issued without payment event
refund requires original payment
financial actions audited
cashier cannot modify paid invoice without permission
patient cannot see other patients' invoices
```

---

# 10. Module 04 — Insurance Claims & Preauthorization

## 10.1 Purpose

Manage insurance eligibility, preauthorization, claims, review, approval, rejection, and payments.

## 10.2 Required Components

Check for:

```text
InsuranceProvider model
InsurancePlan model
PatientInsurancePolicy model
EligibilityCheck model
PreauthorizationRequest model
InsuranceClaim model
ClaimItem model
ClaimDocument model
ClaimDecision model
InsuranceService
ClaimService
PreauthorizationService
payer dashboard
facility claim dashboard
claim tests
```

## 10.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Register patient insurance |  |  |  |
| Check eligibility |  |  |  |
| Request preauthorization |  |  |  |
| Approve preauthorization |  |  |  |
| Reject preauthorization |  |  |  |
| Create claim |  |  |  |
| Attach supporting documents |  |  |  |
| Submit claim |  |  |  |
| Payer reviews claim |  |  |  |
| Request missing information |  |  |  |
| Approve claim |  |  |  |
| Reject claim |  |  |  |
| Claim payment posted |  |  |  |
| Claim linked to invoice |  |  |  |
| Minimum necessary data enforced |  |  |  |

## 10.4 Security/Privacy Checks

```text
insurance users cannot see full EMR by default
claims expose minimum necessary data only
claim document downloads are audited
patient can see claim status where allowed
```

---

# 11. Module 05 — Telemedicine

## 11.1 Purpose

Enable remote care where legally and operationally appropriate.

## 11.2 Required Components

Check for:

```text
Teleconsultation model
TelemedicineConsent model
VirtualWaitingRoom model
CallSession model
TelemedicineNote model
telemedicine routes
video/audio provider abstraction
teleconsult UI
provider telemedicine dashboard
telemedicine tests
```

## 11.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Patient books teleconsultation |  |  |  |
| Telemedicine consent accepted |  |  |  |
| Patient joins virtual waiting room |  |  |  |
| Provider starts session |  |  |  |
| Audio/video session started |  |  |  |
| Provider writes note |  |  |  |
| Prescription issued after teleconsult |  |  |  |
| Payment linked to teleconsultation |  |  |  |
| Call log stored |  |  |  |
| Recording policy enforced |  |  |  |

## 11.4 Safety Checks

```text
telemedicine consent required
emergency disclaimer displayed
recording disabled by default unless policy allows
clinical limitations disclaimer exists
```

---

# 12. Module 06 — Triage & Emergency Workflow

## 12.1 Purpose

Support clinical triage and emergency escalation.

## 12.2 Required Components

Check for:

```text
TriageAssessment model
TriageScore model
EmergencyCase model
VitalSign model
ChiefComplaint model
TriageService
EmergencyWorkflowService
triage dashboard
emergency queue
triage tests
```

## 12.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Patient triage started |  |  |  |
| Chief complaint recorded |  |  |  |
| Vital signs recorded |  |  |  |
| Triage priority assigned |  |  |  |
| Emergency case escalated |  |  |  |
| Doctor notified |  |  |  |
| Emergency access used |  |  |  |
| Critical case routed |  |  |  |
| Triage reassessment |  |  |  |
| Triage completed |  |  |  |

## 12.4 Safety Checks

```text
triage does not replace clinician judgement
priority changes audited
emergency cases bypass normal queue
critical alerts require acknowledgement
```

---

# 13. Module 07 — Ward, Admission & Bed Management

## 13.1 Purpose

Manage inpatient admission, beds, wards, transfers, nursing rounds, and discharge.

## 13.2 Required Components

Check for:

```text
Admission model
Ward model
Bed model
BedAssignment model
WardTransfer model
InpatientNote model
NursingRound model
DischargePlan model
AdmissionService
BedManagementService
ward dashboard
bed board UI
admission tests
```

## 13.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Admit patient |  |  |  |
| Assign bed |  |  |  |
| Transfer ward/bed |  |  |  |
| Record inpatient note |  |  |  |
| Nursing round completed |  |  |  |
| Medication administration recorded |  |  |  |
| Bed occupancy updated |  |  |  |
| Discharge planning started |  |  |  |
| Discharge summary generated |  |  |  |
| Bed released |  |  |  |

## 13.4 Safety Checks

```text
patient cannot be assigned to occupied bed
bed transfer audited
discharge requires authorized role
```

---

# 14. Module 08 — Inventory & Supply Chain

## 14.1 Purpose

Track medical supplies, consumables, medicines, equipment, stock movement, expiry, and procurement.

## 14.2 Required Components

Check for:

```text
InventoryItem model
StockLocation model
StockBatch model
StockMovement model
Supplier model
PurchaseOrder model
GoodsReceipt model
StockAdjustment model
InventoryService
ProcurementService
inventory dashboard
stock audit reports
inventory tests
```

## 14.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Create inventory item |  |  |  |
| Receive stock |  |  |  |
| Move stock |  |  |  |
| Adjust stock |  |  |  |
| Track batch/lot |  |  |  |
| Track expiry |  |  |  |
| Low stock alert |  |  |  |
| Create purchase order |  |  |  |
| Receive goods |  |  |  |
| Stock audit |  |  |  |
| Supplier management |  |  |  |

---

# 15. Module 09 — Staff / HR / Shift Management

## 15.1 Purpose

Manage facility staff profiles, shifts, rosters, professional licenses, training, and multi-facility assignments.

## 15.2 Required Components

Check for:

```text
StaffProfile model
ProfessionalLicense model
StaffShift model
DutyRoster model
LeaveRequest model
DepartmentAssignment model
StaffCredential model
StaffService
RosterService
HR dashboard
shift calendar UI
staff tests
```

## 15.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Create staff profile |  |  |  |
| Assign department |  |  |  |
| Assign shift |  |  |  |
| Create duty roster |  |  |  |
| Request leave |  |  |  |
| Approve/reject leave |  |  |  |
| Track professional license |  |  |  |
| License expiry alert |  |  |  |
| Link training/certification |  |  |  |
| Multi-facility assignment |  |  |  |

---

# 16. Module 10 — Clinical Decision Support / Clinical Alerts

## 16.1 Purpose

Provide safe clinical warnings without overclaiming diagnosis or replacing clinician judgement.

## 16.2 Required Components

Check for:

```text
ClinicalRule model
ClinicalAlert model
DrugInteractionRule model
AllergyAlertRule model
DoseWarningRule model
AlertOverride model
ClinicalDecisionSupportService
RuleEvaluationService
CDSS dashboard
clinical alert tests
```

## 16.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Allergy alert triggered |  |  |  |
| Drug interaction alert triggered |  |  |  |
| Duplicate prescription warning |  |  |  |
| Abnormal lab alert |  |  |  |
| Critical lab alert |  |  |  |
| Age-based dose warning |  |  |  |
| Pregnancy warning where applicable |  |  |  |
| Chronic disease reminder |  |  |  |
| Vaccination reminder |  |  |  |
| Alert override requires reason |  |  |  |

## 16.4 Safety Checks

```text
CDSS disclaimer exists
alerts are advisory
override reason audited
no automated diagnosis without review
clinical rule source recorded
```

---

# 17. Module 11 — Patient Mobile App

## 17.1 Purpose

Audit whether APIs and UI scope exist for patient mobile app.

## 17.2 Required Features

Check for:

```text
Health ID card
QR code
records
lab results
prescriptions
appointments
medicine finder
blood finder
Care Access Map
consent requests
messages
notifications
documents
share records
emergency profile
settings
offline-limited access
```

## 17.3 Audit Questions

```text
Are mobile API endpoints defined?
Are patient mobile screens designed?
Is Flutter app repo/module present?
Is authentication mobile-ready?
Are push notifications supported?
Are QR display/scanning flows supported?
Are mobile privacy rules documented?
```

---

# 18. Module 12 — Provider Mobile App

## 18.1 Required Features

Check for:

```text
scan Health ID
scan document QR
patient lookup
assigned tasks
messages
critical alerts
prescription verification
lab result review
emergency access
offline-limited mode
facility context switching
```

## 18.2 Audit Questions

```text
Are provider mobile APIs defined?
Is role-based access enforced for mobile?
Can provider switch facility context safely?
Are critical alerts mobile-ready?
Is emergency access audited from mobile?
```

---

# 19. Module 13 — Offline Mode & Sync

## 19.1 Purpose

Support limited offline use and safe sync for low-connectivity environments.

## 19.2 Required Components

Check for:

```text
OfflineQueue model
SyncJob model
SyncConflict model
LocalCachePolicy model
OfflineAuditEvent model
SyncService
ConflictResolutionService
offline encryption design
offline tests
```

## 19.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Offline data capture |  |  |  |
| Local encrypted queue |  |  |  |
| Sync retry |  |  |  |
| Conflict detected |  |  |  |
| Conflict resolved |  |  |  |
| Duplicate prevention |  |  |  |
| Offline consent limitation enforced |  |  |  |
| Offline audit synced |  |  |  |
| Bridge Agent sync |  |  |  |
| Sync failure alert |  |  |  |

## 19.4 Security Checks

```text
offline cache encrypted
offline access limited
no full EMR cached by default
sync conflicts audited
offline emergency access limited and reviewed
```

---

# 20. Module 14 — Analytics & Reporting

## 20.1 Required Components

Check for:

```text
AnalyticsSnapshot model
DashboardMetric model
ReportDefinition model
FacilityAnalyticsService
PlatformAnalyticsService
DataQualityAnalyticsService
analytics dashboard
scheduled aggregation jobs
analytics tests
```

## 20.2 Required Dashboards

```text
facility analytics
patient visits
lab volumes
prescription trends
medicine shortages
blood shortages
system usage
training completion
API health
insurance claims
financial reports
data quality dashboard
```

## 20.3 Privacy Checks

```text
analytics are aggregate by default
patient-identifiable exports restricted
small-number suppression where needed
public health analytics de-identified
```

---

# 21. Module 15 — Audit, Compliance & Security Operations Center

## 21.1 Purpose

Central command center for audit logs, access reviews, security incidents, and compliance exports.

## 21.2 Required Components

Check for:

```text
SecurityIncident model
AccessReview model
AuditExport model
SuspiciousAccessFlag model
ComplianceCase model
SecurityOperationsService
AccessReviewService
ComplianceExportService
security dashboard
audit explorer UI
compliance tests
```

## 21.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Search audit logs |  |  |  |
| Filter audit by user/patient/facility |  |  |  |
| Detect suspicious access |  |  |  |
| Review emergency access |  |  |  |
| Create security incident |  |  |  |
| Breach report workflow |  |  |  |
| Role permission review |  |  |  |
| API abuse monitoring |  |  |  |
| Admin action review |  |  |  |
| Compliance export |  |  |  |

## 21.4 Security Checks

```text
audit logs cannot be edited by normal admin
exports are permissioned
sensitive audit access audited
breach workflow exists
```

---

# 22. Module 16 — Support, Helpdesk & Incident Management

## 22.1 Required Components

Check for:

```text
SupportTicket model
TicketMessage model
TicketAssignment model
IncidentReport model
KnowledgeBaseArticle model
SupportService
IncidentService
support dashboard
ticket UI
support tests
```

## 22.2 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Patient creates support ticket |  |  |  |
| Facility creates support ticket |  |  |  |
| Developer creates API support ticket |  |  |  |
| Ticket assigned |  |  |  |
| Ticket escalated |  |  |  |
| Ticket resolved |  |  |  |
| Incident created from ticket |  |  |  |
| SLA tracking |  |  |  |
| Knowledge base article viewed |  |  |  |

---

# 23. Module 17 — Data Import / Migration

## 23.1 Purpose

Support onboarding facilities and importing legacy data.

## 23.2 Required Components

Check for:

```text
ImportJob model
ImportBatch model
ImportRowError model
ImportMapping model
ImportRollback model
DataImportService
MappingReviewService
ImportValidationService
import UI
import tests
```

## 23.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| CSV import |  |  |  |
| Excel import |  |  |  |
| Patient records import |  |  |  |
| Facility import |  |  |  |
| Medicine stock import |  |  |  |
| Lab test catalog import |  |  |  |
| Insurance network import |  |  |  |
| Mapping review |  |  |  |
| Duplicate detection |  |  |  |
| Import rollback |  |  |  |
| Import audit |  |  |  |

---

# 24. Module 18 — Master Admin Control Center

## 24.1 Required Components

Check for:

```text
PlatformSetting model
Country model
Region model
LanguageSetting model
FeatureFlag model
ModuleToggle model
SystemHealthSnapshot model
AdminControlService
FeatureFlagService
SystemHealthService
master admin dashboard
platform settings UI
admin tests
```

## 24.2 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Manage countries |  |  |  |
| Manage regions |  |  |  |
| Manage languages |  |  |  |
| Manage roles/permissions |  |  |  |
| Enable/disable modules |  |  |  |
| Feature flags |  |  |  |
| Maintenance mode |  |  |  |
| System health view |  |  |  |
| Partner approvals dashboard |  |  |  |
| Platform audit view |  |  |  |

---

# 25. Module 19 — Subscription / SaaS Billing

## 25.1 Purpose

Manage paid organization plans, module access, usage limits, and subscriptions.

## 25.2 Required Components

Check for:

```text
SubscriptionPlan model
OrganizationSubscription model
SubscriptionInvoice model
UsageMetric model
PlanFeature model
SubscriptionService
UsageBillingService
PlanLimitService
subscription dashboard
billing portal UI
subscription tests
```

## 25.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Create plan |  |  |  |
| Organization subscribes |  |  |  |
| Trial starts |  |  |  |
| Trial expires |  |  |  |
| Upgrade plan |  |  |  |
| Downgrade plan |  |  |  |
| Cancel subscription |  |  |  |
| Usage limit enforced |  |  |  |
| API usage counted |  |  |  |
| Module activation by plan |  |  |  |
| Subscription invoice generated |  |  |  |

---

# 26. Module 20 — Data Quality & Reconciliation

## 26.1 Purpose

Handle duplicate records, conflicting external data, failed syncs, and data quality issues.

## 26.2 Required Components

Check for:

```text
DataQualityIssue model
ReconciliationCase model
DuplicatePatientCandidate model
ExternalRecordMatch model
DataCompletenessScore model
ReconciliationService
DuplicateDetectionService
DataQualityService
reconciliation dashboard
data quality tests
```

## 26.3 Required Flows

| Flow | Status | Evidence | Missing/Bugs |
|---|---|---|---|
| Duplicate patient detected |  |  |  |
| Duplicate reviewed |  |  |  |
| Patient records merged |  |  |  |
| Records unmerged |  |  |  |
| External record unmatched |  |  |  |
| Failed API sync creates case |  |  |  |
| Conflicting lab result mapping flagged |  |  |  |
| Missing Health ID flagged |  |  |  |
| Data completeness score calculated |  |  |  |
| Manual reconciliation queue |  |  |  |

## 26.4 Safety Checks

```text
merge requires review
unmerge supported
patient identity changes audited
external data not blindly trusted
reconciliation does not overwrite clinical data silently
```

---

# 27. Module 21 — Search / Global Search

## 27.1 Purpose

Search across patients, facilities, documents, partners, messages, and modules with permissions.

## 27.2 Required Components

Check for:

```text
GlobalSearchService
SearchIndex model/table optional
permission-aware search
patient search
document search
facility search
medicine/test search
audit for sensitive searches
search tests
```

## 27.3 Required Flows

```text
search patient by Health ID
search patient by name with permission
search document by verification code
search facility
search medicine
search lab test
search partner
search messages where authorized
```

## 27.4 Security Checks

```text
search results permission-filtered
sensitive patient searches audited
no global patient leak
```

---

# 28. Module 22 — File Storage & Medical Attachments

## 28.1 Purpose

Secure storage of medical documents, images, lab attachments, insurance files, and partner documents.

## 28.2 Required Components

Check for:

```text
FileAsset model
MedicalAttachment model
AttachmentAccessLog model
VirusScanResult model
FileStorageService
AttachmentPolicy
signed URLs
file scan placeholder
attachment tests
```

## 28.3 Required Flows

```text
upload file
scan file
classify file
attach to patient/document/claim/message
download with permission
signed URL expiry
delete/archive file
audit access
```

## 28.4 Security Checks

```text
file type whitelist
size limit
virus scan placeholder
sensitive files private by default
downloads audited
no public direct storage paths
```

---

# 29. Module 23 — Appointment-to-Billing-to-Document End-to-End Flow

## 29.1 Purpose

Audit whether separate modules work together.

## 29.2 Required End-to-End Scenario

Check if this full flow works:

```text
1. Patient books appointment.
2. Patient checks in.
3. Patient enters queue.
4. Provider creates consultation.
5. Provider orders lab test.
6. Billing invoice generated.
7. Patient pays.
8. Receipt generated.
9. Lab result released.
10. Patient receives notification.
11. Verifiable document generated.
12. Patient views document.
13. Audit logs created through entire flow.
```

## 29.3 Audit Result

```text
Status:
Break Point:
Missing Module:
Missing Flow:
Risk:
Next Action:
```

---

# 30. Module 24 — Facility Go-Live Readiness

## 30.1 Purpose

Determine whether a facility can safely go live.

## 30.2 Required Checklist

```text
facility verified
admin account created
staff roles assigned
privacy training completed
departments configured
services configured
document templates active
notification channels configured
audit logs active
demo/training completed
support contact defined
data import completed if required
go-live approval recorded
```

## 30.3 Audit Result

```text
Status:
Missing:
Risks:
Can Go Live: YES / NO
```

---

# 31. Cross-Module Security Scan

Run after all module checks.

| Check | Status | Evidence | Notes |
|---|---|---|---|
| Facility boundaries enforced across all extended modules |  |  |  |
| Patient data not exposed through billing |  |  |  |
| Patient data not exposed through insurance |  |  |  |
| Patient data not exposed through support tickets |  |  |  |
| Offline cache is encrypted or not implemented |  |  |  |
| Mobile APIs enforce same permissions as web |  |  |  |
| File downloads are permissioned |  |  |  |
| Audit logs exist for financial actions |  |  |  |
| Audit logs exist for insurance actions |  |  |  |
| Audit logs exist for admin actions |  |  |  |
| Search is permission-filtered |  |  |  |
| Data import cannot overwrite records silently |  |  |  |
| CDSS alerts are advisory, not automatic diagnosis |  |  |  |

---

# 32. Cross-Module UI Scan

| Check | Status | Evidence | Notes |
|---|---|---|---|
| Extended modules follow OpesCare color system |  |  |  |
| Extended modules use Lucide icons where applicable |  |  |  |
| No emoji icons in production UI |  |  |  |
| Mobile responsive layouts exist |  |  |  |
| Empty states exist |  |  |  |
| Error states exist |  |  |  |
| Loading states exist |  |  |  |
| French labels render |  |  |  |
| Patient-facing language is clear |  |  |  |
| Medical jargon avoided in patient UI |  |  |  |

---

# 33. Cross-Module Test Scan

| Check | Status | Evidence | Notes |
|---|---|---|---|
| Appointment tests exist |  |  |  |
| Queue tests exist |  |  |  |
| Billing tests exist |  |  |  |
| Payment/refund tests exist |  |  |  |
| Insurance tests exist |  |  |  |
| Telemedicine tests exist/planned |  |  |  |
| Triage tests exist |  |  |  |
| Admission/bed tests exist |  |  |  |
| Inventory tests exist |  |  |  |
| Staff/HR tests exist |  |  |  |
| CDSS tests exist |  |  |  |
| Offline sync tests exist |  |  |  |
| Analytics tests exist |  |  |  |
| Audit/security center tests exist |  |  |  |
| Support/helpdesk tests exist |  |  |  |
| Import/migration tests exist |  |  |  |
| Master admin tests exist |  |  |  |
| Subscription tests exist |  |  |  |
| Reconciliation tests exist |  |  |  |
| End-to-end appointment-to-document test exists |  |  |  |

---

# 34. Recommended Report Format Per Module

Use this for every module in the final result:

```text
## Module Name

Status:
Priority:
Evidence:
- Models:
- Migrations:
- Routes:
- Controllers/Services:
- UI:
- Tests:
- Docs:

Implemented Flows:
1.
2.
3.

Missing Flows:
1.
2.
3.

Bugs Found:
1.
2.
3.

Security/Privacy Concerns:
1.
2.
3.

Bilingual/UI Concerns:
1.
2.
3.

Test Gaps:
1.
2.
3.

Recommendation:
Build / Patch / Defer / Security Review / Ready

Next Developer Task:
```

---

# 35. Final Build Order Recommendation Logic

After audit, recommend build order based on real evidence.

Use this order if modules are missing:

## 35.1 Highest Priority for Hospital Pilot

```text
1. Appointments & Booking
2. Queue & Patient Flow
3. Billing, Payments & Wallet
4. Insurance Claims & Preauthorization
5. Data Quality & Reconciliation
6. Audit, Compliance & Security Operations Center
7. Support / Helpdesk
8. Master Admin Control Center
```

## 35.2 Second Priority

```text
9. Staff / HR / Shift Management
10. Triage & Emergency Workflow
11. Inventory & Supply Chain
12. Analytics & Reporting
13. File Storage & Medical Attachments
14. Data Import / Migration
```

## 35.3 Later Phase

```text
15. Telemedicine
16. Ward / Admission / Bed Management
17. Clinical Decision Support
18. Patient Mobile App
19. Provider Mobile App
20. Offline Mode & Sync
21. Subscription / SaaS Billing
```

Adjust this order if the audit shows that some modules already exist or are more urgent for the chosen pilot.

---

# 36. Final Agent Prompt

Copy and run this with Jules, Codex, Claude Code, or another code-audit agent:

```text
Read docs/audit/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_CHECKLIST.md.

Do not modify code.
Do not refactor.
Do not create new features.
Do not add files except the final report.
Do not use OpesHIS OS.
Do not copy OpesHIS OS code, UI, database, or assumptions.

Task:
Run a full read-only implementation audit of the OpesCare repository for the extended modules that were not covered in the first 17-core-module audit.

Audit these modules:
1. Appointments & Booking
2. Queue & Patient Flow
3. Billing, Payments & Wallet
4. Insurance Claims & Preauthorization
5. Telemedicine
6. Triage & Emergency Workflow
7. Ward, Admission & Bed Management
8. Inventory & Supply Chain
9. Staff / HR / Shift Management
10. Clinical Decision Support / Clinical Alerts
11. Patient Mobile App
12. Provider Mobile App
13. Offline Mode & Sync
14. Analytics & Reporting
15. Audit, Compliance & Security Operations Center
16. Support, Helpdesk & Incident Management
17. Data Import / Migration
18. Master Admin Control Center
19. Subscription / SaaS Billing
20. Data Quality & Reconciliation
21. Search / Global Search
22. File Storage & Medical Attachments
23. Appointment-to-Billing-to-Document End-to-End Flow
24. Facility Go-Live Readiness

For each module:
- identify models
- identify migrations
- identify routes
- identify controllers/services
- identify UI pages/components
- identify permissions/policies
- identify audit logs
- identify tests
- identify documentation
- identify missing flows
- identify security/privacy risks
- identify bilingual/UI gaps
- classify status as NOT_STARTED, PLANNED_ONLY, PARTIAL, IMPLEMENTED, IMPLEMENTED_WITH_BUGS, NEEDS_SECURITY_REVIEW, NEEDS_TESTS, BLOCKED, or DEFERRED
- assign priority P0, P1, P2, or P3
- provide exact file evidence

Create the final report:
docs/audit/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md

The report must include:
- executive summary
- module-by-module findings
- P0 critical gaps
- P1 pilot-blocking gaps
- P2 production-readiness gaps
- P3 later-phase modules
- recommended build order
- exact files reviewed
- exact files missing
- no assumptions without evidence

After writing the report, do not modify anything else.
```

---

# 37. Final Rule

This audit must answer one question clearly:

```text
Which OpesCare extended modules are truly implemented, which are partial, and which are missing?
```

If a module is only mentioned in docs but lacks working models, routes, services, UI, permissions, tests, and audit handling, mark it as:

```text
PLANNED_ONLY
```

or:

```text
NOT_STARTED
```

If a module has some code but incomplete flows, mark it as:

```text
PARTIAL
```

Do not mark anything as implemented without evidence.
