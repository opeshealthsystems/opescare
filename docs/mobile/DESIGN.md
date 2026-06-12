# Design System — OpesCare Patient

## Product Context
- **What this is:** A Flutter mobile health app for patients in Cameroon — digital health ID, medical records, insurance marketplace, labs, prescriptions, appointments, and consent management.
- **Who it's for:** Patients in Cameroon of all ages and backgrounds; both urban professionals and rural patients.
- **Space/industry:** Healthcare / digital health / patient-facing medical apps
- **Project type:** Flutter mobile app (Android primary)
- **Memorable thing:** "This is MY health, not the hospital's."

## Aesthetic Direction
- **Direction:** Professional Clinical — UHC-grade professionalism with warmth. Not corporate cold, not playful. The confidence of a premium healthcare platform.
- **Decoration level:** Intentional — gradient Health ID card is the hero; all other surfaces are clean.
- **Mood:** Serious, trustworthy, personal. When a patient opens this app they should feel their health data is safe and truly theirs.
- **Portal parity:** Matches the patient portal exactly — same Clinical Blue, same surface colors, same semantic palette.

## Typography

### Display / Hero — Plus Jakarta Sans
- **Role:** Screen titles, section headers, greeting text
- **Weight:** 700–800
- **Letter-spacing:** −0.2 to −0.5 em
- **Rationale:** More character than Inter while staying geometric and clean. Not on the overused list. Works at large sizes on mobile.
- **Loading:** `GoogleFonts.plusJakartaSans()`

### Body / UI — DM Sans
- **Role:** All body text, descriptions, labels, captions, buttons
- **Weight:** 400 (body), 600–700 (labels/buttons)
- **Rationale:** Clean, slightly warmer than Inter, great legibility on small mobile screens.
- **Loading:** `GoogleFonts.dmSans()`

### Data / Numbers — JetBrains Mono
- **Role:** Health ID numbers, lab values, prices (XAF), policy numbers, timestamps
- **Weight:** 500–700
- **Rationale:** Crisp monospace for numbers that patients need to read accurately. Tabular numbers prevent layout shifting.
- **Loading:** `GoogleFonts.jetBrainsMono()`

### Type Scale
| Style | Font | Size | Weight | Use |
|-------|------|------|--------|-----|
| h1 | Plus Jakarta Sans | 28px | 800 | Page heroes |
| h2 | Plus Jakarta Sans | 22px | 800 | Section titles |
| h3 | Plus Jakarta Sans | 18px | 700 | Card titles |
| h4 | Plus Jakarta Sans | 16px | 700 | Sub-sections |
| bodyLg | DM Sans | 16px | 400 | Prominent body |
| body | DM Sans | 14px | 400 | Standard body |
| bodySm | DM Sans | 13px | 400 | Secondary text |
| caption | DM Sans | 11px | 400 | Captions, meta |
| label | DM Sans | 11px | 700 | All-caps labels |
| button | DM Sans | 14px | 700 | Button text |
| monoLg | JetBrains Mono | 22px | 700 | Large numbers |
| mono | JetBrains Mono | 14px | 600 | Standard numbers |
| monoSm | JetBrains Mono | 12px | 500 | Small numbers |
| healthId | JetBrains Mono | 18px | 700 | Health ID on card |

## Color
- **Approach:** Portal-matched — identical to the patient web portal for brand consistency
- **Primary:** `#1565C0` — Clinical Blue, WCAG AAA on white (7.2:1)
- **Primary 600:** `#1044A0` — darker variant for gradients and hover
- **Primary 700:** `#0D3A7D` — darkest, QR code color
- **Primary 50:** `#EFF6FF` — tinted backgrounds, icon containers, chips
- **Background:** `#F3F4F6` — warm neutral background
- **Surface:** `#FFFFFF` — card surfaces
- **Surface Muted:** `#F9FAFB` — subtle differentiation
- **Divider:** `#E5E7EB` — borders and separators
- **Gradient:** `#1565C0` → `#1044A0` (top-left to bottom-right) — Health ID card, buttons

### Semantic
| Role | Color | Hex |
|------|-------|-----|
| Success | Emerald | `#10B981` |
| Success BG | Light emerald | `#D1FAE5` |
| Warning | Amber | `#F59E0B` |
| Warning BG | Light amber | `#FEF3C7` |
| Danger | Red | `#EF4444` |
| Danger BG | Light red | `#FEE2E2` |
| Info | Blue | `#3B82F6` |
| Info BG | Light blue | `#EFF6FF` |

### Text
| Role | Color |
|------|-------|
| Primary text | `#111827` |
| Secondary text | `#6B7280` |
| Muted text | `#9CA3AF` |
| On primary | `#FFFFFF` |

## Spacing
- **Base unit:** 8px
- **Density:** Comfortable — patients reading health data need breathing room
- **Min touch target:** 48px
- **Scale:** 4 · 8 · 12 · 16 · 20 · 24 · 32 · 40 · 48 · 64

## Layout
- **Approach:** Card-based — patients know this pattern in mobile health apps
- **Border radius:** 22px (Health ID card) · 14–16px (cards) · 12px (list items) · 10px (icon containers) · 20px (pills/badges)
- **Grid:** 4 columns for quick action grids, single column for list views

## Motion
- **Approach:** Minimal-functional — no decoration, only transitions that aid comprehension
- **Easing:** ease-out for entrances, ease-in for exits
- **Duration:** 150ms (state transitions), 250ms (navigation)

## Key Components

### Health ID Card
- Full-bleed gradient card (22px radius)
- QR code embedded on card (90×90px, white background, blue modules)
- Shows: Full Name, Health ID (mono), Date of Birth, Blood Group, Sex, Country, Verified badge
- Box shadow with 35% opacity primary color

### Generate Temporary QR Button
- Gradient button (matches card) below the Health ID card
- Icon + title + subtitle + chevron
- Opens bottom sheet with large QR (220px) and countdown timer

### Quick Actions Grid
- 4 columns × 2 rows
- Icon in colored rounded container (10px radius)
- Label below in 10px caption
- Links to: Labs, Appointments, Prescriptions, Consent, Documents, Access Logs, Insurance, Timeline

### Home Screen Health ID Banner
- Compact gradient banner (not full card)
- Shows Health ID number in mono font
- "Scan ID" chip on the right with QR code icon
- Taps through to full Health ID screen

## Decisions Log
| Date | Decision | Rationale |
|------|----------|-----------|
| 2026-05-31 | Replaced Inter with Plus Jakarta Sans (display) + DM Sans (body) | Inter is overused and generic. PJS has more character at same quality. DM Sans is warmer and equally readable. |
| 2026-05-31 | Added JetBrains Mono for all data/numbers | Health IDs, lab values, prices need a reliable monospace. Tabular numbers prevent layout shifts. |
| 2026-05-31 | Kept Clinical Blue (#1565C0) unchanged | Portal parity. Patients recognise OpesCare instantly on any surface. Consistency > novelty. |
| 2026-05-31 | QR code embedded directly on Health ID card | The card IS the identity document. QR belongs on the card, not below it. Matches real-world ID card conventions. |
| 2026-05-31 | Generate Temporary QR as prominent gradient button | Most-used action on the Health ID screen. Deserves the most visible treatment. |
| 2026-05-31 | 8-item quick actions grid on Health ID screen | Patients want single-tap access to their most-used features from the ID screen. |
