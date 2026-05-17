# OpesCare Communication, Alerts, Tasks, Notifications, Voice, and Internal Messaging System PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Document Type:** Final Product Requirements + Technical Architecture + UI/UX + Template Blueprint  
**Build Direction:** Build from scratch or safely extend existing notification modules  
**Core Backend:** Laravel  
**Database:** PostgreSQL  
**Queue/Cache:** Redis  
**Mobile App:** Flutter recommended  
**Channels:** Email, WhatsApp, SMS fallback, in-app/dashboard notifications, push notifications, voice calls, internal messaging, broadcasts/announcements  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS notification code, templates, UI, database structure, messaging model, or assumptions.  
**Privacy Rule:** External messages must not expose sensitive medical information. Clinical details must stay inside secure authenticated OpesCare views.

---

# 1. Purpose

This document defines the complete OpesCare communication system.

The system must handle:

1. notifications
2. clinical alerts
3. action tasks
4. email messages
5. WhatsApp messages
6. SMS fallback
7. dashboard/in-app alerts
8. mobile push notifications
9. voice notifications
10. internal messaging
11. broadcasts and announcements
12. escalation chains
13. acknowledgement workflows
14. delivery tracking
15. notification preferences
16. bilingual English/French templates
17. moderation and abuse reporting
18. retention, archiving, and legal hold
19. audit logs
20. communication safety and privacy rules

This module is not decoration. It is a clinical workflow layer.

Without it, users would need to manually check dashboards for everything. That is not realistic in hospitals, clinics, labs, pharmacies, insurance workflows, public health reporting, and emergency situations.

---

# 2. Core Principle

OpesCare must send the right communication to the right person, through the right channel, at the right time, with the right urgency, and with the right privacy level.

A safe external notification can say:

```text
A lab result is available. Log in securely to view it.
```

It must not say:

```text
Your HIV result is positive.
```

A safe clinical alert can say:

```text
A critical result requires review. Log in securely.
```

It must not expose patient identity or result details to unauthorized users or insecure channels.

---

# 3. Core Architecture Separation

The system must not treat every communication as a generic notification.

OpesCare must separate:

```text
Notification = informs the user
Task = requires an action
Alert = urgent/risk-based warning
Message = communication between users
Broadcast = announcement to a group
Delivery = actual sending through a channel
```

## 3.1 Notification

Purpose:

```text
inform the recipient
```

Examples:

```text
welcome email
lab result available
prescription update
claim status update
partner application received
```

## 3.2 Task

Purpose:

```text
requires the recipient to do something
```

Examples:

```text
doctor must review critical lab result
patient must approve/deny consent request
lab validator must validate result
pharmacist must confirm reservation
admin must review partner application
```

## 3.3 Alert

Purpose:

```text
urgent warning, risk, safety, or escalation
```

Examples:

```text
critical lab result
emergency access used
suspected unauthorized access
urgent blood request
system incident affecting patient safety
```

## 3.4 Message

Purpose:

```text
conversation between users or organizations
```

Examples:

```text
patient messages assigned doctor
doctor messages nurse
lab asks doctor for sample clarification
pharmacy asks doctor about prescription substitution
hospital messages another hospital about referral
```

## 3.5 Broadcast

Purpose:

```text
announcement to a defined group
```

Examples:

```text
facility-wide announcement
department announcement
public health advisory
partner update
system maintenance notice
```

---

# 4. Supported Channels

## 4.1 Email

Used for:

```text
welcome
OTP when appropriate
password reset
organization onboarding
partner updates
non-urgent health updates
reports
developer/API alerts
security alerts
receipts
```

Email must be beautiful, responsive, branded, and professional.

## 4.2 WhatsApp

Used for:

```text
OTP where approved
consent request alerts
appointment reminders
general health update alerts
medicine reservation notices
blood help notices
security alerts
support notifications
```

WhatsApp must be short and privacy-safe.

## 4.3 SMS Fallback

Used where WhatsApp fails or is unavailable.

SMS must be short, clear, and privacy-safe.

## 4.4 Dashboard/In-App

Used for:

```text
clinical alerts
tasks
lab result review
consent request
emergency access review
workflow assignments
admin approvals
partner governance
sync failures
insurance actions
public health report review
```

Authenticated dashboards can show more detail than external channels, but still only within permissions.

## 4.5 Mobile Push

Used for:

```text
new consent request
new health update
appointment reminder
message received
medicine reservation update
security alert
```

Push notifications must not contain sensitive details.

## 4.6 Voice Notifications

Used for urgent and accessibility-related cases:

```text
critical lab result alert
urgent blood request alert
emergency access review alert
urgent nurse task
appointment reminder
low-literacy support
```

Voice must not disclose sensitive clinical detail unless policy and explicit user opt-in allow it.

## 4.7 Internal Messaging

Secure messaging inside OpesCare for care and operations.

## 4.8 Broadcast Announcements

Controlled group announcements for organizations, departments, partners, and public health teams.

---

# 5. Communication Privacy Rules

## 5.1 External Channel Privacy

External channels include:

```text
email
WhatsApp
SMS
push notification
voice call
```

External messages must not include:

```text
sensitive diagnosis
full lab result value
HIV/STI status
mental health details
full prescription detail
full patient history
medical documents
national ID
full date of birth
patient address
insurance claim details beyond safe summary
QR tokens with medical access
API secrets
```

## 5.2 Authenticated Detail Rule

Clinical detail must be viewed inside:

```text
patient portal
provider dashboard
mobile app authenticated session
secure internal message thread
```

## 5.3 Privacy-Safe External Pattern

Use:

```text
A new health update is available in OpesCare. Log in securely to view it.
```

Do not use:

```text
Your malaria test is positive.
```

unless detailed external notifications are explicitly allowed by country policy, user preference, sensitivity class, and channel security.

## 5.4 Mandatory Masking

