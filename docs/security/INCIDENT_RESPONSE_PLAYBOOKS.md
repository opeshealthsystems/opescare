# OpesCare Incident Response Playbooks

**Version:** 1.0  
**Status:** Active  
**Last Updated:** 2026-05-19  
**Owner:** Security Officer / Privacy Officer  
**Review Cycle:** Semi-annually + After Every Incident

---

## Purpose

These playbooks define the step-by-step response procedures for each class of security, privacy, and operational incident in OpesCare. Each playbook assigns clear roles, actions, timelines, and communication templates.

**Non-negotiable rule:** During any active incident, the incident commander follows the relevant playbook. Deviations must be documented with rationale.

---

## Incident Severity Levels

| Level | Name | Definition | Response Time |
|-------|------|------------|---------------|
| P0 | Critical | Patient harm, mass data breach, full platform down | Immediate — all hands |
| P1 | High | Single-facility data exposure, core module down | < 1 hour |
| P2 | Medium | Feature degradation, single-user impact | < 4 hours |
| P3 | Low | Minor bug, cosmetic issue | Next business day |

---

## Contact Matrix (to be populated per deployment)

| Role | Name | Primary Contact | Secondary Contact |
|------|------|-----------------|-------------------|
| Incident Commander | | | |
| Security Officer | | | |
| Privacy Officer | | | |
| Technical Lead | | | |
| Clinical Safety Officer | | | |
| Legal Counsel | | | |
| Communications Lead | | | |
| Data Protection Authority Contact | | | |

---

## Playbook Index

| # | Playbook | Severity Class |
|---|----------|----------------|
| PB-01 | Data Breach | P0 |
| PB-02 | Wrong Patient Merge | P1 |
| PB-03 | Wrong Lab Result Released | P0 |
| PB-04 | QR Verification Leak | P1 |
| PB-05 | API Key Compromise | P1 |
| PB-06 | Bridge Agent Compromised | P1 |
| PB-07 | Ransomware / Server Compromise | P0 |
| PB-08 | Payment Failure | P2 |
| PB-09 | Webhook Failure | P2 |
| PB-10 | Facility Downtime | P1–P2 |
| PB-11 | Public Health Report Error | P1 |

---

## PB-01: Data Breach

**Trigger:** Confirmed or suspected unauthorized access to patient data, credentials, or PHI (Protected Health Information) by an external attacker or unauthorized internal actor.

**Severity:** P0

**Incident Commander:** Security Officer (escalates to CEO if >1000 records affected)

