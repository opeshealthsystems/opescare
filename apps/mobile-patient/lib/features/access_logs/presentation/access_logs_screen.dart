import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/access_log.dart';
import '../providers/access_logs_provider.dart';

class AccessLogsScreen extends ConsumerWidget {
  const AccessLogsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final logsAsync = ref.watch(accessLogsProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Access Logs')),
      body: logsAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 80, borderRadius: 10),
          SizedBox(height: 8),
          LoadingSkeleton(height: 80, borderRadius: 10),
          SizedBox(height: 8),
          LoadingSkeleton(height: 80, borderRadius: 10),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(accessLogsProvider),
        ),
        data: (logs) {
          if (logs.isEmpty) {
            return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.eye, size: 48, color: AppColors.neutral300),
              const SizedBox(height: 12),
              Text('No access logs yet.', style: AppTextStyles.bodySm),
            ]));
          }
          return Column(children: [
            Container(
              margin: const EdgeInsets.all(16),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.infoLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(children: [
                const Icon(LucideIcons.info, size: 16, color: AppColors.info),
                const SizedBox(width: 10),
                Expanded(child: Text(
                    'This log shows every time a facility or provider accessed your health records.',
                    style: AppTextStyles.bodySm)),
              ]),
            ),
            Expanded(
              child: RefreshIndicator(
                onRefresh: () async => ref.invalidate(accessLogsProvider),
                child: ListView.separated(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: logs.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (_, i) => _LogCard(log: logs[i]),
                ),
              ),
            ),
          ]);
        },
      ),
    );
  }
}

class _LogCard extends StatelessWidget {
  const _LogCard({required this.log});
  final AccessLog log;

  @override
  Widget build(BuildContext context) {
    String formattedDate = log.accessedAt;
    try {
      formattedDate = DateFormat('d MMM yyyy · h:mm a')
          .format(DateTime.parse(log.accessedAt));
    } catch (_) {}

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: log.isEmergency
              ? AppColors.danger.withOpacity(0.4)
              : AppColors.divider,
        ),
      ),
      child: Row(children: [
        Container(
          width: 40, height: 40,
          decoration: BoxDecoration(
            color: log.isEmergency ? AppColors.dangerLight : AppColors.neutral100,
            shape: BoxShape.circle,
          ),
          child: Icon(
            log.isEmergency ? LucideIcons.alertTriangle : LucideIcons.eye,
            size: 18,
            color: log.isEmergency ? AppColors.danger : AppColors.neutral500,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(child: Text(log.facilityName,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
                overflow: TextOverflow.ellipsis)),
            if (log.isEmergency)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.dangerLight,
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text('EMERGENCY',
                    style: AppTextStyles.caption.copyWith(
                        color: AppColors.danger, fontWeight: FontWeight.w700, fontSize: 9)),
              ),
          ]),
          const SizedBox(height: 3),
          Text('${log.accessorRole} · ${log.dataCategory}', style: AppTextStyles.bodySm),
          const SizedBox(height: 3),
          Text(formattedDate, style: AppTextStyles.caption),
        ])),
      ]),
    );
  }
}
