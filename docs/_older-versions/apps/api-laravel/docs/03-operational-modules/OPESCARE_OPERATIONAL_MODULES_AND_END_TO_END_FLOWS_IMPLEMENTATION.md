# OpesCare Operational Modules and Full End-to-End Flows Implementation Plan

**Project:** OpesCare  
**Company:** Opesware  
**Domain:** opescare.com  
**Primary stack:** Laravel + PostgreSQL  
**Recommended supporting services:** Redis queues/cache, PostGIS where geospatial search is needed, selected Python services only when justified  
**Purpose:** This document is the detailed implementation blueprint for the operational modules and complete end-to-end patient care flows that were missing, partial, or not fully connected after the core OpesCare foundation.

---

## 1. How This Document Must Be Used

This is an implementation document for Claude Code, Jules, Gemini, Codex, or any engineering agent working inside the existing OpesCare repository.

The goal is not to rebuild OpesCare from zero. The goal is to **upgrade the existing codebase**, preserve the working core modules, and implement the missing hospital-operation layer cleanly.

Before coding, the agent must:

1. Read the latest audit reports.
2. Confirm which modules already exist.
3. Preserve working modules and passing tests.
4. Patch partial modules instead of duplicating them.
5. Build missing modules with full models, migrations, services, routes, UI/API, permissions, audit logs, tests, demo data, and documentation.
6. Run tests after each module.

---

## 2. Non-Negotiable Rules

```text
Do not use or copy OpesHIS OS.
Do not destroy working OpesCare modules.
Do not duplicate existing tables/models/controllers/services.
Do not expose patient data publicly.
Do not mark a module complete unless all its flows work.
Do not allow students to perform restricted clinical actions.
Do not allow insurance users to view full EMR by default.
Do not allow support users to access patient records without permission and audit.
Do not allow QR codes to expose full patient data.
Do not allow financial changes without audit logs.
Do not allow imports to overwrite records silently.
Do not allow offline sync to cache full EMR by default.
Do not let CDSS act as automated diagnosis.
```

---

## 3. Universal Completion Standard

A module is complete only when every required flow has:

```text
models
migrations
services/business logic
controllers or API handlers
routes
UI pages or API contract
permissions/policies
request validation
audit events
notifications where needed
bilingual labels where user-facing
demo/seed data where useful
feature tests
unit tests where needed
documentation updates
```

If any flow lacks these, the module is **PARTIAL**, not complete.

---

## 4. Operational Modules Covered

This implementation plan covers:

```text
01. Appointments and Booking
02. Queue and Patient Flow
03. Billing, Payments and Wallet
04. Insurance Claims and Preauthorization
05. End-to-End Patient Visit Flow
06. Support, Helpdesk and Incident Management
07. Data Import and Migration
08. Master Admin Control Center
09. Facility Go-Live Readiness
10. Global Search
11. Staff, HR and Shift Management
12. Triage and Emergency Workflow
13. Inventory and Supply Chain
14. File Storage and Medical Attachments
15. Analytics and Reporting
16. Audit, Compliance and Security Operations Center
17. Telemedicine
18. Ward, Admission and Bed Management
19. Clinical Decision Support and Clinical Alerts
20. Offline Mode and Sync
21. Patient Mobile App API Readiness
22. Provider Mobile App API Readiness
23. Subscription and SaaS Billing
```

---

## 5. Build Order

The recommended build order is:

```text
1. Appointments and Booking
2. Queue and Patient Flow
3. Billing, Payments and Wallet
4. Insurance Claims and Preauthorization
5. End-to-End Patient Visit Flow
6. Support, Helpdesk and Incident Management
7. Data Import and Migration
8. Master Admin Control Center
9. Facility Go-Live Readiness
10. Global Search
11. Staff, HR and Shift Management
12. Triage and Emergency Workflow
13. Inventory and Supply Chain
14. File Storage and Medical Attachments
15. Analytics and Reporting
16. Audit, Compliance and Security Operations Center hardening
17. Telemedicine
18. Ward, Admission and Bed Management
19. Clinical Decision Support and Clinical Alerts
20. Offline Mode and Sync
21. Patient and Provider Mobile API readiness
22. Subscription and SaaS Billing
```

---
# Module 01 - Appointments and Booking

**Priority / Action:** P1 - must build before a serious facility pilot if missing

## Purpose

Allow patients, reception teams, doctors, clinics, hospitals, and telemedicine providers to schedule, confirm, reschedule, cancel, check in, and link appointments to encounters, queue tickets, billing, documents, and notifications.

## Required Models / Tables

```text
Appointment
AppointmentSlot
ProviderAvailability
FacilitySchedule
AppointmentType
AppointmentReminder
AppointmentCheckIn
AppointmentCancellation
AppointmentAudit
```

## Required Services

```text
AppointmentService
AvailabilityService
AppointmentReminderService
AppointmentCheckInService
AppointmentPolicyService
```

## Required Controllers / Handlers

```text
AppointmentController
PatientAppointmentController
StaffAppointmentController
ProviderCalendarController
AppointmentCheckInController
```

## Required Routes / API

```text
GET /api/v1/appointments
POST /api/v1/appointments
GET /api/v1/appointments/{id}
POST /api/v1/appointments/{id}/confirm
POST /api/v1/appointments/{id}/reschedule
POST /api/v1/appointments/{id}/cancel
POST /api/v1/appointments/{id}/check-in
GET /api/v1/providers/{id}/availability
POST /api/v1/providers/{id}/availability
```

## Required UI

```text
Patient appointment booking page
Facility staff scheduler
Provider calendar
Appointment detail page
Check-in screen
No-show dashboard
Appointment reminder settings
```

## Required Statuses

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

## Required Permissions

```text
appointments.view_own
appointments.book_own
appointments.manage_facility
appointments.manage_provider_schedule
appointments.check_in
appointments.cancel
appointments.reschedule
```

## Required Audit Events

```text
appointment_created
appointment_confirmed
appointment_rescheduled
appointment_cancelled
appointment_checked_in
appointment_no_show_marked
provider_availability_updated
```

## Flow Implementation Details

### Flow - Patient books appointment

1. Patient opens booking page.
2. Patient selects facility, department, service, provider optional, date and time.
3. System checks provider/facility availability and appointment type rules.
4. System validates patient identity or allows guest pre-registration if enabled.
5. System creates appointment as requested or scheduled depending facility rules.
6. System sends confirmation or pending approval notification.
7. System logs appointment_created audit event.
8. Appointment appears in patient dashboard and facility scheduler.

### Flow - Staff books appointment for patient

1. Receptionist searches or creates patient profile.
2. Receptionist selects service/provider/date/time.
3. System checks availability and conflicts.
4. Receptionist confirms appointment.
5. System sends notification to patient.
6. System logs actor, facility, patient, and appointment details.

### Flow - Provider availability setup

