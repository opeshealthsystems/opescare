<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds demo care facilities (Care Map) and their services.
 * Idempotent – safe to run multiple times.
 */
class DemoServicesSeeder extends Seeder
{
    private const CF1 = '00000000-0000-0000-0018-100000000001';  // Demo Central Hospital (care map)
    private const CF2 = '00000000-0000-0000-0018-100000000002';  // Demo City Clinic (care map)
    private const CF3 = '00000000-0000-0000-0018-100000000003';  // DemoCare Pharmacy (care map)

    public function run(): void
    {
        // ── Care facilities (public directory entries) ────────────────
        $facilities = [
            [
                'id' => self::CF1, 'facility_name' => 'Demo Central Hospital',
                'facility_type' => 'hospital', 'ownership_type' => 'public',
                'license_status' => 'active', 'verification_status' => 'government_verified',
                'listing_status' => 'active', 'country_code' => 'CM',
                'region' => 'Centre', 'city' => 'Yaoundé',
                'address' => '12 Avenue de l\'Indépendance, Yaoundé, Cameroon',
                'latitude' => 3.8687, 'longitude' => 11.5214,
                'phone_primary' => '+237 222 000 100',
                'email' => 'info@demo-central-hospital.test',
            ],
            [
                'id' => self::CF2, 'facility_name' => 'Demo City Clinic',
                'facility_type' => 'clinic', 'ownership_type' => 'private',
                'license_status' => 'active', 'verification_status' => 'license_verified',
                'listing_status' => 'active', 'country_code' => 'CM',
                'region' => 'Littoral', 'city' => 'Douala',
                'address' => '5 Rue des Palmiers, Douala, Cameroon',
                'latitude' => 4.0510, 'longitude' => 9.7679,
                'phone_primary' => '+237 233 000 200',
                'email' => 'contact@demo-city-clinic.test',
            ],
            [
                'id' => self::CF3, 'facility_name' => 'DemoCare Pharmacy',
                'facility_type' => 'pharmacy', 'ownership_type' => 'private',
                'license_status' => 'active', 'verification_status' => 'license_verified',
                'listing_status' => 'active', 'country_code' => 'CM',
                'region' => 'Centre', 'city' => 'Yaoundé',
                'address' => '3 Boulevard de la Liberté, Yaoundé, Cameroon',
                'latitude' => 3.8650, 'longitude' => 11.5169,
                'phone_primary' => '+237 222 000 300',
                'email' => 'pharmacy@democare.test',
            ],
        ];

        foreach ($facilities as $f) {
            if (DB::table('care_facilities')->where('id', $f['id'])->doesntExist()) {
                DB::table('care_facilities')->insert(array_merge($f, [
                    'geocoding_accuracy' => 'street_level',
                    'created_at' => now(), 'updated_at' => now(),
                ]));
            }
        }

        // ── Care facility services ────────────────────────────────────
        $services = [
            // Hospital services
            ['id' => '00000000-0000-0000-0019-100000000001', 'fac' => self::CF1,
             'name' => 'Emergency Medicine', 'category' => 'emergency', 'walk_in' => true],
            ['id' => '00000000-0000-0000-0019-100000000002', 'fac' => self::CF1,
             'name' => 'General Outpatient Consultation', 'category' => 'consultation', 'walk_in' => true],
            ['id' => '00000000-0000-0000-0019-100000000003', 'fac' => self::CF1,
             'name' => 'Cardiology', 'category' => 'specialist', 'walk_in' => false],
            ['id' => '00000000-0000-0000-0019-100000000004', 'fac' => self::CF1,
             'name' => 'Clinical Laboratory', 'category' => 'diagnostic', 'walk_in' => true],
            ['id' => '00000000-0000-0000-0019-100000000005', 'fac' => self::CF1,
             'name' => 'Blood Bank', 'category' => 'blood_bank', 'walk_in' => false],
            ['id' => '00000000-0000-0000-0019-100000000006', 'fac' => self::CF1,
             'name' => 'Inpatient / General Ward', 'category' => 'inpatient', 'walk_in' => false],
            // Clinic services
            ['id' => '00000000-0000-0000-0019-100000000007', 'fac' => self::CF2,
             'name' => 'Family Medicine', 'category' => 'consultation', 'walk_in' => true],
            ['id' => '00000000-0000-0000-0019-100000000008', 'fac' => self::CF2,
             'name' => 'Antenatal Care', 'category' => 'maternal', 'walk_in' => false],
            ['id' => '00000000-0000-0000-0019-100000000009', 'fac' => self::CF2,
             'name' => 'Child Immunisation', 'category' => 'paediatric', 'walk_in' => true],
            // Pharmacy services
            ['id' => '00000000-0000-0000-0019-100000000010', 'fac' => self::CF3,
             'name' => 'Dispensing & Retail Pharmacy', 'category' => 'pharmacy', 'walk_in' => true],
            ['id' => '00000000-0000-0000-0019-100000000011', 'fac' => self::CF3,
             'name' => 'Medication Counselling', 'category' => 'pharmacy', 'walk_in' => true],
        ];

        foreach ($services as $s) {
            if (DB::table('care_facility_services')->where('id', $s['id'])->doesntExist()) {
                DB::table('care_facility_services')->insert([
                    'id'                  => $s['id'],
                    'facility_id'         => $s['fac'],
                    'service_name'        => $s['name'],
                    'service_category'    => $s['category'],
                    'availability_status' => 'available',
                    'appointment_required'=> !$s['walk_in'],
                    'walk_in_allowed'     => $s['walk_in'],
                    'last_updated_at'     => now(),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }
}
