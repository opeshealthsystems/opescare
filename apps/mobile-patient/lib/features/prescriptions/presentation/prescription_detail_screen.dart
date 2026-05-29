import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../providers/prescriptions_provider.dart';

class PrescriptionDetailScreen extends ConsumerWidget {
  const PrescriptionDetailScreen({super.key, required this.id});
  final String id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final rxAsync = ref.watch(prescriptionDetailProvider(id));
    return Scaffold(
      appBar: AppBar(title: const Text('Prescription')),
      body: rxAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 260, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(prescriptionDetailProvider(id)),
        ),
        data: (rx) {
          String date = rx.prescribedAt;
          try {
            date = DateFormat('d MMM yyyy')
                .format(DateTime.parse(rx.prescribedAt));
          } catch (_) {}

          return ListView(padding: const EdgeInsets.all(16), children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    Container(
                      width: 44, height: 44,
                      decoration: BoxDecoration(
                        color: AppColors.infoLight,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(LucideIcons.pill,
                          size: 22, color: AppColors.info),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(rx.medicationName,
                              style: AppTextStyles.h4),
                          Text('${rx.dosage} · ${rx.frequency}',
                              style: AppTextStyles.bodySm),
                        ],
                      ),
                    ),
                  ]),
                  const SizedBox(height: 16),
                  const Divider(),
                  const SizedBox(height: 12),
                  _Row('Prescribed by', rx.prescribedBy),
                  _Row('Facility',      rx.facilityName),
                  _Row('Date',          date),
                  _Row('Status',        rx.status.toUpperCase()),
                  if (rx.dispensedAt != null)
                    _Row('Dispensed', rx.dispensedAt!),
                  if (rx.expiresAt != null)
                    _Row('Expires', rx.expiresAt!),
                  if (rx.instructions != null) ...[
                    const SizedBox(height: 12),
                    const Divider(),
                    const SizedBox(height: 12),
                    Text('Instructions', style: AppTextStyles.h4),
                    const SizedBox(height: 8),
                    Text(rx.instructions!, style: AppTextStyles.body),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 32),
          ]);
        },
      ),
    );
  }
}

class _Row extends StatelessWidget {
  const _Row(this.label, this.value);
  final String label, value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(children: [
        Expanded(
            child: Text(label, style: AppTextStyles.bodySm)),
        Expanded(
          flex: 2,
          child: Text(
            value,
            style: AppTextStyles.body
                .copyWith(fontWeight: FontWeight.w600),
            textAlign: TextAlign.right,
          ),
        ),
      ]),
    );
  }
}
