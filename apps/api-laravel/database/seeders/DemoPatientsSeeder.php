<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Patient;

class DemoPatientsSeeder extends Seeder {
    public function run(): void {
        $patients = [
            ['id' => '00000000-0000-0000-0000-300000000001', 'first_name' => 'Demo Patient', 'last_name' => 'One', 'health_id' => 'OC-DEMO-PAT-0001', 'sex' => 'female', 'date_of_birth' => '1992-04-14', 'identity_status' => 'verified_demo', 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-300000000002', 'first_name' => 'Demo Child', 'last_name' => 'Patient', 'health_id' => 'OC-DEMO-CHILD-0001', 'sex' => 'male', 'date_of_birth' => '2018-08-20', 'identity_status' => 'verified_demo', 'is_demo' => true],
            ['id' => '00000000-0000-0000-0000-300000000003', 'first_name' => 'Demo Emergency', 'last_name' => 'Patient', 'health_id' => 'OC-DEMO-EMERGENCY-0001', 'sex' => 'male', 'date_of_birth' => '1985-11-09', 'identity_status' => 'verified_demo', 'is_demo' => true],
        ];
        foreach ($patients as $p) { Patient::create($p); }
    }
}