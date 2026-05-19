# OpesCare Missing & Incomplete Operational Modules — Complete Implementation Specification

**Project:** OpesCare  
**Company:** Opesware  
**Domain:** opescare.com  
**Document Type:** Implementation-ready Markdown specification  
**Purpose:** This document consolidates everything still lacking, partial, or not fully completed in OpesCare’s operational layer. It is designed for Claude Code, Jules, Gemini, Codex, or any engineering agent to implement the missing hospital/clinic operations modules without breaking the already-working core platform.  
**Primary Stack:** Laravel + PostgreSQL  
**Supporting Services:** Redis queues/cache, PostGIS where location search is needed, selected Python services only where justified  
**Build Strategy:** Upgrade what exists. Build what is missing. Patch partial modules. Do not duplicate working code.  
**Important Rule:** Do not use or copy OpesHIS OS code, database, UI, templates, routes, flows, or assumptions.  
**Status Meaning:** This document describes modules that are missing, partial, or not yet fully connected into working end-to-end flows.

---

# 1. Executive Summary

OpesCare already has a strong digital health foundation:

```text
Health ID / Medical ID
Patient profile / EMR foundation
Consent and privacy
Partner governance
API / SDK / Bridge Agent
Notifications and messaging
Verifiable documents
Pharmacy and blood availability foundations
Public health reporting
Certification / OpesCare Academy
Verified Care Access Map
Bilingual foundation
Audit and security foundation
Data quality / reconciliation foundation
```

What is still lacking is the **operational hospital/clinic layer** that allows a real facility to run day-to-day care:

```text
appointments
queue
billing
payments
insurance
patient visit flow
support
data migration
master admin
go-live readiness
staff/HR
triage
inventory
global search
file attachments
analytics
security operations hardening
telemedicine
ward/admission
clinical decision support
offline sync
mobile API readiness
subscription/SaaS billing
```

This document defines the implementation details for all of them.

---

# 2. Non-Negotiable Implementation Rules

Every agent must follow these rules.

```text
Do not rebuild working modules.
Do not create duplicate tables/models/controllers/services.
Do not break passing tests.
Do not expose patient data publicly.
Do not allow students to perform restricted clinical actions.
Do not allow insurance users to view full EMR by default.
Do not allow support users to access patient records without explicit permission and audit.
Do not allow QR codes to expose full medical data.
Do not allow payment/refund changes without audit.
Do not allow imports to overwrite records silently.
Do not allow offline mode to cache full EMR by default.
Do not let CDSS appear to replace clinical judgment.
Do not make care map, medicine, blood, emergency, or insurance availability sound guaranteed.
```

Before implementing a module:

```text
1. Search the repository for existing implementation.
2. Reuse and extend existing models/services/routes where appropriate.
3. If implementation exists but is partial, patch the missing flows.
4. If nothing exists, build cleanly.
5. Add tests.
6. Update docs.
7. Run test suite.
```

---

# 3. Universal Completion Standard

A module is complete only when every required flow has:

```text
models
migrations or existing schema mapping
services/business logic
controllers/API handlers
routes
UI pages or API contract
permissions/policies
request validation
audit events
notifications where needed
bilingual labels where user-facing
demo/seed data where useful
feature tests
unit tests where useful
documentation update
```

If any flow is missing any of these, the module is **PARTIAL**, not complete.

---

# 4. Implementation Priority

## 4.1 Immediate Operational Core

Build these first:

```text
1. Appointments & Booking
2. Queue & Patient Flow
3. Billing, Payments & Wallet
4. Insurance Claims & Preauthorization
5. End-to-End Patient Visit Flow
```

## 4.2 Facility Readiness Layer

Build next:

```text
6. Support / Helpdesk / Incident Management
7. Data Import / Migration
8. Master Admin Control Center
9. Facility Go-Live Readiness
10. Global Search
```

## 4.3 Operational Maturity Layer

Build after the operational core:

```text
11. Staff / HR / Shift Management
12. Triage & Emergency Workflow
13. Inventory & Supply Chain
14. File Storage & Medical Attachments
15. Analytics & Reporting
16. Audit, Compliance & Security Operations Hardening
```

## 4.4 Later-Phase Advanced Modules

Build once the core is stable:

```text
17. Telemedicine
18. Ward / Admission / Bed Management
19. Clinical Decision Support / Clinical Alerts
20. Offline Mode & Sync
21. Patient Mobile App API Readiness
22. Provider Mobile App API Readiness
23. Subscription / SaaS Billing
```

---

# 5. Module 01 — Appointments & Booking

## 5.1 Purpose

Appointments allow patients, reception teams, doctors, clinics, hospitals, and telemedicine providers to schedule care. This is the first operational entry point into the hospital workflow.

The module must connect to:

```text
patient profile
Health ID
provider availability
facility schedule
notifications
queue
encounters
billing
telemedicine
audit logs
```

## 5.2 Required Models / Tables

```text
Appointment
AppointmentType
AppointmentSlot
ProviderAvailability
FacilitySchedule
AppointmentReminder
AppointmentCheckIn
AppointmentCancellation
AppointmentStatusHistory
AppointmentAudit
```

## 5.3 Required Services

```text
AppointmentService
AvailabilityService
AppointmentReminderService
AppointmentCheckInService
AppointmentCancellationService
AppointmentPolicyService
AppointmentBillingLinkService
```

## 5.4 Required Controllers / Routes

```text
GET    /appointments
GET    /appointments/create
POST   /appointments
GET    /appointments/{appointment}
PATCH  /appointments/{appointment}
POST   /appointments/{appointment}/confirm
POST   /appointments/{appointment}/reschedule
POST   /appointments/{appointment}/cancel
POST   /appointments/{appointment}/check-in
POST   /appointments/{appointment}/mark-no-show

GET    /api/v1/appointments
POST   /api/v1/appointments
GET    /api/v1/appointments/{id}
PATCH  /api/v1/appointments/{id}
POST   /api/v1/appointments/{id}/confirm
POST   /api/v1/appointments/{id}/reschedule
POST   /api/v1/appointments/{id}/cancel
POST   /api/v1/appointments/{id}/check-in
```

## 5.5 Required Statuses

```text
requested
scheduled
confirmed
checked_in
in_queue
in_progress
completed
cancelled
rescheduled
no_show
expired
entered_in_error
```

## 5.6 Required Permissions

```text
appointments.view_own
appointments.book_own
appointments.view_facility
appointments.create_for_patient
appointments.update
appointments.confirm
appointments.reschedule
appointments.cancel
appointments.check_in
appointments.mark_no_show
appointments.manage_provider_availability
```

## 5.7 Required UI

```text
patient appointment booking page
patient appointment list
facility appointment scheduler
provider calendar
reception appointment dashboard
appointment detail page
reschedule modal
cancellation modal
check-in action
no-show management view
```

## 5.8 Flow — Patient Books Appointment

1. Patient opens booking page.
2. Patient selects facility, department, service, appointment type, date, and time.
3. System allows provider selection if provider-level booking is enabled.
4. System checks provider availability, facility schedule, slot capacity, and appointment type rules.
5. System checks whether the patient profile and Health ID exist.
6. If the patient is not logged in, system allows pre-registration if facility policy allows.
7. System creates appointment as `requested`, `scheduled`, or `confirmed` depending facility rule.
8. System sends notification to patient.
9. System sends notification to provider/facility if approval is required.
10. System creates audit event `appointment_created`.
11. Appointment appears in patient dashboard and facility calendar.

## 5.9 Flow — Staff Books Appointment for Patient

1. Receptionist searches patient by Health ID, phone, name, or patient number.
2. If patient does not exist, receptionist creates patient profile with minimum required data.
3. Receptionist selects facility, department, provider/service, date, and time.
4. System checks availability and booking conflicts.
5. Receptionist confirms appointment.
6. System sends SMS/WhatsApp/email/push notification to patient according to preferences.
7. System logs actor, patient, facility, provider, and time slot.
8. Appointment appears in staff scheduler.

## 5.10 Flow — Provider Availability Setup

1. Provider or facility admin opens availability settings.
2. User selects days, time blocks, break times, appointment duration, service types, and maximum bookings.
3. User sets location: physical, telemedicine, or hybrid.
4. System prevents overlapping availability rules.
5. System stores provider availability.
6. System publishes available slots.
7. System logs `provider_availability_updated`.

## 5.11 Flow — Facility Schedule Setup

1. Facility admin opens facility schedule.
2. Admin defines opening days, holiday closures, emergency availability, department hours, and special service hours.
3. System validates no conflicting schedule settings.
4. System applies schedule to appointment availability.
5. System logs `facility_schedule_updated`.

## 5.12 Flow — Appointment Confirmation

1. Appointment is created in requested or scheduled state.
2. Patient or staff confirms appointment depending workflow.
3. System validates slot is still available.
4. Appointment status changes to `confirmed`.
5. Confirmation notification is sent.
6. Audit event `appointment_confirmed` is created.

## 5.13 Flow — Appointment Reschedule

1. Authorized user opens appointment.
2. User selects new date/time.
3. System checks appointment status permits rescheduling.
4. System checks new slot availability.
5. System releases old slot and reserves new slot atomically.
6. System records old and new values.
7. System sends reschedule notification to affected parties.
8. Audit event `appointment_rescheduled` is created.

## 5.14 Flow — Appointment Cancellation

1. Patient, staff, or provider opens appointment.
2. System checks cancellation permission and cancellation window.
3. If staff/provider cancels, reason is required.
4. System changes status to `cancelled`.
5. System releases appointment slot.
6. System triggers deposit/refund/billing rule if applicable.
7. System sends notification.
8. Audit event `appointment_cancelled` is created.

## 5.15 Flow — Appointment Reminder

1. Scheduled job checks upcoming appointments.
2. System sends reminders based on configured times: 24 hours, 3 hours, 1 hour, or custom.
3. Reminder channel respects user preferences.
4. Failed delivery is logged and retried where appropriate.
5. Audit or delivery event is stored.

## 5.16 Flow — Appointment Check-In

