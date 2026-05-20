<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds demo pharmacy_inventories and blood_inventories.
 * Idempotent – safe to run multiple times.
 */
class DemoPharmacyStockSeeder extends Seeder
{
    private const FAC_HOSPITAL  = '00000000-0000-0000-0000-100000000001';
    private const FAC_PHARMACY  = '00000000-0000-0000-0000-100000000004';
    private const FAC_LAB       = '00000000-0000-0000-0000-100000000005';

    public function run(): void
    {
        // ── Pharmacy inventory ───────────────────────────────────────
        $medicines = [
            ['id' => '00000000-0000-0000-0011-100000000001', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Amoxicillin', 'generic' => 'Amoxicillin', 'form' => 'Capsule',
             'strength' => '500 mg', 'qty' => 350, 'status' => 'in_stock'],
            ['id' => '00000000-0000-0000-0011-100000000002', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Paracetamol', 'generic' => 'Paracetamol', 'form' => 'Tablet',
             'strength' => '1000 mg', 'qty' => 1200, 'status' => 'in_stock'],
            ['id' => '00000000-0000-0000-0011-100000000003', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Metformin', 'generic' => 'Metformin HCl', 'form' => 'Tablet',
             'strength' => '500 mg', 'qty' => 28, 'status' => 'low_stock'],
            ['id' => '00000000-0000-0000-0011-100000000004', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Atorvastatin', 'generic' => 'Atorvastatin Calcium', 'form' => 'Tablet',
             'strength' => '40 mg', 'qty' => 180, 'status' => 'in_stock'],
            ['id' => '00000000-0000-0000-0011-100000000005', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Amlodipine', 'generic' => 'Amlodipine Besilate', 'form' => 'Tablet',
             'strength' => '5 mg', 'qty' => 0, 'status' => 'out_of_stock'],
            ['id' => '00000000-0000-0000-0011-100000000006', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Omeprazole', 'generic' => 'Omeprazole', 'form' => 'Capsule',
             'strength' => '20 mg', 'qty' => 540, 'status' => 'in_stock'],
            ['id' => '00000000-0000-0000-0011-100000000007', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Salbutamol Inhaler', 'generic' => 'Salbutamol', 'form' => 'MDI Inhaler',
             'strength' => '100 mcg', 'qty' => 15, 'status' => 'low_stock'],
            ['id' => '00000000-0000-0000-0011-100000000008', 'fac' => self::FAC_HOSPITAL,
             'name' => 'Ciprofloxacin', 'generic' => 'Ciprofloxacin HCl', 'form' => 'Tablet',
             'strength' => '500 mg', 'qty' => 200, 'status' => 'in_stock'],
            ['id' => '00000000-0000-0000-0011-100000000009', 'fac' => self::FAC_PHARMACY,
             'name' => 'Insulin Glargine', 'generic' => 'Insulin Glargine', 'form' => 'Injection',
             'strength' => '100 IU/mL', 'qty' => 60, 'status' => 'in_stock'],
            ['id' => '00000000-0000-0000-0011-100000000010', 'fac' => self::FAC_PHARMACY,
             'name' => 'Artemether/Lumefantrine', 'generic' => 'Artemether/Lumefantrine',
             'form' => 'Tablet', 'strength' => '20/120 mg', 'qty' => 84, 'status' => 'in_stock'],
        ];

        foreach ($medicines as $m) {
            if (DB::table('pharmacy_inventories')->where('id', $m['id'])->doesntExist()) {
                DB::table('pharmacy_inventories')->insert([
                    'id'                 => $m['id'],
                    'facility_id'        => $m['fac'],
                    'medicine_name'      => $m['name'],
                    'generic_name'       => $m['generic'],
                    'form'               => $m['form'],
                    'strength'           => $m['strength'],
                    'available_quantity' => $m['qty'],
                    'stock_status'       => $m['status'],
                    'is_expired'         => false,
                    'is_recalled'        => false,
                    'is_quarantined'     => false,
                    'last_stock_update'  => now(),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }

        // ── Blood inventory ──────────────────────────────────────────
        $bloodGroups = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
        $components  = ['whole_blood', 'packed_red_cells', 'fresh_frozen_plasma', 'platelets'];
        $startUuid   = 1;

        foreach ($bloodGroups as $bg) {
            foreach ($components as $comp) {
                $id = sprintf('00000000-0000-0000-0012-%012d', $startUuid++);
                // Vary quantities realistically
                $units = match(true) {
                    $bg === 'O+' && $comp === 'packed_red_cells' => 18,
                    $bg === 'O-' => random_int(2, 6),    // rare, kept low
                    $comp === 'platelets' => random_int(4, 12),
                    default => random_int(5, 25),
                };

                if (DB::table('blood_inventories')->where('id', $id)->doesntExist()) {
                    DB::table('blood_inventories')->insert([
                        'id'               => $id,
                        'facility_id'      => self::FAC_HOSPITAL,
                        'blood_group'      => $bg,
                        'component'        => $comp,
                        'available_units'  => $units,
                        'is_expired'       => false,
                        'is_quarantined'   => false,
                        'is_unsafe'        => false,
                        'last_stock_update'=> now(),
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }
    }
}
