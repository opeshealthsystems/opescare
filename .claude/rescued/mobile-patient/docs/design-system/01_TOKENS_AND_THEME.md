# 01 — Tokens & Theme (exact)

All values transcribed from `design-preview.html` `:root` and `[data-dark]`.

## Fonts (pubspec)

```yaml
# pubspec.yaml
flutter:
  fonts:
    - family: PlusJakartaSans
      fonts:
        - asset: assets/fonts/PlusJakartaSans-Light.ttf       # weight 300
          weight: 300
        - asset: assets/fonts/PlusJakartaSans-Regular.ttf      # 400
          weight: 400
        - asset: assets/fonts/PlusJakartaSans-Medium.ttf       # 500
          weight: 500
        - asset: assets/fonts/PlusJakartaSans-SemiBold.ttf     # 600
          weight: 600
        - asset: assets/fonts/PlusJakartaSans-Bold.ttf         # 700
          weight: 700
        - asset: assets/fonts/PlusJakartaSans-ExtraBold.ttf    # 800
          weight: 800
    - family: GeistMono
      fonts:
        - asset: assets/fonts/GeistMono-Regular.ttf            # 400
          weight: 400
        - asset: assets/fonts/GeistMono-Medium.ttf             # 500
          weight: 500
        - asset: assets/fonts/GeistMono-SemiBold.ttf           # 600
          weight: 600
        - asset: assets/fonts/GeistMono-Bold.ttf               # 700
          weight: 700
```

Use `fontFamily: 'PlusJakartaSans'` / `'GeistMono'`. Remove `GoogleFonts.dmSans` / `jetBrainsMono` / `plusJakartaSans` calls in the current theme.

## Colors

```dart
// lib/core/theme/app_colors.dart
import 'package:flutter/material.dart';

class AppColors {
  // Clinical Blue
  static const primary    = Color(0xFF1565C0);
  static const primary50  = Color(0xFFEFF6FF);
  static const primary100 = Color(0xFFDBEAFE);
  static const primary200 = Color(0xFFBFDBFE);
  static const primary300 = Color(0xFF93C5FD);
  static const primary400 = Color(0xFF60A5FA);
  static const primary600 = Color(0xFF1044A0);
  static const primary700 = Color(0xFF0D3A7D);
  static const primary800 = Color(0xFF0A2B5C);
  static const primary900 = Color(0xFF071B3B);
  static const gradStart  = Color(0xFF1565C0);
  static const gradEnd    = Color(0xFF1044A0);

  // Semantic
  static const success     = Color(0xFF10B981);
  static const successBg   = Color(0xFFD1FAE5);
  static const successDark  = Color(0xFF059669);
  static const warning     = Color(0xFFF59E0B);
  static const warningBg   = Color(0xFFFEF3C7);
  static const warningText = Color(0xFF92400E); // amber pill text
  static const danger      = Color(0xFFEF4444);
  static const dangerBg    = Color(0xFFFEE2E2);
  static const dangerText  = Color(0xFF991B1B); // red pill text
  static const info        = Color(0xFF3B82F6);
  static const infoBg      = Color(0xFFEFF6FF);
  static const infoText    = Color(0xFF1E40AF); // blue pill text
  static const successText = Color(0xFF065F46); // green pill/alert text

  // Light surfaces & text
  static const bg          = Color(0xFFF3F4F6);
  static const surface     = Color(0xFFFFFFFF);
  static const surfaceMuted = Color(0xFFF9FAFB);
  static const divider     = Color(0xFFE5E7EB);
  static const text        = Color(0xFF111827);
  static const text2       = Color(0xFF6B7280);
  static const text3       = Color(0xFF9CA3AF);
}

class AppColorsDark {
  static const bg          = Color(0xFF0F1117);
  static const surface     = Color(0xFF1A1D27);
  static const surfaceMuted = Color(0xFF222636);
  static const divider     = Color(0xFF2A2D3A);
  static const text        = Color(0xFFF1F5F9);
  static const text2       = Color(0xFF94A3B8);
  static const text3       = Color(0xFF64748B);
  static const primary50   = Color(0xFF0D1B36);
  static const primary100  = Color(0xFF1E3A6E);
  // primary + semantic colors unchanged in dark
}
```

