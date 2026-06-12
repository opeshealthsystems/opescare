# Phase 9: Dependabot, Snyk, Formal Threat Model

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add automated dependency scanning (Dependabot + Snyk) and write the formal threat model document.
**Architecture:** Configuration files only for scanning. Threat model is documentation. No application code changes.
**Tech Stack:** GitHub Dependabot, Snyk, STRIDE methodology

---

## Task 1: Dependabot Configuration (item 73)

**Context:** No Dependabot config exists yet. This adds automated weekly dependency scanning for Composer packages and GitHub Actions workflows.

---

### Step 1.1 — Create `.github/dependabot.yml`

**File:** `C:\laragon\www\opescare\.github\dependabot.yml`

```yaml
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/apps/api-laravel"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "09:00"
      timezone: "Africa/Douala"
    open-pull-requests-limit: 5
    reviewers:
      - "makkowens24"
    labels:
      - "dependencies"
      - "security"
    ignore:
      - dependency-name: "*"
        update-types: ["version-update:semver-major"]
    commit-message:
      prefix: "chore(deps)"

  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "09:00"
      timezone: "Africa/Douala"
    open-pull-requests-limit: 5
    reviewers:
      - "makkowens24"
    labels:
      - "dependencies"
      - "github-actions"
    commit-message:
      prefix: "chore(ci)"
```

**Note on major version ignore:** Major version bumps (e.g., Laravel 11 → 12, PHP 8.3 → 9.x) are excluded from automated PRs because they require manual review and migration planning. Minor and patch updates are automated.

---

### Step 1.2 — Create `.github/workflows/snyk-security.yml`

**File:** `C:\laragon\www\opescare\.github\workflows\snyk-security.yml`

```yaml
name: Snyk Security Scan

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
  schedule:
    - cron: '0 9 * * 1'   # weekly monday 09:00 UTC

jobs:
  snyk:
    name: Snyk PHP Dependency Scan
    runs-on: ubuntu-latest
    permissions:
      contents: read
      security-events: write

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          tools: composer:v2

      - name: Install Composer dependencies
        working-directory: apps/api-laravel
        run: composer install --no-dev --prefer-dist --no-progress

      - name: Run Snyk to check for vulnerabilities
        uses: snyk/actions/php@master
        env:
          SNYK_TOKEN: ${{ secrets.SNYK_TOKEN }}
        with:
          args: >
            --severity-threshold=high
            --file=apps/api-laravel/composer.json

      - name: Upload Snyk results to GitHub Code Scanning
        uses: github/codeql-action/upload-sarif@v3
        if: always()
        with:
          sarif_file: snyk.sarif
        continue-on-error: true
```

---

### Step 1.3 — Create `.snyk` Policy File

**File:** `C:\laragon\www\opescare\.snyk`

```yaml
# Snyk (https://snyk.io) policy file
# See: https://docs.snyk.io/snyk-cli/test-for-vulnerabilities/the-.snyk-file
version: v1.25.0

# Vulnerabilities to ignore with justification
ignore: {}

# Patches to apply
patch: {}
```

**Usage:** When a Snyk scan reports a known false positive or a vulnerability with an accepted compensating control, add an entry under `ignore` with a justification and expiry:

```yaml
ignore:
  SNYK-PHP-EXAMPLE-12345:
    - '*':
        reason: 'Compensating control: WAF blocks this attack vector in production'
        expires: '2027-01-01T00:00:00.000Z'
        created: '2026-05-28T00:00:00.000Z'
```

---

### Step 1.4 — Update `.env.example`

**File:** `C:\laragon\www\opescare\apps\api-laravel\.env.example`

Add the following section:

```dotenv
# Snyk security scanning (set in GitHub Actions secrets, not locally)
SNYK_TOKEN=
```

**Note:** `SNYK_TOKEN` is stored as a GitHub Actions repository secret (`Settings → Secrets and variables → Actions → New repository secret`). It is never committed. The `.env.example` entry documents its existence.

---

### Step 1.5 — Add GitHub Repository Labels

Create the following labels in the GitHub repository (Settings → Labels) if not already present:

| Label | Color | Description |
|-------|-------|-------------|
| `dependencies` | `#0366d6` | Automated dependency updates |
| `security` | `#e11d48` | Security-related changes |
| `github-actions` | `#8b5cf6` | CI/CD workflow changes |

---

### Step 1.6 — Validate YAML Syntax

Run this validation before committing:

```bash
# Validate dependabot.yml (requires python3)
python3 -c "import yaml; yaml.safe_load(open('.github/dependabot.yml')); print('dependabot.yml: OK')"

# Validate snyk workflow
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/snyk-security.yml')); print('snyk-security.yml: OK')"

# Validate .snyk policy
python3 -c "import yaml; yaml.safe_load(open('.snyk')); print('.snyk: OK')"
```

Expected output:
```
dependabot.yml: OK
snyk-security.yml: OK
.snyk: OK
```

---

### Step 1.7 — Configure Snyk in Snyk Dashboard

1. Log in at https://app.snyk.io
2. Import the `opescare` GitHub repository
3. Navigate to Project settings → enable "Test on PR"
4. Set severity threshold to "High" (block PRs with HIGH or CRITICAL vulnerabilities)
5. Enable weekly email reports to `exerateanalytical@gmail.com`
6. Copy the `SNYK_TOKEN` from Account Settings → API Token → add to GitHub repo secrets

---

### Step 1.8 — Test

After merging to `main`:

1. Confirm Dependabot is active: `github.com/makkowens24/opescare/security/dependabot`
2. Trigger Snyk scan manually: `gh workflow run "Snyk Security Scan"`
3. Confirm scan completes without HIGH/CRITICAL findings
4. Verify results appear in GitHub Security tab → Code scanning alerts

---

## Task 2: Formal Threat Model (item 75)

Create the formal threat model document.

**File:** `C:\laragon\www\opescare\apps\api-laravel\docs\security\threat-model.md`

```markdown
# OpesCare Threat Model

**Version:** 1.0
**Date:** 2026-05-28
**Methodology:** STRIDE
**Classification:** CONFIDENTIAL — Internal Security Document
**Review Cycle:** Quarterly, or after any major architectural change

---

## 1. System Overview

OpesCare is a multi-tenant healthcare information system serving clinical facilities in sub-Saharan Africa. It processes Protected Health Information (PHI), payment data, and clinical workflows.

### Data Flow Diagram

```
Patient (Mobile/Web)
        │
        │ HTTPS/TLS 1.3
        ▼
  API Gateway / Load Balancer
  (WAF + ThrottleByClient middleware)
        │
        │ Internal network
        ▼
  Laravel 11 Application (PHP 8.3)
  ┌─────────────────────────────────────┐
  │ Auth (Sanctum JWT)                  │
  │ RBAC (role-based middleware)        │
  │ HasFacilityScope (tenant isolation) │
  │ KMS Encryption (field-level PHI)   │
  │ AccessLogService (audit trail)      │
  └─────────────────────────────────────┘
        │                    │
        │ PostgreSQL          │ External integrations
        ▼                    ▼
  Primary DB          ┌──────────────────────────┐
  + Read Replica      │ WhatsApp Business API     │
                      │ DHIS2 (national registry) │
                      │ MTN MoMo (payments)       │
                      │ AWS KMS (key management)  │
                      │ FHIR consumers            │
                      └──────────────────────────┘
