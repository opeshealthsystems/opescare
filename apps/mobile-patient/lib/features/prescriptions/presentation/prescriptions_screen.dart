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
import '../models/prescription.dart';
import '../providers/prescriptions_provider.dart';

class PrescriptionsScreen extends ConsumerWidget {
  const PrescriptionsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final rxAsync = ref.watch(prescriptionsListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Prescriptions')),
      body: rxAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 88, borderRadius: 12),
            SizedBox(height: 10),
            LoadingSkeleton(height: 88, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(prescriptionsListProvider),
        ),
        data: (list) {
          if (list.isEmpty) {
            return Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(LucideIcons.pill,
                    size: 48, color: AppColors.neutral300),
                const SizedBox(height: 12),
                Text('No prescriptions yet.',
                    style: AppTextStyles.bodySm),
              ]),
            );
          }
          return RefreshIndicator(
            onRefresh: () async =>
                ref.invalidate(prescriptionsListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _RxCard(rx: list[i]),
            ),
          );
        },
      ),
    );
  }
}

class _RxCard extends StatelessWidget {
  const _RxCard({required this.rx});
  final Prescription rx;

  @override
  Widget build(BuildContext context) {
    String formattedDate = rx.prescribedAt;
    try {
      formattedDate = DateFormat('d MMM yyyy')
          .format(DateTime.parse(rx.prescribedAt));
    } catch (_) {}

    final isActive =
        rx.status == 'active' || rx.status == 'issued';
    final badge = _badge(rx.status);

    return GestureDetector(
      onTap: () => context.push('/prescriptions/${rx.id}'),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Row(children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(
              color: isActive
                  ? AppColors.infoLight
                  : AppColors.neutral100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              LucideIcons.pill,
              size: 22,
              color:
                  isActive ? AppColors.info : AppColors.neutral400,
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
                      rx.medicationName,
                      style: AppTextStyles.body
                          .copyWith(fontWeight: FontWeight.w600),
                    ),
                  ),
                  if (badge != null) StatusBadge(badge, small: true),
                ]),
                const SizedBox(height: 3),
                Text('${rx.dosage} · ${rx.frequency}',
                    style: AppTextStyles.bodySm),
                const SizedBox(height: 4),
                Row(children: [
                  const Icon(LucideIcons.user,
                      size: 12, color: AppColors.neutral400),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(rx.prescribedBy,
                        style: AppTextStyles.caption,
                        overflow: TextOverflow.ellipsis),
                  ),
                  Text(formattedDate, style: AppTextStyles.caption),
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

  BadgeStatus? _badge(String s) => switch (s) {
        'active' || 'issued'                         => BadgeStatus.active,
        'dispensed' || 'partially_dispensed'         => BadgeStatus.released,
        'cancelled'                                  => BadgeStatus.cancelled,
        'expired'                                    => BadgeStatus.cancelled,
        _                                            => null,
      };
}
