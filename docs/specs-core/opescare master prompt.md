# OpesCare Consolidated Technical and Operational Blueprint

## Document Title

**OpesCare Digital Health ID and Healthcare Operating Platform**  
**Complete Product, Technical Architecture, Operational Workflow, Interoperability, Security, Governance, and Implementation Blueprint**

Prepared for: **Opesware**  
Product domain: **opescare.com**  
Product name: **OpesCare**  
Core product: **OpesCare Health ID**

---

## 1. Executive Definition

OpesCare is a digital health identity and healthcare operating platform designed to replace paper hospital books with one secure, interoperable, patient-centered Health ID. Each patient receives a unique OpesCare Health ID that links their medical history across hospitals, clinics, laboratories, pharmacies, insurance companies, emergency services, and public health systems.

The purpose of OpesCare is not only to store patient records. Its deeper purpose is to create a trusted healthcare infrastructure layer that allows different health institutions, even when they use different software systems, to identify patients, exchange authorized medical information, synchronize health events, and maintain a continuous medical history for every patient.

In practical terms, OpesCare allows a patient to visit Hospital A, receive consultation, lab tests, prescriptions, and treatment, then later visit Hospital B and allow the doctor to securely view relevant previous medical history. Hospital B can then add new records, and the patient’s longitudinal medical timeline is updated.

OpesCare is therefore both:

1. A **Digital Health ID platform** for patients.
2. A **Health Information Exchange and healthcare operations platform** for providers.

The strongest product positioning is:

**OpesCare: One Health ID. One Medical History. Better Care Everywhere.**

---

## 2. Core Problem

Many healthcare systems still depend on paper hospital books, fragmented records, isolated hospital software, disconnected laboratories, manual prescriptions, and incomplete insurance processes. This creates serious problems.

Patients lose hospital books. Doctors treat patients without knowing their allergies, chronic diseases, previous diagnoses, or medication history. Lab results are repeated because prior results are unavailable. Pharmacies dispense medication without full context. Insurance verification is slow. Public health reporting is weak. Hospitals struggle with duplicate patients, incomplete records, and poor continuity of care.

The problem is not simply that records are on paper. The deeper problem is that the healthcare ecosystem is fragmented.

OpesCare solves this by creating one patient identity and a structured system through which authorized health actors can add, retrieve, and synchronize medical information.

---

## 3. Product Vision

OpesCare should become a healthcare operating layer that connects patients, hospitals, clinics, labs, pharmacies, insurers, and public health authorities.

The long-term vision is that every patient can carry one Health ID and use it across multiple healthcare providers. Every provider can contribute to the patient’s record, subject to consent and authorization. Every medical event can be traced to its source. Every access can be audited. Every hospital can either use OpesCare directly or connect its existing system to OpesCare through APIs and connectors.

OpesCare should not initially position itself as a replacement for every hospital system. That would create resistance. The better strategy is to position OpesCare as:

**A patient identity, medical record exchange, and healthcare operations platform that can work with existing hospital systems while also providing a complete system for facilities without software.**

---

## 4. Design Principles

OpesCare must be built around patient-centered identity, interoperability before replacement, consent and controlled access, clinical safety, low-infrastructure readiness, and modular growth.

The patient should not be trapped inside one hospital’s records. The patient should have one health identity that follows them across healthcare providers.

OpesCare should work with existing hospital information systems, laboratory systems, pharmacy systems, and insurance systems. Facilities without software should be able to use OpesCare directly.

Health information is sensitive. Access must be based on patient consent, provider role, facility relationship, legal basis, and emergency rules.

The system must reduce clinical risk. Records must be source-attributed, timestamped, verified where possible, and protected against silent overwriting.

The platform must support environments with unstable internet, power interruptions, paper-heavy workflows, and limited digital literacy.

OpesCare should begin with a strong MVP and expand into advanced modules without breaking the foundation.

---

## 5. Complete Platform Scope

OpesCare should include the following major modules:

1. Patient Identity Service
2. Master Patient Index
3. Patient Portal and Mobile App
4. Provider Portal
5. Facility Management
6. Practitioner Management
7. Appointment, Queue, and Visit Management
8. Reception and Registration Module
9. Triage and Vitals Module
10. Consultation and Clinical Documentation Module
11. Clinical Timeline
12. Laboratory Module
13. Pharmacy and Prescription Module
14. Billing, Payments, and Cashier Module
15. Insurance and Claims Module
16. Inventory and Medical Stock Module
17. Inpatient, Ward, and Bed Management
18. Nursing Operations Module
19. Emergency Access Module
20. Consent Management Engine
21. Access Control Engine
22. Referral Network
23. Imaging and Radiology Module
24. Immunization Module
25. Maternal and Child Health Module
26. Chronic Disease Management Module
27. Patient Self-Upload and Legacy Record Digitization
28. Data Migration and Historical Record Import
29. Multi-Facility and Branch Management
30. Hospital Integration Gateway
31. API Gateway
32. Developer Platform and Partner Ecosystem
33. Webhooks and Event Notifications
34. Record Synchronization Engine
35. Interoperability Adapter Layer
36. Notification and Communication Service
37. Patient Health Education Module
38. Device and Remote Monitoring Integration
39. Telemedicine and Remote Consultation Module
40. Research and Ethics-Controlled Data Access
41. Public Health Reporting and Analytics
42. Security and Privacy Layer
43. Audit and Compliance Engine
44. Administration Console
45. Offline Sync and Downtime Continuity Service
46. Regulatory Compliance and Country Configuration
47. Trust, Governance, and Oversight Model

---

## 6. OpesCare Health ID

The OpesCare Health ID is the public identity issued to each patient. It is the key that allows the patient to be recognized across facilities.

A weak format would be a sequential number such as `OC-0000001`. This is simple but risky because it can be guessed. A stronger visible format is `OC-CMR-7KQ9-MP42-X8D1`.

This format can include `OC` for OpesCare, a country or deployment code, random alphanumeric segments, checksum validation, and non-sequential structure.

The public Health ID should not be the internal database primary key. Internally, OpesCare should use a UUID or ULID. The public Health ID is used for lookup, cards, QR codes, and patient communication. This separation improves privacy and security.

The Health ID can be delivered through physical card, QR code, NFC card, mobile app, SMS, printable certificate, USSD lookup, and biometric-assisted lookup where legally permitted.

The QR code should not contain the full medical history. It should contain a secure identifier or token that points to the OpesCare system. Sensitive medical information should remain protected behind authentication and authorization.

---

## 7. Patient Registration

Patient registration is the entry point into OpesCare.

A patient may be registered by a hospital, clinic, public health program, insurance program, community health worker, or through self-registration. During registration, OpesCare captures demographic and identity information.

Typical registration fields include full name, date of birth, sex, phone number, address, emergency contact, next of kin, national ID where applicable, insurance number where applicable, blood group if known, allergies if known, and guardian details for minors.

Before creating a new patient, the system must search the Master Patient Index to detect possible duplicates. This prevents one patient from having multiple identities.

If no match exists, OpesCare creates a new patient profile, generates a Health ID, creates initial consent settings, and issues the patient’s card or digital identity.

If a possible match exists, the registrar must review the match. The system should not automatically merge uncertain identities.

---

## 8. Master Patient Index

The Master Patient Index, or MPI, is responsible for matching patient records across facilities.

Patient matching is difficult because different facilities may record names differently. A patient may use different phone numbers, spelling variations, nicknames, or incomplete demographic details.

The MPI should support deterministic and probabilistic matching.

Deterministic matching uses exact identifiers such as OpesCare Health ID, verified national ID, insurance ID, or biometric reference where legally allowed.

Probabilistic matching uses scoring across fields such as name similarity, date of birth, sex, phone number, address, guardian name, and facility history.

The MPI must support duplicate detection, possible match review, patient merge, patient unmerge, identity verification status, identity correction, and audit logs for all identity operations.

Wrong merges can be dangerous. Therefore, patient merging must be controlled, reviewed, and reversible.

---

## 9. Facility Management

Facility Management registers and controls hospitals, clinics, laboratories, pharmacies, imaging centers, insurers, and public health organizations.

Each facility profile should include facility name, facility type, license information, address, departments, contact persons, operating status, integration mode, approved services, and branch relationship.

A facility may be active, pending verification, suspended, archived, or blacklisted.

Facility permissions must depend on facility type. A laboratory can submit lab results. A pharmacy can dispense prescriptions. A hospital can create encounters. An insurer can verify coverage and process claims. A public health authority can receive authorized reports.

OpesCare must verify facilities before granting access. Fake or unverified facilities must not access patient records.

---

## 10. Practitioner Management

Practitioner Management controls doctors, nurses, pharmacists, lab scientists, claims officers, receptionists, administrators, and other users.

Each user account should be linked to person identity, role, facility, department, professional license where applicable, access permissions, authentication method, and account status.

A practitioner may work in multiple facilities. Access should depend on the facility context selected during login or session start.

Account sharing must be prevented as much as possible. Shared accounts make audit logs useless. Each user must have an individual account.

High-risk users such as doctors, administrators, and integration managers should use multi-factor authentication.

---

## 11. Appointment, Queue, and Visit Management

OpesCare should manage patient flow, not only records.

The appointment module allows patients or staff to book visits with doctors, departments, or facilities. It should support walk-ins, scheduled appointments, follow-ups, emergency visits, and referrals.

The queue module allows facilities to organize patients by arrival time, appointment time, priority level, department, and provider availability.

Features include appointment booking, doctor schedules, walk-in registration, queue numbers, waiting room display, priority queue rules, follow-up scheduling, missed appointment tracking, appointment reminders, department routing, and estimated waiting time.

This module makes OpesCare operationally useful to hospitals.

---

## 12. Reception and Check-In Workflow

When a patient arrives at a facility, reception begins the visit.

The receptionist searches for the patient using Health ID, QR code, phone number, name, date of birth, national ID, or insurance number. If the patient exists, the receptionist verifies demographic details and starts a visit. If the patient does not exist, the receptionist registers the patient and issues a Health ID.

The receptionist then selects the visit type. This may be outpatient consultation, emergency visit, antenatal visit, vaccination, lab-only visit, pharmacy-only visit, referral visit, or follow-up.

The system creates an encounter or visit record. The patient is then routed to triage, billing, doctor consultation, lab, pharmacy, or another department depending on facility workflow.

---

## 13. Triage and Vitals Module

Triage captures early clinical information before consultation.

The nurse or triage officer records temperature, blood pressure, pulse, respiratory rate, oxygen saturation, weight, height, BMI, pain score, presenting complaint, pregnancy status where relevant, and emergency severity.

Triage information becomes part of the encounter. Critical values can trigger alerts. For example, very high blood pressure or low oxygen saturation can prioritize the patient.

Triage is especially important for emergency care, outpatient clinics, maternal care, pediatrics, and chronic disease management.

---

## 14. Consultation and Clinical Documentation

The consultation module is used by doctors and clinical officers.

During consultation, the provider can view authorized patient history, allergies, chronic conditions, current medications, recent lab results, past visits, and active warnings.

The provider records presenting complaint, history of present illness, examination findings, diagnosis, differential diagnosis, treatment plan, prescriptions, lab requests, imaging requests, procedures, referrals, sick leave notes, follow-up instructions, admission decision, and discharge summary.

Clinical documentation should be structured but not too slow. The system should allow templates, quick notes, voice-to-text where available, and reusable forms.

---

## 15. Clinical Timeline

The Clinical Timeline displays a patient’s medical history in chronological order.

It should show visits, diagnoses, prescriptions, lab results, imaging reports, admissions, surgeries, vaccinations, referrals, emergency access events, insurance events, and pharmacy dispensing events.

The timeline should support filters by date, facility, provider, diagnosis, medication, result type, and record source.

Each record must show source attribution. A provider should know which facility created a record, when it was created, and whether it is verified.

---

## 16. Laboratory Module

The Laboratory Module handles lab orders, sample collection, result entry, validation, and publication.

The workflow begins when a provider orders a test. The lab receives the request, collects the sample, assigns a sample ID, processes the sample, enters results, validates results, and releases them.

Each lab result should include patient ID, order ID, sample ID, test name, standard code where available, result value, unit, reference range, abnormal flag, collection time, result time, validating scientist, and source lab.

Critical results should trigger provider alerts.

The lab module should support direct use by labs and integration with external laboratory information systems.

---

## 17. Imaging and Radiology Module

The Imaging and Radiology Module manages imaging requests and reports.

It should support imaging orders, radiology appointments, report upload, radiologist validation, image links, and DICOM/PACS integration for advanced facilities.

OpesCare may not store all imaging files directly in early versions because medical images are large. It can store reports and secure links to imaging systems.

Access to imaging must be controlled because radiology data is sensitive and storage-intensive.

---

## 18. Pharmacy and Prescription Module

The Pharmacy Module manages prescriptions, medication verification, dispensing, and medication history.

When a doctor creates a prescription, it is attached to the patient’s record. A connected pharmacy can verify the prescription, check allergies, confirm medicine availability, dispense the medication, and record the dispensing event.

A prescription should include prescription ID, prescribing doctor, facility, medication name, dose, frequency, duration, route, instructions, validity period, and dispensing status.

Prescription statuses may include issued, partially dispensed, fully dispensed, cancelled, expired, or rejected.

The module should reduce fraud, duplicate medication, unsafe combinations, and uncertainty about whether prescribed medication was collected.

---

## 19. Billing, Payments, and Cashier Module

OpesCare should support billing because many hospital workflows depend on payment.

The billing module should handle consultation fees, lab fees, imaging fees, procedure fees, medication fees, admission fees, insurance co-payments, discounts, invoices, receipts, refunds, outstanding balances, cashier reconciliation, and payment reports.

Payment methods may include cash, mobile money, card payments, bank transfer, insurance coverage, voucher, or employer sponsorship.

Facilities should be able to define price lists. Different branches may have different prices.

Billing should connect to clinical workflow without blocking emergency care where policy requires immediate treatment.

---

## 20. Insurance and Claims Module

The Insurance Module links insurance policies to the patient Health ID.

It should support insurance enrollment, policy verification, coverage checking, benefit limits, co-payment rules, pre-authorization, claims submission, claims review, claims approval or rejection, and claim payment tracking.

Insurers should not automatically access the full patient record. They should only see information necessary for eligibility, authorization, and claims processing.

Claims should be linked to real encounters, procedures, prescriptions, and lab results to reduce fraud.

---

## 21. Inventory and Medical Stock Module

Inventory is necessary for pharmacies, labs, wards, and hospitals.

The module should track medications, lab reagents, test kits, consumables, medical supplies, batches, expiry dates, suppliers, purchase orders, stock transfers, stock adjustments, low-stock alerts, and expired stock alerts.

Pharmacy dispensing should deduct stock. Lab test processing may deduct reagents or consumables. Ward usage may deduct supplies.

Inventory should support branch-specific stock and central stock reporting for multi-branch organizations.

---

## 22. Inpatient, Ward, and Bed Management

Hospitals need inpatient workflows.

The module should support admission orders, ward assignment, bed assignment, ward transfers, nursing notes, doctor rounds, medication administration, procedure notes, vital signs charts, fluid balance charts, discharge planning, discharge summary, and death record where applicable.

Bed management should show occupied beds, available beds, reserved beds, cleaning status, and ward capacity.

Inpatient care requires stronger nursing and medication workflows than ordinary outpatient visits.

---

## 23. Nursing Operations Module

Nurses are central users of OpesCare.

The Nursing Module should include triage notes, vital signs, nursing care plans, medication administration record, wound care notes, patient observation charts, shift handover, inpatient monitoring, escalation alerts, and procedure assistance notes.

For admitted patients, nurses should be able to record repeated observations over time. Doctors should be able to review these trends during rounds.

---

## 24. Emergency Access Module

Emergency access allows authorized providers to access limited patient information when normal consent cannot be obtained.

The emergency profile may include patient name, approximate age or date of birth, blood group, critical allergies, chronic conditions, high-risk medications, emergency contacts, and important warnings.

Emergency access must be restricted, logged, and reviewed. The provider must state a reason. The patient or compliance officer may be notified after the event depending on policy.

Repeated emergency access by a user should trigger compliance review.

---

## 25. Consent Management Engine

Consent controls who can access what.

The consent engine should support one-time consent, time-limited consent, facility-level consent, provider-level consent, record-type consent, emergency override, guardian consent, consent revocation, and consent audit trail.

Consent may be granted through mobile app approval, OTP, PIN, digital signature, guardian approval, facility policy, or emergency override.

Consent must be specific. A patient may allow a doctor to view lab results but not unrelated sensitive records.

---

## 26. Access Control Engine

Access control determines what each user can do.

OpesCare should use both role-based access control and attribute-based access control.

Role-based access checks whether the user is a doctor, nurse, pharmacist, lab scientist, receptionist, claims officer, or administrator.

Attribute-based access checks context such as facility, department, patient consent, emergency status, treatment relationship, location, time, and record sensitivity.

The principle should be least privilege. Users should only access what they need to perform their duties.

---

## 27. Referral Network

The Referral Network allows a patient to move from one provider to another without losing clinical context.

A referral should include referring provider, receiving facility, reason for referral, urgency, clinical summary, attached documents, relevant lab results, medications, and consent scope.

The receiving facility can accept, reject, or request more information. After treatment, it can send feedback to the referring provider.

This closes the referral loop and reduces paper referral failures.

---

## 28. Immunization Module

The Immunization Module tracks vaccinations.

It should include vaccine type, dose number, date given, facility, batch number, next dose date, certificate status, and missed dose alerts.

This is useful for child health, school requirements, occupational health, travel health, and public health campaigns.

---

## 29. Maternal and Child Health Module

The Maternal and Child Health Module supports pregnancy, delivery, postnatal care, and child health.

For maternal care, it should track antenatal visits, expected delivery date, pregnancy risk factors, lab results, ultrasound summaries, delivery outcome, complications, and postnatal follow-up.

For child health, it should track birth records, growth monitoring, immunizations, pediatric visits, nutrition status, and guardian linkage.

This module is important because maternal and child health records are often paper-based and easily lost.

---

## 30. Chronic Disease Management Module

The Chronic Disease Module supports long-term conditions such as hypertension, diabetes, asthma, kidney disease, heart disease, HIV, and other chronic illnesses.

It should track diagnosis date, treatment plan, medications, lab trends, vital trends, follow-up visits, complications, adherence notes, and reminders.

Doctors should be able to view trends over time, such as blood pressure, glucose, kidney function, or viral load where applicable.

---

## 31. Patient Self-Upload and Legacy Record Digitization

Patients often have old hospital books, paper prescriptions, lab PDFs, vaccination cards, discharge summaries, and referral letters.

OpesCare should allow patients or facilities to upload old records. These records should be clearly marked by source: patient-submitted, facility-submitted, migrated historical record, or clinically verified.

The system may use OCR-assisted extraction, but extracted data must be reviewed before becoming trusted clinical data.

This allows OpesCare to capture medical history that existed before the platform was adopted.

---

## 32. Data Migration and Historical Import

When onboarding hospitals with existing systems, OpesCare may need to import historical data.

Migration should include patient records, old facility IDs, encounters, lab history, prescriptions, billing history where needed, insurance records, and diagnosis history.

Migration must include mapping, validation, duplicate detection, and source labeling.

Migrated records should be marked as historical. Missing fields should be flagged rather than silently guessed.

---

## 33. Multi-Facility and Branch Management

OpesCare must support organizations with multiple branches.

A hospital group may operate several clinics. An insurance network may work with many facilities. A government health district may supervise multiple health centers.

The system should support parent organization, branches, branch-specific users, branch-specific inventory, branch-specific billing, shared patient identity, organization-level analytics, and branch-level permissions.

This allows OpesCare to serve both small clinics and large health networks.

---

## 34. Hospital Integration Gateway

Hospitals may already have their own systems. OpesCare should connect to them instead of forcing immediate replacement.

The integration gateway supports four modes.

Direct API integration allows advanced hospital systems to connect directly to OpesCare APIs.

Adapter integration allows a connector to translate data from the hospital’s existing system into OpesCare’s standard format.

File-based integration allows hospitals to export CSV, Excel, JSON, XML, or other structured files for import.

OpesCare Lite allows facilities without software to use OpesCare’s web or mobile portal directly.

This flexible model allows OpesCare to work across different levels of digital maturity.

---

## 35. Record Synchronization Engine

The synchronization engine receives and processes medical events from facilities.

When a record arrives, the engine checks source system, facility authorization, user authorization, patient identity, schema validity, duplicate status, timestamp, clinical context, and idempotency key.

Valid records are stored as events in the patient timeline. Duplicate records are rejected or linked. Conflicting records are flagged.

OpesCare should append events rather than overwrite history. If two hospitals disagree about a patient’s allergy, both records should remain visible with source attribution until reviewed.

---

## 36. Pull and Push Data Flow

A pull operation occurs when a facility requests patient information from OpesCare.

The facility identifies the patient. The provider requests access. The consent engine checks permission. The access control engine evaluates role and context. Authorized records are retrieved. The provider views the data. The audit log records the access.

A push operation occurs when a facility sends new information to OpesCare.

The facility creates a medical event. The connector or portal formats the event. The API Gateway authenticates the source. The validation engine checks the data. The MPI confirms patient identity. The synchronization engine checks duplicates and conflicts. The event is added to the patient timeline. Notifications are triggered where needed. The audit log records the action.

This is how OpesCare works with different hospitals even when they have different internal systems.

---

## 37. Interoperability Standards

OpesCare should use recognized healthcare interoperability standards.

Important standards include HL7 FHIR for modern health data APIs, HL7 v2 for legacy hospital messaging, ICD-10 or ICD-11 for diagnoses, LOINC for laboratory tests, SNOMED CT where licensing and context allow, and DICOM for medical imaging.

FHIR resources that should be considered include Patient, Encounter, Observation, DiagnosticReport, MedicationRequest, MedicationDispense, AllergyIntolerance, Condition, Immunization, Practitioner, Organization, Consent, DocumentReference, and Claim.

OpesCare does not need to implement every standard fully at launch, but the architecture should be designed around standards from the beginning.

---

## 38. API Gateway

The API Gateway is the secure entry point for external systems.

It handles authentication, authorization, request validation, rate limiting, routing, throttling, logging, API versioning, and security monitoring.

External systems should never access internal databases directly.

API operations may include create patient, search patient, request access, grant consent, create encounter, submit lab result, issue prescription, record dispensing, verify insurance, submit claim, retrieve emergency profile, and fetch patient timeline.

All API calls must be logged.

---

## 39. Developer Platform and Partner Ecosystem

OpesCare should provide a developer platform for approved partners.

The developer platform should include API documentation, sandbox environment, API keys, OAuth or token-based access, test patients, webhook configuration, integration certification, partner approval workflow, and developer support.

This allows third-party hospital systems, lab systems, insurance systems, pharmacy systems, and approved apps to integrate safely.

---

## 40. Webhooks and Event Notifications

Webhooks allow external systems to receive updates automatically.

Webhook events may include patient created, consent granted, consent revoked, encounter created, lab result ready, prescription issued, prescription dispensed, claim submitted, claim approved, referral accepted, emergency access used, and record updated.

Hospitals with their own systems can subscribe to these events and update their local software.

Webhook security must include signatures, retries, event IDs, and replay protection.

---

## 41. Patient Portal and Mobile App

The Patient Portal allows patients to interact with their health identity.

Patients should be able to view their Health ID, view basic profile, view medical timeline, view lab results, view prescriptions, manage consent, approve access requests, see access logs, update contact details, download permitted records, manage dependents, receive reminders, and upload legacy records.

The patient portal should be available through web and mobile. Basic functions may be available through SMS or USSD in low-connectivity settings.

---

## 42. Family, Guardian, and Dependent Management

The family module allows legal guardians to manage dependents.

It should support parents managing children, caregivers managing elderly patients, legal guardianship, consent for minors, transition to adult control, emergency family contacts, and family medical history.

The system must handle this carefully. A parent or guardian should not have unlimited access forever. When a child reaches the legal age defined by policy, control should transition appropriately.

---

## 43. Telemedicine and Remote Consultation

Telemedicine expands OpesCare beyond physical hospital visits.

The module should support online appointment booking, video consultation, secure chat, remote prescriptions, remote lab requests, digital sick notes, follow-up visits, remote chronic disease monitoring, and doctor availability.

Telemedicine records should be added to the same patient timeline as physical visits.

Remote care must follow licensing, consent, and prescription rules.

---

## 44. Device and Remote Monitoring Integration

OpesCare can integrate with medical devices and wearables.

Supported device categories may include blood pressure monitors, glucose meters, pulse oximeters, smart watches, hospital monitoring devices, and home care devices.

Readings should be clearly marked as device-submitted, patient-submitted, or clinically verified.

This is valuable for chronic disease monitoring.

---

## 45. Clinical Forms and Template Builder

Different specialties need different forms.

The Template Builder should allow authorized administrators to create forms for outpatient consultation, antenatal care, pediatric visit, dental visit, eye clinic, surgical assessment, emergency care, chronic disease follow-up, and nursing observation.

Templates should support required fields, dropdowns, checkboxes, numeric values, text fields, date fields, and form versioning.

This makes OpesCare adaptable across specialties.

---

## 46. Clinical Decision Support

Clinical Decision Support helps providers notice risks.

It may include allergy warnings, drug interaction checks, duplicate medication warnings, abnormal lab flags, pregnancy medication warnings, age-based dosage alerts, renal dose adjustment prompts, vaccination reminders, and chronic disease follow-up reminders.

Clinical decision support must assist, not replace, professional judgment. Alerts should be prioritized to avoid alert fatigue.

---

## 47. Patient Communication and Health Education

OpesCare should communicate with patients beyond record storage.

The module should support appointment reminders, medication reminders, vaccination reminders, follow-up reminders, maternal health messages, child health reminders, chronic disease education, and post-discharge instructions.

Messages should avoid exposing sensitive health information through insecure channels.

---

## 48. Research and Ethics-Controlled Data Access

OpesCare may become valuable for health research, but research access must be controlled.

The module should support research data requests, ethics approval workflow, data-use agreements, anonymized datasets, restricted dashboards, audit logs, and approval expiry.

Researchers should not access identifiable patient data unless legally and ethically approved.

---

## 49. Public Health Reporting and Analytics

OpesCare can support public health reporting through aggregated and permissioned data.

Analytics may include disease trends, outbreak signals, vaccination coverage, maternal health indicators, child health indicators, facility utilization, lab result trends, medication usage, and referral patterns.

Public health reporting must protect patient privacy and follow applicable laws.

---

## 50. Data Architecture

OpesCare should use modular data architecture.

Recommended components include a relational database for identity and transactions, document database or object storage for documents, message queue for events, cache for performance, audit log store, analytics warehouse for aggregated reporting, and backup storage.

The patient record should be event-based. Each consultation, lab result, prescription, dispense event, diagnosis, or referral is stored as an event linked to the patient.

This is safer than treating the patient record as one editable document.

---

## 51. Core Data Entities

Core entities include Patient, PatientIdentifier, Facility, Organization, Branch, Practitioner, UserAccount, Role, Permission, Encounter, Appointment, QueueTicket, VitalSigns, Diagnosis, Allergy, Medication, Prescription, MedicationDispense, LabOrder, LabResult, ImagingOrder, ImagingReport, Procedure, Admission, Ward, Bed, NursingNote, Immunization, Referral, InsurancePolicy, Claim, Invoice, Payment, InventoryItem, StockBatch, StockMovement, ConsentGrant, AccessLog, AuditEvent, IntegrationEvent, Notification, and DocumentReference.

---

## 52. Security Architecture

Security must be built into every layer.

Required controls include encryption in transit, encryption at rest, strong password hashing, MFA for high-risk users, role-based access control, attribute-based access control, API authentication, token expiration, session management, audit logs, intrusion detection, vulnerability scanning, penetration testing, secure backups, and incident response planning.

The system should monitor suspicious access, repeated failed login attempts, unusual record access, emergency access abuse, abnormal API traffic, and large data exports.

---

## 53. Privacy Model

Privacy must be treated as a core product feature.

The system should support data minimization. Users should only see the information required for their role and context.

Sensitive data categories may require additional protection. These may include mental health, reproductive health, HIV status, sexual health, genetic data, and domestic violence-related records depending on applicable law and policy.

Patients should be able to see who accessed their records.

---

## 54. Audit and Compliance Engine

Every important action must be logged.

Audit logs should capture actor, role, facility, patient, action, record type, timestamp, device, IP address, reason, consent status, and emergency override status.

Audit logs must be tamper-resistant. Administrators should not be able to silently delete them.

Compliance dashboards should detect suspicious activity and support investigations.

---

## 55. Offline Sync and Hospital Downtime Mode

OpesCare must support unreliable infrastructure.

Offline mode should allow facilities to continue essential operations during internet downtime.

Supported offline functions may include local patient lookup from encrypted cache, new patient registration, encounter capture, vitals capture, prescription capture, lab sample capture, emergency profile access, and queued synchronization.

When connectivity returns, the system synchronizes queued data, checks duplicates, resolves conflicts, and updates audit logs.

Downtime mode should also include printable fallback forms and reconciliation workflows.

---

## 56. Regulatory Compliance and Country Configuration

OpesCare must be configurable by country or deployment jurisdiction.

Country configuration should include health data laws, age of consent, guardian rules, data retention rules, data localization rules, public health reporting rules, insurance regulations, ID formats, language settings, currency, and facility licensing requirements.

