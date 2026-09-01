# OpesCare — Documentation Index

The product knowledge base. **Start here.**

> Curated index. Links point to each doc in its **current location** (nothing was moved). If a doc disagrees with the code, trust the code and fix the doc.

## Entry points
| Read | For |
|---|---|
| [../CLAUDE.md](../CLAUDE.md) | Monorepo brief — conventions, deploy pipeline, git topology |
| [CLAUDE.md](CLAUDE.md) | Documentation orientation — invariants, how to pick up work, blockers |
| [AS_BUILT_IMPLEMENTATION_REGISTER.md](AS_BUILT_IMPLEMENTATION_REGISTER.md) | Current state of all ~45 modules |
| [audits/SPEC_VS_CODE_GAP_AUDIT.md](audits/SPEC_VS_CODE_GAP_AUDIT.md) | The work list (GAP-0xx) |
| [security/threat-model.md](security/threat-model.md) | STRIDE threat model |
| [../apps/api-laravel/CLAUDE.md](../apps/api-laravel/CLAUDE.md) | Security guardrails / sealed modules (authoritative) |

---

## Living knowledge by area

### Overview
- [OpesCare Consolidated Technical & Operational Blueprint](specs-core/opescare%20master%20prompt.md) — Executive definition, vision, positioning, and full product/architecture/operational/interoperability/governance blueprint — the canonical high-lev…

### Architecture
- [OpesCare Module Architecture Guide](../apps/api-laravel/docs/ARCHITECTURE-MODULES.md) — Maps the 45 app/Modules into 7 domains (5 patient-journey + 2 cross-cutting), with mermaid + SVG diagram. Core system-design reference.
- [OpesCare Validated Project Knowledge](specs-core/PROJECT_KNOWLEDGE.md) — Validated architecture baseline: Laravel modular monolith, PostgreSQL/Redis, FHIR R4 boundary, OAuth2/PKCE/OpenAPI 3.1, non-negotiable invariants, …
- [RBAC & Multi-Facility Architecture](../apps/api-laravel/docs/redesign/OPESCARE_RBAC_ARCHITECTURE.md) — Authoritative design of who-can-access-what: roles, per-facility scoping, portal gating; engineering + Ministry presentation reference.
- [STRIDE Threat Model (Consolidated)](security/threat-model.md) — v2.0 consolidated STRIDE threat model (merges two prior threat-indexed and component-indexed models) with trust boundaries, assets, controls, resid…

### Domain Modules
- [Cameroon Healthcare Facility Registry Plan](plans/2026-05-26-cameroon-facility-registry.md) — Pre-seeds a national Cameroon facility directory (hospitals/pharmacies/labs/insurers) with claim flow and CSV re-import commands; durable domain/mo…
- [Communication, Alerts, Tasks, Notifications & Internal Messaging PRD](modules/OPESCARE_COMMUNICATION_ALERTS_TASKS_MESSAGING_SYSTEM.md) — Full PRD for the notifications/alerts/tasks/email/WhatsApp/SMS/push/voice/internal-messaging domain, with architecture, UI/UX, and template blueprint.
- [Demo Access Page, Accounts, Data & Environment PRD](modules/OPESCARE_DEMO_ACCESS_COMPLETE_FIXED.md) — PRD for the demo-access system (public/internal demo routes, demo accounts, safe fake data) covering all role dashboards; single genuine PRD, no tw…
- [Design: Unified Subscription & Billing](../apps/api-laravel/docs/superpowers/specs/2026-06-17-subscription-billing-design.md) — Feature design generalizing the org subscription engine to patients/families/facilities (XAF, MoMo + Orange Money); placeholder pricing. Durable mo…
- [Digital Health Competency & Certification Module (OpesCare Academy) PRD](modules/OPESCARE_DIGITAL_HEALTH_COMPETENCY_CERTIFICATION.md) — PRD for the role-based training, onboarding, EMR/competency courses, QR-verifiable certificates, and governance/curriculum blueprint module.
- [Medical ID / Health ID System — Final Technical PRD](modules/OPESCARE_MEDICAL_ID_SYSTEM_FINAL.md) — Final PRD for the patient identity foundation: country-based Health ID format, physical/digital cards, QR lookup, rotating/temporary consent QR, an…
- [Partner Contribution & Governance Module PRD](modules/OPESCARE_PARTNER_CONTRIBUTION_GOVERNANCE.md) — PRD for the institutional trust engine: partner onboarding/verification, agreements, data contribution/access scopes, integrations, and approval wo…
- [Public Health Reporting Module — Phase 1-4 PRD](modules/OPESCARE_PUBLIC_HEALTH_REPORTING_PHASES.md) — Phased PRD for preparing, reviewing, approving, submitting, correcting, and monitoring public-health reports plus government system integration and…
- [Verifiable Clinical/Financial/Admin Document Template System — V2 PRD](modules/OPESCARE_VERIFIABLE_DOCUMENT_TEMPLATES_V2.md) — V2 PRD for generating, securing, issuing, verifying, and auditing FHIR-aligned tamper-evident documents (lab/imaging requests & reports, etc.) with…
- [Verified Care Access Map / Health Services Directory PRD](modules/OPESCARE_VERIFIED_CARE_ACCESS_MAP.md) — PRD for a verified healthcare directory & geospatial access map (PostGIS) linked to Health ID, partner governance, pharmacy stock, blood, lab, and …

