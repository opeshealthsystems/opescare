# OpesCare Partner Contribution & Governance Module PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Document Type:** Product Requirements + Technical Architecture + Governance Blueprint  
**Build Direction:** Build from scratch  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Specialist Services:** Python/FastAPI later for contribution quality scoring, anomaly detection, partner risk scoring, and data quality intelligence  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, database structure, UI, module structure, partner model, or assumptions.

---

# 1. Purpose

The **Partner Contribution & Governance Module** is the institutional trust engine of OpesCare.

OpesCare is a digital Health ID and healthcare interoperability platform. It depends on verified partners to contribute patient records, lab results, prescriptions, medicine availability, blood availability, insurance workflows, public health reports, research support, governance rules, and technical integrations.

This module controls:

- who can become an OpesCare partner
- what type of partner they are
- how they are verified
- what agreement they have signed
- what data they can contribute
- what data they can access
- what systems they can integrate
- what workflows they can approve or review
- what public health role they can play
- what research role they can play
- what audit trail follows their actions
- what happens when they make errors or violate rules
- how their contribution quality is measured

This module is not a marketing partner directory. It is a governance, verification, permissions, and accountability layer.

---

# 2. Why This Module Is Needed

OpesCare cannot safely allow every organization to contribute or access health data without structure.

A hospital should not have the same access as an insurance company.

A pharmacy should not have the same rights as a public health authority.

A researcher should not receive identifiable patient data just because they are a partner.

A technology vendor should not receive production API access without verification.

A lab should not publish unvalidated results.

A pharmacy should not show stale stock as available.

A public health partner should not see full patient records by default.

Therefore, OpesCare needs a formal module that defines:

```text
partner identity
partner verification
partner legal/compliance status
contribution permissions
data access permissions
integration permissions
review/approval permissions
audit history
trust level
contribution quality
risk status
```

---

# 3. Core Principle

Every partner action in OpesCare must answer:

1. Who is the partner?
2. Has the partner been verified?
3. What type of partner are they?
4. What agreement governs their access?
5. What contribution are they making?
6. What data are they allowed to access?
7. What data are they allowed to contribute?
8. Are they acting through a verified facility, system, or user?
9. Is patient consent required?
10. Is there a legal/policy basis?
11. Is the action audited?
12. Is the contribution trusted, pending review, or rejected?
13. What happens if the partner contributes wrong or unsafe data?

If these questions cannot be answered, the partner action must be blocked or sent for review.

---

# 4. Product Position

OpesCare’s value comes from trusted health contributions by verified health actors.

The Partner Contribution & Governance Module ensures that every participating organization or professional contributes safely, transparently, and under clearly defined permissions.

This module supports OpesCare’s core vision:

```text
trusted identity
trusted data
trusted partners
controlled access
safe interoperability
audited contribution
```

---

# 5. Partner Universe

The module must support these partner categories.

## 5.1 Government and Public Sector

```text
Government ministries
Ministry of Public Health
Public health authorities
Regional health delegations
District health offices
National health programs
Statistics and demographic agencies
Health regulators
Standards bodies
```

## 5.2 Healthcare Facilities

```text
Hospitals
Teaching hospitals
Specialist hospitals
Clinics
Health centers
Medical centers
Diagnostic centers
Radiology/imaging centers
Blood banks
Ambulance/emergency services
Rehabilitation centers
Dental clinics
Mental health centers
```

## 5.3 Healthcare Professionals

```text
Medical doctors
Specialists
Nurses
Midwives
Dentists
Pharmacists
Laboratory scientists/technicians
Radiologists
Physiotherapists
Mental health professionals
Nutritionists/dietitians
Community health workers
Public health officers
```

## 5.4 Medicines, Pharmacy, and Supply Chain

```text
Pharmacies
Hospital pharmacies
Pharmaceutical distributors
Drug manufacturers
Medical equipment suppliers
Vaccine supply partners
Cold-chain/logistics partners
```

## 5.5 Insurance and Payment

```text
Insurance companies
HMOs/health plans
Employer health schemes
Third-party administrators
Payment providers
Mobile money providers
Banking/payment partners
```

## 5.6 Academic, Research, and Training

```text
Health universities
Medical schools
Nursing schools
Pharmacy schools
Public health schools
Research institutes
Clinical research organizations
Ethics committees
Teaching hospitals
Data science/computer science departments
```

## 5.7 Technology and Interoperability

```text
Hospital information system vendors
Laboratory information system vendors
Pharmacy software vendors
Insurance software vendors
Telemedicine providers
Health technology companies
API integration partners
Cloud/infrastructure providers
Cybersecurity partners
Telecom companies
Device manufacturers
```

## 5.8 Civil Society and Community

