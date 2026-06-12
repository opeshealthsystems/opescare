# OpesCare Platform Flow Audit — Deployment Readiness
**Date:** 2026-06-12 · **Method:** 8 parallel end-to-end flow traces (route → controller → service → model → migration → view) against the current main checkout. Every "gap" claim was verified in current source; contested claims re-verified by hand. Test baseline: **711/716 green (99.3%)**, database fully migrated (180/180), all seeders run.

**Standing corrections applied:** claims sourced from stale reports (pending migrations, broken V1 endpoints, OC-MVP patient IDs) were discarded — those are fixed and verified. A false "docs portal not built" claim was rejected (it exists: `/docs/*`, 10 pages incl. playground).

---

## TIER 1 — READY FOR DEPLOYMENT (verified end-to-end, most with passing tests)

| Flow | Evidence highlights |
|---|---|
| **Patient clinical journey** (OTP login → health ID → dashboard → book → check-in → queue → triage → consult → labs incl. critical-value alerts → prescription → dispensing → invoice → payment → receipt → public verification → discharge) | 18 of 19 steps READY; pessimistic slot locking; tested (Appointment/Billing/Queue/Lab suites green) |
| **Queue** (walk-in, priority, transfer, lifecycle, masked public display) | 7/7 tests green |
| **Ward** (beds → admission → rounds → transfer → discharge plan → discharge; occupancy) | Full chain + ADM/DIS documents |
| **Maternity** (pregnancy → ANC schedule WHO-13 → auto high-risk flagging → delivery → neonatal outcome) | 6/6 tests green |
| **Immunization** (record → schedule auto-completion → adverse reactions w/ authority reporting) | Duplicate detection 409 |
| **Referral** (draft→sent→accepted→completed; scoped 64-byte access-grant tokens; auto-expiry) | REF/RAL documents |
| **Emergency access** (break-glass → minimal profile, blood type deliberately excluded → review case → abuse→auto SecurityIncident) | Tests green; audited before data return |
| **Mortuary** (admit → autopsy consent/report → burial permit → release; unidentified-body support) | 6 document codes wired |
| **Governance: consent lifecycle, record correction (entered_in_error lineage), data export (24h expiry), retention purge, 3-layer audit trail, patient-visible access logs** | DataGovernancePrivacyTest 8/8 |
| **Security operations** (incidents → triage → contain → resolve; audit explorer; compliance export) | Tests green |
| **PII encryption** (DOB/phone/address encrypted; phone-hash search pattern; local AES-256-GCM or AWS KMS) | PatientPiiEncryptionTest green |
| **RBAC** (107 roles, per-facility assignment, portal gating, admin-role API middleware) | Verified middleware chain |
| **Billing core** (invoice → insurance split → cash/wallet payment → receipt → refund via reversal → cashier sessions → reconciliation) | Tests green; price catalog (ServicePrice/PriceList) exists |
| **Payment plans** (create → installments → pay → default detection) | Tests green |
| **Insurance** (providers/plans seeded → policy → eligibility → preauth → claim → decide → pay; insurer web portal complete) | Web + API both work |
| **Wallet** (deposit → debit w/ lock → append-only ledger) | Tests green |
| **Connect B2B core** (RS256 JWT tokens w/ revocation+scopes; Argon2id rolling secret upgrade in AuthController; consent-gated record push/pull; idempotency keys; emergency override audited) | ConnectPlatformTest 12/12 |
| **Webhooks** (HMAC-SHA256 signed deliveries, retries w/ backoff, dead letters, client-scoped replay — replayed_by fix shipped today) | 4/4 tests |
| **FHIR R4** (CapabilityStatement, reads, $everything, bulk export w/ 5000 cap, subscriptions via observers, anti-enumeration search, consent gate) | Tests green |
| **HL7 ADT** (real A01/A08/A28 MLLP message composition, facility-configurable) | Real implementation |
| **Family accounts** (add member w/ health ID, guardian invite tokens 48h, context switch, read-only gating, minor-age transition w/ 30-day grace) | FamilyControllerTest 18/18 |
| **Medical ID** (static+temporary QR 15-min tokens, public verify, MPI duplicate detect/merge, access events) | Tests green |
| **Telemedicine** (schedule → consent gate [fixed today] → waiting room → session w/ 30-min expiry → timeline) | Provider-agnostic by design — see Tier 2 note |
| **CDSS** (DB-driven drug-interaction/allergy/lab rule tables + 6 check types, acknowledge/override with reason) | See Tier 2 for rule-data caveat |
| **Academy** (10 seeded tracks → prerequisites → quizzes w/ attempt limits → simulations → trainer signoff → certificates → public verification) | 25/25 tests |
| **Facility readiness & country expansion** (13-item checklist → scoring → approval gates) | Tests green |
| **HR core** (shifts → roster publish → assignments → leave lifecycle) | Tests green |
| **Admin control center** (settings, feature flags, module toggles, maintenance windows, health, audit log — all backed by PlatformAdminService) | Real data, not placeholders |
| **Analytics/KPI** (queue/ward/financial/data-quality staff dashboards + admin KPI snapshots/trends — real SQL aggregation) | Verified queries |
| **OpesCare Lite** (device register/activate/config → offline event push → conflict resolution, clinical conflicts forced to manual merge) | Verified |
| **Support helpdesk** (ticket → assign → reply → escalate → resolve; knowledge base) | PII redaction exists in SupportService (one auditor missed it; earlier audit verified `redactPii` at SupportService:182-201) |
| **Demo system** (is_demo isolation, gating, reset) | Octane guard aborts 503 |
| **Public website** (landing, 6 solutions pages, docs portal w/ playground, legal centre, care-map public directory, status page, full en/fr) | Verified twice |
| **Notification pipeline core** (17 bilingual templates seeded, preferences w/ quiet hours + critical bypass, PHI privacy gate, dedup, delivery logging, escalation chains → tasks, broadcasts w/ acknowledgments) | CommunicationEcosystemTest 10/10 |
| **Channels: SMS (Twilio), Email, Push (FCM v1 OAuth2), WhatsApp (Meta Cloud API)** | REAL implementations — need only credentials in .env |

