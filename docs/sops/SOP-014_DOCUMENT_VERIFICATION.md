# SOP-014 — Document Verification (QR Code)

**SOP Number:** SOP-014 | **Version:** 1.0 | **Owner:** Any authorised verifier

---

## 1. Purpose
Verify authenticity of OpesCare-issued documents (prescriptions, lab results, discharge summaries, referrals).

## 2. Procedure
1. Scan QR code on document: `https://verify.opescare.com/doc/{token}`
2. Confirm patient name matches patient presenting.
3. Confirm document type, issuing facility, issuing clinician.
4. Confirm document not revoked.

## 3. Document Status Codes
- **Valid** — authentic and current
- **Revoked** — cancelled by issuing facility
- **Expired** — past validity period
- **Not Found** — not in OpesCare system (potential fraud)

## 4. Fraud Action
Do not proceed. Retain document if possible. Contact OpesCare support and issuing facility. Document incident.
