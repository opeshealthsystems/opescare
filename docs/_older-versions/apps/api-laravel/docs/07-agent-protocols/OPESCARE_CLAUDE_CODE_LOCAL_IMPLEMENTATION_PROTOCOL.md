# OPESCARE Claude Code Local Implementation Protocol

**Project:** OpesCare  
**Tool:** Claude Code  
**Purpose:** Controlled local implementation protocol for building missing OpesCare modules without breaking, duplicating, overwriting, or replacing existing working modules.  
**Environment:** Local desktop development  
**Repository:** Current local OpesCare repository  
**Primary Stack:** Laravel + PostgreSQL  
**Important Rule:** Claude Code is the primary builder, but it must audit before coding and must never rebuild working modules blindly.

---

# 1. Claude Code Role

Claude Code should act as the **implementation builder and patching agent**.

Claude Code is responsible for:

```text
reading existing code
auditing current implementation
identifying missing files/flows
patching partial modules
building missing modules
adding migrations carefully
adding tests
updating docs
running local tests
summarizing changes
```

Claude Code must **not** act like a free creative agent. It must follow the existing architecture, documents, audit reports, and build order.

---

# 2. Main Rule

Claude Code must never start by coding.

Every task must begin with:

```text
1. Inspect existing implementation.
2. Search for existing models, migrations, controllers, routes, services, policies, views, tests, and docs.
3. Report what exists.
4. Report what is missing.
5. Propose a minimal patch plan.
6. Only then implement.
```

If a module already exists, Claude Code must extend or patch it. It must not create duplicates.

---

# 3. Source of Truth Documents

Before implementing, Claude Code must read the relevant source documents.

Use these as the source of truth:

```text
PROJECT_KNOWLEDGE.md
PRD.md
UIUX_PRODUCT_INTERFACE_PRD.md
OPESCARE_COMPLETE_MASTER_PROMPT_V3_FULL_FLOWS.md
OPESCARE_MISSING_OPERATIONAL_MODULES_COMPLETE_IMPLEMENTATION.md
OPESCARE_CONNECT_SUITE_INDUSTRY_LEADER_API_SDK_WIDGET_BRIDGE_LITE_WEBHOOKS.md
OPESCARE_PRODUCTION_LAUNCH_GOVERNANCE_COMPLIANCE_AND_DEPLOYMENT_MASTER_PLAN.md
OPESCARE_STRATEGIC_MATURITY_STANDARDS_DATA_DICTIONARY_QA_AND_SCALE_MASTER_PACK.md
latest audit reports
```

If these files are not yet inside the repository, create:

```text
docs/product/
docs/audit/
docs/implementation/
docs/connect/
docs/production/
docs/maturity/
```

Then place them there.

---

# 4. Do Not Break Existing Modules

Claude Code must not break or rewrite modules already implemented.

Protected modules include:

```text
Identity / Authentication
Roles / Permissions / Facilities
Health ID / Medical ID
Patient Profile / EMR foundation
Consent / Privacy / Access Control
Partner Governance
API / SDK / Connect Widget foundation
Notifications / Alerts / Messaging
Verifiable Documents
Demo Access
Pharmacy Stock
Blood Availability
Public Health Reporting
Certification / Academy
Verified Care Access Map
Audit foundation
Data Quality / Reconciliation foundation
Bilingual foundation
```

If a protected module needs changes, Claude Code must:

```text
explain why the change is needed
patch only the missing part
preserve existing tests
add regression tests
avoid renaming existing tables/classes unless absolutely necessary
```

---

# 5. Duplicate Prevention Rules

Before creating any file, Claude Code must search for similar existing code.

Search for:

```text
model name
table name
migration name
controller name
service name
policy name
route path
view/component name
test file
factory/seeder
permission name
```

If something similar exists, reuse it.

Claude Code must not create duplicate versions like:

```text
AppointmentNew
AppointmentModule
AppointmentV2
PatientVisitNew
BillingInvoice2
NewHealthId
```

unless there is a clear migration/refactor plan and explicit approval.

---

# 6. Local Git Rules

Before starting work:

```bash
git status
```

