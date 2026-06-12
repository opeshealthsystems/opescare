import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/insurance_plan_offer.dart';
import '../models/insurance_provider_offer.dart';
import '../providers/insurance_provider.dart';
import 'insurance_plan_detail_screen.dart';

class InsuranceMarketplaceScreen extends ConsumerWidget {
  const InsuranceMarketplaceScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final marketplaceAsync = ref.watch(insuranceMarketplaceProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Browse Insurance Plans'),
        centerTitle: false,
      ),
      body: marketplaceAsync.when(
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            LoadingSkeleton(height: 200, borderRadius: 12),
            SizedBox(height: 12),
            LoadingSkeleton(height: 200, borderRadius: 12),
          ],
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(insuranceMarketplaceProvider),
        ),
        data: (providers) {
          if (providers.isEmpty) {
            return Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(LucideIcons.shieldOff,
                    size: 48, color: AppColors.neutral300),
                const SizedBox(height: 12),
                Text(
                  'No insurance plans available right now.',
                  style: AppTextStyles.bodySm,
                  textAlign: TextAlign.center,
                ),
              ]),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(insuranceMarketplaceProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: providers.length,
              separatorBuilder: (_, __) => const SizedBox(height: 16),
              itemBuilder: (_, i) => _ProviderSection(provider: providers[i]),
            ),
          );
        },
      ),
    );
  }
}

class _ProviderSection extends StatelessWidget {
  const _ProviderSection({required this.provider});
  final InsuranceProviderOffer provider;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _ProviderHeader(provider: provider),
        const SizedBox(height: 8),
        ...provider.plans.map(
          (plan) => Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: _PlanCard(plan: plan, providerName: provider.name),
          ),
        ),
      ],
    );
  }
}

class _ProviderHeader extends StatelessWidget {
  const _ProviderHeader({required this.provider});
  final InsuranceProviderOffer provider;

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Container(
        width: 40,
        height: 40,
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
                    size: 20,
                    color: AppColors.primary500,
                  ),
                ),
              )
            : const Icon(LucideIcons.building2,
                size: 20, color: AppColors.primary500),
      ),
      const SizedBox(width: 10),
      Expanded(
        child: Text(
          provider.name,
          style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w700),
        ),
      ),
    ]);
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan, required this.providerName});
  final InsurancePlanOffer plan;
  final String providerName;

  @override
  Widget build(BuildContext context) {
    return InkWell(
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
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(
              child: Text(
                plan.name,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
              ),
            ),
            if (plan.planType != null)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppColors.primary50,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  plan.planType!.toUpperCase(),
                  style: AppTextStyles.caption.copyWith(
                    color: AppColors.primary600,
                    fontWeight: FontWeight.w600,
                    fontSize: 10,
                  ),
                ),
              ),
          ]),
          if (plan.description != null && plan.description!.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              plan.description!,
              style: AppTextStyles.bodySm.copyWith(color: AppColors.textSecondary),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
          const SizedBox(height: 10),
          Row(children: [
            if (plan.monthlyPremium != null)
              Expanded(
                child: _PricePill(
                  label: '/month',
                  amount: plan.monthlyPremium!,
                ),
              ),
            if (plan.monthlyPremium != null && plan.annualPremium != null)
              const SizedBox(width: 8),
            if (plan.annualPremium != null)
              Expanded(
                child: _PricePill(
                  label: '/year',
                  amount: plan.annualPremium!,
                  muted: true,
                ),
              ),
          ]),
          const SizedBox(height: 10),
          Row(children: [
            if (plan.cashlessAvailable) ...[
              const Icon(LucideIcons.check, size: 13, color: AppColors.success),
              const SizedBox(width: 4),
              Text('Cashless', style: AppTextStyles.caption),
              const SizedBox(width: 12),
            ],
            if (plan.copayPercentage != null) ...[
              const Icon(LucideIcons.percent, size: 13, color: AppColors.neutral400),
              const SizedBox(width: 4),
              Text('${plan.copayPercentage!.toStringAsFixed(0)}% co-pay',
                  style: AppTextStyles.caption),
            ],
            const Spacer(),
            const Icon(LucideIcons.chevronRight,
                size: 16, color: AppColors.neutral400),
          ]),
        ]),
      ),
    );
  }
}

class _PricePill extends StatelessWidget {
  const _PricePill({required this.label, required this.amount, this.muted = false});
  final String label;
  final double amount;
  final bool muted;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: muted ? AppColors.neutral100 : AppColors.primary50,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Text(
          'XAF ${amount.toStringAsFixed(0)}',
          style: AppTextStyles.bodySm.copyWith(
            fontWeight: FontWeight.w700,
            color: muted ? AppColors.textSecondary : AppColors.primary700,
          ),
        ),
        Text(
          label,
          style: AppTextStyles.caption.copyWith(
            color: muted ? AppColors.neutral400 : AppColors.primary500,
          ),
        ),
      ]),
    );
  }
}
