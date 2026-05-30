import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/health_id_card.dart';
import '../providers/health_id_provider.dart';

class HealthIdScreen extends ConsumerWidget {
  const HealthIdScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cardAsync = ref.watch(healthIdCardProvider);
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Health ID'),
        actions: [
          IconButton(
            icon: const Icon(LucideIcons.share2),
            onPressed: () {},
            tooltip: 'Share',
          ),
        ],
      ),
      body: cardAsync.when(
        loading: () => const _HealthIdSkeleton(),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(healthIdCardProvider),
        ),
        data: (card) {
          FirebaseAnalytics.instance.logEvent(name: 'view_health_id');
          return _HealthIdBody(card: card);
        },
      ),
    );
  }
}

class _HealthIdBody extends StatelessWidget {
  const _HealthIdBody({required this.card});
  final HealthIdCard card;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Gradient card
        Container(
          padding: const EdgeInsets.all(22),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: AppColors.primarySurface,
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                Expanded(
                  child: Text(
                    'OpesCare',
                    style: AppTextStyles.body.copyWith(
                      color: Colors.white70,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                const Icon(LucideIcons.heart,
                    color: Colors.white70, size: 20),
              ]),
              const SizedBox(height: 20),
              Text(
                'HEALTH ID',
                style: AppTextStyles.label.copyWith(
                  color: Colors.white54, fontSize: 10, letterSpacing: 1.5,
                ),
              ),
              const SizedBox(height: 6),
              Row(children: [
                Expanded(child: Text(card.healthId, style: AppTextStyles.healthId)),
                GestureDetector(
                  onTap: () {
                    Clipboard.setData(ClipboardData(text: card.healthId));
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Health ID copied')),
                    );
                  },
                  child: const Icon(LucideIcons.copy,
                      color: Colors.white60, size: 18),
                ),
              ]),
              const SizedBox(height: 16),
              Row(children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('FULL NAME',
                          style: AppTextStyles.label.copyWith(
                              color: Colors.white54, fontSize: 9)),
                      const SizedBox(height: 3),
                      Text(
                        card.displayName,
                        style: AppTextStyles.body.copyWith(
                            color: Colors.white,
                            fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('DATE OF BIRTH',
                        style: AppTextStyles.label.copyWith(
                            color: Colors.white54, fontSize: 9)),
                    const SizedBox(height: 3),
                    Text(
                      card.dateOfBirth,
                      style: AppTextStyles.body.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ]),
              const SizedBox(height: 12),
              if (card.isVerified)
                Row(children: [
                  const Icon(LucideIcons.checkCircle,
                      size: 14, color: Colors.white70),
                  const SizedBox(width: 6),
                  Text('Verified Identity',
                      style: AppTextStyles.caption
                          .copyWith(color: Colors.white70)),
                ]),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Clinical Summary
        _InfoSection(
          title: 'Clinical Summary',
          icon: LucideIcons.activity,
          rows: [
            _InfoRow('Sex', card.sex.toUpperCase()),
            _InfoRow('Blood Group', card.bloodGroup),
            if (card.allergySummary != null)
              _InfoRow(
                'Allergies', card.allergySummary!,
                valueColor: AppColors.danger,
                icon: LucideIcons.alertTriangle,
              ),
          ],
        ),
        const SizedBox(height: 16),

        // Emergency contact
        if (card.emergencyContact != null) ...[
          _InfoSection(
            title: 'Emergency Contact',
            icon: LucideIcons.phone,
            rows: [_InfoRow('Contact', card.emergencyContact!)],
          ),
          const SizedBox(height: 16),
        ],

        OutlinedButton.icon(
          onPressed: () {
            Clipboard.setData(ClipboardData(text: card.healthId));
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                  content: Text('Health ID copied to clipboard')),
            );
          },
          icon: const Icon(LucideIcons.copy, size: 16),
          label: const Text('Copy Health ID'),
        ),
        const SizedBox(height: 32),
      ],
    );
  }
}

class _InfoSection extends StatelessWidget {
  const _InfoSection({
    required this.title,
    required this.icon,
    required this.rows,
  });

  final String title;
  final IconData icon;
  final List<_InfoRow> rows;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(children: [
        Padding(
          padding: const EdgeInsets.all(14),
          child: Row(children: [
            Icon(icon, size: 16, color: AppColors.primary500),
            const SizedBox(width: 8),
            Text(title, style: AppTextStyles.h4),
          ]),
        ),
        const Divider(height: 1),
        ...rows.map((r) => Column(children: [
              Padding(
                padding: const EdgeInsets.symmetric(
                    horizontal: 14, vertical: 12),
                child: Row(children: [
                  Expanded(child: Text(r.label, style: AppTextStyles.bodySm)),
                  if (r.icon != null)
                    Padding(
                      padding: const EdgeInsets.only(right: 6),
                      child: Icon(r.icon, size: 14, color: r.valueColor),
                    ),
                  Text(
                    r.value,
                    style: AppTextStyles.body.copyWith(
                      fontWeight: FontWeight.w600,
                      color: r.valueColor ?? AppColors.textPrimary,
                    ),
                  ),
                ]),
              ),
              if (r != rows.last) const Divider(height: 1, indent: 14),
            ])),
      ]),
    );
  }
}

class _InfoRow {
  const _InfoRow(this.label, this.value, {this.valueColor, this.icon});
  final String label, value;
  final Color? valueColor;
  final IconData? icon;
}

class _HealthIdSkeleton extends StatelessWidget {
  const _HealthIdSkeleton();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: const [
        LoadingSkeleton(height: 180, borderRadius: 20),
        SizedBox(height: 24),
        LoadingSkeleton(height: 160, borderRadius: 12),
      ],
    );
  }
}
