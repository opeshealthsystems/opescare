<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoFacilityAssignmentsSeeder extends Seeder {
    public function run(): void {
        // Multi-facility assignment logic
        // OpesCare currently relies on primary_facility_id in the User table
        // No facility_user table exists in this version of the schema.
    }
}