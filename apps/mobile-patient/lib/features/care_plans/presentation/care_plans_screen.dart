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
import '../models/care_plan.dart';
import '../providers/care_plans_provider.dart';

class CarePlansScreen extends ConsumerStatefulWidget {
  const CarePlansScreen({super.key});
  @override
  ConsumerState<CarePlansScreen> createState() => _CarePlansScreenState();
}

class _CarePlansScreenState extends ConsumerState<CarePlansScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabs;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 3, vsync: this);
    _tabs.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final plansAsync = ref.watch(carePlansListProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Care Plans', style: AppTextStyles.h4),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: AppColors.primary50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(LucideIcons.listFilter,
                  size: 16, color: AppColors.primary500),
            ),
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(42),
          child: plansAsync.maybeWhen(
            data: (plans) {
              final active    = plans.where((p) => p.isActive).length;
              final completed = plans.where((p) => !p.isActive).length;
              return _TabBar(
                controller: _tabs,
                activeBadge: active,
                completedBadge: completed,
              );
            },
            orElse: () => _TabBar(controller: _tabs),
          ),
        ),
      ),
      body: plansAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 160, borderRadius: 12),
            SizedBox(height: 10),
            LoadingSkeleton(height: 160, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(carePlansListProvider),
        ),
        data: (plans) {
          final active    = plans.where((p) => p.isActive).toList();
          final completed = plans.where((p) => !p.isActive).toList();

          if (plans.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(LucideIcons.clipboardList,
                      size: 52, color: AppColors.neutral300),
                  const SizedBox(height: 14),
                  Text('No care plans yet',
                      style: AppTextStyles.body
                          .copyWith(fontWeight: FontWeight.w600)),
                  const SizedBox(height: 6),
                  Text('Your care team will assign a plan when needed.',
                      style: AppTextStyles.bodySm,
                      textAlign: TextAlign.center),
                ]),
              ),
            );
          }

          return TabBarView(
            controller: _tabs,
            children: [
              _PlanList(plans: active),
              _PlanList(plans: completed),
              _PlanList(plans: plans),
            ],
          );
        },
      ),
    );
  }
}

// ── Tab bar ──────────────────────────────────────────────────────────────────

class _TabBar extends StatelessWidget {
  const _TabBar({
    required this.controller,
    this.activeBadge = 0,
    this.completedBadge = 0,
  });
  final TabController controller;
  final int activeBadge, completedBadge;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.surface,
      child: TabBar(
        controller: controller,
        labelColor: AppColors.primary500,
        unselectedLabelColor: AppColors.textSecondary,
        indicatorColor: AppColors.primary500,
        indicatorWeight: 2,
        labelStyle: AppTextStyles.bodySm.copyWith(fontWeight: FontWeight.w700),
        unselectedLabelStyle: AppTextStyles.bodySm,
        tabs: [
          _tab('Active', activeBadge),
          _tab('Completed', completedBadge),
          const Tab(text: 'All'),
        ],
      ),
    );
  }

  Widget _tab(String label, int badge) {
    return Tab(
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Text(label),
        if (badge > 0) ...[
          const SizedBox(width: 5),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
            decoration: BoxDecoration(
              color: AppColors.primary500,
              borderRadius: BorderRadius.circular(999),
            ),
            child: Text('$badge',
                style: const TextStyle(
                    fontSize: 9, fontWeight: FontWeight.w700,
                    color: Colors.white)),
          ),
        ],
      ]),
    );
  }
}

// ── Plan list ────────────────────────────────────────────────────────────────

class _PlanList extends StatelessWidget {
  const _PlanList({required this.plans});
  final List<CarePlan> plans;

  @override
  Widget build(BuildContext context) {
    if (plans.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(LucideIcons.clipboardCheck,
                size: 44, color: AppColors.neutral300),
            const SizedBox(height: 12),
            Text('None here', style: AppTextStyles.bodySm),
          ]),
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: () async {},
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: plans.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (ctx, i) => _PlanCard(plan: plans[i]),
      ),
    );
  }
}

