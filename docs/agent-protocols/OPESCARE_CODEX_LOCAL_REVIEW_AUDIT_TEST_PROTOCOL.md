# OPESCARE Codex Local Review, Audit & Test Protocol

**Project:** OpesCare  
**Tool:** Codex  
**Purpose:** Independent local review, testing, debugging, refactoring guidance, and safety audit protocol for OpesCare.  
**Environment:** Local desktop development  
**Repository:** Current local OpesCare repository  
**Primary Stack:** Laravel + PostgreSQL  
**Important Rule:** Codex should act mainly as a reviewer, test writer, auditor, and second-opinion agent. Codex should not randomly rebuild modules Claude Code already implemented.

---

# 1. Codex Role

Codex should act as the **quality control and verification agent**.

Codex is responsible for:

```text
reviewing Claude Code changes
checking for duplicate modules
checking for broken routes
checking migration safety
checking failing tests
writing missing tests
finding security/privacy bugs
reviewing permissions
reviewing audit logs
checking code quality
checking data dictionary consistency
checking API and integration behavior
identifying regressions
proposing focused fixes
```

Codex should not be the main builder unless explicitly assigned.

---

# 2. Main Rule

Codex must never overwrite Claude Code work blindly.

Codex must:

```text
inspect diffs
understand what changed
run tests
identify risks
suggest or apply minimal fixes
avoid large rewrites
preserve working code
```

---

# 3. Best Division of Work

Use Claude Code for:

```text
module implementation
models/migrations/services/controllers/routes/UI
feature creation
integration work
documentation creation
```

Use Codex for:

```text
reviewing implementation
finding bugs
writing tests
checking permissions
checking duplicate code
checking route conflicts
checking security issues
checking migration safety
checking end-to-end flows
refining code quality
```

This separation protects existing modules.

---

# 4. Codex Source of Truth

Codex must read:

```text
docs/product/
docs/implementation/
docs/connect/
docs/production/
docs/maturity/
docs/audit/
latest Claude Code change summary
git diff
test output
```

Codex must not assume the docs are fully implemented. It must verify in code.

---

# 5. Codex Review Checklist

For every Claude Code change, Codex must check:

```text
Did it duplicate an existing model/table/controller/service?
Did it modify protected modules unnecessarily?
Did it add migrations safely?
Did it break existing routes?
Did it bypass permissions?
Did it miss audit logs?
Did it miss bilingual labels?
Did it miss tests?
Did it expose patient data?
Did it create public routes that leak clinical data?
Did it mix patient billing with SaaS billing?
Did it allow insurance users to see full EMR?
Did it allow support users to see patient records without audit?
Did it introduce hardcoded data?
Did it break UI responsiveness?
Did it ignore data dictionary naming?
```

---

# 6. Codex First Task: Baseline Safety Review

Run this before reviewing new implementation:

```text
Review the current OpesCare repository.

Do not modify code.

Create:
docs/audit/CODEX_LOCAL_BASELINE_REVIEW.md

Check:
1. Existing modules
2. Duplicate models/tables/controllers/routes
3. Current failing tests
4. Risky migrations
5. Missing permissions
6. Missing audit logs
7. Public routes exposing sensitive data
8. Demo data isolation
9. API scope enforcement
10. Known partial modules

Do not change files except the review report.
```

---

# 7. Codex Review After Claude Code Task

Use this prompt after Claude finishes a module:

```text
Review the latest changes in the OpesCare repository.

Do not rewrite the module.
Do not duplicate implementation.

Review:
1. git diff
2. migrations
3. models
4. services
5. controllers
6. routes
7. policies/permissions
8. audit events
9. tests
10. docs

Check against:
- OpesCare documents in docs/
- data dictionary
- permission matrix
- security/privacy rules
- existing module architecture

Run:
php artisan test
php artisan route:list

If frontend exists:
npm run build
npm run test

Create:
docs/audit/CODEX_REVIEW_[MODULE_NAME].md

Include:
- pass/fail
- bugs found
- duplicate risks
- security/privacy risks
- missing tests
- migration risks
- recommended fixes
```

---

# 8. Codex Bug Fix Rules

Codex may fix bugs only if:

```text
bug is clear
fix is small
fix does not change architecture
fix does not duplicate module
fix does not rewrite working code
fix can be tested immediately
```

Codex must not perform major implementation unless explicitly instructed.

For major gaps, Codex should create a report and let Claude Code implement.

---

# 9. Codex Testing Responsibilities

Codex should write or improve tests for:

