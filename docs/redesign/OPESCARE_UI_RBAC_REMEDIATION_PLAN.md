# OpesCare — UI Redesign + RBAC Remediation Plan

**Date:** 2026-06-14
**Why:** Live platform has (1) a real RBAC authorization bug (facility users reaching
platform god-mode), and (2) inconsistent, non-mobile, unpolished portal UIs. This plan
fixes both, systematically, **with browser verification at every step** (the discipline
that was missing).

**Non-negotiables from the owner:**
- Keep brand colors. Keep all existing text/messaging (fine-tune only, never delete).
- 100% mobile-first. Premium, authentic, consistent across every account type.
- Lucide icons only (no emojis), self-hosted (no external icon CDN).
- Most effort on the **logged-in dashboards**; landing page redesigned first.
- RBAC correctness baked into every screen (only show what the role may do).

---

## PHASE 0 — RBAC security fix (P0, do FIRST, before more UI)

The authorization model conflates **facility administration** with **platform
administration**. Fix = split them into distinct tiers and gate platform-only areas.

**0.1 Define two admin tiers**
- **Platform tier** (god-mode): `super_admin, platform_admin, system_admin, product_admin, legal_admin, country_admin, regional_admin`.
- **Facility tier**: `facility_admin, clinic_admin, hospital_admin, facility_ceo, department_manager, branch_admin, finance`.

**0.2 Gate platform-only route groups** — Control Center (`/portals/admin/cc*`),
Security Ops (`/portals/admin/security*`), Subscriptions/Connect/Bridge, KPI, Legal,
Certifications, Code-mappings, Developer-accounts, and the bare `/admin/*` god-mode
data routes (all-users/facilities/patients) — to the **platform tier only**, via a new
`platform.admin` middleware (or a policy). Facility admins get a facility-scoped admin
view, never the platform control center.

**0.3 Fix the fallback** — `EnsurePortalAccess::correctPortalFor()` default must NOT be
`/portals/admin`. Unmatched roles → a safe neutral page (or 403), never god-mode.

**0.4 Tests (TDD, required):** `clinic_admin` is **blocked** from `/portals/admin/cc`,
`/portals/admin/security`, `/admin/users`; `super_admin` is **allowed**; `doctor` →
staff only; `patient` → patient only. Run on the Postgres test DB.

**0.5 Deploy** the fix to the live server immediately after tests pass.

> This phase ships independently of the redesign and closes the security hole now.

---

## PHASE 1 — Design system foundation (the consistency fix)

The "color inconsistencies across dashboards/tabs" come from 5 separate CSS files
(`portal.css, public.css, auth.css, landing.css, docs.css`) drifting apart. Fix =
one shared token layer every page consumes.

**1.1 Design tokens** (`public/css/tokens.css`, imported everywhere): brand palette
(#0F4C81 / #0A3560 / #1A6AAF / #14B8A6) + neutral ramp + semantic (success/warning/
danger/info) + typography scale + spacing scale + radius + shadows + z-index. One
source of truth, so every portal renders identical colors/spacing.

**1.2 Mobile-first base**: responsive breakpoints, 44px touch targets, fluid type,
container widths. Sidebar becomes an off-canvas drawer with a hamburger on mobile;
top bar collapses; tables become stacked cards under ~768px.

**1.3 Component kit** (consistent, reused by all portals): `panel/card`, `kpi-card`,
`data-table` (→ card list on mobile), `btn` variants, `badge`, `tabs`, `form-control`,
`modal`, `sidebar-nav`, `topbar`, empty-state, loading/skeleton. All use tokens + Lucide.

**1.4 Icon standard**: Lucide only, self-hosted; one init path (already externalized in
`auth.js` pattern — extend platform-wide); standard sizes (16/18/20/24); zero emojis.

---

## PHASE 2 — Landing page (mobile-first, FIRST per owner)

Rebuild `public/landing.blade.php` + `css/landing.css` on the new tokens: premium,
mobile-first, same sections/text/messaging, brand colors, Lucide. Verify at 375px,
768px, 1280px with screenshots before sign-off.

---

## PHASE 3 — Dashboards per account type (main effort)

For each account type: define the dashboard IA to industry standards (clear KPI row,
primary actions, role-appropriate nav) **and RBAC** (render only what the role can do),
then apply the Phase-1 component kit. Consistent premium layout, mobile-first,
responsive. **Each verified in the browser (screenshots) before "done."**

Order (highest impact first):
1. **Platform Super Admin** (control center / god-mode)
2. **Facility Admin** (hospital/clinic/pharmacy/lab/insurance facility admin)
3. **Staff / Clinical** (doctor, nurse, front desk, etc.)
4. **Patient**
5. **Pharmacy** · 6. **Lab** · 7. **Insurance** · 8. **Health Org** · 9. **Developer** · 10. **Lite**

Each account type covers its dashboard + all its pages/tabs/flows for visual + RBAC
consistency.

---

## PHASE 4 — Cross-cutting + stabilize

- Fix the live **500 errors** (from `storage/logs/laravel.log`, one by one).
- Mobile nav/drawer, responsive tables, form layouts, empty/loading states everywhere.
- Final sweep: no emoji, all icons render offline, consistent tabs/colors, a11y basics.

---

## Execution discipline (the part that was missing)

1. Work in the local preview (`artisan serve`) + headless browser.
2. For every screen: screenshot BEFORE, change CSS/Blade, screenshot AFTER at mobile +
   desktop, confirm icons render and buttons work, THEN commit.
3. Never claim "done/ready" without a verifying screenshot or test output.
4. Commit per coherent unit; deploy in batches; re-verify on the live server.

## Sequencing

```
PHASE 0 (RBAC, urgent) ─► PHASE 1 (design system) ─► PHASE 2 (landing)
        ─► PHASE 3 (dashboards, account-by-account) ─► PHASE 4 (polish + 500s)
```

Phase 0 is independent and ships immediately. Phases 1–4 are the redesign, gated by
per-screen browser verification.
