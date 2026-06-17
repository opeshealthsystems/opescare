# OpesCare — Marketing Context (V1 draft)

> Auto-drafted from the codebase on 2026-06-17. Confidence tags: 🟢 verified in code/landing · 🟡 inferred · 🔴 assumption — needs your confirmation.

## 1. Product Overview
- **One-liner** 🟢: One consent-based digital Health ID that connects patients, hospitals, clinics, labs, pharmacies, insurers, and public-health systems — anywhere care happens.
- **What it does** 🟢: Gives each patient a single trusted Health ID (`CM-HID-XXXX-XXXX-XXXX`) and longitudinal medical history. Approved providers access the right records at the right time, every sensitive action is consent-gated and audited, and emergency access is controlled. Interoperates with existing hospital systems via the Connect API/SDK and FHIR.
- **Category (shelf)** 🟡: Health Information Exchange / national digital health identity & interoperability platform.
- **Product type** 🟢: Multi-sided B2B + B2C SaaS platform (web portals + mobile app + partner API).
- **Business model** 🟢: Subscriptions — patients (Free "Essentiel" / Premium "Santé+"), and organizations (health facilities, insurers, labs, pharmacies, health orgs, developers/API). Payments in **XAF/FCFA** via **MTN MoMo + Orange Money**. 🔴 Final price points TBD.
- **Geography** 🟢: Cameroon first (CM health IDs, EN/FR bilingual, MINSANTE reporting), built for Cameroon's 2026–2030 health digitalization plan.

## 2. Target Audience (multi-sided)
🟢 Six named audiences (each has a Solutions page + portal):
1. **Patients & families** — own their records, book care, share via consent, family/dependent sharing.
2. **Hospitals & clinics** — unified patient records, staff/ward/queue/billing, interoperability.
3. **Laboratories** — orders, results, sample tracking.
4. **Pharmacies** — prescriptions, controlled substances, inventory.
5. **Insurers** — claims, pre-auth, policies, provider network.
6. **Public-health orgs / MINSANTE** — outreach, programs, notifiable-disease reporting, signals.
Plus **developers/integration partners** (Connect API + SDK).

## 3. Personas (buying roles) 🟡
- **Patient (user/financial buyer)** — wants their history in one place, control over who sees it, no more lost paper hospital books.
- **Hospital admin / medical director (decision maker)** — wants interoperability, compliance, operational efficiency without ripping out existing systems.
- **Facility IT / integration lead (technical influencer)** — cares about the Connect API, FHIR, SDK, data security.
- **Insurer claims/ops lead** — wants faster, consent-verified claims and pre-auth.
- **Ministry / public-health official** — wants national coverage, reporting, governance, auditability.

## 4. Problems & Pain Points 🟢/🟡
- Patients depend on **paper hospital books and scattered records**; history is lost between facilities.
- Providers lack the **right information at the right time**; no trusted cross-facility identity.
- Sensitive access is **uncontrolled / unaudited**; consent is informal.
- Existing systems **don't interoperate**; data is siloed per facility.
- Cost of failure: misdiagnosis, repeated tests, delayed care, fraud, no national health data.

## 5. Competitive Landscape 🔴 (needs your input)
- **Direct**: other national HIE / health-ID initiatives, hospital EMR vendors with patient portals. (List the real ones you face in Cameroon/CEMAC.)
- **Secondary**: standalone EMRs, insurer portals, paper + WhatsApp workflows.
- **Indirect**: status quo (paper hospital books), do-nothing.

## 6. Differentiation 🟢/🟡
- **Consent-first, audited access** — patient approves every request; emergency access is logged.
- **One identity across all actors** — patients, hospitals, labs, pharmacies, insurers, public health on one rail.
- **Interoperable, not rip-and-replace** — Connect API/SDK + FHIR work with existing hospital systems.
- **Built for the market** — EN/FR, XAF, MoMo/Orange, MINSANTE reporting, offline-aware mobile.
- **Multi-sided network effect** — each connected actor makes the Health ID more valuable.

## 7. Objections & Anti-Personas 🔴 (confirm)
- "Is my data safe?" → consent gating, encryption, audit trail, hashed tokens.
- "Will it work with our current system?" → Connect API/SDK + FHIR, additive.
- "Patients here won't pay / have no smartphones" → Free tier, USSD, offline card, family payer.
- **Anti-persona**: facilities unwilling to digitize at all; one-off clinics with no referral flow.

## 8. Switching Forces (JTBD) 🟡
- **Push**: lost records, repeated tests, no referral continuity, fraud, manual reporting.
- **Pull**: one Health ID, consent control, interoperability, faster claims.
- **Habit**: paper books, per-facility silos, WhatsApp.
- **Anxiety**: data security, change management, cost, connectivity.

## 9. Customer Language 🔴 (need verbatim quotes from real users)
- Likely problem phrasing: "my hospital book," "they can't find my file," "I had to repeat the tests."
- Use: Health ID, consent, your records, anywhere care happens. Avoid: jargon-heavy "HIE/interoperability" in patient-facing copy.

## 10. Brand Voice 🟡
- **Tone**: trustworthy, institutional, reassuring, clear. Not hypey.
- **Personality**: secure, connected, respectful, modern, locally rooted.
- **Bilingual** EN/FR, professional. Brand color **#0F4C81** (never purple). Lucide icons, never emoji in product UI.

## 11–12. Style & Proof Points
- Style 🟢: EN/FR parity, XAF/FCFA, "Health ID" capitalized, brand blue.
- Proof points 🔴: **pre-launch — no customer logos/testimonials/metrics yet.** Need pilot facility results once live.

## 13. Content & SEO 🔴
- Likely clusters: "digital health ID Cameroon," "dossier médical électronique," "hospital interoperability Cameroon," "MINSANTE reporting," "Mobile Money health payment." Confirm priority keywords.

## 14. Goals 🔴 (confirm)
- Primary business goal: ? (e.g., N facilities onboarded, N patient Health IDs issued in 2026).
- Key conversion actions 🟢: patient "Create Health ID" (signup), facility "Request Partnership Demo," developer API onboarding, **subscription upgrade**.

---

## Biggest gaps to close (your input needed)
1. **Final pricing** for each audience (patient Premium, facility tiers, insurer/lab/pharmacy/dev).
2. **Real competitors** in Cameroon/CEMAC.
3. **Primary 2026 goal + target numbers** (the north-star metric).
4. **Proof points** once the first pilot facility is live.
