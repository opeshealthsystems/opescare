# OpesCare Role Permission Matrix

**Version:** 1.0  
**Status:** Active  
**Last Updated:** 2026-05-19  
**Owner:** Security & Compliance Team

---

## Purpose

The Role Permission Matrix prevents dangerous access mistakes. No user, API client, or integration should receive permissions that are not explicitly granted in this matrix. Every role assignment must reference this document, and all RBAC enforcement must be tested.

**Non-negotiable rule:** Roles must not receive permissions without a matrix entry and corresponding test coverage.

---

## 1. Core Roles

| Role | Category | Description |
|------|----------|-------------|
| `patient` | B2C | Patient managing their own health records |
| `guardian` | B2C | Parent/guardian managing dependent records |
| `receptionist` | Clinical Staff | Front-desk registration, appointments, check-in |
| `doctor` | Clinical Staff | Full clinical consultation authority |
| `nurse` | Clinical Staff | Triage, vitals, nursing care |
| `student_doctor` | Clinical (Supervised) | Supervised consultation, no final authority |
| `student_nurse` | Clinical (Supervised) | Supervised vitals/notes, no finalization |
| `lab_technician` | Laboratory | Lab sample workflow, draft results |
| `lab_manager` | Laboratory | Validate and release lab results |
| `pharmacist` | Pharmacy | Verify prescriptions, dispense |
| `pharmacy_manager` | Pharmacy | Stock, staff, dispensing oversight |
| `cashier` | Billing | Invoices, payments, receipts |
| `billing_officer` | Billing | Billing and insurance preparation |
| `facility_admin` | Administration | Facility setup, staff, roles, reports |
| `hospital_director` | Administration | Dashboards, reports, facility oversight |
| `insurance_reviewer` | Insurance | Claim review (minimum necessary data) |
| `insurance_admin` | Insurance | Insurer config, users, plans |
| `public_health_officer` | Public Health | Aggregate reports, approved public health data |
| `developer` | Technical | API apps, sandbox, docs, webhooks |
| `support_agent` | Support | Helpdesk, technical troubleshooting |
| `privacy_officer` | Governance | Access reviews, privacy complaints |
| `security_officer` | Governance | Security incidents, audit logs |
| `data_steward` | Governance | Data reconciliation, duplicate review |
| `super_admin` | Platform | Full platform configuration (audit-bound) |

---

## 2. Permission Families

| Family | Covers |
|--------|--------|
| `patients` | Patient profile, Health ID, registration, search |
| `health_id` | Health ID issuance, QR, verification |
| `consent` | Consent requests, grants, revocations |
| `appointments` | Booking, rescheduling, cancellation |
| `queue` | Queue management, triage assignments |
| `triage` | Triage records, acuity assessment |
| `encounters` | Visit records, clinical notes, diagnoses |
| `labs` | Lab orders, sample workflow, results |
| `prescriptions` | Prescription issuance, dispensing |
| `pharmacy` | Stock, dispensing workflow |
| `billing` | Invoices, payments, refunds, receipts |
| `insurance` | Claims, pre-auth, insurer config |
| `documents` | Official documents, templates, verification |
| `public_health` | Public health reports, aggregate data |
| `care_map` | Facility/lab/pharmacy maps |
| `academy` | Training modules, certifications |
| `support` | Helpdesk tickets |
| `imports` | Data import jobs |
| `analytics` | Dashboards, reports |
| `audit` | Audit logs, access logs |
| `security` | Security events, incidents |
| `developer_api` | API clients, credentials, sandbox |
| `webhooks` | Webhook subscriptions |
| `bridge_agent` | Bridge Agent connections |
| `lite` | OpesCare Lite device management |
| `admin` | Platform administration |

---

## 3. Role Permission Matrix

