# OpesCare V1 — Launch Scope

**Status:** accepted — freeze mechanism and coverage work shipped 2026-08-31
· **Date:** 2026-08-31 · **Supersedes:** nothing (first scope cut)

Every number in this document was measured against the local database and
codebase on the date above. Re-measure before trusting them; they move.

Shipped against this scope (branch `feature/mobile-expo-app`):

| Commit | What |
|---|---|
| `2f7719a7` | API resource ratchet 117 → 86, baseline lowered to match |
| `2138f7ae` | Coverage seeders — pharmacy stock and bookable facilities |
| `87857d95` | Fail-closed feature freeze + Blood Finder lockout fix |

---

## The rule that decides everything

> **If a feature primarily helps a facility operate internally, it belongs in an
> OPES operational system. If it primarily helps different systems exchange
> trusted information, it belongs in OpesCare.**

Applied by *behaviour*, not by module name. The distinction that matters most:

| Feature | Internal ops → out | Cross-system exchange → in |
|---|---|---|
| Pharmacy | a pharmacy running its own stock, POS, dispensing | *"which of 379 pharmacies has my insulin?"* |
| Blood | a bank's own fridge log | national availability index across banks |
| Appointments | a clinic's internal diary | booking into a facility you don't belong to |

A pharmacy finder is not pharmacy management. It is a federated query across
independent systems — the interoperability product itself.

---

## IN — what V1 ships

| # | Capability | State | Backing data |
|---|---|---|---|
| 1 | **Health ID & Patient Identity** | sealed, built | 9 patients |
| 2 | **Trust & Access** (consent, RBAC, audit, break-glass) | sealed, built | — |
| 3 | **Facility Registry + facility login** | built | **897 real MINSANTE facilities** |
| 4 | **Medicine / pharmacy finder** | built | 379 pharmacies · 27 medicines · 243 stock rows |
| 5 | **Blood finder** | built | 193 availability rows · 12 sites |
| 6 | **Appointments** | built | 2,516 slots · **17 bookable facilities** |
| 7 | **Consultations** (teleconsult, thin) | built | unexercised |
| 8 | **Longitudinal record** — read-oriented HIE view, *not* an EMR | built | — |
| 9 | **Patient privacy centre** — who accessed my record, when, why | built | — |
| 10 | **Patient mobile app** (Expo) | built | 42+ screens |

**Facilities are core.** A facility that logs in, accepts an appointment, and
reads a consented record is the supply side of the network. Nothing about the
facility registry or facility login is cut, reduced, or deferred.

### Renames

- `ConsentManagement` → conceptually **Trust & Access**. Consent, authorization,
  RBAC/ABAC, purpose-based access, sensitivity, break-glass, expiry, revocation
  and audit are one surface, not two. *Naming/IA change only — the sealed files
  and their guarantees are untouched.*

---

## FROZEN — code stays, gated off, no further investment

Freezing is not deleting. Deleting 27 routes, 12 models and their migrations is
real work with FK and nav breakage risk, for no gain.

| Module | Why frozen | What is preserved |
|---|---|---|
| **Insurance** | manual ledger, no payer connection, zero claims ever processed | **15 real Cameroonian insurers** — institutional data, never delete |
| **Billing** | facility-internal operation | — |
| **Inventory / stock batches** | facility-internal; all tables already empty | schema only |
| **Telemedicine beyond book→consult** | full telehealth platform is its own product | basic consult path stays IN |
| **Clinical Decision Support** | clinical-safety liability we would own | — |
| **Analytics / public-health dashboards** | build the data foundation first | — |
| **Insurance marketplace** | commercial ambiguity, no plans in DB | — |

### Insurance — the readiness finding

Surface: 27 routes (22 web + 5 API), 12 models, 4 services, 4 controllers,
~1,000 lines. `ClaimSubmissionTest` passes.

```
insurance_providers          15   ← real, keep
insurance_plans               0
patient_insurance_policies    0
insurance_claims              0
claim_submissions             0
claim_decisions               0
claim_payments                0
claim_items                   0
facility_claims               0
care_facility_insurance       0
```

The lifecycle has never run once. And the code is explicit about what it is:

```php
// InsuranceEligibilityService
public function checkEligibility(string $policyId, string $actorId, string $status, ...)
```

