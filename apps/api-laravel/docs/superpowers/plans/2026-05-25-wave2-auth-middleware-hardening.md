# Wave 2 — Auth & Middleware Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Harden all authentication middleware gaps — rate limiting for provider mobile auth, roleless user portal pass-through, trust-level comparison logic, role_id mass-assignment, facility context bypass, and CareMap admin role check. Zero database migrations.

**Architecture:** All changes are in middleware and route configuration. Each task is an independent fix with its own test. Apply Wave 1 before this wave.

**Tech Stack:** Laravel 13, PHP 8.3

**Findings addressed:** H5, H15, M2, M3, M10, M13

---

## Files Modified in This Wave

| File | Change |
|------|--------|
| `routes/api.php` | Add `throttle:5,1` to provider mobile auth routes |
| `app/Http/Middleware/EnsurePortalAccess.php` | abort(403) for roleless users instead of pass-through |
| `app/Http/Middleware/VerifyPartnerTrustLevel.php` | Implement trust level comparison |
| `app/Http/Middleware/RequireFacilityContext.php` | Explicit super-admin bypass with audit |
| `app/Models/User.php` | Remove role_id from $fillable |
| `tests/Feature/Security/ProviderMobileRateLimitTest.php` | NEW |
| `tests/Feature/Security/PortalAccessRolelessTest.php` | NEW |
| `tests/Feature/Security/PartnerTrustLevelTest.php` | NEW |
| `tests/Feature/Security/UserMassAssignmentTest.php` | NEW |

---

### Task 1: Add rate limiting to provider mobile auth routes

**Finding:** H5 — Provider mobile `/auth/login` and `/auth/otp/verify` have no throttle.

**Files:**
- Modify: `routes/api.php`
- Test: `tests/Feature/Security/ProviderMobileRateLimitTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/ProviderMobileRateLimitTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderMobileRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_mobile_login_is_rate_limited(): void
    {
        // Hit the login endpoint 6 times — should get 429 on the 6th
        for ($i = 0; $i < 5; $i++) {
            $this->post('/api/provider-mobile/auth/login', [
                'email'    => 'test@test.com',
                'password' => 'wrong',
            ]);
        }

        $response = $this->post('/api/provider-mobile/auth/login', [
            'email'    => 'test@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
    }

    public function test_provider_mobile_otp_verify_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/api/provider-mobile/auth/otp/verify', ['otp' => '000000']);
        }

        $response = $this->post('/api/provider-mobile/auth/otp/verify', ['otp' => '000000']);
        $response->assertStatus(429);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/ProviderMobileRateLimitTest.php
```

Expected: FAIL — 422 or 401 on 6th request, not 429

- [ ] **Step 3: Add throttle middleware to provider mobile auth routes**

Open `routes/api.php`. Find the provider mobile auth routes section:

```php
// BEFORE:
Route::prefix('provider-mobile')->group(function () {
    // Public auth endpoints
    Route::post('/auth/login', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'login']);
    Route::post('/auth/otp/verify', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'verifyOtp']);
    Route::post('/auth/push-token', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'registerPushToken']);
    Route::post('/auth/logout', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'logout']);
```

```php
// AFTER:
Route::prefix('provider-mobile')->group(function () {
    // Public auth endpoints — rate-limited to 5 requests per minute (brute-force protection)
    Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'login']);
        Route::post('/otp/verify', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'verifyOtp']);
        Route::post('/push-token', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'registerPushToken']);
        Route::post('/logout', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'logout']);
    });
```

- [ ] **Step 4: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/ProviderMobileRateLimitTest.php
```

Expected: PASS

- [ ] **Step 5: Run full test suite**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add routes/api.php tests/Feature/Security/ProviderMobileRateLimitTest.php
git commit -m "security: add throttle:5,1 to provider mobile auth endpoints"
```

---

### Task 2: Fix EnsurePortalAccess roleless user pass-through

**Finding:** H15 — Users with no role bypass portal access control.

**Files:**
- Modify: `app/Http/Middleware/EnsurePortalAccess.php`
- Test: `tests/Feature/Security/PortalAccessRolelessTest.php`

- [ ] **Step 1: Read current EnsurePortalAccess**

```bash
cat app/Http/Middleware/EnsurePortalAccess.php
```

Find the line (approximately line 88-91) that returns `$next($request)` when the user has no role.

- [ ] **Step 2: Write the failing test**

