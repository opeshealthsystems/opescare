# 03 — Screen blueprints (exact)

Each screen below maps to its existing Dart file and lists its composition top→bottom using the `02` components. Open the matching phone in `design-preview.html` and match exactly. All screens: `Scaffold(backgroundColor: bg)`, app bar = `surface` with `AppType.appBarTitle`, body scrollable, screen h-padding 16.

## Navigation (resolve first)
The preview shows a **4-tab** bottom nav whose set shifts per screen. Canonical tabs to confirm with product: **Home · Health ID · Insurance · Profile** (Family and Care Map appear as active tabs on their own mockups — decide whether they live in the bottom nav or are pushed routes). Active tab gets primary icon+label+dot (`AppBottomNav`). File: `lib/core/router/...` shell + `main_shell.dart`.

---

## 1. Health ID — `features/health_id/presentation/health_id_screen.dart`
App bar "My Health ID" + trailing share-2 & more-vertical icon boxes (36×36 `primary50` radius md).
Body: `HealthIdCard` → `HealthIdGenerateQrButton` (gradient, qr-code icon, "Generate Temporary QR" 14/w700 + "Valid for 10 minutes · Scan at reception" 11/white@65%, trailing chevron) → `QuickActions` (4: Lab Results, Appointments, Prescriptions, Consent) → `PhoneSectionHeader("Recent Access Logs", seeAll)` → `AppListRow`×(hospital "Hôpital Central Yaoundé / Viewed CBC results · Today, 10:24" → green "Allowed"; user-check "Dr. A. Ngouo / Accessed profile · Yesterday, 14:07" → green "Allowed").

## 2. Home — `features/home/presentation/home_screen.dart`
App bar: left two-line ("Good morning," 12/`text3`/w500 + "Jean-Pierre 👋" `appBarTitle`); right bell icon box with red dot badge.
Body: `HealthIdBanner` → `StatCard` row (3: "3 Active Rx", "12 Lab Results", "2 Upcoming") → `PhoneSectionHeader("Quick Actions")` → `QuickActions` (Book Appt, Lab Results, Insurance, Documents) → `PhoneSectionHeader("Upcoming", seeAll)` → `AppListRow`×(calendar "Cardiology Follow-up / CHHUY · 18 Jun · 10:30" → blue "Confirmed"; flask amber "CBC Results Ready / Hôpital Central · 2 days ago" → amber "Review") → **consent banner** (infoBg, 1px `primary200`, radius lg, padding 12×14: shield icon + "Consent Request Pending" 13/w700 `#1E40AF` + "Hôpital Central wants access to your lab results" 11/`#3B82F6` + "Review →" 12/w700 primary).

## 3. Insurance — `features/insurance/presentation/insurance_screen.dart`
App bar "Health Insurance" + "Browse Plans" primary50 pill (12/w700 primary).
Body: **Active policy banner** (`primary50`, 1.5px `primary200`, radius lg, padding 14): shield + "Active Policy" 13/w700 primary + green "Active" pill (right); plan name 15/w700; insurer 12/`text2`; footer (top border `primary200`) two cols → "POLICY NO." labelTiny + mono value; "VALID UNTIL" + mono date.
`PhoneSectionHeader("Available Plans", "34 plans · 15 insurers")` → provider group header (36 logo box + "AXA Cameroun" 13/w700 + "3 plans available" 11/`text3`) → **plan card** (1.5px `divider`, radius lg, padding 14): name 15/w700 + "PRIVATE" tag (primary50/primary, 10/w700); desc 12/`text2`; price row (Monthly `primary50` box: "Monthly" 10/`text3` + `XAF 14,000` monoPrice primary | Annual `surfaceMuted` box: mono `text2`); features row ("✓ Cashless" success/w700, "20% co-pay" `text3`); full-width primary CTA "Enroll in this Plan" (shield icon).
(Marketplace + plan detail screens: `insurance_marketplace_screen.dart`, `insurance_plan_detail_screen.dart` — same card system.)

## 4. Family — `features/family/presentation/family_screen.dart`
App bar "My Family" + user-plus icon box.
Body: "PRIMARY ACCOUNT" labelTiny → **primary card** (gradient, radius lg, padding 14×16, blue shadow): avatar 40 white@20% "J" + name 14/w700 white + mono id 10/white@65% + "You" green-on-glass pill. → "FAMILY MEMBERS" labelTiny + "Manage" (12/w600 primary, right) → `FamilyCard`×(Marie Mbarga / "Spouse · DOB 14 Jun 1990" / chips: rx "2 Active Rx", appt "Appt 20 Jun"; Ethan / "Child · DOB 3 Jan 2015 · Age 11" / chips alert "Vaccination due", ok "Labs OK"; pending dashed "+237 699 ···" / "Invite pending · sent 2 days ago" / amber "Pending") → **Add CTA** (`primary50`, 1.5px dashed `primary200`, radius lg, padding 13: user-plus + "Add or Invite a Family Member" 13/w700 primary).