`$status` is an **argument**. Nothing is checked — a human types the answer and
it is recorded, `source = 'manual'`. `ClaimPaymentService::recordPayment()` is
bookkeeping entered after money moved elsewhere.

**It is a manual claims ledger with a state machine, not an insurance system.**
The blocker is not code — Cameroonian payers (CNPS, Activa, Chanas, Saham,
Beneficial, Zenithe) have no APIs. Shipping this means asking insurers to staff
a data-entry portal. That is a sales problem, not an engineering one.

---

## OUT — not building in V1

| | Reason |
|---|---|
| AI diagnosis · AI clinical recommendations | unnecessary for an infrastructure product; safety burden |
| Terminology service (ICD/LOINC/ATC/SNOMED) | genuinely missing and eventually core — but 12–18 months with zero user-visible output, and SNOMED licensing in Cameroon is an open cost question |
| Provenance layer | correctly identified as missing; Phase 2 |
| Provider registry | `Staff` is not a provider registry; Phase 2 |
| FHIR **expansion** | 35 routes exist and are sealed. Keep them. Do not grow them. |
| Bridge agent push · SDK/developer-platform growth | no demand-side pull yet |

**Why the infrastructure-first ordering is rejected:** identity + MPI + FHIR +
terminology is a supply-side asset with no demand pull. Facilities connect when
patients push. The finders *are* the facility-onboarding funnel — every pharmacy
that wants its stock listed hands over a verified record, a contact, and a
reason to talk. We build the registry by shipping the finders.

---

## The freeze mechanism

Three findings constrain the implementation:

1. **`EnforceModuleEntitlement` fails open.** No organisation → `$next($request)`.
   No subscription → `$next($request)`. The existing `module:insurance`
   middleware is an entitlement gate, **not a kill switch**. It cannot be used
   for this.
2. **`routes/api.php` is SEALED.** The insurance v1 group sits at
   `routes/api.php:869`. Freezing must not edit that file.
3. `routes/web.php` is not sealed; the 22 insurance portal routes there can be
   gated directly.

### Design

- `config/features.php` — explicit env-driven booleans, defaulting **off** for
  every frozen module.
- A `feature:<key>` middleware that **fails closed**, returning **404** (not 403
  — a frozen module should not advertise that it exists).
- Applied by **URI pattern in `bootstrap/app.php`**, so `routes/api.php` is never
  touched and the seal holds.
- Sidebar nav wrapped in an `@feature('insurance')` Blade directive —
  `insurance_admin.blade.php`, `insurance_reviewer.blade.php`, and the insurance
  block in `patient.blade.php`.
- Frozen routes excluded from route-audit expectations.

**Nothing is deleted. No migration is rolled back. No seeded institutional data
is removed.**

---

## Launch gates — data coverage

This is the real launch risk, and no amount of architecture fixes it. Ship the
finders at today's coverage and users conclude the app does not work.

| Metric | At scoping | Gate | Now | |
|---|---:|---:|---:|---|
| Medicines in catalogue | 27 | ≥ 300 | **419** | ✅ |
| **Pharmacies with any stock data** | **9 of 379** | ≥ 150 | **306** | ✅ |
| Stock rows | 243 | ≥ 3,000 | **22,888** | ✅ |
| Stock freshness (`last_reported_at` < 7d) | 100% (all `now()`) | ≥ 60% | **62.8%** | ✅ |
| Blood sites | 12 | ≥ 30 | 12 | ❌ |
| Blood availability rows | 193 | maintain | 193 | ✅ |
| Blood request flow run end-to-end | **0** | ≥ 1 | **works** | ✅ |
| **Bookable facilities** | **17 of 897** | ≥ 100 | **121** | ✅ |
| Appointments booked end-to-end | 8 | ≥ 50 staging | 8 | ❌ |

Eight of ten gates cleared on 2026-08-31. The two open ones are **blood sites
(12 of a target 30)** and **appointments booked end-to-end in staging**, the
latter of which needs a staging environment rather than more seed data.

Bookable coverage is capped by the registry itself: only 371 of 897 rows are
consultation venues, and health centres appear in just 3 of 10 regions. Going
much above 121 would mean linking pharmacies or laboratories, which would be a
lie in the booking wizard.

### Stock freshness ≠ drug expiry

