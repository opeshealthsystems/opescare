# OpesCare Onboarding, Login, Signup, and Public Account Access PRD

## Document Purpose

This document defines the complete login, signup, account onboarding, organization onboarding, and role-based entry experience for OpesCare.

OpesCare is being built from scratch.

Do not use OpesHIS OS.

Do not copy OpesHIS OS layout, UI, code, forms, database assumptions, or onboarding structure.

This document is for designing and implementing the public-facing and authenticated entry flows for:

- Patients
- Guardians
- Hospitals
- Clinics
- Pharmacies
- Laboratories
- Insurance companies
- Public health organizations
- Technology vendors / integration partners
- OpesCare administrators
- Staff users invited by facilities

The onboarding system must be clean, secure, bilingual, production-ready, and built with clear medical language.

---

# 1. Core Onboarding Principle

OpesCare must not use one generic signup form for everyone.

Different users enter OpesCare for different reasons.

A patient wants to manage their Health ID.

A hospital wants to register as a facility and connect patient records.

A pharmacy wants to sync medicine availability and dispense prescriptions.

A laboratory wants to manage orders and release verified results.

An insurance company wants controlled access for eligibility and claims.

A developer wants API or sandbox access.

Therefore, OpesCare needs one unified onboarding system with different onboarding paths.

The system should ask:

**“How do you want to use OpesCare?”**

Then route the user to the correct flow.

---

# 2. Main Public Entry Pages

The account entry experience must include:

1. Login page
2. Signup / Get Started page
3. Patient signup
4. Guardian signup
5. Organization signup
6. Staff invitation acceptance
7. Developer / API access request
8. Password reset
9. OTP verification
10. Facility selector after login
11. Pending approval screen
12. Account suspended screen
13. Email/phone verification screen
14. Bilingual language selector
15. Support/contact fallback

---

# 3. URL Structure

Recommended routes:

```text
/login
/signup
/signup/patient
/signup/guardian
/signup/organization
/signup/developer
/invite/{token}
/forgot-password
/reset-password/{token}
/verify/otp
/verify/email
/pending-approval
/account-suspended
/select-facility
```

Optional future routes:

```text
/signup/hospital
/signup/clinic
/signup/pharmacy
/signup/laboratory
/signup/insurance
/signup/public-health
/signup/vendor
```

For simplicity, use `/signup/organization` first, then ask organization type inside the form.

---

# 4. Supported User Types

The onboarding system must support these user categories:

```text
patient
guardian
hospital
clinic
pharmacy
laboratory
insurance_company
public_health_organization
technology_vendor
staff_invited_user
developer
opesware_admin
```

Each user type must have different permissions, verification requirements, and landing dashboard.

---

# 5. Visual Design Direction

The login and signup pages must look:

- clean
- clinical
- premium
- secure
- trustworthy
- simple
- bilingual-ready
- mobile-friendly
- hospital-grade

Do not use:

- emojis
- random icons
- cartoon doctors
- generic cheap hospital templates
- dark unreadable gradients
- dirty icon packs
- low-resolution medical images
- cluttered forms

Use:

- OpesCare approved color system
- Lucide icons only
- clean white surfaces
- soft blue/teal accents
- clear typography
- visible focus states
- clear error messages
- safe medical language

---

# 6. Approved Login/Signup Color Rules

Use OpesCare colors:

```text
Primary Blue: #0F4C81
Primary Blue Dark: #0A355C
Primary Blue Light: #E8F2FA

Clinical Teal: #0F766E
Clinical Teal Light: #E6F7F5

Background: #F7FAFC
Surface White: #FFFFFF
Text Primary: #0F172A
Text Secondary: #475569
Text Muted: #64748B
Border: #E2E8F0

Success: #15803D
Warning: #B45309
Danger: #B91C1C
Critical: #7F1D1D
System Purple: #6D28D9
```

The login/signup interface should use mostly:

- white
- soft background gray
- primary blue
- clinical teal
- muted borders

Use danger colors only for real errors.

---

# 7. Approved Icons

Use Lucide icons only.

Recommended icons:

```text
Login                     → LogIn
Signup                    → UserPlus
Patient                   → UserRound
Guardian                  → UserRoundPlus
Hospital                  → Hospital
Clinic                    → Building2
Pharmacy                  → Pill
Laboratory                → FlaskConical
Insurance                 → ShieldPlus
Public Health             → ChartColumn
Developer/API             → Code2
Security                  → ShieldCheck
Email                     → Mail
Phone                     → Phone
Password                  → Lock
OTP                       → KeyRound
Language                  → Languages
Facility Selector         → Building2
Pending Approval          → Clock
Account Suspended         → ShieldAlert
Verified                  → BadgeCheck
Warning                   → TriangleAlert
Help                      → CircleHelp
Contact                   → Mail
```

Icons must always support text labels. Do not use icons alone.

---

# 8. Bilingual Requirement

The login and onboarding pages must support:

```text
English
French
```

Do not hard-code user-facing strings.