For external messages, mask:

```text
Health ID
patient name
facility details where risky
clinical condition
test value
full date/time where unnecessary
```

Example:

```text
A health update is available.
```

not:

```text
John N. at Demo Central Hospital has a positive result.
```

---

# 6. Communication Classes

Every communication must be classified.

```text
mandatory
recommended
optional
marketing_or_education
```

## 6.1 Mandatory

Cannot be disabled.

Examples:

```text
OTP
password reset
security alert
emergency access notice where required
privacy/security incident
critical staff clinical alert
consent status change
```

## 6.2 Recommended

Enabled by default but can be controlled depending on policy.

Examples:

```text
lab result available
prescription update
appointment reminder
insurance claim update
medicine reservation update
```

## 6.3 Optional

User can turn off.

Examples:

```text
general tips
education content
non-critical reminders
newsletter
```

## 6.4 Marketing or Education

Must be opt-in where required.

Examples:

```text
health awareness campaign
product updates
non-essential announcements
```

---

# 7. Severity and Priority Matrix

Use controlled priority levels:

```text
low
normal
high
urgent
critical
```

## 7.1 Event Severity Mapping

| Event | Type | Priority | Requires Acknowledgement | Escalation |
|---|---|---:|---:|---:|
| Welcome email | Notification | normal | no | no |
| OTP | Notification | high | no | no |
| Password reset | Notification | high | no | no |
| Lab result available to patient | Notification | high | no | no |
| Critical lab result to doctor | Alert + Task | critical | yes | yes |
| Prescription issued | Notification | high | no | no |
| Prescription clarification needed | Task + Message | high | yes | yes if urgent |
| Consent request pending | Notification + Task | high | yes for requester workflow | yes if expiring |
| Emergency access used | Alert | urgent | yes for review team | yes |
| Appointment reminder | Notification | normal | no | no |
| Medicine stock low | Notification/Task | normal/high | optional | no |
| Medicine out of stock | Alert/Task | high | optional | yes for essential meds |
| Urgent blood request | Alert + Task | critical | yes | yes |
| Insurance claim needs information | Task | high | yes | yes if overdue |
| Public health report pending review | Task | high | yes | yes if overdue |
| Security breach | Alert | critical | yes | yes |
| API sync failed | Task/Alert | high | yes for developer/admin | yes if repeated |
| Partner license expired | Alert + Task | high | yes | yes |
| System maintenance notice | Broadcast | normal | no | no |

---

# 8. Delivery Fallback Logic

Not every event should use every channel.

## 8.1 Default Delivery Order

For ordinary events:

```text
dashboard notification
email or WhatsApp based on preference
SMS fallback only if configured
```

For urgent clinical events:

```text
dashboard alert
push notification
WhatsApp/email safe alert
voice if not acknowledged
escalate to backup user
```

For security events:

```text
email
WhatsApp/SMS if verified
dashboard
force session review where needed
```

## 8.2 Fallback Rule Example

Critical lab result:

```text
1. Create dashboard alert and task for ordering doctor.
2. Send safe push notification.
3. Send safe WhatsApp/email alert.
4. Wait acknowledgement_deadline.
5. If not acknowledged, voice call ordering doctor.
6. If still not acknowledged, escalate to on-duty doctor.
7. If still not acknowledged, escalate to department lead/facility admin.
8. Audit every step.
```

## 8.3 SMS Fallback Rule

Use SMS fallback when:

```text
WhatsApp fails
WhatsApp not enabled
recipient has no WhatsApp
urgent alert requires fallback
policy allows SMS
```

## 8.4 Voice Fallback Rule

Use voice when:

```text
event is urgent/critical
recipient opted in or staff policy requires it
normal channels failed or no acknowledgement
voice is allowed by local policy
```

---

# 9. Anti-Spam, Deduplication, and Digest Rules

Without anti-spam rules, users will be flooded.

## 9.1 Deduplication

Deduplicate similar notifications within a time window.

Example:

```text
Multiple stock changes from same pharmacy within 30 minutes = one grouped notification
```

## 9.2 Grouping

Group low-priority updates:

```text
daily digest
weekly digest
facility summary
stock update summary
API error summary
```

## 9.3 Rate Limits

Apply rate limits for non-critical notifications.

Examples:

```text
maximum 3 non-critical emails per day per patient
maximum 1 stock digest per day per facility admin
maximum repeated API alert every 30 minutes per integration
```

## 9.4 Never Suppress Critical Safety Alerts

Do not suppress:

```text
critical lab result
security incident
emergency access review
urgent blood request
```

But still prevent duplicate spam by updating the same alert thread/task.

---

# 10. Acknowledgement System

Urgent and critical alerts/tasks must have acknowledgement tracking.

## 10.1 Acknowledgement Fields

```text
requires_acknowledgement
acknowledgement_status
acknowledged_by
acknowledged_at
acknowledgement_deadline
escalated_if_not_acknowledged
acknowledgement_note
```

## 10.2 Acknowledgement Statuses

```text
not_required
pending
acknowledged
overdue
escalated
resolved
expired
```

## 10.3 Acknowledgement Rules

- Critical alerts require acknowledgement.
- Acknowledgement does not mean issue is resolved.
- Resolution requires separate status where needed.
- Acknowledgement must be audited.
- Some alerts may require a note before acknowledging.

Example:

```text
Critical lab result acknowledged by Dr. X at 14:03.
```

---

# 11. Escalation Chains

Escalation chains must be configurable.

## 11.1 Escalation Chain Fields

```text
event_type
facility_id nullable
department_id nullable
first_recipient_rule
backup_recipient_rule
escalation_steps_json
time_before_next_step
channels_per_step
active
```

## 11.2 Example: Critical Lab Result

```text
Step 1: ordering doctor
Step 2 after 10 minutes: on-duty doctor
Step 3 after 20 minutes: department head
Step 4 after 30 minutes: facility admin / clinical director
```

