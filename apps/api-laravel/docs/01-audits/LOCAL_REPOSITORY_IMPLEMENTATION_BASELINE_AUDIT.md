# LOCAL REPOSITORY IMPLEMENTATION BASELINE AUDIT

**Project:** OpesCare  
**Auditor:** Claude Code  
**Date:** 2026-05-19  
**Branch:** main  
**Commit:** a956321  
**Status:** Phase 0 Complete — Implementation Ready  

---

## 1. Audit Scope

This document is the Claude Code Phase 0 baseline audit per `docs/07-agent-protocols/OPESCARE_CLAUDE_CODE_LOCAL_IMPLEMENTATION_PROTOCOL.md`. It classifies every module as IMPLEMENTED, PARTIAL, NEEDS_WIRING, NOT_STARTED, or DEFERRED.

---

## 2. Test Suite Status

| Command | Result |
|---|---|
| `php artisan test` | ✅ PASS: 151 tests, 574 assertions |
| `php artisan route:list` | ✅ PASS: 333 routes registered |
| `php artisan migrate:fresh --seed --env=testing` | ✅ PASS (after Codex FK fix) |

---

## 3. Repository Structure Overview

| Layer | Count | Status |
|---|---|---|
| Models (`app/Models/`) | 80 | ✅ |
| Module Services (`app/Modules/`) | 26 modules, 50+ services | ✅ |
| Migrations (`database/migrations/`) | 28 files, 130+ tables | ✅ |
| API Controllers (`app/Http/Controllers/Api/`) | 25+ | ✅ |
| Portal Controllers (`app/Http/Controllers/MedicalId/`) | 4 (Patient, Staff, Admin, Verify) | ⚠️ NEEDS_WIRING |
| Portal Views (`resources/views/portals/`) | 16 blade files | ⚠️ PARTIAL |
| Tests (`tests/Feature/`) | 30 test files | ✅ |
| Docs (`docs/`) | Created this session | ✅ |

---

## 4. Module Classification

### 4.1 IMPLEMENTED (Core — Fully working, tests pass, protected)

| Module | Key Files | Notes |
|---|---|---|
| Health ID / Medical ID | `PatientIdentityService`, `HealthIdGeneratorService`, 5 test files | Full QR, alias, verification flows |
| Patient Profile / EMR foundation | `Patient`, `ClinicalNote`, `Diagnosis`, `VitalSign`, `AllergyRecord` | Foundation complete |
| Consent & Privacy | `ConsentManagementService`, `ConsentService`, migration, tests | Grant, revoke, request flows |
| Emergency Access | `EmergencyAccessService`, `EmergencyAccessEvent`, tests | Override, review flows |
| Partner Governance | `Partners/` module, 9 services, 6 policies, tests | Full partner lifecycle |
| Connect API/SDK Foundation | `IntegrationClient`, `WebhookService`, `ConnectGovernanceController` | Scoped API access |
| Notifications | `NotificationService`, 4 providers (Email/SMS/Push/Voice/WhatsApp), templates | Full multi-channel |
| Messaging | `MessagingService`, `MessageThread`, `Message`, `MessageAttachment` | Thread-based secure messaging |
| Verifiable Documents | `DocumentVerificationService`, `QrCodeGenerationService`, `OfficialDocument` | Sign, verify, revoke, amend |
| Academy / Certification | `CourseService`, `CertificateService`, `QuizService`, 8 services | Full learning flows |
| Care Access Map | `CareMapSearchService`, `BloodAvailabilitySearchService`, `PharmacyStockSearchService` | Multi-resource search |
| Public Health Reporting | `PublicHealthController`, `SignalDetectionService`, `DraftGenerationService` | Report, signal, export |
| Data Governance | `DataExportService`, `CorrectionRequestService`, `CountryPolicyService` | GDPR-like flows |
| Audit Foundation | `AuditLogger`, `AuditEvent`, `AccessLog` | System-wide audit trail |
| MPI / Identity | `MasterPatientIndexService`, `MpiCandidate`, `IdentityMergeCase` | Duplicate detection |
| Communications | `CommunicationRouterService`, broadcasts, tasks | Multi-channel routing |
| Offline / Sync | `SyncService`, `SyncJob`, `SyncConflict`, `OfflineAuditEvent` | Conflict handling |
| Immunization | `ImmunizationService`, `ImmunizationRecord`, `VaccinationSchedule`, tests | Record + schedule |
| Referral | `ReferralService`, `ReferralCase`, `ReferralAccessGrant`, tests | Full referral lifecycle |

### 4.2 PARTIAL — Backend Exists, Portal UI Not Wired

| Module | Backend State | Missing |
|---|---|---|
| Appointments & Booking | `AppointmentService`, `Appointment`, `AppointmentSlot`, migration, tests ✅ | Portal controller passes `collect([])` — no DB query; no book/confirm/cancel/check-in UI actions |
| Queue & Patient Flow | `QueueService`, `QueueTicket`, `PatientCheckIn`, `PatientFlowEvent`, migration, tests ✅ | Portal controller passes `collect([])` — no DB query; no call/transfer/complete actions in UI |
| Billing, Payments & Wallet | `BillingService`, `PaymentService`, `Invoice`, `Payment`, `Receipt`, `Wallet`, migration, tests ✅ | Portal controller passes `collect([])`; no create invoice / process payment UI |
| Support / Helpdesk | `SupportService`, `SupportTicket`, `TicketMessage`, migration, tests ✅ | Portal controller passes `collect([])`; no create ticket / respond UI |
| Global Search | `GlobalSearchService`, `GlobalSearchController`, test ✅ | No portal search bar or results page |
| Facility Go-Live Readiness | `FacilityGoLiveService`, `FacilityGoLiveReadiness`, admin view ✅ | View exists and is wired to static demo data, not DB |
| Triage | `TriageService`, `TriageRecord`, migration ✅ | No portal UI page at all |
| EncounterManagement | `VisitManagementService`, `ConsultationService`, `Visit`, migration ✅ | No portal UI page |
| Operational Flow | `PatientJourneyService`, `OperationalFlowController` ✅ | End-to-end journey not surfaced in portal UI |