1. Provider or facility admin opens calendar settings.
2. User defines working days, time blocks, breaks, appointment duration, service types, telemedicine eligibility, maximum bookings.
3. System prevents overlapping availability rules.
4. System publishes availability to booking module.
5. Audit event provider_availability_updated is created.

### Flow - Appointment reschedule

1. Authorized user selects appointment.
2. System checks appointment status permits reschedule.
3. User selects new slot.
4. System releases old slot and reserves new slot atomically.
5. System notifies patient/provider/facility.
6. System records old and new time in audit log.

### Flow - Appointment cancellation

1. Authorized user selects appointment.
2. System requires cancellation reason if facility/staff cancels.
3. System updates status to cancelled.
4. System releases appointment slot.
5. System notifies affected parties.
6. Billing reversal or deposit rule is triggered if applicable.

### Flow - Appointment check-in

1. Patient arrives or uses mobile check-in if allowed.
2. Reception verifies appointment and patient identity.
3. System marks appointment checked_in.
4. System creates PatientCheckIn and optionally QueueTicket.
5. System links appointment to visit/encounter shell.
6. System logs appointment_checked_in.

### Flow - No-show handling

1. Scheduled job identifies appointments past grace period.
2. Staff confirms no-show or system marks automatically based policy.
3. Patient/provider notifications are sent where configured.
4. No-show metrics update analytics.
5. Audit event appointment_no_show_marked is logged.

## Edge Cases and Bugs to Prevent

```text
Double booking same slot
Provider unavailable after appointment booked
Patient books duplicate appointment
Appointment cancelled after invoice/deposit
Appointment linked to wrong facility
No-show disputes
```

## Security / Privacy Rule

Authentication must be secure, audited, rate-limited, and must not expose sensitive account existence through public errors.

## Required Tests

```text
patient_can_book_available_slot
cannot_double_book_slot
staff_can_book_for_patient
appointment_reschedule_releases_old_slot
appointment_cancel_requires_reason_for_staff
check_in_creates_queue_ticket
no_show_job_marks_expired_appointments
patient_cannot_view_other_patient_appointments
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 02 - Queue and Patient Flow

**Priority / Action:** P1 - required for real clinic/hospital operations

## Purpose

Track the patient journey inside a facility from arrival to triage, consultation, lab, billing, pharmacy, discharge, or referral. The queue system must be facility-bound, privacy-safe, and linked to appointments, encounters, billing, and notifications.

## Required Models / Tables

```text
PatientCheckIn
Queue
QueueStation
QueueTicket
PatientFlowEvent
QueueTransfer
QueuePriorityRule
QueueDisplaySetting
```

## Required Services

```text
QueueService
PatientFlowService
QueueTransferService
QueueDisplayService
WaitTimeEstimationService
```

## Required Controllers / Handlers

```text
QueueController
CheckInController
QueueStationController
QueueDisplayController
PatientFlowController
```

## Required Routes / API

```text
POST /api/v1/check-ins
GET /api/v1/queues
GET /api/v1/queues/{id}/tickets
POST /api/v1/queue-tickets/{id}/call
POST /api/v1/queue-tickets/{id}/transfer
POST /api/v1/queue-tickets/{id}/complete
POST /api/v1/queue-tickets/{id}/skip
GET /api/v1/facilities/{id}/queue-display
```

## Required UI

```text
Reception check-in screen
Queue board for staff
Public masked queue display
Station dashboards for triage/consultation/lab/billing/pharmacy
Patient flow timeline
```

## Required Statuses

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

## Required Permissions

```text
queue.view_facility
queue.manage_station
queue.call_patient
queue.transfer_patient
queue.override_priority
queue.view_public_display
```

## Required Audit Events

```text
patient_checked_in
queue_ticket_created
queue_ticket_called
queue_ticket_transferred
queue_ticket_completed
queue_priority_overridden
patient_flow_event_created
```

## Flow Implementation Details

### Flow - Patient arrival and check-in

1. Patient arrives with appointment, walk-in request, referral, or emergency need.
2. Reception searches patient by Health ID or demographics.
3. System verifies facility context and consent/care relationship if record access is needed.
4. Reception creates check-in record.
5. System creates queue ticket for first station based on visit type.
6. Patient receives queue number or masked display reference.
7. Audit event patient_checked_in is logged.

### Flow - Queue ticket generation

1. System determines queue station: reception, triage, consultation, lab, billing, pharmacy, discharge.
2. System generates ticket number unique per facility/day/station.
3. System assigns priority based on appointment, triage, emergency, disability, age, or facility rule.
4. System stores ticket status as waiting.
5. Ticket appears on staff queue board.

### Flow - Call patient to station

1. Staff opens station queue.
2. Staff clicks call next or selects ticket.
3. System updates status to called.
4. Public display shows masked ticket number only.
5. Optional audio announcement plays without patient full name.
6. Audit event queue_ticket_called is logged.

### Flow - Transfer patient between stations

1. Staff completes current station task.
2. System asks next destination.
3. Staff chooses next station and reason.
4. System closes current queue entry and creates transfer event.
5. New station receives waiting ticket.
6. Patient flow timeline updates.

### Flow - Emergency priority bypass

1. Authorized triage/emergency staff marks emergency priority.
2. System requires reason.
3. Ticket moves ahead of normal queue according to facility policy.
4. Doctor/nurse alert is triggered.
5. Audit logs priority override.

### Flow - Queue completion/discharge

1. Final station marks flow completed.
2. System verifies open tasks: unpaid bill, pending pharmacy, pending lab, pending discharge docs.
3. If no blockers, visit can be closed.
4. Patient flow timeline is finalized.

## Edge Cases and Bugs to Prevent

```text
Patient checked into wrong facility
Duplicate check-in same day
Patient skipped but returns
Emergency priority abuse
Public queue displays patient name accidentally
Station unavailable
```

## Security / Privacy Rule

Role, organization, department and facility boundaries must be enforced in every query, UI and API response.

## Required Tests

```text
check_in_creates_ticket
public_display_masks_patient_identity
staff_can_call_next_ticket
transfer_creates_flow_event
emergency_priority_requires_reason
facility_staff_cannot_view_other_facility_queue
visit_cannot_close_with_open_required_steps
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 03 - Billing, Payments and Wallet

**Priority / Action:** P1 - required before paid facility workflows

## Purpose

Create the financial engine for patient services, invoices, payments, receipts, refunds, wallet/prepayments, cashier reports, reconciliation, and links to insurance and verifiable documents.

## Required Models / Tables

```text
BillingAccount
PriceList
ServicePrice
Invoice
InvoiceItem
Payment
Receipt
Refund
Wallet
WalletTransaction
PaymentReconciliation
CashierSession
```

## Required Services

```text
BillingService
InvoiceService
PaymentService
ReceiptService
RefundService
WalletService
CashierReportService
PaymentReconciliationService
```

## Required Controllers / Handlers

