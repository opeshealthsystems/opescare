# OpesCare Patient Flutter App — Phase 3: Consent + Timeline + Labs + Prescriptions

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` or `superpowers:executing-plans`. Complete Phase 2 first.

**Goal:** Build the four core clinical screens: Consent Requests (approve/deny), Clinical Timeline, Lab Results (list + detail), and Prescriptions (list + detail).

**Prerequisite:** Phase 2 complete — shell and home screen working.

**API endpoints used this phase:**
- `GET /mobile/consent-requests` — list pending consents
- `POST /mobile/consent-requests/{id}/approve` — approve a consent
- `POST /mobile/consent-requests/{id}/deny` — deny a consent
- `POST /mobile/consents/{id}/revoke` — revoke an active consent
- `GET /mobile/timeline` — patient clinical timeline
- `GET /mobile/allergies` — allergy records
- `GET /mobile/clinical` — clinical conditions
- `GET /mobile/immunizations` — immunization records
- `GET /mobile/labs` — lab orders & results list
- `GET /mobile/labs/{id}` — single lab result detail
- `GET /mobile/prescriptions` — prescriptions list
- `GET /mobile/prescriptions/{id}` — single prescription detail

---

## File Map — Phase 3

```
lib/features/
├── consent/
│   ├── models/consent_request.dart
│   ├── data/consent_repository.dart
│   ├── providers/consent_provider.dart
│   └── presentation/
│       ├── consent_screen.dart
│       └── consent_detail_screen.dart
├── timeline/
│   ├── models/timeline_event.dart
│   ├── data/timeline_repository.dart
│   ├── providers/timeline_provider.dart
│   └── presentation/timeline_screen.dart
├── labs/
│   ├── models/lab_result.dart
│   ├── data/labs_repository.dart
│   ├── providers/labs_provider.dart
│   └── presentation/
│       ├── labs_screen.dart
│       └── lab_detail_screen.dart
└── prescriptions/
    ├── models/prescription.dart
    ├── data/prescriptions_repository.dart
    ├── providers/prescriptions_provider.dart
    └── presentation/
        ├── prescriptions_screen.dart
        └── prescription_detail_screen.dart
```

---

## Task 15: Consent Feature

- [ ] **Step 15.1 — Consent model**

Create `apps/mobile-patient/lib/features/consent/models/consent_request.dart`:

```dart
import 'package:equatable/equatable.dart';

enum ConsentStatus { pending, approved, denied, revoked, expired }

class ConsentRequest extends Equatable {
  const ConsentRequest({
    required this.id,
    required this.requestingFacility,
    required this.requestingRole,
    required this.purpose,
    required this.scopeLabels,
    required this.expiresAt,
    required this.status,
    required this.createdAt,
  });

  final String id;
  final String requestingFacility;
  final String requestingRole;
  final String purpose;
  final List<String> scopeLabels; // human-readable scope descriptions
  final String expiresAt;
  final ConsentStatus status;
  final String createdAt;

  factory ConsentRequest.fromJson(Map<String, dynamic> json) {
    final rawStatus = json['status']?.toString() ?? 'pending';
    final status = ConsentStatus.values.firstWhere(
      (s) => s.name == rawStatus,
      orElse: () => ConsentStatus.pending,
    );
    final facility = json['requesting_facility'] as Map? ?? {};
    final rawScopes = json['scope_labels'] as List? ?? [];
    return ConsentRequest(
      id: json['id'].toString(),
      requestingFacility: facility['name']?.toString() ?? 'Unknown Facility',
      requestingRole: json['requesting_role']?.toString() ?? '',
      purpose: json['purpose']?.toString() ?? '',
      scopeLabels: rawScopes.map((s) => s.toString()).toList(),
      expiresAt: json['expires_at']?.toString() ?? '',
      status: status,
      createdAt: json['created_at']?.toString() ?? '',
    );
  }

  @override
  List<Object?> get props => [id, status];
}
```

- [ ] **Step 15.2 — Consent repository**

Create `apps/mobile-patient/lib/features/consent/data/consent_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/consent_request.dart';

class ConsentRepository {
  const ConsentRepository(this._client);
  final ApiClient _client;

