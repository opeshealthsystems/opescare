# OpesCare Demo Access Page, Demo Accounts, Demo Data, and Demo Environment PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Page Name:** Demo Access  
**Public Demo Route:** `/demo-access/public`  
**Internal Demo Route:** `/demo-access/internal`  
**Optional Redirect Route:** `/demo-access`  
**Build Direction:** Build from scratch  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS code, UI, database, demo data, seeders, account structure, permission model, or workflow assumptions.  
**Safety Rule:** Do not use real patient data, real facility data, real doctor data, real insurance data, real public health data, real API credentials, or real government data in demo mode.

---

# 1. Purpose

OpesCare needs a complete **Demo Access** system that allows the founder, internal team, testers, investors, hospitals, pharmacies, laboratories, insurers, public health stakeholders, developers, and partners to explore how OpesCare works using fake demo accounts and safe fake data.

The demo must prove the full OpesCare platform vision:

- one secure Health ID
- patient portal
- guardian/dependent access
- hospital dashboard
- clinic dashboard
- pharmacy dashboard
- laboratory dashboard
- insurance dashboard
- health organization dashboard
- public health dashboard
- developer/API dashboard
- OpesCare platform admin dashboard
- doctor working across multiple hospitals
- facility selector
- patient consent flow
- emergency access flow
- clinical timeline
- lab result flow
- prescription and dispensing flow
- medication availability
- blood availability
- insurance claim flow
- public health reporting
- API sync and webhooks
- reconciliation cases
- demo mobile app behavior
- audit logs and access logs

The Demo Access system is not just a login page. It is a controlled demonstration environment for the complete OpesCare ecosystem.

---

# 2. Core Demo Principle

Every demo account, patient, facility, clinical record, lab result, prescription, claim, report, API event, webhook event, blood record, pharmacy stock item, and public health signal must be fake and clearly marked as demo data.

Every relevant demo screen must show:

```text
DEMO DATA — NOT REAL PATIENT INFORMATION
```

French:

```text
DONNÉES DE DÉMONSTRATION — AUCUNE INFORMATION RÉELLE DE PATIENT
```

No demo data should look like a real person’s confidential medical record.

No demo account should be able to access production data.

No demo action should trigger real-world consequences.

---

# 3. Public Demo vs Internal Demo

OpesCare must separate public demo access from internal demo access.

## 3.1 Public Demo

Route:

```text
/demo-access/public
```

Purpose:

For prospects, investors, hospitals, clinics, pharmacies, labs, insurers, and partners who need to see the product safely.

Public demo may include only limited safe accounts:

```text
Patient Demo
Doctor Demo
Hospital Admin Limited Demo
Clinic Admin Limited Demo
Pharmacy Limited Demo
Laboratory Limited Demo
Insurance Limited Demo
Developer Docs Preview Demo
```

Public demo must not expose:

```text
OpesCare Admin
Public Health full dashboard
Health Organization Admin
security/governance dashboard
demo reset controls
API secret generation
production-like admin tools
country policy controls
real integration credentials
```

## 3.2 Internal Demo

Route:

```text
/demo-access/internal
```

Purpose:

For founder, product team, developers, QA, internal reviewers, and trusted partners.

Internal demo may include advanced roles:

```text
OpesCare Admin Demo
Public Health Officer Demo
Health Organization Admin Demo
Developer/API Demo with temporary demo secrets
Governance/Security Review Demo
Demo Reset Controls
Advanced Reconciliation Demo
```

Internal demo should require admin authentication or a private demo access control.

## 3.3 Redirect Route

Route:

```text
/demo-access
```

Behavior:

- If user is public or unauthenticated, redirect to `/demo-access/public`.
- If authenticated internal admin, show choice between public and internal demo.

---

# 4. Demo Mode Safety Rules

## 4.1 No Real Data

Demo mode must never use:

```text
real patient names
real patient phone numbers
real patient emails
real national IDs
real Health IDs
real hospital licenses
real lab results
real prescriptions
real insurance claims
real public health reports
real API secrets
real government endpoints
real medical documents
```

## 4.2 Demo Data Isolation

Preferred:

```text
separate demo tenant or separate demo database
```

Minimum acceptable:

```text
is_demo=true enforced by global scopes, policies, authorization checks, and query filters
```

Production and demo data must never appear in the same query result.

Every demo row should include where applicable:

```text
is_demo = true
demo_seed_key = "opescare_demo_v1"
demo_reset_group = "core_demo"
```

## 4.3 Demo Environment Flags

Use environment flags:

```text
OPESCARE_DEMO_MODE=true
OPESCARE_PUBLIC_DEMO_MODE=true
OPESCARE_INTERNAL_DEMO_MODE=true
OPESCARE_DEMO_EXTERNAL_SERVICES_SIMULATED=true
```

If `OPESCARE_DEMO_MODE=false`, all demo routes and demo login actions must be disabled or restricted.

## 4.4 Demo Write Restrictions

Demo users may perform safe simulated actions inside the demo environment.

Allowed simulated actions:

```text
approve demo consent request
deny demo consent request
revoke demo consent
view demo patient timeline
create demo visit
create demo consultation note
issue demo prescription
dispense demo medication
push demo lab result
create demo claim
simulate pharmacy stock sync
simulate blood need request
simulate public health report draft
simulate API sync event
simulate webhook delivery
simulate mobile app login
simulate notification
```

Blocked actions:

```text
send real SMS
send real email to external recipients
submit real government report
submit real insurance claim
charge real payment
generate production API keys
create real patient
create real facility
invite real users outside demo mode
access production audit logs
modify production country policy
delete production data
```

---

# 5. Simulated External Services

In demo mode, these services must be simulated:

```text
SMS
email
payments
insurance submission
government submission
public health submission
webhook delivery unless using approved demo receiver
push notifications
real facility verification
production API credential generation
```

## 5.1 Simulated SMS

Demo SMS must not send real SMS.

Display simulated SMS in the demo notification center or demo logs.

## 5.2 Simulated Email

Demo email must not send to real external addresses.

Use internal demo mail viewer or log.

## 5.3 Simulated Government Submission

Public health report submission must show:

```text
Simulated submission successful
```

or

```text
Simulated submission rejected
```

No real government endpoint must be contacted.

## 5.4 Simulated Insurance Submission

Insurance claim submission must stay inside demo.

No real insurer endpoint must be contacted.

## 5.5 Simulated Webhooks

Demo webhooks should be simulated unless the receiver domain is approved.

Allowed demo domains:

```text
*.example.test
localhost
127.0.0.1
approved demo webhook receiver configured by admin
```

---

# 6. Demo Session Rules

## 6.1 Session Lifetime

Use:

```text
public_demo_session_lifetime = 30 minutes
internal_demo_session_lifetime = 2 hours
```

## 6.2 Session Revocation

All demo sessions must be revoked when demo reset runs.

## 6.3 Session Warning

Show warning before expiration where possible:

```text
Your demo session will expire soon. Demo data resets regularly.
```

French:

```text
Votre session de démonstration expirera bientôt. Les données de démonstration sont réinitialisées régulièrement.
```

## 6.4 One-Click Login

One-click login is allowed only in demo mode.

Rules:

```text
one-click login must create temporary demo session
one-click login must be audited
one-click login must not require real credentials
one-click login must not work outside demo mode
one-click login must not allow production access
```

---

# 7. Demo Reset Rules

## 7.1 Reset Schedule

Recommended:

```text
Reset public demo data every 24 hours.
Reset internal demo data manually or on schedule.
```

## 7.2 Reset Command

Create artisan commands:

```text
php artisan opescare:demo:seed
php artisan opescare:demo:reset
```

## 7.3 Reset Behavior

When demo reset runs:

```text
revoke all active demo sessions
clear demo queues
clear demo notifications
clear demo webhooks
clear demo sync logs
clear demo API secrets
clear demo temporary files
clear demo exports
reseed demo data in correct order
redirect active demo users to /demo-access with reset notice
create audit event
```

## 7.4 Reset Notice

After reset, show:

```text
Demo data has been reset. Please start a new demo session.
```

French:

```text
Les données de démonstration ont été réinitialisées. Veuillez démarrer une nouvelle session de démonstration.
```

---

# 8. Demo Access Page Overview

## 8.1 Page Title

```text
OpesCare Demo Access
```

French:

```text
Accès démo OpesCare
```

## 8.2 Page Subtitle

```text
Explore OpesCare using safe demo accounts and fake healthcare data.
```

French:

```text
Explorez OpesCare avec des comptes de démonstration sécurisés et des données de santé fictives.
```

## 8.3 Warning Banner

English:

```text
DEMO ENVIRONMENT
All accounts and records on this page use fake data for demonstration. Do not enter real patient, medical, insurance, facility, or government information.
```

French:

```text
ENVIRONNEMENT DE DÉMONSTRATION
Tous les comptes et dossiers sur cette page utilisent des données fictives pour la démonstration. Ne saisissez aucune information réelle de patient, médicale, d’assurance, d’établissement ou de gouvernement.
```

## 8.4 Main CTAs

```text
Launch Demo
View Demo Guide
Try a Flow
```

French:

```text
Lancer la démo
Voir le guide de démonstration
Tester un parcours
```

---

# 9. Demo Access Page Layout

The Demo Access page should include:

1. Demo environment warning banner
2. Public/internal mode label
3. Quick login cards
4. Demo role filter
5. Patient and guardian demo section
6. Facility demo accounts section
7. Multi-facility doctor demo section
8. Pharmacy, lab, insurance, and public health demo section
9. Developer/API demo section
10. Demo workflow shortcuts
11. Demo data summary
12. Known demo limitations block
13. Demo reset notice
14. Support/contact CTA

---

# 10. Demo Access Components

Create reusable components:

```text
DemoAccessPage
DemoWarningBanner
DemoModeBadge
DemoLoginCard
DemoRoleGrid
DemoCredentialBlock
DemoFlowShortcutCard
DemoDataSummary
DemoEnvironmentStatus
DemoResetNotice
DemoSafetyNote
DemoLimitationsBlock
DemoSessionTimer
DemoRolePermissionSummary
```

## 10.1 Demo Login Card Fields

Each card must show:

```text
Lucide icon
role name
organization
demo email or one-click login
dashboard destination
what to test
allowed actions summary
blocked actions summary
demo data scope
```

## 10.2 Icons

Use Lucide icons only.

```text
Patient → UserRound
Guardian → UserRoundPlus
Doctor → Stethoscope
Multi-Hospital Doctor → Building2 + Stethoscope
Nurse → HeartPulse
Hospital → Hospital
Clinic → Building2
Pharmacy → Pill
Laboratory → FlaskConical
Insurance → ShieldPlus
Public Health → ChartColumn
Health Organization → Network
Developer/API → Code2
Admin → ShieldCheck
Demo Warning → TriangleAlert
Workflow → Route
Login → LogIn
Demo Reset → RefreshCw
```

Do not use emojis.

---

# 11. Demo Credentials Policy

## 11.1 Internal Demo Credentials

Internal demo may use seeded credentials.

Common internal demo password:

```text
DemoPass!2026
```

This password must never be used for production accounts.

## 11.2 Public Demo Credentials

Public demo should use one-click temporary demo sessions instead of exposing passwords.

Public demo cards should show:

```text
Login as Demo Patient
Login as Demo Doctor
Login as Demo Pharmacy
```

not raw password fields.

## 11.3 Password Login Rule

Demo password login must be disabled when:

```text
OPESCARE_DEMO_MODE=false
```

or where public demo policy requires one-click sessions only.

---

# 12. Demo Role Accounts and Permission Boundaries

Every role must define:

```text
allowed_actions
blocked_actions
dashboard_access
demo_data_scope
```

---

## 12.1 Patient Demo

```text
Role: Patient
Name: Demo Patient One
Email: demo.patient@opescare.test
Password: DemoPass!2026
Dashboard: Patient Portal
Health ID: OC-DEMO-PAT-0001
Demo Scope: own demo patient data only
```

Allowed actions:

```text
view Health ID
view QR code
view own demo timeline
view demo lab results
view demo prescriptions
approve demo consent
deny demo consent
revoke demo consent
view own demo access logs
search demo medication availability
view demo blood help guidance
upload demo document placeholder
switch language English/French
simulate mobile app QR view
```

Blocked actions:

```text
view other patients
access staff dashboards
edit verified clinical records directly
submit real data
send real notifications
download production records
```

---

## 12.2 Guardian Demo

```text
Role: Guardian
Name: Demo Guardian
Email: demo.guardian@opescare.test
Password: DemoPass!2026
Dashboard: Guardian Portal
Linked Dependent: Demo Child Patient
Health ID: OC-DEMO-CHILD-0001
Demo Scope: linked dependent demo data only
```

Allowed actions:

```text
view dependent Health ID
approve demo consent for dependent
deny demo consent for dependent
view dependent demo access logs
view dependent demo timeline
```

Blocked actions:

```text
access unrelated patients
change legal guardian status permanently
approve high-risk actions outside demo
access production data
```

---

## 12.3 Doctor Demo

```text
Role: Doctor
Name: Dr. Demo General
Email: demo.doctor@opescare.test
Password: DemoPass!2026
Dashboard: Provider Dashboard
Primary Facility: Demo Central Hospital
Demo Scope: assigned demo facility patients only
```

Allowed actions:

```text
search demo patients
request demo consent
view approved demo patient summary
create demo consultation
order demo lab test
issue demo prescription
create demo referral
view demo timeline after consent
```

Blocked actions:

```text
view patient data without consent or emergency reason
access non-demo patients
delete clinical records
submit real prescription
send real referral
access unrelated facility patients
```

---

## 12.4 Multi-Hospital Doctor Demo

```text
Role: Doctor working at multiple hospitals
Name: Dr. Multi Facility
Email: demo.multi.doctor@opescare.test
Password: DemoPass!2026
Facilities:
  - Demo Central Hospital
  - Demo City Clinic
  - Demo Specialist Hospital
Dashboard: Facility Selector then Provider Dashboard
Demo Scope: selected demo facility context only
```

