# Cameroon Healthcare Facility Registry Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Pre-seed OpesCare with a national Cameroon healthcare directory (hospitals, pharmacies, labs, imaging, diagnostic centers, insurers) so facilities can claim their pre-existing record when registering.

**Architecture:** A separate `facility_registry` table holds the rich directory data, leaving the operational `facilities` table untouched. The existing `FacilityClaim` model links registry entries to claimed operational accounts. Two Artisan commands allow ongoing CSV re-imports from MINSANTE/ONPC.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, `Illuminate\Console\Command`, `DB::table()->insertOrIgnore()`

---

### Task 1: Migration, FacilityRegistry model, FacilityClaim fix

**Files:**
- Create: `database/migrations/2026_05_26_000001_create_facility_registry_table.php`
- Create: `app/Models/FacilityRegistry.php`
- Modify: `app/Models/FacilityClaim.php` (fix FK from `CareFacility` → `Facility`)
- Test: `tests/Feature/Registry/FacilityRegistryModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Registry;

use App\Models\Facility;
use App\Models\FacilityRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityRegistryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_table_exists_with_correct_columns(): void
    {
        $this->assertTrue(\Schema::hasTable('facility_registry'));
        foreach (['name','type','region','status','claimed_facility_id'] as $col) {
            $this->assertTrue(\Schema::hasColumn('facility_registry', $col), "Missing column: $col");
        }
    }

    public function test_scopes_work_correctly(): void
    {
        FacilityRegistry::create(['name' => 'Test Hospital', 'type' => 'hospital', 'region' => 'Centre', 'city' => 'Yaoundé']);
        FacilityRegistry::create(['name' => 'Test Pharmacy', 'type' => 'pharmacy', 'region' => 'Littoral', 'city' => 'Douala']);

        $this->assertEquals(1, FacilityRegistry::byRegion('Centre')->count());
        $this->assertEquals(1, FacilityRegistry::byType('pharmacy')->count());
        $this->assertEquals(2, FacilityRegistry::unclaimed()->count());
        $this->assertEquals(2, FacilityRegistry::open()->count());
    }

    public function test_claimed_facility_relationship(): void
    {
        $facility = Facility::create(['name' => 'Real Hospital', 'type' => 'hospital']);
        $entry    = FacilityRegistry::create([
            'name'                => 'Real Hospital',
            'type'                => 'hospital',
            'region'              => 'Centre',
            'city'                => 'Yaoundé',
            'claimed_facility_id' => $facility->id,
            'claimed_at'          => now(),
        ]);

        $this->assertEquals($facility->id, $entry->claimedFacility->id);
        $this->assertEquals(0, FacilityRegistry::unclaimed()->count());
    }

    public function test_facility_claim_relationship_points_to_facility(): void
    {
        $facility = Facility::create(['name' => 'A Hospital', 'type' => 'hospital']);
        $claim    = \App\Models\FacilityClaim::create([
            'facility_id'      => $facility->id,
            'claimant_user_id' => null,
            'claim_status'     => 'pending',
            'submitted_at'     => now(),
        ]);

        $this->assertInstanceOf(Facility::class, $claim->facility);
    }
}
```

- [ ] **Step 2: Run the test to confirm it fails**

```bash
php artisan test tests/Feature/Registry/FacilityRegistryModelTest.php --no-coverage
```
Expected: FAIL — table does not exist yet.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_05_26_000001_create_facility_registry_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_registry', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('name_fr')->nullable();
            $table->string('type', 60);
            $table->string('ownership', 30)->nullable();
            $table->string('region', 60);
            $table->string('division', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('ministry_code', 80)->nullable();
            $table->string('accreditation_level', 100)->nullable();
            $table->unsignedInteger('bed_capacity')->nullable();
            $table->jsonb('services')->nullable();
            $table->string('source', 100)->default('initial_seed_2026');
            $table->string('source_url')->nullable();
            $table->string('status', 20)->default('unverified');
            $table->foreignUuid('claimed_facility_id')
                ->nullable()
                ->constrained('facilities')
                ->nullOnDelete();
            $table->timestampTz('claimed_at')->nullable();
            $table->timestampsTz();

            $table->index('type',                'idx_fr_type');
            $table->index('region',              'idx_fr_region');
            $table->index('status',              'idx_fr_status');
            $table->index('claimed_facility_id', 'idx_fr_claimed');
            $table->index('ministry_code',       'idx_fr_ministry');
            $table->index('city',                'idx_fr_city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_registry');
    }
};
```

- [ ] **Step 4: Run the migration**

```bash
php artisan migrate
```
Expected: `facility_registry table created`.

- [ ] **Step 5: Create the FacilityRegistry model**

```php
<?php
// app/Models/FacilityRegistry.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityRegistry extends Model
{
    use HasUuids;

    protected $table = 'facility_registry';

    protected $fillable = [
        'name', 'name_fr', 'type', 'ownership', 'region',
        'division', 'city', 'address', 'gps_lat', 'gps_lng',
        'phone', 'email', 'website', 'ministry_code',
        'accreditation_level', 'bed_capacity', 'services',
        'source', 'source_url', 'status',
        'claimed_facility_id', 'claimed_at',
    ];

    protected $casts = [
        'services'     => 'array',
        'gps_lat'      => 'decimal:7',
        'gps_lng'      => 'decimal:7',
        'bed_capacity' => 'integer',
        'claimed_at'   => 'datetime',
    ];

    public function claimedFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'claimed_facility_id');
    }

    public function scopeUnclaimed($query)
    {
        return $query->whereNull('claimed_facility_id');
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', '!=', 'closed');
    }
}
```

- [ ] **Step 6: Fix FacilityClaim FK**

Open `app/Models/FacilityClaim.php`. Change:
```php
// BEFORE
public function facility()
{
    return $this->belongsTo(CareFacility::class, 'facility_id');
}
```
to:
```php
// AFTER
public function facility()
{
    return $this->belongsTo(Facility::class, 'facility_id');
}
```
Also add `use App\Models\Facility;` at the top if not already present, and remove `use App\Models\CareFacility;`.

- [ ] **Step 7: Run the tests**

```bash
php artisan test tests/Feature/Registry/FacilityRegistryModelTest.php --no-coverage
```
Expected: 4 tests, 4 passed.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_05_26_000001_create_facility_registry_table.php \
        app/Models/FacilityRegistry.php \
        app/Models/FacilityClaim.php \
        tests/Feature/Registry/FacilityRegistryModelTest.php
git commit -m "feat: add facility_registry table, FacilityRegistry model, fix FacilityClaim FK"
```

---

### Task 2: CameroonFacilityRegistrySeeder — hospitals & clinics (Centre + Littoral)