  Future<List<ConsentRequest>> fetchRequests({String? status}) async {
    final res = await _client.get(ApiEndpoints.consentRequests,
        params: status != null ? {'status': status} : null);
    final list = res['data'] as List? ?? [];
    return list.map((j) => ConsentRequest.fromJson(j as Map<String, dynamic>)).toList();
  }

  Future<void> approve(String id) =>
      _client.post(ApiEndpoints.approveConsent(id));

  Future<void> deny(String id) =>
      _client.post(ApiEndpoints.denyConsent(id));

  Future<void> revoke(String id) =>
      _client.post(ApiEndpoints.revokeConsent(id));
}
```

- [ ] **Step 15.3 — Consent provider**

Create `apps/mobile-patient/lib/features/consent/providers/consent_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/consent_repository.dart';
import '../models/consent_request.dart';

final consentRepositoryProvider = Provider<ConsentRepository>(
  (ref) => ConsentRepository(ref.watch(apiClientProvider)),
);

final consentRequestsProvider =
    FutureProvider.family<List<ConsentRequest>, String?>((ref, status) {
  return ref.watch(consentRepositoryProvider).fetchRequests(status: status);
});

// Action state: tracks loading for approve/deny/revoke per id
class ConsentActionNotifier extends StateNotifier<AsyncValue<void>> {
  ConsentActionNotifier(this._repo) : super(const AsyncValue.data(null));
  final ConsentRepository _repo;

  Future<void> approve(String id, WidgetRef ref) async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      await _repo.approve(id);
      ref.invalidate(consentRequestsProvider);
    });
  }

  Future<void> deny(String id, WidgetRef ref) async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      await _repo.deny(id);
      ref.invalidate(consentRequestsProvider);
    });
  }

  Future<void> revoke(String id, WidgetRef ref) async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      await _repo.revoke(id);
      ref.invalidate(consentRequestsProvider);
    });
  }
}

final consentActionProvider =
    StateNotifierProvider<ConsentActionNotifier, AsyncValue<void>>((ref) {
  return ConsentActionNotifier(ref.watch(consentRepositoryProvider));
});
```

- [ ] **Step 15.4 — Consent screen**

Create `apps/mobile-patient/lib/features/consent/presentation/consent_screen.dart`:

```dart
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
        children: [
          _ConsentList(status: 'pending', ref: ref),
          _ConsentList(status: null, ref: ref), // all history
        ],
      ),
    );
  }
}

