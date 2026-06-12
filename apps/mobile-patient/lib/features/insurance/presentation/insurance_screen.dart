import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/insurance_plan_offer.dart';
import '../models/insurance_policy.dart';
import '../models/insurance_provider_offer.dart';
import '../providers/insurance_provider.dart';
import 'insurance_plan_detail_screen.dart';

class InsuranceScreen extends ConsumerWidget {
  const InsuranceScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final policiesAsync = ref.watch(insurancePoliciesProvider);
    final marketplaceAsync = ref.watch(insuranceMarketplaceProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Health Insurance', style: AppTextStyles.h4),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: Center(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                decoration: BoxDecoration(
                  color: AppColors.primary50,
                  borderRadius: BorderRadius.circular(999),
                  border: Border.all(color: AppColors.primary200),
                ),
                child: Text(
                  'Browse Plans',
                  style: AppTextStyles.bodySm.copyWith(
                    color: AppColors.primary500,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(insurancePoliciesProvider);
          ref.invalidate(insuranceMarketplaceProvider);
        },
        child: CustomScrollView(
          slivers: [
            // ── Padding top ────────────────────────────────────────────────
            const SliverToBoxAdapter(child: SizedBox(height: 16)),

            // ── Active Policy Banner ────────────────────────────────────────
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: policiesAsync.when(
                  loading: () => const LoadingSkeleton(height: 110, borderRadius: 12),
                  error: (_, __) => const SizedBox.shrink(),
                  data: (policies) {
                    final active = policies.where((p) => p.isActive).toList();
                    if (active.isEmpty) return const SizedBox.shrink();
                    return _ActivePolicyBanner(policy: active.first);
                  },
                ),
              ),
            ),

            // ── Available Plans header ──────────────────────────────────────
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 12),
                child: marketplaceAsync.when(
                  loading: () => const SizedBox.shrink(),
                  error: (_, __) => const SizedBox.shrink(),
                  data: (providers) {
                    if (providers.isEmpty) return const SizedBox.shrink();
                    final planCount = providers.fold<int>(
                        0, (sum, p) => sum + p.plans.length);
                    return Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Available Plans',
                            style: AppTextStyles.body
                                .copyWith(fontWeight: FontWeight.w700)),
                        Text('$planCount plans · ${providers.length} insurers',
                            style: AppTextStyles.bodySm
                                .copyWith(color: AppColors.textMuted)),
                      ],
                    );
                  },
                ),
              ),
            ),

            // ── Marketplace content ─────────────────────────────────────────
            marketplaceAsync.when(
              loading: () => SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                sliver: SliverList(
                  delegate: SliverChildListDelegate([
                    const LoadingSkeleton(height: 200, borderRadius: 12),
                    const SizedBox(height: 12),
                    const LoadingSkeleton(height: 200, borderRadius: 12),
                  ]),
                ),
              ),
              error: (e, _) => SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: ErrorView(
                    message: e.toString(),
                    onRetry: () => ref.invalidate(insuranceMarketplaceProvider),
                  ),
                ),
              ),
              data: (providers) {
                if (providers.isEmpty) {
                  return SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(LucideIcons.shieldOff,
                              size: 48, color: AppColors.neutral300),
                          const SizedBox(height: 12),
                          Text('No insurance plans available right now.',
                              style: AppTextStyles.bodySm,
                              textAlign: TextAlign.center),
                        ],
                      ),
                    ),
                  );
                }
                return SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) =>
                          _ProviderSection(provider: providers[index]),
                      childCount: providers.length,
                    ),
                  ),
                );
              },
            ),

            // ── Bottom padding ──────────────────────────────────────────────
            const SliverToBoxAdapter(child: SizedBox(height: 32)),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Active Policy Banner
// ─────────────────────────────────────────────────────────────────────────────

class _ActivePolicyBanner extends StatelessWidget {
  const _ActivePolicyBanner({required this.policy});
  final InsurancePolicy policy;

  String _fmt(String s) {
    try {
      return DateFormat('d MMM yyyy').format(DateTime.parse(s));
    } catch (_) {
      return s;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.primary50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.primary200, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(children: [
            const Icon(LucideIcons.shieldCheck,
                size: 18, color: AppColors.primary500),
            const SizedBox(width: 10),
            Text('Active Policy',
                style: AppTextStyles.bodySm.copyWith(
                  fontWeight: FontWeight.w700,
                  color: AppColors.primary500,
                )),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
              decoration: BoxDecoration(
                color: AppColors.successLight,
                borderRadius: BorderRadius.circular(999),
              ),
              child: Text('Active',
                  style: AppTextStyles.caption.copyWith(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: AppColors.successDark,
                  )),
            ),
          ]),
          const SizedBox(height: 10),
          Text(
            policy.planName ?? 'Insurance Plan',
            style: AppTextStyles.body
                .copyWith(fontWeight: FontWeight.w700, fontSize: 15),
          ),
          if (policy.providerName != null && policy.providerName!.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(policy.providerName!, style: AppTextStyles.bodySm),
          ],
          Divider(height: 20, color: AppColors.primary200),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('POLICY NO.',
                        style: AppTextStyles.caption.copyWith(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 0.06,
                        )),
                    const SizedBox(height: 2),
                    Text(policy.policyNumber,
                        style: AppTextStyles.monoSm
                            .copyWith(color: AppColors.textPrimary)),
                  ],
                ),
              ),
              if (policy.endDate != null && policy.endDate!.isNotEmpty)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('VALID UNTIL',
                        style: AppTextStyles.caption.copyWith(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 0.06,
                        )),
                    const SizedBox(height: 2),
                    Text(_fmt(policy.endDate!),
                        style: AppTextStyles.monoSm
                            .copyWith(color: AppColors.textPrimary)),
                  ],
                ),
            ],
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Provider Section
// ─────────────────────────────────────────────────────────────────────────────

