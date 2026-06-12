import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/insurance_plan_offer.dart';
import '../providers/insurance_provider.dart';

class InsurancePlanDetailScreen extends ConsumerWidget {
  const InsurancePlanDetailScreen({
    super.key,
    required this.planId,
    required this.planName,
    required this.providerName,
  });

  final String planId;
  final String planName;
  final String providerName;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final planAsync = ref.watch(insurancePlanDetailProvider(planId));

    return Scaffold(
      appBar: AppBar(
        title: Text(planName, overflow: TextOverflow.ellipsis),
        centerTitle: false,
      ),
      body: planAsync.when(
        loading: () => const Padding(
          padding: EdgeInsets.all(16),
          child: Column(children: [
            LoadingSkeleton(height: 180, borderRadius: 12),
            SizedBox(height: 12),
            LoadingSkeleton(height: 120, borderRadius: 12),
            SizedBox(height: 12),
            LoadingSkeleton(height: 80, borderRadius: 12),
          ]),
        ),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(insurancePlanDetailProvider(planId)),
        ),
        data: (plan) => _PlanDetailBody(
          plan: plan,
          providerName: providerName,
        ),
      ),
    );
  }
}

// ─── Stateful body ───────────────────────────────────────────────────────────

class _PlanDetailBody extends ConsumerStatefulWidget {
  const _PlanDetailBody({required this.plan, required this.providerName});
  final InsurancePlanOffer plan;
  final String providerName;

  @override
  ConsumerState<_PlanDetailBody> createState() => _PlanDetailBodyState();
}

class _PlanDetailBodyState extends ConsumerState<_PlanDetailBody> {
  String _selectedPayment = 'mobile_money';
  bool _purchasing = false;