### API & Integration
- [API Resource Layer (DTO / Wire-Contract Decoupling)](../apps/api-laravel/docs/API-RESOURCES.md) — Mandates explicit resource/DTO classes (never raw Eloquent) as the public wire contract; foundation shipped, ratcheted migration in progress.
- [API Versioning & Backward-Compatibility Policy](../apps/api-laravel/docs/API-VERSIONING.md) — URL-path major-version contract to integration partners (HIS, lab, pharmacy, insurer, SDK); defines how the API evolves and what breaks vs. doesn't.
- [Developer Documentation Portal — Design Spec](design-specs/2026-05-20-developer-docs-portal-design.md) — Design spec for the public /docs developer portal covering Connect API, SDK, Bridge Agent, Widget, and Webhooks with Redoc/OpenAPI 3.1 and multi-la…
- [OAuth 2.0 Conformance — Discovery, JWKS & Introspection](../apps/api-laravel/docs/API-OAUTH.md) — Connect OAuth2 client_credentials surface: RS256 JWT, discovery, JWKS, token introspection endpoints for third-party verification.
- [OpenAPI Specification — Generated, Full-Coverage, Self-Syncing](../apps/api-laravel/docs/API-OPENAPI.md) — How the generated OpenAPI 3.1 spec (public/openapi.json) is produced via artisan command and rendered as ReDoc at /docs/playground; full endpoint c…
- [OpesCare Connect: API Overview & Core Design](integration/CONNECT_API_OVERVIEW.md) — Core Connect B2B API design: route-group isolation (mobile vs connect vs webhooks), environment base URLs, and OAuth2 client-credentials authentica…
- [OpesCare Connect: Clinical Interoperability Workflows](integration/CLINICAL_WORKFLOWS.md) — Standard push/pull/search/inventory-sync operations for external medical actors, including privacy-safe patient search by Health ID with scopes and…
- [OpesCare Connect: SDK, Widget & Bridge Agent](integration/SDK_WIDGET_BRIDGE.md) — Developer toolkit overview: PHP/TS/Python SDKs (token refresh, idempotency, webhook verification), embeddable widget, and Bridge Agent connectors.
- [OpesCare Connect: Webhooks & Reconciliation Cases](integration/WEBHOOKS_RECONCILIATION.md) — B2B event webhook catalog (patient/consent/emergency-access/lab events), HMAC signature headers and computation, and clinical matching reconciliati…

