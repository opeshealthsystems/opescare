# 02 — Components (exact)

Build these as reusable widgets in `lib/core/ui/`. Values transcribed from the preview CSS. All reference `AppColors`/`AppRadii`/`AppShadows`/`AppType` from `01`. Icons: the preview uses Lucide (already in the app via `lucide_icons`); icon name is noted per component.

## Status pill — `StatusPill`
CSS `.pill`: padding 3×9, radius full, `AppType.pill` (10/w700/ls .04em). Variants (bg / text):
- green: `successBg` / `successDark` · amber: `warningBg` / `warningText(#92400E)` · red: `dangerBg` / `dangerText(#991B1B)` · blue: `infoBg` / `infoText(#1E40AF)` · gray: `divider` / `text2`.
```dart
class StatusPill extends StatelessWidget {
  final String label; final Color bg; final Color fg;
  const StatusPill(this.label, {required this.bg, required this.fg, super.key});
  factory StatusPill.green(String l) => StatusPill(l, bg: AppColors.successBg, fg: AppColors.successDark);
  factory StatusPill.amber(String l) => StatusPill(l, bg: AppColors.warningBg, fg: AppColors.warningText);
  factory StatusPill.red(String l)   => StatusPill(l, bg: AppColors.dangerBg,  fg: AppColors.dangerText);
  factory StatusPill.blue(String l)  => StatusPill(l, bg: AppColors.infoBg,    fg: AppColors.infoText);
  factory StatusPill.gray(String l)  => StatusPill(l, bg: AppColors.divider,   fg: AppColors.text2);
  @override Widget build(BuildContext c) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
    decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(AppRadii.full)),
    child: Text(label, style: AppType.pill.copyWith(color: fg)));
}
```

## List row — `AppListRow`
CSS `.list-row`: padding 13×16, bottom divider 1px (`divider`). Left icon box 40×40 radius `md`, tinted bg + colored Lucide icon (stroke 2). Title `listTitle`, sub `listSub`. Optional trailing (pill / chevron / mono value).
- Icon tint pairs used: blue `primary50`/`primary`; green `#F0FDF4`/`success`; amber `warningBg`/`#D97706`; red `dangerBg`/`danger`; neutral `surfaceMuted`/`text2`.