```text
Patient advocacy groups
Disease-specific associations
NGOs
Health foundations
Community health organizations
International health organizations
Development partners
Faith-based health organizations where applicable
Recognized outreach organizations
```

## 5.9 Legal, Privacy, and Governance

```text
Data protection experts
Health law experts
Privacy consultants
Medical ethics experts
Compliance auditors
Cybersecurity auditors
Clinical quality reviewers
```

---

# 6. Partner Contribution Categories

Partners should be organized by what they contribute, not only who they are.

## 6.1 Clinical Data Contributors

Examples:

```text
hospitals
clinics
doctors
nurses
specialists
midwives
```

Contribution types:

```text
consultations
diagnoses
vitals
prescriptions
referrals
admissions
discharges
emergency records
clinical notes
care plans
```

## 6.2 Diagnostic Data Contributors

Examples:

```text
laboratories
radiology centers
diagnostic imaging centers
```

Contribution types:

```text
lab orders
sample records
verified lab results
critical result alerts
imaging reports
diagnostic reports
amended results
```

## 6.3 Pharmacy and Medicine Contributors

Examples:

```text
pharmacies
hospital pharmacies
pharmaceutical distributors
drug manufacturers
```

Contribution types:

```text
medicine availability
stock levels
stock-outs
dispense events
batch status
recalls
expired stock warnings
quarantine status
```

## 6.4 Blood and Emergency Service Contributors

Examples:

```text
blood banks
hospitals
emergency services
ambulance services
```

Contribution types:

```text
blood availability
blood group stock
blood component stock
blood need requests
blood transfer status
emergency response status
```

## 6.5 Insurance and Payment Contributors

Examples:

```text
insurance companies
HMOs
payment partners
```

Contribution types:

```text
eligibility
coverage
preauthorization
claims
payment status
policy status
```

## 6.6 Public Health Contributors

Examples:

```text
government
public health agencies
hospitals
labs
pharmacies
blood banks
health programs
```

Contribution types:

```text
notifiable disease rules
public health reports
surveillance data
medicine shortage reports
blood shortage reports
vaccination reports
facility activity reports
outbreak signal review
```

## 6.7 Research and Academic Contributors

Examples:

```text
universities
research institutes
ethics committees
teaching hospitals
```

Contribution types:

```text
research proposals
ethics approvals
study protocols
de-identified analysis
clinical validation
public health studies
training materials
```

## 6.8 Technical Integration Contributors

Examples:

```text
software vendors
telemedicine providers
cloud partners
cybersecurity partners
telecom partners
```

Contribution types:

```text
API integrations
SDK integrations
Bridge Agent setup
webhook connections
identity verification support
security audits
infrastructure support
```

## 6.9 Patient Rights and Community Contributors

Examples:

```text
patient advocacy groups
disease-specific associations
NGOs
community health organizations
```

Contribution types:

```text
patient education
rights review
community feedback
support programs
complaint escalation
accessibility feedback
```

## 6.10 Governance and Compliance Contributors

Examples:

```text
legal experts
privacy experts
ethics committees
medical councils
professional boards
regulators
```

Contribution types:

```text
policy review
consent model review
data protection review
professional verification
ethics approval
compliance audit
```

---

# 7. Partner Statuses

Every partner must have a controlled status.

```text
draft
submitted
under_review
more_information_required
verified
approved
active
limited
suspended
rejected
expired
terminated
archived
```

## 7.1 Status Meaning

### draft

Partner application started but not submitted.

### submitted

Application received.

### under_review

OpesCare team or governance reviewer is reviewing the partner.

### more_information_required

Partner must provide missing documents or clarification.

### verified

Identity/legal existence/professional status has been checked.

### approved

Partner approved for participation but not necessarily active in production.

### active

Partner can use assigned permissions.

### limited

Partner has restricted access because of risk, incomplete verification, or policy limitation.

### suspended

Partner access is temporarily blocked.

### rejected

Partner application was rejected.

### expired

Agreement, license, or verification has expired.

### terminated

Partnership ended.

### archived

Historical record retained.

---

# 8. Partner Trust Levels

Trust level controls contribution confidence and access limits.

```text
level_0_unverified
level_1_registered
level_2_document_verified
level_3_operational_verified
level_4_clinical_trusted
level_5_governance_trusted
```

## 8.1 Level 0: Unverified

Can only submit application.

No data access.

No data contribution.

## 8.2 Level 1: Registered

Application exists.

May upload documents.

No production access.

## 8.3 Level 2: Document Verified

Documents checked.

May enter sandbox or limited onboarding.

## 8.4 Level 3: Operational Verified

Facility/organization verified for operational use.

May contribute allowed operational data.

## 8.5 Level 4: Clinical Trusted