Allowed actions:

```text
select active demo facility after login
switch demo facility context
search demo patients within selected context
request consent from selected facility
create demo consultation under selected facility
view audit context per facility
```

Blocked actions:

```text
mix records between facilities without selected context
act for a facility not assigned
bypass facility selector
view production patients
edit another facility record without proper context
```

Required behavior:

```text
user selects facility after login
all actions include selected facility_id
audit logs include selected facility
consent request shows selected facility
dashboard changes by selected facility
```

---

## 12.5 Nurse Demo

```text
Role: Nurse
Name: Nurse Demo
Email: demo.nurse@opescare.test
Password: DemoPass!2026
Facility: Demo Central Hospital
Dashboard: Nurse Dashboard
Demo Scope: assigned demo ward/queue only
```

Allowed actions:

```text
view demo triage queue
record demo vital signs
write demo nursing note
view demo ward patients
simulate medication administration
```

Blocked actions:

```text
issue prescriptions
validate lab results
approve claims
access full patient history without role permission
access production records
```

---

## 12.6 Hospital Admin Demo

```text
Role: Hospital Admin
Name: Demo Hospital Admin
Email: demo.hospital.admin@opescare.test
Password: DemoPass!2026
Organization: Demo Central Hospital
Dashboard: Hospital Admin Dashboard
Demo Scope: Demo Central Hospital only
```

Allowed actions:

```text
view demo facility dashboard
view demo staff list
view demo departments
view demo services
view demo audit summaries
view demo integration status
view demo reports
```

Blocked actions:

```text
delete users
delete facilities
generate production API keys
submit real public health reports
send real notifications
change production policy
access production audit logs
```

---

## 12.7 Clinic Admin Demo

```text
Role: Clinic Admin
Name: Demo Clinic Admin
Email: demo.clinic.admin@opescare.test
Password: DemoPass!2026
Organization: Demo City Clinic
Dashboard: Clinic Admin Dashboard
Demo Scope: Demo City Clinic only
```

Allowed actions:

```text
view demo appointments
view demo visits
view demo referrals
view demo clinic staff
view demo reports
```

Blocked actions:

```text
access hospital admin functions
access production patients
submit real public health reports
delete records
```

---

## 12.8 Pharmacy Demo

```text
Role: Pharmacist / Pharmacy Admin
Name: Demo Pharmacist
Email: demo.pharmacy@opescare.test
Password: DemoPass!2026
Organization: DemoCare Pharmacy
Dashboard: Pharmacy Dashboard
Demo Scope: DemoCare Pharmacy only
```

Allowed actions:

```text
view demo prescriptions
dispense demo medicine
view demo pharmacy stock
simulate stock sync
simulate medicine reservation
view stock-out alerts
```

Blocked actions:

```text
view full clinical timeline
edit doctor diagnosis
dispense production prescriptions
send real medicine reservation notification
access non-demo stock
```

---

## 12.9 Laboratory Demo

```text
Role: Laboratory Staff / Validator
Name: Demo Lab Validator
Email: demo.lab@opescare.test
Password: DemoPass!2026
Organization: Demo Diagnostic Laboratory
Dashboard: Laboratory Dashboard
Demo Scope: Demo Diagnostic Laboratory only
```

Allowed actions:

```text
view demo lab orders
collect demo sample
enter demo result
validate demo result
release demo result
trigger demo critical result alert
view lab surveillance draft
```

Blocked actions:

```text
edit released result silently
view full patient history
access production lab data
submit real public health report
```

---

## 12.10 Insurance Company Demo

```text
Role: Insurance Officer
Name: Demo Insurance Officer
Email: demo.insurance@opescare.test
Password: DemoPass!2026
Organization: DemoCare Insurance
Dashboard: Insurance Dashboard
Demo Scope: Demo insurance claims only
```

Allowed actions:

```text
view demo claims
check demo eligibility
review demo preauthorization
view minimum necessary demo record package
approve/query/reject demo claim
```

Blocked actions:

```text
view full patient timeline
view unrelated lab results
export full patient record
access non-demo patients
access sensitive records outside claim scope
```

---

## 12.11 Public Health Demo

Internal demo only by default.

```text
Role: Public Health Officer
Name: Demo Public Health Officer
Email: demo.publichealth@opescare.test
Password: DemoPass!2026
Organization: Demo Public Health Unit
Dashboard: Public Health Dashboard
Demo Scope: demo aggregate reports only
```

Allowed actions:

```text
view demo report drafts
review demo notifiable disease report
review demo lab surveillance
review demo medicine stock-out report
review demo blood shortage report
review demo disease signal
approve simulated report
```

Blocked actions:

```text
view full patient records
submit real government reports
access production public health data
export identifiable patient data
change country policy
```

---

## 12.12 Health Organization Demo

Internal demo only by default.

```text
Role: Health Organization Admin
Name: Demo Health Organization Admin
Email: demo.healthorg@opescare.test
Password: DemoPass!2026
Organization: Demo Health Network
Dashboard: Health Organization Dashboard
Demo Scope: Demo Health Network facilities only
```

Allowed actions:

```text
view multi-facility demo network dashboard
view facility-level reports
view integration performance
view patient flow summaries
view program monitoring summaries
```

Blocked actions:

```text
view full patient records without proper role
access facilities outside network
submit real reports
modify production facility settings
```

---

## 12.13 Developer/API Demo

Public demo can show docs preview. Internal demo can show temporary demo API credentials.

```text
Role: Developer
Name: Demo Developer
Email: demo.developer@opescare.test
Password: DemoPass!2026
Organization: Demo HealthTech Vendor
Dashboard: Developer/API Dashboard
Demo Scope: sandbox/demo API only
```

Allowed actions:

```text
view API docs
view SDK examples
view sample responses
simulate patient search
simulate consent request
simulate record push
view demo webhook logs
generate temporary demo secret internal only
```

Blocked actions:

```text
access production endpoints
generate production credentials
send real webhooks to unapproved domains
access real patient data
disable API security
```

---

## 12.14 OpesCare Admin Demo

Internal demo only.

```text
Role: OpesCare Admin
Name: Demo OpesCare Admin
Email: demo.admin@opescare.test
Password: DemoPass!2026
Organization: OpesCare Demo Admin
Dashboard: Platform Admin Dashboard
Demo Scope: demo platform data only
```

Allowed actions:

```text
view demo organization applications
view demo facility verification
view demo API clients
view demo reconciliation cases
view demo audit logs
view demo governance cases
reset demo data internal only
```

Blocked actions:

```text
delete production users
delete production facilities
change production country policy
generate production keys
submit real public health reports
send real SMS/email
access production audit logs
```

---

# 13. Demo Organizations and Facilities

## 13.1 Demo Central Hospital

