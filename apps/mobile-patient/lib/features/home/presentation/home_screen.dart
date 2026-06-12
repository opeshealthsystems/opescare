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

// ─────────────────────────────────────────────────────────────────────────────

class _HomeBody extends StatelessWidget {
  const _HomeBody({required this.summary});
  final DashboardSummary summary;

  @override
  Widget build(BuildContext context) {
    final hasPendingItems = summary.pendingConsentCount > 0 ||
        summary.pendingSurveyCount > 0;

    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      children: [
        const SizedBox(height: 16),

        // ── Header ──────────────────────────────────────────────────────────
        Row(children: [
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(_greeting(),
                  style: AppTextStyles.bodySm
                      .copyWith(color: AppColors.textSecondary, fontSize: 12)),
              const SizedBox(height: 2),
              Text('${summary.patientName} 👋',
                  style: AppTextStyles.h3),
            ]),
          ),
          // Notification bell with badge
          GestureDetector(
            onTap: hasPendingItems
                ? () => context.push(Routes.consent)
                : null,
            child: Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: AppColors.primary50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  const Icon(LucideIcons.bell, size: 18, color: AppColors.primary500),
                  if (hasPendingItems)
                    Positioned(
                      top: 6, right: 6,
                      child: Container(
                        width: 8, height: 8,
                        decoration: const BoxDecoration(
                          color: AppColors.danger,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ),
        ]),
        const SizedBox(height: 16),

        // ── Health ID Banner ─────────────────────────────────────────────────
        _HealthIdBanner(
          healthId: summary.healthId,
          isVerified: summary.isVerified,
          onTap: () => context.go(Routes.healthId),
        ),
        const SizedBox(height: 14),

        // ── Stats Row ────────────────────────────────────────────────────────
        Row(children: [
          _StatCard(
            value: summary.activeRxCount.toString(),
            label: 'Active Rx',
            onTap: () => context.push(Routes.prescriptions),
          ),
          const SizedBox(width: 10),
          _StatCard(
            value: summary.unreadLabCount.toString(),
            label: 'Lab Results',
            onTap: () => context.push(Routes.labs),
          ),
          const SizedBox(width: 10),
          _StatCard(
            value: summary.nextAppointmentDate != null ? '1+' : '0',
            label: 'Upcoming',
            onTap: () => context.push(Routes.appointments),
          ),
        ]),
        const SizedBox(height: 20),

        // ── Quick Actions ────────────────────────────────────────────────────
        Text('Quick Actions',
            style: AppTextStyles.body.copyWith(
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            )),
        const SizedBox(height: 10),
        Row(children: [
          _QuickActionBtn(
            icon: LucideIcons.calendar,
            label: 'Book Appt',
            iconColor: AppColors.primary500,
            bgColor: AppColors.primary50,
            onTap: () => context.push(Routes.bookAppointment),
          ),
          const SizedBox(width: 10),
          _QuickActionBtn(
            icon: LucideIcons.flaskConical,
            label: 'Lab Results',
            iconColor: AppColors.success,
            bgColor: AppColors.successLight,
            onTap: () => context.push(Routes.labs),
          ),
          const SizedBox(width: 10),
          _QuickActionBtn(
            icon: LucideIcons.mapPin,
            label: 'Care Map',
            iconColor: AppColors.info,
            bgColor: AppColors.infoLight,
            onTap: () => context.push(Routes.careMap),
          ),
          const SizedBox(width: 10),
          _QuickActionBtn(
            icon: LucideIcons.clipboardList,
            label: 'Care Plans',
            iconColor: AppColors.primary500,
            bgColor: AppColors.primary50,
            onTap: () => context.push(Routes.carePlans),
          ),
        ]),
        const SizedBox(height: 20),

        // ── Upcoming ─────────────────────────────────────────────────────────
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Upcoming',
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w700)),
            GestureDetector(
              onTap: () => context.push(Routes.appointments),
              child: Text('See all',
                  style: AppTextStyles.bodySm.copyWith(
                    color: AppColors.primary500,
                    fontWeight: FontWeight.w600,
                  )),
            ),
          ],
        ),
        const SizedBox(height: 10),

        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.divider),
          ),
          child: Column(children: [
            if (summary.nextAppointmentDate != null)
              _ListRow(
                iconBg: AppColors.primary50,
                icon: LucideIcons.calendar,
                iconColor: AppColors.primary500,
                title: summary.nextAppointmentFacility ?? 'Appointment',
                subtitle: _fmtDate(summary.nextAppointmentDate!),
                pill: _Pill.blue('Confirmed'),
                isLast: summary.unreadLabCount == 0,
                onTap: () => context.push(Routes.appointments),
              ),
            if (summary.unreadLabCount > 0)
              _ListRow(
                iconBg: AppColors.warningLight,
                icon: LucideIcons.flaskConical,
                iconColor: AppColors.warning,
                title: '${summary.unreadLabCount} Lab Result${summary.unreadLabCount > 1 ? 's' : ''} Ready',
                subtitle: 'Tap to review',
                pill: _Pill.amber('Review'),
                isLast: true,
                onTap: () => context.push(Routes.labs),
              ),
            if (summary.nextAppointmentDate == null && summary.unreadLabCount == 0)
              Padding(
                padding: const EdgeInsets.all(20),
                child: Text('No upcoming items.',
                    style: AppTextStyles.bodySm, textAlign: TextAlign.center),
              ),
          ]),
        ),

        // ── Consent request banner ────────────────────────────────────────────
        if (summary.pendingConsentCount > 0) ...[
          const SizedBox(height: 12),
          GestureDetector(
            onTap: () => context.push(Routes.consent),
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.infoLight,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.primary200),
              ),
              child: Row(children: [
                const Icon(LucideIcons.shield,
                    size: 18, color: AppColors.primary500),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text(
                      'Consent Request${summary.pendingConsentCount > 1 ? 's' : ''} Pending',
                      style: AppTextStyles.body.copyWith(
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF1E40AF),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'A facility wants access to your health records.',
                      style: AppTextStyles.bodySm.copyWith(
                          color: AppColors.info),
                    ),
                  ]),
                ),
                Text('Review →',
                    style: AppTextStyles.bodySm.copyWith(
                      color: AppColors.primary500,
                      fontWeight: FontWeight.w700,
                    )),
              ]),
            ),
          ),
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

  String _fmtDate(String raw) {
    try {
      return DateFormat('EEE, d MMM · h:mm a').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Health ID Banner
// ─────────────────────────────────────────────────────────────────────────────

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
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: AppColors.primary500.withValues(alpha: 0.35),
              blurRadius: 16,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Row(children: [
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('HEALTH ID',
                  style: AppTextStyles.monoXs.copyWith(
                    color: Colors.white60,
                    letterSpacing: 0.1,
                  )),
              const SizedBox(height: 4),
              Text(healthId, style: AppTextStyles.healthId.copyWith(fontSize: 14)),
            ]),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.qrCode, size: 14, color: Colors.white),
              const SizedBox(width: 6),
              Text('Scan ID',
                  style: AppTextStyles.button.copyWith(
                    color: Colors.white,
                    fontSize: 12,
                  )),
            ]),
          ),
        ]),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Stat Card — large mono number on top, small label below
