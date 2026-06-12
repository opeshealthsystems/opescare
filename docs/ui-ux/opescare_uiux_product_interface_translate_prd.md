# OpesCare UI/UX and Product Interface PRD

## Document Title

**OpesCare UI/UX Design System and Product Interface Requirements Document**  
**Complete Interface, Layout, Navigation, Dashboard, Component, Responsive, Accessibility, and Screen-by-Screen PRD**

Project: **OpesCare**  
Domain: **opescare.com**  
Parent company: **Opesware**  
Build direction: **Build from scratch**  
Important constraint: **Do not use OpesHIS OS. Do not copy OpesHIS OS UI, layouts, database assumptions, component structure, or module structure.**

---

# 1. Purpose of This UI/UX PRD

This document defines the complete product interface direction for OpesCare.

The backend PRD defines architecture, modules, flows, data rules, security, APIs, and audit logic. This document defines how users experience the product visually and operationally.

OpesCare must not look like a generic hospital admin template. It must look like a modern digital health infrastructure platform: trustworthy, clinical, clean, fast, secure, and professional.

The interface must support hospitals, clinics, labs, pharmacies, insurers, patients, administrators, integration teams, and future public health users.

Every UI decision must support:

- patient safety
- speed of clinical work
- reduced staff confusion
- clear patient identity
- strong consent visibility
- audit awareness
- mobile and tablet usability
- low-bandwidth environments
- accessibility
- role-specific workflows
- scalable module expansion

---

# 2. Product Interface Philosophy

OpesCare is healthcare infrastructure, not a social app and not a casual admin dashboard.

The interface must communicate:

- trust
- calmness
- speed
- clarity
- privacy
- clinical seriousness
- operational control
- modern institutional quality

The UI must help users answer critical questions quickly:

- Am I viewing the right patient?
- What is this patient’s Health ID?
- What is the patient allergic to?
- Is there consent to view this record?
- What facility created this record?
- Is this data verified or patient-uploaded?
- What is urgent?
- What action is next?
- Has this record synchronized successfully?
- Is this action audited?

The design must reduce dangerous mistakes such as wrong-patient documentation, unapproved access, missed allergies, unreleased lab results, duplicate prescriptions, and unclear billing status.

---

# 3. Core UI Principles

## 3.1 Patient Identity Must Always Be Visible

On every patient-related screen, a persistent patient identity banner must be visible.

The banner should show:

- patient display name
- Health ID
- age/date of birth
- sex
- profile verification status
- facility/local patient ID where applicable
- critical allergies indicator
- consent status
- emergency warning where applicable
- patient photo placeholder where available

This prevents wrong-patient errors.

## 3.2 Role-Specific Interfaces

Receptionists, doctors, nurses, lab staff, pharmacists, cashiers, admins, and patients must not see the same dashboard.

Each role needs a focused interface.

A doctor should see worklist, patient summary, triage, allergies, timeline, notes, orders, and prescriptions.

A cashier should see invoices, payments, receipts, balances, and cashier sessions.

A lab user should see pending orders, sample collection, result entry, validation, and critical result handling.

A patient should see Health ID, consent requests, timeline summary, prescriptions, lab results, dependents, and access logs.

## 3.3 Minimum Necessary Data Display

The interface must show only what the user is authorized to see.

Do not show restricted clinical details to users who only need identity confirmation, billing, pharmacy dispensing, insurance eligibility, or lab sample handling.

## 3.4 Clear State and Status Labels

Every important item must show status clearly.

Examples:

- Provisional patient
- Verified patient
- Consent pending
- Consent granted
- Consent revoked
- Lab result pending validation
- Lab result released
- Lab result amended
- Prescription issued
- Prescription partially dispensed
- Prescription cancelled
- Claim queried
- Sync failed
- Reconciliation required

## 3.5 Actions Must Be Contextual

Do not show every possible action everywhere.

Show actions based on:

- user role
- patient status
- encounter status
- facility status
- consent status
- record status
- current workflow stage

## 3.6 Dangerous Actions Require Confirmation

Dangerous actions must show confirmation dialogs with clear consequences.

Dangerous actions include:

- patient merge
- patient unmerge
- emergency access
- prescription cancellation
- released result amendment
- payment reversal
- stock recall
- facility suspension
- user offboarding
- consent revocation
- record entered-in-error

## 3.7 Everything Important Must Be Auditable

The UI must remind users that sensitive actions are audited, especially emergency access, record export, patient merge, data correction, and restricted record access.

---

# 4. Visual Design Direction

## 4.1 Brand Feel

OpesCare should feel like a premium health infrastructure product.

Visual keywords:

- clinical
- calm
- precise
- secure
- modern
- institutional
- trustworthy
- fast
- organized
- minimal but not empty
- data-rich but not cluttered

Avoid:

- childish colors
- overcrowded dashboards
- generic admin templates
- random gradients everywhere
- weak contrast
- decorative medical icons that do not help workflow
- excessive animations
- unclear card hierarchy

## 4.2 Recommended Color System

Use a professional healthcare palette.

Primary color family:

- deep clinical blue
- clean white
- soft background gray
- dark navy text

Secondary accents:

- teal for positive/verified states
- amber for warnings
- red for critical alerts
- purple/indigo for integrations or system-level tools
- green for completed/success states

Do not rely on color alone to communicate meaning. Always pair color with text/icon.

## 4.3 Suggested Design Tokens

The final implementation should define tokens similar to:

```text
--color-primary: deep clinical blue
--color-primary-hover: darker clinical blue
--color-background: soft off-white
--color-surface: white
--color-surface-muted: light gray
--color-text-primary: near-black/navy
--color-text-secondary: slate gray
--color-border: pale gray
--color-success: green/teal
--color-warning: amber
--color-danger: red
--color-info: blue
--radius-card: 12px to 16px
--radius-button: 8px to 10px
--shadow-card: soft low elevation
--spacing-xs: 4px
--spacing-sm: 8px
--spacing-md: 16px
--spacing-lg: 24px
--spacing-xl: 32px
```

Exact colors should be finalized in the design system file before implementation.

## 4.4 Typography

Use a modern, highly readable sans-serif typeface.

Text hierarchy:

- page title: strong, clear, not oversized
- section titles: medium weight
- body text: readable and compact
- clinical values: monospaced or tabular numbers where useful
- labels: small but readable
- status badges: short, clear, consistent

Avoid tiny text in clinical workflows.

## 4.5 Iconography

Use a consistent icon set.

Icons should support meaning, not decoration.

Examples:

- patient identity
- allergy warning
- consent
- facility
- lab
- prescription
- billing
- insurance
- referral
- audit
- sync status
- emergency
- lock/privacy
- warning
- success

Icons must be consistent in stroke, size, and spacing.

---

# 5. Layout System

## 5.1 Application Shell

The main application shell should include:

- left sidebar navigation on desktop
- top bar with facility, user role, notifications, search, and quick actions
- main content area
- contextual right panel where needed
- persistent patient banner on patient-related screens

Desktop layout:

```text
+--------------------------------------------------------------+
| Top Bar: Facility | Search | Alerts | User | Role            |
+-----------+--------------------------------------------------+
| Sidebar   | Page Header                                      |
| Navigation| Patient Banner where applicable                  |
|           | Main Content                                     |
|           | Optional Right Context Panel                      |
+-----------+--------------------------------------------------+
```

Tablet layout:

- collapsible sidebar
- top navigation remains visible
- patient banner remains sticky
- right panels become drawers

Mobile layout:

- bottom navigation for patient portal
- drawer navigation for staff portal
- stacked cards
- sticky patient identity summary
- large tap targets
- simplified tables converted to cards

## 5.2 Sidebar Navigation

Sidebar should be role-aware.

