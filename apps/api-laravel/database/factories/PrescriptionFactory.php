<?php
namespace Database\Factories;

use App\Models\Facility;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'patient_id'    => null,
            'facility_id'   => Facility::factory(),
            'visit_id'      => null,
            'prescribed_by' => $this->faker->name(),
            'status'        => $this->faker->randomElement(['active', 'dispensed', 'expired']),
            'notes'         => null,
            'prescribed_at' => now()->subDays(rand(1, 60)),
            'dispensed_at'  => null,
            'expires_at'    => now()->addDays(30),
        ];
    }
}