All labels, buttons, errors, help text, and messages must use translation keys.

Language selector must show:

```text
English
Français
```

Do not use flags as the main language selector.

The selected language should be stored by:

- session/cookie for unauthenticated users
- user preference after login
- patient portal preference for patients
- staff profile preference for staff

---

# 9. Clear Medical Language Rule

Use simple, accurate language.

Do not use unnecessary jargon.

Bad:

```text
Initiate longitudinal clinical identity creation.
```

Good:

```text
Create your OpesCare Health ID.
```

Bad:

```text
Organization onboarding artifact submitted.
```

Good:

```text
Your organization application has been submitted.
```

French must be natural and clear.

Bad:

```text
Votre artefact d’intégration organisationnelle a été soumis.
```

Good:

```text
Votre demande d’inscription a été envoyée.
```

---

# 10. Login Page

## Page Goal

Allow existing users to securely access OpesCare.

## URL

```text
/login
```

## Page Title

```text
Sign in to OpesCare
```

French:

```text
Connectez-vous à OpesCare
```

## Main Headline

```text
Welcome back to OpesCare
```

French:

```text
Bienvenue sur OpesCare
```

## Subheadline

```text
Access your Health ID, patient portal, facility dashboard, or integration workspace.
```

French:

```text
Accédez à votre identifiant de santé, portail patient, tableau de bord d’établissement ou espace d’intégration.
```

## Fields

```text
Email or phone number
Password
```

Optional:

```text
Remember this device
```

## Buttons

Primary:

```text
Sign in
```

Secondary:

```text
Sign in with OTP
```

Links:

```text
Forgot password?
Create an account
Need help?
```

## Security Message

```text
For your safety, never share your OpesCare login details with anyone.
```

French:

```text
Pour votre sécurité, ne partagez jamais vos identifiants OpesCare avec quelqu’un.
```

## Login Flow

1. User opens `/login`.
2. User selects language if needed.
3. User enters email/phone and password.
4. System validates credentials.
5. System checks account status.
6. If MFA/OTP is required, user is sent to OTP verification.
7. If user belongs to multiple facilities, user is sent to facility selector.
8. If user belongs to one facility, user enters correct dashboard.
9. If user is a patient, user enters patient portal.
10. Login event is audited.

## Login Failure States

Show clear errors:

### Invalid credentials

```text
The email, phone number, or password is incorrect.
```

French:

```text
L’adresse e-mail, le numéro de téléphone ou le mot de passe est incorrect.
```

### Account pending

```text
Your account is waiting for approval. We will notify you when it is ready.
```

French:

```text
Votre compte est en attente d’approbation. Nous vous informerons lorsqu’il sera prêt.
```

### Account suspended

```text
This account has been suspended. Contact support if you believe this is a mistake.
```

French:

```text
Ce compte a été suspendu. Contactez le support si vous pensez qu’il s’agit d’une erreur.
```

### Facility suspended

```text
This facility is currently suspended and cannot access OpesCare.
```

French:

```text
Cet établissement est actuellement suspendu et ne peut pas accéder à OpesCare.
```

### Too many attempts

```text
Too many login attempts. Please wait before trying again.
```

French:

```text
Trop de tentatives de connexion. Veuillez patienter avant de réessayer.
```

---

# 11. OTP Login Page

## URL

```text
/verify/otp
```

## Headline

```text
Enter verification code
```

French:

```text
Entrez le code de vérification
```

## Subheadline

```text
We sent a code to your registered phone number or email.
```

French:

```text
Nous avons envoyé un code à votre numéro de téléphone ou à votre adresse e-mail enregistrée.
```

## Fields

```text
6-digit code
```

## Buttons

```text
Verify
Resend code
Change phone or email
```

## Failure States

```text
The code is incorrect.
The code has expired.
Too many attempts. Request a new code.
```

French:

```text
Le code est incorrect.
Le code a expiré.
Trop de tentatives. Demandez un nouveau code.
```

## Security Rule

OTP codes must expire.

OTP attempts must be rate-limited.

Never show whether a phone number or email exists in a way that leaks account information.

---

# 12. Signup / Get Started Page

## URL

```text
/signup
```

## Page Goal

Let users choose the correct onboarding path.

## Headline

```text
Get started with OpesCare
```

French:

```text
Commencez avec OpesCare
```

## Subheadline

```text
Choose how you want to use OpesCare.
```

French:

```text
Choisissez comment vous souhaitez utiliser OpesCare.
```

## Signup Cards

### Card 1: Patient

Title:

```text
I am a patient
```

Text:

```text
Create or access your Health ID, manage consent, view health updates, and carry your medical history safely.
```

Icon:

```text
UserRound
```

CTA:

```text
Continue as Patient
```

### Card 2: Guardian

Title:

```text
I manage care for someone
```

Text:

```text
Request access to manage a child, dependent, elderly relative, or someone under your care.
```

Icon:

```text
UserRoundPlus
```

CTA:

```text
Continue as Guardian
```

### Card 3: Hospital or Clinic

Title:

```text
Hospital or Clinic
```

Text:

```text
Register your facility to use Health IDs, patient records, consent, referrals, and interoperability tools.
```

Icon:

```text
Hospital
```

CTA:

```text
Register Organization
```

### Card 4: Pharmacy

Title:

```text
Pharmacy
```

Text:

```text
Connect prescriptions, dispensing records, and medicine availability with verified patient workflows.
```

Icon:

```text
Pill
```

CTA:

```text
Register Pharmacy
```

### Card 5: Laboratory

Title:

```text
Laboratory
```

Text:

```text
Connect lab orders, sample tracking, result validation, and verified reports to patient timelines.
```

Icon:

```text
FlaskConical
```

CTA:

```text
Register Laboratory
```

### Card 6: Insurance Company

Title:

```text
Insurance Company
```

Text:

```text
Support eligibility checks, preauthorization, claims, and controlled access to necessary information.
```

Icon:

```text
ShieldPlus
```

CTA:

```text
Register Insurer
```

### Card 7: Developer or System Vendor

Title:

```text
Developer or System Vendor
```

Text:

```text
Request access to OpesCare Connect API, SDKs, webhooks, sandbox, and integration documentation.
```

Icon:

```text
Code2
```

CTA:

```text
Request API Access
```

### Card 8: Public Health or Research Organization

Title:

```text
Public Health or Research
```

Text:

```text
Contact OpesCare for approved public health reporting, governance, or research collaboration.
```

Icon:

```text
ChartColumn
```

CTA:

```text
Contact Partnership Team
```

## Bottom Text

```text
Already have an account? Sign in.
```

French:

```text
Vous avez déjà un compte ? Connectez-vous.
```

---

# 13. Patient Signup Page

## URL

```text
/signup/patient
```

## Goal

Allow patients to start creating or accessing their OpesCare Health ID.

## Important Rule

Patient self-registration should create a provisional profile until verified by an approved facility or identity method.

## Headline

```text
Create your OpesCare Health ID
```

French:

```text
Créez votre identifiant de santé OpesCare
```

## Subheadline

```text
Your Health ID helps approved healthcare providers identify your record safely when you need care.
```

French:

```text
Votre identifiant de santé aide les prestataires de santé autorisés à retrouver votre dossier en toute sécurité lorsque vous avez besoin de soins.
```

## Form Sections

### Section 1: Basic Information

Fields:

```text
First name
Middle name optional
Last name
Date of birth
Sex
Phone number
Email optional
Preferred language
Country
City/Town
```

### Section 2: Identity Check

Fields:

```text
Do you already have an OpesCare Health ID?
Health ID optional
National ID optional
Insurance number optional
Previous hospital patient number optional
```

### Section 3: Emergency Contact

Fields:

```text
Emergency contact name
Relationship
Emergency contact phone
```

### Section 4: Account Security

Fields:

```text
Password
Confirm password
Accept terms
Accept privacy notice
```

### Section 5: Consent Notice

Text:

```text
By creating an account, you can manage your Health ID, receive consent requests, and view your access logs. Your medical records are not public. Access depends on consent, authorization, and policy.
```

French:

```text
En créant un compte, vous pouvez gérer votre identifiant de santé, recevoir des demandes de consentement et consulter votre journal d’accès. Vos dossiers médicaux ne sont pas publics. L’accès dépend du consentement, de l’autorisation et des règles applicables.
```

## CTA

```text
Create Health ID
```

French:

```text
Créer l’identifiant de santé
```

## Success Screen

Headline:

```text
Your OpesCare account has been created
```

Text:

```text
Your profile is currently provisional. A verified healthcare facility may confirm your identity when you receive care.
```

CTA:

```text
View My Health ID
```

## Patient Signup Flow

1. Patient opens patient signup.
2. Patient selects language.
3. Patient enters basic information.
4. System checks for possible duplicate record.
5. If possible match exists, system asks patient to confirm or continue with review.
6. Patient creates account.
7. OTP verification is sent.
8. Patient verifies phone/email.
9. Provisional profile is created.
10. Health ID is generated or pending depending on policy.
11. Audit event is logged.
12. Patient enters patient portal.

## Failure Controls

- Do not create duplicate patient without duplicate check.
- Do not expose another patient’s details during duplicate check.
- Do not make self-registered profile fully verified automatically.
- Do not allow weak passwords.
- Do not allow unverified phone/email for sensitive actions.
- Do not expose full medical record after signup unless properly linked and authorized.

---

# 14. Guardian Signup Page

## URL

```text
/signup/guardian
```

## Goal

Allow guardians or caregivers to request access to manage someone else’s health profile.

## Headline

```text
Manage care for a child or dependent
```

French:

```text
Gérer les soins d’un enfant ou d’une personne à charge
```

## Subheadline

```text
Request access to help manage a child, dependent, elderly relative, or someone under your care.
```

French:

```text
Demandez l’accès pour aider à gérer les soins d’un enfant, d’une personne à charge, d’un parent âgé ou d’une personne sous votre responsabilité.
```