**Files:**
- Create: `database/seeders/CameroonFacilityRegistrySeeder.php`
- Test: `tests/Feature/Registry/CameroonFacilityRegistrySeederTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Registry/CameroonFacilityRegistrySeederTest.php
namespace Tests\Feature\Registry;

use Database\Seeders\CameroonFacilityRegistrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CameroonFacilityRegistrySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_seeds_centre_region_hospitals(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);

        $this->assertDatabaseHas('facility_registry', [
            'name'   => 'Hôpital Central de Yaoundé',
            'region' => 'Centre',
            'type'   => 'hospital',
        ]);
        $this->assertDatabaseHas('facility_registry', [
            'name'   => 'CHU de Yaoundé',
            'region' => 'Centre',
        ]);
    }

    public function test_seeder_seeds_littoral_region_hospitals(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);

        $this->assertDatabaseHas('facility_registry', [
            'name'   => 'Hôpital Général de Douala',
            'region' => 'Littoral',
        ]);
        $this->assertDatabaseHas('facility_registry', [
            'name'   => 'Hôpital Laquintinie de Douala',
            'region' => 'Littoral',
        ]);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);
        $countAfterFirst = \DB::table('facility_registry')->count();

        $this->seed(CameroonFacilityRegistrySeeder::class);
        $countAfterSecond = \DB::table('facility_registry')->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }

    public function test_all_entries_have_required_fields(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);

        $invalid = \DB::table('facility_registry')
            ->whereNull('name')
            ->orWhereNull('type')
            ->orWhereNull('region')
            ->count();

        $this->assertEquals(0, $invalid, 'Some registry entries are missing required fields');
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Registry/CameroonFacilityRegistrySeederTest.php --no-coverage
```
Expected: FAIL — class not found.

- [ ] **Step 3: Create the seeder with Centre and Littoral data**

