# CODEX_LOCAL_BASELINE_REVIEW

Review: Codex local baseline safety review
Branch: main
Commit: 8fc3658 plus local migration fix
Module: Repository-wide baseline review
Files reviewed: upgradeplans protocols, routes, migrations, models, controllers, services, tests, package scripts
Date: 2026-05-19

## Scope

Codex role per `upgradeplans/OPESCARE_CODEX_LOCAL_REVIEW_AUDIT_TEST_PROTOCOL.md` is quality control, verification, duplicate detection, migration review, security/privacy review, and focused fixes only. Claude Code remains the main builder. This review therefore does not implement new Claude-owned modules.

## Tests Run

| Command | Result |
|---|---|
| `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test` | PASS: 151 tests, 574 assertions |
| `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan route:list` | PASS: 333 routes listed |
| `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan route:list --columns=Method,URI,Name,Action,Middleware` | FAIL: this Laravel version does not support `--columns` |
| `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate:fresh --seed --env=testing` | Initially failed on appointment self-FK, then PASS after fix |
| `npm run build` | FAIL with system Node 20.11.0: Vite requires Node 20.19+ or 22.12+ |
| `C:\Users\PC\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\bin\node.exe .\node_modules\vite\bin\vite.js build` | PASS with bundled Node 24.14.0 |
| `npm run` | PASS: available scripts are `build` and `dev`; no `test` script exists |

## Duplicate Risks

- Duplicate model filename scan: no duplicate model filenames found under `apps/api-laravel/app/Models`.
- Duplicate migration table scan: no duplicated `Schema::create(...)` table declarations found under `apps/api-laravel/database/migrations`.
- Duplicate route name scan: no duplicate named routes found from `artisan route:list --json`.
- Duplicate method+URI scan: no duplicate route method+URI pairs found from `artisan route:list --json`.

## Migration Risks

- Fixed: `apps/api-laravel/database/migrations/2026_05_26_000000_create_appointment_booking_tables.php` placed the `appointments.rescheduled_from_appointment_id` self-reference foreign key inside the `Schema::create('appointments')` closure. PostgreSQL rejected this during `migrate:fresh` because the self-referenced key was not yet available as a unique/primary constraint at the point Laravel emitted the FK DDL.
- Fix applied: moved only that self-reference FK into a separate `Schema::table('appointments')` step after table creation.
- Verification: `migrate:fresh --seed --env=testing` passes after the fix.

## Security And Privacy Risks

- High risk: 131 sensitive-looking `api/v1/*` routes were detected without `auth` or `auth:sanctum` middleware. Examples include admin governance, billing, appointments, queues, support, referrals, immunizations, documents, and public-health endpoints. Some may be intentionally open in demo mode, but this must be explicitly classified before production.
- Public verification routes are present for document, Health ID, QR, and certificate verification. Health ID and QR routes use `throttle:verify`; document and certificate verification should be reviewed for data minimization and rate-limit coverage.
- Support routes now redact PII in tests, but route middleware and role authorization still need hardening before real support operations.
- Connect routes partially use `VerifyIntegrationClient` and `IdempotencyProtection`, but duplicate Connect route groups exist in `routes/api.php`; these should be reviewed for scope consistency and accidental public exposure.

## Permission Issues

- No central route-level authorization pattern is consistently visible for many newly added operational APIs.
- Several controllers accept `actor_id`, `patient_id`, `facility_id`, or `scope` from request input. That is acceptable for tests/prototypes, but production should derive actor/facility context from authenticated principals and policies.
- Facility-boundary tests exist for some modules, but authorization middleware/policies are still the main gap.

## Audit Log Issues

- Appointment, queue, billing, global search, go-live readiness, support, offline sync, and operational journey tests assert audit events.
- Referral and immunization services/controllers should be reviewed for audit event coverage; current code should not be treated as production-complete until sensitive referral/immunization actions are audited.
- Communication routes include two explicit stubs: `POST /api/v1/messages/threads/{id}/participants` and `POST /api/v1/messages/threads/{id}/assign` both map to `CommunicationController@getThread`.

## UI Issues

- Frontend production build passes only when run with bundled Node 24.14.0. The installed system Node 20.11.0 is too old for the current Vite dependency.
- There is no `npm test` script.
- Responsive/styling review was not performed in-browser in this baseline pass.

## API Issues

- `artisan route:list --columns=...` from the protocol is not compatible with the current Laravel version.
- Several routes are under `/api/mobile` rather than `/api/v1`; this may be intentional for the mobile API but should be documented as a versioning exception.
- Sensitive write endpoints should get a standard idempotency policy. Current idempotency middleware is only clearly applied to selected Connect write routes.

## Bugs Found

1. PostgreSQL migration failure in appointment booking self-reference FK.
2. System Node version blocks Vite build.
3. Communication participant/assignment routes are explicit stubs.
4. Sensitive API route middleware is not consistently production-grade.

## Fixes Applied

- Patched `apps/api-laravel/database/migrations/2026_05_26_000000_create_appointment_booking_tables.php` to create the appointment self-reference FK after the appointments table exists.

## Fixes Recommended

1. Add/authenticate middleware and policies for sensitive operational API groups.
2. Replace request-supplied `actor_id` with authenticated user context.
3. Add audit events to referral and immunization state-changing actions.
4. Replace communication message participant/assignment stubs with real handlers or remove routes until implemented.
5. Upgrade local Node to at least 20.19 or use Node 22.12+ so `npm run build` works without the Codex bundled runtime.
6. Add an `npm test` script if frontend tests are expected.
7. Document the `/api/mobile` versioning exception or move mobile routes under `/api/v1/mobile`.

## Ready To Merge

Ready to merge: NO for production readiness.

Reason: Backend tests and migrations now pass, and the migration bug was fixed, but the baseline review found high-priority authorization hardening gaps and stubbed communication routes. These should be addressed before treating the repository as production-ready for real patient data.
