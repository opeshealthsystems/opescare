# Design System Hardening — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminate all deprecated `.withOpacity()` API calls by introducing named opacity tokens in `AppColors`, fix two per-screen icon semantic collisions, and upgrade the Health ID icon to a health-tech-appropriate glyph.

**Architecture:** Three focused tasks — (1) add static colour constants, (2) swap icons in four files, (3) replace every `withOpacity` call with the new tokens or `.withValues()`. No new files created; no business logic touched. The changes are purely cosmetic / API-compliance.

**Tech Stack:** Flutter 3.44+, Dart, `app_colors.dart` design token system, local `lucide_icons` package at `packages/lucide_icons/`

---

## File Map

| Action | Path | Why |
|--------|------|-----|
| Modify | `lib/core/theme/app_colors.dart` | Add 7 opacity tokens |
| Modify | `lib/features/auth/presentation/login_screen.dart` | `shield` → `lock` (trust badge collision) |
| Modify | `lib/features/settings/presentation/settings_screen.dart` | `bell` → `bellRing` (section title collision) |
| Modify | `lib/features/home/presentation/home_screen.dart` | `creditCard` → `fingerprint` (Health ID icon); 4× `withOpacity` → tokens |
| Modify | `lib/features/shell/presentation/main_shell.dart` | `creditCard` → `fingerprint` (nav tab) |
| Modify | `lib/features/access_logs/presentation/access_logs_screen.dart` | 1× `withOpacity` → `dangerBorder` |
| Modify | `lib/features/consent/presentation/consent_screen.dart` | 1× `withOpacity` → `warningBorder` |
| Modify | `lib/features/health_id/presentation/health_id_screen.dart` | 1× `withOpacity` → `primarySurface` |
| Modify | `lib/features/labs/presentation/labs_screen.dart` | 1× `withOpacity` → `dangerBorder` |
| Modify | `lib/features/labs/presentation/lab_detail_screen.dart` | 1× `withOpacity` → `dangerSurface` |
| Modify | `lib/features/timeline/presentation/timeline_screen.dart` | 1× `withOpacity` → `.withValues(alpha:)` |

---

## Task 1: Add opacity tokens to AppColors

**Files:**
- Modify: `lib/core/theme/app_colors.dart`

- [ ] **Step 1: Check current analyze baseline**

Run from `apps/mobile-patient`:
```powershell
flutter analyze lib/ 2>&1 | Select-String "withOpacity"
```
Expected: 10 matches (all info-level deprecation warnings).

- [ ] **Step 2: Add the 7 tokens**

In `lib/core/theme/app_colors.dart`, add after the `// Surfaces` section (after `divider`) and before `// Text`:

```dart
  // Opacity variants — pre-computed to avoid runtime withOpacity() calls
  // Alpha channel is the first two hex digits of the 8-digit colour value.
  static const Color dangerBorder    = Color(0x66EF4444); // danger   @ 40%
  static const Color dangerSurface   = Color(0x4DEF4444); // danger   @ 30%
  static const Color primarySurface  = Color(0x4D1565C0); // primary  @ 30%
  static const Color onPrimarySubtle = Color(0xBFFFFFFF); // white    @ 75%
  static const Color whiteOverlay    = Color(0x26FFFFFF); // white    @ 15%
  static const Color warningBorder   = Color(0x4DF59E0B); // warning  @ 30%
  static const Color warningSurface  = Color(0x26F59E0B); // warning  @ 15%
```

- [ ] **Step 3: Verify the file compiles**

```powershell
flutter analyze lib/core/theme/app_colors.dart
```
Expected: `No issues found!`

- [ ] **Step 4: Commit**

```powershell
git add lib/core/theme/app_colors.dart
git commit -m "feat(design): add 7 named opacity tokens to AppColors"
```

---

## Task 2: Fix icon semantic collisions

**Files:**
- Modify: `lib/features/auth/presentation/login_screen.dart`
- Modify: `lib/features/settings/presentation/settings_screen.dart`
- Modify: `lib/features/home/presentation/home_screen.dart`
- Modify: `lib/features/shell/presentation/main_shell.dart`

### Collision 1 — Login trust badge: `shield` → `lock`

`LucideIcons.shield` is used for the **Consent** tab in the bottom nav and the Consent stat card on the home screen. Using it again on the login screen for "end-to-end encryption" creates a semantic collision (shield = consent, not security).

- [ ] **Step 1: Fix login_screen.dart**

In `lib/features/auth/presentation/login_screen.dart`, find:
```dart
                    const Icon(LucideIcons.shield,
                        color: AppColors.neutral400, size: 14),
```
Replace with:
```dart
                    const Icon(LucideIcons.lock,
                        color: AppColors.neutral400, size: 14),
```

