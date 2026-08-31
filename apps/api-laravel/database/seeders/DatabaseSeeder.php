<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Order matters for the facility pipeline:
        //   1. the three registry seeders fill facility_registry with real
        //      institutions (MINSANTE annuaire, ONPC/WHO, OpenStreetMap),
        //   2. CareMapRegistryStubSeeder promotes every registry row into
        //      care_facilities — the table the mobile app and Care Map read,
        //   3. PharmacyCatalogSeeder then attaches medicine stock to real,
        //      GPS-bearing pharmacies that step 2 created.
        $this->call([
            AccountCategoriesSeeder::class,
            DashboardProfilesSeeder::class,
            RolesSeeder::class,
            NotificationTemplateSeeder::class,
            CameroonFacilityRegistrySeeder::class,
            MinsanteFosaRegistrySeeder::class,
            CameroonOsmPharmacySeeder::class,
            CameroonPharmacyLabRegistrySeeder::class,
            // Backfills GPS/phone onto hospitals the registry seeders above
            // already created, so it must run after all of them. It only ever
            // fills NULLs and never inserts.
            MinsanteCat14BackfillSeeder::class,
            CameroonInsuranceSeeder::class,
            CareMapRegistryStubSeeder::class,
            PharmacyCatalogSeeder::class,
        ]);

        // When demo mode is enabled, seed all demo data automatically so that
        // `php artisan db:seed` (or migrate:fresh --seed) is a single-step setup.
        if (config('demo.enabled', (bool) env('OPESCARE_DEMO_MODE', false))) {
            $this->call(DemoDatabaseSeeder::class);
            // Demo vitals for the demo patient only, so the home screen's
            // Health Vitals card renders populated. Guarded on the demo
            // patient existing, so it is a no-op elsewhere.
            $this->call(DemoPatientVitalsSeeder::class);
        }
    }
}
