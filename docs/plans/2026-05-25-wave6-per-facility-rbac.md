# Wave 6 — Per-Facility RBAC Scoping

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement per-facility role assignment. A user can have different roles at different facilities. A nurse at Facility A cannot use their role at Facility B. Roles remain global definitions; the assignment (user ↔ role ↔ facility) is what becomes per-facility. Apply Waves 1–5 first.

**Architecture:** Add a `facility_role_assignments` pivot table (user_id, facility_id, role_id). Update `EnsurePortalAccess` and the context service to check this table. Preserve backward compatibility by reading from `users.role_id` as fallback during a migration window.

**Tech Stack:** Laravel 13, PostgreSQL

**Findings addressed:** M1 (per-facility roles)

---

## Files Modified in This Wave

| File | Change |
|------|--------|
| `database/migrations/2026_05_25_000004_create_facility_role_assignments_table.php` | NEW |
| `app/Models/FacilityRoleAssignment.php` | NEW Eloquent model |
| `app/Models/User.php` | Add `facilityRoles()` relationship |
| `app/Models/Role.php` | Verify relationship |
| `app/Http/Middleware/EnsurePortalAccess.php` | Use facility-scoped role lookup |
| `app/Services/Portal/PortalContextService.php` | Expose current facility role |
| `database/seeders/FacilityRoleAssignmentSeeder.php` | NEW — migrate existing role_id to new table |
| `tests/Feature/Security/PerFacilityRbacTest.php` | NEW |

---

### Task 1: Create facility_role_assignments table

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration create_facility_role_assignments_table
```

Edit the migration:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_role_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('facility_id')
                ->constrained('facilities')
                ->cascadeOnDelete();
            $table->foreignUuid('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->uuid('assigned_by')->nullable(); // user who made the assignment
            $table->timestampTz('assigned_at')->useCurrent();
            $table->timestampTz('expires_at')->nullable(); // optional time-limited access
            $table->timestampsTz();

            // A user can only have one active role per facility
            $table->unique(['user_id', 'facility_id', 'role_id'], 'fra_user_facility_role_unique');
            $table->index(['user_id', 'facility_id'], 'fra_user_facility_index');
            $table->index(['facility_id', 'role_id'], 'fra_facility_role_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_role_assignments');
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

Expected: Table created without error.

- [ ] **Step 3: Create FacilityRoleAssignment model**

Create `app/Models/FacilityRoleAssignment.php`:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityRoleAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'facility_id',
        'role_id',
        'is_active',
        'assigned_by',
        'assigned_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'assigned_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
```

- [ ] **Step 4: Add facilityRole() helper to User model**

In `app/Models/User.php`, add:

```php
public function facilityRoleAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(FacilityRoleAssignment::class);
}

/**
 * Get the role for this user at a specific facility.
 * Falls back to the global role_id for backward compatibility during transition.
 */
public function roleAtFacility(string $facilityId): ?Role
{
    $assignment = $this->facilityRoleAssignments()
        ->active()
        ->where('facility_id', $facilityId)
        ->with('role')
        ->first();

    if ($assignment) {
        return $assignment->role;
    }

    // Backward compatibility fallback — used during transition from global roles
    return $this->role ?? null;
}
```

- [ ] **Step 5: Commit**

```bash
git add database/migrations/ app/Models/FacilityRoleAssignment.php app/Models/User.php
git commit -m "feat: create facility_role_assignments table and FacilityRoleAssignment model"
```

---

### Task 2: Update EnsurePortalAccess to use facility-scoped role

**Files:**
- Modify: `app/Http/Middleware/EnsurePortalAccess.php`
- Test: `tests/Feature/Security/PerFacilityRbacTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/PerFacilityRbacTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use App\Models\FacilityRoleAssignment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerFacilityRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_role_at_facility_a_cannot_access_portal_at_facility_b(): void
    {
        $facilityA = 'facility-a-uuid-0000-0000-000000000001';
        $facilityB = 'facility-b-uuid-0000-0000-000000000002';

        $role = Role::factory()->create(['name' => 'doctor']);
        $user = User::factory()->create(['role_id' => null]);

        // Assign role only at Facility A
        FacilityRoleAssignment::create([
            'user_id'     => $user->id,
            'facility_id' => $facilityA,
            'role_id'     => $role->id,
            'is_active'   => true,
        ]);

        // Attempt access to patient portal at Facility B — must be 403
        $response = $this->actingAs($user)
            ->withSession(['active_facility_id' => $facilityB])
            ->get(route('portals.patient'));

        $response->assertStatus(403);
    }

    public function test_user_with_role_at_facility_a_can_access_portal_at_facility_a(): void
    {
        $facilityA = 'facility-a-uuid-0000-0000-000000000001';

        $role = Role::factory()->create(['name' => 'doctor']);
        $user = User::factory()->create(['role_id' => null]);

        FacilityRoleAssignment::create([
            'user_id'     => $user->id,
            'facility_id' => $facilityA,
            'role_id'     => $role->id,
            'is_active'   => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_facility_id' => $facilityA])
            ->get(route('portals.patient'));

        // Should NOT be 403 — user has role at this facility
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/PerFacilityRbacTest.php
```

