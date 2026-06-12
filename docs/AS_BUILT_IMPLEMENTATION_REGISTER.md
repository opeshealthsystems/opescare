# OpesCare — As-Built Implementation Register

**Date:** 2026-06-11
**Purpose:** The single source of truth for *what the platform actually contains today*, module by module, derived directly from the code (45 backend modules under `app/Modules`, 486 models, ~200 controllers, ~900 routes, plus the mobile app, SDKs, widget, and Bridge agent). This complements the design specs (intent) and `audits/SPEC_VS_CODE_GAP_AUDIT.md` (spec→missing). Where a module has a known gap, it is flagged inline and cross-referenced to the gap audit.

**Status legend:**
🟢 **Implemented** — built, wired (service + controller/route), and functional.
🟡 **Partial** — substantially built but with a material gap (noted).
🔴 **Stub / Missing** — present in name but core behavior not implemented.

**Platform thesis (one paragraph):** OpesCare is a patient-centered digital health **identity, interoperability, and care-operations** platform — not a single-hospital CRUD system. A patient owns a portable **Health ID** and a longitudinal, event-based record that travels across facilities under consent. It is a Laravel modular monolith (PostgreSQL system-of-record, Redis for queues/cache/webhooks), FHIR R4-aligned at the boundary, OAuth2/PKCE for the partner surface, built for multi-facility, multi-country deployment (Gabon first). It enforces hard invariants throughout: centralized identity writes, no probabilistic auto-merge, immutable clinical events (amend/void/reverse/entered-in-error), idempotent external writes, provenance on every imported record, minimized break-glass emergency access, policy-bounded offline access, and effective-dated per-country regulation packs.

---

## At-a-glance status

| Domain | Modules | Overall |
|--------|---------|---------|
| Identity, records & consent | PatientIdentity, MasterPatientIndex, EncounterManagement, ConsentManagement, Fhir, FileStorage | 🟢 strong core |
| Clinical care & workflow | Triage, OperationalFlow, ClinicalDecisionSupport, WardManagement, Maternity, Immunization, Telemedicine, Referral, Appointments, Queue | 🟢 with 2 gaps (visit-closure safety, telemedicine calls) |
| Diagnostics, pharmacy & supply | Inventory (+ lab/imaging via clinical routes) | 🟢 |
| Revenue & access | Billing, Insurance, Subscription | 🟢 |
| Interoperability & developer platform | Connect, Partners, OpesCareLite, Offline (+ SDK/Widget/Bridge) | 🟡 server strong; widget/SDK/webhook-events incomplete |
| Governance, security & compliance | Governance, SecurityOperations, AccessControl, Legal, ResearchAccess, PublicHealth, Analytics | 🟡 strong except MFA, immutable audit, PH submission |
| Operations & enablement | Staff, Support, DataImport, FacilityReadiness, CountryExpansion, Admin, Search, Communications/Messaging/Notifications/Broadcasts/Tasks | 🟢 with DataImport execution stub |
| Workforce competency | Academy | 🟡 built but access-gate not enforced |

---

## 1. Identity, Records & Consent (the core)

### PatientIdentity — 🟢
**Purpose:** Sole authorized path for permanent patient creation; owns identity profiles and the Health ID. Enforces the "no module inserts a patient directly" invariant.
**Built:** `PatientIdentityService`; Health ID generation (`HealthIdGeneratorService` — `CC-HID-XXXX-XXXX-XXXX` format, safe alphabet excluding O/0/I/1/L, SHA-256 check block validated with `hash_equals`, collision retry); models `Patient`, `PatientIdentityProfile`, `PatientIdentifier`, `PatientExternalIdentifier`, `HealthIdAlias`, `HealthIdQrToken`, `HealthIdVerificationEvent`. Verify / verify-QR / emergency-profile routes wired.

### MasterPatientIndex — 🟢
**Purpose:** Cross-facility identity matching without unsafe auto-merge.
**Built:** `MasterPatientIndexService`; models `MpiCandidate`, `IdentityMergeCase`, `PatientMergeAlias`, `ExternalRecordMatch`, `IdentityRiskFlag`. Uncertain matches route to review (Reconciliation), honoring the no-probabilistic-auto-merge invariant.