This allows OpesCare to expand into different markets without redesigning the whole system.

---

## 57. Trust, Governance, and Oversight

Because OpesCare handles sensitive health identity, it needs a trust framework.

The governance model should include Opesware operational responsibility, privacy officer, security officer, clinical advisory board, patient rights representation, facility representation, regulator relationship, ethics committee, breach review process, and access abuse review process.

The governance model should define who controls data, who can request access, how disputes are handled, and how breaches are managed.

---

## 58. Daily Healthcare Operations Workflow

A complete patient visit works as follows.

The patient arrives at the facility. Reception identifies the patient using Health ID, QR code, phone number, name, or another approved identifier. If the patient exists, the system retrieves the profile. If the patient does not exist, a new profile is created.

The receptionist checks the patient in and creates a visit. The patient may be routed to billing, triage, consultation, emergency care, lab, pharmacy, or another department.

At triage, a nurse records vital signs and presenting complaint. Critical findings may prioritize the patient.

The doctor opens the consultation, requests access where necessary, reviews relevant history, records clinical findings, diagnoses, orders tests, prescribes medication, refers the patient, admits the patient, or discharges the patient.

If lab tests are ordered, the lab receives the request, collects samples, processes results, validates them, and publishes them to the patient record.

If medication is prescribed, the pharmacy verifies the prescription, checks stock and allergies, dispenses medication, and records the dispensing event.

If payment is required, the billing module creates invoices, processes payments, and updates the visit status.

At the end of the visit, the encounter is closed. The patient timeline is updated. Relevant notifications are sent. The full event is auditable.

---

## 59. MVP Scope

The first version of OpesCare should focus on proving the core value.

MVP modules should include patient registration, Health ID generation, QR code card, facility management, provider login, patient search, consent capture, basic encounter records, triage and vitals, consultation notes, allergy records, prescription records, lab result upload, patient timeline, billing basics, audit logs, and basic reporting.

The MVP should be tested in a small number of facilities before expansion.

---

## 60. Phase Two Scope

Phase Two should add hospital system integration, API Gateway expansion, webhooks, pharmacy dispensing, insurance verification, claims processing, inventory management, referrals, appointment and queue management, offline sync, patient mobile app, legacy record upload, and multi-branch management.

This phase turns OpesCare from a basic Health ID system into a healthcare operating platform.

---

## 61. Phase Three Scope

Phase Three should add advanced interoperability, public health reporting, chronic disease programs, maternal and child health programs, telemedicine, device integration, DICOM/PACS integration, AI-assisted summaries, research data access, advanced analytics, and national-scale deployment features.

This phase positions OpesCare as full digital health infrastructure.

---

## 62. Revenue Model

Potential revenue streams include hospital SaaS subscription, clinic subscription, integration setup fee, API usage fee, smart card issuance fee, insurance verification fee, claims processing fee, pharmacy network fee, lab result delivery fee, enterprise health program fee, government deployment contract, and analytics service where legally and ethically permitted.

The patient Health ID should remain affordable or free where possible to encourage adoption.

---

## 63. Key Risks

Major risks include hospital resistance, poor data quality, duplicate patient records, privacy concerns, cybersecurity threats, weak internet connectivity, regulatory uncertainty, integration complexity, clinician adoption failure, misuse of patient data, and wrong patient matching.

These risks must be managed through governance, training, technical design, legal compliance, security, and careful rollout.

---

## 64. Success Metrics

OpesCare should track number of registered patients, number of active facilities, number of encounters recorded, number of Health ID lookups, duplicate patient rate, average patient lookup time, number of lab results synchronized, prescription verification rate, claims processed, consent approval time, emergency access events, system uptime, provider satisfaction, patient satisfaction, integration success rate, and data quality score.

Metrics should guide product improvement and operational decisions.

---

## 65. Final Consolidated Positioning

OpesCare is not just a digital hospital book. It is a secure digital health identity, medical record exchange, and healthcare operations platform.

It connects patients, hospitals, clinics, labs, pharmacies, insurers, and public health systems around one patient-centered Health ID.

It works with different hospitals by supporting direct APIs, adapters, file imports, and OpesCare Lite. It synchronizes records by receiving medical events, validating them, linking them to the correct patient, storing them in the patient timeline, and preserving source attribution.

The foundation of OpesCare is identity, interoperability, consent, synchronization, clinical workflow, security, auditability, and governance.

If implemented carefully, OpesCare can become a major health infrastructure product for Opesware and a practical replacement for fragmented paper-based medical records.

---

## 66. Final Product Statement

**OpesCare is a patient-centered digital health ID and healthcare operating platform that gives every patient one secure medical identity, connects healthcare providers through interoperable records, and enables hospitals, clinics, labs, pharmacies, insurers, and public health systems to deliver safer, faster, and more coordinated care.**



---

# 67. Detailed Operational Flows

This section defines the detailed operational flows of OpesCare. Each flow is written in sequence, followed immediately by a review of gaps, bugs, risks, and controls before moving to the next flow.

---

## Flow 1: Patient Pre-Registration Flow

### Purpose

This flow allows a patient to create a basic OpesCare profile before visiting a hospital or clinic. Pre-registration reduces waiting time at reception and allows the patient to receive a provisional Health ID.

### Actors

The main actors are the patient, guardian where applicable, OpesCare patient portal, identity service, Master Patient Index, notification service, and audit engine.

### Step-by-Step Flow

1. The patient opens the OpesCare web portal or mobile app.
2. The patient selects “Create Health ID” or “Register Patient.”
3. The system displays a privacy notice explaining how patient data will be used.
4. The patient accepts the privacy notice and proceeds.
5. The patient enters basic demographic information: full name, date of birth, sex, phone number, address, and emergency contact.
6. If the patient is a child, a guardian section is displayed.
7. The guardian enters their own name, phone number, relationship to the child, and identity information where required.
8. The system requests phone verification.
9. The notification service sends an OTP to the phone number.
10. The patient or guardian enters the OTP.
11. The identity service verifies the OTP.
12. The Master Patient Index checks whether the patient may already exist.
13. If no duplicate is found, the system creates a provisional patient profile.
14. The system generates a provisional OpesCare Health ID.
15. The patient receives the Health ID by SMS, app notification, and on-screen display.
16. The system marks the profile as “self-registered, pending facility verification.”
17. The audit engine logs the registration event.

### Expected Output

The patient receives a provisional OpesCare Health ID and can use it during their next hospital visit.

### Review: Gaps, Bugs, and Controls

A major gap is duplicate registration. A patient may register twice using different phone numbers. The MPI must compare name, date of birth, sex, phone, and guardian details to detect possible duplicates.

A possible bug is OTP failure due to poor network. The system should allow resend limits, voice OTP where available, and facility-assisted verification.

A risk is false information during self-registration. Therefore, self-registered profiles should remain provisional until verified by a facility, insurer, or approved identity authority.

A privacy gap is unclear consent. The registration flow must clearly explain data use and record the patient’s consent.

A security bug would be allowing unlimited OTP attempts. The system must limit attempts and temporarily block suspicious registration attempts.

---

## Flow 2: Patient Registration at Hospital Reception

### Purpose

This flow registers a new patient directly at a hospital, clinic, or health facility.

### Actors

The actors are the receptionist, patient, guardian where applicable, facility portal, identity service, Master Patient Index, Health ID generator, notification service, and audit engine.

### Step-by-Step Flow

1. The patient arrives at the facility.
2. The receptionist asks whether the patient already has an OpesCare Health ID.
3. If the patient has no Health ID, the receptionist opens the registration module.
4. The receptionist captures demographic details: full name, date of birth, sex, phone number, address, emergency contact, and next of kin.
5. The receptionist captures optional identity details such as national ID, insurance number, or existing hospital number.
6. If the patient is a child, the receptionist captures guardian details.
7. The system sends the captured information to the Master Patient Index.
8. The MPI searches for possible matches.
9. If no match is found, the receptionist proceeds to create a new patient.
10. If possible matches are found, the system displays match candidates with confidence levels.
11. The receptionist reviews the candidates with the patient.
12. If one candidate is confirmed, the existing profile is used instead of creating a duplicate.
13. If none of the candidates match, a new profile is created.
14. The Health ID generator creates a new OpesCare Health ID.
15. The system prints or displays a QR code card.
16. The patient receives the Health ID by SMS where possible.
17. The facility can immediately check the patient in for a visit.
18. The audit engine logs who registered the patient, where, and when.

### Expected Output

A verified or facility-created patient profile exists with a unique Health ID, ready for clinical use.

### Review: Gaps, Bugs, and Controls

A major risk is duplicate creation when reception is busy. The system should require users to review high-confidence matches before allowing a new profile.

A bug may occur if date of birth is unknown. The system should allow approximate age while marking the date as estimated.

A gap is patients without phones. The system must allow registration without phone number and provide printed Health ID cards.

A privacy bug would be exposing match candidates with too much sensitive information. Match results should show only enough demographic information to confirm identity.

A data quality issue is misspelled names. The system should support phonetic matching and later correction.

---

## Flow 3: Returning Patient Check-In Flow

### Purpose

This flow checks in an existing OpesCare patient for a new visit.

### Actors

The actors are the patient, receptionist, facility portal, patient identity service, consent engine, queue module, billing module, and audit engine.

### Step-by-Step Flow

1. The patient arrives at the facility.
2. The receptionist requests the patient’s Health ID, QR card, phone number, or name.
3. The receptionist searches for the patient.
4. The system displays matching patient profiles.
5. The receptionist verifies the patient using at least two identifiers, such as name and date of birth.
6. The receptionist selects the confirmed patient profile.
7. The system checks whether the facility has an existing relationship with the patient.
8. If access is required, the consent engine requests consent from the patient.
9. The patient grants consent using OTP, PIN, app approval, or signed facility consent.
10. The receptionist selects visit type: outpatient, emergency, follow-up, lab-only, pharmacy-only, referral, or admission.
11. The system creates a new encounter.
12. The queue module assigns a queue number.
13. If billing is required before consultation, the patient is routed to cashier.
14. If triage is required, the patient is routed to triage.
15. The audit engine logs the check-in.

### Expected Output

The patient is checked in, an encounter is created, and the patient is routed to the correct department.

### Review: Gaps, Bugs, and Controls

A key gap is wrong patient selection. The system should require confirmation using at least two identifiers.

A bug may occur if consent is requested from a patient who is unconscious. In emergency scenarios, the system should route through emergency access instead of normal consent.

A workflow issue is payment before emergency care. Facility policy should allow emergency bypass when immediate treatment is required.

A queue bug may occur if one patient is placed in multiple queues accidentally. The system should allow only one active queue ticket per department per encounter unless explicitly transferred.

---

## Flow 4: Appointment Booking Flow

### Purpose

This flow allows a patient or facility staff member to schedule a future visit.

### Actors

The actors are the patient, receptionist, appointment module, provider schedule service, notification service, billing module where prepayment applies, and audit engine.

### Step-by-Step Flow

1. The patient opens the app, calls the facility, or visits reception.
2. The patient selects or requests an appointment.
3. The system identifies the patient using Health ID or patient search.
4. The patient selects facility, department, provider, service type, and preferred date.
5. The schedule service checks provider availability.
6. Available appointment slots are displayed.
7. The patient or receptionist selects a slot.
8. If prepayment is required, the billing module generates an invoice.
9. The patient pays or the appointment remains pending payment depending on facility policy.
10. The appointment is confirmed.
11. The notification service sends confirmation by SMS, app notification, or email.
12. Reminder notifications are scheduled.
13. The audit engine logs the appointment creation.

### Expected Output

A confirmed or pending appointment exists with date, time, facility, department, provider, and patient details.

### Review: Gaps, Bugs, and Controls

A scheduling bug may allow double-booking. Provider schedules must lock slots during booking.

A gap is appointment booking for patients without phones. Reception-assisted booking should support printed appointment slips.

A bug may occur when payment fails after slot reservation. The system should release unpaid reserved slots after a configurable time.

A workflow gap is provider unavailability due to emergency or leave. The system should support rescheduling and patient notification.

---

## Flow 5: Walk-In Queue Flow

### Purpose

This flow manages patients who arrive without appointments.

### Actors

The actors are the patient, receptionist, queue module, triage nurse, provider, department users, notification service, and audit engine.

### Step-by-Step Flow

1. The patient arrives at the facility.
2. The receptionist identifies or registers the patient.
3. The receptionist creates a walk-in visit.
4. The queue module assigns a queue number based on department and service type.
5. If triage is required, the patient is routed to triage before doctor queue.
6. The triage nurse records severity.
7. The queue module adjusts priority based on severity, appointment status, emergency flag, pregnancy, child status, elderly status, or facility rules.
8. The patient waits until called.
9. The provider calls the next patient from the queue.
10. The patient enters consultation or service room.
11. The queue ticket status changes to “in service.”
12. After service, the ticket is marked completed, transferred, or routed to another department.
13. The audit engine logs queue movement.

### Expected Output

The patient is routed fairly and efficiently through the facility workflow.

### Review: Gaps, Bugs, and Controls

A bug may occur if priority rules are unclear. Facility administrators must configure queue rules.

A gap is manual queue manipulation. The system should log manual priority changes and require reasons.

A workflow risk is patients being skipped. Queue status should be visible to staff, and skipped patients should be marked with reasons.

A usability issue is patients not knowing their turn. Facilities may need display screens, SMS updates, or verbal calling support.

---

## Flow 6: Consent Granting Flow

### Purpose

This flow grants a provider or facility permission to access patient records.

### Actors

The actors are the patient, provider, facility, consent engine, access control engine, notification service, and audit engine.

### Step-by-Step Flow

1. A provider requests access to the patient’s record.
2. The provider selects the purpose: consultation, emergency review, referral, insurance claim, lab review, or pharmacy dispensing.
3. The provider selects requested scope: full clinical summary, recent visits, lab results, prescriptions, allergies, or specific document.
4. The consent engine checks whether valid consent already exists.
5. If no valid consent exists, the patient is prompted.
6. The patient receives a consent request by app, OTP, PIN prompt, or paper/digital consent at facility.
7. The patient reviews the request.
8. The patient approves or denies.
9. If approved, the consent engine creates a consent grant with scope and expiry.
10. The access control engine uses the consent grant to permit access.
11. The audit engine logs the request and decision.
12. The patient can later revoke the consent.

### Expected Output

The provider receives access only to the approved data scope for the approved period.

### Review: Gaps, Bugs, and Controls

A gap is patients who cannot understand the request. The system should support simple language and staff explanation.

A bug is consent without scope. Every consent must have scope, duration, and purpose.

A security risk is forged consent. The system should verify identity through OTP, PIN, app login, guardian authority, or signed consent.

A workflow issue is consent fatigue. Facilities may use policy-based consent for active treatment relationships, but this must be legally reviewed.

---

## Flow 7: Consent Revocation Flow

### Purpose

This flow allows a patient to withdraw previously granted access.

### Actors

The actors are the patient, consent engine, access control engine, notification service, provider portal, and audit engine.

### Step-by-Step Flow

1. The patient opens the patient portal or requests revocation at a facility.
2. The patient views active consent grants.
3. The patient selects the consent grant to revoke.
4. The system explains the consequence of revocation.
5. The patient confirms revocation.
6. The consent engine marks the grant as revoked.
7. The access control engine blocks future access under that grant.
8. The provider or facility may be notified where appropriate.
9. The audit engine logs the revocation.

### Expected Output

The selected provider or facility can no longer access records under the revoked consent.

### Review: Gaps, Bugs, and Controls

A bug may allow continued access through cached data. Cached records must respect revocation rules where technically possible.

A gap is revocation during active treatment. The system should handle clinical and legal exceptions carefully.

A risk is revoking consent after records have already been viewed. The system cannot erase what was already seen, but it can block future access and log previous access.

---

## Flow 8: Emergency Break-Glass Access Flow

### Purpose

This flow allows authorized emergency access when the patient cannot provide consent.

### Actors

The actors are emergency provider, facility, emergency access module, access control engine, audit engine, compliance officer, notification service, and patient where possible.

### Step-by-Step Flow

1. The emergency provider identifies the patient using Health ID, QR code, phone number, name, or other available identifier.
2. The provider selects “Emergency Access.”
3. The system requires the provider to enter a reason.
4. The access control engine checks whether the provider role is permitted to use emergency access.
5. The system displays a warning that emergency access is audited.
6. The provider confirms.
7. The system grants access only to the emergency profile.
8. The provider views critical allergies, blood group, chronic conditions, high-risk medications, emergency contacts, and important warnings.
9. The audit engine logs the access with reason, time, facility, provider, and records viewed.
10. The compliance officer can review the event.
11. The patient may receive a post-event notification when appropriate.

### Expected Output

The provider receives emergency-relevant information without exposing the full record.

### Review: Gaps, Bugs, and Controls

A risk is abuse of emergency access. The system must monitor frequency, require reasons, and support compliance review.

A bug would be showing full medical history in emergency mode. Emergency access must be restricted to critical data.

A gap is unidentified unconscious patients. The system may need biometric-assisted lookup where legal, or temporary unknown patient profiles.

A privacy concern is notifying the patient when it may create danger. Notification rules should be configurable for sensitive cases.

---

## Flow 9: Triage Flow

### Purpose

This flow captures early clinical assessment and prioritizes care.

### Actors

The actors are triage nurse, patient, queue module, vitals module, clinical alert engine, provider portal, and audit engine.

### Step-by-Step Flow

1. The patient is routed to triage after check-in.
2. The triage nurse opens the patient encounter.
3. The nurse verifies patient identity.
4. The nurse records presenting complaint.
5. The nurse records vital signs: temperature, blood pressure, pulse, respiratory rate, oxygen saturation, weight, height, and pain score.
6. The nurse records pregnancy status where relevant.
7. The system checks for abnormal values.
8. If critical values are detected, the clinical alert engine creates an alert.
9. The queue module updates patient priority if necessary.
10. The nurse saves the triage record.
11. The provider can view the triage summary before consultation.
12. The audit engine logs the triage action.

### Expected Output

The encounter contains triage data, and the patient is prioritized appropriately.

### Review: Gaps, Bugs, and Controls

A bug may occur if vital signs are entered with wrong units. The system should enforce units and valid ranges.

A risk is ignoring critical values. Critical alerts should be prominent and require acknowledgment.

A workflow gap is triage bypass for some visit types. Facility rules should define when triage is mandatory.

A data issue is inaccurate manual entry. Devices may be integrated later, but human verification remains necessary.

---

## Flow 10: Doctor Consultation Flow

### Purpose

This flow supports clinical consultation and documentation.

### Actors

The actors are doctor, patient, provider portal, consent engine, clinical timeline, prescription module, lab module, imaging module, referral module, billing module, and audit engine.

### Step-by-Step Flow

1. The doctor calls the patient from the queue.
2. The doctor opens the active encounter.
3. The system displays patient identity and triage summary.
4. The doctor requests or confirms access to relevant history.
5. The system displays allergies, chronic conditions, current medications, recent visits, recent lab results, and warnings.
6. The doctor records presenting complaint and clinical history.
7. The doctor records examination findings.
8. The doctor enters diagnosis or differential diagnosis.
9. The doctor creates a treatment plan.
10. If tests are required, the doctor creates lab or imaging orders.
11. If medication is required, the doctor creates a prescription.
12. If specialist care is required, the doctor creates a referral.
13. If inpatient care is required, the doctor creates an admission order.
14. If the patient can leave, the doctor creates discharge or follow-up instructions.
15. The doctor closes or pauses the consultation.
16. The encounter remains open if the patient must complete lab, imaging, pharmacy, billing, or admission workflows.
17. The audit engine logs the consultation actions.

### Expected Output

A structured clinical consultation record is added to the patient’s encounter and timeline.

### Review: Gaps, Bugs, and Controls

A bug is doctors accidentally documenting on the wrong patient. The patient identity header must remain visible at all times.

A gap is incomplete documentation. Templates should guide required fields without slowing doctors.

A clinical risk is prescribing despite allergy. Allergy checks should run before prescription finalization.

A usability risk is too many alerts. Alerts must be prioritized by severity.

---

## Flow 11: Lab Order Flow

### Purpose

This flow allows a provider to order laboratory tests.

### Actors

The actors are doctor, patient, lab module, billing module, laboratory staff, notification service, and audit engine.

### Step-by-Step Flow

1. The doctor opens the active encounter.
2. The doctor selects “Order Lab Test.”
3. The doctor searches and selects required tests.
4. The system displays test availability and price where configured.
5. The doctor enters clinical notes and priority.
6. The lab order is created.
7. If payment is required before testing, the billing module generates an invoice.
8. The patient is routed to cashier or lab depending on facility policy.
9. The lab receives the order.
10. The audit engine logs the order.

### Expected Output

A lab order exists and is visible to the lab department.

### Review: Gaps, Bugs, and Controls

A bug may occur if a test is ordered but not available at the facility. The system should display availability.

A gap is external lab referral. The system should allow orders to partner labs.

A billing issue may occur if price lists are outdated. Price management must be controlled by authorized staff.

---

## Flow 12: Lab Sample Collection Flow

### Purpose

This flow manages sample collection for ordered laboratory tests.

### Actors

The actors are lab receptionist, phlebotomist or lab staff, patient, lab module, barcode service, inventory module, and audit engine.

### Step-by-Step Flow

1. The patient arrives at the lab.
2. Lab staff searches for pending lab orders.
3. The patient identity is verified.
4. Lab staff selects the order.
5. The system displays required sample type.
6. Lab staff collects the sample.
7. The system generates a sample ID or barcode.
8. The sample is labeled.
9. Collection time and collector are recorded.
10. Inventory may deduct collection consumables where configured.
11. The sample status changes to “collected.”
12. The audit engine logs sample collection.

### Expected Output

The sample is linked to the patient, order, and encounter with clear traceability.

### Review: Gaps, Bugs, and Controls

A critical bug is sample mix-up. Barcode labeling and patient verification are essential.

A gap is manual labels in low-resource settings. The system should allow handwritten fallback but require later reconciliation.

A risk is collecting before payment when facility policy requires payment. Policy rules must be configurable.

---

## Flow 13: Lab Result Entry and Validation Flow

### Purpose

This flow records and validates laboratory results before release.

### Actors

The actors are lab technician, validating lab scientist, lab module, clinical alert engine, notification service, patient timeline, and audit engine.

### Step-by-Step Flow

1. Lab technician opens collected sample.
2. The technician enters test results.
3. The system validates units, reference ranges, and required fields.
4. The result status becomes “entered, pending validation.”
5. A qualified validator reviews the result.
6. The validator confirms, corrects, or rejects the result.
7. Once approved, the result status becomes “validated.”
8. The result is published to the patient timeline.
9. The ordering provider is notified.
10. If the result is critical, a critical alert is generated.
11. The patient may be notified that a result is available depending on policy.
12. The audit engine logs entry, validation, and release.

### Expected Output

A validated lab result is added to the patient’s record and made available to authorized users.

### Review: Gaps, Bugs, and Controls

A bug is publishing unvalidated results. The system must prevent release before validation unless configured for specific rapid tests.

A clinical risk is wrong units or reference ranges. The system should enforce unit validation and facility-specific reference ranges.

A gap is machine integration. Advanced labs may need analyzer integration to reduce manual entry errors.

A safety issue is critical results not seen by doctors. Critical alerts should require acknowledgment.

---

## Flow 14: Prescription Creation Flow

### Purpose

This flow allows a provider to prescribe medication safely.

### Actors

The actors are doctor, patient, prescription module, allergy engine, clinical decision support, pharmacy module, billing module, and audit engine.

### Step-by-Step Flow

1. The doctor opens the patient encounter.
2. The doctor selects “Create Prescription.”
3. The doctor searches and selects medication.
4. The doctor enters dose, route, frequency, duration, and instructions.
5. The system checks allergies.
6. The system checks duplicate medications.
7. The system checks drug interactions where available.
8. The doctor reviews warnings.
9. The doctor confirms prescription.
10. The prescription status becomes “issued.”
11. The patient may receive a prescription code or digital prescription.
12. The pharmacy can now verify and dispense it.
13. The audit engine logs prescription creation.

### Expected Output

A valid prescription is created and linked to the patient encounter.

### Review: Gaps, Bugs, and Controls

A major clinical bug is prescribing medication to which the patient is allergic. Allergy alerts must be visible before finalization.

A gap is unavailable medicines. Integration with stock can show availability.

A risk is forged prescriptions. Prescriptions should have unique IDs and provider signatures or secure verification.

A usability issue is doctors bypassing warnings. Overrides should require reasons for high-severity warnings.

---

## Flow 15: Pharmacy Dispensing Flow

### Purpose

This flow verifies and dispenses prescribed medication.

### Actors

The actors are pharmacist, patient, pharmacy module, inventory module, billing module, prescription module, audit engine, and notification service.

### Step-by-Step Flow

1. The patient presents at the pharmacy.
2. The pharmacist searches by Health ID, prescription ID, or QR code.
3. The pharmacist verifies patient identity.
4. The system displays active prescriptions.
5. The pharmacist selects the prescription.
6. The system checks prescription validity, expiry, and dispensing status.
7. The system checks stock availability.
8. The pharmacist dispenses full or partial medication.
9. Inventory is deducted by batch.
10. The dispensing event is recorded.
11. The prescription status updates to fully dispensed or partially dispensed.
12. The patient timeline is updated.
13. The audit engine logs the dispensing event.

### Expected Output

Medication dispensing is recorded, stock is updated, and the patient medication history is updated.

### Review: Gaps, Bugs, and Controls

A bug is dispensing an expired prescription. The system must enforce validity period.

A stock bug is deducting the wrong batch. Batch selection should be controlled, especially for expiry tracking.

A fraud risk is repeated dispensing. The system should prevent duplicate dispensing beyond prescription rules.

A gap is external pharmacy dispensing. Partner pharmacies should integrate through API or portal.

---

## Flow 16: Billing and Payment Flow

### Purpose

This flow manages invoices, payments, receipts, and financial status.

### Actors

The actors are cashier, patient, billing module, payment gateway, insurance module, clinical departments, and audit engine.

### Step-by-Step Flow

1. A billable service is created, such as consultation, lab test, imaging, medication, procedure, or admission.
2. The billing module checks the facility price list.
3. The system creates an invoice.
4. If the patient has insurance, the insurance module checks coverage.
5. The system calculates patient responsibility, insurer responsibility, discount, or co-payment.
6. The patient proceeds to cashier or digital payment.
7. The cashier records payment method.
8. If digital payment is used, the payment gateway confirms transaction status.
9. The invoice status updates to paid, partially paid, waived, pending, or cancelled.
10. A receipt is generated.
11. Departments are notified that the service can proceed if payment is required first.
12. The audit engine logs the payment action.

### Expected Output

The patient’s financial status is updated and linked to the encounter.

### Review: Gaps, Bugs, and Controls

A bug is allowing unauthorized price changes. Price lists must be controlled.

A risk is cashier fraud. Cashier reconciliation, shift reports, and audit logs are necessary.

A workflow gap is emergency care. Emergency services should not be blocked where facility or legal policy requires treatment first.

A payment bug may occur when mobile money confirms late. Payment status should support pending and reconciliation.

---

## Flow 17: Insurance Verification Flow

### Purpose

This flow verifies whether a patient has valid insurance coverage.

### Actors

The actors are receptionist, cashier, insurer integration, insurance module, patient, facility, and audit engine.

### Step-by-Step Flow

1. The patient presents insurance details or has insurance linked to their Health ID.
2. The facility requests insurance verification.
3. The insurance module checks stored policy details.
4. If insurer API integration exists, OpesCare queries the insurer.
5. The insurer returns policy status, coverage type, limits, co-payment, and exclusions.
6. The system displays eligibility to authorized staff.
7. The billing module applies coverage rules.
8. The verification result is logged.

### Expected Output

The facility knows whether the patient is covered and what payment rules apply.

### Review: Gaps, Bugs, and Controls

A bug is stale policy information. Live insurer verification is preferred where possible.

A privacy issue is insurers seeing too much clinical data. Verification should not expose full records.

A gap is offline verification. The system should allow cached policy status with clear warning.

---

## Flow 18: Insurance Claim Submission Flow

### Purpose

This flow submits treatment claims to an insurer.

### Actors

The actors are hospital billing officer, insurance module, insurer, clinical records module, claims reviewer, and audit engine.

### Step-by-Step Flow

1. The patient receives billable care.
2. The billing module identifies insurer responsibility.
3. The claims officer opens the claim module.
4. The system generates a claim based on encounter, diagnosis, services, prescriptions, lab tests, and invoices.
5. Required supporting documents are attached.
6. The claim is submitted to the insurer.
7. The insurer reviews the claim.
8. The insurer approves, rejects, queries, or partially approves the claim.
9. The claim status updates in OpesCare.
10. Approved claims are marked for payment tracking.
11. The audit engine logs the claim lifecycle.

### Expected Output

A traceable insurance claim is submitted and tracked.

### Review: Gaps, Bugs, and Controls

A risk is fraudulent claims. Claims should be linked to real encounters and services.

A privacy risk is excessive data sharing. Claims should include only necessary information.

A bug is duplicate claim submission. Idempotency and claim reference checks are required.

---

## Flow 19: Referral Creation Flow

### Purpose

This flow allows one provider to refer a patient to another provider or facility.

### Actors

The actors are referring doctor, patient, referral module, receiving facility, consent engine, notification service, and audit engine.