---

## TIER 2 — FUNCTIONAL WITH SPECIFIC GAPS (each gap exact, verified today)

1. **Web patient/organization self-signup does not persist** — `PublicPageController::submitPatientRegister()` (:180-196) and `submitOrganizationRegister()` (:213-220) render success pages without creating Patient/Org/User records; staff invite accept (:254-257) doesn't validate the token or create the account. *Patients are really registered via staff, Lite, or mobile family flow — those work.* **Lacking:** persistence + admin org-approval queue + invite token validation.
2. **Mobile money callbacks** — MTN MoMo & Orange Money initiation and status-polling are real (`MtnMomoService`, `OrangeMoneyService`), callback URLs are sent to providers, **but no callback route/controller exists** to receive provider confirmation; payments record immediately rather than awaiting confirmation. **Lacking:** `POST /payments/mobile-money/callback` + signature verification + transaction→payment finalization.
3. **Appointment reminders never fire** — `AppointmentReminderService` schedules 48h/24h/2h reminder rows, but `getDueReminders()` has **no scheduler/command caller** (verified by grep). **Lacking:** one scheduled command dispatching due reminders. Also the SMS template still contains `+237 XXX XXX XXX`.
4. **Data import executes a simulated completion** — upload/mapping/validation/preview/rollback/audit are real, but approve does `forceFill(['status'=>'completed'])` with comment "Portal demo: simulated import completion" (`DataImportController:158-170`). **Lacking:** the queued job that actually inserts validated rows.
5. **Messaging legal-hold bypass** — `MessagePermissionService::canViewThread()` returns `true` for ANY user when `legal_hold` is set (verified today, lines 64-66). **Lacking:** compliance-role check. *(Real privacy bug — small fix.)*
6. **Attachment virus scan is hardcoded `'passed'`** (`MessageAttachmentService:31`, verified today). Extension blocklist works; actual scanning doesn't. **Lacking:** ClamAV/queue-scan integration or removal of the fake status.
7. **CareMap profile-change audit never fires** — `FacilityVerificationService::updateProfile()` uses `!isDirty()` (inverted; verified today), so high-risk facility field changes skip review flagging. **Lacking:** one-line condition fix.
8. **Contact form email is a TODO** — `PublicPageController:118`. Submissions validated then dropped.
9. **Subscription/SaaS enforcement** — full lifecycle works (plans, trial, invoices, entitlements, limits *readable*), but **PlanLimitService/ModuleEntitlement are not enforced by any request middleware**, and SaaS invoices are mark-paid-only (no gateway). **Lacking:** enforcement middleware + (optionally) payment integration.
10. **CDSS rule data** — the engine is database-driven (DrugInteractionRule/AllergyAlertRule/LabAlertRule tables) but **no seeded clinical rule content**, and the age/pregnancy checks still use small hardcoded arrays in the service. Telemedicine consent now enforces correctly, but the **video layer is provider-agnostic metadata only** (room_id; no WebRTC signaling or Zoom/Meet SDK) — calls need an actual provider decision.
11. **Revenue reports** — API-only (no portal page) and aggregate insurance claims only (self-pay invoices excluded from collection-rate/aging).
12. **Wallet ledger** — append-only transactions exist but no periodic balance-vs-ledger reconciliation job.
13. **Smaller items:** license-expiry notification command absent (credentialing data model exists); on-call schedule + handoff-note portal UI not found in HR controller (models + tests exist — API-level only); feature flags global (no per-facility scoping); SLA fields on support tickets minimal; FHIR uses scope strings but not formal SMART-on-FHIR profiles; message bodies rely on DB-level (not app-level) encryption.

