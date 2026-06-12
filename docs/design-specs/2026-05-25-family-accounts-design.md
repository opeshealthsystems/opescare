# Family Accounts — Design Spec

**Date:** 2026-05-25
**Status:** Approved — ready for implementation planning

---

## Goal

Allow a guardian (parent, caregiver, or legal representative) to link one or more dependent patients to their OpesCare account, view their health data, manage consent requests on their behalf, and receive multi-channel notifications about their dependents' health events — without modifying any existing patient portal, consent, audit, or QR/Health ID modules.

---

## Architecture

**Approach:** Family management section + guardian context layer (Option 3).

A new `FamilyController` handles all link/invite/manage flows under `/portals/patient/family/*`. Viewing a dependent's data stores `guardian_viewing_patient_id` in the session. A new `GuardianAccessMiddleware` validates the link, enforces access level, and binds the dependent patient onto the request before existing controllers run. Existing controllers receive one additional method call (`resolveViewingPatient()` or `assertWriteAllowed()`) — no logic is removed or replaced.

**Constraint:** Purely additive. No existing table, model, controller, view, middleware, or route is deleted or overridden.

---

## Tech Stack

- Laravel 13, PHP 8.3, PostgreSQL
- Blade templating (portal layout at `resources/views/layouts/portal.blade.php`)
- Laravel Notifications (mail + database channels; SMS via configurable driver)
- Laravel Scheduler (daily command for age transition checks)
- `chillerlan/php-qrcode` v6 (already installed — not used by family feature)

---

## Data Model

### New table: `family_links`

```sql
CREATE TABLE family_links (
    id                          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    guardian_user_id            UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    dependent_patient_id        UUID NOT NULL REFERENCES patients(id) ON DELETE CASCADE,
    relationship                VARCHAR(30) NOT NULL,   -- see enum below
    access_level                VARCHAR(20) NOT NULL DEFAULT 'read_only',  -- full | read_only
    status                      VARCHAR(30) NOT NULL DEFAULT 'pending_invite',
    created_by                  VARCHAR(30) NOT NULL,   -- self_registered | invite_accepted | facility_assigned
    invite_token                VARCHAR(64) NULL,       -- SHA-256 hashed invite token
    invite_expires_at           TIMESTAMP NULL,
    notification_prefs          JSONB NOT NULL DEFAULT '{}',
    age_transition_notified_at  TIMESTAMP NULL,
    age_transition_expires_at   TIMESTAMP NULL,         -- grace period end after majority age
    created_at                  TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at                  TIMESTAMP NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_guardian_dependent UNIQUE (guardian_user_id, dependent_patient_id)
);

CREATE INDEX idx_family_links_guardian  ON family_links (guardian_user_id);
CREATE INDEX idx_family_links_dependent ON family_links (dependent_patient_id);
CREATE INDEX idx_family_links_invite    ON family_links (invite_token) WHERE invite_token IS NOT NULL;
```

**`relationship` enum values:** `parent`, `grandparent`, `spouse`, `sibling`, `caregiver`, `legal_guardian`, `other`

**`access_level` enum values:**
- `full` — guardian can read all data and perform write actions (approve/deny consent, update profile)
- `read_only` — guardian can read all data; write actions return 403

**`status` enum values:** `pending_invite`, `active`, `revoked`, `expired`

**`notification_prefs` JSON structure:**
```json
{
  "lab_result":      { "portal": true,  "email": true,  "sms": false },
  "appointment":     { "portal": true,  "email": true,  "sms": false },
  "consent_request": { "portal": true,  "email": true,  "sms": true  },
  "age_transition":  { "portal": true,  "email": true,  "sms": true  }
}
```
Missing keys default to the system defaults shown above.

**No changes to any existing table.**

---

## New Files

### Migration
- `database/migrations/YYYY_MM_DD_create_family_links_table.php`

### Model
- `app/Models/FamilyLink.php`
  - `$fillable`: all columns except `id`
  - `$casts`: `notification_prefs` → array, timestamps → datetime
  - Relationships: `guardianUser()` belongsTo User, `dependentPatient()` belongsTo Patient
  - Scopes: `active()`, `pendingInvite()`
  - Helper: `isExpiredByAge(): bool` — returns true if `age_transition_expires_at` is in the past

### Middleware
- `app/Http/Middleware/GuardianAccessMiddleware.php`
  - Reads `session('guardian_viewing_patient_id')`
  - Loads active FamilyLink for `(auth()->id(), session_patient_id)`
  - Not found or expired → clears session, redirects to `portals.patient` with error
  - Found → sets `request->attributes->set('guardian_link', $link)` and `request->attributes->set('viewing_patient', $link->dependentPatient)`

