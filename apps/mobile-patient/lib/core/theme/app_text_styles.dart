import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'app_colors.dart';

abstract final class AppTextStyles {
  // ── Display / Hero — Plus Jakarta Sans (bold, geometric, more character than Inter)
  static TextStyle get h1 => GoogleFonts.plusJakartaSans(
        fontSize: 28, fontWeight: FontWeight.w800,
        color: AppColors.textPrimary, height: 1.2,
        letterSpacing: -0.5,
      );
  static TextStyle get h2 => GoogleFonts.plusJakartaSans(
        fontSize: 22, fontWeight: FontWeight.w800,
        color: AppColors.textPrimary, height: 1.25,
        letterSpacing: -0.3,
      );
  static TextStyle get h3 => GoogleFonts.plusJakartaSans(
        fontSize: 18, fontWeight: FontWeight.w700,
        color: AppColors.textPrimary, height: 1.3,
        letterSpacing: -0.2,
      );
  static TextStyle get h4 => GoogleFonts.plusJakartaSans(
        fontSize: 16, fontWeight: FontWeight.w700,
        color: AppColors.textPrimary, height: 1.35,
      );

  // ── Body — DM Sans (clean, slightly warmer than Inter, great on mobile)
  static TextStyle get bodyLg => GoogleFonts.dmSans(
        fontSize: 16, fontWeight: FontWeight.w400,
        color: AppColors.textPrimary, height: 1.55,
      );
  static TextStyle get body => GoogleFonts.dmSans(
        fontSize: 14, fontWeight: FontWeight.w400,
        color: AppColors.textPrimary, height: 1.55,
      );
  static TextStyle get bodySm => GoogleFonts.dmSans(
        fontSize: 13, fontWeight: FontWeight.w400,
        color: AppColors.textSecondary, height: 1.5,
      );
  static TextStyle get caption => GoogleFonts.dmSans(
        fontSize: 11, fontWeight: FontWeight.w400,
        color: AppColors.textMuted, height: 1.4, letterSpacing: 0.2,
      );
  static TextStyle get label => GoogleFonts.dmSans(
        fontSize: 11, fontWeight: FontWeight.w700,
        color: AppColors.textMuted, height: 1.2, letterSpacing: 0.7,
      );
  static TextStyle get buttonLg => GoogleFonts.dmSans(
        fontSize: 15, fontWeight: FontWeight.w700,
        height: 1.2, letterSpacing: 0.1,
      );
  static TextStyle get button => GoogleFonts.dmSans(
        fontSize: 14, fontWeight: FontWeight.w700, height: 1.2,
      );

  // ── Mono — JetBrains Mono (health IDs, lab values, prices, policy numbers)
  static TextStyle get healthId => GoogleFonts.jetBrainsMono(
        fontSize: 18, fontWeight: FontWeight.w700,
        color: AppColors.textOnPrimary, letterSpacing: 0.12,
      );
  static TextStyle get monoLg => GoogleFonts.jetBrainsMono(
        fontSize: 22, fontWeight: FontWeight.w700,
        color: AppColors.textPrimary, letterSpacing: 0.08,
      );
  static TextStyle get mono => GoogleFonts.jetBrainsMono(
        fontSize: 14, fontWeight: FontWeight.w600,
        color: AppColors.textPrimary, letterSpacing: 0.06,
      );
  static TextStyle get monoSm => GoogleFonts.jetBrainsMono(
        fontSize: 12, fontWeight: FontWeight.w500,
        color: AppColors.textPrimary, letterSpacing: 0.04,
      );
  static TextStyle get monoXs => GoogleFonts.jetBrainsMono(
        fontSize: 10, fontWeight: FontWeight.w500,
        color: AppColors.textMuted, letterSpacing: 0.08,
      );
}
