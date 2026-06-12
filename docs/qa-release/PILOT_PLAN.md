# OpesCare Pilot Plan

**Version:** 1.0 | **Last Updated:** 2026-05

---

## 1. Pilot Objectives

1. Validate core clinical workflows in a real facility environment.
2. Identify gaps between designed and actual user workflows.
3. Measure performance under real-world load.
4. Build evidence base for scale-out and regulatory conversations.
5. Train first cohort of OpesCare champions.

---

## 2. Pilot Scope

### Included in Pilot
- Patient registration and Health ID
- Appointment booking and queue management
- Basic consultation (vitals, SOAP note)
- Prescription and pharmacy dispensing
- Laboratory requests and results
- Billing and payments (cash, POS)
- CDSS alerts (allergy, drug interaction, lab)
- Document QR generation and verification
- Patient portal (read-only access to own records)

### Excluded from Pilot (Phase 2)
- Insurance claims submission (manual process in parallel)
- Telemedicine
- OpesCare Lite offline sync
- Full FHIR interoperability
- Multi-facility federation

---

## 3. Pilot Sites

| Site | Type | Patients/Day | Go-Live |
|------|------|-------------|---------|
| Pilot Site A | General hospital (50 beds) | 50–100 | Week 1 |
| Pilot Site B | Clinic (OPD only) | 20–40 | Week 3 |
| Pilot Site C | Pharmacy only | 30–50 | Week 5 |

---

## 4. Pilot Timeline

| Week | Activity |
|------|----------|
| -4 | Facility assessment, infrastructure setup |
| -3 | Data migration from legacy system |
| -2 | Staff training (clinical + admin) |
| -1 | UAT in staging with facility staff |
| W1 | Go-live Pilot Site A with on-site support |
| W2 | Daily check-ins, issue resolution |
| W3 | Go-live Pilot Site B |
| W4 | Mid-pilot review, performance report |
| W5 | Go-live Pilot Site C |
| W6–W8 | Full operations with weekly check-in |
| W8 | End-of-pilot assessment |
| W9 | Pilot report and scale-out decision |

---

## 5. Pilot Success Metrics

| Metric | Target |
|--------|--------|
| Patient registration time | < 5 minutes |
| Health ID lookup time | < 30 seconds |
| System uptime | > 99.5% during pilot |
| Staff adoption rate | > 80% of staff using OpesCare daily by W4 |
| Data quality score | > 90% records with phone + DOB |
| CDSS alert override rate | < 40% |
| Support ticket resolution | > 90% resolved within SLA |
| Patient satisfaction | > 4/5 in exit survey |
| Clinician satisfaction | > 4/5 in exit survey |

---

## 6. Risk Management

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Internet connectivity issues | High | High | OpesCare Lite offline mode (Phase 2); local caching |
| Staff resistance to change | Medium | High | Champion training, visible management support |
| Data migration errors | Medium | High | Staged migration, spot-check validation |
| Performance under load | Low | High | Load testing before go-live |
| Patient data breach | Low | Critical | Security hardening, staff training on SOP-004 |

---

## 7. Support During Pilot

- Dedicated on-site support: Week 1 (Site A), Week 3 (Site B)
- Dedicated WhatsApp support channel per site
- Escalation: product team reachable within 2 hours (business hours)
- Weekly retrospective call with facility management

---

## 8. Exit Criteria (Pilot to Full Rollout)

All of the following must be true:

- [ ] No critical (P1) bugs open for > 48 hours
- [ ] > 80% staff daily active usage
- [ ] System uptime > 99.5% across pilot period
- [ ] Data quality score > 85%
- [ ] Positive clinical lead endorsement
- [ ] Facility manager sign-off
- [ ] No unresolved data integrity issues