```

### Component Inventory

| Component | Technology | Exposure |
|-----------|-----------|---------|
| API Application | Laravel 11, PHP 8.3 | Internal (behind gateway) |
| Database | PostgreSQL (primary + read replica) | Internal only |
| Cache / Queue | Redis | Internal only |
| File Storage | AWS S3 (encrypted) | Private bucket |
| Authentication | Laravel Sanctum (JWT-style tokens) | Public (token exchange) |
| Encryption | AWS KMS + local AES-256-GCM | Internal |
| Notifications | WhatsApp Business API | External |
| National Reporting | DHIS2 API | External |
| Payments | MTN Mobile Money API | External |
| FHIR Export | HL7 FHIR R4 consumers | External |

---

## 2. Assets

| Asset | Classification | Impact if Compromised |
|-------|---------------|----------------------|
| Patient Health Information (PHI) | CRITICAL | Regulatory fines, patient harm, reputational damage |
| Authentication credentials (tokens, passwords) | CRITICAL | Full account takeover, data breach |
| Payment transaction data (MTN MoMo) | HIGH | Financial fraud, regulatory penalties |
| Audit logs | HIGH | Evidence tampering, compliance failure |
| AWS KMS encryption keys | CRITICAL | Mass PHI decryption if keys exposed |
| APP_KEY (Laravel encryption) | CRITICAL | Session forgery, cookie decryption |
| Database credentials | CRITICAL | Full database access |
| Snyk / CI tokens | MEDIUM | Supply chain attack vector |
| WhatsApp API credentials | MEDIUM | Spam/phishing via facility number |
| DHIS2 integration credentials | HIGH | False national health reporting |

---

## 3. Trust Boundaries

```
┌─────────────────────────────────────────────────────────┐
│ UNTRUSTED: Public Internet                              │
│   - Mobile app users                                    │
│   - Browser users                                       │
│   - Webhook sources (WhatsApp, MoMo)                    │
└──────────────────┬──────────────────────────────────────┘
                   │ TLS 1.3 only
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 1: API Gateway / Load Balancer                 │
│   - WAF rules (OWASP top 10)                           │
│   - DDoS protection                                     │
│   - TLS termination                                     │
│   - IP allowlisting for webhook sources                 │
└──────────────────┬──────────────────────────────────────┘
                   │ Internal HTTP (no TLS required)
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 2: Application Layer                           │
│   - Sanctum authentication                             │
│   - RBAC middleware                                     │
│   - HasFacilityScope tenant isolation                   │
│   - Input validation (FormRequest classes)              │
│   - Rate limiting (ThrottleByClient)                    │
└──────────────────┬──────────────────────────────────────┘
                   │ PDO / Eloquent ORM (parameterized queries)
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 3: Database Layer                              │
│   - PostgreSQL (VPC/subnet isolated)                    │
│   - No public endpoint                                  │
│   - Field-level KMS encryption for sensitive columns    │
│   - Read replica for reporting queries                  │
└─────────────────────────────────────────────────────────┘
                   │ HTTPS to external APIs
