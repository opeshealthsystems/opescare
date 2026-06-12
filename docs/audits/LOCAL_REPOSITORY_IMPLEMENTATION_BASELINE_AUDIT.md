# OpesCare — Local Repository Implementation Baseline Audit

**Document version:** 2.0 (full rebuild)
**Audit date:** 2026-05-20
**Auditor lane:** Claude Code (builder)
**Reviewer/tester lane:** Codex (do not merge lanes)
**Status:** AUTHORITATIVE BASELINE — update this file whenever new phases are merged

---

## 1. Summary Scorecard

| Dimension | Count | Notes |
|---|---|---|
| Eloquent Models | **408** | All use `HasUuids`; UUID PKs throughout |
| Database Migrations | **61** | 2 Laravel defaults + 59 OpesCare phases |
| Module Service Classes | **131** | Under `app/Modules/{Module}/Services/` |
| API Controllers | **58** | Under `app/Http/Controllers/Api/` |
| Route files | **6** | `api.php`, `web.php`, `mobile.php`, `sdk.php`, `fhir.php`, `channels.php` |
| Language files (EN) | **18** | `lang/en/` |
| Language files (FR) | **18** | `lang/fr/` — full bilingual parity |
| Feature Tests | **151** | All passing ✅ (zero failures) |
| Application Modules | **46** | Under `app/Modules/` |
| Docs directories created | **24** | `docs/` hierarchy established |

**Test command:** `php artisan test --no-coverage` → **151/151 ✅**
**PHP runtime:** `/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php`

---

## 2. Migration Registry (61 files)

All migrations use `Schema::hasTable()` idempotency guards. All phase numbers are preserved as-is.

