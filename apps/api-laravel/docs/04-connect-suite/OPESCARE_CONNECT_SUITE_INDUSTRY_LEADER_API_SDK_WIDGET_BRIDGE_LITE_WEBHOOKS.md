# OpesCare Interoperability Connect Suite — API, SDK, Widget, Bridge Agent, OpesCare Lite & Webhooks

**Project:** OpesCare  
**Company:** Opesware  
**Domain:** opescare.com  
**Document Type:** Industry-Leading Interoperability Suite Specification  
**Target Components:** API, SDK, Connect Widget, Bridge Agent, OpesCare Lite, Webhooks, Developer Portal, Integration Certification  
**Primary Stack:** Laravel + PostgreSQL  
**Supporting Stack:** Redis queues/cache, PostGIS where needed, private object storage, OpenAPI, AsyncAPI, optional Python services for mapping/ETL where justified  
**Audience:** Claude Code, Jules, Gemini, Codex, internal engineering team, integration partners, external HIS/LIS/pharmacy/insurance vendors, hospital IT teams  
**Build Strategy:** Upgrade existing API/Connect implementation. Preserve what works. Fill gaps to make OpesCare an industry-leading healthcare interoperability platform.  
**Important Rule:** Do not use or copy OpesHIS OS. Do not copy its database, code, UI, routes, assumptions, or integrations.  
**Critical Rule:** This is not only an API document. This is the complete interoperability ecosystem that allows hospitals, clinics, pharmacies, labs, insurance systems, mobile apps, and third-party systems to securely push, pull, verify, sync, and subscribe to OpesCare data.

---

# 1. Executive Summary

OpesCare must not be just a web application. It must become a healthcare connectivity platform.

The interoperability layer must allow approved systems to:

```text
verify Health IDs
request patient consent
pull authorized patient summaries
push encounter summaries
push prescriptions
push lab results
push documents
verify documents
sync facility records
sync medicine stock
sync blood availability
sync appointments
receive webhooks
embed secure widgets
use SDKs
install a Bridge Agent for legacy/local systems
run OpesCare Lite for small or low-connectivity facilities
```

The six core products are:

```text
1. OpesCare API
2. OpesCare SDKs
3. OpesCare Connect Widget
4. OpesCare Bridge Agent
5. OpesCare Lite
6. OpesCare Webhooks
```

Together they form:

```text
OpesCare Connect Suite
```

The goal is to make OpesCare integration-ready for:

```text
hospital information systems
clinic management systems
laboratory systems
pharmacy systems
insurance systems
public health systems
mobile applications
developer platforms
low-connectivity facilities
legacy local database systems
```

---

# 2. Product Positioning

## 2.1 Correct Positioning

```text
OpesCare Connect Suite is a secure healthcare interoperability layer for Health ID verification, consented patient record exchange, document verification, care coordination, facility data sync, and public health-ready reporting.
```

## 2.2 What It Must Not Claim

```text
It must not claim to replace all hospital systems.
It must not claim to own national health data.
It must not claim to bypass patient consent.
It must not claim to automatically diagnose patients.
It must not claim to guarantee medicine, blood, or emergency availability.
It must not claim that SDKs or widgets automatically authorize clinical practice.
```

## 2.3 Strategic Objective

Make OpesCare easy to integrate in three ways:

```text
API-first for technical teams
Widget-first for quick embedding
Bridge Agent-first for legacy/offline systems
Lite-first for small facilities that need a simple OpesCare-powered interface
```

---

# 3. Industry-Grade Requirements

The interoperability suite must meet these requirements.

## 3.1 Security

```text
OAuth2/OIDC-compatible authorization model or secure scoped token model
API scopes
client credentials flow for server-to-server integrations
authorization code + PKCE where user authorization is required
token rotation
token revocation
mTLS optional for high-trust government/facility integrations
webhook signatures
idempotency keys
rate limits
request IDs
full audit logs
sandbox/production separation
least-privilege data access
minimum necessary data sharing
```

## 3.2 Healthcare Interoperability

Support structured healthcare exchange concepts:

```text
FHIR-compatible resource mapping
Patient
Encounter
Observation
DiagnosticReport
MedicationRequest
MedicationDispense
ServiceRequest
DocumentReference
Consent
Organization
Practitioner
Location
Claim
Coverage
```

Do not force full FHIR implementation on day one, but internal mappings should be designed so FHIR compatibility is possible.

## 3.3 Developer Experience

```text
developer portal
API documentation
OpenAPI specification
Postman collection
SDKs
sample apps
webhook simulator
sandbox data
test credentials
integration checklists
production approval workflow
error code catalog
changelog
status page
```

## 3.4 Reliability

```text
99%+ API availability target during pilot
idempotent write endpoints
retry-safe operations
webhook retries
dead-letter queue
sync conflict detection
Bridge Agent heartbeat
delivery logs
request tracing
clear error responses
```

## 3.5 Compliance and Governance

```text
partner approval
facility verification
developer terms acceptance
data processing agreement
production key approval
API access review
audit logs
incident reporting
data minimization
retention policy
breach notification workflow
```

---

# 4. Connect Suite Architecture

## 4.1 High-Level Architecture

```text
External Systems
    ↓
SDKs / API / Connect Widget / Bridge Agent / OpesCare Lite
    ↓
OpesCare API Gateway
    ↓
Authentication, Scopes, Consent, RBAC, Facility Context
    ↓
Domain Services
    ↓
Patient, Health ID, EMR, Documents, Labs, Prescriptions, Billing, Insurance, Care Map, Public Health
    ↓
Audit, Reconciliation, Notifications, Webhooks
```

## 4.2 Core Layers

```text
Developer Portal
API Gateway
Authentication & Authorization
Consent and Access Policy Engine
FHIR/Internal Mapping Layer
Domain API Layer
Webhook Event Bus
Bridge Agent Sync Layer
Widget Authorization Layer
SDK Packages
Sandbox Environment
Production Certification Layer
Monitoring and Audit Layer
```

## 4.3 Required Module Folders

Recommended Laravel structure:

```text
app/Modules/Connect
app/Modules/ApiGateway
app/Modules/DeveloperPortal
app/Modules/Webhooks
app/Modules/SdkSupport
app/Modules/BridgeAgent
app/Modules/OpesCareLite
app/Modules/IntegrationCertification
app/Modules/FhirMapping
app/Modules/Reconciliation
```

