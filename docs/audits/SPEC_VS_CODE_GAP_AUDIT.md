# OpesCare — Spec-vs-Code Gap Audit (Claude-Code-actionable)

**Date:** 2026-06-11 (revised after a completeness re-sweep of `app/Services`, `app/Console`, interop services, and tests)
**What this is:** every capability the specs promise that the code does not yet fully deliver, written so Claude Code can act on each item directly — stable ID, exact file path(s), the precise gap, and an acceptance criterion ("Done when…").
**Companion docs:** `../AS_BUILT_IMPLEMENTATION_REGISTER.md` (current state), `../security/threat-model.md`, `../CLAUDE.md` (entry point).

> **All paths are relative to `apps/api-laravel/` unless prefixed** (`apps/mobile-patient/`, `sdk/`, `widget/`, `bridge-agent/`).

---

## Corrections to the first-pass audit (read first)

The first pass scoped itself to `app/Modules/`, controllers, and routes, and **missed the `app/Services/` tree (64 classes) and `app/Console/` (18 commands).** That produced inaccuracies now corrected here:

- **Public-health submission is NOT entirely faked.** A real DHIS2 integration exists: `app/Services/Integration/Dhis2Service.php` makes live `Http::withBasicAuth()->post("{base}/api/dataValueSets")` calls (`pushDataValues` L49, `pushMonthlySummary` L129, `testConnection` L165), driven by `app/Console/Commands/PushDhis2ReportCommand.php` and the scheduled `health-id:generate-minsante-report` (monthly, `routes/console.php`). The real gap is narrower and is now **GAP-007** (severity downgraded High→Med).
- **HL7 ADT and USSD interop exist** and were never credited: `app/Services/Integration/Hl7AdtService.php`, `app/Services/Interoperability/Hl7AdtParser.php`, `app/Services/Ussd/UssdMenuService.php`, `app/Services/PatientEngagement/UssdSessionService.php`.
- **Mobile money is real:** `app/Services/Payment/{MtnMomoGateway,OrangeMoneyGateway}.php` and `app/Services/Payments/{MtnMomoService,OrangeMoneyService}.php` all make real HTTP calls.
- **Audit archiving exists** (`app/Console/Commands/ArchiveAuditLogs.php`) but uses `Storage::append` (not immutable WORM) — so GAP-009's immutable-store half still stands, with nuance.

A new **tech-debt** section (bottom) flags duplicate service folders and loose scripts found during the sweep.

---

## Tier 1 — Blockers

### GAP-001 — Mobile release build missing INTERNET permission — ✅ RESOLVED 2026-06-11
- **File:** `apps/mobile-patient/android/app/src/main/AndroidManifest.xml`
- **Was:** no permissions in the main manifest (INTERNET only in debug/profile) → release app could not reach the API.
- **Fixed:** added `INTERNET` + `POST_NOTIFICATIONS`; app label set to "OpesCare". Verified well-formed.

### GAP-002 — Firebase not configured (push/Crashlytics/Analytics inert) — 🔴
- **Files:** `apps/mobile-patient/lib/firebase_options.dart` (throws `UnimplementedError`); `apps/mobile-patient/android/app/build.gradle.kts` (no `google-services`/`crashlytics` plugins); missing `apps/mobile-patient/android/app/google-services.json` and `apps/mobile-patient/ios/Runner/GoogleService-Info.plist`; `lib/main.dart` swallows the init exception.
- **Done when:** `flutterfire configure` has generated real config; the Gradle plugins are applied; a release build initializes Firebase and a forced test crash appears in the Crashlytics console; `main.dart` logs (not swallows) init failures.
- **Note:** requires the owner's Firebase project — hand off (see `CLAUDE.md`).

### GAP-003 — Connect Widget not implemented; session tokens validate nothing — 🔴
- **Files:** `widget/connect-widget.html` (40-line demo iframe to an external host); `app/Http/Controllers/Api/V1/Connect/AuthController.php` → `createWidgetSession` (returns a random `wgt_session_*` string; no `WidgetSession` model, no validator anywhere in `app/`).
- **Done when:** widget sessions are persisted with expiry + facility binding + origin allowlist + scopes; a middleware validates `wgt_session_*` on widget routes and rejects expired/unknown-origin tokens; the embeddable widget renders the documented states/actions.

### GAP-004 — Only 2 of ~30 webhook event types are emitted — 🟡
- **Files:** `app/Services/WebhookService.php` (`dispatch` is generic — good); the only call sites in `app/` fire `lab_result.released` and `patient.updated`.
- **Done when:** domain services dispatch the documented events (at minimum `consent.granted/revoked`, `encounter.created`, `prescription.issued`, `medication.dispensed`, `emergency_access.used`, `reconciliation.*`, `security.suspicious_access`); an integration test asserts each is delivered to a subscribed endpoint.