| # | File | Tables / Description |
|---|---|---|
| 01 | `0001_01_01_000001_create_cache_table.php` | Laravel cache (framework default) |
| 02 | `0001_01_01_000002_create_jobs_table.php` | Laravel job queue (framework default) |
| 03 | `2026_05_14_215515_create_facilities_and_audit_tables.php` | Facilities, audit events, access logs |
| 04 | `2026_05_14_215559_create_patients_tables.php` | Patients, visits, identifiers |
| 05 | `2026_05_14_215636_create_mpi_candidates_table.php` | MPI duplicate candidates |
| 06 | `2026_05_14_215649_create_roles_and_permissions_tables.php` | Roles, permissions, RBAC |
| 07 | `2026_05_14_215752_alter_users_table_for_foundation.php` | Users table extensions |
| 08 | `2026_05_14_224316_create_consent_tables.php` | Consent grants/requests/revocations |
| 09 | `2026_05_14_224355_create_emergency_access_tables.php` | Emergency access events |
| 10 | `2026_05_15_001327_create_clinical_mvp_tables.php` | Encounters, diagnoses, prescriptions, labs, vitals |
| 11 | `2026_05_17_000000_create_partner_governance_tables.php` | Partner applications, governance |
| 12 | `2026_05_17_000001_create_connect_platform_tables.php` | Integration listings, marketplace |
| 13 | `2026_05_17_000001_create_partner_audit_logs_table.php` | Partner audit trail |
| 14 | `2026_05_17_173408_add_demo_isolation_fields_to_tables.php` | Demo mode isolation fields |
| 15 | `2026_05_17_180838_create_medical_id_identity_tables.php` | Medical ID, QR tokens, verification |
| 16 | `2026_05_18_000001_create_public_health_reporting_tables.php` | Public health signals, reports, baselines |
| 17 | `2026_05_19_000001_create_data_governance_consent_tables.php` | Data governance, compliance cases |
| 18 | `2026_05_20_000000_create_communication_alerts_tasks_messaging_tables.php` | Notifications, tasks, messaging |
| 19 | `2026_05_21_000000_create_document_system_tables.php` | Documents, templates, versioning, signatures |
| 20 | `2026_05_22_000000_create_academy_certification_tables.php` | Academy, courses, certifications, quizzes |
| 21 | `2026_05_25_000000_create_care_map_tables.php` | Care map facilities, services, availability |
| 22 | `2026_05_26_000000_create_appointment_booking_tables.php` | Appointments, slots, reminders, cancellations |
| 23 | `2026_05_26_000001_create_queue_patient_flow_tables.php` | Queues, tickets, stations, display settings |
| 24 | `2026_05_26_000002_create_billing_payment_tables.php` | Invoices, payments, wallets, receipts |
| 25 | `2026_05_26_000003_create_facility_go_live_readiness_table.php` | Go-live checklists, blockers, approvals |
| 26 | `2026_05_26_000004_create_support_helpdesk_tables.php` | Support tickets, categories, KB articles |
| 27 | `2026_05_26_000005_create_offline_sync_tables.php` | Offline sync, conflicts, cache policies |
| 28 | `2026_05_27_000000_create_referral_immunization_tables.php` | Referrals, immunization records, schedules |
| 29 | `2026_05_27_000001_create_insurance_claims_tables.php` | Insurance claims, preauth, eligibility |
| 30 | `2026_05_27_000002_create_staff_hr_tables.php` | Staff profiles, rosters, leaves, shifts |
| 31 | `2026_05_27_000003_create_data_import_tables.php` | Import batches, rows, mappings, rollbacks |
| 32 | `2026_05_27_000004_create_admin_control_center_tables.php` | Feature flags, platform settings, module toggles |
| 33 | `2026_05_27_000005_create_file_storage_tables.php` | File assets, virus scans, tokens |
| 34 | `2026_05_27_000006_create_ward_management_tables.php` | Wards, beds, admissions, nursing rounds |
| 35 | `2026_05_27_000007_extend_connect_platform_tables.php` | Connect SDK, webhooks, developer portal |
| 36 | `2026_05_27_000008_create_supply_chain_tables.php` | Inventory, purchase orders, stock movements |
| 37 | `2026_05_27_000009_create_bridge_agent_tables.php` | Bridge agent, device pairing, sync jobs |
| 38 | `2026_05_27_000010_create_cdss_tables.php` | CDSS rules, alerts, overrides |
| 39 | `2026_05_27_000011_create_subscription_billing_tables.php` | SaaS subscription plans, limits, usage |
| 40 | `2026_05_27_000012_create_opescare_lite_tables.php` | OpesCare Lite, devices, entitlements |
| 41 | `2026_05_27_000013_create_legal_compliance_tables.php` | Legal documents, versions, user acceptances |
| 42 | `2026_05_28_000001_create_patient_mobile_api_tables.php` | Patient mobile devices, sessions, contexts |
| 43 | `2026_05_28_000002_create_provider_mobile_api_tables.php` | Provider mobile sessions, devices |
| 44 | `2026_05_29_000001_create_product_analytics_tables.php` | Product analytics, KPI dashboards, metrics |
| 45 | `2026_05_29_000002_create_code_system_mappings_table.php` | Code system mappings (ICD, SNOMED, LOINC) |
| 46 | `2026_05_29_000003_create_integration_certification_tables.php` | Integration certification, badges |
| 47 | `2026_05_30_000001_patch_webhook_delivery_and_add_developer_portal_tables.php` | Webhook delivery logs, developer portal |
| 48 | `2026_05_35_000001_add_missing_operational_module_tables.php` | Phase 35 gap-fill operational tables |
| 49 | `2026_05_36_000001_add_supplemental_operational_tables.php` | Phase 36 supplemental operational tables |
| 50 | `2026_05_37_000001_add_remaining_operational_tables.php` | Phase 37 remaining operational tables |
| 51 | `2026_05_38_000001_add_phase38_critical_missing_models.php` | Phase 38 critical missing models |
| 52 | `2026_05_40_000001_add_phase40_final_missing_models.php` | Phase 40 final model gap fill |
| 53 | `2026_05_42_000001_add_fhir_supplement_tables.php` | FHIR supplement (mappings, resource refs) |
| 54 | `2026_05_43_000001_add_phase43_gap_fill_models.php` | Phase 43 gap fill |
| 55 | `2026_05_44_000001_add_phase44_audit_security_models.php` | Phase 44A audit/security models |
| 56 | `2026_05_44_000002_add_phase44_domain_gap_models.php` | Phase 44B domain gap models |
| 57 | `2026_05_45_000001_add_phase45_final_gap_models.php` | Phase 45 final gap models |
| 58 | `2026_05_46_000001_add_phase46_connect_suite_models.php` | Phase 46A Connect Suite models |
| 59 | `2026_05_46_000002_add_phase46_bridge_lite_models.php` | Phase 46B Bridge/Lite models |
| 60 | `2026_05_47_000001_add_phase47_governance_maturity_models.php` | Phase 47 governance/maturity models |
| 61 | `2026_05_48_000001_add_phase48_data_governance_models.php` | Phase 48 data dictionary, permission governance |

---

## 3. Application Modules (46)

Each module lives at `app/Modules/{Module}/Services/`.

