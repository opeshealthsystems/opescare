# OpesCare — Go-Live Readiness Checklist

**Date:** 2026-06-11
**Verdict (planning-grade):** The **core clinical platform (backend API + patient mobile app)** is on the cusp of deployable — gated by the P0 list below plus a green test run. The **Connect / developer platform** (widget, SDKs, webhook events) is **not** ready and should be descoped from the first launch or run on its own track. This is a static-audit assessment; it is **not** a security certification — items §A1 (tests) and §A4 (dependency/pentest) must be executed to confirm.

Companion docs: `CLAUDE.md` (entry point) · `AS_BUILT_IMPLEMENTATION_REGISTER.md` (current state) · `audits/SPEC_VS_CODE_GAP_AUDIT.md` (numbered gaps) · `audits/VERIFICATION_RUNBOOK.md` + `audits/route_auth_check.py` (how to verify).

---

## Update — 2026-06-13 (deployment delta since the 2026-06-11 audit)

Changes landed locally this session that the **next production deploy must account for**.
(All commits are in local `main` but **not yet on origin** — GitHub was network-blocked;
`git push origin main` must succeed before deploying. ~10 commits pending.)

**Must run on production DB (new migration):**
- [ ] `php artisan migrate` — applies `2026_06_13_000001_fix_subscription_currency_to_xaf`
  (flips `subscription_plans` / `subscription_invoices` currency default NGN → **XAF** + back-fills).
  Already run on the dev `opescare` DB this session; production still needs it. Pairs with the
  earlier `lite_configs` XAF fix. All billing is now XAF/FCFA (Cameroon); no Naira anywhere.

**New env vars to set (now in `.env.example`):**
- [ ] `MOBILE_MIN_SUPPORTED_BUILD`, `MOBILE_LATEST_VERSION`, `MOBILE_STORE_URL` — drive the new
  public endpoint `GET /api/mobile/app-config` (patient-app forced-update gate). Safe defaults ship;
  set real values before publishing a mobile build.

**Behaviour changes ops should know:**
- [ ] `/document-preview` (document template gallery) is now **login-gated in production** — public
  only when `demo.enabled=true` (sales walkthroughs). Previously open to anonymous visitors.
- [ ] New public route `GET /api/mobile/app-config` (no auth, gates the app before login) — verify it
  is reachable from the `mobile-api` subdomain.

**Patient mobile app — NOT yet shippable (do not publish the build):**
- Backend mobile API is complete & verified (20/20 mobile tests green). But the Flutter app still needs
  A1 (l10n codegen — won't compile without it), C (EN/FR across 32 screens), D1/D2/D4/D6 (app-lock,
  cert pinning, root detection, session timeout), E (Firebase), and F (release build/signing/store).
  Full task list + status: `apps/mobile-patient/docs/superpowers/plans/2026-06-13-mobile-patient-production-readiness.md`.
- Code-complete this session (unverified — no Flutter SDK here): production HTTPS URL guard, force-update
  wiring, iOS privacy blur, PHI-safe logger. These need `flutter analyze` before relying on them.
- ⇒ **Deploy the web/API platform independently of the mobile app.** The app is a fast-follow, not a blocker.

---

## Component readiness at a glance

| Component | State | Blocking items |
|-----------|-------|----------------|
| Backend API | 🟡 Conditionally ready | P0: ~~GAP-005~~ ✅, ~~GAP-006~~ ✅, GAP-009 (MFA+audit) remaining; config hardening; green tests |
| Patient mobile app | 🟡 Close | GAP-002 (Firebase); GAP-001 ✅ fixed |
| Interop (DHIS2/HL7/MoMo/Orange/USSD) | 🟡 Built, unverified live | Credential/config + end-to-end test |
| Connect dev platform (widget/SDK/webhooks) | 🔴 Not ready | GAP-003/004/010/013/014/016/018 — descope or separate track |
| Ops / infra | 🟡 | Server `.env` hardening; backup cadence |

---

## A. Must verify (run these — currently unknown)

- [ ] **A1 — Test suite green.** `php artisan test` (uses in-memory SQLite; no DB setup). 169 Feature + 4 Unit tests exist but were never executed in the audit. **This is the single biggest unknown.**
- [ ] **A2 — Migrations applied.** `php artisan migrate:status` against the Postgres `opescare` DB → any **Pending** = not run. Back up, then `php artisan migrate`. (177 migrations; newest 2026-06-09.)
- [ ] **A3 — Authoritative route-auth sweep.** `php artisan route:list --json > routes.json && python docs/audits/route_auth_check.py routes.json`. Confirm no PHI route is unauthenticated. (Spot-checks were clean; full sweep pending.)
- [ ] **A4 — Dependency vulnerability scans.** `composer audit` · `pip-audit` (bridge-agent, sdk/python) · Flutter `pana`. (`npm audit` on api-laravel already = 0 vulnerabilities.)
- [ ] **A5 — Working tree committed.** `git status` / `git log --oneline -15`. Expect many doc renames (the consolidation) + new docs + the mobile-manifest fix — review and commit.

## B. P0 — fix before deploy (safety / correctness / security)