1. Patient arrives at facility or checks in through mobile if enabled.
2. Reception verifies patient identity.
3. System changes appointment status to `checked_in`.
4. System creates `AppointmentCheckIn`.
5. System creates or links to `Visit`.
6. System creates queue ticket if queue module is enabled.
7. System logs `appointment_checked_in`.

## 5.17 Flow — No-Show

1. Scheduled job finds appointment past grace period.
2. Staff confirms no-show or system marks automatically based policy.
3. Appointment status changes to `no_show`.
4. Patient/provider are notified where policy allows.
5. Analytics update no-show statistics.
6. Audit event `appointment_no_show_marked` is created.

## 5.18 Required Notifications

```text
appointment_created
appointment_confirmed
appointment_rescheduled
appointment_cancelled
appointment_reminder
appointment_checked_in
appointment_no_show
```

## 5.19 Required Tests

```text
patient can book appointment
staff can book appointment for patient
provider availability prevents double booking
facility closure prevents booking
patient can reschedule appointment
staff cancellation requires reason
appointment reminder job creates notification
check-in creates visit and queue ticket
no-show job works
patient cannot see other patient appointments
provider sees only assigned/facility appointments
```

## 5.20 Acceptance Criteria

This module is complete when:

```text
appointment booking works for patient and staff
availability rules prevent conflicts
appointment statuses transition correctly
check-in links to visit/queue
reminders are sent
permissions are enforced
audit events are created
tests pass
```

---

# 6. Module 02 — Queue & Patient Flow

## 6.1 Purpose

Queue and patient flow tracks the patient’s movement inside a facility from check-in to triage, consultation, lab, billing, pharmacy, discharge, or referral.

This module must connect to:

```text
appointments
visits
triage
consultation
lab
billing
pharmacy
notifications
public queue display
audit logs
```

## 6.2 Required Models / Tables

```text
PatientCheckIn
Queue
QueueStation
QueueTicket
QueueTransfer
PatientFlowEvent
QueuePriorityRule
QueueDisplaySetting
QueueStatusHistory
```

## 6.3 Required Services

```text
QueueService
QueueTicketService
PatientFlowService
QueuePriorityService
QueueDisplayService
QueueTransferService
```

## 6.4 Required Controllers / Routes

```text
GET    /queues
GET    /queues/{queue}
POST   /queues/{queue}/tickets
POST   /queue-tickets/{ticket}/call
POST   /queue-tickets/{ticket}/start-service
POST   /queue-tickets/{ticket}/transfer
POST   /queue-tickets/{ticket}/complete
POST   /queue-tickets/{ticket}/skip
POST   /queue-tickets/{ticket}/cancel
GET    /queue-display/{facility}/{station?}

GET    /api/v1/queues
POST   /api/v1/queue-tickets
POST   /api/v1/queue-tickets/{id}/call
POST   /api/v1/queue-tickets/{id}/transfer
POST   /api/v1/queue-tickets/{id}/complete
```

## 6.5 Required Statuses

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
entered_in_error
```

## 6.6 Required Permissions

```text
queue.view
queue.manage
queue.call_patient
queue.transfer_patient
queue.complete_service
queue.priority_override
queue.view_public_display
```

## 6.7 Required UI

```text
reception check-in screen
staff queue board
station-specific queue board
triage queue
consultation queue
lab queue
billing queue
pharmacy queue
discharge queue
public masked queue display
patient flow timeline
queue analytics panel
```

## 6.8 Flow — Patient Arrival and Check-In

1. Patient arrives with appointment, walk-in request, referral, or emergency complaint.
2. Reception searches patient by Health ID, phone, name, or appointment number.
3. Reception confirms facility context.
4. Reception creates check-in.
5. System creates visit shell if not already created.
6. System determines first queue station based on visit type.
7. System creates queue ticket.
8. Patient receives queue number.
9. Audit event `patient_checked_in` is created.

## 6.9 Flow — Queue Ticket Generation

1. System selects queue station: triage, consultation, lab, billing, pharmacy, discharge, or custom.
2. Ticket number is generated unique per facility/day/station.
3. Priority is assigned from appointment, triage, emergency, age, disability, or staff override.
4. Ticket status becomes `waiting`.
5. Ticket appears on staff queue board.
6. Patient flow event is recorded.

## 6.10 Flow — Call Patient to Station

1. Staff opens station queue.
2. Staff clicks “Call Next” or selects a specific ticket.
3. System updates ticket status to `called`.
4. Public display shows queue number only or masked patient reference.
5. Optional audio notification plays without full patient medical information.
6. Audit event `queue_ticket_called` is created.

## 6.11 Flow — Start Service

1. Patient arrives at station.
2. Staff clicks “Start Service.”
3. System changes ticket status to `in_service`.
4. Patient flow event records station, staff, start time.
5. Visit timeline updates.

## 6.12 Flow — Transfer Patient

1. Staff completes current action and selects next station.
2. Staff chooses transfer reason.
3. System closes current ticket or marks transferred.
4. System creates new queue ticket in destination station.
5. Patient flow event records transfer.
6. Destination station sees waiting ticket.
7. Audit event `queue_ticket_transferred` is created.

## 6.13 Flow — Emergency Priority Bypass

1. Nurse/authorized staff selects emergency priority.
2. System requires reason.
3. System updates ticket priority.
4. Emergency/doctor alert is created.
5. Ticket moves according to emergency rule.
6. Audit event `queue_priority_override` is created.

## 6.14 Flow — Public Queue Display

1. Public queue display fetches called/waiting tickets.
2. System masks patient identity.
3. Display shows ticket number, station, status.
4. No diagnosis, Health ID, full name, phone, or clinical detail is shown.

## 6.15 Flow — Complete Queue

1. Staff completes station service.
2. System checks next required step.
3. If next step exists, transfer flow begins.
4. If no next step, visit can proceed to discharge/close.
5. Patient flow event records completion.

## 6.16 Required Tests

```text
check-in creates queue ticket
queue ticket number unique per facility/station/day
public display masks identity
staff can call ticket
transfer creates new station ticket
emergency priority requires reason
facility boundary enforced
queue completion updates visit timeline
```

## 6.17 Acceptance Criteria

This module is complete when:

```text
check-in to queue works
all station transfers work
public display is privacy-safe
priority/emergency bypass works
queue links to visit
permissions and facility boundaries work
tests pass
```

---

# 7. Module 03 — Billing, Payments & Wallet

## 7.1 Purpose

Billing, Payments & Wallet creates the financial engine for healthcare services. It handles invoices, service fees, lab fees, pharmacy items, insurance coverage, patient responsibility, payments, receipts, refunds, wallet/prepayments, cashier reports, and reconciliation.

This module must connect to:

```text
appointments
visits
encounters
lab
pharmacy
insurance
verifiable documents
notifications
cashier dashboard
audit logs
```

## 7.2 Required Models / Tables

```text
BillingAccount
PriceList
PriceListItem
Invoice
InvoiceItem
InvoiceAdjustment
Payment
PaymentMethod
Receipt
Refund
Wallet
WalletTransaction
CashierSession
PaymentReconciliation
FinancialAudit
```

## 7.3 Required Services

```text
BillingService
InvoiceService
PaymentService
ReceiptService
RefundService
WalletService
PriceListService
ReconciliationService
CashierReportService
InsuranceBillingService
```

## 7.4 Required Controllers / Routes

```text
GET    /billing
GET    /billing/invoices
POST   /billing/invoices
GET    /billing/invoices/{invoice}
POST   /billing/invoices/{invoice}/issue
POST   /billing/invoices/{invoice}/items
POST   /billing/invoices/{invoice}/adjust
POST   /billing/invoices/{invoice}/payments
POST   /billing/payments/{payment}/refund
GET    /billing/receipts/{receipt}
GET    /billing/cashier-report

GET    /api/v1/billing/invoices
POST   /api/v1/billing/invoices
POST   /api/v1/billing/invoices/{id}/payments
POST   /api/v1/billing/payments/{id}/refund
```

## 7.5 Required Statuses

Invoice statuses:

```text
draft
issued
partially_paid
paid
cancelled
voided
refunded
partially_refunded
overdue
entered_in_error
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

Wallet transaction statuses:

```text
pending
posted
reversed
failed
```

## 7.6 Required Permissions

```text
billing.view
billing.create_invoice
billing.issue_invoice
billing.adjust_invoice
billing.record_payment
billing.refund_payment
billing.view_reports
billing.manage_price_list
wallet.view
wallet.manage
```

## 7.7 Required UI

```text
cashier dashboard
invoice creation page
invoice detail page
payment recording modal
receipt view/download
refund modal
patient bills page
wallet dashboard
price list management
financial report page
reconciliation dashboard
```

## 7.8 Flow — Create Invoice

1. Staff opens patient visit or billing dashboard.
2. Staff selects patient, facility, visit, appointment, or encounter.
3. System loads patient and insurance context.
4. Staff adds billable items from price list.
5. System calculates subtotal, discount, insurance coverage, patient responsibility, and balance.
6. Invoice starts as `draft`.
7. Staff issues invoice when ready.
8. Audit event `invoice_created` or `invoice_issued` is created.

## 7.9 Flow — Add Service Item

1. Staff selects item from facility price list.
2. System validates item is active and allowed for facility.
3. Quantity and price are calculated.
4. If item is linked to lab/pharmacy/service event, source reference is stored.
5. Invoice total recalculates.
6. Audit event `invoice_item_added` is created.

## 7.10 Flow — Apply Insurance Coverage

1. System checks patient insurance policy.
2. System verifies eligibility status.
3. System applies coverage rules.
4. System calculates insurer responsibility and patient responsibility.
5. Items requiring preauthorization are flagged.
6. Claim draft is created or updated.
7. Invoice shows coverage as estimated until insurer confirms.

## 7.11 Flow — Issue Invoice

1. Staff reviews draft invoice.
2. System validates at least one item exists.
3. System locks invoice fields except through adjustment process.
4. Invoice status changes to `issued`.
5. Patient notification is sent if configured.
6. Audit event `invoice_issued` is created.

## 7.12 Flow — Record Cash Payment