Example provider navigation:

- Dashboard
- Worklist
- Patients
- Encounters
- Orders
- Prescriptions
- Referrals
- Timeline
- Alerts

Example admin navigation:

- Dashboard
- Facilities
- Staff
- Roles
- Integrations
- Audit Logs
- Reconciliation
- Country Config
- Settings

Example lab navigation:

- Lab Dashboard
- Pending Orders
- Sample Collection
- Result Entry
- Validation Queue
- Released Results
- Critical Results

## 5.3 Top Bar

Top bar should include:

- current facility/branch selector
- global patient search where role allows
- notification bell
- sync status indicator
- user menu
- emergency access shortcut only for allowed roles

## 5.4 Page Header

Each page should have:

- title
- short description
- primary action
- status summary where useful
- breadcrumbs for deep pages

## 5.5 Right Context Panel

Use a right panel for:

- patient quick summary
- consent details
- audit/provenance details
- task details
- form preview
- sync failure details
- reconciliation notes

Do not overload the main page with secondary information.

---

# 6. Responsive Design Requirements

## 6.1 Desktop

Desktop is primary for hospital operations.

Must support:

- large data tables
- split views
- multi-column forms
- patient banner
- side navigation
- modals/drawers
- keyboard-friendly workflows

## 6.2 Tablet

Tablet is important for nurses, wards, rounds, and mobile staff.

Must support:

- touch-friendly controls
- collapsible sidebar
- patient banner
- card-based lists
- simplified form sections

## 6.3 Mobile

Mobile is critical for patient portal, field staff, and emergency lookup.

Must support:

- responsive patient portal
- QR display
- consent approval
- access logs
- lab result viewing
- prescription viewing
- appointment reminders
- emergency profile

Hospital staff mobile views should be simplified and not expose complex dashboards unless optimized.

## 6.4 Tables on Mobile

Tables must convert into cards on small screens.

Each card should show the most important fields first, then expandable details.

## 6.5 Forms on Mobile

Forms must use:

- single-column layout
- large inputs
- grouped sections
- progress indicators for long forms
- sticky save/continue buttons where helpful

---

# 7. Accessibility Requirements

OpesCare must be accessible and readable.

Requirements:

- keyboard navigation
- visible focus states
- sufficient color contrast
- readable font sizes
- labels for all inputs
- error messages linked to inputs
- icons paired with text
- screen-reader-friendly status labels
- avoid color-only meaning
- support reduced motion
- support clear language for patient portal

Clinical software must be usable under stress, low light, and time pressure.

---

# 8. Design System Components

The design system must include the following components.

## 8.1 Buttons

Types:

- primary
- secondary
- outline
- ghost
- danger
- emergency
- icon button
- loading button

Rules:

- destructive buttons must be visually distinct
- emergency button must require confirmation except configured emergency workflows
- loading state must prevent double submission

## 8.2 Inputs

Types:

- text
- number
- date
- time
- date/time
- phone
- email
- select
- multi-select
- searchable select
- textarea
- checkbox
- radio
- file upload
- OTP input
- PIN input

Rules:

- clinical numeric fields must show units
- required fields must be clear
- validation must be immediate where safe
- errors must be specific

## 8.3 Cards

Card types:

- patient summary card
- encounter card
- lab result card
- prescription card
- invoice card
- facility card
- integration status card
- alert card

Cards must have clear hierarchy, spacing, and status labels.

## 8.4 Tables

Tables must support:

- sorting
- filtering
- search
- pagination
- row actions
- status badges
- bulk actions only where safe
- empty states
- loading states

High-risk bulk actions must be avoided or heavily controlled.

## 8.5 Status Badges

Badges must be consistent across the platform.

Examples:

- Verified
- Provisional
- Pending
- Active
- Suspended
- Revoked
- Released
- Amended
- Critical
- Synced
- Failed
- Reconciliation Required

## 8.6 Alerts

Alert levels:

- informational
- success
- warning
- danger
- critical
- blocking

Clinical alerts must be visually distinct from general UI alerts.

## 8.7 Modals and Drawers

Use modals for confirmation and short tasks.

Use drawers for contextual details without leaving the page.

Do not use modals for long clinical forms.

## 8.8 Timeline Component

The timeline component must support:

- chronological display
- filters
- record type icons
- source facility
- verification status
- amendments
- sensitivity labels
- expandable details
- print/download with audit

## 8.9 Patient Banner Component

The patient banner is mandatory on patient-related screens.

It must include:

- display name
- Health ID
- age/date of birth
- sex
- verification status
- consent status
- allergies summary
- emergency warning if any
- quick actions based on role

## 8.10 Consent Panel Component

The consent panel must show:

- requesting facility
- requesting provider/role
- purpose of use
- requested data scope
- expiry
- approve/deny options
- audit note

## 8.11 Sync Status Component

Must show:

- synced
- pending
- failed
- retrying
- reconciliation required
- last sync time
- source system

## 8.12 Empty States

Empty states must be helpful.

Examples:

- “No lab results yet.”
- “No active consent grants.”
- “No reconciliation cases assigned to you.”

Empty states should suggest next safe action where appropriate.

## 8.13 Loading States

Use skeleton loading for dashboards and cards.

Use spinners for small actions.

Prevent duplicate submits with disabled/loading button state.

## 8.14 Error States

Errors must be clear and actionable.

Bad: “Something went wrong.”

Better: “Consent is required before viewing this patient summary. Request consent to continue.”

---

# 9. Global Navigation Architecture

## 9.1 Super Admin Navigation

Super admin sees:

- Overview
- Facilities
- Organizations
- Users
- Roles & Permissions
- Patients
- Reconciliation
- Integrations
- Webhooks
- Audit Logs
- Security Events
- Country Config
- Governance
- Reports
- Settings

## 9.2 Facility Admin Navigation

Facility admin sees:

- Facility Dashboard
- Staff
- Departments
- Services
- Price Lists
- Schedules
- Patients
- Encounters
- Billing
- Inventory
- Integrations
- Reports
- Audit Logs
- Settings

## 9.3 Reception Navigation

Reception sees:

- Dashboard
- Register Patient
- Patient Search
- Appointments
- Check-In
- Queue
- Visit Status

## 9.4 Provider Navigation

Provider sees:

- Dashboard
- Worklist
- Patients
- Encounters
- Clinical Timeline
- Orders
- Prescriptions
- Referrals
- Alerts

## 9.5 Nurse Navigation

Nurse sees:

- Dashboard
- Triage Queue
- Vitals
- Nursing Notes
- Ward Patients
- Medication Administration
- Handover

## 9.6 Lab Navigation

Lab sees:

- Lab Dashboard
- Pending Orders
- Sample Collection
- Result Entry
- Validation Queue
- Released Results
- Critical Results

## 9.7 Pharmacy Navigation

Pharmacy sees:

- Pharmacy Dashboard
- Prescriptions
- Dispensing
- Medication History
- Stock
- Batch Alerts

## 9.8 Cashier Navigation

Cashier sees:

- Cashier Dashboard
- Invoices
- Payments
- Receipts
- Refund Requests
- Cashier Session

## 9.9 Patient Portal Navigation

Patient sees:

- Home
- My Health ID
- My Timeline
- Lab Results
- Prescriptions
- Consent Requests
- Access Logs
- Dependents
- Appointments
- Documents
- Settings

---

# 10. Dashboard Requirements

## 10.1 Super Admin Dashboard

Purpose:

Platform-wide control and monitoring.

Widgets:

- total registered patients
- active facilities
- pending facility approvals
- API health
- sync failures
- reconciliation cases
- emergency access cases pending review
- suspicious access alerts
- webhook failures
- system uptime indicator
- country configuration status

Primary actions:

- review facilities
- open reconciliation queue
- inspect audit logs
- manage integrations
- review emergency access

## 10.2 Facility Admin Dashboard

Widgets:

- visits today
- patients registered today
- active staff
- pending bills
- queue status
- lab turnaround status
- pharmacy stock warnings
- unpaid invoices
- integration sync status
- audit flags

## 10.3 Reception Dashboard

Widgets:

- appointments today
- walk-ins today
- checked-in patients
- waiting queue
- pending registrations
- duplicate match warnings

Primary actions:

- register patient
- search patient
- check in patient
- create appointment

## 10.4 Provider Dashboard

Widgets:

- assigned patients
- waiting patients
- lab results to review
- critical alerts
- unsigned notes
- follow-ups due
- referrals pending feedback

Primary actions:

- open next patient
- search patient
- view worklist
- review critical result

## 10.5 Nurse Dashboard

Widgets:

- triage queue
- critical vitals
- ward patients
- medications due
- handover notes
- abnormal observations

## 10.6 Lab Dashboard

Widgets:

- pending orders
- samples collected
- results pending validation
- critical results
- rejected samples
- turnaround-time alerts

## 10.7 Pharmacy Dashboard

Widgets:

- prescriptions pending
- partial dispenses
- stock low alerts
- near-expiry batches
- recalled batches
- dispensing queue

## 10.8 Cashier Dashboard

Widgets:

- invoices pending payment
- payments today
- reversals pending approval
- refunds pending approval
- cashier session status
- unpaid balances

## 10.9 Patient Dashboard

Widgets:

- Health ID card
- active consent requests
- recent visits
- lab results
- prescriptions
- upcoming appointments
- access log summary
- dependents

---

# 11. Screen-by-Screen PRD

## 11.1 Login Screen

Users:

- staff
- patients
- guardians
- integration admins where applicable

Requirements:

- clean OpesCare branding
- email/phone/username input depending on user type
- password or OTP option depending on portal
- forgot password
- MFA challenge where configured
- facility selection after login where user belongs to multiple facilities
- security notice

Failure states:

- invalid credentials
- account suspended
- facility suspended
- MFA required
- too many attempts
- password expired

## 11.2 Facility Selector Screen

For users linked to multiple facilities.

Requirements:

- list facilities/branches
- show role per facility
- show facility status
- block suspended facility access
- remember selected facility for session

## 11.3 Global Patient Search Screen

Requirements:

- search by Health ID
- QR scan
- phone
- name/date of birth
- local facility ID
- insurance number where allowed
- result confidence display
- duplicate candidate warning
- privacy-safe search results

Search result should show only:

- name initials or partial name depending on role/policy
- Health ID
- age/date of birth
- sex
- verification status
- last known facility where allowed

Do not show clinical data in search results.

## 11.4 Patient Registration Screen

Sections:

1. identity basics
2. contact details
3. emergency contact
4. guardian/dependent details
5. identifiers
6. insurance optional
7. allergies known at registration
8. duplicate candidate review
9. consent notice
10. final review

UX requirements:

- step-by-step flow for long registration
- duplicate candidate warning before save
- provisional status label
- clear required fields
- save draft where allowed

## 11.5 Patient Profile Screen

Sections:

- patient identity banner
- demographics
- Health ID and QR
- identifiers
- guardian/dependents
- verification status
- emergency contacts
- active alerts
- recent activity
- access log shortcut

Actions:

- verify patient
- request demographic correction
- replace card
- print Health ID
- suspend profile
- mark deceased where authorized

## 11.6 Health ID Card Screen

Requirements:

- display Health ID
- QR code
- patient display name
- verification status
- emergency instruction text
- print/download card
- rotate QR token if compromised

QR must not contain full medical history.

## 11.7 Check-In Screen

Requirements:

- patient search/scan
- patient confirmation
- visit type selection
- department selection
- appointment link if any
- billing policy indicator
- triage required indicator
- queue assignment

Failure states:

- patient not found
- possible duplicate
- facility suspended
- patient deceased
- active visit already exists

## 11.8 Appointment Screen

Requirements:

- calendar view
- provider schedule
- slot availability
- service type
- booking confirmation
- payment requirement indicator
- reschedule/cancel actions
- no-show marking

## 11.9 Queue Screen

Views:

- waiting
- in service
- skipped
- completed
- transferred

Requirements:

- priority badges
- queue number
- patient identity summary
- waiting time
- department
- action buttons based on role
- manual priority override with reason

## 11.10 Consent Request Screen

For staff:

- request purpose
- requested scope
- duration
- patient/guardian approval method
- status

For patient:

- requesting facility
- requesting role/provider
- purpose
- exact data requested
- expiry
- approve/deny buttons
- explanation in simple language

## 11.11 Emergency Access Screen

Requirements:

- patient search/scan
- reason required
- warning that action is audited
- emergency data only
- no full history by default
- compliance review created

Emergency summary layout:

- patient identity
- blood group
- allergies
- chronic conditions
- high-risk medications
- emergency contacts
- warnings

## 11.12 Triage Screen

Fields:

- presenting complaint
- temperature
- blood pressure
- pulse
- respiratory rate
- oxygen saturation
- weight
- height
- BMI auto-calculated
- pain score
- pregnancy status where applicable
- acuity level

UX:

- unit labels visible
- abnormal values highlighted
- critical values create alert
- repeated vitals timeline

## 11.13 Consultation Workspace

Layout:

- patient banner at top
- left column: summary, allergies, vitals, medications
- main area: clinical note, diagnosis, plan
- right panel: timeline, labs, orders, prescriptions

Tabs/sections:

- Summary
- Notes
- Diagnosis
- Orders
- Prescription
- Timeline
- Documents
- Referral
- Follow-Up

Actions:

- save draft
- sign note
- amend note
- order lab
- order imaging
- prescribe
- refer
- admit
- close encounter

## 11.14 Clinical Timeline Screen

Requirements:

- chronological event list
- filter by type/date/facility/source/status
- event cards with source and verification
- amendments visible
- sensitive records hidden/redacted
- export/print audited

Event card should show:

- event type
- date/time
- facility
- provider/source
- status
- short summary
- expand button

## 11.15 Allergy and Warning Panel

Requirements:

- allergy list always visible in clinical and prescription screens
- severity labels
- source
- verification status
- last updated
- add/update/inactivate actions
- override reason for high-risk prescribing

## 11.16 Lab Order Screen

Requirements:

- select tests
- search test catalogue
- show availability
- show sample type
- show price if billing configured
- priority selection
- clinical notes
- order confirmation

## 11.17 Lab Sample Collection Screen

Requirements:

- pending orders list
- patient verification
- sample type
- barcode/sample ID
- collection time
- collector
- specimen status
- rejection reason where applicable

## 11.18 Lab Result Entry Screen

Requirements:

- test name
- result value
- unit
- reference range
- abnormal flag
- result notes
- validation status
- save as pending validation

## 11.19 Lab Validation Screen

Requirements:

- pending validation queue
- validator review
- approve/reject
- critical result flag
- release result
- amendment action for released results

## 11.20 Prescription Screen

Requirements:

- medication search
- dose
- route
- frequency
- duration
- instructions
- allergy warnings
- interaction warnings where available
- duplicate medication warning
- issue prescription
- cancel prescription

## 11.21 Pharmacy Dispensing Screen

Requirements:

- search prescription
- verify patient
- active prescriptions
- status
- expiry
- stock availability
- batch selection
- full/partial dispense
- dispense notes
- receipt/label placeholder

## 11.22 Billing and Cashier Screen

Requirements:

- invoice list
- invoice detail
- charges
- payments
- partial payments
- receipt
- reversal
- refund request
- cashier session status
- reconciliation report

## 11.23 Insurance Screen