### EncounterManagement — 🟢 (see visit-closure note)
**Purpose:** The longitudinal encounter/visit record and consultation.
**Built:** `ConsultationService`, `VisitManagementService`; models `Encounter`, `Visit`, `VisitStep`, `VisitTimeline`, `VisitClosure`, `ClinicalNote`. SOAP consultation with CDSS hooks.
**Gap:** visit-closure clinical safety blockers not enforced — see OperationalFlow.

### ConsentManagement — 🟢
**Purpose:** Consent grants, scopes, revocation; gates record access.
**Built:** `ConsentManagementService`; `RequireConsentGrant` middleware enforces active + non-expired + facility-scoped + scoped grants on pull/push; models `ConsentGrant`, `ConsentRequest`, `ConsentRevocation`.

### Fhir — 🟢
**Purpose:** FHIR R4 semantics at the exchange boundary (read-aligned, not a raw FHIR server).
**Built:** `FhirService` + 13 resource mappers (Patient, Encounter, Consent, Observation, DiagnosticReport, MedicationRequest, Condition, AllergyIntolerance, Immunization, Coverage, DocumentReference, Organization, Practitioner); models `FhirMapping(+Field/Version)`, `FhirResourceReference`, `FhirSubscription`.

### FileStorage — 🟢
**Purpose:** Document/attachment storage; underpins verifiable documents.
**Built:** `FileStorageService`; models `FileAsset`, `FileClassification`, `FileShareToken`, `VirusScanResult`, `SignedDownloadToken`. Verifiable-document lifecycle (issue/amend/revoke/entered-in-error/verify/share) and signing (`DocumentSignature`) are wired.

---

## 2. Clinical Care & Workflow

### Triage — 🟢
**Purpose:** Acuity scoring, reassessment, emergency escalation.
**Built:** `TriageService`, `TriageScoringService`, `EmergencyWorkflowService`; models `TriageRecord`, `TriageScore`, `TriageVitalSign`, `TriageReassessment`, `TriageAudit`, `EmergencyCase`, `EmergencyEscalation`.

### OperationalFlow — 🟡
**Purpose:** End-to-end patient journey / visit state machine and medication reconciliation.
**Built:** `PatientJourneyService`, `VisitManagementService`, `MedicationReconciliationService`; `/patient-journey`, `/visits` create/transition/complete/cancel.
**Gap (High, patient-safety):** `VisitManagementService::complete()/transition()` enforce only status transitions; the spec-required closure blockers (unacknowledged critical lab/CDSS alert, missing consultation note / discharge document, patient still in queue) are not checked. → gap audit Tier 1 #6.

### ClinicalDecisionSupport — 🟢
**Purpose:** Drug-interaction / allergy / dose rules, alerts, overrides with QA review.
**Built:** `ClinicalDecisionSupportService`, `DrugInteractionService`, `ClinicalAlertService`, `RuleEvaluationService`, `AlertOverrideService`; models `ClinicalRule`, `DrugInteractionRule`, `AllergyAlertRule`, `DoseWarningRule`, `ClinicalAlert`, `AlertOverride`, `CriticalValueAlert/Acknowledgement`.

### WardManagement — 🟢
**Purpose:** Admissions, beds, transfers, nursing rounds, discharge planning.
**Built:** `AdmissionService`, `WardService`, `DischargePlanningService`; models `Ward`, `Bed`, `BedAssignment`, `BedTransfer`, `Admission`, `NursingRound`, `DischargePlan`, `InpatientNote`, `InpatientMedicationAdministration`.

### Maternity — 🟢
**Purpose:** Antenatal, delivery, postnatal care.
**Built:** `MaternityService`, `AntenatalCareService`; models `PregnancyRecord`, `AntenatalRecord/Visit`, `DeliveryRecord`, `PediatricRecord`.

### Immunization — 🟢
**Purpose:** Vaccination schedules and records.
**Built:** `ImmunizationService`; models `ImmunizationRecord`, `VaccinationSchedule`.