┌──────────────────▼──────────────────────────────────────┐
│ BOUNDARY 4: External Services                           │
│   - AWS KMS (key operations only, no key export)        │
│   - WhatsApp Business API (minimal PHI in messages)     │
│   - MTN MoMo (payment reference IDs, no PHI)           │
│   - DHIS2 (de-identified aggregate data)               │
└─────────────────────────────────────────────────────────┘
```

---

## 4. STRIDE Analysis

### 4.1 API Gateway / Load Balancer

#### Spoofing
- **Threat:** JWT token theft via XSS, network interception, or stolen device allows attacker to impersonate a legitimate user.
- **Likelihood:** MEDIUM — mobile apps are common attack targets.
- **Impact:** HIGH — full account access to patient records.
- **Mitigations:**
  - Access tokens expire in 15 minutes; refresh tokens expire in 7 days.
  - Refresh token rotation: each use invalidates the previous token.
  - Tokens bound to user-agent + IP fingerprint (soft binding, not hard enforcement to allow mobile network switches).
  - HTTPS-only; HSTS enforced.
- **Residual Risk:** LOW — token theft window is limited to 15 minutes without refresh token.

#### Tampering
- **Threat:** Attacker modifies HTTP requests in transit (request body, headers, query parameters) to alter data or bypass business logic.
- **Likelihood:** LOW — TLS in transit.
- **Impact:** HIGH — could corrupt clinical data.
- **Mitigations:**
  - HTTPS enforced end-to-end (HTTP requests redirected to HTTPS).
  - WAF inspects request payloads for injection patterns.
  - All writes go through FormRequest validation (type-checked, whitelisted fields).
  - Parameterized queries via Eloquent ORM (no raw SQL with user input).
- **Residual Risk:** VERY LOW.

#### Repudiation
- **Threat:** User or attacker performs an action and later denies it, with no audit evidence.
- **Likelihood:** LOW — the system has `AccessLogService`.
- **Impact:** HIGH in healthcare — clinician denying a prescription change could be life-threatening in a dispute.
- **Mitigations:**
  - `AccessLogService` records all authenticated requests: user_id, facility_id, action, timestamp, IP, resource_id.
  - Audit logs are append-only (no update/delete endpoints on audit tables).
  - Logs shipped to tamper-evident external storage (CloudWatch / S3) in production.
- **Residual Risk:** LOW.

#### Information Disclosure
- **Threat:** Error messages expose stack traces, table names, file paths, or internal logic to unauthenticated users.
- **Likelihood:** MEDIUM — common in misconfigured Laravel deployments.
- **Impact:** MEDIUM — aids reconnaissance.
- **Mitigations:**
  - `APP_DEBUG=false` in production; generic error messages returned via exception handler.
  - Stack traces logged internally only (never returned in API responses in production).
  - Snyk + Dependabot catch vulnerable packages that might expose internals.
- **Residual Risk:** LOW.

#### Denial of Service
- **Threat:** Attacker floods the API with requests, exhausting server resources and making the service unavailable to legitimate users (patients, clinicians).
- **Likelihood:** MEDIUM — healthcare systems in Africa are targeted by hacktivists and ransomware actors.
- **Impact:** HIGH — unavailable scheduling or prescriptions could delay care.
- **Mitigations:**
  - `ThrottleByClient` middleware: 60 requests/minute per IP for public endpoints, 300/minute for authenticated.
  - WAF rate limiting at the gateway layer (before application layer).
  - `DatabaseHealthMiddleware` returns 503 with `Retry-After` header rather than crashing.
  - Read replica absorbs reporting/read-heavy queries.
- **Residual Risk:** MEDIUM — sophisticated volumetric DDoS still possible without dedicated DDoS mitigation service (e.g., Cloudflare).

#### Elevation of Privilege
- **Threat:** Authenticated user with low privilege (e.g., `receptionist` role) accesses or modifies resources reserved for `clinician` or `admin` roles.
- **Likelihood:** MEDIUM — RBAC misconfiguration is a common developer error.
- **Impact:** HIGH — unauthorized prescription or clinical note access.
- **Mitigations:**
  - RBAC middleware applied to all routes via `role:` and `permission:` middleware.
  - `HasFacilityScope` on all patient-data models ensures cross-facility access is structurally impossible at the ORM level.
  - Route-level authorization via Laravel Policies for resource-specific access.
  - Policy unit tests cover each role for each resource.
- **Residual Risk:** LOW.

---

### 4.2 Database (PostgreSQL)

#### Spoofing
- **Threat:** Attacker obtains database credentials and connects directly to PostgreSQL, bypassing application-layer controls.
- **Likelihood:** LOW — credentials not exposed publicly.
- **Impact:** CRITICAL — full database access, bypasses all RBAC and tenant isolation.
- **Mitigations:**
  - DB credentials stored in AWS Secrets Manager; rotated every 90 days.
  - Application authenticates via rotated credentials fetched at runtime.
  - DB instance has no public endpoint; accessible only from app subnet VPC.
  - DB credentials encrypted in transit (KMS) and at rest.
- **Residual Risk:** LOW.

#### Tampering
- **Threat:** Attacker with DB access modifies clinical records, audit logs, or billing data directly in PostgreSQL.
- **Likelihood:** LOW — DB not publicly accessible.
- **Impact:** CRITICAL — altered clinical records could cause patient harm.
- **Mitigations:**
  - DB accessible only from app subnet (VPC security groups).
  - Audit log tables have `INSERT`-only grants for the application DB user; no `UPDATE`/`DELETE` on audit tables.
  - Point-in-time recovery (PITR) enabled — 30-day retention — supports forensic investigation.
  - KMS-encrypted sensitive fields: tampering changes ciphertext without key access.
- **Residual Risk:** LOW — requires VPC compromise first.

#### Repudiation
- **Threat:** No DB-level audit trail; application-level logs could be cleared if the application DB user has `DELETE` on audit tables.
- **Mitigations:**
  - Application DB role has `INSERT`-only on `audit_logs` table; no `DELETE` or `TRUNCATE`.
  - DB audit logging enabled (PostgreSQL `pgaudit` extension) for DDL and superuser actions.
  - Logs shipped to CloudWatch with retention policy.
- **Residual Risk:** LOW.

#### Information Disclosure
- **Threat:** PHI exposed via query result leakage, backup file exposure, or unencrypted data at rest.
- **Likelihood:** MEDIUM — S3 misconfigurations are common.
- **Impact:** CRITICAL — PHI breach, regulatory fines (HIPAA-equivalent local regulations).
- **Mitigations:**
  - Field-level KMS encryption for: `patients.national_id`, `patients.date_of_birth`, `clinical_notes.content`, `prescriptions.drug_name` (high-sensitivity fields).
  - PostgreSQL storage encrypted at rest (AWS RDS encryption enabled).
  - DB snapshots and backups encrypted with same KMS key.
  - S3 backup bucket: private, SSE-S3 encryption, no public access, versioning enabled.
- **Residual Risk:** LOW.

#### Denial of Service
- **Threat:** Query flooding, long-running analytical queries, or connection pool exhaustion makes the DB unavailable.
- **Likelihood:** MEDIUM — reporting queries can be expensive.
- **Impact:** HIGH — clinical workflows depend on DB availability.
- **Mitigations:**
  - Read replica: all `GET` requests that are read-heavy routed via `database.php` read connection.
  - Query timeouts: `statement_timeout = 30s` configured in PostgreSQL.
  - Connection pooling via PgBouncer (configured in production).
  - `DatabaseHealthMiddleware` returns 503 rather than holding requests open during DB unavailability.
- **Residual Risk:** MEDIUM — extreme load still possible.

---

### 4.3 External Integrations (WhatsApp, MTN MoMo, DHIS2)

#### Spoofing — Webhook Forgery
- **Threat:** Attacker crafts fake webhook payloads pretending to be from WhatsApp, MTN MoMo, or DHIS2 to inject false data or trigger unauthorized actions.
- **Likelihood:** MEDIUM — webhook endpoints must be public.
- **Impact:** HIGH — fake payment confirmations could credit unpaid appointments.
- **Mitigations:**
  - WhatsApp webhooks: HMAC-SHA256 signature verification on `X-Hub-Signature-256` header.
  - MTN MoMo webhooks: API key verification + IP allowlisting to MoMo's published IP ranges.
  - DHIS2: mutual TLS (mTLS) for server-to-server API calls.
  - All webhook endpoints log the source IP; anomalous IPs trigger SecurityIncidentService alert.
- **Residual Risk:** LOW.

#### Information Disclosure — PHI in Messages
- **Threat:** WhatsApp messages containing PHI (patient names, diagnoses, appointment details) are stored on WhatsApp's servers, outside OpesCare's control.
- **Likelihood:** HIGH — notification systems frequently include too much detail.
- **Impact:** HIGH — PHI exposure to Meta/WhatsApp platform.
- **Mitigations:**
  - WhatsApp notifications use reference IDs only (e.g., "Your appointment ref #APT-12345 is confirmed"). No diagnosis, no drug names.
  - Patients can opt out of WhatsApp notifications entirely.
  - Notification content reviewed in privacy impact assessment before any new template is approved.
- **Residual Risk:** MEDIUM — WhatsApp platform access to metadata (who messaged whom, when) is unavoidable.

#### Tampering — DHIS2 Reporting
- **Threat:** Attacker intercepts or modifies DHIS2 API calls, sending false aggregate health data to national registries.
- **Likelihood:** LOW — DHIS2 connection is outbound only.
- **Impact:** HIGH — false public health data.
- **Mitigations:**
  - mTLS for all DHIS2 API calls.
  - DHIS2 reports contain only de-identified aggregate counts; no individual patient records sent.
  - Outbound payload signed with facility certificate before transmission.
- **Residual Risk:** LOW.

---

### 4.4 KMS Encryption Service

#### Key Compromise — AWS KMS
- **Threat:** AWS KMS key is compromised (via IAM credential theft, insider threat), allowing attacker to decrypt all KMS-encrypted PHI fields.
- **Likelihood:** LOW — AWS KMS provides hardware-level key protection.
- **Impact:** CRITICAL — mass PHI decryption.
- **Mitigations:**
  - AWS KMS Customer Managed Keys (CMK) with automatic rotation every 365 days.
  - IAM policy: only the application role can use the KMS key (`kms:Decrypt`, `kms:GenerateDataKey`); no `kms:DeleteKey`, `kms:DisableKey` for the app role.
  - CloudTrail logs all KMS API calls; anomalous decryption volume triggers alert.
  - KMS key policy enforces MFA for administrative actions (key deletion, policy changes).
- **Residual Risk:** LOW.

#### Local Key Exposure — APP_KEY
- **Threat:** Laravel `APP_KEY` is committed to version control, exposed in error messages, or logged — allowing session forgery and cookie decryption.
- **Likelihood:** LOW — `.env` is gitignored.
- **Impact:** HIGH — session hijacking across all users.
- **Mitigations:**
  - `.env` is in `.gitignore`; `.env.example` contains no real values.
  - `APP_KEY` stored in AWS Secrets Manager; injected at deploy time as environment variable.
  - `APP_KEY` rotated every 90 days via `SecretsRotationCommand` (already implemented in Phase 7).
  - Pre-commit hook prevents committing files containing `APP_KEY=base64:`.
- **Residual Risk:** LOW.

#### Domain Separation Failure
- **Threat:** KMS-encrypted data from one context (e.g., one facility's patient records) can be decrypted in another context due to missing domain separation in the encryption scheme.
- **Likelihood:** LOW — fixed in Phase 7 (c6cbe84).
- **Impact:** HIGH — cross-facility PHI decryption.
- **Mitigations:**
  - KMS key derivation uses domain separation prefix (`opescare:{facility_id}:{field_name}`) as AAD (Additional Authenticated Data) in AES-256-GCM envelope encryption.
  - Ciphertext from Facility A is cryptographically bound to Facility A's context; decryption under Facility B's context fails authentication.
- **Residual Risk:** VERY LOW.

---

## 5. Residual Risks

The following risks are accepted with documented compensating controls:

| Risk | Likelihood | Impact | Compensating Control | Accepted By | Review Date |
|------|-----------|--------|---------------------|-------------|-------------|
| Volumetric DDoS exceeding WAF capacity | LOW | HIGH | ThrottleByClient + 503 graceful degradation; manual Cloudflare onboarding if attack occurs | CTO | 2026-09-01 |
| WhatsApp metadata exposure (who-talks-to-whom) | HIGH | LOW | Opt-out available; no clinical content in messages | DPO | 2026-09-01 |
| Long-lived refresh tokens (7-day window) | LOW | HIGH | Refresh token rotation; token family revocation on suspicious activity | Security Officer | 2026-09-01 |
| PostgreSQL superuser access by cloud provider (AWS RDS) | LOW | CRITICAL | AWS shared responsibility model; compensated by field-level KMS encryption | CTO | 2026-09-01 |
| Zero-day in Laravel or PHP 8.3 | LOW | HIGH | Snyk + Dependabot weekly scans; 48-hour patch SLA for CRITICAL CVEs | Security Officer | 2026-09-01 |

---

## 6. Security Controls Summary

| Control | Implementation | Status |
|---------|---------------|--------|
| Authentication | Laravel Sanctum (JWT-style) | Active |
| Authorization | RBAC via spatie/laravel-permission | Active |
| Tenant isolation | HasFacilityScope trait (ORM-level) | Active (Phase 8) |
| Field-level encryption | KmsEncryptionService (AES-256-GCM + AWS KMS) | Active (Phase 7) |
| Secrets rotation | SecretsRotationCommand + runbook | Active (Phase 7) |
| Audit logging | AccessLogService (append-only) | Active |
| Rate limiting | ThrottleByClient middleware | Active |
| Dependency scanning | Snyk + Dependabot | Active (Phase 9) |
| Database health monitoring | DatabaseHealthMiddleware + RegionHealthService | Active (Phase 8) |
| Webhook signature verification | HMAC-SHA256 (WhatsApp), API key + IP (MoMo) | Active |
| Transport security | TLS 1.3, HTTPS-only, HSTS | Active |
| Input validation | FormRequest classes on all write endpoints | Active |
| SQL injection prevention | Eloquent ORM parameterized queries | Active |

---

## 7. Review Schedule

| Trigger | Action |
|---------|--------|
| Quarterly (every 3 months) | Full STRIDE review; update residual risk table |
| After any new external integration | Add STRIDE analysis for new component |
| After any major architecture change (new service, DB engine change) | Update data flow diagram and trust boundaries |
| After any security incident | Post-mortem fed back into threat model |
| After any new regulatory requirement | Review PHI handling sections |

Next scheduled review: **2026-08-28**

---

## 8. Sign-off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Security Officer | | | |
| Chief Technology Officer | | | |
| Data Protection Officer | | | |

This document requires sign-off before the OpesCare system processes live patient data in production. A copy of the signed document must be retained for a minimum of 7 years per healthcare records regulations.

---

*Document prepared by: Engineering Team*
*Methodology: STRIDE (Microsoft Threat Modeling)*
*Next review: 2026-08-28*
*Classification: CONFIDENTIAL*
```

---

## Completion Checklist

- [ ] `.github/dependabot.yml` created and YAML-validated
- [ ] `.github/workflows/snyk-security.yml` created and YAML-validated
- [ ] `.snyk` policy file created
- [ ] `.env.example` updated with `SNYK_TOKEN=`
- [ ] GitHub repository labels created (`dependencies`, `security`, `github-actions`)
- [ ] `SNYK_TOKEN` added to GitHub repository secrets
- [ ] Snyk project imported in Snyk dashboard
- [ ] `docs/security/threat-model.md` created
- [ ] Threat model reviewed and signed off by Security Officer, CTO, DPO before production launch
- [ ] Snyk scan runs clean on `main` branch (no HIGH/CRITICAL findings)
- [ ] Dependabot active and visible in Security tab