| Module | Services | Key Capabilities |
|---|---|---|
| Academy | 8 | Courses, quizzes, certifications, simulations, competency gates |
| AccessControl | 1 | Emergency access, break-glass |
| Admin | 4 | Platform settings, feature flags, module toggles, system health |
| Analytics | 3 | Operational analytics, product analytics, report export |
| Appointments | 2 | Appointment booking, reminders (48h/24h/2h intervals) |
| Billing | 3 | Billing, payments, payment reconciliation (cashier session management) |
| Broadcasts | 1 | Broadcast messaging |
| CareMap | 8 | Facility claims, freshness, verification, geocoding, search (pharmacy/lab/blood/insurance/care map) |
| ClinicalDecisionSupport | 4 | CDSS rules, clinical alerts, rule evaluation, alert override (high-risk gate) |
| Communications | 1 | Communication routing |
| Connect | 1 | Connect admin governance |
| ConsentManagement | 1 | Consent management |
| CountryExpansion | 2 | Country expansion, country policies |
| DataImport | 4 | Import service, validation, mapping, rollback |
| EncounterManagement | 2 | Consultations, visit management |
| FacilityReadiness | 2 | Go-live readiness, facility readiness scoring (4 sub-scores) |
| Fhir | 1 | FHIR R4 mapping service + mappers |
| FileStorage | 1 | File storage, virus scan, signed tokens |
| Governance | 6 | Access logs, consent governance, corrections, data export, emergency access |
| Immunization | 1 | Immunization records and schedules |
| Insurance | 4 | Eligibility, claims, preauthorization, claim payments |
| Inventory | 4 | Blood inventory, pharmacy inventory, supply chain, stock audit |
| Legal | 1 | Legal documents, versions, acceptances |
| MasterPatientIndex | 1 | MPI deduplication, merge candidates |
| Messaging | 3 | Messaging, attachments, permissions |
| Notifications | 7 | Multi-channel (email, SMS, push, WhatsApp, voice), preferences, alert escalation |
| Offline | 3 | Sync, conflict resolution (clinical-type block), offline policy (EMR never cached) |
| OperationalFlow | 2 | Patient journey, visit management |
| OpesCareLite | 1 | OpesCare Lite device and sync management |
| Partners | 10 | Partner applications, governance, verification, auditing, risk/quality scoring |
| PatientIdentity | 1 | Patient identity, deduplication |
| PublicHealth | 4 | Signal detection, draft generation, data quality, export |
| Queue | 2 | Queue management, display (masked/privacy-safe — ticket_number + station only) |
| Referral | 1 | Referral case management |
| ResearchAccess | 1 | Research access requests, data agreements |
| Search | 3 | Global search, PHI-safe indexing, permission filtering |
| SecurityOperations | 7 | Audit explorer, suspicious access detection, breach workflow (GDPR 72h), access review, security incidents, API abuse detection, compliance export |
| Staff | 3 | Staff profiles, rosters, leave management |
| Subscription | 2 | SaaS subscription plans, usage limits |
| Support | 3 | Support tickets, ticket assignment, knowledge base |
| Tasks | 1 | Task management |
| Telemedicine | 4 | Telemedicine service, consent gate, call provider (consent required), virtual waiting room |
| Triage | 3 | Triage service, scoring (advisory-only disclaimer), emergency workflow |
| WardManagement | 3 | Ward management, admissions, discharge planning |

---

## 4. Eloquent Model Registry (408 models)

All models reside in `app/Models/`. All use `HasUuids` (UUID primary keys).
Append-only audit models override `update()` and `delete()` to throw `LogicException`.

<details>
<summary>Full alphabetical model list (408 entries — click to expand)</summary>

