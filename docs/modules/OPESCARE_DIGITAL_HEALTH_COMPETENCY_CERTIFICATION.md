# OpesCare Digital Health Competency & Certification Module PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Document Type:** Product Requirements + Technical Architecture + Governance + Curriculum Blueprint  
**Module Name:** OpesCare Digital Health Competency & Certification Module  
**Alternative Public Name:** OpesCare Academy  
**Build Direction:** Build from scratch or safely extend learning/onboarding modules  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Mobile App:** Flutter recommended  
**Verification:** QR-verifiable certificates  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, database structure, UI, training model, certificate format, or assumptions.  
**Regulatory Safety Rule:** OpesCare certification must never claim to license doctors, nurses, pharmacists, lab scientists, or any clinical professional. It certifies digital health workflow competency, EMR use, data privacy, Health ID workflows, interoperability basics, and safe platform operation only.

---

# 1. Purpose

The OpesCare Digital Health Competency & Certification Module exists to train, assess, certify, and track the ability of users and organizations to safely use OpesCare.

OpesCare is not only a software application. It is a digital health identity, EMR, interoperability, document verification, public health reporting, and care coordination infrastructure. Hospitals, clinics, labs, pharmacies, insurers, universities, students, nurses, doctors, administrators, developers, and public health partners will need structured training before using the system safely.

This module provides:

1. role-based training
2. structured onboarding
3. digital health competency courses
4. EMR workflow certification
5. Health ID workflow certification
6. privacy and consent certification
7. lab workflow certification
8. prescription and pharmacy workflow certification
9. public health reporting certification
10. interoperability/API/FHIR basics certification
11. facility administrator certification
12. student/supervised user training
13. quizzes and practical simulations
14. certificate issuance
15. QR-verifiable certificates
16. certificate renewal
17. certificate revocation
18. staff competency tracking
19. access-control linkage
20. Ministry/partner-ready governance

The goal is to make OpesCare safer, easier to adopt, easier to train, easier to audit, and easier to present to hospitals, universities, councils, and the Ministry of Public Health.

---

# 2. Positioning

Correct positioning:

```text
OpesCare certifies digital health system competency.
OpesCare certifies competence in using OpesCare workflows safely.
OpesCare certifies EMR workflow readiness.
OpesCare certifies privacy, consent, and digital documentation awareness.
```

Incorrect positioning:

```text
OpesCare certifies someone as a doctor.
OpesCare certifies someone as a nurse.
OpesCare gives a medical license.
OpesCare gives clinical authority.
OpesCare replaces professional councils, universities, or the Ministry of Public Health.
```

Recommended public wording:

```text
OpesCare Digital Health Competency Certification
```

Alternative public branding:

```text
OpesCare Academy
```

Avoid:

```text
OpesCare Medical License
OpesCare Doctor Certification
OpesCare Nurse Certification
OpesCare Clinical License
```

## 2.1 Regulatory Disclaimer

Every certificate, course, and public page must include:

```text
This certification confirms completion of OpesCare digital health workflow training. It does not replace professional licensing, clinical qualification, statutory registration, or authorization to practice medicine, nursing, pharmacy, laboratory science, or any regulated health profession.
```

French:

```text
Cette certification confirme l’achèvement d’une formation aux flux de travail numériques d’OpesCare. Elle ne remplace pas l’autorisation professionnelle, la qualification clinique, l’inscription réglementaire, ni le droit d’exercer la médecine, les soins infirmiers, la pharmacie, les sciences de laboratoire ou toute autre profession de santé réglementée.
```

---

# 3. Why This Module Is Needed

OpesCare introduces workflows that require safe use:

```text
Health ID lookup
Health ID verification
QR document verification
patient consent requests
emergency access
EMR documentation
lab test requests
lab result release
prescriptions
pharmacy dispensing
medicine stock updates
blood availability updates
insurance claim workflows
public health reporting
internal messaging
notifications and escalation
document templates
API integrations
audit logs
```

Without training and competency tracking, users may:

```text
access the wrong patient record
share sensitive data through unsafe channels
misuse emergency access
send incomplete public health reports
issue documents incorrectly
ignore critical alerts
misread lab result workflows
send prescriptions incorrectly
misuse patient consent
expose data to insurance/research partners incorrectly
break interoperability workflows
```

Therefore, this module is necessary, but it should be introduced in phases.

---

# 4. Phase Strategy

This module should be included in the product architecture from the beginning but rolled out carefully.

## 4.1 Phase 1: Foundation

Build now.

Scope:

```text
training resource pages
role-based onboarding checklists
short quizzes
completion badges
staff competency tracking
certificate data model
certificate QR verification structure
basic training dashboard
admin course manager placeholder
```

Phase 1 must not heavily market “certification” as a regulatory credential.

## 4.2 Phase 2: Full Role-Based Certification

Build after core OpesCare workflows are stable.

Scope:

```text
complete courses
role-specific exams
simulated workflow tasks
passing score
certificate issuance
QR-verifiable certificates
renewal rules
certificate expiry
certificate revocation
facility competency reports
```

## 4.3 Phase 3: Institutional Partnership

Build with partners.

Scope:

```text
university partnerships
teaching hospital pilots
Ministry training unit engagement
professional council review
CPD alignment where possible
trainer program
super user program
recognized training centers
```

## 4.4 Phase 4: National/Regional Recognition Track

Only after pilots and governance maturity.

Scope:

```text
formal endorsement request
public health workforce training
national digital health capacity-building
Ministry-reviewed curriculum
recognized certificate categories
national reporting/training dashboard
```

---

# 5. Certification Scope

OpesCare certifications should cover:

```text
EMR usage
Health ID workflow
patient consent
patient privacy
digital documentation
lab workflow
prescription workflow
pharmacy workflow
public health reporting workflow
interoperability basics
system administration
role-based platform operation
secure communication
document verification
```

OpesCare certifications must not cover:

```text
medical licensing
nursing licensing
pharmacy licensing
laboratory professional licensing
specialist qualification
clinical competence to practice independently
legal authorization to treat patients
```

---

# 6. Target User Groups

The module must support these learners:

```text
patients
guardians
doctors
student doctors
medical students
clinical interns
nurses
student nurses
midwives
pharmacists
pharmacy students
lab scientists
lab technicians
student lab staff
radiology staff
hospital administrators
clinic administrators
health organization administrators
insurance officers
public health officers
research users
developer/API users
facility IT administrators
OpesCare support/admin users
trainers/super users
```

---

# 7. Certification Tracks

## 7.1 OpesCare Digital Health Foundation

Audience:

```text
all users
patients optional simplified version
facility staff
students
administrators
partners
```

Covers:

```text
what OpesCare is
Health ID basics
patient privacy
consent basics
safe login
dashboard basics
notifications
document verification
what not to share by WhatsApp/email
basic support process
```

Purpose:

Every user understands the platform safely.

---

## 7.2 OpesCare EMR User Certification

Audience:

```text
doctors
nurses
clinical officers where applicable
clinic staff
hospital staff
medical students under supervision
```

Covers:

```text
patient search
Health ID verification
patient registration
consultation notes
clinical timeline
vital signs
prescriptions
lab requests
referrals
document generation
access logs
safe record handling
```

Practical exercises:

```text
find patient by Health ID
request access
open authorized record
create consultation note
order lab test
generate consultation summary
review access log
```

---

## 7.3 OpesCare Doctor / Clinical Provider Workflow Certification

Audience:

```text
licensed doctors
specialists
clinical providers authorized by facility/policy
```

Covers:

```text
consent request workflow
clinical documentation
diagnosis documentation
lab ordering
prescription issuing
referrals
reviewing lab results
critical alert acknowledgement
discharge summaries
medical certificates
emergency access rules
secure messaging with care team
```

Practical exercises:

```text
request patient consent
create consultation note
order lab test
review lab result
issue prescription
create referral
acknowledge critical alert
generate verifiable document
```

Important rule:

This certification does not certify clinical competence. It certifies platform workflow competence.

---

## 7.4 OpesCare Nursing Digital Workflow Certification

Audience:

```text
nurses
student nurses
midwives
ward staff
triage staff
```

Covers:

```text
triage workflow
vital signs entry
nursing notes
ward task notifications
care tasks
patient identification
medication administration recording where applicable
care team messaging
escalation alerts
privacy rules
```

Practical exercises:

```text
verify patient Health ID
record vital signs
respond to care task
acknowledge assigned alert
message doctor in care-team thread
record nursing note
document medication administration where facility policy allows
```

---

## 7.5 OpesCare Student Clinical User Certification

Audience:

```text
medical students
student doctors
clinical interns
student nurses
student lab staff
student pharmacists
```

Covers:

```text
digital patient record basics
read-only and supervised workflow
clinical documentation etiquette
Health ID use
consent basics
case review workflows
professional boundaries
privacy and audit awareness
supervision rules
```

Access restrictions:

```text
students cannot prescribe
students cannot validate lab results
students cannot sign discharge summaries
students cannot issue medical certificates
students cannot use emergency access without supervised policy
students cannot access records outside assigned teaching/care context
```

Practical exercises:

```text
view assigned teaching case
identify privacy restrictions
write draft note for supervisor review
review simulated clinical timeline
answer audit/privacy questions
```

---

## 7.6 OpesCare Laboratory Digital Workflow Certification

Audience:

```text
lab technicians
lab scientists
lab validators
student lab staff
lab administrators
```

Covers:

```text
lab order workflow
sample collection
specimen chain-of-custody
sample rejection
result entry
result validation
critical result alerts
amendment rules
LOINC/local code mapping basics
QR-verifiable lab reports
public health lab surveillance basics
```

Practical exercises:

```text
receive lab order
record sample collection
record sample condition
enter result
validate result
release result
amend released result with reason
acknowledge critical result workflow
```

---

## 7.7 OpesCare Pharmacy Digital Workflow Certification

Audience:

```text
pharmacists
pharmacy attendants
pharmacy students
hospital pharmacy staff
pharmacy administrators
```

Covers:

```text
prescription verification
QR prescription checks
dispensing records
medicine stock updates
medicine reservation workflow
batch/lot/expiry data
GTIN/local code fields
substitution notes
controlled medicine caution
patient privacy
```

Practical exercises:

```text
verify prescription
dispense medication
record batch/lot/expiry where available
handle substitution request
update medicine stock
respond to reservation
protect patient privacy
```

---

## 7.8 OpesCare Data Protection, Privacy & Consent Certification

Audience:

```text
all staff
all clinical users
all administrators
all partners
developers/API users
public health users
insurance users
research users
```

Covers:

```text
patient consent
minimum necessary access
role-based access
facility-based access
emergency access rules
audit logs
data sharing
secure messaging
safe notifications
document verification
breach reporting
what not to send externally
patient rights
```