1. Cashier opens issued invoice.
2. Cashier selects cash payment.
3. System validates amount is positive and not above allowed overpayment rule.
4. Payment is recorded as successful.
5. Invoice balance updates.
6. Receipt is generated.
7. Cashier session records cash movement.
8. Audit event `payment_recorded` is created.

## 7.13 Flow — Record Mobile Money/Card/Bank Payment

1. Cashier or patient selects payment method.
2. System creates pending payment.
3. External payment provider confirms success/failure if integrated.
4. On success, payment status becomes successful.
5. Invoice balance updates.
6. Receipt is generated.
7. Audit event is created.
8. Failed payment remains failed or pending retry.

## 7.14 Flow — Generate Receipt

1. Successful payment triggers receipt generation.
2. Receipt gets unique receipt number.
3. Receipt includes payment amount, method, invoice reference, patient/facility, cashier, date/time.
4. Receipt becomes official document with QR verification.
5. Patient can download receipt.
6. Public verification shows safe metadata only.

## 7.15 Flow — Refund Payment

1. Authorized user selects original payment.
2. System validates payment is refundable.
3. User enters refund amount and reason.
4. System creates refund record.
5. Payment status updates to refunded or partially_refunded.
6. Invoice balance updates.
7. Refund receipt is generated.
8. Audit event `refund_processed` is created.

## 7.16 Flow — Wallet Deposit

1. Patient or cashier initiates deposit.
2. System records wallet transaction as pending.
3. Payment succeeds.
4. Wallet balance increases.
5. Wallet transaction is posted.
6. Receipt is generated.
7. Audit event is created.

## 7.17 Flow — Wallet Payment

1. Patient/cashier selects wallet as payment method.
2. System checks wallet balance.
3. System debits wallet atomically.
4. Invoice balance updates.
5. Receipt is generated.
6. Audit event is created.

## 7.18 Flow — Cashier Closeout

1. Cashier opens shift closeout.
2. System calculates cash, mobile, card, wallet totals.
3. Cashier enters counted cash.
4. System flags difference.
5. Supervisor review may be required.
6. Cashier report is generated.
7. Audit event is created.

## 7.19 Required Tests

```text
invoice can be created
invoice cannot be issued without items
invoice item calculation correct
insurance coverage calculates patient responsibility
cash payment creates receipt
mobile money pending/success flow works
refund requires reason
refund cannot exceed payment
wallet deposit increases balance
wallet payment decreases balance
patient cannot see other patient invoice
cashier cannot edit paid invoice without adjustment permission
financial actions audited
```

## 7.20 Acceptance Criteria

This module is complete when:

```text
invoices work
payments work
receipts work
refunds work
wallet works
insurance responsibility is supported
cashier reports work
audit logs exist
financial privacy is enforced
tests pass
```

---

# 8. Module 04 — Insurance Claims & Preauthorization

## 8.1 Purpose

Insurance Claims & Preauthorization manages patient insurance policies, eligibility checks, preauthorization requests, claims, payer review, supporting documents, approvals, rejections, claim payments, and minimum necessary data sharing.

This module must connect to:

```text
patient profile
billing
invoices
documents
insurance companies
claims dashboard
notifications
audit logs
```

## 8.2 Required Models / Tables

```text
InsuranceProvider
InsurancePlan
PatientInsurancePolicy
EligibilityCheck
PreauthorizationRequest
PreauthorizationDecision
InsuranceClaim
ClaimItem
ClaimDocument
ClaimDecision
ClaimPayment
ClaimMessage
```

## 8.3 Required Services

```text
InsuranceEligibilityService
PreauthorizationService
ClaimService
ClaimDocumentService
ClaimDecisionService
ClaimPaymentService
InsuranceAccessPolicyService
```

## 8.4 Required Statuses

Eligibility statuses:

```text
pending
eligible
not_eligible
unknown
expired
failed
```

Preauthorization statuses:

```text
draft
submitted
under_review
approved
rejected
more_information_required
expired
cancelled
```

Claim statuses:

```text
draft
submitted
under_review
more_information_required
approved
partially_approved
rejected
paid
partially_paid
cancelled
disputed
```

## 8.5 Required Permissions

```text
insurance.policies.manage
insurance.eligibility.check
insurance.preauthorization.create
insurance.preauthorization.review
insurance.claims.create
insurance.claims.submit
insurance.claims.review
insurance.claims.decide
insurance.claims.pay
insurance.claims.view_minimum_data
```

## 8.6 Required UI

```text
patient insurance policy page
eligibility check screen
preauthorization request page
claim creation page
facility claims dashboard
payer claims dashboard
claim detail page
claim document viewer
missing information workflow
claim payment posting page
```

## 8.7 Flow — Register Patient Insurance Policy

1. Patient or staff opens insurance section.
2. User selects insurance provider and plan.
3. User enters policy/member number.
4. System validates required fields.
5. Policy is saved as pending or active depending verification.
6. Audit event `patient_insurance_policy_created` is created.

## 8.8 Flow — Eligibility Check

1. Staff opens patient billing/insurance page.
2. Staff selects policy.
3. System checks eligibility through manual entry or API if available.
4. Eligibility result is stored with timestamp and source.
5. Result appears in billing and claim workflows.
6. Audit event `eligibility_checked` is created.

## 8.9 Flow — Preauthorization Request

1. Provider or billing staff creates request.
2. User selects service/procedure/medication/lab item requiring authorization.
3. User attaches minimum necessary supporting documents.
4. System submits request to payer dashboard/API.
5. Status becomes `submitted`.
6. Payer receives notification.
7. Audit event `preauthorization_submitted` is created.

## 8.10 Flow — Preauthorization Decision

1. Payer opens request.
2. Payer reviews minimum necessary data.
3. Payer approves, rejects, or requests more information.
4. Decision reason is required.
5. Facility and patient are notified where appropriate.
6. Related invoice/claim updates.
7. Audit event is created.

## 8.11 Flow — Claim Creation

1. Invoice is issued or visit completed.
2. System creates claim draft from invoice items and insurance coverage.
3. Staff reviews claim.
4. Staff attaches supporting documents.
5. Claim remains draft until submitted.
6. Audit event `claim_created` is created.

## 8.12 Flow — Claim Submission

1. Staff validates claim.
2. System checks patient policy, invoice, required documents, and minimum necessary data.
3. Claim status becomes submitted.
4. Payer receives claim.
5. Patient may receive claim status notification.
6. Audit event `claim_submitted` is created.

## 8.13 Flow — Payer Review

1. Insurance user opens payer dashboard.
2. System shows only allowed claim data.
3. User reviews items and supporting documents.
4. User requests missing information, approves, partially approves, rejects, or flags dispute.
5. Every document view is audited.

## 8.14 Flow — Missing Information Request

1. Payer requests additional information.
2. Request specifies missing item.
3. Facility receives task/notification.
4. Facility uploads or links allowed documents.
5. Claim returns to review.
6. Audit logs each step.

## 8.15 Flow — Claim Approval/Rejection

1. Payer enters decision.
2. Decision reason is required.
3. Approved amount is recorded.
4. Claim status updates.
5. Invoice/insurance balance updates.
6. Facility and patient are notified where appropriate.
7. Audit event is created.

## 8.16 Flow — Claim Payment Posting

1. Payer or facility records claim payment.
2. System validates claim status allows payment.
3. Payment amount is matched to approved amount.
4. Invoice/receivable is updated.
5. Claim payment receipt or posting record is generated.
6. Audit event is created.

## 8.17 Minimum Necessary Data Rule

Insurance users must not see full EMR by default.

They may see only:

```text
claim items
invoice references
preauthorization references
limited supporting documents
medical necessity summary where required
document metadata
```

They must not see:

```text
full patient timeline
unrelated diagnoses
unrelated lab results
private messages
unrelated prescriptions
full record exports
```

## 8.18 Required Tests

```text
policy can be registered
eligibility check stored
preauthorization requires supporting reason
payer sees minimum necessary data only
claim created from invoice
claim submitted
missing information request works
claim approved/rejected with reason
claim payment updates invoice balance
claim document access audited
patient cannot view other patient claim
```

## 8.19 Acceptance Criteria

This module is complete when:

```text
policy, eligibility, preauthorization, claims, payer review, decisions, and payments work
minimum necessary access is enforced
claims connect to billing
documents are audited
tests pass
```

---

# 9. Module 05 — End-to-End Patient Visit Flow

## 9.1 Purpose

The End-to-End Patient Visit Flow connects the separate modules into one real patient journey. This is the most important operational integration.

The system must prove the complete flow:

```text
appointment -> check-in -> queue -> triage -> consultation -> lab/prescription -> billing -> payment -> receipt -> document -> notification -> visit completion -> audit
```

## 9.2 Required Models / Tables

```text
Visit
VisitStep
VisitTimeline
Encounter
Appointment
PatientCheckIn
QueueTicket
TriageAssessment
Invoice
Payment
Receipt
OfficialDocument
VisitClosure
```

## 9.3 Required Services

```text
VisitService
VisitTimelineService
VisitStepService
VisitClosureService
VisitBillingIntegrationService
VisitDocumentIntegrationService
VisitAuditService
```

## 9.4 Required Statuses

Visit statuses:

```text
scheduled
checked_in
in_queue
in_triage
in_consultation
awaiting_lab
awaiting_billing
awaiting_pharmacy
awaiting_discharge
completed
cancelled
abandoned
entered_in_error
```

Visit step statuses:

```text
pending
in_progress
completed
skipped
cancelled
blocked
```

## 9.5 Required UI

```text
unified visit workspace
visit timeline
patient current station card
pending actions panel
billing status panel
document status panel
visit closure checklist
provider visit view
reception visit view
cashier visit view
```

## 9.6 Flow — Appointment to Check-In

1. Patient books or has appointment.
2. Patient arrives.
3. Reception checks in patient.
4. System creates or updates visit.
5. Appointment status becomes `checked_in`.
6. Queue ticket is created.
7. Visit timeline records check-in.

## 9.7 Flow — Check-In to Queue

