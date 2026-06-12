# Dashboard & Access Control Baseline Audit

**Project:** OpesCare  
**Audit Date:** 2026-05-20  
**Auditor:** Claude Code (Phase A — no code modified)  
**Reference Document:** `OPESCARE_ACCOUNT_TYPES_ROLE_DASHBOARDS_SCREENS_AND_ACCESS_MATRIX.md`  
**Purpose:** Establish the current state of auth, roles, permissions, dashboards, sidebars,
routes, and access policies before beginning the dashboard extension work.

---

## 1. Summary of Findings

| Area | Status | Critical Gap |
|------|--------|-------------|
| Authentication middleware on portal routes | **CRITICAL** | Portal routes use `web` only — no `auth` guard |
| Role-based route guards | **CRITICAL** | No role checks on any portal route |
| Dashboard profile system | **MISSING** | No `dashboard_profiles` table or column |
| Account category taxonomy | **MISSING** | No `account_categories` table |
| Facility context enforcement | **MISSING** | No middleware enforces facility context before data load |
| High-risk access modal | **MISSING** | No confirmation flow for sensitive actions |
| Demo data isolation | **MISSING** | Demo accounts see all (non-demo-scoped) data |
| Roles table | **EXISTS** (partial) | Has name/description only — no category, no dashboard_profile FK |
| Permissions table | **EXISTS** (partial) | Has name/description only — not assigned to any real user |
| Sidebar role-awareness | **MISSING** | All `/portals/staff` roles see identical sidebar |
| Insurance EMR access block | **MISSING** | Insurance users could navigate to `/portals/staff` |
| Student clinical action blocks | **MISSING** | No policy prevents students from finalizing visits |
| Audit logging on patient data views | **MISSING** | AuditEvent model exists but not hooked to portal loads |

---

## 2. Authentication & Route Guard Audit

### 2.1 Portal Route Middleware

All portal route groups use only the `['web']` middleware alias:

```php
Route::middleware(['web'])->group(function () {
    Route::get('/portals/patient', ...);
    Route::get('/portals/staff', ...);
    // ...
});
```

**Gap:** The `auth` middleware is absent. An unauthenticated visitor can load
`/portals/staff`, `/portals/admin`, `/portals/insurance`, and `/portals/developer`
without logging in. Controllers handle the null-user case silently (showing empty data)
rather than redirecting.

**Required fix (Phase B):** Add `auth` middleware to every portal route group.

### 2.2 Role Guards on Routes

No route uses a role-based middleware or Gate check. Examples:

```
GET /portals/admin/cc   → AdminControlCenterController@index  (no role check)
GET /portals/insurance/claims → InsurancePortalController@claims  (no role check)
GET /portals/staff/cdss → CdssController@index  (no role check)
```

**Gap:** A patient-role user who manually navigates to `/portals/admin` will see the
admin portal. An insurance user can browse `/portals/staff` EMR screens.

### 2.3 DemoSessionMiddleware

`DemoSessionMiddleware` is registered in `bootstrap/app.php` but applied globally — it
only checks for expired demo sessions and aborts `demo-access*` paths when demo is
disabled. It does **not** enforce role-based routing.

---

## 3. User & Role Model Audit

### 3.1 User Model

```php
// app/Models/User.php
protected $fillable = [
    'name', 'email', 'password',
    'primary_facility_id',
    'role_id',   // ← FK to roles table
    'status',
];
```

**Present:** `role_id`, `primary_facility_id`, `is_demo`, `status`  
**Missing:** `dashboard_profile` (or derivable from role), `account_category_id`

### 3.2 Roles Table

```sql
-- 2026_05_14_215649_create_roles_and_permissions_tables.php
CREATE TABLE roles (
    id UUID PRIMARY KEY,
    name VARCHAR UNIQUE,
    description VARCHAR NULLABLE,
    timestamps
);
```

**Present:** `name`, `description`  
**Missing:** `account_category_id`, `dashboard_profile_key`, `specialization`, `allowed_portal`

### 3.3 Permissions Table

Present with name/description. The `role_permission` pivot table exists.  
**Gap:** No permissions have been seeded. No `Gate` definitions or `Policy` classes
reference the permissions table in portal controllers.

### 3.4 No Account Category Table

The document requires an `account_categories` table with 21 entries. It does not exist.

### 3.5 No Dashboard Profile Table

The document requires a `dashboard_profiles` table with ~60 entries mapping to sidebar
menus and widget sets. It does not exist. The `DemoAccessController::portalForRole()`
method approximates this with a hard-coded `match` block (13 demo roles → 5 portal URLs).

---

## 4. Existing Portal Inventory (Screen Registry — Current State)

### 4.1 Patient Portal (`/portals/patient`)

| Route Key | Path | Screen |
|-----------|------|--------|
| `portals.patient` | GET `/portals/patient` | Health ID dashboard |
| `portals.patient.appointments` | GET `/portals/patient/appointments` | Appointments list |
| `portals.patient.logs` | GET `/portals/patient/logs` | Access logs |
| `portals.patient.qr` | POST `/portals/patient/generate-qr` | QR token generate |

