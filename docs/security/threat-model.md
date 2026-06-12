# OpesCare STRIDE Threat Model (Consolidated)

**Version:** 2.0 (consolidated)
**Owner:** Platform Engineering / Information Security
**Date:** 2026-06-11
**Methodology:** STRIDE (Microsoft Threat Modeling)
**Classification:** CONFIDENTIAL — Internal Security Document
**Review cadence:** Quarterly, or after any major architectural change

> **Consolidation note.** This document merges two prior threat models that were
> maintained separately:
>
> 1. **Threat-indexed model (2026-05-27)** — organized by STRIDE category with
>    numbered threats (S1–E3), a risk-summary table, and an open-action-items list.
> 2. **Component-indexed model (2026-05-28)** — organized by system component / trust
>    boundary with assets, residual-risk acceptance, controls summary, and sign-off.
>
> Both originals are preserved under
> `docs/_older-versions/_threat-model-sources/` and
> `docs/_older-versions/apps/api-laravel/docs/security/threat-model.md`.
> Where the two sources stated different values for the same control, the
> discrepancy is flagged in **§9 Reconciliation Notes** below and must be resolved
> by the owner before sign-off.

---

## 1. System Overview

OpesCare is a multi-tenant, API-first healthcare information system serving
clinical facilities in sub-Saharan Africa (initial deployment: Gabon). It processes
Protected Health Information (PHI), payment data, and clinical workflows, and stores
PHI at rest under field-level encryption.

### 1.1 Data Flow Diagram

```
Patient (Mobile/Web)
        │ HTTPS / TLS 1.3
        ▼
  API Gateway / Load Balancer
  (Cloudflare WAF + ThrottleByClient middleware)
        │ Internal network
        ▼
  Laravel Application (PHP 8.3)
  ┌─────────────────────────────────────┐
  │ Auth (Sanctum tokens)               │
  │ RBAC (role/permission middleware)   │
  │ HasFacilityScope (tenant isolation) │
  │ KMS Encryption (field-level PHI)    │
  │ AccessLogService (audit trail)      │
  └─────────────────────────────────────┘
        │ PostgreSQL          │ External integrations
        ▼                     ▼
  Primary DB           ┌──────────────────────────┐
  + Read Replica       │ WhatsApp Business API     │
  (Redis: cache/queue) │ DHIS2 (national registry) │
                       │ MTN MoMo / Orange (pay)   │
                       │ AWS KMS (key management)  │
                       │ FHIR R4 consumers         │
                       └──────────────────────────┘
```

### 1.2 Component Inventory

| Component | Technology | Exposure |
|-----------|-----------|---------|
| Patient Portal (Blade) | Web UI for patients and guardians | Public |
| API Application | Laravel, PHP 8.3 | Internal (behind gateway) |
| Database | PostgreSQL (primary + read replica) | Internal only |
| Cache / Queue | Redis | Internal only |
| Queue monitoring | Laravel Horizon (admin-only) | Internal |
| File / Backup Storage | AWS S3 (encrypted) | Private bucket |
| Authentication | Laravel Sanctum (token exchange) | Public (token exchange) |
| Encryption | AWS KMS + local AES-256-GCM | Internal |
| Notifications | WhatsApp Business API | External |
| National Reporting | DHIS2 API | External |
| Payments | MTN MoMo / Orange Money API | External |
| FHIR Export | HL7 FHIR R4 consumers | External |

### 1.3 Assets

| Asset | Classification | Impact if Compromised |
|-------|---------------|----------------------|
| Patient Health Information (PHI) | CRITICAL | Regulatory fines, patient harm, reputational damage |
| Authentication credentials (tokens, passwords) | CRITICAL | Full account takeover, data breach |
| Payment transaction data (MTN MoMo / Orange) | HIGH | Financial fraud, regulatory penalties |
| Audit logs | HIGH | Evidence tampering, compliance failure |
| AWS KMS encryption keys | CRITICAL | Mass PHI decryption if keys exposed |
| APP_KEY (Laravel encryption) | CRITICAL | Session forgery, cookie decryption |
| Database credentials | CRITICAL | Full database access |
| Snyk / CI tokens | MEDIUM | Supply-chain attack vector |
| WhatsApp API credentials | MEDIUM | Spam/phishing via facility number |
| DHIS2 integration credentials | HIGH | False national health reporting |