If a different modular structure exists, preserve it and map this design into the existing structure.

---

# 5. Global Non-Negotiable Rules

```text
Do not expose full EMR through unauthenticated routes.
Do not expose patient records from QR alone.
Do not allow API clients without scopes.
Do not allow production API access without approval.
Do not allow widget access without signed initialization.
Do not allow webhooks without signature verification.
Do not allow Bridge Agent sync without facility pairing and device trust.
Do not allow OpesCare Lite to store full patient records offline by default.
Do not allow external systems to overwrite clinical data silently.
Do not allow duplicate patient creation without reconciliation check.
Do not send sensitive data in webhook payloads unless explicitly scoped.
Do not let API error messages leak patient existence unnecessarily.
Do not bypass consent, emergency policy, or facility relationship checks.
```

---

# 6. OpesCare API

## 6.1 Purpose

The OpesCare API is the official interface for approved external systems to communicate with OpesCare.

It must support:

```text
Health ID verification
consent request and status
authorized patient summary access
encounter push
lab result push
prescription push
document verification
facility sync
medicine stock sync
blood availability sync
appointment sync
insurance eligibility/claim integration
public health reporting integration
webhook management
Bridge Agent pairing
OpesCare Lite sync
```

## 6.2 API Principles

```text
versioned
secure
scoped
audited
idempotent
pagination-ready
rate-limited
developer-friendly
FHIR-compatible where practical
backward-compatible within major versions
```

## 6.3 Base URL Structure

```text
Production: https://api.opescare.com/api/v1
Sandbox:    https://sandbox-api.opescare.com/api/v1
```

If using same domain:

```text
https://opescare.com/api/v1
https://sandbox.opescare.com/api/v1
```

## 6.4 API Versioning

Rules:

```text
all public APIs use /api/v1
breaking changes require /api/v2
non-breaking fields can be added to v1
deprecated fields must be marked in docs
deprecation notice must be published
minimum 6-month deprecation period for production integrations where possible
```

## 6.5 Authentication Models

Support three auth modes.

### 6.5.1 Server-to-Server Client Credentials

For trusted facility/lab/pharmacy/insurance systems.

```text
client_id
client_secret
scopes
facility_id
organization_id
environment
```

### 6.5.2 Authorization Code + PKCE

For user-authorized flows where a patient or provider grants access.

```text
authorize endpoint
callback URL
PKCE verifier/challenge
consent screen
short-lived access token
refresh token where policy allows
```

### 6.5.3 Signed Widget Session Token

For Connect Widget sessions.

```text
widget_client_id
signed initialization payload
nonce
origin
expires_at
scopes
```

## 6.6 API Scopes

Required scope families:

```text
health_id.verify
patients.read_summary
patients.read_timeline
patients.write_demographics
consent.request
consent.read_status
encounters.write
observations.write
lab_orders.write
lab_results.write
prescriptions.write
documents.issue
documents.verify
appointments.read
appointments.write
pharmacy.stock.write
blood.availability.write
care_map.facilities.read
insurance.eligibility.check
insurance.claims.write
public_health.reports.write
webhooks.manage
bridge_agent.sync
lite.sync
```

## 6.7 Scope Rules

```text
Every endpoint must require at least one scope.
Scopes must be environment-specific.
Production scopes must be approved.
Scopes must be shown in developer portal.
Scope changes must be audited.
High-risk scopes require manual approval.
```

High-risk scopes:

```text
patients.read_timeline
encounters.write
lab_results.write
prescriptions.write
insurance.claims.write
public_health.reports.write
bridge_agent.sync
lite.sync
```

## 6.8 Request Headers

Required:

```http
Authorization: Bearer <access_token>
X-Request-ID: <uuid>
Idempotency-Key: <uuid> for write operations
Content-Type: application/json
Accept: application/json
```

Optional:

```http
X-Facility-ID: <facility_uuid>
X-Organization-ID: <organization_uuid>
X-Client-Version: <sdk_or_agent_version>
X-Environment: sandbox|production
```

## 6.9 Standard Success Response

```json
{
  "success": true,
  "data": {},
  "message": "Operation completed",
  "meta": {
    "request_id": "uuid",
    "api_version": "v1"
  }
}
```