### Collision 2 — Settings notifications section title: `bell` → `bellRing`

`LucideIcons.bell` is used as **both** the section header icon AND the first switch row icon in the Notifications section.

- [ ] **Step 2: Fix settings_screen.dart**

In `lib/features/settings/presentation/settings_screen.dart`, find:
```dart
        _SectionTitle(icon: LucideIcons.bell, title: 'Notifications'),
```
Replace with:
```dart
        _SectionTitle(icon: LucideIcons.bellRing, title: 'Notifications'),
```

### Health ID icon upgrade — `creditCard` → `fingerprint`

`creditCard` implies a payment card. Health ID is a digital identity document — `fingerprint` (biometric identity) is semantically correct and available in the local lucide_icons package.

- [ ] **Step 3: Fix home_screen.dart Health ID banner icon**

In `lib/features/home/presentation/home_screen.dart`, find (inside `_HealthIdBanner.build`):
```dart
                const Icon(LucideIcons.creditCard, size: 36, color: Colors.white54),
```
Replace with:
```dart
                const Icon(LucideIcons.fingerprint, size: 36, color: Colors.white54),
```

- [ ] **Step 4: Fix main_shell.dart nav tab icon**

In `lib/features/shell/presentation/main_shell.dart`, find:
```dart
    _TabItem(icon: LucideIcons.creditCard, label: 'Health ID', index: 1),
```
Replace with:
```dart
    _TabItem(icon: LucideIcons.fingerprint, label: 'Health ID', index: 1),
```

- [ ] **Step 5: Verify build**

```powershell
flutter analyze lib/features/auth/presentation/login_screen.dart lib/features/settings/presentation/settings_screen.dart lib/features/home/presentation/home_screen.dart lib/features/shell/presentation/main_shell.dart
```
Expected: `No issues found!`

- [ ] **Step 6: Run tests**

```powershell
flutter test test/widget_test.dart test/features/auth/login_screen_test.dart -v
```
Expected: All pass. (The login screen test checks for 'Welcome back' text and form labels, not specific icons — so it will still pass.)

- [ ] **Step 7: Commit**

```powershell
git add lib/features/auth/presentation/login_screen.dart lib/features/settings/presentation/settings_screen.dart lib/features/home/presentation/home_screen.dart lib/features/shell/presentation/main_shell.dart
git commit -m "fix(icons): resolve semantic collisions — lock for trust badge, bellRing for section title, fingerprint for Health ID"
```

---

## Task 3: Replace withOpacity in access_logs, consent, health_id

**Files:**
- Modify: `lib/features/access_logs/presentation/access_logs_screen.dart`
- Modify: `lib/features/consent/presentation/consent_screen.dart`
- Modify: `lib/features/health_id/presentation/health_id_screen.dart`

- [ ] **Step 1: Fix access_logs_screen.dart**

Find (line ~93):
```dart
              ? AppColors.danger.withOpacity(0.4)
```
Replace with:
```dart
              ? AppColors.dangerBorder
```

- [ ] **Step 2: Fix consent_screen.dart**

Find (line ~135):
```dart
              ? AppColors.warning.withOpacity(0.4)
```
Replace with:
```dart
              ? AppColors.warningBorder
```

- [ ] **Step 3: Fix health_id_screen.dart**

Find (line ~66):
```dart
                color: AppColors.primary500.withOpacity(0.3),
```
Replace with:
```dart
                color: AppColors.primarySurface,
```

- [ ] **Step 4: Verify**

```powershell
flutter analyze lib/features/access_logs/ lib/features/consent/ lib/features/health_id/ 2>&1 | Select-String "withOpacity"
```
Expected: 0 matches for these three directories.

- [ ] **Step 5: Commit**

```powershell
git add lib/features/access_logs/presentation/access_logs_screen.dart lib/features/consent/presentation/consent_screen.dart lib/features/health_id/presentation/health_id_screen.dart
git commit -m "fix(design): replace withOpacity with named tokens in access_logs, consent, health_id"
```

---

## Task 4: Replace withOpacity in home_screen (4 occurrences)

**Files:**
- Modify: `lib/features/home/presentation/home_screen.dart`

There are 4 `withOpacity` calls in this file, all in `_HealthIdBanner` and `_ConsentAlert`.

- [ ] **Step 1: Fix _HealthIdBanner — onPrimarySubtle**

Find (line ~193, inside `_HealthIdBanner`):
```dart
                  color: AppColors.textOnPrimary.withOpacity(0.75),
```
Replace with:
```dart
                  color: AppColors.onPrimarySubtle,
```

- [ ] **Step 2: Fix _HealthIdBanner — whiteOverlay**

Find (line ~205, inside `_HealthIdBanner`):
```dart
                      color: Colors.white.withOpacity(0.15),
```
Replace with:
```dart
                      color: AppColors.whiteOverlay,
```

