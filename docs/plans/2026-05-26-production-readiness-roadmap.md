# OpesCare Production Readiness Roadmap

> **58 gap items → 12 independent waves → full production readiness**
> Each wave produces working, testable software on its own. Execute waves in order
> (infrastructure waves should be done in parallel with product waves in a real team).

---

## Wave Execution Order

| Wave | Plan File | Items | Est. Effort | Dependency |
|------|-----------|-------|-------------|------------|
| W1 | `2026-05-26-wave-pr1-clinical-ehr.md` | 4 gaps (items 3,6,10 + 2-partial) | Medium | None |
| W2 | `2026-05-26-wave-pr2-interoperability.md` | 6 gaps (items 12–17) | Medium | W1 |
| W3 | `2026-05-26-wave-pr3-appointments.md` | 4 gaps (items 18,20,22,23) | Medium | None |
| W4 | `2026-05-26-wave-pr4-billing-insurance.md` | 6 gaps (items 25–30) | Large | None |
| W5 | `2026-05-26-wave-pr5-lab-imaging.md` | 4 gaps (items 32–35) | Medium | W1 |
| W6 | `2026-05-26-wave-pr6-pharmacy.md` | 2 gaps (items 36–37) | Small | None |
| W7 | `2026-05-26-wave-pr7-patient-engagement.md` | 6 gaps (items 39–44) | Large | W1,W3 |
| W8 | `2026-05-26-wave-pr8-provider-staff.md` | 4 gaps (items 45,47,48,49) | Medium | None |
| W9 | `2026-05-26-wave-pr9-compliance.md` | 4 gaps (items 50,51,53,56) | Medium | None |
| W10 | `2026-05-26-wave-pr10-infrastructure.md` | 10 gaps (items 57–66) | Large | None |
| W11 | `2026-05-26-wave-pr11-multitenancy.md` | 3 gaps (items 67,68,70) | Large | W10 |
| W12 | `2026-05-26-wave-pr12-security.md` | 5 gaps (items 71–75) | Medium | W10 |

---

## Canonical Rules (apply to ALL waves)

1. **Never delete or rename existing models, controllers, or routes.**
2. **Never alter existing `$fillable` to remove fields.**
3. **Every new migration is additive — no column drops, no table renames.**
4. **All new code lives in its own module or extends existing modules via new files.**
5. **No changes to Blade views, CSS, or JS unless the wave explicitly targets the frontend.**
6. **Run `php artisan test` after every task. All tests must remain green.**
7. **`is_demo` must never appear in `$fillable`.**
8. **Invite tokens stored as `hash('sha256', $rawToken)` — never expose hash to client.**

---

## Gap ↔ Wave Index

| Gap # | Feature | Wave |
|-------|---------|------|
| 2-partial | E-prescribing pharmacy routing | W1 |
| 3 | Medication reconciliation + drug interaction | W1 |
| 6 | Problem list / diagnosis lifecycle | W1 |
| 10 | Maternity / antenatal care | W1 |
| 12 | LOINC codes on lab results | W2 |
| 13 | SNOMED CT on diagnoses | W2 |
| 14 | CNAMGS national patient ID | W2 |
| 15 | Cross-facility record exchange | W2 |
| 16 | DHIS2 push for MINSANTE | W2 |
| 17 | HL7 v2 ADT messages | W2 |
| 18 | Patient self-booking | W3 |
| 20 | SMS appointment reminders | W3 |
| 22 | Waitlist + cancellation backfill | W3 |
| 23 | Provider schedule / shift management | W3 |
| 25 | Pre-authorization model (complete partial) | W4 |
| 26 | Claims submission + remittance | W4 |
| 27 | Co-pay / co-insurance at PoC | W4 |
| 28 | Mobile Money payments | W4 |
| 29 | Revenue cycle dashboard | W4 |
| 30 | Patient payment plans | W4 |
| 32 | Critical value alerting + ACK | W5 |
| 33 | DICOM viewer / PACS | W5 |
| 34 | Radiology report distribution | W5 |
| 35 | Reference range management | W5 |
| 36 | Drug formulary management | W6 |
| 37 | Controlled substance tracking | W6 |
| 39 | Patient mobile app push framework | W7 |
| 40 | USSD / SMS fallback | W7 |
| 41 | Patient-facing care plan | W7 |
| 42 | Secure patient-provider messaging | W7 |
| 43 | Patient satisfaction surveys | W7 |
| 44 | Medical record PDF download | W7 |
| 45 | Provider credentialing / license | W8 |
| 47 | Care team collaboration / handoff | W8 |
| 48 | On-call scheduling | W8 |
| 49 | Provider performance metrics | W8 |
| 50 | Cameroon Law 2010/012 compliance | W9 |
| 51 | Advance directives / living will | W9 |
| 53 | Automated data retention / purge | W9 |
| 56 | Third-party pen test log | W9 |
| 57 | Multi-region config | W10 |
| 58 | DB read replicas | W10 |
| 59 | Redis + Laravel Horizon | W10 |
| 60 | Automated encrypted backups | W10 |
| 61 | Zero-downtime deployment | W10 |
| 62 | CDN configuration | W10 |
| 63 | Centralized log aggregation | W10 |
| 64 | Synthetic health monitoring | W10 |
| 65 | Per-tenant rate limiting | W10 |
| 66 | Formal DR plan | W10 |
| 67 | True tenant isolation (RLS) | W11 |
| 68 | Tenant onboarding wizard | W11 |
| 70 | API usage analytics per partner | W11 |
| 71 | HSM / KMS for encryption | W12 |
| 72 | Secrets rotation automation | W12 |
| 73 | Dependency vulnerability scanning | W12 |
| 74 | WAF configuration | W12 |
| 75 | Formal threat model | W12 |