```text
InvoiceController
PaymentController
ReceiptController
RefundController
WalletController
CashierController
PatientBillingController
```

## Required Routes / API

```text
POST /api/v1/invoices
GET /api/v1/invoices/{id}
POST /api/v1/invoices/{id}/items
POST /api/v1/invoices/{id}/issue
POST /api/v1/payments
POST /api/v1/payments/{id}/void
POST /api/v1/refunds
GET /api/v1/receipts/{id}
POST /api/v1/wallet/deposits
POST /api/v1/wallet/payments
GET /api/v1/cashier/reports
```

## Required UI

```text
Cashier dashboard
Patient billing page
Invoice detail page
Payment screen
Receipt preview/download
Refund screen
Wallet ledger
Financial report dashboard
```

## Required Statuses

```text
invoice:draft
invoice:issued
invoice:partially_paid
invoice:paid
invoice:cancelled
invoice:voided
invoice:refunded
invoice:overdue
payment:pending
payment:successful
payment:failed
payment:cancelled
payment:refunded
payment:partially_refunded
payment:reconciled
payment:disputed
```

## Required Permissions

```text
billing.view
billing.create_invoice
billing.issue_invoice
payments.record
payments.refund
wallet.manage
cashier.reports
billing.view_patient_own
```

## Required Audit Events

```text
invoice_created
invoice_item_added
invoice_issued
payment_recorded
receipt_generated
refund_processed
wallet_deposit_recorded
cashier_session_closed
payment_reconciled
```

## Flow Implementation Details

### Flow - Create invoice

1. Authorized billing staff opens patient visit or standalone billing page.
2. System fetches patient, facility, visit, appointment, and insurance context where available.
3. Staff selects billable items from price list or adds authorized custom item.
4. System calculates subtotal, discount, insurance coverage, patient responsibility, and balance.
5. Invoice remains draft until issued.
6. Audit event invoice_created is logged.

### Flow - Add service/lab/pharmacy item

1. Service module sends billable event or cashier manually adds item.
2. System validates item code, quantity, price, facility price list, and authorization.
3. System recalculates totals.
4. If invoice is already issued, adjustment rules apply and audit reason is required.

### Flow - Apply insurance coverage

1. System checks patient policy and eligibility result.
2. Coverage rules determine covered amount and patient responsibility.
3. If preauthorization is required, invoice item is flagged pending authorization.
4. Claim draft may be created automatically.
5. Patient-facing bill shows coverage estimate with disclaimer.

### Flow - Record payment

1. Cashier opens issued invoice.
2. Cashier selects payment method: cash, mobile money, card, bank transfer, wallet, insurance.
3. System validates amount and method details.
4. Payment is recorded as pending or successful depending method.
5. Invoice balance updates.
6. If paid, receipt is generated.
7. Audit event payment_recorded is logged.

### Flow - Generate receipt

1. Successful payment triggers receipt generation.
2. Receipt includes receipt number, payment method, amount, payer, cashier, invoice reference, QR verification, and status.
3. Receipt document is stored and linked to official documents module.
4. Patient receives notification.

### Flow - Process refund

1. Authorized user selects original payment.
2. System validates refundable amount.
3. User enters refund reason.
4. System creates refund record and updates payment status.
5. Invoice balance and wallet/payment state update.
6. Refund receipt is generated.
7. Audit event refund_processed is logged.

### Flow - Wallet deposit and payment

1. Patient or cashier records wallet deposit.
2. System creates wallet transaction after payment confirmation.
3. Wallet balance updates.
4. User can apply wallet balance to invoice.
5. Every wallet movement is immutable and audited.

### Flow - Cashier session close

1. Cashier opens daily closing.
2. System totals cash/mobile/card/bank/wallet payments.
3. Cashier enters physical cash count.
4. System detects variance.
5. Supervisor approval required for discrepancy depending threshold.
6. Cashier report is generated.

## Edge Cases and Bugs to Prevent

```text
Negative invoice item
Payment exceeds balance
Receipt without successful payment
Refund without original payment
Cashier edits paid invoice
Insurance amount greater than invoice
Wallet double-spend
Mobile money callback duplicate
```

## Security / Privacy Rule

Health ID QR must never contain full medical history. Tokens must be random, revocable and stored hashed.

## Required Tests

```text
invoice_totals_calculate_correctly
negative_amount_rejected
payment_updates_invoice_balance
receipt_requires_successful_payment
refund_requires_reason_and_original_payment
wallet_balance_cannot_go_negative
cashier_cannot_modify_paid_invoice_without_permission
patient_cannot_view_other_patient_invoice
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 04 - Insurance Claims and Preauthorization

**Priority / Action:** P1/P2 depending pilot payer involvement

## Purpose

Support insurance policy registration, eligibility checks, preauthorization, claim creation, supporting documents, claim review, approval/rejection, payment posting, and minimum necessary data access.

## Required Models / Tables

```text
InsuranceProvider
InsurancePlan
PatientInsurancePolicy
EligibilityCheck
PreauthorizationRequest
InsuranceClaim
ClaimItem
ClaimDocument
ClaimDecision
ClaimPayment
```

## Required Services

```text
InsuranceEligibilityService
PreauthorizationService
ClaimService
ClaimAccessPolicyService
ClaimDocumentService
ClaimPaymentService
```

## Required Controllers / Handlers

```text
InsurancePolicyController
EligibilityController
PreauthorizationController
ClaimController
PayerClaimController
ClaimDocumentController
```

## Required Routes / API

```text
POST /api/v1/patient-insurance-policies
POST /api/v1/insurance/eligibility-checks
POST /api/v1/insurance/preauthorizations
POST /api/v1/insurance/claims
POST /api/v1/insurance/claims/{id}/submit
POST /api/v1/insurance/claims/{id}/request-info
POST /api/v1/insurance/claims/{id}/approve
POST /api/v1/insurance/claims/{id}/reject
POST /api/v1/insurance/claims/{id}/payments
```

## Required UI

```text
Patient insurance policy screen
Facility claims dashboard
Payer claims dashboard
Preauthorization review page
Claim document viewer
Patient claim status page
```

## Required Statuses

```text
policy:active
policy:inactive
policy:expired
eligibility:eligible
eligibility:not_eligible
preauth:draft
preauth:submitted
preauth:approved
preauth:rejected
preauth:expired
claim:draft
claim:submitted
claim:under_review
claim:more_info_required
claim:approved
claim:partially_approved
claim:rejected
claim:paid
claim:closed
```

## Required Permissions

```text
insurance.policies.manage
insurance.eligibility.check
insurance.claims.create
insurance.claims.submit
insurance.claims.review
insurance.claims.decide
insurance.claims.view_minimum_data
```

## Required Audit Events

```text
insurance_policy_registered
eligibility_checked
preauthorization_submitted
preauthorization_decided
claim_created
claim_submitted
claim_document_accessed
claim_decided
claim_payment_posted
```

## Flow Implementation Details

### Flow - Register patient insurance policy

1. Authorized user adds insurer, plan, policy number, member number, expiry, coverage type.
2. System validates duplicate active policy.
3. Policy document can be attached securely.
4. System logs insurance_policy_registered.

### Flow - Eligibility check

1. Facility initiates eligibility check before service or billing.
2. System sends request to insurer API or records manual eligibility result.
3. Result is stored with timestamp, scope, and validity period.
4. Invoice can use eligibility estimate but must show disclaimer.

### Flow - Preauthorization request

1. Provider/billing creates request for service requiring approval.
2. System attaches minimum necessary clinical and financial documents.
3. Request submitted to payer dashboard/API.
4. Payer reviews and approves/rejects/requests info.
5. Decision updates invoice/claim state.

### Flow - Create and submit claim

1. Invoice or visit generates claim draft.
2. System adds claim items and supporting documents.
3. System enforces minimum necessary data.
4. Billing staff reviews and submits.
5. Payer receives claim.
6. Audit logs claim_submitted.

### Flow - Payer review

1. Payer user opens claim.
2. System shows only allowed claim details, not full EMR.
3. Payer can approve, partially approve, reject, or request information.
4. Decision reason required for rejection or partial approval.
5. Facility and patient are notified where appropriate.

### Flow - Claim payment posting

1. Approved claim receives payment reference.
2. Payment is posted against invoice.
3. Invoice insurance balance updates.
4. Reconciliation status is updated.
5. Audit logs claim_payment_posted.

## Edge Cases and Bugs to Prevent

```text
Expired policy
Duplicate policy
Payer requests full EMR
Claim submitted without invoice
Claim duplicate submission
Preauthorization expired before service
Patient responsibility changes after claim decision
```

## Security / Privacy Rule

EMR access requires consent, care relationship, emergency access or valid policy basis. Every access is audited.

## Required Tests

```text
eligibility_result_stored
claim_minimum_data_enforced
payer_cannot_view_full_emr
preauth_decision_updates_claim
claim_rejection_requires_reason
claim_payment_updates_invoice
claim_documents_access_audited
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 05 - End-to-End Patient Visit Flow