Channels:

```text
Step 1: dashboard + push + WhatsApp/email
Step 2: dashboard + push + WhatsApp + voice
Step 3: dashboard + WhatsApp + voice
Step 4: dashboard + WhatsApp + voice + admin alert
```

## 11.3 Example: Urgent Blood Request

```text
Step 1: blood bank officer
Step 2: emergency coordinator
Step 3: hospital administrator
Step 4: regional blood coordination user where configured
```

## 11.4 Escalation Audit

Track:

```text
escalation_started
escalation_step_triggered
recipient_notified
recipient_acknowledged
escalation_resolved
```

---

# 12. Provider Abstraction

Do not hard-code providers.

Use provider interfaces:

```text
EmailProvider
WhatsAppProvider
SmsProvider
PushProvider
VoiceProvider
```

## 12.1 Provider Configuration

Each provider config should support:

```text
provider_name
environment
status
credentials_reference
rate_limit
sender_id
callback_url
fallback_provider
is_default
```

## 12.2 Provider Failover

If primary provider fails:

```text
retry
use fallback provider if configured
mark failed if exhausted
create delivery log
alert admin if repeated failure
```

## 12.3 Local/Demo Mode

In local/demo mode:

```text
do not send real email
do not send real WhatsApp
do not send real SMS
do not make real voice call
write to fake mailbox/log/notification preview instead
```

---

# 13. Template Management and Approval Workflow

Templates must be controlled and versioned.

## 13.1 Template Statuses

```text
draft
in_review
approved
published
archived
rejected
```

## 13.2 Template Workflow

1. Admin creates or edits template.
2. Template is saved as draft.
3. Template is sent for review.
4. Reviewer approves or rejects.
5. Approved version can be published.
6. Old version is archived.
7. Rollback is possible.
8. Audit event is created.

## 13.3 Template Versioning

Track:

```text
template_id
version
language
channel
changed_by
change_summary
published_at
archived_at
```

## 13.4 WhatsApp Provider Approval

WhatsApp templates may need provider approval.

Store:

```text
template_name
provider_template_id
category
language
approval_status
submitted_at
approved_at
rejected_reason
variables_json
```

Approval statuses:

```text
not_submitted
submitted
approved
rejected
paused
disabled
```

---

# 14. Email Design System

Emails must be beautiful, responsive, and medical-grade.

