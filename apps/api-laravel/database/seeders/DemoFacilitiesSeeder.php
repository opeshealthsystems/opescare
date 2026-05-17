<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Facility;

class DemoFacilitiesSeeder extends Seeder {
    public function run(): void {
        $facilities = [
            ['id' => '00000000-0000-0000-0000-100000000001', 'name' => 'Demo Central Hospital', 'type' => 'hospital', 'status' => 'active_demo', 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-100000000002', 'name' => 'Demo City Clinic', 'type' => 'clinic', 'status' => 'active_demo', 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-100000000003', 'name' => 'Demo Specialist Hospital', 'type' => 'hospital', 'status' => 'active_demo', 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-100000000004', 'name' => 'DemoCare Pharmacy', 'type' => 'pharmacy', 'status' => 'active_demo', 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-100000000005', 'name' => 'Demo Diagnostic Laboratory', 'type' => 'laboratory', 'status' => 'active_demo', 'is_demo' => true],
        ];
        foreach ($facilities as $f) { Facility::create($f); }
    }
}