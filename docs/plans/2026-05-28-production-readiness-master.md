# OpesCare Production Readiness — Master Roadmap

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` or `superpowers:executing-plans` to implement each phase plan task-by-task.

**Goal:** Close all 42 gap items (26 ❌ Missing + 16 ⚠️ Partial) across Clinical, Interop, Scheduling, Financial, Lab, Pharmacy, Patient Engagement, Provider/Staff, Compliance, Infrastructure, and Security — without touching or deleting any existing code.

**Constraint:** Every task is additive only. No existing model, migration, service, controller, or config file is deleted or structurally overridden. New columns are added via new migrations. New services sit beside existing ones. New routes register under distinct URIs.

**Audit baseline:** 2026-05-28 scan — 33 ✅ Built · 16 ⚠️ Partial · 26 ❌ Missing (75 items total).

---

## Phase Map

| Phase | Plan File | Items | Risk | Status |
|-------|-----------|-------|------|--------|
| 1 | `2026-05-28-phase1-complete-partials.md` | 2,4,7,11,13,14,16,21,27,28,32,35,39,58,61,62,74 | Low | Additive completions |
| 2 | `2026-05-28-phase2-clinical-interop.md` | 10,17 | Medium | New module |
| 3 | `2026-05-28-phase3-scheduling-staff.md` | 22,23,47,48,49 | Medium | New models + services |
| 4 | `2026-05-28-phase4-financial.md` | 29,30 | Medium | New reporting + models |
| 5 | `2026-05-28-phase5-lab-pharmacy.md` | 34,36,37 | Medium | New models + services |
| 6 | `2026-05-28-phase6-patient-engagement.md` | 40,41,43,44 | Medium | New modules |
| 7 | `2026-05-28-phase7-compliance.md` | 45,51,53,56 | Low-Med | New models + commands |
| 8 | `2026-05-28-phase8-infrastructure.md` | 57,67 | High | Cross-cutting changes |
| 9 | `2026-05-28-phase9-security.md` | 73,75 | Low | Config + docs |

---

## Item → Phase Index

| # | Feature | Phase | Type |
|---|---------|-------|------|
| 2 | E-prescribing pharmacy routing | 1 | Extend Prescription.php |
| 4 | Allergy hard-stop enforcement | 1 | Extend CDSS service |
| 7 | ImagingOrder model | 1 | New model + migration |
| 10 | Maternity / antenatal care | 2 | New module |
| 11 | FHIR subscriptions + bulk export | 1 | Extend FhirController |
| 13 | SNOMED CT on Diagnosis | 1 | New migration + method |
| 14 | CNAMGS / national patient ID | 1 | New migration + field |
| 16 | DHIS2 push for MINSANTE | 1 | New service + command |
| 17 | HL7 v2 ADT messages | 2 | New service |
| 21 | WhatsApp Business API | 1 | Implement stub |
| 22 | Waitlist + cancellation backfill | 3 | New model + service |
| 23 | Provider schedule / shift | 3 | New model + service |
| 27 | Co-pay calculation | 1 | New service |
| 28 | Mobile Money (MoMo) | 1 | New provider |
| 29 | Revenue cycle dashboard | 4 | New reports + queries |
| 30 | Patient payment plans | 4 | New model + service |
| 32 | Critical value acknowledgement | 1 | New model + workflow |
| 34 | Radiology report distribution | 5 | New model + service |
| 35 | Reference range management | 1 | New model + seeder |
| 36 | Drug formulary management | 5 | New model + service |
| 37 | Controlled substance tracking | 5 | New model + service |
| 39 | FCM/APNS push notifications | 1 | Implement stub |
| 40 | USSD / SMS fallback | 6 | New module |
| 41 | Patient-facing care plan | 6 | New model + API |
| 43 | Patient satisfaction surveys | 6 | New model + service |
| 44 | Medical record PDF / FHIR export | 6 | New service + command |
| 45 | Provider credentialing | 7 | New model + service |
| 47 | Care team + handoff notes | 3 | New models |
| 48 | On-call scheduling | 3 | New model + service |
| 49 | Provider performance metrics | 3 | New queries + reports |
| 51 | Advance directives / living will | 7 | New model |
| 53 | Automated data retention / purge | 7 | New command + policy |
| 56 | Pen test log / remediation tracker | 7 | New model |
| 57 | Multi-region / multi-AZ config | 8 | Config + service |
| 58 | DB read replicas (activate) | 1 | Config + .env.example |
| 61 | Zero-downtime deployment | 1 | GitHub Actions workflow |
| 62 | CDN configuration | 1 | Config update |
| 67 | Tenant isolation (RLS) | 8 | Trait across models |
| 73 | Dependabot / Snyk | 9 | Config files |
| 74 | WAF documentation | 1 | Infra config + checklist |
| 75 | Formal threat model | 9 | Documentation |

---

## Non-Goals (deferred by design)

- **DICOM viewer / PACS (item 33):** Requires a dedicated PACS server (Orthanc, DCM4CHEE). The Laravel API should integrate via HTTP with a deployed PACS — this is an infrastructure procurement task, not a code task.
- **HL7 v2 full parsing engine (item 17):** Implement ADT A01/A08/A28 send-only (HL7 FHIR-first platform). Full bidirectional v2 parsing is out of scope for the current sprint.
- **Full multi-region DB failover (item 57):** Covered at RDS/cloud-provider level. Phase 8 adds the application-level failover detection and read-replica routing.

---

## Dependency Order

```
Phase 1 → Phase 2 → Phase 3 → Phase 4
                 ↘ Phase 5
Phase 1 → Phase 6 (independent)
Phase 1 → Phase 7 (independent)
Phase 1,2 → Phase 8 (requires models to be stable)
Any phase → Phase 9 (config/docs, no code deps)
```

Phase 1 must complete before any other phase begins because it activates infrastructure (read replicas, push, CDN) and completes base models (ImagingOrder, SNOMED, CNAMGS) that later phases reference.

---

## Testing Strategy

- **Unit tests:** `php artisan test --filter=<ClassName>` — every new service gets a Feature test.
- **Migration safety:** Every new migration runs `php artisan migrate:fresh --seed` in CI to confirm no conflict.
- **Route check:** `php artisan route:list | grep <prefix>` after adding new controllers.
- **No test deletion:** Existing tests must pass after every phase. Run full suite: `php artisan test`.

---

## Commit Convention

```
feat(phase-N): <description>
fix(phase-N): <description>
```

One commit per task. Never squash phases together.