class _ConsentList extends ConsumerWidget {
  const _ConsentList({required this.status, required this.ref});
  final String? status;
  final WidgetRef ref;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final requestsAsync = ref.watch(consentRequestsProvider(status));
    return requestsAsync.when(
      loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
        LoadingSkeleton(height: 130, borderRadius: 12),
        SizedBox(height: 10),
        LoadingSkeleton(height: 130, borderRadius: 12),
      ]),
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
              Text(status == 'pending'
                  ? 'No pending consent requests.'
                  : 'No consent history yet.',
                  style: AppTextStyles.bodySm),
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
              ? AppColors.warning.withOpacity(0.4) : AppColors.divider,
        ),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Padding(
          padding: const EdgeInsets.all(14),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              Expanded(
                child: Text(request.requestingFacility,
                    style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600)),
              ),
              StatusBadge(
                _statusToBadge(request.status), small: true),
            ]),
            const SizedBox(height: 4),
            Text(request.requestingRole,
                style: AppTextStyles.bodySm),
            const SizedBox(height: 10),
            // Purpose
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.neutral50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('PURPOSE', style: AppTextStyles.label),
                const SizedBox(height: 4),
                Text(request.purpose, style: AppTextStyles.body),
              ]),
            ),
            const SizedBox(height: 10),
            // Scopes
            Text('WHAT THEY WANT TO ACCESS', style: AppTextStyles.label),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6, runSpacing: 6,
              children: request.scopeLabels.map((s) => Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.infoLight,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(s,
                    style: AppTextStyles.caption.copyWith(color: AppColors.infoDark)),
              )).toList(),
            ),
            const SizedBox(height: 8),
            Row(children: [
              const Icon(LucideIcons.clock, size: 13, color: AppColors.neutral400),
              const SizedBox(width: 4),
              Text('Expires $formattedExpiry',
                  style: AppTextStyles.caption),
            ]),
          ]),
        ),
        if (isPending) ...[
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            child: Row(children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: isLoading ? null : () async {
                    final confirmed = await _confirmDeny(context);
                    if (confirmed && context.mounted) {
                      await ref.read(consentActionProvider.notifier)
                          .deny(request.id, ref);
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
                  onPressed: isLoading ? null : () async {
                    await ref.read(consentActionProvider.notifier)
                        .approve(request.id, ref);
                  },
                  icon: const Icon(LucideIcons.check, size: 16),
                  label: const Text('Approve'),
                  style: ElevatedButton.styleFrom(minimumSize: const Size(0, 44)),
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
                  child: const Text('Cancel')),
              ElevatedButton(
                  style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.danger,
                      minimumSize: const Size(80, 40)),
                  onPressed: () => Navigator.pop(context, true),
                  child: const Text('Deny')),
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
```

---

## Task 16: Timeline Feature

- [ ] **Step 16.1 — Timeline event model**

Create `apps/mobile-patient/lib/features/timeline/models/timeline_event.dart`:

```dart
class TimelineEvent {
  const TimelineEvent({
    required this.id,
    required this.type,
    required this.title,
    required this.description,
    required this.facilityName,
    required this.occurredAt,
    required this.isVerified,
    this.sensitivityLabel,
  });

  final String id;
  final String type; // encounter, lab, prescription, immunization, etc.
  final String title;
  final String description;
  final String facilityName;
  final String occurredAt;
  final bool isVerified;
  final String? sensitivityLabel;

  factory TimelineEvent.fromJson(Map<String, dynamic> json) {
    final facility = json['facility'] as Map? ?? {};
    return TimelineEvent(
      id: json['id'].toString(),
      type: json['type']?.toString() ?? 'event',
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      facilityName: facility['name']?.toString() ?? 'Unknown Facility',
      occurredAt: json['occurred_at']?.toString() ?? '',
      isVerified: json['is_verified'] == true,
      sensitivityLabel: json['sensitivity_label']?.toString(),
    );
  }
}
```

- [ ] **Step 16.2 — Timeline repository & provider**

Create `apps/mobile-patient/lib/features/timeline/data/timeline_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/timeline_event.dart';

class TimelineRepository {
  const TimelineRepository(this._client);
  final ApiClient _client;

  Future<List<TimelineEvent>> fetchTimeline({String? type}) async {
    final res = await _client.get(ApiEndpoints.timeline,
        params: type != null ? {'type': type} : null);
    final list = res['data'] as List? ?? [];
    return list.map((j) => TimelineEvent.fromJson(j as Map<String, dynamic>)).toList();
  }
}
```

Create `apps/mobile-patient/lib/features/timeline/providers/timeline_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/timeline_repository.dart';
import '../models/timeline_event.dart';

final timelineRepositoryProvider = Provider<TimelineRepository>(
  (ref) => TimelineRepository(ref.watch(apiClientProvider)),
);

final timelineProvider =
    FutureProvider.family<List<TimelineEvent>, String?>((ref, type) {
  return ref.watch(timelineRepositoryProvider).fetchTimeline(type: type);
});
```

- [ ] **Step 16.3 — Timeline screen**

Create `apps/mobile-patient/lib/features/timeline/presentation/timeline_screen.dart`:

```dart
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
  String? _filter; // null = all

  static const _filters = [
    (label: 'All', value: null),
    (label: 'Encounters', value: 'encounter'),
    (label: 'Labs', value: 'lab'),
    (label: 'Prescriptions', value: 'prescription'),
    (label: 'Immunizations', value: 'immunization'),
  ];

  @override
  Widget build(BuildContext context) {
    final eventsAsync = ref.watch(timelineProvider(_filter));

    return Scaffold(
      appBar: AppBar(title: const Text('My Timeline')),
      body: Column(children: [
        // Filter chips
        SizedBox(
          height: 50,
          child: ListView.separated(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            scrollDirection: Axis.horizontal,
            itemCount: _filters.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (_, i) {
              final f = _filters[i];
              final selected = _filter == f.value;
              return FilterChip(
                label: Text(f.label),
                selected: selected,
                onSelected: (_) => setState(() => _filter = f.value),
                selectedColor: AppColors.primary100,
                checkmarkColor: AppColors.primary500,
                labelStyle: AppTextStyles.bodySm.copyWith(
                  color: selected ? AppColors.primary500 : AppColors.textSecondary,
                  fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
                ),
                side: BorderSide(
                  color: selected ? AppColors.primary300 : AppColors.divider,
                ),
              );
            },
          ),
        ),
        Expanded(
          child: eventsAsync.when(
            loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
              LoadingSkeleton(height: 80, borderRadius: 8),
              SizedBox(height: 10),
              LoadingSkeleton(height: 80, borderRadius: 8),
              SizedBox(height: 10),
              LoadingSkeleton(height: 80, borderRadius: 8),
            ]),
            error: (e, _) => ErrorView(
              message: e.toString(),
              onRetry: () => ref.invalidate(timelineProvider(_filter)),
            ),
            data: (events) {
              if (events.isEmpty) {
                return Center(
                  child: Column(mainAxisSize: MainAxisSize.min, children: [
                    const Icon(LucideIcons.activity,
                        size: 48, color: AppColors.neutral300),
                    const SizedBox(height: 12),
                    Text('No timeline events yet.', style: AppTextStyles.bodySm),
                  ]),
                );
              }
              return RefreshIndicator(
                onRefresh: () async => ref.invalidate(timelineProvider(_filter)),
                child: ListView.builder(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
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
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Timeline spine
        Column(children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: _typeColor(event.type).withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(_typeIcon(event.type), size: 18, color: _typeColor(event.type)),
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
        // Content
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
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: [
                  Expanded(child: Text(event.title,
                      style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600))),
                  if (event.isVerified)
                    const Icon(LucideIcons.checkCircle,
                        size: 14, color: AppColors.success),
                ]),
                const SizedBox(height: 4),
                Text(event.description,
                    style: AppTextStyles.bodySm, maxLines: 2,
                    overflow: TextOverflow.ellipsis),
                const SizedBox(height: 8),
                Row(children: [
                  const Icon(LucideIcons.building2,
                      size: 12, color: AppColors.neutral400),
                  const SizedBox(width: 4),
                  Expanded(child: Text(event.facilityName,
                      style: AppTextStyles.caption, overflow: TextOverflow.ellipsis)),
                  Text(formattedDate, style: AppTextStyles.caption),
                ]),
              ]),
            ),
          ),
        ),
      ]),
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
```

---

## Task 17: Labs Feature

- [ ] **Step 17.1 — Lab result model**

Create `apps/mobile-patient/lib/features/labs/models/lab_result.dart`:

```dart
class LabResult {
  const LabResult({
    required this.id,
    required this.testName,
    required this.status,
    required this.facilityName,
    required this.orderedAt,
    this.resultSummary,
    this.resultValue,
    this.referenceRange,
    this.unit,
    this.isCritical,
    this.releasedAt,
    this.notes,
  });

  final String id;
  final String testName;
  final String status; // pending, processing, released, amended, critical
  final String facilityName;
  final String orderedAt;
  final String? resultSummary;
  final String? resultValue;
  final String? referenceRange;
  final String? unit;
  final bool? isCritical;
  final String? releasedAt;
  final String? notes;

  factory LabResult.fromJson(Map<String, dynamic> json) {
    final facility = json['facility'] as Map? ?? {};
    return LabResult(
      id: json['id'].toString(),
      testName: json['test_name']?.toString() ?? '',
      status: json['status']?.toString() ?? 'pending',
      facilityName: facility['name']?.toString() ?? '',
      orderedAt: json['ordered_at']?.toString() ?? '',
      resultSummary: json['result_summary']?.toString(),
      resultValue: json['result_value']?.toString(),
      referenceRange: json['reference_range']?.toString(),
      unit: json['unit']?.toString(),
      isCritical: json['is_critical'] as bool?,
      releasedAt: json['released_at']?.toString(),
      notes: json['notes']?.toString(),
    );
  }
}
```

- [ ] **Step 17.2 — Labs repository & provider**

Create `apps/mobile-patient/lib/features/labs/data/labs_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/lab_result.dart';

class LabsRepository {
  const LabsRepository(this._client);
  final ApiClient _client;

  Future<List<LabResult>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.labs);
    final list = res['data'] as List? ?? [];
    return list.map((j) => LabResult.fromJson(j as Map<String, dynamic>)).toList();
  }

  Future<LabResult> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.lab(id));
    return LabResult.fromJson(res['data'] as Map<String, dynamic>? ?? res);
  }
}
```

Create `apps/mobile-patient/lib/features/labs/providers/labs_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/labs_repository.dart';
import '../models/lab_result.dart';