Clinical contribution rights approved.

Can contribute validated clinical data according to role.

## 8.6 Level 5: Governance Trusted

High-trust partner for governance, public health, research review, or policy workflows.

Must be tightly permissioned and audited.

---

# 9. Partner Verification Requirements

Verification requirements depend on partner type.

## 9.1 Organization Verification

Required for:

```text
hospitals
clinics
pharmacies
labs
insurers
public health agencies
technology vendors
research institutions
NGOs
```

Required documents may include:

```text
business registration
facility license
health authority license
tax registration
professional license where applicable
authorized representative ID
data protection/compliance document
signed agreement
technical security questionnaire
```

## 9.2 Facility Verification

Required for:

```text
hospitals
clinics
labs
pharmacies
blood banks
radiology centers
health centers
```

Verification checks:

```text
facility type
address/location
license
services offered
responsible officer
department structure
staff roles
integration readiness
data protection readiness
```

## 9.3 Professional Verification

Required for:

```text
doctors
nurses
midwives
pharmacists
lab professionals
specialists
dentists
physiotherapists
mental health professionals
```

Verification checks:

```text
professional license number
professional council/board registration
specialty
facility affiliation
employment/contract relationship
scope of practice
license expiry date
```

## 9.4 Technology Partner Verification

Required for:

```text
HIS vendors
LIS vendors
pharmacy software vendors
insurance system vendors
telemedicine providers
API integration partners
```

Verification checks:

```text
company identity
technical contact
security questionnaire
data protection commitment
sandbox testing
API scope approval
webhook domain approval
production readiness
incident contact
```

## 9.5 Research Partner Verification

Required for:

```text
universities
research institutes
clinical study partners
public health researchers
```

Verification checks:

```text
institution identity
research purpose
ethics approval
data minimization plan
de-identification plan
dataset request scope
principal investigator
data retention plan
publication plan
```

---

# 10. Partner Agreements

Every active partner must be tied to one or more agreements.

## 10.1 Agreement Types

```text
general_partnership_agreement
data_contribution_agreement
data_access_agreement
facility_participation_agreement
clinical_contribution_agreement
public_health_reporting_agreement
research_data_access_agreement
api_integration_agreement
pharmacy_availability_agreement
blood_availability_agreement
insurance_workflow_agreement
security_and_compliance_addendum
data_processing_agreement
```

## 10.2 Agreement Statuses

```text
draft
sent
signed
active
expired
terminated
superseded
revoked
```

## 10.3 Agreement Rules

- No production contribution without relevant active agreement.
- No identifiable research access without research agreement and governance approval.
- No public health submission without public health reporting agreement or legal basis.
- No API production access without integration agreement.
- Agreement expiry must restrict relevant permissions.
- Agreement changes must be audited.
- Agreements should be versioned.

---

# 11. Contribution Permissions

Contribution permissions define what a partner can submit to OpesCare.

## 11.1 Permission Structure

Each contribution permission must define:

```text
partner_id
facility_id optional
contribution_type
allowed_data_categories
allowed_source_systems
requires_review
requires_validation
effective_from
expires_at
status
approved_by
```

## 11.2 Contribution Permission Examples

### Hospital

```text
encounters.create
diagnoses.create
prescriptions.create
referrals.create
admissions.create
discharges.create
emergency_records.create
```

### Laboratory

```text
lab_orders.receive
lab_results.create
lab_results.validate
lab_results.amend
critical_results.alert
```

### Pharmacy

```text
prescriptions.dispense
medicine_stock.sync
medicine_reservations.manage
dispense_events.create
```

### Blood Bank

```text
blood_stock.sync
blood_need_requests.create
blood_reservations.manage
blood_transfers.record
```

### Insurance

```text
eligibility.create
claims.create
claims.update_status
preauthorization.create
coverage.update
```

### Public Health

```text
reporting_rules.manage
public_health_reports.review
public_health_reports.submit
signals.review
```

### Technology Vendor

```text
api_client.create_sandbox
api_integration.test
webhooks.configure
bridge_agent.connect
```

### Research Partner

```text
research_requests.submit
approved_dataset.access
deidentified_data.review
```

---

# 12. Data Access Permissions

Data access permissions define what partners can read.

## 12.1 Access Permission Rules

Access must always consider:

```text
partner type
trust level
agreement status
facility context
user role
purpose of use
patient consent
data sensitivity
country policy
audit requirement
```

## 12.2 Hospital/Clinic Access

May access:

```text
patient summary after consent/policy
clinical timeline after consent/policy
emergency profile during emergency
referral package
own facility records
```

Cannot access by default:

```text
all national records without consent
unrelated patients
sensitive data outside role/purpose
```

## 12.3 Pharmacy Access