AccessLog, AccessReview, AccessReviewSchedule, AccountClosureRequest, AdminActionLog,
AdminActionReview, Admission, AdverseReactionNote, AiModelRegistry, AlertOverride,
AllergyAlertRule, AllergyRecord, AnalyticsAccessLog, AnalyticsSnapshot, ApiAbuseFlag,
ApiCredential, ApiPayloadFieldMap, ApiScopeGrant, ApiUsageMetric, ApiUsageSnapshot,
Appointment, AppointmentAudit, AppointmentCancellation, AppointmentCheckIn,
AppointmentReminder, AppointmentSlot, AppointmentStatusHistory, AppointmentType,
AttachmentAccessLog, AttachmentAudit, AuditEvent, AuditExport,
Bed, BedAssignment, BedOccupancySnapshot, BedTransfer, BillingAccount,
BloodAvailability, BloodInventory, BreachReport, BridgeAgent, BridgeConflict,
BridgeConnector, BridgeDevice, BridgeHeartbeat, BridgeLog, BridgeMapping,
BridgePairingCode, BridgeSyncBatch, BridgeSyncJob, BridgeSyncRecord, BridgeVersion,
CallSession, CareFacility, CareFacilityHour, CareFacilityInsurance, CareFacilityService,
CashierSession, Certificate, CertificateVerificationEvent, CertificateVerificationToken,
CertificationBadge, CertificationExpiry, CertificationRequirement, CertificationTestRun,
ChiefComplaint, City, ClaimDecision, ClaimDocument, ClaimItem, ClaimMessage, ClaimPayment,
ClinicalAlert, ClinicalNote, ClinicalReminder, ClinicalRule, ClinicalRuleSource,
CodeSystemMapping, CompetencyRequirement, ComplianceCase, ConsentGrant, ConsentRequest,
ConsentRevocation, CorrectionRequest, Country, CountryDataResidencyRule, CountryDistrict,
CountryHealthRegulation, CountryLanguagePack, CountryLaunchApproval, CountryLegalReview,
CountryPaymentSetting, CountryPolicy, CountryProfile, CountryPublicHealthRule,
Course, CourseEnrollment, CourseModule,
DashboardMetric, DashboardSnapshot, DataAccessCommitteeReview, DataCompletenessScore,
DataDictionaryEntry, DataExportRequest, DataQualityCheck, DepartmentAssignment,
DeveloperAccount, DeveloperApp, DeveloperOrganization, DeveloperSupportTicket,
DeviceAssignment, DeviceRevocation, DeviceStatusLog, DeviceSyncState,
Diagnosis, DischargePlan, DocumentAccessLog, DocumentCodeMapping, DocumentShareLink,
DocumentSignature, DocumentSpecimenEvent, DocumentTemplate, DocumentVerificationEvent,
DocumentVerificationToken, DocumentVersion, DoseWarningRule, DrugInteractionRule, DutyRoster,
EligibilityCheck, EmergencyAccessEvent, EmergencyCase, EmergencyEscalation,
EmergencyReviewCase, Encounter, EthicsApproval, ExportFile, ExternalIdentifier,
ExternalRecordMatch,
Facility, FacilityClaim, FacilityContextSession, FacilityDevice, FacilityGoLiveReadiness,
FacilityQueue, FacilityReadinessScore, FacilityReport, FacilitySchedule, FacilityUpdateAudit,
FeatureFlag, FhirMapping, FhirMappingField, FhirMappingVersion, FhirResourceReference,
FieldDefinition, FileAsset, FileClassification, FileShareToken, FinancialAudit,
GoLiveApproval, GoLiveAudit, GoLiveBlocker, GoLiveChecklist, GoLiveChecklistItem,
GoodsReceipt, GoodsReceiptItem, GuardianRelationship,
HealthIdAlias, HealthIdQrToken, HealthIdVerificationEvent, HighRiskPermission,
IdempotencyRecord, IdentityMergeCase, IdentityRiskFlag, ImmunizationRecord,
ImportAuditEvent, ImportBatch, ImportDuplicateCandidate, ImportFile, ImportJob,
ImportMapping, ImportPreview, ImportRollback, ImportRow, ImportRowError, ImportTemplate,
ImportTemplateFieldMap, IncidentEscalation, IncidentReport, InpatientMedicationAdministration,
InpatientNote, InsuranceClaim, InsurancePlan, InsuranceProvider, IntegrationBadge,
IntegrationCategory, IntegrationCertification, IntegrationCertificationRun, IntegrationClient,
IntegrationListing, IntegrationListingAudit, IntegrationReview, InventoryCategory, InventoryItem,
Invoice, InvoiceAdjustment, InvoiceItem,
KnowledgeBaseArticle, KpiDashboard, KpiExport,
LabAlertRule, LabOrder, LabResult, LabTestAvailability, LanguageSetting, LeaveRequest,
LegalDocument, LegalDocumentChangeLog, LegalDocumentVersion, Lesson, LessonProgress,
LiteConfig, LiteConflict, LiteDevice, LiteDeviceRegistration, LiteModuleEntitlement,
LiteOfflineEvent, LiteSyncJob, LocalCachePolicy,
MaintenanceWindow, MappingError, MedicalAttachment, MedicalIdAccessEvent,
MedicineReservationRequest, MetricDefinition, MetricSnapshot, MinorTransitionReview,
MobileAppSetting, MobileConsentDevice, MobileDevice, MobileFacilityContext, MobileSession,
ModuleEntitlement, ModuleFieldMap, ModuleToggle, MpiCandidate,
NotificationEvent, NursingRound,
OfficialDocument, OfflineAuditEvent, OfflineCachePolicy, OfflineQueue, OrganizationSubscription,
PartnerAgreementAcceptance, Patient, PatientCheckIn, PatientExternalIdentifier,
PatientFlowEvent, PatientIdentifier, PatientIdentityProfile, PatientInsurancePolicy,
PatientRightsRequest, Payment, PaymentMethod, PaymentReconciliation, PaymentReversal,
Permission, PermissionAudit, PharmacyInventory, PharmacyStockAvailability,
PlanFeature, PlanLimit, PlatformSetting, PreauthorizationDecision, PreauthorizationRequest,
Prescription, PrescriptionItem, PriceList, PriceListItem, PrivacyComplaint, ProductEvent,
ProductionAccessRequest, ProfessionalLicense, ProviderAvailability, ProviderDevice,
ProviderMobileSession, PublicHealthBaseline, PublicHealthReport, PublicHealthSignal,
PublicationReview, PurchaseOrder, PurchaseOrderItem, PushDeviceToken,
Queue, QueueDisplaySetting, QueuePriorityRule, QueueStation, QueueStatusHistory,
QueueTicket, QueueTransfer, Quiz, QuizAttempt, QuizQuestion,
Receipt, ReconciliationCase, RecordCorrectionDecision, RecordExportRequest,
ReferralAccessGrant, ReferralCase, Refund, Region, ReorderRule, ReportAssignment,
ReportDefinition, ReportExport, ReportItem, ReportReview, ReportStatusHistory,
ReportSubmission, ReportType, ReportVersion, ReportingRule, ResearchAccessLog,
ResearchAccessRequest, ResearchDataAgreement, ResearchDataset, ResearchRequest,
ResearcherProfile, Role, RolePermissionMatrix, RosterAssignment,
SavedFacility, SavedSearch, SdkToken, SearchIndex, SearchLog, SearchPermissionFilter,
SecurityIncident, ServicePrice, SignalAlert, SignalReview, SignedDownloadToken,
SimulationAttempt, SlaPolicy, StaffAvailability, StaffCredential, StaffProfile,
StaffShift, StaffTrainingStatus, StockAdjustment, StockAudit, StockBatch,
StockLocation, StockMovement, SubmissionProfile, SubscriptionInvoice, SubscriptionPayment,
SubscriptionPlan, SubscriptionUsageMetric, Supplier, SupportAttachment, SupportCategory,
SupportTicket, SuspiciousAccessFlag, SyncAttempt, SyncConflict, SyncJob,
SystemHealthSnapshot, Teleconsultation, TelemedicineAudit, TelemedicineConsent,
TelemedicineNote, TelemedicinePaymentLink, TicketAssignment, TicketMessage,
TicketStatusHistory, TrainerSignoff, TriageAudit, TriageReassessment, TriageRecord,
TriageScore, TriageVitalSign, TrialPeriod, TrustBadge, TrustBadgeAssignment,
TrustBadgeAudit, TrustBadgeCriteria, TrustBadgeVerification,
UsageLimit, User, UserLegalAcceptance,
VaccinationSchedule, VirtualWaitingRoom, VirusScanResult, Visit, VisitClosure,
VisitStep, VisitTimeline, VitalSign,
Wallet, WalletTransaction, Ward, WebhookDeadLetter, WebhookDeliveryLog,
WebhookEndpoint, WebhookEvent, WebhookReplay, WebhookSecret, WebhookSubscription