final labsRepositoryProvider = Provider<LabsRepository>(
  (ref) => LabsRepository(ref.watch(apiClientProvider)),
);

final labsListProvider = FutureProvider<List<LabResult>>((ref) {
  return ref.watch(labsRepositoryProvider).fetchAll();
});

final labDetailProvider = FutureProvider.family<LabResult, String>((ref, id) {
  return ref.watch(labsRepositoryProvider).fetchOne(id);
});
```

- [ ] **Step 17.3 — Labs list screen**

Create `apps/mobile-patient/lib/features/labs/presentation/labs_screen.dart`:

```dart
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
import '../../../shared/widgets/status_badge.dart';
import '../models/lab_result.dart';
import '../providers/labs_provider.dart';

class LabsScreen extends ConsumerWidget {
  const LabsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final labsAsync = ref.watch(labsListProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Lab Results')),
      body: labsAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 88, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 88, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 88, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(labsListProvider),
        ),
        data: (labs) {
          if (labs.isEmpty) {
            return Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(LucideIcons.flaskConical,
                    size: 48, color: AppColors.neutral300),
                const SizedBox(height: 12),
                Text('No lab results yet.', style: AppTextStyles.bodySm),
              ]),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(labsListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: labs.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _LabCard(lab: labs[i]),
            ),
          );
        },
      ),
    );
  }
}

