# OpesCare Local Implementation Start Protocol

**Project:** OpesCare  
**Purpose:** Final pre-implementation setup document for local development with Claude Code and Codex.  
**Environment:** Local desktop development  
**Primary Tools:** Claude Code and Codex  
**Goal:** Organize all OpesCare source-of-truth documents and begin implementation safely without breaking, duplicating, or overwriting existing modules.

---

# 1. Why This Document Exists

Before starting implementation, OpesCare needs a clean execution structure inside the repository so Claude Code and Codex know exactly:

```text
where documents belong
which documents are source of truth
where audits should be stored
where implementation reports should go
how to begin safely
which agent does what
what not to modify first
```

This document should be added to the repository before any new module implementation begins.

---

# 2. Important Rule

Do not start by building modules.

The first step must be:

```text
repository audit
baseline review
duplicate detection
migration review
test review
missing-module confirmation
```

Only after Claude Code and Codex finish the baseline audit should implementation begin.

---

# 3. Recommended Documentation Folder Structure

Create this structure in the local OpesCare repository:

```text
/docs
  /00-source-of-truth
  /01-audits
  /02-implementation
  /03-operational-modules
  /04-connect-suite
  /05-production-launch
  /06-maturity-scale
  /07-agent-protocols
  /08-reports
```

---

# 4. File Placement Structure

Place the project files into the correct folders.

## 4.1 Source of Truth

```text
/docs/00-source-of-truth/
PROJECT_KNOWLEDGE.md
PRD.md
UIUX_PRODUCT_INTERFACE_PRD.md
```

## 4.2 Implementation Documents

```text
/docs/02-implementation/
OPESCARE_COMPLETE_MASTER_PROMPT_V3_FULL_FLOWS.md
OPESCARE_MISSING_OPERATIONAL_MODULES_COMPLETE_IMPLEMENTATION.md
```

## 4.3 Connect Suite Documents

```text
/docs/04-connect-suite/
OPESCARE_CONNECT_SUITE_INDUSTRY_LEADER_API_SDK_WIDGET_BRIDGE_LITE_WEBHOOKS.md
```

## 4.4 Production Launch Documents

```text
/docs/05-production-launch/
OPESCARE_PRODUCTION_LAUNCH_GOVERNANCE_COMPLIANCE_AND_DEPLOYMENT_MASTER_PLAN.md
```

## 4.5 Maturity and Scale Documents

```text
/docs/06-maturity-scale/
OPESCARE_STRATEGIC_MATURITY_STANDARDS_DATA_DICTIONARY_QA_AND_SCALE_MASTER_PACK.md
```

## 4.6 Agent Protocol Documents

```text
/docs/07-agent-protocols/
OPESCARE_CLAUDE_CODE_LOCAL_IMPLEMENTATION_PROTOCOL.md
OPESCARE_CODEX_LOCAL_REVIEW_AUDIT_TEST_PROTOCOL.md
```

---

# 5. Claude Code Role

Claude Code should be used as the main builder and patcher.

Claude Code should:

```text
audit first
preserve existing working modules
build missing modules
patch partial modules
avoid duplication
add tests
update documentation
run local tests
summarize changes
```

Claude Code should not:

```text
rebuild working modules blindly
create duplicate migrations
create duplicate models
overwrite existing routes without checking
refactor large areas without reason
jump to advanced modules before operational core
```

---

# 6. Codex Role

Codex should be used as the reviewer, tester, auditor, and guardrail.

Codex should:

```text
review Claude Code changes
detect duplicate modules
check migrations
check route conflicts
check permission issues
check audit logs
check privacy/security risks
run tests
write review reports
suggest minimal fixes
```

Codex should not:

```text
randomly rebuild modules
work on the same module at the same time as Claude Code
overwrite Claude Code changes blindly
make large architectural changes without explicit instruction
```

---

# 7. First Claude Code Command

Give Claude Code this as the first task.

```text
Read /docs/07-agent-protocols/OPESCARE_CLAUDE_CODE_LOCAL_IMPLEMENTATION_PROTOCOL.md.

Your first task is Phase 0 only: repository audit and setup.

Do not modify code.
Do not create modules.
Do not add migrations.
Do not refactor.
Do not install packages.

Create only:
docs/01-audits/LOCAL_REPOSITORY_IMPLEMENTATION_BASELINE_AUDIT.md

Audit:
1. existing models
2. existing migrations
3. existing routes
4. existing controllers
5. existing services
6. existing policies
7. existing permissions
8. existing views/components
9. existing tests
10. existing docs
11. duplicated structures
12. partial modules
13. missing modules
14. failing tests
15. uncommitted changes

After the audit, stop.
```