| Role | Key Allowed Actions | Explicitly Blocked |
|------|---------------------|-------------------|
| **patient** | View own profile, Health ID, records, documents, appointments, consent requests, messages, lab results, prescriptions | View other patients' data, edit clinical records, approve claims, access admin |
| **guardian** | Manage dependent records where relationship is authorized, view dependent appointments and consents | Access adult patient without valid relationship/consent, edit clinical records |
| **receptionist** | Register patient, create/manage appointments, check-in, queue management, basic billing | Clinical notes, prescriptions, lab validation, financial refunds |
| **doctor** | Full authorized EMR, consultation notes, prescriptions, lab orders, referrals, diagnoses, triage review | Unrelated facility records, billing refunds, platform admin |
| **nurse** | Triage, vitals, nursing notes, queue actions, patient check-in | Final diagnosis authority (unless facility grants), billing refunds, lab final release |
| **student_doctor** | Supervised notes, learning workflows under attending | Final prescriptions, final diagnosis, lab validation, direct patient contact without supervision |
| **student_nurse** | Supervised vitals and notes | Emergency access without supervisor, clinical finalization |
| **lab_technician** | Receive lab orders, sample collection, draft results, sample tracking | Final result release (requires lab_manager), full EMR access |
| **lab_manager** | Validate and release lab results, manage lab catalog, lab staff oversight | Unrelated patient clinical records |
| **pharmacist** | Verify prescriptions, dispense medications, stock depletion | Full EMR access, billing refunds |
| **pharmacy_manager** | Manage pharmacy stock, staff assignments, dispensing oversight, supplier orders | Unrelated clinical notes |
| **cashier** | Create invoices, process payments, generate receipts | Clinical records beyond billing minimum, refund approvals |
| **billing_officer** | Full billing module, insurance claim preparation | Clinical timeline beyond minimum necessary |
| **facility_admin** | Facility profile, staff accounts, role assignments, service config, go-live checklist | Unrestricted patient browsing, direct clinical data editing |
| **hospital_director** | Dashboards, reports, facility-level oversight, go-live approval | Full EMR access by default (must log emergency access reason) |
| **insurance_reviewer** | Claim review with minimum necessary clinical data, claim status updates | Full EMR, unrelated records, patient contact details beyond claim need |
| **insurance_admin** | Insurer user management, plan configuration, insurer analytics | Patient medical records outside claims |
| **public_health_officer** | Aggregate public health reports, approved indicator submission | Full EMR by default, patient-level identifiable data |
| **developer** | API client management, sandbox access, documentation, webhook subscriptions | Patient identifiable data unless approved production scopes granted |
| **support_agent** | Support tickets, technical troubleshooting logs, facility contact | Patient records unless granted emergency access with audit |
| **privacy_officer** | Access review logs, privacy complaints, data subject requests, consent audit | Billing edits unless separately granted |
| **security_officer** | Security incidents, audit logs, breach response workflows | Clinical note editing |
| **data_steward** | Data reconciliation, duplicate record review, import quality review | Clinical finalization, direct patient care |
| **super_admin** | Full platform configuration, role management, system settings | Should never browse patient data without documented reason and audit trail |

---

## 4. High-Risk Permissions

The following permissions require explicit confirmation on assignment, mandatory audit logging on every use, and are subject to regular access review:

```
patients.view_full                — Read complete patient clinical history
emergency_access.use              — Break-glass access to any patient record
documents.revoke                  — Revoke issued official documents
lab_results.release               — Release final lab results to patient/EMR
prescriptions.issue               — Issue prescriptions (doctor authority)
billing.refund                    — Issue payment refunds
insurance.claims.decide           — Approve or reject insurance claims
public_health.patient_level_submit — Submit identifiable data to public health
audit.view_sensitive              — Access sensitive audit logs
admin.platform.manage             — Modify platform-level settings
api.production.approve            — Approve API client for production access
```

---

## 5. Permission Assignment Flow

```
1. Admin selects user.
2. Admin selects facility/organization context.
3. Admin selects role from this matrix.
4. System displays the permissions the role grants.
5. High-risk permissions (§4) trigger a confirmation dialog.
6. Role is assigned.
7. An AuditEvent is created: action='role_assigned', target=user, actor=admin.
8. Automated tests verify access boundaries for the role.
```

---

## 6. Patient Data Access Minimum Necessary Principle

All roles accessing patient data must adhere to the **Minimum Necessary** principle:

| Access Tier | What is Accessible | Roles |
|-------------|-------------------|-------|
| **Full EMR** | Complete clinical history, all visits, all records | doctor, nurse (restricted), emergency_access |
| **Clinical Summary** | Active problems, allergies, medications, recent visits | nurse, lab_technician, pharmacist |
| **Billing Minimum** | Name, Health ID, insurance, invoice history | cashier, billing_officer, insurance_reviewer |
| **Demographics Only** | Name, DOB, contact details | receptionist, support_agent |
| **Own Records Only** | Own profile, documents, appointments | patient, guardian |
| **Aggregate Only** | No individual records, anonymized/aggregated | public_health_officer, developer (sandbox) |

---

## 7. Emergency Access (Break-Glass)

Break-glass access allows any authenticated clinical user to access a patient record in an emergency, bypassing normal consent/facility restrictions.

**Requirements:**
- Emergency reason must be provided before access is granted.
- All break-glass accesses are logged in `emergency_access_logs` with user, timestamp, reason, and patient.
- Patient is notified of the access within 24 hours (configurable).
- Privacy officer can review all break-glass events.
- Abuse of break-glass access is a disciplinary matter.

---

## 8. CDSS Safety Boundary

Clinical Decision Support System (CDSS) outputs:
- Are **decision-support tools only** — not diagnostic or prescriptive authority.
- Must display: *"Clinical alerts are decision-support tools only. They do not replace professional clinical judgment."*
- Must never appear to override a clinician's documented decision.
- Must never be visible to unauthenticated or unauthorized users.

---

## 9. Offline Access Restrictions (OpesCare Lite)

The following actions are **always blocked offline** regardless of role:

```
full_emr_access               — Requires live connectivity
insurance_claim_submission    — Requires live server validation
public_health_submission      — Requires live reporting infrastructure
final_document_issuance       — Requires digital signature infrastructure
broad_patient_search          — Prevented offline to protect data privacy
non_emergency_consent_expansion — Requires real-time consent server
```

---

## 10. Role Review Schedule

| Frequency | Review Action |
|-----------|---------------|
| Quarterly | Review all super_admin and privacy_officer assignments |
| Semi-annually | Review all high-risk permission holders |
| Annually | Full role matrix review against updated compliance requirements |
| On incident | Immediate review of roles related to the security/privacy incident |
| On staff departure | Immediate revocation of all facility and platform roles |
