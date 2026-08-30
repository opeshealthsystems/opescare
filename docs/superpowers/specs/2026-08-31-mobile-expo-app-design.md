# OpesCare Patient App — Expo Rebuild (Design Spec)

**Date:** 2026-08-31
**Status:** Approved to build (user directive, 2026-08-31 — proceeding autonomously overnight)

## 1. Problem & context

OpesCare has a Flutter patient app (`apps/mobile-patient`) with 19 working feature
modules against a live backend, but the business decision is to ship the patient
app on **Expo/React Native** going forward. Flutter is frozen as a build reference
only — no further Flutter investment.

Separately, 173 AI-generated reference screens exist in `Mobile app screens/`
(freeze-package draft assets, per that folder's own README) that define the
target visual design, but are internally inconsistent — at least two different
brand languages appear across the set, plus some non-screen marketing assets
mixed in.

## 2. Decisions locked in this session

1. **Expo replaces Flutter.** One shipped patient app going forward. Flutter
   (`apps/mobile-patient`) stays in the repo, frozen, as a UI/feature reference —
   no renaming/deletion yet; that's a later cleanup once Expo reaches parity.
2. **Brand: gold/cream.** Canonical OpesCare visual language is the warm
   ivory-background, gold/amber-accent style with the heartbeat-in-ring logo and
   "One Health ID. Connected Care." tagline (seen in login, onboarding, splash-light,
   dashboard). This overrides: (a) the green/white "Medicine Finder" outlier style,
   and (b) the blue `#0F4C81`/`#1565C0` documented in `apps/api-laravel/CLAUDE.md`
   and Flutter's `app_colors.dart` — those are superseded by this new direction.
3. **Reference images are real design material**, not "maybe-rejected drafts" —
   the folder's own disclaimer language is set aside per user instruction. They
   still require triage: dedup, drop non-screen assets (marketing posters etc.),
   and reconcile the two conflicting styles down to the gold/cream one.
4. **"End to end" means real backend work, not just UI.** Where a reference image
   implies a feature the backend doesn't have (confirmed gap: **no Pharmacy/
   Medication module exists anywhere** in `apps/api-laravel`), that backend
   capability gets built — not mocked, not skipped.