```text
Organization Type: Hospital
Facility Code: DEMO-HOSP-001
Name: Demo Central Hospital
City: Demo City
Status: active_demo
Services:
  - Emergency care
  - Outpatient care
  - Inpatient care
  - Laboratory
  - Pharmacy
  - Imaging
  - Billing
  - Referrals
Departments:
  - Emergency
  - Outpatient
  - Internal Medicine
  - Laboratory
  - Pharmacy
  - Billing
  - Blood Bank
```

## 13.2 Demo City Clinic

```text
Organization Type: Clinic
Facility Code: DEMO-CLINIC-001
Name: Demo City Clinic
Status: active_demo
Services:
  - Outpatient care
  - Consultations
  - Basic lab requests
  - Prescriptions
  - Referrals
Departments:
  - Reception
  - Consultation
  - Nursing
```

## 13.3 Demo Specialist Hospital

```text
Organization Type: Hospital
Facility Code: DEMO-HOSP-002
Name: Demo Specialist Hospital
Status: active_demo
Services:
  - Specialty care
  - Cardiology
  - Imaging
  - Referrals
Departments:
  - Cardiology
  - Imaging
  - Specialist Consultation
```

## 13.4 DemoCare Pharmacy

```text
Organization Type: Pharmacy
Facility Code: DEMO-PHARM-001
Name: DemoCare Pharmacy
Status: active_demo
Services:
  - Prescription dispensing
  - Medication availability
  - Reservation
  - Stock sync
```

## 13.5 Demo Diagnostic Laboratory

```text
Organization Type: Laboratory
Facility Code: DEMO-LAB-001
Name: Demo Diagnostic Laboratory
Status: active_demo
Services:
  - Sample collection
  - Result entry
  - Result validation
  - Critical result alerts
```

## 13.6 DemoCare Insurance

```text
Organization Type: Insurance Company
Code: DEMO-INS-001
Name: DemoCare Insurance
Status: active_demo
Services:
  - Eligibility checks
  - Preauthorization
  - Claims
  - Coverage verification
```

## 13.7 Demo Public Health Unit

```text
Organization Type: Public Health Organization
Code: DEMO-PH-001
Name: Demo Public Health Unit
Status: active_demo
Services:
  - Notifiable disease reports
  - Surveillance reports
  - Stock-out monitoring
  - Disease signal review
```

## 13.8 Demo Health Network

```text
Organization Type: Health Organization
Code: DEMO-NETWORK-001
Name: Demo Health Network
Status: active_demo
Facilities:
  - Demo Central Hospital
  - Demo City Clinic
  - Demo Specialist Hospital
```

## 13.9 Demo HealthTech Vendor

```text
Organization Type: Technology Vendor
Code: DEMO-DEV-001
Name: Demo HealthTech Vendor
Status: active_demo
Services:
  - HIS integration
  - API integration
  - Webhooks
  - SDK testing
```

---

# 14. Demo Patients

## 14.1 Main Demo Patient

```text
Name: Demo Patient One
Health ID: OC-DEMO-PAT-0001
Sex: Female
Date of Birth: 1992-04-14
Phone: +237000000001
Email: demo.patient@opescare.test
Verification Status: verified_demo
Blood Group: O+
Allergies:
  - Penicillin, severe
Chronic Conditions:
  - Hypertension
Active Medications:
  - Amlodipine 5mg daily
Emergency Contact:
  - Demo Emergency Contact, +237000000099
```

Timeline records:

```text
2026-05-01: Consultation at Demo City Clinic
2026-05-02: Lab result released by Demo Diagnostic Laboratory
2026-05-03: Prescription issued by Dr. Demo General
2026-05-04: Medication dispensed by DemoCare Pharmacy
2026-05-05: Referral to Demo Specialist Hospital
```

## 14.2 Demo Child Patient

```text
Name: Demo Child Patient
Health ID: OC-DEMO-CHILD-0001
Sex: Male
Date of Birth: 2018-08-20
Guardian: Demo Guardian
Verification Status: verified_demo
Blood Group: A+
Allergies:
  - No known allergy
```

## 14.3 Emergency Demo Patient

```text
Name: Demo Emergency Patient
Health ID: OC-DEMO-EMERGENCY-0001
Sex: Male
Date of Birth: 1985-11-09
Blood Group: O-
Critical Allergy: Severe allergy to aspirin
Chronic Condition: Diabetes
Emergency Profile: enabled
```

## 14.4 Insurance Demo Patient

```text
Name: Demo Insured Patient
Health ID: OC-DEMO-INSURED-0001
Insurance Provider: DemoCare Insurance
Policy Number: DEMO-POLICY-001
Coverage Status: active_demo
```

---

# 15. Demo Consent States

Seed all consent statuses:

```text
pending consent request
granted consent
denied consent
expired consent
revoked consent
emergency override
```

## 15.1 Pending Consent

```text
Patient: Demo Patient One
Requesting Facility: Demo Central Hospital
Purpose: treatment
Requested Data: summary, allergies, recent lab results
Status: pending
```

## 15.2 Granted Consent

```text
Patient: Demo Patient One
Requesting Facility: Demo City Clinic
Purpose: treatment
Status: granted
Expires: demo future time
```

## 15.3 Denied Consent

```text
Patient: Demo Patient One
Requesting Facility: Demo Specialist Hospital
Purpose: referral review
Status: denied
```

## 15.4 Expired Consent

```text
Patient: Demo Patient One
Requesting Facility: Demo Central Hospital
Purpose: treatment
Status: expired
```

## 15.5 Revoked Consent

```text
Patient: Demo Patient One
Requesting Facility: Demo City Clinic
Purpose: treatment
Status: revoked
```

## 15.6 Emergency Override

```text
Patient: Demo Emergency Patient
Facility: Demo Central Hospital
Purpose: emergency
Reason: Demo unconscious patient emergency
Status: emergency_override
Review Status: pending_review
```

---

# 16. Demo Clinical Data

## 16.1 Demo Consultation

```text
Patient: Demo Patient One
Facility: Demo City Clinic
Provider: Dr. Demo General
Visit Type: Outpatient
Chief Complaint: Headache and fever
Diagnosis: Uncomplicated malaria suspected
Plan:
  - Lab test ordered
  - Antimalarial prescribed after confirmation
Status: signed_demo
```

## 16.2 Demo Lab Result

```text
Patient: Demo Patient One
Facility: Demo Diagnostic Laboratory
Test: Malaria rapid diagnostic test
Result: Positive
Status: released_demo
Validator: Demo Lab Validator
Critical: No
```

## 16.3 Demo Critical Lab Result

```text
Patient: Demo Emergency Patient
Facility: Demo Diagnostic Laboratory
Test: Blood glucose
Result: Critically high
Status: released_demo
Critical: Yes
Alert: simulated_demo_alert
```

## 16.4 Demo Prescription

```text
Patient: Demo Patient One
Medication: Artemether/Lumefantrine
Dose: Standard adult course
Status: issued_demo
Prescriber: Dr. Demo General
Prescription Required: yes
```

## 16.5 Demo Dispense Event

```text
Patient: Demo Patient One
Pharmacy: DemoCare Pharmacy
Medication: Artemether/Lumefantrine
Quantity: 1 course
Status: fully_dispensed_demo
Pharmacist: Demo Pharmacist
```