**Priority / Action:** P1 - the most important operational integration

## Purpose

Connect appointment, check-in, queue, triage, consultation, lab/prescription, billing, payment, documents, notifications, and audit into one patient journey.

## Required Models / Tables

```text
Visit
VisitStep
VisitTimeline
Encounter
Appointment
QueueTicket
Invoice
Payment
Receipt
OfficialDocument
NotificationEvent
```

## Required Services

```text
VisitOrchestrationService
VisitStepService
VisitClosureService
VisitAuditService
VisitBillingBridgeService
```

## Required Controllers / Handlers

```text
VisitController
VisitWorkspaceController
VisitStepController
VisitClosureController
```

## Required Routes / API

```text
POST /api/v1/visits
GET /api/v1/visits/{id}
POST /api/v1/visits/{id}/start
POST /api/v1/visits/{id}/steps
POST /api/v1/visits/{id}/close
GET /api/v1/visits/{id}/timeline
```

## Required UI

```text
Unified visit workspace
Patient visit timeline
Provider visit screen
Reception visit screen
Cashier visit billing panel
Visit closure checklist
```

## Required Statuses

```text
planned
arrived
checked_in
in_queue
in_triage
in_consultation
awaiting_lab
awaiting_billing
awaiting_pharmacy
ready_for_discharge
completed
cancelled
abandoned
```

## Required Permissions

```text
visits.view
visits.create
visits.update_step
visits.close
visits.view_timeline
```

## Required Audit Events

```text
visit_created
visit_started
visit_step_added
visit_status_changed
visit_closed
visit_reopened
visit_blocked_from_closure
```

## Flow Implementation Details

### Flow - Appointment to visit start

1. Patient has confirmed appointment or arrives walk-in.
2. Reception checks in patient.
3. System creates Visit if not already created.
4. Visit is linked to appointment, patient, facility, provider/department if known.
5. QueueTicket is created for first station.
6. Visit timeline records arrival.

### Flow - Triage to consultation

1. Triage staff records vitals/chief complaint.
2. System adds triage step to visit.
3. If urgent, priority changes and doctor alert is sent.
4. Patient transfers to consultation queue.
5. Doctor opens unified visit workspace.

### Flow - Consultation to lab/prescription

1. Doctor records consultation note.
2. Doctor may order lab, prescribe medication, refer, or create document.
3. Each action becomes a visit step.
4. Billing draft items may be created from services.
5. Patient timeline updates.

### Flow - Billing and payment

1. Billable visit items generate invoice.
2. Cashier records payment.
3. Receipt is generated and linked to visit.
4. If insurance applies, claim/preauthorization flow is triggered.
5. Visit cannot close if required billing is unpaid unless facility policy allows credit/waiver.

### Flow - Result/document notification

1. Lab result or prescription document is released.
2. Official verifiable document is generated.
3. Patient/provider receives notification.
4. Document link appears in visit timeline.

### Flow - Visit closure

1. System checks open blockers: queue steps, unsigned notes, unpaid bill, pending lab, pending pharmacy, discharge instruction.
2. Authorized user closes visit if no blockers or justified override.
3. Visit status becomes completed.
4. Audit event visit_closed is created.

## Edge Cases and Bugs to Prevent

```text
Walk-in without appointment
Patient leaves before billing
Lab result after visit closure
Provider forgets to sign note
Partial insurance approval after payment
Wrong patient check-in
Visit reopened
```

## Security / Privacy Rule

Consent is purpose-specific, scope-specific, time-limited, revocable and auditable.

## Required Tests

```text
appointment_checkin_creates_visit
visit_timeline_records_all_steps
visit_cannot_close_with_unpaid_required_bill
visit_closure_blocks_unsigned_note
lab_result_after_visit_links_to_timeline
full_visit_e2e_test_passes
wrong_facility_staff_cannot_access_visit
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 06 - Support, Helpdesk and Incident Management

**Priority / Action:** Build if missing

## Purpose

Manage patient, facility, developer and internal support tickets with assignment, SLA, escalation, incidents and knowledge base.

## Required Models / Tables

```text
SupportTicket
TicketMessage
TicketAssignment
IncidentReport
KnowledgeBaseArticle
SlaPolicy
```

## Required Services

```text
SupportService
TicketAssignmentService
IncidentService
KnowledgeBaseService
```

## Required Controllers / Handlers

```text
Support,HelpdeskandIncidentManagementController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
support,_helpdesk_and_incident_management.view
support,_helpdesk_and_incident_management.manage
```

## Required Audit Events

```text
support,_helpdesk_and_incident_management_created
support,_helpdesk_and_incident_management_updated
support,_helpdesk_and_incident_management_completed
```

## Flow Implementation Details

### Flow - Patient creates support ticket

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Facility creates support ticket

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Developer creates API support ticket

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Ticket assignment

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Ticket escalation

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Ticket resolution

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Incident creation from ticket

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - SLA monitoring

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Knowledge base publishing

1. Open the Support, Helpdesk and Incident Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Partners can only contribute data after verification and permission assignment.

## Required Tests

```text
patient_creates_support_ticket_flow_test
facility_creates_support_ticket_flow_test
developer_creates_api_support_ticket_flow_test
ticket_assignment_flow_test
ticket_escalation_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 07 - Data Import and Migration

