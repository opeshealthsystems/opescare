# OpesCare — Complete PRD, Architecture & Module Map

> **Living document.** Last updated: 2026-05-30. Update this file when modules ship or scope changes.
> Source of truth derived from exhaustive codebase exploration of every route, controller, module, model, and config file.

---

## Table of Contents

1. [Product Vision](#1-product-vision)
2. [Problem Statement](#2-problem-statement)
3. [Target Users & Personas](#3-target-users--personas)
4. [System Architecture](#4-system-architecture)
5. [Tech Stack](#5-tech-stack)
6. [Subdomain Map](#6-subdomain-map)
7. [Module Map — 33 Modules Built](#7-module-map--33-modules-built)
8. [Service Layer — app/Modules/](#8-service-layer--appmodules)
9. [Data Model Overview](#9-data-model-overview)
10. [Portal System & RBAC](#10-portal-system--rbac)
11. [Integration Surface](#11-integration-surface)
12. [Notification System — 5 Channels](#12-notification-system--5-channels)
13. [Mobile App — Patient](#13-mobile-app--patient)
14. [Artisan Commands & Scheduled Jobs](#14-artisan-commands--scheduled-jobs)
15. [Contracts, SDK & Widget](#15-contracts-sdk--widget)
16. [CI/CD & Security Scanning](#16-cicd--security-scanning)
17. [Key Flows](#17-key-flows)
18. [What's Built vs What's Missing](#18-whats-built-vs-whats-missing)
19. [Next Priorities](#19-next-priorities)

---

## 1. Product Vision

OpesCare is a **national-grade digital health platform** designed for Africa. It connects patients, clinicians, facilities, insurers, pharmacies, laboratories, NGOs, and government health authorities on a single interoperable infrastructure.

**One-sentence pitch:**
> OpesCare gives every patient a portable, verifiable Health ID and gives every care provider — from a rural clinic to a national hospital — the digital tools to use it.

**Core bets:**
- Health ID as the anchor (one ID per patient, lifetime, shareable with consent)
- Consent-first data sharing (patient controls who sees what)
- Interoperability by default (FHIR R4, HL7, DHIS2 built in, not bolted on)
- Works offline and in low-connectivity (OpesCare Lite, offline sync)
- Multi-tenant SaaS (one platform, many facilities, one national registry)
- Multi-channel communications (Email, WhatsApp, SMS, Push, Voice)

---

## 2. Problem Statement

| Problem | Who it affects | Current workaround |
|---------|---------------|-------------------|
| No portable patient identity across facilities | Patients | Paper cards, repeat registration |
| No patient consent layer for data sharing | Patients + Regulators | None — data shared without consent |
| Clinical data siloed per facility | Providers | Repeat tests, lost records |
| Insurance claims manual and slow | Insurers + Providers | Paper/email-based |
| Public health reporting fragmented | MoH / NGOs | Spreadsheets to DHIS2 |
| Pharmacies can't verify prescriptions | Pharmacists | Phone calls |
| Labs can't deliver results digitally | Labs + Patients | Paper printouts |
| Referrals lost between facilities | Providers | Phone/WhatsApp |
| Rural facilities have no digital tools | Front-line workers | Paper registers |
| Clinical alerts not automated | Doctors | Manual chart review |
| Staff scheduling done manually | HR managers | Spreadsheets |

---

## 3. Target Users & Personas

### B2C — Patients & Families

| Persona | Roles | Key Need |
|---------|-------|----------|
| Patient | `patient` | See my records, control who accesses them |
| Guardian | `guardian` | Manage child's health ID and consent |
| Caregiver | `caregiver` | Temporary care delegation |
| Emergency Contact | `emergency_contact` | Access critical data in a crisis |

### B2B — Healthcare Providers

| Persona | Roles | Key Need |
|---------|-------|----------|
| Doctor / Specialist | `doctor`, `specialist`, `consultant`, `resident`, `visiting_doctor` | See full patient history, order labs/Rx |
| Nurse / Midwife | `nurse`, `ward_nurse`, `midwife`, `nurse_supervisor` | Triage, vitals, ward rounds, discharge |
| Triage Nurse | `triage_nurse` | Emergency triage scoring |
| Receptionist | `receptionist`, `front_desk`, `appointment_coordinator` | Queue, appointments, check-in |
| Lab Technician | `labtech`, `lab_scientist`, `lab_manager`, `lab_validator`, `sample_collection` | Receive orders, enter results |
| Pharmacist | `pharmacist`, `pharmacy_manager`, `pharmacy_technician`, `medicine_stock`, `dispensing_officer` | Verify Rx, dispense, track inventory |
| Billing/Cashier | `cashier`, `billing_officer`, `finance_manager`, `refund_approver`, `wallet_ops` | Invoices, payments, reconciliation |
| Data Steward | `data_steward`, `data_import_officer`, `data_quality_reviewer` | Data imports, quality, correction |
| Queue Manager | `queue_manager`, `records_officer` | Queue and patient flow |

### B2B — Facility Administrators

| Persona | Roles | Key Need |
|---------|-------|----------|
| Facility Admin | `facility_admin`, `clinic_admin`, `hospital_admin`, `facility_ceo`, `branch_admin` | Configure facility, manage staff |
| Department Manager | `department_manager` | Department-level oversight |
| Privacy Officer | `privacy_officer`, `data_protection_officer` | Audit logs, consent reports, complaints |
| Security Officer | `security_officer` | Incidents, breach reports, access reviews |
| Finance Manager | `finance_manager`, `finance` | Revenue reports, billing oversight |
| Compliance Officer | `compliance_officer`, `audit_reviewer` | Audit exports, access reviews |

### B2B — Platform Admins

| Persona | Roles |
|---------|-------|
| Platform Admin | `platform_admin`, `super_admin`, `product_admin`, `system_admin` |
| Legal / Country | `legal_admin`, `country_admin`, `regional_admin` |
| Support | `support_agent`, `support_manager`, `customer_success`, `implementation_lead` |
| Partner | `partner_admin`, `partner_reviewer`, `partner_compliance`, `partner_technical` |
| Academy | `academy_admin` |

### B2B — Insurance

| Persona | Roles |
|---------|-------|
| Insurance Admin | `insurance_admin` |
| Claims Officer | `insurance_claims` |
| Preauth Reviewer | `insurance_preauth` |
| Finance | `insurance_finance` |
| Reviewer | `insurance_reviewer` |

### B2G — Government & NGO

| Persona | Roles |
|---------|-------|
| NGO Program Manager | `health_program_manager`, `ngo_admin` |
| Outreach Worker | `outreach_team`, `mobile_clinic_team` |
| Country/Regional Admin | `country_admin`, `regional_admin` |

### Developer / Partner / System

| Persona | Roles |
|---------|-------|
| Developer | `developer`, `developer_org_admin`, `api_partner`, `api_technical` |
| Webhook Manager | `webhook_manager` |
| Sandbox Dev | `sandbox_developer` |
| Bridge Agent | System auth via `VerifyBridgeAgent` middleware |

---

## 4. System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                            CLIENT LAYER                                  │
│  ┌──────────────┐  ┌─────────────────┐  ┌──────────────────────────┐  │
│  │ Patient App  │  │  Web Portals     │  │  B2B Partner Systems     │  │
│  │ (Flutter)    │  │  (Blade/Laravel) │  │  Connect Widget / SDK    │  │
│  └──────┬───────┘  └────────┬─────────┘  └──────────────┬───────────┘  │
└─────────┼────────────────────┼────────────────────────────┼─────────────┘
          │                    │                            │
          ▼                    ▼                            ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                     NGINX REVERSE PROXY LAYER                            │
│   SSL termination | Subdomain routing | Rate limiting | Static assets    │
│   14 subdomains → single Laravel app (see Subdomain Map)                 │
└──────────────────────────────────┬──────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    LARAVEL APPLICATION (v13.8, PHP 8.3+)                 │
│                                                                          │
│  routes/web.php (641 lines)    routes/api.php (909 lines)                │
│  routes/partners.php (51)      routes/communications.php (69)            │
│  routes/academy.php (38)       routes/console.php                        │
│                                                                          │
│  ┌────────────────────────────────────────────────────────────────────┐ │
│  │  MIDDLEWARE STACK (20 custom middleware classes)                    │ │
│  │  ForceHttps → AddSecurityHeaders → SetLocale → auth guards         │ │
│  │  EnsurePortalAccess → RequireFacilityContext → GuardianAccess      │ │
│  │  VerifyIntegrationClient → IdempotencyProtection → ThrottleByClient│ │
│  │  RequireConsentGrant → VerifySdkToken → VerifyBridgeAgent          │ │
│  │  VerifyPartnerTrustLevel → LogApiUsage → DemoDataScope             │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────┐  ┌──────────────┐  │
│  │ Core API     │  │ Connect API  │  │ FHIR R4   │  │ Mobile API   │  │
│  │ (B2C+ops)    │  │ (B2B interop)│  │ (interop) │  │ (patient+    │  │
│  └──────┬───────┘  └──────┬───────┘  └─────┬─────┘  │  provider)  │  │
│         └─────────────────┴──────────────────┘       └─────────────┘  │
│                            │                                             │
│  ┌─────────────────────────▼────────────────────────────────────────┐  │
│  │             SERVICE LAYER — app/Modules/ (40+ modules)            │  │
│  │  Each module: Services/ + Models/ + Policies/ + Enums/            │  │
│  │  Modules: PatientIdentity, ConsentManagement, AccessControl,      │  │
│  │  EncounterManagement, Governance, PublicHealth, Partners,         │  │
│  │  Notifications (5 providers), Tasks, Messaging, Broadcasts,       │  │
│  │  Academy, CareMap, Appointments, Billing, FacilityReadiness,      │  │
│  │  Immunization, Offline, OperationalFlow, Queue, Referral,         │  │
│  │  Search, Support + more                                           │  │
│  └──────────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────┬──────────────────────────────────────┘
                                   │
          ┌─────────────┬──────────┴──────────┬──────────────┐
          ▼             ▼                      ▼              ▼
  ┌──────────────┐ ┌──────────────┐  ┌───────────────┐ ┌──────────────┐
  │ PostgreSQL   │ │ Redis Cache  │  │ File Storage  │ │ Laravel      │
  │ + read       │ │ + Horizon    │  │ Local → S3    │ │ Horizon      │
  │ replicas     │ │ queue mgmt   │  │               │ │ (queue UI)   │
  └──────────────┘ └──────────────┘  └───────────────┘ └──────────────┘
          │
    ┌─────┴──────────────────────────────────────────────┐
    │              EXTERNAL INTEGRATIONS                   │
    │  DHIS2 | HL7 | WhatsApp Business API | KMS (AWS)    │
    │  Africa's Talking (USSD/SMS) | SMTP | Snyk          │
    └──────────────────────────────────────────────────────┘
```

### Request Flow — Web Portal

```
Browser → /portals/{portal}/{page}
           │
           ▼
ForceHttps → AddSecurityHeaders → SetLocale → auth (session)
           │
           ▼
EnsurePortalAccess middleware (checks 60+ role map)
  → 403 if role not allowed for this portal
           │
           ▼
Portal Controller (MedicalId/ namespace) → Blade View
```

### Request Flow — B2B Connect API

```
Partner System
  1. POST /api/v1/connect/auth/token      (client_id + client_secret in body)
  2. Bearer token issued with scopes
  3. POST /api/v1/connect/patients/search (VerifyIntegrationClient + throttle.client:200,1)
  4. GET  /api/v1/connect/patients/{id}/summary
       → RequireConsentGrant middleware checks patients:read scope
  5. POST /api/v1/connect/records/encounters
       → IdempotencyProtection + consent.grant:patients:write
```

### Request Flow — Mobile Patient

```
Flutter App
  1. POST /api/mobile/auth/login-email    (email + password, throttle:5,1)
     or POST /api/mobile/auth/login → /auth/otp/verify (phone+PIN+OTP)
  2. Bearer token stored in Flutter Secure Storage
  3. GET  /api/mobile/health-id-card      (auth.mobile middleware)
  4. GET  /api/mobile/timeline
  5. POST /api/mobile/consent-requests/{id}/approve
  6. POST /api/mobile/appointments        (book)
```

---

## 5. Tech Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| **Backend framework** | Laravel 13.8, PHP 8.3+ | Monolithic, modular service layer |
| **Database** | PostgreSQL | Read replica support, 121 migrations |
| **Cache / Session** | Redis | Session driver, cache store |
| **Queue** | Redis + Laravel Horizon | Background jobs, queue monitoring dashboard |
| **File storage** | Local (Laragon dev) → S3-compatible | Config via `config/filesystems.php` |
| **PDF generation** | barryvdh/laravel-dompdf ^2.2 | Documents, reports, exports |
| **QR codes** | chillerlan/php-qrcode ^6.0 | Health ID QR generation |
| **Backup** | spatie/laravel-backup ^10.2 | Encrypted automated backups |
| **Web frontend** | Blade + Alpine.js / Livewire | Server-rendered portals |
| **Frontend build** | Vite + npm | `vite.config.js` |
| **Mobile app** | Flutter 3.3+ (Dart 3.3+) | iOS + Android + Web targets |
| **Mobile state** | Riverpod 2.5.1 | Feature-based modular providers |
| **Mobile HTTP** | Dio 5.4.3 | Interceptors, token injection |
| **Mobile routing** | GoRouter 13.2.0 | Declarative navigation |
| **Mobile storage** | Flutter Secure Storage 9.0.0 | Tokens never in SharedPreferences |
| **Mobile icons** | Lucide Icons ^0.257.0 | |
| **Mobile fonts** | Google Fonts ^6.2.1 | |
| **Mobile shimmer** | shimmer ^3.0.0 | Loading skeletons |
| **Mobile i18n** | intl ^0.19.0 | |
| **API standards** | REST + FHIR R4 | Interoperability layer |
| **HL7 messaging** | Custom (`config/hl7.php`) | Legacy HIS sync via Bridge |
| **Public health** | DHIS2 (`config/dhis2.php`) | Disease surveillance push |
| **KMS** | AWS KMS or local (`config/kms.php`) | Patient PII encryption |
| **USSD** | Africa's Talking | Low-tech patient access |
| **WhatsApp** | WhatsApp Business API | Notification delivery channel |
| **SMS** | Configurable provider | Notification delivery channel |
| **Auth** | Custom token per client type | JWT-style, scoped per role |
| **Dev environment** | Laragon (Windows) | Virtual host `opescare.test` |
| **CI/CD** | GitHub Actions | `deploy.yml`, `snyk-security.yml` |
| **Security scanning** | Snyk (`.snyk`) | Dependency vulnerability scanning |

---

## 6. Subdomain Map

14 production subdomains — all served by the same Laravel app, routed by Nginx:

| Subdomain | Purpose |
|-----------|---------|
| `app.opescare.com` | Main portal (staff, patient, admin, insurance) |
| `api.opescare.com` | REST API for B2B integrations |
| `connect.opescare.com` | Connect Platform (B2B interoperability) |
| `fhir.opescare.com` | FHIR R4 endpoints |
| `mobile-api.opescare.com` | Patient + provider mobile API |
| `lite.opescare.com` | OpesCare Lite (low-connectivity) |
| `academy.opescare.com` | Training & certification platform |
| `developer.opescare.com` | Developer self-service portal |
| `docs.opescare.com` | API documentation |
| `caremap.opescare.com` | Verified Care Access Map |
| `bridge.opescare.com` | Bridge agent sync endpoint |
| `public-health.opescare.com` | Public health reporting |
| `ussd.opescare.com` | USSD interface (Africa's Talking) |
| `status.opescare.com` | System status page |

Subdomain enforcement: `EnforceSubdomainScope` middleware + `SUBDOMAIN_ROUTING` env flag.

---

## 7. Module Map — 33 Modules Built

### Module 1: Patient Identity (Health ID)
**Status: Built**

- Health ID issuance, QR code generation (`chillerlan/php-qrcode`)
- Temporary QR for facility visits
- Public verification endpoint (`/verify/health-id`, `/verify/qr/{token}`)
- Health ID card endpoint (`GET /mobile/health-id-card`)
- B2B resolution: `POST /api/v1/connect/patients/resolve` (find or auto-create)
- B2B verification: `POST /api/v1/connect/medical-ids/verify`, `verify-qr`
- Duplicate detection and merge cases (`DuplicateMergeController`)
- Master Patient Index (`PatientIdentityService`)
- Controllers: `MedicalId/VerifyController`, `Connect/HealthIdResolutionController`, `Connect/DuplicateMergeController`

---

### Module 2: Authentication & Authorization
**Status: Built**

| Auth type | Endpoint / Mechanism |
|-----------|---------------------|
| Web portal login (all roles) | `POST /login` → session |
| Patient mobile: email+password | `POST /mobile/auth/login-email` |
| Patient mobile: phone+PIN+OTP | `POST /mobile/auth/login` + `/auth/otp/verify` |
| Provider mobile: phone+OTP | `POST /provider-mobile/auth/login` + `/auth/otp/verify` |
| B2B client credentials | `POST /api/v1/connect/auth/token` |
| SDK token | `VerifySdkToken` middleware |
| Bridge agent | `VerifyBridgeAgent` middleware |
| Portal RBAC | `EnsurePortalAccess` middleware (60+ roles) |
| Staff invite | `GET/POST /invite/{token}` |
| Facility selector | `GET/POST /select-facility` (multi-facility users) |
| Language switch | `GET /lang/{locale}` (en, fr) |
| OTP challenge | `GET/POST /verify/otp` + resend |
| Password reset | `/forgot-password`, `/reset-password/{token}` |
| Demo login-as | `POST /demo-access/login-as` (demo mode only) |

---

### Module 3: Appointments
**Status: Built**

API (`routes/api.php`):
- `GET/POST /api/v1/appointments` — list, create
- `POST /api/v1/appointments/{id}/reschedule`, `/cancel`, `/check-in`
- `POST /api/v1/appointments/no-shows`
- `GET/POST /mobile/appointments` — patient mobile list + **book**
- `POST /mobile/appointments/{id}/cancel`
- `GET /mobile/facilities/{id}/slots` — available slots

Staff portal (`routes/web.php`):
- Create, confirm, cancel, check-in, no-show
- `AppointmentService` in `app/Modules/Appointments/Services/`

Analytics: `GET /api/v1/analytics/appointments`

---

### Module 4: Queue Management
**Status: Built**

- `GET /api/v1/queues/tickets`, `POST /queues/check-ins`
- Call next, start service, transfer, prioritize, complete, cancel
- `GET /api/v1/queues/display` — public display board
- Staff portal: check-in, call, start, complete
- Provider mobile: `GET /provider-mobile/tasks` + call + complete
- `QueueService` in `app/Modules/Queue/Services/`

---

### Module 5: Clinical Encounters & Visits (CDSS included)
**Status: Built**

**Visit flow (web portal):**
- `POST /portals/staff/visits` → store
- `POST /portals/staff/visits/{id}/transition` → state change
- `POST /portals/staff/visits/{id}/triage` + triage escalate
- `POST /portals/staff/visits/{id}/consult` → consultation notes

**Triage API:**
- `GET/POST /api/v1/triage` — active triage list, create
- `POST /api/v1/triage/{id}/score`, `/reassess`, `/escalate`

**CDSS (Clinical Decision Support System) — Staff Portal:**
- `GET /portals/staff/cdss/rules` — clinical rules
- `GET /portals/staff/cdss/lab-rules` — lab threshold rules
- `GET /portals/staff/cdss/drug-interactions` — drug-drug interaction checker
- `GET /portals/staff/cdss/patients/{id}/alerts` — patient-specific alerts
- `GET /portals/staff/cdss/visits/{id}/alerts` — visit-specific alerts
- `POST /portals/staff/cdss/run-checks` — run checks on demand
- `POST /portals/staff/cdss/alerts/{id}/acknowledge`, `/override`, `/dismiss`

**Connect API (B2B):**
- `POST /api/v1/connect/records/encounters` (consent.grant:patients:write + idempotency)
- `GET /api/v1/connect/patients/{id}/summary` (consent.grant:patients:read)
- `GET /api/v1/connect/patients/{id}/emergency-profile`

Services: `ConsultationService`, `VisitManagementService`

---

### Module 6: Laboratory
**Status: Built**

- `POST /api/v1/connect/records/lab-results` (B2B write, consent.grant:labs:write)
- `GET/POST /mobile/labs`, `GET /mobile/labs/{id}` — patient mobile
- Lab portal: orders, results, samples, `mark-collected`, `mark-processing`
- Staff portal: `GET /portals/staff/lab-orders`
- Admin portal: `GET /portals/admin/clinical/lab-orders`
- FHIR: `GET /fhir/R4/DiagnosticReport`, search + individual
- LOINC code mapping (admin code system mappings)

---

### Module 7: Pharmacy & Prescriptions
**Status: Built**

- `POST /api/v1/connect/records/prescriptions` (B2B write, consent.grant:prescriptions:write)
- `GET/POST /mobile/prescriptions`, `GET /mobile/prescriptions/{id}` — patient mobile
- Pharmacy portal: prescriptions, dispense, inventory, controlled substances view
- Staff portal: `GET /portals/staff/prescriptions` (clinical register)
- Admin portal: `GET /portals/admin/clinical/prescriptions`
- Drug formulary: `GET /api/v1/pharmacy/formulary/search`, controlled list, store, toggle availability
- Controlled substances: dispense, witness confirmation, reconcile, log, inventory
- FHIR: `GET /fhir/R4/MedicationRequest`
- ATC code mapping (admin)
- `DrugFormularyController`, `ControlledSubstanceController`

---

### Module 8: Billing & Finance
**Status: Built**

- `GET/POST /api/v1/billing/invoices` — list, create
- `POST /billing/invoices/{id}/payments` — record payment
- `POST /billing/payments/{id}/refund`
- `POST /billing/wallets/deposit`
- `POST/POST /billing/cashier-sessions` — open, close
- Payment plans: `POST /api/v1/payment-plans`, `GET /payment-plans/{id}`, installment payment, patient plans list
- Revenue cycle: summary, aging, denials, trend
- Insurance FHIR: `GET /fhir/R4/Coverage`
- Staff portal: billing create, pay
- `BillingService`, `PaymentService` in `app/Modules/Billing/Services/`

---

### Module 9: Insurance
**Status: Built**

API:
- `POST /api/v1/insurance/eligibility/check`
- `POST /api/v1/insurance/preauth`, `/preauth/{id}/decide`
- `GET/POST /api/v1/insurance/claims`, submit, decide, post payment

Insurance portal (web):
- Providers: list, create, add plans
- Policies: list, create, activate, deactivate, add eligibility rules
- Preauths: list, create, submit, decide (approve/deny/partial), cancel
- Claims: list, create, submit, decide, cancel, mark paid

---

### Module 10: Referrals
**Status: Built**

- `GET/POST /api/v1/referrals`, show, send, accept, reject, complete, cancel
- `POST /api/v1/referrals/expire-stale` — auto-expiry
- Staff portal: list, create, send, accept, reject, complete, cancel (7 actions)
- `ReferralService` in `app/Modules/Referral/Services/`

---

### Module 11: Consent & Governance
**Status: Built**

- `POST /api/v1/connect/consents/request` (B2B: request consent from patient)
- `POST /api/v1/connect/consents/verify` (check active consent)
- Patient mobile: list, approve, deny, revoke consent
- Patient web portal: approve, deny
- `RequireConsentGrant` middleware enforces on every B2B data read/write
- Consent scopes: `patients:read`, `patients:write`, `labs:write`, `prescriptions:write`
- `ConsentManagementService` + `ConsentController` in Connect namespace
- FHIR: `GET /fhir/R4/Consent`

**Patient rights (admin):**
- Emergency access: request (B2B), review (admin), audit
- Data correction requests: admin list, approve, reject
- Data export requests: patient create + list + download; admin approve, reject
- Account closures: admin review
- Privacy complaints: admin resolve
- Minor age transitions: admin manage
- Country policies: list, create, update, publish
- Security incidents: list, create, contain, resolve

Services: `AccessLogService`, `CorrectionRequestService`, `DataExportService`, `CountryPolicyService`, `EmergencyAccessService`

---

### Module 12: Verifiable Documents
**Status: Built**

- `GET/POST /api/v1/documents` — list, create (digitally signed)
- `POST /documents/{id}/amend`, `/revoke`, `/entered-in-error`
- `POST /document-verification/verify-code`
- `POST /documents/{id}/share-links` — time-limited share links
- Public: `GET /verify/document/{token}`, `GET /share/document/{token}`
- Document render: `GET /documents/{id}/view`
- FHIR: `GET /fhir/R4/DocumentReference`

---

### Module 13: Ward & Admissions
**Status: Built**

- `POST /api/v1/ward/admissions` — admit
- `POST /admissions/{id}/assign-bed`, `/transfer`, `/discharge`
- `POST /admissions/{id}/nursing-round`, `/discharge-plan`
- `GET /api/v1/ward/beds/availability`
- Staff portal: wards list, create ward, admissions, admit, discharge, transfer
- Analytics: `GET /portals/staff/analytics/ward`

---

### Module 14: Maternity & Antenatal
**Status: Built**

- `GET/POST /api/v1/maternity/patients/{id}/pregnancies`
- `GET /maternity/pregnancies/{id}`, antenatal visits (list+create), deliveries (list+create)
- Models: `Pregnancy`, `AntenatalVisit`, `Delivery`

---

### Module 15: Telemedicine
**Status: Built (video provider not yet wired)**

- `POST /api/v1/telemedicine/consultations` — book
- `GET /consultations/{id}`, cancel, record consent, join waiting room, initiate call, end call
- Staff portal: list, create, waiting room, call next, show, consent, start, end, cancel
- `TelemedicineController` in both web and API

> **Gap:** Video call provider not integrated. Session model is ready; needs Jitsi, Daily.co, or similar.

---

### Module 16: Public Health Surveillance
**Status: Built**

Phase 1 (Reports):
- `GET /api/v1/public-health/report-types`, reports list, single report
- `POST /reports/generate-drafts`
- `GET /reports/{id}/quality-checks`, dashboard, facility-dashboard

Phase 2 (Governance):
- Submit for review, assign, approve, request correction, reject, cancel, correct
- `GET /reports/{id}/versions`, status-history, review-queue

Phase 3 (Submissions):
- Submission profiles (list, create)
- Submit report, export, download export, integration status

Phase 4 (Intelligence):
- `GET /api/v1/public-health/signals`, single signal
- `POST /signals/trigger-detection`, `POST /signals/{id}/review`
- Intelligence dashboard, trends, shortages

Services: `DraftGenerationService`, `DataQualityCheckService`, `ExportService`, `SignalDetectionService`

---

### Module 17: Immunizations
**Status: Built**

- `GET/POST /api/v1/immunizations` — list, record
- `POST /immunizations/schedule` — create schedule; `GET /immunizations/schedule` — patient schedule
- `GET /immunizations/{id}`, `POST /{id}/adverse-reactions`
- Staff portal: list, record form, store
- Patient mobile: `GET /mobile/immunizations`
- FHIR: `GET /fhir/R4/Immunization`
- `ImmunizationService` in `app/Modules/Immunization/Services/`

---

### Module 18: CareMap (Verified Facility Directory)
**Status: Built**

Public (no auth):
- `GET /api/v1/care-map/facilities`, show, search, nearby
- `GET /care-map/pharmacies/medicine-search`
- `GET /care-map/labs/test-search`
- `GET /care-map/blood/search`
- `GET /care-map/emergency`
- Web: `GET /care-map`, facility profile, emergency page

Authenticated:
- `POST /care-map/facilities/{id}/save` — save to favourites
- `POST /care-map/facilities/{id}/report` — report inaccuracy
- `POST /care-map/facilities/{id}/claim` — claim facility
- `POST /care-map/partner/facilities/{id}/stock-sync` — partner sync

Admin:
- `POST /admin/care-map/facilities/{id}/verify`
- `POST /admin/care-map/facilities/{id}/suspend`
- `GET /admin/care-map/governance`

Services: `FacilityVerificationService`, `FacilityReportService`, `FacilityFreshnessService`, `InsuranceNetworkSearchService`, `MapProviderService`, `PharmacyStockSearchService`, `BloodAvailabilitySearchService`, `LabTestSearchService`

---

### Module 19: Connect Platform (B2B Interoperability)
**Status: Built**

- Token issuance (`POST /api/v1/connect/auth/token`, client_credentials)
- Widget sessions (`POST /connect/widget/sessions`)
- Patient search (`POST /connect/patients/search`)
- Health ID resolve/verify
- Consent request + verify
- Emergency access request + emergency profile
- Record pulls: patient summary (`patients:read` scope), emergency profile
- Record writes: encounters, lab-results, prescriptions (all require consent + idempotency)
- Inventory sync: pharmacy stock, blood stock
- Webhooks: create subscription, replay event
- Reconciliation cases: list, resolve
- Per-client rate limit: 200 req/min

Separate Controllers in Connect namespace:
- `AuthController`, `PatientSearchController`, `HealthIdResolutionController`
- `ConnectGovernanceController`, `RecordController`, `InventoryController`
- `WebhookController`, `ReconciliationController`
- `MedicalIdVerificationController`, `ConsentController`, `EmergencyAccessController`
- `DuplicateMergeController`

---

### Module 20: FHIR R4 API
**Status: Built (read-only + Subscription + Bulk Export)**

| Resource | Endpoints |
|----------|----------|
| CapabilityStatement | `GET /fhir/R4/metadata` (public) |
| Patient | search, get, `$everything` (consent required) |
| Encounter | search, get |
| DiagnosticReport | search, get |
| MedicationRequest | search, get |
| Practitioner | search, get |
| Organization | search, get |
| DocumentReference | search, get |
| Consent | search, get |
| Coverage | search, get |
| Immunization | search, get |
| AllergyIntolerance | search, get |
| Condition | search, get |
| Subscription | list, create, get, delete |
| Bulk Export | `GET /fhir/R4/$export`, `GET /Patient/{id}/$export` |

Auth: `VerifyIntegrationClient` on all except metadata.

> **Gap:** FHIR write (POST/PUT individual resources) not implemented.

---

### Module 21: OpesCare Lite (Low-Connectivity)
**Status: Built**

API:
- Device registration, config sync, push/pull sync
- Offline events, conflict resolution, formulary download

Lite portal (web, simplified UI):
- Dashboard, patient lookup, register patient, check-in
- Consultation, billing, device management (activate/revoke)
- Conflicts list + resolve, offline events per device

`SyncService` in `app/Modules/Offline/Services/`

---

### Module 22: Bridge Agent
**Status: Built**

- `POST /api/v1/bridge/sync` — sync payload from legacy HIS
- `POST /bridge/heartbeat` — agent keepalive
- `GET /bridge/status`
- Rate limit: 300 req/min, `VerifyBridgeAgent` auth
- HL7 messaging support (`config/hl7.php`)
- Admin portal: bridge management (create, toggle, batches)
- `bridge-agent/config.json` — agent configuration

---

### Module 23: SDK
**Status: Built**

- `GET /api/v1/sdk/patients/{id}/summary` (scope: `read_records`)
- `GET /sdk/patients/{id}/encounters` (scope: `read_records`)
- `GET /sdk/facilities/{id}` (scope: `read_facility`)
- `GET /sdk/facilities/{id}/stock` (scope: `read_stock`)
- `POST /sdk/appointments` (scope: `write_appointments`)
- `GET /sdk/appointments/{id}` (scope: `read_appointments`)
- `POST/DELETE /sdk/webhooks/subscriptions` (scope: `manage_webhooks`)
- `GET /sdk/token/introspect`
- Rate limit: 120 req/min, `VerifySdkToken` middleware
- **PHP SDK**: `sdk/php/src/Client.php`
- **Connect Widget**: `widget/connect-widget.html`
- **OpenAPI contracts**: `contracts/openapi/opescare-connect-v1.yaml`, `opescare-mobile-v1.yaml`

---

### Module 24: Developer Portal
**Status: Built**

- Dashboard, onboarding wizard
- Apps: list, create, view (credentials shown once, rotate secret)
- Production access requests: list, create
- Webhook delivery logs per app
- API usage analytics
- Admin side: approve/reject production requests, suspend developer accounts
- `DeveloperPortalController`

---

### Module 25: Admin Control Centre
**Status: Built**

- `/portals/admin/cc/settings` — system settings (get + update)
- `/portals/admin/cc/feature-flags` — toggle per feature key
- `/portals/admin/cc/modules` — enable/disable modules
- `/portals/admin/cc/maintenance` — maintenance windows (create, toggle)
- `/portals/admin/cc/health` — system health checks
- `/portals/admin/cc/audit` — audit log viewer
- Facility go-live readiness: show, create checklist, mark items, approve
- Facility onboarding: list, show, mark items, approve
- KPI dashboard: index, trend, export, recompute
- Code system mappings (LOINC/ICD-10/ATC): list, create, approve, reject, delete
- Integration certifications: list, create, show, record test run, issue/revoke badge, seed requirements
- Global search: `GET /api/v1/admin/global-search` (`GlobalSearchService`)

---

### Module 26: Communications & Notifications
**Status: Built**

Routes in `routes/communications.php`:

**Notifications:**
- List, unread count, mark read, acknowledge, archive

**Tasks:**
- CRUD, acknowledge, complete, assign, escalate
- `ActionTask` model, `TaskService`

**Notification templates (admin):**
- CRUD, review workflows, approval, publishing, rollback

**Deliveries:**
- List, retry failed

**Escalation chains:**
- CRUD, activate/deactivate
- `AlertEscalationService`

**Broadcasts:**
- CRUD, publish, cancel
- `BroadcastService`

**Messaging threads:**
- Threads, messages, participants, assignment, closure
- `MessageThread`, `Message`, `MessageAttachment`, `MessageThreadParticipant`
- `MessagingService`, `MessagePermissionService`, `MessageAttachmentService`

**USSD:** `POST /ussd/callback` (Africa's Talking, no auth)

---

### Module 27: Security Operations
**Status: Built**

- `GET /api/v1/security/audit-log` — searchable audit trail
- `GET /security/suspicious-flags`, review flag
- `POST /security/breaches` — open breach; notify, close
- `POST /security/access-reviews` — initiate, complete
- `POST /security/compliance-exports`
- Pen test tracker: list tests, open findings, create, show, add finding, update finding
- Admin portal: incidents (create, update), emergency access review, audit explorer
- `AddSecurityHeaders` middleware on all responses

---

### Module 28: Staff HR & Scheduling
**Status: Built**

API (`routes/api.php`):
- `GET /api/v1/staff` — directory
- `GET /staff/{id}`, `PATCH /staff/{id}` — profile
- `GET /staff/rosters`, `POST /staff/shifts`, `DELETE /staff/shifts/{id}`
- `POST /staff/leave` — request, approve, reject

Staff portal (`routes/web.php`):
- **Directory:** list, create, status toggle, add licence
- **Shifts:** list, create, toggle (enable/disable)
- **Roster:** list, create, publish, archive, assign staff, unassign
- **Leave:** list, create, approve, reject, withdraw

---

### Module 29: Analytics & Reporting
**Status: Built**

- `GET /api/v1/analytics/facilities/{id}/dashboard`
- `GET /analytics/appointments`, queue, billing stats
- `POST /analytics/exports`, status check
- Provider performance: summary, top diagnoses, facility summary
- Revenue cycle: summary, aging, denials, trend
- Survey reports: `GET /api/v1/reports/surveys/satisfaction`
- Staff portal analytics: queue, ward, financial, data-quality dashboards
- `AnalyticsDashboardController` (web)

---

### Module 30: Care Plans & Advance Directives
**Status: Built**

- `POST /api/v1/care-plans`, `GET /care-plans/{id}`
- `POST /care-plans/{id}/goals`, `PATCH /goals/{goalId}`
- `POST /care-plans/{id}/interventions`
- Patient mobile (read-only): `GET /mobile/care-plans`, `GET /mobile/care-plans/{id}`
- Advance directives: `GET/POST /api/v1/patients/{id}/advance-directives`, show, destroy

---

### Module 31: Academy (Training & Competency)
**Status: Built**

Routes in `routes/academy.php`:
- Courses (list, enroll), lessons, completion
- Quizzes: start, submit
- Simulations: start, submit
- Certificates: verify (public `/verify/certificate/{token}`), revoke, renew
- Trainer sign-offs
- Facility readiness dashboard
- Academy learner dashboard (auth web)

Services: `EnrollmentService`, `QuizService`, `SimulationService`, `CertificateService`, `CertificateVerificationService`, `CompetencyGateService`, `AcademyReportingService`, `CourseService`

---

### Module 32: Partner Management
**Status: Built**

Routes in `routes/partners.php`:
- Partner governance: list, approve, suspend
- Partner documents verification
- Agreements + certifications
- Access + contribution permissions
- Integration cases
- Partner dashboard

Partner module has rich modelling:
- `Partner`, `PartnerFacility`, `PartnerProfessional`, `PartnerContact`
- `PartnerAgreement`, `PartnerDocument`, `PartnerIntegration`, `PartnerGovernanceCase`
- `PartnerAccessPermission`, `PartnerContributionPermission`, `PartnerContribution`
- `PartnerQualityScore`, `PartnerRiskScore`, `PartnerAuditLog`
- Enums: `PartnerType`, `PartnerStatus`, `TrustLevel`
- Policies: `PartnerPolicy`, `PartnerDocumentPolicy`, `PartnerAgreementPolicy`, `PartnerPermissionPolicy`, `PartnerGovernanceCasePolicy`, `PartnerIntegrationPolicy`
- Services: `PartnerApplicationService`, `PartnerPermissionService`, `PartnerAuditService`, `PartnerVerificationService`, `PartnerAgreementService`, `PartnerContributionService`, `PartnerQualityScoreService`, `PartnerRiskScoreService`, `PartnerIntegrationGovernanceService`

---

### Module 33: Subscriptions & SaaS Billing
**Status: Built (manual, not automated)**

- `GET /api/v1/subscriptions/plans`, plan detail
- `GET /subscriptions/my` — current subscription
- `POST /subscriptions` — subscribe, upgrade, cancel
- `GET /subscriptions/usage`, feature limit check
- Admin portal: plan CRUD + toggle, subscription management (detail, cancel, renew, pause, reactivate, change plan), invoices + mark-paid

> **Gap:** No automated payment gateway (Paystack/Stripe). Billing is currently manual admin action.

---

### Module 34: Data Import
**Status: Built**

Staff portal data import flow (upload → map → validate → preview → approve → rollback):
- Upload file (`POST /portals/staff/data-import`)
- Column mapping (`GET/POST /data-import/{id}/mapping`)
- Validate (`POST /data-import/{id}/validate`)
- Preview (`GET /data-import/{id}/preview`)
- Approve (`POST /data-import/{id}/approve`)
- Rollback (`POST /data-import/{id}/rollback`)
- Cancel + audit log

---

### Module 35: Supply Chain
**Status: Built**

Staff portal supply chain:
- Items: list, create
- Suppliers: list, create
- Stock: list, receive stock, adjust
- Purchase orders: list, create, approve
- Goods receipts: list, create
- Stock movements: list

---

### Module 36: Radiology
**Status: Built (API only — no portal UI)**

- `POST /api/v1/radiology/reports` — store report
- `GET /radiology/reports/{id}`, finalize, amend, distribute
- `GET /radiology/facilities/{id}/reports/pending`

> **Gap:** No staff portal view for radiology results. API is complete.

---

### Module 37: File Storage
**Status: Built**

Staff portal file management:
- `GET /portals/staff/files` — list files
- `GET /files/upload` → `POST /files` — upload attachment
- `GET /files/{id}/download`
- `DELETE /files/{id}`

---

### Module 38: Patient Satisfaction Surveys
**Status: Built**

- `GET /api/v1/reports/surveys/satisfaction` — reporting endpoint
- Patient mobile: `GET /mobile/surveys`, show, `POST /surveys/{id}/submit`
- `MobileSurveyController`

---

### Module 39: Medical Record Export
**Status: Built**

Patient mobile:
- `POST /mobile/medical-records/export/pdf` — PDF export
- `POST /mobile/medical-records/export/fhir` — FHIR bundle export
- `MedicalRecordExportController`

---

### Module 40: Demo Mode
**Status: Built**

- `GET /demo-access` — demo landing
- `GET /demo-access/public`, `/demo-access/internal`
- `POST /demo-access/login-as` — login as any demo persona
- `POST /api/demo/reset` — reset demo data (calls artisan command)
- `DemoAccessController`, `DemoDataScope` middleware, `DemoSessionMiddleware`
- `config/demo.php`, seeder: `DemoSeedCommand`, reset: `DemoResetCommand`

---

## 8. Service Layer — app/Modules/

The service layer lives in `app/Modules/`. Each module follows the pattern:
```
app/Modules/{Module}/
  Services/     — Business logic (called from controllers)
  Models/       — Eloquent models specific to this module
  Policies/     — Laravel authorization policies
  Enums/        — PHP 8.1+ enums
```

**Confirmed modules with service classes:**

| Module | Key Services |
|--------|-------------|
| `AccessControl` | `EmergencyAccessService` |
| `Appointments` | `AppointmentService` |
| `Academy` | `EnrollmentService`, `QuizService`, `SimulationService`, `CertificateService`, `CertificateVerificationService`, `CompetencyGateService`, `AcademyReportingService`, `CourseService` |
| `Billing` | `BillingService`, `PaymentService` |
| `Broadcasts` | `BroadcastService` |
| `CareMap` | `FacilityVerificationService`, `FacilityReportService`, `FacilityFreshnessService`, `InsuranceNetworkSearchService`, `MapProviderService`, `PharmacyStockSearchService`, `BloodAvailabilitySearchService`, `LabTestSearchService` |
| `ConsentManagement` | `ConsentManagementService` |
| `EncounterManagement` | `ConsultationService`, `VisitManagementService` |
| `FacilityReadiness` | `FacilityGoLiveService` |
| `Governance` | `AccessLogService`, `CorrectionRequestService`, `DataExportService`, `CountryPolicyService`, `EmergencyAccessService` |
| `Immunization` | `ImmunizationService` |
| `Messaging` | `MessagingService`, `MessagePermissionService`, `MessageAttachmentService` |
| `Notifications` | `NotificationService`, `NotificationTemplateRenderer`, `NotificationPreferenceService`, `AlertEscalationService`, `VoiceNotificationService` |
| `Offline` | `SyncService` |
| `OperationalFlow` | `PatientJourneyService` |
| `Partners` | `PartnerApplicationService`, `PartnerPermissionService`, `PartnerAuditService`, `PartnerVerificationService`, `PartnerAgreementService`, `PartnerContributionService`, `PartnerQualityScoreService`, `PartnerRiskScoreService`, `PartnerIntegrationGovernanceService` |
| `PatientIdentity` | `PatientIdentityService` |
| `PublicHealth` | `DraftGenerationService`, `DataQualityCheckService`, `ExportService`, `SignalDetectionService` |
| `Queue` | `QueueService` |
| `Referral` | `ReferralService` |
| `Search` | `GlobalSearchService` |
| `Support` | `SupportService` |
| `Tasks` | `TaskService` |

---

## 9. Data Model Overview

### Core identity cluster
```
User (auth) ─── Patient ─── HealthId
                    │
                    ├── Guardian (linked patients)
                    ├── ConsentGrant (per scope, per client)
                    ├── ConsentAudit (automatic on each access)
                    ├── AccessLog (every data touch)
                    └── FamilyLink
```

### Clinical cluster
```
Patient
  └── Appointment (AppointmentSlot, AppointmentType, AppointmentWaitlist)
  └── Queue (QueueTicket, QueueDisplay)
  └── Visit / Encounter
        ├── Diagnosis (ICD-10)
        ├── Procedure
        ├── VitalSign
        └── Referral
  └── LabOrder → LabResult
  └── Prescription (MedicationRequest)
  └── AllergyRecord
  └── ConditionRecord
  └── ImmunizationRecord
  └── AntenatalVisit, Delivery, Pregnancy
  └── Admission → Bed, BedAssignment, NursingRound, DischargePlan
  └── CarePlan → CareGoal, CareIntervention
  └── AdvanceDirective
```

### Governance cluster
```
ConsentGrant ─── ConsentAudit
ConsentRequest (pending approval)
EmergencyAccessRequest ─── EmergencyAccessEvent
CorrectionRequest
DataExportRequest
AccountClosureRequest
PrivacyComplaint
MinorTransition
CountryPolicy
SecurityIncident, BreachEvent
AccessReview, AuditEvent, AuditExport
```

### Integration cluster
```
IntegrationClient (B2B credential)
  ├── ApiScopeGrant
  ├── Webhook ─── WebhookDelivery ─── WebhookEvent
  └── HealthIdResolution, HealthIdQrToken

Partner ─── PartnerFacility, PartnerProfessional, PartnerContact
  ├── PartnerAgreement, PartnerDocument, PartnerIntegration
  ├── PartnerGovernanceCase, PartnerAccessPermission
  ├── PartnerContributionPermission, PartnerContribution
  └── PartnerQualityScore, PartnerRiskScore, PartnerAuditLog

LiteDevice ─── OfflineSync ─── OfflineEvent

BridgeAgent ─── BridgeBatch
```

### Notifications/Comms cluster
```
NotificationTemplate ─── NotificationEvent ─── NotificationDelivery
NotificationPreference
EscalationChain ─── EscalationRule
VoiceNotificationJob
ActionTask
MessageThread ─── Message ─── MessageAttachment
MessageThreadParticipant
Broadcast
```

### Operational cluster
```
Facility ─── Staff (User + FacilityRoleAssignment)
  ├── Bed, Ward
  ├── AppointmentSlot
  ├── InventoryItem / Stock
  └── SupplyItem, Supplier, PurchaseOrder, GoodsReceipt, StockMovement
```

### Financial cluster
```
Invoice ─── Payment (cash, card, wallet, insurance)
  ├── InsuranceClaim ─── ClaimLine
  └── PatientPaymentPlan ─── Installment
CashierSession
```

### Analytics cluster
```
ApiUsageLog ─── ApiUsageMetric ─── ApiUsageSnapshot
KpiSnapshot ─── ReportSnapshot
AnalyticsAccessLog ─── AnalyticsSnapshot
```

---

## 10. Portal System & RBAC

### Portal routing

The `EnsurePortalAccess` middleware reads the authenticated user's role and checks the allowed-roles list per portal. 403 if mismatch.

| Portal URL prefix | Roles (60+ total) |
|------------------|-------------------|
| `/portals/patient` | `patient`, `guardian`, `caregiver`, `dependent_manager`, `emergency_contact` |
| `/portals/staff` | All clinical + operational roles (30+) |
| `/portals/insurance` | `insurance_*` roles |
| `/portals/admin` | `platform_admin`, `super_admin`, `privacy_officer`, `security_officer`, etc. |
| `/portals/developer` | `developer`, `developer_org_admin`, `api_partner`, `sandbox_developer`, etc. |
| `/portals/pharmacy` | `pharmacist`, `pharmacy_manager`, `pharmacy_technician`, etc. |
| `/portals/lab` | `labtech`, `lab_scientist`, `lab_manager`, `lab_validator`, `sample_collection` |
| `/portals/healthorg` | `ngo_admin`, `health_program_manager`, `outreach_team`, `mobile_clinic_team` |
| `/portals/lite` | `lite_facility`, `lite_staff`, `lite_device`, `lite_offline_sync` |

### Additional middleware per route group

- `guardian.context` — guardian viewing dependent records
- `facility.context` — require active facility selection
- `api.admin` — require admin role for CareMap admin actions
- `auth:sanctum` — Sanctum auth for mobile care plans, surveys, export

### Sidebar partials (role-specific navigation)

`resources/views/partials/sidebars/`:
`doctor`, `nurse`, `pharmacist`, `pharmacy_manager`, `lab_manager`, `labtech`, `medicine_stock`, `ngo_health_org`, `outreach_mobile`, `sample_collection`, `specialist_doctor`, `insurance_admin`

---

## 11. Integration Surface

### Inbound (what connects to OpesCare)

| System | Protocol | Auth | Purpose |
|--------|---------|------|---------|
| Partner HIS | REST | client_credentials | Read/write patient records |
| SDK consumers | REST | SDK token (scoped) | Embed health data |
| Bridge Agents | REST | `VerifyBridgeAgent` | Sync from legacy HIS |
| Africa's Talking | HTTP webhook | none (USSD) | USSD patient interface |
| OpesCare Lite devices | REST | `VerifyIntegrationClient` | Offline-capable field devices |
| Mobile app (patient) | REST | `auth.mobile` JWT | Patient B2C |
| Provider mobile app | REST | `VerifyIntegrationClient` | Clinical staff mobile |

### Outbound (what OpesCare connects to)

| System | Purpose | Config |
|--------|---------|--------|
| DHIS2 | Monthly public health push | `config/dhis2.php` |
| HL7 | Legacy HIS bridge messaging | `config/hl7.php` |
| WhatsApp Business API | Notification delivery | `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_ACCESS_TOKEN` |
| SMS provider | Notification delivery | `config/services.php` |
| Email (SMTP) | Notification delivery | `config/mail.php` |
| Push (FCM/APNs) | Mobile push notifications | `PushProvider` |
| Voice | Voice call notifications | `VoiceNotificationService` |
| AWS KMS | Encryption key management | `config/kms.php` |
| S3-compatible | File storage (prod) | `config/filesystems.php` |
| Redis / Horizon | Queue + monitoring | `config/horizon.php` |

---

## 12. Notification System — 5 Channels

The notifications module supports 5 delivery channels via dedicated provider classes in `app/Modules/Notifications/Providers/`:

| Channel | Provider class | Notes |
|---------|---------------|-------|
| Email | `EmailProvider.php` | Laravel Mail/SMTP |
| WhatsApp | `WhatsAppProvider.php` | WhatsApp Business API |
| SMS | `SmsProvider.php` | Configurable carrier |
| Push | `PushProvider.php` | FCM (Android) + APNs (iOS) |
| Voice | `VoiceProvider.php` + `VoiceNotificationService.php` | Voice call delivery |

Additional notification infrastructure:
- `NotificationTemplate` + `NotificationTemplateRenderer` — template engine with approval workflow
- `NotificationPreference` + `NotificationPreferenceService` — per-user channel preferences
- `NotificationDelivery` — delivery tracking + retry
- `EscalationChain` + `AlertEscalationService` — escalation rules
- `VoiceNotificationJob` — async voice call queuing

---

## 13. Mobile App — Patient

**Location:** `apps/mobile-patient`

### Architecture
Clean Architecture, feature-based modular structure. Each feature:
```
lib/features/{feature}/
  data/          — API calls, local storage, repositories
  models/        — Data models
  presentation/  — Screens, widgets
  providers/     — Riverpod state providers
```

### Screen map (15 screens across 11 features)

```
/ (root)
├── /login          — email+password or phone+PIN+OTP
├── /otp            — OTP verification step
└── /home (shell — bottom nav)
      ├── /home                     — Dashboard / timeline feed
      ├── /health-id                — Digital Health ID card + QR
      ├── /appointments
      │     ├── /appointments       — List (upcoming + past)
      │     └── /appointments/:id  — Detail
      ├── /labs
      │     ├── /labs               — List
      │     └── /labs/:id          — Detail (results)
      ├── /prescriptions
      │     ├── /prescriptions      — List
      │     └── /prescriptions/:id — Detail
      ├── /consent                  — Pending consent requests
      ├── /documents                — Official documents
      ├── /access-logs              — Who accessed my data
      ├── /timeline                 — Clinical event history
      └── /settings                 — User preferences + push tokens
```

### API calls by screen

| Screen | Endpoint |
|--------|----------|
| Login (email) | `POST /mobile/auth/login-email` |
| Login (phone) | `POST /mobile/auth/login` |
| OTP verify | `POST /mobile/auth/otp/verify` |
| Home | `GET /mobile/timeline` |
| Health ID | `GET /mobile/health-id-card` |
| Appointments | `GET /mobile/appointments` |
| Appointment detail | `GET /mobile/appointments/{id}` |
| **Book appointment** | `POST /mobile/appointments` |
| Cancel appointment | `POST /mobile/appointments/{id}/cancel` |
| Facilities/slots | `GET /mobile/facilities`, `GET /mobile/facilities/{id}/slots` |
| Labs | `GET /mobile/labs` |
| Lab detail | `GET /mobile/labs/{id}` |
| Prescriptions | `GET /mobile/prescriptions` |
| Prescription detail | `GET /mobile/prescriptions/{id}` |
| Consent | `GET /mobile/consent-requests` |
| Approve consent | `POST /mobile/consent-requests/{id}/approve` |
| Deny consent | `POST /mobile/consent-requests/{id}/deny` |
| Revoke consent | `POST /mobile/consents/{id}/revoke` |
| Access Logs | `GET /mobile/access-logs` |
| Documents | `GET /mobile/documents` |
| Allergies | `GET /mobile/allergies` |
| Clinical | `GET /mobile/clinical` |
| Immunizations | `GET /mobile/immunizations` |
| Care plans | `GET /mobile/care-plans`, `GET /mobile/care-plans/{id}` |
| Surveys | `GET /mobile/surveys`, submit |
| Medical record export | `POST /mobile/medical-records/export/pdf`, `/export/fhir` |
| Settings | `GET /mobile/settings`, `PATCH /mobile/settings` |
| Push token | `POST /mobile/push-tokens`, `DELETE /mobile/push-tokens/{id}` |
| Correction request | `POST /mobile/correction-requests` |
| Data export | `POST /mobile/data-export-requests`, list, download |
| Offline policy | `POST /mobile/offline/policies` |

---

## 14. Artisan Commands & Scheduled Jobs

**11 Artisan commands** in `app/Console/Commands/`:

| Command | Schedule | Purpose |
|---------|----------|---------|
| `opescare:demo:seed` | Manual | Seed demo data for all personas |
| `opescare:demo:reset` | Manual / API | Reset demo environment |
| `opescare:check-age-transitions` | Daily | Process guardian transitions when minors turn 18 |
| `opescare:encrypt-patient-pii` | Manual (migration) | Encrypt existing patient PII fields |
| `opescare:import-facility-registry` | Manual | Bulk-import facility registry |
| `opescare:import-insurance-registry` | Manual | Bulk-import insurance registry |
| `opescare:rotate-secrets` | Manual / periodic | Rotate integration client secrets |
| `opescare:push-dhis2-report` | 1st of month 04:00 | Push public health data to DHIS2 |
| `opescare:notify-expiring-credentials` | Monday 08:00 weekly | Alert about expiring provider credentials |
| `opescare:purge-expired-data` | Daily 03:00 | Data retention enforcement |
| `opescare:audit-facility-scope` | Manual | Audit data that crossed facility boundaries |

**Service Providers:**
- `AppServiceProvider`
- `HorizonServiceProvider` — Redis queue monitoring
- `ProductionSafetyServiceProvider` — guards against dangerous operations in production

---

## 15. Contracts, SDK & Widget

### OpenAPI Contracts
`contracts/openapi/`:
- `opescare-connect-v1.yaml` — Full OpenAPI spec for the Connect B2B API
- `opescare-mobile-v1.yaml` — Full OpenAPI spec for the Mobile Patient API

### PHP SDK
`sdk/php/src/Client.php` — PHP client library for consuming the OpesCare API

### Connect Widget
`widget/connect-widget.html` — Embeddable HTML widget for third-party systems to access OpesCare patient data (with consent flow) inside their own interface

### Bridge Agent
`bridge-agent/config.json` — Configuration for the OpesCare Bridge Agent that syncs data from legacy HIS systems

---

## 16. CI/CD & Security Scanning

`.github/workflows/`:
- `deploy.yml` — Automated deployment pipeline
- `snyk-security.yml` — Snyk dependency vulnerability scanning

`.snyk` — Snyk configuration at repo root

**Developer scripts (composer.json):**
- `composer setup` — Full install, key generate, migrate, npm build
- `composer dev` — Concurrent dev server, queue worker, log viewer, Vite (via `concurrently`)
- `composer test` — Clear config + run PHPUnit

**Testing:**
- `tests/Feature/` — Academy, Communications, Documents, MedicalId, Partners, SecurityOperations
- `tests/Unit/` — Unit tests
- PHPUnit ^12.5.12

---

## 17. Key Flows

### Flow 1: New Patient Registration

```
1. Patient visits /signup/patient
2. Submits name, phone, email, DOB
3. OTP sent → /verify/otp
4. Account created → Health ID auto-issued
5. Mobile app: login → Health ID card available
6. QR generated → can be scanned at any facility
```

### Flow 2: Complete Facility Visit

```
Receptionist               Patient                 Doctor
    │                          │                      │
    ├─ Scans QR / search ──────┤
    │  (Connect API or staff portal)
    │
    ├─ POST /queues/check-ins
    │
    │                    Triage Nurse
    │                    ├─ POST /triage (score)
    │                    ├─ CDSS checks (drug interactions, lab alerts)
    │                    └─ Priority assigned
    │
    │                                            Doctor called
    │                                            ├─ View patient summary
    │                                            ├─ CDSS visit alerts
    │                                            ├─ POST /records/encounters
    │                                            ├─ POST /records/lab-results
    │                                            ├─ POST /records/prescriptions
    │                                            └─ Complete queue ticket
    │
    ├─ POST /billing/invoices
    ├─ POST /billing/invoices/{id}/payments
    └─ Receipt generated (DomPDF)
```

### Flow 3: B2B Connect Data Access

```
Partner System
  1. POST /api/v1/connect/auth/token       → scoped access_token
  2. POST /connect/consents/request        → consent_request_id
  (Patient mobile approves via /mobile/consent-requests/{id}/approve)
  3. GET  /connect/patients/{id}/summary   → RequireConsentGrant passes
     → ConsentAudit record created automatically
  4. POST /connect/records/encounters      → IdempotencyProtection key required
  5. Webhook fires → partner notified of subsequent updates
```

### Flow 4: Insurance Claim Lifecycle

```
1. Facility creates invoice for visit
2. POST /api/v1/insurance/preauth         → preauth requested
3. Insurer decides (approve/partial/deny) via insurance portal
4. POST /api/v1/insurance/claims          → claim submitted
5. Insurer adjudicates via insurance portal
6. POST /insurance/claims/{id}/payment    → payment posted
7. Invoice marked paid / partially paid
8. Revenue cycle report updated
```

### Flow 5: Patient Consent Flow

```
B2B Partner
  1. POST /connect/consents/request → {consent_request_id, scope: "patients:read"}

Patient Mobile App
  2. GET  /mobile/consent-requests   → sees pending request with facility + scope
  3. POST /consent-requests/{id}/approve
     → ConsentGrant created with expiry
     → ConsentAudit logged

B2B Partner
  4. GET /connect/patients/{id}/summary → RequireConsentGrant passes
  5. ConsentAudit entry written on every read
  6. Patient can see this in /mobile/access-logs
```

### Flow 6: Public Health Report Submission

```
Data Steward (Facility)
  Phase 1: POST /public-health/reports/generate-drafts
           → DraftGenerationService builds auto-draft from clinical data
           GET  /reports/{id}/quality-checks
           → DataQualityCheckService runs completeness + consistency checks

  Phase 2: POST /reports/{id}/submit-for-review → review queue
           Reviewer: approve / request-correction / reject

  Phase 3: POST /reports/{id}/submit → goes to submission profiles
           → ExportService generates PDF/Excel
           → POST /reports/{id}/export

  Phase 4: PushDhis2ReportCommand fires (1st of month 04:00)
           → sends to DHIS2

Intelligence Layer (automated)
  → SignalDetectionService checks for outbreak signals
  → OutbreakAlert created if threshold exceeded
  → POST /signals/trigger-detection can also be called manually
```

### Flow 7: Emergency Access

```
Emergency Clinician
  1. POST /connect/emergency-access/request → logged immediately
  2. GET  /connect/patients/{id}/emergency-profile → critical data (auto-granted)
  
Privacy Officer (Admin)
  3. Sees entry in /portals/admin/security/emergency-access
  4. Flags if inappropriate → SecurityIncident created
  5. POST /security/suspicious-flags/{id}/review

Patient Mobile
  6. Sees emergency access in /mobile/access-logs
  7. Can file correction request if unauthorized
```

### Flow 8: USSD Patient Access

```
Patient phones *XXX#  (Africa's Talking)
  → POST /api/ussd/callback (no auth)
  → UssdController processes session state
  → Menu: [1] My Health ID  [2] Nearest facility  [3] Emergency
  → Response sent back to Africa's Talking → displayed on phone
```

---

## 18. What's Built vs What's Missing

### Confirmed as fully built

| Module | Backend API | Web Portal | Mobile |
|--------|------------|-----------|--------|
| Patient Identity / Health ID | ✅ | ✅ | ✅ |
| Auth (all types) | ✅ | ✅ | ✅ |
| Appointments (incl. booking) | ✅ | ✅ | ✅ booking + cancel |
| Queue management | ✅ | ✅ | ✅ (provider mobile) |
| Clinical Encounters | ✅ | ✅ | read-only |
| CDSS | — | ✅ | — |
| Laboratory | ✅ | ✅ (lab portal) | ✅ |
| Pharmacy & Prescriptions | ✅ | ✅ (pharmacy portal) | ✅ |
| Billing & Finance | ✅ | ✅ | — |
| Insurance | ✅ | ✅ (insurance portal) | — |
| Referrals | ✅ | ✅ | — |
| Consent & Governance | ✅ | ✅ | ✅ |
| Verifiable Documents | ✅ | ✅ | ✅ |
| Ward & Admissions | ✅ | ✅ | — |
| Maternity & Antenatal | ✅ | — | — |
| Telemedicine (stub) | ✅ | ✅ | — |
| Public Health Surveillance | ✅ | — | — |
| Immunizations | ✅ | ✅ | ✅ |
| CareMap | ✅ | ✅ | — |
| Connect Platform (B2B) | ✅ | ✅ (admin) | — |
| FHIR R4 | ✅ | — | — |
| OpesCare Lite | ✅ | ✅ (lite portal) | — |
| Bridge Agent | ✅ | ✅ (admin) | — |
| SDK | ✅ | — | — |
| Developer Portal | — | ✅ | — |
| Admin Control Centre | — | ✅ | — |
| Communications / Notifications | ✅ | ✅ | push tokens |
| Security Operations | ✅ | ✅ | — |
| HR & Scheduling | ✅ | ✅ | — |
| Analytics & Reports | ✅ | ✅ | — |
| Care Plans | ✅ | — | ✅ read-only |
| Advance Directives | ✅ | — | — |
| Academy | ✅ | ✅ | — |
| Partner Management | ✅ | — | — |
| Subscriptions | ✅ | ✅ (admin) | — |
| Data Import | — | ✅ | — |
| Supply Chain | ✅ | ✅ | — |
| Radiology | ✅ | **❌ no portal UI** | — |
| File Storage | — | ✅ | — |
| Patient Surveys | ✅ | — | ✅ |
| Medical Record Export | ✅ | — | ✅ |
| Demo Mode | ✅ | ✅ | — |

### Confirmed gaps

| Gap | Impact | Effort |
|-----|--------|--------|
| Telemedicine video provider not wired (Jitsi/Daily/Twilio) | Telemedicine is a UI stub | Medium |
| Provider mobile app not scaffolded | No mobile tool for clinical staff | Large |
| Radiology portal UI (no blade views) | Radiologists have no web interface | Small |
| Advance directives patient view (mobile) | Patients can't see their DNR | Small |
| Care plan goal actions (patient) | Care plans are read-only on mobile | Small |
| Patient portal web — incomplete pages | Non-smartphone patients excluded | Medium |
| FHIR write (POST/PUT resources) | One-way interoperability only | Large |
| Subscription auto-billing (Paystack/Stripe) | Manual admin action required | Medium |
| USSD full session tree | Webhook exists, no menu state | Medium |
| Mobile offline mode (SQLite + local sync) | No data when no internet | Large |
| Biometric auth on mobile | Security UX gap | Small |
| WhatsApp / Push end-to-end test | Unclear if delivery pipeline working | Small |
| French translations complete | Only English visibly in use | Medium |
| Data retention scheduled jobs (verify running) | Legal compliance risk | Small |
| KMS key rotation verified | Security risk if not configured | Small |

---

## 19. Next Priorities

### P0 — Before any go-live (production blockers)

1. **Verify data retention job** — `PurgeExpiredDataCommand` must be scheduled and running. Check `routes/console.php`.
2. **KMS configuration** — confirm `config/kms.php` points to real key management, not a dev fallback.
3. **Auth hardening** — audit token expiry, refresh flows, and revocation for all 4 auth types (web, mobile patient, provider mobile, B2B).
4. **Push notification pipeline** — confirm FCM/APNs tokens flow from `POST /mobile/push-tokens` through to `PushProvider` delivery. Test end-to-end.
5. **WhatsApp delivery** — confirm `WHATSAPP_PHONE_NUMBER_ID` + `WHATSAPP_ACCESS_TOKEN` env vars are set and `WhatsAppProvider` sends real messages.
6. **CORS** — verify `config/cors.php` allowed origins match production domain list, not wildcards.
7. **`ProductionSafetyServiceProvider`** — review what guards it enforces, ensure they're all active.

### P1 — High value, clear path

8. **Provider mobile app** — The entire `Api/ProviderMobile/` API is built. Scaffold a new Flutter app using `apps/mobile-patient` as the template. Screens needed: login, facility select, queue (call + complete), patient scan, patient clinical profile.
9. **Telemedicine video integration** — Pick Jitsi Meet (free, self-host) or Daily.co. The session model (`TelemedicineController`) is complete; wire in the room URL.
10. **Radiology portal UI** — Create blade views under `resources/views/portals/staff/radiology/`. The API (`RadiologyReportController`) is 100% done.
11. **Web patient portal** — Build out pages for patients who don't have smartphones (appointment booking, consent approval, document download, access logs).
12. **Subscription auto-billing** — Integrate Paystack or Stripe. The `SubscriptionController` is built; wire in the payment provider.

### P2 — Important, more complex

13. **Mobile offline mode** — Add local SQLite with `sqflite` Flutter package. Use existing `/mobile/offline/policies` + `/offline/policies/{id}/queue` API.
14. **FHIR write operations** — Add POST/PUT on Patient, Encounter, Observation. Needed for government interoperability.
15. **USSD session tree** — Build full menu state machine in `UssdController`: patient lookup by phone, Health ID display, nearest facility, appointment status.
16. **French translations** — Wrap all blade views in `__('...')`, create `lang/fr/` files.

### P3 — Quality & completeness

17. **Advance directives patient view** — Add screen in mobile app, call `GET /mobile/` directives endpoint.
18. **Care plan goal check-off** — Allow patients to mark goals complete from mobile.
19. **Biometric auth** — Add `local_auth` Flutter package to `apps/mobile-patient`.
20. **Survey prompt UX** — Auto-show survey after appointment completion in mobile.
21. **Multi-facility provider switching** — The API supports `POST /provider-mobile/facilities/{id}/switch`. Wire it in the future provider app's facility selector.
22. **Academy mobile access** — Add course catalogue + quiz screens to patient (or provider) app.
23. **Blood bank portal UI** — The staff portal blood inventory is under inventory; consider a dedicated blood bank view.

---

## 20. Key Files Reference

| Path | Purpose |
|------|---------|
| `apps/api-laravel/routes/api.php` | All REST API routes (909 lines) |
| `apps/api-laravel/routes/web.php` | All web portal routes (641 lines) |
| `apps/api-laravel/routes/communications.php` | Notifications, tasks, messaging |
| `apps/api-laravel/routes/academy.php` | Training routes |
| `apps/api-laravel/routes/partners.php` | Partner governance routes |
| `apps/api-laravel/app/Http/Middleware/EnsurePortalAccess.php` | RBAC enforcement |
| `apps/api-laravel/app/Http/Middleware/RequireConsentGrant.php` | Consent scope enforcement |
| `apps/api-laravel/app/Http/Middleware/IdempotencyProtection.php` | Idempotency for writes |
| `apps/api-laravel/config/opescare.php` | System-wide OpesCare config |
| `apps/api-laravel/config/screen_registry.php` | Portal screen/route mapping |
| `apps/api-laravel/config/dhis2.php` | DHIS2 integration config |
| `apps/api-laravel/config/hl7.php` | HL7 messaging config |
| `apps/api-laravel/config/kms.php` | Key management config |
| `apps/api-laravel/config/demo.php` | Demo mode config |
| `apps/api-laravel/config/data_retention.php` | Data retention policies |
| `apps/api-laravel/app/Modules/` | Service layer (40+ modules) |
| `apps/api-laravel/app/Console/Commands/` | 11 Artisan commands |
| `apps/mobile-patient/lib/` | Flutter patient app |
| `apps/mobile-patient/lib/core/router/` | Navigation config |
| `apps/mobile-patient/pubspec.yaml` | Flutter dependencies |
| `contracts/openapi/opescare-connect-v1.yaml` | Connect API OpenAPI spec |
| `contracts/openapi/opescare-mobile-v1.yaml` | Mobile API OpenAPI spec |
| `sdk/php/src/Client.php` | PHP SDK client |
| `widget/connect-widget.html` | Embeddable B2B widget |
| `bridge-agent/config.json` | Bridge agent config |
| `DEPLOYMENT.md` | Production deployment guide |
| `ONBOARDING.md` | Developer onboarding |
| `PROJECT_KNOWLEDGE.md` | Project knowledge base |
| `.github/workflows/deploy.yml` | CI/CD pipeline |
| `.github/workflows/snyk-security.yml` | Security scanning |

---

*For individual feature specs, use `/spec` to file a GitHub issue per item above. This document covers everything currently in the codebase — nothing omitted.*