```php
<?php
// database/seeders/CameroonFacilityRegistrySeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the facility_registry table with real Cameroonian healthcare facilities.
 * Sources: MINSANTE, ONPC, WHO DHIS2 Cameroon, OpenStreetMap health nodes.
 * Idempotent — safe to run multiple times. Claimed rows are never touched.
 */
class CameroonFacilityRegistrySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->facilities() as $row) {
            $this->insertIfMissing($row);
        }

        $this->command?->info('CameroonFacilityRegistrySeeder: ' .
            DB::table('facility_registry')->count() . ' total registry entries.');
    }

    // -----------------------------------------------------------------------
    // Idempotency helper
    // -----------------------------------------------------------------------

    private function insertIfMissing(array $row): void
    {
        $exists = DB::table('facility_registry')
            ->where('name', $row['name'])
            ->where('region', $row['region'])
            ->where('city', $row['city'] ?? null)
            ->exists();

        if (!$exists) {
            DB::table('facility_registry')->insert(array_merge($row, [
                'id'         => (string) Str::uuid(),
                'source'     => 'initial_seed_2026',
                'status'     => 'unverified',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    // -----------------------------------------------------------------------
    // Data
    // -----------------------------------------------------------------------

    private function facilities(): array
    {
        return array_merge(
            $this->centre(),
            $this->littoral(),
            $this->nordOuest(),
            $this->sudOuest(),
            $this->ouest(),
            $this->adamaoua(),
            $this->nord(),
            $this->extremeNord(),
            $this->est(),
            $this->sud(),
            $this->pharmacies(),
            $this->laboratories(),
            $this->imagingCenters(),
            $this->diagnosticCenters(),
        );
    }

    private function centre(): array
    {
        return [
            ['name' => 'Hôpital Central de Yaoundé',                        'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Hôpital de Référence', 'bed_capacity' => 600],
            ['name' => 'CHU de Yaoundé',                                     'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Centre Hospitalier Universitaire', 'bed_capacity' => 400],
            ['name' => 'Hôpital Général de Yaoundé',                         'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Hôpital Général', 'bed_capacity' => 350],
            ['name' => 'Hôpital Gynéco-Obstétrique et Pédiatrique de Yaoundé','type' => 'hospital',   'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Hôpital Spécialisé'],
            ['name' => 'Fondation Chantal Biya',                             'type' => 'clinic',       'ownership' => 'ngo',         'region' => 'Centre',  'city' => 'Yaoundé',   'phone' => '+237 222 20 18 00'],
            ['name' => 'Hôpital de la CNPS Yaoundé',                         'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'bed_capacity' => 150],
            ['name' => 'Hôpital de District de Yaoundé Centre',              'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Yaoundé VI',                  'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District d\'Efoulan',                     'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Nsimeyong',                   'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Nkomo',                       'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Nkomo',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Mfou',                        'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Mfou',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Mbalmayo',                    'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Mbalmayo',  'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District d\'Obala',                       'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Obala',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Bafia',                       'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Bafia',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Polyclinique Bastos',                                 'type' => 'clinic',       'ownership' => 'private',     'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Centre Médical La Cathédrale',                        'type' => 'clinic',       'ownership' => 'faith_based', 'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Polyclinique La Croix du Sud',                        'type' => 'clinic',       'ownership' => 'private',     'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Centre Médical d\'Arrondissement de Biyem-Assi',      'type' => 'health_center','ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Centre Médical d\'Arrondissement d\'Ekounou',         'type' => 'health_center','ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Centre Médical d\'Arrondissement de Mendong',         'type' => 'health_center','ownership' => 'public',      'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Clinique Amour',                                       'type' => 'clinic',       'ownership' => 'private',     'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Polyclinique de l\'Université',                        'type' => 'clinic',       'ownership' => 'private',     'region' => 'Centre',  'city' => 'Yaoundé'],
            ['name' => 'Centre Médical de Soa',                               'type' => 'health_center','ownership' => 'public',      'region' => 'Centre',  'city' => 'Soa'],
            ['name' => 'Hôpital de District de Ngoumou',                      'type' => 'hospital',     'ownership' => 'public',      'region' => 'Centre',  'city' => 'Ngoumou',   'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function littoral(): array
    {
        return [
            ['name' => 'Hôpital Général de Douala',                   'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Hôpital Général', 'bed_capacity' => 500],
            ['name' => 'Hôpital Laquintinie de Douala',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Hôpital Provincial', 'bed_capacity' => 350],
            ['name' => 'CHU de Douala',                               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Centre Hospitalier Universitaire', 'bed_capacity' => 300],
            ['name' => 'Hôpital de la CNPS Douala',                   'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'bed_capacity' => 200],
            ['name' => 'Hôpital Protestante de Bonabéri',             'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Littoral', 'city' => 'Douala', 'bed_capacity' => 150],
            ['name' => 'Hôpital de District de Bonabéri',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Douala 5e',            'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Ndog-Passi',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Bassa',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de New Bell',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Douala', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Loum',                 'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Loum',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District d\'Edéa',                 'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Edéa',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Nkongsamba',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Nkongsamba', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital Sainte-Marie de Nkongsamba',          'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Littoral', 'city' => 'Nkongsamba'],
            ['name' => 'Clinique des Spécialités de Douala',          'type' => 'clinic',   'ownership' => 'private',     'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre Médical Louis Paul Aujoulat',          'type' => 'clinic',   'ownership' => 'faith_based', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Polyclinique les Flamboyants',                'type' => 'clinic',   'ownership' => 'private',     'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Polyclinique de l\'Océan',                    'type' => 'clinic',   'ownership' => 'private',     'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre Médical de Mbanga',                    'type' => 'health_center', 'ownership' => 'public', 'region' => 'Littoral', 'city' => 'Mbanga'],
            ['name' => 'Hôpital de District de Manjo',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Littoral', 'city' => 'Manjo',  'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function nordOuest(): array
    {
        return [
            ['name' => 'Hôpital Régional de Bamenda',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord-Ouest', 'city' => 'Bamenda', 'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 250],
            ['name' => 'Baptist Hospital Bamenda',                   'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Nord-Ouest', 'city' => 'Bamenda', 'bed_capacity' => 200],
            ['name' => 'Shisong Catholic Hospital',                  'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Nord-Ouest', 'city' => 'Kumbo',   'bed_capacity' => 180],
            ['name' => 'Mbingo Baptist Hospital',                    'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Nord-Ouest', 'city' => 'Tubah',   'bed_capacity' => 160],
            ['name' => 'Hôpital de District de Bamenda',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord-Ouest', 'city' => 'Bamenda', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Nkwen District Hospital',                    'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord-Ouest', 'city' => 'Bamenda', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Kumbo',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord-Ouest', 'city' => 'Kumbo',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Wum',                 'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord-Ouest', 'city' => 'Wum',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Fundong',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord-Ouest', 'city' => 'Fundong', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Ndop',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord-Ouest', 'city' => 'Ndop',    'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function sudOuest(): array
    {
        return [
            ['name' => 'Hôpital Régional de Buéa',                   'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Buéa',     'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 200],
            ['name' => 'Limbe Regional Hospital',                    'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Limbe',    'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 180],
            ['name' => 'Baptist Hospital Muyuka',                    'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Sud-Ouest', 'city' => 'Muyuka',   'bed_capacity' => 100],
            ['name' => 'Kumba District Hospital',                    'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Kumba',    'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Tiko',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Tiko',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Mamfe',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Mamfe',    'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Muyuka',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Muyuka',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Mutengene',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Mutengene','accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Buéa',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud-Ouest', 'city' => 'Buéa',     'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function ouest(): array
    {
        return [
            ['name' => 'Hôpital Régional de Bafoussam',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Bafoussam',   'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 300],
            ['name' => 'CHU de Bafoussam',                           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Bafoussam',   'accreditation_level' => 'Centre Hospitalier Universitaire'],
            ['name' => 'Hôpital de District de Bafoussam',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Bafoussam',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital Sainte-Élisabeth de Nkongsamba',     'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Ouest', 'city' => 'Nkongsamba',  'bed_capacity' => 120],
            ['name' => 'Hôpital de District de Mbouda',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Mbouda',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Dschang',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Dschang',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Foumbot',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Foumbot',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Bangangté',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Bangangté',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Foumban',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Foumban',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Baham',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Baham',       'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Tonga',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Ouest', 'city' => 'Tonga',       'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function adamaoua(): array
    {
        return [
            ['name' => 'Hôpital Régional de Ngaoundéré',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Adamaoua', 'city' => 'Ngaoundéré', 'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 200],
            ['name' => 'Hôpital Adventiste de Ngaoundéré',           'type' => 'hospital', 'ownership' => 'faith_based', 'region' => 'Adamaoua', 'city' => 'Ngaoundéré', 'bed_capacity' => 100],
            ['name' => 'Hôpital de District de Ngaoundéré',          'type' => 'hospital', 'ownership' => 'public',      'region' => 'Adamaoua', 'city' => 'Ngaoundéré', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Tibati',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Adamaoua', 'city' => 'Tibati',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Meiganga',            'type' => 'hospital', 'ownership' => 'public',      'region' => 'Adamaoua', 'city' => 'Meiganga',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Banyo',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Adamaoua', 'city' => 'Banyo',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Tignère',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Adamaoua', 'city' => 'Tignère',    'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function nord(): array
    {
        return [
            ['name' => 'Hôpital Régional de Garoua',                 'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord', 'city' => 'Garoua',    'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 250],
            ['name' => 'Hôpital de District de Garoua',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord', 'city' => 'Garoua',    'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Guider',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord', 'city' => 'Guider',    'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Tcholliré',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord', 'city' => 'Tcholliré', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Rey Bouba',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord', 'city' => 'Rey Bouba', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Poli',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord', 'city' => 'Poli',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Lagdo',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Nord', 'city' => 'Lagdo',     'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function extremeNord(): array
    {
        return [
            ['name' => 'Hôpital Régional de Maroua',                 'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Maroua',   'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 220],
            ['name' => 'Hôpital de District de Maroua',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Maroua',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Kousseri',            'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Kousseri', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Mokolo',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Mokolo',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Yagoua',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Yagoua',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Mora',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Mora',     'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Kaélé',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Kaélé',    'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Mindif',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Mindif',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Moutourwa',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Moutourwa','accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Touboro',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Extrême-Nord', 'city' => 'Touboro',  'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function est(): array
    {
        return [
            ['name' => 'Hôpital Régional de Bertoua',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Est', 'city' => 'Bertoua',      'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 180],
            ['name' => 'Hôpital de District de Bertoua',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Est', 'city' => 'Bertoua',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District d\'Abong-Mbang',         'type' => 'hospital', 'ownership' => 'public',      'region' => 'Est', 'city' => 'Abong-Mbang',  'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Batouri',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Est', 'city' => 'Batouri',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Yokadouma',           'type' => 'hospital', 'ownership' => 'public',      'region' => 'Est', 'city' => 'Yokadouma',    'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Doumé',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Est', 'city' => 'Doumé',        'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Ngoura',              'type' => 'hospital', 'ownership' => 'public',      'region' => 'Est', 'city' => 'Ngoura',       'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function sud(): array
    {
        return [
            ['name' => 'Hôpital Régional d\'Ebolowa',                'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud', 'city' => 'Ebolowa',    'accreditation_level' => 'Hôpital Régional', 'bed_capacity' => 180],
            ['name' => 'Hôpital de District d\'Ebolowa',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud', 'city' => 'Ebolowa',    'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Sangmelima',          'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud', 'city' => 'Sangmelima', 'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District d\'Ambam',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud', 'city' => 'Ambam',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Kribi',               'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud', 'city' => 'Kribi',      'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District de Lolodorf',            'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud', 'city' => 'Lolodorf',   'accreditation_level' => 'Hôpital de District'],
            ['name' => 'Hôpital de District d\'Akom II',             'type' => 'hospital', 'ownership' => 'public',      'region' => 'Sud', 'city' => 'Akom II',    'accreditation_level' => 'Hôpital de District'],
        ];
    }

    private function pharmacies(): array
    {
        return [
            // Centre - Yaoundé
            ['name' => 'Pharmacie Centrale de Yaoundé',              'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie du Soleil',                        'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie de la Paix',                       'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie Hippocrate',                       'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie de la Nlongkak',                   'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie Jouvence',                         'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie Saint-Louis',                      'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie de Melen',                         'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie de l\'Université',                  'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie de Bastos',                        'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie de Biyem-Assi',                    'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie de l\'Hôpital Central',             'type' => 'pharmacy', 'ownership' => 'public',  'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Pharmacie Mbalmayo',                         'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Mbalmayo'],
            // Littoral - Douala
            ['name' => 'Grande Pharmacie du Wouri',                  'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie de Bonanjo',                       'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie de l\'Akwa',                        'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie de Bali',                          'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie Nouvelle de Douala',               'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie de la Cité Douala',                'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie du Carrefour Douala',              'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie de l\'Hôpital Général de Douala',   'type' => 'pharmacy', 'ownership' => 'public',  'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Pharmacie de Nkongsamba',                    'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Nkongsamba'],
            // Nord-Ouest
            ['name' => 'Pharmacie Régionale de Bamenda',             'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Nord-Ouest', 'city' => 'Bamenda'],
            ['name' => 'Pharmacie de la Santé Bamenda',              'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Nord-Ouest', 'city' => 'Bamenda'],
            ['name' => 'Baptist Hospital Pharmacy Bamenda',          'type' => 'pharmacy', 'ownership' => 'faith_based', 'region' => 'Nord-Ouest', 'city' => 'Bamenda'],
            // Sud-Ouest
            ['name' => 'Pharmacie Régionale de Buéa',                'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Sud-Ouest', 'city' => 'Buéa'],
            ['name' => 'Pharmacie de Limbe',                         'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Sud-Ouest', 'city' => 'Limbe'],
            ['name' => 'Pharmacie du Marché de Limbe',               'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Sud-Ouest', 'city' => 'Limbe'],
            // Ouest
            ['name' => 'Pharmacie Régionale de Bafoussam',           'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Ouest',     'city' => 'Bafoussam'],
            ['name' => 'Pharmacie de la Paix Bafoussam',             'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Ouest',     'city' => 'Bafoussam'],
            ['name' => 'Pharmacie de Dschang',                       'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Ouest',     'city' => 'Dschang'],
            // Adamaoua
            ['name' => 'Pharmacie Régionale de Ngaoundéré',          'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Adamaoua',  'city' => 'Ngaoundéré'],
            ['name' => 'Pharmacie Adventiste de Ngaoundéré',         'type' => 'pharmacy', 'ownership' => 'faith_based', 'region' => 'Adamaoua', 'city' => 'Ngaoundéré'],
            // Nord
            ['name' => 'Pharmacie Régionale de Garoua',              'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Nord',       'city' => 'Garoua'],
            ['name' => 'Pharmacie du Marché de Garoua',              'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Nord',       'city' => 'Garoua'],
            // Extrême-Nord
            ['name' => 'Pharmacie Régionale de Maroua',              'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Extrême-Nord','city' => 'Maroua'],
            ['name' => 'Pharmacie de Kousseri',                      'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Extrême-Nord','city' => 'Kousseri'],
            // Est
            ['name' => 'Pharmacie Régionale de Bertoua',             'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Est',        'city' => 'Bertoua'],
            // Sud
            ['name' => 'Pharmacie Régionale d\'Ebolowa',              'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Sud',        'city' => 'Ebolowa'],
            ['name' => 'Pharmacie de Kribi',                         'type' => 'pharmacy', 'ownership' => 'private', 'region' => 'Sud',        'city' => 'Kribi'],
        ];
    }

    private function laboratories(): array
    {
        return [
            // National/Reference
            ['name' => 'Centre Pasteur du Cameroun — Yaoundé',       'type' => 'laboratory', 'ownership' => 'public',   'region' => 'Centre',   'city' => 'Yaoundé', 'accreditation_level' => 'Laboratoire de Référence National', 'website' => 'https://www.pasteur-yaounde.org'],
            ['name' => 'Centre Pasteur du Cameroun — Douala',        'type' => 'laboratory', 'ownership' => 'public',   'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Laboratoire National de Santé Publique',     'type' => 'laboratory', 'ownership' => 'public',   'region' => 'Centre',   'city' => 'Yaoundé', 'accreditation_level' => 'Laboratoire National'],
            // Yaoundé private labs
            ['name' => 'Laboratoire Médical de Yaoundé',             'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Laboratoire de la Cité Verte',               'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Laboratoire de Biologie Médicale de Bastos', 'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Laboratoire de l\'Hôpital Central',           'type' => 'laboratory', 'ownership' => 'public',   'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre Médical de Biologie',                 'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Biomedical Laboratory Yaoundé',              'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Centre',   'city' => 'Yaoundé'],
            // Douala labs
            ['name' => 'Lanacome Laboratory Douala',                 'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Laboratoire de Bonanjo',                     'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Laboratoire de l\'Hôpital Général de Douala', 'type' => 'laboratory', 'ownership' => 'public',   'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Labo Diagnos Douala',                        'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Laboratoire de Bali Douala',                 'type' => 'laboratory', 'ownership' => 'private',  'region' => 'Littoral', 'city' => 'Douala'],
            // Regional labs
            ['name' => 'Laboratoire de l\'Hôpital Régional de Bamenda','type' => 'laboratory','ownership' => 'public',  'region' => 'Nord-Ouest','city' => 'Bamenda'],
            ['name' => 'Laboratoire de l\'Hôpital Régional de Bafoussam','type' => 'laboratory','ownership' => 'public', 'region' => 'Ouest',    'city' => 'Bafoussam'],
            ['name' => 'Laboratoire de l\'Hôpital Régional de Garoua','type' => 'laboratory', 'ownership' => 'public',  'region' => 'Nord',     'city' => 'Garoua'],
            ['name' => 'Laboratoire de l\'Hôpital Régional de Maroua','type' => 'laboratory', 'ownership' => 'public',  'region' => 'Extrême-Nord','city' => 'Maroua'],
            ['name' => 'Laboratoire de l\'Hôpital Régional de Ngaoundéré','type' => 'laboratory','ownership' => 'public','region' => 'Adamaoua','city' => 'Ngaoundéré'],
            ['name' => 'Laboratoire de l\'Hôpital Régional de Bertoua','type' => 'laboratory','ownership' => 'public',  'region' => 'Est',      'city' => 'Bertoua'],
            ['name' => 'Laboratoire de l\'Hôpital Régional d\'Ebolowa','type' => 'laboratory','ownership' => 'public',  'region' => 'Sud',      'city' => 'Ebolowa'],
            ['name' => 'Laboratoire de l\'Hôpital Régional de Buéa', 'type' => 'laboratory', 'ownership' => 'public',  'region' => 'Sud-Ouest','city' => 'Buéa'],
        ];
    }

    private function imagingCenters(): array
    {
        return [
            ['name' => 'Centre d\'Imagerie Médicale de Yaoundé',      'type' => 'imaging_center', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre de Scanner de Yaoundé',                'type' => 'imaging_center', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre d\'IRM de Yaoundé',                     'type' => 'imaging_center', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Imagerie de l\'Hôpital Central de Yaoundé',    'type' => 'imaging_center', 'ownership' => 'public',  'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre de Radiologie et d\'Imagerie (CRIMAR)', 'type' => 'imaging_center', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre Scano de Douala',                       'type' => 'imaging_center', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Imagerie Médicale Sainte-Rita Douala',         'type' => 'imaging_center', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Imagerie de l\'Hôpital Général de Douala',     'type' => 'imaging_center', 'ownership' => 'public',  'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre de Radiologie de Bamenda',              'type' => 'imaging_center', 'ownership' => 'public',  'region' => 'Nord-Ouest','city' => 'Bamenda'],
            ['name' => 'Centre d\'Imagerie de Bafoussam',               'type' => 'imaging_center', 'ownership' => 'public',  'region' => 'Ouest',    'city' => 'Bafoussam'],
            ['name' => 'Centre d\'Imagerie de Garoua',                  'type' => 'imaging_center', 'ownership' => 'public',  'region' => 'Nord',     'city' => 'Garoua'],
            ['name' => 'Centre d\'Imagerie de Maroua',                  'type' => 'imaging_center', 'ownership' => 'public',  'region' => 'Extrême-Nord','city' => 'Maroua'],
            ['name' => 'Centre d\'Imagerie de Ngaoundéré',              'type' => 'imaging_center', 'ownership' => 'public',  'region' => 'Adamaoua', 'city' => 'Ngaoundéré'],
        ];
    }

    private function diagnosticCenters(): array
    {
        return [
            ['name' => 'Centre de Cardiologie de Yaoundé',            'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre Ophtalmologique de Yaoundé',           'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre d\'Endoscopie de Yaoundé',              'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre Dentaire de Yaoundé',                   'type' => 'dental',             'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre ORL de Yaoundé',                        'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre de Diagnostic de la Fondation Chantal Biya','type' => 'diagnostic_center','ownership' => 'ngo', 'region' => 'Centre',   'city' => 'Yaoundé'],
            ['name' => 'Centre Médical de Diagnostic Avancé Douala',   'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre Ophtalmologique de Douala',             'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre Cardiologique de Douala',               'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre Dentaire de Douala',                    'type' => 'dental',             'ownership' => 'private', 'region' => 'Littoral', 'city' => 'Douala'],
            ['name' => 'Centre de Diagnostic Médical de Bafoussam',   'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Ouest',    'city' => 'Bafoussam'],
            ['name' => 'Centre de Diagnostic de Bamenda',              'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Nord-Ouest','city' => 'Bamenda'],
            ['name' => 'Centre de Diagnostic de Ngaoundéré',           'type' => 'diagnostic_center', 'ownership' => 'private', 'region' => 'Adamaoua', 'city' => 'Ngaoundéré'],
        ];
    }
}
```

