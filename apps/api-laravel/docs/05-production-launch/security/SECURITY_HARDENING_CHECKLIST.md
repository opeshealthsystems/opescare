# OpesCare Security Hardening Checklist

**Version:** 1.0  
**Last Updated:** 2026-05  
**Owner:** Security Lead  

---

## 1. Application Layer

### 1.1 Laravel Framework Security

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_KEY` is 32+ character random string, rotated annually
- [ ] CSRF protection enabled on all state-changing routes (`VerifyCsrfToken` middleware)
- [ ] All user inputs validated with Laravel's `validate()` or Form Requests
- [ ] SQL injection: use Eloquent/Query Builder parameterized queries — no raw SQL with user input
- [ ] XSS: use `{{ }}` not `{!! !!}` for user-supplied content in Blade; when HTML required, sanitize with `HTMLPurifier`
- [ ] Mass assignment: all models use `$fillable` or `$guarded = ['id']` — no `guarded = []`
- [ ] Sensitive fields not returned in API responses (`$hidden` on models)
- [ ] Rate limiting on all auth endpoints (`throttle:6,1` for login, OTP, password reset)
- [ ] Session: `SESSION_DRIVER=redis`, `SESSION_LIFETIME=120`, `SESSION_SECURE_COOKIE=true`
- [ ] Content Security Policy headers configured
- [ ] HSTS header enabled
- [ ] No `.env` file accessible via web (Nginx configuration blocks `/*.`)

### 1.2 Authentication & Authorization

- [ ] Passwords hashed with `bcrypt` (cost factor ≥12) — never stored plaintext
- [ ] MFA enforced for admin and clinical staff accounts
- [ ] JWT / session tokens expire — short-lived access tokens, refresh token rotation
- [ ] Password complexity enforced (min 12 chars, mixed case, numbers, symbols)
- [ ] Brute-force protection: account lockout after 5 failed attempts
- [ ] Concurrent session management: only one active session per user (configurable)
- [ ] Role-based access control enforced on every controller action
- [ ] Patient data access gate-checked (patient can only see own data)
- [ ] Audit trail for every authentication event

### 1.3 API Security

- [ ] All API endpoints require authentication (no unauthenticated data access)
- [ ] API key secrets stored hashed — never plain in DB
- [ ] Integration client throttling enabled (`throttle.client` middleware)
- [ ] API versioning: `/api/v1/` — breaking changes increment version
- [ ] `X-Content-Type-Options: nosniff` header on all API responses
- [ ] CORS: restrict `Access-Control-Allow-Origin` to known origins
- [ ] Sensitive operations (refunds, overrides, data export) require additional confirmation
- [ ] Webhook signatures verified before processing

---

## 2. Data Layer

### 2.1 Database

- [ ] Database credentials not stored in codebase — environment variables only
- [ ] Database user has minimum required privileges (no `DROP TABLE` in app user)
- [ ] Database accessible only from application servers — no public access
- [ ] All columns containing PHI encrypted at rest where required by policy
- [ ] UUIDs for all primary keys — no auto-increment IDs exposed in URLs
- [ ] Soft deletes for patient records (never hard-delete clinical data)
- [ ] Point-in-time recovery enabled on production database

### 2.2 File Storage

- [ ] Patient documents stored in private S3 bucket — no public ACL
- [ ] Presigned URLs used for document access (short expiry ≤15 min)
- [ ] File type validation server-side (MIME type + extension check)
- [ ] Maximum file size enforced (50MB per upload)
- [ ] Virus scanning on upload via ClamAV or cloud antivirus service
- [ ] Document QR codes signed with facility key — tamper-evident

### 2.3 Offline / Lite

- [ ] Offline data stored encrypted on device (AES-256-GCM)
- [ ] Full EMR access blocked in offline mode by default
- [ ] Official documents cannot be issued offline (only queued for sync)
- [ ] Lite device tokens rotated on revocation

---

## 3. Infrastructure Layer

### 3.1 Network

- [ ] Application servers in private subnet — no direct internet access
- [x] All traffic via load balancer with **WAF enabled**
- [ ] TLS 1.2 minimum, TLS 1.3 preferred
- [ ] Weak cipher suites disabled (`ssl_ciphers ECDHE-ECDSA-AES256-GCM-SHA384:...`)
- [ ] SSH key-based only — no password SSH
- [ ] SSH port non-default or restricted to bastion
- [ ] Security groups: least-privilege port access
- [ ] VPN for admin access to database hosts

### 3.2 Server

- [ ] OS: Ubuntu 24.04 LTS or Debian 12, fully patched
- [ ] Automatic unattended-upgrades for security patches
- [ ] Fail2ban installed and configured
- [ ] UFW/iptables configured (deny all inbound except 80, 443)
- [ ] No root login via SSH
- [ ] AppArmor / SELinux enabled where applicable
- [ ] Log rotation configured — logs not accessible from web

### 3.3 Secrets Management

- [ ] No secrets in git history — use `git-secrets` or `trufflehog`
- [ ] Secrets managed via AWS Secrets Manager, Vault, or `.env` files with restricted permissions (600)
- [ ] API keys rotated at least annually
- [ ] Emergency key rotation procedure documented

---

## 4. Compliance

### 4.1 NDPR (Nigeria Data Protection Regulation)

- [ ] Privacy Policy published and accessible
- [ ] Data Processing Agreement with all sub-processors
- [ ] Patient consent recorded before data collection
- [ ] Patient right to access: export request within 30 days
- [ ] Patient right to erasure: closure request workflow
- [ ] Data breach notification within 72 hours
- [ ] DPO appointed and contact published
- [ ] Data audit trail maintained for 7 years minimum

### 4.2 Healthcare-Specific

- [ ] CDSS alerts clearly labelled as decision-support only
- [ ] Clinical overrides require justification and are audited
- [ ] Emergency access override audited and reviewed
- [ ] Prescription data access role-restricted
- [ ] Lab results access role-restricted

---

## 5. Incident Response

- [ ] Security incident response plan documented
- [ ] Contact list for breach notification (NITDA, affected patients, partners)
- [ ] Incident severity levels defined (P1–P4)
- [ ] P1 (data breach) — 30-minute response SLA, 72-hour NITDA notification
- [ ] Penetration test scheduled annually
- [ ] Vulnerability disclosure policy published
- [ ] Bug bounty programme (optional but recommended)

## WAF Configuration Reference

### Cloudflare WAF (Recommended for OpesCare)

1. **Zone setup:** Add your domain to Cloudflare, set DNS to proxied (orange cloud).
2. **WAF rules enabled:**
   - OWASP Core Rule Set (CRS) — Paranoia Level 2
   - Cloudflare Managed Rules — Enabled
   - Rate limiting: 100 requests/10s per IP on `/api/` routes
3. **Custom rules:**
   ```
   (http.request.uri.path matches "^/api/fhir" and not ip.src in {trusted_fhir_consumer_ips})
   → Block
   ```
4. **Bot management:** Enabled (Super Bot Fight Mode or Bot Management).
5. **DDoS protection:** HTTP DDoS Attack Protection — Sensitivity: High.

### AWS WAF (Alternative)
- Attach to Application Load Balancer
- Enable AWS Managed Rules: `AWSManagedRulesCommonRuleSet`, `AWSManagedRulesSQLiRuleSet`, `AWSManagedRulesKnownBadInputsRuleSet`
- Rate-based rule: 2000 requests/5min per IP

### Verification
After WAF is live, test with:
```bash
curl -H "User-Agent: sqlmap/1.0" https://api.opescare.com/api/health
# Expected: 403 blocked by WAF
```
