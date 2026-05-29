import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../data/appointments_repository.dart';
import '../providers/appointments_provider.dart';

class AppointmentDetailScreen extends ConsumerWidget {
  const AppointmentDetailScreen({super.key, required this.id});
  final String id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final apptAsync = ref.watch(appointmentDetailProvider(id));
    return Scaffold(
      appBar: AppBar(title: const Text('Appointment')),
      body: apptAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 240, borderRadius: 12),
          SizedBox(height: 24),
          LoadingSkeleton(height: 52, borderRadius: 10),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(appointmentDetailProvider(id)),
        ),
        data: (appt) {
          final canCancel = appt.status == 'pending' || appt.status == 'confirmed';
          String formatted = appt.scheduledAt;
          try {
            formatted = DateFormat('EEEE, d MMMM yyyy · h:mm a')
                .format(DateTime.parse(appt.scheduledAt));
          } catch (_) {}

          return ListView(padding: const EdgeInsets.all(16), children: [
            _InfoCard(rows: [
              _Row('Facility',     appt.facilityName),
              _Row('Provider',     appt.providerName),
              _Row('Service',      appt.serviceType),
              _Row('Date & Time',  formatted),
              _Row('Status',       appt.status.toUpperCase()),
              if (appt.checkInCode != null)
                _Row('Check-In Code', appt.checkInCode!),
              if (appt.notes != null)
                _Row('Notes', appt.notes!),
            ]),
            if (canCancel) ...[
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: () async {
                  final confirm = await showDialog<bool>(
                    context: context,
                    builder: (_) => AlertDialog(
                      title: const Text('Cancel appointment?'),
                      content: const Text(
                          'This action cannot be undone. The appointment will be cancelled.'),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(context, false),
                          child: const Text('Keep appointment'),
                        ),
                        ElevatedButton(
                          style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.danger,
                              minimumSize: const Size(80, 40)),
                          onPressed: () => Navigator.pop(context, true),
                          child: const Text('Cancel it'),
                        ),
                      ],
                    ),
                  );
                  if (confirm == true && context.mounted) {
                    await ref.read(appointmentsRepositoryProvider).cancel(id);
                    ref.invalidate(appointmentsListProvider);
                    if (context.mounted) Navigator.of(context).pop();
                  }
                },
                icon: const Icon(LucideIcons.x, size: 16),
                label: const Text('Cancel Appointment'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.danger,
                  minimumSize: const Size(double.infinity, 52),
                ),
              ),
            ],
            const SizedBox(height: 32),
          ]);
        },
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({required this.rows});
  final List<_Row> rows;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(children: rows.map((r) => Column(children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: Row(children: [
            Expanded(child: Text(r.label, style: AppTextStyles.bodySm)),
            Expanded(flex: 2, child: Text(r.value,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
                textAlign: TextAlign.right)),
          ]),
        ),
        if (r != rows.last) const Divider(height: 1, indent: 14),
      ])).toList()),
    );
  }
}

class _Row { const _Row(this.label, this.value); final String label, value; }
