import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/care_plan.dart';
import '../providers/care_plans_provider.dart';

class CarePlansScreen extends ConsumerWidget {
  const CarePlansScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final plansAsync = ref.watch(carePlansListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Care Plans')),
      body: plansAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 100, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 100, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(carePlansListProvider),
        ),
        data: (plans) {
          if (plans.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(LucideIcons.clipboardList,
                      size: 48, color: AppColors.neutral300),
                  const SizedBox(height: 12),
                  Text('No active care plans',
                      style: AppTextStyles.body),
                  const SizedBox(height: 6),
                  Text('Your care team will assign a plan when needed.',
                      style: AppTextStyles.bodySm,
                      textAlign: TextAlign.center),
                ]),
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(carePlansListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: plans.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _PlanCard(plan: plans[i]),
            ),
          );
        },
      ),
    );
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan});
  final CarePlan plan;

  @override
  Widget build(BuildContext context) {
    final achieved = plan.goals.where((g) => g.status == 'achieved').length;
    final total    = plan.goals.length;
    final progress = total > 0 ? achieved / total : 0.0;

    return GestureDetector(
      onTap: () => context.push('${Routes.carePlans}/${plan.id}'),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(
              child: Text(plan.title,
                  style: AppTextStyles.body
                      .copyWith(fontWeight: FontWeight.w600)),
            ),
            const Icon(LucideIcons.chevronRight,
                size: 16, color: AppColors.neutral400),
          ]),
          const SizedBox(height: 8),
          if (total > 0) ...[
            Row(children: [
              Expanded(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: progress,
                    backgroundColor: AppColors.neutral100,
                    color: AppColors.primary500,
                    minHeight: 6,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Text('$achieved/$total goals',
                  style: AppTextStyles.caption),
            ]),
          ] else
            Text('No goals defined', style: AppTextStyles.caption),
        ]),
      ),
    );
  }
}