### 1.4 STRIDE Categories

| Letter | Threat | Security Property Violated |
|--------|--------|---------------------------|
| S | Spoofing | Authentication |
| T | Tampering | Integrity |
| R | Repudiation | Non-repudiation |
| I | Information Disclosure | Confidentiality |
| D | Denial of Service | Availability |
| E | Elevation of Privilege | Authorization |

---

## 2. Trust Boundaries

```
┌─────────────────────────────────────────────────────────┐
│ UNTRUSTED: Public Internet                               │
│   - Mobile app users / Browser users                     │
│   - Webhook sources (WhatsApp, MoMo)                     │
└──────────────────┬──────────────────────────────────────┘
                   │ TLS 1.3 only
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 1: API Gateway / Load Balancer                  │
│   - WAF rules (OWASP Top 10), DDoS protection            │
│   - TLS termination, IP allowlisting for webhooks        │
└──────────────────┬──────────────────────────────────────┘
                   │ Internal HTTP
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 2: Application Layer                            │
│   - Sanctum authentication, RBAC middleware              │
│   - HasFacilityScope tenant isolation                    │
│   - Input validation (FormRequest), ThrottleByClient     │
└──────────────────┬──────────────────────────────────────┘
                   │ PDO / Eloquent (parameterized queries)
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 3: Database Layer                               │
│   - PostgreSQL (VPC/subnet isolated, no public endpoint) │
│   - Field-level KMS encryption, read replica             │
└──────────────────┬──────────────────────────────────────┘
                   │ HTTPS to external APIs
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 4: External Services                            │
│   - AWS KMS (key ops only, no key export)                │
│   - WhatsApp (minimal PHI), MoMo (refs only), DHIS2 (agg)│
└─────────────────────────────────────────────────────────┘
```

---

## 3. STRIDE Analysis by Component

### 3.1 API Gateway / Load Balancer

#### Spoofing
- **Threat:** Token theft via XSS, network interception, or stolen device allows an attacker to impersonate a legitimate user.
- **Likelihood:** MEDIUM — mobile apps are common attack targets.
- **Impact:** HIGH — full account access to patient records.
- **Mitigations:**
  - Access tokens expire in 15 minutes; refresh tokens expire in 7 days, with rotation (each use invalidates the previous token).
  - Tokens soft-bound to user-agent + IP fingerprint (allows mobile network switches).
  - HTTPS-only; HSTS enforced.
  - Brute-force protection: throttle on auth routes (see §9 for the rate-limit discrepancy to resolve).
- **Residual Risk:** LOW — theft window limited without the refresh token. Phishing not fully mitigated until MFA ships (see Action Items).

#### Tampering
- **Threat:** Attacker modifies HTTP requests in transit to alter data or bypass business logic.
- **Likelihood:** LOW — TLS in transit.
- **Impact:** HIGH — could corrupt clinical data.
- **Mitigations:** HTTPS enforced end-to-end; WAF payload inspection; FormRequest validation (type-checked, whitelisted fields); Eloquent parameterized queries; CSP/security headers via SecurityHeaders middleware.
- **Residual Risk:** VERY LOW. *Recommended:* request signing for integration-partner webhooks.

#### Repudiation
- **Threat:** A user or attacker performs an action then denies it, with no audit evidence.
- **Likelihood:** LOW — `AccessLogService` records all authenticated requests.
- **Impact:** HIGH — e.g., a clinician denying a prescription change.
- **Mitigations:** Append-only `security_audit_logs` (no UPDATE/DELETE); `actor_id` taken from the authenticated session (never caller-supplied); entries include actor, patient, action, timestamp, IP, facility; logs shipped to tamper-evident external storage (CloudWatch / S3) in production.
- **Residual Risk:** LOW. *Recommended:* ship audit logs to immutable S3 Object Lock store.