1. Check-in determines first station.
2. System creates queue ticket.
3. Patient appears in queue board.
4. Queue status changes through waiting/called/in_service.
5. Visit timeline records queue movement.

## 9.8 Flow — Queue to Triage

1. Triage station calls patient.
2. Nurse records chief complaint and vitals.
3. Triage priority is assigned.
4. If emergency, priority bypass and alerts are triggered.
5. Visit status changes to `in_triage` then next station.
6. Visit timeline records triage.

## 9.9 Flow — Triage to Consultation

1. Patient transfers to consultation queue.
2. Doctor opens visit workspace.
3. Doctor records consultation note.
4. Doctor can order lab, issue prescription, refer, or discharge.
5. Visit timeline records clinical actions.
6. Consultation note is linked to encounter.

## 9.10 Flow — Consultation to Lab

1. Doctor orders lab test.
2. Lab request document is generated or linked.
3. Patient is sent to billing if prepayment required or directly to lab if facility policy allows.
4. Lab receives order.
5. Sample collection and result workflow begin.
6. Visit timeline updates.

## 9.11 Flow — Consultation to Prescription

1. Doctor creates prescription.
2. Prescription is validated.
3. Verifiable prescription document is generated.
4. Patient can go to pharmacy.
5. Pharmacy verifies QR and dispenses.
6. Visit timeline updates.

## 9.12 Flow — Billing and Payment

1. Billable items from consultation, lab, pharmacy, or service are added to invoice.
2. Cashier issues invoice.
3. Patient or insurer pays.
4. Receipt is generated.
5. Visit billing status updates.
6. Visit timeline records payment.

## 9.13 Flow — Lab Result and Notification

1. Lab result is entered and validated.
2. Critical result alert is triggered where needed.
3. Lab result report is generated.
4. Patient/provider notification is sent.
5. Visit timeline records result release.

## 9.14 Flow — Visit Closure

1. System checks closure requirements:
   - consultation completed
   - billing settled or allowed pending
   - required documents generated
   - prescriptions/labs/referrals handled
   - discharge instructions if needed
2. Authorized user closes visit.
3. Visit status becomes `completed`.
4. Visit summary is generated.
5. Audit event `visit_closed` is created.

## 9.15 Blocking Rules

Visit cannot close if:

```text
required consultation note missing
required payment pending and facility policy blocks closure
critical lab alert unacknowledged
discharge document required but missing
patient still active in queue
required referral/discharge instruction missing
```

## 9.16 Required Tests

```text
appointment check-in creates visit
queue ticket links to visit
triage updates visit status
consultation creates encounter
lab order links to visit
prescription links to visit
invoice links to visit
payment updates visit billing status
receipt generated
visit cannot close with blockers
visit closes after blockers resolved
full end-to-end feature test passes
```

## 9.17 Acceptance Criteria

This module is complete when:

```text
the full patient journey works from appointment to visit completion
all modules exchange data correctly
timeline is complete
blockers prevent unsafe closure
audit events exist
tests pass
```

---

# 10. Module 06 — Support, Helpdesk & Incident Management

## 10.1 Purpose

Support and Helpdesk gives patients, facilities, developers, partners, and internal OpesCare staff a structured way to report problems, request help, escalate incidents, track SLAs, and manage knowledge base content.

## 10.2 Required Models / Tables

```text
SupportTicket
TicketMessage
TicketAssignment
TicketStatusHistory
SupportCategory
IncidentReport
IncidentEscalation
SlaPolicy
KnowledgeBaseArticle
SupportAttachment
```

## 10.3 Required Services

```text
SupportTicketService
TicketAssignmentService
IncidentService
SlaTrackingService
KnowledgeBaseService
SupportAccessPolicyService
```

## 10.4 Required Statuses

Ticket statuses:

```text
new
open
assigned
waiting_for_user
waiting_for_internal_team
escalated
resolved
closed
reopened
cancelled
```

Incident statuses:

```text
reported
triaged
investigating
mitigated
resolved
closed
```

## 10.5 Required Permissions

```text
support.create_ticket
support.view_own_ticket
support.view_facility_tickets
support.assign_ticket
support.reply_ticket
support.escalate_ticket
support.close_ticket
support.manage_kb
support.manage_incidents
```

## 10.6 Required UI

```text
patient support page
facility support portal
developer support page
support agent dashboard
ticket detail page
incident dashboard
SLA dashboard
knowledge base public page
knowledge base admin editor
```

## 10.7 Flow — Patient Creates Ticket

1. Patient opens support page.
2. Patient selects issue category.
3. Patient enters description.
4. Patient may attach screenshot/document if allowed.
5. System creates ticket.
6. Patient receives confirmation.
7. Support queue receives ticket.
8. Audit event `support_ticket_created` is created.

## 10.8 Flow — Facility Creates Ticket

1. Facility user opens support portal.
2. User selects facility context.
3. User selects issue type: technical, billing, integration, data, training, urgent.
4. System creates ticket linked to facility.
5. Ticket is routed to appropriate support queue.
6. Audit event is created.

## 10.9 Flow — Developer Creates API Support Ticket

1. Developer opens developer portal.
2. Developer selects API client/app.
3. Developer selects issue type.
4. System includes request ID/webhook ID if provided.
5. Ticket routes to integration support.
6. Audit event is created.

## 10.10 Flow — Ticket Assignment

1. Support lead opens unassigned queue.
2. Lead assigns ticket to support agent/team.
3. System records assignment.
4. Agent is notified.
5. SLA timer starts or continues.

## 10.11 Flow — Ticket Escalation

1. Agent determines ticket requires escalation.
2. Agent selects escalation level and reason.
3. System routes to senior support/security/engineering/privacy team.
4. Incident may be created if serious.
5. Audit event is created.

## 10.12 Flow — Incident Creation

1. Ticket or admin action creates incident.
2. Incident category is selected: privacy, security, data integrity, service outage, integration failure, payment issue.
3. Severity is assigned.
4. Response owner is assigned.
5. Notifications are sent.
6. Incident timeline begins.

## 10.13 Flow — Ticket Resolution

1. Agent proposes resolution.
2. User confirms or system auto-closes after policy window.
3. Ticket status changes to resolved/closed.
4. Resolution summary is stored.
5. SLA metrics are updated.
6. Audit event is created.

## 10.14 Support Access Rule

Support users must not automatically access patient records.

If ticket requires patient data:

```text
explicit permission is required
minimum necessary access applies
access reason is recorded
access is audited
patient/facility context is enforced
```

## 10.15 Required Tests

```text
patient can create ticket
facility can create ticket
developer can create ticket
ticket assignment works
ticket escalation works
incident creation works
SLA timer works
support user cannot access patient record without permission
ticket attachments require permission
ticket closure works
knowledge base page visible
```

## 10.16 Acceptance Criteria

This module is complete when:

```text
support tickets work
incident escalation works
SLA tracking works
knowledge base works
support access is privacy-safe
tests pass
```

---

# 11. Module 07 — Data Import & Migration

## 11.1 Purpose

Data Import & Migration allows facilities to onboard existing data from spreadsheets, legacy systems, pharmacy systems, lab catalogs, insurance networks, and facility directories.

## 11.2 Required Models / Tables

```text
ImportJob
ImportBatch
ImportFile
ImportRow
ImportRowError
ImportMapping
ImportPreview
ImportRollback
ImportDuplicateCandidate
ImportAudit
```

## 11.3 Required Services

```text
ImportService
ImportValidationService
ImportMappingService
ImportPreviewService
ImportDuplicateDetectionService
ImportRollbackService
ImportAuditService
```

## 11.4 Required Supported Imports

```text
patients
facilities
staff
appointments
medicine catalog
pharmacy stock
lab test catalog
insurance providers
insurance network facilities
price lists
inventory items
```

## 11.5 Required Statuses

```text
uploaded
mapping_required
preview_ready
validated
validation_failed
approved_for_import
importing
completed
completed_with_errors
failed
rolled_back
cancelled
```

## 11.6 Required UI

```text
import wizard
file upload page
mapping screen
preview screen
validation error screen
duplicate review screen
import progress screen
rollback screen
import history page
```

## 11.7 Flow — Upload Import File

1. User selects import type.
2. User uploads CSV/Excel file.
3. System validates file type and size.
4. File is stored privately.
5. Import job is created.
6. System reads headers.
7. Status becomes `mapping_required` or `preview_ready`.

## 11.8 Flow — Field Mapping

1. User maps file columns to OpesCare fields.
2. Required fields are highlighted.
3. System suggests mappings based on column names.
4. User confirms mapping.
5. Mapping is saved for reuse if desired.

## 11.9 Flow — Validation

1. System validates each row.
2. Validation checks required fields, formats, duplicates, references, and allowed values.
3. Errors are stored per row.
4. User sees valid and invalid rows.
5. User can fix file or continue with valid rows only if policy allows.

## 11.10 Flow — Duplicate Detection

1. System checks imported records against existing records.
2. Potential duplicates are flagged.
3. User reviews duplicates.
4. User chooses skip, merge candidate, or create new based on permission.
5. No automatic merge happens without review.

## 11.11 Flow — Import Preview

1. System shows summary:
   - total rows
   - valid rows
   - invalid rows
   - duplicate candidates
   - records to create
   - records to update
2. User approves import.
3. Approval is logged.

## 11.12 Flow — Execute Import

1. System runs import in queue.
2. Records are created/updated according to approved plan.
3. Every created/updated record gets source attribution.
4. Import progress is tracked.
5. Import completes or completes with errors.

## 11.13 Flow — Rollback

1. Authorized user selects import batch.
2. System checks rollback eligibility.
3. System reverses created records and safe updates.
4. Rollback audit event is created.
5. Import status becomes `rolled_back`.

## 11.14 Required Tests

```text
CSV upload works
Excel upload works
invalid file rejected
mapping required fields enforced
validation errors captured
duplicate candidates flagged
preview generated
import creates records
import does not overwrite silently
rollback works
import audit created
```

## 11.15 Acceptance Criteria

This module is complete when:

```text
import workflow supports upload, mapping, validation, duplicate review, preview, execution, rollback, and audit
no silent overwrite exists
tests pass
```

