import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/status_badge.dart';
import '../models/lab_result.dart';
import '../providers/labs_provider.dart';

class LabsScreen extends ConsumerWidget {
  const LabsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final labsAsync = ref.watch(labsListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Lab Results')),
      body: labsAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 88, borderRadius: 12),
            SizedBox(height: 10),
            LoadingSkeleton(height: 88, borderRadius: 12),
            SizedBox(height: 10),
            LoadingSkeleton(height: 88, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(labsListProvider),
        ),
        data: (labs) {
          if (labs.isEmpty) {
            return Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(LucideIcons.flaskConical,
                    size: 48, color: AppColors.neutral300),
                const SizedBox(height: 12),
                Text('No lab results yet.', style: AppTextStyles.bodySm),
              ]),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(labsListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: labs.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _LabCard(lab: labs[i]),
            ),
          );
        },
      ),
    );
  }
}

class _LabCard extends StatelessWidget {
  const _LabCard({required this.lab});
  final LabResult lab;

  @override
  Widget build(BuildContext context) {
    String formattedDate = lab.orderedAt;
    try {
      formattedDate =
          DateFormat('d MMM yyyy').format(DateTime.parse(lab.orderedAt));
    } catch (_) {}

    final badge = _statusBadge(lab.status);
    final isCritical = lab.isCritical == true;

    return GestureDetector(
      onTap: () => context.push('/labs/${lab.id}'),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isCritical
                ? AppColors.dangerBorder
                : AppColors.divider,
          ),
        ),
        child: Row(children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(
              color: isCritical
                  ? AppColors.dangerLight
                  : AppColors.successLight,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              LucideIcons.flaskConical,
              size: 22,
              color: isCritical ? AppColors.danger : AppColors.success,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(children: [
                  Expanded(
                    child: Text(
                      lab.testName,
                      style: AppTextStyles.body
                          .copyWith(fontWeight: FontWeight.w600),
                    ),
                  ),
                  if (badge != null) StatusBadge(badge, small: true),
                ]),
                const SizedBox(height: 4),
                Text(lab.facilityName,
                    style: AppTextStyles.bodySm,
                    overflow: TextOverflow.ellipsis),
                const SizedBox(height: 4),
                Row(children: [
                  const Icon(LucideIcons.calendar,
                      size: 12, color: AppColors.neutral400),
                  const SizedBox(width: 4),
                  Text(formattedDate, style: AppTextStyles.caption),
                  if (isCritical) ...[
                    const SizedBox(width: 8),
                    const Icon(LucideIcons.alertTriangle,
                        size: 12, color: AppColors.danger),
                    const SizedBox(width: 2),
                    Text(
                      'Critical',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.danger,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ]),
              ],
            ),
          ),
          const Icon(LucideIcons.chevronRight,
              size: 16, color: AppColors.neutral400),
        ]),
      ),
    );
  }

  BadgeStatus? _statusBadge(String s) => switch (s) {
        'released'   => BadgeStatus.released,
        'pending'    => BadgeStatus.pending,
        'processing' => BadgeStatus.pending,
        'amended'    => BadgeStatus.provisional,
        'critical'   => BadgeStatus.critical,
        _            => null,
      };
}
