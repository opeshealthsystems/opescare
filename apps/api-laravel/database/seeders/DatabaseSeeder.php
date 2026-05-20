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
        $this->call([
            AccountCategoriesSeeder::class,
            DashboardProfilesSeeder::class,
            RolesSeeder::class,
        ]);

        // When demo mode is enabled, seed all demo data automatically so that
        // `php artisan db:seed` (or migrate:fresh --seed) is a single-step setup.
        if (config('demo.enabled', (bool) env('OPESCARE_DEMO_MODE', false))) {
            $this->call(DemoDatabaseSeeder::class);
        }
    }
}
