import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../access_logs/providers/access_logs_provider.dart';
import '../models/health_id_card.dart';
import '../providers/health_id_provider.dart';

class HealthIdScreen extends ConsumerWidget {
  const HealthIdScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cardAsync = ref.watch(healthIdCardProvider);
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('My Health ID', style: AppTextStyles.h4),
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 4),
            child: Row(children: [
              _AppBarIcon(
                icon: LucideIcons.share2,
                onTap: () {},
              ),
              const SizedBox(width: 6),
              _AppBarIcon(
                icon: LucideIcons.moreVertical,
                onTap: () {},
              ),
            ]),
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
          try {
            FirebaseAnalytics.instance.logEvent(name: 'view_health_id');
          } catch (_) {}
          return _HealthIdBody(card: card);
        },
      ),
    );
  }
}

class _AppBarIcon extends StatelessWidget {
  const _AppBarIcon({required this.icon, required this.onTap});
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 36, height: 36,
        decoration: BoxDecoration(
          color: AppColors.primary50,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, size: 16, color: AppColors.primary500),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Body
// ─────────────────────────────────────────────────────────────────────────────

class _HealthIdBody extends ConsumerWidget {
  const _HealthIdBody({required this.card});
  final HealthIdCard card;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final logsAsync = ref.watch(accessLogsProvider);

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // ── Primary Health ID Card ─────────────────────────────────────────
        _HealthIdCard(card: card),
        const SizedBox(height: 14),

        // ── Generate Temporary QR ──────────────────────────────────────────
        _GenerateQrButton(card: card),
        const SizedBox(height: 12),

        // ── Quick Actions (4 items) ────────────────────────────────────────
        Row(children: [
          _QuickBtn(LucideIcons.flaskConical, 'Lab Results',
              AppColors.primary500, AppColors.primary50,
              () => context.push(Routes.labs)),
          const SizedBox(width: 10),
          _QuickBtn(LucideIcons.calendarCheck, 'Appointments',
              AppColors.success, AppColors.successLight,
              () => context.push(Routes.appointments)),
          const SizedBox(width: 10),
          _QuickBtn(LucideIcons.pill, 'Prescriptions',
              AppColors.warning, AppColors.warningLight,
              () => context.push(Routes.prescriptions)),
          const SizedBox(width: 10),
          _QuickBtn(LucideIcons.shieldCheck, 'Consent',
              AppColors.danger, AppColors.dangerLight,
              () => context.push(Routes.consent)),
        ]),
        const SizedBox(height: 20),

        // ── Recent Access Logs ─────────────────────────────────────────────
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Recent Access Logs',
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w700)),
            GestureDetector(
              onTap: () => context.push(Routes.accessLogs),
              child: Text('See all',
                  style: AppTextStyles.bodySm.copyWith(
                    color: AppColors.primary500,
                    fontWeight: FontWeight.w600,
                  )),
            ),
          ],
        ),
        const SizedBox(height: 10),
        logsAsync.when(
          loading: () => const LoadingSkeleton(height: 100, borderRadius: 12),
          error: (_, __) => const SizedBox.shrink(),
          data: (logs) {
            final recent = logs.take(2).toList();
            if (recent.isEmpty) {
              return Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Text('No recent access logs.',
                    style: AppTextStyles.bodySm,
                    textAlign: TextAlign.center),
              );
            }
            return Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              child: Column(
                children: recent.asMap().entries.map((entry) {
                  final i = entry.key;
                  final log = entry.value;
                  final isLast = i == recent.length - 1;
                  return Column(children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 13),
                      child: Row(children: [
                        Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(
                            color: log.isEmergency
                                ? AppColors.dangerLight
                                : AppColors.primary50,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            log.isEmergency
                                ? LucideIcons.alertTriangle
                                : LucideIcons.building2,
                            size: 18,
                            color: log.isEmergency
                                ? AppColors.danger
                                : AppColors.primary500,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                            Text(log.facilityName,
                                style: AppTextStyles.body
                                    .copyWith(fontWeight: FontWeight.w600)),
                            const SizedBox(height: 2),
                            Text(log.purpose,
                                style: AppTextStyles.bodySm,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis),
                          ]),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 9, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.successLight,
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text('Allowed',
                              style: AppTextStyles.caption.copyWith(
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                                color: AppColors.successDark,
                              )),
                        ),
                      ]),
                    ),
                    if (!isLast)
                      const Divider(
                          height: 1, color: AppColors.divider, indent: 66),
                  ]);
                }).toList(),
              ),
            );
          },
        ),
        const SizedBox(height: 32),
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Primary Health ID Card — redesigned with QR side by side
// ─────────────────────────────────────────────────────────────────────────────