### 4.3 NOT_STARTED (Portal UI entirely missing)

| Module | Status | Phase |
|---|---|---|
| Staff / HR / Shift Management | Models missing; no service, no UI | Phase 12 |
| Inventory & Supply Chain | `PharmacyInventory` exists; no operational inventory service or UI | Phase 14 |
| File Storage & Medical Attachments | No dedicated file attachment module | Phase 15 |
| Analytics & Reporting Dashboard | No portal analytics page | Phase 16 |
| Insurance Claims & Preauthorization | No models or service | Phase 5 |
| Ward / Admission / Bed Management | Not started | Phase 18 |
| Telemedicine | Not started | Phase 17 |
| CDSS / Clinical Decision Support | Not started | Phase 19 |
| Subscription / SaaS Billing | Not started | Phase 23 |

### 4.4 DEFERRED (Advanced — not to be started yet)

```
Bridge Agent
OpesCare Lite
Patient Mobile App API Readiness
Provider Mobile App API Readiness
Country Expansion
AI Governance
```

---

## 5. Duplicate / Conflict Detection

| Check | Result |
|---|---|
| Duplicate model filenames | ✅ None found |
| Duplicate migration tables | ✅ None found |
| Duplicate route names | ✅ None found |
| Duplicate route method+URI | ✅ None found |
| Conflicting module names | ✅ None found |

---

## 6. Missing Portal Wiring (Critical Gap)

The most impactful missing layer is: **portal controllers return empty `collect([])` for all data.**

All four portal controllers (`PatientPortalController`, `StaffPortalController`, `AdminPortalController`) pass static/empty data to views. The underlying services and models exist and work, but they are not connected to the portal UI layer.

### Controllers That Need Wiring:

```
StaffPortalController::appointments()   → AppointmentService
StaffPortalController::queue()          → QueueService  
StaffPortalController::billing()        → BillingService
StaffPortalController::support()        → SupportService
PatientPortalController::index()        → Patient, Appointment, Invoice data
PatientPortalController::appointments() → AppointmentService
AdminPortalController::index()          → Admin KPIs from multiple services
```

---

## 7. Missing UI Actions

### Appointments
- [ ] Book appointment form/modal
- [ ] Confirm appointment action
- [ ] Reschedule appointment modal
- [ ] Cancel appointment (with reason)
- [ ] Check-in action
- [ ] Mark no-show

### Queue
- [ ] Call next patient
- [ ] Start service
- [ ] Transfer to station
- [ ] Complete service
- [ ] Emergency priority bypass

### Billing
- [ ] Create invoice form
- [ ] Add invoice items
- [ ] Process payment modal
- [ ] Issue receipt
- [ ] Wallet deposit / wallet payment

### Support
- [ ] Create new ticket form
- [ ] Reply to ticket
- [ ] Escalate ticket
- [ ] Close ticket

---

## 8. Uncommitted Changes

| File | Status |
|---|---|
| `docs/` (newly created) | Untracked — will be committed |
| All other files | ✅ Clean |

---

## 9. Known Risks (from Codex Baseline Review)

| Risk | Severity | Owner |
|---|---|---|
| 131 API routes without auth middleware | HIGH | Codex recommends fix; Claude Code to add before production |
| `actor_id` supplied from request rather than auth context | HIGH | Fix in Phase 6+ |
| Communication participant/assignment stubs | MEDIUM | Not yet implemented |
| System Node 20.11.0 too old for Vite (requires 20.19+ or 22.12+) | LOW | Dev environment only |

---

## 10. Implementation Build Order (Confirmed)

| Phase | Module | Status |
|---|---|---|
| 0 | Repository audit and setup | ✅ **THIS DOCUMENT** |
| 1 | Wire portal controllers to real data | 🔲 NEXT |
| 2 | Appointments UI — book, confirm, reschedule, cancel, check-in | 🔲 |
| 3 | Queue UI — call, transfer, complete, priority | 🔲 |
| 4 | Billing UI — invoice, payment, receipt, wallet | 🔲 |
| 5 | Insurance Claims & Preauthorization | 🔲 NOT_STARTED |
| 6 | End-to-End Visit Flow wiring | 🔲 |
| 7 | Support portal — create, reply, escalate, close | 🔲 |
| 8 | Data Import UI | 🔲 |
| 9 | Admin Control Center wiring | 🔲 |
| 10 | Go-Live Readiness wiring | 🔲 |
| 11 | Global Search portal | 🔲 |
| 12 | Staff / HR / Shift Management | 🔲 NOT_STARTED |
| 13 | Triage portal UI | 🔲 |
| 14 | Inventory portal UI | 🔲 |
| 15 | File Storage & Medical Attachments | 🔲 NOT_STARTED |
| 16 | Analytics & Reporting | 🔲 NOT_STARTED |
| 17 | API / SDK / Webhooks hardening | 🔲 |
| 18+ | Bridge Agent, Lite, Ward, CDSS | 🔲 DEFERRED |

---

## 11. Next Action

**Immediately begin Phase 1:**  
Wire `StaffPortalController` and `PatientPortalController` to their respective services so appointments, queue, billing, and support show real (demo-seeded) data, then add the missing action UI for each portal page.

Start branch: `feature/opescare-portal-wiring`
