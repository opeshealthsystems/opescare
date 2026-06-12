import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'edit_profile_screen.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/patient_profile.dart';
import '../providers/profile_provider.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileAsync = ref.watch(profileProvider);
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Profile'),
        backgroundColor: AppColors.surface,
        surfaceTintColor: Colors.transparent,
        actions: [
          IconButton(
            icon: const Icon(LucideIcons.pencil),
            tooltip: 'Edit profile',
            onPressed: () {
              final profile = ref.read(profileProvider).valueOrNull;
              if (profile != null) {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => EditProfileScreen(profile: profile),
                  ),
                );
              }
            },
          ),
        ],
      ),
      body: profileAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 120, borderRadius: 14),
            SizedBox(height: 20),
            LoadingSkeleton(height: 200, borderRadius: 12),
            SizedBox(height: 16),
            LoadingSkeleton(height: 100, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(profileProvider),
        ),
        data: (profile) => _ProfileBody(profile: profile),
      ),
    );
  }
}

class _ProfileBody extends StatelessWidget {
  const _ProfileBody({required this.profile});
  final PatientProfile profile;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // ── Gradient header card ──────────────────────────────────────────
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: AppColors.cardGradientStart.withAlpha(77), // ~0.3 alpha
                blurRadius: 16,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Avatar circle
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: AppColors.whiteOverlay,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    profile.firstName.isNotEmpty
                        ? profile.firstName[0].toUpperCase()
                        : 'P',
                    style: AppTextStyles.h2.copyWith(color: Colors.white),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      profile.displayName,
                      style: AppTextStyles.h3.copyWith(color: Colors.white),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      profile.healthId,
                      style: AppTextStyles.monoXs
                          .copyWith(color: Colors.white60),
                    ),
                    const SizedBox(height: 8),
                    // Chips row
                    Row(
                      children: [
                        // Verified chip
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.successLight.withAlpha(64), // 0.25
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: AppColors.success,
                              width: 1,
                            ),
                          ),
                          child: Text(
                            '✓ VERIFIED',
                            style: AppTextStyles.caption.copyWith(
                              color: AppColors.successLight,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        // Status chip
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.whiteOverlay,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            profile.status == 'active'
                                ? 'Active'
                                : profile.status.toUpperCase(),
                            style: AppTextStyles.caption
                                .copyWith(color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // ── PERSONAL INFORMATION ──────────────────────────────────────────
        _SectionLabel(label: 'PERSONAL INFORMATION'),
        const SizedBox(height: 8),
        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider),
          ),
          child: Column(
            children: [
              if (profile.dob != null)
                _InfoRow(label: 'Date of Birth', value: profile.dob!),
              if (profile.dob != null && profile.sex != null)
                const Divider(height: 1, indent: 16),
              if (profile.sex != null)
                _InfoRow(
                    label: 'Sex',
                    value: profile.sex![0].toUpperCase() +
                        profile.sex!.substring(1)),
              if (profile.sex != null && profile.bloodGroup != null)
                const Divider(height: 1, indent: 16),
              if (profile.bloodGroup != null)
                _InfoRow(label: 'Blood Group', value: profile.bloodGroup!),
              if (profile.bloodGroup != null && profile.phone != null)
                const Divider(height: 1, indent: 16),
              if (profile.phone != null)
                _InfoRow(label: 'Phone', value: profile.phone!),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // ── EMERGENCY CONTACT ─────────────────────────────────────────────
        _SectionLabel(label: 'EMERGENCY CONTACT'),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider),
          ),
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: AppColors.dangerLight,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  LucideIcons.phone,
                  size: 18,
                  color: AppColors.danger,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Emergency Contact',
                      style: AppTextStyles.bodySm,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Not set',
                      style: AppTextStyles.body
                          .copyWith(fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              const Icon(LucideIcons.chevronRight,
                  size: 16, color: AppColors.neutral400),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // ── Settings row ──────────────────────────────────────────────────
        GestureDetector(
          onTap: () => context.push(Routes.settings),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.divider),
            ),
            child: Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: AppColors.neutral100,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    LucideIcons.settings,
                    size: 18,
                    color: AppColors.neutral500,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Settings',
                    style: AppTextStyles.body
                        .copyWith(fontWeight: FontWeight.w600),
                  ),
                ),
                const Icon(LucideIcons.chevronRight,
                    size: 16, color: AppColors.neutral400),
              ],
            ),
          ),
        ),
        const SizedBox(height: 32),
      ],
    );
  }
}

/// Uppercase section label with letter-spacing
class _SectionLabel extends StatelessWidget {
  const _SectionLabel({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    return Text(
      label,
      style: AppTextStyles.label.copyWith(
        letterSpacing: 0.8,
        color: AppColors.textMuted,
      ),
    );
  }
}

/// A two-line info row: small label on top, bold value below
class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label.toUpperCase(),
            style: AppTextStyles.caption.copyWith(
              color: AppColors.textMuted,
              letterSpacing: 0.6,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            value,
            style:
                AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}