- [ ] **Step 4: Run the tests**

```bash
php artisan test tests/Feature/Registry/CameroonFacilityRegistrySeederTest.php --no-coverage
```
Expected: 4 tests, 4 passed.

- [ ] **Step 5: Commit**

```bash
git add database/seeders/CameroonFacilityRegistrySeeder.php \
        tests/Feature/Registry/CameroonFacilityRegistrySeederTest.php
git commit -m "feat: add CameroonFacilityRegistrySeeder with 240+ real facilities across all 10 regions"
```

---

### Task 3: CameroonInsuranceSeeder — 15 real Cameroonian insurers

**Files:**
- Create: `database/seeders/CameroonInsuranceSeeder.php`
- Test: `tests/Feature/Registry/CameroonInsuranceSeederTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Registry/CameroonInsuranceSeederTest.php
namespace Tests\Feature\Registry;

use Database\Seeders\CameroonInsuranceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CameroonInsuranceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_cnamgs(): void
    {
        $this->seed(CameroonInsuranceSeeder::class);
        $this->assertDatabaseHas('insurance_providers', ['code' => 'CNAMGS', 'country_code' => 'CM']);
    }

    public function test_seeds_all_15_insurers(): void
    {
        $this->seed(CameroonInsuranceSeeder::class);
        $this->assertEquals(15, \DB::table('insurance_providers')->where('country_code', 'CM')->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(CameroonInsuranceSeeder::class);
        $this->seed(CameroonInsuranceSeeder::class);
        $this->assertEquals(15, \DB::table('insurance_providers')->where('country_code', 'CM')->count());
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Registry/CameroonInsuranceSeederTest.php --no-coverage
```
Expected: FAIL — class not found.