May access:

```text
prescription details
dispense history for relevant prescription
medication allergy warning where needed
medicine stock module
```

Cannot access by default:

```text
full patient timeline
full clinical notes
unrelated lab results
insurance claim details
```

## 12.4 Laboratory Access

May access:

```text
lab orders
patient identity needed for sample matching
test result entry
previous relevant lab result only where allowed
```

Cannot access by default:

```text
full clinical timeline
unrelated prescriptions
insurance claims
```

## 12.5 Insurance Access

May access:

```text
eligibility
claims
preauthorization
minimum necessary supporting information
```

Cannot access by default:

```text
full patient timeline
unrelated diagnoses
unrelated lab results
sensitive data not needed for claim
```

## 12.6 Public Health Access

May access:

```text
aggregate reports
approved surveillance data
approved notifiable disease reports
approved signals
```

Cannot access by default:

```text
full patient records
full identifiable timeline
patient access logs
private consent logs
```

## 12.7 Research Access

May access only:

```text
approved datasets
de-identified or aggregate data by default
time-limited exports
ethics-approved scope
```

Cannot access by default:

```text
identifiable patient records
full raw database
unapproved sensitive data
```

---

# 13. Partner Contribution Workflow

## 13.1 General Workflow

1. Partner applies.
2. Partner type is selected.
3. Required documents are uploaded.
4. OpesCare reviews documents.
5. Partner trust level is assigned.
6. Agreement is signed.
7. Contribution permissions are configured.
8. Data access permissions are configured.
9. Integration method is configured if needed.
10. Partner enters sandbox if technical integration is required.
11. Partner passes certification.
12. Production access is approved.
13. Partner contributes data.
14. Data quality checks run.
15. Contributions are accepted, rejected, or sent for review.
16. Audit logs are created.
17. Partner performance is tracked.

---

# 14. Partner Onboarding Workflow

## 14.1 Application Step

Fields:

```text
partner type
legal name
trade name
country
region/city
address
contact person
contact role
email
phone
website
license/registration number
services offered
intended contribution
integration needs
```

## 14.2 Document Upload Step

Required documents depend on partner type.

Fields:

```text
document_type
file
expiry_date if applicable
issuing_authority
status
review_notes
```

## 14.3 Review Step

Reviewer checks:

```text
identity
license validity
professional status
agreement needs
data contribution scope
data access risks
integration risks
public health/research sensitivity
```

## 14.4 Approval Step

Approver sets:

```text
partner status
trust level
allowed contribution types
allowed access types
required agreements
required technical certification
go-live conditions
```

## 14.5 Activation Step

Partner becomes active only when:

```text
verification complete
required agreements active
permissions approved
technical integration certified where required
security requirements met
```

---

# 15. Technical Integration Governance

Partners connecting systems must use the OpesCare Connect framework.

Integration methods:

```text
Connect API
Connect SDK
Connect Widget
Bridge Agent
Webhooks
OpesCare Lite
```

## 15.1 Integration Statuses

```text
not_started
sandbox_requested
sandbox_active
testing
failed_testing
certified
production_requested
production_active
suspended
revoked
```

## 15.2 Production Integration Requirements

Before production access:

1. Partner verified.
2. Agreement signed.
3. API scopes approved.
4. Sandbox credentials issued.
5. Test patient search passed.
6. Consent flow tested.
7. Push records tested.
8. Pull records tested.
9. Idempotency tested.
10. Webhook verification tested.
11. Error handling tested.
12. Security review completed.
13. Production credentials issued.
14. First production sync monitored.

## 15.3 Integration Permissions

Technical partner permissions must include:

```text
allowed API scopes
allowed webhook events
allowed facilities
allowed environments
rate limits
credential expiry
IP allowlist optional
approved redirect/webhook domains
```

---

# 16. Contribution Quality Scoring

Every active contributor should have a contribution quality score.

## 16.1 Score Factors

```text
data completeness
data accuracy
timeliness
duplicate rate
reconciliation rate
rejection rate
correction rate
stale stock rate
critical alert handling
sync success rate
audit compliance
```

## 16.2 Score Levels

```text
excellent
good
needs_improvement
high_risk
suspended
```

## 16.3 Quality Consequences

If quality is poor:

```text
increase review requirements
limit automatic acceptance
require retraining
restrict API scopes
suspend contribution permission
escalate to governance review
```

---

# 17. Partner Risk Scoring

Partner risk score helps governance teams manage unsafe or unreliable partners.

## 17.1 Risk Factors

```text
expired license
missing agreement
high error rate
high rejected contribution rate
security incident
suspicious access
stale stock reporting
unresolved reconciliation cases
complaints
policy violations
unauthorized access attempt
API abuse
```