**Priority / Action:** Build if missing

## Purpose

Import legacy patient, facility, stock, lab catalog and insurance data safely with preview, mapping, duplicate detection and rollback.

## Required Models / Tables

```text
ImportJob
ImportBatch
ImportRowError
ImportMapping
ImportRollback
ImportTemplate
```

## Required Services

```text
DataImportService
ImportValidationService
MappingReviewService
ImportRollbackService
```

## Required Controllers / Handlers

```text
DataImportandMigrationController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
data_import_and_migration.view
data_import_and_migration.manage
```

## Required Audit Events

```text
data_import_and_migration_created
data_import_and_migration_updated
data_import_and_migration_completed
```

## Flow Implementation Details

### Flow - CSV import

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Excel import

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Patient import

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Facility import

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Medicine stock import

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Lab catalog import

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Insurance network import

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Mapping review

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Duplicate detection

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Import rollback

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Import audit

1. Open the Data Import and Migration workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

External APIs must be scoped, rate-limited, audited and idempotent.

## Required Tests

```text
csv_import_flow_test
excel_import_flow_test
patient_import_flow_test
facility_import_flow_test
medicine_stock_import_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 08 - Master Admin Control Center

**Priority / Action:** Complete if partial

## Purpose

Central super-admin console for countries, regions, languages, roles, module toggles, feature flags, partner approvals and system health.

## Required Models / Tables

```text
PlatformSetting
Country
Region
LanguageSetting
FeatureFlag
ModuleToggle
SystemHealthSnapshot
```

## Required Services

```text
AdminControlService
FeatureFlagService
SystemHealthService
PlatformSettingsService
```

## Required Controllers / Handlers

```text
MasterAdminControlCenterController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
master_admin_control_center.view
master_admin_control_center.manage
```

## Required Audit Events

```text
master_admin_control_center_created
master_admin_control_center_updated
master_admin_control_center_completed
```

## Flow Implementation Details

### Flow - Manage countries

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Manage regions

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Manage languages

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Manage roles

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Manage permissions

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Enable or disable modules

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Feature flags

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Maintenance mode

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - System health view

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Partner approval overview

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Platform audit

1. Open the Master Admin Control Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

External notification channels must not expose clinical details.

## Required Tests

```text
manage_countries_flow_test
manage_regions_flow_test
manage_languages_flow_test
manage_roles_flow_test
manage_permissions_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 09 - Facility Go-Live Readiness

**Priority / Action:** Complete if partial

## Purpose

Checklist and approval workflow to ensure a facility is safe to launch with real users and real patient data.

## Required Models / Tables

```text
GoLiveChecklist
GoLiveChecklistItem
GoLiveApproval
FacilityReadinessScore
```

## Required Services

```text
GoLiveReadinessService
FacilityReadinessScoringService
GoLiveApprovalService
```

## Required Controllers / Handlers

```text
FacilityGo-LiveReadinessController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
facility_go-live_readiness.view
facility_go-live_readiness.manage
```

## Required Audit Events

```text
facility_go-live_readiness_created
facility_go-live_readiness_updated
facility_go-live_readiness_completed
```

## Flow Implementation Details

### Flow - Facility verification

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Admin account check

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Staff role check

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Privacy training check

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Department setup

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Service setup

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Document template activation

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Notification setup

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Audit log check

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Support contact check

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Data import check

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Go-live approval

1. Open the Facility Go-Live Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Public document verification must show only authenticity/status metadata, not clinical details.

## Required Tests

```text
facility_verification_flow_test
admin_account_check_flow_test
staff_role_check_flow_test
privacy_training_check_flow_test
department_setup_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 10 - Global Search

**Priority / Action:** Complete if partial

## Purpose

Permission-filtered search across Health IDs, patients, documents, facilities, medicines, lab tests, partners, messages and support tickets.

## Required Models / Tables

```text
SearchIndex
SearchLog
SavedSearch
```

## Required Services

```text
GlobalSearchService
SearchPermissionService
SearchIndexingService
```

## Required Controllers / Handlers

```text
GlobalSearchController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
global_search.view
global_search.manage
```

## Required Audit Events

```text
global_search_created
global_search_updated
global_search_completed
```

## Flow Implementation Details

### Flow - Search patient by Health ID

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search patient by name with permission

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search document by verification code

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search facility

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search medicine

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search lab test

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search partner

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search authorized messages

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Search support tickets

1. Open the Global Search workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Demo users must never access production data.

## Required Tests

```text
search_patient_by_health_id_flow_test
search_patient_by_name_with_permission_flow_test
search_document_by_verification_code_flow_test
search_facility_flow_test
search_medicine_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 11 - Staff, HR and Shift Management

**Priority / Action:** Complete if partial

## Purpose

Facility staff profiles, professional licenses, shifts, rosters, leave, training status and multi-facility assignments.

## Required Models / Tables

```text
StaffProfile
ProfessionalLicense
StaffShift
DutyRoster
LeaveRequest
DepartmentAssignment
```

## Required Services

```text
StaffService
RosterService
LeaveService
ProfessionalLicenseService
```

## Required Controllers / Handlers

```text
Staff,HRandShiftManagementController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
staff,_hr_and_shift_management.view
staff,_hr_and_shift_management.manage
```

## Required Audit Events

```text
staff,_hr_and_shift_management_created
staff,_hr_and_shift_management_updated
staff,_hr_and_shift_management_completed
```

## Flow Implementation Details

### Flow - Create staff profile

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Assign department

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Assign shift

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Create duty roster

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Request leave

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Approve or reject leave

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Track license

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - License expiry alert

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Link training certification

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Multi-facility assignment

1. Open the Staff, HR and Shift Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Patient-facing translations must be clear and free of unnecessary medical jargon.

## Required Tests

