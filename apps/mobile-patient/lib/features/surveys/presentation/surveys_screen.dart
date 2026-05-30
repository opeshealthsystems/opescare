import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/survey.dart';
import '../providers/surveys_provider.dart';

class SurveysScreen extends ConsumerWidget {
  const SurveysScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final surveysAsync = ref.watch(surveysListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Health Surveys')),
      body: surveysAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 80, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 80, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(surveysListProvider),
        ),
        data: (surveys) {
          if (surveys.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(LucideIcons.clipboardCheck,
                      size: 48, color: AppColors.neutral300),
                  const SizedBox(height: 12),
                  Text('No surveys pending', style: AppTextStyles.body),
                  const SizedBox(height: 6),
                  Text('Check back later for health surveys from your provider.',
                      style: AppTextStyles.bodySm,
                      textAlign: TextAlign.center),
                ]),
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(surveysListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: surveys.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _SurveyCard(survey: surveys[i]),
            ),
          );
        },
      ),
    );
  }
}

class _SurveyCard extends StatelessWidget {
  const _SurveyCard({required this.survey});
  final Survey survey;

  @override
  Widget build(BuildContext context) {
    final isPending   = survey.status == 'sent';
    final isCompleted = survey.status == 'completed';

    return GestureDetector(
      onTap: isPending
          ? () => context.push('/surveys/${survey.id}')
          : null,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isPending ? AppColors.primarySurface : AppColors.divider,
          ),
        ),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: isPending ? AppColors.primary50 : AppColors.neutral100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              isPending ? LucideIcons.clipboardList : LucideIcons.clipboardCheck,
              size: 20,
              color: isPending ? AppColors.primary500 : AppColors.neutral400,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(survey.title,
                  style: AppTextStyles.body
                      .copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: 3),
              Text(
                isPending ? 'Pending · Tap to start' : 'Completed',
                style: AppTextStyles.bodySm.copyWith(
                    color: isPending
                        ? AppColors.primary500
                        : AppColors.textSecondary),
              ),
            ]),
          ),
          if (isPending)
            const Icon(LucideIcons.chevronRight,
                size: 16, color: AppColors.primary500),
          if (isCompleted)
            const Icon(LucideIcons.checkCircle,
                size: 18, color: AppColors.success),
        ]),
      ),
    );
  }
}