### Detection Sources
- Security monitoring alert (anomalous data export, bulk query)
- User report (received data that isn't theirs)
- Third-party notification
- Internal audit log review

### Response Steps

**Hour 0–1 (Containment):**
1. [ ] Confirm breach — distinguish false positive from real incident
2. [ ] Identify affected systems, data types, and estimated record count
3. [ ] Revoke compromised credentials / API keys immediately
4. [ ] Isolate affected systems if breach is ongoing
5. [ ] Preserve all logs — do NOT delete or overwrite
6. [ ] Notify: Security Officer, Privacy Officer, Technical Lead, Legal Counsel
7. [ ] Open incident ticket — assign P0

**Hour 1–4 (Assessment):**
8. [ ] Determine scope: which patients, which data types, which time window
9. [ ] Identify root cause (SQL injection, credential theft, insider, misconfiguration)
10. [ ] Assess whether breach is contained or ongoing
11. [ ] Document timeline of events in incident log
12. [ ] Begin breach notification assessment (regulatory threshold check)

**Hour 4–24 (Notification — if regulatory threshold met):**
13. [ ] Notify Data Protection Authority within required window (72h for GDPR-equivalent)
14. [ ] Draft affected patient notification (see template below)
15. [ ] Notify affected facilities
16. [ ] Prepare public statement if media attention likely

**Day 1–30 (Remediation):**
17. [ ] Patch root cause vulnerability
18. [ ] Force password reset for all affected users
19. [ ] Deploy additional monitoring rules
20. [ ] Complete post-incident report
21. [ ] Update risk register (RISK-006 or new entry)
22. [ ] Schedule retrospective

**Patient Notification Template:**
```
Subject: Important Notice About Your Health Information

We are writing to inform you that [FACILITY NAME] experienced a security incident on [DATE]
that may have affected your health records stored in OpesCare.

What happened: [BRIEF NON-TECHNICAL DESCRIPTION]
What data was involved: [DATA TYPES — e.g., name, health ID, visit dates]
What we have done: [STEPS TAKEN]
What you can do: [RECOMMENDED ACTIONS]

If you have questions, contact: [PRIVACY OFFICER CONTACT]
```

---

## PB-02: Wrong Patient Merge

**Trigger:** Two distinct patient records have been incorrectly merged, combining clinical histories of different patients.

**Severity:** P1 (P0 if active clinical harm resulted)

**Incident Commander:** Data Steward (escalates to Clinical Safety Officer if clinical harm)

### Detection Sources
- Patient complaint: "My records contain someone else's information"
- Clinician alert: "This patient's history doesn't match what I know"
- Data quality review
- Automated duplicate detection flag post-merge

### Response Steps

**Immediate (0–2 hours):**
1. [ ] Confirm the incorrect merge — identify both original patient IDs
2. [ ] Place a hold on both merged record — flag `identity_status = 'suspended'` for safety
3. [ ] Alert all facilities currently treating either patient
4. [ ] Notify Clinical Safety Officer if either patient has active clinical episode
5. [ ] Retrieve pre-merge audit log to reconstruct original states

**Assessment (2–8 hours):**
6. [ ] Identify all clinical records affected: visits, notes, diagnoses, lab results, prescriptions, documents
7. [ ] Determine which records belong to which patient
8. [ ] Identify if any clinical decisions were made using merged (incorrect) data
9. [ ] Assess patient harm risk for each affected patient

**Remediation (8–24 hours):**
10. [ ] Un-merge records: restore both patients to pre-merge state using audit log
11. [ ] Re-assign all clinical records to correct patient_id
12. [ ] Remove `suspended` hold after verification
13. [ ] Notify treating clinicians of corrected record state
14. [ ] Notify affected patients (see template)
15. [ ] Document root cause (how did merge happen?)
16. [ ] Update merge controls to prevent recurrence

**Patient Notification Template:**
```
We identified that your health record at [FACILITY] was temporarily combined with
another patient's record. This has been corrected.

If you believe any clinical decisions were made based on incorrect information,
please contact [CLINICAL SAFETY OFFICER CONTACT] immediately.
```

---

## PB-03: Wrong Lab Result Released

**Trigger:** A finalized lab result has been released to a patient or EMR that contains values belonging to a different patient or contains an error in the reported values.

**Severity:** P0

**Incident Commander:** Lab Manager + Clinical Safety Officer

### Detection Sources
- Patient or clinician reports: "This result doesn't match clinical picture"
- Lab technician discovers labeling error
- Internal QA review
- Result value physically impossible for patient demographics

### Response Steps

**Immediate (0–1 hour):**
1. [ ] Confirm the error — identify correct result vs released result
2. [ ] Flag incorrect result in system — prevent further clinical use
3. [ ] Alert ordering clinician(s) immediately by phone AND in-app message
4. [ ] Assess whether any clinical action (treatment, medication, surgery) was taken based on wrong result
5. [ ] Notify Clinical Safety Officer

**Clinical Safety Assessment (0–4 hours):**
6. [ ] Determine if patient received harmful treatment based on wrong result
7. [ ] If harm occurred: activate PB-01 and clinical harm response in parallel
8. [ ] Locate correct sample / correct result for affected patient
9. [ ] Release corrected result with amendment note
10. [ ] Notify patient of correction

**Remediation:**
11. [ ] Identify root cause: mislabeled sample, system entry error, batch import error
12. [ ] Correct root cause in system and process
13. [ ] Review all results released in same batch for other errors
14. [ ] Add to lab QA report
15. [ ] Update RISK-003 in risk register

**Communication to Ordering Clinician:**
```
URGENT: Lab Result Correction

Patient: [PATIENT NAME] — Health ID: [HEALTH ID]
Result: [TEST NAME] — Released: [DATE/TIME]

The previously released result was incorrect. The correct value is [CORRECTED VALUE].

Please review any clinical actions taken and contact [CLINICAL SAFETY OFFICER] immediately.
```

---

## PB-04: QR Verification Leak

**Trigger:** A patient's QR code has been captured, shared, or used in an unauthorized context — exposing the patient's Health ID or allowing unauthorized facility access.

**Severity:** P1

**Incident Commander:** Security Officer + Privacy Officer

### Detection Sources
- Patient complaint: "Someone else has my QR code"
- Social media post containing QR code
- Abnormal scan pattern: same QR scanned at unfamiliar facility
- Security scan of public channels

### Response Steps

**Immediate (0–2 hours):**
1. [ ] Identify affected patient and QR token
2. [ ] Revoke current QR token — generate new QR for patient
3. [ ] Identify which facilities/users scanned the leaked QR
4. [ ] Assess what data was accessible via QR (QR must NEVER expose full EMR — confirm this holds)
5. [ ] Notify affected patient

**Assessment (2–8 hours):**
6. [ ] Review QR scan audit logs for unauthorized access
7. [ ] Determine how QR was leaked (screenshot, print, digital share)
8. [ ] Check if any unauthorized facility accessed patient data via QR
9. [ ] Assess privacy impact

**Remediation:**
10. [ ] If unauthorized clinical data access occurred: treat as data breach → activate PB-01
11. [ ] Remind facility staff: QR codes are for identity verification only — do NOT display in clinical notes or public screens
12. [ ] Update QR policy in staff training
13. [ ] Add rate limiting on QR scan endpoint if not already present

**Safety Confirmation:**
- QR code must never embed or return full medical data — only Health ID for lookup
- Full patient data requires authenticated session after QR scan

---

## PB-05: API Key Compromise

**Trigger:** An OpesCare API client credential (API key, OAuth client secret) has been leaked, stolen, or used from an unauthorized source.

**Severity:** P1

**Incident Commander:** Security Officer + Developer Relations (if third-party client)

### Detection Sources
- Developer reports leaked key
- Anomalous API usage: unusual volumes, unusual endpoints, unusual IP ranges
- API key found in public code repository
- Rate limit violation from unexpected source

### Response Steps

**Immediate (0–1 hour):**
1. [ ] Revoke compromised API key immediately — no grace period
2. [ ] Identify all requests made with compromised key: endpoints, data accessed, timestamps
3. [ ] Determine if patient data was accessed
4. [ ] Notify API client owner (developer/organization)
5. [ ] If patient data accessed: activate PB-01

**Assessment (1–4 hours):**
6. [ ] Review audit logs for compromised key usage
7. [ ] Identify root cause: leaked in code, transmitted insecurely, insider
8. [ ] Check for lateral movement: did attacker use key to access other credentials?
9. [ ] Assess scope of data exposure

**Remediation:**
10. [ ] Issue new API credentials to legitimate owner
11. [ ] Update API key rotation policy if key was old
12. [ ] Add monitoring rule for anomalous API usage patterns
13. [ ] If key found in public repo: request repo owner to remove and rotate git history
14. [ ] Require developer to complete security review before key reactivation

**Security Controls Reminder:**
- API keys must be stored in environment variables — never in source code
- API keys scoped to minimum necessary permissions
- Production API keys require `api.production.approve` (high-risk permission)

---

## PB-06: Bridge Agent Compromised

**Trigger:** An OpesCare Bridge Agent (HIS integration middleware) has been compromised — either the server running it, its credentials, or the messages it processes.

**Severity:** P1

**Incident Commander:** Security Officer + Technical Lead

### Detection Sources
- Bridge Agent sends anomalous data
- HIS reports unauthorized data requests
- Bridge Agent credential found in unauthorized location
- Connection from unexpected IP

### Response Steps

**Immediate (0–1 hour):**
1. [ ] Disable compromised Bridge Agent connection immediately
2. [ ] Revoke Bridge Agent API credentials
3. [ ] Identify all data transmitted through agent since compromise window
4. [ ] Notify connected HIS administrators
5. [ ] Assess if patient data was exfiltrated

**Assessment (1–4 hours):**
6. [ ] Review Bridge Agent audit logs
7. [ ] Identify compromise vector: server breach, credential leak, MITM
8. [ ] Assess data integrity: was any data modified in transit?
9. [ ] Identify affected patients (if any data was altered or accessed)

**Remediation:**
10. [ ] Rebuild Bridge Agent on clean server if server compromise confirmed
11. [ ] Rotate all credentials used by Bridge Agent
12. [ ] Verify data integrity of all records imported during compromise window
13. [ ] Re-establish connection with enhanced monitoring
14. [ ] Update Bridge Agent security hardening checklist

---

## PB-07: Ransomware / Server Compromise

**Trigger:** OpesCare production server(s) are compromised by ransomware, rootkit, or unauthorized persistent access.

**Severity:** P0

**Incident Commander:** Technical Lead + Security Officer (escalates to CEO immediately)

### Response Steps

**Immediate (0–30 minutes):**
1. [ ] Isolate compromised server(s) from network — cut internet access
2. [ ] Do NOT pay any ransom — document demand for law enforcement
3. [ ] Notify: CEO, Security Officer, Privacy Officer, Legal Counsel
4. [ ] Activate business continuity plan — switch to backup systems if available
5. [ ] Preserve forensic evidence: do NOT reboot or modify compromised systems
6. [ ] Notify law enforcement

**Assessment (30 min – 4 hours):**
7. [ ] Identify compromise vector: phishing, unpatched vulnerability, insider
8. [ ] Assess data impact: was database encrypted? Was data exfiltrated?
9. [ ] Identify backup integrity: last known good backup and its date
10. [ ] Determine recovery path

**Recovery:**
11. [ ] Restore from clean backup to clean infrastructure
12. [ ] Verify data integrity before bringing back online
13. [ ] Patch root cause vulnerability before restoration
14. [ ] Gradually restore services — monitoring each step
15. [ ] If patient data exfiltrated: activate PB-01

**Communication Template (to facilities):**
```
SYSTEM STATUS NOTICE

OpesCare is currently experiencing a critical system incident.
Access may be unavailable until [ESTIMATED TIME].

For emergencies, please use [FALLBACK PROCEDURE].
Updates will be sent every [INTERVAL].
```

---

## PB-08: Payment Failure

**Trigger:** Payment processing fails for one or more patients/facilities — invoices cannot be processed, payments not recorded, or refunds not issued.

**Severity:** P2 (P1 if affecting entire facility billing)

**Incident Commander:** Billing Officer / Technical Lead

### Detection Sources
- Patient reports payment not processed
- Cashier receives payment gateway error
- Billing reconciliation mismatch
- Payment provider notification

### Response Steps

**Immediate (0–2 hours):**
1. [ ] Confirm scope: single transaction, single facility, or platform-wide
2. [ ] Check payment gateway status page
3. [ ] Review error logs from payment processing service
4. [ ] Identify affected invoices and payment status

**Resolution:**
5. [ ] If gateway issue: wait for resolution, retry affected transactions
6. [ ] If system bug: hotfix and deploy
7. [ ] Reconcile all failed transactions manually
8. [ ] Issue receipts for payments that were actually processed
9. [ ] For any double-charges: issue refund (`billing.refund` high-risk permission — requires supervisor)

**Audit Requirement:**
- Every payment correction and refund must have audit log entry
- `billing.refund` requires explicit approval and documentation of reason
- Refund reversals reviewed by billing_officer within 24 hours

---

## PB-09: Webhook Failure

**Trigger:** OpesCare webhooks are failing to deliver — events not reaching subscribed endpoints, retries exhausted, or incorrect payloads delivered.

**Severity:** P2

**Incident Commander:** Developer Relations / Technical Lead

### Detection Sources
- Third-party integration reports missed events
- Webhook delivery log shows high failure rate
- Monitoring alert: webhook queue depth > threshold

### Response Steps

**Immediate (0–2 hours):**
1. [ ] Identify affected webhook subscriptions (specific endpoints or all)
2. [ ] Check recipient endpoint availability (their servers down?)
3. [ ] Review webhook delivery logs: error codes, response bodies
4. [ ] Pause automatic retries if endpoint returning permanent error (4xx)

**Resolution:**
5. [ ] If recipient endpoint issue: notify developer/organization
6. [ ] If OpesCare payload issue: identify affected event types and fix
7. [ ] Replay missed events (if within retention window and safe to replay)
8. [ ] Confirm replay with recipient — avoid duplicate processing
9. [ ] Document all replayed events in audit log

**Prevention:**
- Webhook secrets must be verified on each delivery
- Duplicate event protection: use event_id for idempotency
- Webhook retry policy: exponential backoff, max 5 retries, dead letter queue

---

## PB-10: Facility Downtime

**Trigger:** A facility loses access to OpesCare — whether due to network failure, platform outage, or facility-level configuration issue.

**Severity:** P1 (if emergency care facility), P2 (routine)

**Incident Commander:** Technical Lead + Facility Admin contact

### Detection Sources
- Facility reports cannot access system
- Monitoring alert: no requests from facility in X minutes
- Facility admin submits support ticket

### Response Steps

**Immediate (0–1 hour):**
1. [ ] Confirm scope: facility network, OpesCare platform, or specific feature
2. [ ] Check platform health dashboard — is it broader than one facility?
3. [ ] Contact facility admin to confirm their network is up
4. [ ] Check facility-specific configuration (subdomain, authentication)

**If Platform Issue:**
5. [ ] Escalate to Technical Lead — resolve platform issue
6. [ ] Communicate ETA to all affected facilities

**If Facility Network Issue:**
7. [ ] Guide facility admin to activate OpesCare Lite offline mode if available
8. [ ] Ensure Lite devices are charged and ready
9. [ ] Provide manual procedure fallback documentation

**If Configuration Issue:**
10. [ ] Support agent resolves with facility admin
11. [ ] Document fix in facility's support history

**Offline Fallback:**
- OpesCare Lite devices available for: patient registration, triage vitals, appointment check-in
- Full EMR, insurance claims, official documents unavailable offline (enforced by system)
- Staff should have printed offline emergency protocol

---

## PB-11: Public Health Report Error

**Trigger:** A public health report has been submitted to a national authority containing incorrect, incomplete, or improperly de-identified data.

**Severity:** P1

**Incident Commander:** Privacy Officer + Public Health Officer

### Detection Sources
- Public health authority reports data anomaly
- Internal data steward review finds error
- Automated validation failure on report generation

### Response Steps

**Immediate (0–4 hours):**
1. [ ] Confirm error nature: incorrect values, wrong de-identification, wrong reporting period
2. [ ] Preserve original submitted report
3. [ ] Identify affected data records and time window
4. [ ] Notify public health authority of potential error — do not wait for confirmation
5. [ ] Assess whether any patient-identifiable data was inadvertently included

**If Patient-Identifiable Data Submitted:**
6. [ ] Treat as data breach — activate PB-01
7. [ ] Request public health authority to quarantine submitted data
8. [ ] Notify Privacy Officer and Legal Counsel

**Correction:**
9. [ ] Generate corrected report
10. [ ] Have corrected report reviewed by public_health_officer and privacy_officer before resubmission
11. [ ] Submit correction with accompanying explanation letter
12. [ ] Document correction in audit log

**Prevention:**
- Public health reports must pass automated de-identification check before submission
- `public_health.patient_level_submit` is a high-risk permission — requires explicit confirmation
- Reports reviewed by privacy_officer before submission to authorities

---

## Post-Incident Requirements (All Playbooks)

After every P0 and P1 incident:

1. **Post-Incident Report** — completed within 5 business days:
   - Incident timeline
   - Root cause analysis (5 Whys)
   - Impact assessment (patients affected, data exposed, downtime duration)
   - Mitigations applied
   - Lessons learned
   - Changes to prevent recurrence

2. **Risk Register Update** — relevant risk record updated within 48 hours

3. **Retrospective Meeting** — attended by incident commander, security officer, technical lead, and relevant domain owners

4. **Regulatory Notification** — if required, complete within statutory time window (typically 72 hours for data breaches)

5. **Communication to Affected Parties** — patients, facilities, regulators, as applicable

---

## Incident Log Template

```
INCIDENT LOG

Incident ID: INC-YYYY-MM-DD-NNN
Playbook: PB-XX
Severity: P0 / P1 / P2 / P3
Status: Open / Contained / Resolved / Closed

Incident Commander: 
Opened: [TIMESTAMP]
Contained: [TIMESTAMP]
Resolved: [TIMESTAMP]

Summary:

Timeline:
- [TIMESTAMP] [ACTION/FINDING]
- [TIMESTAMP] [ACTION/FINDING]

Affected:
- Patients: [COUNT or N/A]
- Facilities: [LIST]
- Data types: [LIST]

Root Cause:

Mitigations Applied:

Open Actions:
- [ ] [ACTION] — Owner: [NAME] — Due: [DATE]

Regulatory Notification Required: Yes / No
  If Yes: Submitted: [DATE]

Post-Incident Report Due: [DATE]
```