## Form Sections

### Guardian Information

```text
First name
Last name
Phone number
Email optional
Date of birth
Relationship to patient
Preferred language
```

### Patient / Dependent Information

```text
Patient full name
Patient date of birth
Patient sex
Patient Health ID if known
Relationship
Reason for access
```

### Proof / Verification

```text
Upload supporting document optional at MVP
Facility-assisted verification option
```

### Security

```text
Password
Confirm password
Accept terms
Accept privacy notice
```

## CTA

```text
Request Guardian Access
```

French:

```text
Demander l’accès en tant que tuteur
```

## Success Message

```text
Your request has been submitted. Guardian access may require verification before it becomes active.
```

French:

```text
Votre demande a été envoyée. L’accès en tant que tuteur peut nécessiter une vérification avant d’être activé.
```

## Safety Rules

- Guardian access must not be automatic for high-risk actions.
- Guardian relationship must have a legal or approved basis.
- Adult transition must later trigger review.
- Expired guardian access must stop working.
- Guardian actions must be audited.

---

# 15. Organization Signup Page

## URL

```text
/signup/organization
```

## Goal

Allow hospitals, clinics, pharmacies, labs, insurers, public health organizations, and technology vendors to apply for OpesCare access.

## Headline

```text
Register your organization with OpesCare
```

French:

```text
Inscrivez votre organisation sur OpesCare
```

## Subheadline

```text
Apply to connect your healthcare organization to OpesCare. Our team will review your information before activation.
```

French:

```text
Demandez à connecter votre organisation de santé à OpesCare. Notre équipe examinera vos informations avant l’activation.
```

## Organization Type Options

```text
Hospital
Clinic
Pharmacy
Laboratory
Insurance Company
Public Health Organization
Technology Vendor
Other
```

French:

```text
Hôpital
Clinique
Pharmacie
Laboratoire
Compagnie d’assurance
Organisation de santé publique
Fournisseur technologique
Autre
```

## Form Sections

### Section 1: Organization Type

Fields:

```text
Organization type
Is this organization already using healthcare software?
Does the organization need API integration?
Does the organization need OpesCare Lite?
```

### Section 2: Organization Information

Fields:

```text
Organization legal name
Trade name optional
Registration/license number
Country
Region/State
City/Town
Address
Website optional
Main phone number
Main email
```

### Section 3: Primary Contact

Fields:

```text
Full name
Role/Title
Email
Phone
Preferred language
```

### Section 4: Services Provided

Show relevant checkboxes depending on organization type.

For hospital/clinic:

```text
Outpatient care
Emergency care
Inpatient care
Laboratory
Pharmacy
Imaging/Radiology
Maternity
Surgery
Specialty care
Insurance billing
```

For pharmacy:

```text
Prescription dispensing
Medicine stock availability
Medication reservation
Insurance billing
Delivery service optional
```

For laboratory:

```text
Sample collection
Result entry
Result validation
External lab integration
Critical result alerts
```

For insurer:

```text
Eligibility checks
Preauthorization
Claims processing
Policy management
Provider network
```

For technology vendor:

```text
Hospital information system
Laboratory system
Pharmacy system
Insurance system
API integration
Bridge Agent integration
Webhook integration
```

### Section 5: Documents

Fields:

```text
Business registration document
Facility license document
Professional license optional
Tax document optional
Other supporting document optional
```

### Section 6: Integration Interest

Fields:

```text
Do you want to connect an existing system?
Current software name
Do you need API access?
Do you need SDK support?
Do you need widget integration?
Do you need Bridge Agent support?
Do you want OpesCare Lite?
Estimated number of users
Estimated number of patients per month
```

### Section 7: Terms and Privacy

Required checkboxes:

```text
I confirm that the information provided is accurate.
I understand that OpesCare will review this application before activation.
I agree to the Terms and Conditions.
I have read the Privacy and Patient Data Processing notice.
```

## CTA

```text
Submit Organization Application
```

French:

```text
Envoyer la demande d’inscription
```

## Organization Signup Flow

1. User opens organization signup.
2. User selects organization type.
3. System adjusts form fields based on type.
4. User enters organization details.
5. User enters primary contact.
6. User uploads required documents.
7. User selects integration needs.
8. User accepts terms and privacy notice.
9. System creates organization application in pending status.
10. Confirmation email/SMS is sent.
11. OpesCare admin reviews application.
12. Admin approves, rejects, or requests more information.
13. If approved, organization account is created.
14. Primary contact is invited to create admin account.
15. Facility onboarding continues inside authenticated dashboard.

## Organization Application Statuses

```text
draft
submitted
under_review
more_information_required
approved
rejected
activated
suspended
```

## Success Screen

Headline:

```text
Application submitted
```

Text:

```text
Thank you. Our team will review your organization application. We may contact you if more information is needed.
```

French:

```text
Merci. Notre équipe examinera la demande d’inscription de votre organisation. Nous pourrons vous contacter si des informations supplémentaires sont nécessaires.
```

CTA:

```text
Return to Home
```

---

# 16. Hospital Signup Variant

If organization type is Hospital, show a tailored message.

## Hospital Message

```text
Register your hospital to use Health IDs, patient timelines, consent workflows, emergency access, referrals, billing, pharmacy, laboratory, and interoperability tools.
```

French:

```text
Inscrivez votre hôpital pour utiliser les identifiants de santé, l’historique médical, les consentements, l’accès d’urgence, les références médicales, la facturation, la pharmacie, le laboratoire et les outils d’interopérabilité.
```

## Hospital-Specific Required Fields

```text
Hospital type
Number of branches
Number of beds optional
Emergency department yes/no
Laboratory yes/no
Pharmacy yes/no
Imaging yes/no
Existing hospital system yes/no
```

---

# 17. Clinic Signup Variant

## Clinic Message

```text
Register your clinic to manage patient Health IDs, visits, prescriptions, lab results, referrals, and approved record sharing.
```

French:

```text
Inscrivez votre clinique pour gérer les identifiants de santé, les consultations, les ordonnances, les résultats de laboratoire, les références médicales et le partage autorisé des dossiers.
```

## Clinic-Specific Fields

```text
Clinic type
Specialties
Number of practitioners
Laboratory on site yes/no
Pharmacy on site yes/no
Existing software yes/no
```

---

# 18. Pharmacy Signup Variant

## Pharmacy Message

```text
Register your pharmacy to support prescription dispensing, medication history, medicine availability, and safe stock synchronization.
```

French:

```text
Inscrivez votre pharmacie pour gérer la délivrance des ordonnances, l’historique des médicaments, la disponibilité des médicaments et la synchronisation sécurisée du stock.
```

## Pharmacy-Specific Fields

```text
Pharmacy license number
Licensed pharmacist in charge
Operating hours
Medicine stock sync interest yes/no
Reservation support yes/no
Existing pharmacy software yes/no
Delivery service optional
```

## Pharmacy Safety Notice

```text
Prescription-required medicines must be clearly marked. Expired, recalled, quarantined, or unavailable stock must never appear as available.
```

French:

```text
Les médicaments nécessitant une ordonnance doivent être clairement indiqués. Les stocks expirés, rappelés, mis en quarantaine ou indisponibles ne doivent jamais apparaître comme disponibles.
```

---

# 19. Laboratory Signup Variant

## Laboratory Message

```text
Register your laboratory to connect lab orders, sample tracking, result validation, critical alerts, and verified reports to patient timelines.
```

French:

```text
Inscrivez votre laboratoire pour connecter les demandes d’analyse, le suivi des échantillons, la validation des résultats, les alertes critiques et les rapports vérifiés à l’historique médical du patient.
```

## Laboratory-Specific Fields

```text
Laboratory license number
Tests offered
Sample collection locations
Result validation workflow
Existing lab software yes/no
Critical result notification support yes/no
```

## Lab Safety Notice

```text
Released lab results must not be silently edited. Corrections must be recorded as amendments.
```

French:

```text
Les résultats de laboratoire publiés ne doivent pas être modifiés en silence. Les corrections doivent être enregistrées comme des modifications.
```

---

# 20. Insurance Company Signup Variant

## Insurance Message

```text
Register your insurance organization to support eligibility checks, preauthorization, claims, and controlled access to necessary information.
```

French:

```text
Inscrivez votre organisme d’assurance pour gérer les vérifications d’éligibilité, les autorisations préalables, les demandes de remboursement et l’accès contrôlé aux informations nécessaires.
```

## Insurance-Specific Fields

```text
Insurance license/registration number
Coverage types
Provider network size optional
Claims system name optional
API integration interest
Preauthorization workflow interest
```

## Insurance Privacy Notice

```text
Insurers should only access the minimum necessary information required for eligibility, authorization, claims, or policy-related workflows.
```

French:

```text
Les assureurs ne doivent accéder qu’aux informations strictement nécessaires pour l’éligibilité, l’autorisation, les demandes de remboursement ou les opérations liées à la police d’assurance.
```

---

# 21. Public Health Signup Variant

## Public Health Message

```text
Contact OpesCare to discuss approved public health reporting, controlled data access, and governance-based collaboration.
```

French:

```text
Contactez OpesCare pour discuter du reporting de santé publique approuvé, de l’accès contrôlé aux données et d’une collaboration encadrée par la gouvernance.
```

## Required Fields

```text
Organization name
Public health role
Jurisdiction
Purpose of interest
Contact person
Email
Phone
Message
```

## Safety Notice

```text
Public health and research data access must follow approved governance, privacy, and data minimization rules.
```

French:

```text
L’accès aux données de santé publique et de recherche doit respecter les règles approuvées de gouvernance, de confidentialité et de minimisation des données.
```

---

# 22. Developer / API Access Signup

## URL

```text
/signup/developer
```

## Goal

Allow technology vendors and developers to request sandbox/API access.

## Headline

```text
Request OpesCare API access
```

French:

```text
Demander l’accès à l’API OpesCare
```

## Subheadline

```text
Connect healthcare systems to OpesCare using APIs, SDKs, webhooks, widgets, or Bridge Agent tools.
```

French:

```text
Connectez des systèmes de santé à OpesCare grâce aux API, SDK, webhooks, widgets ou outils Bridge Agent.
```

## Form Fields

```text
Full name
Organization
Role
Email
Phone
Country
System type
Integration purpose
Expected data flow
Sandbox access requested yes/no
Production access requested yes/no
```

## System Type Options

```text
Hospital information system
Laboratory information system
Pharmacy system
Insurance system
Blood bank system
Mobile app
Public health system
Other
```

## Expected Data Flow Options

```text
Pull patient summary
Push encounters
Push lab results
Push prescriptions
Sync pharmacy stock
Sync blood inventory
Receive webhooks
Other
```

## CTA

```text
Request API Access
```

## Developer Safety Notice

```text
Production API access requires organization verification, approved scopes, secure credentials, and compliance review.
```

French:

```text
L’accès API en production nécessite la vérification de l’organisation, des autorisations approuvées, des identifiants sécurisés et une revue de conformité.
```

---

# 23. Staff Invitation Acceptance

## URL

```text
/invite/{token}
```

## Goal

Allow facility staff to accept an invitation and create their account.

## Headline

```text
Accept your OpesCare invitation
```

French:

```text
Acceptez votre invitation OpesCare
```

## Subheadline

```text
You have been invited to join an organization on OpesCare.
```

French:

```text
Vous avez été invité à rejoindre une organisation sur OpesCare.
```

## Display Before Account Creation

```text
Organization
Facility/Branch
Assigned role
Invited by
Invitation expiry
```

## Fields

```text
Full name
Phone
Password
Confirm password
Preferred language
Accept terms
```

## CTA

```text
Accept Invitation
```

## Failure States

```text
Invitation expired
Invitation already used
Invitation revoked
Organization suspended
Role no longer available
```

## Security Rules

- Invitation token must expire.
- Invitation token must be single-use.
- Role must be checked again before activation.
- Staff login must be audited.
- Staff must not be able to change assigned role during invitation acceptance.

---

# 24. Facility Selector After Login

## URL

```text
/select-facility
```

## Goal

If a user belongs to multiple facilities, allow them to select where they are working.

## Headline

```text
Choose your facility
```

French:

```text
Choisissez votre établissement
```

## Facility Card Shows

```text
Facility name
Branch
User role
Facility status
Last login optional
```

## CTA

```text
Continue
```

## Rules

- Suspended facilities must be shown as unavailable.
- User cannot enter facility where assignment is inactive.
- Facility selection must be stored in session.
- Every action after login must use selected facility context.

---

# 25. Pending Approval Screen

## URL

```text
/pending-approval
```

## Headline

```text
Your application is under review
```

French:

```text
Votre demande est en cours d’examen
```

## Text

```text
Thank you for applying to OpesCare. Our team is reviewing your information. We may contact you if more details are needed.
```

French:

```text
Merci d’avoir envoyé votre demande à OpesCare. Notre équipe examine vos informations. Nous pourrons vous contacter si des détails supplémentaires sont nécessaires.
```

## Status Card

Show:

```text
Application submitted
Current status
Organization name
Application reference
Submitted date
Contact email
```

## CTA

```text
Contact Support
Return to Home
```

---

# 26. Account Suspended Screen

## URL

```text
/account-suspended
```

## Headline

```text
Account access is currently suspended
```

French:

```text
L’accès au compte est actuellement suspendu
```

## Text

```text
This account cannot access OpesCare at the moment. Contact support if you believe this is a mistake.
```

French:

```text
Ce compte ne peut pas accéder à OpesCare pour le moment. Contactez le support si vous pensez qu’il s’agit d’une erreur.
```

## Do Not Show

Do not expose internal suspension reasons to unauthorized users.

## CTA

```text
Contact Support
```

---

# 27. Forgot Password Page

## URL

```text
/forgot-password
```

## Headline

```text
Reset your password
```

French:

```text
Réinitialisez votre mot de passe
```

## Text

```text
Enter your email address or phone number. If an account exists, we will send password reset instructions.
```

French:

```text
Entrez votre adresse e-mail ou votre numéro de téléphone. Si un compte existe, nous enverrons les instructions de réinitialisation.
```

## Security Rule

Do not reveal whether an account exists.

## CTA

```text
Send Reset Instructions
```

---

# 28. Reset Password Page

## URL

```text
/reset-password/{token}
```

## Fields

```text
New password
Confirm new password
```

## Password Rules

- minimum length
- complexity rule based on security policy
- cannot reuse recent password where implemented
- token expires
- token single-use

## Success Message

```text
Your password has been updated. You can now sign in.
```

French:

```text
Votre mot de passe a été mis à jour. Vous pouvez maintenant vous connecter.
```

---

# 29. Form Component Requirements

All onboarding forms must use consistent components:

```text
TextInput
PhoneInput
EmailInput
PasswordInput
SelectInput
MultiSelect
Checkbox
FileUpload
OTPInput
LanguageSelector
OrganizationTypeCard
RoleCard
StatusBadge
AlertBox
StepIndicator
SubmitButton
```

## Form Behavior

- show required fields clearly
- validate fields inline
- do not lose form data on accidental navigation
- use loading state on submit
- prevent duplicate submission
- show clear success/error messages
- support keyboard navigation
- work on mobile
- support French text without layout breakage

---

# 30. Validation Rules

## General

Every form must validate:

- required fields
- email format
- phone format
- password strength
- accepted terms
- valid organization type
- valid file type
- file size limit
- duplicate submission protection

## Organization Documents

Accepted file types:

```text
PDF
JPG
PNG
```

Optional future:

```text
DOCX
```

Rejected file message:

```text
This file type is not supported. Please upload a PDF, JPG, or PNG file.
```

French:

```text
Ce type de fichier n’est pas pris en charge. Veuillez importer un fichier PDF, JPG ou PNG.
```

---

# 31. Security Requirements

## Login Security

- rate-limit login attempts
- audit successful login
- audit suspicious failed attempts
- support MFA/OTP
- secure password hashing
- session timeout
- device/session management later

## Signup Security

- rate-limit signup attempts
- prevent bot submissions
- verify phone/email
- scan uploads where file uploads exist
- do not expose private user existence
- do not activate organizations automatically
- do not grant API access automatically

## API/Developer Access

- sandbox access must be reviewed
- production access requires organization verification
- API credentials must not be generated from public form alone

## Patient Data Protection

- no medical records shown during signup
- no full record access before identity/authorization checks
- no sensitive data in emails/SMS
- no patient data in frontend logs

---

# 32. Audit Requirements

Audit these events:

```text
login_success
login_failed_sensitive
logout
password_reset_requested
password_reset_completed
otp_requested
otp_verified
signup_started
patient_signup_completed
guardian_request_submitted
organization_application_submitted
organization_application_reviewed
organization_application_approved
organization_application_rejected
developer_access_requested
staff_invitation_accepted
facility_selected
account_suspended_login_attempt
```

Each audit event should include where available:

```text
actor_id
actor_type
ip_address
user_agent
timestamp
facility_id if applicable
organization_id if applicable
action
result
reason where applicable
```

---

# 33. Notification Requirements

## Patient Signup

Send safe message:

```text
Your OpesCare account has been created. Log in securely to view your Health ID.
```

French:

```text
Votre compte OpesCare a été créé. Connectez-vous de manière sécurisée pour consulter votre identifiant de santé.
```

## Organization Application

```text
Your organization application has been submitted. We will contact you after review.
```

French:

```text
La demande de votre organisation a été envoyée. Nous vous contacterons après examen.
```

## Staff Invitation

```text
You have been invited to join an organization on OpesCare.
```

French:

```text
Vous avez été invité à rejoindre une organisation sur OpesCare.
```

## Password Reset

```text
If this request came from you, use the secure link to reset your password.
```

French:

```text
Si cette demande vient de vous, utilisez le lien sécurisé pour réinitialiser votre mot de passe.
```

Never include sensitive medical information in onboarding notifications.

---

# 34. Database Model Suggestions

## users

```text
id
uuid
name
email
phone
password_hash
preferred_language
account_status
email_verified_at
phone_verified_at
last_login_at
created_at
updated_at
```

## user_profiles

```text
id
user_id
first_name
middle_name
last_name
date_of_birth
sex
profile_type
created_at
updated_at
```

## organization_applications

```text
id
uuid
organization_type
legal_name
trade_name
registration_number
license_number
country
region
city
address
website
main_phone
main_email
primary_contact_name
primary_contact_role
primary_contact_email
primary_contact_phone
preferred_language
status
review_notes
submitted_at
reviewed_at
created_at
updated_at
```

## organization_application_documents

```text
id
organization_application_id
document_type
file_path
file_name
mime_type
status
uploaded_at
reviewed_at
created_at
updated_at
```

## staff_invitations

```text
id
uuid
organization_id
facility_id
email
phone
role_id
invited_by
token_hash
expires_at
accepted_at
revoked_at
status
created_at
updated_at
```

## developer_access_requests

```text
id
uuid
name
organization
role
email
phone
country
system_type
integration_purpose
expected_data_flow
sandbox_requested
production_requested
status
submitted_at
reviewed_at
created_at
updated_at
```

## otp_challenges

```text
id
user_id nullable
phone nullable
email nullable
purpose
code_hash
expires_at
attempt_count
verified_at
created_at
updated_at
```

---

# 35. Onboarding Statuses

## User Account Status

```text
active
pending_verification
pending_approval
suspended
deactivated
locked
```

## Organization Application Status

```text
draft
submitted
under_review
more_information_required
approved
rejected
activated
suspended
```

## Developer Request Status

```text
submitted
under_review
approved_for_sandbox
approved_for_production
rejected
more_information_required
```