Practical exercises:

```text
approve/deny consent scenario
identify unsafe WhatsApp message
handle emergency access scenario
review audit log
choose minimum necessary dataset
report suspicious access
```

Mandatory rule:

No production access to sensitive patient data should be granted until this certification or equivalent internal training is completed.

---

## 7.9 OpesCare Public Health Reporting Certification

Audience:

```text
public health officers
hospital reporting staff
district health staff
lab surveillance teams
pharmacy stock reporting users
blood bank reporting users
```

Covers:

```text
notifiable disease reporting
aggregate reporting
de-identification
lab surveillance
medicine stock-out reporting
blood shortage reporting
public health dashboards
report approval workflows
data quality
```

Practical exercises:

```text
create report draft
review aggregate data
identify identifiable data risk
approve simulated report
submit simulated public health report
handle rejected report
```

---

## 7.10 OpesCare Interoperability & FHIR Basics Certification

Audience:

```text
developers
hospital IT staff
HIS vendors
LIS vendors
pharmacy software vendors
insurance system vendors
technical administrators
```

Covers:

```text
Health ID API
patient lookup
consent API
FHIR basics
Patient resource
Observation
DiagnosticReport
MedicationRequest
ServiceRequest
DocumentReference
Consent
webhooks
SDK usage
Bridge Agent
API security
data mapping
idempotency
reconciliation
error handling
```

Practical exercises:

```text
verify Health ID through API
request consent
push lab result
push prescription
handle webhook
map local test code
handle reconciliation error
retry failed sync
```

---

## 7.11 OpesCare Facility Administrator Certification

Audience:

```text
hospital admins
clinic admins
health organization admins
department administrators
OpesCare internal admins
```

Covers:

```text
facility setup
departments
staff roles
permissions
audit review
dashboard monitoring
partner permissions
document templates
notification/escalation configuration
integration monitoring
demo mode controls
```

Practical exercises:

```text
create facility department
assign staff role
review audit log
configure escalation chain
review document template status
monitor failed integration
suspend unsafe user
```

---

## 7.12 OpesCare Super User / Trainer Certification

Audience:

```text
facility super users
trainers
implementation champions
OpesCare trainers
teaching hospital trainers
```

Covers:

```text
all foundation workflows
role-based training delivery
simulation management
user support
issue escalation
training reports
certificate verification
competency tracking
training ethics
```

Practical exercises:

```text
train simulated staff group
review trainee progress
resolve common workflow issue
issue training completion recommendation
generate facility competency report
```

---

## 7.13 OpesCare Implementation Lead Certification

Audience:

```text
deployment leads
project managers
hospital IT leads
OpesCare implementation team
partner implementation leads
```

Covers:

```text
implementation planning
facility onboarding
data migration basics
workflow mapping
training planning
go-live readiness
support process
risk management
security readiness
integration readiness
```

Practical exercises:

```text
create implementation checklist
map facility departments
define training schedule
review go-live readiness
identify risk gaps
prepare post-launch support plan
```

---

## 7.14 OpesCare Insurance Workflow Certification

Audience:

```text
insurance officers
facility billing teams
claim reviewers
health plan administrators
```

Covers:

```text
eligibility checks
preauthorization
claim submission
minimum necessary access
claim documents
patient privacy
claim status updates
secure facility-insurance messaging
```

Practical exercises:

```text
review claim
request missing information
approve/reject simulated claim
identify over-access risk
send claim query message
```

---

# 8. Certification Levels

Use levels to make the system scalable and professional.

```text
Level 1: Foundation
Level 2: Role-Based Operator
Level 3: Advanced Workflow User
Level 4: Trainer / Super User
Level 5: Implementation Lead
```

## 8.1 Level 1: Foundation

Basic safe platform use.

## 8.2 Level 2: Role-Based Operator

Can perform routine tasks in assigned role.

## 8.3 Level 3: Advanced Workflow User

Can handle complex workflows, exceptions, and escalations.

## 8.4 Level 4: Trainer / Super User

Can train others and support facility adoption.

## 8.5 Level 5: Implementation Lead

Can lead deployment and governance rollout.

---

# 9. Course Structure

Each course must have:

```text
course title
course code
level
target audience
prerequisites
duration
learning objectives
lessons
reading materials
videos
practical simulations
quizzes
final exam
passing score
certificate validity
renewal requirement
trainer qualification
regulatory disclaimer
```

## 9.1 Course Statuses

```text
draft
in_review
approved
published
archived
retired
```

## 9.2 Lesson Types

```text
reading
video
interactive walkthrough
simulation
case scenario
quiz
practical assessment
final exam
```

## 9.3 Assessment Types

```text
multiple choice
scenario-based quiz
simulation task
document review exercise
workflow completion
supervisor sign-off
final exam
```

---

# 10. Practical Simulation System

Certifications should not rely on theory only.

## 10.1 Simulation Requirements

The module must support:

```text
demo patient
demo Health ID
demo facility
demo doctor
demo nurse
demo lab result
demo prescription
demo consent request
demo critical alert
demo pharmacy stock
demo public health report
demo API client
```

## 10.2 Simulation Scoring

Track:

```text
task completed
task failed
privacy violation
wrong patient selected
wrong role action attempted
time taken
number of attempts
score
feedback
```

## 10.3 Simulation Failure Examples

Fail if learner:

```text
opens wrong patient record
tries to prescribe as student
shares result through unsafe channel
uses emergency access without reason
validates lab result without permission
sends identifiable data to public health report by default
```

---

# 11. Exam and Scoring Rules

## 11.1 Passing Scores

Recommended:

```text
Foundation: 70%
Role-Based Operator: 75%
Advanced Workflow User: 80%
Trainer/Super User: 85%
Implementation Lead: 85%
Privacy & Consent Certification: 85%
```

## 11.2 Attempt Rules

```text
maximum attempts per exam
cooldown after failed attempts
review materials after failure
trainer review for repeated failure
```

## 11.3 Exam Integrity

Support:

```text
question randomization
question bank
time limit
attempt tracking
IP/device log
anti-copy warning
manual review for suspicious attempts
```

## 11.4 Practical Assessment

Some courses must require practical simulation completion before certificate issuance.

Examples:

```text
doctor workflow
nursing workflow
lab workflow
pharmacy workflow
public health reporting
interoperability/API
facility administration
```

---

# 12. Certificate Issuance

Every certificate must be verifiable.

## 12.1 Certificate Fields

```text
certificate number
verification QR code
verification code
holder name
holder role/category
course name
course code
certification level
score or pass status
issue date
expiry date
issuer
status
regulatory disclaimer
```

## 12.2 Certificate Number Format

Recommended:

```text
CERT-{COUNTRY}-{TRACK}-{YEAR}-{RANDOM}-{CHECK}
```

Examples:

```text
CERT-CM-EMR-2026-8KQ2-MN7P
CERT-CM-NURSEWF-2026-P7AA-Q92D
CERT-CM-PRIVACY-2026-KD88-RT6M
CERT-CM-FHIR-2026-MP42-X8D1
```

## 12.3 Certificate Verification QR

QR points to:

```text
https://opescare.com/verify/certificate/{token}
```

QR must not contain:

```text
full identity document
private user profile
exam answers
internal user ID
sensitive employment record
```

## 12.4 Public Certificate Verification Page

May show:

```text
certificate valid yes/no
holder name or masked holder name depending policy
course name
level
issue date
expiry date
status
issuer
```

Must not show:

```text
exam answers
private staff data
internal HR data
disciplinary history
full user account details
```

## 12.5 Certificate Statuses

```text
issued
active
expired
revoked
suspended
superseded
entered_in_error
```

## 12.6 Certificate Revocation

Certificate can be revoked if:

```text
fraudulent completion
identity mismatch
serious platform misuse
professional status problem where relevant to access
security violation
course retired and replacement required
issued in error
```

Revocation requires:

```text
reason
authorized reviewer
audit event
notification to holder
```

---

# 13. Certificate Renewal

Certificates should not last forever.

## 13.1 Validity Period

Recommended:

```text
Foundation: 2 years
Privacy & Consent: 1 year
Role-Based Clinical Workflows: 1–2 years
Interoperability/API: 1 year
Super User/Trainer: 1 year
Implementation Lead: 2 years
```

## 13.2 Renewal Requirements

```text
short refresher course
updated policy review
new quiz
practical refresh where needed
accept new privacy/security rules
```

## 13.3 Renewal Alerts

Send reminders:

```text
60 days before expiry
30 days before expiry
7 days before expiry
on expiry
```

---

# 14. Access Control Linkage

Certification can be linked to system access.

## 14.1 Rule

Some system permissions should require completed training.

Examples:

```text
production sensitive patient access requires privacy training
lab result validation requires lab workflow training
prescription issuing requires provider role and prescription workflow training
facility admin rights require facility administrator training
public health reporting approval requires public health reporting training
API production access requires interoperability/API training
```

## 14.2 Access Gates

Access gate checks:

```text
user role
facility role
professional verification
certificate status
certificate expiry
course requirement
country/facility policy
```

## 14.3 Important Safety Rule

Certification alone must never grant clinical authority.

Example:

A user cannot prescribe just because they passed the prescription workflow course. They must also be:

```text
licensed/authorized professional
assigned correct facility role
allowed by facility/country policy
granted system permission
```

---

# 15. Student and Supervised User Rules

Student users require strict boundaries.

## 15.1 Student Role Restrictions

Student users cannot:

```text
issue prescriptions
validate lab results
sign discharge summaries
sign medical certificates
approve public health reports
use emergency access independently
access unrelated patient records
export records
```

## 15.2 Student Allowed Actions

Student users may:

```text
view assigned teaching cases
complete simulations
write draft notes for supervisor review
participate in training scenarios
view anonymized/de-identified learning records
complete quizzes and exams
```

## 15.3 Supervisor Approval

Some practical assessments require:

```text
supervisor review
supervisor sign-off
training coordinator approval
```

---

# 16. Ministry and Institutional Adoption Requirements

To be considered by the Ministry of Public Health or institutional partners, the module must cover governance, safety, and curriculum structure.

## 16.1 Governance Structure

Create:

```text
certification board
curriculum review committee
clinical advisory committee
privacy/legal review group
technical standards review group
exam integrity officer/group
appeals committee
```

## 16.2 Curriculum Governance

Every course must have:

```text
learning objectives
target audience
scope limits
prerequisites
module outline
practical exercises
assessment method
passing score
certificate validity
renewal requirement
review date
approved by
```

## 16.3 Clinical Safety Boundaries

Every course must clearly state:

```text
this certification does not authorize clinical practice
this certification does not replace professional license
this certification does not replace university qualification
this certification does not replace Ministry or council approval
this certification only confirms OpesCare digital workflow competency
```