---

# 12. Module 08 — Master Admin Control Center

## 12.1 Purpose

Master Admin Control Center gives OpesCare super administrators control over platform settings, countries, regions, languages, roles, permissions, modules, feature flags, system health, partner approvals, maintenance mode, and platform audit.

## 12.2 Required Models / Tables

```text
PlatformSetting
Country
Region
City
LanguageSetting
FeatureFlag
ModuleToggle
SystemHealthSnapshot
MaintenanceWindow
AdminActionLog
```

## 12.3 Required Services

```text
PlatformSettingService
CountryRegionService
FeatureFlagService
ModuleToggleService
SystemHealthService
MaintenanceService
AdminAuditService
```

## 12.4 Required UI

```text
super admin dashboard
platform settings page
country/region/city manager
language manager
role/permission overview
module toggle page
feature flag page
maintenance mode page
system health page
partner approval summary
admin action audit page
```

## 12.5 Flow — Manage Countries/Regions

1. Super admin opens geographic settings.
2. Admin creates/updates country, region, city.
3. System validates codes and names.
4. Changes affect facility addresses, Health ID, care map, public health reporting.
5. Audit event is created.

## 12.6 Flow — Manage Languages

1. Admin opens language settings.
2. Admin enables/disables supported languages.
3. English and French must remain supported unless explicitly changed by policy.
4. System updates language availability.
5. Audit event is created.

## 12.7 Flow — Module Toggle

1. Admin opens module toggles.
2. Admin enables/disables module by country, organization, facility, plan, or global setting.
3. System checks dependencies.
4. If disabling affects active workflows, warning is shown.
5. Change is logged.

## 12.8 Flow — Feature Flag

1. Admin creates feature flag.
2. Admin defines scope: global, country, organization, facility, user group.
3. Admin enables/disables flag.
4. System applies flag safely.
5. Audit event is created.

## 12.9 Flow — Maintenance Mode

1. Admin schedules maintenance window.
2. System notifies affected users.
3. Maintenance mode is activated.
4. Critical emergency/verification routes remain available if configured.
5. Maintenance ends and system logs event.

## 12.10 Flow — System Health

1. Admin opens system health page.
2. System shows:
   - database status
   - queue status
   - storage status
   - API health
   - webhook failures
   - failed jobs
   - scheduled tasks
   - disk usage
   - error rates
3. Admin can inspect failures.
4. Audit event records sensitive access if needed.

## 12.11 Required Tests

```text
super admin can update platform setting
non-super admin cannot update platform setting
country/region creation works
module toggle respects dependencies
feature flag applies by scope
maintenance mode works
system health visible only to authorized users
admin actions audited
```

## 12.12 Acceptance Criteria

This module is complete when:

```text
platform settings can be managed
module toggles and feature flags work
system health is visible
maintenance mode works
all actions are audited
tests pass
```

---

# 13. Module 09 — Facility Go-Live Readiness

## 13.1 Purpose

Facility Go-Live Readiness ensures a hospital, clinic, pharmacy, lab, or insurer is safely configured before using OpesCare with real data.

## 13.2 Required Models / Tables

```text
GoLiveChecklist
GoLiveChecklistItem
GoLiveApproval
FacilityReadinessScore
GoLiveBlocker
GoLiveAudit
```

## 13.3 Required Services

```text
GoLiveReadinessService
FacilityReadinessScoreService
GoLiveApprovalService
GoLiveBlockerService
```

## 13.4 Required UI

```text
facility go-live dashboard
checklist page
blocker list
readiness score card
approval workflow page
implementation lead dashboard
```

## 13.5 Required Checklist Items

```text
facility verified
facility admin account created
departments configured
services configured
staff roles assigned
professional licenses captured where required
privacy training completed
certification requirements configured
document templates active
notifications configured
audit logging enabled
billing settings configured if used
insurance settings configured if used
care map listing verified
support contact assigned
data import completed or marked not needed
demo/training completed
go-live approval recorded
```

## 13.6 Flow — Create Go-Live Checklist

1. Facility is approved or onboarded.
2. System creates checklist from facility type.
3. Each item is assigned owner and due date.
4. Implementation lead can view readiness score.

## 13.7 Flow — Complete Checklist Item

1. Responsible user completes item.
2. System verifies automatically where possible.
3. Manual items require evidence or confirmation.
4. Item is marked completed.
5. Readiness score updates.
6. Audit event is created.

## 13.8 Flow — Detect Blocker

1. System checks critical requirements.
2. Missing critical item creates blocker.
3. Facility cannot go live while P0 blocker exists.
4. Blocker appears on readiness dashboard.

## 13.9 Flow — Approve Go-Live

1. All critical items are complete.
2. Implementation lead requests approval.
3. Authorized approver reviews.
4. Approval is recorded.
5. Facility status becomes ready/live.
6. Audit event is created.

## 13.10 Required Tests

```text
checklist generated by facility type
critical blocker prevents go-live
completion updates readiness score
approval requires permission
facility cannot go live without privacy/audit/staff roles
audit event created
```

## 13.11 Acceptance Criteria

This module is complete when:

```text
every facility has go-live checklist
blockers prevent unsafe launch
readiness score works
approval workflow works
audit events exist
tests pass
```

---

# 14. Module 10 — Global Search

## 14.1 Purpose

Global Search allows users to search across OpesCare while enforcing permissions and facility boundaries.

## 14.2 Search Targets

```text
patients
Health IDs
appointments
visits
documents
facilities
medicines
lab tests
partners
support tickets
messages where authorized
claims
invoices
```

## 14.3 Required Models / Tables

```text
SearchIndex
SearchLog
SavedSearch
SearchPermissionFilter
```

## 14.4 Required Services

```text
GlobalSearchService
PatientSearchService
DocumentSearchService
FacilitySearchService
SearchPermissionService
SearchAuditService
```

## 14.5 Required UI

```text
global search bar
advanced search page
search result tabs
patient search result card
document result card
facility result card
medicine/lab result card
recent searches where safe
```

## 14.6 Flow — Search Patient by Health ID

1. User enters Health ID.
2. System validates format.
3. System checks user permission.
4. System returns patient result if authorized or safe verification result if not.
5. Sensitive search is audited.

## 14.7 Flow — Search Patient by Name

1. User enters name.
2. System checks facility/role/purpose.
3. Results are filtered by permission.
4. Patient identifiers are masked where needed.
5. Search event is audited.

## 14.8 Flow — Search Document by Verification Code

1. User enters document code.
2. System identifies document.
3. Public-safe result is shown if user is not authorized.
4. Full document requires permission.
5. Audit event is created.

## 14.9 Flow — Search Facility / Medicine / Lab Test

1. User enters query.
2. System searches care map, pharmacy stock, lab availability.
3. Results show verified/freshness indicators.
4. No patient data is exposed.

## 14.10 Required Tests

```text
patient search permission-filtered
Health ID search audited
document search public-safe
facility search works
medicine search works
lab test search works
unauthorized results hidden
```

## 14.11 Acceptance Criteria

This module is complete when:

```text
global search works across core resources
permission filtering is enforced
sensitive searches audited
tests pass
```

---

# 15. Module 11 — Staff / HR / Shift Management

## 15.1 Purpose

Staff/HR manages staff profiles, professional licenses, departments, shifts, duty rosters, leave, certification/training status, and multi-facility assignments.

## 15.2 Required Models / Tables

```text
StaffProfile
ProfessionalLicense
StaffCredential
StaffShift
DutyRoster
RosterAssignment
LeaveRequest
DepartmentAssignment
StaffTrainingStatus
StaffAvailability
```

## 15.3 Required Services

```text
StaffService
LicenseTrackingService
RosterService
LeaveService
StaffTrainingLinkService
StaffAvailabilityService
```

## 15.4 Required UI

```text
staff directory
staff profile page
professional license page
shift calendar
duty roster builder
leave request page
training/certification status page
multi-facility assignment screen
```

## 15.5 Flow — Create Staff Profile

1. Facility admin opens staff module.
2. Admin creates staff profile or links existing user.
3. Admin assigns role, department, facility.
4. System stores staff category and license fields where applicable.
5. Audit event is created.

## 15.6 Flow — Track Professional License

1. Admin enters license number, issuing body, expiry date, profession.
2. System stores license record.
3. System schedules expiry reminders.
4. Expired license can block certain permissions if policy requires.
5. Audit event is created.

## 15.7 Flow — Create Duty Roster

1. HR/admin selects department and period.
2. Admin assigns staff to shifts.
3. System checks conflicts, leave, and availability.
4. Roster is published.
5. Staff are notified.
6. Audit event is created.

## 15.8 Flow — Leave Request

1. Staff submits leave request.
2. Supervisor reviews.
3. Supervisor approves or rejects.
4. Roster availability updates.
5. Audit event is created.

## 15.9 Flow — Link Certification Status

1. Staff profile fetches Academy certification status.
2. Required training completion is shown.
3. Missing training can block go-live or permissions.
4. Facility competency dashboard updates.

## 15.10 Required Tests

```text
staff profile created
department assignment works
license expiry alert works
expired license can block permission
duty roster prevents conflicts
leave request approval works
training status linked
multi-facility staff assignment isolated
```

## 15.11 Acceptance Criteria

This module is complete when:

```text
staff profiles, licenses, shifts, rosters, leave, training, and multi-facility assignments work
permissions and audits exist
tests pass
```

---

# 16. Module 12 — Triage & Emergency Workflow

## 16.1 Purpose

Triage and Emergency Workflow supports nurse/clinical triage, vital signs, chief complaints, priority classification, emergency escalation, emergency queue bypass, and critical case routing.

## 16.2 Required Models / Tables

```text
TriageAssessment
TriageScore
ChiefComplaint
EmergencyCase
TriageVitalSign
EmergencyEscalation
TriageReassessment
TriageAudit
```

## 16.3 Required Services

```text
TriageService
TriagePriorityService
EmergencyWorkflowService
EmergencyEscalationService
TriageAuditService
```

## 16.4 Required UI