### Telemedicine — 🟡
**Purpose:** Teleconsultation sessions with consent and a virtual waiting room.
**Built:** `TelemedicineService`, `TelemedicineConsentService`, `VirtualWaitingRoomService`, `CallProviderService`; models `Teleconsultation`, `TelemedicineConsent/Note/Audit`, `CallSession`, `VirtualWaitingRoom`, `TelemedicinePaymentLink`.
**Gap (Med):** `CallProviderService::initiateCall()` only creates a `CallSession` with a locally-hashed `room_id` — no Twilio/Agora/WebRTC/TURN/ICE or media-token issuance, so calls cannot actually connect. → gap audit Tier 2.

### Referral — 🟢
**Purpose:** Inter-facility referrals with consented record-package access.
**Built:** `ReferralService`; models `ReferralCase`, `ReferralAccessGrant`, `CrossFacilityRecordRequest`.

### Appointments — 🟡
**Purpose:** Booking, self-booking, reschedule, cancel, no-show, waitlist, reminders.
**Built:** `AppointmentService`, `PatientSelfBookingService`, `WaitlistService`, `AppointmentReminderService`; full model set (`Appointment`, `AppointmentSlot`, `AppointmentType`, `AppointmentWaitlist`, `AppointmentReminder`, `AppointmentCheckIn`, `AppointmentStatusHistory`); staff + patient booking UIs.
**Gap (Med):** reminder scheduling/sending methods exist but no cron/job invokes them — reminders & booking confirmations are never dispatched. → gap audit Tier 2 / SOP-005.

### Queue — 🟢
**Purpose:** Check-in, patient flow, prioritization, public queue display (PHI-masked).
**Built:** `QueueService`, `QueueDisplayService`; models `Queue`, `QueueTicket`, `QueueStation`, `QueueTransfer`, `QueuePriorityRule`, `QueueStatusHistory`, `QueueDisplaySetting`.

---

## 3. Diagnostics, Pharmacy & Supply

### Inventory — 🟢
**Purpose:** Stock control, pharmacy dispensing, controlled substances, blood bank, supply chain.
**Built:** `PharmacyInventoryService`, `BloodInventoryService`, `StockAuditService`, `SupplyChainService`; models for stock (`StockBatch`, `StockMovement`, `StockAdjustment`, `StockAudit`, `ReorderRule`), procurement (`PurchaseOrder(+Item)`, `GoodsReceipt(+Item)`, `Supplier`), controlled substances (`ControlledSubstanceRecord/Inventory/Dispensing`), blood (`BloodInventory`, `BloodAvailability`), and pharmacy (`PharmacyInventory`, `DrugFormulary`, `PharmacyStockAvailability`).
**Note:** Lab/pathology and imaging flows (orders, results, critical values, radiology, DICOM) are modeled (`LabOrder`, `LabResult`, `LabPathReport`, `ImagingOrder`, `RadiologyReport`, `DicomStudy`, `SpecialtyDiagnosticReport`) and wired through `routes/clinical.php` rather than a dedicated `app/Modules` folder.

---

## 4. Revenue & Access

### Billing — 🟢
**Purpose:** Invoicing, payments, wallet, cashier sessions, reconciliation.
**Built:** `BillingService`, `PaymentService`, `PaymentReconciliationService`; models `Invoice(+Item/Adjustment)`, `Payment`, `PaymentMethod`, `PaymentPlan(+Installment/Item)`, `Wallet`, `WalletTransaction`, `CashierSession`, `Receipt`, `Refund`, `MobileMoneyTransaction`. Discounts/waivers via approval workflow (`InvoiceAdjustment.requires_approval`).

### Insurance — 🟢
**Purpose:** Eligibility, preauthorization, claims, claim payments — with minimum-necessary disclosure.
**Built:** `InsuranceEligibilityService`, `PreauthorizationService`, `ClaimService`, `ClaimPaymentService`; models `InsuranceProvider/Plan`, `PatientInsurancePolicy`, `EligibilityCheck`, `PreauthorizationRequest/Decision`, `InsuranceClaim`, `ClaimSubmission/Item/Decision/Payment/Message/Document`, `RemittanceAdvice`. Minimum-necessary view enforced in code.

### Subscription — 🟢
**Purpose:** Facility subscription plans, usage, limits.
**Built:** `SubscriptionService`, `PlanLimitService`; models `SubscriptionPlan`, `PlanFeature/Limit`, `OrganizationSubscription`, `SubscriptionInvoice/Payment`, `SubscriptionUsageMetric`, `TrialPeriod`, `UsageLimit`.

