<?php

/*
|--------------------------------------------------------------------------
| Module Feature Flags — V1 Launch Scope Freeze
|--------------------------------------------------------------------------
|
| See docs/plans/V1_LAUNCH_SCOPE.md. These flags freeze whole modules out of
| the V1 launch surface. Freezing is NOT deleting: no route file is removed,
| no migration is rolled back, no seeded institutional data is touched (the
| 15 real Cameroonian insurance_providers rows and the 897 facility rows stay
| exactly where they are). Flip a flag back on and the module returns intact.
|
| These flags are a KILL SWITCH and they FAIL CLOSED. They are deliberately
| NOT the same thing as `module:<key>` (App\Http\Middleware\EnforceModuleEntitlement),
| which is a subscription-entitlement gate and fails OPEN — it calls $next()
| when no organization or no active subscription is resolvable. Never use that
| middleware to freeze a module.
|
| Consumed by:
|   - App\Support\Features::enabled()
|   - App\Http\Middleware\EnforceFeatureFlag  ('feature:<key>' + the URI-pattern
|     freeze map applied in bootstrap/app.php)
|   - the @feature('<key>') Blade directive (App\Providers\AppServiceProvider)
|
*/

/*
 * Frozen modules default OFF in every environment EXCEPT `testing`.
 *
 * The existing suite exercises the frozen code paths directly (ClaimSubmissionTest,
 * billing, CDSS, inventory and analytics feature tests). Defaulting ON under
 * APP_ENV=testing keeps that suite green, so the freeze stays a deployment
 * decision rather than a reason to delete coverage for code we intend to
 * un-freeze later. Production and staging must opt in explicitly, per module.
 *
 * A test that needs to assert frozen behaviour overrides the flag directly:
 *   config(['features.flags.insurance' => false]);
 */
$frozenDefault = env('APP_ENV', 'production') === 'testing';

return [

    /*
    |----------------------------------------------------------------------
    | Flags
    |----------------------------------------------------------------------
    |
    | One explicit env-driven boolean per frozen module. A key that is absent
    | from this list is treated as frozen by Features::enabled() — unknown
    | keys never grant access.
    |
    */
    'flags' => [

        // Manual claims ledger. 15 real insurers seeded; every other insurance
        // table is empty and the claim lifecycle has never run once in prod.
        // Cameroonian payers have no APIs — this is a sales problem, not code.
        'insurance' => (bool) env('FEATURE_INSURANCE', $frozenDefault),

        // Facility-internal patient billing (invoices, cashier sessions,
        // wallets, payment plans). Internal ops, not cross-system exchange.
        // NOTE: this does NOT cover OpesCare's own platform revenue —
        // portals/admin/financial/*, portals/admin/subscription/* and the
        // MoMo/Orange gateway callbacks stay live. That is how we get paid.
        'billing' => (bool) env('FEATURE_BILLING', $frozenDefault),

        // Facility-internal stock, supply chain and batch tracking. All tables
        // already empty; schema preserved.
        // NOTE: this does NOT cover the pharmacy/blood FINDERS, which are the
        // interoperability product itself and ship in V1 — nor the partner
        // stock-sync ingest that feeds them.
        'inventory_ops' => (bool) env('FEATURE_INVENTORY_OPS', $frozenDefault),

        // Drug-interaction / allergy / lab-rule alerting. Clinical-safety
        // liability we would own; out of scope until we can carry it.
        'clinical_decision_support' => (bool) env('FEATURE_CLINICAL_DECISION_SUPPORT', $frozenDefault),

        // Analytics and public-health dashboards. Build the data foundation
        // first — dashboards over 2.4% pharmacy coverage mislead more than
        // they inform.
        // NOTE: MINSANTE statutory report generation/submission stays live;
        // only the dashboard/intelligence read surface is frozen.
        'analytics_dashboards' => (bool) env('FEATURE_ANALYTICS_DASHBOARDS', $frozenDefault),

        // Patient-facing plan shopping and purchase. Commercial ambiguity and
        // zero insurance_plans rows in the database.
        'insurance_marketplace' => (bool) env('FEATURE_INSURANCE_MARKETPLACE', $frozenDefault),

        // Full telehealth platform — waiting-room queue management and video
        // session orchestration. The thin book -> consult path (create, view,
        // consent, cancel, start, end) stays IN and is NOT gated by this flag.
        'telemedicine_full' => (bool) env('FEATURE_TELEMEDICINE_FULL', $frozenDefault),

    ],

];
