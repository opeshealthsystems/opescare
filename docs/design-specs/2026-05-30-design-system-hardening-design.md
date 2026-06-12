# Design System Hardening — Sub-project A

**Date:** 2026-05-30
**App:** `apps/mobile-patient` (Flutter / Riverpod)
**Scope:** Fix deprecated `.withOpacity()` API calls using named color tokens, resolve icon semantic collisions per screen, swap Health ID icon for a more appropriate health-tech icon.

---

## Context

After the Phase 1–5 production readiness work, `flutter analyze` reports info-level warnings for `withOpacity` (deprecated in Flutter 3.x+, replaced by `.withValues(alpha:)`). Additionally, a screen-by-screen icon audit found two semantic collisions and one icon that doesn't reflect health-tech standards.

---

## Section 1 — New AppColors Opacity Tokens

Add 7 static color constants to `lib/core/theme/app_colors.dart`.

These replace every `withOpacity()` call in the codebase with a named, inspectable token:

```dart
// Transparent variants — pre-computed to avoid runtime color computation
static const Color dangerBorder   = Color(0x66EF4444); // danger @ 40% — border rings
static const Color dangerSurface  = Color(0x4DEF4444); // danger @ 30% — surface tint
static const Color primarySurface = Color(0x4D1565C0); // primary500 @ 30% — surface tint
static const Color onPrimarySubtle = Color(0xBFFFFFFF); // white @ 75% — subdued label on primary bg
static const Color whiteOverlay   = Color(0x26FFFFFF); // white @ 15% — chip on gradient card
static const Color warningBorder  = Color(0x4DF59E0B); // warning @ 30% — border rings
static const Color warningSurface = Color(0x26F59E0B); // warning @ 15% — surface tint
```

One call is kept dynamic because the base color is computed at runtime:
```dart
// timeline_screen.dart — _typeColor is dynamic
color: _typeColor(event.type).withValues(alpha: 0.1),
```

---

## Section 2 — Icon Semantic Collision Fixes

### Collision 1: `home_screen.dart` — `LucideIcons.shield`
- **Problem:** Used for both the "Consents" stat card AND the "Secured with end-to-end encryption" trust badge at the bottom of the login screen.
- **Fix:** Trust badge in `login_screen.dart` → `LucideIcons.lockKeyhole`

### Collision 2: `settings_screen.dart` — `LucideIcons.bell`
- **Problem:** Used as the "Notifications" section title icon AND as the individual "Enable notifications" switch row icon — visually indistinguishable at a glance.
- **Fix:** Section title icon → `LucideIcons.bellRing` (distinct from the individual row `LucideIcons.bell`)

### Health-Tech Icon Upgrade: Health ID card
- **Current:** `LucideIcons.creditCard` — implies payment card
- **Better:** `LucideIcons.idCard` — semantically correct for a health identity document
- **Changed in:** `home_screen.dart` (Health ID banner), `main_shell.dart` (bottom nav tab), `health_id_screen.dart` (wherever creditCard is used for Health ID concept)

---

## Section 3 — withOpacity Replacement Map

Exact substitutions to make in each file:

### `lib/features/access_logs/presentation/access_logs_screen.dart`
```dart
// Before:
AppColors.danger.withOpacity(0.4)
// After:
AppColors.dangerBorder
```

### `lib/features/consent/presentation/consent_screen.dart`
```dart
// Before:
AppColors.warning.withOpacity(0.4)
// After:
AppColors.warningBorder
```

### `lib/features/health_id/presentation/health_id_screen.dart`
```dart
// Before:
AppColors.primary500.withOpacity(0.3)
// After:
AppColors.primarySurface
```

### `lib/features/home/presentation/home_screen.dart`
```dart
// Before (3 occurrences):
AppColors.textOnPrimary.withOpacity(0.75)  → AppColors.onPrimarySubtle
Colors.white.withOpacity(0.15)             → AppColors.whiteOverlay
AppColors.warning.withOpacity(0.3)         → AppColors.warningBorder
AppColors.warning.withOpacity(0.15)        → AppColors.warningSurface
```

### `lib/features/labs/presentation/labs_screen.dart`
```dart
AppColors.danger.withOpacity(0.4)  → AppColors.dangerBorder
```

### `lib/features/labs/presentation/lab_detail_screen.dart`
```dart
AppColors.danger.withOpacity(0.3)  → AppColors.dangerSurface
```

### `lib/features/timeline/presentation/timeline_screen.dart`
```dart
// Before (dynamic — cannot use a static token):
_typeColor(event.type).withOpacity(0.1)
// After:
_typeColor(event.type).withValues(alpha: 0.1)
```

---

## Out of Scope

- Replacing `Colors.white` with `AppColors.textOnPrimary` (deferred)
- Adding `const` to widgets flagged by `prefer_const` lint
- `loading_skeleton.dart` shimmer gradient (shimmer package owns those colors internally)

---

## Verification

After changes:
```powershell
flutter analyze lib/core/theme/app_colors.dart lib/features/
```
Expected: Zero `withOpacity` deprecation warnings. All existing tests pass.