---

## 5. Interoperability & Developer Platform ("Connect")

### Connect — 🟡
**Purpose:** The partner/system API surface: token auth, patient search, consent request/verify, summary/emergency-profile pull, record push (encounters/labs/prescriptions), inventory sync, reconciliation.
**Built (🟢 server core):** OAuth2 client-credentials `POST /connect/auth/token`, per-client throttle, `IdempotencyProtection` + consent-grant middleware on writes/pulls; controllers under `Api/V1/Connect/`; `ConnectAdminService`; developer-portal data layer (`DeveloperAccount/Organization/App`, `ApiCredential`, `ApiScopeGrant`, `ProductionAccessRequest`, `IntegrationCertification(+Run)`, `ApiUsageMetric`).
**Gaps:**
- 🔴 **Embeddable Connect Widget not implemented** — `widget/connect-widget.html` is a demo iframe to an external host; widget session tokens (`wgt_session_*`) validate nothing (no `WidgetSession` model/validator). → gap audit Tier 1 #3.
- 🟡 **Webhook delivery engine is strong** (signed, retried, dead-lettered, replayable, SSRF-guarded) but **only `lab_result.released` and `patient.updated` are ever emitted** — ~28 documented event types never fire. → gap audit Tier 1 #4.
- 🟡 **SDKs (PHP/Python/TS)** implement plumbing (token cache, retry/backoff, HMAC verify, idempotency) but only 6 of ~24 promised method groups; missing inventory/availability/reservation/dispense/document/sync-status/reconciliation. → gap audit Tier 2.
- 🟡 **OpenAPI contract** documents 6 of ~25+ endpoints.
- 🔴/🟡 Missing Connect endpoints: medication & blood availability/reservation, blood needs/transfers, dispense push, document push, referral package pull, lab-result amendment, sync-status.

### Partners — 🟢
**Purpose:** Partner organizations, agreements, trust levels, contribution governance, quality/risk scoring.
**Built:** Full domain — enums (`PartnerType/Status/TrustLevel`), 16 models, 6 policies, and services (`PartnerApplicationService`, `PartnerAgreementService`, `PartnerPermissionService`, `PartnerContributionService`, `PartnerIntegrationGovernanceService`, `PartnerQualityScoreService`, `PartnerRiskScoreService`, `PartnerVerificationService`, `PartnerAuditService`).

### OpesCareLite — 🟡
**Purpose:** Lightweight, offline-first deployment for low-connectivity facilities.
**Built:** `OpesCareLiteService`; models `LiteConfig`, `LiteDevice(+Registration)`, `LiteModuleEntitlement`, `LiteOfflineEvent`, `LiteSyncJob`, `LiteConflict`; `v1/lite/*` routes.
**Gap:** offline cache encryption not evidenced (shared with Offline below).

### Offline — 🟡
**Purpose:** Policy-bounded offline data access, sync, and conflict resolution.
**Built:** `OfflinePolicyService`, `SyncService`, `ConflictResolutionService`; models `OfflineQueue`, `OfflineAuditEvent`, `OfflineCachePolicy`, `LocalCachePolicy`, `SyncConflict/Attempt/Job`.
**Gap (Med):** no enforced device AES-256 encryption attestation before caching EMR/Medical-ID data (documented P0 control). → gap audit Tier 2.

### Bridge agent (`bridge-agent/`) — 🟡
**Purpose:** On-prem connector that syncs legacy/local facility systems up to the platform.
**Built (🟢 server side):** `BridgeAgent/BridgeDevice/BridgePairingCode` models, `VerifyBridgeAgent` middleware (key-hash + active check), pairing/heartbeat/status routes, admin portal, `BridgeMapping/Connector/Version`. Agent side: auth, config, CSV connector, local SQLite queue, syncer, heartbeat.
**Gaps (Med):** agent ships only a **CSV connector** (no Excel/JSON/XML/SFTP/DB despite config enums); local-queue **encryption not evidenced**; agent-side mapping/validation/auto-update absent.

---

## 6. Governance, Security & Compliance