class _LabCard extends StatelessWidget {
  const _LabCard({required this.lab});
  final LabResult lab;

  @override
  Widget build(BuildContext context) {
    String formattedDate = lab.orderedAt;
    try {
      formattedDate = DateFormat('d MMM yyyy').format(DateTime.parse(lab.orderedAt));
    } catch (_) {}

    final badge = _statusBadge(lab.status);

    return GestureDetector(
      onTap: () => context.push('/labs/${lab.id}'),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: lab.isCritical == true
                ? AppColors.danger.withOpacity(0.4) : AppColors.divider,
          ),
        ),
        child: Row(children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(
              color: lab.isCritical == true
                  ? AppColors.dangerLight : AppColors.successLight,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(LucideIcons.flaskConical, size: 22,
                color: lab.isCritical == true
                    ? AppColors.danger : AppColors.success),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(
            crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              Expanded(child: Text(lab.testName,
                  style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600))),
              if (badge != null) StatusBadge(badge, small: true),
            ]),
            const SizedBox(height: 4),
            Text(lab.facilityName, style: AppTextStyles.bodySm,
                overflow: TextOverflow.ellipsis),
            const SizedBox(height: 4),
            Row(children: [
              const Icon(LucideIcons.calendar, size: 12, color: AppColors.neutral400),
              const SizedBox(width: 4),
              Text(formattedDate, style: AppTextStyles.caption),
              if (lab.isCritical == true) ...[
                const SizedBox(width: 8),
                const Icon(LucideIcons.alertTriangle,
                    size: 12, color: AppColors.danger),
                const SizedBox(width: 2),
                Text('Critical', style: AppTextStyles.caption
                    .copyWith(color: AppColors.danger, fontWeight: FontWeight.w600)),
              ],
            ]),
          ])),
          const Icon(LucideIcons.chevronRight, size: 16, color: AppColors.neutral400),
        ]),
      ),
    );
  }

  BadgeStatus? _statusBadge(String s) => switch (s) {
        'released'   => BadgeStatus.released,
        'pending'    => BadgeStatus.pending,
        'processing' => BadgeStatus.pending,
        'amended'    => BadgeStatus.provisional,
        'critical'   => BadgeStatus.critical,
        _ => null,
      };
}
```

- [ ] **Step 17.4 — Lab detail screen**

Create `apps/mobile-patient/lib/features/labs/presentation/lab_detail_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../providers/labs_provider.dart';