### Step-by-Step Flow

1. The doctor opens the patient encounter.
2. The doctor selects “Create Referral.”
3. The doctor selects referral destination, specialty, urgency, and reason.
4. The doctor writes a clinical summary.
5. The doctor attaches relevant labs, imaging, prescriptions, and notes.
6. The consent engine confirms that the patient agrees to share referral information.
7. The referral is submitted.
8. The receiving facility is notified.
9. The patient receives referral instructions.
10. The audit engine logs the referral.

### Expected Output

A structured referral package is created and sent to the receiving facility.

### Review: Gaps, Bugs, and Controls

A gap is referral without patient consent. The system should require consent except where law or emergency policy allows.

A bug is attaching irrelevant sensitive records. Referral attachments should be selected intentionally.

A workflow issue is receiving facility not responding. The referral should have statuses and escalation.

---

## Flow 20: Referral Acceptance and Feedback Flow

### Purpose

This flow allows the receiving facility to accept, manage, and close a referral.

### Actors

The actors are receiving facility, specialist, patient, referral module, notification service, and audit engine.

### Step-by-Step Flow

1. The receiving facility receives referral notification.
2. Authorized staff opens the referral.
3. The facility reviews the referral summary and attached records.
4. The facility accepts, rejects, or requests more information.
5. If accepted, an appointment or visit is created.
6. The patient attends the receiving facility.
7. The specialist provides care.
8. After care, the receiving facility writes feedback.
9. The feedback is sent to the referring provider.
10. The referral is closed or marked for follow-up.
11. The audit engine logs all actions.

### Expected Output

The referral loop is completed and the referring provider receives outcome information.

### Review: Gaps, Bugs, and Controls

A gap is no feedback from receiving facility. The system should remind and escalate open referrals.

A privacy issue is referral staff accessing unnecessary records. Access should be scoped to referral package.

A bug is duplicate referral visits. The referral should link to one or more controlled encounters.

---

## Flow 21: Inpatient Admission Flow

### Purpose

This flow admits a patient to a ward and bed.

### Actors

The actors are doctor, admission officer, ward nurse, bed manager, billing module, patient, and audit engine.

### Step-by-Step Flow

1. The doctor decides that the patient requires admission.
2. The doctor creates an admission order.
3. The system checks available wards and beds.
4. The admission officer assigns a ward and bed.
5. The patient’s encounter status changes to admitted.
6. The ward nurse receives admission notification.
7. Admission notes are created.
8. Billing may create admission deposit or package invoice depending on policy.
9. The patient is moved to the assigned bed.
10. The audit engine logs admission.

### Expected Output

The patient is admitted with a ward, bed, admission order, and inpatient record.

### Review: Gaps, Bugs, and Controls

A bug is assigning an occupied bed. Bed status must be locked during assignment.

A gap is emergency admission without payment. Policy must allow urgent admission workflow.

A workflow risk is patient physically moved but not digitally admitted. Ward dashboards should show pending admissions.

---

## Flow 22: Inpatient Daily Care Flow

### Purpose

This flow manages inpatient care during hospitalization.

### Actors

The actors are doctors, nurses, patient, ward module, medication administration module, lab module, pharmacy module, and audit engine.

### Step-by-Step Flow

1. The ward nurse opens the inpatient dashboard.
2. The nurse records routine vitals and observations.
3. Doctors perform ward rounds.
4. Doctors update progress notes, diagnosis, and treatment plan.
5. Medication orders are reviewed.
6. Nurses administer medication and record administration status.
7. Additional lab or imaging orders are created when needed.
8. Results return to the inpatient record.
9. Care plans are updated.
10. Critical alerts are escalated.
11. Shift handover notes are recorded.
12. The audit engine logs all actions.

### Expected Output

The inpatient record reflects daily care, nursing activity, medication administration, and doctor review.

### Review: Gaps, Bugs, and Controls

A major clinical bug is medication ordered but not administered. Medication administration records must track due, given, missed, refused, or delayed doses.

A gap is handover failure. Shift handover should be structured and visible.

A safety issue is deteriorating patient unnoticed. Repeated abnormal vitals should trigger escalation alerts.

---

## Flow 23: Ward Transfer Flow

### Purpose

This flow transfers an admitted patient from one ward or bed to another.

### Actors

The actors are ward nurse, doctor, bed manager, receiving ward, patient, and audit engine.

### Step-by-Step Flow

1. A transfer need is identified.
2. The doctor or authorized nurse initiates transfer.
3. The system checks available destination beds.
4. The destination ward confirms readiness.
5. The current ward prepares transfer notes.
6. The patient is moved physically.
7. The bed assignment is updated.
8. The previous bed status changes to cleaning or available depending on workflow.
9. The receiving ward accepts the patient digitally.
10. The audit engine logs the transfer.

### Expected Output

The patient’s inpatient location is accurately updated.

### Review: Gaps, Bugs, and Controls

A bug is physical transfer without digital acceptance. The system should show pending transfers.

A bed management issue is failing to release previous bed. Bed status must update automatically but allow manual correction with reason.

A clinical gap is missing transfer notes. Transfer should require handover summary.

---

## Flow 24: Discharge Flow

### Purpose

This flow discharges an admitted patient.

### Actors

The actors are doctor, nurse, billing officer, pharmacy, patient, discharge module, and audit engine.

### Step-by-Step Flow

1. The doctor determines that the patient can be discharged.
2. The doctor creates discharge summary.
3. The discharge summary includes diagnosis, treatment given, procedures, medication, follow-up instructions, warning signs, and next appointment.
4. The pharmacy prepares discharge medication if needed.
5. Billing calculates final invoice.
6. The patient settles payment or insurance approval is completed.
7. The nurse completes discharge checklist.
8. The patient is marked discharged.
9. The bed status changes to cleaning or available.
10. The discharge summary is added to the patient timeline.
11. Follow-up reminders are scheduled.
12. The audit engine logs discharge.

### Expected Output

The inpatient episode is closed and the patient leaves with clear instructions.

### Review: Gaps, Bugs, and Controls

A bug is discharge without summary. The system should require discharge summary for inpatient cases.

A workflow gap is billing delay. Facilities may allow clinical discharge before financial closure depending on policy.

A safety issue is no follow-up plan. Discharge should require follow-up instruction or explicit “no follow-up required.”

---

## Flow 25: Inventory Stock-In Flow

### Purpose

This flow records new stock received by a facility.

### Actors

The actors are inventory officer, supplier, inventory module, pharmacy or store manager, finance where applicable, and audit engine.

### Step-by-Step Flow

1. Inventory officer receives items from supplier.
2. The officer opens stock-in module.
3. The officer selects item or creates item if authorized.
4. The officer enters quantity, batch number, expiry date, supplier, purchase order, and cost.
5. The system validates required fields.
6. The stock is added to facility inventory.
7. Items with expiry dates are added to expiry monitoring.
8. The audit engine logs stock-in.

### Expected Output

Inventory quantity increases with traceable batch and supplier information.

### Review: Gaps, Bugs, and Controls

A bug is stock without batch or expiry for medicines. Batch and expiry should be mandatory for regulated items.

A fraud risk is fake stock-in. Stock-in should be reviewed or approved based on facility policy.

A gap is supplier quality. Supplier records should support approval status.

---

## Flow 26: Inventory Stock-Out and Adjustment Flow

### Purpose

This flow records stock usage, loss, damage, expiry, transfer, or correction.

### Actors

The actors are inventory officer, pharmacist, lab staff, ward staff, inventory module, approving manager, and audit engine.

### Step-by-Step Flow

1. Staff initiates stock-out or adjustment.
2. The item and batch are selected.
3. The reason is selected: dispensing, lab use, ward use, damage, expiry, loss, correction, or transfer.
4. Quantity is entered.
5. The system checks available quantity.
6. High-risk adjustments require approval.
7. Inventory quantity is updated.
8. The stock movement is recorded.
9. The audit engine logs the action.

### Expected Output

Stock levels reflect actual usage or adjustment with traceability.

### Review: Gaps, Bugs, and Controls

A bug is negative stock. The system should prevent negative stock unless explicitly allowed with approval.

A fraud risk is unexplained adjustments. All adjustments should require reason and user traceability.

A gap is expiry management. The system should alert before expiry, not only after expiry.

---

## Flow 27: Legacy Record Upload Flow

### Purpose

This flow allows old records to be uploaded into OpesCare.

### Actors

The actors are patient, facility staff, document module, OCR service where available, clinician reviewer, and audit engine.

### Step-by-Step Flow

1. The patient or staff selects “Upload Legacy Record.”
2. The file is uploaded or scanned.
3. The uploader selects document type, date, facility source, and description.
4. The document is stored securely.
5. The document is marked as patient-submitted or facility-submitted.
6. OCR may extract text where available.
7. A clinician or authorized reviewer may verify the record.
8. Verified data may be converted into structured record entries.
9. The document appears in the patient timeline with source status.
10. The audit engine logs upload and verification.

### Expected Output

Old records become available in the patient timeline with clear trust status.

### Review: Gaps, Bugs, and Controls

A bug is treating unverified uploads as clinical truth. Uploaded records must show verification status.

A privacy risk is uploading another person’s document. Patient identity verification and reviewer checks are needed.

A gap is poor scan quality. The system should allow rejection or request re-upload.

---

## Flow 28: Data Migration Flow for Existing Hospitals

### Purpose

This flow imports historical data from a hospital’s existing system.

### Actors

The actors are hospital IT team, OpesCare integration team, migration tool, MPI, data validation engine, facility administrator, and audit engine.

### Step-by-Step Flow

1. The hospital provides exported data or integration access.
2. The OpesCare team reviews data structure.
3. Fields are mapped to OpesCare entities.
4. Test migration is performed in sandbox.
5. Data validation identifies missing fields, duplicates, invalid dates, and inconsistent identifiers.
6. MPI matching links historical patients to existing OpesCare IDs where possible.
7. Unmatched patients are prepared for new Health ID creation.
8. Hospital administrators review migration reports.
9. Approved migration is performed in production.
10. Migrated records are marked as historical and source-attributed.
11. The audit engine logs migration.

### Expected Output

Historical hospital records are imported safely and linked to patient identities.

### Review: Gaps, Bugs, and Controls

A major bug is wrong patient matching during migration. MPI confidence thresholds and human review are required.

A gap is dirty data. Migration should not silently clean uncertain data without logging.

A risk is downtime. Migration should be planned with rollback strategy.

---

## Flow 29: External Hospital API Push Flow

### Purpose

This flow allows a hospital with its own system to send patient events to OpesCare.

### Actors

The actors are external hospital system, connector, API Gateway, validation engine, MPI, synchronization engine, patient timeline, and audit engine.

### Step-by-Step Flow

1. A medical event is created in the hospital system.
2. The hospital system sends the event to the OpesCare connector or API.
3. The API Gateway authenticates the hospital system.
4. The request signature and token are validated.
5. The validation engine checks schema and required fields.
6. The MPI identifies the patient.
7. If the patient is not found, the system applies configured rules: reject, create provisional profile, or queue for review.
8. The synchronization engine checks idempotency key and duplicate status.
9. The event is accepted and stored.
10. The patient timeline is updated.
11. A response is sent to the hospital system.
12. The audit engine logs the event.

### Expected Output

External hospital data is synchronized into OpesCare safely.

### Review: Gaps, Bugs, and Controls

A bug is accepting unauthenticated data. API authentication and signatures are mandatory.

A risk is duplicate events due to retries. Idempotency keys are required.

A gap is unmapped local codes. The connector should support mapping tables and unmapped-code review.

---

## Flow 30: External Hospital API Pull Flow

### Purpose

This flow allows an external hospital system to retrieve authorized patient records.

### Actors

The actors are external hospital system, provider, patient, consent engine, API Gateway, access control engine, patient record service, and audit engine.

### Step-by-Step Flow

1. The external hospital system identifies the patient.
2. The provider requests patient data through the hospital system.
3. The hospital system calls OpesCare API.
4. The API Gateway authenticates the system.
5. The access control engine checks provider role, facility status, and purpose.
6. The consent engine checks patient consent.
7. If consent is valid, the patient record service retrieves authorized data.
8. The API returns only permitted data.
9. The hospital system displays the data to the provider.
10. The audit engine logs the access.

### Expected Output

The external hospital receives authorized patient information without direct database access.

### Review: Gaps, Bugs, and Controls

A privacy bug is returning full records by default. APIs must be scope-limited.

A security risk is hospital system account sharing. Provider identity should be passed where possible.

A gap is patient denial of consent. The hospital should receive a clear denial response and alternative workflow.

---

## Flow 31: Webhook Event Delivery Flow

### Purpose

This flow sends event notifications to integrated partner systems.

### Actors

The actors are OpesCare event service, partner system, webhook service, retry service, security service, and audit engine.

### Step-by-Step Flow

1. An event occurs in OpesCare, such as lab result ready or prescription dispensed.
2. The event service checks which partner systems subscribed to that event.
3. The webhook service creates a webhook payload.
4. The payload is signed.
5. The webhook is sent to the partner endpoint.
6. The partner system verifies signature.
7. The partner returns success or failure.
8. If failure occurs, retry rules are applied.
9. If repeated failure occurs, the webhook subscription is flagged.
10. The audit engine logs delivery status.

### Expected Output

Partner systems receive timely event updates.

### Review: Gaps, Bugs, and Controls

A bug is webhook replay attack. Payloads should include timestamp, event ID, and signature.

A risk is partner downtime. Retry and dead-letter queues are needed.

A privacy gap is sending too much data. Webhooks should send event notice, not full sensitive records unless explicitly authorized.

---

## Flow 32: Patient Access Log Review Flow

### Purpose

This flow allows patients to see who accessed their records.

### Actors

The actors are patient, patient portal, audit engine, access log service, and support team where needed.

### Step-by-Step Flow

1. The patient logs into the portal.
2. The patient opens “Who viewed my records.”
3. The access log service retrieves patient-specific access logs.
4. The system displays facility, role, date, purpose, and access type.
5. The patient can flag suspicious access.
6. A complaint or review ticket is created.
7. Compliance team reviews the flagged access.
8. Outcome is recorded.

### Expected Output

The patient gains transparency and can report suspicious access.

### Review: Gaps, Bugs, and Controls

A privacy issue is showing staff names where this may create safety issues. Display rules should follow policy.

A bug is incomplete logs. All access must be logged centrally.

A gap is patient misunderstanding. The interface should explain legitimate access types.

---

## Flow 33: User Onboarding Flow

### Purpose

This flow creates staff accounts for a facility.

### Actors

The actors are facility administrator, OpesCare admin, practitioner, identity service, role management service, and audit engine.

### Step-by-Step Flow

1. Facility administrator requests new user account.
2. Required user details are entered.
3. Role and department are selected.
4. Professional license is added where applicable.
5. The system checks whether the user already exists.
6. The account is created or linked to existing practitioner identity.
7. Authentication setup is completed.
8. MFA is configured for high-risk roles.
9. The user receives login instructions.
10. The audit engine logs account creation.

### Expected Output

A user account exists with correct role, facility, and permissions.

### Review: Gaps, Bugs, and Controls

A bug is giving excessive permissions. Role templates should be reviewed.

A risk is fake practitioners. License verification should be supported.

A security gap is inactive staff retaining access. Offboarding flow is required.

---

## Flow 34: User Offboarding Flow

### Purpose

This flow removes or suspends access when staff leave or change roles.

### Actors

The actors are facility administrator, OpesCare admin, user management service, access control engine, audit engine, and security team where needed.

### Step-by-Step Flow

1. Facility administrator identifies staff departure or role change.
2. The user account is searched.
3. The administrator selects suspend, remove facility access, or change role.
4. Active sessions are terminated.
5. API tokens or device sessions linked to the user are revoked.
6. The account status is updated.
7. The audit engine logs the action.
8. Security review occurs if access misuse is suspected.

### Expected Output

Former or changed staff no longer have inappropriate access.

### Review: Gaps, Bugs, and Controls

A security bug is leaving sessions active. Session termination is mandatory.

A gap is delayed offboarding. Facilities should have periodic access review.

A risk is shared accounts. Individual accounts reduce this risk.

---

## Flow 35: Facility Onboarding Flow

### Purpose

This flow registers and activates a healthcare facility on OpesCare.

### Actors

The actors are facility owner, OpesCare onboarding team, facility management module, integration team, training team, and audit engine.

### Step-by-Step Flow

1. Facility applies to join OpesCare.
2. OpesCare collects facility documents and license information.
3. Facility type and services are verified.
4. Facility profile is created.
5. Departments are configured.
6. Users are created.
7. Integration mode is selected: direct API, adapter, file import, or OpesCare Lite.
8. Workflows are configured.
9. Staff training is completed.
10. Test transactions are performed.
11. Facility is activated.
12. Audit engine logs onboarding.

### Expected Output

The facility is active and ready to use OpesCare.

### Review: Gaps, Bugs, and Controls

A risk is onboarding unlicensed facilities. Verification must be mandatory.

A gap is poor training. Go-live should require minimum training completion.

A bug is wrong facility permissions. Facility type should control default permissions.

---

## Flow 36: Facility Suspension Flow

### Purpose

This flow suspends a facility due to closure, breach, license issue, or security concern.

### Actors

The actors are OpesCare admin, compliance officer, facility management module, access control engine, notification service, and audit engine.

### Step-by-Step Flow

1. A suspension reason is identified.
2. Authorized admin opens facility profile.
3. Admin selects suspension type: temporary, security, regulatory, or permanent.
4. The system asks for reason and approval where required.
5. Facility access is restricted.
6. Active API keys are disabled where necessary.
7. Facility users are blocked from new access unless exceptions apply.
8. Patients with ongoing care may be handled through transition rules.
9. Facility is notified.
10. Audit engine logs suspension.

### Expected Output

The facility can no longer access or submit data except under controlled exceptions.

### Review: Gaps, Bugs, and Controls

A risk is harming active patient care. Suspension policy must handle ongoing patients.

A security bug is leaving API keys active. API keys must be revoked or disabled.

A governance gap is arbitrary suspension. Approval workflow and evidence should be required.

---

## Flow 37: Patient Record Correction Flow

### Purpose

This flow corrects errors in patient demographic or clinical records.

### Actors

The actors are patient, provider, facility admin, correction service, audit engine, and reviewer where needed.

### Step-by-Step Flow

1. An error is identified.
2. A correction request is created.
3. The request identifies record type and proposed correction.
4. Demographic corrections are routed to authorized staff.
5. Clinical corrections are routed to the original provider or authorized clinical reviewer.
6. The reviewer approves, rejects, or amends the correction.
7. The corrected value is added.
8. The original value is preserved for audit.
9. The patient timeline reflects the correction where appropriate.
10. The audit engine logs the correction.

### Expected Output

Incorrect records are corrected without destroying historical traceability.

### Review: Gaps, Bugs, and Controls

A bug is silently editing clinical notes. Corrections should be amendments, not invisible overwrites.

A gap is disputed corrections. The system should support dispute status.

A risk is unauthorized demographic changes. Identity-sensitive fields should require verification.

---

## Flow 38: Duplicate Patient Merge Flow

### Purpose

This flow merges duplicate patient profiles safely.

### Actors

The actors are MPI, identity reviewer, facility admin, patient where needed, audit engine, and clinical safety reviewer for complex cases.

### Step-by-Step Flow

1. MPI identifies possible duplicate profiles.
2. A reviewer opens the duplicate case.
3. The system displays demographic and record comparison.
4. The reviewer checks identifiers, date of birth, sex, phone, facility history, and clinical records.
5. If duplicates are confirmed, the reviewer selects primary profile.
6. The system previews merge impact.
7. The reviewer confirms merge.
8. Records are linked under the primary patient identity.
9. The old profile is marked merged, not deleted.
10. Audit engine logs the merge.

### Expected Output

Duplicate profiles are unified while preserving traceability.

### Review: Gaps, Bugs, and Controls

A critical bug is merging two different patients. High-confidence rules and human review are mandatory.

A gap is inability to reverse merge. Unmerge capability must exist.

A clinical risk is conflicting data after merge. Conflicts should be flagged.

---

## Flow 39: Wrong Merge Unmerge Flow

### Purpose

This flow reverses an incorrect patient merge.

### Actors

The actors are identity reviewer, MPI, audit engine, clinical reviewer, and affected facilities where needed.

### Step-by-Step Flow

1. A wrong merge is reported.
2. The reviewer opens the merge history.
3. The system displays records added from each original profile.
4. The reviewer separates records back to original identities.
5. Ambiguous records are flagged for manual review.
6. The system recreates or reactivates separated patient profiles.
7. Access logs and audit history remain preserved.
8. Affected facilities may be notified.
9. Audit engine logs the unmerge.

### Expected Output

Incorrectly merged identities are separated safely.

### Review: Gaps, Bugs, and Controls

A bug is losing records during unmerge. Merge operations must preserve original record ownership metadata.

A gap is ambiguous records. Ambiguous records require review, not automatic assignment.

A safety risk is clinicians using mixed history before unmerge. The system should flag affected records.

---

## Flow 40: Telemedicine Consultation Flow

### Purpose

This flow supports remote care.

### Actors

The actors are patient, doctor, telemedicine module, appointment module, consent engine, prescription module, lab module, payment module, and audit engine.

### Step-by-Step Flow

1. Patient books telemedicine appointment.
2. The system confirms provider availability.
3. Payment is processed if required.
4. At appointment time, patient and doctor join secure video or chat.
5. The doctor verifies patient identity.
6. Consent is confirmed.
7. Doctor reviews relevant history.
8. Doctor conducts remote consultation.
9. Doctor records notes.
10. Doctor may issue prescription, lab request, referral, or follow-up.
11. The telemedicine encounter is saved to patient timeline.
12. Audit engine logs the session.

### Expected Output

A remote consultation is completed and recorded like any other encounter.

### Review: Gaps, Bugs, and Controls

A gap is poor internet. The system should support fallback chat or phone documentation.

A legal risk is remote prescribing rules. Country configuration must control what is allowed.

A privacy risk is recording video without consent. Recording policy must be explicit.

---

## Flow 41: Device Data Upload Flow

### Purpose

This flow uploads readings from home or hospital devices.

### Actors

The actors are patient, device, device integration service, patient portal, clinical review service, and audit engine.

### Step-by-Step Flow

1. Patient connects approved device or manually enters reading.
2. The device sends reading to OpesCare or patient enters it.
3. The system records data type, value, unit, time, and source.
4. The reading is marked device-submitted or patient-submitted.
5. Abnormal readings may trigger alerts.
6. A provider may review and mark the reading clinically reviewed.
7. The reading appears in the patient timeline or chronic disease dashboard.
8. Audit engine logs submission.

### Expected Output

Remote readings are added to the patient record with source status.

### Review: Gaps, Bugs, and Controls

A bug is trusting unverified readings as clinical facts. Source status is mandatory.

A gap is device calibration. Device metadata and approved device lists should be maintained.

A risk is false alarms from bad readings. Alerts should consider repeated values and context.

---

## Flow 42: Public Health Reporting Flow

### Purpose

This flow sends aggregated or permitted health data to public health authorities.

### Actors

The actors are public health module, analytics engine, reporting authority, governance service, privacy engine, and audit engine.

### Step-by-Step Flow

1. A reporting requirement is configured.
2. The analytics engine collects eligible data.
3. The privacy engine removes or limits identifiable information where required.
4. Reports are generated by disease, location, facility, period, or program.
5. Authorized public health users access dashboards or receive reports.
6. Report access is logged.
7. Any identifiable reporting follows legal basis and approval rules.

### Expected Output

Public health authorities receive useful reports without unnecessary privacy exposure.

### Review: Gaps, Bugs, and Controls

A privacy bug is exposing identifiable data unnecessarily. De-identification should be default.

A governance gap is unclear legal basis. Reporting rules must be configured by jurisdiction.

A data quality risk is incomplete facility reporting. Data quality scores should accompany reports.

---

## Flow 43: Research Data Request Flow

### Purpose

This flow controls research access to OpesCare data.

### Actors

The actors are researcher, ethics committee, data governance officer, research module, privacy engine, audit engine, and legal reviewer where needed.

### Step-by-Step Flow

1. Researcher submits a data request.
2. The request includes purpose, data needed, duration, ethics approval, and institution.
3. Governance team reviews the request.
4. Ethics approval is verified.
5. Privacy engine determines whether data can be anonymized or aggregated.
6. Data-use agreement is signed where required.
7. Approved dataset or dashboard access is created.
8. Researcher accesses only approved data.
9. Access expires after approved period.
10. Audit engine logs all access.

### Expected Output

Research data is accessed ethically and under control.

### Review: Gaps, Bugs, and Controls

A privacy risk is re-identification. Small datasets and rare conditions need special protection.

A governance gap is missing ethics review. Research access should not bypass approval.

A bug is access continuing after expiry. Expiry enforcement is mandatory.

---

## Flow 44: Incident Response Flow

### Purpose

This flow handles security or privacy incidents.

### Actors

The actors are security team, privacy officer, system admin, affected facility, affected patient where needed, legal team, and audit engine.

### Step-by-Step Flow

1. Incident is detected through monitoring, report, or audit review.
2. Security team classifies severity.
3. Affected accounts, API keys, or systems are contained.
4. Logs are preserved.
5. Root cause investigation begins.
6. Impacted patients or facilities are identified.
7. Required notifications are prepared according to law and policy.
8. Fixes are applied.
9. System is monitored for recurrence.
10. Post-incident report is created.
11. Policies or controls are updated.

### Expected Output

The incident is contained, investigated, corrected, and documented.

### Review: Gaps, Bugs, and Controls

A bug is deleting logs during incident response. Logs must be preserved.

A gap is unclear escalation. Severity levels and response roles must be predefined.

A legal risk is delayed breach notification. Country configuration should include notification timelines.

---

## Flow 45: Backup and Disaster Recovery Flow

### Purpose

This flow protects OpesCare from data loss and major outages.

### Actors

The actors are infrastructure team, database service, backup service, disaster recovery environment, security team, and audit engine.

### Step-by-Step Flow

1. Production data is backed up on schedule.
2. Backups are encrypted.
3. Backups are stored separately from production.
4. Backup integrity is checked.
5. Restore tests are performed regularly.
6. If disaster occurs, incident response is triggered.
7. Recovery environment is activated.
8. Data is restored to acceptable recovery point.
9. Services are validated.
10. Users are redirected or informed according to policy.
11. Post-recovery review is completed.

### Expected Output

OpesCare can recover from failure with controlled data loss and downtime.

### Review: Gaps, Bugs, and Controls

A bug is untested backups. Backups must be restored in tests regularly.

A security risk is unencrypted backups. Encryption is mandatory.

A gap is unclear recovery target. Recovery time and recovery point objectives must be defined.

---

## Flow 46: Country Configuration Flow

### Purpose

This flow configures OpesCare for a specific legal and operational market.

### Actors

The actors are OpesCare admin, legal team, compliance officer, country configuration service, product team, and audit engine.

### Step-by-Step Flow

1. A new country or deployment environment is created.
2. Legal and health data requirements are reviewed.
3. Age of consent is configured.
4. Guardian rules are configured.
5. Data retention rules are configured.
6. Data localization rules are configured.
7. Facility licensing fields are configured.
8. Insurance rules are configured.
9. Public health reporting rules are configured.
10. Currency and language are configured.
11. ID formats are configured.
12. Configuration is reviewed and approved.
13. Audit engine logs configuration.

### Expected Output

OpesCare operates according to the country or deployment environment’s rules.

### Review: Gaps, Bugs, and Controls

A legal risk is wrong configuration. Legal review and approval should be required.

A bug is changing rules without versioning. Configurations should be versioned.

A gap is multi-country patient movement. Cross-border access policies should be defined later.

---

## Flow 47: End-to-End Patient Journey Flow

### Purpose

This flow combines major modules into one real patient journey.

### Actors

The actors are patient, receptionist, nurse, doctor, lab, cashier, pharmacist, insurer, patient portal, synchronization engine, and audit engine.

### Step-by-Step Flow

1. Patient arrives at Hospital A.
2. Reception identifies patient by Health ID.
3. Patient is checked in.
4. Consent is granted for consultation.
5. Triage records vitals.
6. Doctor reviews history and examines patient.
7. Doctor orders lab test and prescribes initial medication if needed.
8. Billing creates invoice.
9. Patient pays or insurance is verified.
10. Lab collects sample.
11. Lab enters and validates result.
12. Doctor reviews result.
13. Doctor updates diagnosis and treatment plan.
14. Pharmacy dispenses medication.
15. Encounter is closed.
16. Patient receives follow-up reminder.
17. Patient later visits Hospital B.
18. Hospital B requests access.
19. Patient grants consent.
20. Doctor at Hospital B sees relevant Hospital A history.
21. Hospital B adds new records.
22. OpesCare synchronizes the new events into the patient timeline.

### Expected Output

The patient’s medical history follows them across facilities, while each facility retains proper source attribution.

### Review: Gaps, Bugs, and Controls

The largest risk is assuming every facility has the same workflow. OpesCare must support configurable workflows.

A bug is loss of source attribution. Every record must show facility, user, time, and origin system.

A privacy risk is broad access at Hospital B. Consent and access control must restrict what is shared.

A synchronization risk is duplicate or conflicting records. The sync engine must use idempotency, conflict flags, and review queues.

---

## Flow 48: Final Flow Review and System-Level Gap Scan

### Purpose

This final review scans all flows for cross-system gaps and operational bugs.