## 5. Add Family Member — `features/family/presentation/add_family_member_screen.dart`
App bar back + "Add Family Member". `SegmentedToggle` (New Member | Link Account). Fields (`AppInput`): Full Name; row[Date of Birth | Sex select]; row[Relationship select | Blood Group select]; Emergency Contact Phone. **Consent notice** (infoBg, 1px `primary200`, radius md, padding 10×12: info icon + "A Health ID will be generated for this member…" 11/`#1E40AF`). Full-width gradient CTA "Add Sophie to Family" (user-plus icon).

## 6. Invite Family Member — `features/family/presentation/invite_family_member_screen.dart`
App bar back + "Invite Family Member". `SegmentedToggle` 3-up (Phone | Email | QR Code). Phone field with country box (🇨🇲 + chevron, 64 wide) + number. Relationship select. `AccessLevelSelector` (View Only selected / Guardian / Full). Full-width gradient CTA "Send Invite" (send icon). "Pending Invites" header + `AppListRow` (amber user icon "+237 699 ··· / Sent 2 days ago · Expires in 5 days" → amber "Pending").

## 7. Care Map — `features/care_map/presentation/care_map_screen.dart`
App bar "Care Map" + search icon box. **Map area** 140 tall (gradient `#EFF6FF`→`#DBEAFE`, grid + road lines, colored teardrop pins 🏥/💊/🔬, blue "my location" dot, "Yaoundé, CM" 9/`text3` bottom-right) — use the real map widget if available, else this placeholder. `FilterChips` (All•Hospitals•Clinics•Pharmacies•Labs•Emergency). `FacilityCard`×(Hôpital Central Yaoundé / "General · Your assigned hospital" / ok-chip "Connected" + "★ 4.3" / 1.2 km; Clinique de l'Espoir / "Clinic · Cardiology, Internal Med" / "★ 4.7 · Open now" / 2.8 km; Pharmacie du Lac / "Pharmacy · 24h open" / "Open · Closes midnight" success / 0.6 km).

## 8. Care Plans — `features/care_plans/presentation/care_plans_screen.dart` (+ detail)
App bar "Care Plans" + filter icon. Status tabs (Active [count 2] | Completed | All) using `AppTabBar`. `CarePlanCard`×(Hypertension Management / "Dr. A. Ngouo · Started 1 Mar 2026" / green "Active" / goal pills 💊🩺🥗 / 4-6 progress 66% / "Next check-in: 18 Jun 2026"; Diabetes Type 2 Control / "Dr. M. Fon · Started 10 Jan 2026" / blue "Active" / pills 💉🏃 / 2-5 progress 40% / footer HbA1c 7.4% warning mono + "View Labs →").

## 9. Login — `features/auth/presentation/login_screen.dart`
Centered auth logo (52 `primary50` rounded-14 heart icon + "OpesCare" 20/w800 primary + "Welcome back" 18/w800 + "Sign in with your phone number or email" 13/`text2`). `SegmentedToggle` (Phone | Email). Phone field (country box + number) OR email. PIN field (password, ls .4em) with "Forgot PIN?" link. Gradient CTA "Sign In". "New to OpesCare? Create account →". Footer terms box (`surfaceMuted`, 10/`text3`, links primary).

## 10. OTP — `features/auth/presentation/otp_screen.dart`
Back chevron. Auth logo (green `#D1FAE5` box, phone icon `#059669`) + "Enter your code" + "We sent a 6-digit code to +237 699 ·· ·· 00". `OtpBoxes` (3 filled "3 8 4", cursor, 2 empty). Gradient CTA "Verify Code". "Didn't receive it? Resend in 0:42" (`text3`). Info note (infoBg, 1px `primary200`: "Your code expires in 10 minutes for security." 11/`#1E40AF`).

## 11. Register — (new: `features/auth/presentation/register_screen.dart`)
Header: back + "Create Health Profile" + "1 of 3". 3-segment progress bar (active `primary`, rest `divider`). "PERSONAL INFORMATION" labelTiny. Fields: First Name; Last Name; row[Date of Birth | Sex]; row[Blood Group | Country]. Gradient CTA "Continue — Step 2 →". (Steps 2/3 follow same pattern.)

## 12. Profile — `features/profile/presentation/profile_screen.dart`
App bar "My Profile" + edit (pencil) icon box. **Header card** (gradient, radius 16, padding 18, blue shadow): avatar 52 "J" + name 16/w800 white + mono id 10/white@65% + badges ("✓ VERIFIED" green-glass, "Active" white-glass). Grouped info card "PERSONAL INFORMATION" (`surface`, 1px `divider`, radius lg): rows Date of Birth / Sex / Blood Group / Phone (each = sub label 12/`text3` + value 13/w600). Grouped card "EMERGENCY CONTACT": Marie Mbarga — Spouse / +237 699 111 222.

## 13. Edit Profile — `features/profile/presentation/edit_profile_screen.dart`
App bar back + "Edit Profile" + "Save" primary pill. Groups (labelTiny headers): Personal (row[First|Last], Date of Birth ✎ with primary border = edited, row[Sex|Blood Group]); Contact (Phone, Email); Emergency Contact (Contact Name, row[Phone|Relation]). All `AppInput` 13px.

## 14. Settings — `features/settings/presentation/settings_screen.dart`
App bar "Settings". Section "NOTIFICATIONS" → grouped rows with 36 icon boxes + `AppSwitch` (Push Notifications on, Consent Alerts on). Section "PRIVACY & DATA" → tappable rows w/ chevron (Access Logs green, Export My Records blue, Delete Account red text). **Sign Out** button (`dangerBg`, 1px danger@20%, radius lg, log-out icon + "Sign Out" 14/w700 danger).

## 15. Lab Results list — `features/labs/presentation/labs_screen.dart`
App bar "Lab Results" + search. **Critical banner** (`dangerBg`, 1px danger@25%, radius lg: alert-circle danger + "1 Critical Value Requires Review" 12/w700 `#991B1B` + "WBC 13.2 — above normal range" 10/danger). `AppTabBar` (All [12] | Recent | Critical | Pending). `AppListRow`×results: e.g. flask red icon "Complete Blood Count / Hôpital Central · 2 Jun 2026" → red "Critical" pill + mono "13.2 ↑" 10/`text3`. (More rows green "Normal", amber "Review".)

## 16. Lab detail — `features/labs/presentation/lab_detail_screen.dart`
App bar back + test name. `LabValueHero` (e.g. WBC 13.2 danger, "×10³/µL" unit) + range bar (min/normal-zone/max, dot at value) + interpretation `AppAlert` (warning "WBC count elevated…") + ordered/reported meta rows + a per-analyte list (`AppListRow`s with mono values + status pills).

## 17. Prescriptions — `features/prescriptions/presentation/prescriptions_screen.dart` (+ detail)
App bar "Prescriptions" (+ search). Tabs (Active | Past). `AppListRow`/cards: drug name `listTitle` + "dose · freq · prescriber" sub + status pill (green "Active", gray "Completed") + mono refill/date. Detail (`prescription_detail_screen.dart`): drug header, dosage block (mono), instructions, prescriber + pharmacy, refed actions.

## 18. Appointments — `features/appointments/presentation/appointments_screen.dart`
App bar "Appointments" (+ filter). Tabs (Upcoming | Past). `AppListRow` cards: calendar icon + title + "facility · date · time" + status pill (blue "Confirmed", amber "Pending", gray "Completed"). FAB / CTA → Book.

## 19. Book Appointment — `features/appointments/presentation/book_appointment_screen.dart`
Stepper (`step-dots`: done/active/todo widths 18/26/8). Steps: pick facility (`FacilityCard` list) → pick slot (date chips + time grid) → confirm summary. Gradient CTA per step ("Continue", "Confirm Booking").

## 20. Appointment detail — `features/appointments/presentation/appointment_detail_screen.dart`
Header card (facility + datetime, status pill), provider row, location/Care-Map link, actions (Reschedule outline, Cancel danger).

## 21. Health Timeline — `features/timeline/presentation/timeline_screen.dart`
App bar "Health Timeline" (+ filter). `TimelineItem` list grouped by date: each = colored dot+icon, vertical line, date label, title, meta. Event types tinted (encounter primary, lab amber/red, prescription `#D97706`, immunization success, document blue).

## 22. Consent Requests — `features/consent/presentation/consent_screen.dart`
App bar "Consent Requests". `AppTabBar` (Pending | History). `ConsentCard`: pending variant (primary50/primary200) with requester + scope + expiry, then Approve | Deny actions. Approved/denied = neutral card with status pill.

## 23. Access Logs — `features/access_logs/presentation/access_logs_screen.dart`
App bar "Access Logs". Filter chips (All | Allowed | Emergency | Denied). `AppListRow` per event (actor icon, "who / action · when", trailing pill green "Allowed" / red "Denied" / amber "Emergency").

## 24. Documents — `features/documents/presentation/documents_screen.dart`
App bar "Documents" (+ search). Type tabs/chips. `AppListRow` per document (file icon tinted by type, title + "issuer · date", trailing download/verify chevron + status pill "Verified" green).

## 25. Medical Export — `features/medical_export/presentation/medical_export_screen.dart`
Intro card, selectable record-type checklist, format selector, gradient CTA "Export My Records" (download icon), recent-exports list with status.

## 26. Surveys — `features/surveys/presentation/surveys_screen.dart` (+ wizard)
List of pending/completed surveys (`AppListRow` + pill). Wizard (`survey_wizard_screen.dart`): `step-dots`, question text, `survey-opt` radio options (selected = primary50/primary border + filled radio), Back/Next.

## 27. Referrals — `features/referrals/presentation/referrals_screen.dart`
App bar "Referrals". Tabs (Active | Past). Cards: from→to facility, reason, status pill, consent/access note, "View package" action.

---

### Parity checklist per screen
- [ ] background `bg`, app bar `surface` + `appBarTitle`
- [ ] all data (IDs, values, prices, dates) in `GeistMono`
- [ ] tints/pills match the exact hex pairs in `02`
- [ ] correct bottom-nav active tab + dot
- [ ] light + dark both correct
- [ ] spacing: screen h-pad 16, card radius/padding per component