class _HealthIdCard extends StatelessWidget {
  const _HealthIdCard({required this.card});
  final HealthIdCard card;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary500.withValues(alpha: 0.35),
            blurRadius: 24,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Background decorative circles
          Positioned(
            top: -50, right: -50,
            child: Container(
              width: 180, height: 180,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.05),
              ),
            ),
          ),
          Positioned(
            bottom: -60, left: -30,
            child: Container(
              width: 160, height: 160,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.04),
              ),
            ),
          ),

          // Card content
          Padding(
            padding: const EdgeInsets.all(22),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ── Brand row ──────────────────────────────────────────────
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(children: [
                      Container(
                        width: 30, height: 30,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.18),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(LucideIcons.heart,
                            size: 16, color: Colors.white),
                      ),
                      const SizedBox(width: 8),
                      Text('OpesCare',
                          style: AppTextStyles.body.copyWith(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                          )),
                    ]),
                    if (card.isVerified)
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981).withValues(alpha: 0.2),
                          border: Border.all(
                            color: const Color(0xFF10B981).withValues(alpha: 0.4),
                          ),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(mainAxisSize: MainAxisSize.min, children: [
                          Container(
                            width: 6, height: 6,
                            decoration: const BoxDecoration(
                              shape: BoxShape.circle,
                              color: Color(0xFF34D399),
                            ),
                          ),
                          const SizedBox(width: 5),
                          Text('VERIFIED',
                              style: AppTextStyles.monoXs.copyWith(
                                color: const Color(0xFF6EE7B7),
                                fontSize: 10,
                              )),
                        ]),
                      ),
                  ],
                ),

                const SizedBox(height: 18),

                // ── Main content: info + QR side by side ──────────────────
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Left: patient info
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('FULL NAME',
                              style: AppTextStyles.monoXs.copyWith(
                                  color: Colors.white54)),
                          const SizedBox(height: 3),
                          Text(
                            card.displayName,
                            style: AppTextStyles.h3.copyWith(
                              color: Colors.white,
                              fontWeight: FontWeight.w800,
                              letterSpacing: -0.3,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 14),

                          Text('HEALTH ID',
                              style: AppTextStyles.monoXs.copyWith(
                                  color: Colors.white54)),
                          const SizedBox(height: 3),
                          GestureDetector(
                            onTap: () {
                              Clipboard.setData(
                                  ClipboardData(text: card.healthId));
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                    content: Text('Health ID copied')),
                              );
                            },
                            child: Row(children: [
                              Flexible(
                                child: Text(card.healthId,
                                    style: AppTextStyles.healthId
                                        .copyWith(fontSize: 15)),
                              ),
                              const SizedBox(width: 6),
                              const Icon(LucideIcons.copy,
                                  size: 14, color: Colors.white54),
                            ]),
                          ),
                          const SizedBox(height: 14),

                          // DOB + Blood Group
                          Row(children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('DATE OF BIRTH',
                                      style: AppTextStyles.monoXs.copyWith(
                                          color: Colors.white54)),
                                  const SizedBox(height: 3),
                                  Text(card.dateOfBirth,
                                      style: AppTextStyles.body.copyWith(
                                        color: Colors.white,
                                        fontWeight: FontWeight.w700,
                                        fontSize: 13,
                                      )),
                                ],
                              ),
                            ),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('BLOOD GROUP',
                                      style: AppTextStyles.monoXs.copyWith(
                                          color: Colors.white54)),
                                  const SizedBox(height: 3),
                                  Text(card.bloodGroup.isNotEmpty
                                      ? card.bloodGroup
                                      : '—',
                                      style: AppTextStyles.body.copyWith(
                                        color: Colors.white,
                                        fontWeight: FontWeight.w700,
                                        fontSize: 13,
                                      )),
                                ],
                              ),
                            ),
                          ]),
                        ],
                      ),
                    ),

                    const SizedBox(width: 16),

                    // Right: QR code
                    Container(
                      width: 90,
                      height: 90,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.all(8),
                      child: (card.qrPayload != null &&
                              card.qrPayload!.isNotEmpty)
                          ? QrImageView(
                              data: card.qrPayload!,
                              version: QrVersions.auto,
                              size: 74,
                              backgroundColor: Colors.white,
                              eyeStyle: const QrEyeStyle(
                                eyeShape: QrEyeShape.square,
                                color: AppColors.primary700,
                              ),
                              dataModuleStyle: const QrDataModuleStyle(
                                dataModuleShape: QrDataModuleShape.square,
                                color: AppColors.primary500,
                              ),
                            )
                          : const Icon(LucideIcons.qrCode,
                              size: 40, color: AppColors.primary200),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Generate Temporary QR Button
// ─────────────────────────────────────────────────────────────────────────────

class _GenerateQrButton extends StatelessWidget {
  const _GenerateQrButton({required this.card});
  final HealthIdCard card;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => _showTempQrDialog(context, card),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [AppColors.primary500, AppColors.primary600],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Row(children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(LucideIcons.qrCode, size: 22, color: Colors.white),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Generate Temporary QR',
                  style: AppTextStyles.body.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                  )),
              const SizedBox(height: 2),
              Text('Valid for 10 minutes · Scan at reception',
                  style: AppTextStyles.bodySm.copyWith(
                    color: Colors.white.withValues(alpha: 0.65),
                    fontSize: 12,
                  )),
            ]),
          ),
          Icon(LucideIcons.chevronRight,
              size: 18, color: Colors.white.withValues(alpha: 0.6)),
        ]),
      ),
    );
  }

  void _showTempQrDialog(BuildContext context, HealthIdCard card) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _TempQrSheet(card: card),
    );
  }
}