## 16.4 Privacy and Data Protection

Every certification track must include:

```text
patient consent
minimum necessary access
audit logs
data sharing rules
secure messaging
safe external notification rules
document verification
incident reporting
breach response basics
```

## 16.5 Public Health Reporting Alignment

For Ministry adoption, include:

```text
notifiable disease reporting
aggregate reporting
surveillance dashboards
medicine stock-outs
blood availability
facility reporting completeness
data quality
de-identification
report approval workflow
```

## 16.6 Interoperability Alignment

For technical credibility, include:

```text
Health ID lookup
FHIR basics
DocumentReference
DiagnosticReport
Observation
MedicationRequest
ServiceRequest
Consent
webhooks
API scopes
Bridge Agent
data mapping
reconciliation
```

## 16.7 Training Partnerships

Recommended partners:

```text
teaching hospitals
medical schools
nursing schools
pharmacy schools
public health schools
professional councils
hospital associations
health universities
Ministry training units
district health offices
health professional boards
```

## 16.8 Ministry Engagement Strategy

Do not first ask the Ministry to recognize OpesCare as a licensing body.

Instead ask for:

```text
pilot approval
curriculum review
digital health training partnership
CPD alignment discussion
endorsement for platform-use training
public health reporting training collaboration
teaching hospital pilot
```

---

# 17. Course Catalog V1

Initial recommended courses:

```text
OPC-FOUND-101: OpesCare Digital Health Foundation
OPC-PRIV-101: OpesCare Data Protection, Privacy & Consent
OPC-EMR-101: OpesCare EMR User Foundation
OPC-CLIN-201: Clinical Provider Workflow
OPC-NURSE-201: Nursing Digital Workflow
OPC-STUDENT-101: Student Clinical User Safety
OPC-LAB-201: Laboratory Digital Workflow
OPC-PHARM-201: Pharmacy Digital Workflow
OPC-PH-201: Public Health Reporting
OPC-FHIR-201: Interoperability & FHIR Basics
OPC-ADMIN-201: Facility Administrator Workflow
OPC-SUPER-401: Super User / Trainer
OPC-IMPL-501: Implementation Lead
```

---

# 18. Course Content Details

## 18.1 OPC-FOUND-101: Digital Health Foundation

Modules:

```text
What is OpesCare?
Health ID basics
Patient privacy basics
Dashboard navigation
Notifications and tasks
Document verification
Basic support and safety
```

Assessment:

```text
10–20 question quiz
basic platform walkthrough
```

## 18.2 OPC-PRIV-101: Data Protection, Privacy & Consent

Modules:

```text
patient consent
minimum necessary access
emergency access
audit logs
secure messaging
safe notifications
document sharing
incident reporting
```

Assessment:

```text
scenario questions
unsafe message identification
consent simulation
```

## 18.3 OPC-EMR-101: EMR User Foundation

Modules:

```text
patient lookup
Health ID verification
clinical timeline
documents
basic record workflows
access logs
```

Assessment:

```text
workflow simulation
quiz
```

## 18.4 OPC-CLIN-201: Clinical Provider Workflow

Modules:

```text
consultation notes
lab orders
prescriptions
referrals
lab result review
critical alerts
clinical documents
```

Assessment:

```text
simulated consultation
simulated lab order
simulated prescription
critical alert acknowledgement
```

## 18.5 OPC-NURSE-201: Nursing Digital Workflow

Modules:

```text
triage
vitals
nursing notes
tasks
ward communication
care escalation
```

Assessment:

```text
vitals entry simulation
task completion simulation
care-team messaging scenario
```

## 18.6 OPC-LAB-201: Laboratory Digital Workflow

Modules:

```text
lab orders
specimen chain-of-custody
result entry
validation
critical results
amendments
LOINC/local mapping basics
```

Assessment:

```text
sample processing simulation
result validation simulation
amendment scenario
```

## 18.7 OPC-PHARM-201: Pharmacy Digital Workflow

Modules:

```text
prescription verification
dispensing
medicine reservation
stock updates
batch/lot/expiry
substitution notes
privacy
```

Assessment:

```text
prescription verification simulation
dispense simulation
stock update simulation
```

## 18.8 OPC-PH-201: Public Health Reporting

Modules:

```text
aggregate reports
notifiable disease workflow
lab surveillance
medicine stock-outs
blood shortage reporting
approval workflow
```

Assessment:

```text
report creation simulation
de-identification scenario
approval workflow
```

## 18.9 OPC-FHIR-201: Interoperability & FHIR Basics

Modules:

```text
Health ID API
FHIR resource basics
Patient
Observation
DiagnosticReport
MedicationRequest
ServiceRequest
DocumentReference
Consent
webhooks
Bridge Agent
reconciliation
```

Assessment:

```text
API simulation
mapping exercise
webhook failure exercise
```

---

# 19. Training and Certification UI

## 19.1 Learner Dashboard

Show:

```text
assigned courses
completed courses
certificates
expiry dates
renewal requirements
progress percentage
recommended next course
badges
simulation scores
```

## 19.2 Course Player

Features:

```text
lesson list
progress tracking
video/reading content
quiz blocks
simulation launch
notes
resources
completion status
```

## 19.3 Exam UI

Features:

```text
timer
question navigation
scenario blocks
submit confirmation
score page
feedback
retry rules
```

## 19.4 Certificate Page

Show:

```text
certificate
QR verification
certificate number
course
level
holder
issue date
expiry date
status
download PDF
share certificate
```

## 19.5 Admin Training Dashboard

Show:

```text
users enrolled
completion rates
failed attempts
certificate expiry
facility competency coverage
role readiness
privacy certification compliance
training gaps
```

## 19.6 Facility Competency Dashboard

For hospital/clinic admins:

```text
staff training status
required certifications by role
expired certificates
users blocked by missing training
department readiness
go-live readiness
```

## 19.7 Admin Course Builder

```text
course metadata
modules
lessons
quizzes
simulations
passing score
certificate validity
publish/archive
preview
```

---

# 20. Certificate Template Requirements

Certificates must be professional and verifiable.

## 20.1 Certificate Design

Include:

```text
OpesCare Academy
certificate title
holder name
course name
course code
certification level
certificate number
verification QR code
verification code
issue date
expiry date
status
issuer
disclaimer
```

## 20.2 Certificate Footer

English:

```text
This certificate confirms completion of OpesCare digital health workflow training. It does not replace professional licensing, clinical qualification, statutory registration, or authorization to practice a regulated health profession.
```

French:

```text
Ce certificat confirme l’achèvement d’une formation aux flux de travail numériques d’OpesCare. Il ne remplace pas l’autorisation professionnelle, la qualification clinique, l’inscription réglementaire ni le droit d’exercer une profession de santé réglementée.
```

## 20.3 Certificate Verification Page

Public page shows:

```text
certificate valid yes/no
course name
level
holder name or masked name depending policy
issue date
expiry date
status
issuer
disclaimer
```

Does not show:

```text
exam answers
internal account information
private staff data
disciplinary details
full employment records
```

---

# 21. Badges and Micro-Credentials

In addition to full certificates, use badges for smaller achievements.

Examples:

```text
Health ID Basics Badge
Consent Safety Badge
Secure Messaging Badge
Document Verification Badge
Lab Result Review Badge
Prescription Workflow Badge
API Sandbox Badge
```

Badges should not replace full certification where certification is required for access.

---

# 22. Notifications

Certification module must send notifications for:

```text
course assigned
course started
course completed
exam failed
exam passed
certificate issued
certificate expiring
certificate expired
certificate revoked
renewal required
trainer feedback received
```

Notifications must follow OpesCare communication safety rules.

---

# 23. Audit Requirements

Audit events:

```text
course_created
course_published
course_archived
user_enrolled
lesson_completed
quiz_started
quiz_submitted
simulation_started
simulation_completed
exam_started
exam_submitted
exam_passed
exam_failed
certificate_issued
certificate_renewed
certificate_expired
certificate_revoked
certificate_verified_publicly
certificate_downloaded
certificate_shared
access_granted_after_certification
access_blocked_missing_certification
```

Audit fields:

```text
actor_id
learner_id
course_id
certificate_id nullable
action
old_value nullable
new_value nullable
score nullable
ip_address
user_agent
timestamp
reason nullable
```

---

# 24. Data Models

## 24.1 courses

```text
id
uuid
course_code
title
description
level
target_audience_json
prerequisites_json
status
language
validity_months
passing_score
requires_simulation
requires_supervisor_signoff
created_by
approved_by nullable
published_at nullable
created_at
updated_at
```

## 24.2 course_modules

```text
id
course_id
title
description
sort_order
status
created_at
updated_at
```

## 24.3 lessons

```text
id
course_module_id
lesson_type
title
content
video_url nullable
resource_url nullable
sort_order
estimated_minutes
status
created_at
updated_at
```

## 24.4 quizzes

```text
id
course_id
title
passing_score
time_limit_minutes nullable
max_attempts nullable
status
created_at
updated_at
```

## 24.5 quiz_questions

```text
id
quiz_id
question_type
question_text
options_json
correct_answer_json
explanation
points
sort_order
created_at
updated_at
```

## 24.6 course_enrollments

```text
id
user_id
course_id
status
progress_percentage
started_at nullable
completed_at nullable
expires_at nullable
created_at
updated_at
```

## 24.7 lesson_progress

```text
id
user_id
lesson_id
status
completed_at nullable
created_at
updated_at
```

## 24.8 quiz_attempts

```text
id
user_id
quiz_id
attempt_number
score
status
started_at
submitted_at nullable
created_at
updated_at
```

## 24.9 simulation_attempts

```text
id
user_id
course_id
simulation_type
score
status
mistakes_json
started_at
completed_at nullable
created_at
updated_at
```

## 24.10 certificates

```text
id
uuid
certificate_number
verification_code
user_id
course_id
level
status
score nullable
issued_at
expires_at nullable
revoked_at nullable
revocation_reason nullable
certificate_pdf_path nullable
certificate_hash nullable
created_at
updated_at
```

## 24.11 certificate_verification_tokens

```text
id
certificate_id
token_hash
status
expires_at nullable
revoked_at nullable
last_used_at nullable
created_at
updated_at
```

## 24.12 certificate_verification_events

```text
id
certificate_id nullable
verification_code nullable
token_hash nullable
result
ip_address
user_agent
verified_by_user_id nullable
public_verification
created_at
```

## 24.13 competency_requirements

```text
id
role_name
permission_name nullable
course_id
required
effective_from
expires_at nullable
created_at
updated_at
```

## 24.14 trainer_signoffs

```text
id
learner_id
course_id
trainer_id
status
notes nullable
signed_at nullable
created_at
updated_at
```