### Governance — 🟡
**Purpose:** Data governance, consent, corrections, retention, country policy, data export / patient rights (DSAR).
**Built:** `ConsentService`, `CorrectionRequestService` (amend-not-delete, statement-of-disagreement), `DataExportService`, `CountryPolicyService`, `AccessLogService`, `EmergencyAccessService`; models incl. `DataRetentionPolicy`, `PatientRightsRequest`, `RecordExportRequest`, `PrivacyComplaint`, `BreachReport` (72h clock), `CountryDataResidencyRule`.
**Gaps (Med):** data-residency rule is consumed only as a launch checkbox (no runtime cross-border enforcement); `DataRetentionService::enforce()` has no legal-hold pause. → gap audit Tier 2.

### SecurityOperations — 🟡
**Purpose:** Audit explorer, suspicious-access detection, breach workflow, incidents, compliance export, API-abuse detection, access reviews.
**Built:** `AuditExplorerService`, `SuspiciousAccessDetectionService`, `BreachWorkflowService`, `SecurityIncidentService`, `ComplianceExportService`, `ApiAbuseDetectionService`, `AccessReviewService`; models `SecurityAuditLog`, `SecurityIncident`, `BreachReport`, `SuspiciousAccessFlag`, `AccessReview(+Schedule)`, `PenTestEngagement/Finding`.
**Gap (High):** audit log "append-only" is a code convention only — **no immutable external store (S3 Object Lock) and no pgaudit**. → gap audit Tier 1 #9.

### AccessControl — 🟡
**Purpose:** RBAC, roles, permissions, high-risk permission auditing, emergency (break-glass) access.
**Built:** `EmergencyAccessService`; models `Role`, `Permission`, `RolePermissionMatrix`, `HighRiskPermission`, `PermissionAudit`, `EmergencyAccessEvent`. Facility-scope + consent middleware enforced.
**Gaps (High/Med):** **no MFA/TOTP** (login is single-factor; the only OTP is the *primary* phone factor); emergency access is logged/reviewed but not time-boxed (no `expires_at` TTL). → gap audit Tier 1 #9 / SOP-004.

### Legal — 🟢
**Purpose:** Legal document management, versioning, user acceptances.
**Built:** `LegalDocumentService`; models `LegalDocument(+Version/ChangeLog)`, `UserLegalAcceptance`, `PartnerAgreementAcceptance`.

### ResearchAccess — 🟢
**Purpose:** De-identified research data requests and access governance.
**Built:** `ResearchAccessService`; models `ResearchRequest`, `ResearchAccessRequest/Log`, `ResearchDataset`, `ResearchDataAgreement`, `ResearcherProfile`, `DataAccessCommitteeReview`, `EthicsApproval`, `PublicationReview`.

### PublicHealth — 🟡 (corrected)
**Purpose:** Disease signals, baselines, phased statutory reporting, national (DHIS2/MINSANTE) transmission.
**Built:** `SignalDetectionService`, `DataQualityCheckService`, `DraftGenerationService`, `ExportService` (module); full reporting workflow (types, rules, drafts, review/approve/correct/reject, versions, status history), small-cell suppression (<5), `PublicHealthReport/Signal/Baseline`, `SignalAlert/Review`. **A real DHIS2 integration exists** in the services layer: `app/Services/Integration/Dhis2Service.php` (`pushDataValues`, `pushMonthlySummary`, `testConnection` — live `Http::post(.../api/dataValueSets)`), `config/dhis2.php`, `app/Console/Commands/PushDhis2ReportCommand.php`, and the scheduled monthly `health-id:generate-minsante-report`.
**Gaps (Med):** the *interactive* `submitReport` endpoint (`Api/V1/PublicHealth/PublicHealthController.php` L433) fabricates its response instead of routing through `Dhis2Service` → GAP-007; export is CSV-only (Excel/PDF missing); baselines are simplistic. (Correction: the first pass wrongly called DHIS2 submission entirely "faked" — the batch path is real.)

### Analytics — 🟢
**Purpose:** Operational and product analytics, dashboards, KPI export.
**Built:** `OperationalAnalyticsService`, `ProductAnalyticsService`, `ReportExportService`; models `DashboardMetric/Profile/Snapshot`, `KpiDashboard`, `KpiExport`, `MetricDefinition/Snapshot`, `AnalyticsSnapshot/AccessLog`, `ProductEvent`.

