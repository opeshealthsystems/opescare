import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/section_header.dart';
import '../../health_id/providers/health_id_provider.dart';
import '../models/dashboard_summary.dart';
import '../providers/home_provider.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final summaryAsync = ref.watch(dashboardSummaryProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            ref.invalidate(dashboardSummaryProvider);
            ref.invalidate(healthIdCardProvider);
          },
          child: summaryAsync.when(
            loading: () => const HomeScreenSkeleton(),
            error: (e, _) => ErrorView(
              message: e.toString(),
              onRetry: () => ref.invalidate(dashboardSummaryProvider),
            ),
            data: (summary) => _HomeBody(summary: summary),
          ),
        ),
      ),
    );
  }
}

class _HomeBody extends StatelessWidget {
  const _HomeBody({required this.summary});
  final DashboardSummary summary;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      children: [
        const SizedBox(height: 16),
        // Header
        Row(children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(_greeting(),
                    style: AppTextStyles.caption
                        .copyWith(color: AppColors.textSecondary)),
                const SizedBox(height: 2),
                Text(summary.patientName, style: AppTextStyles.h3),
              ],
            ),
          ),
          Container(
            width: 40, height: 40,
            decoration: const BoxDecoration(
              shape: BoxShape.circle, color: AppColors.primary500,
            ),
            child: Center(
              child: Text(
                summary.patientName.isNotEmpty
                    ? summary.patientName[0].toUpperCase()
                    : 'P',
                style: AppTextStyles.h4.copyWith(
                    color: AppColors.textOnPrimary),
              ),
            ),
          ),
        ]),
        const SizedBox(height: 16),

        // Health ID banner
        _HealthIdBanner(
          healthId: summary.healthId,
          isVerified: summary.isVerified,
          onTap: () => context.push(Routes.healthId),
        ),
        const SizedBox(height: 12),

        // Quick stats
        _QuickStats(summary: summary),
        const SizedBox(height: 20),

        // Consent alert
        if (summary.pendingConsentCount > 0) ...[
          _ConsentAlert(
            count: summary.pendingConsentCount,
            onTap: () => context.push(Routes.consent),
          ),
          const SizedBox(height: 16),
        ],

        // Next appointment
        if (summary.nextAppointmentDate != null) ...[
          SectionHeader(
            title: 'Upcoming Appointment',
            icon: LucideIcons.calendar,
            onSeeAll: () => context.push(Routes.appointments),
          ),
          const SizedBox(height: 10),
          _AppointmentCard(
            date: summary.nextAppointmentDate!,
            facility: summary.nextAppointmentFacility ?? 'Facility',
          ),
          const SizedBox(height: 20),
        ],

        // Recent access
        if (summary.recentAccessCount > 0) ...[
          SectionHeader(
            title: 'Recent Access',
            icon: LucideIcons.eye,
            onSeeAll: () => context.push(Routes.accessLogs),
          ),
          const SizedBox(height: 10),
          _AccessLogBanner(count: summary.recentAccessCount),
          const SizedBox(height: 20),
        ],

        const SizedBox(height: 32),
      ],
    );
  }

  String _greeting() {
    final hour = DateTime.now().hour;
    if (hour < 12) return 'Good morning,';
    if (hour < 17) return 'Good afternoon,';
    return 'Good evening,';
  }
}

// ── Health ID Banner ────────────────────────────────────────────────────────

class _HealthIdBanner extends StatelessWidget {
  const _HealthIdBanner({
    required this.healthId,
    required this.isVerified,
    required this.onTap,
  });

  final String healthId;
  final bool isVerified;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'HEALTH ID',
                  style: AppTextStyles.label.copyWith(
                    color: AppColors.textOnPrimary.withOpacity(0.75),
                    fontSize: 10,
                  ),
                ),
                const SizedBox(height: 6),
                Text(healthId, style: AppTextStyles.healthId),
                const SizedBox(height: 8),
                if (isVerified)
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(mainAxisSize: MainAxisSize.min, children: [
                      const Icon(LucideIcons.checkCircle,
                          size: 11, color: Colors.white),
                      const SizedBox(width: 4),
                      Text(
                        'Verified',
                        style: AppTextStyles.caption
                            .copyWith(color: Colors.white),
                      ),
                    ]),
                  ),
              ],
            ),
          ),
          const Icon(LucideIcons.idCard, size: 36, color: Colors.white54),
        ]),
      ),
    );
  }
}

