# OpesCare Public Health Reporting Module
## Phase 1 to Phase 4 Complete Technical PRD and Implementation Blueprint

**Project:** OpesCare  
**Parent Company:** Opesware  
**Domain:** opescare.com  
**Build Direction:** Build from scratch  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Specialist Services:** Python/FastAPI later for analytics, anomaly detection, disease signal detection, data quality intelligence, and public health trend analysis  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, database structure, module structure, API design, UI, or reporting assumptions.

---

# 1. Document Purpose

This document defines the complete OpesCare Public Health Reporting Module from Phase 1 to Phase 4.

The module allows OpesCare to prepare, review, approve, submit, correct, and monitor public health reports generated from hospitals, clinics, laboratories, pharmacies, blood banks, and connected health systems.

This document covers:

1. Public health reporting principles
2. What data can be reported
3. What data must not be automatically reported
4. Reporting architecture
5. Phase 1: report draft generation and internal dashboards
6. Phase 2: review, approval, correction, and audit workflow
7. Phase 3: government system integration and exports
8. Phase 4: anomaly detection, outbreak intelligence, and regional health signals
9. Data models
10. Permissions
11. API endpoints
12. User interface requirements
13. Data quality checks
14. Privacy rules
15. Audit requirements
16. Testing requirements
17. Acceptance criteria
18. Final safety review

The goal is to build a reporting system that supports public health without exposing unnecessary patient data.

---

# 2. Core Principle

OpesCare must not automatically send all patient data to government.

OpesCare must prepare and submit only:

- approved reportable data
- minimum necessary data
- legally allowed data
- properly reviewed data where review is required
- aggregate or de-identified data by default
- identifiable data only when law or approved policy requires it

The system must be able to produce government reporting, but it must not become an uncontrolled patient surveillance system.

---

# 3. Clear Product Statement

OpesCare Public Health Reporting helps health facilities and approved authorities understand public health trends, notifiable diseases, medicine shortages, blood shortages, vaccination activity, maternal and child health indicators, facility activity, and emergency signals through controlled, auditable, privacy-aware reporting.

---

# 4. What OpesCare Can Report

OpesCare may generate public health reports from these data categories:

1. Notifiable disease events
2. Facility aggregate activity
3. Laboratory surveillance
4. Pharmacy stock and essential medicine availability
5. Blood availability and blood shortage
6. Vaccination and immunization
7. Maternal and child health
8. Chronic disease indicators
9. Emergency/outbreak signals
10. Health system capacity
11. Referral indicators
12. Mortality indicators
13. Program-specific reports
14. Data quality and reporting completeness

---

# 5. What OpesCare Must Not Automatically Report

OpesCare must not automatically send:

- full patient medical history
- full patient timeline
- unrestricted patient identifiers
- full consultation notes
- full prescriptions with patient identity unless required
- full lab results with patient identity unless required
- sensitive diagnoses without approved legal basis
- HIV/STI/mental health or other sensitive condition data in identifiable form unless law/policy requires it
- patient location history
- patient access logs
- private consent logs, except audit summaries where legally required
- full insurance records
- unreviewed suspected outbreak signals as confirmed events

---

# 6. Reporting Data Sensitivity Classes

All reportable data must be classified.

## 6.1 Aggregate Data

Data counted at facility, district, region, age group, disease category, or reporting period level.

Example:

```text
Malaria tests positive: 120
Facility: Example District Hospital
Reporting period: May 2026
```

Default public health reporting should prefer aggregate data.

## 6.2 De-identified Data

Data where direct identifiers are removed.

Example:

```text
Age group: 5-14
Sex: Female
District: Douala 5
Disease: Suspected measles
```

Use only where aggregate is insufficient.

## 6.3 Pseudonymized Data

Data uses a coded reference instead of direct patient identity.

Example:

```text
case_reference: CASE-2026-000034
```

Mapping to patient identity must be protected and accessible only to authorized users.

## 6.4 Identifiable Data

Data includes direct patient identity.

Example:

```text
Patient name
Health ID
Phone number
Exact address
National ID
```

Identifiable reporting is allowed only when legally required or explicitly approved by public health policy.

---

# 7. Public Health Report Types

## 7.1 Notifiable Disease Report

Purpose: Report suspected or confirmed diseases that must be communicated to public health authorities.

Possible triggers:

- doctor diagnosis
- lab confirmed result
- emergency diagnosis
- admission diagnosis
- death record
- outbreak signal
- public health officer manual report

Core fields:

```text
report_id
disease_or_condition
reporting_status: suspected / probable / confirmed
case_count
reporting_facility
district
region
date_detected
reporting_period
age_group
sex
lab_confirmation_status
source_module
patient_identity_required: yes/no
patient_reference if legally required
submitted_by
reviewed_by
submitted_at
```

## 7.2 Aggregate Facility Activity Report

Purpose: Provide facility-level activity statistics.

Fields:

```text
facility
district
region
reporting_period
outpatient_visits
emergency_visits
admissions
discharges
referrals_out
referrals_in
deliveries
c_sections
deaths
lab_tests_performed
prescriptions_issued
pharmacy_dispenses
```

## 7.3 Laboratory Surveillance Report

Purpose: Track disease testing and positivity.

Fields:

```text
facility
lab_department
district
region
reporting_period
test_name
test_code
specimen_type
positive_count
negative_count
inconclusive_count
rejected_sample_count
critical_result_count
average_turnaround_time
```

## 7.4 Pharmacy Stock-Out Report

Purpose: Track essential medicine availability and shortages.

