<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RequirePlatformAdmin — separates PLATFORM administration (god-mode) from
 * FACILITY administration.
 *
 * Bug it fixes: EnsurePortalAccess allowed facility admins (clinic_admin,
 * hospital_admin, …) into /portals/admin in the SAME bucket as platform admins
 * (super_admin, platform_admin, …). The platform Control Center, Security Ops,
 * subscriptions, and the bare /admin/* god-mode data routes were therefore
 * reachable by a facility user. This middleware restricts those platform-only
 * paths to the platform role tier; facility admins keep their facility-scoped
 * admin views and get 403 on platform-only areas.
 *
 * It is a no-op on any path that is not platform-only, so it is safe to attach
 * broadly.
 */
class RequirePlatformAdmin
{
    /** Roles operated by the platform owner (OpesCare) — full god-mode tier. */
    private const PLATFORM_ROLES = [
        'super_admin', 'platform_admin', 'system_admin', 'product_admin',
        'legal_admin', 'country_admin', 'regional_admin',
        // Compliance / security / support / partner / academy are platform-company
        // functions (not facility staff) and legitimately use the platform console.
        'privacy_officer', 'data_protection_officer', 'security_officer',
        'compliance_officer', 'audit_reviewer', 'emergency_access_reviewer',
        'support_agent', 'support_manager', 'customer_success',
        'implementation_lead', 'training_support',
        'partner_admin', 'partner_reviewer', 'partner_compliance', 'partner_technical',
        'academy_admin',
    ];

    /**
     * Path prefixes that expose platform-wide (cross-facility / god-mode)
     * capabilities and must never be reachable by a facility-tier admin.
     */
    private const PLATFORM_ONLY_PREFIXES = [
        // Platform console
        'portals/admin/cc',
        'portals/admin/security',
        'portals/admin/subscription',
        'portals/admin/connect',
        'portals/admin/bridge',
        'portals/admin/kpi',
        'portals/admin/legal',
        'portals/admin/certifications',
        'portals/admin/code-mappings',
        'portals/admin/developer',
        'portals/admin/go-live',
        'portals/admin/reports',
        // Bare /admin/* god-mode data management (all users/facilities/patients)
        'admin/users',
        'admin/facilities',
        'admin/patients',
        'admin/staff',
        'admin/organizations',
        'admin/roles',
        'admin/care-map',
        'admin/academy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = ltrim($request->path(), '/');

        // Only guard platform-only paths; everything else passes straight through.
        $isPlatformOnly = false;
        foreach (self::PLATFORM_ONLY_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                $isPlatformOnly = true;
                break;
            }
        }
        if (! $isPlatformOnly) {
            return $next($request);
        }

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Resolve role with the same facility-aware logic as EnsurePortalAccess,
        // falling back to the user's global role (platform admins have no facility).
        $facilityId = session('active_facility_id') ?? $user->primary_facility_id ?? null;
        $role = ($facilityId && method_exists($user, 'roleAtFacility'))
            ? $user->roleAtFacility($facilityId)
            : null;
        $roleName = ($role?->name) ?? ($user->role?->name);

        if (! in_array($roleName, self::PLATFORM_ROLES, true)) {
            abort(403, 'This area is restricted to OpesCare platform administrators.');
        }

        return $next($request);
    }
}
