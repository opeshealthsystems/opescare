<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Features — the single source of truth for the V1 launch-scope module freeze.
 *
 * Two questions, one answer:
 *   1. "Is module <key> live?"            -> Features::enabled('insurance')
 *   2. "Is this URI part of a frozen module?" -> Features::featureForRequest($request)
 *
 * Both FAIL CLOSED. An unknown key, a missing config file, a non-boolean value
 * — every one of those reads as frozen. The only way to be enabled is for
 * config('features.flags.<key>') to be exactly true.
 *
 * This is deliberately not EnforceModuleEntitlement. That middleware gates on a
 * subscription entitlement and fails OPEN (no organization -> $next; no active
 * subscription -> $next). It is an upsell gate. This is a kill switch.
 *
 * @see config/features.php
 * @see \App\Http\Middleware\EnforceFeatureFlag
 * @see docs/plans/V1_LAUNCH_SCOPE.md
 */
final class Features
{
    /**
     * URI-pattern freeze map: feature key => Request::is() patterns.
     *
     * Populated once at boot from bootstrap/app.php, which is the one place
     * that declares what is frozen. Kept out of config/ on purpose: this map
     * must stay correct even when config:cache is stale, and it is applied at
     * middleware-registration time, not at request time.
     *
     * @var array<string, list<string>>
     */
    private static array $frozenPaths = [];

    /**
     * Declare the frozen URI surface. Called from bootstrap/app.php.
     *
     * @param  array<string, list<string>>  $map
     */
    public static function freeze(array $map): void
    {
        self::$frozenPaths = $map;
    }

    /**
     * @return array<string, list<string>>
     */
    public static function frozenPaths(): array
    {
        return self::$frozenPaths;
    }

    /**
     * Is this module live?
     *
     * Fails closed: anything other than a literal `true` in
     * config('features.flags.<key>') means frozen.
     */
    public static function enabled(string $key): bool
    {
        $flags = config('features.flags');

        if (! is_array($flags) || ! array_key_exists($key, $flags)) {
            return false;
        }

        return $flags[$key] === true;
    }

    /**
     * Inverse of enabled(), for readability at call sites.
     */
    public static function frozen(string $key): bool
    {
        return ! self::enabled($key);
    }

    /**
     * Which frozen module does this request belong to, if any?
     *
     * Returns null when the URI is not part of any frozen module — the request
     * is then none of this mechanism's business and passes straight through.
     */
    public static function featureForRequest(Request $request): ?string
    {
        foreach (self::$frozenPaths as $key => $patterns) {
            if ($patterns !== [] && $request->is(...$patterns)) {
                return $key;
            }
        }

        return null;
    }
}