## 17.2 Risk Levels

```text
low
moderate
high
critical
```

## 17.3 Risk Actions

```text
monitor
request correction
limit permissions
require review
suspend API access
suspend partner
terminate partnership
notify governance team
```

---

# 18. Partner Performance Dashboard

Dashboards should show:

```text
active partners
pending applications
partners under review
expired agreements
expired licenses
integration health
contribution volume
contribution quality score
reconciliation cases
sync failures
stock freshness
public health reporting completeness
partner risk level
```

## 18.1 Facility Partner Dashboard

For hospitals/clinics:

```text
patients served
records contributed
consent requests
referrals
public health report drafts
sync status
data quality issues
```

## 18.2 Pharmacy Partner Dashboard

```text
stock updates
stale stock
stock-outs
dispense events
medicine reservations
expired/recalled/quarantined stock alerts
```

## 18.3 Lab Partner Dashboard

```text
orders received
samples collected
results released
critical results
amendments
rejected samples
lab surveillance drafts
```

## 18.4 Insurance Partner Dashboard

```text
eligibility checks
preauthorization requests
claims submitted
claims approved/rejected
minimum necessary access events
```

## 18.5 Public Health Partner Dashboard

```text
report drafts
reports reviewed
reports submitted
signals reviewed
medicine shortages
blood shortages
facility reporting completeness
```

## 18.6 Developer Partner Dashboard

```text
API clients
sandbox status
production status
webhook health
sync failures
rate limit usage
credential expiry
reconciliation cases
```

---

# 19. Governance Review Cases

The module must support governance cases.

## 19.1 Case Types

```text
partner_application_review
document_verification
license_expiry
agreement_expiry
data_quality_issue
suspicious_access
contribution_violation
integration_failure
research_request_review
public_health_role_review
insurance_access_review
API_abuse_review
```

## 19.2 Case Statuses

```text
new
assigned
under_review
more_information_required
resolved
escalated
rejected
closed
```

## 19.3 Case Actions

```text
approve
reject
request_information
suspend_partner
limit_permissions
restore_permissions
escalate
close
```

---

# 20. Partner Suspension and Termination

## 20.1 Suspension Reasons

```text
expired license
expired agreement
security incident
unauthorized access
poor data quality
false data contribution
API abuse
unresolved compliance issue
government/regulatory instruction
professional license revoked
```

## 20.2 Suspension Effects

When partner is suspended:

```text
block new data access
block new contribution
disable API clients
pause webhooks
prevent public availability display
preserve existing records
audit suspension
notify responsible contacts
```

## 20.3 Termination Effects

When partner is terminated:

```text
disable active access
revoke API credentials
archive partner profile
retain historical records
retain audit logs
disable new contributions
mark agreements terminated
```

Termination must not delete legally retained clinical records.

---

# 21. Partner Contacts and Responsibility

Every partner should have contacts:

```text
primary contact
technical contact
clinical governance contact
privacy/data protection contact
billing/contact if needed
emergency contact
```

Each contact has:

```text
name
role
email
phone
preferred language
contact type
status
```

---

# 22. Partner Documents

Document types:

```text
business_registration
facility_license
professional_license
tax_registration
data_processing_agreement
partnership_agreement
research_ethics_approval
security_questionnaire
insurance_authorization
public_health_authorization
API_integration_approval
```

Document statuses:

```text
uploaded
pending_review
verified
rejected
expired
superseded
revoked
```

Document expiry must trigger alerts.

---

# 23. Alerts and Notifications

Partner governance alerts:

```text
partner_application_submitted
document_expiring
license_expired
agreement_expired
partner_approved
partner_suspended
API_credentials_expiring
contribution_quality_dropped
risk_score_increased
sync_failures_high
stale_stock_detected
public_health_report_pending
research_request_pending
```

Alert severity:

```text
info
warning
danger
critical
```

---

# 24. Audit Requirements

Audit all sensitive partner actions.

## 24.1 Audit Events

```text
partner_application_created
partner_application_submitted
partner_document_uploaded
partner_document_verified
partner_document_rejected
partner_status_changed
partner_trust_level_changed
partner_agreement_created
partner_agreement_signed
partner_agreement_expired
partner_permission_granted
partner_permission_revoked
partner_suspended
partner_terminated
partner_api_client_created
partner_api_client_revoked
partner_contribution_received
partner_contribution_accepted
partner_contribution_rejected
partner_contribution_flagged
partner_risk_score_changed
partner_quality_score_changed
partner_governance_case_created
partner_governance_case_resolved
```

## 24.2 Audit Fields

```text
actor_id
actor_role
partner_id
organization_id
facility_id nullable
action
old_value
new_value
reason
ip_address
user_agent
timestamp
correlation_id
```