5. **Priority order** (user's explicit ask): medication/drug/pharmacy finder and
   appointments are the most-needed features, ahead of secondary screens.
6. **No account creation performed by the assistant.** Per operating constraints,
   the assistant does not create login/demo accounts directly. A demo-patient
   credential (email+password on the existing 3 seeded demo patients) is deferred;
   the app is built against the live API contracts and real auth flow regardless,
   and can be logged into once a credential exists.

## 3. What already exists (reuse, don't rebuild)

`apps/api-laravel` already exposes a complete `/mobile/*` REST surface, live at
`https://opescare.cloud/api`, used today by the Flutter app:

- Auth: `POST /mobile/auth/login-email` (primary, email+password), legacy
  `POST /mobile/auth/login` (phone+PIN) → `/otp/verify` / `/otp/resend`,
  `POST /mobile/auth/refresh` (7-day grace).
- Patient profile (`/mobile/me`), timeline, health-ID card + temporary QR,
  allergies/clinical/immunizations, labs, prescriptions (view), appointments
  (list/show/book/cancel), facilities + slots, documents, settings + push
  tokens, family (members + invitations), care plans, surveys, medical record
  export (PDF/FHIR), insurance + marketplace + purchase, referrals,
  consent-requests (approve/deny/revoke), access logs, correction requests,
  data export requests, offline policies.
- Mobile Money callbacks (MTN/Orange) already exist at the platform level —
  relevant once Pharmacy reservation/payment is built.

**Gap confirmed:** no Pharmacy, Medicine, or Drug model/controller/route exists
anywhere in `app/Modules/*` or `routes/api.php`. The `Inventory` module is a thin
`Services/`-only stub, not a consumer-facing catalog. Medicine Finder is net-new:
data model, search, geolocation "nearby pharmacies," availability, and a
reservation flow (matching the reference screen's "Reserve This Medicine" /
"Upload Prescription" actions).

## 4. Architecture

- **Location:** new `apps/mobile-expo/` app. `apps/mobile-patient/` untouched.
- **Stack:** Expo + TypeScript, Expo Router (file-based nav, matches the 5-tab
  bottom nav seen in the dashboard reference: Home / Records / Health ID /
  Messages / Profile), NativeWind (Tailwind for RN) driven by a generated
  `theme.ts` design-token file, TanStack Query for all server state,
  Zustand for session/auth state, `expo-secure-store` for the bearer token,
  axios for HTTP.
- **Auth flow:** mirrors Flutter's — email+password primary, phone+PIN+OTP
  legacy, silent refresh via the 7-day grace endpoint. Token stored in
  SecureStore, attached via an axios interceptor, refreshed on 401.
- **Build/deploy:** EAS Build, using the existing authenticated Expo session
  on this machine (account `opesware`) — no interactive login needed.
- **i18n:** EN/FR via `expo-localization` + `i18next`, mirroring the Laravel
  `__('namespace.key')` convention and the project's 1:1 parity requirement.

## 5. Tooling built as part of this work

1. **Image triage script** (`tools/screens-triage/`): perceptual-hash
   clustering over the 173 images to group near-duplicates, filename + vision
   pass to tag each survivor with an app screen role, and flag/exclude
   non-screen assets (marketing posters, showcase graphics). Rejected/duplicate
   images are moved to `Mobile app screens/_archive/`, never hard-deleted.
2. **Design-token extractor**: samples actual pixel colors from the curated
   gold/cream reference set (background, primary gold, text colors, semantic
   colors) into `apps/mobile-expo/theme/tokens.ts` and the NativeWind config,
   rather than eyeballed hex guesses.
3. **Pixel-diff tool** (`tools/pixel-diff/`): captures a live render of a built
   screen and diffs it against its matched reference image (mismatch % +
   heatmap overlay), used as the parity gate before a screen is considered done.

## 6. Delivery phases

- **Phase 0 (sequential, foundation):** triage tool + design tokens + Expo
  scaffold + theme + navigation shell + API client + auth screens (splash,
  onboarding, login, OTP) wired to the real `/mobile/auth/*` endpoints +
  pixel-diff tool. Everything after this depends on it — built first, by the
  assistant directly, not parallelized.
- **Phase 1 (parallel fan-out, existing backend only):** Home dashboard,
  Health ID (card + QR), Appointments (list/detail/book/cancel + facility
  directory/slots), Records/Timeline, Prescriptions (view), Profile/Settings.
  Each screen agent works in an isolated git worktree against the Phase 0
  foundation, runs the pixel-diff tool against its reference image before
  returning, touches only its own screen/component files.
- **Phase 2 (new backend + client): Pharmacy/Medicine Finder.** Built as one
  coherent pass (not parallelized across multiple agents) to avoid migration/
  route collisions: `Pharmacy` Laravel module (facilities of type pharmacy
  already exist per `docs/accounts/staf accounts.md`'s facility list — reuse
  facility records where possible), `Medicine` catalog, stock/availability,
  geolocation search, reservation flow, then the matching Expo screens
  restyled to gold/cream (not the green mockup style).
- **Phase 3 (parallel fan-out, remaining secondary screens):** Insurance,
  Referrals, Family, Labs, Documents, Consent/Governance, Surveys, and any
  other screen categories the triage tool surfaces from the 173 images that
  aren't covered above.
- **Phase 4:** i18n parity pass, EAS build (Android + iOS), store-readiness
  checks, first downloadable build link.

## 7. Definition of done (per screen)

A screen is "done" when: it renders from real API data (no hardcoded mock
data in the shipped build), matches its reference image per the pixel-diff
tool within an agreed tolerance, uses only the shared theme/nav/API-client
primitives (no one-off styling), and — if it required a backend capability
that didn't exist — that capability is implemented and tested, not stubbed.

## 8. Explicitly out of scope for this spec

Renaming/removing the Flutter app, creating any login/demo account, and any
action requiring the assistant to send messages, publish content, or handle
credentials on the user's behalf.