  Future<void> _confirmPurchase() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => _PurchaseConfirmDialog(
        planName: widget.plan.name,
        providerName: widget.providerName,
        monthlyPremium: widget.plan.monthlyPremium,
        paymentMethod: _selectedPayment,
      ),
    );
    if (confirmed != true || !mounted) return;

    setState(() => _purchasing = true);
    try {
      final result = await ref.read(insuranceRepositoryProvider).purchasePlan(
        widget.plan.id,
        paymentMethod: _selectedPayment,
      );
      if (!mounted) return;
      _showSuccess(result['policy_number']?.toString() ?? '');
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString()),
          backgroundColor: AppColors.danger,
        ),
      );
    } finally {
      if (mounted) setState(() => _purchasing = false);
    }
  }

  void _showSuccess(String policyNumber) {
    showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          const SizedBox(height: 8),
          Container(
            width: 64,
            height: 64,
            decoration: const BoxDecoration(
              color: AppColors.primary50,
              shape: BoxShape.circle,
            ),
            child: const Icon(LucideIcons.shieldCheck,
                size: 32, color: AppColors.primary500),
          ),
          const SizedBox(height: 16),
          Text('Enrollment Submitted!',
              style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text(
            'Your policy is pending activation. You will be notified once confirmed.',
            style: AppTextStyles.bodySm
                .copyWith(color: AppColors.textSecondary),
            textAlign: TextAlign.center,
          ),
          if (policyNumber.isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: AppColors.neutral100,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                'Policy No: $policyNumber',
                style: AppTextStyles.bodySm
                    .copyWith(fontWeight: FontWeight.w600),
              ),
            ),
          ],
          const SizedBox(height: 20),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                // Pop success dialog, plan detail, and marketplace
                Navigator.of(context).pop();
                Navigator.of(context).pop();
                Navigator.of(context).pop();
              },
              child: const Text('Done'),
            ),
          ),
        ]),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final plan = widget.plan;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

        // ── Header banner ─────────────────────────────────────────────────
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.primary500,
            borderRadius: BorderRadius.circular(14),
          ),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(widget.providerName,
                style: AppTextStyles.bodySm
                    .copyWith(color: AppColors.onPrimarySubtle)),
            const SizedBox(height: 4),
            Text(plan.name,
                style: AppTextStyles.body.copyWith(
                    color: Colors.white, fontWeight: FontWeight.w700)),
            if (plan.planType != null) ...[
              const SizedBox(height: 8),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppColors.whiteOverlay,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  plan.planType!.toUpperCase(),
                  style: AppTextStyles.caption.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 10,
                  ),
                ),
              ),
            ],
          ]),
        ),

        const SizedBox(height: 16),

        // ── Pricing ───────────────────────────────────────────────────────
        _SectionCard(
          title: 'Pricing',
          child: Column(children: [
            if (plan.monthlyPremium != null)
              _DetailRow(
                icon: LucideIcons.calendarDays,
                label: 'Monthly Premium',
                value: 'XAF ${plan.monthlyPremium!.toStringAsFixed(0)}',
                highlight: true,
              ),
            if (plan.annualPremium != null) ...[
              const SizedBox(height: 8),
              _DetailRow(
                icon: LucideIcons.calendar,
                label: 'Annual Premium',
                value: 'XAF ${plan.annualPremium!.toStringAsFixed(0)}',
              ),
            ],
            if (plan.deductible != null) ...[
              const SizedBox(height: 8),
              _DetailRow(
                icon: LucideIcons.receipt,
                label: 'Deductible',
                value: 'XAF ${plan.deductible!.toStringAsFixed(0)}',
              ),
            ],
            if (plan.copayPercentage != null) ...[
              const SizedBox(height: 8),
              _DetailRow(
                icon: LucideIcons.percent,
                label: 'Co-pay',
                value: '${plan.copayPercentage!.toStringAsFixed(0)}%',
              ),
            ],
          ]),
        ),

        const SizedBox(height: 12),

        // ── Benefits ──────────────────────────────────────────────────────
        _SectionCard(
          title: 'Benefits',
          child: Column(children: [
            _BenefitRow(
              icon: LucideIcons.creditCard,
              label: 'Cashless Treatment',
              enabled: plan.cashlessAvailable,
            ),
            const SizedBox(height: 8),
            _BenefitRow(
              icon: LucideIcons.clipboardCheck,
              label: 'Requires Pre-authorization',
              enabled: plan.requiresPreauthorization,
              showAsWarning: true,
            ),
          ]),
        ),

        if (plan.description != null && plan.description!.isNotEmpty) ...[
          const SizedBox(height: 12),
          _SectionCard(
            title: 'About this Plan',
            child: Text(
              plan.description!,
              style: AppTextStyles.bodySm
                  .copyWith(color: AppColors.textSecondary),
            ),
          ),
        ],

        if (plan.provider?.contactPhone != null ||
            plan.provider?.contactEmail != null) ...[
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Provider Contact',
            child: Column(children: [
              if (plan.provider?.contactPhone != null)
                _DetailRow(
                  icon: LucideIcons.phone,
                  label: 'Phone',
                  value: plan.provider!.contactPhone!,
                ),
              if (plan.provider?.contactEmail != null) ...[
                const SizedBox(height: 8),
                _DetailRow(
                  icon: LucideIcons.mail,
                  label: 'Email',
                  value: plan.provider!.contactEmail!,
                ),
              ],
            ]),
          ),
        ],

        const SizedBox(height: 16),

        // ── Payment method ────────────────────────────────────────────────
        _SectionCard(
          title: 'Payment Method',
          child: Column(children: [
            _PaymentOption(
              value: 'mobile_money',
              label: 'Mobile Money',
              icon: LucideIcons.smartphone,
              groupValue: _selectedPayment,
              onChanged: (v) => setState(() => _selectedPayment = v!),
            ),
            _PaymentOption(
              value: 'card',
              label: 'Debit / Credit Card',
              icon: LucideIcons.creditCard,
              groupValue: _selectedPayment,
              onChanged: (v) => setState(() => _selectedPayment = v!),
            ),
            _PaymentOption(
              value: 'bank_transfer',
              label: 'Bank Transfer',
              icon: LucideIcons.landmark,
              groupValue: _selectedPayment,
              onChanged: (v) => setState(() => _selectedPayment = v!),
            ),
          ]),
        ),

        const SizedBox(height: 24),

        // ── Enroll button ─────────────────────────────────────────────────
        SizedBox(
          width: double.infinity,
          height: 50,
          child: ElevatedButton.icon(
            onPressed: _purchasing ? null : _confirmPurchase,
            icon: _purchasing
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: Colors.white),
                  )
                : const Icon(LucideIcons.shieldCheck),
            label: Text(_purchasing ? 'Processing...' : 'Enroll in this Plan'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary500,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ),

        const SizedBox(height: 8),
        Text(
          'Your enrollment will be reviewed and activated within 1–2 business days.',
          style: AppTextStyles.caption
              .copyWith(color: AppColors.textSecondary),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 24),
      ]),
    );
  }
}

// ─── Reusable widgets ─────────────────────────────────────────────────────────

class _SectionCard extends StatelessWidget {
  const _SectionCard({required this.title, required this.child});
  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(title,
            style: AppTextStyles.bodySm.copyWith(
              fontWeight: FontWeight.w700,
              color: AppColors.textSecondary,
            )),
        const SizedBox(height: 10),
        child,
      ]),
    );
  }
}

class _DetailRow extends StatelessWidget {
  const _DetailRow({
    required this.icon,
    required this.label,
    required this.value,
    this.highlight = false,
  });

