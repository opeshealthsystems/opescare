# OpesCare Platform — Changelog

All notable changes to the OpesCare platform are recorded here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### In Progress
- Codex production-hardening branch (argon2id bridge agents, health-ID lookup hashing)

---

## [0.9.0-rc1] — 2026-06-16

First release candidate targeting Cameroon 2026-2030 health digitalization plan.
Covers FHIR R4 compliance, Connect Suite SDK, full portal suite, and mobile apps.

### Added
- **Platform spine** — `VERSION`, `CHANGELOG.md`, `LARAGON_SETUP.md` at repo root
- **FHIR R4** — 92/92 document codes covered; full BRF (mortuary_admission) wiring
- **Connect Suite SDK** — PHP, Python, TypeScript packages under `sdk/`
- **Bridge Agent** — Python bridge service with test suite (`bridge-agent/`)
- **Portal suite** — 9 portal types: staff, doctor, patient, admin, insurance, lab,
  pharmacy, health-org, developer; all routes wired, RBAC-enforced
- **Mobile apps** — Patient mobile app (Flutter) and provider mobile app
- **i18n** — Full EN/FR coverage across all portals via `lang/en/public.php` and
  `lang/fr/public.php`; hardcoded strings replaced with `__()` calls
- **Design system** — Helix v2 (`public/css/helix-v2.css`, `portal.css`) with
  utility classes; zero inline styles remaining in portal layouts
- **Demo seed** — 8 OpesCare facility types, 55 role accounts, RBAC-verified
- **CDSS** — Clinical decision support alerts wired into staff portal
- **Data import wizard** — Multi-step CSV/XLSX import with audit log
- **Ward management** — Live bed map, occupancy KPIs, admission/transfer/discharge

### Fixed
- **CL-1** — Ward service: `admit()` now accepts `actorId`; controller resolves
  models before delegating to service layer
- **CL-4** — Mobile money callback: status check corrected to `successful`
- **CL-5** — 13 migration FK constraints wrapped in SQLite guard for test DB
- **CL-6** — 5 duplicate migration timestamps renamed to next available sequence
- **CL-7** — `FhirService::medicationRequest()` added as mapper wrapper
- **CL-8** — Patient PDF vitals query: fixed crash (`patient_id` not on
  `vital_signs`); now traverses `triageRecord.visit` relationship chain
- **CL-2/3** — Auth lockout and IDOR gaps in communications closed; test bypass
  guards hardened in `VerifyBearerToken` and `VerifySdkToken` middleware
- **RBAC** — Hard-isolated facility admins from platform god-mode
- **CSP** — Allow `unsafe-inline` for portal inline JS; auth JS moved to
  external file to satisfy stricter pages
- Replaced all emoji icons with Lucide across portal dashboards
- Admin portal 500 errors (10 fixes — Postgres + platform-no-facility edge cases)

### Security
- MFA enforcement hardened for web and mobile flows
- Module entitlement checks enforced at middleware layer
- USSD callback verification added
- Admin API routes now require `RequireApiAdminRole` middleware
- Communication APIs require authentication

---

## Version History

| Version     | Date       | Notes                          |
|-------------|------------|-------------------------------|
| 0.9.0-rc1   | 2026-06-16 | First release candidate        |

---

> **Downstream consumers:** the Connect Suite SDK (`sdk/`), Bridge Agent
> (`bridge-agent/`), and mobile apps (`apps/mobile-*`) each track this version
> as their upstream contract. Breaking changes in the API surface will bump the
> minor version; additive changes bump the patch.
