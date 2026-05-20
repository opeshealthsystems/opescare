<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Enforce role-to-portal routing.
 *
 * If an authenticated user attempts to access a portal they are not authorised
 * for (e.g. a patient navigating to /portals/staff), they are silently
 * redirected to the correct portal for their role instead of receiving a 403,
 * which produces a better UX while still preventing cross-portal access.
 *
 * Unauthenticated users are redirected to login (the auth middleware fires
 * before this one, but the guard is duplicated here for safety).
 */
class EnsurePortalAccess
{
    /**
     * Portal prefix → role names allowed in that portal.
     * Role names match the canonical names seeded in RolesSeeder.
     */
    private const PORTAL_ROLES = [
        'portals/patient' => [
            'patient', 'guardian', 'caregiver', 'dependent_manager', 'emergency_contact',
        ],
        'portals/staff' => [
            // Clinical providers
            'doctor', 'multi_doctor', 'specialist', 'consultant', 'resident', 'visiting_doctor',
            // Nursing
            'nurse', 'triage_nurse', 'ward_nurse', 'midwife', 'nurse_supervisor',
            // Training
            'student_doctor', 'student_nurse', 'intern',
            // Front desk & operations
            'receptionist', 'front_desk', 'appointment_coordinator', 'queue_manager', 'records_officer',
            // Lab
            'labtech', 'lab_scientist', 'lab_manager', 'lab_validator', 'sample_collection',
            // Pharmacy
            'pharmacist', 'pharmacy_technician', 'pharmacy_manager', 'medicine_stock', 'dispensing_officer',
            // Billing
            'cashier', 'billing_officer', 'finance_manager', 'refund_approver', 'wallet_ops',
            // Data quality
            'data_steward', 'reconciliation_officer', 'data_import_officer', 'data_quality_reviewer',
        ],
        'portals/insurance' => [
            'insurance_reviewer', 'insurance_claims', 'insurance_preauth',
            'insurance_admin', 'insurance_finance',
        ],
        'portals/admin' => [
            // Facility administration
            'facility_admin', 'clinic_admin', 'hospital_admin', 'facility_ceo',
            'department_manager', 'branch_admin', 'finance',
            // Platform administration
            'platform_admin', 'super_admin', 'product_admin', 'system_admin',
            'legal_admin', 'country_admin', 'regional_admin',
            // Compliance & security (access security ops section)
            'privacy_officer', 'data_protection_officer', 'security_officer',
            'compliance_officer', 'audit_reviewer', 'emergency_access_reviewer',
            // Support & CS
            'support_agent', 'support_manager', 'customer_success',
            'implementation_lead', 'training_support',
            // Partner governance
            'partner_admin', 'partner_reviewer', 'partner_compliance', 'partner_technical',
            // Academy administration
            'academy_admin',
        ],
        'portals/developer' => [
            'developer', 'developer_org_admin', 'api_partner', 'api_technical',
            'webhook_manager', 'sandbox_developer',
        ],
        'portals/lite' => [
            'lite_facility', 'lite_staff', 'lite_device', 'lite_offline_sync',
        ],
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user     = Auth::user();
        $roleName = $user->role?->name;

        // No role assigned yet → allow through (will show empty dashboard)
        // A future gate can restrict further once roles are seeded.
        if (!$roleName) {
            return $next($request);
        }

        $requestedPrefix = $this->detectPortalPrefix($request->path());

        // Not a portal path we manage — skip
        if ($requestedPrefix === null) {
            return $next($request);
        }

        $allowedRoles = self::PORTAL_ROLES[$requestedPrefix] ?? [];

        if (!in_array($roleName, $allowedRoles, true)) {
            return redirect($this->correctPortalFor($roleName), 302);
        }

        return $next($request);
    }

    private function detectPortalPrefix(string $path): ?string
    {
        foreach (array_keys(self::PORTAL_ROLES) as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $prefix;
            }
        }
        return null;
    }

    private function correctPortalFor(string $role): string
    {
        return match (true) {
            in_array($role, self::PORTAL_ROLES['portals/patient'])    => '/portals/patient',
            in_array($role, self::PORTAL_ROLES['portals/staff'])      => '/portals/staff',
            in_array($role, self::PORTAL_ROLES['portals/insurance'])  => '/portals/insurance/claims',
            in_array($role, self::PORTAL_ROLES['portals/developer'])  => '/portals/developer',
            in_array($role, self::PORTAL_ROLES['portals/lite'])       => '/portals/lite',
            default                                                    => '/portals/admin',
        };
    }
}