Expected: FAIL — facility B access is allowed (still uses global role)

- [ ] **Step 3: Update EnsurePortalAccess to check facility-scoped role**

Open `app/Http/Middleware/EnsurePortalAccess.php`. In the `handle()` method, replace the global role lookup with a facility-scoped one:

```php
public function handle(Request $request, Closure $next, string $portalType = 'patient'): Response
{
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    // Get the current facility from session
    $facilityId = session('active_facility_id') ?? $user->primary_facility_id;

    // Look up role for this user at this specific facility
    $role = null;
    if ($facilityId && method_exists($user, 'roleAtFacility')) {
        $role = $user->roleAtFacility($facilityId);
    } else {
        // Fallback: global role (backward compatibility)
        $role = $user->role ?? null;
    }

    // Patient portal: patients don't need a facility role — they access their own data
    if ($portalType === 'patient' && $user->patient_id) {
        return $next($request);
    }

    // All other portals: require a valid role at the current facility
    if (!$role) {
        abort(403, 'You do not have a role assigned at this facility. Contact your administrator.');
    }

    // Check portal-role mapping (existing PORTAL_ROLES logic)
    $allowedPortals = self::PORTAL_ROLES[$role->name] ?? [];
    if (!in_array($portalType, $allowedPortals, true)) {
        abort(403, 'Your role does not permit access to this portal.');
    }

    return $next($request);
}
```

- [ ] **Step 4: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/PerFacilityRbacTest.php
```

Expected: PASS

- [ ] **Step 5: Run full test suite**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/EnsurePortalAccess.php tests/Feature/Security/PerFacilityRbacTest.php
git commit -m "feat: update EnsurePortalAccess to use per-facility role assignment"
```

---

### Task 3: Seeder to migrate existing global role_id assignments

**Files:**
- Create: `database/seeders/FacilityRoleAssignmentSeeder.php`

- [ ] **Step 1: Create the seeder**

Create `database/seeders/FacilityRoleAssignmentSeeder.php`:

```php
<?php
namespace Database\Seeders;

use App\Models\FacilityRoleAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class FacilityRoleAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Migrating global role_id assignments to per-facility assignments...');

        $migrated = 0;
        $skipped  = 0;

        User::whereNotNull('role_id')
            ->whereNotNull('primary_facility_id')
            ->chunkById(100, function ($users) use (&$migrated, &$skipped) {
                foreach ($users as $user) {
                    // Check if already migrated
                    $exists = FacilityRoleAssignment::where('user_id', $user->id)
                        ->where('facility_id', $user->primary_facility_id)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    FacilityRoleAssignment::create([
                        'user_id'     => $user->id,
                        'facility_id' => $user->primary_facility_id,
                        'role_id'     => $user->role_id,
                        'is_active'   => true,
                        'assigned_by' => null,
                        'assigned_at' => $user->created_at,
                    ]);
                    $migrated++;
                }
            });

        $this->command->info("Done. Migrated: {$migrated} | Skipped (already exists): {$skipped}");
    }
}
```

- [ ] **Step 2: Run the seeder**

```bash
php artisan db:seed --class=FacilityRoleAssignmentSeeder
```

Expected: All existing users with role_id get a corresponding per-facility assignment.

- [ ] **Step 3: Verify**

```bash
php artisan tinker --execute="echo App\Models\FacilityRoleAssignment::count() . ' assignments created';"
```

Expected: Count matches users with role_id + primary_facility_id.

- [ ] **Step 4: Commit**

```bash
git add database/seeders/FacilityRoleAssignmentSeeder.php
git commit -m "feat: seeder to migrate existing global role_id to per-facility role assignments"
```

---

### Task 4: Wave 6 final verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --parallel
```

Expected: All tests pass.

- [ ] **Step 2: Verify per-facility RBAC works end-to-end**

```bash
php artisan test tests/Feature/Security/PerFacilityRbacTest.php -v
```

Expected: Both tests pass.

- [ ] **Step 3: Verify seeder ran correctly**

```bash
php artisan tinker --execute="
\$usersWithRole = App\Models\User::whereNotNull('role_id')->whereNotNull('primary_facility_id')->count();
\$assignments   = App\Models\FacilityRoleAssignment::count();
echo \"Users with role_id: \$usersWithRole | FRA records: \$assignments\";
"
```

Expected: Counts match (or FRA >= users, meaning some users have multiple facility assignments).