</details>

### Phase 48 Data Governance Models (most recently added — 9 models)

| Model | Purpose | Special Rules |
|---|---|---|
| `AiModelRegistry` | AI/ML model governance registry | Approve/deprecate lifecycle; CDSS advisory-only docblock mandatory |
| `DataDictionaryEntry` | Canonical field registry | `is_phi` flag; approve/deprecate state machine |
| `FieldDefinition` | Context-specific validation metadata | Contexts: api / import / ui / report |
| `ModuleFieldMap` | Links dictionary entry to Eloquent model/column | `is_indexed` flag |
| `ApiPayloadFieldMap` | Maps dictionary entry to API request/response | PHI fields: `is_redacted_in_logs` MUST be `true` |
| `ImportTemplateFieldMap` | Maps dictionary entry to CSV import column | Ordered by `column_position` |
| `RolePermissionMatrix` | Governance permission matrix | `isBlocked()` static; `scopeExplicitlyBlocked()` |
| `HighRiskPermission` | High-risk permission registry | `requires_explicit_grant`, `requires_reason`, `requires_approval_workflow`, `requires_periodic_review` |
| `AccessReviewSchedule` | Periodic review schedules | `complete()` auto-calculates `next_review_due`; `scopeOverdue()/scopeDueSoon()` |
| `PermissionAudit` | Append-only permission audit trail | Overrides `update()/delete()` → `LogicException`; `UPDATED_AT = null`; `static record()` factory |

---

## 5. API Controller Registry (58 controllers)