  final IconData icon;
  final String label;
  final String value;
  final bool highlight;

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Icon(icon, size: 14, color: AppColors.neutral400),
      const SizedBox(width: 8),
      Expanded(
          child: Text(label,
              style: AppTextStyles.bodySm
                  .copyWith(color: AppColors.textSecondary))),
      Text(
        value,
        style: AppTextStyles.monoSm.copyWith(
          fontWeight: FontWeight.w700,
          color: highlight ? AppColors.primary600 : AppColors.textPrimary,
        ),
      ),
    ]);
  }
}

class _BenefitRow extends StatelessWidget {
  const _BenefitRow({
    required this.icon,
    required this.label,
    required this.enabled,
    this.showAsWarning = false,
  });

  final IconData icon;
  final String label;
  final bool enabled;
  final bool showAsWarning;

  @override
  Widget build(BuildContext context) {
    Color statusColor;
    IconData statusIcon;

    if (enabled) {
      statusColor = showAsWarning ? AppColors.warning : AppColors.success;
      statusIcon = LucideIcons.checkCircle2;
    } else {
      statusColor = AppColors.neutral300;
      statusIcon = LucideIcons.xCircle;
    }

    return Row(children: [
      Icon(icon, size: 14, color: AppColors.neutral400),
      const SizedBox(width: 8),
      Expanded(child: Text(label, style: AppTextStyles.bodySm)),
      Icon(statusIcon, size: 16, color: statusColor),
    ]);
  }
}

class _PaymentOption extends StatelessWidget {
  const _PaymentOption({
    required this.value,
    required this.label,
    required this.icon,
    required this.groupValue,
    required this.onChanged,
  });

  final String value;
  final String label;
  final IconData icon;
  final String groupValue;
  final ValueChanged<String?> onChanged;

  @override
  Widget build(BuildContext context) {
    final selected = value == groupValue;
    return GestureDetector(
      onTap: () => onChanged(value),
      child: Container(
        margin: const EdgeInsets.only(bottom: 6),
        padding:
            const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: selected ? AppColors.primary50 : AppColors.surface,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: selected ? AppColors.primary300 : AppColors.divider,
          ),
        ),
        child: Row(children: [
          Icon(icon,
              size: 16,
              color: selected
                  ? AppColors.primary500
                  : AppColors.neutral400),
          const SizedBox(width: 10),
          Expanded(child: Text(label, style: AppTextStyles.bodySm)),
          // Custom radio dot — avoids deprecated Radio.groupValue / onChanged API
          Container(
            width: 20,
            height: 20,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(
                color: selected ? AppColors.primary500 : AppColors.neutral300,
                width: 2,
              ),
            ),
            child: selected
                ? Center(
                    child: Container(
                      width: 10,
                      height: 10,
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        color: AppColors.primary500,
                      ),
                    ),
                  )
                : null,
          ),
        ]),
      ),
    );
  }
}

// ─── Purchase confirmation dialog ─────────────────────────────────────────────

class _PurchaseConfirmDialog extends StatelessWidget {
  const _PurchaseConfirmDialog({
    required this.planName,
    required this.providerName,
    required this.monthlyPremium,
    required this.paymentMethod,
  });

  final String planName;
  final String providerName;
  final double? monthlyPremium;
  final String paymentMethod;

  String get _paymentLabel => switch (paymentMethod) {
        'mobile_money' => 'Mobile Money',
        'card' => 'Debit / Credit Card',
        _ => 'Bank Transfer',
      };

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape:
          RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: const Text('Confirm Enrollment'),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        _ConfirmRow(label: 'Plan', value: planName),
        const SizedBox(height: 8),
        _ConfirmRow(label: 'Provider', value: providerName),
        if (monthlyPremium != null) ...[
          const SizedBox(height: 8),
          _ConfirmRow(
            label: 'Monthly',
            value: 'XAF ${monthlyPremium!.toStringAsFixed(0)}',
          ),
        ],
        const SizedBox(height: 8),
        _ConfirmRow(label: 'Payment', value: _paymentLabel),
        const SizedBox(height: 16),
        Text(
          'By confirming, you agree to enroll in this plan. Your policy activates after verification.',
          style: AppTextStyles.caption
              .copyWith(color: AppColors.textSecondary),
          textAlign: TextAlign.center,
        ),
      ]),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context, false),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () => Navigator.pop(context, true),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary500,
            foregroundColor: Colors.white,
          ),
          child: const Text('Confirm'),
        ),
      ],
    );
  }
}

class _ConfirmRow extends StatelessWidget {
  const _ConfirmRow({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Text('$label: ',
          style: AppTextStyles.bodySm
              .copyWith(color: AppColors.textSecondary)),
      Expanded(
          child: Text(value,
              style: AppTextStyles.bodySm
                  .copyWith(fontWeight: FontWeight.w600))),
    ]);
  }
}
