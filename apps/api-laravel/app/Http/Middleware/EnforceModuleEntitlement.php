<?php

namespace App\Http\Middleware;

use App\Models\ModuleEntitlement;
use App\Models\OrganizationSubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforceModuleEntitlement — gates API access behind subscription module entitlements.
 *
 * Usage in routes:
 *   Route::middleware(['auth:sanctum', 'module:telemedicine'])->group(...);
 *
 * The module key is matched against module_entitlements.module_key for the
 * organization's active subscription. If no subscription is found or the module
 * is not entitled, the request is rejected with 403.
 *
 * Passes silently when:
 *   - No organization_id is resolvable (non-org routes)
 *   - The subscription plan has no limits for this module (treated as included)
 */
class EnforceModuleEntitlement
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $orgId = $user->organization_id
            ?? $user->facility?->organization_id
            ?? null;

        if (!$orgId) {
            return $next($request);
        }

        $subscription = OrganizationSubscription::where('organization_id', $orgId)
            ->whereIn('status', ['active', 'trial'])
            ->latest('created_at')
            ->first();

        if (!$subscription) {
            return response()->json([
                'error' => 'No active subscription. Please contact your organisation administrator.',
                'code'  => 'SUBSCRIPTION_REQUIRED',
            ], 402);
        }

        $entitled = ModuleEntitlement::where('subscription_id', $subscription->id)
            ->where('module_key', $moduleKey)
            ->where('enabled', true)
            ->exists();

        if (!$entitled) {
            return response()->json([
                'error'  => "Module '{$moduleKey}' is not included in your subscription plan.",
                'code'   => 'MODULE_NOT_ENTITLED',
                'module' => $moduleKey,
            ], 403);
        }

        return $next($request);
    }
}