## 16.6 Demo Referral

```text
Patient: Demo Patient One
From: Demo City Clinic
To: Demo Specialist Hospital
Reason: Follow-up for persistent symptoms
Status: accepted_demo
```

---

# 17. Demo Pharmacy Stock

Every stock item must include:

```text
last_updated_at
freshness_status
is_demo=true
```

## 17.1 Recent Stock Examples

```text
Medication: Artemether/Lumefantrine 20/120mg
Status: available
Quantity: 42
Last Updated: recent_demo
Freshness Status: recent
Prescription Required: yes
```

```text
Medication: Amlodipine 5mg
Status: available
Quantity: 100
Last Updated: recent_demo
Freshness Status: recent
Prescription Required: yes
```

```text
Medication: Insulin Regular
Status: low_stock
Quantity: 5
Last Updated: recent_demo
Freshness Status: recent
Prescription Required: yes
```

```text
Medication: Amoxicillin 500mg
Status: out_of_stock
Quantity: 0
Last Updated: recent_demo
Freshness Status: recent
Prescription Required: yes
```

## 17.2 Stale Stock Example

```text
Medication: Salbutamol Inhaler
Status: available
Quantity: 12
Last Updated: stale_demo
Freshness Status: stale
Prescription Required: yes
Warning: Stock not recently confirmed
```

Purpose:

- test Find Medication
- test stock status
- test low stock
- test out of stock
- test stale warning
- test prescription required warning
- test medicine reservation

---

# 18. Demo Blood Inventory

Every blood stock item must include:

```text
last_updated_at
freshness_status
is_demo=true
```

## 18.1 Recent Blood Stock

```text
Facility: Demo Central Hospital Blood Bank
Blood Group: O+
Component: Packed red cells
Available Units: 4
Status: available
Last Updated: recent_demo
Freshness Status: recent
```

```text
Facility: Demo Central Hospital Blood Bank
Blood Group: O-
Component: Packed red cells
Available Units: 1
Status: low_units
Last Updated: recent_demo
Freshness Status: recent
```

```text
Facility: Demo Central Hospital Blood Bank
Blood Group: A+
Component: Whole blood
Available Units: 0
Status: not_available
Last Updated: recent_demo
Freshness Status: recent
```

## 18.2 Stale Blood Stock

```text
Facility: Demo Specialist Hospital Blood Bank
Blood Group: B+
Component: Packed red cells
Available Units: 3
Status: available
Last Updated: stale_demo
Freshness Status: stale
Warning: Blood stock not recently confirmed
```

## 18.3 Demo Blood Need Request

```text
Facility: Demo City Clinic
Blood Group Needed: O-
Component: Packed red cells
Units Needed: 2
Urgency: urgent
Status: pending_demo
Patient Identity: hidden
```

Purpose:

- test Find Blood
- test blood availability
- test blood need request
- test stale blood warning
- test no patient identity exposure
- test provider-only blood reservation

---

# 19. Demo Insurance Data

## 19.1 Demo Policy

```text
Patient: Demo Insured Patient
Insurer: DemoCare Insurance
Policy Number: DEMO-POLICY-001
Coverage Status: active
Plan: Demo Standard Health Plan
```

## 19.2 Demo Eligibility

```text
Status: eligible
Service: Outpatient consultation
Copay: 20%
Authorization Required: no
```

## 19.3 Demo Preauthorization

```text
Patient: Demo Insured Patient
Service: MRI Scan
Status: pending_review
Requested By: Demo Specialist Hospital
Insurer: DemoCare Insurance
```

## 19.4 Demo Claim

```text
Claim ID: DEMO-CLAIM-001
Patient: Demo Insured Patient
Facility: Demo Central Hospital
Amount: 45,000 XAF
Status: submitted
Review Status: pending
```

Purpose:

- test insurer dashboard
- test minimum necessary data
- test claim flow
- test preauthorization
- test claim query

---

# 20. Demo Public Health Data

Public health demo data must be aggregate or de-identified by default.

## 20.1 Demo Notifiable Disease Draft

```text
Report Type: Notifiable Disease
Condition: Suspected measles
Facility: Demo City Clinic
Status: draft
Case Count: 2
Data Classification: aggregate
Patient Identity: not included
```

## 20.2 Demo Lab Surveillance Report

```text
Report Type: Lab Surveillance
Disease/Test: Malaria RDT
Facility: Demo Diagnostic Laboratory
Positive Count: 18
Negative Count: 32
Reporting Period: Current Demo Week
Status: pending_review
Patient Identity: not included
```

## 20.3 Demo Pharmacy Stock-Out Report

```text
Report Type: Medicine Stock-Out
Medicine: Amoxicillin 500mg
Facility: DemoCare Pharmacy
Status: draft
Stock Status: out_of_stock
```

## 20.4 Demo Blood Shortage Report

```text
Report Type: Blood Shortage
Blood Group: O-
Facility: Demo Central Hospital
Status: draft
Available Units: 1
Urgency: warning
Patient Identity: not included
```

## 20.5 Demo Disease Signal

```text
Signal Type: Lab Positivity Spike
Condition: Malaria
Area: Demo District
Status: under_review
Confidence: medium
Patient Identity: not included
```

---

# 21. Demo Developer/API Data

## 21.1 Demo API Client

```text
Client Name: Demo HealthTech HIS Client
Environment: sandbox_demo
Client ID: demo_client_his_001
Scopes:
  - patients.search
  - consent.request
  - patient.summary.read
  - records.encounters.push
  - records.lab_results.push
  - pharmacy_stock.sync
  - blood_stock.sync
```

Never display a permanent real client secret.

Show:

```text
Client Secret: hidden
```

Internal demo may show:

```text
Generate Temporary Demo Secret
```

## 21.2 Demo API Credential Restrictions

Demo API credentials must:

```text
expire after 60 minutes
work only in demo/sandbox
never access production endpoints
be rate-limited
never send real SMS/email/payment/government submissions
never expose real secrets
never create real webhooks outside approved demo domains
```

## 21.3 Demo Webhook

```text
Webhook URL: https://demo-vendor.example.test/webhooks/opescare
Events:
  - consent.granted
  - lab_result.released
  - sync.failed
Status: active_demo
Delivery Mode: simulated
```

## 21.4 Demo Sync Events

```text
patient.search: success
consent.request: pending
encounter.push: synced
lab_result.push: pending_reconciliation
pharmacy_stock.sync: synced
blood_stock.sync: synced
webhook.delivery: failed_demo
```

---

# 22. Demo Notifications

Seed demo notifications:

```text
consent request notification
lab result available notification
medicine reservation notification
access log notification
public health review notification
sync failed notification
demo session expiry notification
```

Notification safety:

- no sensitive clinical content in notification text
- no real SMS/email delivery
- notification center only or simulated delivery log

Example:

```text
You have a new demo health update. Log in securely to view it.
```

French:

```text
Vous avez une nouvelle mise à jour de démonstration. Connectez-vous de manière sécurisée pour la consulter.
```

