<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Completes the demo developer account record in developer_accounts.
 *
 * The user (demo.developer@opescare.test) is created by DemoDeveloperSeeder.
 * This seeder adds the developer_accounts row with all fields filled.
 *
 * Idempotent – inserts only if the row does not already exist.
 */
class DemoDeveloperAccountSeeder extends Seeder
{
    private const DEV_ACCOUNT_ID = '00000000-0000-0000-0000-400000000001';
    private const DEV_USER_ID    = '00000000-0000-0000-0000-200000000050';

    public function run(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('developer_accounts')) {
            $this->command->warn('developer_accounts table not found — skipping.');
            return;
        }

        if (DB::table('developer_accounts')->where('id', self::DEV_ACCOUNT_ID)->exists()) {
            return;
        }

        DB::table('developer_accounts')->insert([
            'id'                       => self::DEV_ACCOUNT_ID,
            'user_id'                  => self::DEV_USER_ID,
            'display_name'             => 'OpesCare Demo Developer',
            'email'                    => 'demo.developer@opescare.test',
            'company_name'             => 'Acme Health Systems',
            'website_url'              => 'https://acmehealthsystems.example.com',
            'status'                   => 'active',
            'email_verification_token' => null,
            'email_verified_at'        => now(),
            'api_terms_accepted'       => true,
            'api_terms_accepted_at'    => now(),
            'api_terms_version'        => 'v1.0',
            'sandbox_only'             => false,
            'admin_notes'              => 'Demo account — pre-approved for all integration types.',
            'suspended_by'             => null,
            'suspend_reason'           => null,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);
    }
}