Fields:

```text
facility
pharmacy_type
district
region
medicine_name
generic_name
form
strength
stock_status
available_quantity_range
low_stock_indicator
stock_out_indicator
expired_batch_indicator
recalled_batch_indicator
quarantined_batch_indicator
last_stock_update
```

## 7.5 Blood Availability and Shortage Report

Purpose: Track blood availability and urgent blood needs.

Fields:

```text
facility_or_blood_bank
district
region
blood_group
component
available_units
low_units_indicator
urgent_need_requests
fulfilled_requests
unfulfilled_requests
expired_units
quarantined_units
last_stock_update
```

Patient identity must not be included in public blood need reports.

## 7.6 Vaccination and Immunization Report

Purpose: Track vaccine administration and coverage indicators.

Fields:

```text
facility
district
region
reporting_period
vaccine_type
dose_number
age_group
sex where allowed
doses_administered
missed_appointments
adverse_event_indicator
```

## 7.7 Maternal and Child Health Report

Purpose: Track antenatal, delivery, postnatal, and child health indicators.

Fields:

```text
facility
district
region
reporting_period
antenatal_visits
first_anc_visits
deliveries
facility_deliveries
c_sections
maternal_complications
maternal_deaths
neonatal_deaths
low_birth_weight_count
postnatal_visits
```

## 7.8 Chronic Disease Indicator Report

Purpose: Track chronic disease care patterns.

Fields:

```text
facility
district
region
reporting_period
condition
new_cases
follow_up_visits
missed_follow_ups
complication_count
medication_stock_out_indicator
```

## 7.9 Emergency and Outbreak Signal Report

Purpose: Identify unusual increases that may require investigation.

Fields:

```text
signal_id
signal_type
suspected_condition
symptom_cluster
facility
district
region
count
time_window
baseline_count
increase_percentage
confidence_level
status
reviewed_by
review_outcome
```

Status must clearly distinguish:

```text
signal
suspected
under_review
confirmed
dismissed
```

## 7.10 Health System Capacity Report

Purpose: Track operational readiness and pressure.

Fields:

```text
facility
district
region
reporting_period
available_beds
occupied_beds
icu_beds_available
oxygen_availability
blood_stock_status
essential_medicine_stock_status
lab_capacity_status
emergency_department_load
ambulance_availability where applicable
```

---

# 8. Reporting Statuses

All reports must use controlled statuses.

```text
draft
pending_review
requires_correction
approved_for_submission
submitted
accepted
rejected
corrected
resubmitted
cancelled
archived
```

## Status Meaning

- **draft:** Report was generated but is not yet reviewed.
- **pending_review:** Report is ready for public health officer review.
- **requires_correction:** Reviewer found missing or incorrect information.
- **approved_for_submission:** Report is approved internally and ready to submit.
- **submitted:** Report was sent to an external authority or exported.
- **accepted:** External system or authority accepted the report.
- **rejected:** External system or reviewer rejected the report.
- **corrected:** Report was corrected after rejection or review.
- **resubmitted:** Corrected report was submitted again.
- **cancelled:** Report was cancelled because it was invalid, duplicate, or no longer required.
- **archived:** Report is no longer active but retained for history and audit.

---

# 9. Roles and Permissions

## 9.1 Roles

Required roles:

```text
public_health_viewer
public_health_report_preparer
public_health_reviewer
public_health_approver
public_health_submitter
facility_reporting_officer
lab_reporting_officer
pharmacy_reporting_officer
blood_bank_reporting_officer
system_admin
governance_reviewer
```

## 9.2 Permissions

```text
reports.view
reports.generate
reports.edit_draft
reports.review
reports.approve
reports.submit
reports.correct
reports.cancel
reports.export
reports.view_sensitive
reports.view_identifiable
signals.view
signals.review
dashboard.view_public_health
config.manage_reporting_rules
```

## 9.3 Permission Rules

- Only authorized users can view public health reports.
- Identifiable reports require special permission.
- Report submitter must be authorized.
- Report approver should not approve their own correction where separation of duties is required.
- All sensitive report access must be audited.
- Facility users should only see their facility reports unless assigned to regional/national roles.
- Government users must see only data approved for their jurisdiction and role.

---

# 10. Data Sources Inside OpesCare

Public health reports may draw from:

```text
patients
encounters
diagnoses
triage_records
vital_signs
lab_orders
lab_results
prescriptions
dispense_events
pharmacy_inventory
blood_inventory
blood_need_requests
admissions
discharges
death_records
vaccination_records
maternal_child_health_records
referrals
facility_capacity_snapshots
emergency_access_records where appropriate
forms/questionnaires
documents only after structured extraction/review
```

Rules:

- Unverified patient-uploaded documents must not generate official reports automatically.
- Draft notes must not generate official reports unless signed or validated.
- Unvalidated lab results must not generate confirmed lab surveillance reports.
- Released lab results can trigger confirmed report drafts.
- Diagnoses may trigger suspected or probable report drafts.
- Death records must be reviewed before official submission where required.

---

# 11. Phase 1: Report Draft Generation and Internal Dashboards

## 11.1 Phase 1 Goal

Phase 1 builds the internal reporting foundation.

It should generate draft reports and show internal dashboards, but should not automatically submit data to government systems yet.

## 11.2 Phase 1 Scope

Build:

1. Reporting module foundation
2. Report type definitions
3. Report rule configuration
4. Draft generation engine
5. Data quality checks
6. Internal public health dashboard
7. Facility reporting dashboard
8. Notifiable disease draft generation
9. Facility aggregate draft generation
10. Lab surveillance draft generation
11. Pharmacy stock-out draft generation
12. Blood shortage draft generation
13. Audit logs for report generation
14. Bilingual UI labels
15. Clear-language report descriptions

## 11.3 Phase 1 Out of Scope

Do not build yet:

- direct government API submission
- final legal reporting automation
- AI outbreak detection
- automated national submission
- identifiable reporting without policy
- advanced regional predictive analytics

## 11.4 Phase 1 Report Generation Flow

1. System receives or updates facility data.
2. Reporting rule engine checks if data matches report criteria.
3. Draft report is generated.
4. Data quality checks run.
5. Report is marked as draft or pending_review depending on configuration.
6. Dashboard updates.
7. Authorized users can view draft.
8. Audit event is created.

## 11.5 Phase 1 Data Quality Checks

Validate:

```text
facility identifier exists
facility is active
district/region mapping exists
reporting period is valid
report type is configured
required fields are present
counts are not negative
dates are valid
duplicate draft does not already exist
lab result validation status is acceptable
inventory update is recent enough
blood stock is not expired/quarantined
```

## 11.6 Phase 1 Public Health Dashboard

Dashboard cards:

```text
report drafts
reports pending review
notifiable disease drafts
lab surveillance drafts
pharmacy stock-out drafts
blood shortage drafts
facility reporting completeness
data quality score
stale inventory reports
unmapped facility locations
```

Charts:

```text
draft reports by type
reports by facility
reports by district
lab positivity trend
medicine stock-out trend
blood shortage trend
facility reporting completeness
```

## 11.7 Phase 1 Facility Dashboard

Facility users should see:

```text
reports generated from their facility
missing data warnings
pending draft reports
stock-out reports
lab surveillance summaries
blood availability reports
data quality issues
reporting completeness score
```

## 11.8 Phase 1 Required Data Models

### public_health_report_types

```text
id
uuid
code
name
description
sensitivity_level
default_review_required
is_active
created_at
updated_at
```

### public_health_reporting_rules

```text
id
uuid
report_type_id
trigger_source
trigger_condition
aggregation_level
requires_review
allows_auto_submit
requires_patient_identity
country_policy_id
effective_from
effective_to
status
created_at
updated_at
```

### public_health_reports

```text
id
uuid
report_type_id
facility_id
district_id
region_id
reporting_period_start
reporting_period_end
status
sensitivity_level
data_classification
generated_by_system
generated_from_source
data_quality_score
requires_review
requires_correction
payload_json
created_by
updated_by
created_at
updated_at
```

### public_health_report_items

```text
id
report_id
indicator_code
indicator_name
value
age_group
sex
disease_code
program_code
metadata_json
created_at
updated_at
```

### public_health_data_quality_checks

```text
id
report_id
check_code
check_name
status
severity
message
field_reference
created_at
updated_at
```

### public_health_dashboard_snapshots

```text
id
uuid
scope_type
scope_id
period_start
period_end
metrics_json
created_at
updated_at
```

## 11.9 Phase 1 API Endpoints

```text
GET  /api/v1/public-health/report-types
GET  /api/v1/public-health/reports
GET  /api/v1/public-health/reports/{id}
POST /api/v1/public-health/reports/generate-drafts
GET  /api/v1/public-health/reports/{id}/quality-checks
GET  /api/v1/public-health/dashboard
GET  /api/v1/public-health/facility-dashboard
```

## 11.10 Phase 1 Audit Events

```text
public_health_report_draft_generated
public_health_report_viewed
public_health_report_quality_check_completed
public_health_dashboard_viewed
public_health_rule_created
public_health_rule_updated
```

## 11.11 Phase 1 Tests

Required tests:

1. Report draft is generated for configured notifiable disease.
2. Report draft is not generated for inactive rule.
3. Facility aggregate report counts are correct.
4. Lab surveillance draft uses only validated/released results.
5. Pharmacy stock-out draft excludes expired/recalled/quarantined stock.
6. Blood shortage draft excludes expired/quarantined/unsafe blood.
7. Duplicate draft is not created for same facility/reporting period/rule.
8. Data quality checks run.
9. Unauthorized users cannot view reports.
10. Identifiable fields are not included unless rule allows them.
11. Audit log is created.

## 11.12 Phase 1 Acceptance Criteria

Phase 1 is acceptable when:

1. Public Health Reporting module exists.
2. Report types can be configured.
3. Reporting rules can generate drafts.
4. Notifiable disease drafts can be generated.
5. Facility aggregate drafts can be generated.
6. Lab surveillance drafts can be generated.
7. Pharmacy stock-out drafts can be generated.
8. Blood shortage drafts can be generated.
9. Dashboards show draft reports and data quality.
10. No reports are submitted externally yet.
11. Full patient records are not included.
12. Sensitive/identifiable data is blocked unless configured.
13. All draft generation is audited.

---

# 12. Phase 2: Review, Approval, Correction, and Audit Workflow

## 12.1 Phase 2 Goal

Phase 2 adds governance and workflow controls so reports can be reviewed, corrected, approved, rejected, cancelled, and audited before submission.

## 12.2 Phase 2 Scope

Build:

1. Report review queue
2. Reviewer assignment
3. Report approval workflow
4. Correction workflow
5. Rejection workflow
6. Cancellation workflow
7. Commenting and notes
8. Status history
9. Review audit logs
10. Sensitive data access control
11. Two-person approval for high-risk reports
12. Bilingual review UI
13. Report versioning