### System-Level Findings

The flows cover patient identity, registration, check-in, appointment, queue, consent, emergency access, triage, consultation, labs, pharmacy, billing, insurance, referrals, inpatient care, inventory, legacy records, migration, external integrations, webhooks, access logs, users, facilities, corrections, duplicate management, telemedicine, devices, public health, research, incident response, disaster recovery, and country configuration.

### Remaining Controls Required

The system must include strict identity verification, duplicate detection, audit logging, consent scoping, emergency access review, facility verification, user offboarding, role-based and attribute-based access, API authentication, webhook security, data validation, downtime workflows, backup testing, legal configuration, and clinical safety alerts.

### Critical Bugs to Prevent

The most dangerous bugs are wrong patient matching, wrong patient merge, documenting on the wrong patient, publishing unvalidated lab results, prescribing despite allergy, dispensing expired or duplicate prescriptions, exposing full records without consent, allowing unauthenticated API access, leaving former staff accounts active, and losing audit logs.

### Final Control Statement

OpesCare must be designed as healthcare infrastructure, not ordinary business software. Every flow must preserve patient safety, privacy, traceability, and continuity of care.



---

# 68. Second-Pass Flow QA: Gap, Bug, and Fix Remediation

This section reviews the operational flows again as a complete system. The purpose is to detect bugs that may not appear inside a single flow but become dangerous when multiple flows interact. The fixes below are now part of the OpesCare operating specification and must be implemented as mandatory controls.

---

## 68.1 Cross-Flow Bug: Patient Identity Can Be Created in Too Many Places

### Identified Gap

The flows allow patient creation through pre-registration, hospital reception, data migration, API push, emergency unknown-patient workflow, and facility-assisted registration. If these creation points are not controlled by the same identity rules, OpesCare can create duplicate patient records.

### Risk

Duplicate identities can split medical history across multiple profiles. A doctor may miss allergies, prior diagnoses, prescriptions, or lab results because the record exists under another patient profile.

### Fix

All patient creation routes must pass through one central Patient Identity Service and one Master Patient Index check before a permanent Health ID is issued.

### Corrected Rule

No module is allowed to create a permanent patient profile directly. Every patient profile must be created through the Patient Identity Service. If confidence is low, the system must create a provisional profile or review case instead of a permanent duplicate identity.

### Implementation Control

The Patient Identity Service must expose a single `createPatientCandidate` operation. Registration, migration, API integration, emergency workflows, and self-registration must all use this same operation.

---

## 68.2 Cross-Flow Bug: Provisional Patient Profiles Were Not Fully Defined

### Identified Gap

The flows mention provisional profiles, but the lifecycle of provisional profiles is not fully defined.

### Risk

A provisional profile may be used permanently without verification, leading to weak identity quality. It may also receive clinical records that later need to be merged or corrected.

### Fix

Create a clear provisional profile lifecycle.

### Corrected Rule

A provisional profile can receive clinical records, but it must be visibly marked as provisional until verified. Verification can occur through facility confirmation, identity document review, guardian verification, insurance verification, or approved identity authority.

### Required Statuses

A patient profile can have one of the following identity statuses:

- self-registered provisional
- facility-created provisional
- verified by facility
- verified by identity document
- verified by insurer
- verified by national authority where applicable
- merged
- deceased
- suspended pending review

### Implementation Control

Every patient profile must carry `identity_status`, `verification_source`, `verified_at`, and `verified_by` fields.

---

## 68.3 Cross-Flow Bug: Unknown Emergency Patient Was Not Properly Handled

### Identified Gap

The emergency flow mentions unidentified unconscious patients but does not define how they are registered and later reconciled.

### Risk

Emergency departments often treat patients before identity is known. If OpesCare cannot handle this, staff may create unsafe workarounds or document under the wrong patient.

### Fix

Add an Unknown Patient Emergency Flow.

### Corrected Flow

1. Emergency provider selects “Create Unknown Emergency Patient.”
2. System creates a temporary emergency identity such as `TEMP-ER-[facility]-[timestamp]-[random]`.
3. Emergency care is documented under this temporary profile.
4. The temporary profile is clearly marked as “Unknown Emergency Patient.”
5. Staff may add descriptive details such as approximate age, sex, clothing, location found, and arrival method.
6. Once identity is discovered, staff initiate identity resolution.
7. MPI checks whether the patient already exists.
8. If an existing patient is confirmed, the temporary emergency record is linked or merged after review.
9. If no existing patient exists, a permanent OpesCare Health ID is created.
10. Audit logs preserve the temporary identity history.

### Implementation Control

Temporary emergency profiles must not be silently converted into permanent identities. They must pass through identity resolution.

---

## 68.4 Cross-Flow Bug: Consent and Billing Can Conflict With Emergency Care

### Identified Gap

Some flows route patients to consent and billing before service. In emergencies, this can delay care.

### Risk

Emergency treatment may be delayed because staff wait for payment, consent, insurance verification, or normal registration.

### Fix

Emergency care must have a bypass pathway.

### Corrected Rule

When the visit type is emergency, the system must allow clinical care to begin before normal billing, complete identity verification, or standard consent. The system must still log emergency access, create an emergency encounter, and require later reconciliation.

### Implementation Control

Emergency encounters should have a `care_before_admin_clearance` flag. Billing, identity verification, and consent reconciliation can be completed after stabilization.

---

## 68.5 Cross-Flow Bug: Cached Offline Data Can Violate Revoked Consent

### Identified Gap

The offline flow allows local cached access, while the revocation flow blocks future access. The conflict is not fully resolved.

### Risk

A patient may revoke consent, but an offline facility may continue accessing cached records.

### Fix

Offline cache must use expiry, scope, and revocation reconciliation.

### Corrected Rule

Offline cached patient records must be encrypted, time-limited, scope-limited, and refreshed when connectivity returns. If a consent grant is revoked while a facility is offline, the local system must block future access once it reconnects and must log any access that occurred during disconnection.

### Implementation Control

Every cached record must include `cache_scope`, `cache_created_at`, `cache_expires_at`, `consent_grant_id`, and `offline_access_reason`.

---

## 68.6 Cross-Flow Bug: Audit Logging Was Mentioned but Not Standardized

### Identified Gap

Most flows say “audit engine logs the action,” but the exact audit structure is not standardized across flows.

### Risk

Audit logs may be inconsistent. Some logs may miss patient ID, facility ID, consent status, device, reason, or source system.

### Fix

Define a mandatory audit event schema.

### Corrected Rule

Every audit event must include:

- audit event ID
- actor user ID
- actor role
- facility ID
- organization ID where applicable
- patient ID where applicable
- encounter ID where applicable
- action type
- resource type
- resource ID
- consent grant ID where applicable
- emergency override flag
- source system
- device ID
- IP address where available
- timestamp
- reason or purpose where required
- before-and-after values for sensitive changes

### Implementation Control

No high-risk operation should be accepted unless the audit event is successfully written or queued in a tamper-resistant log.

---

## 68.7 Cross-Flow Bug: Record Source Attribution Needs Stronger Enforcement

### Identified Gap

Several flows depend on source attribution, but the document does not define a universal metadata model for records.

### Risk

Doctors may not know whether a record came from a verified hospital, patient upload, migration import, external API, device, or manual entry.

### Fix

All clinical records must carry source metadata.

### Corrected Rule

Every clinical record must include:

- source type
- source facility
- source user
- source system
- external reference ID where applicable
- verification status
- created timestamp
- submitted timestamp
- last updated timestamp
- record confidence level where applicable

### Implementation Control

The patient timeline must display source and verification status clearly.

---

## 68.8 Cross-Flow Bug: Wrong Patient Selection During Busy Workflows

### Identified Gap

Patient lookup appears in reception, lab, pharmacy, billing, emergency, and external API flows. The verification standard is not identical across all flows.

### Risk

Staff can document, bill, dispense, or upload results under the wrong patient.

### Fix

Add a universal patient verification requirement.

### Corrected Rule

Before any high-risk action, the user must verify at least two patient identifiers. High-risk actions include consultation, lab collection, result entry, prescription, dispensing, admission, discharge, billing, insurance claim, referral, and record correction.

### Acceptable Identifiers

Acceptable identifiers include Health ID, full name, date of birth, phone number, guardian name, national ID, insurance number, patient photo where available, or biometric confirmation where legally permitted.

### Implementation Control

The user interface must show a persistent patient identity banner during all clinical and financial actions.

---

## 68.9 Cross-Flow Bug: Lab Result Correction Was Missing

### Identified Gap

The lab result flow explains entry and validation but does not explain what happens when a result is released and later discovered to be wrong.

### Risk

Incorrect lab results can cause wrong diagnosis or treatment. Silently editing results destroys trust.

### Fix

Add a lab result amendment workflow.

### Corrected Flow

1. Lab identifies released result error.
2. Authorized lab supervisor opens result amendment.
3. Supervisor enters reason for amendment.
4. Original result remains preserved.
5. Corrected result is entered and validated.
6. Result status changes to “amended.”
7. Ordering provider is notified.
8. Patient timeline shows both original and amended status, with original result restricted or marked invalid based on policy.
9. Audit log records the full amendment.

### Implementation Control

Released lab results must not be overwritten. They must be amended.

---

## 68.10 Cross-Flow Bug: Prescription Cancellation Was Missing

### Identified Gap

The prescription flow creates prescriptions and pharmacy flow dispenses them, but cancellation and modification are not fully defined.

### Risk

A wrong prescription may remain active and be dispensed after the doctor intended to cancel it.

### Fix

Add prescription cancellation and amendment rules.

### Corrected Rule

A prescription may be cancelled only by an authorized prescriber or clinical supervisor before dispensing. If partially dispensed, only the undispensed balance can be cancelled. If fully dispensed, the record cannot be cancelled; it can only be clinically annotated.

### Required Statuses

Prescription statuses must include:

- draft
- issued
- partially dispensed
- fully dispensed
- cancelled
- expired
- suspended pending review

### Implementation Control

Pharmacy verification must check real-time prescription status before dispensing.

---

## 68.11 Cross-Flow Bug: Medication Administration Was Not Connected to Pharmacy Stock

### Identified Gap

Inpatient care mentions medication administration, while pharmacy dispensing and inventory handle stock. The connection between ward medication administration and stock deduction is not fully defined.

### Risk

Ward medications may be administered without accurate inventory tracking. Alternatively, stock may be deducted at dispensing but not reflected in actual administration.

### Fix

Separate medication ordering, ward issue, and administration.

### Corrected Rule

For admitted patients, medication workflow should support:

1. Doctor medication order.
2. Pharmacy issue to ward or patient.
3. Ward medication administration record.
4. Dose-level status: due, given, refused, held, missed, delayed, vomited, or discontinued.
5. Stock deduction based on facility policy: at pharmacy issue or at administration.

### Implementation Control

The medication administration record must be linked to prescription/order ID and stock movement where applicable.

---

## 68.12 Cross-Flow Bug: Billing Could Block Clinical Documentation

### Identified Gap

Billing flow may route patients before services, but clinical staff still need to document what happened even if payment is pending.

### Risk

If documentation depends too heavily on payment status, providers may document outside the system.

### Fix

Separate clinical documentation permission from service financial clearance.

### Corrected Rule

Financial status may control whether a non-emergency service can proceed, but it must not prevent authorized staff from documenting clinical care that has already occurred.

### Implementation Control

Encounter documentation must remain available to authorized providers regardless of billing status, with financial restrictions applied to service execution, not recordkeeping.

---

## 68.13 Cross-Flow Bug: Refunds and Reversals Were Not Defined

### Identified Gap

Billing mentions refunds but does not define reversal controls.

### Risk

Cashiers may reverse payments fraudulently or refund without approval.

### Fix

Add refund and payment reversal workflow.

### Corrected Rule

Refunds and reversals require reason, original transaction reference, user authorization, approval threshold, and audit logging. Completed clinical services should not be deleted when payment is reversed.

### Implementation Control

Payment reversal must create a reversing transaction, not delete the original payment.

---

## 68.14 Cross-Flow Bug: Insurance Claim Corrections Were Missing

### Identified Gap

Claims can be submitted and approved or rejected, but correction, resubmission, and insurer query response are not fully defined.

### Risk

Rejected claims may be resubmitted incorrectly or duplicated.

### Fix

Add claim lifecycle states.

### Required Statuses

Insurance claim statuses should include:

- draft
- submitted
- received by insurer
- queried
- under review
- approved
- partially approved
- rejected
- resubmitted
- paid
- closed
- cancelled

### Implementation Control

Every resubmission must link to the original claim and carry a version number.

---

## 68.15 Cross-Flow Bug: Referrals Need Expiry and Access Limits

### Identified Gap

Referral flow shares records but does not define expiry of referral access.

### Risk

A receiving facility may retain access longer than necessary.

### Fix

Referral access must be time-limited and scope-limited.

### Corrected Rule

Referral packages must include access expiry. After expiry, the receiving facility must request renewed access unless ongoing care relationship exists.

### Implementation Control

Referral consent should create a `referral_access_grant` tied to referral ID, scope, and expiry.

---

## 68.16 Cross-Flow Bug: Admission and Outpatient Encounter Relationship Was Not Fully Defined

### Identified Gap

A patient may begin as outpatient and become inpatient, but the transition between outpatient encounter and admission episode is not fully defined.

### Risk

Records may split into unrelated outpatient and inpatient events, making the timeline confusing.

### Fix

Admission should link to the originating encounter.

### Corrected Rule

If a patient is admitted from an outpatient or emergency visit, the inpatient admission must reference the original encounter ID. The timeline should show the visit as converted to admission or linked to admission episode.

### Implementation Control

Admission records must include `originating_encounter_id` where applicable.

---

## 68.17 Cross-Flow Bug: Death Record Handling Was Too Thin

### Identified Gap

Discharge and inpatient flows mention death record where applicable but do not define death workflow.

### Risk

A deceased patient profile may remain active. Future appointments, prescriptions, or fraud may occur under the profile.

### Fix

Add deceased patient workflow.

### Corrected Flow

1. Authorized provider records death event.
2. Death is reviewed or certified according to facility and legal rules.
3. Patient profile status changes to deceased.
4. Future appointments and prescriptions are blocked.
5. Existing claims, bills, and legal records remain accessible to authorized users.
6. Family or legal access follows policy.
7. Audit log records the event.

### Implementation Control

Deceased status must be identity-level, not only encounter-level.

---

## 68.18 Cross-Flow Bug: Inventory Expiry and Recall Handling Was Incomplete

### Identified Gap

Inventory handles expiry alerts but does not define expired stock blocking or product recall.

### Risk

Expired or recalled medication may be dispensed.

### Fix

Add expiry and recall enforcement.

### Corrected Rule

Expired batches must be blocked from dispensing and administration. Recalled batches must be flagged and blocked immediately. The system should identify patients who received recalled batches.

### Implementation Control

Stock batch status should include active, near expiry, expired, quarantined, recalled, depleted, and destroyed.

---

## 68.19 Cross-Flow Bug: API Pull Did Not Define Minimum Necessary Responses

### Identified Gap

API pull says authorized data is returned but does not define minimum necessary response principles.

### Risk

External systems may receive too much patient data.

### Fix

API responses must be purpose-scoped.

### Corrected Rule

The API must return data according to purpose and scope. For example, pharmacy dispensing should receive active prescription, allergy warnings, and patient verification details, not full consultation notes.

### Implementation Control

Every API token and request must carry `purpose_of_use`, `requested_scope`, and `requesting_actor` where possible.

---

## 68.20 Cross-Flow Bug: External System Identity of Actual User Was Not Guaranteed

### Identified Gap

External hospital API flows authenticate the hospital system but may not identify the actual doctor, nurse, or pharmacist using that system.

### Risk

Audit logs show only the external system, not the actual human actor.

### Fix

External integrations should pass end-user identity where possible.

### Corrected Rule

External systems must include requesting user identifier, role, department, and facility context where technically possible. If unavailable, the integration must be marked as system-level access and treated as higher risk.

### Implementation Control

API payloads should support `external_user_id`, `external_user_role`, and `external_session_id`.

---

## 68.21 Cross-Flow Bug: Webhook Payloads Could Leak Data

### Identified Gap

Webhook flow mentions event delivery but must be stricter about payload contents.

### Risk

Webhook endpoints may leak sensitive medical information if they are compromised.

### Fix

Use minimal webhook payloads.

### Corrected Rule

Webhooks should normally send event ID, event type, timestamp, patient reference, and resource reference. The partner system should call the API to retrieve details after authentication and authorization.

### Implementation Control

Webhook payloads must not include full clinical content unless explicitly approved for that partner and event type.

---

## 68.22 Cross-Flow Bug: Patient Portal Access for Minors Needs Stronger Rules

### Identified Gap

Guardian access is mentioned, but transition from child to adult control is not operationally defined.

### Risk

A guardian may continue accessing sensitive records after the patient becomes legally independent.

### Fix

Add dependent transition rules.

### Corrected Rule

When a dependent reaches the configured age of control, OpesCare must trigger account transition. Guardian access should be reviewed, restricted, or converted to patient-approved access depending on law and policy.

### Implementation Control

The country configuration service must define `minor_age_limit`, `guardian_access_rules`, and `transition_workflow`.

---

## 68.23 Cross-Flow Bug: Patient Self-Upload Can Create Clinical Noise

### Identified Gap

Legacy uploads and self-uploads can fill the timeline with unverified documents.

### Risk

Doctors may waste time or trust unverified records incorrectly.

### Fix

Separate patient uploads from verified clinical timeline.

### Corrected Rule

Patient-submitted documents should appear in a separate “Patient Uploaded Records” section until reviewed. Verified or clinically relevant extracts can then be promoted into the main clinical timeline.

### Implementation Control

Each document must have `trust_status`: uploaded, pending review, verified, rejected, partially verified, or archived.

---

## 68.24 Cross-Flow Bug: Telemedicine Identity Verification Was Too Light

### Identified Gap

Telemedicine flow says the doctor verifies patient identity but does not define how.

### Risk

The wrong person may attend a remote consultation using another patient’s account.

### Fix

Telemedicine must include stronger identity confirmation.

### Corrected Rule

Before remote consultation, the system should verify patient login, OTP or app authentication, and at least one identity confirmation step. For high-risk consultations, the provider may request visual confirmation, document confirmation, or guardian confirmation.

### Implementation Control

Telemedicine encounters must include `identity_verified_method`.

---

## 68.25 Cross-Flow Bug: Device Data Can Overwhelm Providers

### Identified Gap

Device upload flow allows readings, but there is no rule to prevent excessive low-value data.

### Risk

Doctors may be flooded with readings and miss important changes.

### Fix

Device data should be summarized and threshold-based.

### Corrected Rule

Device readings should be stored in trends, not shown as endless timeline events. Alerts should be generated only for configured thresholds, repeated abnormalities, or clinically defined patterns.

### Implementation Control

Remote monitoring dashboards should summarize trends and exceptions.

---

## 68.26 Cross-Flow Bug: Public Health Reporting Needs Data Quality Flags

### Identified Gap

Public health reporting aggregates data but does not clearly show data quality limitations.

### Risk

Authorities may make decisions from incomplete or biased data.

### Fix

Reports should include data quality indicators.

### Corrected Rule

Public health dashboards must show reporting coverage, facility participation, missing data rates, duplicate rates, and verification status.

### Implementation Control

Every public health report should include a data quality score or confidence note.

---

## 68.27 Cross-Flow Bug: Research Data Re-Identification Risk Needs Stronger Protection

### Identified Gap

Research access mentions anonymization but needs stronger protection for small datasets.

### Risk

Patients with rare conditions or small-location data may be re-identified.

### Fix

Apply privacy-preserving research controls.

### Corrected Rule

Research datasets must apply de-identification, aggregation thresholds, suppression of small cell counts, purpose limitation, access expiry, and audit review.

### Implementation Control

Research exports must be reviewed by privacy officer or ethics committee before release.

---

## 68.28 Cross-Flow Bug: Country Configuration Changes Can Break Existing Records

### Identified Gap

Country configuration flow allows changes but does not define how changes affect existing consents, records, and workflows.

### Risk

Changing age of consent, retention rules, or access rules can create inconsistency.

### Fix

Country configuration must be versioned and effective-dated.

### Corrected Rule

Every country configuration change must have version number, effective date, approver, change reason, and migration impact review.

### Implementation Control

Existing records should retain the policy version that governed them at creation, while future actions apply the new policy version.

---

## 68.29 Cross-Flow Bug: Notification Content Could Reveal Sensitive Data

### Identified Gap

Notifications are used in many flows, but message content rules are not defined.

### Risk

SMS or email could reveal sensitive diagnosis, lab results, HIV status, pregnancy status, mental health details, or insurance information.

### Fix

Define safe notification templates.

### Corrected Rule

Notifications over insecure channels should be generic. For example: “You have a new health update in OpesCare” instead of revealing the condition or result.

### Implementation Control

Notification templates must be classified by sensitivity and approved before use.

---

## 68.30 Cross-Flow Bug: Role Permissions Need Separation of Duties

### Identified Gap

The flows define roles but do not enforce separation of duties.

### Risk

One user may create, approve, bill, refund, and audit the same transaction, enabling fraud.

### Fix

Add separation-of-duty rules.

### Corrected Rule

High-risk actions should require separate actors or approval thresholds. Examples include refunds, stock adjustments, facility suspension, user role elevation, claim approval, and record correction.

### Implementation Control

The access control engine must support permission conflicts and approval workflows.

---

## 68.31 Cross-Flow Bug: Clinical Decision Support Can Create Liability and Alert Fatigue

### Identified Gap

Clinical alerts are described but not governed.

### Risk

Too many alerts will be ignored. Wrong alerts may influence clinical decisions incorrectly.

### Fix

Govern clinical alerts through severity, evidence level, and override rules.

### Corrected Rule

Alerts must be categorized as informational, warning, severe, or blocking. Severe alerts require acknowledgment. Blocking alerts require authorized override or cannot be bypassed depending on policy.

### Implementation Control

Each alert should record whether it was shown, acknowledged, overridden, and why.

---

## 68.32 Cross-Flow Bug: Encounter Closure Criteria Were Not Defined

### Identified Gap

Several flows say the encounter is closed, but closure rules are not defined.

### Risk

Encounters may remain open forever or close before labs, billing, prescriptions, and referrals are complete.

### Fix

Add encounter closure rules.

### Corrected Rule

An encounter can be open, in progress, pending lab, pending imaging, pending pharmacy, pending billing, admitted, discharged, cancelled, or closed. Closure requires that mandatory clinical documentation is complete and unresolved department tasks are either completed, cancelled, or explicitly left pending.

### Implementation Control

The system should show open encounter dashboards and aging alerts.

---

## 68.33 Cross-Flow Bug: Cancelled Visits Were Missing

### Identified Gap

Appointments and encounters can be created, but cancellation rules are not detailed.

### Risk

Cancelled visits may leave active queue tickets, bills, lab orders, or prescriptions.

### Fix

Add visit cancellation cascade rules.

### Corrected Rule

Cancelling a visit must check dependent objects. Pending queue tickets should be cancelled. Unpaid invoices may be voided. Lab orders, prescriptions, and referrals require separate cancellation rules depending on status.

### Implementation Control

Visit cancellation must show an impact preview before confirmation.

---

## 68.34 Cross-Flow Bug: Data Migration Could Import Sensitive Records Without Consent Policy

### Identified Gap

Migration imports historical records, but consent and sensitive record handling are not defined.

### Risk

Highly sensitive historical records may become widely visible after migration.

### Fix

Sensitive migrated records must be classified and access-controlled.

### Corrected Rule

Migration must classify record types and apply sensitivity labels. Sensitive records should require restricted access according to country and facility policy.

### Implementation Control

Migration mapping must include `record_sensitivity` and `default_access_policy`.

---

## 68.35 Cross-Flow Bug: Integration Failure Handling Was Too Thin

### Identified Gap

External API and webhook flows mention failure but do not fully define monitoring and reconciliation.

### Risk

A hospital may think data synchronized when it failed. OpesCare may miss important records.

### Fix

Add integration monitoring and reconciliation dashboards.

### Corrected Rule

Every integration should expose sync status, failed events, retry count, last successful sync, pending queue, and error reason. Failed clinical events must be visible to integration support and facility administrators.

### Implementation Control

Critical failed events should trigger alerts.

---

## 68.36 Cross-Flow Bug: Patient Death, Merge, and Consent Interactions Need Control

### Identified Gap

Deceased status, patient merge, consent, and guardian access can interact in complex ways.

### Risk

A deceased patient may still have active consent, appointments, prescriptions, or guardian access. A merged profile may retain incorrect consent grants.

### Fix

Identity status changes must trigger dependent reviews.

### Corrected Rule

When a patient is marked deceased, merged, unmerged, suspended, or identity-corrected, the system must review active appointments, consents, prescriptions, insurance claims, access grants, and portal access.

### Implementation Control

Identity status changes should trigger an `identity_status_change_review` workflow.

---

## 68.37 Final Remediation Summary

The flows are now stronger after the second-pass review. The most important fixes are:

1. All patient creation must go through one Patient Identity Service.
2. Provisional profiles need formal lifecycle statuses.
3. Unknown emergency patients need temporary identities and later reconciliation.
4. Emergency care must bypass normal admin barriers while preserving audit.
5. Offline caches must respect consent scope, expiry, and reconciliation.
6. Audit logs need a mandatory schema.
7. All records need source metadata and verification status.
8. High-risk actions require two patient identifiers.
9. Released lab results must be amended, not overwritten.
10. Prescriptions need cancellation, expiry, and partial-dispense rules.
11. Ward medication administration must connect to prescription and stock controls.
12. Billing reversals and refunds must use reversing transactions.
13. Insurance claims need full lifecycle states and versioning.
14. Referral access must be time-limited and scope-limited.
15. Admission must link outpatient, emergency, and inpatient episodes.
16. Deceased patient handling must be identity-level.
17. Expired and recalled stock must be blocked.
18. External APIs must be purpose-scoped and actor-aware.
19. Webhooks must use minimal payloads.
20. Minor-to-adult account transitions must be controlled.
21. Patient uploads must remain separate until verified.
22. Telemedicine needs explicit identity verification.
23. Device data must be summarized and threshold-driven.
24. Public health reports must include data quality indicators.
25. Research data needs re-identification controls.
26. Country configuration must be versioned and effective-dated.
27. Notifications must not leak sensitive data.
28. Separation of duties must be enforced for high-risk actions.
29. Clinical alerts need governance and severity levels.
30. Encounter closure and cancellation need cascade rules.
31. Migration must classify sensitive records.
32. Integration failures need dashboards and alerting.
33. Identity status changes must trigger dependent reviews.

These remediations should be treated as required design rules before engineering implementation begins.



---

# 69. OpesCare Connect Suite: Hospital Push, Pull, SDK, Widget, and Integration Architecture

This section defines how hospitals, clinics, laboratories, pharmacies, insurers, and third-party health systems communicate with OpesCare. It expands the OpesCare platform from a medical record system into a true interoperability infrastructure.

The central idea is simple: every connected health system must be able to **pull authorized patient data from OpesCare** and **push new patient data to OpesCare**.

This must work whether the hospital uses Opesware software, a third-party hospital system, old legacy software, spreadsheets, paper workflows, or no existing digital system at all.

---

## 69.1 Core Integration Principle

OpesCare must not depend on one hospital software. Different hospitals will use different systems. Some will use Opesware-built systems. Some will use other hospital information systems. Some will have laboratory systems only. Some will have pharmacy systems only. Some will still use paper.

Therefore, OpesCare must provide multiple integration methods.

The recommended integration product family is called:

**OpesCare Connect Suite**

The OpesCare Connect Suite includes:

1. **OpesCare Connect API**
2. **OpesCare Connect SDK**
3. **OpesCare Connect Widget**
4. **OpesCare Bridge Agent**
5. **OpesCare Lite Portal**
6. **OpesCare Webhooks and Event Service**
7. **OpesCare Integration Dashboard**

Together, these products allow any hospital system to communicate with OpesCare.

---

## 69.2 Integration Levels

OpesCare should support five integration levels.

### Level 1: Opesware-Native Systems

These are hospital systems built by Opesware or designed from the beginning to work with OpesCare.

In these systems, OpesCare functions can be built directly into the user interface.

Examples:

- Pull from OpesCare
- Push to OpesCare
- Sync with OpesCare
- Request Patient Consent
- View OpesCare Timeline
- Send Lab Result to OpesCare
- Send Prescription to OpesCare
- Send Discharge Summary to OpesCare

This is the cleanest and most powerful integration level because the hospital software and OpesCare can share compatible data models, authentication methods, and workflow rules.

### Level 2: Third-Party Systems Using API

These are hospitals that already use their own software and have technical teams or vendors capable of integrating with APIs.

They connect through the OpesCare Connect API.

Their system can search patients, request consent, pull patient data, push encounters, push lab results, push prescriptions, push discharge summaries, and receive webhook notifications.

### Level 3: Third-Party Systems Using SDK

Some software vendors may not want to write raw API calls. For them, OpesCare should provide SDKs.

The SDK wraps API calls into simple functions such as:

- searchPatient()
- requestConsent()
- pullPatientSummary()
- pushEncounter()
- pushLabResult()
- pushPrescription()
- pushDischargeSummary()
- syncOfflineEvents()

SDKs should be available for common programming languages, including JavaScript or TypeScript, PHP, Python, Java, and C#/.NET.