Requirements:

- coverage details
- eligibility status
- preauthorization
- claims
- claim status timeline
- denial/query resolution
- minimum necessary clinical attachments

## 11.24 Inventory Screen

Requirements:

- item list
- stock levels
- batch list
- expiry alerts
- low-stock alerts
- stock movement ledger
- stock-in
- adjustment
- transfer
- recall
- destruction

## 11.25 Inpatient Ward Board

Requirements:

- ward overview
- bed status
- admitted patients
- pending transfers
- medications due
- critical vitals
- discharge candidates

## 11.26 Nursing Medication Administration Screen

Requirements:

- patient verification
- medication due list
- dose status
- given/refused/missed/held/delayed
- notes
- audit trail

## 11.27 Referral Screen

Requirements:

- create referral
- select target facility/specialty
- reason and urgency
- select records to share
- consent indicator
- send referral
- referral inbox
- accept/reject/request info
- feedback
- close referral

## 11.28 Document Upload Screen

Requirements:

- upload file
- scan/photo option later
- document type
- source
- date
- description
- verification status
- reviewer actions
- OCR status later

## 11.29 Reconciliation Workbench

Requirements:

- list cases
- filter by reason/severity/source
- case detail
- source payload
- patient candidates
- recommended action
- resolve/reject/escalate
- audit history

## 11.30 Integration Dashboard

Requirements:

- facility integration status
- API health
- last sync
- pending events
- failed events
- records pushed today
- records pulled today
- webhook delivery
- bridge agent status
- credential status

## 11.31 Webhook Management Screen

Requirements:

- subscriptions
- endpoint URL
- event types
- secret rotation
- test event
- delivery attempts
- failures
- pause/resume

## 11.32 Audit Log Screen

Requirements:

- search by patient
- search by actor
- search by facility
- search by action
- search by date
- filter emergency access
- filter exports
- view details
- export with strict permission

## 11.33 Country Config Screen

Requirements:

- country pack list
- policy versions
- age of consent
- guardian rules
- retention rules
- public health reporting rules
- language/currency
- publish with effective date
- impact report

## 11.34 Governance Review Screen

Requirements:

- emergency access review
- access abuse cases
- research requests
- breach reviews
- exception requests
- decisions
- comments
- approval/rejection
- expiry where applicable

---

# 12. Patient Portal UX

The patient portal must be simpler than staff portals.

Tone:

- plain language
- reassuring
- privacy-focused
- mobile-first

Main patient portal screens:

1. Home
2. My Health ID
3. Consent Requests
4. My Timeline
5. Lab Results
6. Prescriptions
7. Appointments
8. Access Logs
9. Dependents
10. Documents
11. Settings

## 12.1 Patient Home

Show:

- Health ID card
- active consent requests
- recent updates
- upcoming appointment
- recent lab result notice
- recent prescription
- access log summary

## 12.2 Consent Request Experience

Must show:

- who is requesting
- why they are requesting
- what they want to see
- how long access will last
- approve/deny
- simple explanation

Do not use technical terms like “scope” without explanation.

## 12.3 Access Log Experience

Show:

- date/time
- facility
- role/provider type
- purpose
- data category viewed
- emergency access flag if applicable
- report suspicious access button

## 12.4 Lab Results Experience

Lab results should be displayed carefully.

If result requires doctor explanation, show message such as:

“Your result is available. Please review with your healthcare provider.”

Do not use alarming language unnecessarily.

---

# 13. Provider Portal UX

Provider portal must prioritize clinical speed.

Provider must see:

- worklist
- next patient
- patient banner
- triage summary
- allergies
- active meds
- recent labs
- timeline
- note editor
- diagnosis
- orders
- prescriptions
- follow-up

Provider actions must be reachable within two clicks from patient workspace.

Critical information must be visible without scrolling:

- allergies
- critical alerts
- consent state
- Health ID
- patient identity

---

# 14. Staff Portal UX by Role

## 14.1 Reception UX

Reception interface must prioritize speed and duplicate prevention.

Key screens:

- quick search
- register patient
- check-in
- appointment list
- queue
- duplicate warning review

## 14.2 Nurse UX

Nurse interface must be touch-friendly.

Key screens:

- triage queue
- vitals capture
- ward list
- medications due
- nursing notes
- handover

## 14.3 Lab UX

Lab interface must prioritize specimen traceability.

Key screens:

- pending orders
- sample collection
- result entry
- validation queue
- critical results
- rejected specimens

## 14.4 Pharmacy UX

Pharmacy interface must prioritize prescription safety and stock visibility.

Key screens:

- prescription search
- active prescriptions
- allergy warning
- stock batch selection
- partial dispense
- dispense history

## 14.5 Cashier UX

Cashier interface must prioritize speed, accuracy, and reconciliation.

Key screens:

- invoice search
- payment capture
- receipt
- pending payments
- reversals/refunds
- cashier session

---

# 15. Clinical Safety UX Rules

These rules are mandatory.

## 15.1 Wrong Patient Prevention

Always show patient banner on patient-related screens.

Before high-risk actions, require identity confirmation.

High-risk actions:

- lab sample collection
- prescription issue
- dispensing
- admission
- discharge
- payment allocation
- document upload
- patient merge
- result release

## 15.2 Allergy Visibility

Allergy warning must be visible on:

- consultation screen
- prescription screen
- pharmacy dispense screen
- emergency profile
- admission screen

## 15.3 Consent Visibility

Consent state must be visible before viewing restricted records.

States:

- no consent
- pending consent
- granted
- expired
- revoked
- emergency override

## 15.4 Sync Visibility

Every externally synchronized record must show sync state:

- local only
- pending sync
- synced
- failed
- reconciliation required

## 15.5 Amendment Visibility

Amended records must clearly show:

- amended status
- original preserved
- amendment reason
- amendment date
- amended by

---

# 16. Data Density Rules

Healthcare dashboards need data, but not clutter.

Rules:

- Use summary cards for critical information.
- Use tables for operational queues.
- Use expandable panels for details.
- Use drawers for secondary context.
- Use tabs only when content is clearly separated.
- Avoid hiding critical safety information inside tabs.

Critical safety information must be immediately visible.

---

# 17. Forms UX Rules

Forms must be designed for speed and accuracy.

Rules:

- group related fields
- show required fields clearly
- use defaults where safe
- use search dropdowns for large lists
- show units beside clinical values
- validate inline
- preserve drafts where appropriate
- avoid losing data on navigation
- autosave long clinical notes where possible
- use confirmation before leaving unsaved forms

---

# 18. Error and Empty State UX

## 18.1 Error Messages

Error messages must explain:

- what happened
- why it happened
- what the user can do next

Examples:

Bad:

“Error.”

Good:

“This record cannot be opened because patient consent has expired. Request new consent to continue.”

## 18.2 Empty States

Empty states must be helpful.

Examples:

- “No lab results have been added yet.”
- “No active consent grants.”
- “No failed sync events.”
- “No patients are waiting in this queue.”

---

# 19. Notification UX Rules

Notifications must be safe and minimal.

For insecure channels such as SMS or email, do not reveal sensitive information.

Bad:

“Your HIV test result is positive.”

Good:

“You have a new health update in OpesCare. Please log in securely to view it.”

Notification categories:

- consent request
- lab result available
- prescription issued
- appointment reminder
- emergency access used
- record accessed
- sync failure for admins
- claim update
- referral update

---

# 20. Search UX

Search must be fast and safe.

Patient search should support:

- Health ID
- QR scan
- phone
- name/date of birth
- local facility ID
- insurance number where allowed

Search results must not reveal clinical details.

Potential duplicate matches must be clearly shown but privacy-safe.

---

# 21. Admin UX

Admin screens must support powerful operations safely.

Rules:

- dangerous actions require confirmation
- high-risk changes require reason
- some actions require two-person approval
- audit logs must be easy to inspect
- configuration changes must be versioned
- facility and integration status must be clear

---

# 22. Integration UX

Integration screens must make sync visible.

Integration dashboard must show:

- connection status
- last sync
- failed events
- pending events
- API health
- webhook health
- bridge agent health
- credential expiry
- rate-limit warnings
- reconciliation cases

Failed sync must never disappear silently.

---

# 23. Offline and Downtime UX

When offline:

- show offline banner
- show what functions are available
- show what cannot be done
- mark records as local only/pending sync
- prevent false synced status
- show queued events
- show sync result after reconnect

Do not make users think data has synchronized when it has not.

---

# 24. Page Performance Requirements

The UI must feel fast.

Targets:

- dashboard initial load should be optimized
- patient search should return quickly
- patient banner should load before non-critical widgets
- long timelines should use pagination or lazy loading
- large tables should use server-side pagination
- file uploads should show progress
- background operations should show status

Do not load entire patient history when only summary is needed.

---

# 25. Audit-Aware UX

For sensitive actions, show audit notice.

Examples:

- emergency access
- patient merge
- patient unmerge
- record export
- consent override
- role elevation
- payment reversal
- stock adjustment
- facility suspension

Message example:

“This action will be recorded in the audit log with your user account, facility, timestamp, and reason.”

---

# 26. UI Permissions and Visibility Rules

The UI must not show actions the user cannot perform, except where showing disabled action helps explain permission limits.

If disabled, show reason:

- “Requires patient consent.”
- “Only a lab validator can release results.”
- “Facility is suspended.”
- “Prescription has expired.”
- “You do not have permission to reverse payments.”

Backend permissions must still enforce all rules. UI hiding is not security.

---

# 27. MVP UI Scope

The MVP UI should include:

1. login
2. facility selector
3. staff dashboard shell
4. role-aware sidebar/topbar
5. facility management basics
6. staff management basics
7. patient registration
8. patient search
9. Health ID/QR screen
10. patient profile
11. consent request/grant screens
12. emergency access screen
13. check-in screen
14. queue screen
15. triage screen
16. consultation workspace
17. clinical timeline
18. prescription screen
19. lab result upload/validation screen
20. billing basics
21. patient portal basics
22. audit log screen
23. reconciliation workbench skeleton
24. integration dashboard skeleton
25. notification center

Do not attempt to design every advanced screen before MVP if it slows down core safety workflows. But the design system must support future expansion.

---

# 28. Future UI Scope

Future UI modules:

- full inpatient ward board
- medication administration record
- full inventory dashboard
- insurance claims workbench
- public health dashboards
- research request portal
- telemedicine interface
- device monitoring dashboard
- bridge agent dashboard
- developer portal
- country policy management advanced screen
- governance committee dashboard

---

# 29. Acceptance Criteria for UI/UX Implementation

A UI implementation is acceptable only if:

1. patient banner appears on every patient-related screen
2. role-based navigation is implemented
3. consent state is visible before restricted access
4. emergency access requires reason and audit warning
5. allergies are visible during consultation and prescribing
6. status badges are consistent
7. dangerous actions require confirmation
8. forms validate clearly
9. mobile patient portal is usable
10. tables become cards on mobile
11. audit-sensitive actions display audit notice
12. sync failures are visible
13. unverified documents are visually distinct
14. amended records are visually distinct
15. UI does not expose unauthorized actions/data
16. empty states are helpful
17. loading states prevent duplicate submission
18. errors explain next action
19. design is consistent across modules
20. UI works across desktop, tablet, and mobile where required

---

# 30. Design QA Checklist

Before any UI PR is accepted, answer:

1. Is the right user role targeted?
2. Is patient identity visible where needed?
3. Is consent state visible where needed?
4. Are dangerous actions confirmed?
5. Are errors clear?
6. Are empty states useful?
7. Are loading states present?
8. Does mobile layout work?
9. Are tables responsive?
10. Are statuses consistent?
11. Are icons consistent?
12. Is sensitive information protected?
13. Are unauthorized actions hidden or disabled with reason?
14. Is the audit implication clear?
15. Does the page avoid clutter?
16. Is the primary action obvious?
17. Does the flow reduce wrong-patient risk?
18. Does the screen match the PRD flow?
19. Are accessibility basics met?
20. Are screenshots included in the PR?

---

# 31. First UI Task for Jules or Coding Agent

Use this exact task for the first UI implementation:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, and docs/UIUX_PRODUCT_INTERFACE_PRD.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy any OpesHIS OS layout, UI, module structure, database structure, or code.

Task: Create the initial OpesCare design system and application shell.

Scope:
1. Create base layout shell with topbar, sidebar, and main content area.
2. Create role-aware navigation configuration, but only placeholder roles for now.
3. Create reusable UI components: Button, Input, Card, Badge, Alert, Table wrapper, Modal, Drawer, EmptyState, LoadingState, PatientBanner placeholder, ConsentStatusBadge, SyncStatusBadge.
4. Create design token file for colors, spacing, radius, typography, shadows, and status colors.
5. Create sample dashboard page showing cards and placeholder widgets.
6. Create responsive behavior for desktop, tablet, and mobile.
7. Add accessibility basics: focus states, labels, contrast-safe classes.
8. Do not implement real patient logic yet.
9. Do not implement backend clinical logic yet.
10. Open a PR with screenshots, component list, design decisions, and known limitations.
```

---

# 32. Final UI/UX Position

OpesCare’s UI must be calm, clinical, fast, safe, and trustworthy.

It must not be a generic admin dashboard.

It must actively prevent mistakes.

It must make identity, consent, allergies, status, source, audit, and sync state visible at the right time.

The design is not decoration. The design is part of patient safety.



---

# 33. Bilingual Platform Requirement: English and French

OpesCare must be built as a bilingual platform from the beginning.

Supported MVP languages:

1. English
2. French

English and French must be treated as first-class languages across the product, not as a later patch.

The system must support bilingual UI text, navigation, forms, validation messages, notifications, consent screens, patient-facing content, staff dashboards, admin screens, reports, and public-facing product pages where applicable.

---

## 33.1 Core Language Rule

All user-facing text must use translation keys.

Do not hard-code English or French strings directly inside views, components, controllers, JavaScript, Vue, React, Blade templates, validation messages, email templates, SMS templates, or notification templates.

Bad:

```text
Register Patient
```

Good:

```text
__('patients.actions.register_patient')
```

or equivalent frontend i18n key:

```text
t('patients.actions.register_patient')
```

---

## 33.2 Default Language

The platform should support a configurable default language.

Recommended defaults:

- system default: English
- user preference: English or French
- facility default language: English or French
- patient portal preference: English or French

A user should be able to switch between English and French without losing their workflow state.

---

## 33.3 Language Selector UX

A language selector must be available in:

- login screen
- patient portal
- staff user profile/settings
- public pages where applicable

For authenticated staff, selected language should be stored in the user profile.

For patients, selected language should be stored in patient portal preferences.

For unauthenticated users, selected language may be stored in session/cookie.

Language selector labels:

```text
English
Français
```

Do not use flag icons as the main language selector because language is not the same as country.

---

## 33.4 Translation File Structure

Laravel backend should use structured translation files.

Recommended backend structure:

```text
resources/lang/en/
├── common.php
├── auth.php
├── validation.php
├── patients.php
├── facilities.php
├── consent.php
├── emergency.php
├── encounters.php
├── triage.php
├── laboratory.php
├── prescriptions.php
├── pharmacy.php
├── billing.php
├── insurance.php
├── inventory.php
├── referrals.php
├── integrations.php
├── audit.php
├── notifications.php
└── errors.php