#### Information Disclosure
- **Threat:** Error messages expose stack traces, table names, or internal logic.
- **Likelihood:** MEDIUM — common in misconfigured Laravel deployments.
- **Impact:** MEDIUM — aids reconnaissance.
- **Mitigations:** `APP_DEBUG=false` enforced via ProductionSafetyServiceProvider; generic error messages; stack traces logged internally only; Snyk + Dependabot catch vulnerable packages.
- **Residual Risk:** LOW.

#### Denial of Service
- **Threat:** Attacker floods the API, exhausting resources.
- **Likelihood:** MEDIUM–HIGH — healthcare systems in the region are targeted by hacktivists and ransomware actors.
- **Impact:** HIGH — unavailable scheduling/prescriptions delays care.
- **Mitigations:** `ThrottleByClient` middleware; Cloudflare WAF rate limiting at gateway; `DatabaseHealthMiddleware` returns 503 with `Retry-After`; read replica absorbs read-heavy queries; health-check endpoint cached at edge.
- **Residual Risk:** MEDIUM — sophisticated volumetric DDoS still possible. *Recommended:* Cloudflare Under Attack Mode during incidents; adaptive rate limiting by server load.

#### Elevation of Privilege
- **Threat:** A low-privilege user (e.g., `receptionist`) accesses resources reserved for `clinician`/`admin`.
- **Likelihood:** MEDIUM — RBAC misconfiguration is a common developer error.
- **Impact:** HIGH — unauthorized prescription or clinical-note access.
- **Mitigations:** RBAC `role:`/`permission:` middleware on all routes; `HasFacilityScope` makes cross-facility access structurally impossible at the ORM level; Laravel Policies for resource-specific access, with policy unit tests per role/resource.
- **Residual Risk:** LOW. *Recommended:* ABAC for fine-grained permissions; promote `HasFacilityScope` to a global scope on all Patient models.

### 3.2 Database (PostgreSQL)

#### Spoofing
- **Threat:** Attacker obtains DB credentials and connects directly, bypassing app-layer controls.
- **Likelihood:** LOW. **Impact:** CRITICAL — bypasses all RBAC and tenant isolation.
- **Mitigations:** Credentials in AWS Secrets Manager, rotated every 90 days; no public DB endpoint (app-subnet VPC only); credentials encrypted in transit and at rest.
- **Residual Risk:** LOW.

#### Tampering
- **Threat:** Attacker with DB access modifies clinical records, audit logs, or billing data directly.
- **Likelihood:** LOW. **Impact:** CRITICAL — altered records could cause patient harm.
- **Mitigations:** VPC security-group restriction; `INSERT`-only grant on audit tables (no UPDATE/DELETE/TRUNCATE); point-in-time recovery (30-day retention); KMS-encrypted fields are tamper-evident (AES-256-GCM auth tag).
- **Residual Risk:** LOW — requires VPC compromise first. *Recommended:* PostgreSQL row-level security.

#### Repudiation
- **Threat:** Application-level logs could be cleared if the DB user has DELETE on audit tables.
- **Mitigations:** Application DB role has `INSERT`-only on `audit_logs`; `pgaudit` enabled for DDL/superuser actions; logs shipped to CloudWatch with retention.
- **Residual Risk:** LOW.

#### Information Disclosure
- **Threat:** PHI exposed via query-result leakage, backup exposure, or unencrypted data at rest.
- **Likelihood:** MEDIUM — S3 misconfigurations are common. **Impact:** CRITICAL.
- **Mitigations:** Field-level KMS encryption for high-sensitivity columns (e.g., national ID, date of birth, clinical-note content, prescription drug name); RDS encryption at rest; encrypted snapshots/backups; private S3 bucket with SSE and versioning; Eloquent API resources strip sensitive fields; facility-ownership checks prevent cross-facility leakage; demo patients excluded from production admin views.
- **Residual Risk:** LOW. *Recommended:* automated PII scanning on API response payloads.

