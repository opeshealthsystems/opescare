<?php

namespace Database\Seeders;

use App\Models\CareFacility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pre-populates care_facilities with stub listings from facility_registry entries
 * that have GPS coordinates. These stubs make Cameroonian healthcare facilities
 * discoverable on the CareMap immediately — before any facility claims itself.
 *
 * Stub listings have:
 *   - listing_status  = 'active'    (visible in CareMap search)
 *   - verification_status = 'unverified' (not yet partner-verified)
 *   - country_code    = 'CM'
 *
 * Idempotent: skips registry entries that already have a matching care_facilities
 * listing (matched by facility_name + city + country_code = 'CM').
 *
 * When a facility claims its registry entry and the claim is approved, the
 * corresponding care_facilities stub is activated (verification_status updated).
 */
class CareMapRegistryStubSeeder extends Seeder
{
    public function run(): void
    {
        $registryEntries = DB::table('facility_registry')
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->get();

        $created  = 0;
        $skipped  = 0;

        foreach ($registryEntries as $entry) {
            $alreadyExists = DB::table('care_facilities')
                ->where('facility_name', $entry->name)
                ->where('city', $entry->city)
                ->where('country_code', 'CM')
                ->exists();

            if ($alreadyExists) {
                $skipped++;
                continue;
            }

            DB::table('care_facilities')->insert([
                'id'                  => (string) Str::uuid(),
                'facility_name'       => $entry->name,
                'facility_type'       => $entry->type,
                'ownership_type'      => $entry->ownership,
                'country_code'        => 'CM',
                'region'              => $entry->region,
                'city'                => $entry->city ?? '',
                'address'             => $entry->address ?? (($entry->city ?? '') . ', Cameroon'),
                'latitude'            => $entry->gps_lat,
                'longitude'           => $entry->gps_lng,
                'phone_primary'       => $entry->phone ?? 'N/A',
                'email'               => $entry->email,
                'website'             => $entry->website,
                'listing_status'      => 'active',
                'verification_status' => 'unverified',
                'license_status'      => 'active',
                'integration_status'  => 'none',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            $created++;
        }

        $total = DB::table('care_facilities')->where('country_code', 'CM')->count();

        $this->command?->info(
            "CareMapRegistryStubSeeder: {$created} stub(s) created, {$skipped} skipped (already exist). " .
            "Total CM care_facilities: {$total}."
        );
    }
}