### Level 4: Embedded Widget Integration

Some hospital systems may want a simple plug-in interface instead of deep API integration.

The OpesCare Connect Widget can be embedded into their system. It provides a ready-made interface for patient search, consent request, pull, push, document upload, and sync status.

This reduces the work required by third-party vendors.

### Level 5: Bridge Agent and OpesCare Lite

Older hospitals may not have modern APIs. Some may export CSV, Excel, XML, JSON, or database reports. Others may use paper.

For these facilities, OpesCare provides:

- **OpesCare Bridge Agent** for legacy software and file-based sync
- **OpesCare Lite Portal** for manual use by hospitals without software

This ensures that even low-technology facilities can participate.

---

## 69.3 Push and Pull Definitions

### Pull from OpesCare

Pull means the hospital system requests patient data from OpesCare.

Examples:

- Pull emergency profile
- Pull recent clinical summary
- Pull allergies
- Pull lab results
- Pull medication history
- Pull referral package
- Pull insurance eligibility
- Pull full record with explicit consent

Pull must always be controlled by consent, purpose, user role, facility authorization, and data scope.

### Push to OpesCare

Push means the hospital system sends new patient data to OpesCare.

Examples:

- Push consultation record
- Push diagnosis
- Push prescription
- Push lab result
- Push imaging report
- Push discharge summary
- Push vaccination record
- Push referral
- Push pharmacy dispensing event
- Push insurance claim event

Push must always pass through authentication, validation, patient matching, duplicate detection, source attribution, and audit logging.

---

## 69.4 High-Level Integration Architecture

The integration architecture should be structured as follows:

```text
Hospital System
     ↓
OpesCare SDK / Widget / Bridge Agent / Direct API
     ↓
OpesCare API Gateway
     ↓
Authentication + Facility Verification + User Verification
     ↓
Consent Engine + Access Control Engine
     ↓
Validation Engine
     ↓
Master Patient Index
     ↓
Synchronization Engine
     ↓
Patient Timeline and Record Services
```

For data pull:

```text
Hospital System
     ↓
Request patient data
     ↓
OpesCare API Gateway
     ↓
Consent + Access Control
     ↓
Patient Record Service
     ↓
Purpose-scoped response
     ↓
Hospital System
```

For data push:

```text
Hospital System
     ↓
Create medical event
     ↓
SDK / Widget / Bridge / API formats payload
     ↓
OpesCare API Gateway authenticates request
     ↓
Validation engine validates schema
     ↓
MPI matches patient
     ↓
Sync engine checks duplicates and conflicts
     ↓
Event is written to patient timeline
     ↓
Sync status returned to hospital system
```

---

## 69.5 Opesware-Native Pull and Push Buttons

For Opesware-based hospital systems, the user interface should include direct actions.

### Pull from OpesCare Button

This button retrieves authorized patient data from OpesCare.

When clicked, it should:

1. Confirm the patient identity.
2. Check whether the patient has an OpesCare Health ID.
3. Request consent if needed.
4. Pull only the selected data scope.
5. Display the retrieved data inside the hospital system.
6. Log the access.

Available pull scopes should include:

- emergency summary
- allergies only
- recent clinical summary
- medication history
- lab history
- imaging reports
- referral package
- full record with consent

### Push to OpesCare Button

This button sends selected hospital data to OpesCare.

When clicked, it should:

1. Confirm patient identity.
2. Confirm that the record is complete enough to share.
3. Package the data into an OpesCare-compatible event.
4. Send it to OpesCare through the API Gateway.
5. Receive sync status.
6. Show success, warning, or error.
7. Store the sync reference locally.

### Sync with OpesCare Button

This button performs both pull and push operations.

It can:

- pull updated patient summary
- push unsynced local events
- resolve sync conflicts
- display failed sync events
- refresh consent status

---

## 69.6 OpesCare Connect API

The OpesCare Connect API is the official API used by external systems.

The API must be secure, versioned, documented, and purpose-scoped.

### Core API Categories

The API should include:

1. Authentication APIs
2. Facility APIs
3. Patient Search APIs
4. Consent APIs
5. Patient Pull APIs
6. Clinical Record Push APIs
7. Lab APIs
8. Prescription and Pharmacy APIs
9. Insurance APIs
10. Referral APIs
11. Document APIs
12. Webhook APIs
13. Sync Status APIs
14. Error and Reconciliation APIs

---

## 69.7 Example API Endpoints

Recommended endpoint structure:

```text
POST /api/v1/auth/token
POST /api/v1/patients/search
POST /api/v1/consents/request
POST /api/v1/consents/verify
GET  /api/v1/patients/{health_id}/summary
GET  /api/v1/patients/{health_id}/emergency-profile
GET  /api/v1/patients/{health_id}/lab-results
GET  /api/v1/patients/{health_id}/medications
POST /api/v1/records/encounters
POST /api/v1/records/lab-results
POST /api/v1/records/prescriptions
POST /api/v1/records/dispense-events
POST /api/v1/records/imaging-reports
POST /api/v1/records/discharge-summaries
POST /api/v1/records/referrals
POST /api/v1/documents/upload
GET  /api/v1/sync/events/{event_id}
GET  /api/v1/sync/status
POST /api/v1/webhooks/subscriptions
```

All endpoints must require authentication except public status or discovery endpoints explicitly approved by OpesCare.

---

## 69.8 Authentication and Authorization for Integrations

External systems must never push or pull data anonymously.

Authentication should support:

- OAuth 2.0 client credentials for system-to-system integration
- signed API keys for simpler integrations
- mutual TLS for high-security partners
- JWT-based access tokens
- rotating secrets
- facility-specific credentials
- environment separation between sandbox and production

Authorization should check:

- facility status
- facility type
- API product subscription
- endpoint permission
- purpose of use
- requested data scope
- patient consent
- user role where available

External systems should include the actual human actor where possible.

Required external actor fields should include:

- external user ID
- external user name where policy allows
- external user role
- department
- facility ID
- session ID

If the external system cannot identify the actual user, the integration should be marked as higher risk and limited in scope.

---

## 69.9 Patient Search API

Hospitals need to find patients before pulling or pushing data.

### Search Request

A hospital system may search using:

- OpesCare Health ID
- QR token
- phone number
- national ID hash where applicable
- insurance number
- name and date of birth
- facility local patient number mapped to OpesCare

### Example Request

```json
{
  "facility_id": "FAC-001",
  "search_type": "health_id",
  "search_value": "OC-CMR-7KQ9-MP42-X8D1",
  "requesting_user": {
    "external_user_id": "DOC-991",
    "role": "doctor",
    "department": "outpatient"
  }
}
```

### Example Response

```json
{
  "status": "success",
  "matches": [
    {
      "patient_ref": "pat_8f92a",
      "health_id": "OC-CMR-7KQ9-MP42-X8D1",
      "display_name": "Amina T.",
      "sex": "female",
      "age": 32,
      "identity_status": "verified_by_facility",
      "match_confidence": "exact"
    }
  ]
}
```

The response should not expose sensitive clinical data. It should only provide enough information to confirm identity.

---

## 69.10 Consent Request API

Before pulling protected data, the hospital system may need consent.

### Example Request

```json
{
  "patient_ref": "pat_8f92a",
  "facility_id": "FAC-001",
  "requesting_user": {
    "external_user_id": "DOC-991",
    "role": "doctor"
  },
  "purpose": "consultation",
  "requested_scope": [
    "recent_clinical_summary",
    "allergies",
    "medication_history",
    "recent_lab_results"
  ],
  "duration_minutes": 240
}
```

### Example Response

```json
{
  "status": "pending_patient_approval",
  "consent_request_id": "conreq_12093",
  "approval_methods": [
    "patient_app",
    "sms_otp",
    "facility_pin"
  ]
}
```

If the patient approves, the system returns a consent grant.

```json
{
  "status": "approved",
  "consent_grant_id": "cgrant_99811",
  "scope": [
    "recent_clinical_summary",
    "allergies",
    "medication_history",
    "recent_lab_results"
  ],
  "expires_at": "2026-05-05T16:30:00Z"
}
```

---

## 69.11 Patient Data Pull API

The hospital system pulls data by purpose and scope.

### Example Pull Request

```json
{
  "facility_id": "FAC-001",
  "requesting_user": {
    "external_user_id": "DOC-991",
    "role": "doctor",
    "department": "outpatient"
  },
  "purpose": "consultation",
  "scope": "recent_clinical_summary",
  "consent_grant_id": "cgrant_99811"
}
```

### Example Response

```json
{
  "patient": {
    "patient_ref": "pat_8f92a",
    "health_id": "OC-CMR-7KQ9-MP42-X8D1",
    "display_name": "Amina T.",
    "sex": "female",
    "age": 32
  },
  "summary": {
    "allergies": [
      {
        "substance": "Penicillin",
        "severity": "high",
        "source": "Hospital A",
        "recorded_at": "2025-09-14"
      }
    ],
    "chronic_conditions": [
      {
        "condition": "Hypertension",
        "source": "Clinic B",
        "recorded_at": "2024-11-02"
      }
    ],
    "recent_visits": [
      {
        "date": "2026-04-20",
        "facility": "Hospital A",
        "diagnosis": "Malaria, unspecified",
        "summary": "Treated with ACT therapy"
      }
    ],
    "recent_labs": [
      {
        "date": "2026-04-20",
        "test": "Malaria RDT",
        "result": "Positive",
        "source": "Hospital A Lab"
      }
    ]
  }
}
```

The API should not return full medical history unless the request purpose, user role, consent scope, and policy allow it.

---

## 69.12 Clinical Event Push API

Hospitals push data to OpesCare as clinical events.

A clinical event may be:

- encounter
- diagnosis
- lab result
- prescription
- dispense event
- imaging report
- procedure
- discharge summary
- referral
- vaccination
- admission
- nursing record

### Example Encounter Push

```json
{
  "health_id": "OC-CMR-7KQ9-MP42-X8D1",
  "facility_id": "FAC-001",
  "source_system": "HospitalA-HIS",
  "external_reference": "HOSPITAL-A-VISIT-12345",
  "idempotency_key": "HOSPITAL-A-VISIT-12345-ENCOUNTER-V1",
  "provider": {
    "external_user_id": "DOC-991",
    "role": "doctor",
    "department": "outpatient"
  },
  "encounter": {
    "type": "outpatient",
    "started_at": "2026-05-05T09:15:00Z",
    "ended_at": "2026-05-05T10:05:00Z",
    "presenting_complaint": "Fever and headache",
    "clinical_summary": "Patient presented with fever, headache, and chills.",
    "diagnoses": [
      {
        "code": "B54",
        "system": "ICD-10",
        "label": "Malaria, unspecified"
      }
    ],
    "treatment_plan": "ACT therapy, hydration, follow-up if symptoms persist."
  }
}
```

### Example Response

```json
{
  "status": "accepted",
  "opes_event_id": "evt_893922",
  "opes_record_id": "rec_729201",
  "patient_ref": "pat_8f92a",
  "sync_status": "stored_in_patient_timeline",
  "message": "Encounter added to patient timeline."
}
```

---

## 69.13 Idempotency and Duplicate Prevention

Hospitals may retry API calls when internet is unstable. Without controls, the same encounter or lab result may be pushed multiple times.

Every push request must include an idempotency key.

The idempotency key should be unique per source event.

Example:

```text
{facility_id}-{external_reference}-{record_type}-{version}
```

If OpesCare receives the same idempotency key twice, it should not create duplicate records. It should return the original sync result.

### Corrected Rule

No pushed clinical event should be accepted without idempotency protection.

---

## 69.14 Validation Engine for Pushed Data

Before storing pushed data, OpesCare must validate it.

Validation checks should include:

- authenticated facility
- active facility status
- authorized endpoint
- valid patient reference or Health ID
- valid provider or external actor where available
- required fields present
- valid date formats
- valid coding systems where used
- valid record type
- duplicate detection
- idempotency key
- source attribution
- record sensitivity classification

If validation fails, OpesCare should return a structured error.

### Example Error Response

```json
{
  "status": "rejected",
  "error_code": "MISSING_REQUIRED_FIELD",
  "message": "encounter.started_at is required",
  "sync_status": "not_stored"
}
```

For non-critical mapping issues, OpesCare may accept the record but flag it for review.

---

## 69.15 Source Attribution for Pushed Data

Every pushed record must preserve source attribution.

Required metadata:

- source facility
- source organization
- source system
- external reference
- provider or external user
- department
- submission timestamp
- clinical event timestamp
- verification status
- idempotency key
- API client ID

This allows doctors to know where each record came from.

The patient timeline should display source attribution clearly.

Example:

```text
Consultation Summary
Source: Hospital A
Submitted by: HospitalA-HIS
Provider: DOC-991
Date of care: 05 May 2026
Sync status: Verified source
```

---

## 69.16 OpesCare Connect SDK

The SDK is a developer toolkit that simplifies API integration.

Instead of writing raw HTTP requests, developers can call SDK functions.

### SDK Functions

The SDK should include:

```text
initializeClient()
searchPatient()
requestConsent()
verifyConsent()
pullPatientSummary()
pullEmergencyProfile()
pullLabResults()
pullMedicationHistory()
pushEncounter()
pushLabResult()
pushPrescription()
pushDispenseEvent()
pushDischargeSummary()
pushReferral()
uploadDocument()
syncOfflineEvents()
getSyncStatus()
subscribeWebhook()
```

### Example JavaScript SDK Usage

```javascript
const opesCare = new OpesCareClient({
  clientId: "client_abc",
  clientSecret: "secret_xyz",
  facilityId: "FAC-001",
  environment: "production"
});

const patient = await opesCare.searchPatient({
  healthId: "OC-CMR-7KQ9-MP42-X8D1"
});

const consent = await opesCare.requestConsent({
  patientRef: patient.patient_ref,
  purpose: "consultation",
  scope: ["recent_clinical_summary", "allergies"]
});

const summary = await opesCare.pullPatientSummary({
  patientRef: patient.patient_ref,
  consentGrantId: consent.consent_grant_id,
  scope: "recent_clinical_summary"
});

await opesCare.pushEncounter({
  patientRef: patient.patient_ref,
  externalReference: "VISIT-12345",
  idempotencyKey: "VISIT-12345-ENCOUNTER-V1",
  encounter: {
    type: "outpatient",
    presentingComplaint: "Fever and headache",
    diagnosis: "Malaria, unspecified",
    treatmentPlan: "ACT therapy and follow-up"
  }
});
```

### SDK Requirements

The SDK must handle:

- authentication
- token refresh
- request signing
- idempotency keys
- retries
- offline queueing where supported
- error handling
- logging
- sandbox mode
- production mode

---

## 69.17 OpesCare Connect Widget

The widget is an embeddable user interface that third-party hospital systems can place inside their software.

It is useful when the hospital vendor does not want to build a full integration.

### Widget Capabilities

The widget should support:

- patient search
- QR scan
- consent request
- pull patient summary
- show emergency profile
- push current visit
- upload document
- push lab result
- push prescription
- show sync status
- show failed sync events

### Example Embed Code

```html
<script src="https://sdk.opescare.com/widget.js"></script>

<div id="opescare-widget"></div>

<script>
  OpesCareWidget.mount("#opescare-widget", {
    facilityId: "FAC-001",
    mode: "patient-sync",
    patientExternalReference: "LOCAL-PAT-8821",
    encounterExternalReference: "VISIT-12345"
  });
</script>
```

### Widget Security

The widget must not rely only on front-end secrets. It should use secure server-issued tokens or signed sessions.

The hospital system should request a widget session token from OpesCare, then load the widget with that token.

### Widget Session Flow

1. Hospital backend authenticates with OpesCare.
2. Hospital backend requests a widget session token.
3. OpesCare returns a short-lived widget token.
4. Hospital frontend loads widget with token.
5. Widget performs only permitted actions.
6. All actions are logged.

---

## 69.18 OpesCare Bridge Agent

The Bridge Agent is a local connector installed at the hospital for legacy systems.

It is useful when a hospital has old software that cannot call modern APIs.

### Bridge Agent Functions

The Bridge Agent can:

- read exported CSV, Excel, XML, or JSON files
- connect to local databases where approved
- monitor folders for new files
- map local fields to OpesCare fields
- queue data when internet is down
- sync when internet returns
- send records securely to OpesCare
- receive pull results where configured
- log errors
- show sync dashboard

### Bridge Agent Architecture

```text
Hospital Legacy System
     ↓
Export file / Local database / Folder monitor
     ↓
OpesCare Bridge Agent
     ↓
Mapping + Validation + Queue
     ↓
Secure API Sync
     ↓
OpesCare Cloud
```

### Bridge Agent Requirements

The Bridge Agent must support:

- encrypted local storage
- secure credentials
- automatic retry
- offline queue
- mapping configuration
- sync logs
- failed event review
- software updates
- health check reporting

---

## 69.19 OpesCare Lite Portal

OpesCare Lite is for facilities without software.

It allows them to use OpesCare directly through a browser or mobile device.

### OpesCare Lite Functions

Facilities can:

- register patients
- search Health ID
- request consent
- view patient summary
- record consultation
- order labs
- upload lab results
- issue prescriptions
- dispense medication
- record payments
- upload documents
- create referrals
- close encounters

This ensures that paper-based hospitals can still participate in OpesCare.

---

## 69.20 Webhooks and Event Notifications for Integrated Systems

Webhooks allow OpesCare to notify external systems when important events occur.

### Webhook Events

Common events include:

- patient.created
- patient.updated
- consent.granted
- consent.revoked
- encounter.created
- lab_result.ready
- prescription.issued
- prescription.cancelled
- medication.dispensed
- referral.created
- referral.accepted
- claim.submitted
- claim.approved
- emergency_access.used
- record.amended
- sync.failed

### Minimal Webhook Payload

```json
{
  "event_id": "wevt_78211",
  "event_type": "lab_result.ready",
  "occurred_at": "2026-05-05T10:30:00Z",
  "patient_ref": "pat_8f92a",
  "resource_type": "lab_result",
  "resource_id": "lab_99201",
  "signature": "signed_payload_hash"
}
```

The webhook should usually not include full clinical content. The partner system should call the API to retrieve details after authorization.

### Webhook Security

Webhooks must include:

- signed payloads
- timestamp
- event ID
- replay protection
- retry rules
- dead-letter queue
- endpoint verification

---

## 69.21 Sync Status Dashboard

Every integrated facility should have an OpesCare Connect Dashboard.

### Dashboard Fields

The dashboard should show:

- facility name
- integration type
- connection status
- API health
- last successful sync
- pending events
- failed events
- records pushed today
- records pulled today
- consent requests today
- webhook delivery status
- API error rate
- token status
- bridge agent status where applicable

### Example Dashboard

```text
OpesCare Connect Dashboard

Facility: Hospital A
Integration Type: API + SDK
Connection Status: Active
Last Successful Sync: Today, 10:42 AM
Pending Events: 3
Failed Events: 1
Records Pushed Today: 39
Records Pulled Today: 45
Consent Requests Today: 12
Webhook Status: Healthy
Bridge Agent: Not Installed
```

This dashboard helps both OpesCare and the hospital trust that synchronization is working.

---

## 69.22 Sync Failure Handling

Sync failures must be visible and recoverable.

### Failure Types

Possible failures include:

- authentication failed
- invalid patient ID
- patient match not found
- consent missing
- schema validation failed
- duplicate event
- unmapped code
- network failure
- expired token
- facility suspended
- unauthorized scope

### Failure Response

Every failed event should show:

- failure reason
- error code
- event ID
- facility ID
- source reference
- retry status
- required action

### Example Failure Response

```json
{
  "status": "rejected",
  "error_code": "CONSENT_REQUIRED",
  "message": "Patient consent is required before pulling recent clinical summary.",
  "required_action": "request_consent",
  "retry_allowed": true
}
```

### Corrected Rule

Clinical sync failures must never disappear silently. They must appear in the dashboard and be available for reconciliation.

---

## 69.23 Offline Sync for Integrated Hospitals

Hospitals may lose internet connection. OpesCare integrations must support offline queueing.

### Offline Push Queue

When internet is unavailable, the SDK, Bridge Agent, or Opesware-native system should queue events locally.

Queued events should include:

- event type
- patient reference
- local patient reference
- payload
- idempotency key
- timestamp
- user
- facility
- retry count
- sync status

When internet returns, the queued events are sent to OpesCare.

### Offline Pull Limitation

Pulling fresh patient data requires connectivity. Offline pull can only show cached records that are still valid under consent and cache rules.

### Corrected Rule

Offline systems may push queued records later, but must not claim that data is synchronized until OpesCare confirms acceptance.

---

## 69.24 Data Mapping for Third-Party Systems

Third-party systems may use different field names and local codes.

The OpesCare integration layer must support mapping.

### Mapping Examples

```text
Local Field             OpesCare Field
pt_name                 patient.full_name
visit_no                encounter.external_reference
doctor_code             provider.external_user_id
test_desc               lab_result.test_name
result_val              lab_result.result_value
rx_drug                 prescription.medication_name
```

### Code Mapping

Hospitals may use local diagnosis codes, local lab names, or local medication names.

The mapping engine should support:

- local diagnosis to ICD
- local lab test to LOINC where possible
- local medication name to standard medication catalog where possible
- local department names to OpesCare department types

Unmapped codes should not necessarily block sync. They can be accepted with review status if the record is otherwise safe.

---

## 69.25 Data Contracts for Pushed Records

Each pushed record type must have a data contract.

A data contract defines required fields, optional fields, accepted values, validation rules, source metadata, and error responses.

### Encounter Contract

Required fields:

- patient reference or Health ID
- facility ID
- source system
- external reference
- idempotency key
- provider or responsible user
- encounter type
- start time

Optional fields:

- end time
- presenting complaint
- clinical summary
- diagnosis
- treatment plan
- prescriptions
- lab orders
- referrals

### Lab Result Contract

Required fields:

- patient reference or Health ID
- facility ID
- lab source
- order ID or external reference
- sample ID where applicable
- test name
- result value
- unit where applicable
- result timestamp
- validation status
- validator where applicable

### Prescription Contract

Required fields:

- patient reference or Health ID
- facility ID
- prescriber
- medication name
- dose
- frequency
- duration
- issue timestamp
- prescription status

### Discharge Summary Contract

Required fields:

- patient reference or Health ID
- facility ID
- admission reference
- discharge date
- final diagnosis
- treatment given
- discharge medication where applicable
- follow-up instruction
- responsible provider

---

## 69.26 Pull Scopes and Minimum Necessary Access

Pull access must be limited by purpose.

### Recommended Pull Scopes

```text
emergency_summary
identity_confirmation
allergies_only
current_medications
recent_clinical_summary
recent_lab_results
lab_history
imaging_reports
prescription_history
pharmacy_view
insurance_view
referral_package
inpatient_summary
full_record_with_explicit_consent
```

### Scope Examples

A pharmacist should usually pull:

- identity confirmation
- active prescription
- allergy warning
- dispensing status

A lab should usually pull:

- identity confirmation
- lab order
- sample instructions

A doctor may pull:

- recent clinical summary
- allergies
- medications
- recent labs
- chronic conditions

An insurer may pull:

- eligibility data
- claim-related documents
- authorization information

The insurer should not automatically receive full clinical history.

---

## 69.27 Consent and Purpose of Use in Integrations

Every pull request must include purpose of use.

Purpose examples:

- consultation
- emergency_care
- referral
- lab_processing
- pharmacy_dispensing
- insurance_verification
- claim_processing
- public_health_reporting
- research_approved

Purpose determines what data can be returned.

### Corrected Rule

A valid API token alone is not enough. The request must also have valid purpose, authorized scope, facility permission, and consent where required.

---

## 69.28 Patient Matching During Push

When a hospital pushes data, OpesCare must know which patient the record belongs to.

Preferred identifiers:

1. OpesCare patient reference
2. OpesCare Health ID
3. QR token
4. mapped local patient ID
5. national ID hash where applicable
6. insurance number
7. demographic matching as fallback

If the patient cannot be confidently matched, OpesCare should not attach the record to the wrong patient.

### Corrected Rule

Unmatched pushed records should go into a reconciliation queue, not into a random patient profile.

---

## 69.29 Reconciliation Queue

The reconciliation queue handles records that could not be safely synchronized.

Reasons may include:

- patient not found
- multiple patient matches
- invalid data
- conflicting identity information
- missing required fields
- unmapped facility
- duplicate suspicion

Authorized staff can review and resolve reconciliation cases.

Resolution options:

- attach to confirmed patient
- create provisional patient
- reject record
- request correction from source facility
- merge with existing record
- mark as duplicate

Every resolution must be audited.

---

## 69.30 Integration Certification

Before a third-party system can connect to production, it should pass certification.

Certification checks:

- authentication works
- patient search works safely
- consent workflow is respected
- pull scopes are correct
- push payloads follow contracts
- idempotency works
- errors are handled correctly
- webhook signatures are verified
- audit metadata is included
- no excessive data is requested
- sandbox tests passed

Certified integrations can receive production credentials.

---

## 69.31 Sandbox Environment

OpesCare should provide a sandbox environment for developers.

The sandbox should include:

- test API keys
- test facilities
- test patients
- sample consent flows
- sample records
- fake lab results
- fake prescriptions
- webhook testing
- API documentation
- error simulation

The sandbox must not contain real patient data.

---

## 69.32 Versioning and Backward Compatibility

Hospital integrations can break if APIs change unexpectedly.

OpesCare must version APIs.

Example:

```text
/api/v1/records/encounters
/api/v2/records/encounters
```

Rules:

- breaking changes require a new major version
- old versions should be supported for a defined period
- deprecation notices should be sent to partners
- SDKs should clearly state supported API versions

---

## 69.33 Integration Security Threats and Fixes

### Threat: Stolen API Keys

Fix: Rotate secrets, use short-lived tokens, restrict by IP where possible, use mTLS for high-risk integrations.

### Threat: Fake Hospital Pushes Data

Fix: Verify facility, sign requests, require production certification, monitor suspicious activity.

### Threat: Excessive Patient Data Pull

Fix: Purpose-scoped APIs, consent checks, rate limits, access logs, anomaly detection.

### Threat: Webhook Replay Attack

Fix: Signed payloads, timestamps, event IDs, replay protection.

### Threat: Duplicate Data Push

Fix: Idempotency keys and duplicate detection.

### Threat: Wrong Patient Attachment

Fix: MPI matching, two identifiers, reconciliation queue, no unsafe auto-attachment.

---

## 69.34 Complete Hospital Push Flow

This is the corrected complete push flow for hospital systems.

1. Patient receives care in hospital system.
2. Hospital system creates a local clinical event.
3. Hospital system checks whether patient has OpesCare Health ID or mapped patient reference.
4. Hospital system packages the event using API, SDK, widget, or Bridge Agent.
5. The request includes facility ID, external user, event type, external reference, idempotency key, timestamp, and source system.
6. The request is sent to OpesCare API Gateway.
7. API Gateway authenticates the client.
8. Facility status is checked.
9. Endpoint permission is checked.
10. Payload schema is validated.
11. Patient identity is matched by MPI.
12. If patient match is uncertain, the event enters reconciliation queue.
13. If patient match is confirmed, sync engine checks duplicates.
14. Sync engine checks conflicts and source metadata.
15. Event is stored in patient timeline.
16. Source attribution is attached.
17. Audit log is written.
18. Sync response is returned to hospital.
19. Hospital stores OpesCare event ID locally.
20. Sync dashboard updates status.

---

## 69.35 Complete Hospital Pull Flow

This is the corrected complete pull flow for hospital systems.

1. Hospital identifies patient using Health ID, QR code, mapped local ID, or search.
2. Hospital system calls patient search API.
3. OpesCare returns identity confirmation only.
4. Provider requests data scope.
5. Request includes facility, provider, purpose of use, requested scope, and patient reference.
6. Consent engine checks active consent.
7. If consent is missing, consent request is triggered.
8. Patient grants or denies consent.
9. Access control engine checks role and facility permission.
10. Patient record service retrieves only permitted data.
11. Response is filtered by scope.
12. Hospital system displays data.
13. Audit log records access.
14. Sync dashboard updates pull count.

---

## 69.36 Complete Widget-Based Flow

1. Hospital system requests widget session token from OpesCare.
2. OpesCare validates hospital backend credentials.
3. OpesCare returns short-lived widget token.
4. Hospital frontend loads OpesCare widget.
5. User searches patient inside widget.
6. Widget requests consent if needed.
7. Widget displays approved summary.
8. User selects data to push or pull.
9. Widget sends operation to OpesCare.
10. OpesCare validates and processes request.
11. Widget displays sync result.
12. Audit log records action.

---

## 69.37 Complete Bridge Agent Flow

1. Hospital legacy system exports data or stores data in local database.
2. Bridge Agent detects new data.
3. Bridge Agent maps local fields to OpesCare data contract.
4. Bridge Agent validates required fields.
5. Bridge Agent queues events locally.
6. If internet is available, Bridge Agent sends events to OpesCare.
7. If internet is unavailable, Bridge Agent keeps encrypted queue.
8. OpesCare processes events through API Gateway.
9. Sync responses return to Bridge Agent.
10. Bridge Agent updates local sync status.
11. Failed records appear in Bridge dashboard.
12. Facility staff or integration support resolves failures.

---

## 69.38 Complete SDK-Based Flow

