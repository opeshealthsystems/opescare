# OpesCare Patient Safety & Clinical Risk Register

**Version:** 1.0  
**Status:** Active  
**Last Updated:** 2026-05-19  
**Owner:** Clinical Safety Officer / Privacy Officer  
**Review Cycle:** Quarterly + On Incident

---

## Purpose

This Clinical Risk Register documents the top patient-safety and data-integrity risks identified in OpesCare. Each risk includes its root cause, potential harm, existing controls, residual risk, and required mitigations. All mitigations marked **[ENFORCED]** are implemented in code. Those marked **[PROCESS]** are enforced through workflow policy and staff training.

**Non-negotiable rule:** Any code change that touches clinical data pathways must be reviewed against this register. Any new risk identified must be added here within 5 business days of discovery.

---

## Risk Severity Scale

| Level | Score | Definition |
|-------|-------|------------|
| Critical | 4 | Could cause patient death or severe irreversible harm |
| High | 3 | Could cause significant clinical harm or privacy breach |
| Medium | 2 | Could cause moderate harm, operational disruption, or compliance violation |
| Low | 1 | Minor impact, easily corrected, no patient harm expected |

**Residual Risk = Likelihood (1-4) × Impact (1-4) after controls applied.**

---

## RISK-001: Wrong Patient Selected

**ID:** RISK-001  
**Category:** Clinical Safety  
**Severity:** Critical  
**Owner:** Clinical Safety Officer

### Description
A clinician opens, edits, or creates clinical records (notes, prescriptions, lab orders) under the wrong patient identity.

### Root Causes
- Multiple patients open in browser tabs simultaneously
- Similarity in patient names or Health IDs
- Rapid patient throughput in high-pressure environments
- Auto-populated fields after patient selection not re-verified

### Potential Harm
- Wrong treatment administered
- Wrong medication prescribed
- Clinical records permanently attached to wrong patient
- Legal liability

### Existing Controls
- **[ENFORCED]** Patient name and Health ID displayed persistently in page header on all clinical views
- **[ENFORCED]** Clinical actions (prescriptions, notes) require `patient_id` in POST body — server validates match against session context
- **[ENFORCED]** Audit log on every clinical write includes patient_id, user_id, facility_id, timestamp
- **[PROCESS]** Clinical staff training: always verify full name + DOB before clinical action

### Residual Risk
- **Likelihood:** 2 (possible in high-volume settings)
- **Impact:** 4 (critical harm potential)
- **Residual Score:** 8 (High)

### Required Mitigations
1. **[PROCESS]** Two-identifier verification (name + DOB) before any clinical action — mandatory training requirement
2. **[ENFORCED]** Patient identity confirmation dialog on prescription issue and lab order creation
3. **[PROCESS]** No clinical workflows from search results pages — must navigate to dedicated patient view first

---

## RISK-002: Duplicate Patient Record

**ID:** RISK-002  
**Category:** Data Integrity  
**Severity:** High  
**Owner:** Data Steward

### Description
Two or more OpesCare records represent the same real patient, leading to split clinical history, duplicate Health IDs, or conflicting demographic data.

### Root Causes
- Patient registered at multiple facilities without Health ID lookup
- Name variations (spelling, prefixes, maiden names)
- OCR errors from paper/import records
- Manual entry errors

### Potential Harm
- Incomplete clinical picture leading to wrong treatment
- Double medication — drug interactions not detected
- Insurance claims rejected or duplicated
- Patient rights violation (records not unified on request)

### Existing Controls
- **[ENFORCED]** Health ID (OC-CMR-XXXX) is unique across platform
- **[ENFORCED]** Duplicate detection on registration: system warns if name + DOB match exists
- **[ENFORCED]** `data_steward` role can review and merge duplicate records
- **[ENFORCED]** Merge actions are audit-logged with before/after state
- **[ENFORCED]** Soft-delete on merged records — original not physically deleted

### Residual Risk
- **Likelihood:** 3 (common in multi-facility deployments without QR scan)
- **Impact:** 3 (significant harm potential)
- **Residual Score:** 9 (High)

### Required Mitigations
1. **[PROCESS]** Receptionists must search by Health ID first before creating new patient
2. **[ENFORCED]** QR-scan registration: scanning patient QR returns existing Health ID, blocking duplicate creation
3. **[PROCESS]** Data steward reviews flagged duplicates weekly
4. **[ENFORCED]** Merge requires supervisor confirmation and dual approval for merges affecting active clinical records

---

## RISK-003: Wrong Lab Result Attached to Patient

**ID:** RISK-003  
**Category:** Clinical Safety  
**Severity:** Critical  
**Owner:** Lab Manager / Clinical Safety Officer

### Description
A laboratory result (blood test, culture, imaging report) is attached to the wrong patient record.