### GAP-005 — Data-import execution is a stub (no records created) — 🔴
- **File:** `app/Http/Controllers/MedicalId/DataImportController.php` → `approve()` (~L141–175): sets status `completed` and `imported_rows = valid_rows` but creates nothing (comment: "For portal demo: simulate immediate completion"). `app/Modules/DataImport/Services/ImportRollbackService.php` notes "when real import execution is wired".
- **Done when:** `approve()` dispatches a queued job that creates records through `PatientIdentityService` (honoring the centralized-identity invariant), runs duplicate detection against the MPI, writes provenance, and is rollback-able; a test importing N valid rows creates N patients and is reversible.

### GAP-006 — Visit closure has no clinical safety blockers — 🟡 (patient-safety)
- **File:** `app/Modules/OperationalFlow/Services/VisitManagementService.php` → `complete()` / `transition()` (only a status state-machine; `complete()` checks "not already completed").
- **Done when:** `→ completed` is blocked while a critical lab/CDSS alert is unacknowledged, a required consultation note or discharge document is missing, or an open queue ticket exists (per E2E spec §9.15); a test exists for each blocker.

### GAP-007 — Interactive public-health submission not wired to the real DHIS2 path — 🟡 (was "High/faked")
- **Files:** `app/Http/Controllers/Api/V1/PublicHealth/PublicHealthController.php` → `submitReport` (L433) fabricates `external_reference = 'EXT-'.bin2hex(random_bytes(4))` (L458) and a hardcoded success — it does **not** call the real service. The real service is `app/Services/Integration/Dhis2Service.php` (`pushMonthlySummary` L129).
- **Done when:** `submitReport` routes through `Dhis2Service` (directly or by queuing `PushDhis2ReportCommand`), returns the real DHIS2 import counts (or a "queued" status with later reconciliation), and never fabricates an external reference.

### GAP-008 — Academy competency gate coded but never enforced — 🟡
- **File:** `app/Modules/Academy/Services/CompetencyGateService.php` → `authorizeAction()` is complete but has no callers (only `registerRequirement()` is used).
- **Done when:** a middleware/policy invokes `authorizeAction()` on the sensitive routes the spec §14 names (e.g. privacy-cert for sensitive PHI access, interop training for production API); a test confirms an uncertified actor is blocked.

### GAP-009 — Security P0s: no MFA; audit archive not immutable — 🔴
- **Files:** no `totp/google2fa/two_factor` anywhere; login is single-factor (`app/Http/Controllers/.../MobileAuthController.php` uses `PatientOtpCode` as the *primary* factor). `app/Console/Commands/ArchiveAuditLogs.php` archives via `Storage::append` (L95) — not WORM/Object Lock.
- **Done when:** TOTP (or equivalent second factor) enrollment + challenge is enforced for staff/admin login; audit archives land in an immutable store (S3 Object Lock or equivalent) with verification.

---

## Tier 2 — Medium

### GAP-010 — SDKs expose ~6 of ~24 method groups — 🟡
- **Files:** `sdk/php/src/Modules`, `sdk/python/src/opescare/modules`, `sdk/typescript/src/modules` (only HealthIds, Patients, Consents, Records, Fhir, Webhooks).
- **Done when:** inventory/availability/reservation, dispense push, document push, lab amendment, sync-status, and reconciliation methods exist in all three SDKs with parity to the published method list.

### GAP-011 — Telemedicine calls cannot connect — 🟡
- **File:** `app/Modules/Telemedicine/Services/CallProviderService.php` → `initiateCall()` only creates a `CallSession` with a hashed `room_id`; no WebRTC/Twilio/Agora/TURN/ICE token issuance.
- **Done when:** a call-provider adapter issues per-session media tokens + ICE/TURN config, gated by the existing recording-consent check; a test obtains a joinable session.

### GAP-012 — Offline EMR caching not encryption-gated — 🟡
- **File:** `app/Modules/Offline/Services/OfflinePolicyService.php` (policy records only; no device AES-256 attestation before caching).
- **Done when:** the policy gate requires and verifies device-side AES-256 encryption before allowing EMR/Medical-ID caching.

### GAP-013 — Bridge agent: CSV connector only; queue encryption unproven — 🟡
- **Files:** `bridge-agent/opescare_bridge/connectors/` (only `csv_connector.py`); `bridge-agent/opescare_bridge/queue/local_queue.py` (SQLite, no encryption layer).
- **Done when:** Excel/JSON/XML/SFTP/DB connectors exist (or are explicitly descoped); the local queue is encrypted at rest.

### GAP-014 — OpenAPI contract covers ~6 of ~25+ endpoints — 🟡
- **File:** `contracts/openapi/opescare-connect-v1.yaml`.
- **Done when:** every live Connect endpoint is documented and the contract is used in SDK/partner validation.

### GAP-015 — Appointment reminders never dispatched — 🟡
- **Files:** `app/Modules/Appointments/Services/AppointmentReminderService.php` (`scheduleReminders`/`getDueReminders` have no callers); `routes/console.php` schedules no reminder command.
- **Done when:** a scheduled command/job dispatches due reminders (and booking confirmations) via the existing `PushNotificationService`/`WhatsAppNotificationService`; a test asserts a due reminder is sent.

