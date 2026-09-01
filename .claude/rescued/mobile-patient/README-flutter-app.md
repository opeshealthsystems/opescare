# opescare_patient

OpesCare Patient mobile app (Flutter) — Cameroon (bilingual EN/FR).

## Development

```bash
flutter pub get          # also runs gen-l10n (generate: true) → AppLocalizations
flutter run              # uses the debug default API host (http://opescare.test/api)
```

To point a debug run at another API host:

```bash
flutter run --dart-define=API_BASE_URL=https://mobile-api.opescare.com/api
```

## Release build (production)

The production API URL **must** be injected at build time. Release builds refuse
to start with the local HTTP dev default (`ApiEndpoints.baseUrl` throws), so
cleartext PHI traffic can never ship by accident. Replace the host with the real
production domain.

```bash
flutter build apk       --release --dart-define=API_BASE_URL=https://mobile-api.opescare.com/api
flutter build appbundle --release --dart-define=API_BASE_URL=https://mobile-api.opescare.com/api
flutter build ipa       --release --dart-define=API_BASE_URL=https://mobile-api.opescare.com/api
```

Bump `version: x.y.z+<build>` in `pubspec.yaml` each release, and keep
`ForceUpdateService.currentBuildNumber` in sync so the forced-update gate works.

## Production readiness

See `docs/superpowers/plans/2026-06-13-mobile-patient-production-readiness.md`
for the full implementation plan (build, i18n, security, observability, release).