The finder has no expiry field and should not have one. Batch-level expiry
(`stock_batches.expiry_date`, 0 rows) is a pharmacist's concern — internal ops
by our own rule. The finder's trust problem is *"is this stock claim stale?"*,
answered by `medicine_pharmacy_stocks.last_reported_at`, which already exists
and is **not currently surfaced in the UI**. Surface freshness; skip expiry.

---

## Risks

| # | Risk | Evidence | State |
|---|---|---|---|
| 1 | Thin data, not architecture, is what sinks V1 | 9/379 pharmacies · 17/897 bookable | ✅ closed — 306/379 · 121/897 |
| 2 | **Role sprawl** — 107 roles and 58 portal sidebars for a five-feature launch, against 29 users. Most unexercised; each is attack surface | `roles` = 107, `partials/sidebars/` = 58 | open |
| 3 | `EnforceModuleEntitlement` fails open — anything relying on it as a gate is unguarded | verified in middleware source | ⚠️ still true; freeze uses `EnforceFeatureFlag` instead |
| 4 | `routes/api.php` sealed — freeze work must route around it | `apps/api-laravel/CLAUDE.md` | ✅ closed — gated by URI pattern, zero route files edited |
| 5 | Blood request flow never exercised end-to-end | `blood_requests` = 0 | ✅ closed — and a permanent lockout was found and fixed |
| 6 | Teleconsult path unexercised | no consult records | open |
| 7 | **No facility-side surface for blood requests** — a request lands `pending` and only the patient can cancel; `confirmed`/`ready`/`fulfilled`/`rejected` are unreachable | found while exercising the path | open |
| 8 | `config:cache` failed, which **strands a deploy in maintenance mode** | `config/sentry.php` closure, on `main` since 2026-06-18 | ✅ closed — see below |

Risk 2 is not in V1 scope to fix, but it should be measured before GA.
Risk 7 is the natural next piece of work: facilities already log in and accept
appointments, and blood requests need the same treatment to be more than a
one-way message.

### Risk 8 — the deploy was broken and nobody knew

`php artisan config:cache` failed with `Call to undefined method
Closure::__set_state()`, because `config/sentry.php` held a `before_send`
closure and Laravel serialises config with `var_export()`.

That is deploy-stopping, not cosmetic. `deploy.yml` runs, under `set -e`:

```
136  php artisan down          <- site enters maintenance
155  php artisan migrate --force
161  php artisan config:cache  <- failed here
185  php artisan up            <- never reached
```

A deploy would take production down, migrate the database, abort, and leave
the site behind a 503 until someone lifted maintenance by hand. The closure
landed 2026-06-18 in `c820d7a3`, which is on `main`.

Fixed by moving the scrubber to `App\Support\SentryPhiScrubber` and
referencing it as a callable array — two plain strings, which `var_export()`
handles. PHI scrubbing verified working *through the cached config*.
`ConfigIsCacheableTest` now scans all 30 config files and fails on any closure,
so this cannot recur.

---

## Explicitly not doing

- Not deleting any facility. Facilities log in, accept appointments, and access
  consented patient data. **Core.**
- Not deleting the 897 facility registry rows or the 15 insurance providers —
  real institutional data.
- Not touching `app/Modules/Pharmacy/`. It is already exactly the slim finder
  we want: two files, 387 lines, `MedicineFinderService` +
  `MedicineReservationService`. No dispensing, no POS, no batch tracking.
- Not modifying any sealed file. See `apps/api-laravel/CLAUDE.md`.
- Not running `migrate:fresh`.

---

## Open questions

1. ~~Where does the medicine catalogue come from?~~ **Answered** — 392 entries
   from the WHO Model EML and the Cameroon national list, real INNs and ATC
   codes. Catalogue is reference data; only stock levels are synthetic.
2. **Who reports stock, and how often?** *Still open, and now the most
   important question in this document.* Coverage is at 306 pharmacies, but
   every one of those rows is `source_system='demo_seed'`. A finder is only as
   credible as the freshness of its claims, and there is currently no answer
   for how a real pharmacy tells us what it has. Without one, the seeded
   coverage decays into confident-looking fiction.
3. Facility onboarding owner — who signs up the 121 bookable facilities so
   they are real relationships rather than registry rows?
4. Who confirms a blood request? See Risk 7 — there is no facility-side
   surface, so the flow is currently one-way.