class LabDetailScreen extends ConsumerWidget {
  const LabDetailScreen({super.key, required this.id});
  final String id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final labAsync = ref.watch(labDetailProvider(id));
    return Scaffold(
      appBar: AppBar(title: const Text('Lab Result')),
      body: labAsync.when(
        loading: () => const _LabDetailSkeleton(),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(labDetailProvider(id)),
        ),
        data: (lab) => ListView(padding: const EdgeInsets.all(16), children: [
          // Status header
          if (lab.isCritical == true)
            Container(
              padding: const EdgeInsets.all(12),
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: AppColors.dangerLight,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.danger.withOpacity(0.3)),
              ),
              child: Row(children: [
                const Icon(LucideIcons.alertTriangle, color: AppColors.danger, size: 18),
                const SizedBox(width: 10),
                Expanded(child: Text(
                    'This is a critical result. Please review with your healthcare provider.',
                    style: AppTextStyles.body.copyWith(color: AppColors.dangerDark))),
              ]),
            ),
          // Test info card
          _DetailCard(title: lab.testName, rows: [
            if (lab.resultValue != null)
              _Row('Result', '${lab.resultValue}${lab.unit != null ? ' ${lab.unit}' : ''}',
                  valueStyle: AppTextStyles.h3.copyWith(color:
                      lab.isCritical == true ? AppColors.danger : AppColors.textPrimary)),
            if (lab.referenceRange != null)
              _Row('Reference Range', lab.referenceRange!),
            _Row('Status', lab.status.toUpperCase()),
            _Row('Ordered', _fmt(lab.orderedAt)),
            if (lab.releasedAt != null)
              _Row('Released', _fmt(lab.releasedAt!)),
            _Row('Facility', lab.facilityName),
          ]),
          if (lab.notes != null) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.infoLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: [
                  const Icon(LucideIcons.info, size: 16, color: AppColors.info),
                  const SizedBox(width: 8),
                  Text('Provider Note', style: AppTextStyles.h4),
                ]),
                const SizedBox(height: 8),
                Text(lab.notes!, style: AppTextStyles.body),
              ]),
            ),
          ],
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.neutral50,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(children: [
              const Icon(LucideIcons.info, size: 14, color: AppColors.neutral400),
              const SizedBox(width: 8),
              Expanded(child: Text(
                  'Results are for informational purposes. Please discuss with your doctor.',
                  style: AppTextStyles.caption)),
            ]),
          ),
          const SizedBox(height: 32),
        ]),
      ),
    );
  }

  String _fmt(String raw) {
    try { return DateFormat('d MMM yyyy, h:mm a').format(DateTime.parse(raw)); }
    catch (_) { return raw; }
  }
}

class _DetailCard extends StatelessWidget {
  const _DetailCard({required this.title, required this.rows});
  final String title;
  final List<_Row> rows;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface, borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(children: [
        Padding(padding: const EdgeInsets.all(14),
            child: Align(alignment: Alignment.centerLeft,
                child: Text(title, style: AppTextStyles.h4))),
        const Divider(height: 1),
        ...rows.map((r) => Column(children: [
          Padding(padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              child: Row(children: [
                Expanded(child: Text(r.label, style: AppTextStyles.bodySm)),
                Text(r.value, style: r.valueStyle ?? AppTextStyles.body
                    .copyWith(fontWeight: FontWeight.w600)),
              ])),
          if (r != rows.last) const Divider(height: 1, indent: 14),
        ])),
      ]),
    );
  }
}

class _Row {
  const _Row(this.label, this.value, {this.valueStyle});
  final String label, value;
  final TextStyle? valueStyle;
}