## Radii & Shadows

```dart
// lib/core/theme/app_radii.dart
class AppRadii {
  static const sm = 6.0;
  static const md = 10.0;
  static const lg = 14.0;
  static const xl = 20.0;
  static const card = 16.0;
  static const full = 9999.0;
}

// lib/core/theme/app_shadows.dart
import 'package:flutter/material.dart';
class AppShadows {
  static const sm = [
    BoxShadow(color: Color(0x12000000), blurRadius: 3, offset: Offset(0, 1)),   // rgba(0,0,0,.07)
    BoxShadow(color: Color(0x0A000000), blurRadius: 2, offset: Offset(0, 1)),   // rgba(0,0,0,.04)
  ];
  static const md = [
    BoxShadow(color: Color(0x14000000), blurRadius: 16, offset: Offset(0, 4)),  // .08
    BoxShadow(color: Color(0x0D000000), blurRadius: 4, offset: Offset(0, 2)),   // .05
  ];
  static const lg = [
    BoxShadow(color: Color(0x24000000), blurRadius: 40, offset: Offset(0, 12)), // .14
    BoxShadow(color: Color(0x12000000), blurRadius: 8, offset: Offset(0, 4)),   // .07
  ];
  // Blue glow used on the Health ID card / gradient banners
  static const blue = [
    BoxShadow(color: Color(0x591565C0), blurRadius: 32, offset: Offset(0, 8)),  // rgba(21,101,192,.35)
    BoxShadow(color: Color(0x331565C0), blurRadius: 8, offset: Offset(0, 2)),   // .2
  ];
}
```

The Clinical-Blue gradient (Health ID card, banners, primary CTA):
`LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [AppColors.gradStart, AppColors.gradEnd])` (CSS `135deg`).

## Typography scale (exact)

Family: `PlusJakartaSans` for everything except data, which uses `GeistMono`. Sizes/weights as used in the preview:

| Token | Family / size / weight / spacing | Used for |
|-------|----------------------------------|----------|
| displayHero | Jakarta 42 / w800 / ls −0.02em | onboarding/section heroes |
| screenTitleLg | Jakarta 32 / w800 / ls −0.02em | large titles |
| appBarTitle | Jakarta 17 / w700 | screen app-bar titles |
| sectionTitle | Jakarta 13 / w700 | in-screen section headers |
| body | Jakarta 15 / w400 / line-height 1.6 | body text |
| bodyLg | Jakarta 16 / w400 / lh 1.7 | long descriptions |
| listTitle | Jakarta 14 / w600 | list row titles |
| listSub | Jakarta 12 / w400, color text3 | list row subtitles |
| label | Jakarta 11 / w700 / ls .06em / UPPERCASE, color text2 | input labels |
| labelTiny | Jakarta 10 / w600/700 / ls .08–.10em / UPPERCASE, color text3 | meta labels |
| pill | Jakarta 10 / w700 / ls .04em | status pills |
| chip | Jakarta 9 / w700 | family chips |
| buttonText | Jakarta 14 / w700 / ls .01em | buttons |
| mono.id | GeistMono 15 / w700 / ls .12em | Health ID |
| mono.statNum | GeistMono 24 / w700 | home stat numbers |
| mono.labHero | GeistMono 48 / w700 | lab detail hero value |
| mono.labMed | GeistMono 28 / w700 | lab specimens |
| mono.price | GeistMono 15 / w700 | XAF prices, policy numbers |
| mono.small | GeistMono 10–12 / w600–700 / ls .08em | inline IDs, distances, timestamps |