```text
triage dashboard
triage assessment form
vital signs form
emergency queue
triage priority badge
doctor escalation panel
reassessment screen
```

## 16.5 Flow — Start Triage

1. Patient arrives from check-in or emergency.
2. Nurse opens triage form.
3. System links triage to visit.
4. Nurse records chief complaint.
5. Nurse records vital signs.
6. System calculates or suggests priority where configured.
7. Nurse confirms priority.
8. Audit event is created.

## 16.6 Flow — Emergency Escalation

1. Nurse marks patient as emergency.
2. System requires reason.
3. Patient queue priority updates.
4. Doctor/emergency team alert is created.
5. Emergency case record is opened.
6. Audit event is created.

## 16.7 Flow — Triage Reassessment

1. Nurse reopens triage case.
2. Nurse records new vitals or condition change.
3. System updates priority if needed.
4. Change reason is stored.
5. Audit event is created.

## 16.8 Safety Rule

Triage scores and priorities support clinical operations but do not replace clinical judgment.

## 16.9 Required Tests

```text
triage starts from visit
vitals recorded
priority assigned
emergency escalation requires reason
doctor alert created
reassessment updates priority
triage audit created
```

## 16.10 Acceptance Criteria

This module is complete when:

```text
triage, vitals, priority, escalation, reassessment, and audit work
tests pass
```

---

# 17. Module 13 — Inventory & Supply Chain

## 17.1 Purpose

Inventory & Supply Chain manages medical supplies, consumables, medicines, equipment, batches/lots, expiry, stock movements, suppliers, purchase orders, goods receipts, and stock audits.

## 17.2 Required Models / Tables

```text
InventoryItem
InventoryCategory
StockLocation
StockBatch
StockMovement
StockAdjustment
Supplier
PurchaseOrder
PurchaseOrderItem
GoodsReceipt
StockAudit
ReorderRule
```

## 17.3 Required Services

```text
InventoryService
StockMovementService
BatchExpiryService
PurchaseOrderService
GoodsReceiptService
SupplierService
StockAuditService
ReorderAlertService
```

## 17.4 Required UI

```text
inventory dashboard
item catalog
stock location page
stock movement page
batch/expiry tracker
supplier page
purchase order page
goods receipt page
stock audit page
low stock alert page
```

## 17.5 Flow — Create Inventory Item

1. Authorized user creates item.
2. User enters item category, unit, code, reorder level, expiry tracking rule.
3. System validates duplicate item code.
4. Item is created.
5. Audit event is created.

## 17.6 Flow — Receive Stock

1. User selects purchase order or direct receipt.
2. User enters item, quantity, batch/lot, expiry, supplier.
3. System validates item and location.
4. Stock batch is created or updated.
5. Stock movement is recorded.
6. Audit event is created.

## 17.7 Flow — Move Stock

1. User selects source location and destination location.
2. User selects item/batch and quantity.
3. System checks available quantity.
4. System records stock movement.
5. Balances update atomically.
6. Audit event is created.

## 17.8 Flow — Stock Adjustment

1. Authorized user selects item/batch.
2. User enters adjustment type and reason.
3. System requires approval for high-risk adjustments if configured.
4. Stock balance updates.
5. Audit event is created.

## 17.9 Flow — Expiry Tracking

1. Scheduled job checks expiring batches.
2. System creates alerts for soon-to-expire and expired stock.
3. Expired stock can be blocked from dispensing.
4. Audit event is created if stock is marked expired.

## 17.10 Flow — Purchase Order

1. User creates purchase order.
2. User selects supplier and items.
3. Approval workflow runs if required.
4. Purchase order is sent/printed.
5. Goods receipt later links to purchase order.

## 17.11 Required Tests

```text
inventory item created
stock received with batch/expiry
stock movement updates balances
adjustment requires reason
expiry alert generated
expired stock blocked where configured
purchase order created
goods receipt updates stock
stock audit works
```

## 17.12 Acceptance Criteria

This module is complete when:

```text
inventory items, batches, movements, adjustments, suppliers, purchase orders, receipts, expiry, and audits work
tests pass
```

---

# 18. Module 14 — File Storage & Medical Attachments

## 18.1 Purpose

File Storage & Medical Attachments securely handles uploaded documents, lab attachments, imaging files, insurance documents, partner documents, support screenshots, and medical files.

## 18.2 Required Models / Tables

```text
FileAsset
MedicalAttachment
AttachmentAccessLog
FileClassification
VirusScanResult
SignedDownloadToken
AttachmentAudit
```

## 18.3 Required Services

```text
FileStorageService
FileValidationService
VirusScanService
AttachmentService
SignedUrlService
AttachmentAccessPolicyService
```

## 18.4 Required UI

```text
file upload component
attachment list
document attachment viewer
claim attachment viewer
patient attachment panel
support ticket attachment panel
admin file audit page
```

## 18.5 Flow — Upload File

1. User selects file.
2. System validates file type, size, and context.
3. File is stored in private storage.
4. Virus scan placeholder/job runs.
5. File asset record is created.
6. Audit event is created.

## 18.6 Flow — Attach File to Resource

1. User selects file or uploads new file.
2. User selects resource: patient, document, claim, message, ticket, partner.
3. System checks permission.
4. Attachment link is created.
5. Audit event is created.

## 18.7 Flow — Download File

1. User requests download.
2. System checks permission.
3. System creates signed URL or streams file securely.
4. Download is logged.
5. Token expires after configured time.

## 18.8 Flow — Archive/Delete File

1. Authorized user archives file.
2. System checks retention rules.
3. File becomes unavailable for normal use.
4. Deletion is soft or delayed depending policy.
5. Audit event is created.

## 18.9 Required Tests

```text
allowed file uploads
disallowed file rejected
large file rejected
private storage path not exposed
download requires permission
signed URL expires
attachment access audited
support user cannot access patient attachment without permission
```

## 18.10 Acceptance Criteria

This module is complete when:

```text
uploads, private storage, attachment linking, signed downloads, scan placeholder, retention, and audit work
tests pass
```

---

# 19. Module 15 — Analytics & Reporting

## 19.1 Purpose

Analytics & Reporting provides operational, clinical-administrative, public health, financial, API, training, and data quality dashboards without exposing patient data unnecessarily.

## 19.2 Required Models / Tables

```text
AnalyticsSnapshot
DashboardMetric
ReportDefinition
MetricSnapshot
ReportExport
AnalyticsAccessLog
```

## 19.3 Required Services

```text
AnalyticsAggregationService
FacilityAnalyticsService
FinancialAnalyticsService
PublicHealthAnalyticsService
ApiAnalyticsService
TrainingAnalyticsService
DataQualityAnalyticsService
ReportExportService
```

## 19.4 Required Dashboards

```text
facility visits
appointments
queue waiting time
billing and payments
insurance claims
lab volumes
prescription trends
medicine shortages
blood shortages
care map usage
training completion
API health
data quality
public health summaries
```

## 19.5 Flow — Generate Daily Analytics Snapshot

1. Scheduled job runs.
2. System aggregates metrics by facility/organization/country.
3. Patient-level data is not exposed in dashboard snapshots.
4. Snapshot is stored.
5. Failures are logged.

## 19.6 Flow — Facility Dashboard

1. Facility admin opens analytics.
2. System checks facility permission.
3. Dashboard shows facility-specific metrics.
4. No unrelated facility data is shown.
5. Access is logged where sensitive.

## 19.7 Flow — Export Report

1. Authorized user selects report.
2. System checks export permission.
3. System applies de-identification/suppression rules where required.
4. Export file is generated.
5. Download is audited.

## 19.8 Privacy Rules

```text
aggregate by default
patient-identifiable exports restricted
small-number suppression where needed
public health analytics de-identified by default
exports audited
```

## 19.9 Required Tests

```text
daily snapshot generated
facility dashboard filtered by facility
financial dashboard permission works
public health dashboard de-identified
export requires permission
small-number suppression works
analytics access audited
```

## 19.10 Acceptance Criteria

This module is complete when:

```text
dashboards work
metrics are accurate enough for operations
privacy rules are enforced
exports are audited
tests pass
```

---

# 20. Module 16 — Audit, Compliance & Security Operations Hardening

## 20.1 Purpose

This module hardens the existing audit/compliance foundation into a central Security Operations Center for audit review, suspicious access detection, emergency access review, breach response, access review, admin action monitoring, API abuse monitoring, and compliance exports.

## 20.2 Required Models / Tables

```text
AuditEvent
SecurityIncident
AccessReview
SuspiciousAccessFlag
ComplianceCase
BreachReport
ApiAbuseFlag
AdminActionReview
AuditExport
```

## 20.3 Required Services

```text
AuditExplorerService
SuspiciousAccessDetectionService
EmergencyAccessReviewService
SecurityIncidentService
BreachWorkflowService
AccessReviewService
ComplianceExportService
ApiAbuseDetectionService
```

## 20.4 Required UI

```text
security operations dashboard
audit explorer
suspicious access queue
emergency access review page
security incident page
breach report workflow
role/access review page
API abuse dashboard
admin action review
compliance export page
```

## 20.5 Flow — Audit Explorer

1. Authorized privacy/security user opens audit explorer.
2. User filters by actor, patient, facility, action, module, time.
3. System returns results based on permission.
4. Access to audit explorer is itself logged.

## 20.6 Flow — Suspicious Access Detection

1. Scheduled job scans audit events.
2. System flags unusual behavior:
   - many patient records accessed
   - access outside facility
   - repeated failed access
   - emergency access pattern
   - unusual API usage
3. Flag is created.
4. Security user reviews.
5. Case is closed or escalated.

## 20.7 Flow — Emergency Access Review

1. Emergency access creates review task.
2. Reviewer opens case.
3. Reviewer checks reason, actor, patient, data accessed.
4. Reviewer marks justified, unjustified, or needs investigation.
5. Outcome is audited.

## 20.8 Flow — Breach Report

1. Security incident is escalated to breach workflow.
2. System records date/time discovered, affected data, severity, containment steps.
3. Responsible owner is assigned.
4. Tasks are created.
5. Final report is generated.
6. Audit log is preserved.