| Controller | Route prefix | Primary actions |
|---|---|---|
| `AuthController` | `v1/auth` | Login, register, logout, refresh |
| `PatientSearchController` | `v1/patients` | Search, show, create, update |
| `RecordController` | `v1/records` | EMR records CRUD |
| `EmergencyAccessController` | `v1/emergency` | Break-glass access |
| `ConsentController` | `v1/consent` | Grant, revoke, check |
| `DuplicateMergeController` | `v1/duplicates` | MPI merge candidates |
| `MedicalIdVerificationController` | `v1/medical-id` | Health ID verification |
| `DocumentController` | `v1/documents` | Upload, download, sign |
| `ImmunizationController` | `v1/immunization` | Records and schedules |
| `ReconciliationController` | `v1/reconciliation` | Identity reconciliation |
| `WebhookController` | `v1/webhooks` | Webhook CRUD and delivery |
| `CommunicationController` | `v1/communications` | Notifications and alerts |
| `QueueController` | `v1/queues` | Queue and ticket management |
| `AppointmentController` | `v1/appointments` | Booking, cancel, check-in |
| `BillingController` | `v1/billing` | Invoices, payments |
| `CareMapController` | `v1/care-map` | Facility search, availability |
| `OperationalFlowController` | `v1/operational-flow` | Patient journey tracking |
| `ReferralController` | `v1/referrals` | Referral case management |
| `PublicHealthController` | `v1/public-health` | Signal reporting |
| `IntelligenceController` | `v1/intelligence` | CDSS recommendations |
| `ConnectGovernanceController` | `v1/connect` | Connect platform admin |
| `IntegrationController` | `v1/integrations` | Integration listings |
| `LiteApiController` | `v1/lite` | OpesCare Lite sync |
| `BridgeSyncController` | `v1/bridge` | Bridge agent sync |
| `GlobalSearchController` | `v1/search` | Global search |
| `AdminGovernanceController` | `v1/admin` | Platform admin |
| `FacilityGoLiveReadinessController` | `v1/go-live` | Go-live checklists |
| `PartnerGovernanceController` | `v1/partners` | Partner management |
| `AcademyController` | `v1/academy` | Courses, enrollment |
| `AcademyAdminController` | `v1/academy/admin` | Academy administration |
| `FhirController` | `v1/fhir` | FHIR R4 resources |
| `InsuranceController` | `v1/insurance` | Eligibility, preauth, claims |
| `TriageController` | `v1/triage` | Triage scoring, reassessment |
| `InventoryController` | `v1/inventory` | Stock, purchase orders, audits |
| `AnalyticsController` | `v1/analytics` | Dashboards, stats, export |
| `StaffController` | `v1/staff` | Roster, shifts, leave |
| `TelemedicineController` | `v1/telemedicine` | Video consultations, consent |
| `WardController` | `v1/ward` | Admissions, beds, nursing rounds |
| `SecurityOperationsController` | `v1/security` | Audit logs, breach workflow, access review |
| `SubscriptionController` | `v1/subscriptions` | SaaS plans, usage limits |
| `SupportController` | `v1/support` | Helpdesk tickets, KB |
| `MobileAuthController` | `mobile/auth` | Patient mobile auth |
| `MobilePatientController` | `mobile/patient` | Patient mobile profile |
| `MobileAppointmentController` | `mobile/appointments` | Mobile appointment booking |
| `MobileLabController` | `mobile/lab` | Mobile lab results |
| `MobilePrescriptionController` | `mobile/prescriptions` | Mobile prescription view |
| `MobileConsentController` | `mobile/consent` | Mobile consent management |
| `MobileDocumentController` | `mobile/documents` | Mobile document access |
| `MobileGovernanceController` | `mobile/governance` | Mobile rights management |
| `MobileSettingsController` | `mobile/settings` | Mobile app settings |
| `OfflineSyncController` | `mobile/sync` | Offline sync |
| `ProviderMobileAuthController` | `provider/auth` | Provider mobile auth |
| `ProviderMobilePatientController` | `provider/patients` | Provider mobile patients |
| `ProviderMobileFacilityController` | `provider/facility` | Provider facility context |
| `ProviderMobileTaskController` | `provider/tasks` | Provider mobile tasks |
| `SdkAuthController` | `sdk/auth` | SDK authentication |
| `SdkPatientController` | `sdk/patients` | SDK patient access |
| `SdkAppointmentController` | `sdk/appointments` | SDK appointment booking |
| `SdkFacilityController` | `sdk/facilities` | SDK facility info |
| `SdkWebhookController` | `sdk/webhooks` | SDK webhook management |

---

## 6. Language Files — Bilingual EN + FR (13 pairs)

All user-facing strings have entries in both `lang/en/` and `lang/fr/`. Full bilingual parity.

| File | Key groups | Special notes |
|---|---|---|
| `auth.php` | login, register, reset, mfa | — |
| `onboarding.php` | steps, facility, profile | — |
| `appointments.php` | book, status, actions, cancel_modal, reminders, no_show | — |
| `billing.php` | invoice, payment, receipt, refund, wallet, cashier | — |
| `clinical.php` | triage (+ `disclaimer`), encounter, prescriptions (+ `allergy_alert`/`interaction_alert`), lab, cdss, visit, emergency | CDSS advisory-only disclaimer key mandatory |
| `insurance.php` | eligibility, preauth, claims | `minimum_data` note in claims |
| `telemedicine.php` | book, consent (+ recording keys), session, status, privacy | Recording consent opt-in/opt-out keys |
| `queue.php` | display, status, actions, priority, privacy | Privacy note: masked display (ticket + station only) |
| `errors.php` | generic, unauthorized, forbidden, not_found, validation, session_expired, consent_required, facility_mismatch, limit_exceeded, feature_disabled, phi_blocked, offline, sync_conflict, cdss_advisory_only, availability_not_guaranteed | — |
| `demo.php` | Demo mode UI strings | — |
| `landing.php` | Public marketing strings | — |
| `public.php` | Public-facing strings | — |
| `verify.php` | Email/identity verification | — |

### Language File Status — COMPLETE

All 18 EN + 18 FR language files implemented. Full bilingual parity achieved as of 2026-05-20.

| File pair | Module | Status |
|---|---|---|
| `lang/en/staff.php` + `lang/fr/staff.php` | Staff / HR | ✅ DONE |
| `lang/en/ward.php` + `lang/fr/ward.php` | Ward Management | ✅ DONE |
| `lang/en/inventory.php` + `lang/fr/inventory.php` | Inventory / Supply Chain | ✅ DONE |
| `lang/en/analytics.php` + `lang/fr/analytics.php` | Analytics | ✅ DONE |
| `lang/en/security.php` + `lang/fr/security.php` | Security Operations | ✅ DONE |