### Root Causes
- Manual entry of results without order ID reference
- Sample labeling error at collection
- Batch result upload matching by name only (not order ID)
- Lab technician working on multiple orders simultaneously

### Potential Harm
- Wrong diagnosis based on another patient's results
- Missed diagnosis (patient's real results never seen)
- Wrong treatment — drug contraindicated given true result
- Potential patient death in critical result scenarios

### Existing Controls
- **[ENFORCED]** Lab results are linked to `lab_order_id` — order ID is mandatory on result entry
- **[ENFORCED]** Lab order links `patient_id` + `facility_id` — result inherits these
- **[ENFORCED]** Final result release requires `lab_manager` role (`lab_results.release` high-risk permission)
- **[ENFORCED]** Audit log on every result creation and release
- **[ENFORCED]** Critical/abnormal flags (HH, LL) trigger notification workflow

### Residual Risk
- **Likelihood:** 2
- **Impact:** 4
- **Residual Score:** 8 (High)

### Required Mitigations
1. **[ENFORCED]** Sample barcode scanned at collection — barcode tied to `lab_order_id`
2. **[PROCESS]** Lab manager reviews patient name + order details before releasing any result
3. **[ENFORCED]** Results with `flag = 'HH'` or `flag = 'LL'` require explicit acknowledgment by ordering provider
4. **[PROCESS]** Lab standard operating procedure: print sample label from system — no manual label writing

---

## RISK-004: Wrong Prescription Issued

**ID:** RISK-004  
**Category:** Clinical Safety  
**Severity:** Critical  
**Owner:** Clinical Safety Officer / Pharmacist

### Description
A patient receives a prescription for the wrong medication, wrong dose, wrong route, or wrong duration — or a prescription intended for another patient.

### Root Causes
- High-volume clinical environment leading to selection errors
- Similar drug names (lookalike/soundalike)
- Incorrect patient in context (links to RISK-001)
- CDSS alert dismissed without reading
- Prescription item copied from template without review

### Potential Harm
- Adverse drug reaction
- Drug interaction with existing medications
- Overdose or under-dose
- Patient death in severe cases

### Existing Controls
- **[ENFORCED]** `prescriptions.issue` is a high-risk permission — doctor/authorized role only
- **[ENFORCED]** CDSS drug interaction alerts displayed on prescription creation
- **[ENFORCED]** CDSS disclaimer: "Clinical alerts are decision-support tools only. They do not replace professional clinical judgment." — mandatory on all CDSS-touching views
- **[ENFORCED]** Pharmacist must verify prescription before dispensing — `pharmacist` role only for dispense action
- **[ENFORCED]** Prescription linked to `patient_id` + `visit_id` — cannot be detached

### Residual Risk
- **Likelihood:** 2
- **Impact:** 4
- **Residual Score:** 8 (High)

### Required Mitigations
1. **[ENFORCED]** Five Rights check displayed in pharmacist dispense view: Right Patient, Right Drug, Right Dose, Right Route, Right Time
2. **[PROCESS]** Pharmacist reads prescription aloud with patient before dispensing
3. **[ENFORCED]** Allergy warning shown prominently if prescribed drug matches known patient allergy
4. **[PROCESS]** Doctors must review CDSS alerts before signing — dismissal is logged

---

## RISK-005: Critical Lab Result Not Acknowledged

**ID:** RISK-005  
**Category:** Clinical Safety  
**Severity:** High  
**Owner:** Clinical Safety Officer / Lab Manager

### Description
A critically abnormal lab result (e.g., dangerously high potassium, positive blood culture) is released but never reviewed by the responsible clinician, leading to delayed or missed treatment.

### Root Causes
- Clinician not notified of result availability
- Notification received but dismissed without reading
- Ordering clinician absent / shift change
- Result released outside normal hours

### Potential Harm
- Patient deterioration due to untreated critical condition
- Missed sepsis, electrolyte imbalance, or malignancy
- Delayed treatment escalation

### Existing Controls
- **[ENFORCED]** `flag = 'HH'` or `flag = 'LL'` marks result as critical
- **[ENFORCED]** Push notification sent to ordering provider on critical result release
- **[ENFORCED]** In-app notification persists until acknowledged
- **[PROCESS]** Lab phones ordering provider directly for critical results (paper backup protocol)

### Residual Risk
- **Likelihood:** 2
- **Impact:** 3
- **Residual Score:** 6 (Medium)

### Required Mitigations
1. **[ENFORCED]** Critical result acknowledgment required — unacknowledged critical results escalate to department head after 2 hours
2. **[ENFORCED]** Acknowledgment is audit-logged: user, timestamp, action taken note
3. **[PROCESS]** On-call coverage protocol: when ordering clinician is unavailable, results escalate to on-call provider
4. **[ENFORCED]** Daily unacknowledged critical results report available to hospital_director

---

## RISK-006: Emergency Access (Break-Glass) Abused

**ID:** RISK-006  
**Category:** Privacy / Security  
**Severity:** High  
**Owner:** Privacy Officer / Security Officer

### Description
A clinical user invokes break-glass emergency access to view a patient's full EMR without a legitimate clinical emergency — curiosity access, unauthorized research, or snooping on celebrity/VIP patients.

### Root Causes
- Easy access to break-glass without sufficient friction
- No real-time monitoring of access patterns
- Staff unaware of consequences
- Access not reviewed regularly

### Potential Harm
- Patient privacy violation
- Regulatory penalty (data protection law)
- Reputational damage to facility and platform
- Legal action by patient

### Existing Controls
- **[ENFORCED]** Emergency reason mandatory before access granted — cannot proceed without reason
- **[ENFORCED]** Every break-glass access logged in `emergency_access_logs` with user, timestamp, patient, reason
- **[ENFORCED]** Patient notified of break-glass access within 24 hours (configurable per facility)
- **[ENFORCED]** Privacy officer has read access to all break-glass events via audit log view
- **[PROCESS]** Abuse of break-glass access is a disciplinary matter — stated in staff agreement

### Residual Risk
- **Likelihood:** 2
- **Impact:** 3
- **Residual Score:** 6 (Medium)

### Required Mitigations
1. **[ENFORCED]** Privacy officer receives weekly summary of all break-glass events
2. **[PROCESS]** Any break-glass use >3 times per week per user automatically flags for review
3. **[PROCESS]** Annual recertification of break-glass access policy for all clinical staff
4. **[ENFORCED]** Audit dashboard: break-glass frequency by user, by patient, by time-of-day

---

## RISK-007: Insurance User Accesses More Clinical Data Than Necessary

**ID:** RISK-007  
**Category:** Privacy / Compliance  
**Severity:** High  
**Owner:** Privacy Officer

### Description
An insurance reviewer or insurance admin accesses patient clinical records beyond what is strictly necessary for the specific insurance claim being reviewed.

### Root Causes
- Overly broad data access in insurance claim review views
- API responses returning full clinical history when only billing minimum needed
- Poor data minimization in claim export packets

### Potential Harm
- Patient privacy violation
- Breach of Minimum Necessary principle (HIPAA/GDPR equivalent)
- Insurance misuse of sensitive clinical data
- Regulatory penalty

### Existing Controls
- **[ENFORCED]** `insurance_reviewer` role: Billing Minimum tier only — Name, Health ID, insurance, invoice history, and claim-relevant clinical data only
- **[ENFORCED]** `insurance_admin` role: No access to patient medical records outside claims
- **[ENFORCED]** API endpoints check role before returning clinical data — insurance roles receive filtered responses
- **[PROCESS]** Insurance access agreement mandates Minimum Necessary principle

### Residual Risk
- **Likelihood:** 2
- **Impact:** 3
- **Residual Score:** 6 (Medium)

### Required Mitigations
1. **[ENFORCED]** Insurance claim packets include only: diagnosis codes, procedure codes, visit dates, billing codes — no free-text clinical notes
2. **[PROCESS]** Annual review of insurance data access logs by privacy officer
3. **[ENFORCED]** Insurance reviewer access to any patient record outside an active claim is blocked and logged

---

## RISK-008: Stale Medicine Availability Data Causes Wrong Dispense Decision

**ID:** RISK-008  
**Category:** Clinical Safety / Data Integrity  
**Severity:** Medium  
**Owner:** Pharmacy Manager

### Description
A pharmacist or prescriber sees medication as available in the system when it is actually out of stock, leading to prescription of unavailable medication or delay in patient care.

### Root Causes
- Pharmacy stock not updated in real time (offline lag)
- Manual stock adjustments not entered into system
- Import data not reconciled with physical inventory

### Potential Harm
- Patient unable to get prescribed medication at facility
- Prescriber selects drug based on false availability
- Treatment delay

### Existing Controls
- **[ENFORCED]** Pharmacy stock module tracks current quantity with depletion on dispense
- **[PROCESS]** Daily physical stock count reconciliation SOP
- **[ENFORCED]** Low-stock alerts generated when stock falls below configured threshold

### Residual Risk
- **Likelihood:** 3
- **Impact:** 2
- **Residual Score:** 6 (Medium)

### Required Mitigations
1. **[ENFORCED]** Stock availability indicator shown on prescription item — clear "In Stock / Low Stock / Out of Stock" status
2. **[PROCESS]** Pharmacist must confirm physical availability before marking dispense complete
3. **[ENFORCED]** Offline mode shows stock data as "may be outdated" warning when not connected
4. **[PROCESS]** Weekly stock reconciliation report reviewed by pharmacy_manager

---

## RISK-009: Stale Blood Availability Causes Wrong Clinical Decision

**ID:** RISK-009  
**Category:** Clinical Safety / Data Integrity  
**Severity:** High  
**Owner:** Lab Manager / Clinical Safety Officer

### Description
A clinician orders or relies on blood product availability data that is outdated, leading to surgical or emergency planning based on unavailable blood units.

### Root Causes
- Blood bank inventory not updated in real time
- Cross-match results not linked to inventory depletion
- Offline sync delays in blood bank module

### Potential Harm
- Emergency surgery undertaken without verified blood availability
- Patient death due to uncontrolled bleeding without blood products
- Wrong blood type assigned in emergency

### Existing Controls
- **[ENFORCED]** Blood bank inventory module with real-time depletion tracking
- **[PROCESS]** Blood bank SOP: any issue of blood unit immediately entered into system before leaving bank
- **[ENFORCED]** Blood type verification required before cross-match release

### Residual Risk
- **Likelihood:** 2
- **Impact:** 4
- **Residual Score:** 8 (High)

### Required Mitigations
1. **[ENFORCED]** Blood bank data marked "live only" — unavailable in offline mode
2. **[PROCESS]** Critical blood orders trigger phone confirmation to blood bank in addition to system order
3. **[ENFORCED]** Blood availability data timestamp shown — data older than 30 minutes shows staleness warning
4. **[PROCESS]** Blood bank SOP reviewed and signed annually by all blood bank staff

---

## RISK-010: Offline Sync Overwrites Live Clinical Data

**ID:** RISK-010  
**Category:** Data Integrity / Clinical Safety  
**Severity:** High  
**Owner:** Technical Lead / Clinical Safety Officer

### Description
When an OpesCare Lite device reconnects after offline operation, its locally-collected data sync overwrites more recent clinical data entered by online users, causing data loss or clinical record corruption.

### Root Causes
- Last-write-wins conflict resolution without clinical awareness
- Network interruption during sync — partial write
- Lite device clock skew causing incorrect timestamp ordering

### Potential Harm
- Clinical note from online session overwritten by stale offline draft
- Lab order cancelled offline but re-opened on sync
- Patient allergy update from online session lost
- Clinical record shows inconsistent state

### Existing Controls
- **[ENFORCED]** Lite offline mode blocks: full EMR write, insurance claims, official document issuance, broad patient search
- **[ENFORCED]** Offline queue uses `source_system = 'lite_offline'` and `source_reference` for traceability
- **[ENFORCED]** Sync conflict detection: if server record updated_at > device record, flag as conflict — do NOT auto-overwrite
- **[ENFORCED]** Conflicts presented to authorized user for manual resolution

### Residual Risk
- **Likelihood:** 2
- **Impact:** 3
- **Residual Score:** 6 (Medium)

### Required Mitigations
1. **[ENFORCED]** All Lite sync operations append — never overwrite clinical finalized records (status=signed, status=released, status=dispensed)
2. **[ENFORCED]** Sync conflicts logged in audit table with full before/after state
3. **[PROCESS]** Lite device administrators review sync conflict report daily after reconnection
4. **[ENFORCED]** Device clock skew >5 minutes triggers sync warning and prevents clinical writes

---

## Risk Register Summary

| Risk ID | Description | Severity | Residual Score | Status |
|---------|-------------|----------|----------------|--------|
| RISK-001 | Wrong Patient Selected | Critical | 8 (High) | Controls Active |
| RISK-002 | Duplicate Patient Record | High | 9 (High) | Controls Active |
| RISK-003 | Wrong Lab Result Attached | Critical | 8 (High) | Controls Active |
| RISK-004 | Wrong Prescription Issued | Critical | 8 (High) | Controls Active |
| RISK-005 | Critical Result Not Acknowledged | High | 6 (Medium) | Controls Active |
| RISK-006 | Emergency Access Abused | High | 6 (Medium) | Controls Active |
| RISK-007 | Insurance User Sees Too Much Data | High | 6 (Medium) | Controls Active |
| RISK-008 | Stale Medicine Availability | Medium | 6 (Medium) | Controls Active |
| RISK-009 | Stale Blood Availability | High | 8 (High) | Controls Active |
| RISK-010 | Offline Sync Overwrites Clinical Data | High | 6 (Medium) | Controls Active |

---

## Review & Escalation Policy

1. **Quarterly Review:** All risks reviewed; residual scores updated; new risks added from incident reports.
2. **On Incident:** Relevant risk record updated within 48 hours. Mitigation effectiveness re-evaluated.
3. **Escalation:** Any risk with residual score ≥ 12 escalates immediately to hospital_director and privacy_officer.
4. **Closure:** A risk is only removed when all mitigations are fully enforced and independently verified.