---

# 25. Data Models

## 25.1 partners

```text
id
uuid
partner_type
legal_name
trade_name
country_code
region
city
address
website
status
trust_level
risk_level
quality_score
primary_contact_id nullable
created_at
updated_at
```

## 25.2 partner_facilities

```text
id
partner_id
facility_type
facility_name
facility_code
license_number
country_code
region
city
address
status
verified_at
created_at
updated_at
```

## 25.3 partner_professionals

```text
id
partner_id nullable
user_id nullable
professional_type
full_name
license_number
licensing_body
specialty
license_expires_at
status
verified_at
created_at
updated_at
```

## 25.4 partner_contacts

```text
id
partner_id
name
role
email
phone
preferred_language
contact_type
status
created_at
updated_at
```

## 25.5 partner_documents

```text
id
partner_id
document_type
file_path
file_name
mime_type
status
expiry_date nullable
reviewed_by nullable
reviewed_at nullable
review_notes nullable
created_at
updated_at
```

## 25.6 partner_agreements

```text
id
partner_id
agreement_type
version
status
effective_from
expires_at
signed_by_partner_at nullable
signed_by_opescare_at nullable
file_path nullable
created_at
updated_at
```

## 25.7 partner_contribution_permissions

```text
id
partner_id
facility_id nullable
contribution_type
allowed_data_categories_json
requires_review
requires_validation
effective_from
expires_at nullable
status
approved_by
created_at
updated_at
```

## 25.8 partner_access_permissions

```text
id
partner_id
facility_id nullable
access_type
allowed_data_scopes_json
purpose_allowed_json
requires_consent
effective_from
expires_at nullable
status
approved_by
created_at
updated_at
```

## 25.9 partner_integrations

```text
id
partner_id
facility_id nullable
integration_type
environment
status
allowed_scopes_json
webhook_domains_json nullable
rate_limit_policy
certified_at nullable
production_enabled_at nullable
created_at
updated_at
```

## 25.10 partner_contributions

```text
id
partner_id
facility_id nullable
contribution_type
source_system
resource_type
resource_id
status
quality_status
review_status
received_at
accepted_at nullable
rejected_at nullable
created_at
updated_at
```

## 25.11 partner_quality_scores

```text
id
partner_id
period_start
period_end
score
score_level
metrics_json
created_at
updated_at
```

## 25.12 partner_risk_scores

```text
id
partner_id
risk_level
risk_score
risk_factors_json
status
calculated_at
created_at
updated_at
```

## 25.13 partner_governance_cases

```text
id
uuid
partner_id
case_type
status
severity
assigned_to nullable
description
resolution_notes nullable
created_at
updated_at
closed_at nullable
```

---

# 26. API Endpoints

## 26.1 Partner Applications

```text
GET  /api/v1/partners
POST /api/v1/partners
GET  /api/v1/partners/{id}
PUT  /api/v1/partners/{id}
POST /api/v1/partners/{id}/submit
POST /api/v1/partners/{id}/approve
POST /api/v1/partners/{id}/reject
POST /api/v1/partners/{id}/suspend
POST /api/v1/partners/{id}/terminate
```

## 26.2 Partner Documents

```text
GET  /api/v1/partners/{id}/documents
POST /api/v1/partners/{id}/documents
POST /api/v1/partners/{id}/documents/{document_id}/verify
POST /api/v1/partners/{id}/documents/{document_id}/reject
```

## 26.3 Agreements

```text
GET  /api/v1/partners/{id}/agreements
POST /api/v1/partners/{id}/agreements
POST /api/v1/partners/{id}/agreements/{agreement_id}/mark-signed
POST /api/v1/partners/{id}/agreements/{agreement_id}/terminate
```

## 26.4 Permissions

```text
GET  /api/v1/partners/{id}/contribution-permissions
POST /api/v1/partners/{id}/contribution-permissions
POST /api/v1/partners/{id}/contribution-permissions/{permission_id}/revoke

GET  /api/v1/partners/{id}/access-permissions
POST /api/v1/partners/{id}/access-permissions
POST /api/v1/partners/{id}/access-permissions/{permission_id}/revoke
```

## 26.5 Integrations

```text
GET  /api/v1/partners/{id}/integrations
POST /api/v1/partners/{id}/integrations
POST /api/v1/partners/{id}/integrations/{integration_id}/certify
POST /api/v1/partners/{id}/integrations/{integration_id}/enable-production
POST /api/v1/partners/{id}/integrations/{integration_id}/suspend
```

## 26.6 Contributions