---

# 23. Demo Mobile App Support

The demo must support mobile app behavior.

Seed/implement placeholders for:

```text
demo patient mobile login
demo device registration
demo QR display
demo push notification simulation
demo PIN placeholder
demo biometric placeholder
demo consent approval from mobile
demo access log view from mobile
demo Find Medication from mobile
demo Find Blood Help from mobile
```

Mobile demo constraints:

- no real push notifications
- no real biometric requirement in web demo
- no real patient data
- no production API tokens

---

# 24. Demo Reconciliation Cases

## 24.1 Multiple Patient Match

```text
Case ID: DEMO-REC-001
Reason: multiple_patient_matches
Source: Demo HealthTech HIS
Status: pending_review
```

## 24.2 Lab Result Needs Review

```text
Case ID: DEMO-REC-002
Reason: unmapped_test_code
Source: Demo Diagnostic Laboratory
Status: pending_review
```

## 24.3 Stock Sync Warning

```text
Case ID: DEMO-REC-003
Reason: stale_stock_update
Source: DemoCare Pharmacy
Status: warning
```

Purpose:

- show reconciliation workbench
- show data quality
- show sync failures
- show safe error handling

---

# 25. Demo Workflow Shortcuts

The Demo Access page should include quick links called **Try a Flow**.

Each flow should show steps and open the relevant demo account or dashboard.

## 25.1 Patient Consent Flow

Steps:

1. Login as Doctor Demo.
2. Search Demo Patient One.
3. Request access.
4. Login as Demo Patient.
5. Approve consent.
6. Return as Doctor Demo.
7. View approved patient summary.
8. Check access log as patient.

Shortcut button:

```text
Try Consent Flow
```

## 25.2 Multi-Hospital Doctor Flow

Steps:

1. Login as Dr. Multi Facility.
2. Select Demo Central Hospital.
3. View provider dashboard.
4. Switch facility context to Demo City Clinic.
5. Confirm dashboard changes.
6. Confirm audit context changes.

Shortcut button:

```text
Try Multi-Facility Doctor Flow
```

## 25.3 Pharmacy Stock and Find Medication Flow

Steps:

1. Login as Pharmacy Demo.
2. View stock.
3. Update demo stock.
4. Login as Patient Demo.
5. Search Find Medication.
6. See pharmacy availability and stale stock warning.

Shortcut button:

```text
Try Medication Availability Flow
```

## 25.4 Blood Availability Flow

Steps:

1. Login as Hospital Admin or Blood Bank Demo.
2. View blood inventory.
3. Create demo blood need.
4. Login as Provider Demo.
5. Search Find Blood.
6. View safe blood availability and stale stock warning.

Shortcut button:

```text
Try Blood Availability Flow
```

## 25.5 Insurance Claim Flow

Steps:

1. Login as Hospital Admin.
2. Create demo invoice/claim.
3. Login as Insurance Demo.
4. Review claim.
5. Approve or query claim.
6. Verify patient data is minimum necessary.

Shortcut button:

```text
Try Insurance Flow
```

## 25.6 Public Health Reporting Flow

Internal demo by default.

Steps:

1. Login as Public Health Demo.
2. View report drafts.
3. Open lab surveillance report.
4. Review data quality.
5. Approve simulated report.
6. Confirm no patient identity appears.
7. Confirm government submission is simulated only.

Shortcut button:

```text
Try Public Health Reporting Flow
```

## 25.7 Developer API Sync Flow

Steps:

1. Login as Developer Demo.
2. View sandbox client.
3. Generate temporary demo secret if internal demo.
4. View sample API call.
5. Simulate patient search.
6. Simulate lab result push.
7. See pending reconciliation.
8. View simulated webhook delivery.

Shortcut button:

```text
Try API Sync Flow
```

## 25.8 Emergency Access Flow

Steps:

1. Login as Doctor Demo.
2. Search Demo Emergency Patient.
3. Use emergency access.
4. Enter reason.
5. View limited emergency profile.
6. Login as Admin/Public Health/Governance reviewer.
7. Review emergency access event.
8. Login as patient and view access log where allowed.

Shortcut button:

```text
Try Emergency Access Flow
```

---

# 26. Known Demo Limitations Block

The Demo Access page must display a visible section:

```text
What is simulated in this demo
```

Content:

```text
SMS messages are simulated.
Emails are simulated.
Payments are simulated.
Insurance submissions are simulated.
Government/public health submissions are simulated.
Webhook delivery is simulated unless an approved demo receiver is configured.
Production API credentials are not created.
Real facility verification is not performed.
All patients and records are fake.
Demo data resets regularly.
```

French:

```text
Ce qui est simulé dans cette démo
Les SMS sont simulés.
Les e-mails sont simulés.
Les paiements sont simulés.
Les soumissions d’assurance sont simulées.
Les soumissions gouvernementales/de santé publique sont simulées.
La livraison des webhooks est simulée sauf si un récepteur de démonstration approuvé est configuré.
Aucun identifiant API de production n’est créé.
Aucune vérification réelle d’établissement n’est effectuée.
Tous les patients et dossiers sont fictifs.
Les données de démonstration sont réinitialisées régulièrement.
```

---

# 27. Demo Seeders

## 27.1 Required Seeders

Create seeders:

```text
DemoOrganizationsSeeder
DemoFacilitiesSeeder
DemoDepartmentsSeeder
DemoServicesSeeder
DemoRolesAndPermissionsSeeder
DemoUsersSeeder
DemoFacilityAssignmentsSeeder
DemoPatientsSeeder
DemoConsentSeeder
DemoClinicalRecordsSeeder
DemoPharmacyStockSeeder
DemoBloodInventorySeeder
DemoInsuranceSeeder
DemoPublicHealthSeeder
DemoDeveloperSeeder
DemoNotificationsSeeder
DemoReconciliationSeeder
DemoAuditSeeder
```

## 27.2 Seeder Order

Seed demo data in this exact order:

```text
1. DemoOrganizationsSeeder
2. DemoFacilitiesSeeder
3. DemoDepartmentsSeeder
4. DemoServicesSeeder
5. DemoRolesAndPermissionsSeeder
6. DemoUsersSeeder
7. DemoFacilityAssignmentsSeeder
8. DemoPatientsSeeder
9. DemoConsentSeeder
10. DemoClinicalRecordsSeeder
11. DemoPharmacyStockSeeder
12. DemoBloodInventorySeeder
13. DemoInsuranceSeeder
14. DemoPublicHealthSeeder
15. DemoDeveloperSeeder
16. DemoNotificationsSeeder
17. DemoReconciliationSeeder
18. DemoAuditSeeder
```

Seeder dependency rules:

- users must exist before facility assignments
- patients must exist before clinical records
- facilities must exist before clinical records
- pharmacy must exist before pharmacy stock
- blood bank/facility must exist before blood inventory
- insurance organization must exist before claims
- public health organization must exist before public health reports
- developer organization must exist before API client demo
- audit logs should be seeded last

---

