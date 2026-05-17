<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;

class DemoUsersSeeder extends Seeder {
    public function run(): void {
        $users = [
            ['id' => '00000000-0000-0000-0000-200000000001', 'name' => 'Dr. Demo General', 'email' => 'demo.doctor@opescare.test', 'password' => bcrypt('DemoPass!2026'), 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-200000000002', 'name' => 'Dr. Multi Facility', 'email' => 'demo.multi.doctor@opescare.test', 'password' => bcrypt('DemoPass!2026'), 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-200000000003', 'name' => 'Demo Patient One', 'email' => 'demo.patient@opescare.test', 'password' => bcrypt('DemoPass!2026'), 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-200000000004', 'name' => 'Demo Guardian', 'email' => 'demo.guardian@opescare.test', 'password' => bcrypt('DemoPass!2026'), 'is_demo' => true],
        ];
        foreach ($users as $u) { User::create($u); }
    }
}