```text
GET  /api/v1/partners/{id}/contributions
GET  /api/v1/partners/{id}/contributions/{contribution_id}
POST /api/v1/partners/{id}/contributions/{contribution_id}/accept
POST /api/v1/partners/{id}/contributions/{contribution_id}/reject
POST /api/v1/partners/{id}/contributions/{contribution_id}/flag
```

## 26.7 Governance Cases

```text
GET  /api/v1/partner-governance/cases
POST /api/v1/partner-governance/cases
GET  /api/v1/partner-governance/cases/{id}
POST /api/v1/partner-governance/cases/{id}/assign
POST /api/v1/partner-governance/cases/{id}/resolve
POST /api/v1/partner-governance/cases/{id}/escalate
POST /api/v1/partner-governance/cases/{id}/close
```

## 26.8 Dashboards

```text
GET /api/v1/partner-governance/dashboard
GET /api/v1/partner-governance/risk-dashboard
GET /api/v1/partner-governance/quality-dashboard
GET /api/v1/partner-governance/integration-dashboard
```

---

# 27. UI Requirements

## 27.1 Admin Navigation

Add:

```text
Partner Governance
  - Dashboard
  - Partner Applications
  - Active Partners
  - Facilities
  - Professionals
  - Documents
  - Agreements
  - Permissions
  - Integrations
  - Contributions
  - Quality Scores
  - Risk Scores
  - Governance Cases
  - Alerts
```

## 27.2 Partner Profile Page

Sections:

```text
overview
verification status
trust level
documents
agreements
contacts
facilities
professionals
contribution permissions
access permissions
integrations
contribution history
quality score
risk score
audit history
governance cases
```

## 27.3 Partner Application Review Page

Show:

```text
partner details
partner type
documents
license status
requested contribution rights
requested access rights
risk warnings
review notes
approve/reject/request information buttons
```

## 27.4 Permission Matrix UI

Create permission matrix by partner type.

Rows:

```text
contribution types
access scopes
integration scopes
review rights
```

Columns:

```text
requested
approved
requires review
expires
status
```

## 27.5 Quality Dashboard UI

Show:

```text
quality score
rejection rate
duplicate rate
correction rate
stale stock rate
sync success rate
reconciliation cases
trend over time
```

## 27.6 Risk Dashboard UI

Show:

```text
risk level
risk factors
expired documents
expired agreements
security incidents
policy violations
suspension recommendations
```

---

# 28. Bilingual Requirements

All partner governance UI must support English and French.

Examples:

```text
Partner Governance → Gouvernance des partenaires
Partner Application → Demande de partenariat
Contribution Permissions → Autorisations de contribution
Access Permissions → Autorisations d’accès
Trust Level → Niveau de confiance
Risk Level → Niveau de risque
Quality Score → Score de qualité
Agreement Expired → Accord expiré
License Expired → Licence expirée
Partner Suspended → Partenaire suspendu
```

Use clear language. Avoid legal or technical jargon where a simple phrase is possible.

---

# 29. Security and Privacy Rules

## 29.1 Required Controls

```text
role-based access
facility-based access
trust-level checks
agreement checks
license expiry checks
permission checks
purpose-of-use checks
consent checks where patient data is involved
audit logs
document access control
API credential security
integration rate limits
```

## 29.2 Blocked Behaviors

Do not allow:

```text
unverified partner production access
expired agreement contribution
expired license contribution
research access without approval
insurance full timeline access by default
public health full record access by default
pharmacy full clinical record access by default
technology vendor production API access without certification
partner access to unrelated facilities
silent permission escalation
```

---

# 30. Error Codes

Use stable error codes.

```text
PARTNER_NOT_VERIFIED
PARTNER_SUSPENDED
PARTNER_AGREEMENT_REQUIRED
PARTNER_AGREEMENT_EXPIRED
PARTNER_LICENSE_EXPIRED
PARTNER_PERMISSION_REQUIRED
PARTNER_ACCESS_DENIED
PARTNER_CONTRIBUTION_NOT_ALLOWED
PARTNER_INTEGRATION_NOT_CERTIFIED
PARTNER_PRODUCTION_ACCESS_DENIED
PARTNER_DOCUMENT_REQUIRED
PARTNER_DOCUMENT_REJECTED
PARTNER_RISK_TOO_HIGH
PARTNER_TRUST_LEVEL_INSUFFICIENT
```

Example response:

```json
{
  "status": "rejected",
  "error_code": "PARTNER_AGREEMENT_REQUIRED",
  "message": "An active agreement is required before this partner can contribute this type of data.",
  "required_action": "complete_partner_agreement"
}
```

---

# 31. Testing Requirements

Required tests:

1. Unverified partner cannot access production.
2. Partner without agreement cannot contribute restricted data.
3. Expired license blocks contribution.
4. Expired agreement blocks relevant permission.
5. Suspended partner cannot contribute.
6. Suspended partner API credentials are revoked.
7. Hospital can contribute encounters when approved.
8. Pharmacy can sync stock only when approved.
9. Pharmacy cannot view full clinical timeline by default.
10. Lab can release results only with lab permission.
11. Insurance cannot access full patient timeline.
12. Research partner cannot access identifiable data without approval.
13. Public health partner sees aggregate reports by default.
14. Technology vendor cannot get production API without certification.
15. Contribution quality score updates after accepted/rejected contributions.
16. Risk score increases after policy violation.
17. Document expiry triggers alert.
18. Agreement expiry triggers alert.
19. Governance case can suspend partner.
20. Permission change is audited.
21. Trust level change is audited.
22. Partner status change is audited.
23. Integration production enablement requires certification.
24. Partner cannot access unrelated facility data.
25. French UI labels render without breaking layout.

---

# 32. Acceptance Criteria

This module is complete when:

1. Partner types are defined.
2. Contribution categories are defined.
3. Partner application workflow exists.
4. Partner document verification exists.
5. Partner agreements exist.
6. Partner trust levels exist.
7. Partner statuses exist.
8. Contribution permissions exist.
9. Data access permissions exist.
10. Integration permissions exist.
11. Partner dashboards exist.
12. Quality scoring exists.
13. Risk scoring exists.
14. Governance cases exist.
15. Partner suspension works.
16. Partner termination works.
17. Expired licenses trigger alerts.
18. Expired agreements trigger alerts.
19. Partner API access is controlled.
20. Partner data access is controlled by role, agreement, trust level, purpose, consent, and policy.
21. Public health partners do not see full records by default.
22. Insurance partners do not see full timelines by default.
23. Research partners require governance approval.
24. Technology vendors require sandbox/certification before production.
25. Every sensitive partner action is audited.
26. English and French UI labels exist.
27. Tests cover permission, privacy, expiry, suspension, and risk cases.

---

# 33. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md, and docs/partners/OPESCARE_PARTNER_CONTRIBUTION_GOVERNANCE.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS partner model, database, UI, permissions, or integration assumptions.

Task: Create the Partner Contribution & Governance Module foundation.

Scope:
1. Create module placeholder: app/Modules/Partners.
2. Create docs/partners folder if missing.
3. Add model placeholders:
   - Partner
   - PartnerFacility
   - PartnerProfessional
   - PartnerContact
   - PartnerDocument
   - PartnerAgreement
   - PartnerContributionPermission
   - PartnerAccessPermission
   - PartnerIntegration
   - PartnerContribution
   - PartnerQualityScore
   - PartnerRiskScore
   - PartnerGovernanceCase

4. Add route placeholders for:
   - partner applications
   - partner documents
   - agreements
   - contribution permissions
   - access permissions
   - integrations
   - contributions
   - governance cases
   - dashboards

5. Add policy placeholders:
   - PartnerPolicy
   - PartnerDocumentPolicy
   - PartnerAgreementPolicy
   - PartnerPermissionPolicy
   - PartnerIntegrationPolicy
   - PartnerGovernanceCasePolicy

6. Add service placeholders:
   - PartnerApplicationService
   - PartnerVerificationService
   - PartnerAgreementService
   - PartnerPermissionService
   - PartnerContributionService
   - PartnerQualityScoreService
   - PartnerRiskScoreService
   - PartnerIntegrationGovernanceService

7. Add audit event enum placeholders for all partner governance events.

8. Add error code enum placeholders.

9. Add admin navigation placeholder for Partner Governance.

10. Add tests proving:
   - unverified partner cannot access production
   - partner without agreement cannot contribute restricted data
   - expired license blocks contribution
   - suspended partner cannot contribute
   - pharmacy cannot view full clinical timeline by default
   - insurance cannot view full patient timeline by default
   - public health partner gets aggregate reports by default
   - production API access requires certification
   - permission changes are audited

11. Do not implement full clinical modules in this task.
12. Do not grant real partner access automatically.
13. Do not expose patient data in placeholder responses.
14. Open a PR with summary, files created, risks, tests, and next recommended tasks.
```

---

# 34. Final Rule

The Partner Contribution & Governance Module is how OpesCare keeps its ecosystem safe.

The correct model is:

```text
verify the partner
verify the agreement
verify the facility or professional
assign trust level
assign contribution permissions
assign access permissions
certify technical integrations
monitor data quality
monitor risk
audit every sensitive action
suspend when unsafe
```

No partner should contribute, access, review, export, or integrate health data unless their identity, agreement, purpose, permission, and governance status support that action.

If a partner’s role cannot be verified, governed, audited, and limited, the partner must not be allowed to operate inside OpesCare.