## 12.3 Review Workflow

1. Report draft enters pending_review.
2. Public health reviewer opens report.
3. System logs access.
4. Reviewer checks data quality warnings.
5. Reviewer approves, requests correction, rejects, or cancels.
6. Status changes.
7. Reviewer comment is saved.
8. Audit event is logged.

## 12.4 Correction Workflow

1. Reviewer marks report requires_correction.
2. Correction reason is required.
3. Facility reporting officer or authorized user updates draft.
4. System reruns data quality checks.
5. Corrected report returns to pending_review.
6. Report version number increments.
7. Audit event is logged.

## 12.5 Approval Workflow

1. Reviewer opens pending report.
2. Data quality checks must pass or override reason must be entered.
3. Reviewer clicks approve.
4. If high-risk or identifiable, second approver may be required.
5. Report status becomes approved_for_submission.
6. Audit event is logged.

## 12.6 Rejection Workflow

1. Reviewer opens report.
2. Reviewer selects reject.
3. Rejection reason is required.
4. Report status becomes rejected.
5. Report remains stored for audit.
6. Audit event is logged.

## 12.7 Cancellation Workflow

1. Authorized user selects cancel.
2. Reason is required.
3. System checks report has not been accepted externally.
4. If not submitted/accepted, status becomes cancelled.
5. If already submitted, correction or cancellation notice must be used instead.
6. Audit event is logged.

## 12.8 Report Versioning

Each correction creates a new version.

Model:

```text
report_id
version_number
payload_json
changed_by
change_reason
created_at
```

Original versions must be preserved.

## 12.9 Additional Data Models

### public_health_report_reviews

```text
id
uuid
report_id
reviewer_id
action
comment
reviewed_at
created_at
updated_at
```

### public_health_report_status_history

```text
id
report_id
old_status
new_status
changed_by
reason
changed_at
```

### public_health_report_versions

```text
id
report_id
version_number
payload_json
change_reason
created_by
created_at
```

### public_health_report_assignments

```text
id
report_id
assigned_to
assigned_by
assignment_status
assigned_at
completed_at
```

## 12.10 Phase 2 API Endpoints

```text
POST /api/v1/public-health/reports/{id}/submit-for-review
POST /api/v1/public-health/reports/{id}/assign
POST /api/v1/public-health/reports/{id}/approve
POST /api/v1/public-health/reports/{id}/request-correction
POST /api/v1/public-health/reports/{id}/reject
POST /api/v1/public-health/reports/{id}/cancel
POST /api/v1/public-health/reports/{id}/correct
GET  /api/v1/public-health/reports/{id}/versions
GET  /api/v1/public-health/reports/{id}/status-history
GET  /api/v1/public-health/review-queue
```

## 12.11 Phase 2 Audit Events

```text
public_health_report_submitted_for_review
public_health_report_assigned
public_health_report_approved
public_health_report_correction_requested
public_health_report_corrected
public_health_report_rejected
public_health_report_cancelled
public_health_report_version_created
public_health_sensitive_report_viewed
```

## 12.12 Phase 2 UI Requirements

Pages:

```text
Public Health Review Queue
Report Detail
Data Quality Warnings Panel
Correction Request Panel
Approval Confirmation Modal
Rejection Modal
Status History Timeline
Report Version Comparison
Sensitive Data Access Warning
```

UI rules:

- show report status clearly
- show data quality score
- show review notes
- show source facility
- show reporting period
- show sensitive/identifiable data warning
- require reason for correction/rejection/cancellation
- show audit notice before high-risk actions

## 12.13 Phase 2 Tests

Required tests:

1. Only authorized reviewer can review report.
2. Report cannot be approved if required fields missing unless override allowed.
3. Correction creates new version.
4. Rejection requires reason.
5. Cancellation requires reason.
6. Status history is recorded.
7. Sensitive report access is audited.
8. High-risk report requires second approval when configured.
9. Facility user cannot approve national report unless authorized.
10. Rejected reports cannot be submitted without correction/resubmission flow.

## 12.14 Phase 2 Acceptance Criteria

Phase 2 is acceptable when:

1. Review queue exists.
2. Reports can move from draft to pending_review.
3. Reports can be approved.
4. Reports can require correction.
5. Reports can be rejected.
6. Reports can be cancelled safely.
7. Report versions are preserved.
8. Status history is visible.
9. Sensitive report access is audited.
10. High-risk reports can require two-person approval.
11. No external submission occurs without approval.
12. All review actions are audited.

---

# 13. Phase 3: Government Integration and Exports

## 13.1 Phase 3 Goal

Phase 3 allows approved reports to be submitted to government or approved public health systems through configurable integration methods.

## 13.2 Phase 3 Scope

Build:

1. Government/public health endpoint configuration
2. Submission profiles
3. DHIS2-style API integration placeholder
4. FHIR reporting endpoint placeholder where applicable
5. CSV export
6. Excel export
7. PDF export
8. Secure file export
9. Submission response tracking
10. Retry and correction workflow
11. Submission audit
12. Integration status dashboard

## 13.3 Supported Submission Methods

```text
api_submission
dhis2_api
fhir_endpoint
csv_export
excel_export
pdf_export
secure_file_transfer
manual_submission_record
bridge_agent_to_government_system
```

## 13.4 Submission Workflow