---

## 7. Operations & Enablement

### Staff — 🟢
**Purpose:** Staff/HR, rosters, shifts, leave, credentials, on-call.
**Built:** `StaffService`, `RosterService`, `LeaveService`; models `StaffProfile`, `StaffShift`, `StaffCredential`, `ProfessionalLicense`, `DutyRoster`, `RosterAssignment`, `LeaveRequest`, `OnCallSchedule`, `ProviderShift`, `StaffTrainingStatus`.

### Support — 🟢
**Purpose:** Helpdesk tickets, SLA, assignment, escalation, knowledge base.
**Built:** `SupportService`, `TicketAssignmentService`, `KnowledgeBaseService`; models `SupportTicket`, `TicketAssignment/Message/StatusHistory`, `SupportCategory`, `SupportAttachment`, `SlaPolicy`, `KnowledgeBaseArticle`, `IncidentReport`.

### DataImport — 🔴 (execution stub)
**Purpose:** Bulk data migration/onboarding (upload → map → validate → preview → import → rollback).
**Built (🟢 up to staging):** `ImportService`, `ImportValidationService`, `ImportMappingService`, `ImportRollbackService`; full wizard UI; models `ImportBatch/File/Job/Row/RowError`, `ImportMapping/Template`, `ImportPreview`, `ImportDuplicateCandidate`, `ImportRollback`, `ImportAuditEvent`.
**Gap (High):** `DataImportController::approve()` marks the import complete but **creates no records** ("simulate immediate completion") — no `Patient::create`, no duplicate detection, no queued job. The actual import does not happen. → gap audit Tier 1 #5 / SOP-016.

### FacilityReadiness — 🟢
**Purpose:** Go-live readiness scoring and approval gating.
**Built:** `FacilityGoLiveService`, `FacilityReadinessScoringService`; models `FacilityGoLiveReadiness`, `FacilityReadinessScore`, `GoLiveChecklist(+Item)`, `GoLiveApproval/Audit/Blocker`, `TenantOnboardingCheckpoint`.

### CountryExpansion — 🟢
**Purpose:** Per-country policy, legal review, launch approval (effective-dated regulation packs).
**Built:** `CountryExpansionService`; models `Country`, `CountryProfile/Policy`, `CountryHealthRegulation`, `CountryDataResidencyRule`, `CountryLanguagePack`, `CountryPaymentSetting`, `CountryPublicHealthRule`, `CountryLaunchApproval`, `CountryLegalReview`.

### Admin — 🟢
**Purpose:** Master admin control center, feature flags, platform/system health.
**Built:** `PlatformAdminService`, `FeatureFlagService`, `SystemHealthService`; models `PlatformSetting`, `FeatureFlag`, `SystemHealthSnapshot`, `MaintenanceWindow`, `AdminActionLog/Review`.

### Search — 🟢
**Purpose:** Permission-filtered global search with audit.
**Built:** `GlobalSearchService`, `SearchIndexingService`, `SearchPermissionService`; models `SearchIndex`, `SearchLog`, `SearchPermissionFilter`, `SavedSearch`.

### Communications layer — 🟢 (Communications, Messaging, Notifications, Broadcasts, Tasks)
**Purpose:** Routing, secure messaging, multi-channel notifications, broadcasts, and action tasks.
**Built:**
- **Notifications** — the richest sub-system: `NotificationService` + channel services and providers for **Email, SMS, Push, Voice, WhatsApp**, plus `AlertEscalationService`, `NotificationPreferenceService`, `NotificationTemplateRenderer`; models `NotificationEvent/Delivery/Template/Preference`, `EscalationChain`, `VoiceNotificationJob`, `PushDeviceToken`.
- **Messaging** — `MessagingService`, `MessagePermissionService`, `MessageAttachmentService`; threaded messages with participants/attachments.
- **Communications** — `CommunicationRouterService` (channel routing).
- **Broadcasts** — `BroadcastService`; `Broadcast`, `BroadcastAcknowledgement`.
- **Tasks** — `TaskService`; `ActionTask`, `ReportAssignment`.
**Note:** Channels are implemented; the only gap is the *scheduling wiring* for appointment reminders (see Appointments).