// ── Plan card ────────────────────────────────────────────────────────────────

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan});
  final CarePlan plan;

  String _fmtDate(String? raw) {
    if (raw == null) return '';
    try {
      return DateFormat('d MMM yyyy').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }

  @override
  Widget build(BuildContext context) {
    final achieved = plan.goals.where((g) => g.status == 'achieved').length;
    final total    = plan.goals.length;
    final progress = total > 0 ? achieved / total : 0.0;
    final isActive = plan.isActive;

    return GestureDetector(
      onTap: () => context.push('${Routes.carePlans}/${plan.id}'),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.divider),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            // ── Header ──────────────────────────────────────────────────
            Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                  Text(plan.title,
                      style: AppTextStyles.body
                          .copyWith(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 3),
                  Text(
                    [
                      if (plan.providerName != null) plan.providerName!,
                      if (plan.startedAt != null)
                        'Started ${_fmtDate(plan.startedAt)}',
                    ].join(' · '),
                    style: AppTextStyles.bodySm,
                  ),
                ]),
              ),
              const SizedBox(width: 8),
              _StatusPill(isActive: isActive),
            ]),

            // ── Goal pills ───────────────────────────────────────────────
            if (plan.interventions.isNotEmpty) ...[
              const SizedBox(height: 10),
              Wrap(
                spacing: 6,
                runSpacing: 5,
                children: plan.interventions.take(3).map((iv) {
                  return _GoalPill(label: iv.description, type: iv.type);
                }).toList(),
              ),
            ],

            // ── Progress bar ─────────────────────────────────────────────
            if (total > 0) ...[
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Goals completed',
                      style: AppTextStyles.caption
                          .copyWith(color: AppColors.textMuted)),
                  Text('$achieved / $total',
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w700,
                        color: AppColors.primary500,
                      )),
                ],
              ),
              const SizedBox(height: 5),
              ClipRRect(
                borderRadius: BorderRadius.circular(3),
                child: LinearProgressIndicator(
                  value: progress,
                  backgroundColor: AppColors.neutral100,
                  color: isActive ? AppColors.primary500 : AppColors.success,
                  minHeight: 5,
                ),
              ),
            ],

            // ── Next check-in ────────────────────────────────────────────
            if (plan.nextCheckIn != null) ...[
              const Divider(height: 18, color: AppColors.divider),
              Row(children: [
                const Icon(LucideIcons.calendar,
                    size: 12, color: AppColors.neutral400),
                const SizedBox(width: 6),
                Text('Next check-in: ',
                    style: AppTextStyles.caption
                        .copyWith(color: AppColors.textMuted)),
                Text(_fmtDate(plan.nextCheckIn),
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w700,
                      color: AppColors.textPrimary,
                    )),
              ]),
            ],
          ],
        ),
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.isActive});
  final bool isActive;
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
      decoration: BoxDecoration(
        color: isActive ? AppColors.successLight : AppColors.neutral100,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        isActive ? 'Active' : 'Completed',
        style: AppTextStyles.caption.copyWith(
          fontSize: 10, fontWeight: FontWeight.w700,
          color: isActive ? AppColors.successDark : AppColors.textSecondary,
        ),
      ),
    );
  }
}

class _GoalPill extends StatelessWidget {
  const _GoalPill({required this.label, required this.type});
  final String label, type;

  static const _colors = {
    'medication': (Color(0xFFEFF6FF), Color(0xFF1E40AF)),
    'exercise':   (Color(0xFFF0FDF4), Color(0xFF065F46)),
    'diet':       (Color(0xFFFEF3C7), Color(0xFF92400E)),
    'monitoring': (Color(0xFFF5F3FF), Color(0xFF4C1D95)),
  };

  @override
  Widget build(BuildContext context) {
    final palette = _colors[type] ??
        (AppColors.primary50, const Color(0xFF1E40AF));
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: palette.$1,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label.length > 22 ? '${label.substring(0, 22)}…' : label,
        style: AppTextStyles.caption.copyWith(
          fontSize: 10, fontWeight: FontWeight.w600,
          color: palette.$2,
        ),
      ),
    );
  }
}