class _TempQrSheet extends StatefulWidget {
  const _TempQrSheet({required this.card});
  final HealthIdCard card;

  @override
  State<_TempQrSheet> createState() => _TempQrSheetState();
}

class _TempQrSheetState extends State<_TempQrSheet> {
  // In a real implementation this calls the API; we use the static QR payload
  // as a stand-in until the temp QR endpoint is wired to a provider.
  final _expiry = DateTime.now().add(const Duration(minutes: 10));

  String get _timeLeft {
    final diff = _expiry.difference(DateTime.now());
    if (diff.isNegative) return 'Expired';
    return '${diff.inMinutes}:${(diff.inSeconds % 60).toString().padLeft(2, '0')} remaining';
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(24),
      ),
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 36),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        // Handle
        Container(
          width: 40, height: 4,
          decoration: BoxDecoration(
            color: AppColors.divider,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(height: 20),

        Text('Temporary QR Code', style: AppTextStyles.h3),
        const SizedBox(height: 4),
        Text(
          'Show this to reception staff. Valid for 10 minutes.',
          style: AppTextStyles.bodySm,
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 24),

        // QR
        Container(
          width: 220, height: 220,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.primary100, width: 2),
            boxShadow: [
              BoxShadow(
                color: AppColors.primary500.withValues(alpha: 0.12),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          padding: const EdgeInsets.all(16),
          child: widget.card.qrPayload != null &&
                  widget.card.qrPayload!.isNotEmpty
              ? QrImageView(
                  data: widget.card.qrPayload!,
                  version: QrVersions.auto,
                  size: 188,
                  backgroundColor: Colors.white,
                  eyeStyle: const QrEyeStyle(
                    eyeShape: QrEyeShape.square,
                    color: AppColors.primary700,
                  ),
                  dataModuleStyle: const QrDataModuleStyle(
                    dataModuleShape: QrDataModuleShape.square,
                    color: AppColors.primary500,
                  ),
                )
              : const Icon(LucideIcons.qrCode,
                  size: 80, color: AppColors.primary200),
        ),

        const SizedBox(height: 16),

        // Timer badge
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: AppColors.warningLight,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: AppColors.warningBorder),
          ),
          child: Row(mainAxisSize: MainAxisSize.min, children: [
            const Icon(LucideIcons.timer, size: 14, color: AppColors.warning),
            const SizedBox(width: 6),
            Text(_timeLeft,
                style: AppTextStyles.mono.copyWith(
                  color: AppColors.warning,
                  fontSize: 13,
                )),
          ]),
        ),
        const SizedBox(height: 12),
        Text(widget.card.healthId,
            style: AppTextStyles.monoSm.copyWith(
                color: AppColors.textSecondary)),
        const SizedBox(height: 20),
        OutlinedButton.icon(
          onPressed: () => Navigator.pop(context),
          icon: const Icon(LucideIcons.x, size: 16),
          label: const Text('Close'),
          style: OutlinedButton.styleFrom(
            minimumSize: const Size(double.infinity, 48),
            shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12)),
          ),
        ),
      ]),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Quick Action Button
// ─────────────────────────────────────────────────────────────────────────────

class _QuickBtn extends StatelessWidget {
  const _QuickBtn(this.icon, this.label, this.color, this.bgColor, this.onTap);
  final IconData icon;
  final String label;
  final Color color, bgColor;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider),
          ),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Container(
              width: 38, height: 38,
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 18, color: color),
            ),
            const SizedBox(height: 7),
            Text(
              label,
              style: AppTextStyles.caption.copyWith(
                fontSize: 10, height: 1.2, fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ]),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Loading Skeleton
// ─────────────────────────────────────────────────────────────────────────────

class _HealthIdSkeleton extends StatelessWidget {
  const _HealthIdSkeleton();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.all(16),
      child: Column(children: [
        LoadingSkeleton(height: 200, borderRadius: 22),
        SizedBox(height: 14),
        LoadingSkeleton(height: 68, borderRadius: 14),
        SizedBox(height: 12),
        LoadingSkeleton(height: 76, borderRadius: 12),
        SizedBox(height: 20),
        LoadingSkeleton(height: 110, borderRadius: 12),
      ]),
    );
  }
}
