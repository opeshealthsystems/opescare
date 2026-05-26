<?php

namespace Tests\Feature\CareMap;

use App\Models\CareFacility;
use App\Models\Facility;
use App\Models\FacilityClaim;
use App\Models\FacilityRegistry;
use App\Models\User;
use App\Modules\CareMap\Services\FacilityClaimService;
use Database\Seeders\CameroonFacilityRegistrySeeder;
use Database\Seeders\CareMapRegistryStubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests for the registry → CareMap integration pipeline:
 *
 *   1. GPS coordinates present on major facilities after seeding.
 *   2. CareMapRegistryStubSeeder creates care_facilities stubs from GPS-bearing entries.
 *   3. Registry claim approval stamps registry entry + auto-creates care_facilities listing.
 *   4. Original CareMap claim approval (no registry_entry_id) updates care_facilities.partner_id.
 *   5. Duplicate pending registry claims are blocked.
 *   6. Claiming an already-claimed registry entry is blocked.
 *   7. Stub seeder is idempotent.
 *   8. Approval activates existing stub rather than creating a duplicate.
 */
class CareMapRegistryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private FacilityClaimService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FacilityClaimService();
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    // ── Test 1: GPS coordinates present on major seeded facilities ───────────

    public function test_major_facilities_have_gps_after_seeder(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);

        $majorHospitals = [
            ['name' => 'Hôpital Central de Yaoundé',    'region' => 'Centre'],
            ['name' => 'Hôpital Général de Douala',     'region' => 'Littoral'],
            ['name' => 'Hôpital Régional de Bamenda',   'region' => 'Nord-Ouest'],
            ['name' => 'Hôpital Régional de Bafoussam', 'region' => 'Ouest'],
            ['name' => 'Hôpital Régional de Ngaoundéré','region' => 'Adamaoua'],
            ['name' => 'Hôpital Régional de Garoua',    'region' => 'Nord'],
            ['name' => 'Hôpital Régional de Maroua',    'region' => 'Extrême-Nord'],
            ['name' => 'Hôpital Régional de Bertoua',   'region' => 'Est'],
            ['name' => "Hôpital Régional d'Ebolowa",    'region' => 'Sud'],
            ['name' => 'Hôpital Régional de Buéa',      'region' => 'Sud-Ouest'],
        ];

        foreach ($majorHospitals as $h) {
            $entry = FacilityRegistry::where('name', $h['name'])->where('region', $h['region'])->first();
            $this->assertNotNull($entry, "Missing registry entry: {$h['name']}");
            $this->assertNotNull($entry->gps_lat, "{$h['name']} missing gps_lat");
            $this->assertNotNull($entry->gps_lng, "{$h['name']} missing gps_lng");
        }

        $pasteur = FacilityRegistry::where('name', 'Centre Pasteur du Cameroun — Yaoundé')->first();
        $this->assertNotNull($pasteur);
        $this->assertNotNull($pasteur->gps_lat);
    }

    // ── Test 2: Stub seeder creates care_facilities from GPS entries ─────────

    public function test_stub_seeder_creates_care_facilities_from_gps_entries(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);

        $gpsBearing = FacilityRegistry::whereNotNull('gps_lat')->count();
        $this->assertGreaterThan(0, $gpsBearing, 'Seeder produced no GPS-bearing entries');

        $this->seed(CareMapRegistryStubSeeder::class);

        $cmStubs = CareFacility::where('country_code', 'CM')->count();
        $this->assertGreaterThanOrEqual($gpsBearing, $cmStubs,
            'Stub seeder should create at least as many stubs as GPS-bearing registry entries');
    }

    public function test_stub_seeder_sets_correct_defaults(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);
        $this->seed(CareMapRegistryStubSeeder::class);

        $stubs = CareFacility::where('country_code', 'CM')->get();
        $this->assertNotEmpty($stubs, 'No CM stubs were created');

        foreach ($stubs as $stub) {
            $this->assertEquals('active',     $stub->listing_status,      "{$stub->facility_name} listing_status wrong");
            $this->assertEquals('unverified', $stub->verification_status, "{$stub->facility_name} verification_status wrong");
            $this->assertNotNull($stub->latitude,  "{$stub->facility_name} missing latitude");
            $this->assertNotNull($stub->longitude, "{$stub->facility_name} missing longitude");
        }
    }

    // ── Test 3: Stub seeder is idempotent ────────────────────────────────────

    public function test_stub_seeder_is_idempotent(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);
        $this->seed(CareMapRegistryStubSeeder::class);

        $countAfterFirst = CareFacility::where('country_code', 'CM')->count();

        $this->seed(CareMapRegistryStubSeeder::class);
        $countAfterSecond = CareFacility::where('country_code', 'CM')->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond,
            'Running stub seeder twice must not create duplicate entries');
    }

    // ── Test 4: Registry claim approval stamps registry + creates listing ────

    public function test_approve_registry_claim_stamps_registry_and_creates_care_facility(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);

        $registryEntry = FacilityRegistry::whereNotNull('gps_lat')->first();
        $this->assertNotNull($registryEntry);

        $facility  = Facility::create(['name' => 'My Clinic', 'type' => 'clinic']);
        $claimant  = $this->makeUser();
        $admin     = $this->makeUser();

        $claim = $this->service->submitClaim(
            facilityId:      $facility->id,
            userId:          $claimant->id,
            reason:          'This is our facility',
            registryEntryId: $registryEntry->id,
        );

        $this->assertEquals('submitted',        $claim->claim_status);
        $this->assertEquals($registryEntry->id, $claim->registry_entry_id);

        $this->service->approveClaim($claim->id, $admin->id);

        // Registry entry stamped
        $registryEntry->refresh();
        $this->assertEquals($facility->id, $registryEntry->claimed_facility_id);
        $this->assertEquals('verified',    $registryEntry->status);
        $this->assertNotNull($registryEntry->claimed_at);

        // care_facilities listing auto-created
        $careFacility = CareFacility::where('facility_id', $facility->id)->first();
        $this->assertNotNull($careFacility, 'care_facilities entry should be created on approval');
        $this->assertEquals('active',            $careFacility->listing_status);
        $this->assertEquals('partner_verified',  $careFacility->verification_status);
        $this->assertEquals($registryEntry->name, $careFacility->facility_name);
        $this->assertEqualsWithDelta(
            (float) $registryEntry->gps_lat,
            (float) $careFacility->latitude,
            0.0001,
            'Latitude should match registry GPS'
        );
    }

    // ── Test 5: Duplicate pending registry claim is blocked ──────────────────

    public function test_duplicate_pending_registry_claim_is_blocked(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);
        $registryEntry = FacilityRegistry::whereNotNull('gps_lat')->first();

        $facility = Facility::create(['name' => 'Hospital X', 'type' => 'hospital']);
        $claimant = $this->makeUser();

        $this->service->submitClaim($facility->id, $claimant->id, 'First claim', $registryEntry->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('FACILITY_CLAIM_ALREADY_EXISTS');

        $this->service->submitClaim($facility->id, $claimant->id, 'Duplicate', $registryEntry->id);
    }

    // ── Test 6: Already-claimed registry entry blocks new claim ──────────────

    public function test_claiming_already_claimed_registry_entry_is_blocked(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);
        $registryEntry = FacilityRegistry::whereNotNull('gps_lat')->first();

        $existingFacility = Facility::create(['name' => 'Owner Clinic', 'type' => 'clinic']);
        $registryEntry->update([
            'claimed_facility_id' => $existingFacility->id,
            'claimed_at'          => now(),
        ]);

        $newFacility = Facility::create(['name' => 'Impostor Clinic', 'type' => 'clinic']);
        $impostor    = $this->makeUser();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('REGISTRY_ENTRY_ALREADY_CLAIMED');

        $this->service->submitClaim(
            $newFacility->id,
            $impostor->id,
            'Trying to claim',
            $registryEntry->id,
        );
    }

    // ── Test 7: Approval activates existing stub, not a new duplicate ─────────

    public function test_approve_registry_claim_activates_existing_stub(): void
    {
        $this->seed(CameroonFacilityRegistrySeeder::class);
        $this->seed(CareMapRegistryStubSeeder::class);

        $registryEntry = FacilityRegistry::whereNotNull('gps_lat')->first();
        $facility      = Facility::create(['name' => 'Approved Clinic', 'type' => 'clinic']);
        $claimant      = $this->makeUser();
        $admin         = $this->makeUser();

        // Manually link the pre-existing stub to this facility so upsert finds it
        CareFacility::where('facility_name', $registryEntry->name)
            ->where('city', $registryEntry->city)
            ->where('country_code', 'CM')
            ->update(['facility_id' => $facility->id]);

        $claim = $this->service->submitClaim(
            $facility->id,
            $claimant->id,
            'Claiming my facility',
            $registryEntry->id,
        );

        $countBefore = CareFacility::where('facility_id', $facility->id)->count();
        $this->service->approveClaim($claim->id, $admin->id);
        $countAfter = CareFacility::where('facility_id', $facility->id)->count();

        $this->assertEquals($countBefore, $countAfter,
            'Approval should activate the existing stub — not create a second listing');

        $stub = CareFacility::where('facility_id', $facility->id)->first();
        $this->assertEquals('partner_verified', $stub->verification_status);
        $this->assertEquals('active',           $stub->listing_status);
    }

    // ── Test 8: Original CareMap claim flow (no registry_entry_id) ───────────

    public function test_approve_caremap_claim_updates_care_facility_partner(): void
    {
        $facility = Facility::create(['name' => 'Map Clinic', 'type' => 'clinic']);
        $claimant = $this->makeUser();
        $admin    = $this->makeUser();

        // Pre-existing care_facilities entry linked to this operational Facility
        $careFacility = CareFacility::create([
            'facility_id'         => $facility->id,
            'facility_name'       => 'Map Clinic',
            'facility_type'       => 'clinic',
            'country_code'        => 'CM',
            'region'              => 'Centre',
            'city'                => 'Yaoundé',
            'address'             => 'Yaoundé, Cameroon',
            'phone_primary'       => '+237 000 000 000',
            'listing_status'      => 'active',
            'verification_status' => 'unverified',
            'license_status'      => 'active',
            'integration_status'  => 'none',
        ]);

        // Submit claim without registry_entry_id (original CareMap flow)
        $claim = $this->service->submitClaim($facility->id, $claimant->id);

        $this->assertNull($claim->registry_entry_id);

        $this->service->approveClaim($claim->id, $admin->id);

        $careFacility->refresh();
        $this->assertEquals($claimant->id,      $careFacility->partner_id);
        $this->assertEquals('partner_verified',  $careFacility->verification_status);
    }
}