### Controller
- `app/Http/Controllers/MedicalId/FamilyController.php`
  - `index()` — family dashboard: all active + pending links for the guardian
  - `addForm()` / `store()` — self-register a new dependent (creates Patient record + FamilyLink only; no User account is created for the dependent — they can register their own portal account independently later if desired, `created_by = self_registered`)
  - `inviteForm()` / `sendInvite()` — find existing patient by Health ID or email; create pending FamilyLink; dispatch `FamilyInviteNotification`
  - `acceptInvite(string $token)` / `confirmInvite(string $token)` — token validation, activate link
  - `editForm(FamilyLink $link)` / `update(FamilyLink $link)` — change relationship, access_level, notification_prefs
  - `revoke(FamilyLink $link)` — set status = revoked
  - `switchTo(string $patientId)` — set `session(['guardian_viewing_patient_id' => $patientId])`
  - `switchBack()` — forget `guardian_viewing_patient_id`
  - `guardianConsentApprove(FamilyLink $link)` / `guardianConsentDeny(FamilyLink $link)` — dependent re-grants or denies access after age transition notice

### Modifications to existing PatientPortalController (additive only)
Two new private methods added; no existing methods changed:

```php
// Returns dependent patient if guardian context is active, otherwise own patient
private function resolveViewingPatient(): ?Patient
{
    if (request()->attributes->has('viewing_patient')) {
        return request()->attributes->get('viewing_patient');
    }
    return $this->resolvePatient(); // existing method, unchanged
}

// Aborts 403 if guardian is in read_only mode attempting a write
private function assertWriteAllowed(): void
{
    $link = request()->attributes->get('guardian_link');
    if ($link && $link->access_level === 'read_only') {
        abort(403, 'Read-only guardian access does not permit this action.');
    }
}
```

Existing data-fetch methods (`appointments()`, `labResults()`, `prescriptions()`, `documents()`, `accessLogs()`, `consentRequests()`, `profile()`) each get one line changed: `$this->resolvePatient()` → `$this->resolveViewingPatient()`.

Write methods (`updateProfile()`, `approveConsent()`, `denyConsent()`) each get one line added at the top: `$this->assertWriteAllowed();`.

`index()` and `generateTemporaryQr()` are **not changed** — the Health ID dashboard and QR generation always operate on the authenticated user's own patient.

### Views (all new — no existing views modified)
- `resources/views/portals/patient/family/index.blade.php` — guardian dashboard, dependent cards
- `resources/views/portals/patient/family/add.blade.php` — self-register dependent form
- `resources/views/portals/patient/family/invite.blade.php` — invite by Health ID or email
- `resources/views/portals/patient/family/invite-accept.blade.php` — invite acceptance page (no auth required)
- `resources/views/portals/patient/family/edit.blade.php` — edit link settings + notification prefs
- `resources/views/partials/guardian-context-banner.blade.php` — "Viewing: [Name]  [Switch Back]" banner

**Banner injection:** `layouts/portal.blade.php` already has `@yield('patient_banner')` at line 112. Each existing portal view that supports guardian context yields this partial when guardian context is active. The layout itself is not modified.

**Sidebar addition:** A new `My Family` section is appended to `resources/views/partials/sidebars/patient.blade.php` below the existing Resources section. No existing links are removed or reordered.

### Notifications
- `app/Notifications/FamilyEventNotification.php` — implements `toMail()`, `toDatabase()`, `toSms()` (stub dispatching to configured SMS driver). Channel selection from `notification_prefs`.
- `app/Notifications/FamilyInviteNotification.php` — invite email with accept link (token-signed URL, 48h expiry).

### Listeners
- `app/Listeners/NotifyGuardiansOfPatientEvent.php` — listens on `LabResult::created`, `Appointment::created`, `Appointment::updated`, `ConsentRequest::created`. Finds active FamilyLinks for the patient and dispatches `FamilyEventNotification` per guardian. **No changes to event dispatchers.**

### Scheduled Command
- `app/Console/Commands/CheckAgeTransitions.php` — runs daily. Logic:
  1. Find patients with `date_of_birth` = today − 18 years → set `age_transition_expires_at = now() + 30 days` on all active family_links for that patient; dispatch notification.
  2. Find patients whose 18th birthday is in exactly 60 days → dispatch 60-day warning notification; set `age_transition_notified_at`.
  3. Find family_links where `age_transition_expires_at < now()` and `status = active` → set `status = expired`.

---

## Routes

All new routes are added inside the existing authenticated patient portal middleware group in `routes/web.php`. No existing routes are modified.