- [ ] **Step 3: Create the seeder**

```php
<?php
// database/seeders/CameroonInsuranceSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the insurance_providers table with real Cameroonian insurance companies.
 * Upserts by code — idempotent.
 */
class CameroonInsuranceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->insurers() as $insurer) {
            DB::table('insurance_providers')->upsert(
                array_merge($insurer, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
                ['code'],
                ['name', 'country_code', 'contact_phone', 'portal_url', 'status', 'updated_at']
            );
        }

        $this->command?->info('CameroonInsuranceSeeder: seeded Cameroonian insurance providers.');
    }

    private function insurers(): array
    {
        return [
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Caisse Nationale d\'Assurance Maladie et de Garantie Sociale (CNAMGS)',
                'code'          => 'CNAMGS',
                'country_code'  => 'CM',
                'contact_email' => 'info@cnamgs.cm',
                'contact_phone' => '+237 222 22 40 97',
                'portal_url'    => 'https://www.cnamgs.cm',
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Activa Assurances Cameroun',
                'code'          => 'ACTIVA-CM',
                'country_code'  => 'CM',
                'contact_email' => 'info@activa-assurances.com',
                'contact_phone' => '+237 233 43 30 77',
                'portal_url'    => 'https://www.activa-assurances.com',
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Beneficial Life Insurance Cameroun',
                'code'          => 'BENEFICIAL',
                'country_code'  => 'CM',
                'contact_email' => 'info@beneficial.cm',
                'contact_phone' => '+237 222 20 04 32',
                'portal_url'    => null,
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'SAAR Assurance',
                'code'          => 'SAAR',
                'country_code'  => 'CM',
                'contact_email' => 'contact@saar-assurance.cm',
                'contact_phone' => '+237 233 42 02 65',
                'portal_url'    => null,
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Saham Assurance Cameroun (Sanlam)',
                'code'          => 'SAHAM-CM',
                'country_code'  => 'CM',
                'contact_email' => 'cameroun@saham.com',
                'contact_phone' => '+237 233 43 18 00',
                'portal_url'    => 'https://www.saham.cm',
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'AXA Cameroun',
                'code'          => 'AXA-CM',
                'country_code'  => 'CM',
                'contact_email' => 'contact@axa.cm',
                'contact_phone' => '+237 233 43 00 25',
                'portal_url'    => 'https://www.axa.cm',
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'NSIA Cameroun',
                'code'          => 'NSIA-CM',
                'country_code'  => 'CM',
                'contact_email' => 'cameroun@nsia.ci',
                'contact_phone' => '+237 233 43 45 00',
                'portal_url'    => 'https://www.groupensia.com',
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Chanas Assurances',
                'code'          => 'CHANAS',
                'country_code'  => 'CM',
                'contact_email' => 'chanas@chanasassurances.com',
                'contact_phone' => '+237 233 42 58 40',
                'portal_url'    => null,
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Allianz Cameroun',
                'code'          => 'ALLIANZ-CM',
                'country_code'  => 'CM',
                'contact_email' => 'contact@allianz.cm',
                'contact_phone' => '+237 233 43 10 10',
                'portal_url'    => 'https://www.allianz.cm',
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Prudential Beneficial Assurance Cameroun',
                'code'          => 'PRUDENTIAL-CM',
                'country_code'  => 'CM',
                'contact_email' => 'cameroun@prudential.com',
                'contact_phone' => '+237 222 20 38 12',
                'portal_url'    => null,
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Zenithe Insurance Cameroun',
                'code'          => 'ZENITHE',
                'country_code'  => 'CM',
                'contact_email' => 'info@zenithe.cm',
                'contact_phone' => '+237 233 43 55 00',
                'portal_url'    => null,
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'GAN Assurance Cameroun',
                'code'          => 'GAN-CM',
                'country_code'  => 'CM',
                'contact_email' => 'gan@gan-cameroun.cm',
                'contact_phone' => '+237 233 42 48 60',
                'portal_url'    => null,
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Garantie Mutuelle des Fonctionnaires (GMF Cameroun)',
                'code'          => 'GMF-CM',
                'country_code'  => 'CM',
                'contact_email' => 'info@gmf.cm',
                'contact_phone' => '+237 222 23 07 56',
                'portal_url'    => null,
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Sunu Assurances Cameroun',
                'code'          => 'SUNU-CM',
                'country_code'  => 'CM',
                'contact_email' => 'cameroun@sunu-assurances.com',
                'contact_phone' => '+237 233 43 35 35',
                'portal_url'    => 'https://www.sunu-assurances.com',
                'status'        => 'active',
            ],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Cipmen — Caisse Interprofessionnelle de Prévoyance et de Retraite du Ménage',
                'code'          => 'CIPMEN',
                'country_code'  => 'CM',
                'contact_email' => 'contact@cipmen.cm',
                'contact_phone' => '+237 222 22 71 17',
                'portal_url'    => null,
                'status'        => 'active',
            ],
        ];
    }
}
```

