# OpesCare — Verification Runbook

The deploy-readiness checks below need a PHP runtime and package-registry access.
They could **not** run in the assistant's sandbox (no PHP; network locked down — no
`apt`, no static-binary download, pip/composer registries 403-blocked). They are
quick to run in your **Laragon** environment, which already has PHP 8.3 + Composer.

Run everything from `C:\laragon\www\opescare\apps\api-laravel` unless noted.

## 1. Test suite (the biggest unknown)

Tests use in-memory SQLite (`phpunit.xml` already sets `DB_CONNECTION=sqlite`,
`DB_DATABASE=:memory:`), so no database setup is needed.

```bash
php artisan test
# or for a coverage summary of which areas pass:
php artisan test --testsuite=Feature
```

169 Feature tests + 4 Unit tests exist across 28 areas. A green run validates a
large amount of behavior at once. Record failures — they are the real go/no-go signal.

## 2. Dependency vulnerability scans

```bash
# PHP (reads composer.lock, queries the security-advisories DB)
composer audit

# JavaScript (api-laravel build deps) — assistant ran this in-sandbox: 0 vulnerabilities,
# but re-run locally to confirm against the live registry
npm audit

# Python (Bridge agent + Python SDK)
pip install pip-audit
pip-audit -r ../../bridge-agent/requirements.txt   # or: cd ../../bridge-agent && pip-audit
cd ../../sdk/python && pip-audit

# Flutter / Dart (mobile app)
cd ../../apps/mobile-patient && flutter pub outdated && dart pub global activate pana && pana .
```

> In-sandbox result already obtained: **`npm audit` on `apps/api-laravel` = 0 vulnerabilities** (info/low/moderate/high/critical all 0). Treat as preliminary; re-run locally.

## 3. Route auth-coverage (authoritative)

Static parsing of `routes/*.php` is **unreliable** — Laravel resolves middleware at
runtime through nested fluent groups, controller `__construct` middleware, and aliases
defined in `bootstrap/app.php` (e.g. `auth.mobile` → `AuthenticateMobilePatient`,
`auth.bearer` → `VerifyBearerToken`). Use the framework's own resolved list instead:

```bash
php artisan route:list --json > routes.json
python ../../docs/audits/route_auth_check.py routes.json
```

`route_auth_check.py` flags any route whose **resolved** middleware contains no auth
layer and that isn't on the intentional-public allowlist (auth/login/token, FHIR
metadata, care-map, document verification, health checks, inbound webhooks, demo).
Review each flagged route: it should either gain auth middleware or be added to the
allowlist as deliberately public.

### What the assistant already verified by hand (spot-checks, reliable)
- `mobile/*` patient data (me, timeline, prescriptions, labs, insurance, consent, access-logs) sits inside `Route::middleware('auth.mobile')->group` → `AuthenticateMobilePatient`. ✅
- FHIR resources (`/Patient`, `/Encounter`, …) sit behind `auth.bearer:patients:read` (scoped); only `/fhir/R4/metadata` is public, which is correct per FHIR. ✅
- All `v1/*` clinical/operational/billing/public-health/consents/referrals/files routes sit behind `VerifyIntegrationClient::class`. ✅
- `v1/connect`: token endpoint public; everything else behind `auth.bearer` + client throttle. ✅

The authoritative `route:list` run will turn these spot-checks into a complete list.

## 4. Suggested order

1. `php artisan test` — does the platform actually work?
2. `composer audit` + `npm audit` + `pip-audit` — known-vulnerable dependencies?
3. `route:list` + `route_auth_check.py` — any PHI route missing auth?
4. Feed failures back into `SPEC_VS_CODE_GAP_AUDIT.md` as new GAP-0xx items.