**Missing from document requirements:** Lab results, Prescriptions, Medical documents,
Consent & access, Who accessed my record (distinct from logs), Messages, Care access map
link (present as external nav), Medicine finder, Blood finder, Billing & receipts,
Insurance status.

### 4.2 Staff Portal (`/portals/staff`)

All staff roles (doctor, nurse, pharmacist, lab tech, specialist, etc.) share one portal.
The sidebar is identical for all roles.

| Section | Route Keys |
|---------|-----------|
| Overview | `portals.staff`, `portals.staff.analytics` |
| Clinical | `portals.staff.appointments`, `portals.staff.queue`, `portals.staff.visits`, `portals.staff.cdss`, `portals.staff.immunizations`, `portals.staff.referrals` |
| HR & Staff | `portals.staff.hr.directory`, `.hr.shifts`, `.hr.roster`, `.hr.leave` |
| Inventory | `portals.staff.inventory.pharmacy`, `.inventory.blood` |
| Lab | `portals.staff.analytics.data_quality` (partial) |
| Ward | `portals.staff.wards`, `.wards.admissions` |
| Telemedicine | `portals.staff.telemedicine.*` |
| Billing | `portals.staff.billing.*` |
| Supply Chain | `portals.staff.supply.*` |
| Files | `portals.staff.files.*` |
| Data Import | `portals.staff.data_import.*` |
| CDSS | `portals.staff.cdss.*` |
| Search | `portals.staff.search` |
| Support | `portals.staff.support.*` |

**Gap (role separation):**  
- A `nurse` should NOT see CDSS drug interactions, HR management, or Supply Chain purchasing.  
- A `pharmacist` should NOT see Visit/Consult screens or Triage.  
- A `labtech` should NOT see Prescriptions or CDSS rule management.  
- A `data_import` officer should see only Data Import — not clinical or HR screens.

### 4.3 Admin Portal (`/portals/admin`)

Present screens: Control Center (settings, modules, feature flags, health, audit log,
maintenance), Security Ops (incidents, emergency access, audit explorer), Bridge Admin,
Connect (clients, tokens, webhooks, widget), Certifications, Code Mappings, Developer
accounts, Legal admin, Onboarding, KPI, Subscription, Go-Live Readiness.

**Gap:** No separate `facility_admin` vs `platform_admin` vs `super_admin` views.
All admin roles see identical sidebar. A `facility_admin` should NOT see Platform
Control Center (`/portals/admin/cc`), subscription management, or global legal admin.

### 4.4 Insurance Portal (`/portals/insurance`)

Present screens: Claims, Pre-authorizations, Policies, Providers.

**Gap:** Insurance users could manually navigate to `/portals/staff` EMR screens.
No data scope limits insurance users to minimum-necessary claim data.

### 4.5 Developer Portal (`/portals/developer`)

Present screens: Apps, Production requests, Onboarding, Webhook deliveries.

**Gap:** No sandbox vs production scope enforcement visible in portal layer.

### 4.6 Missing Portals (Required by Document, Not Yet Built)

| Dashboard Profile | Required Portal Path | Status |
|-------------------|---------------------|--------|
| Nurse Dashboard | Sidebar scoped to ward/triage/queue | Missing — shares `/portals/staff` |
| Pharmacist Dashboard | Sidebar scoped to prescriptions/dispensing/stock | Missing |
| Lab Technician Dashboard | Sidebar scoped to lab orders/results/samples | Missing |
| Student Clinical Dashboard | Sidebar with blocked finalize actions | Missing |
| Reception / Front Desk Dashboard | Sidebar: registration, appointments, queue | Missing |
| Public Health Officer Dashboard | Aggregate reports only, no patient-level | Missing |
| Support Agent Dashboard | Patient record view with high-risk modal | Missing |
| Privacy / Security Officer Dashboard | Audit explorer, breach workflow | Missing |
| Data Steward Dashboard | Reconciliation, import, data quality | Missing |
| Academy Learner Dashboard | Course/certification content | Missing |
| NGO / Outreach Dashboard | Program/mobile clinic management | Missing |

---

## 5. Sidebar Audit

### 5.1 Staff Portal Sidebar (`portals/staff/index.blade.php`)

Sections currently rendered for **every** staff role:
- Overview (Dashboard, Analytics)
- Clinical (Appointments, Queue, Visits, CDSS, Immunizations, Referrals)
- HR & Staff (Directory, Shifts, Roster, Leave)
- Inventory (Pharmacy, Blood Bank)
- Ward Management (Wards, Beds, Admissions)
- Telemedicine
- Billing
- Supply Chain
- Files
- Data Import
- Search
- Support

**Required role-scoped sidebar sections:**

| Role | Should See | Should NOT See |
|------|-----------|----------------|
| Doctor | Clinical, Telemedicine, Billing (own), Files | HR management, Supply Chain purchasing |
| Nurse | Clinical (ward-focused), Ward, Queue | CDSS drug interactions, Billing management, Supply purchasing |
| Pharmacist | Prescriptions, Inventory (pharmacy), Dispensing | Visits/Consult, CDSS, HR management |
| Lab Tech | Lab orders, Lab results, Files | Prescriptions, CDSS, HR |
| Billing Officer | Billing, Receipts, Reports | Clinical, HR, Inventory |
| Data Import Officer | Data Import only | All clinical and admin screens |
| Receptionist | Appointments, Queue, Patient registration | Clinical, HR, Inventory |

