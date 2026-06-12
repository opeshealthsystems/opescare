# OpesCare Developer Documentation Portal — Design Spec

**Date:** 2026-05-20  
**Status:** Approved  
**Author:** OpesCare Engineering

---

## Goal

Build a comprehensive, publicly accessible developer documentation portal at `/docs` that allows third-party developers to integrate with OpesCare using any of the five supported integration types: Connect API, SDK, Bridge Agent, Widget, and Webhooks. The portal requires zero login, works from any browser, and ships with real code examples in five languages.

---

## Architecture

**Blade-native, zero new Composer packages.** A new `DocsController` handles all `/docs/*` routes (public, no auth middleware). A new `layouts/docs.blade.php` provides the two-column layout (sticky left nav + content). Code tabs use vanilla JS with `localStorage` for language persistence. The interactive playground embeds Redoc from CDN against a hand-authored OpenAPI 3.1 YAML served as a static file.

---

## URL Structure

| URL | Blade View | Route Name |
|-----|-----------|------------|
| `/docs` | `docs/index` | `docs.index` |
| `/docs/authentication` | `docs/authentication` | `docs.authentication` |
| `/docs/api` | `docs/api` | `docs.api` |
| `/docs/sdk` | `docs/sdk` | `docs.sdk` |
| `/docs/bridge` | `docs/bridge` | `docs.bridge` |
| `/docs/widget` | `docs/widget` | `docs.widget` |
| `/docs/webhooks` | `docs/webhooks` | `docs.webhooks` |
| `/docs/errors` | `docs/errors` | `docs.errors` |
| `/docs/playground` | `docs/playground` | `docs.playground` |
| `/docs/changelog` | `docs/changelog` | `docs.changelog` |

All routes: `Route::get()` only, no middleware.

---

## Layout: `layouts/docs.blade.php`

```
┌─────────────────────────────────────────────────────────────┐
│  TOPBAR: OpesCare logo | "Developer Docs" | GitHub | Status │
├──────────────┬──────────────────────────────────────────────┤
│  LEFT NAV    │  CONTENT AREA                                │
│  (sticky,    │                                              │
│   240px)     │  H1 page title                               │
│              │  Lead paragraph                              │
│  Navigation  │                                              │
│  items (see  │  Code tabs: PHP|JS|Python|cURL|Java          │
│  below)      │  ┌──────────────────────────────────────┐    │
│              │  │  code block                          │    │
│              │  └──────────────────────────────────────┘    │
│              │                                              │
│              │  Response example                            │
└──────────────┴──────────────────────────────────────────────┘
```

**Colour palette:** matches existing OpesCare brand (indigo `#4F46E5` primary, dark sidebar `#0F172A` background text).  
**Fonts:** Inter (already loaded via Google Fonts on public pages).  
**Mobile:** sidebar collapses to hamburger menu below 768px.

---

## Left Nav Hierarchy

```
Getting Started
  ├─ Quickstart
  ├─ Environments & Base URLs
  └─ Sandbox Credentials

Authentication
  ├─ Connect API (OAuth 2.0 client credentials)
  ├─ SDK (Bearer token)
  └─ Bridge Agent Token

Connect API
  ├─ Overview
  ├─ Authentication Endpoint
  ├─ Patient Endpoints
  ├─ Consent Endpoints
  ├─ Records Endpoints
  ├─ Inventory Endpoints
  ├─ Webhook Subscriptions
  └─ Reconciliation

SDK
  ├─ Installation
  ├─ Initialisation
  ├─ Patient Methods
  ├─ Facility Methods
  ├─ Appointments
  └─ Webhooks via SDK

Bridge Agent
  ├─ What is the Bridge?
  ├─ Setup & Registration
  ├─ Sync Endpoint
  ├─ Heartbeat
  └─ Status

Widget
  ├─ Embed Code
  ├─ Configuration Options
  ├─ Events & Callbacks
  └─ Styling

Webhooks
  ├─ Event Types
  ├─ Payload Schema
  ├─ Signature Verification
  ├─ Retry Policy
  └─ Testing

Errors & Troubleshooting
  ├─ HTTP Status Codes
  ├─ Error Response Format
  └─ Common Errors

Interactive Playground
Changelog
```

---

## Code Examples: Languages

Five tabs on every code snippet: **PHP | JavaScript | Python | cURL | Java**

Language preference is stored in `localStorage('docs_lang')` and applied automatically on page load.

---

## Integration Types — Content Summary

### Connect API (REST, OAuth 2.0)
- Token endpoint: `POST /api/v1/connect/auth/token` — `client_credentials` grant
- 16 endpoints covering patient search, consent, records, inventory, webhooks, reconciliation
- Auth: `Authorization: Bearer {access_token}` header on every request
- Sandbox credentials shown: `client_id: demo_dev_sandbox`, `client_secret: demo_secret_sandbox_2026`