```dart
// lib/core/theme/app_type.dart  (helper styles; colors applied at call site or via theme)
import 'package:flutter/material.dart';
import 'app_colors.dart';

class AppType {
  static const _jak = 'PlusJakartaSans';
  static const _mono = 'GeistMono';

  static const displayHero = TextStyle(fontFamily: _jak, fontSize: 42, fontWeight: FontWeight.w800, letterSpacing: -0.84, height: 1.1);
  static const screenTitleLg = TextStyle(fontFamily: _jak, fontSize: 32, fontWeight: FontWeight.w800, letterSpacing: -0.64, height: 1.15);
  static const appBarTitle = TextStyle(fontFamily: _jak, fontSize: 17, fontWeight: FontWeight.w700);
  static const sectionTitle = TextStyle(fontFamily: _jak, fontSize: 13, fontWeight: FontWeight.w700);
  static const body = TextStyle(fontFamily: _jak, fontSize: 15, fontWeight: FontWeight.w400, height: 1.6);
  static const bodyLg = TextStyle(fontFamily: _jak, fontSize: 16, fontWeight: FontWeight.w400, height: 1.7);
  static const listTitle = TextStyle(fontFamily: _jak, fontSize: 14, fontWeight: FontWeight.w600, height: 1.2);
  static const listSub = TextStyle(fontFamily: _jak, fontSize: 12, fontWeight: FontWeight.w400, color: AppColors.text3);
  static const label = TextStyle(fontFamily: _jak, fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: 0.66, color: AppColors.text2);
  static const labelTiny = TextStyle(fontFamily: _jak, fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 0.9, color: AppColors.text3);
  static const pill = TextStyle(fontFamily: _jak, fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 0.4);
  static const chip = TextStyle(fontFamily: _jak, fontSize: 9, fontWeight: FontWeight.w700, letterSpacing: 0.36);
  static const buttonText = TextStyle(fontFamily: _jak, fontSize: 14, fontWeight: FontWeight.w700, letterSpacing: 0.14);

  // Mono / data
  static const monoId = TextStyle(fontFamily: _mono, fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: 1.8);
  static const monoStat = TextStyle(fontFamily: _mono, fontSize: 24, fontWeight: FontWeight.w700, height: 1, color: AppColors.primary);
  static const monoLabHero = TextStyle(fontFamily: _mono, fontSize: 48, fontWeight: FontWeight.w700, height: 1);
  static const monoLabMed = TextStyle(fontFamily: _mono, fontSize: 28, fontWeight: FontWeight.w700);
  static const monoPrice = TextStyle(fontFamily: _mono, fontSize: 15, fontWeight: FontWeight.w700);
  static const monoSmall = TextStyle(fontFamily: _mono, fontSize: 11, fontWeight: FontWeight.w600, letterSpacing: 0.8);
}
```
(letterSpacing in Flutter is in logical px: em × fontSize. e.g. −0.02em × 42 ≈ −0.84.)

## ThemeData

```dart
// lib/core/theme/app_theme.dart
import 'package:flutter/material.dart';
import 'app_colors.dart';

ThemeData buildLightTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: AppColors.primary,
    primary: AppColors.primary,
    surface: AppColors.surface,
    background: AppColors.bg,
    error: AppColors.danger,
    brightness: Brightness.light,
  );
  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: AppColors.bg,
    fontFamily: 'PlusJakartaSans',
    dividerColor: AppColors.divider,
    appBarTheme: const AppBarTheme(
      backgroundColor: AppColors.surface,
      foregroundColor: AppColors.text,
      elevation: 0,
      surfaceTintColor: Colors.transparent,
      titleTextStyle: TextStyle(fontFamily: 'PlusJakartaSans', fontSize: 17, fontWeight: FontWeight.w700, color: AppColors.text),
    ),
    // Inputs: 1.5px divider border, lg radius, primary focus + 3px halo (see components)
  );
}

ThemeData buildDarkTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: AppColors.primary,
    primary: AppColors.primary,
    surface: AppColorsDark.surface,
    background: AppColorsDark.bg,
    brightness: Brightness.dark,
  );
  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: AppColorsDark.bg,
    fontFamily: 'PlusJakartaSans',
    dividerColor: AppColorsDark.divider,
    appBarTheme: const AppBarTheme(
      backgroundColor: AppColorsDark.surface,
      foregroundColor: AppColorsDark.text,
      elevation: 0,
      surfaceTintColor: Colors.transparent,
    ),
  );
}
```

Wire in `app.dart`: `theme: buildLightTheme(), darkTheme: buildDarkTheme(), themeMode: ThemeMode.system`.

> For dark mode, surface/text/divider tokens must switch to `AppColorsDark`. Easiest: expose a `context`-based token getter (e.g. an extension `BuildContext.tokens`) returning the right set per `Theme.of(context).brightness`. Components in `02` reference tokens through that getter so they adapt automatically.