# 28. Demo Data Relationship Rules

## 28.1 Multi-Facility Doctor

Dr. Multi Facility works at:

```text
Demo Central Hospital → role: doctor
Demo City Clinic → role: doctor
Demo Specialist Hospital → role: specialist_consultant
```

Required behavior:

```text
facility selector appears after login
selected facility stored in session
audit logs include selected facility
consent request includes selected facility
dashboard changes based on selected facility
```

## 28.2 Patient-Provider Relationship

Demo Patient One has:

```text
visit at Demo City Clinic
lab result from Demo Diagnostic Laboratory
prescription from Dr. Demo General
dispense event from DemoCare Pharmacy
referral to Demo Specialist Hospital
access log from Demo Central Hospital
```

## 28.3 Insurance Relationship

Demo Insured Patient has:

```text
coverage with DemoCare Insurance
claim from Demo Central Hospital
preauthorization from Demo Specialist Hospital
```

## 28.4 Public Health Relationship

Demo reports come from:

```text
Demo City Clinic → notifiable disease draft
Demo Diagnostic Laboratory → lab surveillance
DemoCare Pharmacy → medicine stock-out
Demo Central Hospital → blood shortage
```

---

# 29. Demo API Endpoints

Recommended internal demo endpoints:

```text
GET  /api/demo/access/accounts
POST /api/demo/access/login-as
GET  /api/demo/data/summary
POST /api/demo/reset
POST /api/demo/flows/consent/reset
POST /api/demo/flows/pharmacy-stock/reset
POST /api/demo/flows/blood/reset
POST /api/demo/flows/public-health/reset
POST /api/demo/api/generate-temporary-secret
```

Security:

```text
/api/demo/reset requires internal admin permission
/api/demo/api/generate-temporary-secret requires internal demo permission
/api/demo/access/login-as works only in demo mode
demo endpoints disabled when OPESCARE_DEMO_MODE=false
demo endpoints cannot query non-demo records
```

---

# 30. Demo Dashboard Landing After Login

Each demo account should land in the correct dashboard:

```text
demo.patient@opescare.test → Patient Portal
demo.guardian@opescare.test → Guardian Portal
demo.doctor@opescare.test → Provider Dashboard
demo.multi.doctor@opescare.test → Facility Selector
demo.nurse@opescare.test → Nurse Dashboard
demo.hospital.admin@opescare.test → Hospital Admin Dashboard
demo.clinic.admin@opescare.test → Clinic Admin Dashboard
demo.pharmacy@opescare.test → Pharmacy Dashboard
demo.lab@opescare.test → Laboratory Dashboard
demo.insurance@opescare.test → Insurance Dashboard
demo.publichealth@opescare.test → Public Health Dashboard
demo.healthorg@opescare.test → Health Organization Dashboard
demo.developer@opescare.test → Developer/API Dashboard
demo.admin@opescare.test → Platform Admin Dashboard
```

---

# 31. Demo Audit Events

Add audit events:

```text
demo_login_started
demo_login_completed
demo_login_failed
demo_flow_started
demo_flow_completed
demo_reset_requested
demo_reset_completed
demo_api_secret_generated
demo_external_service_simulated
demo_session_expired
demo_session_revoked
```

For one-click login, store:

```text
demo_role
demo_user_id
ip_address
user_agent
started_at
session_expires_at
demo_mode_type: public/internal
```

For demo reset, store:

```text
requested_by
reset_group
started_at
completed_at
sessions_revoked_count
records_reseeded_count
```

---

# 32. Bilingual Demo Requirements

All demo page text must use translation keys.

English/French examples:

```text
Demo Access → Accès démo
Demo Environment → Environnement de démonstration
Patient Demo → Démo Patient
Guardian Demo → Démo Tuteur
Hospital Demo → Démo Hôpital
Clinic Demo → Démo Clinique
Pharmacy Demo → Démo Pharmacie
Laboratory Demo → Démo Laboratoire
Insurance Demo → Démo Assurance
Public Health Demo → Démo Santé publique
Developer Demo → Démo Développeur
Login as Demo User → Se connecter comme utilisateur démo
Copy Password → Copier le mot de passe
Try a Flow → Tester un parcours
Demo Data → Données fictives
Not Real Patient Information → Informations patient non réelles
Known Demo Limitations → Limites connues de la démo
Session Expires Soon → La session expirera bientôt
```

Bilingual QA must test that French text does not break:

```text
cards
buttons
dashboard links
flow shortcut cards
warning banners
credential blocks
session timer
limitations block
```

---

# 33. Demo Page Copy

## Header

```text
Explore OpesCare with Demo Access
```

French:

```text
Explorez OpesCare avec l’accès démo
```

## Subtitle

```text
Use safe demo accounts to see how patients, hospitals, clinics, pharmacies, labs, insurers, public health teams, developers, and administrators work together on OpesCare.
```

French:

```text
Utilisez des comptes de démonstration sécurisés pour voir comment les patients, hôpitaux, cliniques, pharmacies, laboratoires, assureurs, équipes de santé publique, développeurs et administrateurs travaillent ensemble sur OpesCare.
```

## Section: Choose a Demo Account

```text
Select a role below to open the matching dashboard and test the workflow.
```

French:

```text
Sélectionnez un rôle ci-dessous pour ouvrir le tableau de bord correspondant et tester le parcours.
```

## Section: Try a Complete Flow

```text
Follow guided demo flows to understand consent, patient records, medicine availability, blood availability, insurance claims, public health reporting, and API sync.
```

French:

```text
Suivez des parcours guidés pour comprendre le consentement, les dossiers patients, la disponibilité des médicaments, la disponibilité du sang, les demandes d’assurance, les rapports de santé publique et la synchronisation API.
```

## Footer Note

```text
Demo data resets regularly. Changes made in demo mode are for testing only.
```

French:

```text
Les données de démonstration sont réinitialisées régulièrement. Les modifications effectuées en mode démo servent uniquement aux tests.
```

---

# 34. Testing Requirements

Required tests:

1. `/demo-access/public` route exists.
2. `/demo-access/internal` route exists.
3. `/demo-access` redirects correctly.
4. Demo mode can be disabled.
5. Public demo does not show internal-only roles.
6. Internal demo requires authorized access.
7. Demo accounts are flagged as demo.
8. Demo data is flagged as demo.
9. Demo users cannot access non-demo records.
10. Demo users cannot send real SMS.
11. Demo users cannot send real email.
12. Demo users cannot submit real government report.
13. Demo users cannot submit real insurance claim.
14. Demo API credentials expire.
15. Demo API credentials cannot call production endpoints.
16. Demo sessions expire.
17. Demo reset revokes active demo sessions.
18. Demo reset clears demo queues, notifications, webhooks, sync logs, temporary secrets, exports.
19. Seeders run in correct dependency order.
20. Multi-hospital doctor has multiple facility assignments.
21. Multi-hospital doctor must select facility after login.
22. Consent states are seeded: pending, granted, denied, expired, revoked, emergency override.
23. Demo notifications are seeded.
24. Demo mobile behavior placeholders exist.
25. Recent and stale pharmacy stock examples exist.
26. Recent and stale blood inventory examples exist.
27. Public health demo reports do not expose patient identity.
28. Insurance demo cannot view full patient timeline.
29. Pharmacy demo cannot view full clinical notes.
30. Lab demo cannot silently edit released result.
31. Demo page renders in English.
32. Demo page renders in French.
33. French text does not break cards/buttons.
34. Lucide icons are used.
35. Emojis are not used as interface icons.
36. Demo audit events are created.