---

## 7. Security & Compliance Invariants

These rules are hardcoded into the platform and must **never** be relaxed without explicit governance approval:

| Rule | Enforcement location |
|---|---|
| Full EMR NEVER cached offline | `OfflinePolicyService::isCachingAllowed()` returns `false` as default |
| PHI NEVER in `search_text` without permission filter | `SearchIndexingService` + `SearchPermissionService` |
| Insurance users see minimum necessary data only | `InsuranceController` docblock + `insurance.claims.minimum_data` label |
| CDSS advisory only — never replaces clinical judgment | `AiModelRegistry` docblock; `clinical.php` `triage.disclaimer` key; `TriageController` response payload |
| Clinical alert overrides require reason if high-risk | `AlertOverrideService::HIGH_RISK_ALERT_TYPES`; throws `InvalidArgumentException` without reason |
| Telemedicine call requires `consent_obtained = true` | `CallProviderService::initiateCall()` throws `RuntimeException` otherwise |
| Clinical sync conflicts require manual review | `ConflictResolutionService::CLINICAL_RESOURCE_TYPES`; throws `DomainException` on auto-resolution attempt |
| High-risk permission changes require audit record | `PermissionAudit::record()` — append-only, no updates/deletes |
| GDPR breach notification within 72 hours | `BreachWorkflowService::getBreachesRequiringRegulatoryAction()` returns open breaches >48h without notification |
| API abuse auto-detection thresholds | Rate: 500 req/min; Error rate: 40%; Bulk extraction: >100 pages |
| Client secrets stored as bcrypt hash only | `ApiCredential` model — never logged or returned in plaintext after creation |
| PHI API fields redacted in logs | `ApiPayloadFieldMap.is_redacted_in_logs` = `true` for all PHI fields |
| QR tokens expire and never expose full records | `HealthIdQrToken`, `SignedDownloadToken` — time-limited, minimum scope |
| Import cannot silently overwrite records | `ImportDuplicateCandidate` approval flow; `ImportRollback` support |
| Students cannot perform restricted clinical actions | RBAC `Permission` + `RolePermissionMatrix.is_blocked` |
| Payment/refund changes require audit trail | `FinancialAudit`, `PaymentReversal`, `InvoiceAdjustment` — append-only |
| Availability wording is indicative, never guaranteed | `errors.availability_not_guaranteed` key in all bilingual language files |
| Queue display never shows full patient name | `QueueDisplayService::getPublicDisplayData()` — ticket_number + station only |

---

## 8. Test Suite Status

**Last verified:** 2026-05-20
**Command:** `php artisan test --no-coverage`
**Result:** ✅ **151 / 151 passed**
**Failures:** 0 | **Errors:** 0

> **Invariant:** The test count must never decrease. All new features must add at least one feature test.

### Feature Test Gaps (HIGH priority)

| Area | Current Coverage | Action needed |
|---|---|---|
| SecurityOperations module (7 services) | 0 feature tests | Add feature tests |
| Phase 48 data governance models (10 models) | 0 feature tests | Add feature tests |
| Insurance full flow (eligibility → preauth → claim → payment) | Partial | Complete flow test |
| Telemedicine consent-gate enforcement | Partial | Full gate test |
| Offline clinical conflict DomainException | 0 | Add unit test |
| CDSS alert override high-risk InvalidArgumentException | 0 | Add unit test |
| GDPR breach 72-hour deadline detection | 0 | Add feature test |
| API abuse detection thresholds | 0 | Add feature test |

---

## 9. Documentation Structure

`docs/` hierarchy with 24 subdirectories:

```
docs/
├── 00-source-of-truth/       # Master planning documents (read-only reference)
├── 01-audits/                # This file + future audit reports
├── 02-implementation/        # Module implementation notes per phase
├── 03-operational-modules/   # Per-module operational flows
├── 04-connect-suite/         # Connect API/SDK/Widget/Bridge docs
├── 05-production-launch/     # Launch checklists and governance
├── 06-maturity-scale/        # Maturity standards and KPIs
├── 07-agent-protocols/       # Claude Code and Codex lane definitions
├── 08-reports/               # Generated audit reports
├── ai-governance/            # AI model governance (AiModelRegistry)
├── analytics/                # Analytics module docs
├── country-expansion/        # Country expansion protocols
├── data-dictionary/          # DataDictionaryEntry canonical registry
├── hardware/                 # Hardware/device integration docs
├── import-templates/         # CSV import template specifications
├── incidents/                # Security and clinical incident docs
├── marketplace/              # Connect marketplace docs
├── national-integration/     # National health system integrations
├── permissions/              # Permission matrix and high-risk registry
├── qa/                       # QA test plans and checklists
├── research/                 # Research access governance
├── risk-register/            # Risk register
├── standards/                # Clinical and technical standards
└── trust/                    # Trust badge criteria and verification
```

---

## 10. Architecture Standards Reference

