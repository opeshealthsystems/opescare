import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/status_badge.dart';
import '../models/referral.dart';
import '../providers/referrals_provider.dart';

class ReferralsScreen extends ConsumerWidget {
  const ReferralsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final referralsAsync = ref.watch(referralsListProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Referrals')),
      body: referralsAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 110, borderRadius: 12),
            SizedBox(height: 10),
            LoadingSkeleton(height: 110, borderRadius: 12),
            SizedBox(height: 10),
            LoadingSkeleton(height: 110, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(referralsListProvider),
        ),
        data: (list) {
          if (list.isEmpty) {
            return Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(LucideIcons.arrowRightLeft,
                    size: 48, color: AppColors.neutral300),
                const SizedBox(height: 12),
                Text('No referrals found.', style: AppTextStyles.bodySm),
              ]),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(referralsListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _ReferralCard(referral: list[i]),
            ),
          );
        },
      ),
    );
  }
}

class _ReferralCard extends StatelessWidget {
  const _ReferralCard({required this.referral});
  final Referral referral;

  String _formatDate(String s) {
    try {
      return DateFormat('d MMM yyyy').format(DateTime.parse(s));
    } catch (_) {
      return s;
    }
  }

  @override
  Widget build(BuildContext context) {
    final badge = referral.isCompleted ? BadgeStatus.released : BadgeStatus.active;
    final urgencyColor = switch (referral.urgency.toLowerCase()) {
      'urgent' || 'emergency' => AppColors.danger,
      'high'                  => AppColors.warning,
      _                       => AppColors.neutral500,
    };

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: AppColors.primary50,
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(LucideIcons.arrowRightLeft,
                size: 18, color: AppColors.primary500),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(children: [
                  Expanded(
                    child: Text(
                      referral.receivingFacility,
                      style: AppTextStyles.body
                          .copyWith(fontWeight: FontWeight.w600),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  StatusBadge(badge, small: true),
                ]),
                const SizedBox(height: 2),
                Text(
                  'From: ${referral.referringFacility}',
                  style: AppTextStyles.bodySm,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ]),
        const SizedBox(height: 10),
        const Divider(height: 1, color: AppColors.divider),
        const SizedBox(height: 10),
        Text(referral.reason, style: AppTextStyles.bodySm),
        const SizedBox(height: 8),
        Row(children: [
          _Chip(
            icon: LucideIcons.alertCircle,
            label: referral.urgency.toUpperCase(),
            color: urgencyColor,
          ),
          const SizedBox(width: 8),
          _Chip(
            icon: LucideIcons.calendar,
            label: _formatDate(referral.referredAt),
            color: AppColors.neutral500,
          ),
          if (referral.completedAt != null) ...[
            const SizedBox(width: 8),
            _Chip(
              icon: LucideIcons.checkCircle,
              label: 'Done ${_formatDate(referral.completedAt!)}',
              color: AppColors.success,
            ),
          ],
        ]),
      ]),
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip({
    required this.icon,
    required this.label,
    required this.color,
  });

  final IconData icon;
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Row(mainAxisSize: MainAxisSize.min, children: [
      Icon(icon, size: 11, color: color),
      const SizedBox(width: 3),
      Text(label,
          style: AppTextStyles.caption.copyWith(color: color)),
    ]);
  }
}
