# OpenAPI Specification — Convention, Guardrail & Backlog

**Status:** Foundation shipped · coverage growing (ratcheted by a contract test)
**Spec:** [`public/openapi.yaml`](../public/openapi.yaml) (OpenAPI 3.1.0)
**Rendered:** ReDoc at `/docs/playground`
**Related:** [API-VERSIONING.md](API-VERSIONING.md), [API-RESOURCES.md](API-RESOURCES.md), [API-OAUTH.md](API-OAUTH.md)

---

## 1. Convention

1. **Absolute, exact paths.** The `servers` URL is the bare host
   (`http://opescare.test`, `https://api.opescare.com`). Every path includes its
   full real prefix — `/api/v1/...` for the API, `/.well-known/...` for
   discovery. A documented path string equals the live route URI exactly.
2. **Only real endpoints.** Never document an aspirational or approximate
   endpoint. If you can't point to a registered route, it doesn't belong in the
   spec. (The previous hand-written spec had drifted — wrong base path, SDK
   paths that didn't resolve — which is exactly what the guardrail now prevents.)
3. **Schemas grounded in code.** Request bodies mirror the controller's
   `$request->validate([...])` rules; responses mirror the actual
   `response()->json(...)` shape (and reuse the resource schemas from
   [API-RESOURCES.md](API-RESOURCES.md): `ClinicalNote`, `AllergyRecord`,
   `Diagnosis`).
4. **Accurate security.** Each operation declares the security scheme its route
   actually enforces: `BearerAuth` (RS256 JWT), `ClientId`+`ClientSecret`
   (VerifyIntegrationClient), or `BridgeAgentId`+`BridgeAgentKey`.

## 2. Guardrail (contract test)

[`tests/Feature/Architecture/OpenApiContractTest.php`](../tests/Feature/Architecture/OpenApiContractTest.php)
asserts **every documented (method, path) resolves to a real registered route**
and fails CI otherwise — no phantom endpoints can be published. It also prints
honest coverage (documented operations vs total API routes) on each run.

## 3. Current coverage

**15 verified operations** of ~590 API routes (~2.5%). Documented surfaces:

| Surface | Endpoints |
|---------|-----------|
| OAuth | `POST /api/v1/connect/auth/token`, `POST /api/v1/connect/auth/introspect` |
| Discovery | `GET /.well-known/jwks.json`, `GET /.well-known/oauth-authorization-server` |
| Encounters | notes (POST), notes/{note} (GET), notes/{note}/amend (POST), allergies (POST), diagnoses (POST) |
| Records (B2B) | records/encounters, records/lab-results, records/prescriptions (POST) |
| Bridge | sync (POST), heartbeat (POST), status (GET) |

These are the highest-stakes external/partner surfaces. The long tail is the
**backlog** — documented accurately or not at all.

## 4. Backlog (next, by external value)

1. **SDK** (`/api/v1/sdk/*`) — patients summary/encounters, facilities, stock,
   appointments, webhooks, token introspection.
2. **Connect patients & consent** — patients/search, patients/resolve,
   patients/{health_id}/summary, consents/request, consents/verify,
   emergency-access/request, medical-ids/verify(-qr).
3. **Connect inventory / webhooks / reconciliation** — blood & pharmacy stock
   sync, webhook subscriptions + event replay, reconciliation cases.
4. **FHIR R4** (`/api/fhir/R4/*`) — generated separately from the
   CapabilityStatement; cross-link rather than duplicate.
5. **Mobile / ProviderMobile** — patient- and provider-app endpoints.

## 5. Adding an endpoint to the spec

1. Confirm the route is live: `php artisan route:list | grep <path>`.
2. Add the path under `paths:` with the **full** URI, correct method, and the
   security scheme the route enforces.
3. Ground the request body in the controller's validation rules and the response
   in its actual JSON shape (reuse `#/components/schemas/*`).
4. Run `php artisan test --filter=OpenApiContractTest` — it must stay green
   (proves the path is real) and the coverage number ticks up.