## Staff Invitation Status

```text
pending
accepted
expired
revoked
```

---

# 36. Empty States

## No Organization Yet

```text
You are not connected to an organization yet.
```

French:

```text
Vous n’êtes pas encore rattaché à une organisation.
```

## No Facility Available

```text
You do not have access to any active facility.
```

French:

```text
Vous n’avez accès à aucun établissement actif.
```

## Application Not Found

```text
We could not find this application. Check the link or contact support.
```

French:

```text
Nous n’avons pas trouvé cette demande. Vérifiez le lien ou contactez le support.
```

---

# 37. Accessibility Requirements

All onboarding pages must support:

- keyboard navigation
- visible focus rings
- clear labels
- readable contrast
- screen-reader-friendly form fields
- helpful error messages
- mobile-friendly inputs
- large tap targets
- no color-only meaning
- icons paired with labels

---

# 38. Mobile Responsiveness

On mobile:

- signup cards stack vertically
- forms become single-column
- CTAs are full-width
- file upload remains usable
- language selector is visible
- progress indicator remains compact
- no horizontal scrolling
- French text wraps cleanly

---

# 39. Page Layout Recommendations

## Login Layout

Desktop:

```text
Left: brand message and product trust points
Right: login form card
```

Mobile:

```text
Logo
Headline
Login form
Links
Security note
```

## Signup Selection Layout

Desktop:

```text
Hero header
Grid of onboarding cards
Support CTA
```

Mobile:

```text
Hero header
Stacked onboarding cards
Support CTA
```

## Organization Signup Layout

Use multi-step form:

```text
Step 1: Organization type
Step 2: Organization details
Step 3: Contact person
Step 4: Services
Step 5: Documents
Step 6: Integration needs
Step 7: Review and submit
```

Do not show one overwhelming long form on mobile.

---

# 40. Production Acceptance Criteria

The onboarding system is acceptable when:

1. Login works for patient, staff, organization admin, and invited users.
2. Signup selection routes users to the correct flow.
3. Patient signup creates a provisional profile, not a fully verified one.
4. Guardian signup creates a request, not automatic full access.
5. Organization signup creates a pending application.
6. Hospitals, clinics, pharmacies, labs, insurers, public health organizations, and developers have tailored onboarding content.
7. Staff invitation tokens are secure, single-use, and expiring.
8. Facility selector works for multi-facility users.
9. Suspended accounts and facilities are blocked clearly.
10. Password reset does not reveal account existence.
11. OTP verification is secure and rate-limited.
12. English and French text are supported through translation keys.
13. No user-facing text is hard-coded.
14. Lucide icons are used; no emojis.
15. Forms are mobile responsive.
16. French text does not break layout.
17. Errors are clear and actionable.
18. Security messages are visible.
19. Sensitive medical data is not exposed in onboarding.
20. All important onboarding events are audited.

---

# 41. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, and docs/product/ONBOARDING.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS UI, layout, content, components, database structure, or code.

Task: Build the OpesCare onboarding, login, signup, and organization application interface foundation.

Scope:
1. Create routes/placeholders for:
   - /login
   - /signup
   - /signup/patient
   - /signup/guardian
   - /signup/organization
   - /signup/developer
   - /invite/{token}
   - /forgot-password
   - /verify/otp
   - /pending-approval
   - /account-suspended
   - /select-facility

2. Create reusable components:
   - AuthLayout
   - LoginForm
   - SignupTypeCard
   - PatientSignupForm
   - GuardianSignupForm
   - OrganizationSignupForm
   - DeveloperAccessRequestForm
   - StaffInviteAcceptForm
   - OTPInput
   - LanguageSelector
   - FacilitySelectorCard
   - PendingApprovalCard
   - AuthAlert
   - PasswordInput
   - FileUploadField
   - StepIndicator

3. Use OpesCare color system.

4. Use Lucide icons only.

5. Do not use emojis.

6. Make all pages bilingual-ready using translation keys.

7. Add English and French translation keys for all onboarding pages.

8. Build responsive desktop/tablet/mobile layouts.

9. Add clear empty, loading, and error states.

10. Add client-side validation where appropriate.

11. Do not implement final backend approval logic unless assigned separately.

12. Do not grant organization/API access automatically.

13. Do not create real patient clinical records in this task.

14. Open a PR with:
   - summary
   - screenshots
   - routes created
   - components created
   - translation files added
   - known limitations
   - next recommended tasks
```

---

# 42. Final Rule

The OpesCare onboarding experience must feel safe, serious, and clear.

A patient must understand that they are creating a Health ID, not exposing their medical records publicly.

A guardian must understand that access requires verification.

A hospital, clinic, pharmacy, lab, insurer, or public health organization must understand that onboarding requires review.

A developer must understand that API access is controlled and approved.

No user should enter the wrong onboarding path because of confusing design.

No organization should become active without review.

No medical data should be exposed during signup.

The onboarding experience is the front door of OpesCare. It must be secure, bilingual, clear, and institution-grade.
