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
import '../../../shared/widgets/status_badge.dart';
import '../models/appointment.dart';
import '../providers/appointments_provider.dart';

class AppointmentsScreen extends ConsumerWidget {
  const AppointmentsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final apptAsync = ref.watch(appointmentsListProvider);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Appointments'),
        actions: [
          IconButton(
            icon: const Icon(LucideIcons.calendarPlus),
            onPressed: () {},
            tooltip: 'Book appointment',
          ),
        ],
      ),
      body: apptAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 100, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 100, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(appointmentsListProvider),
        ),
        data: (list) {
          if (list.isEmpty) {
            return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.calendar, size: 48, color: AppColors.neutral300),
              const SizedBox(height: 12),
              Text('No appointments yet.', style: AppTextStyles.bodySm),
            ]));
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(appointmentsListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _ApptCard(appt: list[i]),
            ),
          );
        },
      ),
    );
  }
}

class _ApptCard extends StatelessWidget {
  const _ApptCard({required this.appt});
  final Appointment appt;

  DateTime _tryParse(String s) {
    try { return DateTime.parse(s); } catch (_) { return DateTime.now(); }
  }

  @override
  Widget build(BuildContext context) {
    final dt = _tryParse(appt.scheduledAt);
    final date = DateFormat('EEE, d MMM yyyy').format(dt);
    final time = DateFormat('h:mm a').format(dt);
    final isUpcoming = appt.status == 'confirmed' || appt.status == 'pending';
    final badge = _badge(appt.status);

    return GestureDetector(
      onTap: () => context.push('/appointments/${appt.id}'),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isUpcoming ? AppColors.primary200 : AppColors.divider,
          ),
        ),
        child: Row(children: [
          Container(
            width: 48, height: 48,
            decoration: BoxDecoration(
              color: isUpcoming ? AppColors.primary50 : AppColors.neutral100,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
              Text(DateFormat('d').format(dt),
                  style: AppTextStyles.h3.copyWith(
                      color: isUpcoming ? AppColors.primary500 : AppColors.neutral400,
                      fontSize: 18)),
              Text(DateFormat('MMM').format(dt),
                  style: AppTextStyles.caption.copyWith(
                      color: isUpcoming ? AppColors.primary500 : AppColors.neutral400)),
            ]),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              Expanded(child: Text(appt.facilityName,
                  style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis)),
              if (badge != null) StatusBadge(badge, small: true),
            ]),
            const SizedBox(height: 3),
            Text(appt.serviceType, style: AppTextStyles.bodySm),
            const SizedBox(height: 4),
            Row(children: [
              const Icon(LucideIcons.clock, size: 12, color: AppColors.neutral400),
              const SizedBox(width: 4),
              Text(time, style: AppTextStyles.caption),
            ]),
          ])),
          const Icon(LucideIcons.chevronRight, size: 16, color: AppColors.neutral400),
        ]),
      ),
    );
  }

  BadgeStatus? _badge(String s) => switch (s) {
        'confirmed' => BadgeStatus.active,
        'pending'   => BadgeStatus.pending,
        'completed' => BadgeStatus.released,
        'cancelled' || 'no_show' => BadgeStatus.cancelled,
        _ => null,
      };
}
