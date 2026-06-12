# Mobile hardening — changes 2026-06-11

PHI-protection and launch-readiness changes to the patient app. **One required step
before the app will build** (localization codegen) — see §Required.

## What changed

### 1. Screenshot / screen-recording protection (done, native) 🟢
`android/app/src/main/kotlin/.../MainActivity.kt` now sets `FLAG_SECURE` in `onCreate`.
This blocks screenshots and screen recording and hides app content in the recent-apps
switcher — patient health data must not leak there. No build step needed.
*(iOS has no exact equivalent; if you want parity, add a privacy blur on
`applicationWillResignActive` in `AppDelegate`. Not done here.)*

### 2. French localization foundation (done, needs codegen + translation) 🟡
Gabon is francophone; the app was English-only. Added the i18n framework:
- `pubspec.yaml`: added `flutter_localizations` and `generate: true`.
- `l10n.yaml`: gen-l10n config (output class `AppLocalizations`).
- `lib/l10n/app_en.arb` + `lib/l10n/app_fr.arb`: 36 starter strings, full en/fr parity.
- `lib/app.dart`: wired `localizationsDelegates` + `supportedLocales` on `MaterialApp.router`.

**Still to do (needs a French speaker + a build env):** expand the ARBs to cover all
screens, and migrate hardcoded UI strings to `AppLocalizations.of(context).<key>`.
This is incremental — the framework is in place; screens can move over one at a time.

### 3. Forced-update gate (done as a file, NOT wired) 🟡
`lib/core/update/force_update_service.dart` — a self-contained, **fail-open** force-update
client + blocking screen + optional `ForceUpdateGate` wrapper. It is **not** wired into
startup (so it cannot affect app boot until you do it deliberately).
To activate: implement the backend endpoint `GET /mobile/app-config`
(`{min_supported_build, latest_version, store_url}`), bump `currentBuildNumber` per release,
and wrap the app body with `ForceUpdateGate` (see the file's header doc).

## Required before the next build ⚠️

The localization wiring imports a generated file. Run this once (regenerates
`AppLocalizations`); the app will not compile until you do:

```bash
cd apps/mobile-patient
flutter pub get          # with generate:true this also runs gen-l10n
# (or explicitly) flutter gen-l10n
flutter analyze
flutter build apk --debug   # smoke-test the build
```

If `flutter pub get` reports an `intl` version conflict (flutter_localizations pins intl),
let pub resolve it / adjust the `intl` constraint as advised — it's a version solve, not a code issue.

## Not done (my genuine recommendations that still need a build env / your input)

- **App-lock (biometric/PIN) + inactivity auto-logout** — high-value PHI protection on
  shared phones. Needs `local_auth`, a lock screen, and a lifecycle timer; I didn't add it
  blind because it touches app lifecycle and needs build verification.
- **Certificate pinning** — MITM hardening for PHI in transit (fast-follow).
- **Firebase configuration** (GAP-002) — push/Crashlytics/Analytics remain inert until
  `flutterfire configure` is run against your Firebase project.