If there are uncommitted user changes, Claude Code must not overwrite them.

Claude Code should create a branch per task:

```bash
git checkout -b feature/opescare-appointments
git checkout -b feature/opescare-queue
git checkout -b feature/opescare-billing
git checkout -b fix/opescare-consent-regression
```

Commit only after tests pass.

Commit format:

```text
feat(appointments): implement booking and check-in flow
feat(queue): add patient queue transfer workflow
feat(billing): add invoices payments and receipts
fix(consent): enforce revoked consent on provider access
test(visit): add end-to-end patient visit flow coverage
docs(connect): update API scope matrix
```

---

# 7. Build Order for Claude Code

Claude Code must build in this order unless instructed otherwise:

```text
Phase 0 — Repository audit and setup
Phase 1 — Core module verification
Phase 2 — Appointments & Booking
Phase 3 — Queue & Patient Flow
Phase 4 — Billing, Payments & Wallet
Phase 5 — Insurance Claims & Preauthorization
Phase 6 — End-to-End Patient Visit Flow
Phase 7 — Support / Helpdesk
Phase 8 — Data Import / Migration
Phase 9 — Master Admin Control Center
Phase 10 — Facility Go-Live Readiness
Phase 11 — Global Search
Phase 12 — Staff / HR / Shift Management
Phase 13 — Triage & Emergency Workflow
Phase 14 — Inventory & Supply Chain
Phase 15 — File Storage & Medical Attachments
Phase 16 — Analytics & Reporting
Phase 17 — API / SDK / Widget / Webhooks hardening
Phase 18 — Bridge Agent
Phase 19 — OpesCare Lite
Phase 20 — QA, security, production readiness
```

Do not jump to Bridge Agent, OpesCare Lite, CDSS, or telemedicine before the operational core works.

---

# 8. Module Completion Rule

A module is complete only when it has:

```text
models
migrations
services
controllers/API handlers
routes
UI pages/components or API contract
policies/permissions
request validation
audit events
notifications where needed
bilingual labels where user-facing
factories/seeders/demo data where useful
feature tests
unit tests where useful
documentation update
```

If any part is missing, mark it as:

```text
PARTIAL
```

Do not call it complete.

---

# 9. Claude Code Task Template

Use this prompt for every module:

```text
You are working in the local OpesCare Laravel repository.

Task:
Implement or patch the [MODULE NAME] module.

Important:
Do not rebuild working modules.
Do not duplicate existing models, migrations, routes, controllers, services, views, policies, permissions, or tests.
Audit existing implementation first.
Report what exists and what is missing before coding.
Preserve passing tests.
Follow the OpesCare source-of-truth documents in docs/.
Implement only the missing flows.

Required output before coding:
1. Existing files found
2. Missing pieces
3. Patch/build plan
4. Risks
5. Tests to add

Then implement:
- models/migrations only if missing
- services/business logic
- controllers/API routes
- UI if required
- permissions/policies
- audit logs
- notifications where needed
- bilingual labels
- tests
- docs update

After implementation:
Run tests.
Report files changed.
Report tests run.
Report remaining gaps.
```

---

# 10. First Claude Code Task: Repository Audit

Run this before any implementation:

```text
Audit the current OpesCare repository.

Do not modify code.

Find:
1. Existing modules
2. Existing models
3. Existing migrations
4. Existing routes
5. Existing controllers
6. Existing services
7. Existing policies/permissions
8. Existing views/components
9. Existing tests
10. Existing docs
11. Missing operational modules
12. Duplicate or conflicting structures
13. Failing tests
14. Uncommitted changes

Create:
docs/audit/LOCAL_REPOSITORY_IMPLEMENTATION_BASELINE_AUDIT.md

Do not change anything else.
```

---

# 11. Second Claude Code Task: Appointments Module

Use after baseline audit:

```text
Implement Appointments & Booking only.

Do not touch billing, queue, insurance, or EMR except for safe integration points.

Required flows:
- patient books appointment
- staff books appointment for patient
- provider availability
- facility schedule
- appointment confirmation
- reschedule
- cancellation with reason
- reminders
- check-in link to visit/queue placeholder
- no-show tracking

Before coding:
Audit whether appointments already exists.

After coding:
Add tests for booking, conflict prevention, reschedule, cancellation, reminder, check-in, no-show, permissions, and audit logs.
```