#### Denial of Service
- **Threat:** Query flooding, expensive analytical queries, or connection-pool exhaustion.
- **Likelihood:** MEDIUM. **Impact:** HIGH.
- **Mitigations:** Read replica for read-heavy `GET`s; `statement_timeout = 30s`; PgBouncer connection pooling; `DatabaseHealthMiddleware` returns 503 rather than holding connections.
- **Residual Risk:** MEDIUM.

### 3.3 External Integrations (WhatsApp, MTN MoMo / Orange, DHIS2)

#### Spoofing — Webhook Forgery
- **Threat:** Forged webhook payloads inject false data or trigger unauthorized actions (e.g., fake payment confirmations).
- **Likelihood:** MEDIUM. **Impact:** HIGH.
- **Mitigations:** WhatsApp HMAC-SHA256 signature on `X-Hub-Signature-256`; MoMo API-key verification + IP allowlisting; DHIS2 mTLS; source-IP logging with `SecurityIncidentService` alerts on anomalies.
- **Residual Risk:** LOW.

#### Information Disclosure — PHI in Messages
- **Threat:** WhatsApp messages containing PHI are stored on Meta servers outside OpesCare's control.
- **Likelihood:** HIGH. **Impact:** HIGH.
- **Mitigations:** Notifications use reference IDs only (no diagnosis/drug names); patient opt-out; privacy review before any new template is approved.
- **Residual Risk:** MEDIUM — message metadata exposure is unavoidable.

#### Tampering — DHIS2 Reporting
- **Threat:** Intercepted/modified DHIS2 calls send false aggregate data to national registries.
- **Likelihood:** LOW. **Impact:** HIGH.
- **Mitigations:** mTLS; de-identified aggregate counts only; outbound payload signed with facility certificate.
- **Residual Risk:** LOW.

### 3.4 KMS Encryption Service

#### Key Compromise — AWS KMS
- **Threat:** Compromised KMS key (IAM theft, insider) enables mass PHI decryption.
- **Likelihood:** LOW. **Impact:** CRITICAL.
- **Mitigations:** Customer-Managed Keys with automatic rotation (365 days); least-privilege IAM (app role limited to `Decrypt`/`GenerateDataKey`; no `DeleteKey`/`DisableKey`); CloudTrail logging with anomaly alerts; MFA enforced for KMS administrative actions.
- **Residual Risk:** LOW.

#### Local Key Exposure — APP_KEY
- **Threat:** `APP_KEY` committed to VCS, logged, or exposed — enabling session forgery and cookie decryption.
- **Likelihood:** LOW. **Impact:** HIGH.
- **Mitigations:** `.env` gitignored; placeholders in `.env.example`; `APP_KEY` in Secrets Manager, injected at deploy; rotation every 90 days via `SecretsRotationCommand`; pre-commit hook blocks `APP_KEY=base64:` commits.
- **Residual Risk:** LOW.

#### Domain Separation Failure
- **Threat:** Ciphertext from one context (facility) decryptable in another due to missing domain separation.
- **Likelihood:** LOW. **Impact:** HIGH.
- **Mitigations:** KMS envelope encryption binds ciphertext to context via Additional Authenticated Data (AAD). *See §9 — the exact AAD label differs between the two source documents and must be confirmed against the implementation.*
- **Residual Risk:** VERY LOW.

---

## 4. Supplementary Threat Scenarios (from the threat-indexed model)

These scenarios were itemized in the 2026-05-27 model and are retained here in
addition to the per-component analysis above.