```text
create_staff_profile_flow_test
assign_department_flow_test
assign_shift_flow_test
create_duty_roster_flow_test
request_leave_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 12 - Triage and Emergency Workflow

**Priority / Action:** Complete if partial

## Purpose

Clinical triage workflow for arrival priority, vitals, chief complaint, emergency escalation and reassessment.

## Required Models / Tables

```text
TriageAssessment
TriageScore
EmergencyCase
ChiefComplaint
VitalSign
TriageReassessment
```

## Required Services

```text
TriageService
EmergencyWorkflowService
TriageScoringService
```

## Required Controllers / Handlers

```text
TriageandEmergencyWorkflowController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
triage_and_emergency_workflow.view
triage_and_emergency_workflow.manage
```

## Required Audit Events

```text
triage_and_emergency_workflow_created
triage_and_emergency_workflow_updated
triage_and_emergency_workflow_completed
```

## Flow Implementation Details

### Flow - Start triage

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Record chief complaint

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Record vitals

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Assign priority

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Escalate emergency

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Notify doctor

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Use emergency access

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Route critical case

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Reassess triage

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Complete triage

1. Open the Triage and Emergency Workflow workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Production UI must use hospital-grade design and Lucide icons, not emojis.

## Required Tests

```text
start_triage_flow_test
record_chief_complaint_flow_test
record_vitals_flow_test
assign_priority_flow_test
escalate_emergency_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 13 - Inventory and Supply Chain

**Priority / Action:** Complete if partial

## Purpose

Facility stock management for supplies, consumables, medicines, equipment, suppliers and procurement.

## Required Models / Tables

```text
InventoryItem
StockLocation
StockBatch
StockMovement
Supplier
PurchaseOrder
GoodsReceipt
StockAdjustment
```

## Required Services

```text
InventoryService
StockMovementService
ProcurementService
StockAuditService
```

## Required Controllers / Handlers

```text
InventoryandSupplyChainController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
inventory_and_supply_chain.view
inventory_and_supply_chain.manage
```

## Required Audit Events

```text
inventory_and_supply_chain_created
inventory_and_supply_chain_updated
inventory_and_supply_chain_completed
```

## Flow Implementation Details

### Flow - Create inventory item

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Receive stock

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Move stock

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Adjust stock

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Track batch/lot

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Track expiry

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Low stock alert

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Create purchase order

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Receive goods

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Stock audit

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Supplier management

1. Open the Inventory and Supply Chain workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Medicine availability is reported, not guaranteed, and must show freshness warnings.

## Required Tests

```text
create_inventory_item_flow_test
receive_stock_flow_test
move_stock_flow_test
adjust_stock_flow_test
track_batch/lot_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 14 - File Storage and Medical Attachments

**Priority / Action:** Complete if partial

## Purpose

Secure file upload, storage, scanning placeholders, classification, signed download and audit for medical files and attachments.

## Required Models / Tables

```text
FileAsset
MedicalAttachment
AttachmentAccessLog
VirusScanResult
FileShareToken
```

## Required Services

```text
FileStorageService
AttachmentService
FileAccessPolicyService
VirusScanPlaceholderService
```

## Required Controllers / Handlers

```text
FileStorageandMedicalAttachmentsController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
file_storage_and_medical_attachments.view
file_storage_and_medical_attachments.manage
```

## Required Audit Events

```text
file_storage_and_medical_attachments_created
file_storage_and_medical_attachments_updated
file_storage_and_medical_attachments_completed
```

## Flow Implementation Details

### Flow - Upload file

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Validate file type and size

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Scan file placeholder

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Classify file

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Attach to patient/document/claim/message

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Download with permission

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Generate signed URL

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Expire signed URL

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Archive/delete file

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Audit file access

1. Open the File Storage and Medical Attachments workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Blood availability is reported, not guaranteed, and patient identity must be protected.

## Required Tests

```text
upload_file_flow_test
validate_file_type_and_size_flow_test
scan_file_placeholder_flow_test
classify_file_flow_test
attach_to_patient/document/claim/message_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 15 - Analytics and Reporting

**Priority / Action:** Complete if partial

## Purpose

Operational, financial, clinical, training, API, public health and data quality dashboards with privacy-safe aggregation.

## Required Models / Tables

```text
AnalyticsSnapshot
DashboardMetric
ReportDefinition
MetricSnapshot
ReportExport
```

## Required Services

```text
AnalyticsAggregationService
FacilityAnalyticsService
PlatformAnalyticsService
ReportExportService
```

## Required Controllers / Handlers

```text
AnalyticsandReportingController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
analytics_and_reporting.view
analytics_and_reporting.manage
```

## Required Audit Events

```text
analytics_and_reporting_created
analytics_and_reporting_updated
analytics_and_reporting_completed
```

## Flow Implementation Details

### Flow - Facility visits analytics

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Lab volume analytics

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Prescription trends

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Medicine shortage analytics

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Blood shortage analytics

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Training completion

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - API health

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Insurance claims analytics

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Financial reports

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Data quality dashboard

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Export report

1. Open the Analytics and Reporting workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Public health reports must be aggregate/de-identified unless legal authority permits otherwise.

## Required Tests

```text
facility_visits_analytics_flow_test
lab_volume_analytics_flow_test
prescription_trends_flow_test
medicine_shortage_analytics_flow_test
blood_shortage_analytics_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 16 - Audit, Compliance and Security Operations Center

**Priority / Action:** Harden if partial

## Purpose

Central command center for audit explorer, suspicious access, incidents, breach workflows, role reviews, emergency reviews and compliance exports.

## Required Models / Tables

```text
AuditEvent
SecurityIncident
AccessReview
SuspiciousAccessFlag
ComplianceCase
AuditExport
```

## Required Services

```text
SecurityOperationsService
AccessReviewService
SuspiciousAccessDetectionService
ComplianceExportService
```

## Required Controllers / Handlers

```text
Audit,ComplianceandSecurityOperationsCenterController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
audit,_compliance_and_security_operations_center.view
audit,_compliance_and_security_operations_center.manage
```

## Required Audit Events

```text
audit,_compliance_and_security_operations_center_created
audit,_compliance_and_security_operations_center_updated
audit,_compliance_and_security_operations_center_completed
```

## Flow Implementation Details

### Flow - Search audit logs

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Filter audit logs

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Detect suspicious access

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Review emergency access

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Create incident

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Breach report workflow

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Role permission review

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - API abuse monitoring

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Admin action review

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Compliance export

1. Open the Audit, Compliance and Security Operations Center workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Certification confirms platform/digital health competency, not professional licensing.

## Required Tests

```text
search_audit_logs_flow_test
filter_audit_logs_flow_test
detect_suspicious_access_flow_test
review_emergency_access_flow_test
create_incident_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 17 - Telemedicine

**Priority / Action:** Phase 2 unless assigned

## Purpose

Remote consultation workflow with consent, virtual waiting room, video/audio provider abstraction, notes, e-prescription and payment.

## Required Models / Tables

```text
Teleconsultation
TelemedicineConsent
VirtualWaitingRoom
CallSession
TelemedicineNote
```

## Required Services

```text
TelemedicineService
VirtualWaitingRoomService
CallProviderService
TelemedicineConsentService
```

## Required Controllers / Handlers

```text
TelemedicineController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
telemedicine.view
telemedicine.manage
```

## Required Audit Events

```text
telemedicine_created
telemedicine_updated
telemedicine_completed
```

