import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../providers/care_plans_provider.dart';

class CarePlanDetailScreen extends ConsumerWidget {
  const CarePlanDetailScreen({super.key, required this.id});
  final String id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final planAsync = ref.watch(carePlanDetailProvider(id));
    return Scaffold(
      appBar: AppBar(title: const Text('Care Plan')),
      body: planAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 80, borderRadius: 12),
          SizedBox(height: 16),
          LoadingSkeleton(height: 160, borderRadius: 12),
          SizedBox(height: 16),
          LoadingSkeleton(height: 120, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(carePlanDetailProvider(id)),
        ),
        data: (plan) {
          final achieved = plan.goals.where((g) => g.status == 'achieved').length;
          final total    = plan.goals.length;
          final pct      = plan.progressPct ??
              (total > 0 ? ((achieved / total) * 100).round() : 0);

          return ListView(padding: const EdgeInsets.all(16), children: [
            // Progress card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              child: Column(children: [
                Text(plan.title,
                    style: AppTextStyles.h4,
                    textAlign: TextAlign.center),
                const SizedBox(height: 12),
                SizedBox(
                  width: 80, height: 80,
                  child: Stack(alignment: Alignment.center, children: [
                    CircularProgressIndicator(
                      value: pct / 100,
                      backgroundColor: AppColors.neutral100,
                      color: AppColors.primary500,
                      strokeWidth: 8,
                    ),
                    Text('$pct%',
                        style: AppTextStyles.h3
                            .copyWith(color: AppColors.primary500)),
                  ]),
                ),
                const SizedBox(height: 8),
                Text('$achieved of $total goals achieved',
                    style: AppTextStyles.bodySm),
              ]),
            ),
            const SizedBox(height: 20),

            // Goals
            if (plan.goals.isNotEmpty) ...[
              Row(children: [
                const Icon(LucideIcons.target,
                    size: 15, color: AppColors.primary500),
                const SizedBox(width: 6),
                Text('GOALS', style: AppTextStyles.label),
              ]),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Column(
                  children: plan.goals.map((goal) {
                    final isAchieved = goal.status == 'achieved';
                    return Column(children: [
                      Padding(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 14, vertical: 12),
                        child: Row(children: [
                          Icon(
                            isAchieved
                                ? LucideIcons.checkCircle
                                : LucideIcons.circle,
                            size: 16,
                            color: isAchieved
                                ? AppColors.success
                                : AppColors.neutral400,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(goal.description,
                                style: AppTextStyles.body),
                          ),
                        ]),
                      ),
                      if (goal != plan.goals.last)
                        const Divider(height: 1, indent: 40),
                    ]);
                  }).toList(),
                ),
              ),
              const SizedBox(height: 20),
            ],

            // Interventions
            if (plan.interventions.isNotEmpty) ...[
              Row(children: [
                const Icon(LucideIcons.stethoscope,
                    size: 15, color: AppColors.primary500),
                const SizedBox(width: 6),
                Text('INTERVENTIONS', style: AppTextStyles.label),
              ]),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Column(
                  children: plan.interventions.map((iv) => Column(children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 12),
                      child: Row(children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.primary50,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(iv.type.toUpperCase(),
                              style: AppTextStyles.caption.copyWith(
                                  color: AppColors.primary500,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 9)),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                            child: Text(iv.description,
                                style: AppTextStyles.body)),
                      ]),
                    ),
                    if (iv != plan.interventions.last)
                      const Divider(height: 1, indent: 14),
                  ])).toList(),
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
