# OpesCare — Documentation Entry Point (for Claude Code)

This folder holds all OpesCare project documentation. If you are Claude Code working on this repo, start here.

## What OpesCare is (1 paragraph)

A patient-centered digital health **identity, interoperability, and care-operations** platform (not a single-hospital CRUD app). A patient owns a portable **Health ID** and a longitudinal, event-based record that travels across facilities under consent. Laravel modular monolith (`apps/api-laravel`), PostgreSQL system-of-record, Redis for queues/cache/webhooks, FHIR R4 at the boundary, OAuth2/PKCE for the partner surface; Flutter patient app (`apps/mobile-patient`); Connect SDKs (`sdk/`), embeddable widget (`widget/`), and on-prem Bridge agent (`bridge-agent/`). Multi-facility, multi-country (Gabon first). See the full thesis and invariants in the register below.

## The three documents that matter

| Doc | Use it for |
|-----|-----------|
| [`AS_BUILT_IMPLEMENTATION_REGISTER.md`](AS_BUILT_IMPLEMENTATION_REGISTER.md) | **Current state** — what each of the 45 modules + the `app/Services` layer actually contains, with file references and 🟢/🟡/🔴 status. Read this before assuming something is or isn't built. |
| [`audits/SPEC_VS_CODE_GAP_AUDIT.md`](audits/SPEC_VS_CODE_GAP_AUDIT.md) | **The work list** — every gap as `GAP-0xx` with exact file path(s), the precise problem, and an acceptance criterion ("Done when…"). Also `TD-0xx` tech-debt cleanup items. |
| [`security/threat-model.md`](security/threat-model.md) | Consolidated STRIDE threat model; §8 lists open security actions, §9 lists config values to reconcile against code. |

Also: [`GO_LIVE_READINESS.md`](GO_LIVE_READINESS.md) — the prioritized deploy checklist (P0/P1/P2 + verification steps). `INDEX.md` lists every other document by category.

## Hard invariants — do not violate when editing code

These run through the codebase; breaking them is a correctness/safety bug:

1. **Centralized identity writes** — create patients only via `PatientIdentityService`; never `Patient::create` directly in a module.
2. **No probabilistic auto-merge** — uncertain identity matches go to the Reconciliation/MPI review path, never silent linking.
3. **Immutable clinical events** — released labs, dispenses, claims, invoices, audit rows are never hard-overwritten; use amend / void / reverse / entered-in-error.
4. **Idempotent external writes** — partner writes require `Idempotency-Key`; same key+hash returns the same result, same key+different hash returns 409.
5. **Provenance on every imported record**; **break-glass emergency access minimized + reviewed**; **offline access bounded by signed short-lived grants**; **per-country effective-dated regulation packs**.

## How to pick up work

1. Open `audits/SPEC_VS_CODE_GAP_AUDIT.md`, choose a `GAP-0xx` (Tier 1 = deployment blockers first).
2. Read the listed file path(s), confirm the gap still exists (the audit is dated 2026-06-11).
3. Implement to satisfy the "Done when…" criterion, honoring the invariants above.
4. Add/extend a test under `apps/api-laravel/tests/Feature/<Area>` and run `php artisan test`.
5. If the change alters a module's wiring, update its entry in `AS_BUILT_IMPLEMENTATION_REGISTER.md` so it doesn't drift.

## Current blockers (Tier 1 summary)

- **GAP-001** ✅ resolved (mobile INTERNET permission).
- **GAP-002** Mobile Firebase not configured (needs the owner's Firebase project — hand off, don't fabricate config).
- **GAP-003** Connect widget not implemented; session tokens validate nothing.
- **GAP-004** Only 2 of ~30 webhook events emitted.
- **GAP-005** Data-import `approve()` creates no records (stub).
- **GAP-006** Visit closure has no clinical safety blockers (patient-safety).
- **GAP-007** Interactive public-health submit not wired to the real `Dhis2Service`.
- **GAP-008** Academy competency gate coded but never enforced.
- **GAP-009** No MFA; audit archive not immutable.

## Accuracy notes

- This documentation set was reconciled on 2026-06-11, including a sweep of `app/Services` (64 classes) and `app/Console` (18 commands) that an earlier pass missed. If you find the docs disagree with the code, **trust the code and correct the doc** (note the date).
- The old `audits/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md` is **stale/superseded** — it marks many now-built modules as "NOT_STARTED". Do not rely on it.
- Tests (169 Feature + 4 Unit) exist but were not executed during the audit — a green `php artisan test` is not yet verified and should gate go-live.