## Quick-action button — `QuickActionButton`
CSS `.quick-btn` in a 4-col grid (gap 10, margin 12×16). Card: `surface`, 1px `divider`, radius `lg`, padding 14×8, column centered, gap 7. Icon box 38×38 radius `md` tinted; label `AppType` 10/w600/`text2`/center. The four home actions: Book Appt (calendar, primary50/primary), Lab Results (flask, #F0FDF4/success), Insurance (shield, primary50/primary), Documents (file, warningBg/#D97706).

## Stat card — `StatCard`
CSS `.stat-card` in 3-col grid (gap 10, margin 12×16). `surface`, 1px `divider`, radius `lg`, padding 14×10, center. Number = `AppType.monoStat` (Geist 24/w700/primary); label = 10/w600/`text3`/ls .04em, marginTop 5.

## Health ID card — `HealthIdCard`
CSS `.hid-card`: margin 16, radius 20, **gradient** (gradStart→gradEnd 135°), padding 20, `AppShadows.blue`. Two faint white circles as background décor (200px top-right −60/−60 at 5% white; 140px bottom-left at 4%) — use a `Stack` with `ClipRRect`.
Layout (Row, gap 16):
- Left column (info):
  - Top row: brand (30×30 rounded-8 white@18% box + heart icon white + "OpesCare" 13/w700 white) ↔ verified badge (green@20% bg, green@40% border, full radius, 6px green dot `#34D399` + "VERIFIED" 10/w700 `#6EE7B7` ls .06em).
  - "FULL NAME" label (9/w600 white@50% ls .10em UPPER) → name 18/w800 white (two lines), mb 12.
  - "HEALTH ID" label → id `AppType.monoId` white (e.g. `OPC·2847·9301·CM`), mb 14.
  - 2×2 meta grid (gap 8): each item = label 9/w600 white@50% UPPER + value 12/w700 white@95%. (DOB, Blood Group, Sex, Country.)
- Right: QR box 88×88 white, radius 12, padding 10, containing the QR (use `qr_flutter` `QrImageView` 68×68, eyeColor/dataModuleColor = primary on white).

## Gradient banner (compact Health ID) — `HealthIdBanner`
Home screen banner. margin 14×16, gradient, radius `lg`, padding 14×16, `AppShadows.blue`, Row gap 14: left = "HEALTH ID" tiny label white@60% + mono id 14/w700 white ls .10em; right = white@15% rounded-10 pill with qr icon + "Scan ID" 12/w700 white.

## Buttons — `AppButton`
CSS `.btn`: padding 11×22, radius `lg`, `AppType.buttonText`, optional leading icon (size 15, gap 8). Variants:
- **primary**: bg `primary`, fg white. (Full-width CTA variant: radius `lg`, padding 11–13, centered, used as gradient `phone-btn-primary` = gradient bg.)
- **secondary**: bg `primary50`, fg `primary`, border 1.5px `primary200`.
- **outline**: transparent, fg `text`, border 1.5px `divider` (hover → primary).
- **ghost**: bg `surfaceMuted`, fg `text2`.
- **danger**: bg `dangerBg`, fg `danger`.
- **phone-btn-primary** (full-width gradient): margin 16, gradient, radius `lg`, padding 13×16, centered, white 14/w700.
- **phone-btn-outline**: `surface`, 1.5px `divider`, radius `lg`, text 14/w600 `text2`.

## Input — `AppInput`
CSS `.input`: full width, `surface`, border 1.5px `divider`, radius `lg`, padding 12×14, text 14, `text`. **Focus**: border `primary` + 3px halo `rgba(21,101,192,.12)` → in Flutter use `focusedBorder` + a subtle outer `BoxShadow`/`Container` glow.
Label above (`.input-label` = `AppType.label`, mb 6). Mono variant for IDs (fontFamily GeistMono, ls .06em). "Select"-style field = same box with trailing chevron-down (Lucide `chevron-down`, 12, `text3`).

## Alert — `AppAlert`
CSS `.alert`: Row gap 12, padding 14×16, radius `lg`, text 14. Leading Lucide icon (18, stroke 2.2, `currentColor`). Variants (bg / text / icon):
- success `successBg` / `#065F46` (check-circle) · warning `warningBg` / `#92400E` (alert-triangle) · danger `dangerBg` / `#991B1B` (alert-circle) · info `infoBg` / `#1E40AF` (info). Body supports bold lead-in (`<strong>`) then regular.

## Bottom navigation — `AppBottomNav`
CSS `.bottom-nav`: `surface`, top 1px `divider`, padding `10 0 16`. Each item: column, gap 4, Lucide icon 20 (active stroke `primary`, else `text3`), label 10/w600 (active `primary`, else `text3`), and a 4px primary **dot** under the active item only. Icons: Home `home`, Health ID `heart` ⚠️(preview uses heart — but the design-system-hardening spec calls for `id-card`; confirm with product), Insurance `shield`, Family `users`, Care Map `navigation`, Profile `user`.

## Family member card — `FamilyCard`
CSS `.fam-card`: `surface`, 1px `divider`, radius `lg`, padding 14, margin 0×16 mb 10, Row gap 12. Avatar 44 circle, tinted bg + initial 16/w800. Name `fam-name` 14/w700; rel 11/`text3`; chips row (gap 5): `.chip` 9/w700 padding 2×7 radius full — rx `#FEF3C7`/`#92400E`, appt `#EFF6FF`/`#1E40AF`, alert `#FEE2E2`/`#991B1B`, ok `#D1FAE5`/`#065F46`. Trailing chevron-right 14 `text3`. Pending variant: opacity .7, dashed border, amber "Pending" pill.

## Facility card (Care Map) — `FacilityCard`
CSS `.facility-card`: padding 13×16, bottom divider. Icon box 42×42 radius `md` tinted. Name 14/w600; meta 11/`text3`; optional chip row (`Connected` ok-chip, `★ 4.3`, "Open now"). Trailing distance = `AppType.monoSmall` `primary` (e.g. `1.2 km`).

## Care plan card — `CarePlanCard`
CSS `.plan-card`: `surface`, 1px `divider`, radius `lg`, padding 14, margin 0×16 mb 10. Title 14/w700 + meta 12/`text3`; status pill top-right. Goal pills row (wrap, gap 6): rounded-full 10/w600, tinted (primary50/primary, successBg/successDark, warningBg/#92400E). Progress: label row ("Goals completed" 10/text3 ↔ "4 / 6" 10/w700 primary) + bar (`.plan-progress-bar` height 4, track `divider`, fill `primary`, radius 2). Footer (top border): calendar icon + "Next check-in: <date bold>" or HbA1c mono value + "View Labs →".

## OTP boxes — `OtpBoxes`
CSS `.otp-box`: 6 boxes, flex gap 7, each height 52, border 1.5px `divider`, radius `lg`, `surface`, char `GeistMono` 20/w700. Filled: border `primary`, bg `primary50`, text `primary`. Cursor: border `primary` + 3px halo.

## Lab value hero + range bar — `LabValueHero`
CSS `.lab-hero`: centered, value `AppType.monoLabHero` (Geist 48/w700) colored by status (`success`/`danger`/`warning`), unit row 13/`text2` below. Range bar `.lab-range-track` height 6 radius 3 track `divider`; green zone overlay `rgba(16,185,129,.22)`; dot 12 circle white-bordered, colored by status; min/max labels `.lab-range-lbl` mono 9/`text3`.

## Timeline item — `TimelineItem`
CSS `.tl-item`: Row gap 10, padding 0×16 pb 16. Left: dot 30 circle (tinted by event type, Lucide icon inside) + vertical `.tl-line` 2px `divider` flex. Body: date 10/w600 `text3` ls .05em; title 13/w700; meta 11/`text3`.

## Consent card — `ConsentCard`
CSS `.consent-card`: radius `lg`, 1px `divider`, `surface`, margin 0×16 mb 10. Pending variant: border `primary200`, bg `primary50`. Body content then `.consent-actions` row (top divider): two half buttons — Approve (`primary`, 13/w700) | Deny (`danger`, left divider).

## Tab bar — `AppTabBar`
CSS `.tab-bar`: bottom 1px `divider`, horizontal scroll, items padding `10 12 8`, 2px bottom border (active `primary`, else transparent), text 12/w600 (active `primary`, else `text2`), optional count badge (primary bg, white, 9/w700, radius full).

## Filter chips — `FilterChips`
CSS `.filter-chip`: scroll row, padding 5×12, radius full, 1px `divider`, `surface`, 11/w600 `text2`. Active: bg `primary`, border `primary`, white.

## Segmented toggle — `SegmentedToggle`
Used on Login (Phone/Email), Add Member (New/Link), Invite (Phone/Email/QR). Container `surfaceMuted`, radius `lg`, padding 4, 1px `divider`. Active segment: `surface`, radius 10, `AppShadows.sm`, text 12/w700 `text`; inactive 12/w600 `text3`.

## Access-level selector — `AccessLevelSelector`
Invite screen. 3 equal cards gap 8: selected = 1.5px `primary` border, `primary50` bg, title 11/w700 `primary` + sub 10/primary@70%. Unselected = 1px `divider`, `surface`, title 11/w700 `text2` + sub 10/`text3`. (View Only / Guardian / Full.)

## Toggle switch — `AppSwitch` (Settings)
40×22 track radius 11, thumb 16 white, padding 3. On = `primary` track, thumb right. Use Flutter `Switch` themed to `activeColor: AppColors.primary` or a custom 40×22 to match exactly.

## Section header (in-screen) — `PhoneSectionHeader`
CSS `.phone-section-header`: padding 14×16 8, Row space-between: title `AppType.sectionTitle` ↔ optional "See all" 12/w600 `primary` (or a count 11/`text3`).
