import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/care_map_facility.dart';
import '../providers/care_map_provider.dart';

class CareMapScreen extends ConsumerWidget {
  const CareMapScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final facilitiesAsync = ref.watch(filteredFacilitiesProvider);
    final filter = ref.watch(careMapFilterProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Care Map', style: AppTextStyles.h4),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: AppColors.primary50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(LucideIcons.search,
                  size: 18, color: AppColors.primary500),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(careMapFacilitiesProvider),
        child: CustomScrollView(
          slivers: [
            // ── Map placeholder ──────────────────────────────────────────
            SliverToBoxAdapter(child: _MapPlaceholder()),

            // ── Filter chips ─────────────────────────────────────────────
            SliverToBoxAdapter(
              child: SizedBox(
                height: 52,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(
                      horizontal: 16, vertical: 10),
                  children: _filters.map((f) {
                    final active = f.key == filter;
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: GestureDetector(
                        onTap: () => ref
                            .read(careMapFilterProvider.notifier)
                            .state = f.key,
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 150),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 5),
                          decoration: BoxDecoration(
                            color: active
                                ? AppColors.primary500
                                : AppColors.surface,
                            borderRadius: BorderRadius.circular(999),
                            border: Border.all(
                              color: active
                                  ? AppColors.primary500
                                  : AppColors.divider,
                            ),
                          ),
                          child: Text(f.label,
                              style: AppTextStyles.caption.copyWith(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: active
                                    ? Colors.white
                                    : AppColors.textSecondary,
                              )),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            ),

            // ── Divider ──────────────────────────────────────────────────
            const SliverToBoxAdapter(
              child: Divider(height: 1, color: AppColors.divider),
            ),

            // ── Facilities list ──────────────────────────────────────────
            facilitiesAsync.when(
              loading: () => SliverPadding(
                padding: const EdgeInsets.all(16),
                sliver: SliverList(
                  delegate: SliverChildListDelegate([
                    const LoadingSkeleton(height: 80, borderRadius: 12),
                    const SizedBox(height: 10),
                    const LoadingSkeleton(height: 80, borderRadius: 12),
                    const SizedBox(height: 10),
                    const LoadingSkeleton(height: 80, borderRadius: 12),
                  ]),
                ),
              ),
              error: (e, _) => SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: ErrorView(
                    message: e.toString(),
                    onRetry: () => ref.invalidate(careMapFacilitiesProvider),
                  ),
                ),
              ),
              data: (facilities) {
                if (facilities.isEmpty) {
                  return SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.all(40),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(LucideIcons.mapPin,
                              size: 48, color: AppColors.neutral300),
                          const SizedBox(height: 12),
                          Text('No facilities found',
                              style: AppTextStyles.body
                                  .copyWith(fontWeight: FontWeight.w600)),
                          const SizedBox(height: 4),
                          Text(
                              'Try a different filter or check your location.',
                              style: AppTextStyles.bodySm,
                              textAlign: TextAlign.center),
                        ],
                      ),
                    ),
                  );
                }
                return SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, i) => _FacilityRow(facility: facilities[i]),
                    childCount: facilities.length,
                  ),
                );
              },
            ),
            const SliverToBoxAdapter(child: SizedBox(height: 32)),
          ],
        ),
      ),
    );
  }

  static const _filters = [
    _Filter('all',       'All'),
    _Filter('hospital',  'Hospitals'),
    _Filter('clinic',    'Clinics'),
    _Filter('pharmacy',  'Pharmacies'),
    _Filter('lab',       'Labs'),
    _Filter('emergency', 'Emergency'),
  ];
}

class _Filter {
  const _Filter(this.key, this.label);
  final String key, label;
}

// ── Decorative map placeholder ───────────────────────────────────────────────

class _MapPlaceholder extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      height: 148,
      color: const Color(0xFFEFF6FF),
      child: Stack(children: [
        // Grid
        CustomPaint(size: Size.infinite, painter: _GridPainter()),
        // Roads
        CustomPaint(size: Size.infinite, painter: _RoadPainter()),
        // Location dot
        Positioned(
          left: MediaQuery.of(context).size.width * 0.38,
          top: 60,
          child: Container(
            width: 16, height: 16,
            decoration: BoxDecoration(
              color: AppColors.primary500,
              shape: BoxShape.circle,
              border: Border.all(color: Colors.white, width: 2.5),
              boxShadow: [
                BoxShadow(
                  color: AppColors.primary500.withValues(alpha: 0.35),
                  blurRadius: 10, spreadRadius: 3,
                ),
              ],
            ),
          ),
        ),
        // Pins
        _pin(left: 0.15, top: 0.25, color: AppColors.primary500),
        _pin(left: 0.53, top: 0.42, color: AppColors.success),
        _pin(left: 0.70, top: 0.18, color: AppColors.warning),
        // Label
        Positioned(
          bottom: 6, right: 12,
          child: Text('Yaoundé, CM',
              style: AppTextStyles.caption.copyWith(
                fontSize: 9, color: AppColors.textMuted)),
        ),
      ]),
    );
  }

  Widget _pin({required double left, required double top, required Color color}) {
    return Positioned(
      left: left * 340,
      top: top * 148,
      child: Container(
        width: 20, height: 20,
        decoration: BoxDecoration(
          color: color, shape: BoxShape.circle,
          border: Border.all(color: Colors.white, width: 2),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.4),
              blurRadius: 6, offset: const Offset(0, 2)),
          ],
        ),
      ),
    );
  }
}