1. Report status is approved_for_submission.
2. Authorized submitter selects submission method.
3. System validates report against submission profile.
4. Payload is transformed to required format.
5. Submission is sent or exported.
6. External response is recorded.
7. If accepted, report status becomes accepted.
8. If rejected, report status becomes rejected or requires_correction.
9. Correction/resubmission workflow begins if needed.
10. Audit event is logged.

## 13.5 Submission Profiles

Submission profiles define where and how reports go.

Fields:

```text
id
uuid
name
country_policy_id
report_type_id
destination_type
endpoint_url
auth_method
payload_format
mapping_rules_json
requires_manual_review
is_active
created_at
updated_at
```

## 13.6 Payload Formats

Supported payload types:

```text
json
fhir_json
dhis2_json
csv
xlsx
pdf
xml later if required
```

## 13.7 Submission Response Tracking

Track:

```text
submission_id
report_id
destination
method
payload_hash
submitted_by
submitted_at
status
external_reference
response_code
response_body_safe
error_message
retry_count
accepted_at
rejected_at
```

Do not store sensitive external responses in ordinary logs.

## 13.8 Additional Data Models

### public_health_submission_profiles

```text
id
uuid
name
report_type_id
destination_type
endpoint_url
auth_method
payload_format
mapping_rules_json
active
created_at
updated_at
```

### public_health_report_submissions

```text
id
uuid
report_id
submission_profile_id
submission_method
payload_hash
status
external_reference
response_code
safe_response_summary
submitted_by
submitted_at
accepted_at
rejected_at
retry_count
created_at
updated_at
```

### public_health_export_files

```text
id
uuid
report_id
file_type
file_path
file_hash
generated_by
generated_at
download_count
expires_at
created_at
updated_at
```

## 13.9 Phase 3 API Endpoints

```text
GET  /api/v1/public-health/submission-profiles
POST /api/v1/public-health/submission-profiles
PUT  /api/v1/public-health/submission-profiles/{id}
POST /api/v1/public-health/reports/{id}/submit
POST /api/v1/public-health/reports/{id}/resubmit
GET  /api/v1/public-health/reports/{id}/submissions
POST /api/v1/public-health/reports/{id}/export
GET  /api/v1/public-health/exports/{id}/download
GET  /api/v1/public-health/integration-status
```

## 13.10 Phase 3 Security Rules

- Only approved reports can be submitted.
- Only authorized submitters can submit.
- Submission credentials must be securely stored.
- Exported files must expire or be access-controlled.
- Downloads must be audited.
- Patient-identifiable exports require special permission.
- Failed submissions must not be silently ignored.
- Manual exports must still be recorded as submissions.
- Government endpoint configuration must be restricted to trusted admins.

## 13.11 Phase 3 UI Requirements

Pages:

```text
Submission Profiles
Approved Reports Ready to Submit
Submission Detail
Export Report Modal
Submission History
Integration Status Dashboard
Rejected Submission Correction Screen
```

Status display:

```text
ready_to_submit
submitting
submitted
accepted
rejected
retrying
exported
manual_submission_recorded
```

## 13.12 Phase 3 Tests

Required tests:

1. Unapproved report cannot be submitted.
2. Unauthorized user cannot submit.
3. Submission profile validates required mapping.
4. CSV export produces expected columns.
5. PDF export hides unauthorized identifiers.
6. API submission records response.
7. Rejected submission can enter correction workflow.
8. Export download is audited.
9. Expired export cannot be downloaded.
10. Submission credentials are not exposed.
11. Retry does not duplicate accepted report.
12. Payload hash is stored.

## 13.13 Phase 3 Acceptance Criteria

Phase 3 is acceptable when:

1. Approved reports can be submitted.
2. Submission profiles can be configured.
3. API submission is supported as a framework.
4. CSV/Excel/PDF export is supported.
5. Submission response is tracked.
6. Rejected submissions can be corrected and resubmitted.
7. Manual submission can be recorded.
8. Exports are access-controlled.
9. All submissions are audited.
10. Integration dashboard shows submission status.
11. No report is submitted without approval.
12. No full patient record is submitted by default.

---

# 14. Phase 4: Anomaly Detection, Outbreak Intelligence, and Regional Health Signals

## 14.1 Phase 4 Goal

Phase 4 adds intelligence to help detect unusual public health signals, disease clusters, medicine shortages, blood shortages, lab positivity spikes, and health system pressure.

This phase may use Python services for analytics and anomaly detection.

## 14.2 Phase 4 Scope

Build:

1. Signal detection engine
2. Disease cluster detection
3. Lab positivity spike detection
4. Medicine shortage intelligence
5. Blood shortage intelligence
6. Facility capacity pressure detection
7. Regional dashboard
8. Signal review workflow
9. Confidence level scoring
10. False positive/false negative feedback
11. Alerting engine
12. Public health intelligence API
13. Python analytics service where needed

## 14.3 Signal Types

```text
disease_cluster
symptom_cluster
lab_positivity_spike
medicine_stock_out_cluster
blood_shortage_cluster
maternal_death_signal
neonatal_death_signal
unusual_death_cluster
facility_capacity_pressure
referral_spike
emergency_visit_spike
```

## 14.4 Signal Statuses

```text
new_signal
under_review
needs_more_data
dismissed
confirmed
escalated
resolved
```

## 14.5 Signal Detection Flow

1. Data enters OpesCare from facilities.
2. Aggregation job runs.
3. Baseline is calculated for facility/district/region.
4. Current pattern is compared with baseline.
5. If threshold is crossed, signal is created.
6. Signal appears in review queue.
7. Public health reviewer investigates.
8. Reviewer confirms, dismisses, escalates, or requests more data.
9. Alert may be sent to approved users.
10. Audit event is logged.

