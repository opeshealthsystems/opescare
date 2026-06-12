# SOP-016 — Data Import & Migration

**SOP Number:** SOP-016 | **Version:** 1.0 | **Owner:** Data Manager / IT Lead

---

## 1. Key Rules
- Never silently overwrite existing records
- Always validate before import
- Always import with audit trail
- Facility authorisation required
- Test in staging first

## 2. Procedure
1. Prepare import file (CSV/JSON per OpesCare template). Required: first_name, last_name, DOB, gender, phone, facility_id.
2. Upload to staging and review validation report. Correct errors.
3. Facility manager signs off on validated import.
4. Upload authorised file via OpesCare > Admin > Data Import.
5. System processes in background with duplicate detection.
6. Review import report; spot-check 10% of imported records; resolve flagged errors.

## 3. Related SOPs
SOP-001 Patient Registration
