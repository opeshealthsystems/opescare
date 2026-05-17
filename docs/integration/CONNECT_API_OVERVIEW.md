# OpesCare Connect: API Overview & Core Design

The OpesCare Connect B2B API provides secure, auditable, and clinical-grade interoperability interfaces for hospitals, laboratories, pharmacies, insurers, and vendor systems.

---

## 1. Directory Structure & Environment Gateways

To guarantee extreme isolation between patient-facing interactions and business-to-business dispatches, the API is split into isolated route groups:

```text
/api/mobile/...             Official OpesCare mobile app (PIN/OTP authenticated)
/api/v1/connect/...         External verified integration clients (OAuth2 Credentialed)
/api/v1/webhooks/...        Webhook subscriptions and secure signature callbacks
```

### Environment Base URLs
*   **Sandbox**: `https://sandbox.connect.opescare.com/api/v1/connect`
*   **Production**: `https://api.connect.opescare.com/api/v1/connect`

---

## 2. Authentication and OAuth2 Access Tokens

All third-party integration clients must authenticate using standard **OAuth2 Client Credentials Grant** to retrieve a temporary Bearer access token.

### Get Token Request
*   **Endpoint**: `POST /api/v1/connect/auth/token`
*   **Headers**: `Content-Type: application/json`

```json
{
  "client_id": "client_your_facility_id",
  "client_secret": "secret_facility_private_secret_key",
  "grant_type": "client_credentials"
}
```

### Token Response
```json
{
  "access_token": "eyJhbGciOiJSUzI1NiIs...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "patients.search records.push consent.request"
}
```

---

## 3. Scopes Policy

Access is locked down via granular, least-privilege scopes:

| Scope | Allowed Roles | Description |
| :--- | :--- | :--- |
| `patients.search` | hospital, clinic, pharmacy, laboratory | Search patients without clinical histories |
| `records.pull` | hospital, clinic | Retrieve approved patient clinical summaries |
| `records.push` | hospital, clinic, laboratory, pharmacy | Submit new clinical records, prescriptions, or lab results |
| `inventory.sync` | pharmacy, blood_bank | Sync medicine or blood stock indices |
| `webhooks.manage` | all | Configure callback webhook subscriptions |

---

## 4. Standard Error Format

In compliance with professional clinical specs, all Connect errors utilize a standard, un-translated, machine-readable `error_code` alongside a correlation ID for tracing.

### Error Payload Example
```json
{
  "status": "rejected",
  "error_code": "CONSENT_REQUIRED",
  "message": "Patient consent is required before viewing this clinical record.",
  "required_action": "request_consent",
  "correlation_id": "req_opescare_8910_cba2"
}
```

### Standard Interoperability Error Codes
*   `AUTHENTICATION_FAILED`: Missing or invalid credentials.
*   `INSUFFICIENT_SCOPE`: Client lack permission scope for target endpoint.
*   `FACILITY_SUSPENDED`: Target facility locked due to compliance audits.
*   `CONSENT_REQUIRED`: Explicit patient consent grant required.
*   `IDEMPOTENCY_CONFLICT`: Re-using an Idempotency-Key with a different payload.
*   `RATE_LIMIT_EXCEEDED`: Too many requests; retry after the indicated header.
*   `VALIDATION_FAILED`: Payload schema constraints violated.
*   `RECONCILIATION_REQUIRED`: Patient search returned duplicate suspects.
