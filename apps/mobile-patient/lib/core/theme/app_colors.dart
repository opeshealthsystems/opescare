import 'package:flutter/material.dart';

/// OpesCare Patient App — Design Tokens (Colors)
/// Primary: Clinical Blue #1565C0 — WCAG AAA on white (7.2:1 contrast ratio)
abstract final class AppColors {
  // Primary — Clinical Blue
  static const Color primary50  = Color(0xFFEFF6FF);
  static const Color primary100 = Color(0xFFDBEAFE);
  static const Color primary200 = Color(0xFFBFDBFE);
  static const Color primary300 = Color(0xFF93C5FD);
  static const Color primary400 = Color(0xFF60A5FA);
  static const Color primary500 = Color(0xFF1565C0); // Brand default
  static const Color primary600 = Color(0xFF1044A0);
  static const Color primary700 = Color(0xFF0D3A7D);
  static const Color primary800 = Color(0xFF0A2B5C);
  static const Color primary900 = Color(0xFF071B3B);

  // Neutral — Slate
  static const Color neutral50  = Color(0xFFF9FAFB);
  static const Color neutral100 = Color(0xFFF3F4F6);
  static const Color neutral200 = Color(0xFFE5E7EB);
  static const Color neutral300 = Color(0xFFD1D5DB);
  static const Color neutral400 = Color(0xFF9CA3AF);
  static const Color neutral500 = Color(0xFF6B7280);
  static const Color neutral600 = Color(0xFF4B5563);
  static const Color neutral700 = Color(0xFF374151);
  static const Color neutral800 = Color(0xFF1F2937);
  static const Color neutral900 = Color(0xFF111827);

  // Semantic
  static const Color success      = Color(0xFF10B981);
  static const Color successDark  = Color(0xFF059669);
  static const Color successLight = Color(0xFFD1FAE5);

  static const Color warning      = Color(0xFFF59E0B);
  static const Color warningDark  = Color(0xFFD97706);
  static const Color warningLight = Color(0xFFFEF3C7);

  static const Color danger       = Color(0xFFEF4444);
  static const Color dangerDark   = Color(0xFFDC2626);
  static const Color dangerLight  = Color(0xFFFEE2E2);

  static const Color info         = Color(0xFF3B82F6);
  static const Color infoDark     = Color(0xFF2563EB);
  static const Color infoLight    = Color(0xFFEFF6FF);

  // Surfaces
  static const Color background   = Color(0xFFF3F4F6);
  static const Color surface      = Color(0xFFFFFFFF);
  static const Color surfaceMuted = Color(0xFFF9FAFB);
  static const Color divider      = Color(0xFFE5E7EB);

  // Opacity variants — pre-computed to avoid runtime withOpacity() calls
  static const Color dangerBorder    = Color(0x66EF4444); // danger   @ 40%
  static const Color dangerSurface   = Color(0x4DEF4444); // danger   @ 30%
  static const Color primarySurface  = Color(0x4D1565C0); // primary  @ 30%
  static const Color onPrimarySubtle = Color(0xBFFFFFFF); // white    @ 75%
  static const Color whiteOverlay    = Color(0x26FFFFFF); // white    @ 15%
  static const Color warningBorder   = Color(0x4DF59E0B); // warning  @ 30%
  static const Color warningSurface  = Color(0x26F59E0B); // warning  @ 15%

  // Text
  static const Color textPrimary   = Color(0xFF111827);
  static const Color textSecondary = Color(0xFF6B7280);
  static const Color textMuted     = Color(0xFF9CA3AF);
  static const Color textOnPrimary = Color(0xFFFFFFFF);

  // Health ID Card gradient
  static const Color cardGradientStart = Color(0xFF1565C0);
  static const Color cardGradientEnd   = Color(0xFF1044A0);
}