## 14.6 Lab Positivity Spike Flow

1. Lab results are aggregated by test/disease/location/time.
2. System calculates positivity rate.
3. System compares with baseline.
4. Spike triggers signal.
5. Signal is reviewed.
6. If confirmed, report draft can be generated.
7. Audit event is logged.

## 14.7 Medicine Shortage Signal Flow

1. Pharmacy stock data is aggregated.
2. System detects stock-out across multiple facilities or regions.
3. Essential medicine shortage signal is created.
4. Signal shows affected medicine, area, facilities, last stock update, and severity.
5. Public health or supply chain reviewer acts.
6. Audit event is logged.

## 14.8 Blood Shortage Signal Flow

1. Blood inventory and urgent need requests are aggregated.
2. System detects shortage by group/component/region.
3. Signal is created.
4. Hospitals/blood banks may be alerted.
5. Review and response are tracked.
6. Audit event is logged.

## 14.9 Capacity Pressure Signal Flow

1. Facility capacity data is updated.
2. System checks bed occupancy, emergency load, oxygen availability, blood stock, and medicine stock.
3. If pressure threshold is reached, signal is created.
4. Reviewer confirms or dismisses.
5. Approved stakeholders can view dashboard.
6. Audit event is logged.

## 14.10 Python Analytics Service

Recommended service:

```text
services/public-health-intelligence-python
```

Functions:

```text
calculate_baselines
detect_spikes
detect_clusters
score_signal_confidence
generate_trend_indicators
identify_stale_data
rank_facility_pressure
```

Laravel remains the source of truth.

Python service must not own canonical patient records.

Data sent to Python should be minimized and de-identified or aggregate where possible.

## 14.11 Phase 4 Data Models

### public_health_signals

```text
id
uuid
signal_type
status
scope_type
scope_id
facility_id nullable
district_id nullable
region_id nullable
condition_code nullable
indicator_code
baseline_value
current_value
increase_percentage
confidence_level
severity
detected_at
reviewed_at
resolved_at
metadata_json
created_at
updated_at
```

### public_health_signal_reviews

```text
id
signal_id
reviewer_id
action
comment
reviewed_at
created_at
updated_at
```

### public_health_signal_alerts

```text
id
signal_id
recipient_type
recipient_id
channel
status
sent_at
acknowledged_at
created_at
updated_at
```

### public_health_baselines

```text
id
scope_type
scope_id
indicator_code
period_type
baseline_value
calculated_at
metadata_json
created_at
updated_at
```

## 14.12 Phase 4 API Endpoints

```text
GET  /api/v1/public-health/signals
GET  /api/v1/public-health/signals/{id}
POST /api/v1/public-health/signals/{id}/review
POST /api/v1/public-health/signals/{id}/confirm
POST /api/v1/public-health/signals/{id}/dismiss
POST /api/v1/public-health/signals/{id}/escalate
POST /api/v1/public-health/signals/{id}/resolve
GET  /api/v1/public-health/intelligence/dashboard
GET  /api/v1/public-health/intelligence/trends
GET  /api/v1/public-health/intelligence/shortages
```

## 14.13 Phase 4 UI Requirements

Pages:

```text
Public Health Intelligence Dashboard
Signal Review Queue
Disease Cluster Map
Lab Positivity Trends
Medicine Shortage Map
Blood Shortage Dashboard
Facility Capacity Pressure Dashboard
Signal Detail Page
Signal Review Timeline
```

UI safety rules:

- label signals as signals, not confirmed outbreaks
- show confidence level
- show data freshness
- show affected area
- show review status
- show data quality warnings
- avoid panic language
- avoid public patient identity exposure

## 14.14 Phase 4 Alert Rules

Alerts should be sent for:

```text
critical disease signal
confirmed outbreak signal
severe blood shortage
regional essential medicine shortage
critical facility capacity pressure
unusual death cluster
```

Alert recipients must be role- and jurisdiction-limited.

## 14.15 Phase 4 Tests

Required tests:

1. Signal generated when threshold is crossed.
2. Signal not generated when data is stale unless stale-data signal is intended.
3. Lab positivity spike calculates correctly.
4. Medicine shortage signal excludes unverified stock.
5. Blood shortage signal excludes expired/quarantined units.
6. Signal can be reviewed.
7. Signal can be confirmed/dismissed/escalated/resolved.
8. Confidence level is stored.
9. Patient identity is not exposed in signal dashboard.
10. Unauthorized users cannot view signals outside jurisdiction.
11. Python service cannot write canonical patient records.
12. Signal alert is audited.

## 14.16 Phase 4 Acceptance Criteria

Phase 4 is acceptable when:

1. Public health signals can be detected.
2. Signals distinguish suspected vs confirmed.
3. Signal review workflow exists.
4. Lab positivity spikes can be detected.
5. Medicine shortage clusters can be detected.
6. Blood shortage clusters can be detected.
7. Facility capacity pressure can be detected.
8. Regional dashboards exist.
9. Alerts are role- and jurisdiction-limited.
10. No patient identity is exposed in public dashboards.
11. Analytics use minimized or aggregate data.
12. Python service does not own canonical clinical records.
13. All signal actions are audited.

---

# 15. Cross-Phase Reporting Architecture

## 15.1 Laravel Responsibilities

Laravel handles:

```text
report definitions
report rules
report generation orchestration
canonical data storage
permissions
review workflows
approval workflows
submission workflows
audit logs
dashboards
exports
API endpoints
```

