import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/status_badge.dart';
import '../models/consent_request.dart';
import '../providers/consent_provider.dart';

class ConsentScreen extends ConsumerStatefulWidget {
  const ConsentScreen({super.key});

  @override
  ConsumerState<ConsentScreen> createState() => _ConsentScreenState();
}

class _ConsentScreenState extends ConsumerState<ConsentScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Consent Requests'),
        bottom: TabBar(
          controller: _tab,
          labelStyle: AppTextStyles.button,
          tabs: const [
            Tab(text: 'Pending'),
            Tab(text: 'History'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: const [
          _ConsentList(status: 'pending'),
          _ConsentList(status: null),
        ],
      ),
    );
  }
}

class _ConsentList extends ConsumerWidget {
  const _ConsentList({required this.status});
  final String? status;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final requestsAsync = ref.watch(consentRequestsProvider(status));
    return requestsAsync.when(
      loading: () => ListView(
        padding: const EdgeInsets.all(16),
        children: const [
          LoadingSkeleton(height: 130, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 130, borderRadius: 12),
        ],
      ),
      error: (e, _) => ErrorView(
        message: e.toString(),
        onRetry: () => ref.invalidate(consentRequestsProvider(status)),
      ),
      data: (items) {
        if (items.isEmpty) {
          return Center(
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.shieldCheck,
                  size: 48, color: AppColors.neutral300),
              const SizedBox(height: 12),
              Text(
                status == 'pending'
                    ? 'No pending consent requests.'
                    : 'No consent history yet.',
                style: AppTextStyles.bodySm,
              ),
            ]),
          );
        }
        return RefreshIndicator(
          onRefresh: () async =>
              ref.invalidate(consentRequestsProvider(status)),
          child: ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            separatorBuilder: (_, __) => const SizedBox(height: 10),
            itemBuilder: (_, i) => _ConsentCard(request: items[i]),
          ),
        );
      },
    );
  }
}

class _ConsentCard extends ConsumerWidget {
  const _ConsentCard({required this.request});
  final ConsentRequest request;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final actionState = ref.watch(consentActionProvider);
    final isLoading = actionState is AsyncLoading;
    final isPending = request.status == ConsentStatus.pending;

    String formattedExpiry = request.expiresAt;
    try {
      formattedExpiry = DateFormat('d MMM yyyy')
          .format(DateTime.parse(request.expiresAt));
    } catch (_) {}

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isPending
              ? AppColors.warningBorder
              : AppColors.divider,
        ),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                Expanded(
                  child: Text(
                    request.requestingFacility,
                    style: AppTextStyles.body
                        .copyWith(fontWeight: FontWeight.w600),
                  ),
                ),
                StatusBadge(_statusToBadge(request.status), small: true),
              ]),
              const SizedBox(height: 4),
              Text(request.requestingRole, style: AppTextStyles.bodySm),
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.neutral50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('PURPOSE', style: AppTextStyles.label),
                    const SizedBox(height: 4),
                    Text(request.purpose, style: AppTextStyles.body),
                  ],
                ),
              ),
              const SizedBox(height: 10),
              Text('WHAT THEY WANT TO ACCESS', style: AppTextStyles.label),
              const SizedBox(height: 6),
              Wrap(
                spacing: 6, runSpacing: 6,
                children: request.scopeLabels
                    .map((s) => Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.infoLight,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            s,
                            style: AppTextStyles.caption
                                .copyWith(color: AppColors.infoDark),
                          ),
                        ))
                    .toList(),
              ),
              const SizedBox(height: 8),
              Row(children: [
                const Icon(LucideIcons.clock,
                    size: 13, color: AppColors.neutral400),
                const SizedBox(width: 4),
                Text('Expires $formattedExpiry',
                    style: AppTextStyles.caption),
              ]),
            ],
          ),
        ),
        if (isPending) ...[
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.symmetric(
                horizontal: 14, vertical: 10),
            child: Row(children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: isLoading
                      ? null
                      : () async {
                          final confirmed = await _confirmDeny(context);
                          if (confirmed && context.mounted) {
                            await ref
                                .read(consentActionProvider.notifier)
                                .deny(request.id, ref);
                            try { FirebaseAnalytics.instance.logEvent(name: 'consent_denied'); } catch (_) {}
                          }
                        },
                  icon: const Icon(LucideIcons.x, size: 16),
                  label: const Text('Deny'),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size(0, 44),
                    foregroundColor: AppColors.danger,
                    side: const BorderSide(color: AppColors.danger),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: isLoading
                      ? null
                      : () async {
                          await ref
                              .read(consentActionProvider.notifier)
                              .approve(request.id, ref);
                          try { FirebaseAnalytics.instance.logEvent(name: 'consent_approved'); } catch (_) {}
                        },
                  icon: const Icon(LucideIcons.check, size: 16),
                  label: const Text('Approve'),
                  style: ElevatedButton.styleFrom(
                      minimumSize: const Size(0, 44)),
                ),
              ),
            ]),
          ),
        ],
      ]),
    );
  }

  Future<bool> _confirmDeny(BuildContext context) async {
    return await showDialog<bool>(
          context: context,
          builder: (_) => AlertDialog(
            title: const Text('Deny access?'),
            content: const Text(
                'The facility will not be able to view your health records.'),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Cancel'),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.danger,
                  minimumSize: const Size(80, 40),
                ),
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Deny'),
              ),
            ],
          ),
        ) ??
        false;
  }

  BadgeStatus _statusToBadge(ConsentStatus s) => switch (s) {
        ConsentStatus.pending  => BadgeStatus.pending,
        ConsentStatus.approved => BadgeStatus.verified,
        ConsentStatus.denied   => BadgeStatus.cancelled,
        ConsentStatus.revoked  => BadgeStatus.revoked,
        ConsentStatus.expired  => BadgeStatus.cancelled,
      };
}