- [ ] **Step 3: Fix _ConsentAlert — warningBorder**

Find (line ~338, inside `_ConsentAlert`):
```dart
                color: AppColors.warning.withOpacity(0.3)),
```
Replace with:
```dart
                color: AppColors.warningBorder),
```

- [ ] **Step 4: Fix _ConsentAlert — warningSurface**

Find (line ~344, inside `_ConsentAlert`):
```dart
                color: AppColors.warning.withOpacity(0.15),
```
Replace with:
```dart
                color: AppColors.warningSurface,
```

- [ ] **Step 5: Verify**

```powershell
flutter analyze lib/features/home/presentation/home_screen.dart 2>&1 | Select-String "withOpacity"
```
Expected: 0 matches.

- [ ] **Step 6: Commit**

```powershell
git add lib/features/home/presentation/home_screen.dart
git commit -m "fix(design): replace 4x withOpacity with named tokens in home_screen"
```

---

## Task 5: Replace withOpacity in labs, lab_detail, timeline

**Files:**
- Modify: `lib/features/labs/presentation/labs_screen.dart`
- Modify: `lib/features/labs/presentation/lab_detail_screen.dart`
- Modify: `lib/features/timeline/presentation/timeline_screen.dart`

- [ ] **Step 1: Fix labs_screen.dart**

Find (line ~87):
```dart
                ? AppColors.danger.withOpacity(0.4)
```
Replace with:
```dart
                ? AppColors.dangerBorder
```

- [ ] **Step 2: Fix lab_detail_screen.dart**

Find (line ~41):
```dart
                      color: AppColors.danger.withOpacity(0.3)),
```
Replace with:
```dart
                      color: AppColors.dangerSurface),
```

- [ ] **Step 3: Fix timeline_screen.dart — dynamic colour (use .withValues)**

This call uses a **runtime-computed** base colour (`_typeColor(event.type)`) so a static token cannot be used. Use the non-deprecated `.withValues()` API instead.

Find (line ~146):
```dart
                color: _typeColor(event.type).withOpacity(0.1),
```
Replace with:
```dart
                color: _typeColor(event.type).withValues(alpha: 0.1),
```

- [ ] **Step 4: Verify zero withOpacity warnings across the entire lib/**

```powershell
flutter analyze lib/ 2>&1 | Select-String "withOpacity"
```
Expected: **0 matches** — all deprecated calls eliminated.

- [ ] **Step 5: Run full test suite**

```powershell
flutter test -v
```
Expected: `+10: All tests passed!` (same 10 tests as before — these changes are purely cosmetic).

- [ ] **Step 6: Commit**

```powershell
git add lib/features/labs/presentation/labs_screen.dart lib/features/labs/presentation/lab_detail_screen.dart lib/features/timeline/presentation/timeline_screen.dart
git commit -m "fix(design): replace withOpacity in labs + lab_detail; .withValues() in timeline"
```

---

## Self-Review

**Spec coverage:**

| Spec requirement | Task |
|---|---|
| Add `dangerBorder` token (`danger @ 40%`) | Task 1 |
| Add `dangerSurface` token (`danger @ 30%`) | Task 1 |
| Add `primarySurface` token (`primary500 @ 30%`) | Task 1 |
| Add `onPrimarySubtle` token (`white @ 75%`) | Task 1 |
| Add `whiteOverlay` token (`white @ 15%`) | Task 1 |
| Add `warningBorder` token (`warning @ 30%`) | Task 1 |
| Add `warningSurface` token (`warning @ 15%`) | Task 1 |
| Fix login trust badge: `shield` → `lock` | Task 2 |
| Fix settings section title: `bell` → `bellRing` | Task 2 |
| Upgrade Health ID icon: `creditCard` → `fingerprint` | Task 2 (home_screen + main_shell) |
| Replace `access_logs_screen.dart` `withOpacity(0.4)` | Task 3 |
| Replace `consent_screen.dart` `withOpacity(0.4)` | Task 3 |
| Replace `health_id_screen.dart` `withOpacity(0.3)` | Task 3 |
| Replace `home_screen.dart` × 4 `withOpacity` | Task 4 |
| Replace `labs_screen.dart` `withOpacity(0.4)` | Task 5 |
| Replace `lab_detail_screen.dart` `withOpacity(0.3)` | Task 5 |
| Replace `timeline_screen.dart` dynamic `withOpacity(0.1)` | Task 5 |
| Zero `withOpacity` warnings in `flutter analyze` | Task 5 Step 4 |
| All tests pass | Task 5 Step 5 |

**No gaps. No placeholders. Type consistency: `AppColors.dangerBorder` defined in Task 1, used in Tasks 3 and 5 — consistent.**
