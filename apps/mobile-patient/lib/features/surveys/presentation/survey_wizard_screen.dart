import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../models/survey.dart';
import '../providers/surveys_provider.dart';

class SurveyWizardScreen extends ConsumerStatefulWidget {
  const SurveyWizardScreen({super.key, required this.id});
  final String id;

  @override
  ConsumerState<SurveyWizardScreen> createState() => _SurveyWizardState();
}

class _SurveyWizardState extends ConsumerState<SurveyWizardScreen> {
  int _currentIndex = 0;
  final Map<String, dynamic> _responses = {};
  bool _isSubmitting = false;
  String? _submitError;

  @override
  Widget build(BuildContext context) {
    final surveyAsync = ref.watch(surveyDetailProvider(widget.id));
    return Scaffold(
      body: surveyAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Scaffold(
          appBar: AppBar(),
          body: ErrorView(
            message: e.toString(),
            onRetry: () => ref.invalidate(surveyDetailProvider(widget.id)),
          ),
        ),
        data: (survey) => _buildWizard(context, survey),
      ),
    );
  }

  Widget _buildWizard(BuildContext context, Survey survey) {
    final questions = survey.questions;
    if (questions.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(survey.title)),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.alertCircle,
                  size: 48, color: AppColors.neutral300),
              const SizedBox(height: 12),
              Text('This survey has no questions.',
                  style: AppTextStyles.body),
            ]),
          ),
        ),
      );
    }

    final q      = questions[_currentIndex];
    final isLast = _currentIndex == questions.length - 1;
    final progress = (_currentIndex + 1) / questions.length;

    return Scaffold(
      appBar: AppBar(
        title: Text(survey.title),
        leading: IconButton(
          icon: const Icon(LucideIcons.arrowLeft),
          onPressed: () {
            if (_currentIndex == 0) {
              context.pop();
            } else {
              setState(() => _currentIndex--);
            }
          },
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(4),
          child: LinearProgressIndicator(
            value: progress,
            backgroundColor: AppColors.neutral100,
            color: AppColors.primary500,
            minHeight: 4,
          ),
        ),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(
            'Question ${_currentIndex + 1} of ${questions.length}',
            style: AppTextStyles.caption,
          ),
          const SizedBox(height: 8),
          Text(q.text, style: AppTextStyles.h4),
          const SizedBox(height: 20),
          Expanded(child: _buildQuestionInput(q)),

          if (_submitError != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.dangerLight,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_submitError!,
                  style: AppTextStyles.bodySm
                      .copyWith(color: AppColors.danger)),
            ),
            const SizedBox(height: 12),
          ],

          ElevatedButton(
            onPressed: _isSubmitting ? null : () => _handleNext(survey, isLast),
            child: _isSubmitting
                ? const SizedBox(
                    height: 20, width: 20,
                    child: CircularProgressIndicator(
                        color: Colors.white, strokeWidth: 2))
                : Text(isLast ? 'Submit' : 'Next'),
          ),
          const SizedBox(height: 8),
        ]),
      ),
    );
  }

  Widget _buildQuestionInput(SurveyQuestion q) {
    if (q.type == 'text') {
      return TextFormField(
        maxLines: 4,
        onChanged: (v) => _responses[q.key] = v,
        initialValue: _responses[q.key] as String?,
        style: AppTextStyles.body,
        decoration: const InputDecoration(
          hintText: 'Your answer...',
        ),
      );
    }

    if (q.type == 'single_choice') {
      return ListView(
        children: q.options.map((opt) {
          return GestureDetector(
            onTap: () => setState(() => _responses[q.key] = opt),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 8.0),
              child: Row(children: [
                // ignore: deprecated_member_use
                Radio<String>(
                  value: opt,
                  // ignore: deprecated_member_use
                  groupValue: _responses[q.key] as String?,
                  // ignore: deprecated_member_use
                  onChanged: (v) {
                    setState(() => _responses[q.key] = v);
                  },
                  activeColor: AppColors.primary500,
                ),
                const SizedBox(width: 8),
                Expanded(child: Text(opt, style: AppTextStyles.body)),
              ]),
            ),
          );
        }).toList(),
      );
    }

    // multi_choice
    final selected = (_responses[q.key] as List<String>?) ?? [];
    return ListView(
      children: q.options.map((opt) {
        final isChecked = selected.contains(opt);
        return GestureDetector(
          onTap: () {
            final updated = List<String>.from(selected);
            if (isChecked) {
              updated.remove(opt);
            } else {
              updated.add(opt);
            }
            setState(() => _responses[q.key] = updated);
          },
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 8.0),
            child: Row(children: [
              Checkbox(
                value: isChecked,
                onChanged: (v) {
                  final updated = List<String>.from(selected);
                  if (v == true) {
                    updated.add(opt);
                  } else {
                    updated.remove(opt);
                  }
                  setState(() => _responses[q.key] = updated);
                },
                activeColor: AppColors.primary500,
              ),
              const SizedBox(width: 8),
              Expanded(child: Text(opt, style: AppTextStyles.body)),
            ]),
          ),
        );
      }).toList(),
    );
  }

  Future<void> _handleNext(Survey survey, bool isLast) async {
    if (!isLast) {
      setState(() => _currentIndex++);
      return;
    }
    setState(() { _isSubmitting = true; _submitError = null; });
    try {
      await ref.read(surveysRepositoryProvider).submit(widget.id, _responses);
      ref.invalidate(surveysListProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Survey submitted — thank you!')),
        );
        context.pop();
      }
    } catch (e) {
      setState(() {
        _isSubmitting = false;
        _submitError = 'Submission failed. Please try again.';
      });
    }
  }
}
