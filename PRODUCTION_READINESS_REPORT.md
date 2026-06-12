# OpesCare Production Readiness Report — FRESH CODE SCAN (v2)
**Date:** 2026-06-12 · **Method:** 12 parallel line-by-line code auditors over every module, route file, middleware, migration, seeder, config, Blade view, and Dart file. All findings based on actual source code only (no project docs/checklists consulted). Highest-impact claims independently re-verified by direct grep.

---

## 1. HEADLINE VERDICT

**NOT production-ready.** This fresh scan found a class of defect the surface scan missed: **multiple API V1 controllers call service methods that do not exist** — those endpoints throw fatal errors the moment they're hit. The web portals (Blade) are in much better shape than the JSON API layer.

### Verified broken endpoints (controller → non-existent service method)
| Controller | Broken calls (verified) |
|---|---|
| `Api/V1/AnalyticsController` | `getFacilityDashboard`, `getAppointmentStats`, `getQueueStats`, `getBillingStats` — service only has `visitSummary/dashboardSnapshot/...` |
| `Api/V1/StaffController` | `staff->listForFacility`, `staff->get`, `staff->updateProfile`, `roster->getRoster`, `roster->assignShift` — none exist in StaffService/RosterService |
| `Api/V1/TelemedicineController` | `teleconsult->book`, `teleconsult->get`, `consent->record`, `waitingRoom->join`; `cancel()` signature mismatch (string id vs model) |
| `Api/V1/TriageController` | `scoring->computeScore`, `triage->reassess`, `triage->listActiveForFacility`, `emergency->escalateFromTriage` |
| `Api/V1/InsuranceController` | `claims->listForUser`, `claims->getMinimumNecessaryView`; `decide()` argument order swapped vs service |
| `Api/V1/SubscriptionController` | `listActivePlans`, `getPlan`, `getForOrganization`, `limits->getCurrentUsage` missing; `subscribe/changePlan/cancel` signature mismatches |
| `Api/Fhir/FhirController` | calls `fhirService->patientEverything()` (×2: lines ~110, ~523) — method is `patientBundle()`. `$everything` and `$export` crash |
| `Modules/Queue/QueueDisplayService` | uses `ticket_number`, `station` relation, `queue_station_id` column, `patient_first_name` — none exist on QueueTicket |
| `Modules/Inventory/SupplyChainService:307` | calls `$item->totalStock()` — not defined on InventoryItem (low-stock detection crashes) |
| `WardController` | `recordNursingRound()` routed to method missing on WardService |
| **ResearchAccess** | controller fully implemented but **zero routes registered** — module unreachable |

**Pattern:** API V1 controllers were written against imagined service interfaces and never integration-tested. Any module above is NOT READY at the API layer even where its service logic is sound.

---

## 2. CLINICAL-SAFETY BUGS (verified or high-confidence)

