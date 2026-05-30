import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../providers/labs_provider.dart';

class LabDetailScreen extends ConsumerWidget {
  const LabDetailScreen({super.key, required this.id});
  final String id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final labAsync = ref.watch(labDetailProvider(id));
    return Scaffold(
      appBar: AppBar(title: const Text('Lab Result')),
      body: labAsync.when(
        loading: () => const _LabDetailSkeleton(),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(labDetailProvider(id)),
        ),
        data: (lab) {
          try { FirebaseAnalytics.instance.logEvent(name: 'lab_result_viewed', parameters: {'lab_id': id}); } catch (_) {}
          return ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (lab.isCritical == true)
              Container(
                padding: const EdgeInsets.all(12),
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: AppColors.dangerLight,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                      color: AppColors.dangerSurface),
                ),
                child: Row(children: [
                  const Icon(LucideIcons.alertTriangle,
                      color: AppColors.danger, size: 18),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'This is a critical result. Please review with your healthcare provider.',
                      style: AppTextStyles.body
                          .copyWith(color: AppColors.dangerDark),
                    ),
                  ),
                ]),
              ),
            _DetailCard(
              title: lab.testName,
              rows: [
                if (lab.resultValue != null)
                  _Row(
                    'Result',
                    '${lab.resultValue}'
                    '${lab.unit != null ? ' ${lab.unit}' : ''}',
                    valueStyle: AppTextStyles.h3.copyWith(
                      color: lab.isCritical == true
                          ? AppColors.danger
                          : AppColors.textPrimary,
                    ),
                  ),
                if (lab.referenceRange != null)
                  _Row('Reference Range', lab.referenceRange!),
                _Row('Status', lab.status.toUpperCase()),
                _Row('Ordered', _fmt(lab.orderedAt)),
                if (lab.releasedAt != null)
                  _Row('Released', _fmt(lab.releasedAt!)),
                _Row('Facility', lab.facilityName),
              ],
            ),
            if (lab.notes != null) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.infoLight,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: [
                      const Icon(LucideIcons.info,
                          size: 16, color: AppColors.info),
                      const SizedBox(width: 8),
                      Text('Provider Note', style: AppTextStyles.h4),
                    ]),
                    const SizedBox(height: 8),
                    Text(lab.notes!, style: AppTextStyles.body),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.neutral50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(children: [
                const Icon(LucideIcons.info,
                    size: 14, color: AppColors.neutral400),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Results are for informational purposes. Please discuss with your doctor.',
                    style: AppTextStyles.caption,
                  ),
                ),
              ]),
            ),
            const SizedBox(height: 32),
          ],
        );
        },
      ),
    );
  }

  String _fmt(String raw) {
    try {
      return DateFormat('d MMM yyyy, h:mm a').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }
}

class _DetailCard extends StatelessWidget {
  const _DetailCard({required this.title, required this.rows});
  final String title;
  final List<_Row> rows;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(children: [
        Padding(
          padding: const EdgeInsets.all(14),
          child: Align(
            alignment: Alignment.centerLeft,
            child: Text(title, style: AppTextStyles.h4),
          ),
        ),
        const Divider(height: 1),
        ...rows.map((r) => Column(children: [
              Padding(
                padding: const EdgeInsets.symmetric(
                    horizontal: 14, vertical: 12),
                child: Row(children: [
                  Expanded(
                      child: Text(r.label, style: AppTextStyles.bodySm)),
                  Text(
                    r.value,
                    style: r.valueStyle ??
                        AppTextStyles.body
                            .copyWith(fontWeight: FontWeight.w600),
                  ),
                ]),
              ),
              if (r != rows.last)
                const Divider(height: 1, indent: 14),
            ])),
      ]),
    );
  }
}

class _Row {
  const _Row(this.label, this.value, {this.valueStyle});
  final String label, value;
  final TextStyle? valueStyle;
}

class _LabDetailSkeleton extends StatelessWidget {
  const _LabDetailSkeleton();

  @override
  Widget build(BuildContext context) => ListView(
        padding: const EdgeInsets.all(16),
        children: const [
          LoadingSkeleton(height: 200, borderRadius: 12),
          SizedBox(height: 16),
          LoadingSkeleton(height: 120, borderRadius: 12),
        ],
      );
}