1. Third-party developer installs OpesCare SDK.
2. Developer initializes SDK with facility credentials.
3. SDK authenticates and receives token.
4. Hospital app calls SDK function such as `searchPatient()`.
5. SDK sends request to OpesCare API.
6. SDK handles token refresh, retries, and errors.
7. Hospital app receives structured response.
8. For push operations, SDK generates or accepts idempotency key.
9. SDK sends clinical event.
10. SDK returns sync status.
11. Hospital app stores OpesCare record ID.

---

## 69.39 Integration Governance

OpesCare integrations must be governed.

Governance rules:

- every facility must be verified before production access
- every integration must have an owner
- API credentials must be tied to a facility or organization
- integrations must pass sandbox tests
- high-volume integrations must be monitored
- suspicious integrations can be suspended
- data-sharing agreements must be signed
- access logs must be available for review

---

## 69.40 Recommended Product Naming

The integration product family should be named clearly.

Recommended names:

- **OpesCare Connect API**: direct API integration
- **OpesCare Connect SDK**: developer toolkit
- **OpesCare Connect Widget**: embeddable hospital interface
- **OpesCare Bridge Agent**: legacy system connector
- **OpesCare Lite**: direct portal for facilities without software
- **OpesCare Connect Dashboard**: integration monitoring dashboard

The complete suite is:

**OpesCare Connect Suite**

---

## 69.41 Final Integration Positioning

OpesCare must be built as a platform that can communicate with many hospital systems, not as a closed application.

For Opesware-native systems, OpesCare should provide direct actions such as:

- Pull from OpesCare
- Push to OpesCare
- Sync with OpesCare

For third-party systems, OpesCare should provide:

- API
- SDK
- widget
- webhooks
- sandbox
- certification

For old systems, OpesCare should provide:

- Bridge Agent
- file import
- local queue
- mapping tools

For hospitals without software, OpesCare should provide:

- OpesCare Lite Portal

This model makes OpesCare flexible enough to work across hospitals with different software, different workflows, and different infrastructure levels.

The final architecture is:

```text
Any Hospital System
     ↓
OpesCare Connect Suite
     ↓
OpesCare API Gateway
     ↓
Consent + Access Control + Validation
     ↓
MPI + Synchronization Engine
     ↓
Patient Timeline
```

This is how OpesCare becomes not just a patient record platform, but the interoperability backbone for digital healthcare.



---

# 70. OpesCare Full Product Requirements Document — Build From Scratch

## 70.1 Document Status

This PRD is the consolidated build document for OpesCare.

OpesCare will be built from scratch.

The project will not use OpesHIS OS.

The project will not be a fork, rewrite, patch, or continuation of OpesHIS OS.

OpesHIS OS may be treated only as past reference context, but no code, database structure, module structure, or architecture from OpesHIS OS should be assumed as the foundation for OpesCare.

This PRD defines OpesCare as a new Laravel-based modular monolith with selected Python services for specialist intelligence modules only.

---

## 70.2 Product Name

Product name: OpesCare

Domain: opescare.com

Parent company: Opesware

Primary product category: Digital Health ID, longitudinal patient record, and healthcare interoperability platform.

Core promise: One patient. One Health ID. One trusted medical history across hospitals, clinics, labs, pharmacies, insurers, and approved public health systems.

---

## 70.3 Product Vision

OpesCare is a digital health identity and interoperability platform that replaces fragmented paper hospital books and isolated hospital records with one secure patient-centered Health ID.

The patient owns one persistent OpesCare Health ID. Hospitals and clinics can create records for the patient, and with proper authorization, other healthcare providers can retrieve approved parts of the patient’s medical history.

OpesCare is not only a hospital management system. It is a healthcare identity, record continuity, consent, and integration platform.

The system must allow:

- patients to carry their medical identity everywhere
- hospitals to push records to the patient’s OpesCare timeline
- hospitals to pull approved records from the patient’s OpesCare timeline
- doctors to avoid treating patients blindly
- labs and pharmacies to work from verified orders and prescriptions
- insurers to validate eligibility and claims without receiving unnecessary clinical history
- public health bodies to receive controlled aggregate or reportable data
- future AI and analytics services to work on structured, governed, traceable data

---

## 70.4 Main Technology Decision

OpesCare Core will be built in Laravel.

Database: PostgreSQL

Queue and cache: Redis

Frontend: Laravel Blade, Inertia, Vue, or React depending on final implementation decision. The system must remain API-first.

Python services: only for specialist intelligence functions such as duplicate matching, OCR, AI summaries, analytics, public health intelligence, anomaly detection, and future clinical decision-support assistance.

Initial architecture: modular Laravel monolith.

Future architecture: extract services only when the product has proven scale, clear service boundaries, and operational need.

---

## 70.5 Architecture Principles

OpesCare must follow these architecture principles:

1. Build from scratch.
2. Use Laravel as the transactional source-of-truth platform.
3. Use PostgreSQL as the canonical database.
4. Use Redis for queues, cache, session acceleration, throttling, webhook retries, and async processing.
5. Do not store canonical clinical records in Redis.
6. Use UUID or ULID internal IDs.
7. Do not expose database IDs as patient-facing Health IDs.
8. Use a modular monolith first.
9. Use API-first design.
10. Use OpenAPI for external API contracts.
11. Align interoperability semantics with FHIR R4 where practical.
12. Do not try to build a full FHIR server in MVP.
13. Separate clinical events, audit logs, provenance, billing, and operational tasks.
14. All high-risk operations must be audited.
15. All externally pushed records must carry provenance.
16. All external writes must be idempotent.
17. All uncertain patient matching must go to reconciliation.
18. No probabilistic auto-merge of patients.
19. Released clinical records must be amended, not silently overwritten.
20. No AI-generated code should bypass security, consent, audit, or identity controls.

---

## 70.6 Target Users

### Patient

The patient uses OpesCare to access their Health ID, view approved parts of their medical history, grant or revoke consent, see who accessed their record, receive reminders, and carry their health identity across facilities.

### Guardian or Dependent Manager

A guardian manages consent and access for minors, dependents, elderly relatives, or patients legally unable to manage their own records.

### Receptionist or Registrar

The receptionist registers patients, searches Health IDs, verifies identity, checks in patients, creates visits, and routes patients to departments.

### Nurse

The nurse performs triage, captures vitals, records nursing notes, supports inpatient care, administers medication, and handles handovers.

### Doctor or Clinician

The doctor reviews approved patient history, documents consultation, creates diagnoses, orders labs or imaging, prescribes medication, creates referrals, admits patients, and writes discharge summaries.

### Lab Staff

Lab staff receive orders, collect samples, label specimens, enter results, validate results, amend released results where necessary, and publish results to the patient timeline.

### Pharmacist

The pharmacist verifies prescriptions, checks status, dispenses medication, manages partial dispensing, blocks expired/cancelled prescriptions, and links dispensing to stock batches.

### Cashier and Billing Officer

The cashier handles invoices, payments, receipts, refunds, reversals, cashier sessions, and reconciliation.

### Insurance Officer

The insurance officer verifies coverage, submits claims, handles denials, resubmissions, and payment tracking.

### Facility Administrator

The facility administrator manages branches, departments, staff, roles, schedules, pricing, services, and facility-level configuration.

### OpesCare Administrator

The OpesCare administrator manages facilities, integration clients, country configuration, security controls, audit reviews, and platform governance.

### Integration Developer

The integration developer connects third-party HIS, LIS, pharmacy, insurer, or legacy systems to OpesCare through API, SDK, widget, bridge agent, or file import.

### Public Health or Research User

These users access approved, controlled, minimized, aggregated, or ethics-approved datasets depending on governance rules.

---

## 70.7 Core Problem Statement

Healthcare records are fragmented across paper books, hospitals, labs, pharmacies, and insurers. Patients often lose records. Doctors often treat patients without allergy, medication, chronic disease, or recent lab history. Hospitals may use different systems that do not communicate. Insurance and public health reporting are often disconnected from clinical events.

OpesCare solves this by creating a secure patient-centered Health ID and interoperability backbone that allows approved medical history to follow the patient while preserving consent, privacy, auditability, and source attribution.

---

## 70.8 Product Scope

### In Scope

The full product scope includes:

- patient identity and Health ID
- Master Patient Index
- patient portal
- guardian and dependent management
- facility management
- practitioner management
- staff access control
- consent management
- emergency access
- appointments and queue
- registration and check-in
- triage and vitals
- consultation and clinical notes
- clinical timeline
- allergies and diagnoses
- prescriptions
- pharmacy dispensing
- laboratory orders, samples, results, validation, and amendment
- imaging reports and document references
- billing and payments
- insurance eligibility and claims
- inpatient and ward management
- nursing operations
- referrals
- immunization
- maternal and child health
- chronic disease management
- forms and questionnaire engine
- terminology and coding services
- legacy record uploads
- data migration
- reconciliation workbench
- hospital integration gateway
- API gateway
- SDKs
- embedded widget
- bridge agent
- webhooks
- sync engine
- notifications
- offline and downtime mode
- public health reporting
- research access controls
- security and privacy controls
- audit and compliance
- country and regulatory configuration
- administration console
- governance and oversight

### Out of Scope for MVP

The MVP should not attempt to build everything at once.

The following are not mandatory for the first MVP:

- full inpatient management
- full telemedicine
- advanced AI clinical decision support
- national public health integration
- full research data platform
- advanced remote monitoring
- full insurer integrations
- automated machine-learning disease prediction
- full FHIR server behavior
- national biometric identity integration
- hardware Health ID cards with NFC

These can be added after the foundation is stable.

---

## 70.9 MVP Definition

The MVP must prove the core value of OpesCare:

A patient can be registered, receive a Health ID, receive care at one facility, and later another authorized facility can pull approved medical history and push a new record back to the patient timeline.

The MVP must include:

1. Laravel modular project scaffold
2. PostgreSQL database
3. Redis queue/cache foundation
4. authentication and staff login
5. facility management
6. practitioner management
7. role and permission system
8. audit logging foundation
9. patient registration
10. Health ID generation
11. QR code generation
12. patient search
13. provisional and verified patient statuses
14. basic Master Patient Index candidate detection
15. guardian and dependent basics
16. consent request, grant, expiry, and revocation
17. emergency summary access
18. unknown emergency patient temporary profile
19. reconciliation queue skeleton
20. check-in and visit creation
21. triage and vitals
22. consultation note
23. allergies and diagnoses
24. prescription issuance
25. lab result upload with validation status
26. clinical timeline
27. basic billing
28. basic patient portal
29. basic provider portal
30. OpesCare Connect API MVP
31. push encounter API
32. pull patient summary API
33. webhook skeleton
34. sync status dashboard
35. OpenAPI contract
36. tests for critical safety rules

---

## 70.10 Non-Negotiable Safety Rules

The system must never:

- create a permanent patient outside the Patient Identity Service
- merge patients automatically based only on probability
- attach pushed records to uncertain patient matches
- delete audit logs
- silently overwrite released lab results
- silently overwrite signed clinical notes
- dispense cancelled or expired prescriptions
- dispense recalled or expired medication batches
- expose full patient records without proper consent and purpose
- expose clinical content in webhook payloads by default
- expose sensitive diagnosis details in SMS notifications
- allow staff to share one user account
- allow suspended facilities to push or pull data
- allow expired integration tokens to access data
- allow patient records to be exported without audit
- store production patient data in GitHub
- commit `.env`, API keys, database dumps, logs, tokens, or credentials
- let AI agents merge code directly into main

---

## 70.11 Repository and Delivery Requirements

The project should be hosted on GitHub as a new repository.

Recommended repository name:

`opescare-platform`

Recommended structure:

```text
opescare-platform/
├── apps/
│   ├── api-laravel/
│   ├── web-portal/
│   └── patient-portal/
├── services/
│   └── ai-python/
├── contracts/
│   └── openapi/
├── sdk/
│   ├── php/
│   ├── typescript/
│   └── python/
├── infra/
├── docs/
│   ├── adr/
│   ├── product/
│   ├── security/
│   ├── integration/
│   └── runbooks/
└── .github/
    ├── workflows/
    ├── ISSUE_TEMPLATE/
    ├── PULL_REQUEST_TEMPLATE.md
    └── copilot-instructions.md
```

Main branch must remain stable.

All work must happen through feature branches and pull requests.

Every pull request must include:

- summary
- module affected
- database changes
- API changes
- security impact
- audit impact
- tests added
- AI agent used
- screenshots where UI is involved
- known risks
- rollback notes where applicable

---

## 70.12 System-Wide Data Model Principles

All important records must include:

- internal UUID/ULID
- created_at
- updated_at
- created_by
- updated_by where applicable
- facility_id where applicable
- patient_id where applicable
- encounter_id where applicable
- source_system where applicable
- external_reference where applicable
- verification_status where applicable
- sensitivity_level where applicable
- audit_event_id where applicable

Clinical records must be treated as events, not one giant editable patient document.

The patient timeline aggregates events.

Events must preserve provenance.

---

# 71. Module-by-Module PRD With Detailed Flows

---

## 71.1 Patient Identity Service

### Purpose

The Patient Identity Service is the only module allowed to create permanent patient profiles. It generates the internal patient record and visible OpesCare Health ID.

### MVP Requirements

- create patient profile
- generate Health ID
- support provisional and verified statuses
- support demographic correction
- support deceased status
- support patient photo later
- support patient identifiers
- prevent duplicate creation

### Key Entities

- Patient
- PatientIdentifier
- IdentityVerification
- DemographicSnapshot
- HealthId
- DeceasedStatus

### Flow: New Patient Registration

1. Registrar opens registration screen.
2. Registrar enters patient demographics.
3. System validates required fields.
4. System sends details to MPI candidate search.
5. MPI returns exact match, possible match, or no match.
6. If exact match exists, system blocks new permanent patient and suggests existing patient.
7. If possible match exists, system displays review candidates.
8. Registrar either confirms existing patient, routes to review, or creates provisional patient if allowed.
9. If no match exists, Patient Identity Service creates patient.
10. System generates internal UUID/ULID.
11. System generates visible Health ID.
12. System creates first patient identifier record.
13. System logs audit event.
14. System displays QR code and registration summary.

### Flow: Patient Verification

1. Authorized staff opens patient verification panel.
2. Staff selects verification method.
3. Staff uploads or enters verification evidence.
4. System validates evidence requirements.
5. Staff confirms verification.
6. Patient status changes from provisional to verified.
7. System records verification source, verifier, timestamp, and facility.
8. Audit event is created.

### Failure and Bug Controls

- do not create patient without MPI check
- do not allow duplicate Health ID
- do not allow permanent patient creation from migration, API, emergency, or registration without identity service
- do not silently overwrite demographics
- demographic corrections must preserve old values
- deceased patient status must block future appointments and prescriptions

### Acceptance Criteria

- patient can be created only through Patient Identity Service
- Health ID is unique
- duplicate candidate warning appears before creation
- provisional status is visible
- verification changes are audited
- demographic corrections preserve history

---

## 71.2 Master Patient Index

### Purpose

The MPI detects whether multiple records may belong to the same person.

### MVP Requirements

- exact Health ID lookup
- exact identifier matching
- basic demographic match scoring
- possible duplicate queue
- merge case creation
- unmerge capability design

### Key Entities

- MpiCandidate
- MatchScore
- MergeCase
- UnmergeCase
- PatientAlias

### Flow: Duplicate Candidate Detection

1. New patient data enters MPI.
2. MPI checks exact Health ID if provided.
3. MPI checks phone, date of birth, sex, names, guardian, and other identifiers.
4. MPI scores possible matches.
5. If exact verified identifier exists, system returns exact match.
6. If match is probable but not exact, system returns possible candidates.
7. If no useful match exists, system returns no match.
8. Candidate result is attached to registration workflow.

### Flow: Merge Review

1. Reviewer opens duplicate case.
2. System displays both patient profiles side-by-side.
3. Reviewer checks demographics, identifiers, facility visits, and records.
4. Reviewer selects primary patient profile.
5. System previews merge impact.
6. Reviewer confirms merge with reason.
7. System links records under primary profile.
8. Secondary profile is marked merged, not deleted.
9. Provenance and audit records are created.

### Failure and Bug Controls

- no probabilistic auto-merge
- all merges must be reversible
- merged profiles must not be deleted
- conflicting data must be flagged
- twin/newborn confusion must be handled carefully

### Acceptance Criteria

- exact match blocks duplicate creation
- possible matches require human review
- merge requires reason
- unmerge path exists in design
- audit logs show who merged and why

---

## 71.3 Family, Guardian, and Dependent Management

### Purpose

This module manages minors, dependents, guardians, caregivers, and delegated access.

### MVP Requirements

- link guardian to patient
- set relationship type
- set legal basis
- set expiry/effective dates
- allow guardian consent for minors
- handle transition when minor becomes adult based on country policy

### Key Entities

- GuardianLink
- RelatedPerson
- DelegatedAccess
- LegalBasis
- MajorityTransition

### Flow: Add Guardian

1. Registrar opens patient family panel.
2. Registrar selects add guardian.
3. System searches whether guardian already exists as patient or related person.
4. Registrar enters guardian name, phone, relationship, legal basis, and proof where required.
5. System validates required fields.
6. Registrar sets effective date and expiry if applicable.
7. System creates guardian link.
8. Guardian receives notification where possible.
9. Audit event is logged.

### Flow: Guardian Gives Consent

1. Provider requests access to minor patient record.
2. Consent engine checks whether patient requires guardian consent.
3. System finds active verified guardian link.
4. Guardian receives consent request.
5. Guardian approves or denies.
6. Consent grant is created with guardian as authorizing actor.
7. Audit event records guardian relationship and legal basis.

### Failure and Bug Controls

- expired guardian links cannot approve consent
- informal caregiver must not get full legal guardian rights by default
- adult transition must trigger review
- guardian access must be scoped

### Acceptance Criteria

- guardian links have effective dates
- guardian consent is audited
- expired guardian cannot approve
- adult transition flag is generated

---

## 71.4 Facility Management

### Purpose

This module manages healthcare facilities, branches, departments, licenses, and operational status.

### MVP Requirements

- create facility
- create branches
- configure departments
- set facility status
- upload facility license documents
- suspend facility
- block suspended facility from API access

### Key Entities

- Facility
- Branch
- Department
- License
- FacilityService
- FacilityStatus

### Flow: Facility Onboarding

1. OpesCare admin opens facility onboarding form.
2. Admin enters facility name, ownership, type, location, contact, and service categories.
3. Admin uploads license documents.
4. System creates facility in pending status.
5. Compliance reviewer verifies documents.
6. Facility status changes to active.
7. Departments and users can be configured.
8. API credentials may be created after activation.
9. Audit event is logged.

### Flow: Facility Suspension

1. Admin opens facility profile.
2. Admin selects suspend.
3. System requests reason and suspension type.
4. If high-risk, second approval is required.
5. Facility status changes to suspended.
6. API keys are disabled.
7. Facility users are blocked from new sensitive access.
8. Ongoing patient-care exceptions are handled by policy.
9. Audit event is logged.

### Failure and Bug Controls

- suspended facilities cannot push or pull data
- inactive licenses should create alerts
- API credentials must be tied to facility
- facility deletion should be disabled if records exist

### Acceptance Criteria

- facility can be activated only after required data
- suspended facility API access is rejected
- facility status changes are audited

---

## 71.5 Practitioner and Staff Management

### Purpose

This module manages staff accounts, practitioners, roles, assignments, credentials, and offboarding.

### MVP Requirements

- create staff user
- assign role
- assign facility and department
- manage practitioner credentials
- suspend/offboard staff
- revoke active sessions

### Key Entities

- UserAccount
- Practitioner
- PractitionerAssignment
- Credential
- Role
- Permission
- StaffSession

### Flow: Staff Onboarding

1. Facility admin opens staff creation form.
2. Admin enters staff details.
3. Admin selects role, department, and facility scope.
4. If clinical staff, credential details are entered.
5. System validates uniqueness and required fields.
6. User account is created.
7. Role permissions are assigned.
8. Staff receives invitation.
9. Audit event is logged.

### Flow: Staff Offboarding

1. Admin opens staff profile.
2. Admin selects suspend, deactivate, or remove facility access.
3. System asks for reason.
4. Active sessions are revoked.
5. API tokens belonging to staff are revoked.
6. Future assignments are ended.
7. Audit event is logged.

### Failure and Bug Controls

- no shared accounts
- inactive staff cannot access records
- expired clinical credentials must generate warning or block based on policy
- role elevation requires audit

### Acceptance Criteria

- staff cannot act outside assigned facility scope
- offboarded user cannot log in
- session revocation works
- role changes are audited

---

## 71.6 Authentication and Access Control

### Purpose

This module controls login, token issuance, role-based access, attribute-based access, and API access.

### MVP Requirements

- staff login
- patient login later or basic portal auth
- role and permission matrix
- facility scoping
- API tokens for integration clients
- MFA-ready architecture
- session management

### Key Entities

- AuthSession
- Role
- Permission
- PolicyRule
- ApiClient
- TokenScope

### Flow: Staff Login

1. Staff enters credentials.
2. System validates credentials.
3. System checks account status.
4. System checks facility assignment.
5. MFA is requested where enabled.
6. Session is created.
7. Audit event is logged.
8. User lands on role-specific dashboard.

### Flow: Authorization Check

1. User attempts action.
2. System checks authentication.
3. System checks role permission.
4. System checks facility scope.
5. System checks patient consent where relevant.
6. System checks purpose of use where relevant.
7. System allows or rejects action.
8. Rejection is logged for high-risk actions.

### Failure and Bug Controls

- deny by default
- no broad admin access without scope
- every API client must have scopes
- expired tokens rejected
- sensitive roles require MFA later

### Acceptance Criteria

- unauthorized users cannot view patient records
- facility scoping works
- token scopes are enforced
- authorization denials return clear error

---

## 71.7 Consent Management

### Purpose

Consent controls who can access what data, for what purpose, and for how long.

### MVP Requirements

- request consent
- approve consent
- deny consent
- revoke consent
- consent expiry
- guardian consent
- emergency exception
- consent audit trail

### Key Entities

- ConsentRequest
- ConsentGrant
- ConsentScope
- ConsentRevocation
- ConsentToken

### Flow: Request Consent

1. Provider requests patient record access.
2. Provider selects purpose of use.
3. Provider selects requested scope.
4. System checks whether existing consent is valid.
5. If not, system creates consent request.
6. Patient or guardian receives request through available method.
7. Patient approves or denies.
8. If approved, consent grant is created with expiry.
9. Access control engine applies grant.
10. Audit event is logged.

### Flow: Revoke Consent

1. Patient opens consent center.
2. Patient selects active consent grant.
3. System displays consequences.
4. Patient confirms revocation.
5. Consent grant is marked revoked.
6. Future access is blocked.
7. Provider may be notified depending on policy.
8. Audit event is logged.

### Failure and Bug Controls

- consent must have scope, purpose, and expiry
- broad permanent consent should not be default
- revoked consent must block future access
- cached offline records must expire
- consent cannot be faked without patient/guardian verification

### Acceptance Criteria

- data cannot be pulled without consent unless emergency policy applies
- revoked consent blocks future access
- consent scope filters response data
- guardian consent is properly attributed

---

## 71.8 Emergency Access

### Purpose

Emergency access allows limited access to critical information when normal consent cannot be obtained.

### MVP Requirements

- emergency profile view
- reason required
- role restriction
- audit log
- post-event review queue
- unknown emergency patient creation

### Key Entities

- EmergencyAccessEvent
- EmergencyReason
- EmergencyReviewCase
- TemporaryPatient

### Flow: Known Patient Emergency Access

1. Provider identifies patient by Health ID, QR, phone, or search.
2. Provider selects emergency access.
3. System asks for reason.
4. System checks provider role.
5. System displays warning that access is audited.
6. Provider confirms.
7. System displays emergency profile only.
8. Audit event is created.
9. Review case is created for compliance.

### Flow: Unknown Emergency Patient

1. Provider selects create unknown emergency patient.
2. System creates temporary emergency ID.
3. Provider records approximate sex, age, arrival method, and descriptive notes where available.
4. Emergency care is documented under temporary profile.
5. Later, staff initiate identity resolution.
6. MPI searches for existing patient.
7. If confirmed, temporary profile is linked or merged under review.
8. If no match, permanent profile is created through Patient Identity Service.
9. Audit trail preserves temporary identity history.

### Failure and Bug Controls

- emergency access cannot show full record by default
- reason required
- unknown patient cannot silently become permanent
- abuse must appear in review dashboard

### Acceptance Criteria

- emergency profile shows only critical data
- no reason means no emergency access
- emergency events are audited
- unknown patient workflow exists

---

## 71.9 Registration, Check-In, and Visit Management

### Purpose

This module handles front-desk workflows and creates facility visits.

### MVP Requirements

- search existing patient
- create check-in
- select visit type
- route to queue, triage, doctor, lab, pharmacy, billing, or emergency
- create encounter when care starts

### Key Entities

- Visit
- CheckIn
- QueueTicket
- VisitType
- VisitStatus

### Flow: Returning Patient Check-In

1. Receptionist searches by Health ID, QR, name, phone, or local identifier.
2. System displays candidates.
3. Receptionist verifies at least two identifiers.
4. Receptionist selects patient.
5. System checks active consent/facility relationship where needed.
6. Receptionist selects visit type.
7. System creates visit.
8. System creates queue ticket if required.
9. Patient is routed to triage, doctor, lab, pharmacy, billing, or emergency.
10. Audit event is logged.

### Failure and Bug Controls

- do not create duplicate patient just because search spelling fails
- wrong-patient selection must be reduced by identity banner and confirmation
- one active queue ticket per department/service unless transferred

### Acceptance Criteria

- returning patient can be checked in
- patient identity confirmation is required
- queue ticket is created correctly
- check-in is audited

---

## 71.10 Appointment and Queue Module

### Purpose

This module manages scheduled and walk-in patient flow.

### MVP Requirements

- create appointment
- reschedule appointment
- cancel appointment
- check in appointment
- walk-in queue
- queue priority

### Key Entities

- Appointment
- Schedule
- Slot
- QueueTicket
- NoShow

### Flow: Appointment Booking

1. User selects facility, department, provider, service, and date.
2. System loads available slots.
3. User selects slot.
4. System locks slot temporarily.
5. If prepayment required, invoice is created.
6. Appointment is confirmed after policy requirements.
7. Patient receives safe notification.
8. Audit event is logged.

### Flow: Walk-In Queue

1. Receptionist checks in patient.
2. System determines destination queue.
3. Queue number is generated.
4. Triage may update priority.
5. Provider calls patient.
6. Queue ticket changes to in-service.
7. Ticket closes, transfers, or remains pending depending on flow.

### Failure and Bug Controls

- prevent double booking
- release unpaid reserved slot after timeout
- queue priority changes require reason
- cancelled visit must cascade to queue ticket

### Acceptance Criteria

- appointment slot cannot be double-booked
- walk-in queue works
- cancellation updates dependent queue entries

---

## 71.11 Triage and Vitals

### Purpose

Triage captures initial assessment and prioritizes patient care.

### MVP Requirements

- capture complaint
- capture vitals
- capture pain score
- capture pregnancy status where relevant
- detect abnormal values
- update acuity

### Key Entities

- TriageRecord
- VitalSign
- AcuityScore
- ClinicalAlert

### Flow: Triage

1. Nurse opens patient visit.
2. Nurse verifies patient identity banner.
3. Nurse records presenting complaint.
4. Nurse records vitals.
5. System validates units and ranges.
6. System flags abnormal values.
7. Nurse saves triage.
8. Queue priority updates if needed.
9. Provider can view triage before consultation.
10. Audit event is logged.

### Failure and Bug Controls

- impossible values blocked or flagged
- unit mistakes prevented
- triage must link to active visit/encounter
- critical values create alert

### Acceptance Criteria

- vitals can be captured
- abnormal values flagged
- triage appears in consultation view
- audit log created

---

## 71.12 Consultation and Clinical Documentation

### Purpose

This module allows clinicians to document care and create orders.

### MVP Requirements

- consultation note
- diagnosis
- allergy update
- treatment plan
- lab order
- prescription
- referral later
- note sign-off

### Key Entities

- Encounter
- ClinicalNote
- Diagnosis
- AllergyRecord
- CarePlan
- ClinicalOrder

### Flow: Doctor Consultation

1. Doctor opens worklist.
2. Doctor selects patient.
3. Patient identity banner is displayed.
4. System displays triage, allergies, recent summary, and warnings.
5. Doctor documents history and examination.
6. Doctor records diagnosis.
7. Doctor records plan.
8. Doctor orders lab/imaging if needed.
9. Doctor prescribes medication if needed.
10. Doctor signs note or saves as draft.
11. Encounter remains open until required tasks are complete or explicitly pending.
12. Audit event is logged.

### Failure and Bug Controls

- wrong patient banner always visible
- signed note cannot be silently edited
- amendments preserve original
- encounter closure requires required documentation

### Acceptance Criteria

- doctor can document consultation
- diagnoses appear in timeline
- allergies are visible before prescribing
- signed notes are immutable except amendment

---

## 71.13 Clinical Timeline

### Purpose

The clinical timeline displays patient medical events chronologically.

### MVP Requirements

- display encounters
- display allergies
- display diagnoses
- display lab results
- display prescriptions
- display uploaded documents with status
- show source facility and verification status
- filter by type, date, facility

### Key Entities

- TimelineEvent
- EventSource
- VerificationStatus
- SensitivityTag

### Flow: Timeline Rendering