### SDK
- PHP: `composer require opescare/sdk` (install instructions; SDK wraps the bearer-token API)
- JS: `npm install @opescare/sdk`
- Initialise: `OpesCareSDK::init($sdkToken)` / `new OpesCareSDK({ token })`
- Methods: `getPatient()`, `getFacility()`, `listAppointments()`, `subscribeWebhook()`, `introspect()`
- Auth: SDK tokens created in the developer portal, passed in `Authorization: Bearer`

### Bridge Agent
- Agent syncs local HIS data to OpesCare via three endpoints
- `POST /api/v1/bridge/sync` — push visits, vitals, labs
- `POST /api/v1/bridge/heartbeat` — keepalive every 5 min
- `GET /api/v1/bridge/status` — read agent status
- Auth: `X-Bridge-Token` header (generated when registering the agent)

### Widget
- Drop-in `<script>` + `<div>` embed
- Config object: `patientId`, `facilityId`, `sdkToken`, `theme`, `locale`
- JS events: `opescare:loaded`, `opescare:consent-granted`, `opescare:error`
- Security: `sandbox="allow-scripts allow-same-origin"` iframe attributes

### Webhooks
- Subscribe via `POST /api/v1/connect/webhooks/subscriptions` or developer portal UI
- Event types: `appointment.created`, `appointment.updated`, `lab_result.ready`, `prescription.ready`, `consent.granted`, `payment.completed`, `patient.registered`
- Payload signed with HMAC-SHA256; signature in `X-OpesCare-Signature` header
- Retry: exponential back-off, 5 attempts (1s, 5s, 30s, 2min, 10min)
- Verification code in all 5 languages

---

## OpenAPI YAML

**File:** `public/docs/openapi.yaml`  
**Spec version:** OpenAPI 3.1.0  
**Served as:** static file (no Laravel controller needed)

Sections:
- `info` — title, version, contact
- `servers` — sandbox (`/api/v1`) and production
- `components.securitySchemes` — BearerAuth (Connect), SdkToken, BridgeToken
- `paths` — all 28 endpoints (16 Connect + 9 SDK + 3 Bridge)
- Full request/response schemas with examples

---

## Demo Developer Account

**User:** `demo.developer@opescare.test` (id `00000000-0000-0000-0000-200000000050`)  
**Action:** Add a `developer_accounts` row via `DemoDeveloperAccountSeeder` (new, separate seeder).

Fields to populate:

| Column | Value |
|--------|-------|
| `id` | `00000000-0000-0000-0000-400000000001` |
| `user_id` | `00000000-0000-0000-0000-200000000050` |
| `display_name` | `OpesCare Demo Developer` |
| `email` | `demo.developer@opescare.test` |
| `company_name` | `Acme Health Systems` |
| `website_url` | `https://acmehealthsystems.example.com` |
| `status` | `active` |
| `email_verified_at` | `now()` |
| `api_terms_accepted` | `true` |
| `api_terms_accepted_at` | `now()` |
| `api_terms_version` | `v1.0` |
| `sandbox_only` | `false` |

Seeder is idempotent (checks `doesntExist()` before insert). Registered in `DemoDatabaseSeeder`.

---

## Files Created / Modified

| File | Status |
|------|--------|
| `resources/views/layouts/docs.blade.php` | **New** |
| `resources/views/docs/index.blade.php` | **New** |
| `resources/views/docs/authentication.blade.php` | **New** |
| `resources/views/docs/api.blade.php` | **New** |
| `resources/views/docs/sdk.blade.php` | **New** |
| `resources/views/docs/bridge.blade.php` | **New** |
| `resources/views/docs/widget.blade.php` | **New** |
| `resources/views/docs/webhooks.blade.php` | **New** |
| `resources/views/docs/errors.blade.php` | **New** |
| `resources/views/docs/playground.blade.php` | **New** |
| `resources/views/docs/changelog.blade.php` | **New** |
| `public/docs/openapi.yaml` | **New** |
| `public/css/docs.css` | **New** |
| `app/Http/Controllers/DocsController.php` | **New** |
| `database/seeders/DemoDeveloperAccountSeeder.php` | **New** |
| `routes/web.php` | **Modified** — add `/docs` route group |
| `database/seeders/DemoDatabaseSeeder.php` | **Modified** — register new seeder |

---

## Security Constraints (from session)

- No patient data exposed publicly
- QR codes do not expose full medical data
- Sandbox credentials shown are for demo only (hashed secrets in DB)
- Docs are read-only — no write operations from documentation pages
- Patient data security constraints remain unchanged