---

# 35. Acceptance Criteria

The Demo Access system is acceptable when:

1. Public and internal demo modes are separated.
2. Public demo exposes only safe limited roles.
3. Internal demo exposes advanced roles only to authorized users.
4. Demo safety banner is visible.
5. Known demo limitations block is visible.
6. Demo sessions expire.
7. Demo reset revokes active sessions.
8. Demo reset reseeds data safely.
9. Demo accounts cannot access production data.
10. Demo data cannot appear with production data in the same query result.
11. Demo API credentials expire and cannot touch production.
12. Simulated external services cannot send real messages/submissions.
13. Seeders run in correct dependency order.
14. Demo organizations exist.
15. Demo facilities exist.
16. Demo departments and services exist.
17. Demo users exist.
18. Demo facility assignments exist.
19. Multi-hospital doctor works correctly.
20. Demo patients exist.
21. Demo guardian/dependent data exists.
22. Demo consent states are fully represented.
23. Demo clinical records exist.
24. Demo lab results exist.
25. Demo prescription and dispense event exist.
26. Demo pharmacy stock includes recent and stale examples.
27. Demo blood inventory includes recent and stale examples.
28. Demo insurance claim exists.
29. Demo public health reports exist.
30. Demo API/sync events exist.
31. Demo notifications exist.
32. Demo reconciliation cases exist.
33. Demo mobile behavior is represented.
34. Demo workflows are guided.
35. Demo page is bilingual-ready.
36. English and French demo pages are tested.
37. Lucide icons are used.
38. No emojis are used as interface icons.
39. Demo page is responsive on desktop, tablet, and mobile.
40. All demo login and reset actions are audited.

---

# 36. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, docs/public-health/OPESCARE_PUBLIC_HEALTH_REPORTING_PHASES.md, docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md, and docs/demo/OPESCARE_DEMO_ACCESS.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS demo data, UI, code, module structure, database, or account structure.

Task: Build the OpesCare Demo Access page and demo data foundation.

Scope:
1. Create routes:
   - /demo-access
   - /demo-access/public
   - /demo-access/internal

2. Add public/internal demo mode separation.

3. Add visible demo warning banner.

4. Add known demo limitations block.

5. Create components:
   - DemoAccessPage
   - DemoWarningBanner
   - DemoModeBadge
   - DemoLoginCard
   - DemoRoleGrid
   - DemoCredentialBlock
   - DemoFlowShortcutCard
   - DemoDataSummary
   - DemoEnvironmentStatus
   - DemoResetNotice
   - DemoSafetyNote
   - DemoLimitationsBlock
   - DemoSessionTimer
   - DemoRolePermissionSummary

6. Create demo account cards for:
   - patient
   - guardian
   - doctor
   - multi-hospital doctor
   - nurse
   - hospital admin
   - clinic admin
   - pharmacy
   - laboratory
   - insurance
   - public health internal only
   - health organization internal only
   - developer/API
   - OpesCare admin internal only

7. Add demo seeders in this exact order:
   - DemoOrganizationsSeeder
   - DemoFacilitiesSeeder
   - DemoDepartmentsSeeder
   - DemoServicesSeeder
   - DemoRolesAndPermissionsSeeder
   - DemoUsersSeeder
   - DemoFacilityAssignmentsSeeder
   - DemoPatientsSeeder
   - DemoConsentSeeder
   - DemoClinicalRecordsSeeder
   - DemoPharmacyStockSeeder
   - DemoBloodInventorySeeder
   - DemoInsuranceSeeder
   - DemoPublicHealthSeeder
   - DemoDeveloperSeeder
   - DemoNotificationsSeeder
   - DemoReconciliationSeeder
   - DemoAuditSeeder

8. Add is_demo, demo_seed_key, and demo_reset_group fields where applicable.

9. Add environment flags:
   - OPESCARE_DEMO_MODE
   - OPESCARE_PUBLIC_DEMO_MODE
   - OPESCARE_INTERNAL_DEMO_MODE
   - OPESCARE_DEMO_EXTERNAL_SERVICES_SIMULATED

10. Add commands:
   - php artisan opescare:demo:seed
   - php artisan opescare:demo:reset

11. Add demo session lifetimes:
   - public demo: 30 minutes
   - internal demo: 2 hours

12. Add demo reset behavior:
   - revoke active demo sessions
   - clear demo queues
   - clear demo notifications
   - clear demo webhooks
   - clear demo sync logs
   - clear demo API temporary secrets
   - reseed data
   - redirect active users with reset notice

13. Add demo API restrictions:
   - expire temporary secret after 60 minutes
   - demo/sandbox only
   - cannot access production endpoint
   - rate-limited
   - no real SMS/email/payment/government/insurance submission

14. Add simulated external services for:
   - SMS
   - email
   - payments
   - insurance submissions
   - government submissions
   - public health submissions
   - webhook delivery
   - push notifications

15. Add demo mobile support placeholders:
   - patient mobile login
   - device registration
   - QR display
   - push notification simulation
   - PIN/biometric placeholder
   - mobile consent approval

16. Add translation keys for English and French.

17. Use Lucide icons only.

18. Do not use emojis.

19. Add tests proving:
   - demo routes exist
   - public/internal demo separation works
   - demo mode can be disabled
   - demo accounts are flagged as demo
   - demo data is flagged as demo
   - demo accounts cannot access non-demo production data
   - public demo does not show internal roles
   - demo sessions expire
   - demo reset revokes sessions
   - multi-hospital doctor has multiple facility assignments
   - consent states are seeded
   - notifications are seeded
   - recent and stale stock examples exist
   - demo API credentials cannot access production
   - simulated services do not send real messages/submissions

20. Open a PR with:
   - summary
   - screenshots
   - seeded accounts
   - routes created
   - seeders created
   - tests added
   - security risks
   - known limitations
   - next recommended tasks
```

---

# 37. Final Rule

Demo Access is not a toy feature.

It is how OpesCare proves the full platform vision.

The demo must show how patients, guardians, doctors, multi-facility doctors, hospitals, clinics, pharmacies, labs, insurers, public health teams, health organizations, developers, and platform administrators work together safely.

But it must never risk real patient privacy or real-world operations.

The correct demo model is:

```text
fake data only
demo environment only
public/internal separation
safe workflows
clear warning labels
isolated data
no real external submissions
expiring demo sessions
resettable seed data
strict role boundaries
multi-facility access
mobile demo support
complete guided flows
bilingual interface
audited demo actions
```

If demo data can touch production data, the demo design is unsafe and must be rejected.