- [x] **B1 — GAP-006** Visit-closure safety guards. ✅ Done 2026-06-13 (commit 869a81f5). `VisitManagementService` now blocks `→ completed` on unacknowledged critical alert / missing consult note (consultation-bearing types) / open queue ticket. 6 feature tests.
- [x] **B2 — GAP-005** Data-import execution. ✅ Done 2026-06-13 (commit 019c2525). Patient imports run through `PatientIdentityService` (MPI dedup + canonical CM-HID + audit), provenance via `import_record_links`, real rollback. 3 feature tests. *(Requires migration `2026_06_13_000002` on production.)*
- [~] **B3 — GAP-009** MFA + immutable audit. **Audit half ✅ done 2026-06-13** (commit pending): `ArchiveAuditLogs` now writes to a configurable immutable disk (`config/audit.php` → S3 Object Lock/WORM in prod) as write-once `put()` objects (replacing mutable `Storage::append`), each with a SHA-256 digest sidecar + integrity check before the hot table is purged. 2 feature tests. **Set `AUDIT_ARCHIVE_DISK` to a WORM S3 bucket in prod.** — **MFA — scaffold done 2026-06-13; enforcement still TODO.** Built (no new dependency, RFC 6238 TOTP in pure PHP, pinned to the RFC test vector): migration `2026_06_13_000003` (encrypted `two_factor_secret` / `two_factor_recovery_codes` / `two_factor_confirmed_at` on users), `config/mfa.php`, `App\Modules\Auth\Services\TwoFactorService` (secret gen, provisioning URI, verify with ±1 window, recovery codes), and `User::hasTwoFactorEnabled()` / `requiresTwoFactor()`. 6 tests. **Remaining (browser-testable session):** enrollment UI (show QR + confirm code + store recovery codes), a challenge step in `PublicPageController::submitLogin` (hold user in session → verify → complete), and route enforcement for `MFA_REQUIRED_ROLES`. Deliberately NOT wired into login yet — that change must be manually exercised to avoid staff/admin lockout.
- [ ] **B4 — Config hardening (server `.env`).** `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`, `SESSION_SECURE_COOKIE=true`, rotated real `APP_KEY` + secrets via env, HTTPS + HSTS. Confirm `ProductionSafetyServiceProvider` enforces debug-off. *(Working copy `.env` is currently `local`/`debug=true`.)*
- [ ] **B5 — GAP-008** Enforce the Academy competency gate at sensitive routes (only if training-gated access is a compliance requirement for launch).

## C. P1 — important (before or immediately after go-live)

- [ ] **C1 — GAP-007** Wire the interactive public-health `submitReport` to the real `Dhis2Service` (the batch DHIS2 path already works).
- [ ] **C2 — GAP-015** Schedule appointment reminders/confirmations (service exists; no cron wired).
- [ ] **C3 — GAP-012** Enforce device AES-256 encryption before offline EMR caching.
- [ ] **C4 — GAP-002** Configure Firebase for the mobile app (`flutterfire configure` + Gradle plugins) — needs the owner's Firebase project.
- [ ] **C5 — GAP-011** Real telemedicine call provider (WebRTC/Twilio/Agora) — only if telemedicine is in launch scope.
- [ ] **C6 — TD-001/002/003** Remove duplicate service folders (`Payment/` vs `Payments/`, two DHIS2 services, etc.), loose debug scripts (`patch_diagnosis.php`, `col_check.php`, `fid_check.php`, `seal_check.php`, `scratch/*`), and the stray nested `apps/api-laravel/apps/` directory.

## D. P2 — Connect developer platform (descope unless launching it)

- [ ] **D1 — GAP-003** Build the embeddable widget + validate `wgt_session_*` tokens (expiry/origin/scope).
- [ ] **D2 — GAP-004** Emit the ~28 documented webhook events (only `lab_result.released` + `patient.updated` fire today).
- [ ] **D3 — GAP-010** Complete the SDK method set (inventory/availability/dispense/document/sync-status/reconciliation).
- [ ] **D4 — GAP-013/014/016/018** Bridge connectors + queue encryption; full OpenAPI; missing Connect endpoints; webhook subscription management.

## E. Pre-launch ops

- [ ] **E1** Confirm DHIS2 / MTN MoMo / Orange Money / WhatsApp credentials are set for the target environment and connect end-to-end (not just present in code).
- [ ] **E2** Backup cadence per SOP-020 (currently daily; SOP asks hourly for DB) — `routes/console.php`.
- [ ] **E3** Build frontend assets (`npm run build`) and cache config/routes (`php artisan config:cache route:cache`) on the server.
- [ ] **E4** Mark the stale `audits/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md` as historical (banner already added).

---

## Suggested sequence

1. **A1–A2** first — does it work, is the DB current? (Cheapest, highest signal.)
2. **B1–B5** — close the safety/correctness/security P0s.
3. **A3–A4** — prove auth coverage and clean dependencies.
4. **C** items — important fixes; **E** ops prep.
5. Descope **D** (Connect) unless a partner integration is a launch requirement.

Paste the output of `php artisan migrate:status` and `php artisan test` and I'll triage results and fold any failures into `SPEC_VS_CODE_GAP_AUDIT.md` as new numbered items.
