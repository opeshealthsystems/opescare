<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

/**
 * Seeds canonical role names from the OpesCare Account Types document.
 * All inserts are idempotent via firstOrCreate.
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // Patient & Family
            ['name' => 'patient',              'description' => 'Patient Account'],
            ['name' => 'guardian',             'description' => 'Guardian / Parent Account'],
            ['name' => 'caregiver',            'description' => 'Caregiver Account'],
            ['name' => 'dependent_manager',    'description' => 'Dependent Manager Account'],
            ['name' => 'emergency_contact',    'description' => 'Emergency Contact Account'],

            // Clinical Providers
            ['name' => 'doctor',               'description' => 'General Doctor'],
            ['name' => 'multi_doctor',         'description' => 'Multi-Facility Doctor'],
            ['name' => 'specialist',           'description' => 'Specialist Doctor'],
            ['name' => 'consultant',           'description' => 'Consultant Doctor'],
            ['name' => 'resident',             'description' => 'Resident Doctor'],
            ['name' => 'visiting_doctor',      'description' => 'Visiting Doctor'],

            // Nursing & Midwifery
            ['name' => 'nurse',                'description' => 'Nurse'],
            ['name' => 'triage_nurse',         'description' => 'Triage Nurse'],
            ['name' => 'ward_nurse',           'description' => 'Ward Nurse'],
            ['name' => 'midwife',              'description' => 'Midwife'],
            ['name' => 'nurse_supervisor',     'description' => 'Nurse Supervisor'],

            // Clinical Training
            ['name' => 'student_doctor',       'description' => 'Student Doctor'],
            ['name' => 'student_nurse',        'description' => 'Student Nurse'],
            ['name' => 'intern',               'description' => 'Intern / Trainee'],
            ['name' => 'medical_supervisor',   'description' => 'Medical School Supervisor'],

            // Front Desk & Operations
            ['name' => 'receptionist',         'description' => 'Receptionist / Front Desk'],
            ['name' => 'front_desk',           'description' => 'Front Desk Officer'],
            ['name' => 'appointment_coordinator', 'description' => 'Appointment Coordinator'],
            ['name' => 'queue_manager',        'description' => 'Queue Manager'],
            ['name' => 'records_officer',      'description' => 'Records Officer'],

            // Laboratory
            ['name' => 'labtech',              'description' => 'Lab Technician'],
            ['name' => 'lab_scientist',        'description' => 'Lab Scientist'],
            ['name' => 'lab_manager',          'description' => 'Lab Manager'],
            ['name' => 'lab_validator',        'description' => 'Lab Result Validator'],
            ['name' => 'sample_collection',    'description' => 'Sample Collection Officer'],

            // Pharmacy
            ['name' => 'pharmacist',           'description' => 'Pharmacist'],
            ['name' => 'pharmacy_technician',  'description' => 'Pharmacy Technician'],
            ['name' => 'pharmacy_manager',     'description' => 'Pharmacy Manager'],
            ['name' => 'medicine_stock',       'description' => 'Medicine Stock Manager'],
            ['name' => 'dispensing_officer',   'description' => 'Dispensing Officer'],

            // Billing & Finance
            ['name' => 'cashier',              'description' => 'Cashier'],
            ['name' => 'billing_officer',      'description' => 'Billing Officer'],
            ['name' => 'finance_manager',      'description' => 'Finance Manager'],
            ['name' => 'refund_approver',      'description' => 'Refund Approver'],
            ['name' => 'wallet_ops',           'description' => 'Wallet / Payment Operations'],

            // Insurance
            ['name' => 'insurance_reviewer',   'description' => 'Insurance Reviewer'],
            ['name' => 'insurance_claims',     'description' => 'Insurance Claims Officer'],
            ['name' => 'insurance_preauth',    'description' => 'Insurance Pre-Authorization Officer'],
            ['name' => 'insurance_admin',      'description' => 'Insurance Admin'],
            ['name' => 'insurance_finance',    'description' => 'Insurance Finance / Payment Officer'],

            // Facility Administration
            ['name' => 'facility_admin',       'description' => 'Facility Admin'],
            ['name' => 'clinic_admin',         'description' => 'Clinic Admin'],
            ['name' => 'hospital_admin',       'description' => 'Hospital Admin'],
            ['name' => 'facility_ceo',         'description' => 'Hospital Director / Facility Executive'],
            ['name' => 'department_manager',   'description' => 'Department Manager'],
            ['name' => 'branch_admin',         'description' => 'Branch / Multi-Facility Admin'],
            ['name' => 'finance',              'description' => 'Facility Finance Role'],

            // Health Organization / NGO
            ['name' => 'ngo_admin',            'description' => 'Health Organization / NGO Admin'],
            ['name' => 'health_program_manager', 'description' => 'Health Program Manager'],
            ['name' => 'outreach_team',        'description' => 'Outreach Team'],
            ['name' => 'mobile_clinic_team',   'description' => 'Mobile Clinic Team'],

            // Public Health
            ['name' => 'public_health_officer', 'description' => 'Public Health Officer'],
            ['name' => 'public_health_reviewer', 'description' => 'Public Health Data Reviewer'],
            ['name' => 'public_health_admin',  'description' => 'Public Health Admin'],
            ['name' => 'government_supervisor', 'description' => 'Ministry / Government Supervisor'],
            ['name' => 'disease_surveillance', 'description' => 'Disease Surveillance Officer'],
            ['name' => 'public_health_api',    'description' => 'Public Health Reporting API Client'],

            // Developer / API Partner
            ['name' => 'developer',            'description' => 'Developer Account'],
            ['name' => 'developer_org_admin',  'description' => 'Developer Organization Admin'],
            ['name' => 'api_partner',          'description' => 'API Partner Admin'],
            ['name' => 'api_technical',        'description' => 'API Technical User'],
            ['name' => 'webhook_manager',      'description' => 'Webhook Manager'],
            ['name' => 'sandbox_developer',    'description' => 'Sandbox Developer'],

            // Integration Device / Bridge Agent
            ['name' => 'bridge_agent',         'description' => 'Bridge Agent Device Account'],
            ['name' => 'bridge_admin',         'description' => 'Bridge Agent Admin'],
            ['name' => 'integration_device',   'description' => 'Integration Device Account'],
            ['name' => 'facility_device',      'description' => 'Facility Device Account'],
            ['name' => 'kiosk',                'description' => 'Kiosk / Shared Workstation'],

            // OpesCare Lite
            ['name' => 'lite_facility',        'description' => 'OpesCare Lite Facility Account'],
            ['name' => 'lite_staff',           'description' => 'OpesCare Lite Staff Account'],
            ['name' => 'lite_device',          'description' => 'OpesCare Lite Device Account'],
            ['name' => 'lite_offline_sync',    'description' => 'OpesCare Lite Offline Sync Account'],

            // Support & Customer Success
            ['name' => 'support_agent',        'description' => 'Support Agent'],
            ['name' => 'support_manager',      'description' => 'Support Manager'],
            ['name' => 'customer_success',     'description' => 'Customer Success'],
            ['name' => 'implementation_lead',  'description' => 'Implementation Lead'],
            ['name' => 'training_support',     'description' => 'Training Support'],

            // Privacy, Security & Compliance
            ['name' => 'privacy_officer',      'description' => 'Privacy Officer'],
            ['name' => 'data_protection_officer', 'description' => 'Data Protection Officer'],
            ['name' => 'security_officer',     'description' => 'Security Officer'],
            ['name' => 'compliance_officer',   'description' => 'Compliance Officer'],
            ['name' => 'audit_reviewer',       'description' => 'Audit Reviewer'],
            ['name' => 'emergency_access_reviewer', 'description' => 'Emergency Access Reviewer'],

            // Data Quality / Reconciliation
            ['name' => 'data_steward',         'description' => 'Data Steward'],
            ['name' => 'reconciliation_officer', 'description' => 'Reconciliation Officer'],
            ['name' => 'data_import_officer',  'description' => 'Data Import Officer'],
            ['name' => 'data_quality_reviewer', 'description' => 'Data Quality Reviewer'],

            // Academy / Certification
            ['name' => 'academy_learner',      'description' => 'Academy Learner'],
            ['name' => 'academy_instructor',   'description' => 'Academy Instructor / Trainer'],
            ['name' => 'academy_admin',        'description' => 'Academy Admin'],
            ['name' => 'certification_reviewer', 'description' => 'Certification Reviewer'],

            // Partner Governance
            ['name' => 'partner_admin',        'description' => 'Partner Admin'],
            ['name' => 'partner_reviewer',     'description' => 'Partner Reviewer'],
            ['name' => 'partner_compliance',   'description' => 'Partner Compliance Reviewer'],
            ['name' => 'partner_technical',    'description' => 'Partner Technical Contact'],

            // Platform / Super Admin
            ['name' => 'super_admin',          'description' => 'Super Admin'],
            ['name' => 'platform_admin',       'description' => 'Platform Owner'],
            ['name' => 'system_admin',         'description' => 'System Administrator'],
            ['name' => 'product_admin',        'description' => 'Product Admin'],
            ['name' => 'legal_admin',          'description' => 'Legal Document Admin'],
            ['name' => 'country_admin',        'description' => 'Country Admin'],
            ['name' => 'regional_admin',       'description' => 'Regional Admin'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], ['description' => $role['description']]);
        }
    }
}
