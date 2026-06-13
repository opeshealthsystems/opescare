# OpesCare Patient Mobile App — Production Readiness Design

**Date:** 2026-06-13
**Status:** Approved (design) — pending implementation plan
**Scope owner:** Mobile (Flutter) + supporting Laravel backend endpoint

## Goal

Take the OpesCare Patient Flutter app (`apps/mobile-patient`) from ~70–80%
to 100% production-ready for the **Cameroon** market (bilingual EN/FR, XAF,
MINSANTE context), closing every gap identified in the 2026-06-13 assessment.

## Constraints & execution mode

- **Plan-only this session.** No Flutter code is written or built here: the
  session has no Flutter SDK and GitHub is network-blocked. This document plus
  the implementation plan are the deliverables. Execution happens in a Flutter
  build environment.
- The single **backend** piece (`GET /api/v1/mobile/app-config`) is buildable in
  `apps/api-laravel` and is specified here for that later execution.
- Honor standing constraints: do not delete/override existing work; build only
  what is missing; keep everything Cameroon-only.

## Current state (verified)

- 109 Dart files, clean feature architecture (Riverpod, go_router, dio,
  `flutter_secure_storage`). Data layer is real (19 repos call the live API;
  zero mock data). Token storage is encrypted.
- Android `FLAG_SECURE` screenshot protection: **done**.
- i18n framework wired (en/fr ARBs, 36 starter strings) but **0 of 32 screens**
  migrated; generated `AppLocalizations` not yet produced (app will not compile
  until `flutter gen-l10n` runs).
- API base URL defaults to `http://opescare.test/api` (dev, plain HTTP).
- Firebase deps present but inert (no project configured).
- Force-update service file exists but is not wired; backend endpoint missing.

## Work-streams

### A — Build & ship blockers (critical path)
- **A1 Localization codegen.** Run `flutter pub get` / `flutter gen-l10n`;
  resolve any `intl` version solve; confirm `AppLocalizations` generates and the
  app compiles.
- **A2 Production API config.** Keep `String.fromEnvironment('API_BASE_URL', …)`
  but change the default to a safe non-dev value and require an HTTPS production
  URL injected at build time via `--dart-define=API_BASE_URL=https://<prod>/api`.
  Document the release build command. Reject/avoid silent HTTP in release.
- **A3 Release config.** Android signing keystore + `build.gradle` signingConfig;
  iOS bundle id + signing; verify launcher icons/splash; version/build bump
  scheme (`version: x.y.z+build`).

### B — Backend support (Laravel — buildable independently)
- **B1 `GET /api/v1/mobile/app-config`.** Returns
  `{ min_supported_build, latest_version, store_url }`. Values from config/env so
  ops can bump without a deploy. Public (no auth) — it gates the app before login.
  Add a controller + route under the existing `v1` group; add a feature test.
- **B2 Force-update wiring (Flutter).** Wrap app body in `ForceUpdateGate`
  (already implemented, fail-open); set `currentBuildNumber` per release; point
  it at `ApiEndpoints.baseUrl + /mobile/app-config`.

### C — Localization (all 32 screens, EN/FR)
- **C1** Expand `app_en.arb` + `app_fr.arb` to cover every UI string (full parity).
- **C2** Migrate all 32 presentation screens from hardcoded English to
  `AppLocalizations.of(context)`; one feature at a time to keep diffs reviewable.
- **C3** Locale switcher + persisted preference; verify fr-FR date/number/currency
  formatting (XAF, no decimals).

### D — Security hardening (PHI-grade)
- **D1 App-lock.** `local_auth` biometric/PIN lock screen + inactivity
  auto-logout timer on app lifecycle.
- **D2 Certificate pinning** on the dio HttpClient for the production API host.
- **D3 iOS privacy blur** on `applicationWillResignActive` in `AppDelegate`
  (parity with Android FLAG_SECURE).
- **D4 Root/jailbreak detection** — warn or block on compromised devices.
- **D5 PHI-safe logging** — redact tokens/PHI from logs and (later) crash reports.
- **D6 Session inactivity timeout** — server-token-aware session expiry distinct
  from the app-lock timer.

### E — Observability (Firebase — create + wire, kept inert until configured)
- **E1** Create Firebase project; run `flutterfire configure` (generates
  `firebase_options.dart` + platform config). This is an operator step.
- **E2** Wire Crashlytics (with PHI redaction), Analytics (privacy-safe events),
  and Messaging (push). All initialization guarded so a missing config never
  breaks app boot (ship-safe before E1 completes).

### F — Release readiness
- **F1** `flutter analyze` clean; `flutter test` passes; smoke-build APK + IPA.
- **F2** Store assets: privacy policy URL, Play data-safety / App Store privacy
  declarations, screenshots, store listing (EN/FR).
- **F3** Cameroon audit: confirm no Gabon/other-country strings remain after i18n
  (the lone `Gabon` comment was already corrected on 2026-06-13).

## Sequencing

```
A1 ─┬─> (B, C, D run in parallel) ─> E ─> F
A2 ─┤
A3 ─┘
```

A is the critical path — nothing builds or ships without A1 (codegen) and A2
(production URL). B/C/D are independent and parallelizable. E can proceed once a
Firebase project exists. F is the final gate.

## Out of scope

- Provider/staff mobile app (separate `ProviderMobile` API surface exists; not
  this effort).
- New product features beyond closing the readiness gaps.
- Backend changes beyond B1.

## Success criteria (definition of "100%")

1. App compiles and builds release artifacts (APK + IPA) clean.
2. Release build points at the production HTTPS API; no dev/HTTP default ships.
3. All 32 screens render in EN and FR; locale switch persists.
4. Force-update gate active against a live `/mobile/app-config`.
5. App-lock, cert pinning, iOS privacy blur, root detection, PHI-safe logging,
   and session timeout all in place.
6. Crashlytics/Analytics/push functioning once Firebase is configured; app boots
   safely if not.
7. `flutter analyze` + tests pass; store-listing assets and privacy declarations
   ready; no non-Cameroon content.