```text
permissions
facility boundaries
patient privacy
audit events
workflow status transitions
migrations
API scopes
idempotency
webhooks
document verification privacy
billing/payment correctness
insurance minimum necessary access
support data minimization
queue public display masking
Health ID QR privacy
```

---

# 10. Required Codex Test Commands

Codex must run:

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

Codex must document exact failures.

---

# 11. Codex Security Review Areas

Codex must pay special attention to:

```text
Health ID verification
public document verification
patient record access
consent revocation
emergency access
insurance claim access
support ticket access
file downloads
API scopes
webhook signatures
Bridge Agent pairing
OpesCare Lite offline sync
billing refunds
admin permissions
```

---

# 12. Codex Duplicate Detection

Codex must search for duplicate/conflicting structures.

Check for duplicate:

```text
appointments tables/models
queue tables/models
invoice/payment/receipt tables/models
insurance claim tables/models
visit/encounter tables/models
Health ID models
document verification models
API client/token models
webhook models
support ticket models
import job models
```

If duplicates exist, Codex must not delete them immediately. It must report:

```text
duplicate files
risk
which seems canonical
recommended consolidation plan
```

---

# 13. Codex Migration Review Rules

For each migration, Codex must check:

```text
does table already exist?
does column already exist?
are foreign keys correct?
are indexes needed?
is rollback safe?
does migration destroy data?
does migration match data dictionary?
does migration use UUIDs consistently?
does migration add facility_id/organization_id where needed?
```

---

# 14. Codex Permission Review Rules

Codex must verify:

```text
routes protected by middleware
policies exist
roles have correct permissions
high-risk permissions not granted by default
facility context enforced
patient access requires consent/care relationship
insurance minimum necessary access enforced
support access audited
```

---

# 15. Codex Audit Log Review Rules

Codex must verify audit events for:

```text
appointment create/update/cancel/check-in
queue call/transfer/priority override
invoice/payment/refund
insurance eligibility/claim decision
visit closure
support ticket access/escalation
data import approval/rollback
admin setting change
Health ID scan
document verify/revoke/amend
emergency access
API access
webhook delivery
Bridge Agent sync
Lite sync
```

---

# 16. Codex UI Review Rules

Codex should check:

```text
UI follows existing design system
Lucide icons used
no emojis in production UI
mobile responsive
loading states exist
empty states exist
error states exist
English/French labels exist
no patient data leaks in public views
```

---

# 17. Codex API Review Rules

Codex must check:

```text
all API endpoints versioned under /api/v1
auth required unless intentionally public
scopes enforced
rate limits exist
idempotency on write endpoints
request IDs supported
errors follow standard format
audit logs created
OpenAPI docs updated
```

---

# 18. Codex Webhook Review Rules

Codex must check:

```text
webhook events are scoped
payloads signed
signature verification docs exist
delivery retry works
dead-letter queue exists
manual replay works
sensitive payloads are not default
delivery logs exist
```

---

# 19. Codex Bridge Agent Review Rules

Codex must check:

```text
pairing code expires
agent requires approval
facility scoping works
credentials encrypted
local queue design safe
sync idempotent
conflicts create reconciliation case
remote revoke works
logs redact sensitive data
```

---

# 20. Codex OpesCare Lite Review Rules

Codex must check:

```text
device registration required
unapproved device blocked
Lite config scoped to facility
offline mode limited
full EMR not cached offline by default
sync conflicts handled
official documents not finalized offline without sync
```

---

# 21. Codex Review Report Format

Codex must output:

```text
Review:
Branch:
Commit:
Module:
Files reviewed:
Tests run:
Test result:
Duplicate risks:
Migration risks:
Security/privacy risks:
Permission issues:
Audit log issues:
UI issues:
API issues:
Bugs found:
Fixes applied:
Fixes recommended:
Ready to merge: YES/NO
Reason:
```

---

# 22. Codex Stop Conditions

Codex must stop and report if:

```text
it finds data-loss migration
it finds duplicate core module
it finds public patient data leak
it finds permission bypass
it finds insurance full EMR exposure
it finds support unrestricted patient access
it finds tests failing from unclear cause
```

---

# 23. Suggested Workflow With Claude Code

Use this rhythm:

```text
1. Claude Code implements one module on a branch.
2. Claude Code runs tests and writes change summary.
3. Codex reviews the branch.
4. Codex writes review report.
5. Claude Code fixes major issues.
6. Codex verifies again.
7. Commit/merge only when tests pass.
```

Do not let both agents modify the same module at the same time.

---

# 24. Final Rule

Codex is the guardrail.

It should protect the repository from duplication, broken existing modules, unsafe permissions, weak tests, and accidental privacy/security failures.