---

# 25. API Endpoints

## 25.1 Learner

```text
GET  /api/v1/academy/courses
GET  /api/v1/academy/courses/{id}
POST /api/v1/academy/courses/{id}/enroll
POST /api/v1/academy/lessons/{id}/complete
GET  /api/v1/academy/my-progress
GET  /api/v1/academy/my-certificates
GET  /api/v1/academy/certificates/{id}/download
```

## 25.2 Quizzes and Exams

```text
POST /api/v1/academy/quizzes/{id}/start
POST /api/v1/academy/quizzes/{id}/submit
GET  /api/v1/academy/quiz-attempts/{id}
```

## 25.3 Simulations

```text
POST /api/v1/academy/simulations/{course_id}/start
POST /api/v1/academy/simulations/{attempt_id}/submit
GET  /api/v1/academy/simulations/{attempt_id}/result
```

## 25.4 Certificates

```text
GET  /verify/certificate/{token}
POST /api/v1/certificate-verification/verify-code
POST /api/v1/admin/certificates/{id}/revoke
POST /api/v1/admin/certificates/{id}/renew
```

## 25.5 Admin

```text
GET  /api/v1/admin/academy/courses
POST /api/v1/admin/academy/courses
PUT  /api/v1/admin/academy/courses/{id}
POST /api/v1/admin/academy/courses/{id}/publish
POST /api/v1/admin/academy/courses/{id}/archive
GET  /api/v1/admin/academy/enrollments
POST /api/v1/admin/academy/enrollments
GET  /api/v1/admin/academy/competency-dashboard
GET  /api/v1/admin/academy/facility-readiness
POST /api/v1/admin/academy/competency-requirements
```

---

# 26. Permissions

## 26.1 Learner Permissions

```text
academy.view_courses
academy.enroll
academy.take_quiz
academy.take_simulation
academy.view_own_certificates
academy.download_own_certificate
```

## 26.2 Admin Permissions

```text
academy.manage_courses
academy.publish_courses
academy.manage_enrollments
academy.view_reports
academy.revoke_certificates
academy.manage_competency_requirements
academy.manage_trainer_signoffs
```

## 26.3 Trainer Permissions

```text
academy.view_assigned_learners
academy.review_simulation
academy.signoff_practical
academy.provide_feedback
```

---

# 27. Bilingual Requirements

The module must support English and French.

Each course must support:

```text
English title
French title
English lessons
French lessons
English quiz questions
French quiz questions
English certificate labels
French certificate labels
English disclaimer
French disclaimer
```

Core labels:

```text
Course → Cours
Certificate → Certificat
Certification Level → Niveau de certification
Issued Date → Date d’émission
Expiry Date → Date d’expiration
Verify Certificate → Vérifier le certificat
Passing Score → Score de réussite
Practical Simulation → Simulation pratique
This is not a professional license → Ceci n’est pas une autorisation professionnelle
```

---

# 28. UI Requirements

## 28.1 Public Academy Page

Sections:

```text
what OpesCare Academy is
what certification means
what certification does not mean
available tracks
institutional training option
certificate verification
contact for partnerships
```

## 28.2 Learner Dashboard

```text
assigned courses
recommended courses
progress
certificates
expiring certificates
badges
next action
```

## 28.3 Course Detail Page

```text
course overview
target audience
prerequisites
modules
duration
assessment method
validity
disclaimer
enroll button
```

## 28.4 Certificate Viewer

```text
certificate preview
QR code
certificate number
status
download
share
verification link
```

## 28.5 Facility Competency Dashboard

```text
staff list
role
required courses
completed courses
expired certificates
blocked permissions
department readiness
go-live readiness
```

## 28.6 Admin Course Builder

```text
course metadata
modules
lessons
quizzes
simulations
passing score
certificate validity
publish/archive
preview
```

---

# 29. Certificate Document Template

Certificate must be PDF and web-verifiable.

Required fields:

```text
OpesCare Academy
certificate title
holder name
course name
course code
certification level
certificate number
verification QR code
verification code
issue date
expiry date
status
issuer
disclaimer
```

Watermarks:

```text
VALID
EXPIRED
REVOKED
SUPERSEDED
ENTERED IN ERROR
```

---

# 30. Error Codes

```text
COURSE_NOT_FOUND
COURSE_NOT_PUBLISHED
COURSE_ACCESS_DENIED
COURSE_PREREQUISITE_REQUIRED
QUIZ_NOT_FOUND
QUIZ_ATTEMPT_LIMIT_REACHED
QUIZ_TIME_EXPIRED
SIMULATION_NOT_FOUND
SIMULATION_REQUIRED
SUPERVISOR_SIGNOFF_REQUIRED
CERTIFICATE_NOT_FOUND
CERTIFICATE_EXPIRED
CERTIFICATE_REVOKED
CERTIFICATE_VERIFICATION_FAILED
CERTIFICATE_TOKEN_EXPIRED
CERTIFICATION_REQUIRED_FOR_PERMISSION
PROFESSIONAL_LICENSE_REQUIRED
```

---

# 31. Testing Requirements

Required tests:

1. Course can be created.
2. Course can be published.
3. Learner can enroll.
4. Lesson progress is tracked.
5. Quiz can be submitted.
6. Passing score issues completion status.
7. Failed quiz does not issue certificate.
8. Simulation requirement blocks certificate until completed.
9. Supervisor signoff blocks certificate where required.
10. Certificate number is generated.
11. Certificate QR is generated.
12. Certificate token is stored hashed.
13. Public certificate verification works.
14. Revoked certificate shows revoked status.
15. Expired certificate shows expired status.
16. Certificate disclaimer appears.
17. Certificate does not claim professional licensing.
18. Missing privacy certification can block sensitive access.
19. Student role cannot unlock prescribing permission from certificate alone.
20. Facility admin can view staff competency dashboard.
21. French labels render.
22. Audit events are created.
23. Certificate renewal reminder can be generated.
24. Certificate revocation requires reason.
25. API/FHIR course completion does not automatically grant production API credentials.

---

# 32. Acceptance Criteria

This module is complete when:

1. Certification is positioned as digital health competency, not professional licensing.
2. Regulatory disclaimer exists everywhere necessary.
3. Role-based course tracks exist.
4. Student/supervised user track exists.
5. Privacy and consent certification exists.
6. EMR user certification exists.
7. Nursing workflow certification exists.
8. Clinical provider workflow certification exists.
9. Lab workflow certification exists.
10. Pharmacy workflow certification exists.
11. Public health reporting certification exists.
12. Interoperability/API/FHIR basics certification exists.
13. Facility administrator certification exists.
14. Super user/trainer certification exists.
15. Implementation lead certification exists.
16. Courses support lessons, quizzes, simulations, and final exams.
17. Practical simulations exist for role-based workflows.
18. Certificate issuance exists.
19. Certificates are QR-verifiable.
20. Certificates expire and can be renewed.
21. Certificates can be revoked.
22. Certificate public verification is privacy-safe.
23. Training completion can be linked to system access gates.
24. Certification alone never grants clinical authority.
25. Student restrictions are enforced.
26. Facility competency dashboard exists.
27. Admin course builder exists.
28. English and French support exists.
29. Audit logs exist.
30. Tests cover certification, access gating, certificate verification, expiry, revocation, and regulatory safety.

---

# 33. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md, docs/identity/OPESCARE_MEDICAL_ID_SYSTEM_FINAL.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, docs/communications/OPESCARE_COMMUNICATION_ALERTS_TASKS_MESSAGING_SYSTEM.md, docs/documents/OPESCARE_VERIFIABLE_DOCUMENT_TEMPLATES_V2.md, and docs/certification/OPESCARE_DIGITAL_HEALTH_COMPETENCY_CERTIFICATION.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS training/certification code, UI, database, certificate format, or assumptions.

Task: Create the OpesCare Digital Health Competency & Certification Module foundation.

Scope:
1. Create module placeholder: app/Modules/Academy.
2. Create docs/certification folder if missing.
3. Add model placeholders:
   - Course
   - CourseModule
   - Lesson
   - Quiz
   - QuizQuestion
   - CourseEnrollment
   - LessonProgress
   - QuizAttempt
   - SimulationAttempt
   - Certificate
   - CertificateVerificationToken
   - CertificateVerificationEvent
   - CompetencyRequirement
   - TrainerSignoff

4. Add services:
   - CourseService
   - EnrollmentService
   - QuizService
   - SimulationService
   - CertificateService
   - CertificateVerificationService
   - CompetencyGateService
   - AcademyReportingService

5. Add routes for:
   - course catalog
   - course detail
   - enrollment
   - lesson progress
   - quiz start/submit
   - simulation attempts
   - certificates
   - certificate verification
   - admin course manager
   - competency dashboard

6. Add certificate verification:
   - QR points to /verify/certificate/{token}
   - token stored hashed
   - certificate has status and expiry

7. Add certificate disclaimer:
   - does not replace professional licensing
   - does not authorize clinical practice
   - confirms digital health workflow competency only

8. Add initial course placeholders:
   - Digital Health Foundation
   - Data Protection, Privacy & Consent
   - EMR User Foundation
   - Clinical Provider Workflow
   - Nursing Digital Workflow
   - Student Clinical User Safety
   - Laboratory Digital Workflow
   - Pharmacy Digital Workflow
   - Public Health Reporting
   - Interoperability & FHIR Basics
   - Facility Administrator Workflow

9. Add competency gate placeholders:
   - sensitive patient access requires privacy certification
   - production API access requires interoperability training
   - lab validation requires lab workflow training plus role permission
   - prescribing requires clinical role permission plus workflow training
   - certification alone never grants clinical authority

10. Add tests proving:
   - certificate QR is generated
   - certificate token is stored hashed
   - revoked certificate verifies as revoked
   - expired certificate verifies as expired
   - certificate disclaimer appears
   - certification does not grant professional license
   - student cannot prescribe even after course completion
   - missing privacy certification blocks sensitive access
   - French labels render
   - audit events are created

11. Do not implement full clinical modules in this task.
12. Do not expose patient data in placeholder responses.
13. Open a PR with summary, files created, screenshots, tests, risks, and next recommended tasks.
```

---

# 34. Final Rule

OpesCare certification must build trust without crossing regulatory boundaries.

The correct model is:

```text
certify digital health competency
certify OpesCare workflow readiness
certify privacy and consent understanding
certify role-based platform operation
certify interoperability basics
verify certificates with QR
track renewals and expiry
link training to access control where appropriate
never replace professional licensing
never authorize clinical practice by certificate alone
```

If a certificate can be mistaken for a medical license, nursing license, pharmacy license, lab professional license, or authority to practice, the wording and design must be corrected before launch.