1. **Telemedicine consent check is broken** — `CallProviderService` checks `$consult->consent_obtained`, a field that doesn't exist on Teleconsultation. Consent gating silently doesn't work.
2. **Ward bed double-assignment race** — `WardService::admit()` checks `isAvailable()` then creates without `lockForUpdate()`; two concurrent admits can take the same bed.
3. **Hardcoded drug-safety lists** — Beers criteria, paediatric and pregnancy contraindications are string arrays inside `ClinicalDecisionSupportService` (~lines 346/378/420). No way to update drug safety without a code deploy.
4. **Hardcoded notifiable-disease lists** — `PublicHealth/DraftGenerationService:79,173` (Malaria/Measles/Cholera/...), ignoring the rule engine.
5. **Triage vital thresholds** hardcoded and clinically debatable (no MAP, no agonal-breathing range).
6. **Messaging legal-hold bypass** — `MessagePermissionService::canViewThread()` returns `true` for ANY user when `legal_hold` is set (comment says "authorized compliance actors" but there's no role check).
7. **Attachment virus scan is fake** — `MessageAttachmentService` hardcodes `$scanStatus = 'passed'`.
8. **Insurance claim payment** — null `approved_amount`+`claimed_amount` leaves claims stuck in `partially_paid`; status set directly from user-supplied decision value.
9. **Legal re-acceptance not enforced** — new document versions with `requires_reacceptance=true` never invalidate old acceptances.
10. **CareMap `FacilityVerificationService::updateProfile()`** logic inverted (`!isDirty()`), so change-audit/high-risk flagging never fires.
11. **Subscription reactivation logic inverted** — `doesntExist()` where `exists()` intended; paused entitlements never re-enable.

---

## 3. SECURITY (fresh scan, verified)

**Critical/High:**
- `routes/communications.php` — **entire file has zero auth middleware**: notifications, tasks, messages, admin templates, escalation chains, broadcasts all open.
- `routes/academy.php` — zero auth middleware including `/v1/admin/academy/*` (seed tracks, revoke/renew certificates).
- `routes/partners.php` — only the test route has middleware; approve/suspend partner, certify integration, enable-production are **unauthenticated**; 8 routes are stub closures returning `"Stub: ..."`.
- **No global auth on API routes** (`bootstrap/app.php` appends only CORS/demo/security-headers/logging) — every group must opt in, and three files didn't.
- CORS wildcard (`allowed_origins/methods/headers = ['*']`).
- `VerifyIntegrationClient` is the only gate on many admin-grade endpoints — it authenticates a client but checks **no role** (AdminGovernance approve-corrections, emergency reviews, data exports; cross-facility access in Immunization, Inventory, Insurance, ProviderMobile patient lookup).
- Connect client secrets stored plaintext (`ConnectAdminService.php:22`, comment "plain for demo").
- Test bypass tokens in `VerifyIntegrationClient`/`VerifySdkToken` (gated only by `APP_ENV=testing`).
- `DemoDataScope` middleware is **Octane-incompatible** (mutates global config; demo-data leakage if Octane used — currently aborts 503).
- Map provider API keys returned in JSON responses (`MapProviderService.php:30,40`).
- USSD callback webhook: no signature validation, no rate limit. `patch_diagnosis.php` sitting at repo root (manual DB-patch script — remove).

---

## 4. DATABASE & MIGRATIONS (verified live)

- **158 of 182 migrations PENDING** on the local DB (24 ran).
- **4 pairs of duplicate timestamps** (fresh scan found two more than before): `2026_05_17_000001` ×2, `2026_05_26_000001` ×2, `2026_05_26_000002` ×2, `2026_05_31_000001` ×2.
- **17 migrations (2026_06_09_*) use raw `ALTER TABLE ... ADD CONSTRAINT CHECK`** — fails on SQLite (breaks the test environment, which uses sqlite in-memory).
- `config/database.php` **defaults to sqlite** if `DB_CONNECTION` unset — silent data loss in misconfigured prod.
- `SystemAccountSeeder` and `LegalDocumentSeeder` **not wired into DatabaseSeeder** (system account UUID …0001 is required by Lite/Connect; legal docs are stubs requiring counsel-reviewed text).
- `DemoDeveloperAccountSeeder` creates a client flagged `environment='production'` with predictable UUIDs and `hash('sha256','prod_secret_opeshisos_2026')` — runs if `OPESCARE_DEMO_MODE=true`.
- Missing from `.env.example`: `OPESCARE_SYSTEM_PROVIDER_ID`, `OPESCARE_DEFAULT_COUNTRY`.

---

## 5. HARDCODED / FAKE DATA (production paths)

- Support phone `+237 XXX XXX XXX` — `AppointmentSmsReminder.php:44`, `UssdSessionService.php:38`; unverified `1800-OPES-CARE` in `UssdMenuService.php:17`; URL `opescare.cm/caremap` hardcoded.
- `'demo-provider'` fallback user in `ProviderMobileTaskController:127`.
- Default UUID fallbacks (MobileGovernanceController, system provider), `SMP-90123` lab sample fallback, `DEMO-PATIENT-ID` in StaffPortalController.
- Health ID format `OC-MVP-…` hardcoded (PatientIdentity:84); currency `XAF` and country `CM` hardcoded (OpesCareLite, config).
- Invoice document template is literally `'<div>Invoice</div>'` (`PatientJourneyService:143`).
- DHIS2 data-element UIDs are placeholders (config-overridable — acceptable, must be set).
- PHP SDK returns mock tokens + hardcoded "John D."; connect-widget test session token; bridge-agent sandbox creds + `C:\LegacyHIS\exports` path.

---

## 6. MODULE SCORECARD (fresh, stricter)

**READY (API layer verified or low-risk):** Search, SecurityOperations, Communications (router service), Maternity, Offline, OperationalFlow*, PatientIdentity*, ConsentManagement*, CountryExpansion, EncounterManagement, FacilityReadiness, FileStorage*, AccessControl, Admin, Billing*, CareMap* (*= minor fixes listed above)

**NEEDS WORK:** Appointments (self-booking validation, race), Academy (auth + 'XX' country code), Governance (no transactions on consent/correction; no role checks), Immunization (no facility scoping), Inventory (`totalStock()` missing, batch uniqueness), Legal (re-acceptance), Messaging (legal-hold bypass, fake scan, plaintext bodies), MasterPatientIndex (validation), Queue (display service column bugs), Referral (transactions), Support (audit schema inconsistency), OpesCareLite (no outer transaction on push sync), PublicHealth (hardcoded diseases, no transactions), Staff (controller broken), Subscription (controller broken + inverted logic), Connect (plaintext secrets, no webhook HMAC)

**NOT READY:** Analytics (all 4 API endpoints fatal), Broadcasts (empty stub service), Partners (2 stub services + 8 stub routes, unauthenticated), Insurance API (missing methods), ResearchAccess (zero routes), Tasks (stub model, demo-provider fallback), Telemedicine (broken consent + 3 missing methods), Triage (4 missing methods), WardManagement (bed race + method gaps), Fhir API (`patientEverything` crash, no SMART scopes), Notifications (Voice stub, provider interfaces without implementations, unsafe template renderer), DataImport (portal import is simulated — no real import executes; Excel unsupported)

---

## 7. CLIENT APPS

**Web portals (Blade): READY.** Fresh scan verified 100% form→route alignment, controller→view variable binding, full en/fr lang parity, no lorem/coming-soon. Only items: contact-form email dispatch is a TODO, a few untranslated `<select>` labels, demo credentials in login view (must hide in prod).

**Flutter app: NOT READY.** `firebase_options.dart` throws `UnimplementedError`; **platform-detection bug** in `auth_repository.dart:86` (uses `dart.library.html` and inverts it — iOS reports 'android', breaking push); token-refresh race (no request queue); OTP "resend" doesn't resend; Health-ID share button is a no-op; weak email validation; keystore/signing not set up; default API URL is `opescare.test`.

---

## 8. WHAT "DEPLOY TOMORROW" WOULD ACTUALLY TAKE

**Cannot responsibly deploy the full API tomorrow.** A scoped deployment is possible: web portals + the verified-ready module APIs, with the broken/unauthenticated route files disabled.

**P0 — fatal/security (est. 1–2 days of focused work):**
1. Fix 4 duplicate migration timestamps; run 158 pending migrations; wire + run SystemAccountSeeder/LegalDocumentSeeder.
2. Add auth middleware to communications.php, academy.php, partners.php; remove/implement stub routes; restrict CORS.
3. Fix the 7 controller↔service mismatch clusters (Analytics, Staff, Telemedicine, Triage, Insurance, Subscription, Fhir) or unroute them.
4. Fix telemedicine consent field, ward bed lockForUpdate, messaging legal-hold check, subscription inverted condition, CareMap isDirty inversion.
5. Hash Connect secrets; add role checks behind VerifyIntegrationClient for admin/governance endpoints; facility-scope Immunization/Inventory/ProviderMobile.
6. Replace `+237 XXX XXX XXX`, 'demo-provider', invoice stub template; set production .env (mail/redis/S3/cron/KMS).

**P1 — before real patients:** drug/disease lists → DB; transactions in Governance/Referral/PublicHealth/Partners/Lite sync; real legal documents; webhook HMAC; DataImport real executor; SQLite-incompatible CHECK constraints fixed (restores test suite); notification provider implementations + template seeding.

**Flutter app:** separate track (Firebase config, platform-detection fix, signing) — several days + store review.