---

# 8. First Codex Command

After Claude Code creates the baseline audit, give Codex this task.

```text
Read /docs/07-agent-protocols/OPESCARE_CODEX_LOCAL_REVIEW_AUDIT_TEST_PROTOCOL.md.

Review Claude Code’s baseline audit.

Do not modify code.
Do not create modules.
Do not add migrations.
Do not refactor.

Create only:
docs/01-audits/CODEX_BASELINE_AUDIT_REVIEW.md

Check whether Claude missed:
1. duplicated modules
2. duplicate migrations
3. duplicate routes
4. duplicate models
5. migration risks
6. permission risks
7. missing audit logs
8. unsafe public routes
9. failing tests
10. broken route definitions
11. unprotected patient data access
12. demo-to-production leakage
13. API scope weaknesses
14. existing modules that should be preserved

After the review, stop.
```

---

# 9. Implementation Must Not Begin Until

Implementation should not begin until both files exist:

```text
docs/01-audits/LOCAL_REPOSITORY_IMPLEMENTATION_BASELINE_AUDIT.md
docs/01-audits/CODEX_BASELINE_AUDIT_REVIEW.md
```

Both should be reviewed before starting the first module.

---

# 10. First Module After Audit

After the baseline audits are complete, start with:

```text
Appointments & Booking
```

Do not start with:

```text
Bridge Agent
OpesCare Lite
Telemedicine
CDSS
Offline Sync
Subscription Billing
Country Expansion
AI Governance
```

Those should come later.

---

# 11. Correct Implementation Order

Use this order:

```text
Phase 0 — Repository audit and setup
Phase 1 — Core already-built module verification
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
Phase 20 — QA / Security / Production readiness
```

---

# 12. Working Rhythm

Use this rhythm for every module.

```text
1. Claude Code audits the target module.
2. Claude Code reports what exists and what is missing.
3. Claude Code implements only missing or partial parts.
4. Claude Code runs tests.
5. Claude Code writes change summary.
6. Codex reviews the branch.
7. Codex runs tests.
8. Codex writes review report.
9. Claude Code fixes issues found by Codex.
10. Codex verifies again.
11. Move to next module only after tests pass.
```

---

# 13. Test Commands

After each implementation task, run:

```bash
php artisan test
php artisan route:list
php artisan migrate:fresh --seed --env=testing
```

If frontend exists, also run:

```bash
npm run build
npm run test
```

---

# 14. Git Rules

Before each task:

```bash
git status
```

If there are uncommitted changes, do not overwrite them.

Create a branch per module:

```bash
git checkout -b feature/opescare-appointments
git checkout -b feature/opescare-queue
git checkout -b feature/opescare-billing
git checkout -b feature/opescare-insurance
git checkout -b feature/opescare-visit-flow
```

Use commit messages like:

```text
feat(appointments): implement booking and check-in flow
feat(queue): add patient flow and station transfers
feat(billing): implement invoices payments and receipts
feat(insurance): implement claims and preauthorization
test(visit): add end-to-end patient visit flow test
fix(consent): enforce revoked consent access denial
```

---

# 15. Duplicate Prevention Rule

Before creating any new file, model, migration, route, service, or controller, the agent must search for existing equivalents.

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
permission name
test file
factory/seeder
```

Do not create duplicates like:

```text
AppointmentNew
AppointmentV2
BillingInvoice2
PatientVisitNew
NewHealthId
QueueModuleNew
```

---

# 16. Protected Existing Modules

These must not be rebuilt blindly:

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

If a protected module needs changes:

```text
patch only what is missing
preserve existing tests
add regression tests
avoid renaming existing structures
document the reason
```

---

# 17. Module Completion Rule

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

If any of these are missing, mark it as:

```text
PARTIAL
```

Do not call it complete.

---

# 18. Stop Conditions

Stop and request review if:

```text
existing implementation conflicts with documents
migration may destroy data
duplicate module exists but canonical version is unclear
tests fail and cause is unclear
security/privacy behavior is ambiguous
module requires business decision
```

---

# 19. Final Instruction

Do not generate more product documents before the repository audit.

The correct next action is:

```text
1. organize docs folder
2. place all source files correctly
3. run Claude Code baseline audit
4. run Codex baseline review
5. start Appointments only after both audits
```

This protects existing modules and gives Claude Code and Codex a controlled implementation path.