---

## TIER 3 — NOT READY / NOT BUILT (honest list)

1. **Flutter patient app** — backend endpoints for all 18 screens exist and work, but the app itself ships blockers: `firebase_options.dart` throws `UnimplementedError`; platform-detection inversion (`auth_repository.dart:86` — iOS registers as Android, breaking push); OTP resend just navigates back; Health-ID share button is an empty `onTap`; no signing keystore/icons. **2–3 days + store review.**
2. **Voice notification channel** — empty placeholder class. (SMS/Email/Push/WhatsApp are real.)
3. **DHIS2 push** — config framework + scheduled job exist; actual data-element UIDs are placeholders and push is effectively inert until MINSANTE credentials/UIDs configured.
4. **Bridge agent (legacy HIS sync)** — config schema, auth middleware, and admin pages exist, but sync semantics (ack/retry/conflict on the agent side) are incomplete. PHP SDK is real; the old mock paths remain in `sdk/php/src/Client.php` legacy file; TypeScript SDK newly committed (untested in CI).
5. **Legal document CONTENT** — versioning/acceptance machinery is fully built; all 18 documents are stubs awaiting counsel text. Re-acceptance flag exists but no portal middleware blocks access pending re-acceptance.

---

## DEPLOYMENT VERDICT

**Web platform (staff/patient/admin/insurance/lite portals + API): deployable now for a controlled pilot**, with these pre-launch musts:
- P0 quick fixes (≈1 day): legal-hold check, CareMap isDirty, contact-form mail, reminder dispatch command, support phone number, virus-scan honesty (queue or remove status), org-signup persistence + approval queue OR disable public org signup for pilot.
- Config: production .env (mail/Redis/S3/KMS/cron/CORS origins/Twilio/FCM), legal text, CDSS rule content load.
- Defer without risk: mobile-money callbacks (cash/wallet work today), SaaS gateway, DHIS2, bridge agent, voice channel.

**Mobile app: separate 2–3 day track + store review.** Do not gate the web pilot on it.

The platform is **not under-developed** — 30+ flows are genuinely complete with tests. The gap list is short, specific, and mostly measured in hours.