```php
// Family management
Route::get('/portals/patient/family',                     [FamilyController::class, 'index'])->name('portals.patient.family');
Route::get('/portals/patient/family/add',                 [FamilyController::class, 'addForm'])->name('portals.patient.family.add');
Route::post('/portals/patient/family/add',                [FamilyController::class, 'store'])->name('portals.patient.family.store');
Route::get('/portals/patient/family/invite',              [FamilyController::class, 'inviteForm'])->name('portals.patient.family.invite');
Route::post('/portals/patient/family/invite',             [FamilyController::class, 'sendInvite'])->name('portals.patient.family.invite.send');
Route::post('/portals/patient/family/switch/{patientId}', [FamilyController::class, 'switchTo'])->name('portals.patient.family.switch');
Route::post('/portals/patient/family/switch-back',        [FamilyController::class, 'switchBack'])->name('portals.patient.family.switch.back');
Route::get('/portals/patient/family/{link}/edit',         [FamilyController::class, 'editForm'])->name('portals.patient.family.edit');
Route::post('/portals/patient/family/{link}/edit',        [FamilyController::class, 'update'])->name('portals.patient.family.update');
Route::post('/portals/patient/family/{link}/revoke',      [FamilyController::class, 'revoke'])->name('portals.patient.family.revoke');
Route::post('/portals/patient/family/{link}/guardian-consent/approve', [FamilyController::class, 'guardianConsentApprove'])->name('portals.patient.family.guardian_consent.approve');
Route::post('/portals/patient/family/{link}/guardian-consent/deny',    [FamilyController::class, 'guardianConsentDeny'])->name('portals.patient.family.guardian_consent.deny');

// Public invite acceptance (no auth required)
Route::get('/family/invite/accept/{token}',  [FamilyController::class, 'acceptInvite'])->name('portals.patient.family.invite.accept');
Route::post('/family/invite/accept/{token}', [FamilyController::class, 'confirmInvite'])->name('portals.patient.family.invite.confirm');
```

---

## Access Control

`GuardianAccessMiddleware` is registered in `bootstrap/app.php` (or `Kernel.php` depending on Laravel 13 setup) as a named middleware `guardian.context`. It is applied only to routes that support guardian context switching — not to the family management routes themselves (those always operate on the authenticated user's own data).

Authorization within `FamilyController`: every action verifies `$link->guardian_user_id === auth()->id()` before proceeding. Model binding with a custom `resolveRouteBinding()` on `FamilyLink` enforces this automatically.

Invite token: stored as `hash('sha256', $rawToken)` in the database. The raw token is embedded in the invite URL. Lookup by hashed value — never expose the hash to the client.

---

## Audit Trail

When guardian context is active, `PortalContextService::auditPatientAccess()` is called with:
- `patientId` = dependent's patient ID
- `actorId` = guardian's user ID (passed explicitly)
- `actorType` = `'guardian'`

The `MedicalIdAccessEvent` table records this without schema changes — `actor_type` and `actor_id` already exist.

`ConsentGrant.authorizing_actor` is set to `'guardian'` when a guardian approves a consent request on behalf of a dependent. This field already accepts `guardian` as a value — no migration needed.

---

## Notification Events Reference

| Event key | Trigger | Default: portal | email | sms |
|---|---|---|---|---|
| `lab_result` | `LabResult::created` | ✓ | ✓ | — |
| `appointment` | `Appointment::created` or `updated` | ✓ | ✓ | — |
| `consent_request` | `ConsentRequest::created` | ✓ | ✓ | ✓ |
| `age_transition` | `CheckAgeTransitions` command | ✓ | ✓ | ✓ |

---

## Age Transition Rules

- Age of majority: **18 years** (configurable via `config('family.majority_age')`, default 18)
- Warning period: 60 days before 18th birthday
- Grace period: 30 days after 18th birthday
- After grace period: link status → `expired`; guardian session cleared on next request
- Dependent re-grants access: `age_transition_expires_at` set to null; link remains `active`

---

## What Is Not In Scope (This Iteration)

- Facility admin UI for creating facility-assigned links (links can be created via artisan tinker until a dedicated admin form is built in a later iteration)
- SMS gateway integration (the `toSms()` method is a stub; wire up Twilio/Vonage separately)
- Push notifications (mobile app scope)
- Guardian-to-guardian delegation (a guardian delegating their own guardian access to another user)

---

## Testing Plan

Each task produces PHPUnit feature tests before implementation (TDD). Key test cases:

- Guardian can view dependent's labs/appointments/prescriptions/documents when link is active
- Guardian cannot view dependent's data when link is revoked or expired
- Read-only guardian receives 403 on write actions (profile update, consent approve/deny)
- Full-access guardian can approve/deny consent on behalf of dependent
- Invite token flow: invite created → token sent → accepted → link activated
- Self-registration: new Patient record created and linked, not overwriting any existing patient
- Age transition command: sets `age_transition_expires_at` on correct links, expires links past grace period
- Guardian notification dispatched when lab result created for dependent
- Audit event records `actor_type = guardian` and correct `actor_id` when guardian views data
- Switching back clears `guardian_viewing_patient_id` from session