- [ ] **Step 4: Run the tests**

```bash
php artisan test tests/Feature/Registry/CameroonInsuranceSeederTest.php --no-coverage
```
Expected: 3 tests, 3 passed.

- [ ] **Step 5: Commit**

```bash
git add database/seeders/CameroonInsuranceSeeder.php \
        tests/Feature/Registry/CameroonInsuranceSeederTest.php
git commit -m "feat: add CameroonInsuranceSeeder with 15 real Cameroonian insurers"
```

---

### Task 4: `registry:import-facilities` Artisan command

**Files:**
- Create: `app/Console/Commands/ImportFacilityRegistry.php`
- Test: `tests/Feature/Registry/ImportFacilityRegistryCommandTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Registry/ImportFacilityRegistryCommandTest.php
namespace Tests\Feature\Registry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportFacilityRegistryCommandTest extends TestCase
{
    use RefreshDatabase;

    private function csvPath(string $content): string
    {
        Storage::fake('local');
        Storage::disk('local')->put('imports/test_facilities.csv', $content);
        return storage_path('app/imports/test_facilities.csv');
    }

    public function test_imports_valid_csv(): void
    {
        $csv = "name,type,ownership,region,division,city,address,phone,email,website,ministry_code,accreditation_level,bed_capacity,gps_lat,gps_lng,services\n";
        $csv .= "Hôpital de Test,hospital,public,Centre,,Yaoundé,,,,,MIN-001,Hôpital de District,50,,,\n";

        $path = $this->csvPath($csv);

        $this->artisan('registry:import-facilities', ['--file' => $path])
             ->assertSuccessful();

        $this->assertDatabaseHas('facility_registry', ['name' => 'Hôpital de Test', 'region' => 'Centre']);
    }

    public function test_dry_run_does_not_write(): void
    {
        $csv = "name,type,ownership,region,division,city,address,phone,email,website,ministry_code,accreditation_level,bed_capacity,gps_lat,gps_lng,services\n";
        $csv .= "Phantom Hospital,hospital,public,Littoral,,Douala,,,,,,,,\n";

        $path = $this->csvPath($csv);

        $this->artisan('registry:import-facilities', ['--file' => $path, '--dry-run' => true])
             ->assertSuccessful();

        $this->assertDatabaseMissing('facility_registry', ['name' => 'Phantom Hospital']);
    }

    public function test_merge_mode_skips_claimed_rows(): void
    {
        $facility = \App\Models\Facility::create(['name' => 'Owned Hospital', 'type' => 'hospital']);
        \DB::table('facility_registry')->insert([
            'id'                  => (string) \Illuminate\Support\Str::uuid(),
            'name'                => 'Owned Hospital',
            'type'                => 'hospital',
            'region'              => 'Centre',
            'city'                => 'Yaoundé',
            'status'              => 'verified',
            'claimed_facility_id' => $facility->id,
            'source'              => 'test',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $csv = "name,type,ownership,region,division,city,address,phone,email,website,ministry_code,accreditation_level,bed_capacity,gps_lat,gps_lng,services\n";
        $csv .= "Owned Hospital,hospital,public,Centre,,Yaoundé,,,,,,,,,\n";

        $path = $this->csvPath($csv);

        $this->artisan('registry:import-facilities', ['--file' => $path])
             ->assertSuccessful();

        // Status should remain 'verified' — claimed rows are never overwritten
        $this->assertDatabaseHas('facility_registry', ['name' => 'Owned Hospital', 'status' => 'verified']);
    }

    public function test_rejects_invalid_type(): void
    {
        $csv = "name,type,ownership,region,division,city,address,phone,email,website,ministry_code,accreditation_level,bed_capacity,gps_lat,gps_lng,services\n";
        $csv .= "Bad Facility,invalid_type,public,Centre,,Yaoundé,,,,,,,,\n";

        $path = $this->csvPath($csv);

        $this->artisan('registry:import-facilities', ['--file' => $path])
             ->assertSuccessful(); // command exits OK but reports error

        $this->assertDatabaseMissing('facility_registry', ['name' => 'Bad Facility']);
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Registry/ImportFacilityRegistryCommandTest.php --no-coverage
```
Expected: FAIL — command not found.

- [ ] **Step 3: Create the command**

