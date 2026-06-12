# OpesCare Patient Flutter App — Phase 2: Shell + Home + Health ID

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` or `superpowers:executing-plans`. Complete Phase 1 first.

**Goal:** Build the main navigation shell (bottom tabs with Lucide icons), the Home dashboard screen, and the Health ID digital card screen.

**Prerequisite:** Phase 1 complete — auth flow working, design system in place.

**API endpoints used this phase:**
- `GET /mobile/me` — patient profile
- `GET /mobile/health-id-card` — Health ID card data
- `GET /mobile/consent-requests` — pending consents (count for home badge)
- `GET /mobile/labs` — recent labs (home summary)
- `GET /mobile/prescriptions` — recent prescriptions (home summary)
- `GET /mobile/appointments` — upcoming appointment (home summary)

---

## File Map — Phase 2

```
lib/
├── features/
│   ├── shell/
│   │   └── presentation/
│   │       └── main_shell.dart         # Bottom nav shell (StatefulShellRoute)
│   ├── home/
│   │   ├── data/
│   │   │   └── home_repository.dart    # Fetch dashboard summary
│   │   ├── models/
│   │   │   └── dashboard_summary.dart  # Typed response model
│   │   ├── providers/
│   │   │   └── home_provider.dart      # FutureProvider for dashboard
│   │   └── presentation/
│   │       └── home_screen.dart        # Main home dashboard
│   └── health_id/
│       ├── data/
│       │   └── health_id_repository.dart
│       ├── models/
│       │   └── health_id_card.dart
│       ├── providers/
│       │   └── health_id_provider.dart
│       └── presentation/
│           └── health_id_screen.dart
├── shared/
│   └── widgets/
│       ├── health_id_card_widget.dart  # Reusable Health ID card
│       ├── status_badge.dart           # Verified / Pending / Critical badges
│       ├── section_header.dart         # "Labs · See all" row
│       ├── loading_skeleton.dart       # Shimmer placeholder
│       └── error_view.dart             # Error + retry widget
```

---

## Task 10: Shared Widgets

**Files:**
- Create: `lib/shared/widgets/status_badge.dart`
- Create: `lib/shared/widgets/section_header.dart`
- Create: `lib/shared/widgets/loading_skeleton.dart`
- Create: `lib/shared/widgets/error_view.dart`

- [ ] **Step 10.1 — Status badge widget**

Create `apps/mobile-patient/lib/shared/widgets/status_badge.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';

enum BadgeStatus {
  verified,
  provisional,
  pending,
  active,
  revoked,
  critical,
  released,
  cancelled,
  synced,
  failed,
}

class StatusBadge extends StatelessWidget {
  const StatusBadge(this.status, {super.key, this.small = false});

  final BadgeStatus status;
  final bool small;

  @override
  Widget build(BuildContext context) {
    final config = _config[status]!;
    final fs = small ? 10.0 : 11.0;
    final iconSize = small ? 10.0 : 12.0;
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: small ? 6 : 8,
        vertical: small ? 2 : 3,
      ),
      decoration: BoxDecoration(
        color: config.bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(config.icon, size: iconSize, color: config.fg),
          const SizedBox(width: 4),
          Text(config.label,
              style: AppTextStyles.label.copyWith(
                  fontSize: fs, color: config.fg, letterSpacing: 0.4)),
        ],
      ),
    );
  }

  static const _config = <BadgeStatus, _BadgeCfg>{
    BadgeStatus.verified:    _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.checkCircle, 'Verified'),
    BadgeStatus.provisional: _BadgeCfg(AppColors.warningLight, AppColors.warningDark, LucideIcons.clock, 'Provisional'),
    BadgeStatus.pending:     _BadgeCfg(AppColors.warningLight, AppColors.warningDark, LucideIcons.clock, 'Pending'),
    BadgeStatus.active:      _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.activity, 'Active'),
    BadgeStatus.revoked:     _BadgeCfg(AppColors.dangerLight,  AppColors.dangerDark,  LucideIcons.xCircle, 'Revoked'),
    BadgeStatus.critical:    _BadgeCfg(AppColors.dangerLight,  AppColors.dangerDark,  LucideIcons.alertTriangle, 'Critical'),
    BadgeStatus.released:    _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.checkCircle, 'Released'),
    BadgeStatus.cancelled:   _BadgeCfg(AppColors.neutral100,   AppColors.neutral600,  LucideIcons.xCircle, 'Cancelled'),
    BadgeStatus.synced:      _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.refreshCw, 'Synced'),
    BadgeStatus.failed:      _BadgeCfg(AppColors.dangerLight,  AppColors.dangerDark,  LucideIcons.wifiOff, 'Failed'),
  };
}