### Operations & Deployment
- [Backup & Restore Runbook](runbooks/BACKUP_AND_RESTORE_RUNBOOK.md) — Detailed runbook with pg_dump/RDS backup scripts, S3 versioning, restore and PITR procedures, monthly verification, and backup alerts (companion to…
- [Deploy-Readiness Verification Runbook](audits/VERIFICATION_RUNBOOK.md) — Step-by-step Laragon commands to run deploy-readiness checks (test suite, composer/npm CVE audits) that could not run in the assistant sandbox; an …
- [Deployment Runbook](runbooks/DEPLOYMENT_RUNBOOK.md) — Production deployment runbook: pre-deploy checklist, env vars, server requirements, zero-downtime deploy steps, Nginx/supervisor config, verificati…
- [Deployment Supervision & Observability (systemd)](../deploy/README.md) — Operational guide wiring Horizon + scheduler via systemd unit files plus error-tracking backend. Tightly coupled to deploy/systemd/ assets it docum…
- [Disaster Recovery Plan](runbooks/disaster-recovery-plan.md) — DR plan with RTO/RPO/MTD objectives, backup strategy, failure-scenario playbooks (DB/app/region/ransomware), test schedule, escalation, and validat…
- [Disaster Recovery Plan](../apps/api-laravel/docs/disaster-recovery-plan.md) — DR plan v1.0 with RTO/RPO recovery objectives, quarterly review cadence; CTO/Platform owned.
- [Monitoring & Observability Runbook](runbooks/MONITORING_AND_ALERTS.md) — Monitoring stack, health endpoints, SLA targets, P1-P3 alert definitions, logging retention, performance monitoring, on-call rotation, and status p…
- [Production Go-Live Runbook](../apps/api-laravel/docs/RUNBOOK-PRODUCTION.md) — Single-source go-live runbook: pre-deploy sign-off gate (full test suite, config:assert-production), top-to-bottom deploy steps.
- [Production Launch, Governance, Compliance & Deployment Master Plan](specs-core/OPESCARE_PRODUCTION_LAUNCH_GOVERNANCE_COMPLIANCE_AND_DEPLOYMENT_MASTER_PLAN.md) — Master plan for the production-launch layer: legal docs, compliance framework, clinical governance, Ministry adoption pathway, facility onboarding,…
- [SOP-001 Patient Registration](sops/SOP-001_PATIENT_REGISTRATION.md) — Standard operating procedure for registering new patients, capturing demographics, obtaining consent, and generating a Health ID.
- [SOP-002 Health ID Verification](sops/SOP-002_HEALTH_ID_VERIFICATION.md) — SOP for verifying patient identity via Health ID/QR scan before clinical, billing, or document actions.
- [SOP-003 Patient Consent](sops/SOP-003_CONSENT.md) — SOP for capturing, recording, and revoking informed consent (registration, procedure, data-sharing, research).
- [SOP-004 Emergency Access](sops/SOP-004_EMERGENCY_ACCESS.md) — Break-glass SOP for time-limited, audited record access in genuine medical emergencies without prior consent.
- [SOP-005 Appointments & Booking](sops/SOP-005_APPOINTMENTS.md) — SOP for booking, confirming, reminding, and tracking attendance of appointments including cancellations and no-shows.
- [SOP-006 Queue & Patient Flow](sops/SOP-006_QUEUE.md) — SOP for patient check-in, priority assignment (P1-P5), calling, and queue escalation.
- [SOP-007 Triage](sops/SOP-007_TRIAGE.md) — Clinical triage SOP with P1-P5 colour categories, 2-minute vital-sign/AVPU assessment, and P1 escalation.
- [SOP-008 Clinical Consultation](sops/SOP-008_CONSULTATION.md) — SOP for clinical consultations using SOAP notes, CDSS alert review, order issuance, and visit closure.
- [SOP-009 Laboratory Requests & Results](sops/SOP-009_LAB.md) — SOP for lab request, specimen handling, result entry, and critical-value notification workflow.
- [SOP-010 Prescriptions](sops/SOP-010_PRESCRIPTION.md) — SOP for issuing prescriptions with CDSS checks, alert handling, e-signature, and controlled-substance dual authorisation.
- [SOP-011 Pharmacy Dispensing](sops/SOP-011_PHARMACY.md) — SOP for receiving prescriptions, stock check, dispensing with batch/expiry, patient counselling, and inventory update.
- [SOP-012 Billing & Payments](sops/SOP-012_BILLING.md) — SOP for invoice generation, payment collection across modes, receipts, discounts/waivers, and cash handling.
- [SOP-013 Insurance Claims & Preauthorisation](sops/SOP-013_INSURANCE.md) — SOP for insurance verification, preauthorisation, claims submission, and handling rejected claims (NHIS/HMO).
- [SOP-014 Document Verification (QR Code)](sops/SOP-014_DOCUMENT_VERIFICATION.md) — SOP for verifying authenticity of OpesCare-issued documents via QR code and handling fraud/invalid statuses.
- [SOP-015 Record Correction](sops/SOP-015_RECORD_CORRECTION.md) — SOP for amendment-based record correction with audit-trail preservation and NDPR 30-day response requirement.
- [SOP-016 Data Import & Migration](sops/SOP-016_DATA_IMPORT.md) — SOP for validated, audited bulk data import/migration with staging tests and facility sign-off.
- [SOP-017 Support & Helpdesk](sops/SOP-017_SUPPORT.md) — SOP defining L1-L3 support tiers, SLAs, ticket management, and escalation thresholds.
- [SOP-018 Incident Reporting](sops/SOP-018_INCIDENT_REPORTING.md) — SOP for categorising and reporting security/clinical/operational/compliance incidents with NITDA 72-hour breach notification.
- [SOP-019 Planned & Unplanned Downtime](sops/SOP-019_DOWNTIME.md) — SOP for announcing and executing planned maintenance and managing unplanned outages with status-page timeline.
- [SOP-020 Backup & Restore Operations](sops/SOP-020_BACKUP_RESTORE.md) — Operator-facing SOP for backup schedule, daily verification checklist, and monthly restore test; references the backup runbook (twin of BACKUP_AND_…
- [Secrets Rotation Runbook](runbooks/secrets-rotation-runbook.md) — Runbook for rotating APP_KEY, DB password, MTN/Orange Money API keys, and KMS data keys with thresholds and post-rotation checklist.
- [WAF Configuration Guide](security/waf-configuration.md) — v1.0 operational guide for configuring Cloudflare (primary) and AWS WAF in front of the API/patient portal, including OWASP CRS paranoia-level-2 se…
- [WAF Configuration Guide](../apps/api-laravel/docs/waf-configuration.md) — Cloudflare-primary WAF config guide for public API + patient portal; security-ops operational doc, quarterly review.

### Security & Compliance
- [Data Governance, Privacy, Consent & Patient Rights PRD](modules/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md) — Defines data governance principles, data categories, legal/compliance posture, consent model, emergency access, patient rights, and data sharing ru…
- [Incident Response Playbooks](security/INCIDENT_RESPONSE_PLAYBOOKS.md) — Active v1.0 step-by-step response procedures per incident class with severity levels (P0–P3), roles, timelines, and comms templates; canonical secu…
- [OpesCare Patient Safety & Clinical Risk Register](reference/CLINICAL_RISK_REGISTER.md) — Active patient-safety/data-integrity risk register: per-risk root cause, harm, controls, residual risk, and ENFORCED/PROCESS mitigations; quarterly…
- [STRIDE Threat Model](../apps/api-laravel/docs/threat-model.md) — STRIDE threat-modelling of the OpesCare platform, v1.0, annual review. (Authoritative copy; a stale duplicate under docs/security/ exists only in t…
- [Security Hardening Checklist](security/SECURITY_HARDENING_CHECKLIST.md) — v1.0 Laravel/app/infra hardening checklist (APP_DEBUG, CSRF, XSS, mass-assignment, rate-limiting, session, CSP/HSTS, env exposure); live security c…
- [Spec-vs-Code Gap Audit](audits/SPEC_VS_CODE_GAP_AUDIT.md) — Current (2026-06-11) actionable gap register: every spec capability the code does not yet fully deliver, with stable GAP IDs, exact file paths, and…

### Product, Plans & Specs
- [Cameroon Healthcare Facility Registry — Design Spec](design-specs/2026-05-26-cameroon-facility-registry-design.md) — Feature design spec for a pre-seeded national facility/insurer registry (MINSANTE/ONPC/WHO data), claim flow, and re-importable CSV seeding for fac…
- [Family Accounts — Design Spec](design-specs/2026-05-25-family-accounts-design.md) — Feature design spec for guardian/dependent linking, guardian-context middleware, consent-on-behalf, and multi-channel notifications (purely additiv…
- [OpesCare Design System & Redesign Spec](../apps/api-laravel/docs/redesign/OPESCARE_DESIGN_SYSTEM.md) — Authoritative UI design system: brand tokens (navy/blue palette), --p-* token rules, retiring hardcoded hex/inline styles. Product/design source of…
- [Patient App (Expo) — Design Spec](superpowers/specs/2026-08-31-mobile-expo-app-design.md) — The decision to move the patient app to Expo / React Native, the API surface it consumes, and the screen inventory it has to cover.
- [Patient App (Expo) — App Brief](../apps/mobile-expo/CLAUDE.md) — Conventions for `apps/mobile-expo`: expo-router layout, NativeWind, `theme/tokens.js`, EAS build profiles. Stays beside the code.
- [Pilot Plan](qa-release/PILOT_PLAN.md) — v1.0 facility-pilot plan: objectives, in/out-of-scope workflows, success criteria, and champion training; a product rollout/plan artifact rather th…

### Development
- [Laragon Local Development Setup](../LARAGON_SETUP.md) — Windows/Laragon quick-start: PHP 8.3, PostgreSQL 15, Node 20, Composer, repo layout — local dev environment guide.
- [OpesCare API — AI Model Instruction Override / Sealed Modules](../apps/api-laravel/CLAUDE.md) — Authoritative AI-assistant guardrails: sealed Health-ID module, sealed-file list, 10 absolute prohibitions, verification checklist. Must stay besid…
- [OpesCare Platform Changelog](../CHANGELOG.md) — Keep-a-Changelog / SemVer release log; current line is 0.9.0-rc1 plus an in-progress production-hardening entry. Belongs at repo root by convention.
- [Patient App Store Submission Checklist](qa-release/store-submission-checklist.md) — Pre-flight + per-platform (Play Store / App Store) submission checklist for the Expo patient app: versioning, privacy policy, EAS builds, icons/spla…
- [QA Checklist](qa-release/QA_CHECKLIST.md) — v1.0 manual QA checklist organized by core module (identity, appointments, queue, pharmacy, lab, billing, ...); standing pre-release QA test refere…
- [Release Checklist](qa-release/RELEASE_CHECKLIST.md) — v1.0 phased release checklist (T-48h / T-2h / cutover) covering merges, tests, migrations, staging smoke, security review, CVE scans, and backups; …

### Reference
- [OpesCare Data Dictionary & Master Data Catalog](reference/DATA_DICTIONARY.md) — Canonical source of truth for DB field names, types, controlled values, and naming conventions (snake_case, UUID PKs, FK and timestamp patterns); m…
- [OpesCare Role Permission Matrix](reference/ROLE_PERMISSION_MATRIX.md) — Authoritative RBAC matrix of core roles (B2C, clinical staff, etc.) and their explicitly granted permissions; every role assignment and RBAC test m…

---

## Archive — completed / superseded (68)

Historical: completed one-off implementation plans (the May 2026 wave/phase series) and duplicates. Kept for provenance — **not** current product knowledge. A future cleanup can relocate these to `docs/_archive/`.

<details><summary><strong>../</strong> — 2 docs</summary>

- [Platform Flow Audit — Deployment Readiness (2026-06-12)](../PLATFORM_FLOW_AUDIT.md)
- [Production Readiness Report — Fresh Code Scan v2 (2026-06-12)](../PRODUCTION_READINESS_REPORT.md)

</details>

<details><summary><strong>../apps/api-laravel/docs/handoff/</strong> — 2 docs</summary>

- [Clinical-Safety Re-Audit (2026-06-17)](../apps/api-laravel/docs/handoff/2026-06-17-clinical-safety-reaudit.md)
- [Handoff: Payment Security Patches for Fenced Files](../apps/api-laravel/docs/handoff/2026-06-17-payment-security-patches.md)

</details>

<details><summary><strong>../apps/api-laravel/docs/superpowers/plans/</strong> — 2 docs</summary>

- [Complete Bilingualization (EN/FR) Implementation Plan](../apps/api-laravel/docs/superpowers/plans/2026-06-16-bilingualization.md)
- [100% Production Readiness Implementation Plan](../apps/api-laravel/docs/superpowers/plans/2026-06-18-production-readiness.md)

</details>

<details><summary><strong>audits/</strong> — 6 docs</summary>

- [Codex Local Baseline Safety Review](audits/CODEX_LOCAL_BASELINE_REVIEW.md)
- [Dashboard & Access Control Baseline Audit](audits/DASHBOARD_ACCESS_BASELINE_AUDIT.md)
- [Local Repository Implementation Baseline Audit](audits/LOCAL_REPOSITORY_IMPLEMENTATION_BASELINE_AUDIT.md)
- [Extended Modules Implementation Audit Checklist](audits/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_CHECKLIST.md)
- [Extended Modules Implementation Audit Result](audits/OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md)
- [Full Implementation Audit Checklist](audits/OPESCARE_FULL_IMPLEMENTATION_AUDIT_CHECKLIST.md)

</details>

<details><summary><strong>integration/</strong> — 2 docs</summary>

- [OpesCare Connect Platform API/SDK/Bridge/Widget/Webhooks Blueprint](integration/OPESCARE_CONNECT_PLATFORM_API_SDK_BRIDGE_WIDGET_WEBHOOKS.md)
- [OpesCare Interoperability Connect Suite (Industry-Leader) Specification](integration/OPESCARE_CONNECT_SUITE_INDUSTRY_LEADER_API_SDK_WIDGET_BRIDGE_LITE_WEBHOOKS.md)

</details>

<details><summary><strong>plans/</strong> — 39 docs</summary>

- [Developer Documentation Portal Implementation Plan](plans/2026-05-20-developer-docs-portal.md)
- [Wave 1 - Security & Rate Limiting Plan](plans/2026-05-21-wave1-security-rate-limiting.md)
- [Wave 2 SP-3: Patient Self-Booking Plan](plans/2026-05-23-wave2-sp3-patient-booking.md)
- [Wave 2 SP-4: SMS + Email Notifications Plan](plans/2026-05-23-wave2-sp4-notifications.md)
- [Patient Portal Production Readiness Plan](plans/2026-05-24-patient-portal-production.md)
- [Family Accounts - Pre-Deployment Bug Fixes](plans/2026-05-25-family-accounts-predeployment-fixes.md)
- [Family Accounts Implementation Plan](plans/2026-05-25-family-accounts.md)
- [Wave 1 - Low-Risk Hardening Plan](plans/2026-05-25-wave1-low-risk-hardening.md)
- [Wave 2 - Auth & Middleware Hardening Plan](plans/2026-05-25-wave2-auth-middleware-hardening.md)
- [Wave 3 - Data & Session Security Hardening](plans/2026-05-25-wave3-data-session-security.md)
- [Wave 4 - Critical Security Fixes (Blockers)](plans/2026-05-25-wave4-critical-security-fixes.md)
- [Wave 5 - PII Column-Level Encryption](plans/2026-05-25-wave5-pii-encryption.md)
- [Wave 6 - Per-Facility RBAC Scoping](plans/2026-05-25-wave6-per-facility-rbac.md)
- [Wave 7 - FHIR Consent, Audit & Module Hardening](plans/2026-05-25-wave7-fhir-consent-audit-hardening.md)
- [Wave 8 - Production Config Lock & Infra Hardening](plans/2026-05-25-wave8-production-config-lock.md)
- [Wave 9 - Final Verification & Production Sign-Off](plans/2026-05-25-wave9-final-verification.md)
- [Production Readiness Roadmap (May 26)](plans/2026-05-26-production-readiness-roadmap.md)
- [Wave PR-1: Clinical EHR Completion](plans/2026-05-26-wave-pr1-clinical-ehr.md)
- [Wave PR-10: Infrastructure & Reliability](plans/2026-05-26-wave-pr10-infrastructure.md)
- [Wave PR-11: Multi-tenancy & Admin](plans/2026-05-26-wave-pr11-multitenancy.md)
- [Wave PR-12: Security Hardening Plan](plans/2026-05-26-wave-pr12-security.md)
- [Wave PR-2: Interoperability Completions](plans/2026-05-26-wave-pr2-interoperability.md)
- [Wave PR-3: Appointments & Scheduling](plans/2026-05-26-wave-pr3-appointments.md)
- [Wave PR-4: Billing & Insurance Completion](plans/2026-05-26-wave-pr4-billing-insurance.md)
- [Wave PR-5: Laboratory & Imaging](plans/2026-05-26-wave-pr5-lab-imaging.md)
- [Wave PR-6: Pharmacy Completions](plans/2026-05-26-wave-pr6-pharmacy.md)
- [Wave PR-7: Patient Engagement](plans/2026-05-26-wave-pr7-patient-engagement.md)
- [Wave PR-8: Provider & Staff](plans/2026-05-26-wave-pr8-provider-staff.md)
- [Wave PR-9: Compliance & Governance](plans/2026-05-26-wave-pr9-compliance.md)
- [Wave 10 - Final 100% Production Hardening](plans/2026-05-26-wave10-final-100-percent.md)
- [Phase 2: Maternity Module + HL7 v2 ADT](plans/2026-05-28-phase2-clinical-interop.md)
- [Phase 3: Scheduling & Provider/Staff Workflows](plans/2026-05-28-phase3-scheduling-staff.md)
- [Phase 4: Revenue Cycle Dashboard + Payment Plans](plans/2026-05-28-phase4-financial.md)
- [Phase 5: Radiology + Drug Formulary + Controlled Substances](plans/2026-05-28-phase5-lab-pharmacy.md)
- [Phase 6: USSD, Care Plans, Surveys, Record Export](plans/2026-05-28-phase6-patient-engagement.md)
- [Phase 7: Credentialing, Advance Directives, Retention, Pen Test Log](plans/2026-05-28-phase7-compliance.md)
- [Phase 8: Multi-Region Config + Tenant Isolation](plans/2026-05-28-phase8-infrastructure.md)
- [Phase 9: Dependabot, Snyk, Threat Model](plans/2026-05-28-phase9-security.md)
- [Production Readiness - Master Roadmap (May 28)](plans/2026-05-28-production-readiness-master.md)

</details>

<details><summary><strong>reference/</strong> — 1 docs</summary>

- [OpesCare Strategic Maturity, Standards, Data Governance, QA & Scale Master Pack](reference/OPESCARE_STRATEGIC_MATURITY_STANDARDS_DATA_DICTIONARY_QA_AND_SCALE_MASTER_PACK.md)

</details>

<details><summary><strong>security/</strong> — 1 docs</summary>

- [Security Sign-Off Report (2026-05-25)](security/SECURITY_SIGNOFF_2026-05-25.md)

</details>

<details><summary><strong>specs-core/</strong> — 3 docs</summary>

- [Missing & Incomplete Operational Modules — Implementation Spec](specs-core/OPESCARE_MISSING_OPERATIONAL_MODULES_COMPLETE_IMPLEMENTATION.md)
- [Operational Modules & End-to-End Flows Implementation Plan](specs-core/OPESCARE_OPERATIONAL_MODULES_AND_END_TO_END_FLOWS_IMPLEMENTATION.md)
- [Master Prompt V3 — Full Module Flows (Upgrade + Build Missing)](specs-core/opescare_v3_full_flows.md)

</details>

---
*Generated 148-doc index (80 living, 68 archived). Regenerate after adding docs.*