1. User opens patient timeline.
2. System checks authorization and consent.
3. System retrieves permitted events only.
4. Events are sorted chronologically.
5. Each event shows source, type, date, and verification status.
6. Sensitive or unauthorized items are hidden or redacted.
7. Access is audited.

### Failure and Bug Controls

- unverified uploads must be clearly labeled
- patient-submitted documents must not appear as verified clinical facts
- restricted records hidden if unauthorized
- source attribution always visible

### Acceptance Criteria

- timeline shows patient history
- filters work
- source facility is visible
- unauthorized data is not displayed

---

## 71.14 Laboratory Module

### Purpose

The lab module manages lab orders, sample collection, result entry, validation, release, and amendment.

### MVP Requirements

- create lab order
- collect sample
- assign sample ID
- enter result
- validate result
- release result
- amend released result

### Key Entities

- LabOrder
- Specimen
- LabResult
- ResultValidation
- ResultAmendment

### Flow: Lab Order

1. Doctor creates lab order from encounter.
2. Doctor selects test.
3. System checks availability and price if configured.
4. Order is sent to lab queue.
5. Billing may be triggered depending on policy.
6. Audit event is logged.

### Flow: Sample Collection

1. Patient arrives at lab.
2. Lab staff opens pending orders.
3. Staff verifies patient identity.
4. Staff collects sample.
5. System generates sample ID or barcode.
6. Staff labels sample.
7. Status becomes collected.
8. Audit event is logged.

### Flow: Result Validation and Release

1. Lab technician enters result.
2. System validates required fields, units, and reference ranges.
3. Result status becomes entered pending validation.
4. Qualified validator reviews result.
5. Validator approves or rejects.
6. Approved result is released.
7. Provider is notified.
8. Timeline is updated.
9. Audit event is logged.

### Flow: Result Amendment

1. Lab identifies released result error.
2. Supervisor opens amendment request.
3. Supervisor enters reason.
4. Original result remains preserved.
5. Corrected result is entered.
6. Corrected result is validated.
7. Result status becomes amended.
8. Provider is notified.
9. Timeline shows amended status.
10. Audit event is logged.

### Failure and Bug Controls

- released result cannot be overwritten
- sample mismatch must stop process
- unvalidated result cannot be released except configured rapid-test exception
- critical result must notify provider

### Acceptance Criteria

- order-to-result workflow works
- sample ID is linked to patient/order
- validation required before release
- amendment preserves original

---

## 71.15 Prescription and Pharmacy Module

### Purpose

This module manages prescriptions and medication dispensing.

### MVP Requirements

- create prescription
- check allergies
- issue prescription
- cancel prescription
- dispense full or partial quantity
- block expired/cancelled prescription
- link dispense to stock batch when inventory enabled

### Key Entities

- MedicationRequest
- PrescriptionItem
- DispenseEvent
- PrescriptionStatus
- MedicationWarning

### Flow: Prescription Creation

1. Doctor opens encounter.
2. Doctor selects medication.
3. Doctor enters dose, route, frequency, duration, and instructions.
4. System checks allergies.
5. System checks duplicate medication.
6. System checks drug interactions where available.
7. Doctor reviews warnings.
8. Doctor confirms prescription.
9. Prescription status becomes issued.
10. Audit event is logged.

### Flow: Pharmacy Dispensing

1. Patient presents at pharmacy.
2. Pharmacist searches by Health ID or prescription ID.
3. Pharmacist verifies patient identity.
4. System displays active prescriptions.
5. Pharmacist selects prescription.
6. System checks status, expiry, and prior dispenses.
7. System checks stock if inventory is active.
8. Pharmacist dispenses full or partial quantity.
9. Dispense event is recorded.
10. Inventory is deducted where applicable.
11. Timeline updates.
12. Audit event is logged.

### Flow: Prescription Cancellation

1. Authorized prescriber opens prescription.
2. Prescriber selects cancel.
3. System checks dispensing status.
4. If not dispensed, prescription becomes cancelled.
5. If partially dispensed, only remaining balance is cancelled.
6. If fully dispensed, cancellation is blocked and annotation is allowed.
7. Audit event is logged.

### Failure and Bug Controls

- cancelled prescription cannot be dispensed
- expired prescription cannot be dispensed
- fully dispensed prescription cannot be deleted
- allergy warnings must show before issue

### Acceptance Criteria

- prescription can be issued
- allergy warning appears
- partial dispensing works
- cancellation rules work
- dispense event appears in timeline

---

## 71.16 Billing, Payments, and Cashier

### Purpose

This module manages service charges, invoices, payments, receipts, refunds, reversals, and cashier reconciliation.

### MVP Requirements

- create charges
- generate invoice
- record payment
- issue receipt
- reverse payment
- cashier session
- basic outstanding balance

### Key Entities

- AccountLedger
- ChargeEvent
- Invoice
- Payment
- PaymentReversal
- CashierSession

### Flow: Billing and Payment

1. Billable service is created.
2. Billing engine checks price list.
3. Charge is created.
4. Invoice is generated.
5. Patient pays by supported method.
6. Cashier records payment.
7. Receipt is generated.
8. Invoice status updates.
9. Audit event is logged.

### Flow: Payment Reversal

1. Cashier or supervisor opens payment.
2. User selects reverse.
3. System requests reason.
4. If amount exceeds threshold, approval is required.
5. System creates reversing transaction.
6. Original payment remains visible.
7. Invoice balance updates.
8. Audit event is logged.

### Failure and Bug Controls

- payments cannot be deleted
- reversal must not delete original payment
- emergency care must not be blocked by billing where policy allows care first
- cashier session must reconcile cash

### Acceptance Criteria

- invoice and payment workflow works
- receipt generated
- reversal creates linked reversing transaction
- cashier session can close with totals

---

## 71.17 Insurance and Claims

### Purpose

This module manages insurance eligibility, coverage, claims, claim responses, denials, approvals, and resubmissions.

### MVP Requirements

- record insurance coverage
- verify eligibility manually or through API later
- create claim
- submit claim
- record insurer response
- handle denial/resubmission

### Key Entities

- CoverageRecord
- EligibilityCase
- ClaimCase
- ClaimResponse
- PreAuthorization

### Flow: Eligibility Verification

1. Staff opens patient insurance section.
2. Staff selects insurer and policy.
3. System checks stored policy status.
4. If API exists, system queries insurer.
5. Eligibility response is recorded.
6. Billing rules are updated.
7. Audit event is logged.

### Flow: Claim Submission

1. Billing officer opens claim module.
2. System gathers encounter, charges, diagnosis, services, prescriptions, and supporting documents.
3. Officer reviews claim.
4. Claim is submitted.
5. Insurer response is recorded as received, queried, approved, partially approved, denied, or rejected.
6. Claim can be resubmitted with versioning.
7. Audit event is logged.

### Failure and Bug Controls

- claim submissions must be idempotent
- resubmission must link to original claim
- insurer should not receive full patient history by default
- eligibility is separate from claim adjudication

### Acceptance Criteria

- insurance coverage can be added
- eligibility can be recorded
- claim can be created and tracked
- denial/resubmission states exist

---

## 71.18 Inventory and Medical Stock

### Purpose

Inventory tracks medicines, reagents, consumables, batches, expiry, recalls, transfers, and stock movements.

### MVP Requirements

- stock item creation
- batch stock-in
- batch expiry
- stock movement
- stock adjustment
- recall status
- block expired/recalled batch

### Key Entities

- StockItem
- Batch
- StockMovement
- StockAdjustment
- RecallNotice
- Supplier

### Flow: Stock-In

1. Inventory officer receives stock.
2. Officer opens stock-in screen.
3. Officer selects item.
4. Officer enters batch number, quantity, expiry date, supplier, and cost.
5. System validates required fields.
6. Stock quantity increases.
7. Batch status becomes active or near-expiry based on rules.
8. Audit event is logged.

### Flow: Stock-Out or Adjustment

1. Staff selects item and batch.
2. Staff selects reason.
3. Staff enters quantity.
4. System checks available quantity.
5. High-risk adjustments require approval.
6. Stock movement is recorded.
7. Quantity updates.
8. Audit event is logged.

### Flow: Batch Recall

1. Authorized user creates recall notice.
2. User selects affected batch.
3. Batch status becomes recalled.
4. System blocks future dispensing/use.
5. System identifies patients who received batch where possible.
6. Audit event is logged.

### Failure and Bug Controls

- negative stock blocked unless special approved policy
- expired/recalled batch cannot be dispensed
- all adjustments require reason
- stock movement must be traceable

### Acceptance Criteria

- batch stock-in works
- stock movement ledger is visible
- recalled batch is blocked
- expired batch is blocked

---

## 71.19 Inpatient, Ward, and Nursing

### Purpose

This module handles admission, beds, ward transfers, inpatient daily care, nursing notes, medication administration, and discharge.

### MVP Status

Not required in first MVP except architecture placeholders.

### Key Entities

- Admission
- Bed
- Ward
- BedAssignment
- WardTransfer
- NursingNote
- MedicationAdministration
- DischargeSummary

### Flow: Admission

1. Doctor decides patient requires admission.
2. Doctor creates admission order.
3. System checks bed availability.
4. Admission officer assigns ward and bed.
5. Patient status becomes admitted.
6. Ward nurse receives notification.
7. Billing deposit may be triggered depending on policy.
8. Audit event is logged.

### Flow: Ward Transfer

1. Staff initiates transfer.
2. Destination ward/bed is selected.
3. System checks availability.
4. Current ward prepares transfer note.
5. Destination ward accepts patient.
6. Bed assignment updates.
7. Old bed changes to cleaning or available.
8. Audit event is logged.

### Flow: Medication Administration

1. Nurse opens MAR.
2. Nurse sees due medication.
3. Nurse verifies patient.
4. Nurse administers or records omission.
5. System records dose status.
6. Stock deduction happens based on facility policy.
7. Audit event is logged.

### Flow: Discharge

1. Doctor prepares discharge summary.
2. Pharmacy prepares discharge medications.
3. Billing finalizes account.
4. Nurse completes discharge checklist.
5. Patient is marked discharged.
6. Bed becomes cleaning or available.
7. Timeline updates.
8. Audit event is logged.

### Failure and Bug Controls

- one active patient per bed
- admission must link to originating encounter
- medication administration must link to order
- discharge summary required before clinical discharge closure

### Acceptance Criteria

- admission workflow can be added later without changing patient identity model
- bed assignment prevents double occupancy
- medication administration has dose-level status

---

## 71.20 Referral Network

### Purpose

Referral module manages transfer of patient care between providers or facilities.

### MVP Requirements

- create referral
- attach scoped referral package
- referral acceptance
- referral expiry
- referral feedback

### Key Entities

- ReferralCase
- ReferralPackage
- ReferralAccessGrant
- ReferralStatus

### Flow: Create Referral

1. Doctor opens encounter.
2. Doctor selects create referral.
3. Doctor selects target facility/provider/specialty.
4. Doctor enters reason and urgency.
5. Doctor selects records to include.
6. Patient consent is captured if required.
7. Referral package is created with expiry.
8. Receiving facility is notified.
9. Audit event is logged.

### Flow: Accept Referral

1. Receiving facility opens referral inbox.
2. Authorized user reviews referral package.
3. Facility accepts, rejects, or requests more information.
4. If accepted, appointment or visit is created.
5. After care, feedback is sent to referring provider.
6. Referral is closed.
7. Audit event is logged.

### Failure and Bug Controls

- referral access must expire
- only selected records are shared
- referral package must not expose full record by default
- receiving facility access is scoped

### Acceptance Criteria

- referral can be created
- referral package is scoped
- receiving facility can accept
- expired referral access is blocked

---

## 71.21 Forms and Questionnaire Engine

### Purpose

This module allows OpesCare to define structured clinical forms without hard-coding every form.

### MVP Requirements

- create form template
- version form template
- capture form responses
- link response to patient and encounter
- mark sensitive forms
- extract structured values later

### Key Entities

- FormTemplate
- FormVersion
- FormQuestion
- FormResponse
- ExtractionMap

### Flow: Create Form Template

1. Admin opens form builder.
2. Admin creates form name, category, and sensitivity level.
3. Admin adds questions and validation rules.
4. Admin saves draft.
5. Admin publishes version.
6. Published version becomes available to selected roles.
7. Audit event is logged.

### Flow: Capture Form Response

1. Staff opens patient encounter.
2. Staff selects available form.
3. System loads active version.
4. Staff completes answers.
5. System validates required fields.
6. Response is saved and linked to encounter.
7. Timeline updates where appropriate.
8. Audit event is logged.

### Failure and Bug Controls

- published form versions cannot be silently changed
- sensitive forms require special access
- response must reference exact form version
- patient-submitted form must be marked as such

### Acceptance Criteria

- form builder exists
- responses save correctly
- versioning works
- sensitive forms are access-controlled

---

## 71.22 Terminology and Coding Services

### Purpose

This module manages clinical codes, local mappings, diagnoses, medications, labs, and value sets.

### MVP Requirements

- maintain code systems
- search codes
- validate code
- map local terms to standard terms
- mark inactive codes

### Key Entities

- CodeSystem
- ValueSet
- CodeConcept
- CodeMap
- TermVersion

### Flow: Code Selection

1. User enters diagnosis, test, or medication search.
2. System searches terminology table.
3. User selects correct concept.
4. System stores code, system, display, and version.
5. Record is saved with terminology metadata.
6. Audit event is logged where required.

### Flow: Local Code Mapping

1. Integration sends local code.
2. Terminology service checks mapping table.
3. If mapped, standard code is attached.
4. If unmapped, record is accepted with local code only or sent to reconciliation depending on policy.
5. Mapping review case may be created.
6. Audit event is logged.

### Failure and Bug Controls

- do not store unversioned codes where version matters
- unmapped codes must be visible
- inactive codes should warn or block depending on context

### Acceptance Criteria

- terminology search works
- code metadata is stored
- unmapped integration codes are flagged

---

## 71.23 Legacy Record Upload and Digitization

### Purpose

This module allows old paper hospital books, lab reports, prescriptions, and documents to be uploaded or scanned.

### MVP Requirements

- upload document
- classify document type
- link to patient
- mark source
- mark verification status
- support review queue

### Key Entities

- DocumentReference
- BinaryAsset
- LegacyRecord
- VerificationStatus
- OcrResult later

### Flow: Upload Legacy Record

1. User opens upload screen.
2. User selects patient.
3. User uploads file or scan.
4. User selects document type, date, source, and description.
5. System scans file for allowed format and security.
6. Document is saved as uploaded/unverified.
7. Reviewer may verify document.
8. Verified extracted information may be promoted to timeline.
9. Audit event is logged.

### Failure and Bug Controls

- unverified documents not treated as clinical truth
- file malware scanning required before rendering
- document must show source and trust status
- wrong-patient upload must be correctable through amendment/reassignment workflow

### Acceptance Criteria

- document upload works
- unverified status visible
- verified status requires reviewer
- timeline distinguishes documents from structured records

---

## 71.24 Reconciliation and Data Integrity Workbench

### Purpose

This module handles records that cannot be safely attached, merged, imported, or synchronized.

### MVP Requirements

- show reconciliation cases
- classify reason
- assign reviewer
- resolve case
- reject case
- attach to patient
- create provisional patient where allowed
- audit resolution

### Key Entities

- ReconciliationCase
- SyncConflict
- IdempotencyKey
- CorrectionRequest
- MergeReview

### Flow: Reconciliation Case Intake

1. A pushed record, migration row, duplicate candidate, or correction request fails safe processing.
2. System creates reconciliation case.
3. Case includes reason, source, payload, patient candidates, and risk level.
4. Reviewer opens case.
5. Reviewer selects resolution.
6. System applies resolution through correct service.
7. Audit event is logged.

### Failure and Bug Controls

- uncertain patient record must not be silently attached
- rejected records must retain reason
- same idempotency key with different payload returns conflict
- reviewer cannot bypass identity service

### Acceptance Criteria

- reconciliation cases are created
- reviewer can resolve safely
- all resolutions are audited
- unresolved records do not appear in patient timeline

---

## 71.25 OpesCare Connect API and Integration Gateway

### Purpose

This module enables hospitals, labs, pharmacies, insurers, and third-party systems to push and pull patient data.

### MVP Requirements

- API authentication
- facility verification
- patient search API
- consent API
- pull summary API
- push encounter API
- push lab result API
- push prescription API
- sync status API
- idempotency key handling
- OpenAPI documentation

### Key Entities

- IntegrationClient
- ApiToken
- ApiScope
- IntegrationEvent
- SyncStatus
- IdempotencyRecord

### Flow: External Pull

1. External system authenticates.
2. External system searches patient.
3. OpesCare returns identity confirmation only.
4. External system requests patient summary with purpose and scope.
5. Consent engine checks valid consent.
6. Access control checks facility and role.
7. Patient record service returns only permitted data.
8. Access is audited.
9. Pull count updates in sync dashboard.

### Flow: External Push

1. External system creates clinical event.
2. System sends event with facility ID, user, source system, external reference, and idempotency key.
3. API Gateway authenticates client.
4. Facility status is checked.
5. Schema is validated.
6. MPI matches patient.
7. If match uncertain, record goes to reconciliation.
8. If match confirmed, duplicate/idempotency check runs.
9. Record is stored with provenance.
10. Timeline updates.
11. Audit event is logged.
12. Response returns sync status.

### Failure and Bug Controls

- unauthenticated push rejected
- suspended facility rejected
- missing idempotency key rejected
- duplicate retry returns original result
- same key with different payload returns conflict
- uncertain patient match goes to reconciliation

### Acceptance Criteria

- external client can search patient
- external client can request consent
- external client can pull scoped summary
- external client can push encounter
- sync status is visible

---

## 71.26 SDK, Widget, and Bridge Agent

### Purpose

These tools make integration easier for different levels of hospital technology.

### Components

- OpesCare Connect SDK
- OpesCare Connect Widget
- OpesCare Bridge Agent
- OpesCare Lite Portal

### Flow: SDK Integration

1. Developer installs SDK.
2. Developer configures client ID, secret, and facility ID.
3. SDK authenticates.
4. SDK calls searchPatient, requestConsent, pullSummary, or pushEncounter.
5. SDK handles retries and idempotency.
6. SDK returns structured result.

### Flow: Widget Integration

1. Hospital backend requests short-lived widget token.
2. OpesCare validates backend credentials.
3. OpesCare returns widget token.
4. Hospital frontend loads widget.
5. Staff searches patient or pushes data through widget.
6. Widget performs only authorized actions.
7. Audit event is logged.

### Flow: Bridge Agent Sync

1. Legacy system exports file or exposes database data.
2. Bridge agent detects new data.
3. Bridge maps fields to OpesCare data contract.
4. Bridge validates data.
5. Bridge queues data if offline.
6. Bridge syncs when online.
7. OpesCare processes through API Gateway.
8. Failed records appear in dashboard.

### Failure and Bug Controls

- widget cannot expose permanent secret
- bridge local queue must be encrypted
- SDK must not hide sync failures
- failed records must appear in reconciliation dashboard

### Acceptance Criteria

- SDK design documented
- widget session security defined
- bridge agent workflow defined
- old systems can integrate without direct API development

---

## 71.27 Webhooks and Sync Engine

### Purpose

The sync engine coordinates async events, retries, webhooks, and delivery to partner systems.

### MVP Requirements

- outbox table
- webhook subscription
- signed webhook payload
- retry attempts
- dead-letter queue
- sync status dashboard

### Key Entities

- OutboxEvent
- WebhookSubscription
- DeliveryAttempt
- DeadLetterEvent
- SyncState

### Flow: Webhook Delivery

1. Internal event occurs.
2. System creates outbox event.
3. Worker picks event.
4. System finds subscribed partners.
5. Minimal payload is signed.
6. Payload is sent.
7. Partner returns success or failure.
8. Failure triggers retry policy.
9. Exhausted retries go to dead-letter queue.
10. Audit/sync log is updated.

### Failure and Bug Controls

- webhook payload must not include full PHI by default
- replay protection required
- retry loop must be bounded
- failed delivery must be visible

### Acceptance Criteria

- webhooks can be subscribed
- signed event is sent
- failed delivery is retried
- dead-letter event is visible

---

## 71.28 Notifications

### Purpose

Notifications inform patients and staff without leaking sensitive information.

### MVP Requirements

- notification templates
- SMS/email/in-app/push-ready design
- sensitivity classification
- delivery log
- retry

### Key Entities

- NotificationTemplate
- NotificationEvent
- DeliveryChannel
- SensitivityClass

### Flow: Consent Notification

1. Consent request is created.
2. Notification engine selects safe template.
3. Message is sent through available channel.
4. Delivery status is recorded.
5. Patient responds through approved method.
6. Audit event is logged.

### Failure and Bug Controls

- SMS/email cannot reveal sensitive diagnosis
- duplicate notifications prevented by dedupe key
- failed notifications retried with limits

### Acceptance Criteria

- safe templates are used
- delivery is logged
- sensitive content is not sent over insecure channels

---

## 71.29 Patient Portal

### Purpose

The patient portal lets patients manage their Health ID, view summary, approve consent, and monitor access.

### MVP Requirements

- patient login or OTP-based access
- view Health ID and QR
- view approved timeline summary
- consent center
- access log
- basic profile update requests

### Key Entities

- PatientPortalAccount
- PatientDevice
- ConsentPreference
- AccessLogView

### Flow: Patient Approves Consent

1. Patient receives consent notification.
2. Patient opens portal or OTP link.
3. Patient views requesting facility, purpose, and scope.
4. Patient approves or denies.
5. System creates or rejects consent grant.
6. Provider system receives status.
7. Audit event is logged.

### Failure and Bug Controls

- patient must see purpose and scope before approval
- compromised device/session can be revoked
- guardian/dependent view separated

### Acceptance Criteria

- patient can view Health ID
- patient can approve consent
- patient can revoke consent
- patient can see access logs

---

## 71.30 Provider Portal

### Purpose

The provider portal is the workspace for clinical users.

### MVP Requirements

- role-specific dashboard
- worklist
- patient search
- patient summary
- encounter workspace
- clinical timeline
- orders and prescription actions

### Key Entities

- Worklist
- EncounterContext
- ProviderPreference

### Flow: Provider Opens Patient Record

1. Provider logs in.
2. Provider opens worklist or searches patient.
3. System checks role and facility assignment.
4. System checks consent or active care relationship.
5. Patient banner appears.
6. Provider views permitted summary.
7. Access is audited.

### Failure and Bug Controls

- provider cannot open unauthorized patient records
- patient context must remain visible
- switching patient requires confirmation

### Acceptance Criteria

- provider dashboard works
- permitted patient summary displays
- unauthorized access blocked
- access audit created

---

## 71.31 Public Health Reporting

### Purpose

This module supports controlled reporting to public health authorities.

### MVP Status

Not required for first MVP, but architecture must support it later.

### Key Entities

- ReportableCase
- PublicHealthSubmission
- MinistryMapping
- DataQualityRule

### Flow: Public Health Submission

1. System identifies reportable event.
2. Report is generated according to country configuration.
3. Data quality checks run.
4. Identifiable data is minimized or removed unless legally required.
5. Submission is sent or exported.
6. Acknowledgment is tracked.
7. Audit event is logged.

### Failure and Bug Controls

- do not expose full patient data unnecessarily
- duplicate reports suppressed
- data quality score included
- jurisdiction routing validated

### Acceptance Criteria

- reportable event can be queued later
- data quality checks are defined
- submission audit exists

---

## 71.32 Research and Ethics-Controlled Data Access

### Purpose

This module governs research access to de-identified or approved patient data.

### MVP Status

Not required for MVP.

### Key Entities

- ResearchRequest
- EthicsApproval
- DatasetExtract
- DeIdentificationProfile
- DataUseAgreement

### Flow: Research Request

1. Researcher submits request.
2. Governance team reviews purpose and ethics approval.
3. Data fields are minimized.
4. De-identification profile is selected.
5. Dataset is prepared.
6. Output is reviewed.
7. Access is granted for limited period.
8. Audit event is logged.

### Failure and Bug Controls

- no direct identifiers in normal research extracts
- small-cell suppression required
- access expires
- ethics approval required

### Acceptance Criteria

- research workflow defined
- direct export blocked without approval
- de-identification rules exist

---

## 71.33 Offline and Downtime Mode

### Purpose

This module allows limited safe operation during internet failure.

### MVP Requirements

- basic downtime plan
- offline queue design
- sync later
- conflict/reconciliation path

### Key Entities

- OfflineSession
- OfflineToken
- LocalQueue
- SyncLedger
- DowntimePacket

### Flow: Offline Data Capture

1. Facility loses connectivity.
2. System enters downtime mode.
3. Staff captures minimal required data locally or on paper.
4. Offline token controls access if previously issued.
5. Events are queued locally.
6. Connectivity returns.
7. Events sync with idempotency keys.
8. Conflicts go to reconciliation.
9. Sync report is generated.
10. Audit events are created.

### Failure and Bug Controls

- offline records cannot claim synced until confirmed
- stale consent must expire
- replay duplicates prevented
- conflicts cannot be silently resolved

### Acceptance Criteria

- offline queue design exists
- replay uses idempotency
- conflicts enter reconciliation

---

## 71.34 Security, Privacy, Audit, and Compliance

### Purpose

This area protects patient records and provides accountability.

### MVP Requirements

- audit log
- access log
- login logs
- role changes log
- patient record access audit
- data export audit
- secret handling rules
- sensitive notification rules

### Key Entities

- AuditEvent
- ProvenanceEvent
- SecurityEvent
- ComplianceCase
- RetentionPolicy

### Flow: Audit Logging

1. User performs sensitive action.
2. System builds audit event.
3. Event includes actor, role, facility, patient, resource, action, reason, IP/device where available, and timestamp.
4. Event is written to append-only audit store.
5. Action proceeds only if audit write succeeds or is safely queued.

### Failure and Bug Controls

- audit logs cannot be deleted
- PHI must not be written into ordinary logs
- export requires audit
- secrets not committed to code
- security events reviewed

### Acceptance Criteria

- audit log created for all high-risk actions
- patient can see access log
- no delete endpoint for audit events

---

## 71.35 Country and Regulatory Configuration

### Purpose

This module allows OpesCare to adapt to different legal and operational environments without hard-coding assumptions.

### MVP Requirements

- country pack design
- consent age configuration
- guardian rule configuration
- retention rule configuration
- public health mapping later
- language/currency settings

### Key Entities

- CountryPack
- PolicyVersion
- EffectiveDate
- LocalizationRule
- RetentionRule

### Flow: Publish Country Policy Pack

1. Admin creates country pack draft.
2. Admin configures legal and operational rules.
3. Governance reviewer approves pack.
4. Pack is published with effective date.
5. Facilities in that country use active version.
6. Historical records retain policy context where required.
7. Audit event is logged.

### Failure and Bug Controls

- policy edits must be versioned
- no silent retroactive changes
- country-specific assumptions must be configured, not guessed

### Acceptance Criteria

- policy pack can be versioned
- effective date works
- changes are audited

---

# 72. System-Wide State Machines

## 72.1 Patient Identity Statuses

Patient identity statuses:

- self_registered_provisional
- facility_created_provisional
- verified_by_facility
- verified_by_identity_document
- verified_by_insurer
- verified_by_authority
- merged
- deceased
- suspended_pending_review

Rules:

- provisional patients can receive care records but must be visibly marked
- merged patients cannot receive new direct records
- deceased patients cannot receive future appointments or prescriptions
- suspended patients require review before sensitive actions

---

## 72.2 Encounter Statuses

Encounter statuses:

- open
- in_progress
- pending_lab
- pending_imaging
- pending_pharmacy
- pending_billing
- admitted
- discharged
- cancelled
- closed

Rules:

- encounter closure requires documentation status review
- cancellation must check dependent objects
- admitted encounter links to inpatient admission

---

## 72.3 Prescription Statuses

Prescription statuses:

- draft
- issued
- partially_dispensed
- fully_dispensed
- cancelled
- expired
- suspended_pending_review

Rules:

- cancelled prescriptions cannot be dispensed
- partially dispensed prescriptions can cancel only remaining balance
- fully dispensed prescriptions cannot be deleted

---

## 72.4 Lab Result Statuses

Lab result statuses:

- ordered
- sample_collected
- in_progress
- entered_pending_validation
- validated
- released
- amended
- cancelled
- rejected

Rules:

- released result cannot be overwritten
- amendment preserves original
- critical result creates alert

---

## 72.5 Claim Statuses

Claim statuses:

- draft
- submitted
- received_by_insurer
- queried
- under_review
- approved
- partially_approved
- rejected
- denied
- resubmitted
- paid
- closed
- cancelled

Rules:

- resubmission links to original claim
- duplicate submission blocked by idempotency and payer reference

---

## 72.6 Inventory Batch Statuses

Batch statuses:

- active
- near_expiry
- expired
- quarantined
- recalled
- depleted
- destroyed

Rules:

- expired, quarantined, recalled, destroyed, and depleted batches cannot be dispensed

---

# 73. API Requirements

## 73.1 API Design Rules

All external APIs must:

- use `/api/v1` versioning
- require authentication unless explicitly public
- validate schema
- enforce facility status
- enforce purpose of use
- enforce consent where relevant
- enforce scopes
- require idempotency key for writes
- return structured errors
- write audit logs
- preserve provenance

## 73.2 MVP API Endpoints