## 6.10 Standard Error Response

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable message",
    "details": {},
    "request_id": "uuid"
  }
}
```

## 6.11 Standard Error Codes

```text
AUTHENTICATION_REQUIRED
INVALID_TOKEN
TOKEN_EXPIRED
INSUFFICIENT_SCOPE
FACILITY_CONTEXT_REQUIRED
FACILITY_ACCESS_DENIED
PATIENT_NOT_FOUND
PATIENT_ACCESS_DENIED
CONSENT_REQUIRED
CONSENT_DENIED
CONSENT_EXPIRED
EMERGENCY_REASON_REQUIRED
RESOURCE_NOT_FOUND
VALIDATION_FAILED
IDEMPOTENCY_CONFLICT
DUPLICATE_PATIENT_CANDIDATE
RECONCILIATION_REQUIRED
RATE_LIMIT_EXCEEDED
WEBHOOK_SIGNATURE_INVALID
BRIDGE_AGENT_NOT_PAIRED
SYNC_CONFLICT
PRODUCTION_ACCESS_NOT_APPROVED
```

## 6.12 Pagination

Use cursor pagination for large datasets.

```http
GET /api/v1/patients?cursor=abc&limit=50
```

Response:

```json
{
  "success": true,
  "data": [],
  "meta": {
    "next_cursor": "abc",
    "has_more": true,
    "limit": 50
  }
}
```

## 6.13 Idempotency

All write endpoints must support `Idempotency-Key`.

Applies to:

```text
patient creation
encounter push
lab result push
prescription push
document issue
appointment creation
claim submission
stock sync
blood availability sync
public health report submission
Bridge Agent sync
OpesCare Lite sync
```

Rules:

```text
same key + same payload returns same result
same key + different payload returns IDEMPOTENCY_CONFLICT
idempotency records expire after configurable period
idempotency usage is audited for high-risk endpoints
```

## 6.14 Rate Limits

Default limits:

```text
sandbox: generous but protected
production normal client: 600 requests/minute
production high-trust client: negotiated
webhook management endpoints: stricter
document verification public endpoints: protected by abuse rules
```

Rate limits must be per:

```text
client
IP
facility
scope family
endpoint risk level
```

## 6.15 API Audit Requirements

Audit every sensitive API call:

```text
client_id
organization_id
facility_id
actor_user_id nullable
patient_id nullable
scope
endpoint
method
request_id
ip_address
user_agent
status_code
resource_type
resource_id
created_at
```

Do not store full sensitive request body in logs by default.

---

# 7. API Endpoint Catalog

## 7.1 Developer and Auth

```text
POST /api/v1/oauth/token
POST /api/v1/oauth/revoke
GET  /api/v1/me
GET  /api/v1/scopes
GET  /api/v1/health
```

## 7.2 Health ID

```text
POST /api/v1/health-ids/verify
GET  /api/v1/health-ids/{health_id}/status
POST /api/v1/health-ids/{health_id}/emergency-access
```

## 7.3 Consent

```text
POST /api/v1/consents/requests
GET  /api/v1/consents/requests/{id}
GET  /api/v1/consents/status
POST /api/v1/consents/{id}/revoke
```

## 7.4 Patients

```text
GET  /api/v1/patients/{id}/summary
GET  /api/v1/patients/{id}/timeline
POST /api/v1/patients
PATCH /api/v1/patients/{id}
GET  /api/v1/patients/search
```

## 7.5 Encounters

```text
POST /api/v1/encounters
GET  /api/v1/encounters/{id}
PATCH /api/v1/encounters/{id}
POST /api/v1/encounters/{id}/close
```

## 7.6 Observations and Vitals

```text
POST /api/v1/observations
GET  /api/v1/patients/{id}/observations
```

## 7.7 Lab

```text
POST /api/v1/lab/orders
POST /api/v1/lab/results
GET  /api/v1/lab/results/{id}
POST /api/v1/lab/results/{id}/amend
```

## 7.8 Prescriptions

```text
POST /api/v1/prescriptions
GET  /api/v1/prescriptions/{id}
POST /api/v1/prescriptions/{id}/cancel
POST /api/v1/prescriptions/{id}/dispense
```

## 7.9 Documents

```text
POST /api/v1/documents
GET  /api/v1/documents/{id}
POST /api/v1/documents/verify
GET  /api/v1/documents/{id}/download
POST /api/v1/documents/{id}/revoke
POST /api/v1/documents/{id}/amend
```

## 7.10 Appointments

```text
GET  /api/v1/appointments
POST /api/v1/appointments
GET  /api/v1/appointments/{id}
PATCH /api/v1/appointments/{id}
POST /api/v1/appointments/{id}/check-in
POST /api/v1/appointments/{id}/cancel
```

## 7.11 Pharmacy Stock

```text
POST /api/v1/pharmacy/stock/sync
GET  /api/v1/pharmacy/stock/search
POST /api/v1/pharmacy/reservations
```

## 7.12 Blood Availability

```text
POST /api/v1/blood/availability/sync
GET  /api/v1/blood/availability/search
POST /api/v1/blood/requests
```

## 7.13 Insurance

```text
POST /api/v1/insurance/eligibility/check
POST /api/v1/insurance/preauthorizations
POST /api/v1/insurance/claims
GET  /api/v1/insurance/claims/{id}
POST /api/v1/insurance/claims/{id}/decision
```

## 7.14 Public Health

```text
POST /api/v1/public-health/reports
GET  /api/v1/public-health/reports/{id}
POST /api/v1/public-health/reports/{id}/submit
```

## 7.15 Webhooks

```text
GET    /api/v1/webhooks/subscriptions
POST   /api/v1/webhooks/subscriptions
PATCH  /api/v1/webhooks/subscriptions/{id}
DELETE /api/v1/webhooks/subscriptions/{id}
POST   /api/v1/webhooks/subscriptions/{id}/test
GET    /api/v1/webhooks/deliveries
POST   /api/v1/webhooks/deliveries/{id}/replay
```

## 7.16 Bridge Agent

```text
POST /api/v1/bridge/pair
POST /api/v1/bridge/heartbeat
POST /api/v1/bridge/sync/push
GET  /api/v1/bridge/sync/pull
POST /api/v1/bridge/conflicts/{id}/resolve
POST /api/v1/bridge/logs
```

## 7.17 OpesCare Lite

```text
POST /api/v1/lite/register-device
POST /api/v1/lite/sync/push
GET  /api/v1/lite/sync/pull
GET  /api/v1/lite/config
POST /api/v1/lite/offline-events
```

---

# 8. API Data Safety Rules

## 8.1 Patient Summary

Patient summary may include only approved fields:

```text
patient display name
Health ID
age or date of birth depending policy
sex/gender where clinically relevant
known allergies
active conditions summary
active medications summary
recent encounters summary
emergency contacts where authorized
source attribution
```

Must not include:

```text
full clinical timeline without authorization
private notes
unrelated facility records
insurance claims unrelated to requester
support tickets
private messages
```

## 8.2 Lab Result Push

External systems pushing lab results must provide:

```text
patient identifier
order identifier if available
test name
test code
result value
unit
reference range
abnormal flag
performing lab
validator
result timestamp
status
source system
idempotency key
```

Lab results must not overwrite existing released results silently.

## 8.3 Prescription Push

External systems pushing prescriptions must provide:

```text
patient identifier
prescriber
facility
medicine name
generic name if available
dose
frequency
duration
route
instructions
status
issued_at
source system
idempotency key
```

Prescription actions must check prescriber authority where possible.

## 8.4 Document Issue

External systems issuing documents must provide:

```text
document type
patient
facility
issuer
payload
template
signature/authorization evidence
status
```

All official documents must receive:

```text
document number
verification code
QR token
hash
status
audit event
```

---

# 9. Developer Portal

## 9.1 Purpose

The Developer Portal is the control center for third-party developers and integration partners.

## 9.2 Required Features

```text
developer registration
organization profile
app creation
sandbox credentials
production access request
API key/token management
scope request and approval
webhook subscription management
webhook delivery logs
test webhook button
API docs
OpenAPI spec download
Postman collection download
SDK download links
sample code
integration checklist
certification test suite
support tickets
usage dashboard
rate limit dashboard
audit logs
status page
changelog
```

## 9.3 Developer App Statuses

```text
draft
sandbox_active
production_requested
under_review
production_approved
production_rejected
suspended
revoked
```

## 9.4 Production Approval Flow

1. Developer creates sandbox app.
2. Developer tests required endpoints.
3. Developer completes integration checklist.
4. Developer accepts Developer/API Terms.
5. Developer submits production request.
6. OpesCare reviews:
   - organization identity
   - facility/partner relationship
   - scopes requested
   - security checklist
   - webhook endpoint security
   - data processing agreement if needed
7. Approval grants production credentials.
8. Approval is audited.

## 9.5 Integration Certification

Certification test suite must verify:

```text
authentication works
scopes enforced
idempotency used
webhook signatures verified
retries handled
patient access consent respected
errors handled correctly
rate limits respected
PHI not logged improperly
production callback URL secure
```

## 9.6 Developer Portal Models

```text
DeveloperAccount
DeveloperOrganization
DeveloperApp
ApiCredential
ApiScopeGrant
ProductionAccessRequest
IntegrationCertificationRun
ApiUsageMetric
DeveloperSupportTicket
```

---

# 10. OpesCare SDKs

## 10.1 Purpose

SDKs make OpesCare easy to integrate by reducing developer mistakes.

The SDKs must handle:

```text
authentication
request signing where applicable
scopes
idempotency
request IDs
retries
pagination
typed errors
webhook signature verification
FHIR/internal mapping helpers
sandbox configuration
production configuration
```

## 10.2 Required SDK Packages

Minimum:

```text
PHP SDK
JavaScript / TypeScript SDK
Python SDK
```

Later:

```text
Dart / Flutter SDK
Java SDK
C# SDK
```

## 10.3 SDK Naming

```text
opescare-php
opescare-js
opescare-python
opescare-dart
```

## 10.4 SDK Modules

Each SDK must include:

```text
Client
Auth
HealthIds
Consents
Patients
Encounters
Lab
Prescriptions
Documents
Appointments
Pharmacy
Blood
Insurance
PublicHealth
Webhooks
BridgeAgent
Lite
Errors
Retries
Pagination
Idempotency
```

## 10.5 SDK Initialization

Example conceptual structure:

```text
client = OpesCareClient(
    client_id,
    client_secret,
    environment="sandbox",
    facility_id="..."
)
```

## 10.6 SDK Authentication Helper

Must support:

```text
client credentials token request
token refresh if applicable
token caching
token expiry detection
token revocation helper
```

## 10.7 SDK Idempotency Helper

For write operations:

```text
auto-generate idempotency key
allow manual idempotency key
store idempotency key in request metadata
surface idempotency conflict clearly
```

## 10.8 SDK Error Handling

SDK must map API errors into typed exceptions/errors:

```text
AuthenticationError
AuthorizationError
ConsentRequiredError
ValidationError
RateLimitError
IdempotencyConflictError
ReconciliationRequiredError
SyncConflictError
WebhookSignatureError
```

## 10.9 SDK Webhook Verification Helper

Must provide:

```text
verify_signature(payload, signature_header, secret)
parse_event(payload)
detect_replay(timestamp, tolerance)
```

## 10.10 SDK Retry Rules

Retry only safe cases:

```text
network timeout
5xx response
429 with retry-after
webhook replay-safe request
```

Do not retry blindly:

```text
validation errors
authorization errors
consent errors
idempotency conflicts
```

## 10.11 SDK Documentation

Each SDK must include:

```text
installation
quick start
authentication
Health ID verification example
consent request example
push lab result example
push prescription example
verify document example
webhook verification example
Bridge Agent helper example
error handling
sandbox usage
production checklist
```

## 10.12 SDK Testing

Each SDK must have:

```text
unit tests
mock API tests
auth tests
webhook signature tests
retry tests
idempotency tests
pagination tests
error mapping tests
```

## 10.13 SDK Release Process

```text
semantic versioning
changelog
GitHub releases
package registry publishing
deprecation notices
compatibility matrix
security patch process
```

## 10.14 SDK Acceptance Criteria

SDK is industry-ready when:

```text
all required packages exist
docs exist
examples exist
tests pass
versioning exists
webhook verification exists
idempotency exists
typed errors exist
production sample app exists
```

---

# 11. OpesCare Connect Widget

## 11.1 Purpose

The Connect Widget lets third-party systems embed secure OpesCare workflows without building full API integrations.

It must support:

```text
Health ID verification
patient consent request
record sharing request
document verification
appointment handoff
lab result handoff
prescription handoff
facility identity verification
```

## 11.2 Widget Use Cases

```text
Hospital system wants to verify Health ID inside its UI.
Clinic system wants to request patient consent.
Pharmacy wants to verify prescription QR.
Lab wants to verify lab order and push result.
Insurance portal wants to verify claim document.
Third-party patient portal wants to let patient connect OpesCare account.
```

## 11.3 Widget Integration Modes

```text
JavaScript embed
iframe embed with strict security
redirect flow
popup flow
server-generated signed session
```

Recommended default:

```text
JavaScript bootstrap + hosted secure iframe
```

This allows OpesCare to control sensitive UI while partner systems embed it.

## 11.4 Widget Security Rules

```text
registered origins only
signed initialization payload
nonce required
short expiry
CSP restrictions
sandboxed iframe
postMessage origin validation
no full EMR in widget by default
scoped widget sessions
session timeout
audit every widget action
```

## 11.5 Widget Initialization

Partner backend requests widget session:

```http
POST /api/v1/widgets/sessions
```

Payload:

```json
{
  "widget_type": "health_id_verify",
  "facility_id": "uuid",
  "allowed_origin": "https://partner-system.com",
  "scopes": ["health_id.verify"],
  "expires_in": 600,
  "language": "en"
}
```

Response:

```json
{
  "widget_session_token": "short_lived_token",
  "embed_url": "https://connect.opescare.com/widget/session/...",
  "expires_at": "..."
}
```

## 11.6 Widget Types

```text
health_id_verify
consent_request
record_share_request
document_verify
prescription_verify
lab_order_verify
patient_connect
appointment_handoff
```

## 11.7 Widget Events

Widget sends events to parent page through postMessage:

```text
opescare.widget.ready
opescare.health_id.verified
opescare.consent.requested
opescare.consent.granted
opescare.document.verified
opescare.prescription.verified
opescare.widget.cancelled
opescare.widget.error
```

## 11.8 Widget UI Requirements

```text
OpesCare branding
partner facility name
clear patient consent wording
English/French language support
mobile responsive
accessibility basics
loading state
error state
cancel flow
session expiry message
```

## 11.9 Widget Audit Events

```text
widget_session_created
widget_loaded
widget_health_id_verified
widget_consent_requested
widget_consent_granted
widget_document_verified
widget_session_expired
widget_origin_rejected
```

## 11.10 Widget Tests

```text
unregistered origin blocked
expired session blocked
tampered token blocked
postMessage origin validation works
Health ID verification widget works
consent widget works
document verification widget works
French labels render
widget actions audited
```

## 11.11 Widget Acceptance Criteria

The Widget is industry-ready when:

```text
secure embed works
origins are enforced
sessions are short-lived
all widget actions audited
no full EMR leaks
developer docs exist
examples exist
sandbox widget works
```

---

# 12. OpesCare Webhooks

## 12.1 Purpose

Webhooks notify approved external systems when important OpesCare events happen.

They must be reliable, signed, replayable, auditable, and scoped.

## 12.2 Webhook Principles

```text
signed payloads
scoped subscriptions
event versioning
delivery retries
dead-letter queue
manual replay
delivery logs
idempotent event IDs
minimal payloads by default
payload redaction where sensitive
test events
sandbox simulator
```

## 12.3 Webhook Event Format

```json
{
  "id": "evt_123",
  "type": "patient.created",
  "version": "1.0",
  "created_at": "2026-05-19T10:00:00Z",
  "data": {},
  "meta": {
    "organization_id": "uuid",
    "facility_id": "uuid",
    "environment": "sandbox",
    "request_id": "uuid"
  }
}
```

## 12.4 Webhook Headers

```http
X-OpesCare-Event-ID: evt_123
X-OpesCare-Event-Type: patient.created
X-OpesCare-Signature: t=timestamp,v1=signature
X-OpesCare-Delivery-ID: whd_123
X-OpesCare-Retry-Count: 0
```

## 12.5 Signature Rule

Signature should be HMAC-based over:

```text
timestamp + "." + raw_payload
```

Receiver must verify:

```text
timestamp tolerance
signature match
event replay protection
known endpoint secret
```

## 12.6 Webhook Events

Required event catalog:

```text
patient.created
patient.updated
health_id.created
health_id.verified
consent.requested
consent.granted
consent.denied
consent.revoked
encounter.created
encounter.closed
lab_order.created
lab_result.released
lab_result.amended
prescription.issued
prescription.cancelled
prescription.dispensed
document.issued
document.verified
document.revoked
appointment.created
appointment.checked_in
appointment.cancelled
invoice.issued
payment.recorded
receipt.generated
claim.submitted
claim.decided
public_health_report.submitted
pharmacy_stock.updated
blood_availability.updated
reconciliation.case_created
bridge_agent.sync_failed
lite.sync_conflict
```

## 12.7 Payload Sensitivity Levels

```text
metadata_only
limited_summary
sensitive_payload
```

Default should be:

```text
metadata_only
```

Sensitive payloads require explicit scope approval.

## 12.8 Delivery Retry Policy

```text
initial attempt immediately
retry after 1 minute
retry after 5 minutes
retry after 15 minutes
retry after 1 hour
retry after 6 hours
retry after 24 hours
move to dead-letter after max attempts
```

## 12.9 Webhook Delivery Statuses

```text
pending
delivered
failed
retrying
dead_letter
replayed
disabled
```

## 12.10 Manual Replay

Developer portal must allow replay if:

```text
event belongs to client
subscription is active
replay window is valid
actor has permission
```

Replay is audited.

## 12.11 Webhook Subscription Model

```text
WebhookSubscription
WebhookEndpoint
WebhookSecret
WebhookDelivery
WebhookEvent
WebhookReplay
WebhookDeadLetter
```

## 12.12 Webhook Tests

```text
event is created
payload signed
invalid signature rejected by SDK helper
delivery success recorded
delivery failure retried
dead-letter created
manual replay works
scopes filter events
sensitive payload blocked without approval
test webhook works
```

## 12.13 Webhook Acceptance Criteria

Webhooks are industry-ready when:

```text
event catalog exists
signed delivery works
retries work
dead-letter works
manual replay works
developer dashboard works
SDK verifier exists
AsyncAPI docs exist
sandbox simulator exists
```

---

# 13. OpesCare Bridge Agent

## 13.1 Purpose

The Bridge Agent connects local/legacy hospital, clinic, lab, pharmacy, and facility systems to OpesCare.

It is designed for environments where:

```text
existing systems run locally
systems have local databases
internet is unstable
staff cannot build full API integrations
CSV exports are the only available method
facility wants controlled sync to OpesCare
```

## 13.2 Bridge Agent Form Factor

Recommended options:

```text
Docker container
Windows service
Linux service
lightweight desktop installer
facility appliance later
```

## 13.3 Bridge Agent Core Functions

```text
facility pairing
device registration
local configuration
source connector setup
data mapping
secure local queue
sync push
sync pull
retry logic
conflict detection
logs and diagnostics
heartbeat
auto-update
remote disable/revoke
```

## 13.4 Connector Types

Start with:

```text
CSV folder watcher
REST API connector
PostgreSQL connector
MySQL connector
MSSQL connector
manual upload connector
```

Later:

```text
HL7 v2 connector
FHIR connector
DHIS2 connector
custom vendor connectors
```

## 13.5 Facility Pairing Flow

1. Facility admin generates Bridge pairing code in OpesCare.
2. IT staff installs Bridge Agent.
3. Bridge Agent asks for pairing code.
4. Agent sends device fingerprint and environment info.
5. OpesCare validates code.
6. Facility admin approves device.
7. Agent receives scoped credentials.
8. Agent begins heartbeat.
9. Audit event `bridge_agent_paired` is created.

## 13.6 Source Mapping Flow

1. Admin selects connector type.
2. Agent connects to local source.
3. Agent reads schema/sample data.
4. User maps local fields to OpesCare fields.
5. Mapping is saved.
6. Test sync validates mapping.
7. Mapping approval is logged.

## 13.7 Sync Push Flow

1. Agent detects new/updated local data.
2. Agent transforms data into OpesCare sync format.
3. Agent adds idempotency key.
4. Agent queues data locally.
5. Agent pushes to OpesCare.
6. OpesCare validates and checks reconciliation.
7. Success/failure returned.
8. Agent updates local sync status.
9. Audit event is created.

## 13.8 Sync Pull Flow

1. Agent requests authorized updates from OpesCare.
2. OpesCare checks facility and scopes.
3. Agent receives allowed updates.
4. Agent applies local write only if connector supports safe writeback.
5. Conflicts create review cases.
6. Sync event is audited.

## 13.9 Offline Handling

If internet is unavailable:

```text
agent queues outbound events
agent keeps heartbeat failure logs
agent retries when online
agent does not delete queued records until confirmed
agent protects queue with encryption
```

## 13.10 Conflict Handling

Conflicts occur when:

```text
patient match uncertain
external record duplicates existing patient
same record changed in both systems
mapping invalid
required data missing
external system sends invalid update
```

Conflict statuses:

```text
open
under_review
resolved_keep_opescare
resolved_accept_external
resolved_manual_merge
rejected
```

## 13.11 Bridge Agent Dashboard

Must show:

```text
paired agents
agent version
facility
last heartbeat
sync status
queued records
failed records
conflicts
connector status
logs
update availability
remote disable button
```

## 13.12 Bridge Agent Security Rules

```text
credentials stored encrypted
pairing codes expire
agent scoped to facility
remote revocation supported
logs must not expose full patient data
local queue encrypted
agent updates signed
connector secrets encrypted
```

## 13.13 Bridge Agent Models

```text
BridgeAgent
BridgeDevice
BridgePairingCode
BridgeConnector
BridgeMapping
BridgeSyncJob
BridgeSyncRecord
BridgeConflict
BridgeHeartbeat
BridgeLog
BridgeVersion
```

## 13.14 Bridge Agent Tests

```text
pairing code expires
agent pairing works
unapproved agent blocked
heartbeat recorded
CSV watcher detects file
mapping validation works
sync push works
idempotency works
failed sync retries
conflict created
remote disable blocks sync
logs redact sensitive data
```

## 13.15 Bridge Agent Acceptance Criteria

Bridge Agent is industry-ready when:

```text
installable agent exists
pairing works
connectors work
local queue encrypted
sync works
conflicts handled
dashboard exists
remote revocation works
update strategy exists
logs and diagnostics exist
tests pass
```

---

# 14. OpesCare Lite

## 14.1 Purpose

OpesCare Lite is a lightweight facility-facing client for small clinics, pharmacies, labs, health centers, mobile clinics, and low-connectivity environments.

It is not a separate platform. It is a simplified OpesCare client connected to the main OpesCare cloud and sync architecture.

## 14.2 Product Definition

```text
OpesCare Lite = a lightweight operational client that allows small facilities to perform essential care workflows with minimal infrastructure while syncing to OpesCare.
```

## 14.3 Target Users

```text
small clinics
rural health centers
pharmacies
small labs
mobile clinics
school clinics
NGO health teams
outreach teams
```

## 14.4 Core Features

```text
Health ID lookup
basic patient registration
basic appointment/walk-in
simple queue/check-in
basic consultation note
basic vitals
basic prescription
basic lab request/result
basic billing/receipt
document QR generation
medicine stock update
blood availability update if applicable
offline-limited data capture
sync to OpesCare cloud
low-bandwidth mode
tablet/mobile responsive UI
```

## 14.5 What Lite Must Not Become

```text
It must not become a disconnected second database platform.
It must not bypass OpesCare consent/access rules.
It must not store full EMR offline by default.
It must not allow unapproved clinical authority.
It must not create incompatible data models.
```

## 14.6 Deployment Options

```text
responsive web app mode
progressive web app mode
tablet-first mode
local-lite appliance later
Flutter app later
```

Recommended first version:

```text
Laravel responsive web/PWA mode using same backend and API contracts
```

## 14.7 Lite Modes

```text
Online Mode
Low-Bandwidth Mode
Offline-Limited Mode
```

## 14.8 Lite Online Mode

Uses standard OpesCare APIs and backend.

Supports:

```text
patient lookup
visit creation
consultation note
prescription
billing
document generation
sync immediately
```

## 14.9 Lite Low-Bandwidth Mode

Optimizations:

```text
reduced payloads
compressed responses
minimal UI assets
pagination
background sync
avoid heavy dashboards
```

## 14.10 Lite Offline-Limited Mode

Allowed actions:

```text
draft patient registration
draft vitals
draft consultation note
draft billing receipt pending sync
draft medicine stock update
```

Blocked actions offline by default:

```text
full EMR access
insurance claim submission
public health submission
final official document issuance
broad patient search
non-emergency consent expansion
```

## 14.11 Lite Sync Flow

1. Lite device registers with facility.
2. Device receives allowed modules/config.
3. User works online or offline-limited.
4. Offline actions enter local encrypted queue.
5. When online, Lite syncs queued records.
6. Server validates access, consent, and conflicts.
7. Conflicts go to reconciliation.
8. User sees sync status.

## 14.12 Lite Device Registration

Required:

```text
device name
facility
authorized user
device fingerprint
environment
approved modules
last seen
status
```

Statuses:

```text
pending
active
suspended
revoked
lost
```

## 14.13 Lite UI Requirements

```text
simple dashboard
large buttons
low-bandwidth-friendly
English/French
offline indicator
sync status indicator
Health ID scan/search
today's visits
basic patient card
basic consultation form
basic prescription form
basic billing form
document QR view
```

## 14.14 Lite Models

```text
LiteDevice
LiteDeviceRegistration
LiteConfig
LiteSyncJob
LiteOfflineEvent
LiteConflict
LiteModuleEntitlement
```

## 14.15 Lite Tests

```text
device registration works
unapproved device blocked
Lite config fetched
offline event queued
sync push works
conflict created
full EMR blocked offline
official document blocked offline until sync
low-bandwidth response works
French labels render
```

## 14.16 Lite Acceptance Criteria

OpesCare Lite is industry-ready when:

```text
small facility can use core workflows
Lite uses same backend rules
offline-limited mode is safe
sync works
conflicts handled
device management works
low-bandwidth UI works
tests pass
```

---

# 15. FHIR / Healthcare Data Mapping Layer

## 15.1 Purpose

OpesCare should maintain internal data models but map them to healthcare interoperability standards where useful.

## 15.2 Required Resource Mapping

Map internal records to:

```text
Patient
Practitioner
Organization
Location
Encounter
Observation
DiagnosticReport
ServiceRequest
MedicationRequest
MedicationDispense
DocumentReference
Consent
Coverage
Claim
```

## 15.3 Mapping Rules

```text
internal model remains source of truth
FHIR mapping layer transforms data
mapping version stored
source system stored
coding systems stored where available
local codes allowed but mapped where possible
unmapped codes flagged for data quality
```

## 15.4 Code Systems

Support:

```text
local facility codes
LOINC where available for lab tests
ICD-10 where available for diagnoses
ATC or local medicine codes where available
GTIN where available for medicine/product codes
```

## 15.5 FHIR Mapping Models

```text
FhirMapping
FhirResourceReference
CodeSystemMapping
ExternalIdentifier
MappingError
```

## 15.6 Tests

```text
patient maps to Patient resource
lab result maps to Observation/DiagnosticReport
prescription maps to MedicationRequest
document maps to DocumentReference
consent maps to Consent
unmapped code creates mapping issue
```

---

# 16. Reconciliation and Data Quality for Integrations

## 16.1 Purpose

External systems will send imperfect data. OpesCare must detect duplicates, conflicts, and unsafe updates.

## 16.2 Reconciliation Triggers

```text
patient duplicate candidate
uncertain Health ID match
external record without patient ID
conflicting demographic data
conflicting lab result
duplicate prescription
same document submitted twice
mapping code unknown
Bridge Agent sync conflict
Lite sync conflict
```

## 16.3 Reconciliation Flow

1. External data arrives.
2. System checks matching confidence.
3. If confidence is high, data is linked.
4. If confidence is low, reconciliation case is created.
5. Data steward reviews.
6. Decision is audited.
7. External system receives status or webhook.

## 16.4 Reconciliation Statuses

```text
open
under_review
resolved_matched
resolved_created_new
resolved_merged
rejected
needs_more_information
```

## 16.5 No Silent Overwrite Rule

No external system can silently overwrite:

```text
patient identity
released lab result
final consultation note
issued prescription
official document
billing record
insurance claim decision
```

Amendment/versioning is required.

---

# 17. Sandbox Environment

## 17.1 Purpose

Sandbox allows partners to test without real patient data.

## 17.2 Sandbox Requirements

```text
separate environment
fake data only
test patients
test Health IDs
test facilities
test documents
test webhooks
test Bridge Agent pairing
test Lite device
reset capability
API usage metrics
clear sandbox labeling
```

## 17.3 Sandbox Data

Provide:

```text
demo patient
demo doctor
demo hospital
demo lab
demo pharmacy
demo insurer
demo Health ID
demo lab result
demo prescription
demo document
demo claim
demo webhook events
```

## 17.4 Sandbox Reset

Developers can reset sandbox app data.

Rules:

```text
reset does not affect production
reset is audited
sandbox IDs marked clearly
```

---

# 18. Production Integration Certification

## 18.1 Purpose

No external system should go live without certification.

## 18.2 Certification Checklist

```text
developer terms accepted
DPA signed where required
organization verified
facility relationship verified
scopes approved
sandbox tests completed
webhook signature verification implemented
idempotency implemented
error handling verified
rate limit behavior verified
data minimization verified
security contact provided
production callback URLs verified
incident contact provided
```

## 18.3 Automated Certification Tests

```text
auth test
scope denial test
Health ID verify test
consent request test
patient summary permission test
lab result push test
prescription push test
webhook delivery test
webhook retry test
idempotency test
rate limit test
invalid payload test
```

## 18.4 Certification Statuses

```text
not_started
in_progress
passed
failed
production_approved
suspended
revoked
```

---

# 19. Monitoring and Observability

## 19.1 Required Dashboards

```text
API health
API latency
error rate
rate limit hits
auth failures
webhook delivery
webhook dead letters
Bridge Agent heartbeat
Bridge sync failures
Lite sync failures
reconciliation cases
SDK version usage
top endpoints
production client activity
suspicious access
```

## 19.2 Alerts

```text
API down
error rate high
webhook failures high
Bridge Agent offline
Lite sync conflicts high
reconciliation backlog high
auth failures spike
rate limit abuse
sensitive endpoint access anomaly
```

## 19.3 Required Logs

```text
request_id
client_id
facility_id
endpoint
status
latency
scope
actor
resource
error_code
```

Do not log full patient payloads.

---

# 20. Public Developer Documentation

## 20.1 Required Docs

```text
Getting Started
Authentication
Scopes
Environments
Health ID API
Consent API
Patient API
Lab API
Prescription API
Documents API
Appointments API
Pharmacy Stock API
Blood Availability API
Insurance API
Public Health API
Webhooks
Bridge Agent
OpesCare Lite
Connect Widget
SDKs
Errors
Rate Limits
Idempotency
FHIR Mapping
Sandbox
Production Approval
Changelog
Status Page
```

## 20.2 Required Examples

```text
verify Health ID
request consent
pull patient summary
push lab result
push prescription
issue document
verify document
subscribe to webhook
verify webhook signature
install Bridge Agent
embed Connect Widget
register Lite device
```

---

# 21. Security Checklist for Connect Suite

```text
API tokens hashed
client secrets never shown again after creation
production apps require approval
scopes required for every endpoint
rate limits enabled
idempotency on writes
audit logs for sensitive calls
webhook signatures implemented
webhook replay protection implemented
widget origin validation implemented
widget session expiry implemented
Bridge Agent credentials encrypted
Bridge Agent remote revoke works
Lite offline cache encrypted
Lite full EMR offline blocked by default
sandbox and production separated
developer terms acceptance required
DPA required where applicable
public docs do not reveal secrets
logs redact sensitive data
```

---

# 22. Implementation Roadmap

## 22.1 Phase 1 — API Hardening

```text
API versioning
scope matrix
error catalog
idempotency
rate limits
OpenAPI docs
developer portal MVP
audit logs
sandbox credentials
```

## 22.2 Phase 2 — Webhooks and SDKs

```text
event catalog
signed webhooks
delivery retries
dead-letter queue
manual replay
PHP SDK
JS/TS SDK
Python SDK
webhook verifier helpers
sample apps
```

## 22.3 Phase 3 — Connect Widget

```text
widget sessions
origin validation
Health ID verify widget
consent widget
document verify widget
postMessage events
sandbox demo
widget docs
```

## 22.4 Phase 4 — Bridge Agent

```text
agent pairing
heartbeat
CSV watcher
REST connector
database connector
mapping UI
encrypted queue
sync push/pull
conflict handling
dashboard
remote revoke
```

## 22.5 Phase 5 — OpesCare Lite

```text
Lite device registration
Lite config
Lite UI
basic patient workflow
offline-limited queue
sync push/pull
conflict handling
low-bandwidth mode
```

## 22.6 Phase 6 — Certification and Marketplace

```text
integration certification
partner app listings
approved integrations directory
developer analytics
API usage billing optional
```

---

# 23. Claude Code / Jules Implementation Tasks

## 23.1 Task 1 — API Hardening

```text
Audit existing IntegrationClient, WebhookSubscription, ConnectPlatformTest, API routes, tokens, scopes, and developer portal code.
Do not duplicate existing implementation.
Add missing API versioning, scope matrix, rate limits, idempotency middleware, request IDs, error catalog, OpenAPI spec, and API audit logs.
Add tests for scope denial, idempotency conflict, rate limiting, request ID, and audit logs.
```

## 23.2 Task 2 — Developer Portal

```text
Create or upgrade developer portal with app creation, sandbox keys, production request, scopes, webhook management, API docs, usage metrics, integration certification status, support tickets, and changelog.
```

## 23.3 Task 3 — Webhooks

```text
Implement event catalog, signed payloads, delivery queue, retry policy, dead-letter queue, manual replay, delivery dashboard, test webhook button, and SDK verification helpers.
```

## 23.4 Task 4 — SDK Packages

```text
Create SDK specs and package skeletons for PHP, JavaScript/TypeScript, and Python.
Include auth, requests, idempotency, typed errors, pagination, webhook signature verification, examples, tests, and release workflow.
```

## 23.5 Task 5 — Connect Widget

```text
Implement widget session API, signed initialization, registered origins, hosted iframe, Health ID verification widget, consent widget, document verification widget, postMessage events, theme/language settings, and widget audit logs.
```

## 23.6 Task 6 — Bridge Agent

```text
Implement Bridge Agent pairing, device registry, heartbeat, connector definitions, CSV watcher MVP, REST connector MVP, mapping system, encrypted local queue design, sync push/pull endpoints, conflict creation, dashboard, logs, and remote revoke.
```

## 23.7 Task 7 — OpesCare Lite

```text
Implement OpesCare Lite device registration, config endpoint, Lite UI shell, low-bandwidth mode, offline-limited event queue, sync push/pull endpoints, conflict handling, and core workflows for small facilities.
```

## 23.8 Task 8 — Certification

```text
Implement integration certification tests: auth, scopes, Health ID verify, consent, patient summary, lab result push, prescription push, webhook delivery, idempotency, rate limits, invalid payload, and data minimization.
```

---

# 24. Test Matrix

## 24.1 API Tests

```text
all endpoints require auth unless public by design
all endpoints require scopes
facility boundary enforced
consent required where applicable
idempotency works
rate limits work
error format consistent
audit logs created
```

## 24.2 SDK Tests

```text
auth works
token cache works
request ID added
idempotency key added
pagination works
typed errors map correctly
webhook signature verification works
retry rules work
```

## 24.3 Widget Tests

```text
registered origin accepted
unregistered origin rejected
expired session rejected
tampered token rejected
postMessage origin validated
Health ID verify works
consent request works
document verify works
```

## 24.4 Webhook Tests

```text
event created
payload signed
delivery succeeds
delivery retries
dead-letter created
manual replay works
invalid endpoint handled
scopes filter events
sensitive payload blocked by default
```

## 24.5 Bridge Agent Tests

```text
pairing works
expired pairing blocked
heartbeat recorded
CSV connector imports sample
mapping validation works
sync push works
sync pull works
conflict created
remote revoke blocks sync
logs redact data
```

## 24.6 Lite Tests

```text
device registration works
unapproved device blocked
config fetched
low-bandwidth mode works
offline event queued
sync works
conflict created
full EMR offline blocked
official document finalization blocked until sync
```

---

# 25. Launch Blockers

Do not call the Connect Suite production-ready if any of these remain true:

```text
API endpoints lack scopes
write endpoints lack idempotency
webhooks are unsigned
webhooks lack retries
SDKs do not exist as installable packages
widget accepts unregistered origins
widget sessions do not expire
Bridge Agent cannot be revoked remotely
Bridge Agent logs expose patient data
OpesCare Lite stores full EMR offline by default
sandbox and production are not separated
developer portal lacks production approval
external systems can overwrite records silently
webhook payloads expose sensitive data by default
API docs are incomplete
no integration certification exists
```

---

# 26. Industry-Leader Acceptance Criteria

The Connect Suite becomes industry-leading when:

```text
API is versioned, scoped, audited, rate-limited, documented, and idempotent
OpenAPI spec exists
developer portal is complete
sandbox works
production approval workflow works
PHP, JS/TS, and Python SDKs exist
SDKs include auth, retries, errors, idempotency, webhook verification
Connect Widget is secure, embeddable, multilingual, audited, and origin-locked
Webhooks are signed, retried, replayable, logged, and scoped
Bridge Agent is installable, paired, monitored, encrypted, and conflict-aware
OpesCare Lite supports small facilities with safe offline-limited sync
FHIR/internal mapping layer exists
reconciliation handles unsafe external data
integration certification blocks unsafe production access
monitoring dashboards exist
security tests pass
documentation is complete
sample apps exist
```

---

# 27. Final Agent Instruction

Use this instruction for Claude Code, Jules, Codex, or Gemini:

```text
You are upgrading OpesCare Connect Suite to industry-leading interoperability standards.

Focus on API, SDK, Connect Widget, Webhooks, Bridge Agent, and OpesCare Lite.

Audit existing implementation first. Preserve working code. Do not duplicate models, routes, services, or tests. Patch missing pieces.

Implement API hardening, developer portal, SDK package structure, secure widget sessions, signed webhooks, Bridge Agent pairing/sync, OpesCare Lite device/sync model, FHIR mapping layer, reconciliation, sandbox, production approval, integration certification, monitoring, documentation, and tests.

Do not use or copy OpesHIS OS.

Do not expose patient data publicly.

Do not allow production integrations without scopes, approval, audit, and tests.

Open a PR with files changed, tests, screenshots, docs updated, risks, and next recommended tasks.
```

---

# 28. Final Definition of Done

This document is complete only when developers can use it to build the full interoperability suite without asking what API, SDK, Widget, Webhooks, Bridge Agent, or OpesCare Lite should do.

The implementation is complete only when all acceptance criteria, tests, launch blockers, documentation, and certification requirements are satisfied.