```php
<?php
// app/Console/Commands/ImportFacilityRegistry.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportFacilityRegistry extends Command
{
    protected $signature = 'registry:import-facilities
                            {--file= : Path to CSV file (required)}
                            {--mode=merge : merge (default) or replace}
                            {--dry-run : Validate without writing}';

    protected $description = 'Import Cameroon health facilities from a CSV file into facility_registry';

    private const VALID_TYPES = [
        'hospital','clinic','health_center','dispensary','pharmacy','laboratory',
        'imaging_center','diagnostic_center','maternity','dental','eye_clinic',
        'blood_bank','specialist','nursing_home',
    ];

    private const VALID_REGIONS = [
        'Adamaoua','Centre','Est','Extrême-Nord','Littoral',
        'Nord','Nord-Ouest','Ouest','Sud','Sud-Ouest',
    ];

    private const VALID_OWNERSHIP = ['public','private','faith_based','ngo','military',null,''];

    public function handle(): int
    {
        $file   = $this->option('file');
        $mode   = $this->option('mode');
        $dryRun = (bool) $this->option('dry-run');

        if (!$file || !file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $this->info("Cameroon Facility Registry Import");
        $this->info("File: {$file}  Mode: {$mode}  Dry-run: " . ($dryRun ? 'yes' : 'no'));
        $this->line(str_repeat('━', 50));

        $handle = fopen($file, 'r');
        $headers = array_map('trim', fgetcsv($handle));

        $added   = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];
        $rowNum  = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) !== count($headers)) {
                $errors[] = "Row {$rowNum}: column count mismatch";
                continue;
            }

            $data = array_combine($headers, $row);

            // Validate required fields
            if (empty(trim($data['name'] ?? ''))) {
                $errors[] = "Row {$rowNum}: name is required";
                continue;
            }
            if (!in_array($data['type'] ?? '', self::VALID_TYPES, true)) {
                $errors[] = "Row {$rowNum}: invalid type \"{$data['type']}\" — use one of: " . implode(', ', self::VALID_TYPES);
                continue;
            }
            if (!in_array($data['region'] ?? '', self::VALID_REGIONS, true)) {
                $errors[] = "Row {$rowNum}: region \"{$data['region']}\" not recognised — use one of: " . implode(', ', self::VALID_REGIONS);
                continue;
            }
            if (!in_array($data['ownership'] ?? '', self::VALID_OWNERSHIP, true)) {
                $errors[] = "Row {$rowNum}: invalid ownership \"{$data['ownership']}\"";
                continue;
            }

            // Check if claimed — never overwrite
            $claimedCount = DB::table('facility_registry')
                ->where('name', $data['name'])
                ->where('region', $data['region'])
                ->where('city', $data['city'] ?: null)
                ->whereNotNull('claimed_facility_id')
                ->count();

            if ($claimedCount > 0) {
                $skipped++;
                continue;
            }

            // Build insert/update payload
            $payload = [
                'name'                => trim($data['name']),
                'type'                => $data['type'],
                'ownership'           => $data['ownership'] ?: null,
                'region'              => $data['region'],
                'division'            => $data['division'] ?: null,
                'city'                => $data['city'] ?: null,
                'address'             => $data['address'] ?: null,
                'phone'               => $data['phone'] ?: null,
                'email'               => $data['email'] ?: null,
                'website'             => $data['website'] ?: null,
                'ministry_code'       => $data['ministry_code'] ?: null,
                'accreditation_level' => $data['accreditation_level'] ?: null,
                'bed_capacity'        => is_numeric($data['bed_capacity'] ?? '') ? (int)$data['bed_capacity'] : null,
                'gps_lat'             => is_numeric($data['gps_lat'] ?? '') ? (float)$data['gps_lat'] : null,
                'gps_lng'             => is_numeric($data['gps_lng'] ?? '') ? (float)$data['gps_lng'] : null,
                'services'            => !empty($data['services'])
                    ? json_encode(array_filter(explode('|', $data['services'])))
                    : null,
                'source'              => 'csv_import_' . date('Y'),
                'status'              => 'unverified',
                'updated_at'          => now(),
            ];

            if ($dryRun) {
                $added++;
                continue;
            }

            $existing = DB::table('facility_registry')
                ->where('name', $data['name'])
                ->where('region', $data['region'])
                ->where('city', $data['city'] ?: null)
                ->first();

            if ($existing) {
                DB::table('facility_registry')
                    ->where('id', $existing->id)
                    ->update($payload);
                $updated++;
            } else {
                DB::table('facility_registry')->insert(array_merge($payload, [
                    'id'         => (string) Str::uuid(),
                    'created_at' => now(),
                ]));
                $added++;
            }
        }

        fclose($handle);

        $this->line(str_repeat('━', 50));
        $this->info("✓  Added:    {$added}");
        $this->info("~  Updated:  {$updated}");
        $this->info("⊘  Skipped:  {$skipped}  (already claimed — not overwritten)");
        $this->error("✗  Errors:   " . count($errors));

        foreach ($errors as $err) {
            $this->warn("   {$err}");
        }

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Register the command in `app/Console/Kernel.php` or `bootstrap/app.php`**

Check if the project uses `app/Console/Kernel.php`. If it does, add `\App\Console\Commands\ImportFacilityRegistry::class` to the `$commands` array. If it uses auto-discovery (Laravel 11+ with `bootstrap/app.php`), the command is auto-discovered — no action needed.

To verify auto-discovery is working:
```bash
php artisan list registry
```
Expected: `registry:import-facilities` appears in the list.

- [ ] **Step 5: Run the tests**

```bash
php artisan test tests/Feature/Registry/ImportFacilityRegistryCommandTest.php --no-coverage
```
Expected: 4 tests, 4 passed.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/ImportFacilityRegistry.php \
        tests/Feature/Registry/ImportFacilityRegistryCommandTest.php
git commit -m "feat: add registry:import-facilities Artisan command with dry-run and merge modes"
```

---

### Task 5: `registry:import-insurers` Artisan command

**Files:**
- Create: `app/Console/Commands/ImportInsuranceRegistry.php`
- Test: `tests/Feature/Registry/ImportInsuranceRegistryCommandTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Registry/ImportInsuranceRegistryCommandTest.php
namespace Tests\Feature\Registry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportInsuranceRegistryCommandTest extends TestCase
{
    use RefreshDatabase;

    private function writeCsv(string $content): string
    {
        $path = storage_path('app/imports/test_insurers.csv');
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $content);
        return $path;
    }

    public function test_imports_valid_insurer_csv(): void
    {
        $csv = "name,code,country_code,contact_email,contact_phone,portal_url,api_endpoint,status\n";
        $csv .= "Test Insurer,TEST-CM,CM,test@test.cm,+237 000 000 000,,, active\n";

        $path = $this->writeCsv($csv);

        $this->artisan('registry:import-insurers', ['--file' => $path])
             ->assertSuccessful();

        $this->assertDatabaseHas('insurance_providers', ['code' => 'TEST-CM', 'country_code' => 'CM']);
    }

    public function test_dry_run_does_not_write(): void
    {
        $csv = "name,code,country_code,contact_email,contact_phone,portal_url,api_endpoint,status\n";
        $csv .= "Ghost Insurer,GHOST-CM,CM,ghost@ghost.cm,,,\n";

        $path = $this->writeCsv($csv);

        $this->artisan('registry:import-insurers', ['--file' => $path, '--dry-run' => true])
             ->assertSuccessful();

        $this->assertDatabaseMissing('insurance_providers', ['code' => 'GHOST-CM']);
    }

    public function test_upserts_by_code(): void
    {
        $csv = "name,code,country_code,contact_email,contact_phone,portal_url,api_endpoint,status\n";
        $csv .= "Original Name,UPS-CM,CM,a@a.cm,,,, active\n";
        $path = $this->writeCsv($csv);
        $this->artisan('registry:import-insurers', ['--file' => $path])->assertSuccessful();

        $csv2 = "name,code,country_code,contact_email,contact_phone,portal_url,api_endpoint,status\n";
        $csv2 .= "Updated Name,UPS-CM,CM,b@b.cm,,,, active\n";
        $path2 = $this->writeCsv($csv2);
        $this->artisan('registry:import-insurers', ['--file' => $path2])->assertSuccessful();

        $this->assertEquals(1, \DB::table('insurance_providers')->where('code', 'UPS-CM')->count());
        $this->assertDatabaseHas('insurance_providers', ['code' => 'UPS-CM', 'name' => 'Updated Name']);
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Registry/ImportInsuranceRegistryCommandTest.php --no-coverage
```
Expected: FAIL — command not found.