class _LabDetailSkeleton extends StatelessWidget {
  const _LabDetailSkeleton();
  @override
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.all(16),
    children: const [
      LoadingSkeleton(height: 200, borderRadius: 12),
      SizedBox(height: 16),
      LoadingSkeleton(height: 120, borderRadius: 12),
    ],
  );
}
```

---

## Task 18: Prescriptions Feature

- [ ] **Step 18.1 — Prescription model**

Create `apps/mobile-patient/lib/features/prescriptions/models/prescription.dart`:

```dart
class Prescription {
  const Prescription({
    required this.id,
    required this.medicationName,
    required this.dosage,
    required this.frequency,
    required this.status,
    required this.prescribedBy,
    required this.facilityName,
    required this.prescribedAt,
    this.instructions,
    this.dispensedAt,
    this.expiresAt,
  });

  final String id, medicationName, dosage, frequency, status;
  final String prescribedBy, facilityName, prescribedAt;
  final String? instructions, dispensedAt, expiresAt;

  factory Prescription.fromJson(Map<String, dynamic> json) {
    final facility  = json['facility']  as Map? ?? {};
    final prescriber = json['prescriber'] as Map? ?? {};
    return Prescription(
      id:              json['id'].toString(),
      medicationName:  json['medication_name']?.toString() ?? '',
      dosage:          json['dosage']?.toString() ?? '',
      frequency:       json['frequency']?.toString() ?? '',
      status:          json['status']?.toString() ?? 'active',
      prescribedBy:    prescriber['name']?.toString() ?? '',
      facilityName:    facility['name']?.toString() ?? '',
      prescribedAt:    json['prescribed_at']?.toString() ?? '',
      instructions:    json['instructions']?.toString(),
      dispensedAt:     json['dispensed_at']?.toString(),
      expiresAt:       json['expires_at']?.toString(),
    );
  }
}
```

- [ ] **Step 18.2 — Prescriptions repository & provider**

Create `apps/mobile-patient/lib/features/prescriptions/data/prescriptions_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/prescription.dart';

class PrescriptionsRepository {
  const PrescriptionsRepository(this._client);
  final ApiClient _client;

  Future<List<Prescription>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.prescriptions);
    final list = res['data'] as List? ?? [];
    return list.map((j) => Prescription.fromJson(j as Map<String, dynamic>)).toList();
  }

  Future<Prescription> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.prescription(id));
    return Prescription.fromJson(res['data'] as Map<String, dynamic>? ?? res);
  }
}
```

Create `apps/mobile-patient/lib/features/prescriptions/providers/prescriptions_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/prescriptions_repository.dart';
import '../models/prescription.dart';

final prescriptionsRepositoryProvider = Provider<PrescriptionsRepository>(
  (ref) => PrescriptionsRepository(ref.watch(apiClientProvider)),
);

final prescriptionsListProvider = FutureProvider<List<Prescription>>((ref) {
  return ref.watch(prescriptionsRepositoryProvider).fetchAll();
});

final prescriptionDetailProvider =
    FutureProvider.family<Prescription, String>((ref, id) {
  return ref.watch(prescriptionsRepositoryProvider).fetchOne(id);
});
```

- [ ] **Step 18.3 — Prescriptions list screen**

Create `apps/mobile-patient/lib/features/prescriptions/presentation/prescriptions_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/status_badge.dart';
import '../models/prescription.dart';
import '../providers/prescriptions_provider.dart';

class PrescriptionsScreen extends ConsumerWidget {
  const PrescriptionsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final rxAsync = ref.watch(prescriptionsListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Prescriptions')),
      body: rxAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 88, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 88, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(prescriptionsListProvider),
        ),
        data: (list) {
          if (list.isEmpty) {
            return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.pill, size: 48, color: AppColors.neutral300),
              const SizedBox(height: 12),
              Text('No prescriptions yet.', style: AppTextStyles.bodySm),
            ]));
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(prescriptionsListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _RxCard(rx: list[i]),
            ),
          );
        },
      ),
    );
  }
}

class _RxCard extends StatelessWidget {
  const _RxCard({required this.rx});
  final Prescription rx;