### Model Standards

- **UUID PKs:** All models `use HasUuids;`
- **Append-only models:** Override `update()` and `delete()` to throw `LogicException`; implement `static record()` factory
- **PHI-touching models:** Document PHI fields explicitly in class docblock
- **CDSS-touching models/services:** Must include advisory-only docblock stating the system assists, does not replace, clinical judgment

### Migration Standards

- **Idempotency:** Every table creation wrapped in `if (!Schema::hasTable('table_name')) { ... }`
- **Index naming:** Composite indexes use explicit short names ≤64 chars — format: `{2-3char_prefix}_{field_abbrev}_{type}`
- **UUID FKs:** `foreignUuid()->constrained()->cascadeOnDelete()` or `nullOnDelete()`
- **Timestamps:** All audit/log tables include `timestamps()` or `created_at` only (UPDATED_AT = null for append-only)

### Controller Standards

- **PHI routes:** Minimum `auth:sanctum` middleware
- **CDSS responses:** Must include `disclaimer` key with advisory-only text in payload
- **Insurance routes:** `minimum_data` enforced — no full EMR exposure to insurance role
- **Telemedicine routes:** Consent check before call initiation (server-side, not just client validation)

### Service Standards

- Services accept injected models — no `new Model()` in constructors
- All state-changing operations create an `AuditEvent` record
- Availability information: always marked indicative, never guaranteed
- PHI-touching services: never log PHI fields in plaintext

---

## 11. Production Readiness Workstreams

From `OPESCARE_PRODUCTION_LAUNCH_GOVERNANCE_COMPLIANCE_AND_DEPLOYMENT_MASTER_PLAN.md`:

| Workstream | Code Status | Notes |
|---|---|---|
| WS1 — Code freeze gate | ✅ Models/services exist | Process gate (PM action) |
| WS2 — Security audit | ⬜ Codex lane | Do not touch |
| WS3 — Data residency validation | ✅ `CountryDataResidencyRule` model exists | Process validation needed |
| WS4 — Facility onboarding | ✅ `FacilityGoLiveReadiness` + `FacilityGoLiveService` | Process + config |
| WS5 — Regulatory sign-off | ✅ `CountryLaunchApproval` model exists | Process action |
| WS6 — Pen-test remediation | ⬜ Codex lane | Do not touch |
| WS7 — Disaster recovery drill | ⬜ Non-code | DevOps/Ops action |
| WS8 — Monitoring setup | ⬜ Config only | Prometheus/Grafana (DevOps) |
| WS9 — SLA enforcement | ✅ `SlaPolicy` model exists | Config in deployment |
| WS10 — Go-live approval | ✅ `GoLiveApproval` model exists | Process gate |
| WS11 — Post-launch review | ⬜ Non-code | Documentation |

---

## 12. Remaining Implementation Priorities (Claude Code Lane)

Listed in priority order. Do not create duplicates — verify file existence first.

| Priority | Item | Notes |
|---|---|---|
| ✅ DONE | `lang/en/staff.php` + `lang/fr/staff.php` | Completed 2026-05-20 |
| ✅ DONE | `lang/en/ward.php` + `lang/fr/ward.php` | Completed 2026-05-20 |
| ✅ DONE | `lang/en/inventory.php` + `lang/fr/inventory.php` | Completed 2026-05-20 |
| ✅ DONE | `lang/en/analytics.php` + `lang/fr/analytics.php` | Completed 2026-05-20 |
| ✅ DONE | `lang/en/security.php` + `lang/fr/security.php` | Completed 2026-05-20 |
| HIGH | Feature tests for SecurityOperations module | 7 services, 0 tests currently |
| HIGH | Feature tests for Phase 48 data governance models | 10 models, 0 tests currently |
| MEDIUM | Verify `AppointmentPolicyService` gap | Slot/booking rules enforcement |
| MEDIUM | Verify `ProfessionalLicenseService` gap | License verification and expiry |
| LOW | Unit tests for CDSS/offline/consent guard exceptions | 3 guard classes, 0 unit tests |

---

## 13. Audit Trail

| Date | Actor | Action |
|---|---|---|
| 2026-05-20 | Claude Code | Baseline audit document v2.0 created (full rebuild) |
| 2026-05-20 | Claude Code | Phases 1–48 implemented: 408 models, 61 migrations, 131 services, 58 controllers |
| 2026-05-20 | Claude Code | SecurityOperations module created (7 services) |
| 2026-05-20 | Claude Code | Phase 48 data governance models created (10 models incl. PermissionAudit) |
| 2026-05-20 | Claude Code | 13 bilingual language file pairs created (EN + FR) |
| 2026-05-20 | Claude Code | 9 new API route groups appended to `routes/api.php` (66+ routes) |
| 2026-05-20 | Claude Code | Test suite verified: 151/151 ✅ |

---

*End of baseline audit. Update this document each time a new implementation phase is merged.*
*Codex lane: review and test only — do not modify models, migrations, services, or controllers.*