---

## 8. Workforce Competency

### Academy — 🟡
**Purpose:** Digital-health competency certification — courses, modules, lessons, quizzes, simulations, badges, expiry; intended to gate sensitive access to certified staff.
**Built:** `CourseService`, `EnrollmentService`, `QuizService`, `SimulationService`, `CertificateService`, `CertificateVerificationService`, `AcademyReportingService`, `CompetencyGateService`; models `Course/Module/Lesson`, `CourseEnrollment`, `LessonProgress`, `Quiz(+Question/Attempt)`, `SimulationAttempt`, `Certificate`, `CertificationBadge/Requirement/Expiry`, `CompetencyRequirement`, `TrainerSignoff`.
**Gap (High):** `CompetencyGateService::authorizeAction()` is fully written but **never called** by any sensitive-access path — only `registerRequirement()` is used. The training-gated-access control is defined but inactive. → gap audit Tier 1 #8.

---

## 9. Client deliverables & sub-products

### Patient mobile app (Flutter, `apps/mobile-patient`) — 🟡 → 🟢 after Firebase
**Built:** auth (email/password + legacy phone/OTP), token-refresh interceptor, Health ID + QR, profile, care plans, surveys, medical export, appointment booking, insurance marketplace, family members, care map, referrals, documents, labs, prescriptions, timeline, access logs, offline UX banners, dark mode, FCM/local-notification plumbing.
**Fixed (2026-06-11):** release `AndroidManifest.xml` now declares `INTERNET` + `POST_NOTIFICATIONS`; app label set to "OpesCare".
**Remaining gaps:** Firebase not configured (`firebase_options.dart` stub, no `google-services.json`/plist, Gradle plugins not applied) → push/crash/analytics inert; app icon/splash assets + config; a few accessibility `Semantics` labels and one analytics event. → gap audit Tier 1 #1–2 / Tier 3.

### Connect SDKs (`sdk/php`, `sdk/python`, `sdk/typescript`) — 🟡
Plumbing complete (token cache, retry/backoff, HMAC webhook verify, idempotency, typed errors, sandbox/prod). Only 6 of ~24 method groups implemented. → gap audit Tier 2.

### Connect Widget (`widget/`) — 🔴
Demo iframe only; the actual embeddable widget and its session-token security are not implemented. → gap audit Tier 1 #3.

### Verifiable Documents / Medical ID — 🟢
Full lifecycle (issue/amend/revoke/entered-in-error/verify/share), signing, QR, hashing, numbering, templates, chain-of-custody (`DocumentSpecimenEvent`), public verification routes.

### Care Access Map — 🟢
Public facility directory with geospatial (Haversine) search; facility registry, services, hours, insurance networks, pharmacy stock, lab tests, blood availability, facility claims/verification/freshness.

---

## 10. Cross-cutting service layer (`app/Services` — 64 classes, was under-covered in v1)

Not all logic lives under `app/Modules`. A parallel `app/Services/` tree holds 64 service classes in 23 subfolders. The notable ones the first pass missed:

- **Integration / Interoperability — 🟢:** `Integration/Dhis2Service.php` + `Interoperability/Dhis2PushService.php` (DHIS2 Web API, real HTTP), `Integration/Hl7AdtService.php` + `Interoperability/Hl7AdtParser.php` (HL7 v2 ADT messaging), `Interoperability/CrossFacilityRecordService.php`.
- **Payments — 🟢:** `Payment/MtnMomoGateway.php`, `Payment/OrangeMoneyGateway.php`, `Payment/MobileMoneyService.php` and `Payments/MtnMomoService.php`, `Payments/OrangeMoneyService.php` — real MTN MoMo + Orange Money mobile-money integrations (all make live HTTP calls). *(Two overlapping folders — see Tech Debt.)*
- **Lab / Imaging — 🟢:** `Lab/DicomWebService.php` (DICOMweb), `Lab/RadiologyReportService.php`, `Lab/CriticalValueService.php`, `Lab/ReferenceRangeService.php`.
- **USSD — 🟢:** `Ussd/UssdMenuService.php`, `PatientEngagement/UssdSessionService.php` — feature-phone (USSD) access path.
- **Documents — 🟢:** 10 services (`Documents/DocumentIssuanceService`, `DocumentAmendmentService`, `DocumentRevocationService`, `DocumentVerificationService`, `DocumentShareService`, `DocumentTemplateService`, `DocumentNumberService`, `QrCodeGenerationService`, on-demand/assembler).
- **Security — 🟢:** `Security/KmsEncryptionService.php` (field-level AES-256-GCM + KMS — note empty AAD, GAP-026), `Security/PenTestService.php`.
- **Clinical — 🟢:** `Clinical/AllergyHardStopService.php`, `Clinical/AdvanceDirectiveService.php`, `Clinical/CarePlanService.php`, `Clinical/CareTeamService.php`.
- **Reports / Tenancy / Portal / Dashboard — 🟢:** `Reports/RevenueCycleService.php`, `Reports/ProviderPerformanceService.php`, `Tenancy/TenantOnboardingService.php`, `Tenancy/ApiUsageAnalyticsService.php`, `Portal/PortalContextService.php`.
- **Simulators — 🟢 (sandbox):** `Simulators/SimulatedEmailService.php`, `SimulatedSmsService.php`, `SimulatedWebhookService.php` — used for demo/test channels.
- **Top-level — 🟢:** `WebhookService.php`, `JwtService.php`, `AuditLogger.php`, `FacilityCodeGenerator.php`.

## 11. Scheduled automation (`app/Console` — 18 commands)

18 console commands exist; `routes/console.php` schedules: `backup:run` (daily 01:00), `backup:monitor` (daily 09:00), `backup:clean` (daily), `opescare:enforce-data-retention` (daily 02:00), `health-id:notify-expiring` (daily 08:00), `health-id:generate-minsante-report` (monthly), `health-id:archive-audit-logs` (monthly), `health-id:purge-revoked-tokens` (daily), `health-id:purge-bulk-exports` (daily), `maintenance:process` (every minute). Other commands (manual/triggered): `opescare:push-dhis2`, `RotateSecretsCommand`, `EncryptExistingPatientPii`, `CheckAgeTransitions` (minor→adult), `NotifyExpiringCredentials`, `ImportFacilityRegistry`, `ImportInsuranceRegistry`, `DemoSeed/Reset`, `AuditFacilityScope`, `PurgeExpiredData`. *Note: no appointment-reminder command is scheduled — GAP-015.*

## 12. Tests

`tests/Feature` holds **169 feature tests across 28 areas** (Academy, Appointments, Billing, CareMap, Clinical, Commands, Communications, Compliance, Connect, Documents, Interoperability, Lab, MedicalId, Mobile, Notifications, Partners, Pharmacy, Security, SecurityOperations, Staff, Tenancy, …) plus 4 Unit tests. Coverage is broad. **Not executed in this audit** — a green `php artisan test` should gate go-live and is currently unverified.

## 13. Tech debt / pre-deploy cleanup

- **Duplicate service folders** (confirm canonical, merge the other): `Payment/` vs `Payments/`; `Integration/Dhis2Service` vs `Interoperability/Dhis2PushService`; `ProviderPerformanceService` in `Reports/` and `Staff/`; `CarePlanService` in `Clinical/` and `PatientEngagement/`; `WaitlistService` in `app/Modules/Appointments/Services/` and `app/Services/Appointments/`. → GAP-audit TD-001.
- **Loose debug/patch scripts** to remove: `patch_diagnosis.php` (repo root); `apps/api-laravel/{col_check.php,col_check2.php,col_verify.php,fid_check.php,seal_check.php}`; `apps/api-laravel/scratch/generate_partner_*.php`. → TD-002.
- **Stray nested directory:** `apps/api-laravel/apps/api-laravel/public/images/leaflet/*` — delete. → TD-003.

## How to use this register

- Treat this as the **current-state** companion to the design specs (intent) and `audits/SPEC_VS_CODE_GAP_AUDIT.md` (the gap list).
- Every 🟡/🔴 here maps to a numbered item in the gap audit; close those to turn this register fully 🟢.
- Regenerate this document whenever a module's wiring changes materially, so it never drifts the way the old `OPESCARE_EXTENDED_MODULES_IMPLEMENTATION_AUDIT_RESULT.md` did.