## 20.9 Flow — Access Review

1. Admin schedules periodic access review.
2. Facility admin/security user reviews staff roles and permissions.
3. Excess permissions are removed.
4. Review completion is logged.

## 20.10 Required Tests

```text
audit explorer requires permission
audit access is logged
suspicious access flag created
emergency access review works
security incident created
breach workflow status transitions
access review removes permission
API abuse flag created
compliance export requires permission
```

## 20.11 Acceptance Criteria

This module is complete when:

```text
audit/security center is usable
suspicious access can be detected and reviewed
emergency access review works
breach workflow exists
exports are controlled
tests pass
```

---

# 21. Module 17 — Telemedicine

## 21.1 Purpose

Telemedicine enables remote consultations where legally and operationally appropriate. This module is later phase unless a facility pilot requires it.

## 21.2 Required Models / Tables

```text
Teleconsultation
TelemedicineConsent
VirtualWaitingRoom
CallSession
TelemedicineNote
TelemedicinePaymentLink
TelemedicineAudit
```

## 21.3 Required Services

```text
TelemedicineService
TelemedicineConsentService
VideoProviderService
VirtualWaitingRoomService
TelemedicineBillingService
TelemedicineAuditService
```

## 21.4 Required UI

```text
telemedicine booking page
telemedicine consent page
virtual waiting room
provider teleconsult dashboard
video/audio session page
telemedicine note page
call history
```

## 21.5 Flow — Book Teleconsultation

1. Patient selects telemedicine appointment type.
2. System shows eligible providers and slots.
3. Patient books appointment.
4. System creates teleconsultation record.
5. Payment rule runs if prepayment is required.
6. Notification is sent.

## 21.6 Flow — Telemedicine Consent

1. Before session, patient sees telemedicine consent.
2. Consent explains limitations, privacy, emergency warning, and recording policy.
3. Patient accepts.
4. Consent record is stored.
5. Without consent, session cannot start.

## 21.7 Flow — Virtual Waiting Room

1. Patient joins waiting room.
2. System verifies appointment, payment, and consent.
3. Provider sees waiting patient.
4. Provider starts session.

## 21.8 Flow — Video/Audio Session

1. Provider starts call.
2. System creates call session.
3. Video provider link/token is generated.
4. Call metadata is recorded.
5. Recording is disabled by default unless policy allows.
6. Audit event is created.

## 21.9 Flow — Teleconsultation Note and Prescription

1. Provider writes consultation note.
2. Provider can issue prescription if authorized.
3. Prescription is linked to teleconsultation.
4. Verifiable document is generated.
5. Patient receives notification.

## 21.10 Required Tests

```text
teleconsultation booking works
consent required before session
emergency disclaimer shown
virtual waiting room works
call session logged
recording disabled by default
teleconsult note linked to encounter
e-prescription permission enforced
```

## 21.11 Acceptance Criteria

This module is complete when:

```text
teleconsult booking, consent, waiting room, session, notes, billing, and prescription integration work
tests pass
```

---

# 22. Module 18 — Ward, Admission & Bed Management

## 22.1 Purpose

Ward, Admission & Bed Management supports inpatient workflows for hospitals: admission, bed assignment, ward transfer, inpatient notes, nursing rounds, medication administration, bed occupancy, discharge planning, and discharge summary.

## 22.2 Required Models / Tables

```text
Admission
Ward
Bed
BedAssignment
WardTransfer
InpatientNote
NursingRound
InpatientMedicationAdministration
DischargePlan
BedOccupancySnapshot
```

## 22.3 Required Services

```text
AdmissionService
BedManagementService
WardTransferService
InpatientCareService
NursingRoundService
DischargePlanningService
BedOccupancyService
```

## 22.4 Required UI

```text
admission page
bed board
ward dashboard
bed assignment modal
ward transfer page
inpatient notes page
nursing rounds page
discharge planning page
bed occupancy dashboard
```

## 22.5 Flow — Admit Patient

1. Authorized staff selects patient/visit.
2. Staff creates admission.
3. Staff selects ward/service.
4. System checks bed availability.
5. Admission status becomes active.
6. Audit event is created.

## 22.6 Flow — Assign Bed

1. Staff selects available bed.
2. System checks bed is not occupied.
3. Bed assignment is created.
4. Bed status becomes occupied.
5. Audit event is created.

## 22.7 Flow — Ward/Bed Transfer

1. Staff selects patient admission.
2. Staff selects destination ward/bed.
3. System checks destination availability.
4. Transfer reason is required.
5. Old bed is released and new bed assigned atomically.
6. Audit event is created.

## 22.8 Flow — Nursing Round

1. Nurse opens ward list.
2. Nurse selects patient.
3. Nurse records round notes, vitals, observations, tasks.
4. System updates inpatient timeline.
5. Audit event is created.

## 22.9 Flow — Medication Administration

1. Nurse opens medication administration record.
2. Nurse verifies patient and medication order.
3. Nurse records administered, refused, missed, or held.
4. Reason is required for exceptions.
5. Audit event is created.

## 22.10 Flow — Discharge Planning

1. Provider starts discharge plan.
2. System checks pending billing, pharmacy, lab, documents, bed release.
3. Provider completes discharge instructions.
4. Discharge summary is generated.
5. Bed is released.
6. Admission status becomes discharged.

## 22.11 Required Tests

```text
admit patient
cannot assign occupied bed
bed transfer releases old bed
nursing round saved
medication administration exception requires reason
discharge blocked by pending requirements
discharge releases bed
```

## 22.12 Acceptance Criteria

This module is complete when:

```text
admission, bed assignment, transfers, inpatient notes, nursing rounds, medication administration, discharge planning, and bed release work
tests pass
```

---

# 23. Module 19 — Clinical Decision Support / Clinical Alerts

## 23.1 Purpose

Clinical Decision Support provides advisory safety alerts for allergies, drug interactions, duplicate prescriptions, abnormal labs, critical labs, age-based warnings, pregnancy warnings, chronic disease reminders, and vaccination reminders.

This module must not diagnose patients or replace clinician judgment.

## 23.2 Required Models / Tables

```text
ClinicalRule
ClinicalRuleSource
ClinicalAlert
DrugInteractionRule
AllergyAlertRule
DoseWarningRule
LabAlertRule
AlertOverride
ClinicalReminder
```

## 23.3 Required Services

```text
ClinicalDecisionSupportService
RuleEvaluationService
DrugInteractionService
AllergyAlertService
LabAlertService
ReminderService
AlertOverrideService
```

## 23.4 Required UI

```text
clinical alert panel
alert modal
alert history
provider override modal
clinical rule admin
CDSS disclaimer panel
```

## 23.5 Flow — Allergy Alert

1. Provider creates prescription/order.
2. System checks patient allergies.
3. If medicine/substance conflicts, alert is shown.
4. Provider can cancel action or override with reason.
5. Override is audited.

## 23.6 Flow — Drug Interaction Alert

1. Provider adds medication.
2. System checks active medications.
3. Interaction rule triggers if found.
4. Alert shows severity and source.
5. Override requires reason.
6. Audit event is created.

## 23.7 Flow — Duplicate Prescription Warning

1. Provider prescribes medication.
2. System checks current active prescriptions.
3. Duplicate or similar medication warning appears.
4. Provider confirms or cancels.
5. Audit event is created.

## 23.8 Flow — Critical Lab Alert

1. Lab result is validated.
2. System checks critical thresholds.
3. Critical alert is created.
4. Provider/nurse is notified.
5. Alert requires acknowledgement.
6. Escalation occurs if unacknowledged.
7. Audit event is created.

## 23.9 Flow — Alert Override

1. Alert is displayed.
2. Provider selects override.
3. Provider enters reason.
4. System records override.
5. Order/prescription continues.
6. Audit event is created.

## 23.10 Safety Disclaimer

All CDSS pages must state:

```text
Clinical alerts are decision-support tools only. They do not replace professional clinical judgment.
```

## 23.11 Required Tests

```text
allergy alert triggers
drug interaction alert triggers
duplicate prescription warning triggers
critical lab alert triggers
override requires reason
alert acknowledgement works
escalation works
CDSS disclaimer visible
```

## 23.12 Acceptance Criteria

This module is complete when:

```text
advisory alerts work
override requires reason
alerts are audited
no automated diagnosis is implied
tests pass
```

---

# 24. Module 20 — Offline Mode & Sync

## 24.1 Purpose

Offline Mode & Sync supports limited, safe operation in low-connectivity environments. It must be implemented carefully because offline health data is high-risk.

## 24.2 Required Models / Tables

```text
OfflineQueue
OfflineCachePolicy
SyncJob
SyncConflict
SyncAttempt
OfflineAuditEvent
DeviceSyncState
```

## 24.3 Required Services

```text
OfflinePolicyService
OfflineQueueService
SyncService
ConflictResolutionService
OfflineAuditService
DeviceSyncService
```

## 24.4 Required UI

```text
offline status indicator
sync queue dashboard
sync conflict resolution page
offline warning banner
device sync status page
```

## 24.5 Flow — Offline Data Capture

1. User loses connection.
2. System enters offline-limited mode.
3. Only allowed forms/actions remain available.
4. User captures permitted data.
5. Data is stored in encrypted local queue.
6. Offline audit event is created locally.

## 24.6 Flow — Sync Retry

1. Connection returns.
2. System submits queued changes.
3. Server validates permissions, timestamps, and conflicts.
4. Successful items are marked synced.
5. Failed items remain queued or become conflict cases.

## 24.7 Flow — Conflict Detection

1. Server detects record changed since offline capture.
2. Conflict is created.
3. User/admin must review.
4. No silent overwrite is allowed.
5. Audit event is created.

## 24.8 Flow — Conflict Resolution

1. Authorized user opens conflict.
2. User compares local and server data.
3. User chooses keep server, apply local, merge manually, or discard.
4. Resolution reason is stored.
5. Audit event is created.

## 24.9 Offline Privacy Rules

```text
do not cache full EMR by default
encrypt local data
limit offline data by role/facility
offline consent changes must sync before broad access
emergency offline access must be reviewed after sync
```