resources/lang/fr/
├── common.php
├── auth.php
├── validation.php
├── patients.php
├── facilities.php
├── consent.php
├── emergency.php
├── encounters.php
├── triage.php
├── laboratory.php
├── prescriptions.php
├── pharmacy.php
├── billing.php
├── insurance.php
├── inventory.php
├── referrals.php
├── integrations.php
├── audit.php
├── notifications.php
└── errors.php
```

If the frontend is React or Vue, frontend translations should use a matching namespace structure:

```text
resources/js/i18n/en/*.json
resources/js/i18n/fr/*.json
```

or a similar structured i18n directory.

---

## 33.5 Required Translation Areas

The following must be translatable:

- navigation labels
- page titles
- dashboard widgets
- buttons
- form labels
- placeholders
- helper text
- validation errors
- system errors
- empty states
- loading states
- status badges
- confirmation dialogs
- audit notices
- consent explanations
- emergency access warnings
- patient instructions
- SMS templates
- email templates
- in-app notifications
- report labels
- exported PDF labels
- printable forms
- patient card text where applicable
- onboarding text
- tooltips
- table headers
- filter labels
- API error messages where user-facing

---

## 33.6 Clinical Safety Translation Rules

Translations must not change the medical meaning of labels, alerts, or instructions.

High-risk terms must be reviewed carefully in both languages.

Examples of high-risk terms:

- Allergy
- Critical result
- Emergency access
- Consent revoked
- Prescription cancelled
- Prescription expired
- Partially dispensed
- Fully dispensed
- Lab result amended
- Patient deceased
- Recalled batch
- Quarantined stock
- Payment reversed
- Record entered in error
- Reconciliation required

French translations must be clinically and operationally clear, not casual.

Examples:

```text
Allergy → Allergie
Critical result → Résultat critique
Emergency access → Accès d’urgence
Consent revoked → Consentement révoqué
Prescription cancelled → Ordonnance annulée
Prescription expired → Ordonnance expirée
Partially dispensed → Partiellement délivrée
Fully dispensed → Entièrement délivrée
Lab result amended → Résultat de laboratoire modifié
Patient deceased → Patient décédé
Recalled batch → Lot rappelé
Quarantined stock → Stock mis en quarantaine
Payment reversed → Paiement contrepassé
Record entered in error → Dossier saisi par erreur
Reconciliation required → Rapprochement requis
```

---

## 33.7 Bilingual Consent UX

Consent is a legal and clinical safety area. It must be understandable in both English and French.

Consent screens must show:

- requesting facility
- requesting provider or role
- purpose of access
- data requested
- expiry period
- approve button
- deny button
- plain-language explanation

English example:

```text
Hospital A is requesting access to your recent clinical summary, allergies, current medications, and recent lab results for consultation. This access will expire in 4 hours.
```

French example:

```text
L’Hôpital A demande l’accès à votre résumé clinique récent, vos allergies, vos médicaments actuels et vos résultats de laboratoire récents pour une consultation. Cet accès expirera dans 4 heures.
```

The patient must be able to view the consent request in their preferred language before approving.

---

## 33.8 Bilingual Emergency Access UX

Emergency access warnings must be clear in both languages.

English:

```text
Emergency access is audited. You must provide a reason before viewing this patient’s emergency profile.
```

French:

```text
L’accès d’urgence est audité. Vous devez fournir une raison avant de consulter le profil d’urgence de ce patient.
```

Emergency profile labels must be bilingual:

```text
Blood group → Groupe sanguin
Critical allergies → Allergies critiques
Chronic conditions → Maladies chroniques
High-risk medications → Médicaments à haut risque
Emergency contacts → Contacts d’urgence
```

---

## 33.9 Bilingual Patient Portal

The patient portal must be fully bilingual.

Patient-facing French must be simple and clear.

Avoid overly technical French when the patient needs plain understanding.

Examples:

```text
My Health ID → Mon identifiant de santé
My Timeline → Mon historique médical
Consent Requests → Demandes de consentement
Who viewed my records → Qui a consulté mon dossier
Lab Results → Résultats de laboratoire
Prescriptions → Ordonnances
Dependents → Personnes à charge
Access Logs → Journal d’accès
```

---

## 33.10 Bilingual Staff Portal

Staff portal language must be operational and precise.

Examples:

```text
Patient Search → Recherche de patient
Register Patient → Enregistrer un patient
Check In → Enregistrer l’arrivée
Triage Queue → File de triage
Consultation Notes → Notes de consultation
Pending Lab Orders → Demandes de laboratoire en attente
Result Validation → Validation des résultats
Dispense Medication → Délivrer le médicament
Invoice → Facture
Receipt → Reçu
Reconciliation Queue → File de rapprochement
Audit Logs → Journaux d’audit
```

---

## 33.11 Bilingual Validation Messages

All validation messages must exist in English and French.

Examples:

English:

```text
The patient date of birth is required.
The Health ID format is invalid.
Consent is required before viewing this record.
This prescription has expired and cannot be dispensed.
```

French:

```text
La date de naissance du patient est obligatoire.
Le format de l’identifiant de santé est invalide.
Le consentement est requis avant de consulter ce dossier.
Cette ordonnance a expiré et ne peut pas être délivrée.
```

---

## 33.12 Bilingual Notifications

Notification templates must exist in English and French.

Notifications must respect privacy in both languages.

English safe message:

```text
You have a new health update in OpesCare. Please log in securely to view it.
```

French safe message:

```text
Vous avez une nouvelle mise à jour de santé dans OpesCare. Veuillez vous connecter de manière sécurisée pour la consulter.
```

Do not reveal sensitive diagnoses, lab details, or prescriptions in SMS or email unless policy explicitly allows it.

---

## 33.13 Bilingual Reports and Printable Documents

Reports and printable documents must support English and French.

This includes:

- Health ID card
- appointment slip
- invoice
- receipt
- prescription printout where applicable
- lab result printout
- discharge summary
- referral letter
- consent form
- patient summary export

Each generated document should use the language selected by the user or facility policy.

---

## 33.14 Date, Time, Number, and Currency Localization

The platform must support localization of:

- dates
- times
- numbers
- currency
- pluralization

Do not concatenate translated strings manually.

Bad:

```text
3 + " days remaining"
```

Good:

Use translation pluralization rules.

Examples:

English:

```text
1 day remaining
3 days remaining
```

French:

```text
1 jour restant
3 jours restants
```

Currency display should be configurable by country/facility policy.

---

## 33.15 Database Content Translation

System interface text must use translation files.

Database-driven content may need translation support where relevant.

Examples:

- service names
- form templates
- notification templates
- patient education content
- public health labels
- report names
- country policy descriptions

Recommended pattern for translatable database fields:

```json
{
  "name": {
    "en": "Consultation",
    "fr": "Consultation"
  }
}
```

or a normalized translations table if the content becomes large.

Do not translate clinical free-text notes automatically inside the MVP. Doctor-entered notes should remain in the language entered unless a future translation feature is explicitly designed and clinically reviewed.

---

## 33.16 Search in a Bilingual System

Search should work across English and French labels where applicable.

Examples:

A user searching “allergy” or “allergie” should find the allergy module.

A user searching “invoice” or “facture” should find billing invoices.

Patient names should not be translated.

Clinical codes and terminology should support multilingual display labels later where available.

---

## 33.17 API Language Handling

External APIs should support language preference for user-facing messages.

Recommended header:

```text
Accept-Language: en
Accept-Language: fr
```

API error responses can return user-facing messages in the requested language, but error codes must remain stable and language-independent.

Example:

```json
{
  "status": "rejected",
  "error_code": "CONSENT_REQUIRED",
  "message": "Le consentement est requis avant de consulter ce dossier.",
  "required_action": "request_consent"
}
```

The `error_code` must not be translated.

---

## 33.18 Bilingual Design QA Checklist

Before any UI PR is accepted, verify:

1. No hard-coded user-facing strings.
2. English translation exists.
3. French translation exists.
4. Validation messages are translated.
5. Empty states are translated.
6. Buttons and status badges are translated.
7. Consent screens are translated.
8. Emergency access warnings are translated.
9. Notifications are translated.
10. PDF/export labels are translated where applicable.
11. Language selector works.
12. User language preference persists.
13. Switching language does not break layout.
14. French text does not overflow buttons/cards/tables.
15. Date/time formats are localized.
16. Pluralization works.
17. API error codes remain stable.
18. Sensitive SMS/email templates remain privacy-safe in both languages.
19. Clinical safety terms are reviewed.
20. Screenshots include both English and French for important UI pages.

---

## 33.19 First Bilingual Implementation Task for Jules or Coding Agent

Use this task before building large UI screens:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, and the bilingual requirements section.

We are building OpesCare from scratch.
Do not use OpesHIS OS.

Task: Add bilingual English/French foundation to the Laravel application and frontend structure.

Scope:
1. Add backend translation folders for English and French.
2. Add base translation files for common, auth, validation, patients, facilities, consent, emergency, encounters, laboratory, prescriptions, pharmacy, billing, integrations, audit, notifications, and errors.
3. Add frontend i18n structure if frontend framework exists.
4. Add a language selector component placeholder.
5. Add user language preference field placeholder or migration plan, but do not implement full user profile logic unless the auth module already exists.
6. Add middleware or design note for locale detection: user preference, session/cookie, Accept-Language fallback, system default.
7. Replace any scaffolded user-facing hard-coded strings with translation keys.
8. Add documentation explaining translation key naming conventions.
9. Add tests or checklist proving English and French translation files load.
10. Do not implement clinical modules yet.

Open a pull request with:
- summary
- files created/modified
- language fallback rules
- screenshots if UI exists
- limitations
- next recommended bilingual tasks
```

---

## 33.20 Clear Medical Language and Anti-Jargon Rule

OpesCare must use clear medical language.

The platform must not use unnecessary jargon, complicated technical words, vague system language, or confusing medical expressions when simple and accurate language can explain the same thing.

The system serves doctors, nurses, pharmacists, lab staff, cashiers, administrators, patients, guardians, insurers, and integration users. Not all users have the same technical or medical background. Patient-facing language must be especially simple, respectful, and easy to understand.

The rule is:

**Use clear, accurate, medically safe language. Do not use jargon unless the user role requires it and the meaning is clinically necessary.**

---

## 33.21 Language Levels by User Type

OpesCare must use different language depth depending on the user.

### Patient and Guardian Language

Patient-facing language must be simple, calm, and direct.

Use words a normal patient can understand.

Avoid technical words unless they are necessary. If a technical term is necessary, explain it in simple words.

Example:

Bad:

```text
Your longitudinal clinical record has been updated.
```

Good:

```text
Your medical history has been updated.
```

French bad:

```text
Votre dossier clinique longitudinal a été mis à jour.
```

French good:

```text
Votre historique médical a été mis à jour.
```

### Clinical Staff Language

Doctors, nurses, lab staff, and pharmacists can see standard medical terms where needed, but the interface must still be clear.

Use medically accurate labels, but avoid unnecessary complexity.

Example:

Bad:

```text
Pharmacotherapeutic intervention successfully instantiated.
```

Good:

```text
Prescription created successfully.
```

French good:

```text
Ordonnance créée avec succès.
```

### Administrative and Cashier Language

Billing, insurance, and admin language must be operationally clear.

Example:

Bad:

```text
Revenue event reconciliation artifact generated.
```

Good:

```text
Payment reconciliation report created.
```

French good:

```text
Rapport de rapprochement des paiements créé.
```

### Developer and Integration Language

Developer screens may use technical words such as API, webhook, sync, token, and endpoint, but every error message must still be clear and actionable.

Example:

Bad:

```text
Webhook dispatch failed due to non-2xx downstream response.
```

Good:

```text
Webhook delivery failed. The receiving system did not confirm the request. Check the endpoint and try again.
```

French good:

```text
L’envoi du webhook a échoué. Le système destinataire n’a pas confirmé la demande. Vérifiez l’endpoint et réessayez.
```

---

## 33.22 Forbidden Jargon and Preferred Replacements

The following words or phrases should not be used in patient-facing screens unless there is a strong reason and a simple explanation.

| Avoid | Use Instead |
|---|---|
| longitudinal clinical record | medical history |
| data subject | patient |
| clinical artifact | medical record / document |
| encounter | visit / consultation |
| care episode | visit / hospital stay |
| provenance | source / where this record came from |
| consent scope | what the provider can see |
| authorization grant | access permission |
| revocation | cancel access / remove access |
| interoperability | system connection / data sharing between systems |
| synchronization | update / sync where staff already understands it |
| reconciliation | review and match / fix unmatched records |
| adjudication | claim review |
| dispense event | medicine given / medicine dispensed |
| de-identification | remove personal details |
| PHI | patient health information |
| immutable | cannot be changed directly |
| amendment | correction / update with reason |
| idempotency | duplicate protection |
| endpoint | API address / connection point |

For staff and technical users, some of these words may be used where needed, but they must not make the interface unclear.

---

## 33.23 French Clear-Language Rules

French translations must not sound like machine-translated legal or technical text.

Use clear, natural, medically appropriate French.

Avoid overly complex French when a simple phrase is clearer.

Examples:

Bad:

```text
La révocation de l’autorisation d’accès a été effectuée.
```

Better:

```text
L’accès a été retiré.
```

Bad:

```text
L’événement clinique a été synchronisé avec succès.
```

Better for patients:

```text
Votre dossier médical a été mis à jour.
```

Better for staff:

```text
L’information médicale a été synchronisée avec succès.
```

Bad:

```text
Le processus d’adjudication de la réclamation est en cours.
```

Better:

```text
La demande de remboursement est en cours d’examen.
```

French must preserve medical meaning. Do not oversimplify in a way that changes the clinical meaning.

---

## 33.24 Required Tone

All OpesCare text must be:

- clear
- calm
- respectful
- medically accurate
- short where possible
- action-oriented
- free of unnecessary jargon
- consistent across modules

Avoid language that is:

- alarming without reason
- vague
- overly technical
- overly legalistic
- machine-translated
- casual or slang
- insulting or blaming
- ambiguous

Bad:

```text
You failed to provide consent.
```

Good:

```text
Access was not approved. The provider cannot view this record unless you approve access.
```

French good:

```text
L’accès n’a pas été approuvé. Le prestataire ne peut pas consulter ce dossier sans votre accord.
```

---

## 33.25 Medical Safety Terms That Must Stay Precise

Some medical terms must remain precise and must not be replaced with vague words.

Keep these terms clear and consistent:

- allergy / allergie
- diagnosis / diagnostic
- prescription / ordonnance
- lab result / résultat de laboratoire
- blood group / groupe sanguin
- pregnancy / grossesse
- chronic disease / maladie chronique
- emergency / urgence
- critical result / résultat critique
- consent / consentement
- medical history / historique médical
- discharge summary / résumé de sortie
- referral / orientation médicale or référence médicale, depending on final terminology decision

If a term has legal or clinical importance, keep the accurate term and add explanation if needed.

Example patient-facing message:

```text
Allergy means a medicine, food, or substance that can cause a harmful reaction in your body.
```

French:

```text
Une allergie est une réaction dangereuse que votre corps peut avoir face à un médicament, un aliment ou une substance.
```

---

## 33.26 Jargon Protection Rule for AI Agents and Developers

Every AI agent, developer, and reviewer working on OpesCare must follow this rule:

**Do not write user-facing text with unnecessary jargon. If a simpler medical or operational phrase exists, use the simpler phrase.**

Before adding any new UI text, validation message, notification, tooltip, PDF label, consent explanation, or patient-facing instruction, the implementer must ask:

1. Can a patient or non-technical staff member understand this?
2. Is this wording medically accurate?
3. Is there a simpler word that preserves the meaning?
4. Does this wording sound machine-translated?
5. Does the French version sound natural and clear?
6. Does the text avoid blame or fear?
7. Does the message tell the user what to do next?

If the answer is not clear, rewrite the text.

---

## 33.27 Translation Review Workflow

All English and French text must pass a translation review before release.

Review levels:

### Level 1: General UI Review

For navigation, buttons, empty states, dashboards, and simple forms.

Reviewer checks:

- clarity
- consistency
- spelling
- layout fit
- no hard-coded strings
- English/French completeness

### Level 2: Medical Safety Review

Required for:

- allergies
- prescriptions
- lab results
- emergency access
- diagnoses
- discharge summaries
- patient warnings
- consent text
- critical alerts

Reviewer checks:

- clinical accuracy
- no dangerous simplification
- correct French medical wording
- consistent terms across modules

### Level 3: Legal/Consent Review

Required for:

- consent text
- privacy notices
- data access explanations
- research access
- emergency override explanations
- patient rights text

Reviewer checks:

- meaning is clear
- patient rights are understandable
- no misleading promise
- English and French say the same thing

---

## 33.28 Translation Key Naming Rules

Translation keys must be organized and predictable.

Use module-based keys.

Examples:

```text
patients.actions.register
patients.status.verified
patients.status.provisional
consent.messages.request_patient
consent.actions.approve
consent.actions.deny
emergency.warnings.audit_notice
laboratory.status.pending_validation
prescriptions.errors.expired
billing.actions.reverse_payment
integrations.status.sync_failed
```

Do not use vague keys like:

```text
message1
button_text
label_new
error_general
```

Translation keys must describe the meaning, not the current English text.

---

## 33.29 Layout Protection for French Text

French text is often longer than English. The UI must be designed to handle longer French labels without breaking.

Rules:

- buttons must allow reasonable text width
- cards must not overflow
- tables must handle longer headers
- status badges must remain readable
- side navigation must support longer French labels
- mobile screens must wrap text cleanly
- do not use fixed widths that break in French

Every important UI screen must be tested in both English and French.

Examples:

English:

```text
Reconciliation Required
```

French:

```text
Rapprochement requis
```

English:

```text
Request Consent
```

French:

```text
Demander le consentement
```

The French text may be longer. The component must not break.

---

## 33.30 Bilingual Glossary Control

Create a controlled bilingual glossary before major UI development.

Recommended file:

```text
docs/product/BILINGUAL_GLOSSARY.md
```

The glossary must include approved English and French terms for:

- patient
- Health ID
- medical history
- consent
- emergency access
- allergy
- diagnosis
- prescription
- lab result
- pharmacy
- billing
- invoice
- receipt
- claim
- insurance
- referral
- sync
- reconciliation
- audit log
- access log
- guardian
- dependent
- facility
- practitioner
- encounter/visit
- discharge
- admission
- stock batch
- recalled batch
- critical result

All UI translations must follow the glossary.

If a term changes, update the glossary and translation files together.

---

## 33.31 Examples of Clear English and French Messages

### Consent Required

English:

```text
Consent is required before this record can be viewed.
```

French:

```text
Le consentement est requis avant de consulter ce dossier.
```

### Access Removed

English:

```text
Access has been removed. This facility can no longer view this record unless new consent is granted.
```

French:

```text
L’accès a été retiré. Cet établissement ne peut plus consulter ce dossier sans un nouveau consentement.
```

### Emergency Access Warning

English:

```text
Emergency access is audited. Enter the reason before viewing this patient’s emergency profile.
```

French:

```text
L’accès d’urgence est audité. Indiquez la raison avant de consulter le profil d’urgence de ce patient.
```

### Prescription Expired

English:

```text
This prescription has expired and cannot be dispensed.
```

French:

```text
Cette ordonnance a expiré et ne peut pas être délivrée.
```

### Lab Result Amended

English:

```text
This lab result was corrected. The original result is kept for audit.
```

French:

```text
Ce résultat de laboratoire a été corrigé. Le résultat initial est conservé pour l’audit.
```

### Sync Failed

English:

```text
Sync failed. The record has not been added to OpesCare yet. Review the error and try again.
```

French:

```text
La synchronisation a échoué. Le dossier n’a pas encore été ajouté à OpesCare. Vérifiez l’erreur et réessayez.
```

### Patient Not Found

English:

```text
No patient was found with these details. Check the information or register a new patient if needed.
```

French:

```text
Aucun patient n’a été trouvé avec ces informations. Vérifiez les informations ou enregistrez un nouveau patient si nécessaire.
```

---

## 33.32 Anti-Jargon QA Checklist

Before any translation or UI text is approved, check:

1. Is the message clear in English?
2. Is the message clear in French?
3. Is the French natural, not machine-translated?
4. Is there any unnecessary jargon?
5. Can the jargon be replaced with a simpler phrase?
6. If medical terminology is used, is it necessary?
7. Is the medical meaning preserved?
8. Does the user know what action to take next?
9. Is the message respectful and calm?
10. Does the message avoid blame?
11. Does it avoid revealing sensitive information unnecessarily?
12. Does it match the approved glossary?
13. Does it fit in the UI without breaking layout?
14. Does it work for mobile screens?
15. Does it work for both staff and patient context?

If any answer fails, rewrite the text before implementation.

---

## 33.33 Updated First Bilingual Implementation Task for Jules or Coding Agent

Use this task before building large UI screens:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, and the bilingual requirements section.

We are building OpesCare from scratch.
Do not use OpesHIS OS.

Task: Add bilingual English/French foundation to the Laravel application and frontend structure, with clear-language and anti-jargon protection.

Scope:
1. Add backend translation folders for English and French.
2. Add base translation files for common, auth, validation, patients, facilities, consent, emergency, encounters, laboratory, prescriptions, pharmacy, billing, integrations, audit, notifications, and errors.
3. Add frontend i18n structure if frontend framework exists.
4. Add a language selector component placeholder.
5. Add user language preference field placeholder or migration plan, but do not implement full user profile logic unless the auth module already exists.
6. Add middleware or design note for locale detection: user preference, session/cookie, Accept-Language fallback, system default.
7. Replace any scaffolded user-facing hard-coded strings with translation keys.
8. Add docs/product/BILINGUAL_GLOSSARY.md with approved English and French terms.
9. Add docs/product/CLEAR_LANGUAGE_GUIDE.md with anti-jargon rules for English and French.
10. Add documentation explaining translation key naming conventions.
11. Add tests or checklist proving English and French translation files load.
12. Add a QA checklist that blocks jargon-heavy UI text.
13. Do not implement clinical modules yet.

Open a pull request with:
- summary
- files created/modified
- language fallback rules
- glossary terms added
- clear-language rules added
- screenshots if UI exists
- limitations
- next recommended bilingual tasks
```

---

## 33.34 Final Bilingual and Clear-Language Requirement

OpesCare must be bilingual by architecture, not by last-minute translation.

Every screen, form, status, notification, consent request, emergency warning, validation error, and patient-facing message must support English and French from the beginning.

The platform must use clear medical language.

Do not use unnecessary jargon.

Do not use technical words to sound advanced.

Use the simplest accurate phrase that protects medical meaning.

The interface must help people understand what is happening, what it means, and what they should do next.