- **S3 — Guardian spoofing a dependent's identity** (Family / FamilyLink module). Mitigations: `GuardianAccessMiddleware` validates the relationship before data access; audit log per guardian access; age-transition workflow removes guardian access at 18. Risk: **MEDIUM**, mitigated.
- **R2 — Patient denies granting consent** (Consent module). Mitigations: consent grants stored with `created_at`, `revoked_at`, grantor `patient_id`; ownership verified before any patient-id filter. Risk: **MEDIUM**, mitigated.
- **I2 — PHI in application logs.** Mitigations: production log level `warning`; 90-day retention; user-agent sanitized before logging in DemoAccessController. Risk: **HIGH**, partial. *Recommended:* structured log PII-scrubbing middleware.
- **I3 — PHI in error responses.** Mitigations: DB errors redacted from 500s in VerifyIntegrationClient; SDK scope names removed from 403s; `APP_DEBUG=false` enforced. Risk: **MEDIUM**, mitigated.
- **I4 — PHI in backups.** Mitigations: AES-256 encryption before upload; SSE-S3/KMS on the bucket; backup password in secrets manager. Risk: **HIGH**, mitigated.
- **D3 — Queue exhaustion via bulk job submission** (Redis/Horizon). Mitigations: Horizon `maxProcesses`; 256 MB per-worker memory limit; failed jobs captured for replay. Risk: **MEDIUM**, mitigated. *Recommended:* per-queue max job-size limits.
- **E3 — Mass assignment of privileged fields.** Mitigations: `role_id` removed from `User.$fillable`; `is_demo` removed from `Patient.$fillable`; explicit `$fillable` on all models. Risk: **MEDIUM**, mitigated.

---

## 5. Risk Summary

| ID | Threat | Likelihood | Impact | Risk | Status |
|----|--------|-----------|--------|------|--------|
| S1 | Patient credential / token spoofing | High | Critical | **CRITICAL** | Mitigated (MFA needed) |
| S2 | Integration partner spoofing | Medium | High | **HIGH** | Mitigated |
| S3 | Guardian identity spoofing | Low | High | **MEDIUM** | Mitigated |
| T1 | DB direct manipulation | Low | Critical | **HIGH** | Mitigated |
| T2 | Request tampering | Low | Medium | **LOW** | Mitigated |
| T3 | Encrypted field tampering | Very Low | High | **LOW** | Mitigated |
| R1 | Provider denies access | Medium | High | **HIGH** | Partial (external audit log) |
| R2 | Consent denial | Low | High | **MEDIUM** | Mitigated |
| I1 | PHI in API responses | Medium | Critical | **CRITICAL** | Mitigated |
| I2 | PHI in logs | Medium | High | **HIGH** | Partial |
| I3 | PHI in error responses | Low | High | **MEDIUM** | Mitigated |
| I4 | PHI in backups | Low | Critical | **HIGH** | Mitigated |
| D1 | API flood | High | High | **HIGH** | Mitigated |
| D2 | User rate-limiting | Medium | Medium | **MEDIUM** | Mitigated |
| D3 | Queue exhaustion | Low | High | **MEDIUM** | Mitigated |
| E1 | Privilege escalation to admin | Low | Critical | **HIGH** | Mitigated |
| E2 | Cross-facility data access | Medium | Critical | **CRITICAL** | Mitigated |
| E3 | Mass assignment | Low | High | **MEDIUM** | Mitigated |
| K1 | KMS key compromise | Low | Critical | **HIGH** | Mitigated |
| K2 | APP_KEY exposure | Low | High | **MEDIUM** | Mitigated |

---

## 6. Residual Risks (Accepted with Compensating Controls)

| Risk | Likelihood | Impact | Compensating Control | Accepted By | Review Date |
|------|-----------|--------|---------------------|-------------|-------------|
| Volumetric DDoS exceeding WAF capacity | LOW | HIGH | ThrottleByClient + 503 graceful degradation; manual Cloudflare onboarding | CTO | 2026-09-01 |
| WhatsApp metadata exposure (who-talks-to-whom) | HIGH | LOW | Opt-out available; no clinical content in messages | DPO | 2026-09-01 |
| Long-lived refresh tokens (7-day window) | LOW | HIGH | Refresh-token rotation; token-family revocation on suspicious activity | Security Officer | 2026-09-01 |
| PostgreSQL superuser access by cloud provider (AWS RDS) | LOW | CRITICAL | Shared-responsibility model; field-level KMS encryption | CTO | 2026-09-01 |
| Zero-day in Laravel or PHP 8.3 | LOW | HIGH | Snyk + Dependabot weekly scans; 48-hour patch SLA for CRITICAL CVEs | Security Officer | 2026-09-01 |

