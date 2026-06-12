#!/usr/bin/env python3
"""
Authoritative route auth-coverage check for OpesCare.

Static parsing of routes/*.php is unreliable (Laravel resolves middleware at
runtime through nested fluent groups, controller __construct middleware, and
aliases). This script instead consumes the *authoritative* output of:

    php artisan route:list --json > routes.json

then flags any route whose resolved middleware contains no authentication layer,
excluding routes that are intentionally public.

Usage (from apps/api-laravel, with PHP/Laragon):
    php artisan route:list --json > routes.json
    python docs/audits/route_auth_check.py apps/api-laravel/routes.json
"""
import sys, json, re

# Middleware that counts as authentication (substring match, case-insensitive)
AUTH = [
    "auth", "sanctum", "auth.bearer", "auth.mobile", "verifybearertoken",
    "authenticatemobilepatient", "verifyintegrationclient", "verifybridgeagent",
    "verifysdktoken", "ensureportalaccess", "requirefacilitycontext",
    "requireconsentgrant", "role:", "permission:", "can:", "api.admin",
]
# URIs that are SUPPOSED to be public (regex, matched against the uri)
PUBLIC_OK = [
    r"^_?ignition", r"^sanctum/", r"^up$", r"livewire",
    r"auth/(login|token|otp|refresh|register|password|forgot)",
    r"/login", r"/token$", r"/refresh$",
    r"fhir/R4/metadata", r"\.well-known",
    r"care-map", r"pharmacies/medicine-search", r"facilities/nearby", r"nearby$",
    r"verify/", r"verify-code", r"certificate-verification",
    r"documents?/.*/verify", r"/health$", r"/status$", r"/metadata$",
    r"webhooks?/(inbound|receive|callback)", r"^demo/", r"momo|orange|whatsapp/(callback|webhook)",
]

def is_auth(mw):
    s = (";".join(mw) if isinstance(mw, list) else str(mw)).lower()
    if re.search(r"\bauth\b", s):
        return True
    return any(a in s for a in AUTH)

def is_public_ok(uri):
    return any(re.search(p, uri, re.I) for p in PUBLIC_OK)

def main(path):
    data = json.load(open(path, encoding="utf-8"))
    total = len(data)
    flagged = []
    for r in data:
        uri = r.get("uri", "")
        mw = r.get("middleware", []) or []
        if not is_auth(mw) and not is_public_ok(uri):
            flagged.append((r.get("method", "?"), uri, ";".join(mw) if isinstance(mw, list) else str(mw)))
    print(f"Total routes: {total}")
    print(f"Authenticated or intentionally-public: {total - len(flagged)}")
    print(f"FLAGGED (no auth, not on public allowlist): {len(flagged)}\n")
    for method, uri, mw in sorted(flagged, key=lambda x: x[1]):
        print(f"  {method:8} {uri:60} mw=[{mw}]")
    if not flagged:
        print("No unexpected unauthenticated routes. ✅")
    print("\nReview each flagged route: it should either gain auth middleware or "
          "be added to PUBLIC_OK if it is deliberately public.")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("usage: python route_auth_check.py <routes.json from `php artisan route:list --json`>")
        sys.exit(2)
    main(sys.argv[1])