### GAP-016 — Missing Connect endpoints — 🟡
- **File:** `routes/api.php` (`connect` group).
- **Missing:** medication & blood availability/reservation, blood needs/transfers, dispense-event push, document push, referral package pull, lab-result amendment, sync-status.
- **Done when:** the documented Connect endpoints exist with consent + idempotency middleware, or are explicitly descoped in the spec.

### GAP-017 — Data residency & retention legal-hold are review flags, not runtime controls — 🟡
- **Files:** `CountryDataResidencyRule` consumed only as a checklist boolean in `app/Modules/CountryExpansion/Services/CountryExpansionService.php`; `app/Services/Compliance/DataRetentionService.php` `enforce()` has no `legal_hold` pause.
- **Done when:** cross-border PHI/backup/export operations are blocked by residency rules at runtime; retention enforcement skips records under legal hold.

### GAP-018 — Webhook subscription management incomplete — 🟡
- **File:** `routes/api.php` (`v1/connect` exposes create + replay only).
- **Done when:** list / update / pause-resume / test-send endpoints exist for webhook subscriptions.

### GAP-019 — Emergency access not time-boxed — 🟡
- **Files:** `EmergencyAccessEvent` (migration `2026_05_14_224355…`); `app/Modules/Governance/Services/EmergencyAccessService.php` (logs reason + review case, no `expires_at`).
- **Done when:** emergency grants carry a TTL (e.g. 60 min) with enforced expiry; a timed compliance-officer notification fires per SOP-004.

---

## Tier 3 — Low (polish)

- **GAP-020** Mobile accessibility `Semantics` labels missing on Home/Login key widgets (`apps/mobile-patient/lib/.../home_screen.dart`, `login_screen.dart`).
- **GAP-021** Mobile `appointment_booked` analytics event not logged (`book_appointment_screen.dart`).
- **GAP-022** Health-ID `idCard` icon swap not done; bottom nav uses `heart` (`main_shell.dart`).
- **GAP-023** App icon/splash config blocks absent in `apps/mobile-patient/pubspec.yaml`; `assets/icon/` has no `icon.png`.
- **GAP-024** Backup cadence daily vs SOP-020 hourly (`routes/console.php`).
- **GAP-025** SDK pagination helpers and `ReconciliationRequiredError`/`FacilitySuspendedError` types missing (`sdk/*`).
- **GAP-026** KMS per-facility AAD domain separation not implemented; AAD empty (`app/Services/Security/KmsEncryptionService.php`).
- **GAP-027** Provider-mobile offline-limited mode absent (`app/Http/Controllers/Api/ProviderMobile/*`).
- **GAP-028** Automated PII scanning on API responses absent (threat-model §8 P2).

---

## Tech-debt found during the sweep (not spec gaps, but pre-deploy cleanup)

- **TD-001 — Duplicate service folders.** `app/Services/Payment/` vs `app/Services/Payments/` (both implement MTN MoMo + Orange Money); `app/Services/Integration/Dhis2Service.php` vs `app/Services/Interoperability/Dhis2PushService.php`; `ProviderPerformanceService` in both `Reports/` and `Staff/`; `CarePlanService` in both `Clinical/` and `PatientEngagement/`; `WaitlistService` in both `app/Modules/Appointments/Services/` and `app/Services/Appointments/`. **Action:** confirm which is canonical, delete/merge the other to avoid divergent behavior.
- **TD-002 — Loose debug/patch scripts in the tree.** `patch_diagnosis.php` (repo root); `apps/api-laravel/{col_check.php,col_check2.php,col_verify.php,fid_check.php,seal_check.php}`; `apps/api-laravel/scratch/generate_partner_*.php`. **Action:** remove before deploy (not autoloaded production code).
- **TD-003 — Stray nested directory.** `apps/api-laravel/apps/api-laravel/public/images/leaflet/*` (3 leaflet marker PNGs in an accidental nested path). **Action:** delete the nested `apps/api-laravel/apps/` tree.

---

## Coverage note (what this revision actually swept)

Now covered: `app/Modules/` (45), `app/Http/Controllers/` (162), `routes/*`, `database/migrations/` (177), `app/Services/` (64 classes across 23 subfolders), `app/Console/Commands/` (18, with `routes/console.php` schedule), interop (`Integration/`, `Interoperability/`), payments, `Ussd/`, `Lab/` (incl. `DicomWebService`), `Documents/`, `Security/` (KMS), the mobile app, SDKs, widget, and bridge agent. **Tests:** 169 Feature tests across 28 areas + 4 Unit tests exist (`tests/Feature`, `tests/Unit`) — present and broad, but **not executed in this audit**; a green `php artisan test` run should be part of go/no-go and is not yet verified here.