```text
POST /api/v1/auth/token
POST /api/v1/patients/search
POST /api/v1/consents/request
POST /api/v1/consents/verify
POST /api/v1/consents/{id}/revoke
GET  /api/v1/patients/{health_id}/summary
GET  /api/v1/patients/{health_id}/emergency-profile
POST /api/v1/records/encounters
POST /api/v1/records/lab-results
POST /api/v1/records/prescriptions
POST /api/v1/records/dispense-events
GET  /api/v1/sync/status
POST /api/v1/webhooks/subscriptions
GET  /api/v1/reconciliation/cases
POST /api/v1/reconciliation/cases/{id}/resolve
```

## 73.3 Standard API Error Format

```json
{
  "status": "rejected",
  "error_code": "CONSENT_REQUIRED",
  "message": "Patient consent is required before pulling this record.",
  "required_action": "request_consent"
}
```

---

# 74. Testing Requirements

Every module must include tests.

Required test types:

- unit tests
- feature tests
- API tests
- permission tests
- validation tests
- audit tests
- consent tests
- negative tests
- duplicate prevention tests
- sync/idempotency tests

Critical tests:

1. cannot create permanent patient outside Patient Identity Service
2. cannot access patient record without consent or emergency exception
3. cannot use emergency access without reason
4. cannot merge patients without review
5. cannot delete audit logs
6. cannot silently overwrite released lab result
7. cannot dispense cancelled prescription
8. cannot dispense expired or recalled batch
9. cannot push external record without authentication
10. cannot push external record without idempotency key
11. uncertain patient match goes to reconciliation
12. revoked consent blocks future access
13. expired token rejected
14. suspended facility rejected
15. patient can view access log

---

# 75. Build Phases

## Phase 1: Foundation

- repository scaffold
- Laravel setup
- PostgreSQL setup
- Redis setup
- CI setup
- coding standards
- authentication baseline
- role/permission baseline
- audit log foundation
- facility and staff models
- ADRs

## Phase 2: Patient Identity Core

- patient model
- Health ID generation
- QR code
- patient registration
- patient search
- provisional status
- verification status
- guardian/dependent basics
- MPI candidate detection

## Phase 3: Consent and Access

- consent request
- consent grant
- consent revocation
- scoped access
- emergency access
- unknown emergency patient
- access logs
- reconciliation skeleton

## Phase 4: Clinical MVP

- check-in
- triage/vitals
- encounter
- consultation note
- allergies
- diagnoses
- prescription
- lab result upload
- clinical timeline
- forms runtime basics

## Phase 5: Integration MVP

- OpesCare Connect API
- OpenAPI contract
- patient search API
- consent API
- pull summary API
- push encounter API
- push lab result API
- push prescription API
- idempotency
- sync status
- webhook skeleton

## Phase 6: Operational MVP

- appointment basics
- queue basics
- basic billing
- basic pharmacy dispensing
- basic inventory
- patient portal basics
- provider dashboard polish
- notifications
- downtime plan

---

# 76. AI Agent Execution Rules

Jules, Codex, Claude Code, Gemini, and Copilot must not work randomly on the same files.

Rules:

1. One task, one branch, one primary AI agent.
2. Another AI or human reviews.
3. Human approves merge.
4. Main branch is protected.
5. AI agents cannot merge directly to main.
6. AI agents must follow this PRD.
7. No shortcuts around patient identity, consent, audit, security, provenance, or reconciliation.
8. No module should invent its own patient creation logic.
9. No API should be created without validation, authorization, and audit.
10. No migration should be created without data model explanation.

First Jules task after this PRD:

```text
Read docs/PROJECT_KNOWLEDGE.md and docs/PRD.md. Do not build the full system yet.

Task: Create the initial Laravel-based OpesCare project scaffold from scratch. Do not use OpesHIS OS. Do not import or reference OpesHIS OS code. Create repository structure, Laravel app placeholder, docs folders, ADR folder, OpenAPI folder, Python service placeholder, CI placeholder, README, and module placeholder folders only. Do not create patient logic yet. Open a pull request with summary, file list, and next-step recommendations.
```

---

# 77. Final PRD Position

This PRD is now the authoritative build-from-scratch direction for OpesCare.

OpesCare will be built as a new product.

OpesHIS OS will not be used.

The Laravel system is the core platform.

Python is used later only for specialist services.

The first engineering target is not to build every healthcare feature immediately. The first target is to build a safe, auditable, modular foundation that protects patient identity, consent, clinical record integrity, and interoperability from day one.

If a feature conflicts with patient safety, auditability, consent, source attribution, or identity integrity, the feature must be redesigned before implementation.



---

# 15. Exhaustive Flow Coverage Requirement

The previous sections define the architecture and core module flows. This section makes the master prompt stricter. The build is not acceptable unless every module implements or explicitly documents the following flow categories:

1. create/initiate flow
2. view/retrieve flow
3. update/amend flow
4. cancel/revoke/void flow
5. approval/review flow
6. rejection/failure flow
7. exception/escalation flow
8. audit/provenance flow
9. notification flow where applicable
10. reconciliation flow where applicable
11. role/permission flow
12. state transition flow
13. test coverage flow

No module should only implement the happy path.

Every module must define actors, entry points, required permissions, data created, data updated, state changes, validation rules, failure modes, audit events, notifications, API endpoints where relevant, UI screens where relevant, background jobs where relevant, rollback/reversal/amendment rules, and tests.

---

# 16. Complete Flow Matrix By Module

The following flow matrix is mandatory. Every flow must either be implemented in the correct build phase or explicitly marked as deferred with a safe placeholder, documented reason, and future implementation plan.

---

## 16.1 Patient Identity Flow Matrix

Required flows:

1. patient self-registration
2. facility-assisted patient registration
3. patient registration from emergency temporary profile
4. patient registration from data migration
5. patient registration from external API push
6. Health ID generation
7. Health ID regeneration/replacement for lost card
8. QR code generation
9. QR code rotation if compromised
10. patient demographic update request
11. patient demographic correction approval
12. patient identifier addition
13. patient identifier removal/inactivation
14. patient verification by facility
15. patient verification by identity document
16. patient verification by insurer
17. patient verification by approved authority
18. patient profile suspension pending review
19. patient profile reactivation
20. deceased patient marking
21. deceased patient correction/reversal if marked wrongly
22. merged patient status enforcement
23. patient duplicate candidate detection
24. patient search by Health ID
25. patient search by phone
26. patient search by name/date of birth
27. patient search by local facility ID mapping
28. patient profile access audit
29. patient identity export request
30. patient account closure request where legally allowed

Controls:

- no permanent patient creation outside Patient Identity Service
- no duplicate Health ID
- all identity changes audited
- uncertain identity creates review case
- deceased and merged statuses block unsafe future activity

---

## 16.2 Master Patient Index Flow Matrix

Required flows:

1. exact Health ID match
2. exact verified identifier match
3. phone/date/name probabilistic candidate match
4. guardian/dependent-assisted match
5. possible duplicate case creation
6. duplicate case assignment
7. duplicate case review
8. duplicate case rejection
9. patient merge
10. merge impact preview
11. post-merge conflict flagging
12. unmerge request
13. unmerge review
14. unmerge execution
15. ambiguous record review after unmerge
16. duplicate case audit
17. MPI false-positive reporting
18. MPI false-negative reporting
19. matching rule configuration
20. matching rule versioning

Controls:

- no automatic probabilistic merge
- no deletion of secondary merged profile
- merge lineage must be preserved
- unmerge must be possible where lineage exists

---

## 16.3 Guardian, Family, and Dependent Flow Matrix

Required flows:

1. add guardian
2. verify guardian relationship
3. reject guardian request
4. guardian consent approval
5. guardian consent denial
6. guardian access expiry
7. guardian access revocation
8. guardian replacement
9. caregiver access creation
10. caregiver access restriction
11. dependent profile linkage
12. dependent profile unlinking
13. minor-to-adult transition detection
14. minor-to-adult transition notification
15. patient claims adult account
16. guardian access review after adulthood
17. emergency contact addition
18. emergency contact removal
19. family medical history entry
20. family medical history correction

Controls:

- guardian access must have legal basis
- guardian access must be scoped
- age-of-control comes from country policy
- caregiver is not automatically guardian

---

## 16.4 Facility Management Flow Matrix

Required flows:

1. facility application
2. facility document upload
3. facility license verification
4. facility approval
5. facility rejection
6. facility activation
7. facility suspension
8. facility reactivation
9. facility closure
10. branch creation
11. branch suspension
12. department creation
13. department deactivation
14. service catalogue setup
15. facility price list setup
16. facility working hours setup
17. facility integration mode selection
18. facility API credential issuance
19. facility API credential rotation
20. facility API credential revocation
21. facility license expiry alert
22. facility audit review
23. facility data access report
24. facility administrator assignment
25. facility administrator removal

Controls:

- pending/rejected/suspended facilities cannot access patient data
- API credentials only for active verified facilities
- facility deletion blocked if records exist

---

## 16.5 Practitioner and Staff Flow Matrix

Required flows:

1. staff invitation
2. staff account activation
3. staff login
4. MFA enrollment
5. password reset
6. role assignment
7. role change
8. role elevation approval
9. department assignment
10. practitioner license capture
11. practitioner license verification
12. license expiry warning
13. staff suspension
14. staff reactivation
15. staff offboarding
16. active session revocation
17. API token revocation
18. staff access review
19. failed login lockout
20. suspicious access escalation
21. staff profile correction
22. staff duplicate account merge

Controls:

- no shared accounts
- offboarded staff cannot retain sessions
- role elevation audited
- clinical credential status visible

---

## 16.6 Authentication and Access Control Flow Matrix

Required flows:

1. staff login
2. patient login
3. guardian login
4. API client authentication
5. OAuth client credential token issuance
6. token refresh
7. token expiry
8. token revocation
9. session creation
10. session timeout
11. session termination
12. permission check
13. facility scope check
14. department scope check
15. patient consent check
16. emergency override check
17. record sensitivity check
18. purpose-of-use check
19. unauthorized access denial
20. rate-limit trigger
21. suspicious access detection
22. access review case creation

Controls:

- deny by default
- least privilege
- facility context required
- purpose-of-use required for sensitive access

---

## 16.7 Consent Flow Matrix

Required flows:

1. consent request creation
2. patient consent approval
3. patient consent denial
4. guardian consent approval
5. guardian consent denial
6. facility-assisted consent
7. OTP consent
8. app-based consent
9. PIN consent
10. paper/digital signed consent capture
11. consent expiry
12. consent renewal
13. consent revocation
14. consent scope expansion request
15. consent scope reduction
16. consent access denial
17. emergency consent bypass
18. consent audit view
19. consent dispute
20. consent policy version change impact

Controls:

- every consent must have purpose, scope, expiry
- revoked consent blocks future access
- consent cannot be unlimited by default
- consent method is recorded

---

## 16.8 Emergency Access Flow Matrix

Required flows:

1. known patient emergency access
2. unknown patient emergency profile creation
3. temporary emergency ID generation
4. emergency record documentation
5. emergency patient identity resolution
6. emergency temporary profile merge
7. emergency access review case
8. emergency access approval after review
9. emergency access abuse escalation
10. emergency access patient notification where safe
11. emergency access suppression of unsafe notification
12. emergency profile update
13. emergency contact notification where policy allows
14. emergency access report

Controls:

- reason required
- emergency summary only by default
- every emergency access reviewed
- unknown patient must be reconciled

---

## 16.9 Registration, Check-In, Appointment, and Queue Flow Matrix

Required flows:

1. walk-in registration
2. returning patient check-in
3. appointment booking
4. appointment confirmation
5. appointment reminder
6. appointment reschedule
7. appointment cancellation
8. no-show marking
9. appointment check-in
10. visit creation
11. visit cancellation
12. visit transfer to department
13. queue ticket creation
14. queue priority update
15. queue manual override with reason
16. queue skip patient
17. queue recall skipped patient
18. queue transfer to another department
19. queue close/completion
20. emergency queue bypass
21. elderly/pregnant/child priority configuration
22. duplicate active visit detection

Controls:

- no double-booked slots
- queue override audited
- cancelled visits cascade to dependent tasks
- emergency bypass supported

---

## 16.10 Triage and Vitals Flow Matrix

Required flows:

1. triage record creation
2. vital sign entry
3. vital sign unit validation
4. abnormal value flagging
5. critical value alert
6. acuity assignment
7. triage note amendment
8. triage cancellation entered-in-error
9. triage escalation
10. triage bypass with reason
11. repeated vitals capture
12. vitals trend display
13. device-assisted vitals entry later
14. manual correction review

Controls:

- impossible values blocked/flagged
- critical vitals alert provider
- triage must link to visit/encounter

---

## 16.11 Consultation and Clinical Documentation Flow Matrix

Required flows:

1. start consultation
2. view patient summary
3. view allergies
4. view active medications
5. view recent labs
6. create draft clinical note
7. sign clinical note
8. amend signed note
9. mark note entered-in-error
10. add diagnosis
11. revise diagnosis
12. resolve/inactivate diagnosis
13. add allergy
14. update allergy severity
15. inactivate allergy
16. create care plan
17. update care plan
18. create lab order
19. create imaging order
20. create prescription
21. create referral
22. create admission order
23. create follow-up plan
24. close consultation
25. reopen encounter with reason
26. cancel encounter with dependent object review

Controls:

- signed notes immutable except amendment
- allergies visible before prescribing
- diagnosis changes preserve history
- wrong-patient prevention banner always visible

---

## 16.12 Clinical Timeline Flow Matrix

Required flows:

1. timeline render
2. timeline filtering
3. timeline search
4. timeline sensitivity filtering
5. unverified document display
6. verified clinical event display
7. event detail view
8. event provenance view
9. event amendment display
10. event entered-in-error display
11. restricted event redaction
12. patient-facing timeline view
13. provider-facing timeline view
14. insurer-limited timeline view
15. timeline export request
16. timeline print/download with audit

Controls:

- unauthorized records hidden or redacted
- provenance visible
- unverified patient uploads separated

---

## 16.13 Laboratory Flow Matrix

Required flows:

1. lab order creation
2. lab order cancellation
3. lab order payment hold where applicable
4. specimen collection
5. specimen rejection
6. specimen recollection request
7. barcode/sample ID generation
8. sample transfer
9. result entry
10. result validation
11. result rejection by validator
12. result release
13. critical result alert
14. critical result acknowledgment
15. result amendment
16. result entered-in-error
17. result print/download
18. external lab result import
19. lab analyzer integration placeholder
20. lab turnaround-time reporting

Controls:

- unvalidated result not released
- released result amended, not overwritten
- sample mismatch stops process
- critical result requires acknowledgment

---

## 16.14 Imaging and Radiology Flow Matrix

Required flows:

1. imaging order creation
2. imaging order cancellation
3. imaging appointment scheduling
4. patient identity verification before imaging
5. imaging study creation
6. accession number capture
7. report draft
8. report validation/sign-off
9. report amendment
10. report entered-in-error
11. DICOM/PACS reference capture
12. image access link creation
13. image/report download with audit
14. external imaging report import
15. critical imaging finding alert

Controls:

- wrong accession must not attach to patient
- image storage via secure reference where needed
- report amendments preserve original

---

## 16.15 Prescription and Pharmacy Flow Matrix

Required flows:

1. prescription draft
2. prescription issue
3. allergy check
4. duplicate medication check
5. interaction warning
6. dosage warning placeholder
7. prescription cancellation
8. prescription expiry
9. prescription suspension
10. prescription renewal/refill
11. prescription partial dispense
12. prescription full dispense
13. dispense cancellation before completion
14. dispense correction
15. medication return
16. stock batch selection
17. blocked dispense for expired/cancelled prescription
18. controlled medication audit placeholder
19. pharmacy external dispense API
20. patient medication history view

Controls:

- cancelled/expired prescriptions cannot be dispensed
- fully dispensed prescription cannot be deleted
- high-risk override requires reason

---

## 16.16 Billing and Cashier Flow Matrix

Required flows:

1. price list creation
2. price list update
3. service charge creation
4. invoice generation
5. invoice cancellation/void
6. payment collection
7. receipt generation
8. partial payment
9. outstanding balance
10. discount request
11. discount approval
12. payment reversal
13. refund request
14. refund approval
15. cashier session open
16. cashier session close
17. cashier reconciliation
18. mobile money pending status
19. failed payment reconciliation
20. insurance co-payment calculation
21. emergency care billing deferment

Controls:

- payments never deleted
- reversals are separate transactions
- discounts and refunds require permissions
- clinical documentation not blocked after care delivered

---

## 16.17 Insurance and Claims Flow Matrix

Required flows:

1. coverage record creation
2. coverage verification
3. coverage expiry
4. eligibility request
5. eligibility response
6. preauthorization request
7. preauthorization approval
8. preauthorization denial
9. claim draft
10. claim submission
11. claim received
12. claim query
13. claim correction
14. claim resubmission
15. claim partial approval
16. claim rejection
17. claim denial
18. claim payment posting
19. claim closure
20. duplicate claim prevention
21. insurer API integration placeholder

Controls:

- insurer receives minimum necessary data
- claim resubmission versioned
- duplicate claim blocked

---

## 16.18 Inventory Flow Matrix

Required flows:

1. item creation
2. item category setup
3. supplier creation
4. purchase order placeholder
5. stock-in
6. batch creation
7. batch expiry alert
8. stock transfer
9. transfer acceptance
10. stock adjustment
11. adjustment approval
12. stock-out
13. stock consumption by lab
14. stock consumption by pharmacy
15. stock consumption by ward
16. batch quarantine
17. batch recall
18. batch destruction
19. expired batch blocking
20. low-stock alert
21. stock ledger report

Controls:

- no negative stock without approved policy
- expired/recalled/quarantined stock blocked
- all stock movements audited

---

## 16.19 Inpatient, Ward, and Nursing Flow Matrix

Required flows:

1. admission order
2. bed availability check
3. bed assignment
4. admission confirmation
5. ward transfer
6. bed release
7. nursing note creation
8. nursing note amendment
9. vital charting
10. fluid charting
11. medication administration due list
12. medication dose given
13. dose missed
14. dose refused
15. dose held
16. doctor round note
17. inpatient lab order
18. inpatient pharmacy issue
19. discharge planning
20. discharge summary
21. discharge against medical advice
22. death record
23. deceased status update
24. inpatient billing finalization

Controls:

- one active patient per bed
- medication administration linked to order
- discharge summary required
- death status is identity-level

---

## 16.20 Referral Flow Matrix

Required flows:

1. referral creation
2. referral package selection
3. referral consent
4. referral send
5. referral receipt
6. referral accept
7. referral reject
8. referral request more information
9. referral appointment creation
10. referral access expiry
11. referral cancellation
12. referral feedback
13. referral closure
14. referral escalation for no response

Controls:

- only selected records shared
- referral access expires
- full chart not exposed by default

---

## 16.21 Forms and Questionnaire Flow Matrix

Required flows:

1. form template draft
2. form question creation
3. form validation rule creation
4. form publish
5. form versioning
6. form retirement
7. form response creation
8. form response amendment
9. form response entered-in-error
10. sensitive form access control
11. patient-submitted form
12. form export with audit
13. form mapping to structured data

Controls:

- published form versions immutable
- response references exact form version
- sensitive forms restricted

---

## 16.22 Terminology Flow Matrix

Required flows:

1. code system creation
2. code import
3. code search
4. code selection
5. code versioning
6. code deactivation
7. local code mapping
8. mapping review
9. unmapped code reconciliation
10. value set creation
11. medication catalogue setup
12. lab test catalogue setup
13. diagnosis catalogue setup
14. terminology export

Controls:

- versioned codes preserved
- inactive codes warn/block
- unmapped codes visible

---

## 16.23 Document and Legacy Record Flow Matrix

Required flows:

1. document upload
2. file validation
3. malware scan placeholder
4. document classification
5. patient association
6. document verification request
7. document approval
8. document rejection
9. document partial verification
10. OCR extraction request
11. OCR result review
12. structured data promotion
13. document reassignment to correct patient
14. document archive
15. document access audit
16. document download audit

Controls:

- uploaded documents unverified by default
- no direct clinical truth without review
- wrong-patient document can be reassigned with audit

---

## 16.24 Reconciliation Flow Matrix

Required flows:

1. patient-not-found case
2. multiple-patient-match case
3. invalid payload case
4. duplicate suspicion case
5. idempotency conflict case
6. unmapped code case
7. facility mismatch case
8. document mismatch case
9. case assignment
10. case resolution
11. case rejection
12. case escalation
13. attach to confirmed patient
14. create provisional patient
15. request correction from source
16. mark duplicate
17. reconciliation audit report

Controls:

- unresolved cases do not enter timeline
- all resolutions audited
- reviewers cannot bypass identity rules

---

## 16.25 Integration API Flow Matrix

Required flows:

1. API client registration
2. API credential issuance
3. API token request
4. API token expiry
5. API credential rotation
6. API credential revocation
7. patient search API
8. consent request API
9. consent verify API
10. pull summary API
11. pull emergency profile API
12. push encounter API
13. push lab result API
14. push prescription API
15. push dispense event API
16. push document API
17. sync status API
18. failed sync API
19. reconciliation API
20. API rate limit
21. API abuse detection
22. API version deprecation
23. API sandbox-to-production promotion

Controls:

- idempotency required for writes
- purpose-of-use required for pulls
- external user identity captured where possible
- suspended clients blocked

---

## 16.26 SDK, Widget, Bridge, and Lite Flow Matrix

Required flows:

1. SDK installation guide
2. SDK client initialization
3. SDK token refresh
4. SDK retry logic
5. SDK idempotency handling
6. SDK error surfacing
7. widget session token request
8. widget session expiry
9. widget patient search
10. widget consent request
11. widget push/pull
12. widget audit metadata
13. bridge agent installation
14. bridge credential setup
15. bridge field mapping
16. bridge file watch
17. bridge local queue
18. bridge encrypted storage
19. bridge sync retry
20. bridge failed record dashboard
21. bridge update mechanism
22. Lite portal manual patient search
23. Lite portal manual encounter entry
24. Lite portal manual lab/prescription entry

Controls:

- widget never exposes permanent secret
- bridge queue encrypted
- SDK does not hide failed syncs
- Lite portal records provenance

---

## 16.27 Webhook and Sync Engine Flow Matrix

Required flows:

1. webhook subscription creation
2. webhook endpoint verification
3. webhook secret rotation
4. outbox event creation
5. webhook payload signing
6. webhook delivery
7. webhook success
8. webhook retry
9. webhook failure
10. dead-letter queue
11. replay protection
12. webhook pause
13. webhook resume
14. webhook event audit
15. partner re-fetch detail API
16. sync dashboard update

Controls:

- minimal payload by default
- no full PHI unless explicitly approved
- bounded retries
- dead-letter visible

---

## 16.28 Notification Flow Matrix

Required flows:

1. template creation
2. template approval
3. template sensitivity classification
4. notification event creation
5. SMS dispatch
6. email dispatch
7. in-app dispatch
8. push dispatch placeholder
9. delivery success
10. delivery failure
11. retry
12. deduplication
13. patient opt-out where legally allowed
14. mandatory safety notification override
15. notification audit

Controls:

- no sensitive details in insecure channels
- templates approved
- delivery logged

---

## 16.29 Patient Portal Flow Matrix

Required flows:

1. patient account creation
2. patient login
3. OTP verification
4. device registration
5. device revocation
6. view Health ID
7. view QR
8. view clinical summary
9. view timeline
10. view lab result
11. view prescription
12. approve consent
13. deny consent
14. revoke consent
15. view access logs
16. flag suspicious access
17. upload legacy document
18. manage dependents
19. update contact request
20. download record with audit

Controls:

- patient sees purpose/scope before consent
- dependent access policy enforced
- downloads audited

---

## 16.30 Provider Portal Flow Matrix

Required flows:

1. provider dashboard
2. provider worklist
3. patient search
4. patient open with consent check
5. patient summary view
6. timeline view
7. triage review
8. consultation workspace
9. order entry
10. prescription entry
11. referral creation
12. admission order
13. task list
14. alert acknowledgment
15. note amendment
16. provider handover placeholder

Controls:

- unauthorized patient blocked
- patient banner visible
- alert overrides audited

---

## 16.31 Public Health Flow Matrix

Required flows:

1. reportable condition configuration
2. reportable event detection
3. report draft
4. data minimization
5. data quality scoring
6. jurisdiction routing
7. report approval
8. report submission
9. acknowledgment tracking
10. duplicate suppression
11. correction submission
12. public health dashboard
13. aggregate export
14. reporting audit

Controls:

- identifiable data minimized
- data quality shown
- jurisdiction policy applied

---

## 16.32 Research Flow Matrix

Required flows:

1. research request
2. ethics document upload
3. governance review
4. data minimization
5. de-identification profile selection
6. small-cell suppression
7. dataset preview
8. dataset approval
9. dataset release
10. access expiry
11. access revocation
12. research audit
13. publication/result tracking placeholder

Controls:

- no identifiable export by default
- ethics approval required
- access expires

---

## 16.33 Offline and Downtime Flow Matrix

Required flows:

1. downtime detection
2. downtime mode activation
3. offline user authorization
4. offline patient lookup from valid cache
5. offline emergency access
6. offline encounter capture
7. offline queue storage
8. offline paper fallback entry
9. reconnect detection
10. queued event sync
11. idempotency replay protection
12. conflict detection
13. reconciliation case creation
14. downtime report
15. offline cache expiry
16. offline cache purge

Controls:

- cache encrypted
- stale consent expires
- queued records not marked synced until accepted

---

## 16.34 Security, Privacy, Audit, and Compliance Flow Matrix

Required flows:

1. audit event creation
2. audit event search
3. patient access log view
4. compliance case creation
5. suspicious access escalation
6. data export request
7. data export approval
8. data export delivery
9. security incident detection
10. incident containment
11. breach impact analysis
12. breach notification workflow
13. root cause analysis
14. corrective action tracking
15. backup job
16. backup restore test
17. data retention job
18. legal hold
19. account lockout
20. secret rotation

Controls:

- audit logs append-only
- PHI not in ordinary logs
- exports audited
- backups encrypted and tested

---

## 16.35 Country Configuration Flow Matrix

Required flows:

1. country pack draft
2. age-of-consent configuration
3. guardian policy configuration
4. retention policy configuration
5. data localization configuration
6. public health reporting configuration
7. language configuration
8. currency configuration
9. facility licensing rules
10. policy review
11. policy approval
12. policy publication
13. policy effective date
14. policy rollback/supersession
15. policy impact report

Controls:

- policy versions immutable after publication
- effective-dated changes
- no regional assumptions hard-coded

---

## 16.36 Governance Flow Matrix

Required flows:

1. governance committee setup
2. reviewer assignment
3. emergency access review
4. access abuse review
5. research review
6. breach review
7. exception request
8. exception approval
9. exception expiry
10. policy approval
11. disputed access resolution
12. facility sanction review
13. audit report approval
14. governance decision archive

Controls:

- no self-approval
- exceptions expire
- decisions immutable

---

# 17. Flow Completeness Gate

Before any module is marked complete, the implementer must answer these questions:

1. What creates the record?
2. Who can view it?
3. Who can update or amend it?
4. Who can cancel, revoke, void, or reverse it?
5. What are all possible statuses?
6. What happens if the wrong patient is selected?
7. What happens if consent is missing?
8. What happens if the facility is suspended?
9. What happens if the user is offboarded?
10. What happens if the network fails?
11. What happens if the same request is retried?
12. What is the audit event?
13. What is the provenance/source metadata?
14. What notifications are sent?
15. What data is hidden from unauthorized roles?
16. What tests prove this works?
17. What failure modes were tested?
18. What cannot be deleted?
19. What must be amended instead of overwritten?
20. What enters reconciliation instead of being accepted?

If these questions cannot be answered, the module is incomplete.

---

# 18. Revised First Task for Jules

Use this exact first task:

```text
Read this master prompt carefully.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not import OpesHIS OS code.
Do not copy OpesHIS OS database or module structure.

Task: Create the initial Laravel-based OpesCare project scaffold from scratch.

Scope:
1. Create the monorepo structure.
2. Add Laravel app placeholder under apps/api-laravel.
3. Add Python service placeholder under services/ai-python.
4. Add contracts/openapi folder.
5. Add docs/adr, docs/product, docs/security, docs/integration, docs/runbooks.
6. Add .github workflow placeholders.
7. Add README.md explaining architecture and build-from-scratch rule.
8. Add ADR-0001 documenting Laravel modular monolith + PostgreSQL + Redis + Python specialist services.
9. Add module placeholder folders only.
10. Add docs/FLOW_COMPLETENESS_GATE.md using the flow completeness gate from the master prompt.
11. Do not create patient logic yet.
12. Do not create database migrations yet.
13. Do not implement authentication yet.
14. Open a pull request with summary, file list, architecture notes, risks, and next recommended PRs.
```

---

# 19. Final Engineering Instruction

Build OpesCare slowly and correctly.

Do not chase speed at the cost of patient safety.

Do not treat healthcare records like ordinary CRUD data.

Every feature must consider:

- patient safety
- identity integrity
- consent
- privacy
- auditability
- source attribution
- clinical workflow
- facility context
- role permissions
- data validation
- failure handling
- future interoperability

If a feature conflicts with patient safety, identity integrity, consent, auditability, or provenance, redesign it before implementation.

The goal is not only to make OpesCare work. The goal is to make OpesCare trusted.