class _BadgeCfg {
  const _BadgeCfg(this.bg, this.fg, this.icon, this.label);
  final Color bg, fg;
  final IconData icon;
  final String label;
}
```

- [ ] **Step 10.2 — Section header widget**

Create `apps/mobile-patient/lib/shared/widgets/section_header.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';

class SectionHeader extends StatelessWidget {
  const SectionHeader({
    super.key,
    required this.title,
    this.onSeeAll,
    this.icon,
  });

  final String title;
  final VoidCallback? onSeeAll;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        if (icon != null) ...[
          Icon(icon, size: 16, color: AppColors.primary500),
          const SizedBox(width: 6),
        ],
        Text(title, style: AppTextStyles.h4),
        const Spacer(),
        if (onSeeAll != null)
          GestureDetector(
            onTap: onSeeAll,
            child: Row(children: [
              Text('See all', style: AppTextStyles.bodySm.copyWith(
                  color: AppColors.primary500, fontWeight: FontWeight.w600)),
              const SizedBox(width: 2),
              const Icon(LucideIcons.chevronRight,
                  size: 14, color: AppColors.primary500),
            ]),
          ),
      ],
    );
  }
}
```

- [ ] **Step 10.3 — Loading skeleton**

Create `apps/mobile-patient/lib/shared/widgets/loading_skeleton.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import '../../core/theme/app_colors.dart';

class LoadingSkeleton extends StatelessWidget {
  const LoadingSkeleton({
    super.key,
    this.width,
    this.height = 16,
    this.borderRadius = 8,
  });

  final double? width;
  final double height;
  final double borderRadius;

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: AppColors.neutral200,
      highlightColor: AppColors.neutral100,
      child: Container(
        width: width ?? double.infinity,
        height: height,
        decoration: BoxDecoration(
          color: AppColors.neutral200,
          borderRadius: BorderRadius.circular(borderRadius),
        ),
      ),
    );
  }
}

class HomeScreenSkeleton extends StatelessWidget {
  const HomeScreenSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(children: [
        const LoadingSkeleton(height: 100, borderRadius: 14),
        const SizedBox(height: 12),
        Row(children: const [
          Expanded(child: LoadingSkeleton(height: 72, borderRadius: 10)),
          SizedBox(width: 8),
          Expanded(child: LoadingSkeleton(height: 72, borderRadius: 10)),
          SizedBox(width: 8),
          Expanded(child: LoadingSkeleton(height: 72, borderRadius: 10)),
        ]),
        const SizedBox(height: 20),
        const LoadingSkeleton(height: 90, borderRadius: 10),
        const SizedBox(height: 12),
        const LoadingSkeleton(height: 90, borderRadius: 10),
      ]),
    );
  }
}
```

- [ ] **Step 10.4 — Error view**

Create `apps/mobile-patient/lib/shared/widgets/error_view.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';

