# OpesCare QA Checklist

**Version:** 1.0 | **Last Updated:** 2026-05

---

## 1. Core Modules

### Patient Identity & Registration
- [ ] New patient registration creates unique Health ID
- [ ] Duplicate detection prevents exact duplicates
- [ ] NHIS/HMO number stored correctly
- [ ] Patient QR code scannable and returns correct patient
- [ ] Consent recorded at registration
- [ ] Patient portal access works for registered patient

### Appointments
- [ ] Appointment can be booked for existing patient
- [ ] Scheduling conflict detection works
- [ ] SMS/email confirmation sent
- [ ] Reminder sent 24 hours before
- [ ] DNA (Did Not Attend) status works
- [ ] Appointment cancellation and reschedule works

### Queue & Patient Flow
- [ ] Walk-in patient added to queue
- [ ] Priority levels (P1–P5) assigned correctly
- [ ] Queue display screen shows waiting patients
- [ ] Patient called and moved to "in service"
- [ ] Visit completed → queue entry completed

### Clinical (EMR)
- [ ] SOAP note saves correctly
- [ ] Vitals recorded
- [ ] Lab request issued
- [ ] Prescription issued
- [ ] CDSS allergy alert fires for known allergen
- [ ] CDSS drug interaction alert fires
- [ ] Override requires reason (min 10 chars)
- [ ] Override logged in audit trail

### Pharmacy
- [ ] Prescription appears in pharmacy queue
- [ ] Dispensing records batch number + expiry
- [ ] Inventory decremented on dispense
- [ ] Patient counselling notes saved

### Laboratory
- [ ] Lab request appears in lab worklist
- [ ] Result entered and returned to clinician
- [ ] Critical value triggers alert
- [ ] CDSS lab alert fires for critical value

### Billing
- [ ] Invoice auto-generated from services
- [ ] Payment recorded (cash + POS + transfer)
- [ ] Receipt issued
- [ ] NHIS claim flow works
- [ ] Invoice marked paid and locked

### Insurance
- [ ] Eligibility check works
- [ ] Preauthorisation submitted
- [ ] Claim submitted with documents attached
- [ ] Claim status updated on payment

### Ward & Admissions
- [ ] Patient admitted to ward/bed
- [ ] Bed status updated to occupied
- [ ] Transfer between beds/wards works
- [ ] Discharge process works
- [ ] Length of stay calculated correctly

---

## 2. Data Governance & Compliance

- [ ] Patient consent can be given and revoked
- [ ] Record correction request workflow works
- [ ] Data export request produces downloadable file
- [ ] Emergency access override is audited
- [ ] Audit log shows all critical events
- [ ] Patient portal shows patient their own data only
- [ ] Another patient's data cannot be accessed via URL manipulation

---

## 3. Performance

- [ ] Homepage loads < 1 second
- [ ] Patient search returns results < 500ms
- [ ] Dashboard loads < 2 seconds
- [ ] 10 concurrent users: no degradation
- [ ] Queue display updates in < 5 seconds

---

## 4. Security

- [ ] Unauthenticated access returns 401 on all protected routes
- [ ] CSRF token required on all POST/PUT/DELETE
- [ ] XSS attempt in patient name field is escaped
- [ ] SQL injection attempt returns error, not data
- [ ] Rate limiting blocks excessive login attempts
- [ ] Patient A cannot access Patient B records
- [ ] Staff role cannot access admin routes

---

## 5. Cross-Browser

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Chrome (Android)
- [ ] Mobile Safari (iOS)

---

## 6. OpesCare Lite

- [ ] Lite dashboard loads on mobile device
- [ ] Health ID lookup works
- [ ] Patient registration via Lite form
- [ ] Queue check-in via Lite
- [ ] Offline indicator shows correctly
- [ ] Device registration API works
- [ ] Sync push accepts offline events
- [ ] Blocked offline actions rejected

---

## Sign-off

| Tested By | Date | Result |
|-----------|------|--------|
| QA Engineer | | |
| Clinical Lead | | |
| Security Lead | | |
| Product Owner | | |