## 14.1 Colors

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
Border: #E2E8F0
Success: #15803D
Warning: #B45309
Danger: #B91C1C
Critical: #7F1D1D
```

## 14.2 Layout

```text
outer background #F7FAFC
centered container max-width 640px
header with logo
main white card
clear title
short body
CTA button
privacy/safety note
footer links
plain-text fallback
```

## 14.3 Typography

```text
system font stack
title 24px bold
body 16px
small note 13px
line-height 1.5
```

## 14.4 Email Rendering QA

Test email templates on:

```text
Gmail web
Gmail mobile
Outlook desktop
Outlook web
Yahoo
Apple Mail
mobile email clients
dark mode
plain text fallback
low bandwidth
```

---

# 15. Base Email Template

```html
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ subject }}</title>
</head>
<body style="margin:0;background:#F7FAFC;font-family:Arial,Helvetica,sans-serif;color:#0F172A;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#F7FAFC;padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:18px;overflow:hidden;">
          <tr>
            <td style="padding:24px 28px;border-bottom:1px solid #E2E8F0;">
              <div style="font-size:22px;font-weight:700;color:#0F4C81;">OpesCare</div>
              <div style="font-size:13px;color:#475569;margin-top:4px;">Secure Digital Health ID</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <h1 style="font-size:24px;line-height:1.25;margin:0 0 12px;color:#0F172A;">{{ title }}</h1>
              <p style="font-size:16px;line-height:1.55;margin:0 0 20px;color:#475569;">{{ intro }}</p>
              {{ content_block }}
              {{ cta_block }}
              <div style="margin-top:24px;padding:16px;border-radius:12px;background:#E8F2FA;color:#0A355C;font-size:14px;line-height:1.5;">
                {{ safety_note }}
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding:22px 28px;background:#F8FAFC;border-top:1px solid #E2E8F0;color:#64748B;font-size:13px;line-height:1.5;">
              <div>OpesCare by Opesware</div>
              <div style="margin-top:8px;">This message was sent because of activity on your OpesCare account or organization.</div>
              <div style="margin-top:8px;">
                <a href="{{ privacy_url }}" style="color:#0F4C81;">Privacy</a> ·
                <a href="{{ help_url }}" style="color:#0F4C81;">Help Center</a> ·
                <a href="{{ support_url }}" style="color:#0F4C81;">Support</a>
              </div>
            </td>
          </tr>
        </table>
        <div style="max-width:640px;margin-top:14px;color:#94A3B8;font-size:12px;line-height:1.4;">
          If this message was not expected, log in directly through OpesCare or contact support.
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
```

---

# 16. Complete Core Template Set

Each template must have:

```text
English subject
English body
English CTA
English safety note
French subject
French body
French CTA
French safety note
channel versions
privacy classification
priority
requires_acknowledgement true/false
```

## 16.1 Welcome — Patient

English subject:

```text
Welcome to OpesCare
```

English body:

```text
Your OpesCare account has been created. You can now access your Health ID, manage consent requests, view health updates, and see who accessed your records.
```

English CTA:

```text
Open My Health ID
```

English safety note:

```text
Your medical records are not public. Access to your information depends on authorization, consent, policy, and audit controls.
```

French subject:

```text
Bienvenue sur OpesCare
```

French body:

```text
Votre compte OpesCare a été créé. Vous pouvez maintenant accéder à votre identifiant de santé, gérer les demandes de consentement, consulter vos mises à jour de santé et voir qui a consulté vos dossiers.
```

French CTA:

```text
Ouvrir mon identifiant de santé
```

French safety note:

```text
Vos dossiers médicaux ne sont pas publics. L’accès à vos informations dépend de l’autorisation, du consentement, des règles applicables et des contrôles d’audit.
```

## 16.2 OTP

English subject:

```text
Your OpesCare verification code
```

English body:

```text
Use this code to continue securely: {{ otp_code }}. It expires in {{ expiry_minutes }} minutes.
```

English safety note:

```text
Never share this code with anyone. OpesCare staff will never ask for your verification code.
```

French subject:

```text
Votre code de vérification OpesCare
```

French body:

```text
Utilisez ce code pour continuer en toute sécurité : {{ otp_code }}. Il expire dans {{ expiry_minutes }} minutes.
```

French safety note:

```text
Ne partagez jamais ce code. Le personnel d’OpesCare ne vous demandera jamais votre code de vérification.
```

## 16.3 Lab Result Available — Patient

English subject:

```text
A lab result is ready to view
```

English body:

```text
A lab result has been released to your OpesCare account. Log in securely to view it.
```

English CTA:

```text
View Health Update
```

English safety note:

```text
For your privacy, this message does not include the result details.
```

French subject:

```text
Un résultat de laboratoire est disponible
```

French body:

```text
Un résultat de laboratoire a été publié dans votre compte OpesCare. Connectez-vous de manière sécurisée pour le consulter.
```

French CTA:

```text
Voir la mise à jour de santé
```

French safety note:

```text
Pour protéger votre vie privée, ce message ne contient pas les détails du résultat.
```

## 16.4 Critical Lab Result — Provider

English subject:

```text
Urgent action required in OpesCare
```

English body:

```text
A critical result has been released and requires attention from an authorized clinical user. Log in to OpesCare to review the case securely.
```

English CTA:

```text
Review Critical Result
```

English safety note:

```text
This message does not include patient or result details. Review the result only inside OpesCare.
```

French subject:

```text
Action urgente requise dans OpesCare
```

French body:

```text
Un résultat critique a été publié et nécessite l’attention d’un utilisateur clinique autorisé. Connectez-vous à OpesCare pour examiner le cas en toute sécurité.
```

French CTA:

```text
Examiner le résultat critique
```

French safety note:

```text
Ce message ne contient pas les détails du patient ni du résultat. Consultez le résultat uniquement dans OpesCare.
```

## 16.5 Prescription Update — Patient

English subject:

```text
A prescription update is available
```

English body:

```text
A prescription update has been added to your OpesCare account. Log in securely to view the details and pharmacy options where available.
```

English CTA:

```text
View Prescription Update
```

English safety note:

```text
Always follow guidance from a qualified healthcare professional before using any medicine.
```

French subject:

```text
Une mise à jour d’ordonnance est disponible
```

French body:

```text
Une mise à jour d’ordonnance a été ajoutée à votre compte OpesCare. Connectez-vous de manière sécurisée pour voir les détails et les options de pharmacie disponibles.
```

French CTA:

```text
Voir la mise à jour d’ordonnance
```

French safety note:

```text
Suivez toujours les conseils d’un professionnel de santé qualifié avant d’utiliser un médicament.
```

## 16.6 Consent Request — Patient

English subject:

```text
A provider is requesting access
```

English body:

```text
{{ facility_name }} is requesting access to selected health information for {{ purpose }}. Review the request before approving or denying it.
```

English CTA:

```text
Review Access Request
```

English safety note:

```text
You can see who is asking, why access is needed, what information is requested, and how long access will last.
```

French subject:

```text
Un prestataire demande un accès
```

French body:

```text
{{ facility_name }} demande l’accès à certaines informations de santé pour {{ purpose }}. Examinez la demande avant de l’approuver ou de la refuser.
```

French CTA:

```text
Examiner la demande d’accès
```

French safety note:

```text
Vous pouvez voir qui fait la demande, pourquoi l’accès est nécessaire, quelles informations sont demandées et combien de temps l’accès durera.
```

## 16.7 Emergency Access Used — Patient

English subject:

```text
Emergency access was used
```

English body:

```text
An authorized provider used emergency access for your OpesCare profile. Log in to view the access details where available.
```

English CTA:

```text
View Access Log
```

English safety note:

```text
Emergency access is limited, reason-based, audited, and reviewed.
```

French subject:

```text
Un accès d’urgence a été utilisé
```

French body:

```text
Un prestataire autorisé a utilisé l’accès d’urgence pour votre profil OpesCare. Connectez-vous pour voir les détails d’accès disponibles.
```

French CTA:

```text
Voir le journal d’accès
```

French safety note:

```text
L’accès d’urgence est limité, basé sur une raison, audité et examiné.
```

## 16.8 Medicine Reservation Update

English subject:

```text
Medicine reservation update
```

English body:

```text
A pharmacy has updated your medicine reservation request. Log in to OpesCare to view the status and next steps.
```

French subject:

```text
Mise à jour de réservation de médicament
```

French body:

```text
Une pharmacie a mis à jour votre demande de réservation de médicament. Connectez-vous à OpesCare pour voir le statut et les prochaines étapes.
```

## 16.9 Blood Availability Update

English subject:

```text
Blood availability update
```

English body:

```text
A blood availability update is available in OpesCare. Please follow guidance from authorized healthcare professionals.
```

French subject:

```text
Mise à jour de disponibilité du sang
```

French body:

```text
Une mise à jour sur la disponibilité du sang est disponible dans OpesCare. Veuillez suivre les conseils des professionnels de santé autorisés.
```

## 16.10 Insurance Claim Update

English subject:

```text
Insurance claim update
```

English body:

```text
There is an update on an insurance workflow linked to your OpesCare account. Log in securely to view the status.
```

French subject:

```text
Mise à jour de demande d’assurance
```

French body:

```text
Une mise à jour est disponible sur un processus d’assurance lié à votre compte OpesCare. Connectez-vous de manière sécurisée pour voir le statut.
```

## 16.11 Partner Application Submitted

English subject:

```text
Your OpesCare partner application was received
```

English body:

```text
Thank you for applying to become an OpesCare partner. Our team will review your information and contact you if more details are needed.
```

French subject:

```text
Votre demande de partenariat OpesCare a été reçue
```

French body:

```text
Merci d’avoir demandé à devenir partenaire OpesCare. Notre équipe examinera vos informations et vous contactera si des détails supplémentaires sont nécessaires.
```

## 16.12 API Sync Failed — Developer

English subject:

```text
OpesCare API sync issue
```

English body:

```text
A sync event from your integration failed and requires review. Log in to the developer dashboard to inspect the error and retry if appropriate.
```

French subject:

```text
Problème de synchronisation API OpesCare
```

French body:

```text
Un événement de synchronisation de votre intégration a échoué et nécessite un examen. Connectez-vous au tableau de bord développeur pour inspecter l’erreur et réessayer si nécessaire.
```

---

# 17. WhatsApp and SMS Templates

## 17.1 WhatsApp Template Model

Store:

```text
template_name
provider_template_id
category
language
approval_status
body
variables_json
```

## 17.2 WhatsApp Templates

### OTP

English:

```text
Your OpesCare verification code is {{ otp_code }}. It expires in {{ expiry_minutes }} minutes. Do not share this code.
```

French:

```text
Votre code de vérification OpesCare est {{ otp_code }}. Il expire dans {{ expiry_minutes }} minutes. Ne partagez pas ce code.
```

### Lab Result Available

English:

```text
A new health update is available in OpesCare. Log in securely to view it: {{ secure_link }}
```

French:

```text
Une nouvelle mise à jour de santé est disponible dans OpesCare. Connectez-vous de manière sécurisée pour la consulter : {{ secure_link }}
```

### Consent Request

English:

```text
{{ facility_name }} is requesting access to selected health information in OpesCare. Review the request here: {{ secure_link }}
```

French:

```text
{{ facility_name }} demande l’accès à certaines informations de santé dans OpesCare. Examinez la demande ici : {{ secure_link }}
```

### Security Alert

English:

```text
Security alert: a new login was detected on your OpesCare account. If this was not you, secure your account now: {{ secure_link }}
```

French:

```text
Alerte de sécurité : une nouvelle connexion a été détectée sur votre compte OpesCare. Si ce n’était pas vous, sécurisez votre compte maintenant : {{ secure_link }}
```

## 17.3 SMS Templates

OTP:

```text
OpesCare code: {{ otp_code }}. Expires in {{ expiry_minutes }} min. Do not share.
```

Health update:

```text
OpesCare: A new health update is available. Log in securely to view it.
```

Consent:

```text
OpesCare: A provider requested access. Log in to review and approve or deny.
```

Security:

```text
OpesCare security alert: New login detected. Secure your account if this was not you.
```

---

# 18. Voice Notifications

## 18.1 Voice Opt-In and Policy Rules

Voice must support:

```text
voice opt-in
voice allowed hours
urgent override
language preference
maximum retry attempts
do-not-call list
acknowledgement capture
```

## 18.2 Voice Templates

Critical provider alert:

```text
This is OpesCare. You have an urgent clinical action requiring review. Please log in securely to OpesCare.
```

Appointment reminder:

```text
This is OpesCare. You have an upcoming appointment. Please check your OpesCare account for details.
```

Blood alert:

```text
This is OpesCare. An urgent blood availability action needs review. Please log in securely to OpesCare.
```

Security alert:

```text
This is OpesCare. A security alert was triggered on your account. Please log in securely or contact support.
```

French versions must be created before production launch.

---

# 19. Dashboard Notifications and Task Inbox

Every dashboard should include:

```text
notification center
task inbox
critical alerts panel
unread count
action-required count
priority filters
mark as read
acknowledge
snooze where allowed
open related item
```

## 19.1 Task Fields

```text
id
task_type
title
description
assigned_to
assigned_role
facility_id nullable
organization_id nullable
patient_id nullable
related_resource_type
related_resource_id
priority
status
due_at
acknowledged_at nullable
completed_at nullable
escalation_chain_id nullable
created_at
updated_at
```

## 19.2 Task Statuses

```text
open
acknowledged
in_progress
waiting
completed
cancelled
overdue
escalated
```

---

# 20. Internal Messaging

## 20.1 Messaging Types

```text
direct message
group conversation
facility staff thread
patient-provider thread
referral thread
lab clarification thread
pharmacy clarification thread
insurance claim thread
support thread
hospital-to-hospital thread
public health case thread
developer support thread
```

## 20.2 Messaging Boundaries

Patient cannot message random doctor.

Doctor cannot message unrelated patient.

Closed care relationship blocks new patient-provider chat unless reopened by policy.

Hospital-to-hospital thread requires referral, transfer, blood request, specialist consult, or approved organization context.

Insurance-to-facility thread requires claim/preauthorization context.

Public health thread cannot expose patient identity by default.

## 20.3 Moderation and Abuse Reporting

Messaging must support:

```text
report message
mute thread
block where appropriate
admin moderation
harassment/abuse case
disciplinary escalation
audit
```

## 20.4 Attachments

Attachment security:

```text
file type whitelist
file size limit
virus scan
PHI classification
encrypted storage
signed URL downloads
download audit
blocked executable files
legal hold support
```

Allowed types:

```text
PDF
JPG
PNG
DOCX where allowed
lab report reference
prescription reference
claim document reference
```

## 20.5 Message Editing and Deletion

Rules:

```text
allow edit within short window
preserve edit history
allow delete for user view only where policy allows
do not hard-delete clinical communication
admins can archive/close threads
legal hold prevents deletion
```

---

# 21. Broadcast Announcements

Broadcasts are not chat messages.

## 21.1 Broadcast Types

```text
facility_wide_announcement
department_announcement
partner_announcement
public_health_advisory
system_maintenance_notice
training_update
policy_update
```

## 21.2 Broadcast Fields

```text
title
body
target_type
target_ids
priority
language
requires_acknowledgement
publish_at
expires_at
created_by
status
```

## 21.3 Broadcast Statuses

```text
draft
scheduled
published
expired
cancelled
archived
```

---

# 22. Notification Preferences

Users manage preferences by category and channel.

## 22.1 Categories

```text
account_and_security
health_updates
consent_requests
appointments
prescriptions
lab_results
medicine_availability
blood_help
messages
insurance_updates
partner_admin_alerts
developer_api_alerts
public_health_alerts
broadcasts
```

## 22.2 Channels

```text
email
WhatsApp
SMS fallback
mobile push
dashboard only
voice
```

## 22.3 Quiet Hours

Quiet hours apply to normal notifications.

Urgent/critical alerts may bypass quiet hours.

## 22.4 Preference Conflict Rule

Mandatory notifications override user preferences.

Recommended/optional notifications follow preferences.

Marketing/education requires opt-in where required.

---

# 23. Retention, Archiving, and Legal Hold

## 23.1 Notifications

Routine notifications:

```text
retain summary for configured period
archive after retention period
delete delivery provider raw payload where safe
```

## 23.2 Messages

Healthcare-related messages should not be hard-deleted casually.

Use:

```text
archive
closed
deleted_for_view
legal_hold
```

## 23.3 Legal Hold

Legal hold prevents deletion or destruction of:

```text
messages
attachments
delivery logs
audit events
clinical communication threads
```

## 23.4 Export Policy

Exports require permission and audit.

---

# 24. Data Models

## 24.1 notification_templates

```text
id
uuid
event_type
channel
language
subject
title
body
cta_label
template_html nullable
template_text
priority
communication_class
approval_status
version
provider_template_id nullable
provider_approval_status nullable
created_at
updated_at
```

## 24.2 notification_events

```text
id
uuid
event_type
communication_type
actor_id nullable
recipient_user_id nullable
recipient_contact nullable
recipient_type
related_resource_type nullable
related_resource_id nullable
payload_json
priority
status
requires_acknowledgement
acknowledgement_status
acknowledged_by nullable
acknowledged_at nullable
acknowledgement_deadline nullable
escalation_chain_id nullable
created_at
updated_at
```

## 24.3 notification_deliveries

```text
id
uuid
notification_event_id
channel
recipient
provider
status
attempt_count
sent_at nullable
delivered_at nullable
read_at nullable
failed_at nullable
error_code nullable
error_message nullable
created_at
updated_at
```

## 24.4 notification_preferences

```text
id
user_id
category
email_enabled
whatsapp_enabled
sms_enabled
push_enabled
voice_enabled
dashboard_enabled
quiet_hours_json nullable
language
created_at
updated_at
```

## 24.5 escalation_chains

```text
id
uuid
name
event_type
facility_id nullable
department_id nullable
steps_json
active
created_at
updated_at
```

## 24.6 action_tasks

```text
id
uuid
task_type
title
description
assigned_to nullable
assigned_role nullable
facility_id nullable
organization_id nullable
patient_id nullable
related_resource_type nullable
related_resource_id nullable
priority
status
due_at nullable
acknowledged_at nullable
completed_at nullable
escalation_chain_id nullable
created_at
updated_at
```

## 24.7 voice_notification_jobs

```text
id
uuid
notification_event_id
recipient_phone
voice_template_id
status
attempt_count
scheduled_at
sent_at nullable
acknowledged_at nullable
failed_at nullable
created_at
updated_at
```

## 24.8 message_threads

```text
id
uuid
thread_type
context_type nullable
context_id nullable
organization_id nullable
facility_id nullable
patient_id nullable
title
priority
status
created_by
assigned_to nullable
legal_hold
created_at
updated_at
closed_at nullable
```

## 24.9 message_thread_participants

```text
id
thread_id
user_id
role_in_thread
status
last_read_at nullable
muted_until nullable
created_at
updated_at
```

## 24.10 messages

```text
id
uuid
thread_id
sender_id
message_type
body
status
edited_at nullable
deleted_for_sender_at nullable
created_at
updated_at
```

## 24.11 message_attachments

```text
id
message_id
file_path
file_name
mime_type
file_size
classification
scan_status
encrypted
created_at
updated_at
```

## 24.12 broadcasts

```text
id
uuid
broadcast_type
title
body
target_type
target_ids_json
priority
language
requires_acknowledgement
status
publish_at nullable
expires_at nullable
created_by
created_at
updated_at
```

---

# 25. API Endpoints

## 25.1 Notifications

```text
GET  /api/v1/notifications
GET  /api/v1/notifications/unread-count
POST /api/v1/notifications/{id}/mark-read
POST /api/v1/notifications/{id}/acknowledge
POST /api/v1/notifications/mark-all-read
POST /api/v1/notifications/{id}/archive
GET  /api/v1/notification-preferences
PUT  /api/v1/notification-preferences
```

## 25.2 Tasks

```text
GET  /api/v1/tasks
GET  /api/v1/tasks/{id}
POST /api/v1/tasks/{id}/acknowledge
POST /api/v1/tasks/{id}/complete
POST /api/v1/tasks/{id}/assign
POST /api/v1/tasks/{id}/escalate
```

## 25.3 Admin Templates and Delivery

```text
GET  /api/v1/admin/notification-templates
POST /api/v1/admin/notification-templates
PUT  /api/v1/admin/notification-templates/{id}
POST /api/v1/admin/notification-templates/{id}/submit-review
POST /api/v1/admin/notification-templates/{id}/approve
POST /api/v1/admin/notification-templates/{id}/publish
POST /api/v1/admin/notification-templates/{id}/rollback
GET  /api/v1/admin/notification-deliveries
POST /api/v1/admin/notification-deliveries/{id}/retry
```

## 25.4 Escalation

```text
GET  /api/v1/admin/escalation-chains
POST /api/v1/admin/escalation-chains
PUT  /api/v1/admin/escalation-chains/{id}
POST /api/v1/admin/escalation-chains/{id}/activate
POST /api/v1/admin/escalation-chains/{id}/deactivate
```

## 25.5 Messaging

```text
GET  /api/v1/messages/threads
POST /api/v1/messages/threads
GET  /api/v1/messages/threads/{id}
POST /api/v1/messages/threads/{id}/messages
POST /api/v1/messages/threads/{id}/participants
POST /api/v1/messages/threads/{id}/assign
POST /api/v1/messages/threads/{id}/close
POST /api/v1/messages/threads/{id}/reopen
POST /api/v1/messages/{id}/edit
POST /api/v1/messages/{id}/delete-for-me
POST /api/v1/messages/{id}/report
POST /api/v1/messages/{id}/attachments
```

## 25.6 Broadcasts

```text
GET  /api/v1/broadcasts
POST /api/v1/admin/broadcasts
PUT  /api/v1/admin/broadcasts/{id}
POST /api/v1/admin/broadcasts/{id}/publish
POST /api/v1/admin/broadcasts/{id}/cancel
POST /api/v1/broadcasts/{id}/acknowledge
```

---

# 26. Permissions

## 26.1 Notifications

```text
notifications.view_own
notifications.manage_preferences
notifications.manage_templates
notifications.view_delivery_logs
notifications.retry_delivery
notifications.send_voice
notifications.manage_escalations
notifications.acknowledge_critical
```

## 26.2 Tasks

```text
tasks.view_own
tasks.assign
tasks.acknowledge
tasks.complete
tasks.escalate
tasks.manage_facility
```

## 26.3 Messaging

```text
messages.view_own
messages.create_thread
messages.send
messages.attach_file
messages.view_patient_thread
messages.view_facility_thread
messages.view_admin_thread
messages.assign_thread
messages.close_thread
messages.moderate
messages.report
messages.export
```

## 26.4 Broadcasts

```text
broadcasts.view
broadcasts.create
broadcasts.publish
broadcasts.cancel
broadcasts.acknowledge
```

---

# 27. Audit Requirements

Audit events:

```text
notification_event_created
notification_sent
notification_failed
notification_suppressed
notification_read
notification_acknowledged
notification_preference_changed
notification_template_created
notification_template_approved
notification_template_published
voice_notification_sent
voice_notification_acknowledged
task_created
task_acknowledged
task_completed
task_escalated
message_thread_created
message_sent
message_read
message_edited
message_deleted_for_view
message_reported
message_attachment_uploaded
message_attachment_downloaded
message_thread_assigned
message_thread_closed
message_thread_escalated
broadcast_created
broadcast_published
broadcast_acknowledged
```

Audit fields:

```text
actor_id
recipient_id
channel
event_type
template_id
thread_id nullable
message_id nullable
task_id nullable
resource_type nullable
resource_id nullable
ip_address
user_agent
timestamp
result
reason
```

---

# 28. Error Codes

```text
NOTIFICATION_TEMPLATE_NOT_FOUND
NOTIFICATION_CHANNEL_DISABLED
NOTIFICATION_SUPPRESSED_BY_PRIVACY_RULE
NOTIFICATION_SUPPRESSED_BY_USER_PREFERENCE
NOTIFICATION_DELIVERY_FAILED
NOTIFICATION_PROVIDER_UNAVAILABLE
NOTIFICATION_ACKNOWLEDGEMENT_REQUIRED
VOICE_NOTIFICATION_NOT_ALLOWED
TASK_NOT_FOUND
TASK_ACCESS_DENIED
TASK_ALREADY_COMPLETED
ESCALATION_CHAIN_NOT_FOUND
MESSAGE_THREAD_NOT_FOUND
MESSAGE_ACCESS_DENIED
MESSAGE_RECIPIENT_NOT_ALLOWED
MESSAGE_ATTACHMENT_BLOCKED
MESSAGE_THREAD_CLOSED
MESSAGE_CONTEXT_REQUIRED
MESSAGE_PATIENT_RELATIONSHIP_REQUIRED
BROADCAST_ACCESS_DENIED
BROADCAST_ALREADY_PUBLISHED
```

---

# 29. Testing Requirements

Required tests:

1. Welcome email renders correctly.
2. OTP email does not expose sensitive data.
3. Lab result email does not include result value.
4. Critical lab alert creates notification, task, and acknowledgement requirement.
5. Critical lab alert escalates if unacknowledged.
6. Prescription email does not include full medication details unless secure policy allows.
7. WhatsApp consent request is privacy-safe.
8. SMS fallback works when WhatsApp fails.
9. Voice alert does not expose patient details.
10. Voice respects opt-in and urgent override rules.
11. User preferences suppress allowed notifications.
12. Mandatory security alerts cannot be disabled.
13. Quiet hours suppress normal notifications.
14. Critical alerts can bypass quiet hours.
15. Deduplication prevents repeated non-critical spam.
16. Digest groups low-priority updates.
17. Template approval workflow works.
18. Template rollback works.
19. WhatsApp provider approval status is stored.
20. French templates render correctly.
21. Email plain-text fallback exists.
22. Email renders in mobile layout.
23. Patient cannot message random doctor.
24. Doctor cannot message unrelated patient.
25. Doctor can message nurse in same facility/care context.
26. Lab can message doctor about relevant lab order.
27. Pharmacy can message doctor about prescription clarification.
28. Insurance can message facility about claim only.
29. Public health thread does not expose patient identity by default.
30. Message attachments are scanned.
31. Executable files are blocked.
32. Message edit history is preserved.
33. Closed thread blocks new messages unless reopened.
34. Unauthorized user cannot view thread.
35. Message report creates moderation case.
36. Broadcast can target facility/department/partner group.
37. Broadcast acknowledgement works where required.
38. Legal hold prevents deletion.
39. Audit events are created.
40. No external notification includes sensitive clinical details.

---

# 30. Acceptance Criteria

The module is complete when:

1. Notifications, tasks, alerts, messages, broadcasts, and deliveries are separated.
2. Email templates are styled, responsive, branded, and versioned.
3. WhatsApp templates include provider approval model.
4. SMS fallback exists.
5. Dashboard notifications exist.
6. Task inbox exists.
7. Clinical acknowledgement exists.
8. Escalation chains exist.
9. Voice notification rules exist.
10. Internal messaging exists.
11. Broadcast announcements exist.
12. Preferences exist.
13. Mandatory notification rules exist.
14. Quiet hours exist.
15. Anti-spam, dedupe, and digest rules exist.
16. Delivery providers are abstracted.
17. Delivery logs exist.
18. Retry/failover exists.
19. Template approval and rollback exist.
20. English/French templates exist.
21. External notifications are privacy-safe.
22. Clinical details stay inside secure authenticated views.
23. Patient-to-provider messaging is controlled by care relationship.
24. Staff messaging is facility/role/context-based.
25. Hospital-to-hospital messaging supports referrals/transfers.
26. Insurance messaging requires claim/preauthorization context.
27. Public health messaging protects patient identity by default.
28. Attachments are scanned, controlled, and audited.
29. Message moderation/reporting exists.
30. Retention/legal hold exists.
31. All sensitive actions are audited.
32. Tests cover safety, delivery, templates, escalation, tasks, messaging, broadcasts, and retention.

---

# 31. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md, docs/identity/OPESCARE_MEDICAL_ID_SYSTEM_FINAL.md, docs/integration/OPESCARE_CONNECT_PLATFORM.md, and docs/communications/OPESCARE_COMMUNICATION_ALERTS_TASKS_MESSAGING_SYSTEM.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS notification code, templates, UI, database, messaging model, or assumptions.

Task: Create the OpesCare Communication, Alerts, Tasks, Notifications, and Messaging foundation.

Scope:
1. Create module placeholders:
   - app/Modules/Communications
   - app/Modules/Notifications
   - app/Modules/Tasks
   - app/Modules/Messaging
   - app/Modules/Broadcasts

2. Create docs/communications folder if missing.

3. Add model placeholders:
   - NotificationTemplate
   - NotificationEvent
   - NotificationDelivery
   - NotificationPreference
   - EscalationChain
   - ActionTask
   - VoiceNotificationJob
   - MessageThread
   - MessageThreadParticipant
   - Message
   - MessageAttachment
   - Broadcast

4. Add provider interfaces:
   - EmailProvider
   - WhatsAppProvider
   - SmsProvider
   - PushProvider
   - VoiceProvider

5. Add service placeholders:
   - CommunicationRouterService
   - NotificationService
   - TaskService
   - AlertEscalationService
   - EmailNotificationService
   - WhatsAppNotificationService
   - SmsNotificationService
   - PushNotificationService
   - VoiceNotificationService
   - NotificationPreferenceService
   - NotificationTemplateRenderer
   - MessagingService
   - MessagePermissionService
   - MessageAttachmentService
   - BroadcastService

6. Add routes for:
   - notification center
   - task inbox
   - acknowledgement
   - preferences
   - admin templates
   - delivery logs
   - escalation chains
   - messaging
   - attachments
   - broadcasts

7. Add base email template with OpesCare design system.

8. Add first bilingual templates:
   - welcome patient
   - OTP
   - lab result available
   - critical lab provider alert
   - prescription update
   - consent request
   - emergency access used
   - medicine reservation update
   - blood availability update
   - insurance claim update
   - partner application submitted
   - API sync failed

9. Add privacy rule that external notifications cannot include sensitive clinical details.

10. Add task/alert separation:
   - critical clinical events create both alert and action task
   - acknowledgement required for critical alerts

11. Add escalation chain placeholders.

12. Add messaging boundaries:
   - patient cannot message random doctor
   - doctor cannot message unrelated patient
   - insurance messaging requires claim context
   - public health threads protect patient identity by default

13. Add attachment scan placeholder.

14. Add broadcast placeholders.

15. Add tests proving:
   - lab result email does not include result value
   - critical lab alert creates acknowledgement task
   - escalation triggers if unacknowledged
   - WhatsApp consent request is privacy-safe
   - notification preferences suppress allowed notification
   - mandatory security alert cannot be disabled
   - patient cannot message random doctor
   - doctor can message nurse in same facility context
   - insurance cannot message without claim context
   - unauthorized user cannot view thread
   - message attachment requires scan placeholder
   - broadcast can target facility group
   - audit events are created

16. Do not implement real external provider credentials in this task.
17. Do not send real WhatsApp/SMS/voice in local/demo mode.
18. Do not expose patient data in placeholder responses.
19. Open a PR with summary, files created, template previews, tests, risks, and next recommended tasks.
```

---

# 32. Final Rule

OpesCare communication must be beautiful, clear, secure, actionable, and privacy-safe.

The correct model is:

```text
notifications inform
tasks require action
alerts warn and escalate
messages support secure conversation
broadcasts announce to groups
external channels stay minimal
clinical details stay inside secure views
critical events require acknowledgement
unsafe or unauthorized messages are blocked
every delivery and sensitive action is traceable
```

If a communication can expose sensitive patient information to the wrong person or through the wrong channel, it must be blocked, rewritten, or forced into secure in-app viewing.
