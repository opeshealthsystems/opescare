# OpesCare Production Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the current production-readiness blockers for the deployed `opescare.cloud` platform and produce a safe, repeatable Git-to-server deployment path.

**Architecture:** Fix authorization at the route/middleware boundary first, then harden public callback/auth surfaces, then close subscription/MFA/config gaps. Each task adds tests before implementation and includes a verification command so we can prove the blocker is closed before pushing.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, PHPUnit/Pest via `php artisan test`, Composer audit, npm audit/build, Flutter patient app.

---

## File Structure

- Modify: `apps/api-laravel/routes/api.php`
  - Add `api.admin` to platform-admin API groups that currently use only `VerifyIntegrationClient`.
- Modify: `apps/api-laravel/routes/communications.php`
  - Put patient/staff communication routes behind authenticated middleware and admin communication routes behind `verify.integration.client` plus `api.admin`.
- Modify: `apps/api-laravel/app/Http/Controllers/Api/V1/CommunicationController.php`
  - Stop trusting request-body `user_id`; derive actor from authenticated user/request attributes or reject.
- Modify: `apps/api-laravel/app/Http/Controllers/Api/V1/MobileMoneyCallbackController.php`
  - Verify callback signatures/shared secrets before finalizing payments.
- Modify: `apps/api-laravel/config/services.php`
  - Add explicit mobile money callback secret/signature config keys.
- Modify: `apps/api-laravel/app/Http/Controllers/Api/ProviderMobile/ProviderMobileAuthController.php`
  - Replace credential/OTP stubs with real verification and session-bound OTP challenge.
- Create or modify: `apps/api-laravel/app/Models/ProviderOtpCode.php`
  - Persist provider OTP challenge state if no equivalent model already exists.
- Create or modify: `apps/api-laravel/database/migrations/*provider_otp_codes*`
  - Store hashed provider OTPs with expiry, use marker, device fingerprint, and user binding.
- Modify: `apps/api-laravel/app/Http/Controllers/Api/Ussd/UssdController.php`
  - Verify Africa's Talking callback source/signature or configured shared secret.
- Modify: `apps/api-laravel/config/services.php`
  - Add USSD callback verification config.
- Modify: `apps/api-laravel/app/Http/Middleware/EnforceModuleEntitlement.php`
  - Query `is_enabled` and enforce cleanly.
- Modify: selected module route groups in `apps/api-laravel/routes/api.php`
  - Attach `module:<key>` to billable modules.
- Modify: `apps/api-laravel/app/Http/Controllers/PublicPageController.php`
  - Wire MFA challenge into login flow for roles from `config/mfa.php`.
- Create or modify: `apps/api-laravel/app/Http/Middleware/EnsureTwoFactorVerified.php`
  - Prevent privileged portal access until MFA challenge is satisfied.
- Modify: `apps/api-laravel/routes/web.php`
  - Add MFA challenge routes and apply MFA middleware to privileged portal routes.
- Modify: `apps/api-laravel/config/cors.php`
  - Restrict CORS via environment-configured allowed origins.
- Modify: `apps/api-laravel/app/Providers/ProductionSafetyServiceProvider.php`
  - Fail fast in production for dangerous configuration, not just log.
- Modify: `apps/mobile-patient/lib/firebase_options.dart`
  - Replace stub with real FlutterFire output before mobile production build.
- Modify: `apps/mobile-patient/lib/features/auth/data/auth_repository.dart`
  - Fix platform detection.
- Modify: `apps/api-laravel/composer.json` and `apps/api-laravel/composer.lock`
  - Update vulnerable Composer packages to advisory-safe versions.
- Test: add focused feature tests under `apps/api-laravel/tests/Feature/Security/`
  - Production route authorization, communication auth, callback signatures, provider mobile auth, USSD verification, module entitlements, MFA enforcement, production config safety.

---

### Task 1: Lock Admin API Routes Behind `api.admin`

**Files:**
- Modify: `apps/api-laravel/routes/api.php`
- Test: `apps/api-laravel/tests/Feature/Security/AdminApiAuthorizationTest.php`