## Flow Implementation Details

### Flow - Book teleconsultation

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Accept telemedicine consent

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Join waiting room

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Start call

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Write note

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Issue e-prescription

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Link payment

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Store call log

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Apply recording policy

1. Open the Telemedicine workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Care map results must be verified/freshness-aware and must not guarantee treatment or stock.

## Required Tests

```text
book_teleconsultation_flow_test
accept_telemedicine_consent_flow_test
join_waiting_room_flow_test
start_call_flow_test
write_note_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 18 - Ward, Admission and Bed Management

**Priority / Action:** Phase 2/3 unless pilot needs inpatient workflows

## Purpose

Manage inpatient admission, bed assignment, ward transfer, nursing rounds, medication administration and discharge.

## Required Models / Tables

```text
Admission
Ward
Bed
BedAssignment
WardTransfer
InpatientNote
NursingRound
DischargePlan
```

## Required Services

```text
AdmissionService
BedManagementService
WardTransferService
DischargePlanningService
```

## Required Controllers / Handlers

```text
Ward,AdmissionandBedManagementController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
ward,_admission_and_bed_management.view
ward,_admission_and_bed_management.manage
```

## Required Audit Events

```text
ward,_admission_and_bed_management_created
ward,_admission_and_bed_management_updated
ward,_admission_and_bed_management_completed
```

## Flow Implementation Details

### Flow - Admit patient

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Assign bed

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Transfer ward/bed

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Record inpatient note

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Complete nursing round

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Record medication administration

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Update bed occupancy

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Start discharge planning

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Generate discharge summary

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Release bed

1. Open the Ward, Admission and Bed Management workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Every sensitive action must be permission-checked, facility-scoped where applicable, validated, audited and tested.

## Required Tests

```text
admit_patient_flow_test
assign_bed_flow_test
transfer_ward/bed_flow_test
record_inpatient_note_flow_test
complete_nursing_round_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 19 - Clinical Decision Support and Clinical Alerts

**Priority / Action:** Build carefully as advisory

## Purpose

Advisory safety alerts for allergies, interactions, duplicate prescriptions, abnormal labs and preventive reminders.

## Required Models / Tables

```text
ClinicalRule
ClinicalAlert
DrugInteractionRule
AllergyAlertRule
DoseWarningRule
AlertOverride
```

## Required Services

```text
ClinicalDecisionSupportService
RuleEvaluationService
ClinicalAlertService
AlertOverrideService
```

## Required Controllers / Handlers

```text
ClinicalDecisionSupportandClinicalAlertsController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
clinical_decision_support_and_clinical_alerts.view
clinical_decision_support_and_clinical_alerts.manage
```

## Required Audit Events

```text
clinical_decision_support_and_clinical_alerts_created
clinical_decision_support_and_clinical_alerts_updated
clinical_decision_support_and_clinical_alerts_completed
```

## Flow Implementation Details

### Flow - Trigger allergy alert

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Trigger drug interaction alert

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Duplicate prescription warning

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Abnormal lab alert

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Critical lab alert

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Age-based dose warning

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Pregnancy warning

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Chronic disease reminder

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Vaccination reminder

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Override alert with reason

1. Open the Clinical Decision Support and Clinical Alerts workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Every sensitive action must be permission-checked, facility-scoped where applicable, validated, audited and tested.

## Required Tests

```text
trigger_allergy_alert_flow_test
trigger_drug_interaction_alert_flow_test
duplicate_prescription_warning_flow_test
abnormal_lab_alert_flow_test
critical_lab_alert_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 20 - Offline Mode and Sync

**Priority / Action:** Phase 2/3 high-security

## Purpose

Limited offline capture, encrypted local queue, sync retry, conflict resolution and audit sync for low-connectivity environments.

## Required Models / Tables

```text
OfflineQueue
SyncJob
SyncConflict
LocalCachePolicy
OfflineAuditEvent
```

## Required Services

```text
OfflineSyncService
ConflictResolutionService
OfflinePolicyService
SyncRetryService
```

## Required Controllers / Handlers

```text
OfflineModeandSyncController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
offline_mode_and_sync.view
offline_mode_and_sync.manage
```

## Required Audit Events

```text
offline_mode_and_sync_created
offline_mode_and_sync_updated
offline_mode_and_sync_completed
```

## Flow Implementation Details

### Flow - Offline data capture

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Local encrypted queue

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Sync retry

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Conflict detection

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Conflict resolution

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Duplicate prevention

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Offline consent limitation

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Offline audit sync

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Bridge Agent sync

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Sync failure alert

1. Open the Offline Mode and Sync workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Every sensitive action must be permission-checked, facility-scoped where applicable, validated, audited and tested.

## Required Tests

```text
offline_data_capture_flow_test
local_encrypted_queue_flow_test
sync_retry_flow_test
conflict_detection_flow_test
conflict_resolution_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 21 - Patient Mobile App API Readiness

**Priority / Action:** Complete API contract

## Purpose

API endpoints and security contract for a future Flutter patient app.

## Required Models / Tables

```text
MobileSession
PushDeviceToken
MobileConsentDevice
```

## Required Services

```text
MobileAuthService
PushNotificationService
MobileApiPolicyService
```

## Required Controllers / Handlers

```text
PatientMobileAppAPIReadinessController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
patient_mobile_app_api_readiness.view
patient_mobile_app_api_readiness.manage
```

## Required Audit Events

```text
patient_mobile_app_api_readiness_created
patient_mobile_app_api_readiness_updated
patient_mobile_app_api_readiness_completed
```

## Flow Implementation Details

### Flow - Health ID card

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - QR display

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Records

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Lab results

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Prescriptions

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Appointments

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Medicine finder

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Blood finder

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Care map

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Consent requests

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Messages

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Notifications

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Documents

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Share records

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Emergency profile

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Settings

1. Open the Patient Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Every sensitive action must be permission-checked, facility-scoped where applicable, validated, audited and tested.

## Required Tests

```text
health_id_card_flow_test
qr_display_flow_test
records_flow_test
lab_results_flow_test
prescriptions_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 22 - Provider Mobile App API Readiness

**Priority / Action:** Complete API contract

## Purpose

API endpoints and security contract for future provider mobile app.

## Required Models / Tables

```text
MobileSession
ProviderDevice
PushDeviceToken
FacilityContextSession
```

## Required Services

```text
ProviderMobileService
MobileFacilityContextService
MobileEmergencyAccessService
```

## Required Controllers / Handlers

```text
ProviderMobileAppAPIReadinessController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
provider_mobile_app_api_readiness.view
provider_mobile_app_api_readiness.manage
```

## Required Audit Events

```text
provider_mobile_app_api_readiness_created
provider_mobile_app_api_readiness_updated
provider_mobile_app_api_readiness_completed
```

## Flow Implementation Details

### Flow - Scan Health ID

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Scan document QR

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Patient lookup

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Assigned tasks

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Messages

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Critical alerts

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Prescription verification

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Lab result review

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Emergency access

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Offline-limited mode

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Facility context switching

1. Open the Provider Mobile App API Readiness workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Every sensitive action must be permission-checked, facility-scoped where applicable, validated, audited and tested.

## Required Tests

```text
scan_health_id_flow_test
scan_document_qr_flow_test
patient_lookup_flow_test
assigned_tasks_flow_test
messages_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Module 23 - Subscription and SaaS Billing