---

# 12. Third Claude Code Task: Queue Module

```text
Implement Queue & Patient Flow only.

Required flows:
- patient arrival/check-in
- queue ticket generation
- station queues
- call patient
- start service
- transfer between stations
- emergency priority bypass
- public masked display
- queue completion
- link queue events to visit timeline

Do not expose patient full name on public display.
Add tests for privacy, transfer, priority override, facility boundary, and audit logs.
```

---

# 13. Fourth Claude Code Task: Billing Module

```text
Implement Billing, Payments & Wallet only.

Required flows:
- invoice creation
- invoice items
- issue invoice
- cash payment
- mobile/card/bank payment placeholder
- receipt generation
- refund with reason
- wallet deposit
- wallet payment
- cashier closeout
- reconciliation placeholder
- audit logs

Do not mix SaaS subscription billing with patient billing.
Add tests for amount validation, payment-receipt linkage, refund limits, wallet balance, permissions, and audit logs.
```

---

# 14. Fifth Claude Code Task: Insurance Module

```text
Implement Insurance Claims & Preauthorization.

Required flows:
- patient insurance policy
- eligibility check
- preauthorization request
- payer decision
- claim creation from invoice
- claim submission
- missing information request
- claim approval/rejection
- claim payment posting
- minimum necessary data access

Insurance users must not access full EMR by default.
Add tests for minimum necessary access and claim document audit.
```

---

# 15. Sixth Claude Code Task: End-to-End Visit Flow

```text
Implement the end-to-end visit integration.

Required flow:
appointment -> check-in -> queue -> triage placeholder or full triage if available -> consultation/encounter -> lab/prescription placeholder if needed -> invoice -> payment -> receipt -> document -> notification -> visit closure -> audit trail.

Do not rewrite existing EMR/document/notification modules.
Connect them safely.

Add a full feature test for the entire patient journey.
```

---

# 16. Required Test Commands

After each task, run:

```bash
php artisan test
php artisan route:list
php artisan migrate:fresh --seed --env=testing
```

If frontend exists:

```bash
npm run build
npm run test
```

If tests fail:

```text
do not continue to next module
identify failing test
fix regression
rerun tests
document cause
```

---

# 17. Migration Safety Rules

Before adding migrations:

```text
check existing migrations
check table exists
check column exists
avoid destructive migrations
do not rename/drop columns without explicit migration plan
use nullable/backfill pattern for existing data
write rollback method where possible
```

Never run destructive commands on production.

Local only:

```bash
php artisan migrate:fresh --seed
```

Production later:

```bash
php artisan migrate --force
```

---

# 18. UI Safety Rules

When changing UI:

```text
preserve existing layout
use existing design system
use Lucide icons, not emoji icons
keep English/French labels
add empty states
add loading states
add error states
mobile responsive
do not break existing dashboards
```

---

# 19. Documentation Update Rule

Every module implementation must update docs:

```text
docs/modules/[module].md
docs/audit/[module]_implementation_status.md
docs/testing/[module]_test_plan.md
```

Include:

```text
implemented flows
missing flows
routes
permissions
audit events
tests
known risks
```

---

# 20. Change Summary Format

After every task, Claude Code must output:

```text
Task:
Branch:
Summary:
Files changed:
Migrations added:
Routes added:
Permissions added:
Audit events added:
Tests added:
Tests run:
Test result:
Risks:
Remaining gaps:
Next recommended task:
```

---

# 21. Stop Conditions

Claude Code must stop and ask for instruction if:

```text
existing implementation conflicts with documents
migration would destroy data
duplicate module exists but unclear which one is canonical
tests fail and cause is unclear
security/privacy behavior is ambiguous
module requires business decision
```

---

# 22. Final Rule

Claude Code is the builder, but not the boss.

It must build in controlled phases, preserve existing modules, and never mark anything complete without tests, audit logs, permissions, and documentation.