- [ ] **Step 1: Write the route-regression test**

Create `apps/api-laravel/tests/Feature/Security/AdminApiAuthorizationTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class AdminApiAuthorizationTest extends TestCase
{
    public function test_all_v1_admin_routes_require_api_admin_middleware(): void
    {
        $violations = collect(app('router')->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/v1/admin'))
            ->reject(fn ($route) => in_array('api.admin', $route->gatherMiddleware(), true))
            ->map(fn ($route) => implode('|', $route->methods()) . ' ' . $route->uri())
            ->values()
            ->all();

        $this->assertSame([], $violations);
    }
}
```

- [ ] **Step 2: Run the test and confirm it fails**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/AdminApiAuthorizationTest.php
```

Expected: FAIL listing current admin routes without `api.admin`.

- [ ] **Step 3: Add `api.admin` to the main admin route group**

In `apps/api-laravel/routes/api.php`, change:

```php
Route::prefix('v1/admin')->middleware(VerifyIntegrationClient::class)->group(function () {
```

to:

```php
Route::prefix('v1/admin')->middleware([VerifyIntegrationClient::class, 'api.admin'])->group(function () {
```

- [ ] **Step 4: Ensure communication admin routes also require `api.admin`**

In `apps/api-laravel/routes/communications.php`, change:

```php
Route::prefix('admin')->middleware(['verify.integration.client'])->group(function () {
```

to:

```php
Route::prefix('admin')->middleware(['verify.integration.client', 'api.admin'])->group(function () {
```

- [ ] **Step 5: Verify the route-regression test passes**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/AdminApiAuthorizationTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add apps/api-laravel/routes/api.php apps/api-laravel/routes/communications.php apps/api-laravel/tests/Feature/Security/AdminApiAuthorizationTest.php
git commit -m "fix: require admin role for admin api routes"
```

---

### Task 2: Authenticate Communication APIs And Remove Body-User Trust

**Files:**
- Modify: `apps/api-laravel/routes/communications.php`
- Modify: `apps/api-laravel/app/Http/Controllers/Api/V1/CommunicationController.php`
- Test: `apps/api-laravel/tests/Feature/Security/CommunicationAuthorizationTest.php`

- [ ] **Step 1: Write route middleware regression tests**

Create `apps/api-laravel/tests/Feature/Security/CommunicationAuthorizationTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class CommunicationAuthorizationTest extends TestCase
{
    public function test_all_non_admin_communication_routes_require_authentication(): void
    {
        $prefixes = [
            'api/v1/notifications',
            'api/v1/notification-preferences',
            'api/v1/tasks',
            'api/v1/messages',
            'api/v1/broadcasts',
        ];

        $violations = collect(app('router')->getRoutes())
            ->filter(fn ($route) => collect($prefixes)->contains(fn ($prefix) => str_starts_with($route->uri(), $prefix)))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'api/v1/admin'))
            ->reject(function ($route) {
                $middleware = $route->gatherMiddleware();
                return in_array('auth:sanctum', $middleware, true)
                    || in_array('auth.mobile', $middleware, true)
                    || in_array('auth', $middleware, true)
                    || in_array('verify.integration.client', $middleware, true);
            })
            ->map(fn ($route) => implode('|', $route->methods()) . ' ' . $route->uri())
            ->values()
            ->all();

        $this->assertSame([], $violations);
    }
}
```

- [ ] **Step 2: Run the test and confirm it fails**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/CommunicationAuthorizationTest.php
```

Expected: FAIL listing communication routes with only `api`.

- [ ] **Step 3: Add middleware groups in `routes/communications.php`**

Wrap patient/staff communication routes in an authenticated API middleware group:

```php
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Notifications, preferences, tasks, messages, broadcasts
});
```

Keep admin communication routes under:

```php
Route::prefix('admin')->middleware(['verify.integration.client', 'api.admin'])->group(function () {
    // Admin templates, deliveries, escalations, broadcasts
});
```

- [ ] **Step 4: Add a controller helper for actor identity**

In `CommunicationController`, add:

```php
private function actorUserId(Request $request): string
{
    $user = $request->user();

    if ($user?->id) {
        return (string) $user->id;
    }

    $providerId = $request->attributes->get('provider_id');
    if (is_string($providerId) && \Illuminate\Support\Str::isUuid($providerId)) {
        return $providerId;
    }

    abort(401, 'Authenticated user context is required.');
}
```

- [ ] **Step 5: Replace request-body `user_id` in user-scoped methods**

In methods such as `getNotifications`, `getUnreadCount`, `getThreads`, `createThread`, `getThread`, and `sendMessage`, replace validation of body `user_id` with:

```php
$userId = $this->actorUserId($request);
```

Use `$userId` in queries and permission checks.

- [ ] **Step 6: Verify route middleware test passes**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/CommunicationAuthorizationTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add apps/api-laravel/routes/communications.php apps/api-laravel/app/Http/Controllers/Api/V1/CommunicationController.php apps/api-laravel/tests/Feature/Security/CommunicationAuthorizationTest.php
git commit -m "fix: authenticate communication APIs"
```

---

### Task 3: Verify Mobile Money Callback Authenticity

**Files:**
- Modify: `apps/api-laravel/app/Http/Controllers/Api/V1/MobileMoneyCallbackController.php`
- Modify: `apps/api-laravel/config/services.php`
- Test: `apps/api-laravel/tests/Feature/Security/MobileMoneyCallbackSecurityTest.php`

- [ ] **Step 1: Write unsigned-callback rejection tests**

Create `apps/api-laravel/tests/Feature/Security/MobileMoneyCallbackSecurityTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class MobileMoneyCallbackSecurityTest extends TestCase
{
    public function test_mtn_callback_requires_valid_signature_when_secret_configured(): void
    {
        config(['services.mtn_momo.callback_secret' => 'test-secret']);

        $this->postJson('/api/payments/mobile-money/mtn/callback', [
            'referenceId' => 'pay_123',
            'status' => 'SUCCESSFUL',
        ])->assertStatus(401);
    }

    public function test_orange_callback_requires_valid_signature_when_secret_configured(): void
    {
        config(['services.orange_money.callback_secret' => 'test-secret']);

        $this->postJson('/api/payments/mobile-money/orange/callback', [
            'txnid' => 'pay_123',
            'status' => 'SUCCESS',
        ])->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run tests and confirm they fail**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/MobileMoneyCallbackSecurityTest.php
```

Expected: FAIL because unsigned callbacks are accepted.

- [ ] **Step 3: Add service config keys**

In `apps/api-laravel/config/services.php`, add:

```php
'mtn_momo' => [
    'callback_secret' => env('MTN_MOMO_CALLBACK_SECRET'),
],

'orange_money' => [
    'callback_secret' => env('ORANGE_MONEY_CALLBACK_SECRET'),
],
```

- [ ] **Step 4: Add signature verification helper**

In `MobileMoneyCallbackController`, add:

```php
private function verifyCallbackSignature(Request $request, ?string $secret): bool
{
    if (!$secret) {
        return !app()->isProduction();
    }

    $signature = (string) $request->header('X-Callback-Signature', '');
    if ($signature === '') {
        return false;
    }

    $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

    return hash_equals($expected, $signature);
}
```

- [ ] **Step 5: Enforce the helper in both callbacks**

At the top of `mtnCallback`:

```php
if (!$this->verifyCallbackSignature($request, config('services.mtn_momo.callback_secret'))) {
    return response()->json(['error' => 'Invalid callback signature.'], 401);
}
```

At the top of `orangeCallback`:

```php
if (!$this->verifyCallbackSignature($request, config('services.orange_money.callback_secret'))) {
    return response()->json(['error' => 'Invalid callback signature.'], 401);
}
```

- [ ] **Step 6: Verify tests pass**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/MobileMoneyCallbackSecurityTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add apps/api-laravel/app/Http/Controllers/Api/V1/MobileMoneyCallbackController.php apps/api-laravel/config/services.php apps/api-laravel/tests/Feature/Security/MobileMoneyCallbackSecurityTest.php
git commit -m "fix: verify mobile money callbacks"
```

---

### Task 4: Replace Provider Mobile Login And OTP Stubs

**Files:**
- Modify: `apps/api-laravel/app/Http/Controllers/Api/ProviderMobile/ProviderMobileAuthController.php`
- Create: `apps/api-laravel/app/Models/ProviderOtpCode.php`
- Create: `apps/api-laravel/database/migrations/YYYY_MM_DD_HHMMSS_create_provider_otp_codes_table.php`
- Test: `apps/api-laravel/tests/Feature/Security/ProviderMobileAuthSecurityTest.php`

- [ ] **Step 1: Write OTP stub rejection tests**

Create `apps/api-laravel/tests/Feature/Security/ProviderMobileAuthSecurityTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProviderMobileAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_login_rejects_invalid_pin_hash(): void
    {
        User::factory()->create([
            'email' => 'clinician@example.test',
            'password' => Hash::make('correct-password'),
            'status' => 'active',
        ]);

        $this->postJson('/api/provider-mobile/auth/login', [
            'email' => 'clinician@example.test',
            'pin_hash' => 'wrong-password',
            'device_fingerprint' => 'device-1',
            'platform' => 'android',
        ])->assertStatus(401);
    }

    public function test_provider_otp_verify_rejects_without_pending_challenge(): void
    {
        $this->postJson('/api/provider-mobile/auth/otp/verify', [
            'otp_code' => '123456',
            'device_fingerprint' => 'device-1',
            'platform' => 'android',
        ])->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run tests and confirm they fail**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/ProviderMobileAuthSecurityTest.php
```

Expected: FAIL because stubs accept too much.

- [ ] **Step 3: Create provider OTP table migration**

Create a migration with:

```php
Schema::create('provider_otp_codes', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('device_fingerprint', 128);
    $table->string('code_hash');
    $table->timestampTz('expires_at');
    $table->timestampTz('used_at')->nullable();
    $table->timestampsTz();
    $table->index(['user_id', 'device_fingerprint', 'used_at']);
});
```

- [ ] **Step 4: Create `ProviderOtpCode` model**

Create `apps/api-laravel/app/Models/ProviderOtpCode.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProviderOtpCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'code_hash',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
```

- [ ] **Step 5: Verify provider credentials in login**

In `ProviderMobileAuthController@login`, after loading `$user`, reject invalid credentials with:

```php
if (!\Illuminate\Support\Facades\Hash::check($validated['pin_hash'], $user->password)) {
    return response()->json(['error' => 'Invalid credentials.'], 401);
}
```

Then create a hashed OTP challenge:

```php
$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
\App\Models\ProviderOtpCode::create([
    'user_id' => $user->id,
    'device_fingerprint' => $validated['device_fingerprint'],
    'code_hash' => \Illuminate\Support\Facades\Hash::make($otp),
    'expires_at' => now()->addMinutes(10),
]);
```

- [ ] **Step 6: Verify OTP against pending challenge**

In `verifyOtp`, find and consume the latest pending OTP:

```php
$otpRecord = \App\Models\ProviderOtpCode::where('device_fingerprint', $validated['device_fingerprint'])
    ->whereNull('used_at')
    ->where('expires_at', '>', now())
    ->latest()
    ->first();

if (!$otpRecord || !\Illuminate\Support\Facades\Hash::check($validated['otp_code'], $otpRecord->code_hash)) {
    return response()->json(['error' => 'Invalid or expired OTP.'], 401);
}

$otpRecord->update(['used_at' => now()]);
$userId = $otpRecord->user_id;
```

Use this `$userId` instead of `resolveUserId($request)` in `verifyOtp`.

- [ ] **Step 7: Verify tests pass**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/ProviderMobileAuthSecurityTest.php
```

Expected: PASS.

- [ ] **Step 8: Commit**

```powershell
git add apps/api-laravel/app/Http/Controllers/Api/ProviderMobile/ProviderMobileAuthController.php apps/api-laravel/app/Models/ProviderOtpCode.php apps/api-laravel/database/migrations apps/api-laravel/tests/Feature/Security/ProviderMobileAuthSecurityTest.php
git commit -m "fix: replace provider mobile auth stubs"
```

---

### Task 5: Verify USSD Callback Source

**Files:**
- Modify: `apps/api-laravel/app/Http/Controllers/Api/Ussd/UssdController.php`
- Modify: `apps/api-laravel/config/services.php`
- Test: `apps/api-laravel/tests/Feature/Security/UssdCallbackSecurityTest.php`

- [ ] **Step 1: Write unsigned USSD rejection test**

Create `apps/api-laravel/tests/Feature/Security/UssdCallbackSecurityTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class UssdCallbackSecurityTest extends TestCase
{
    public function test_ussd_callback_requires_shared_secret_when_configured(): void
    {
        config(['services.africastalking.ussd_callback_secret' => 'secret']);

        $this->post('/api/ussd/callback', [
            'sessionId' => 's1',
            'serviceCode' => '*123#',
            'phoneNumber' => '+237600000000',
            'text' => '',
        ])->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run test and confirm it fails**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/UssdCallbackSecurityTest.php
```

Expected: FAIL.

- [ ] **Step 3: Add config key**

In `config/services.php`, add:

```php
'africastalking' => [
    'ussd_callback_secret' => env('AFRICASTALKING_USSD_CALLBACK_SECRET'),
],
```

- [ ] **Step 4: Enforce shared secret header**

At the top of `UssdController::callback`, add:

```php
$secret = config('services.africastalking.ussd_callback_secret');
if ($secret && !hash_equals($secret, (string) $request->header('X-USSD-Callback-Secret'))) {
    return response('Unauthorized', 401);
}
```

In production, configure `AFRICASTALKING_USSD_CALLBACK_SECRET`.

- [ ] **Step 5: Verify test passes**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/UssdCallbackSecurityTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add apps/api-laravel/app/Http/Controllers/Api/Ussd/UssdController.php apps/api-laravel/config/services.php apps/api-laravel/tests/Feature/Security/UssdCallbackSecurityTest.php
git commit -m "fix: verify ussd callbacks"
```

---

### Task 6: Fix And Attach Module Entitlement Enforcement

**Files:**
- Modify: `apps/api-laravel/app/Http/Middleware/EnforceModuleEntitlement.php`
- Modify: `apps/api-laravel/routes/api.php`
- Test: `apps/api-laravel/tests/Feature/Security/ModuleEntitlementSecurityTest.php`

- [ ] **Step 1: Write field-name regression test**

Create `apps/api-laravel/tests/Feature/Security/ModuleEntitlementSecurityTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class ModuleEntitlementSecurityTest extends TestCase
{
    public function test_module_entitlement_middleware_uses_is_enabled_column(): void
    {
        $source = file_get_contents(app_path('Http/Middleware/EnforceModuleEntitlement.php'));

        $this->assertStringContainsString("where('is_enabled', true)", $source);
        $this->assertStringNotContainsString("where('enabled', true)", $source);
    }

    public function test_billable_modules_have_module_middleware_attached(): void
    {
        $routes = collect(app('router')->getRoutes());

        $expected = [
            'api/v1/billing' => 'module:billing',
            'api/v1/insurance' => 'module:insurance',
            'api/v1/telemedicine' => 'module:telemedicine',
            'api/v1/analytics' => 'module:analytics',
        ];

        foreach ($expected as $prefix => $middleware) {
            $matching = $routes->filter(fn ($route) => str_starts_with($route->uri(), $prefix));
            $this->assertNotEmpty($matching, "No routes found for {$prefix}");
            $this->assertTrue(
                $matching->every(fn ($route) => in_array($middleware, $route->gatherMiddleware(), true)),
                "{$prefix} must include {$middleware}"
            );
        }
    }
}
```

- [ ] **Step 2: Run test and confirm it fails**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/ModuleEntitlementSecurityTest.php
```

Expected: FAIL.

- [ ] **Step 3: Fix middleware column**

In `EnforceModuleEntitlement.php`, change:

```php
->where('enabled', true)
```

to:

```php
->where('is_enabled', true)
```

- [ ] **Step 4: Attach module middleware to selected billable route groups**

In `routes/api.php`, change examples:

```php
Route::prefix('v1/billing')->middleware(VerifyIntegrationClient::class)->group(function () {
```

to:

```php
Route::prefix('v1/billing')->middleware([VerifyIntegrationClient::class, 'module:billing'])->group(function () {
```

Apply the same pattern to the billable modules in the test.

- [ ] **Step 5: Verify tests pass**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/ModuleEntitlementSecurityTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add apps/api-laravel/app/Http/Middleware/EnforceModuleEntitlement.php apps/api-laravel/routes/api.php apps/api-laravel/tests/Feature/Security/ModuleEntitlementSecurityTest.php
git commit -m "fix: enforce module entitlements"
```

---

### Task 7: Wire MFA Enforcement For Privileged Portal Roles

**Files:**
- Modify: `apps/api-laravel/app/Http/Controllers/PublicPageController.php`
- Create: `apps/api-laravel/app/Http/Middleware/EnsureTwoFactorVerified.php`
- Modify: `apps/api-laravel/routes/web.php`
- Test: `apps/api-laravel/tests/Feature/Auth/TwoFactorLoginFlowTest.php`

- [ ] **Step 1: Write login-flow enforcement test**

Create `apps/api-laravel/tests/Feature/Auth/TwoFactorLoginFlowTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorLoginFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_privileged_user_is_redirected_to_mfa_challenge_after_password_login(): void
    {
        config(['mfa.required_roles' => ['super_admin']]);

        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);

        $user->role()->create(['name' => 'super_admin']);

        $response = $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/mfa/challenge');
    }
}
```

- [ ] **Step 2: Run test and confirm it fails**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Auth/TwoFactorLoginFlowTest.php
```

Expected: FAIL because login does not branch to MFA challenge.

- [ ] **Step 3: Add MFA session handoff in `submitLogin`**

After password authentication but before final portal redirect, add:

```php
if ($user->requiresTwoFactor()) {
    $request->session()->put('mfa:user_id', $user->id);
    $request->session()->forget('mfa:verified');

    return redirect('/mfa/challenge');
}
```

- [ ] **Step 4: Add MFA middleware**

Create `EnsureTwoFactorVerified.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->requiresTwoFactor() && !$request->session()->get('mfa:verified')) {
            return redirect('/mfa/challenge');
        }

        return $next($request);
    }
}
```

- [ ] **Step 5: Register challenge routes**

In `routes/web.php`, add GET/POST routes for `/mfa/challenge` that display a form and verify a TOTP or recovery code using `TwoFactorService`.

- [ ] **Step 6: Apply MFA middleware to privileged portal groups**

Add `mfa.verified` to privileged portal route groups after `auth`.

- [ ] **Step 7: Verify tests pass**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Auth/TwoFactorLoginFlowTest.php tests/Feature/Auth/TwoFactorServiceTest.php
```

Expected: PASS.

- [ ] **Step 8: Commit**

```powershell
git add apps/api-laravel/app/Http/Controllers/PublicPageController.php apps/api-laravel/app/Http/Middleware/EnsureTwoFactorVerified.php apps/api-laravel/routes/web.php apps/api-laravel/tests/Feature/Auth/TwoFactorLoginFlowTest.php
git commit -m "fix: enforce mfa for privileged users"
```

---

### Task 8: Harden Production Config, CORS, And Composer Advisories

**Files:**
- Modify: `apps/api-laravel/config/cors.php`
- Modify: `apps/api-laravel/app/Providers/ProductionSafetyServiceProvider.php`
- Modify: `apps/api-laravel/composer.json`
- Modify: `apps/api-laravel/composer.lock`
- Test: `apps/api-laravel/tests/Feature/Security/ProductionConfigurationTest.php`

- [ ] **Step 1: Write production config safety test**

Create `apps/api-laravel/tests/Feature/Security/ProductionConfigurationTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class ProductionConfigurationTest extends TestCase
{
    public function test_cors_origins_are_environment_configured(): void
    {
        $source = file_get_contents(config_path('cors.php'));

        $this->assertStringContainsString('CORS_ALLOWED_ORIGINS', $source);
        $this->assertStringNotContainsString("'allowed_origins' => ['*']", $source);
    }

    public function test_production_safety_provider_throws_for_debug_mode(): void
    {
        $source = file_get_contents(app_path('Providers/ProductionSafetyServiceProvider.php'));

        $this->assertStringContainsString('throw new', $source);
        $this->assertStringContainsString('APP_DEBUG', $source);
    }
}
```

- [ ] **Step 2: Run test and confirm it fails**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/ProductionConfigurationTest.php
```

Expected: FAIL.

- [ ] **Step 3: Restrict CORS through env**

In `config/cors.php`, change `allowed_origins` to:

```php
'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'https://opescare.cloud,https://www.opescare.cloud')))),
```

- [ ] **Step 4: Fail fast on dangerous production config**

In `ProductionSafetyServiceProvider`, replace log-only critical checks for production debug/demo mode with exceptions:

```php
throw new \RuntimeException('Unsafe production configuration: APP_DEBUG must be false.');
```

Use clear exception messages for debug mode and demo mode.

- [ ] **Step 5: Update Composer dependencies**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar update laravel/framework guzzlehttp/psr7 symfony/http-foundation symfony/http-kernel symfony/mailer symfony/mime symfony/polyfill-intl-idn symfony/routing --with-all-dependencies
```

Expected: lockfile updates to advisory-safe versions.

- [ ] **Step 6: Verify Composer audit is clean**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar audit --no-dev
```

Expected: no production advisories.

- [ ] **Step 7: Verify tests pass**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Security/ProductionConfigurationTest.php
```

Expected: PASS.

- [ ] **Step 8: Commit**

```powershell
git add apps/api-laravel/config/cors.php apps/api-laravel/app/Providers/ProductionSafetyServiceProvider.php apps/api-laravel/composer.json apps/api-laravel/composer.lock apps/api-laravel/tests/Feature/Security/ProductionConfigurationTest.php
git commit -m "fix: harden production configuration"
```

---

### Task 9: Fix Mobile Patient Release Blockers

**Files:**
- Modify: `apps/mobile-patient/lib/firebase_options.dart`
- Modify: `apps/mobile-patient/lib/features/auth/data/auth_repository.dart`
- Test: `apps/mobile-patient/test/production_readiness_test.dart`

- [ ] **Step 1: Write mobile readiness tests**

Create `apps/mobile-patient/test/production_readiness_test.dart`:

```dart
import 'dart:io';

import 'package:flutter_test/flutter_test.dart';

void main() {
  test('firebase options are not the generated stub', () {
    final file = File('lib/firebase_options.dart');
    final source = file.readAsStringSync();

    expect(source, isNot(contains('throw UnimplementedError')));
    expect(source, isNot(contains('STUB')));
  });

  test('auth repository does not use dart.library.html as android detection', () {
    final file = File('lib/features/auth/data/auth_repository.dart');
    final source = file.readAsStringSync();

    expect(source, isNot(contains("bool.fromEnvironment('dart.library.html')")));
  });
}
```

- [ ] **Step 2: Run test and confirm it fails**

Run:

```powershell
cd C:\laragon\www\opescare\apps\mobile-patient
flutter test test/production_readiness_test.dart
```

Expected: FAIL until Firebase options are generated and platform detection is fixed.

- [ ] **Step 3: Generate Firebase options**

Run from `apps/mobile-patient`:

```powershell
flutterfire configure --project=opescare-patient
```

Expected: `lib/firebase_options.dart` contains real `FirebaseOptions`.

- [ ] **Step 4: Fix platform detection**

In `auth_repository.dart`, replace `_platform()` with:

```dart
String _platform() {
  if (kIsWeb) return 'web';
  if (Platform.isIOS) return 'ios';
  return 'android';
}
```

Also import:

```dart
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;
```

- [ ] **Step 5: Verify mobile tests pass**

Run:

```powershell
cd C:\laragon\www\opescare\apps\mobile-patient
flutter test test/production_readiness_test.dart
```

Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add apps/mobile-patient/lib/firebase_options.dart apps/mobile-patient/lib/features/auth/data/auth_repository.dart apps/mobile-patient/test/production_readiness_test.dart
git commit -m "fix: clear mobile patient release blockers"
```

---

### Task 10: Full Local Verification Before Push

**Files:**
- No source edits unless verification exposes a defect.

- [ ] **Step 1: Clear Laravel caches**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan optimize:clear
```

Expected: cache clear commands succeed.

- [ ] **Step 2: Run Laravel tests**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --stop-on-failure
```

Expected: PASS.

- [ ] **Step 3: Run Composer production audit**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar audit --no-dev
```

Expected: no production advisories.

- [ ] **Step 4: Build Laravel frontend**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
npm run build
```

Expected: production assets build successfully.

- [ ] **Step 5: Run npm production audits**

Run:

```powershell
cd C:\laragon\www\opescare\apps\api-laravel
npm audit --omit=dev
cd C:\laragon\www\opescare\sdk\typescript
npm audit --omit=dev
```

Expected: `found 0 vulnerabilities`.

- [ ] **Step 6: Re-run route scan**

Run a route-table check that confirms:

```text
adminNoApiAdminCount = 0
communicationNoAuthCount = 0
moduleMiddlewareRouteCount > 0
```

- [ ] **Step 7: Commit any verification fixes**

```powershell
git status --short
git add <changed files>
git commit -m "test: verify production hardening"
```

---

### Task 11: Push And Deploy To `opescare.cloud`

**Files:**
- No source edits.

- [ ] **Step 1: Push branch**

Run locally:

```powershell
git push origin <branch-name>
```

Expected: branch pushed.

- [ ] **Step 2: Merge through GitHub**

Merge only after checks and review are complete.

- [ ] **Step 3: SSH to live server**

Run from local machine:

```powershell
ssh <deploy-user>@opescare.cloud
```

- [ ] **Step 4: Pull and deploy on live server**

Run on server, adjusting the path to the actual deployed repo:

```bash
cd /var/www/opescare
git fetch origin
git checkout main
git pull --ff-only origin main
cd apps/api-laravel
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan down --render="errors::503" || true
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
npm ci
npm run build
php artisan queue:restart
php artisan up
```

- [ ] **Step 5: Confirm required production env values**

Before `config:cache`, ensure live `.env` contains:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://opescare.cloud
CORS_ALLOWED_ORIGINS=https://opescare.cloud,https://www.opescare.cloud
MTN_MOMO_CALLBACK_SECRET=<provider-secret>
ORANGE_MONEY_CALLBACK_SECRET=<provider-secret>
AFRICASTALKING_USSD_CALLBACK_SECRET=<provider-secret>
MFA_REQUIRED_ROLES=super_admin,platform_admin,system_admin,facility_admin
SESSION_SECURE_COOKIE=true
```

- [ ] **Step 6: Post-deploy health checks**

Run on server:

```bash
php artisan about
php artisan migrate:status
php artisan schedule:list
curl -I https://opescare.cloud/login
```

Expected:

```text
Environment: production
Debug Mode: OFF
Config/Routes/Views: CACHED
HTTP login route returns 200 or 302 over HTTPS
```

---

## Self-Review

- Spec coverage: This plan covers the confirmed blockers from the audit: admin route authorization, communication API auth, mobile money signatures, provider mobile auth stubs, USSD verification, module entitlement enforcement, MFA enforcement, production config/CORS, Composer advisories, and mobile release blockers.
- Placeholder scan: No task relies on unspecified "fix later" work. Provider secrets and server paths are intentionally deployment-specific and represented as explicit environment values/placeholders to be filled on the live server.
- Type consistency: Middleware names match the existing route names: `verify.integration.client`, `api.admin`, `auth.mobile`, `auth:sanctum`, and `module:<key>`. The entitlement field is consistently corrected to `is_enabled`.