  @override
  Widget build(BuildContext context) {
    String formattedDate = rx.prescribedAt;
    try {
      formattedDate = DateFormat('d MMM yyyy').format(DateTime.parse(rx.prescribedAt));
    } catch (_) {}

    final isActive = rx.status == 'active' || rx.status == 'issued';
    final badge = _badge(rx.status);

    return GestureDetector(
      onTap: () => context.push('/prescriptions/${rx.id}'),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Row(children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(
              color: isActive ? AppColors.infoLight : AppColors.neutral100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(LucideIcons.pill, size: 22,
                color: isActive ? AppColors.info : AppColors.neutral400),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              Expanded(child: Text(rx.medicationName,
                  style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600))),
              if (badge != null) StatusBadge(badge, small: true),
            ]),
            const SizedBox(height: 3),
            Text('${rx.dosage} · ${rx.frequency}',
                style: AppTextStyles.bodySm),
            const SizedBox(height: 4),
            Row(children: [
              const Icon(LucideIcons.user, size: 12, color: AppColors.neutral400),
              const SizedBox(width: 4),
              Expanded(child: Text(rx.prescribedBy,
                  style: AppTextStyles.caption, overflow: TextOverflow.ellipsis)),
              Text(formattedDate, style: AppTextStyles.caption),
            ]),
          ])),
          const Icon(LucideIcons.chevronRight, size: 16, color: AppColors.neutral400),
        ]),
      ),
    );
  }

  BadgeStatus? _badge(String s) => switch (s) {
        'active' || 'issued'             => BadgeStatus.active,
        'dispensed' || 'partially_dispensed' => BadgeStatus.released,
        'cancelled'                      => BadgeStatus.cancelled,
        'expired'                        => BadgeStatus.cancelled,
        _ => null,
      };
}
```

- [ ] **Step 18.4 — Wire up router for Phase 3 screens**

Update the `StatefulShellRoute` branches in `app_router.dart` to import and use the real screens:

```dart
// Add these imports at the top of app_router.dart:
import '../../features/consent/presentation/consent_screen.dart';
import '../../features/timeline/presentation/timeline_screen.dart';
import '../../features/labs/presentation/labs_screen.dart';
import '../../features/labs/presentation/lab_detail_screen.dart';
import '../../features/prescriptions/presentation/prescriptions_screen.dart';
import '../../features/prescriptions/presentation/prescription_detail_screen.dart';

// Replace the consent branch with:
StatefulShellBranch(routes: [
  GoRoute(path: Routes.consent, builder: (_, __) => const ConsentScreen()),
]),

// Replace the timeline branch with:
StatefulShellBranch(routes: [
  GoRoute(path: Routes.timeline, builder: (_, __) => const TimelineScreen()),
]),

// Add as top-level GoRoutes (outside shell, accessible from home stats):
GoRoute(path: Routes.labs, builder: (_, __) => const LabsScreen(),
    routes: [
      GoRoute(
        path: ':id',
        builder: (_, state) => LabDetailScreen(id: state.pathParameters['id']!),
      ),
    ]),
GoRoute(path: Routes.prescriptions, builder: (_, __) => const PrescriptionsScreen(),
    routes: [
      GoRoute(
        path: ':id',
        builder: (_, state) => PrescriptionDetailScreen(
            id: state.pathParameters['id']!),
      ),
    ]),
```

- [ ] **Step 18.5 — Analyze and run**

```bash
cd C:\laragon\www\opescare\apps\mobile-patient
flutter analyze lib/
flutter run --dart-define=API_BASE_URL=http://10.0.2.2/api
```

Expected: All 4 new screens work. Consent tab shows approve/deny cards. Timeline shows chronological events with type icons. Labs list and detail work. Prescriptions list and detail work. No linter errors.

- [ ] **Step 18.6 — Commit Phase 3**

```bash
cd C:\laragon\www\opescare
git add apps/mobile-patient/
git commit -m "feat(mobile): Phase 3 - consent, timeline, labs, prescriptions screens"
```

---

## Phase 3 Complete ✓

**What you now have:**
- Consent screen: tabbed (pending/history), approve with confirmation, deny with dialog, scope labels shown in plain language — all using Lucide icons
- Timeline screen: filterable by type, visual spine with type-colored icons, facility + date
- Labs list + detail: critical result banner, reference range, provider notes, clinical disclaimer
- Prescriptions list + detail: dosage/frequency, prescriber, facility, status badges
- All screens use Lucide icons, no emojis

**Next:** Proceed to `2026-05-29-patient-flutter-app-phase4.md` — Appointments + Access Logs + Documents + Settings.