---

## 7. Security Controls Summary

| Control | Implementation | Status |
|---------|---------------|--------|
| Authentication | Laravel Sanctum (token-based) | Active |
| Authorization | RBAC via spatie/laravel-permission | Active |
| Tenant isolation | HasFacilityScope trait (ORM-level) | Active |
| Field-level encryption | KmsEncryptionService (AES-256-GCM + AWS KMS) | Active |
| Secrets rotation | SecretsRotationCommand + runbook | Active |
| Audit logging | AccessLogService (append-only) | Active |
| Rate limiting | ThrottleByClient middleware | Active |
| Dependency scanning | Snyk + Dependabot | Active |
| Database health monitoring | DatabaseHealthMiddleware + RegionHealthService | Active |
| Webhook signature verification | HMAC-SHA256 (WhatsApp), API key + IP (MoMo) | Active |
| Transport security | TLS 1.3, HTTPS-only, HSTS | Active |
| Input validation | FormRequest classes on all write endpoints | Active |
| SQL injection prevention | Eloquent ORM parameterized queries | Active |

---

## 8. Open Action Items

| Priority | Action | Owner | Due |
|----------|--------|-------|-----|
| P0 | Implement MFA (TOTP/SMS) for patient login | Engineering | Q3 2026 |
| P0 | Ship audit logs to immutable S3 with Object Lock | Engineering | Q3 2026 |
| P1 | mTLS for FHIR integration partners | Engineering | Q4 2026 |
| P1 | Structured log PII-scrubbing middleware | Engineering | Q4 2026 |
| P1 | Request signing for integration-partner webhooks | Engineering | Q4 2026 |
| P2 | Enable pgaudit on PostgreSQL | Platform | Q4 2026 |
| P2 | Promote HasFacilityScope to a global scope on Patient models | Engineering | Q4 2026 |
| P2 | Automated PII scanning on API response payloads | Engineering | Q4 2026 |

---

## 9. Reconciliation Notes (Discrepancies to Resolve)

The two source models disagreed on the following details. Confirm each against the
running implementation and update the relevant section above.

| Topic | 2026-05-27 model | 2026-05-28 model | Action |
|-------|------------------|------------------|--------|
| Authenticated rate limit | 1200 req/min (integration partners), 600 (regular) | 300 req/min authenticated, 60 public | Confirm actual `ThrottleByClient` config |
| Laravel version | Laravel 13 | Laravel 11 | Confirm installed version |
| KMS domain-separation AAD label | `opescare:kms:field-encryption:v1` | `opescare:{facility_id}:{field_name}` | Confirm encryption scheme in code |
| DB credential rotation | 60 days | 90 days | Confirm secrets-rotation cadence |
| Region | Gabon | "sub-Saharan Africa" (general) | Cosmetic — align wording |
| Token lifetimes | not specified | 15 min access / 7 day refresh | Adopt the 05-28 values if correct |

---

## 10. Review Schedule

| Trigger | Action |
|---------|--------|
| Quarterly (every 3 months) | Full STRIDE review; update residual-risk table |
| After any new external integration | Add STRIDE analysis for the new component |
| After any major architecture change | Update data-flow diagram and trust boundaries |
| After any security incident | Post-mortem fed back into the threat model |
| After any new regulatory requirement | Review PHI-handling sections |

Next scheduled review: **2026-09-11**

---

## 11. Sign-off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Security Officer | | | |
| Chief Technology Officer | | | |
| Data Protection Officer | | | |

This document requires sign-off before the OpesCare system processes live patient
data in production. A signed copy must be retained for a minimum of 7 years per
healthcare-records regulations.

---

*Methodology: STRIDE (Microsoft Threat Modeling). Consolidated from the 2026-05-27 (threat-indexed) and 2026-05-28 (component-indexed) models. Classification: CONFIDENTIAL.*