## 24.10 Required Tests

```text
offline mode limits actions
local queue encrypts/marks sensitive data
sync retry works
conflict created when record changed
no silent overwrite
conflict resolution audited
offline emergency access reviewed
```

## 24.11 Acceptance Criteria

This module is complete when:

```text
offline mode is limited and secure
sync works
conflicts are resolved safely
audit is preserved
tests pass
```

---

# 25. Module 21 — Patient Mobile App API Readiness

## 25.1 Purpose

Patient mobile app readiness ensures all required APIs exist for a Flutter app or other mobile client without rebuilding backend logic.

## 25.2 Required API Capabilities

```text
mobile login/MFA
Health ID card
QR display
patient profile
clinical timeline
lab results
prescriptions
appointments
medicine finder
blood finder
care map
consent requests
messages
notifications
documents
record sharing
emergency profile
settings
device push tokens
```

## 25.3 Required Models / Tables

```text
MobileSession
PushDeviceToken
MobileDevice
MobileAppSetting
```

## 25.4 Required Routes

```text
GET  /api/v1/mobile/patient/home
GET  /api/v1/mobile/patient/health-id
GET  /api/v1/mobile/patient/timeline
GET  /api/v1/mobile/patient/lab-results
GET  /api/v1/mobile/patient/prescriptions
GET  /api/v1/mobile/patient/appointments
GET  /api/v1/mobile/patient/documents
GET  /api/v1/mobile/patient/consents
POST /api/v1/mobile/patient/push-token
```

## 25.5 Required Tests

```text
mobile patient auth works
patient can view own Health ID
patient cannot view another patient data
push token stored
notifications privacy-safe
document download permission works
consent action works
```

## 25.6 Acceptance Criteria

This module is complete when:

```text
patient mobile APIs cover all patient app needs
permissions match web
push tokens work
tests pass
```

---

# 26. Module 22 — Provider Mobile App API Readiness

## 26.1 Purpose

Provider mobile app readiness supports doctors, nurses, pharmacists, lab staff, and other authorized providers.

## 26.2 Required API Capabilities

```text
provider mobile login/MFA
facility context switch
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
push notifications
```

## 26.3 Required Models / Tables

```text
ProviderMobileSession
ProviderDevice
PushDeviceToken
MobileFacilityContext
```

## 26.4 Required Routes

```text
GET  /api/v1/mobile/provider/home
POST /api/v1/mobile/provider/facility-context
POST /api/v1/mobile/provider/scan-health-id
POST /api/v1/mobile/provider/scan-document
GET  /api/v1/mobile/provider/tasks
GET  /api/v1/mobile/provider/messages
GET  /api/v1/mobile/provider/alerts
POST /api/v1/mobile/provider/emergency-access
POST /api/v1/mobile/provider/push-token
```

## 26.5 Required Tests

```text
provider mobile auth works
facility context required
Health ID scan respects consent
document scan privacy-safe
critical alert appears
emergency access requires reason
mobile action audited
```

## 26.6 Acceptance Criteria

This module is complete when:

```text
provider mobile APIs work
facility context is enforced
critical workflows are audited
tests pass
```

---

# 27. Module 23 — Subscription & SaaS Billing

## 27.1 Purpose

Subscription & SaaS Billing manages organization-level OpesCare subscriptions, plans, trial periods, module activation, usage limits, API usage billing, plan upgrades/downgrades, and subscription invoices.

This is separate from patient medical billing.

## 27.2 Required Models / Tables

```text
SubscriptionPlan
PlanFeature
OrganizationSubscription
SubscriptionInvoice
SubscriptionPayment
UsageMetric
UsageLimit
ModuleEntitlement
TrialPeriod
```

## 27.3 Required Services

```text
SubscriptionService
PlanFeatureService
UsageBillingService
PlanLimitService
ModuleEntitlementService
SubscriptionInvoiceService
TrialService
```

## 27.4 Required UI

```text
subscription plan admin
organization billing portal
subscription invoice page
usage dashboard
module entitlement page
trial management page
upgrade/downgrade page
```

## 27.5 Flow — Create Subscription Plan

1. Super admin creates plan.
2. Admin defines price, billing interval, features, modules, limits.
3. Plan is saved as draft or active.
4. Audit event is created.

## 27.6 Flow — Organization Subscribes

1. Organization admin selects plan.
2. System creates subscription.
3. Trial is applied if configured.
4. Module entitlements are activated.
5. Billing schedule is created.
6. Audit event is created.

## 27.7 Flow — Enforce Usage Limit

1. User performs usage-limited action: API call, facility count, user count, document generation, SMS/WhatsApp, etc.
2. System checks plan limit.
3. If within limit, action proceeds.
4. If limit exceeded, action is blocked or overage rule applies.
5. Event is recorded.

## 27.8 Flow — Upgrade/Downgrade

1. Organization admin selects new plan.
2. System calculates proration if enabled.
3. New entitlements are applied.
4. Downgrade warnings show if features will be lost.
5. Audit event is created.

## 27.9 Flow — Cancel Subscription

1. Organization admin requests cancellation.
2. System shows consequences.
3. Subscription is cancelled at period end or immediately based policy.
4. Module entitlements update.
5. Audit event is created.

## 27.10 Required Tests

```text
plan created
organization subscribes
trial starts and expires
module entitlement applied
usage limit enforced
upgrade works
downgrade warning works
subscription invoice generated
subscription billing separate from patient billing
```

## 27.11 Acceptance Criteria

This module is complete when:

```text
plans, subscriptions, trials, entitlements, usage limits, invoices, upgrades, downgrades, and cancellations work
tests pass
```

---

# 28. Full End-to-End Flow Test Suite

These tests must exist before a serious pilot.

## 28.1 Full Patient Visit Test

```text
patient signs up
Health ID generated
appointment booked
appointment confirmed
patient checked in
queue ticket generated
triage completed
consultation note created
lab order created
invoice generated
payment recorded
receipt generated
lab result released
document generated
notification sent
visit closed
audit trail complete
```

## 28.2 Insurance Visit Test

```text
patient policy registered
eligibility checked
appointment booked
visit completed
invoice generated
preauthorization requested if needed
claim created
claim submitted
payer reviews minimum necessary data
claim approved
payment posted
invoice balance updated
```

## 28.3 Pharmacy Dispense Test

```text
doctor issues prescription
prescription QR generated
patient searches medicine
pharmacy verifies prescription
pharmacy dispenses
stock updated
dispensing receipt generated
audit trail complete
```

## 28.4 Emergency Flow Test

```text
patient arrives emergency
triage marks emergency
queue priority bypass works
doctor alert sent
emergency access reason required
emergency review task created
audit trail complete
```

## 28.5 Data Import to Go-Live Test

```text
facility created
facility verified
staff imported
patients imported
services configured
document templates active
privacy training completed
go-live checklist completed
go-live approved
```

---

# 29. Cross-Cutting Required Audit Events

Every implementation must add audit events for:

```text
appointment_created
appointment_rescheduled
appointment_cancelled
appointment_checked_in
queue_ticket_created
queue_ticket_called
queue_ticket_transferred
queue_priority_override
invoice_created
invoice_issued
invoice_adjusted
payment_recorded
receipt_generated
refund_processed
wallet_transaction_posted
eligibility_checked
preauthorization_submitted
claim_submitted
claim_decided
claim_payment_posted
visit_created
visit_step_completed
visit_closed
support_ticket_created
support_ticket_escalated
incident_created
import_job_created
import_approved
import_completed
import_rolled_back
admin_setting_changed
feature_flag_changed
go_live_approved
staff_role_changed
triage_completed
emergency_escalated
stock_received
stock_adjusted
file_uploaded
file_downloaded
analytics_exported
security_incident_created
teleconsultation_started
admission_created
bed_assigned
clinical_alert_triggered
alert_override_recorded
sync_conflict_created
subscription_changed
```

---

# 30. Cross-Cutting Required Permissions

```text
appointments.view_own
appointments.manage_facility
queue.manage
queue.priority_override
billing.create_invoice
billing.record_payment
billing.refund_payment
insurance.claims.create
insurance.claims.review
visit.manage
support.create_ticket
support.manage
imports.manage
admin.platform.manage
golive.manage
search.global
staff.manage
triage.manage
inventory.manage
files.upload
files.download
analytics.view
analytics.export
security.audit.view
telemedicine.use
wards.manage
cdss.rules.manage
offline.sync.manage
mobile.patient.use
mobile.provider.use
subscriptions.manage
```

---

# 31. Final Build Instruction for Agents

Use this instruction:

```text
You are implementing the missing and incomplete operational modules for OpesCare.

Do not rebuild working modules.
Do not duplicate existing code.
Do not use or copy OpesHIS OS.
Audit first, then implement.
For each module, create or patch models, migrations, services, routes, controllers, UI/API, permissions, audit events, notifications, tests, seeders/demo data, and docs.
Every flow listed in this document must be implemented or explicitly marked deferred with reason.
Run tests after each module.
Do not mark any module complete without evidence and passing tests.
```

---

# 32. Launch Readiness Blockers

Do not launch a real facility pilot if any of these remain true:

```text
appointments do not link to visit/check-in
queue does not link to visit
billing cannot generate invoice/payment/receipt
full visit flow cannot close safely
insurance users can see full EMR by default
support users can access records without audit
imports can overwrite data silently
facility can go live without roles/privacy/audit/templates
global search leaks unauthorized patient records
files are stored publicly
critical patient actions lack audit logs
emergency access lacks review
payments/refunds lack audit
```

---

# 33. Final Acceptance Criteria

The operational layer is complete when:

```text
appointments work
queue works
billing works
payments and receipts work
insurance works
visit flow works end-to-end
support works
data import works
master admin works
go-live readiness works
global search is permission-safe
staff/HR works
triage works
inventory works
file attachments are secure
analytics are privacy-safe
security operations are usable
telemedicine is ready if enabled
wards/admissions work if enabled
CDSS is advisory and audited
offline sync is limited and secure if enabled
mobile APIs are ready
SaaS billing is separated from patient billing
all critical tests pass
```