## 15.2 PostgreSQL Responsibilities

PostgreSQL stores:

```text
report data
report items
versions
review history
submission records
signal records
baselines
data quality checks
audit references
```

## 15.3 Redis Responsibilities

Redis handles:

```text
background jobs
report generation queue
submission retry queue
dashboard snapshot refresh
signal detection scheduling
alert dispatch queue
cache for safe aggregate dashboard metrics
```

Redis must not store canonical report records permanently.

## 15.4 Python Responsibilities

Python may handle:

```text
statistical baselines
anomaly detection
cluster detection
trend analysis
forecasting later
data quality intelligence later
```

Python must receive minimized, aggregate, or de-identified data where possible.

---

# 16. Public Health Rule Configuration

Reporting rules must be configurable by country policy.

Rule fields:

```text
report_type
trigger_source
trigger_condition
aggregation_level
requires_review
requires_identifiable_data
allows_auto_submit
submission_profile
effective_date
expiry_date
jurisdiction
```

Examples:

```text
If diagnosis code indicates suspected cholera, create notifiable disease draft.
If lab result confirms cholera, create confirmed notifiable disease draft.
If stock status of essential medicine is out_of_stock for more than 24 hours, create pharmacy stock-out report.
If blood group O- units fall below threshold, create blood shortage report.
```

---

# 17. Data Quality Scoring

Every report should have a data quality score.

Score factors:

```text
required fields completeness
facility mapping completeness
period correctness
duplicate check
data freshness
code mapping quality
validation status
source reliability
count consistency
```

Quality levels:

```text
excellent
good
needs_review
poor
blocked
```

Rules:

- blocked reports cannot be submitted
- poor reports require correction or override
- overrides require reason and audit

---

# 18. Privacy and Patient Data Protection

Mandatory rules:

1. Aggregate by default.
2. De-identify where possible.
3. Identifiable data only when required.
4. Never send full patient history automatically.
5. Never expose patient identity in public dashboards.
6. Use small-cell suppression for public or research outputs.
7. Audit every sensitive report view.
8. Audit every export.
9. Restrict identifiable reports by role.
10. Apply country policy configuration.
11. Display clear warning before identifiable export/submission.
12. Do not use unverified patient-uploaded documents for official reporting.

---

# 19. Small-Cell Suppression

For public or research-style aggregate output, suppress small counts when there is re-identification risk.

Rule example:

```text
If count is below configured threshold, display "< threshold" or suppress value.
```

Configurable threshold:

```text
default_small_cell_threshold = 5
```

This should be configurable by policy.

---

# 20. Reporting APIs Summary

## Phase 1 APIs

```text
GET  /api/v1/public-health/report-types
GET  /api/v1/public-health/reports
GET  /api/v1/public-health/reports/{id}
POST /api/v1/public-health/reports/generate-drafts
GET  /api/v1/public-health/reports/{id}/quality-checks
GET  /api/v1/public-health/dashboard
GET  /api/v1/public-health/facility-dashboard
```

## Phase 2 APIs

```text
POST /api/v1/public-health/reports/{id}/submit-for-review
POST /api/v1/public-health/reports/{id}/assign
POST /api/v1/public-health/reports/{id}/approve
POST /api/v1/public-health/reports/{id}/request-correction
POST /api/v1/public-health/reports/{id}/reject
POST /api/v1/public-health/reports/{id}/cancel
POST /api/v1/public-health/reports/{id}/correct
GET  /api/v1/public-health/reports/{id}/versions
GET  /api/v1/public-health/reports/{id}/status-history
GET  /api/v1/public-health/review-queue
```

## Phase 3 APIs

```text
GET  /api/v1/public-health/submission-profiles
POST /api/v1/public-health/submission-profiles
PUT  /api/v1/public-health/submission-profiles/{id}
POST /api/v1/public-health/reports/{id}/submit
POST /api/v1/public-health/reports/{id}/resubmit
GET  /api/v1/public-health/reports/{id}/submissions
POST /api/v1/public-health/reports/{id}/export
GET  /api/v1/public-health/exports/{id}/download
GET  /api/v1/public-health/integration-status
```

## Phase 4 APIs

```text
GET  /api/v1/public-health/signals
GET  /api/v1/public-health/signals/{id}
POST /api/v1/public-health/signals/{id}/review
POST /api/v1/public-health/signals/{id}/confirm
POST /api/v1/public-health/signals/{id}/dismiss
POST /api/v1/public-health/signals/{id}/escalate
POST /api/v1/public-health/signals/{id}/resolve
GET  /api/v1/public-health/intelligence/dashboard
GET  /api/v1/public-health/intelligence/trends
GET  /api/v1/public-health/intelligence/shortages
```

---

# 21. UI Navigation Additions

Add to staff/admin portal:

```text
Public Health
  - Dashboard
  - Report Drafts
  - Review Queue
  - Approved Reports
  - Submissions
  - Data Quality
  - Disease Signals
  - Medicine Shortages
  - Blood Shortages
  - Facility Completeness
  - Reporting Rules
  - Submission Profiles
```

Add to facility portal:

```text
Facility Reporting
  - My Reports
  - Draft Reports
  - Corrections Required
  - Facility Dashboard
  - Stock-Out Reports
  - Lab Surveillance
  - Blood Availability Reports
```

---

# 22. Bilingual UI Requirements

All reporting UI must support English and French.

Examples:

```text
Public Health Reports → Rapports de santé publique
Report Drafts → Brouillons de rapports
Pending Review → En attente d’examen
Approved for Submission → Approuvé pour soumission
Requires Correction → Correction requise
Notifiable Disease → Maladie à déclaration obligatoire
Medicine Stock-Out → Rupture de stock de médicament
Blood Shortage → Pénurie de sang
Data Quality Score → Score de qualité des données
```

Use clear medical language.

Avoid jargon-heavy wording.

---

# 23. Security Risks and Controls

## Risk: Government receives too much patient data

Control:

- aggregate by default
- identifiable reporting disabled unless policy enables
- approval workflow
- audit logs

## Risk: False outbreak alert causes panic

Control:

- label signal status clearly
- review workflow
- confidence score
- no public confirmation without review

## Risk: Sensitive diseases exposed

Control:

- sensitivity classification
- role permissions
- restricted dashboards
- privacy review

## Risk: Duplicate reports

Control:

- duplicate detection by report type/facility/period/indicator
- idempotency for submissions

## Risk: Bad data sent externally

Control:

- data quality checks
- review before submission
- correction workflow

## Risk: Stale stock creates wrong shortage reports

Control:

- data freshness checks
- stale labels
- stock update timestamps

---

# 24. Complete Build Roadmap

## Phase 1 Build Order

1. Create public health module scaffold.
2. Create report type model.
3. Create reporting rule model.
4. Create report model.
5. Create report item model.
6. Create data quality check service.
7. Create draft generation service.
8. Create notifiable disease draft generator.
9. Create facility aggregate draft generator.
10. Create lab surveillance draft generator.
11. Create pharmacy stock-out draft generator.
12. Create blood shortage draft generator.
13. Create internal dashboard.
14. Add tests.

## Phase 2 Build Order

1. Create review queue.
2. Add report assignment.
3. Add approval workflow.
4. Add correction workflow.
5. Add rejection workflow.
6. Add cancellation workflow.
7. Add report versions.
8. Add status history.
9. Add sensitive access audit.
10. Add two-person approval configuration.
11. Add tests.

## Phase 3 Build Order

1. Create submission profile model.
2. Create export service.
3. Add CSV export.
4. Add Excel export.
5. Add PDF export.
6. Add API submission placeholder.
7. Add submission response tracking.
8. Add resubmission workflow.
9. Add integration status dashboard.
10. Add tests.

## Phase 4 Build Order

1. Create signal model.
2. Create baseline model.
3. Create signal detection service.
4. Add disease cluster detection.
5. Add lab positivity spike detection.
6. Add medicine shortage signal.
7. Add blood shortage signal.
8. Add facility pressure signal.
9. Create intelligence dashboard.
10. Add Python analytics service placeholder.
11. Add signal review workflow.
12. Add tests.

---

# 25. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, and docs/public-health/OPESCARE_PUBLIC_HEALTH_REPORTING_PHASES.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS code, database, module structure, reporting logic, or API design.

Task: Create the Public Health Reporting Module foundation for Phase 1 only.

Scope:
1. Create module placeholder: app/Modules/PublicHealth.
2. Create docs/public-health folder if missing.
3. Add model placeholders for ReportType, ReportingRule, PublicHealthReport, ReportItem, DataQualityCheck, DashboardSnapshot.
4. Add migration placeholders or draft migration files if project migration standard exists.
5. Add route placeholders for Phase 1 APIs.
6. Add service placeholders: DraftGenerationService, DataQualityCheckService, NotifiableDiseaseDraftGenerator, FacilityAggregateDraftGenerator, LabSurveillanceDraftGenerator, PharmacyStockOutDraftGenerator, BloodShortageDraftGenerator.
7. Add policy placeholders for public health permissions.
8. Add audit event enum placeholders for report draft generation and dashboard views.
9. Add dashboard placeholder page/component if UI exists.
10. Add tests proving:
   - routes are registered
   - unauthorized users cannot access reports
   - report draft generation requires permission
   - generated drafts do not include full patient records
11. Do not build government submission in this task.
12. Do not build AI signal detection in this task.
13. Do not send any data externally.
14. Open a PR with summary, files created, risks, and next recommended tasks.
```

---

# 26. Final Review Checklist

Before considering this module complete, verify:

1. Are reports generated only from approved data sources?
2. Are full patient records blocked from automatic reporting?
3. Is aggregate reporting the default?
4. Is identifiable reporting controlled?
5. Are data quality checks present?
6. Is every report status clear?
7. Is every review action audited?
8. Can reports be corrected without overwriting history?
9. Are external submissions blocked until Phase 3?
10. Are government endpoints configurable?
11. Are rejected submissions correctable?
12. Are exports audited?
13. Are signals labeled clearly as signal/suspected/confirmed?
14. Is patient identity hidden in blood shortage reports?
15. Is medicine stock based on verified/recent inventory?
16. Is blood stock based on safe, non-expired, non-quarantined inventory?
17. Are dashboards role-based?
18. Are reports bilingual-ready?
19. Are sensitive diseases protected?
20. Are tests included for privacy and failure cases?

---

# 27. Final Safety Rule

The Public Health Reporting Module must support government reporting, but it must not become uncontrolled patient data transfer.

The system should help authorities understand public health conditions, disease signals, medicine shortages, blood shortages, and facility capacity while protecting patient privacy and preserving trust.

The correct model is:

```text
collect approved data
generate safe drafts
check data quality
review sensitive reports
approve before submission
submit through approved channels
audit every step
protect patient identity
```

If a report cannot prove that it is necessary, authorized, minimum, and auditable, it must not be submitted.
