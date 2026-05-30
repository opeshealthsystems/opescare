import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
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
      appBar: AppBar(title: const Text('My Profile')),
      body: profileAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 120, borderRadius: 12),
          SizedBox(height: 16),
          LoadingSkeleton(height: 200, borderRadius: 12),
        ]),
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
        // Avatar + name card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
              begin: Alignment.topLeft, end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(children: [
            Container(
              width: 56, height: 56,
              decoration: const BoxDecoration(
                color: AppColors.whiteOverlay, shape: BoxShape.circle,
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
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(profile.displayName,
                    style: AppTextStyles.h3.copyWith(color: Colors.white)),
                const SizedBox(height: 4),
                Text(profile.healthId,
                    style: AppTextStyles.caption
                        .copyWith(color: Colors.white70)),
                const SizedBox(height: 6),
                _StatusChip(status: profile.status),
              ]),
            ),
          ]),
        ),
        const SizedBox(height: 20),

        // Demographics
        _InfoSection(title: 'Personal Information', icon: LucideIcons.user, rows: [
          if (profile.dob != null)    _Row('Date of Birth', profile.dob!),
          if (profile.sex != null)    _Row('Sex', profile.sex!.toUpperCase()),
          if (profile.bloodGroup != null) _Row('Blood Group', profile.bloodGroup!),
          if (profile.phone != null)  _Row('Phone', profile.phone!),
          if (profile.email != null)  _Row('Email', profile.email!),
        ]),
        const SizedBox(height: 16),

        // Health summary
        _InfoSection(title: 'Health Summary', icon: LucideIcons.activity, rows: [
          _Row('Active Allergies', '${profile.allergiesCount}'),
          _Row('Active Conditions', '${profile.conditionsCount}'),
        ]),
        const SizedBox(height: 32),
      ],
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.status});
  final String status;

  @override
  Widget build(BuildContext context) {
    final isActive = status == 'active';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.whiteOverlay,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(isActive ? LucideIcons.checkCircle : LucideIcons.alertCircle,
            size: 11, color: Colors.white),
        const SizedBox(width: 4),
        Text(isActive ? 'Active' : status.toUpperCase(),
            style: AppTextStyles.caption.copyWith(color: Colors.white)),
      ]),
    );
  }
}

class _InfoSection extends StatelessWidget {
  const _InfoSection({
    required this.title,
    required this.icon,
    required this.rows,
  });
  final String title;
  final IconData icon;
  final List<_Row> rows;

  @override
  Widget build(BuildContext context) {
    if (rows.isEmpty) return const SizedBox.shrink();
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(children: [
        Padding(
          padding: const EdgeInsets.all(14),
          child: Row(children: [
            Icon(icon, size: 16, color: AppColors.primary500),
            const SizedBox(width: 8),
            Text(title, style: AppTextStyles.h4),
          ]),
        ),
        const Divider(height: 1),
        ...rows.map((r) => Column(children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(children: [
              Expanded(child: Text(r.label, style: AppTextStyles.bodySm)),
              Text(r.value,
                  style: AppTextStyles.body
                      .copyWith(fontWeight: FontWeight.w600)),
            ]),
          ),
          if (r != rows.last) const Divider(height: 1, indent: 14),
        ])),
      ]),
    );
  }
}

class _Row {
  const _Row(this.label, this.value);
  final String label, value;
}