### 5.2 Admin Portal Sidebar

One sidebar for all admin roles. No facility-scoped vs platform-scoped differentiation.

### 5.3 Patient Portal Sidebar

Sidebar present but missing: Lab results, Prescriptions, Documents, Consent management,
Billing & receipts, Insurance status, Messages.

---

## 6. Facility Context Audit

### 6.1 Multi-Facility Context Selector

Route `/select-facility` exists (`showSelectFacility` / `submitSelectFacility` in
`PublicPageController`). However:

- No middleware enforces that a multi-facility user must select a context before viewing data.
- `User::primary_facility_id` exists but no session-level `active_facility_id` is set.
- No audit event is emitted on context switch.

### 6.2 Facility-Scoped Data Queries

Portal controllers do not filter queries by active facility context. `StaffPortalController`
queries are global — not scoped to `auth()->user()->primary_facility_id`.

---

## 7. High-Risk Action Audit

No high-risk confirmation modal exists in any portal. Actions that require it per the
document (emergency access, patient merge, record amendment, refund approval, role
permission change, support access to patient record) have no `reason` capture or
secondary confirmation flow.

---

## 8. Demo Data Isolation Audit

Demo accounts (`is_demo = true`) now correctly land in real portals via
`DemoAccessController::portalForRole()`. However:

- Portal controllers do not apply an `is_demo` data scope.
- A demo doctor who visits `/portals/staff/visits` will see real visit records.
- `IsDemoRecord` trait exists on models but portal controllers do not call its scope.
- No demo banner is displayed in portals when a demo user is logged in.
- Real-world side effects (SMS, email, billing, lab order dispatch) are not blocked in
  demo sessions.

---

## 9. Audit Logging Audit

`AuditEvent` model and migration exist. `SecurityIncidentService`, `AuditExplorerService`,
and related services are in place. However:

- No portal controller calls `AuditEvent::create()` or equivalent on patient data views.
- No automatic audit on: patient record load, lab result view, prescription view, QR scan.
- The `/portals/admin/security/audit-explorer` screen exists but has no data to show from
  portal activity.

---

## 10. Gap List (Prioritized)

### P0 — Security critical (must fix before Phase C)

1. Add `auth` middleware to all portal route groups — currently any visitor can load portals.
2. Add role-based route middleware — prevent cross-portal access (insurance on staff, patient on admin, etc.).

### P1 — Required for role-aware dashboards (Phase C/D)

3. Add `dashboard_profile` derivation to `User` (via role or explicit column).
4. Add `account_categories` seeder (21 entries) and link to `roles`.
5. Split staff portal sidebar into role-scoped partials (doctor / nurse / pharmacist / labtech / billing / reception / data-import).
6. Split admin portal sidebar into facility-admin vs platform-admin views.
7. Expand patient portal sidebar to include all required sections from document.

### P2 — Data scoping (Phase E)

8. Scope all portal controller queries to active facility context.
9. Apply `is_demo` scope in portal controllers when `auth()->user()->is_demo`.
10. Enforce facility context session before loading data for multi-facility roles.

### P3 — High-risk & audit (Phase E/G)

11. Implement high-risk access modal (Blade component + middleware hook).
12. Wire `AuditEvent` to patient record views in portal controllers.

### P4 — Missing portals (Phase D)

13. Build nurse, pharmacist, labtech, reception, support-agent, public-health, data-steward sidebar partials and landing screens.

---

## 11. File Inventory

### Existing middleware

| File | Purpose | Gap |
|------|---------|-----|
| `DemoSessionMiddleware.php` | Demo session expiry, demo route gating | Does not enforce role routing |
| `SetLocale.php` | EN/FR locale from session | None |
| `IdempotencyProtection.php` | POST idempotency | None |
| `VerifyIntegrationClient.php` | API integration auth | None |
| `VerifyPartnerTrustLevel.php` | Partner trust level | None |

**Missing middleware:** `RequireAuth`, `RequireRole`, `RequireFacilityContext`, `AuditPatientAccess`, `DemoDataScope`.

### Portal view files

| View | Sidebar | Role-aware |
|------|---------|-----------|
| `portals/patient/index.blade.php` | Minimal (5 links) | No |
| `portals/staff/index.blade.php` | Full (14 sections) | No — identical for all staff |
| `portals/admin/index.blade.php` | Full (admin sections) | No — identical for all admins |
| `portals/insurance/claims.blade.php` | Insurance sidebar | No |

---

## 12. Next Phase Recommendation

**Phase B** (next): Register existing screen keys, add `auth` middleware to portal routes,
and add the P0 role-based route guard. No new views yet — only infrastructure hardening
and screen registry creation.

Do not proceed to Phase C (role-dashboard mapping) until Phase B P0 items are resolved,
as building role-aware sidebars on top of unauthenticated routes would create false
security.