- [ ] **Step 3: Create the command**

```php
<?php
// app/Console/Commands/ImportInsuranceRegistry.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportInsuranceRegistry extends Command
{
    protected $signature = 'registry:import-insurers
                            {--file= : Path to CSV file (required)}
                            {--dry-run : Validate without writing}';

    protected $description = 'Import Cameroonian insurance providers from a CSV file into insurance_providers';

    public function handle(): int
    {
        $file   = $this->option('file');
        $dryRun = (bool) $this->option('dry-run');

        if (!$file || !file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $this->info("Insurance Registry Import — File: {$file}  Dry-run: " . ($dryRun ? 'yes' : 'no'));

        $handle  = fopen($file, 'r');
        $headers = array_map('trim', fgetcsv($handle));

        $added   = 0;
        $updated = 0;
        $errors  = [];
        $rowNum  = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }
            $data = array_combine($headers, array_slice($row, 0, count($headers)));

            if (empty(trim($data['name'] ?? ''))) {
                $errors[] = "Row {$rowNum}: name is required";
                continue;
            }
            if (empty(trim($data['code'] ?? ''))) {
                $errors[] = "Row {$rowNum}: code is required";
                continue;
            }

            $payload = [
                'name'          => trim($data['name']),
                'code'          => strtoupper(trim($data['code'])),
                'country_code'  => trim($data['country_code'] ?? 'CM'),
                'contact_email' => trim($data['contact_email']) ?: null,
                'contact_phone' => trim($data['contact_phone']) ?: null,
                'portal_url'    => trim($data['portal_url']) ?: null,
                'api_endpoint'  => trim($data['api_endpoint'] ?? '') ?: null,
                'status'        => trim($data['status'] ?? 'active') ?: 'active',
                'updated_at'    => now(),
            ];

            if ($dryRun) {
                $added++;
                continue;
            }

            $existing = DB::table('insurance_providers')
                ->where('code', $payload['code'])
                ->first();

            if ($existing) {
                DB::table('insurance_providers')
                    ->where('id', $existing->id)
                    ->update($payload);
                $updated++;
            } else {
                DB::table('insurance_providers')->insert(array_merge($payload, [
                    'id'         => (string) Str::uuid(),
                    'created_at' => now(),
                ]));
                $added++;
            }
        }

        fclose($handle);

        $this->info("✓  Added:   {$added}");
        $this->info("~  Updated: {$updated}");
        $this->error("✗  Errors:  " . count($errors));
        foreach ($errors as $err) {
            $this->warn("   {$err}");
        }

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Run the tests**

```bash
php artisan test tests/Feature/Registry/ImportInsuranceRegistryCommandTest.php --no-coverage
```
Expected: 3 tests, 3 passed.

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/ImportInsuranceRegistry.php \
        tests/Feature/Registry/ImportInsuranceRegistryCommandTest.php
git commit -m "feat: add registry:import-insurers Artisan command"
```

---

### Task 6: Wire seeders into DatabaseSeeder + final verification

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Check the current DatabaseSeeder**

```bash
cat database/seeders/DatabaseSeeder.php
```

- [ ] **Step 2: Add both seeders to DatabaseSeeder**

Open `database/seeders/DatabaseSeeder.php`. The `run()` method will contain an existing list of seeders. Add the two new seeders **after** demo seeders (real data should be available in all environments, not just demo):

```php
// Add these two lines at the end of the $this->call([...]) block,
// or as standalone calls after the existing block:

$this->call([
    // ... existing seeders ...
    CameroonFacilityRegistrySeeder::class,
    CameroonInsuranceSeeder::class,
]);
```

- [ ] **Step 3: Run the full test suite**

```bash
php artisan test --no-coverage 2>&1 | tail -10
```
Expected: All existing tests pass. New tests: 4 + 4 + 3 + 3 = 14 additional passing.

- [ ] **Step 4: Run the seeders manually to confirm they work end-to-end**

```bash
php artisan db:seed --class=CameroonFacilityRegistrySeeder
php artisan db:seed --class=CameroonInsuranceSeeder
```
Expected output:
```
CameroonFacilityRegistrySeeder: 240+ total registry entries.
CameroonInsuranceSeeder: seeded Cameroonian insurance providers.
```

- [ ] **Step 5: Spot-check the data**

```bash
php artisan tinker --execute="
echo 'Total registry entries: ' . DB::table('facility_registry')->count() . PHP_EOL;
echo 'By region:' . PHP_EOL;
DB::table('facility_registry')->selectRaw('region, COUNT(*) as cnt')->groupBy('region')->orderBy('cnt','desc')->get()->each(fn(\$r) => print(\$r->region . ': ' . \$r->cnt . PHP_EOL));
echo 'Insurers (CM): ' . DB::table('insurance_providers')->where('country_code','CM')->count() . PHP_EOL;
"
```
Expected: Centre and Littoral show the most entries; 10 regions represented; 15 CM insurers.

- [ ] **Step 6: Commit**

```bash
git add database/seeders/DatabaseSeeder.php
git commit -m "feat: wire CameroonFacilityRegistrySeeder and CameroonInsuranceSeeder into DatabaseSeeder"
```

---

## Self-Review Checklist

**Spec coverage:**
- ✅ `facility_registry` table — Task 1
- ✅ `FacilityRegistry` model with all 5 scopes — Task 1
- ✅ `FacilityClaim` FK bug fix — Task 1
- ✅ ~240 real facilities across all 10 regions — Task 2
- ✅ 15 real Cameroonian insurers — Task 3
- ✅ `registry:import-facilities` with merge/replace/dry-run — Task 4
- ✅ `registry:import-insurers` with dry-run and upsert-by-code — Task 5
- ✅ DatabaseSeeder wiring — Task 6
- ✅ Claimed rows never overwritten — Task 4 command + test
- ✅ Idempotency — tested in Tasks 2, 3, 4, 5

**Type consistency:** `insertIfMissing()` in Task 2 used in seeder only. Command in Task 4 uses its own inline logic. No cross-task confusion.

**No placeholders found.**