// ─────────────────────────────────────────────────────────────────────────────

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.value,
    required this.label,
    required this.onTap,
  });

  final String value, label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider),
          ),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Text(value,
                style: AppTextStyles.monoLg.copyWith(
                  color: AppColors.primary500,
                  fontSize: 24,
                  height: 1,
                )),
            const SizedBox(height: 5),
            Text(label,
                style: AppTextStyles.caption.copyWith(
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textMuted,
                  letterSpacing: 0.04,
                ),
                textAlign: TextAlign.center),
          ]),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Quick Action Button
// ─────────────────────────────────────────────────────────────────────────────

class _QuickActionBtn extends StatelessWidget {
  const _QuickActionBtn({
    required this.icon,
    required this.label,
    required this.iconColor,
    required this.bgColor,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final Color iconColor, bgColor;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider),
          ),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Container(
              width: 38, height: 38,
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 18, color: iconColor),
            ),
            const SizedBox(height: 7),
            Text(
              label,
              style: AppTextStyles.caption.copyWith(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
                height: 1.2,
              ),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ]),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// List Row — used in Upcoming section
// ─────────────────────────────────────────────────────────────────────────────

class _Pill {
  const _Pill._(this.label, this.bg, this.fg);
  const _Pill.blue(String label)
      : this._(label, AppColors.infoLight, const Color(0xFF1E40AF));
  const _Pill.amber(String label)
      : this._(label, AppColors.warningLight, const Color(0xFF92400E));

  final String label;
  final Color bg, fg;
}

class _ListRow extends StatelessWidget {
  const _ListRow({
    required this.iconBg,
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.subtitle,
    required this.pill,
    required this.isLast,
    required this.onTap,
  });

  final Color iconBg, iconColor;
  final IconData icon;
  final String title, subtitle;
  final _Pill pill;
  final bool isLast;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Column(children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
          child: Row(children: [
            Container(
              width: 40, height: 40,
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 18, color: iconColor),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(title,
                    style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600)),
                const SizedBox(height: 2),
                Text(subtitle, style: AppTextStyles.bodySm),
              ]),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
              decoration: BoxDecoration(
                color: pill.bg,
                borderRadius: BorderRadius.circular(999),
              ),
              child: Text(pill.label,
                  style: AppTextStyles.caption.copyWith(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: pill.fg,
                    letterSpacing: 0.04,
                  )),
            ),
          ]),
        ),
        if (!isLast)
          const Divider(height: 1, color: AppColors.divider, indent: 66),
      ]),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Loading Skeleton
// ─────────────────────────────────────────────────────────────────────────────

class HomeScreenSkeleton extends StatelessWidget {
  const HomeScreenSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: const [
        LoadingSkeleton(height: 40, borderRadius: 8),
        SizedBox(height: 16),
        LoadingSkeleton(height: 68, borderRadius: 14),
        SizedBox(height: 14),
        LoadingSkeleton(height: 72, borderRadius: 12),
        SizedBox(height: 20),
        LoadingSkeleton(height: 88, borderRadius: 12),
        SizedBox(height: 20),
        LoadingSkeleton(height: 130, borderRadius: 14),
      ],
    );
  }
}