// ── Quick Stats ─────────────────────────────────────────────────────────────

class _QuickStats extends StatelessWidget {
  const _QuickStats({required this.summary});
  final DashboardSummary summary;

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      _StatCard(
        icon: LucideIcons.flaskConical,
        label: 'Lab Results',
        value: summary.unreadLabCount.toString(),
        color: AppColors.success,
        onTap: () => context.push(Routes.labs),
      ),
      const SizedBox(width: 8),
      _StatCard(
        icon: LucideIcons.pill,
        label: 'Active Rx',
        value: summary.activeRxCount.toString(),
        color: AppColors.primary500,
        onTap: () => context.push(Routes.prescriptions),
      ),
      const SizedBox(width: 8),
      _StatCard(
        icon: LucideIcons.shield,
        label: 'Consents',
        value: summary.pendingConsentCount.toString(),
        color: summary.pendingConsentCount > 0
            ? AppColors.warning
            : AppColors.neutral400,
        onTap: () => context.push(Routes.consent),
      ),
    ]);
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    required this.onTap,
  });

  final IconData icon;
  final String label, value;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider),
          ),
          child: Column(children: [
            Icon(icon, size: 20, color: color),
            const SizedBox(height: 6),
            Text(
              value,
              style: AppTextStyles.h3
                  .copyWith(color: color, fontSize: 20),
            ),
            const SizedBox(height: 2),
            Text(label,
                style: AppTextStyles.caption,
                textAlign: TextAlign.center),
          ]),
        ),
      ),
    );
  }
}

// ── Consent Alert ───────────────────────────────────────────────────────────

class _ConsentAlert extends StatelessWidget {
  const _ConsentAlert({required this.count, required this.onTap});

  final int count;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.warningLight,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: AppColors.warning.withOpacity(0.3)),
        ),
        child: Row(children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: AppColors.warning.withOpacity(0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(LucideIcons.bell,
                size: 18, color: AppColors.warningDark),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$count consent request${count > 1 ? 's' : ''} pending',
                  style: AppTextStyles.body.copyWith(
                      fontWeight: FontWeight.w600,
                      color: AppColors.warningDark),
                ),
                Text(
                  'A facility wants to access your health records.',
                  style: AppTextStyles.bodySm,
                ),
              ],
            ),
          ),
          const Icon(LucideIcons.chevronRight,
              size: 16, color: AppColors.warningDark),
        ]),
      ),
    );
  }
}

// ── Appointment Card ────────────────────────────────────────────────────────

class _AppointmentCard extends StatelessWidget {
  const _AppointmentCard({required this.date, required this.facility});

  final String date, facility;

  @override
  Widget build(BuildContext context) {
    String formatted = date;
    try {
      formatted = DateFormat('EEE, d MMM · h:mm a')
          .format(DateTime.parse(date));
    } catch (_) {}

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(children: [
        Container(
          width: 40, height: 40,
          decoration: BoxDecoration(
            color: AppColors.primary50,
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(LucideIcons.calendar,
              size: 20, color: AppColors.primary500),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(facility,
                  style: AppTextStyles.body
                      .copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: 2),
              Text(formatted, style: AppTextStyles.bodySm),
            ],
          ),
        ),
        const Icon(LucideIcons.chevronRight,
            size: 16, color: AppColors.neutral400),
      ]),
    );
  }
}

// ── Access Log Banner ───────────────────────────────────────────────────────

class _AccessLogBanner extends StatelessWidget {
  const _AccessLogBanner({required this.count});
  final int count;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(children: [
        Container(
          width: 40, height: 40,
          decoration: BoxDecoration(
            color: AppColors.infoLight,
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(LucideIcons.eye,
              size: 20, color: AppColors.info),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '$count recent access${count > 1 ? 'es' : ''}',
                style: AppTextStyles.body
                    .copyWith(fontWeight: FontWeight.w600),
              ),
              Text(
                'See who accessed your health records.',
                style: AppTextStyles.bodySm,
              ),
            ],
          ),
        ),
        const Icon(LucideIcons.chevronRight,
            size: 16, color: AppColors.neutral400),
      ]),
    );
  }
}
