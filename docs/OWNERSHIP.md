# OpesCare Docs — Ownership Matrix

This file maps documentation areas to their responsible owners.
For PR review routing, see `CODEOWNERS` at the repo root.

| Area | Directory | Owner | Notes |
|------|-----------|-------|-------|
| Platform spine | `/VERSION`, `/CHANGELOG.md` | @makkowens24 | Update on every release |
| Local dev setup | `/LARAGON_SETUP.md` | @makkowens24 | Update when toolchain changes |
| API contracts / OpenAPI | `/contracts/openapi/` | @makkowens24 | Must stay in sync with routes |
| Connect Suite integration | `/docs/integration/` | @makkowens24, @jiencestonmorningstar | FHIR + SDK + webhook docs |
| Security | `/docs/security/` | @makkowens24 | Threat model, hardening checklist, WAF |
| Deployment & runbooks | `/docs/deployment/`, `/docs/runbooks/` | @makkowens24 | CloudPanel, DR, secrets rotation |
| SOPs | `/docs/sops/` | @makkowens24 | Operational procedures |
| Mobile | `/docs/mobile/` | @jiencestonmorningstar | Patient app (Expo) docs |
| Clinical specs | `/docs/specs-core/` | @makkowens24 | Operational modules, governance |
| Design specs | `/docs/design-specs/`, `/docs/ui-ux/` | @makkowens24 | Helix v2, portal layouts |
| QA & release | `/docs/qa-release/` | @makkowens24 | Go-live readiness, sign-off |
| Audit logs | `/docs/audit/`, `/docs/audits/` | @makkowens24 | Immutable — append only |
| Agent protocols | `/docs/agent-protocols/` | @makkowens24 | Bridge and AI agent specs |
| Plans (active) | `/docs/plans/` | @makkowens24 | Delete or archive when complete |
| Superseded / archived | `/docs/_superseded/`, `/docs/_older-versions/` | @makkowens24 | Do not edit; archive only |

## Rules

1. **Version bump** — any change that alters an API contract, SDK interface, or
   FHIR mapping must update `VERSION` and `CHANGELOG.md` in the same PR.

2. **Audit docs are append-only** — `/docs/audit/` and `/docs/audits/` entries
   must never be edited or deleted after merge.

3. **Plans lifecycle** — files in `/docs/plans/` that are fully implemented
   should be moved to `/docs/_superseded/` rather than deleted.

4. **OpenAPI must match routes** — any PR touching `routes/api.php` or adding
   a new API controller must also update the relevant OpenAPI spec in
   `/contracts/openapi/`.

5. **Security docs require platform lead sign-off** — PRs touching
   `/docs/security/` need explicit approval from @makkowens24 even if another
   owner is listed.