class ErrorView extends StatelessWidget {
  const ErrorView({
    super.key,
    required this.message,
    this.onRetry,
  });

  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64, height: 64,
              decoration: BoxDecoration(
                color: AppColors.dangerLight,
                shape: BoxShape.circle,
              ),
              child: const Icon(LucideIcons.wifiOff,
                  color: AppColors.danger, size: 28),
            ),
            const SizedBox(height: 16),
            Text('Something went wrong', style: AppTextStyles.h4),
            const SizedBox(height: 8),
            Text(message,
                style: AppTextStyles.bodySm, textAlign: TextAlign.center),
            if (onRetry != null) ...[
              const SizedBox(height: 24),
              OutlinedButton.icon(
                onPressed: onRetry,
                icon: const Icon(LucideIcons.refreshCw, size: 16),
                label: const Text('Try again'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(160, 44),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
```

---

## Task 11: Health ID Models & Repository

**Files:**
- Create: `lib/features/health_id/models/health_id_card.dart`
- Create: `lib/features/health_id/data/health_id_repository.dart`
- Create: `lib/features/health_id/providers/health_id_provider.dart`

- [ ] **Step 11.1 — Health ID card model**

Create `apps/mobile-patient/lib/features/health_id/models/health_id_card.dart`:

```dart
import 'package:equatable/equatable.dart';

class HealthIdCard extends Equatable {
  const HealthIdCard({
    required this.healthId,
    required this.displayName,
    required this.dateOfBirth,
    required this.sex,
    required this.bloodGroup,
    required this.isVerified,
    required this.photoUrl,
    required this.allergySummary,
    required this.emergencyContact,
    required this.issuedAt,
  });

  final String healthId;
  final String displayName;
  final String dateOfBirth;
  final String sex;
  final String bloodGroup;
  final bool isVerified;
  final String? photoUrl;
  final String? allergySummary;
  final String? emergencyContact;
  final String issuedAt;

  factory HealthIdCard.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;
    return HealthIdCard(
      healthId: data['health_id']?.toString() ?? '',
      displayName: data['display_name']?.toString() ?? '',
      dateOfBirth: data['date_of_birth']?.toString() ?? '',
      sex: data['sex']?.toString() ?? '',
      bloodGroup: data['blood_group']?.toString() ?? '',
      isVerified: data['is_verified'] == true,
      photoUrl: data['photo_url']?.toString(),
      allergySummary: data['allergy_summary']?.toString(),
      emergencyContact: data['emergency_contact']?.toString(),
      issuedAt: data['issued_at']?.toString() ?? '',
    );
  }

  @override
  List<Object?> get props => [healthId, displayName, isVerified];
}
```

- [ ] **Step 11.2 — Health ID repository**

Create `apps/mobile-patient/lib/features/health_id/data/health_id_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/health_id_card.dart';

class HealthIdRepository {
  const HealthIdRepository(this._client);
  final ApiClient _client;

  Future<HealthIdCard> fetchCard() async {
    final res = await _client.get(ApiEndpoints.healthIdCard);
    return HealthIdCard.fromJson(res);
  }
}
```

- [ ] **Step 11.3 — Health ID provider**

Create `apps/mobile-patient/lib/features/health_id/providers/health_id_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/health_id_repository.dart';
import '../models/health_id_card.dart';

final healthIdRepositoryProvider = Provider<HealthIdRepository>(
  (ref) => HealthIdRepository(ref.watch(apiClientProvider)),
);

final healthIdCardProvider = FutureProvider<HealthIdCard>((ref) {
  return ref.watch(healthIdRepositoryProvider).fetchCard();
});
```

---

## Task 12: Home Dashboard

**Files:**
- Create: `lib/features/home/models/dashboard_summary.dart`
- Create: `lib/features/home/data/home_repository.dart`
- Create: `lib/features/home/providers/home_provider.dart`
- Create: `lib/features/home/presentation/home_screen.dart`

- [ ] **Step 12.1 — Dashboard summary model**

Create `apps/mobile-patient/lib/features/home/models/dashboard_summary.dart`:

```dart
class DashboardSummary {
  const DashboardSummary({
    required this.patientName,
    required this.healthId,
    required this.isVerified,
    required this.pendingConsentCount,
    required this.unreadLabCount,
    required this.activeRxCount,
    required this.nextAppointmentDate,
    required this.nextAppointmentFacility,
    required this.recentAccessCount,
  });

  final String patientName;
  final String healthId;
  final bool isVerified;
  final int pendingConsentCount;
  final int unreadLabCount;
  final int activeRxCount;
  final String? nextAppointmentDate;
  final String? nextAppointmentFacility;
  final int recentAccessCount;
}
```

- [ ] **Step 12.2 — Home repository (parallel fetch)**

Create `apps/mobile-patient/lib/features/home/data/home_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/dashboard_summary.dart';

class HomeRepository {
  const HomeRepository(this._client);
  final ApiClient _client;

  Future<DashboardSummary> fetchSummary() async {
    // Parallel fetch for speed
    final results = await Future.wait([
      _client.get(ApiEndpoints.me),
      _client.get(ApiEndpoints.consentRequests, params: {'status': 'pending', 'per_page': '1'}),
      _client.get(ApiEndpoints.labs, params: {'status': 'released', 'per_page': '1'}),
      _client.get(ApiEndpoints.prescriptions, params: {'status': 'active', 'per_page': '1'}),
      _client.get(ApiEndpoints.appointments, params: {'upcoming': '1', 'per_page': '1'}),
      _client.get(ApiEndpoints.accessLogs, params: {'per_page': '1'}),
    ]);

    final me = results[0];
    final patient = me['data'] as Map<String, dynamic>? ?? me;

    final consentMeta = (results[1]['meta'] as Map?)??{};
    final labMeta     = (results[2]['meta'] as Map?)??{};
    final rxMeta      = (results[3]['meta'] as Map?)??{};
    final apptList    = results[4]['data'] as List? ?? [];
    final logMeta     = (results[5]['meta'] as Map?)??{};

    Map<String, dynamic>? nextAppt;
    if (apptList.isNotEmpty) nextAppt = apptList.first as Map<String, dynamic>;

    return DashboardSummary(
      patientName:             patient['display_name']?.toString() ?? 'Patient',
      healthId:                patient['health_id']?.toString() ?? '',
      isVerified:              patient['is_verified'] == true,
      pendingConsentCount:     (consentMeta['total'] ?? 0) as int,
      unreadLabCount:          (labMeta['total'] ?? 0) as int,
      activeRxCount:           (rxMeta['total'] ?? 0) as int,
      nextAppointmentDate:     nextAppt?['scheduled_at']?.toString(),
      nextAppointmentFacility: (nextAppt?['facility'] as Map?)?['name']?.toString(),
      recentAccessCount:       (logMeta['total'] ?? 0) as int,
    );
  }
}
```

- [ ] **Step 12.3 — Home provider**

Create `apps/mobile-patient/lib/features/home/providers/home_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/home_repository.dart';
import '../models/dashboard_summary.dart';

final homeRepositoryProvider = Provider<HomeRepository>(
  (ref) => HomeRepository(ref.watch(apiClientProvider)),
);

final dashboardSummaryProvider = FutureProvider<DashboardSummary>((ref) {
  return ref.watch(homeRepositoryProvider).fetchSummary();
});
```

- [ ] **Step 12.4 — Home screen UI**

Create `apps/mobile-patient/lib/features/home/presentation/home_screen.dart`:

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
import '../../../shared/widgets/section_header.dart';
import '../../../shared/widgets/status_badge.dart';
import '../../health_id/providers/health_id_provider.dart';
import '../models/dashboard_summary.dart';
import '../providers/home_provider.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final summaryAsync = ref.watch(dashboardSummaryProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            ref.invalidate(dashboardSummaryProvider);
            ref.invalidate(healthIdCardProvider);
          },
          child: summaryAsync.when(
            loading: () => const HomeScreenSkeleton(),
            error: (e, _) => ErrorView(
              message: e.toString(),
              onRetry: () => ref.invalidate(dashboardSummaryProvider),
            ),
            data: (summary) => _HomeBody(summary: summary),
          ),
        ),
      ),
    );
  }
}

class _HomeBody extends StatelessWidget {
  const _HomeBody({required this.summary});
  final DashboardSummary summary;

  @override
  Widget build(BuildContext context) {
    final greeting = _greeting();
    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      children: [
        const SizedBox(height: 16),
        // ── Header ──────────────────────────────────
        Row(children: [
          Expanded(child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(greeting, style: AppTextStyles.caption.copyWith(
                  color: AppColors.textSecondary)),
              const SizedBox(height: 2),
              Text(summary.patientName, style: AppTextStyles.h3),
            ],
          )),
          Container(
            width: 40, height: 40,
            decoration: const BoxDecoration(
              shape: BoxShape.circle, color: AppColors.primary500,
            ),
            child: Center(
              child: Text(
                summary.patientName.isNotEmpty
                    ? summary.patientName[0].toUpperCase() : 'P',
                style: AppTextStyles.h4.copyWith(color: AppColors.textOnPrimary),
              ),
            ),
          ),
        ]),
        const SizedBox(height: 16),

        // ── Health ID Card ───────────────────────────
        _HealthIdBanner(
          healthId: summary.healthId,
          isVerified: summary.isVerified,
          onTap: () => context.push(Routes.healthId),
        ),
        const SizedBox(height: 12),

        // ── Quick Stats ──────────────────────────────
        _QuickStats(summary: summary),
        const SizedBox(height: 20),

        // ── Consent Alert ────────────────────────────
        if (summary.pendingConsentCount > 0) ...[
          _ConsentAlert(count: summary.pendingConsentCount,
              onTap: () => context.push(Routes.consent)),
          const SizedBox(height: 16),
        ],

        // ── Next Appointment ─────────────────────────
        if (summary.nextAppointmentDate != null) ...[
          SectionHeader(
            title: 'Upcoming Appointment',
            icon: LucideIcons.calendar,
            onSeeAll: () => context.push(Routes.appointments),
          ),
          const SizedBox(height: 10),
          _AppointmentCard(
            date: summary.nextAppointmentDate!,
            facility: summary.nextAppointmentFacility ?? 'Facility',
          ),
          const SizedBox(height: 20),
        ],

        // ── Recent Access ────────────────────────────
        if (summary.recentAccessCount > 0) ...[
          SectionHeader(
            title: 'Recent Access',
            icon: LucideIcons.eye,
            onSeeAll: () => context.push(Routes.accessLogs),
          ),
          const SizedBox(height: 10),
          _AccessLogBanner(count: summary.recentAccessCount),
          const SizedBox(height: 20),
        ],

        const SizedBox(height: 32),
      ],
    );
  }

  String _greeting() {
    final hour = DateTime.now().hour;
    if (hour < 12) return 'Good morning,';
    if (hour < 17) return 'Good afternoon,';
    return 'Good evening,';
  }
}

class _HealthIdBanner extends StatelessWidget {
  const _HealthIdBanner({
    required this.healthId,
    required this.isVerified,
    required this.onTap,
  });
  final String healthId;
  final bool isVerified;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(children: [
          Expanded(child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('HEALTH ID',
                  style: AppTextStyles.label.copyWith(
                      color: AppColors.textOnPrimary.withOpacity(0.75),
                      fontSize: 10)),
              const SizedBox(height: 6),
              Text(healthId, style: AppTextStyles.healthId),
              const SizedBox(height: 8),
              if (isVerified)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    const Icon(LucideIcons.checkCircle,
                        size: 11, color: Colors.white),
                    const SizedBox(width: 4),
                    Text('Verified',
                        style: AppTextStyles.caption.copyWith(color: Colors.white)),
                  ]),
                ),
            ],
          )),
          const Icon(LucideIcons.idCard,
              size: 36, color: Colors.white54),
        ]),
      ),
    );
  }
}

class _QuickStats extends StatelessWidget {
  const _QuickStats({required this.summary});
  final DashboardSummary summary;

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      _StatCard(
        icon: LucideIcons.flaskConical,
        label: 'Lab Results',
        value: summary.unreadLabCount.toString(),
        color: AppColors.success,
        onTap: () => context.push(Routes.labs),
      ),
      const SizedBox(width: 8),
      _StatCard(
        icon: LucideIcons.pill,
        label: 'Active Rx',
        value: summary.activeRxCount.toString(),
        color: AppColors.primary500,
        onTap: () => context.push(Routes.prescriptions),
      ),
      const SizedBox(width: 8),
      _StatCard(
        icon: LucideIcons.shield,
        label: 'Consents',
        value: summary.pendingConsentCount.toString(),
        color: summary.pendingConsentCount > 0
            ? AppColors.warning : AppColors.neutral400,
        onTap: () => context.push(Routes.consent),
      ),
    ]);
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    required this.onTap,
  });
  final IconData icon;
  final String label, value;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.divider),
          ),
          child: Column(children: [
            Icon(icon, size: 20, color: color),
            const SizedBox(height: 6),
            Text(value,
                style: AppTextStyles.h3.copyWith(color: color, fontSize: 20)),
            const SizedBox(height: 2),
            Text(label,
                style: AppTextStyles.caption, textAlign: TextAlign.center),
          ]),
        ),
      ),
    );
  }
}

class _ConsentAlert extends StatelessWidget {
  const _ConsentAlert({required this.count, required this.onTap});
  final int count;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.warningLight,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.warning.withOpacity(0.3)),
        ),
        child: Row(children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: AppColors.warning.withOpacity(0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(LucideIcons.bell, size: 18, color: AppColors.warningDark),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('$count consent request${count > 1 ? 's' : ''} pending',
                style: AppTextStyles.body.copyWith(
                    fontWeight: FontWeight.w600, color: AppColors.warningDark)),
            Text('A facility wants to access your health records.',
                style: AppTextStyles.bodySm),
          ])),
          const Icon(LucideIcons.chevronRight,
              size: 16, color: AppColors.warningDark),
        ]),
      ),
    );
  }
}

class _AppointmentCard extends StatelessWidget {
  const _AppointmentCard({required this.date, required this.facility});
  final String date, facility;

  @override
  Widget build(BuildContext context) {
    String formatted = date;
    try {
      formatted = DateFormat('EEE, d MMM · h:mm a')
          .format(DateTime.parse(date));
    } catch (_) {}

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(children: [
        Container(
          width: 40, height: 40,
          decoration: BoxDecoration(
            color: AppColors.primary50,
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(LucideIcons.calendar,
              size: 20, color: AppColors.primary500),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(facility, style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600)),
          const SizedBox(height: 2),
          Text(formatted, style: AppTextStyles.bodySm),
        ])),
        const Icon(LucideIcons.chevronRight,
            size: 16, color: AppColors.neutral400),
      ]),
    );
  }
}

class _AccessLogBanner extends StatelessWidget {
  const _AccessLogBanner({required this.count});
  final int count;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(children: [
        Container(
          width: 40, height: 40,
          decoration: BoxDecoration(
            color: AppColors.infoLight,
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(LucideIcons.eye, size: 20, color: AppColors.info),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('$count recent access${count > 1 ? 'es' : ''}',
              style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600)),
          Text('See who accessed your health records.',
              style: AppTextStyles.bodySm),
        ])),
        const Icon(LucideIcons.chevronRight,
            size: 16, color: AppColors.neutral400),
      ]),
    );
  }
}
```

---

## Task 13: Health ID Screen

**Files:**
- Create: `lib/features/health_id/presentation/health_id_screen.dart`

- [ ] **Step 13.1 — Write Health ID screen**

Create `apps/mobile-patient/lib/features/health_id/presentation/health_id_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/status_badge.dart';
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
            onPressed: () {}, // Phase 4: share/export
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
        data: (card) => _HealthIdBody(card: card),
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
        // ── The Card ─────────────────────────────────
        Container(
          padding: const EdgeInsets.all(22),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
              begin: Alignment.topLeft, end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: AppColors.primary500.withOpacity(0.3),
                blurRadius: 20, offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              Expanded(child: Text('OpesCare',
                  style: AppTextStyles.body.copyWith(
                      color: Colors.white70, fontWeight: FontWeight.w600))),
              const Icon(LucideIcons.heart, color: Colors.white70, size: 20),
            ]),
            const SizedBox(height: 20),
            Text('HEALTH ID', style: AppTextStyles.label.copyWith(
                color: Colors.white54, fontSize: 10, letterSpacing: 1.5)),
            const SizedBox(height: 6),
            Row(children: [
              Expanded(child: Text(card.healthId, style: AppTextStyles.healthId)),
              GestureDetector(
                onTap: () {
                  Clipboard.setData(ClipboardData(text: card.healthId));
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Health ID copied')));
                },
                child: const Icon(LucideIcons.copy, color: Colors.white60, size: 18),
              ),
            ]),
            const SizedBox(height: 16),
            Row(children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('FULL NAME', style: AppTextStyles.label.copyWith(
                    color: Colors.white54, fontSize: 9)),
                const SizedBox(height: 3),
                Text(card.displayName, style: AppTextStyles.body.copyWith(
                    color: Colors.white, fontWeight: FontWeight.w600)),
              ])),
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('DATE OF BIRTH', style: AppTextStyles.label.copyWith(
                    color: Colors.white54, fontSize: 9)),
                const SizedBox(height: 3),
                Text(card.dateOfBirth, style: AppTextStyles.body.copyWith(
                    color: Colors.white, fontWeight: FontWeight.w600)),
              ]),
            ]),
            const SizedBox(height: 12),
            if (card.isVerified)
              Row(children: [
                const Icon(LucideIcons.checkCircle,
                    size: 14, color: Colors.white70),
                const SizedBox(width: 6),
                Text('Verified Identity',
                    style: AppTextStyles.caption.copyWith(color: Colors.white70)),
              ]),
          ]),
        ),
        const SizedBox(height: 24),

        // ── Clinical Summary ──────────────────────────
        _InfoSection(title: 'Clinical Summary', icon: LucideIcons.activity, rows: [
          _InfoRow('Sex', card.sex.toUpperCase()),
          _InfoRow('Blood Group', card.bloodGroup),
          if (card.allergySummary != null)
            _InfoRow('Allergies', card.allergySummary!,
                valueColor: AppColors.danger,
                icon: LucideIcons.alertTriangle),
        ]),
        const SizedBox(height: 16),

        // ── Emergency Contact ─────────────────────────
        if (card.emergencyContact != null)
          _InfoSection(title: 'Emergency Contact', icon: LucideIcons.phone, rows: [
            _InfoRow('Contact', card.emergencyContact!),
          ]),

        const SizedBox(height: 16),
        // Copy Health ID button
        OutlinedButton.icon(
          onPressed: () {
            Clipboard.setData(ClipboardData(text: card.healthId));
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Health ID copied to clipboard')));
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
  const _InfoSection({required this.title, required this.icon, required this.rows});
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
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(children: [
              Expanded(child: Text(r.label, style: AppTextStyles.bodySm)),
              if (r.icon != null)
                Padding(padding: const EdgeInsets.only(right: 6),
                    child: Icon(r.icon, size: 14, color: r.valueColor)),
              Text(r.value,
                  style: AppTextStyles.body.copyWith(
                      fontWeight: FontWeight.w600,
                      color: r.valueColor ?? AppColors.textPrimary)),
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
    return ListView(padding: const EdgeInsets.all(16), children: const [
      LoadingSkeleton(height: 180, borderRadius: 20),
      SizedBox(height: 24),
      LoadingSkeleton(height: 160, borderRadius: 12),
    ]);
  }
}
```

---

## Task 14: Main Shell (Bottom Navigation)

**Files:**
- Create: `lib/features/shell/presentation/main_shell.dart`
- Modify: `lib/core/router/app_router.dart`

- [ ] **Step 14.1 — Write main shell with Lucide bottom nav**

Create `apps/mobile-patient/lib/features/shell/presentation/main_shell.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';

class MainShell extends StatelessWidget {
  const MainShell({super.key, required this.navigationShell});
  final StatefulNavigationShell navigationShell;

  static const _tabs = [
    _TabItem(icon: LucideIcons.home,       label: 'Home',      route: Routes.home),
    _TabItem(icon: LucideIcons.idCard,     label: 'Health ID', route: Routes.healthId),
    _TabItem(icon: LucideIcons.shield,     label: 'Consent',   route: Routes.consent),
    _TabItem(icon: LucideIcons.activity,   label: 'Timeline',  route: Routes.timeline),
    _TabItem(icon: LucideIcons.settings,   label: 'Settings',  route: Routes.settings),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: navigationShell,
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: AppColors.surface,
          border: Border(top: BorderSide(color: AppColors.divider)),
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 60,
            child: Row(
              children: List.generate(_tabs.length, (i) {
                final tab = _tabs[i];
                final isSelected = navigationShell.currentIndex == i;
                return Expanded(
                  child: InkWell(
                    onTap: () => navigationShell.goBranch(i,
                        initialLocation: i == navigationShell.currentIndex),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(tab.icon,
                            size: 22,
                            color: isSelected
                                ? AppColors.primary500
                                : AppColors.neutral400),
                        const SizedBox(height: 3),
                        Text(tab.label,
                            style: AppTextStyles.caption.copyWith(
                              fontSize: 10,
                              color: isSelected
                                  ? AppColors.primary500
                                  : AppColors.neutral400,
                              fontWeight: isSelected
                                  ? FontWeight.w600
                                  : FontWeight.w400,
                            )),
                      ],
                    ),
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }
}

class _TabItem {
  const _TabItem({required this.icon, required this.label, required this.route});
  final IconData icon;
  final String label;
  final String route;
}
```

- [ ] **Step 14.2 — Update router with StatefulShellRoute**

Replace `apps/mobile-patient/lib/core/router/app_router.dart` entirely:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/otp_screen.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/health_id/presentation/health_id_screen.dart';
import '../../features/shell/presentation/main_shell.dart';

abstract final class Routes {
  static const login        = '/login';
  static const otp          = '/otp';
  static const home         = '/home';
  static const healthId     = '/health-id';
  static const consent      = '/consent';
  static const timeline     = '/timeline';
  static const labs         = '/labs';
  static const labDetail    = '/labs/:id';
  static const prescriptions     = '/prescriptions';
  static const prescriptionDetail = '/prescriptions/:id';
  static const appointments      = '/appointments';
  static const appointmentDetail = '/appointments/:id';
  static const bookAppointment   = '/appointments/book';
  static const accessLogs        = '/access-logs';
  static const documents         = '/documents';
  static const documentDetail    = '/documents/:id';
  static const settings          = '/settings';
}

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: Routes.home,
    redirect: (context, state) {
      final status = authState.status;
      final isAuth   = status == AuthStatus.authenticated;
      final isLoggingIn = state.matchedLocation == Routes.login ||
          state.matchedLocation == Routes.otp;
      if (status == AuthStatus.unknown) return null;
      if (!isAuth && !isLoggingIn) return Routes.login;
      if (isAuth && isLoggingIn) return Routes.home;
      return null;
    },
    routes: [
      GoRoute(path: Routes.login, builder: (_, __) => const LoginScreen()),
      GoRoute(path: Routes.otp,   builder: (_, __) => const OtpScreen()),

      StatefulShellRoute.indexedStack(
        builder: (context, state, shell) => MainShell(navigationShell: shell),
        branches: [
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.home, builder: (_, __) => const HomeScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.healthId, builder: (_, __) => const HealthIdScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.consent,
                builder: (_, __) => const _PlaceholderScreen('Consent', LucideIcons.shield)),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.timeline,
                builder: (_, __) => const _PlaceholderScreen('Timeline', LucideIcons.activity)),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.settings,
                builder: (_, __) => const _PlaceholderScreen('Settings', LucideIcons.settings)),
          ]),
        ],
      ),
    ],
  );
});

