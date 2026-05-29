import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/timeline_event.dart';
import '../providers/timeline_provider.dart';

class TimelineScreen extends ConsumerStatefulWidget {
  const TimelineScreen({super.key});

  @override
  ConsumerState<TimelineScreen> createState() => _TimelineScreenState();
}

class _TimelineScreenState extends ConsumerState<TimelineScreen> {
  String? _filter;

  static const _filters = [
    (label: 'All',           value: null as String?),
    (label: 'Encounters',    value: 'encounter'),
    (label: 'Labs',          value: 'lab'),
    (label: 'Prescriptions', value: 'prescription'),
    (label: 'Immunizations', value: 'immunization'),
  ];

  @override
  Widget build(BuildContext context) {
    final eventsAsync = ref.watch(timelineProvider(_filter));

    return Scaffold(
      appBar: AppBar(title: const Text('My Timeline')),
      body: Column(children: [
        SizedBox(
          height: 50,
          child: ListView.separated(
            padding: const EdgeInsets.symmetric(
                horizontal: 16, vertical: 10),
            scrollDirection: Axis.horizontal,
            itemCount: _filters.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (_, i) {
              final f = _filters[i];
              final selected = _filter == f.value;
              return FilterChip(
                label: Text(f.label),
                selected: selected,
                onSelected: (_) =>
                    setState(() => _filter = f.value),
                selectedColor: AppColors.primary100,
                checkmarkColor: AppColors.primary500,
                labelStyle: AppTextStyles.bodySm.copyWith(
                  color: selected
                      ? AppColors.primary500
                      : AppColors.textSecondary,
                  fontWeight: selected
                      ? FontWeight.w600
                      : FontWeight.w400,
                ),
                side: BorderSide(
                  color: selected
                      ? AppColors.primary300
                      : AppColors.divider,
                ),
              );
            },
          ),
        ),
        Expanded(
          child: eventsAsync.when(
            loading: () => ListView(
              padding: const EdgeInsets.all(16),
              children: const [
                LoadingSkeleton(height: 80, borderRadius: 8),
                SizedBox(height: 10),
                LoadingSkeleton(height: 80, borderRadius: 8),
                SizedBox(height: 10),
                LoadingSkeleton(height: 80, borderRadius: 8),
              ],
            ),
            error: (e, _) => ErrorView(
              message: e.toString(),
              onRetry: () => ref.invalidate(timelineProvider(_filter)),
            ),
            data: (events) {
              if (events.isEmpty) {
                return Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(LucideIcons.activity,
                          size: 48, color: AppColors.neutral300),
                      const SizedBox(height: 12),
                      Text('No timeline events yet.',
                          style: AppTextStyles.bodySm),
                    ],
                  ),
                );
              }
              return RefreshIndicator(
                onRefresh: () async =>
                    ref.invalidate(timelineProvider(_filter)),
                child: ListView.builder(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: events.length,
                  itemBuilder: (_, i) => _TimelineItem(
                    event: events[i],
                    isLast: i == events.length - 1,
                  ),
                ),
              );
            },
          ),
        ),
      ]),
    );
  }
}

class _TimelineItem extends StatelessWidget {
  const _TimelineItem({required this.event, required this.isLast});

  final TimelineEvent event;
  final bool isLast;

  @override
  Widget build(BuildContext context) {
    String formattedDate = event.occurredAt;
    try {
      formattedDate = DateFormat('d MMM yyyy')
          .format(DateTime.parse(event.occurredAt));
    } catch (_) {}

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Column(children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(
                color: _typeColor(event.type).withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(_typeIcon(event.type),
                  size: 18, color: _typeColor(event.type)),
            ),
            if (!isLast)
              Expanded(
                child: Container(
                  width: 2,
                  color: AppColors.divider,
                  margin: const EdgeInsets.symmetric(vertical: 4),
                ),
              ),
          ]),
          const SizedBox(width: 12),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 16),
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: [
                      Expanded(
                        child: Text(
                          event.title,
                          style: AppTextStyles.body
                              .copyWith(fontWeight: FontWeight.w600),
                        ),
                      ),
                      if (event.isVerified)
                        const Icon(LucideIcons.checkCircle,
                            size: 14, color: AppColors.success),
                    ]),
                    const SizedBox(height: 4),
                    Text(
                      event.description,
                      style: AppTextStyles.bodySm,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Row(children: [
                      const Icon(LucideIcons.building2,
                          size: 12, color: AppColors.neutral400),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          event.facilityName,
                          style: AppTextStyles.caption,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Text(formattedDate,
                          style: AppTextStyles.caption),
                    ]),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  IconData _typeIcon(String type) => switch (type) {
        'encounter'    => LucideIcons.stethoscope,
        'lab'          => LucideIcons.flaskConical,
        'prescription' => LucideIcons.pill,
        'immunization' => LucideIcons.syringe,
        'admission'    => LucideIcons.bed,
        'document'     => LucideIcons.fileText,
        _              => LucideIcons.activity,
      };

  Color _typeColor(String type) => switch (type) {
        'encounter'    => AppColors.primary500,
        'lab'          => AppColors.success,
        'prescription' => AppColors.info,
        'immunization' => AppColors.warningDark,
        'admission'    => AppColors.danger,
        _              => AppColors.neutral500,
      };
}