class _ProviderSection extends StatelessWidget {
  const _ProviderSection({required this.provider});
  final InsuranceProviderOffer provider;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        // Provider header
        Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: const BoxDecoration(
            border: Border(top: BorderSide(color: AppColors.divider)),
          ),
          child: Row(children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: AppColors.primary50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: provider.logoUrl != null
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: Image.network(
                        provider.logoUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Icon(
                          LucideIcons.building2,
                          size: 18,
                          color: AppColors.primary500,
                        ),
                      ),
                    )
                  : const Icon(LucideIcons.building2,
                      size: 18, color: AppColors.primary500),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(provider.name,
                      style: AppTextStyles.body.copyWith(
                          fontWeight: FontWeight.w700, fontSize: 13)),
                  Text(
                      '${provider.plans.length} plan${provider.plans.length != 1 ? 's' : ''} available',
                      style: AppTextStyles.bodySm),
                ],
              ),
            ),
          ]),
        ),
        // Plan cards
        ...provider.plans.map((plan) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: _PlanCard(plan: plan, providerName: provider.name),
            )),
        const SizedBox(height: 8),
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Plan Card
// ─────────────────────────────────────────────────────────────────────────────

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan, required this.providerName});
  final InsurancePlanOffer plan;
  final String providerName;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.surface,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => InsurancePlanDetailScreen(
              planId: plan.id,
              planName: plan.name,
              providerName: providerName,
            ),
          ),
        ),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider, width: 1.5),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              // Name + badge
              Row(children: [
                Expanded(
                  child: Text(plan.name,
                      style: AppTextStyles.body.copyWith(
                          fontWeight: FontWeight.w700, fontSize: 15)),
                ),
                if (plan.planType != null) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.primary50,
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: Text(
                      plan.planType!.toUpperCase(),
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.primary600,
                        fontWeight: FontWeight.w700,
                        fontSize: 10,
                      ),
                    ),
                  ),
                ],
              ]),

              // Description
              if (plan.description != null &&
                  plan.description!.isNotEmpty) ...[
                const SizedBox(height: 6),
                Text(plan.description!,
                    style: AppTextStyles.bodySm,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis),
              ],

              // Pricing
              if (plan.monthlyPremium != null ||
                  plan.annualPremium != null) ...[
                const SizedBox(height: 10),
                _PriceRow(plan: plan),
              ],

              // Features
              const SizedBox(height: 10),
              Row(children: [
                if (plan.cashlessAvailable) ...[
                  Text('✓ Cashless',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.success,
                        fontWeight: FontWeight.w700,
                      )),
                  const SizedBox(width: 12),
                ],
                if (plan.copayPercentage != null)
                  Text(
                      '${plan.copayPercentage!.toStringAsFixed(0)}% co-pay',
                      style: AppTextStyles.caption),
              ]),

              // CTA
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  onPressed: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => InsurancePlanDetailScreen(
                        planId: plan.id,
                        planName: plan.name,
                        providerName: providerName,
                      ),
                    ),
                  ),
                  icon: const Icon(LucideIcons.shieldCheck, size: 15),
                  label: const Text('Enroll in this Plan'),
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.primary500,
                    foregroundColor: Colors.white,
                    minimumSize: const Size(double.infinity, 42),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                    elevation: 0,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Price Row — extracted to avoid Expanded-in-Column issues
// ─────────────────────────────────────────────────────────────────────────────

class _PriceRow extends StatelessWidget {
  const _PriceRow({required this.plan});
  final InsurancePlanOffer plan;

  static String _fmt(double v) =>
      'XAF ${v.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]},')}';

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      if (plan.monthlyPremium != null)
        Expanded(child: _PricePill(label: 'Monthly', value: _fmt(plan.monthlyPremium!), isPrimary: true)),
      if (plan.monthlyPremium != null && plan.annualPremium != null)
        const SizedBox(width: 8),
      if (plan.annualPremium != null)
        Expanded(child: _PricePill(label: 'Annual', value: _fmt(plan.annualPremium!), isPrimary: false)),
    ]);
  }
}

class _PricePill extends StatelessWidget {
  const _PricePill({
    required this.label,
    required this.value,
    required this.isPrimary,
  });

  final String label, value;
  final bool isPrimary;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: isPrimary ? AppColors.primary50 : AppColors.surfaceMuted,
        borderRadius: BorderRadius.circular(8),
        border: isPrimary ? null : Border.all(color: AppColors.divider),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(label,
              style: AppTextStyles.caption.copyWith(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: AppColors.textMuted,
              )),
          const SizedBox(height: 2),
          Text(value,
              style: AppTextStyles.monoSm.copyWith(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: isPrimary
                    ? AppColors.primary500
                    : AppColors.textSecondary,
              )),
        ],
      ),
    );
  }
}
