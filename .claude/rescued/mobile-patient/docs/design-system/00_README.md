# OpesCare Patient — Design System Implementation Spec

**Source of truth:** `apps/mobile-patient/design-preview.html` (open it in a browser; it has a Dark toggle).
**Goal:** make the Flutter app match that preview **exactly** — same palette, type, components, and screens.
**Audience:** the Flutter developer. Every value here is transcribed from the preview's CSS.

This spec is split into:

1. **`01_TOKENS_AND_THEME.md`** — exact colors, radii, shadows, spacing, typography, plus ready-to-paste Flutter `AppColors` / `AppRadii` / `AppShadows` / `AppType` and `ThemeData`. Also the font bundling (Plus Jakarta Sans + Geist Mono).
2. **`02_COMPONENTS.md`** — every reusable component (pill, list row, quick action, Health ID card, gradient banner, bottom nav, buttons, inputs, alerts, family/facility/plan cards, OTP boxes, lab value + range bar, timeline item, consent card, tab bar, filter chips, segmented toggle, access-level selector) with exact values and Flutter implementations.
3. **`03_SCREENS.md`** — a blueprint for each of the ~25 screens, mapped to its existing `lib/features/.../*_screen.dart` file.

## Ground rules for exact fidelity

- **Use the tokens, never hardcode.** Every color/space/radius comes from `AppColors`/`AppRadii`/`AppType`.
- **Mono font is mandatory for data:** Health IDs, lab values, prices (XAF), policy numbers, timestamps, stat numbers — all `Geist Mono`. Everything else is `Plus Jakarta Sans`.
- **Phone frame ≈ 340 px wide** in the mockup; treat horizontal screen padding as **16 px** (the preview's `margin:…16px`), card inner padding **14–20 px** as noted per component.
- **Bottom nav** in the preview is **4 tabs**, and the active tab varies by screen (Home / Health ID / Insurance / Profile, with Family and Care Map shown as active on their own screens). Confirm the final canonical 4–5 tab set with product — see `03_SCREENS.md` §Navigation.

## Fonts — bundle these (required)

The preview uses **Plus Jakarta Sans** (already available via `google_fonts`) and **Geist Mono** (may NOT be in your `google_fonts` version). To guarantee an exact match, **bundle both as assets** rather than relying on the network:

1. Download the families:
   - Plus Jakarta Sans: https://fonts.google.com/specimen/Plus+Jakarta+Sans (weights 300–800)
   - Geist Mono: https://fonts.google.com/specimen/Geist+Mono (weights 400–700) — or https://vercel.com/font
2. Put the `.ttf` files in `assets/fonts/`.
3. Declare them in `pubspec.yaml` (see `01_TOKENS_AND_THEME.md` §Fonts) and reference by `fontFamily`, dropping the `google_fonts` calls for these two so rendering is deterministic offline.

## How to work through it

1. Apply `01` (theme) first — it re-skins the whole app globally.
2. Build the reusable widgets from `02` into `lib/core/ui/` (or `lib/widgets/`).
3. Rebuild each screen per `03`, composing the `02` widgets. Run `flutter run` and compare to the matching phone in `design-preview.html` until pixel-equal.
4. Verify light **and** dark (the preview's `[data-dark]` palette is in `01`).

> Note: the preview is the canonical visual. Where this text and the preview ever disagree, the preview wins — re-open it and match.