// ignore_for_file: unused_import
import 'package:lucide_icons/lucide_icons.dart';

class _PlaceholderScreen extends StatelessWidget {
  const _PlaceholderScreen(this.name, this.icon);
  final String name;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(name)),
      body: Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Icon(icon, size: 48, color: AppColors.neutral300),
          const SizedBox(height: 12),
          Text('$name — coming in Phase 3/4',
              style: AppTextStyles.bodySm),
        ]),
      ),
    );
  }
}

// ignore: unused_import
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';
```

- [ ] **Step 14.3 — Analyze and run**

```bash
cd C:\laragon\www\opescare\apps\mobile-patient
flutter analyze lib/
flutter run --dart-define=API_BASE_URL=http://10.0.2.2/api
```

Expected: App launches, bottom nav shows 5 tabs (Home, Health ID, Consent, Timeline, Settings) with Lucide icons. Home shows dashboard. Health ID shows card.

- [ ] **Step 14.4 — Commit Phase 2**

```bash
cd C:\laragon\www\opescare
git add apps/mobile-patient/
git commit -m "feat(mobile): Phase 2 - shell, home dashboard, health ID screen"
```

---

## Phase 2 Complete ✓

**What you now have:**
- Bottom navigation shell with 5 tabs using Lucide icons (no emojis)
- Home dashboard: greeting, Health ID banner, quick stats (labs/rx/consents), consent alert, appointment card, access log banner
- Health ID screen: gradient card, copy Health ID, clinical summary, emergency contact
- Shared widgets: StatusBadge, SectionHeader, LoadingSkeleton, ErrorView — all using Lucide icons
- Pull-to-refresh on all screens
- Placeholder screens for tabs coming in Phase 3/4

**Next:** Proceed to `2026-05-29-patient-flutter-app-phase3.md` — Consent + Timeline + Labs + Prescriptions.