Create `tests/Feature/Security/PortalAccessRolelessTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAccessRolelessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_no_role_cannot_access_patient_portal(): void
    {
        // User with no role assigned
        $user = User::factory()->create(['role_id' => null]);

        $response = $this->actingAs($user)
            ->withSession(['active_facility_id' => 'test-facility'])
            ->get(route('portals.patient'));

        // Must be 403, not 200
        $response->assertStatus(403);
    }

    public function test_user_with_no_role_cannot_access_admin_portal(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $response = $this->actingAs($user)
            ->withSession(['active_facility_id' => 'test-facility'])
            ->get('/portal/admin');

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 3: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/PortalAccessRolelessTest.php
```

Expected: FAIL — user passes through (200 or redirect, not 403)

- [ ] **Step 4: Fix EnsurePortalAccess**

Open `app/Http/Middleware/EnsurePortalAccess.php`. Find the section that handles missing role and replace the pass-through with an abort:

```php
// BEFORE (find this pattern — user has no role, returns next):
if (!$user->role) {
    return $next($request);
}
// OR: if role is null, falls through to return $next($request)

// AFTER — replace with:
if (!$user->role) {
    abort(403, 'Your account has no role assigned. Contact your administrator.');
}
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/PortalAccessRolelessTest.php
```

Expected: PASS

- [ ] **Step 6: Run full test suite to catch any regressions**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Middleware/EnsurePortalAccess.php tests/Feature/Security/PortalAccessRolelessTest.php
git commit -m "security: abort(403) for roleless users in EnsurePortalAccess instead of pass-through"
```

---

### Task 3: Implement VerifyPartnerTrustLevel comparison logic

**Finding:** M2 — `$minTrustLevel` parameter declared but never used; all partners pass regardless of trust level.

**Files:**
- Modify: `app/Http/Middleware/VerifyPartnerTrustLevel.php`
- Test: `tests/Feature/Security/PartnerTrustLevelTest.php`

- [ ] **Step 1: Read current VerifyPartnerTrustLevel**

```bash
cat app/Http/Middleware/VerifyPartnerTrustLevel.php
```

Identify the `handle(Request $request, Closure $next, $minTrustLevel)` signature and note that `$minTrustLevel` is not compared to the partner's actual trust level.

- [ ] **Step 2: Write the failing test**

Create `tests/Feature/Security/PartnerTrustLevelTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerTrustLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_below_minimum_trust_level_is_rejected(): void
    {
        // Trust levels: 1=basic, 2=verified, 3=certified, 4=strategic
        // A partner with trust_level=1 should be rejected from a min_trust=3 route
        $middleware = new \App\Http\Middleware\VerifyPartnerTrustLevel();

        $request = \Illuminate\Http\Request::create('/test', 'GET');
        // Simulate a partner with trust_level=1 set on request attributes
        $request->attributes->set('partner_trust_level', 1);

        $called = false;
        $next = function ($req) use (&$called) {
            $called = true;
            return response()->json(['ok' => true]);
        };

        $response = $middleware->handle($request, $next, '3');

        $this->assertFalse($called, 'Next should not be called for low-trust partner');
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_partner_meeting_minimum_trust_level_is_allowed(): void
    {
        $middleware = new \App\Http\Middleware\VerifyPartnerTrustLevel();
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->attributes->set('partner_trust_level', 4);

        $called = false;
        $next = function ($req) use (&$called) {
            $called = true;
            return response()->json(['ok' => true]);
        };

        $response = $middleware->handle($request, $next, '3');

        $this->assertTrue($called, 'Next should be called for sufficient-trust partner');
    }
}
```

- [ ] **Step 3: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/PartnerTrustLevelTest.php
```

Expected: FAIL — `$called` is true even for low-trust partner (comparison missing)

- [ ] **Step 4: Implement trust level comparison**

Open `app/Http/Middleware/VerifyPartnerTrustLevel.php`. In the `handle()` method, add the comparison after the partner is resolved:

```php
public function handle(Request $request, Closure $next, string $minTrustLevel = '1'): Response
{
    // Get partner trust level from request attributes (set by upstream partner auth middleware)
    $partnerTrustLevel = (int) $request->attributes->get('partner_trust_level', 0);
    $required          = (int) $minTrustLevel;

    if ($partnerTrustLevel < $required) {
        return response()->json([
            'error'   => 'insufficient_trust_level',
            'message' => 'Your partner account does not meet the trust level required for this operation.',
        ], 403);
    }

    return $next($request);
}
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/PartnerTrustLevelTest.php
```

Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/VerifyPartnerTrustLevel.php tests/Feature/Security/PartnerTrustLevelTest.php
git commit -m "security: implement trust level comparison in VerifyPartnerTrustLevel middleware"
```

---

### Task 4: Remove role_id from User.$fillable

**Finding:** M10 — role_id in $fillable enables role escalation through mass assignment.

**Files:**
- Modify: `app/Models/User.php`
- Test: `tests/Feature/Security/UserMassAssignmentTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/UserMassAssignmentTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_id_cannot_be_mass_assigned_on_user(): void
    {
        $originalRoleId = 'original-role-uuid';
        $user = User::factory()->create(['role_id' => $originalRoleId]);

        // Attempt mass assignment of role_id
        $user->fill(['role_id' => 'attacker-role-uuid', 'name' => 'New Name']);
        $user->save();

        // role_id should NOT have changed
        $this->assertEquals($originalRoleId, $user->fresh()->role_id,
            'role_id must not be mass-assignable');

        // name CAN change (it's in fillable)
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_is_demo_cannot_be_mass_assigned_on_user(): void
    {
        $user = User::factory()->create(['is_demo' => false]);
        $user->fill(['is_demo' => true]);
        $user->save();

        $this->assertFalse((bool) $user->fresh()->is_demo,
            'is_demo must not be mass-assignable on User');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/UserMassAssignmentTest.php
```

Expected: FAIL — role_id changes (currently in $fillable)

- [ ] **Step 3: Update User model**

Open `app/Models/User.php`. Find `$fillable` array and remove `role_id` and `is_demo`:

```php
// BEFORE ($fillable contains role_id and possibly is_demo):
protected $fillable = [
    'name',
    'email',
    'password',
    'role_id',          // ← REMOVE THIS
    'is_demo',          // ← REMOVE THIS (if present)
    'primary_facility_id',
    'patient_id',
    'status',
    // ... other fields
];

// AFTER:
protected $fillable = [
    'name',
    'email',
    'password',
    'primary_facility_id',
    'patient_id',
    'status',
    'phone',
    'language',
    // ... other fields EXCEPT role_id and is_demo
];
```

Add a comment above `$fillable`:

```php
// role_id and is_demo are intentionally excluded from $fillable.
// Assign roles via: $user->role()->associate($role); $user->save();
// Toggle demo status via: $user->forceFill(['is_demo' => true])->save();
protected $fillable = [
```

- [ ] **Step 4: Check all existing code that sets role_id**

```bash
grep -rn "role_id" app/Http/Controllers/ app/Services/ database/seeders/ | grep -v test
```

For any place that uses `User::create(['role_id' => ...])` or `$user->fill(['role_id' => ...])`, update to use direct assignment:

```php
// If any seeder/controller does:
User::create(['role_id' => $roleId, 'name' => 'X', ...])
// Change to:
$user = User::create(['name' => 'X', ...]);
$user->role_id = $roleId;
$user->save();
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/UserMassAssignmentTest.php
```

Expected: PASS

- [ ] **Step 6: Run full test suite**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 7: Commit**

```bash
git add app/Models/User.php tests/Feature/Security/UserMassAssignmentTest.php
git commit -m "security: remove role_id and is_demo from User fillable to prevent mass assignment"
```

---

### Task 5: Make RequireFacilityContext bypass explicit for super-admin

**Finding:** M3 — Facility context bypassed implicitly for patient/admin portals; platform admins operate with no facility scoping.

**Files:**
- Modify: `app/Http/Middleware/RequireFacilityContext.php`
- Test: `tests/Feature/Security/FacilityContextMiddlewareTest.php`

- [ ] **Step 1: Read current RequireFacilityContext**

```bash
cat app/Http/Middleware/RequireFacilityContext.php
```

Find line 48 where the bypass for patient/admin portals occurs.

- [ ] **Step 2: Write the failing test**

Create `tests/Feature/Security/FacilityContextMiddlewareTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityContextMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_bypass_is_logged_when_no_facility_context(): void
    {
        // A user with a super_admin flag and no facility context
        $admin = User::factory()->create([
            'is_super_admin'       => true,
            'primary_facility_id'  => null,
        ]);

        \Illuminate\Support\Facades\Log::spy();

        $response = $this->actingAs($admin)
            ->get(route('portals.patient'));

        // Should not be 403 (super-admin bypasses facility requirement)
        // But the bypass should be logged
        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')
            ->with('super_admin_facility_bypass', \Mockery::any())
            ->atLeast()->once();
    }
}
```

- [ ] **Step 3: Update RequireFacilityContext**

Open `app/Http/Middleware/RequireFacilityContext.php`. Find the bypass section and make it explicit:

```php
// BEFORE (implicit bypass for patient/admin portals):
// Something like: if (str_starts_with($request->path(), 'portal')) return $next($request);

// AFTER — replace implicit bypass with explicit super-admin check:
$user = Auth::user();

// Patient portal bypasses facility context — patients access their own data, not facility data
if ($request->routeIs('portals.patient*')) {
    return $next($request);
}

// Super-admin bypass — must be explicit and logged
if ($user && property_exists($user, 'is_super_admin') && $user->is_super_admin) {
    \Illuminate\Support\Facades\Log::info('super_admin_facility_bypass', [
        'user_id'    => $user->id,
        'path'       => $request->path(),
        'ip_address' => $request->ip(),
    ]);
    return $next($request);
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/Security/FacilityContextMiddlewareTest.php
php artisan test
```

Expected: All tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Middleware/RequireFacilityContext.php tests/Feature/Security/FacilityContextMiddlewareTest.php
git commit -m "security: make super-admin facility bypass explicit and audited in RequireFacilityContext"
```

---

### Task 6: Add admin role check to CareMap admin routes

**Finding:** M13 — `POST /v1/care-map/admin/facilities/{id}/verify` behind `auth:sanctum` only; any authenticated web user can verify/suspend a facility.

**Files:**
- Modify: `routes/api.php`
- Test: `tests/Feature/Security/CareMapAdminAuthTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/CareMapAdminAuthTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareMapAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_verify_care_map_facility(): void
    {
        // A regular patient user (no admin role)
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/v1/care-map/admin/facilities/some-facility-id/verify');

        // Must be 403, not 200
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_suspend_care_map_facility(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/v1/care-map/admin/facilities/some-facility-id/suspend');

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/CareMapAdminAuthTest.php
```

Expected: FAIL — 404 or 200, not 403

- [ ] **Step 3: Add admin guard to CareMap admin routes**

In `routes/api.php`, find the CareMap admin routes:

```php
// BEFORE:
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/facilities/{id}/save', ...);
    Route::post('/facilities/{id}/report', ...);
    Route::post('/facilities/{id}/claim', ...);
    Route::post('/partner/facilities/{id}/stock-sync', ...);
    // Admin actions
    Route::post('/admin/facilities/{id}/verify', ...);
    Route::post('/admin/facilities/{id}/suspend', ...);
});

// AFTER — separate admin actions into their own stricter group:
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/facilities/{id}/save', [\App\Http\Controllers\Api\V1\CareMapController::class, 'saveFacility']);
    Route::post('/facilities/{id}/report', [\App\Http\Controllers\Api\V1\CareMapController::class, 'reportFacility']);
    Route::post('/facilities/{id}/claim', [\App\Http\Controllers\Api\V1\CareMapController::class, 'claimFacility']);
    Route::post('/partner/facilities/{id}/stock-sync', [\App\Http\Controllers\Api\V1\CareMapController::class, 'partnerStockSync']);
});

// Admin-only CareMap actions — require sanctum + admin role
Route::middleware(['auth:sanctum', 'portal.access:admin'])->group(function () {
    Route::post('/admin/facilities/{id}/verify', [\App\Http\Controllers\Api\V1\CareMapController::class, 'adminVerifyFacility']);
    Route::post('/admin/facilities/{id}/suspend', [\App\Http\Controllers\Api\V1\CareMapController::class, 'adminSuspendFacility']);
});
```

- [ ] **Step 4: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/CareMapAdminAuthTest.php
```

Expected: PASS

- [ ] **Step 5: Run full test suite**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add routes/api.php tests/Feature/Security/CareMapAdminAuthTest.php
git commit -m "security: restrict CareMap admin verify/suspend to portal.access:admin middleware"
```

---

### Task 7: Wave 2 final verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --parallel
```

Expected: All tests pass.

- [ ] **Step 2: Verify provider mobile throttle is live**

```bash
grep -A5 "provider-mobile" routes/api.php | grep throttle
```

Expected: `throttle:5,1` present

- [ ] **Step 3: Verify no roleless pass-through**

```bash
grep -A3 "role" app/Http/Middleware/EnsurePortalAccess.php | grep -i "abort\|403\|next"
```

Expected: `abort(403)` before any `$next($request)` when role is null.