**Priority / Action:** Phase 2/3 unless business requires now

## Purpose

Organization subscription plans, trials, usage limits, module activation, API usage billing and SaaS invoices.

## Required Models / Tables

```text
SubscriptionPlan
OrganizationSubscription
SubscriptionInvoice
UsageMetric
PlanFeature
PlanLimit
```

## Required Services

```text
SubscriptionService
UsageBillingService
PlanLimitService
ModuleEntitlementService
```

## Required Controllers / Handlers

```text
SubscriptionandSaaSBillingController
```

## Required Routes / API

```text
Define REST/web routes according to existing Laravel routing conventions
```

## Required UI

```text
Dashboard/list view
Detail view
Create/edit forms
Status and history view
```

## Required Statuses

```text
draft
active
pending
completed
cancelled
failed
archived
```

## Required Permissions

```text
subscription_and_saas_billing.view
subscription_and_saas_billing.manage
```

## Required Audit Events

```text
subscription_and_saas_billing_created
subscription_and_saas_billing_updated
subscription_and_saas_billing_completed
```

## Flow Implementation Details

### Flow - Create plan

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Start trial

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Subscribe organization

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Trial expiry

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Upgrade plan

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Downgrade plan

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Cancel subscription

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Enforce usage limit

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Count API usage

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Activate modules by plan

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

### Flow - Generate subscription invoice

1. Open the Subscription and SaaS Billing workspace for this flow.
2. Validate actor role, facility context and permission.
3. Validate required input and related records.
4. Run service-layer business logic inside a transaction where needed.
5. Update status and related records.
6. Create audit event.
7. Trigger notification or downstream integration where required.
8. Return UI/API response and update timeline/dashboard.

## Edge Cases and Bugs to Prevent

```text
Wrong facility context
Unauthorized access
Missing related record
Duplicate action
Cancelled or archived record
Failed notification/integration
```

## Security / Privacy Rule

Every sensitive action must be permission-checked, facility-scoped where applicable, validated, audited and tested.

## Required Tests

```text
create_plan_flow_test
start_trial_flow_test
subscribe_organization_flow_test
trial_expiry_flow_test
upgrade_plan_flow_test
permission_denial_test
audit_event_test
```

## Acceptance Criteria

This module is complete only when every required flow has persistence, service logic, routes, UI/API, permissions, validation, audit events, bilingual labels where needed, and passing tests.

# Full End-to-End Implementation Scenarios

## Scenario 1 - Appointment to Completed Visit

1. Patient books appointment.
2. Appointment is confirmed.
3. Patient checks in at facility.
4. Queue ticket is generated.
5. Triage is completed if enabled.
6. Doctor opens visit workspace.
7. Consultation note is created.
8. Doctor orders lab or writes prescription.
9. Billable items generate invoice.
10. Payment is recorded.
11. Receipt is generated with verification QR.
12. Lab result or prescription document is generated.
13. Patient receives notification.
14. Visit is closed only after blockers are resolved.
15. Full audit trail is available.

## Scenario 2 - Walk-in Patient Flow

1. Patient arrives without appointment.
2. Reception searches Health ID or creates profile.
3. System creates visit and check-in.
4. Queue ticket is generated.
5. Patient goes through triage/consultation/billing as configured.
6. All steps appear on visit timeline.
7. Visit closes with receipt/documents.

## Scenario 3 - Insured Patient Flow

1. Patient policy is registered.
2. Eligibility is checked.
3. Appointment/visit begins.
4. Service requiring preauthorization triggers request.
5. Payer approves or rejects.
6. Invoice separates covered amount and patient responsibility.
7. Claim is submitted with minimum necessary documents.
8. Payer decision updates invoice.
9. Payment/receipt/reconciliation complete.

## Scenario 4 - Emergency Patient Flow

1. Patient arrives in emergency.
2. Emergency check-in is created.
3. Triage priority is assigned.
4. Emergency access reason is recorded if EMR access is needed.
5. Doctor/nurse alerts are sent.
6. Billing can be deferred based on facility policy.
7. Emergency access review task is created.
8. Visit timeline and audit trail are preserved.

## Scenario 5 - External System Pushes Result Into Visit

1. Partner system authenticates through scoped API.
2. It pushes lab result with patient Health ID and visit/encounter reference.
3. Idempotency key prevents duplicate import.
4. Consent/policy and facility context are checked.
5. Result links to patient timeline and visit.
6. Verification document is generated.
7. Notification is sent.
8. Failed match creates reconciliation case.

## Scenario 6 - Facility Go-Live

1. Facility is verified.
2. Admin account is created.
3. Departments/services are configured.
4. Staff roles and permissions are assigned.
5. Privacy training is completed.
6. Document templates are active.
7. Notification channels are tested.
8. Data import is completed or waived.
9. Support contact is defined.
10. Audit/security settings are verified.
11. Go-live approval is recorded.

---

# Universal Tests Required Before Completion

```text
php artisan test
php artisan route:list
php artisan migrate:fresh --seed --env=testing
npm run build where frontend exists
```

At minimum, there must be feature tests for:

```text
appointment booking
check-in and queue ticket
billing and payment receipt
insurance claim minimum data access
end-to-end visit completion
support ticket
import preview and rollback
go-live blocker
global search permission filtering
file upload access control
cashier financial audit
emergency access review
```

---

# Pull Request Requirements

Every PR must include:

```text
summary
modules touched
files added
files changed
migrations added
routes added
permissions added
audit events added
screenshots where UI exists
tests added
test results
known risks
next recommended tasks
```

---

# Launch Blockers

Do not pilot with real patient data if any of these are true:

```text
appointments cannot create check-ins
check-ins cannot create queue tickets
billing cannot produce audited receipts
insurance can view full EMR by default
public queue display exposes patient names
support agents can open patient records without audit
imports can overwrite records silently
Health ID QR exposes medical data
emergency access has no review
payments/refunds can be changed without audit
facility can go live without privacy training and roles
search leaks patient data across facility boundaries
```

---

# Final Agent Instruction

```text
You are implementing OpesCare operational modules and end-to-end flows. Audit first. Preserve working modules. Patch partial modules. Build missing modules. Do not duplicate. Do not use or copy OpesHIS OS. Every operational module must include models, migrations, services, routes, UI/API, permissions, audit logs, tests, and bilingual labels. Every end-to-end flow must pass feature tests. Do not mark a module complete without evidence.
```