class _GridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF93C5FD).withValues(alpha: 0.4)
      ..strokeWidth = 0.5;
    for (double x = 0; x < size.width; x += 28) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), paint);
    }
    for (double y = 0; y < size.height; y += 28) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
    }
  }
  @override
  bool shouldRepaint(_) => false;
}

class _RoadPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final main = Paint()
      ..color = const Color(0xFFBFDBFE)
      ..strokeWidth = 10;
    final side = Paint()
      ..color = const Color(0xFFDBEAFE)
      ..strokeWidth = 5;
    canvas.drawLine(Offset(0, size.height * 0.52),
        Offset(size.width, size.height * 0.52), main);
    canvas.drawLine(Offset(size.width * 0.45, 0),
        Offset(size.width * 0.45, size.height), main);
    canvas.drawLine(Offset(size.width * 0.22, 0),
        Offset(size.width * 0.22, size.height), side);
    canvas.drawLine(Offset(size.width * 0.72, 0),
        Offset(size.width * 0.72, size.height), side);
  }
  @override
  bool shouldRepaint(_) => false;
}

// ── Facility row ─────────────────────────────────────────────────────────────

class _FacilityRow extends StatelessWidget {
  const _FacilityRow({required this.facility});
  final CareMapFacility facility;

  static const _typeIcons = {
    'hospital':  LucideIcons.building2,
    'clinic':    LucideIcons.home,
    'pharmacy':  LucideIcons.flaskConical,
    'lab':       LucideIcons.microscope,
    'emergency': LucideIcons.alertTriangle,
  };
  static const _typeColors = {
    'hospital':  AppColors.primary500,
    'clinic':    AppColors.success,
    'pharmacy':  AppColors.warning,
    'lab':       AppColors.info,
    'emergency': AppColors.danger,
  };
  static const _typeBgs = {
    'hospital':  AppColors.primary50,
    'clinic':    AppColors.successLight,
    'pharmacy':  AppColors.warningLight,
    'lab':       AppColors.infoLight,
    'emergency': AppColors.dangerLight,
  };

  @override
  Widget build(BuildContext context) {
    final icon  = _typeIcons[facility.type]  ?? LucideIcons.mapPin;
    final color = _typeColors[facility.type] ?? AppColors.neutral500;
    final bg    = _typeBgs[facility.type]    ?? AppColors.neutral100;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: AppColors.divider)),
      ),
      child: Row(children: [
        Container(
          width: 42, height: 42,
          decoration: BoxDecoration(
            color: bg, borderRadius: BorderRadius.circular(10)),
          child: Icon(icon, size: 18, color: color),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(facility.name,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600)),
            const SizedBox(height: 2),
            Text(
              [
                facility.typeLabel,
                if (facility.specialties.isNotEmpty)
                  facility.specialties.take(2).join(', '),
              ].join(' · '),
              style: AppTextStyles.bodySm,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 4),
            Row(children: [
              if (facility.isConnected)
                _badge('Connected', AppColors.successLight, AppColors.successDark),
              if (facility.isConnected) const SizedBox(width: 6),
              if (facility.rating != null)
                Text('★ ${facility.rating!.toStringAsFixed(1)}',
                    style: AppTextStyles.caption.copyWith(fontSize: 10)),
              if (facility.isOpen != null) ...[
                const SizedBox(width: 6),
                Text(
                  facility.isOpen!
                      ? 'Open${facility.openUntil != null ? " · closes ${facility.openUntil}" : ""}'
                      : 'Closed',
                  style: AppTextStyles.caption.copyWith(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: facility.isOpen!
                        ? AppColors.success
                        : AppColors.danger,
                  ),
                ),
              ],
            ]),
          ]),
        ),
        if (facility.distanceKm != null) ...[
          const SizedBox(width: 8),
          Text(facility.distanceLabel,
              style: AppTextStyles.monoSm.copyWith(
                color: AppColors.primary500, fontSize: 11)),
        ],
      ]),
    );
  }

  Widget _badge(String label, Color bg, Color fg) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
        decoration: BoxDecoration(
          color: bg, borderRadius: BorderRadius.circular(999)),
        child: Text(label,
            style: AppTextStyles.caption.copyWith(
              fontSize: 9, fontWeight: FontWeight.w700, color: fg)),
      );
}
