<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DemoOrganizationsSeeder::class,
            DemoFacilitiesSeeder::class,
            DemoDepartmentsSeeder::class,
            DemoServicesSeeder::class,
            DemoRolesAndPermissionsSeeder::class,
            DemoUsersSeeder::class,
            DemoFacilityAssignmentsSeeder::class,
            DemoPatientsSeeder::class,
            DemoConsentSeeder::class,
            DemoClinicalRecordsSeeder::class,
            DemoPharmacyStockSeeder::class,
            DemoBloodInventorySeeder::class,
            DemoInsuranceSeeder::class,
            DemoPublicHealthSeeder::class,
            DemoDeveloperSeeder::class,
            DemoNotificationsSeeder::class,
            DemoReconciliationSeeder::class,
            DemoAuditSeeder::class,
        ]);
    }
}