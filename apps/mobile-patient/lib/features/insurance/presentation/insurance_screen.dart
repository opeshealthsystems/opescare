import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/status_badge.dart';
import '../models/insurance_policy.dart';
import '../providers/insurance_provider.dart';

class InsuranceScreen extends ConsumerWidget {
  const InsuranceScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final policiesAsync = ref.watch(insurancePoliciesProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Insurance')),
      body: policiesAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 120, borderRadius: 12),
            SizedBox(height: 10),
            LoadingSkeleton(height: 120, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(insurancePoliciesProvider),
        ),
        data: (list) {
          if (list.isEmpty) {
            return Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(LucideIcons.shieldCheck,
                    size: 48, color: AppColors.neutral300),
                const SizedBox(height: 12),
                Text('No insurance policies found.',
                    style: AppTextStyles.bodySm),
              ]),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(insurancePoliciesProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _PolicyCard(policy: list[i]),
            ),
          );
        },
      ),
    );
  }
}

class _PolicyCard extends StatelessWidget {
  const _PolicyCard({required this.policy});
  final InsurancePolicy policy;

  String _formatDate(String s) {
    try {
      return DateFormat('d MMM yyyy').format(DateTime.parse(s));
    } catch (_) {
      return s;
    }
  }

  @override
  Widget build(BuildContext context) {
    final badge = policy.isActive ? BadgeStatus.active : BadgeStatus.cancelled;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: policy.isActive ? AppColors.primary200 : AppColors.divider,
        ),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            width: 42, height: 42,
            decoration: BoxDecoration(
              color: policy.isActive
                  ? AppColors.primary50
                  : AppColors.neutral100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              LucideIcons.shieldCheck,
              size: 20,
              color: policy.isActive
                  ? AppColors.primary500
                  : AppColors.neutral400,
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
                      policy.providerName ?? 'Insurance Provider',
                      style: AppTextStyles.body
                          .copyWith(fontWeight: FontWeight.w600),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  StatusBadge(badge, small: true),
                ]),
                if (policy.planName != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    policy.planName!,
                    style: AppTextStyles.bodySm,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
        ]),
        const SizedBox(height: 12),
        const Divider(height: 1, color: AppColors.divider),
        const SizedBox(height: 12),
        _InfoRow(
          icon: LucideIcons.hash,
          label: 'Policy No.',
          value: policy.policyNumber,
        ),
        const SizedBox(height: 6),
        _InfoRow(
          icon: LucideIcons.calendarCheck,
          label: 'Start',
          value: _formatDate(policy.startDate),
        ),
        if (policy.endDate != null && policy.endDate!.isNotEmpty) ...[
          const SizedBox(height: 6),
          _InfoRow(
            icon: LucideIcons.calendarX,
            label: 'End',
            value: _formatDate(policy.endDate!),
          ),
        ],
      ]),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Icon(icon, size: 13, color: AppColors.neutral400),
      const SizedBox(width: 6),
      Text(
        '$label: ',
        style: AppTextStyles.caption
            .copyWith(color: AppColors.textSecondary),
      ),
      Expanded(
        child: Text(
          value,
          style: AppTextStyles.caption
              .copyWith(color: AppColors.textPrimary),
          overflow: TextOverflow.ellipsis,
        ),
      ),
    ]);
  }
}
