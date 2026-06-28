# OpesCare — Monorepo Brief (read this first)

OpesCare is a **patient-centered national digital health-ID, interoperability, and
care-operations platform** — Cameroon-first (MINSANTE regulations), designed
multi-country, bilingual **EN/FR**. A patient owns a portable **Health ID** and a
longitudinal, **consent-scoped** record that travels across facilities. It is a
server-rendered **Laravel modular monolith** + REST/**FHIR R4** API — *not* a
single-hospital CRUD app.

> **This is the front door.** It orients you and routes you to the deep knowledge.
> If anything here disagrees with the code, **trust the code and fix the doc**
> (note the date you checked).

## Where the knowledge lives — read the right one
| If you need… | Go to |
|---|---|
| **Security guardrails — sealed modules + hard prohibitions** | [`apps/api-laravel/CLAUDE.md`](apps/api-laravel/CLAUDE.md) — *authoritative; never modify a sealed file without explicit instruction* |
| **What's built / the work list / product invariants** | [`docs/CLAUDE.md`](docs/CLAUDE.md) → `docs/AS_BUILT_IMPLEMENTATION_REGISTER.md`, `docs/audits/SPEC_VS_CODE_GAP_AUDIT.md` |
| **The full knowledge base** | `docs/` (being reorganized: `00-overview … 70-development` + `docs/README.md` index) |
| **Decisions & state across sessions** (Claude Code) | the project memory: `~/.claude/projects/C--laragon-www-opescare/memory/MEMORY.md` |

## Monorepo map
| Path | What it is | Deployed to prod? |
|---|---|---|
| `apps/api-laravel/` | **The platform** — Laravel 13 / PHP 8.3: REST/FHIR API **and** server-rendered Blade portals | ✅ this is the deployed app |
| `apps/mobile-patient/` | Flutter patient app | built/shipped separately |
| `sdk/{php,typescript,python}/` | Connect SDKs for integration partners | no |
| `widget/` | Embeddable Connect widget | no |
| `bridge-agent/` | On-prem facility data-sync agent | deployed to facilities, not the API host |
| `contracts/` | API / integration contracts | no |
| `deploy/` | systemd units + deploy notes | ops only |
| `docs/` | Documentation / product knowledge | no |

## Stack & data invariants
- **PostgreSQL** is the system of record; **Redis** runs cache/queue/Horizon. ~45 domain modules under `apps/api-laravel/app/Modules/<Name>/`; cross-cutting logic in `app/Services/`.
- **Never use MySQL date functions** (`DATE_FORMAT`) — this is Postgres; use `TO_CHAR(col,'YYYY-MM')`.
- **Typed backed enums** (verification/identity/audit/status) — never compare with `=== 'string'`; use `match()`, `->value`, or helper methods (e.g. `->isBlocked()`).
- **Identity writes are centralized** — create patients only via `PatientIdentityService`; no probabilistic auto-merge (uncertain matches → Reconciliation/MPI review).
- **Clinical events are immutable** — amend / void / reverse / entered-in-error, never hard-overwrite. **External writes are idempotent** (`Idempotency-Key`).

## Conventions — honor on every change
- **i18n (EN/FR):** every user-facing string goes through `__('namespace.key')` or the `@enum($value[,'group'])` Blade directive — never hardcode. `lang/en/*.php` and `lang/fr/*.php` MUST stay **1:1** (enforced by `php scripts/i18n-audit.php`). Status/severity/tier values render via `@enum`; role badges via `lang/portal.php`; portal nav via `public.portal.nav_*`.
- **UI:** **Lucide icons only** (never emoji); brand color **#0F4C81** (never purple); currency **XAF/FCFA**; payments **MTN MoMo / Orange Money** only.
- **Portals are role-driven:** `role.dashboard_profile_key` → `resources/views/partials/sidebars/{key}.blade.php`, rendered by `layouts/portal.blade.php`. **Every feature needs a route + a nav link + a `route:list` check** — never leave a page reachable by URL only.
- **Security (full list in the app CLAUDE.md):** `facility_id` only from `$request->attributes` (set by auth middleware) — never headers/body/session/fallback; `ConsentGrant` gate on every patient endpoint; no patient `LIKE`/enumeration search; client secrets **Argon2id** only.

## How it runs & deploys
- **`git push` to `main` auto-deploys** via `.github/workflows/deploy.yml`: run **test suite** (hard gate) → SSH to the prod host → `git pull` → `migrate --force` → `config/route/view/event:cache` → restart Horizon/queues → reload PHP-FPM → health check → lift maintenance. **A red test halts the rollout — prod is not updated.**
- Prod is a git checkout of the monorepo (being slimmed via sparse-checkout to ship only `apps/api-laravel`). Horizon + scheduler run under **systemd** (`deploy/systemd/`); errors go to **Sentry** (DSN-gated, PHI-scrubbed).
- **Git topology:** the local dev checkout (`C:\laragon\www\opescare`) is on `main`; `apps/api-laravel` is part of *this* repo (not a nested repo); agent worktrees live under `.claude/worktrees/` and can be **stale** — always `git rev-parse --abbrev-ref HEAD` before committing, and edit the live tree, not a worktree copy.

## Verify before calling something "done"
```bash
php artisan test --parallel        # the deploy gate
php scripts/i18n-audit.php          # EN/FR 1:1 parity (must be 0 mismatches)
php artisan view:cache              # every Blade compiles
php artisan route:list              # routes are wired
```

---
*Curated brief — keep it short and current; push deep/volatile detail into `docs/` and the two CLAUDE.md files above, and decisions into project memory.*
