# Missing Feature Modules — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build 4 missing Flutter feature modules (Profile, Care Plans, Surveys, Medical Export) that have full backend API support, fixing a backend auth middleware mismatch that currently causes 401 errors on those endpoints.

**Architecture:** Backend fix first (change `auth:sanctum` → `auth.mobile` on the secondary route group, fix 4 controller resolvers), then Flutter data layer (models → repositories → providers), then presentation layer (screens), then wiring (router + settings navigation). Each feature follows the established `model → repository → FutureProvider → screen` pattern from labs/appointments/prescriptions.

**Tech Stack:** Laravel 11 (backend fix), Flutter 3.44+, Riverpod 2.x, GoRouter 13.x, `url_launcher ^6.3.0` (new dep for PDF opening)

---

## File Map

### Backend (apps/api-laravel)
| Action | Path |
|--------|------|
| Modify | `routes/api.php` (line 896) |
| Modify | `app/Http/Controllers/Api/Mobile/MobileCarePlanController.php` |
| Modify | `app/Http/Controllers/Api/Mobile/MobileSurveyController.php` |
| Modify | `app/Http/Controllers/Api/Mobile/MedicalRecordExportController.php` |

### Flutter (apps/mobile-patient)
| Action | Path |
|--------|------|
| Modify | `lib/core/api/api_endpoints.dart` |
| Modify | `pubspec.yaml` |
| Create | `lib/features/profile/models/patient_profile.dart` |
| Create | `lib/features/profile/data/profile_repository.dart` |
| Create | `lib/features/profile/providers/profile_provider.dart` |
| Create | `lib/features/profile/presentation/profile_screen.dart` |
| Create | `lib/features/care_plans/models/care_plan.dart` |
| Create | `lib/features/care_plans/data/care_plans_repository.dart` |
| Create | `lib/features/care_plans/providers/care_plans_provider.dart` |
| Create | `lib/features/care_plans/presentation/care_plans_screen.dart` |
| Create | `lib/features/care_plans/presentation/care_plan_detail_screen.dart` |
| Create | `lib/features/surveys/models/survey.dart` |
| Create | `lib/features/surveys/data/surveys_repository.dart` |
| Create | `lib/features/surveys/providers/surveys_provider.dart` |
| Create | `lib/features/surveys/presentation/surveys_screen.dart` |
| Create | `lib/features/surveys/presentation/survey_wizard_screen.dart` |
| Create | `lib/features/medical_export/data/medical_export_repository.dart` |
| Create | `lib/features/medical_export/presentation/medical_export_screen.dart` |
| Modify | `lib/core/router/app_router.dart` |
| Modify | `lib/features/settings/presentation/settings_screen.dart` |

---

## Task 1: Fix backend auth middleware mismatch

**Files (backend):**
- Modify: `apps/api-laravel/routes/api.php`
- Modify: `apps/api-laravel/app/Http/Controllers/Api/Mobile/MobileCarePlanController.php`
- Modify: `apps/api-laravel/app/Http/Controllers/Api/Mobile/MobileSurveyController.php`
- Modify: `apps/api-laravel/app/Http/Controllers/Api/Mobile/MedicalRecordExportController.php`

**Context:** The second mobile route group (around line 896 of `api.php`) uses `auth:sanctum` but the Flutter app sends `PatientAccessToken` bearer tokens only understood by the custom `auth.mobile` middleware (`AuthenticateMobilePatient`). The controllers in that group also call `$request->user()->patient?->id` which doesn't work with `auth.mobile` — that middleware sets `$request->attributes->get('patient_id')` instead.

- [ ] **Step 1: Fix the route group middleware**

In `apps/api-laravel/routes/api.php`, find (near line 896):
```php
Route::prefix('mobile')->middleware('auth:sanctum')->group(function () {
```
Change to:
```php
Route::prefix('mobile')->middleware('auth.mobile')->group(function () {
```

- [ ] **Step 2: Fix MobileCarePlanController**

In `apps/api-laravel/app/Http/Controllers/Api/Mobile/MobileCarePlanController.php`, in the `index()` method:

Change:
```php
$patientId = $request->user()->patient?->id;

if (! $patientId) {
    return response()->json(['message' => 'No patient record linked to account.'], 404);
}
```
To:
```php
$patientId = $request->attributes->get('patient_id');

if (! $patientId) {
    return response()->json(['message' => 'No patient record linked to account.'], 404);
}
```

- [ ] **Step 3: Fix MobileSurveyController**

In `apps/api-laravel/app/Http/Controllers/Api/Mobile/MobileSurveyController.php`, in the `index()` method:

Change:
```php
$patientId = $request->user()->patient?->id;
```
To:
```php
$patientId = $request->attributes->get('patient_id');
```

- [ ] **Step 4: Fix MedicalRecordExportController — exportPdf**

In `apps/api-laravel/app/Http/Controllers/Api/Mobile/MedicalRecordExportController.php`, in `exportPdf()`:

Change:
```php
$patientId = $request->user()->patient?->id;

if (! $patientId) {
    return response()->json(['message' => 'No patient record linked to account.'], 404);
}
```
To:
```php
$patientId = $request->attributes->get('patient_id');

if (! $patientId) {
    return response()->json(['message' => 'No patient record linked to account.'], 404);
}
```

- [ ] **Step 5: Fix MedicalRecordExportController — exportFhir**

Same change in `exportFhir()`:

Change:
```php
$patientId = $request->user()->patient?->id;

if (! $patientId) {
    return response()->json(['message' => 'No patient record linked to account.'], 404);
}
```
To:
```php
$patientId = $request->attributes->get('patient_id');

if (! $patientId) {
    return response()->json(['message' => 'No patient record linked to account.'], 404);
}
```

- [ ] **Step 6: Commit backend fix**

```bash
git add apps/api-laravel/routes/api.php \
  apps/api-laravel/app/Http/Controllers/Api/Mobile/MobileCarePlanController.php \
  apps/api-laravel/app/Http/Controllers/Api/Mobile/MobileSurveyController.php \
  apps/api-laravel/app/Http/Controllers/Api/Mobile/MedicalRecordExportController.php
git commit -m "fix(api): change auth:sanctum to auth.mobile on secondary mobile route group; fix 4 controller patient resolvers"
```

---

## Task 2: Add new API endpoints + url_launcher

**Files:**
- Modify: `apps/mobile-patient/lib/core/api/api_endpoints.dart`
- Modify: `apps/mobile-patient/pubspec.yaml`

- [ ] **Step 1: Add 7 new endpoint getters to api_endpoints.dart**

In `lib/core/api/api_endpoints.dart`, add after the `offlinePolicies` line (at the end of the class, before the closing `}`):

```dart
  // Care Plans (read-only for patient)
  static String get carePlans           => '$_base/mobile/care-plans';
  static String carePlan(String id)     => '$_base/mobile/care-plans/$id';

  // Patient Surveys
  static String get surveys                   => '$_base/mobile/surveys';
  static String survey(String id)             => '$_base/mobile/surveys/$id';
  static String submitSurvey(String id)       => '$_base/mobile/surveys/$id/submit';

  // Medical Record Export
  static String get exportRecordsPdf   => '$_base/mobile/medical-records/export/pdf';
  static String get exportRecordsFhir  => '$_base/mobile/medical-records/export/fhir';
```

Note: `ApiEndpoints.me` for `GET /mobile/me` already exists and will be used by the Profile feature.

- [ ] **Step 2: Add url_launcher to pubspec.yaml**

In `apps/mobile-patient/pubspec.yaml`, under `dependencies:`, add after `connectivity_plus`:

```yaml
  url_launcher: ^6.3.0
```

- [ ] **Step 3: Install**

```powershell
cd apps/mobile-patient
flutter pub get
```

Expected: Clean install, no version conflicts.

- [ ] **Step 4: Verify analyze**

```powershell
flutter analyze lib/core/api/api_endpoints.dart
```

Expected: `No issues found!`

- [ ] **Step 5: Commit**

```bash
git add apps/mobile-patient/lib/core/api/api_endpoints.dart apps/mobile-patient/pubspec.yaml apps/mobile-patient/pubspec.lock
git commit -m "feat(mobile): add care-plan, survey, export endpoints + url_launcher dep"
```

---

## Task 3: Profile feature

**Files:**
- Create: `lib/features/profile/models/patient_profile.dart`
- Create: `lib/features/profile/data/profile_repository.dart`
- Create: `lib/features/profile/providers/profile_provider.dart`
- Create: `lib/features/profile/presentation/profile_screen.dart`

- [ ] **Step 1: Create PatientProfile model**

Create `lib/features/profile/models/patient_profile.dart`:

```dart
class PatientProfile {
  const PatientProfile({
    required this.healthId,
    required this.displayName,
    required this.firstName,
    required this.lastName,
    required this.status,
    required this.allergiesCount,
    required this.conditionsCount,
    this.phone,
    this.email,
    this.dob,
    this.sex,
    this.bloodGroup,
  });

  final String healthId, displayName, firstName, lastName, status;
  final String? phone, email, dob, sex, bloodGroup;
  final int allergiesCount, conditionsCount;

  factory PatientProfile.fromJson(Map<String, dynamic> json) => PatientProfile(
        healthId:        json['health_id']?.toString()       ?? '',
        displayName:     json['display_name']?.toString()    ?? '',
        firstName:       json['first_name']?.toString()      ?? '',
        lastName:        json['last_name']?.toString()       ?? '',
        status:          json['status']?.toString()          ?? 'active',
        phone:           json['phone']?.toString(),
        email:           json['email']?.toString(),
        dob:             json['dob']?.toString(),
        sex:             json['sex']?.toString(),
        bloodGroup:      json['blood_group']?.toString(),
        allergiesCount:  (json['allergies_count']  as num?)?.toInt() ?? 0,
        conditionsCount: (json['conditions_count'] as num?)?.toInt() ?? 0,
      );
}
```

- [ ] **Step 2: Create ProfileRepository**

Create `lib/features/profile/data/profile_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/patient_profile.dart';

class ProfileRepository {
  const ProfileRepository(this._client);
  final ApiClient _client;

  Future<PatientProfile> fetch() async {
    final res = await _client.get(ApiEndpoints.me);
    return PatientProfile.fromJson(res);
  }
}
```

- [ ] **Step 3: Create profileProvider**

Create `lib/features/profile/providers/profile_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/profile_repository.dart';
import '../models/patient_profile.dart';

final profileRepositoryProvider = Provider<ProfileRepository>(
  (ref) => ProfileRepository(ref.watch(apiClientProvider)),
);

final profileProvider = FutureProvider<PatientProfile>((ref) =>
    ref.watch(profileRepositoryProvider).fetch());
```

- [ ] **Step 4: Create ProfileScreen**

Create `lib/features/profile/presentation/profile_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/patient_profile.dart';
import '../providers/profile_provider.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileAsync = ref.watch(profileProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('My Profile')),
      body: profileAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 120, borderRadius: 12),
          SizedBox(height: 16),
          LoadingSkeleton(height: 200, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(profileProvider),
        ),
        data: (profile) => _ProfileBody(profile: profile),
      ),
    );
  }
}

class _ProfileBody extends StatelessWidget {
  const _ProfileBody({required this.profile});
  final PatientProfile profile;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Avatar + name card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
              begin: Alignment.topLeft, end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(children: [
            Container(
              width: 56, height: 56,
              decoration: const BoxDecoration(
                color: AppColors.whiteOverlay, shape: BoxShape.circle,
              ),
              child: Center(
                child: Text(
                  profile.firstName.isNotEmpty
                      ? profile.firstName[0].toUpperCase()
                      : 'P',
                  style: AppTextStyles.h2.copyWith(color: Colors.white),
                ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(profile.displayName,
                    style: AppTextStyles.h3.copyWith(color: Colors.white)),
                const SizedBox(height: 4),
                Text(profile.healthId,
                    style: AppTextStyles.caption
                        .copyWith(color: Colors.white70)),
                const SizedBox(height: 6),
                _StatusChip(status: profile.status),
              ]),
            ),
          ]),
        ),
        const SizedBox(height: 20),

        // Demographics
        _InfoSection(title: 'Personal Information', icon: LucideIcons.user, rows: [
          if (profile.dob != null)    _Row('Date of Birth', profile.dob!),
          if (profile.sex != null)    _Row('Sex', profile.sex!.toUpperCase()),
          if (profile.bloodGroup != null) _Row('Blood Group', profile.bloodGroup!),
          if (profile.phone != null)  _Row('Phone', profile.phone!),
          if (profile.email != null)  _Row('Email', profile.email!),
        ]),
        const SizedBox(height: 16),

        // Health summary
        _InfoSection(title: 'Health Summary', icon: LucideIcons.activity, rows: [
          _Row('Active Allergies', '${profile.allergiesCount}'),
          _Row('Active Conditions', '${profile.conditionsCount}'),
        ]),
        const SizedBox(height: 32),
      ],
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.status});
  final String status;

  @override
  Widget build(BuildContext context) {
    final isActive = status == 'active';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.whiteOverlay,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(isActive ? LucideIcons.checkCircle : LucideIcons.alertCircle,
            size: 11, color: Colors.white),
        const SizedBox(width: 4),
        Text(isActive ? 'Active' : status.toUpperCase(),
            style: AppTextStyles.caption.copyWith(color: Colors.white)),
      ]),
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
  final List<_Row> rows;

  @override
  Widget build(BuildContext context) {
    if (rows.isEmpty) return const SizedBox.shrink();
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
              Text(r.value,
                  style: AppTextStyles.body
                      .copyWith(fontWeight: FontWeight.w600)),
            ]),
          ),
          if (r != rows.last) const Divider(height: 1, indent: 14),
        ])),
      ]),
    );
  }
}

class _Row {
  const _Row(this.label, this.value);
  final String label, value;
}
```

- [ ] **Step 5: Verify**

```powershell
flutter analyze lib/features/profile/
```
Expected: `No issues found!`

- [ ] **Step 6: Commit**

```bash
git add lib/features/profile/
git commit -m "feat(mobile): Patient Profile feature — model, repository, provider, screen"
```

---

## Task 4: Care Plans feature

**Files:**
- Create: `lib/features/care_plans/models/care_plan.dart`
- Create: `lib/features/care_plans/data/care_plans_repository.dart`
- Create: `lib/features/care_plans/providers/care_plans_provider.dart`
- Create: `lib/features/care_plans/presentation/care_plans_screen.dart`
- Create: `lib/features/care_plans/presentation/care_plan_detail_screen.dart`

- [ ] **Step 1: Create CarePlan models**

Create `lib/features/care_plans/models/care_plan.dart`:

```dart
class CarePlanGoal {
  const CarePlanGoal({
    required this.id,
    required this.description,
    required this.status,
    this.achievedAt,
  });
  final String id, description, status;
  final String? achievedAt;

  factory CarePlanGoal.fromJson(Map<String, dynamic> json) => CarePlanGoal(
        id:          json['id']?.toString()          ?? '',
        description: json['description']?.toString() ?? '',
        status:      json['status']?.toString()      ?? 'pending',
        achievedAt:  json['achieved_at']?.toString(),
      );
}

class CarePlanIntervention {
  const CarePlanIntervention({
    required this.id,
    required this.description,
    required this.type,
  });
  final String id, description, type;

  factory CarePlanIntervention.fromJson(Map<String, dynamic> json) =>
      CarePlanIntervention(
        id:          json['id']?.toString()          ?? '',
        description: json['description']?.toString() ?? '',
        type:        json['type']?.toString()        ?? 'general',
      );
}

class CarePlan {
  const CarePlan({
    required this.id,
    required this.title,
    required this.status,
    required this.goals,
    required this.interventions,
    this.startedAt,
    this.progressPct,
  });
  final String id, title, status;
  final String? startedAt;
  final List<CarePlanGoal> goals;
  final List<CarePlanIntervention> interventions;
  final int? progressPct; // populated only in detail response

  factory CarePlan.fromJson(Map<String, dynamic> json) {
    // List response: flat plan object with nested goals/interventions
    // Detail response: { plan: {...}, goals: [...], interventions: [...], progress_pct: N }
    final planJson = json.containsKey('plan')
        ? (json['plan'] as Map<String, dynamic>)
        : json;
    final goalsList = json['goals'] as List? ??
        planJson['goals'] as List? ?? [];
    final interventionsList = json['interventions'] as List? ??
        planJson['interventions'] as List? ?? [];

    return CarePlan(
      id:            planJson['id']?.toString()        ?? '',
      title:         planJson['title']?.toString()     ?? 'Care Plan',
      status:        planJson['status']?.toString()    ?? 'active',
      startedAt:     planJson['started_at']?.toString(),
      goals:         goalsList
          .map((g) => CarePlanGoal.fromJson(g as Map<String, dynamic>))
          .toList(),
      interventions: interventionsList
          .map((i) => CarePlanIntervention.fromJson(i as Map<String, dynamic>))
          .toList(),
      progressPct: (json['progress_pct'] as num?)?.toInt(),
    );
  }
}
```

- [ ] **Step 2: Create CarePlansRepository**

Create `lib/features/care_plans/data/care_plans_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/care_plan.dart';

class CarePlansRepository {
  const CarePlansRepository(this._client);
  final ApiClient _client;

  Future<List<CarePlan>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.carePlans);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => CarePlan.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<CarePlan> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.carePlan(id));
    final data = res['data'] as Map<String, dynamic>? ?? res;
    return CarePlan.fromJson(data);
  }
}
```

- [ ] **Step 3: Create care plans providers**

Create `lib/features/care_plans/providers/care_plans_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/care_plans_repository.dart';
import '../models/care_plan.dart';

final carePlansRepositoryProvider = Provider<CarePlansRepository>(
  (ref) => CarePlansRepository(ref.watch(apiClientProvider)),
);

final carePlansListProvider = FutureProvider<List<CarePlan>>((ref) =>
    ref.watch(carePlansRepositoryProvider).fetchAll());

final carePlanDetailProvider =
    FutureProvider.family<CarePlan, String>((ref, id) =>
        ref.watch(carePlansRepositoryProvider).fetchOne(id));
```

- [ ] **Step 4: Create CarePlansScreen**

Create `lib/features/care_plans/presentation/care_plans_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/care_plan.dart';
import '../providers/care_plans_provider.dart';

class CarePlansScreen extends ConsumerWidget {
  const CarePlansScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final plansAsync = ref.watch(carePlansListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Care Plans')),
      body: plansAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 100, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 100, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(carePlansListProvider),
        ),
        data: (plans) {
          if (plans.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(LucideIcons.clipboardList,
                      size: 48, color: AppColors.neutral300),
                  const SizedBox(height: 12),
                  Text('No active care plans',
                      style: AppTextStyles.body),
                  const SizedBox(height: 6),
                  Text('Your care team will assign a plan when needed.',
                      style: AppTextStyles.bodySm,
                      textAlign: TextAlign.center),
                ]),
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(carePlansListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: plans.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _PlanCard(plan: plans[i]),
            ),
          );
        },
      ),
    );
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan});
  final CarePlan plan;

  @override
  Widget build(BuildContext context) {
    final achieved = plan.goals.where((g) => g.status == 'achieved').length;
    final total    = plan.goals.length;
    final progress = total > 0 ? achieved / total : 0.0;

    return GestureDetector(
      onTap: () => context.push('${Routes.carePlans}/${plan.id}'),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(
              child: Text(plan.title,
                  style: AppTextStyles.body
                      .copyWith(fontWeight: FontWeight.w600)),
            ),
            const Icon(LucideIcons.chevronRight,
                size: 16, color: AppColors.neutral400),
          ]),
          const SizedBox(height: 8),
          if (total > 0) ...[
            Row(children: [
              Expanded(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: progress,
                    backgroundColor: AppColors.neutral100,
                    color: AppColors.primary500,
                    minHeight: 6,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Text('$achieved/$total goals',
                  style: AppTextStyles.caption),
            ]),
          ] else
            Text('No goals defined', style: AppTextStyles.caption),
        ]),
      ),
    );
  }
}
```

- [ ] **Step 5: Create CarePlanDetailScreen**

Create `lib/features/care_plans/presentation/care_plan_detail_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../providers/care_plans_provider.dart';

class CarePlanDetailScreen extends ConsumerWidget {
  const CarePlanDetailScreen({super.key, required this.id});
  final String id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final planAsync = ref.watch(carePlanDetailProvider(id));
    return Scaffold(
      appBar: AppBar(title: const Text('Care Plan')),
      body: planAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 80, borderRadius: 12),
          SizedBox(height: 16),
          LoadingSkeleton(height: 160, borderRadius: 12),
          SizedBox(height: 16),
          LoadingSkeleton(height: 120, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(carePlanDetailProvider(id)),
        ),
        data: (plan) {
          final achieved = plan.goals.where((g) => g.status == 'achieved').length;
          final total    = plan.goals.length;
          final pct      = plan.progressPct ??
              (total > 0 ? ((achieved / total) * 100).round() : 0);

          return ListView(padding: const EdgeInsets.all(16), children: [
            // Progress card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              child: Column(children: [
                Text(plan.title,
                    style: AppTextStyles.h4,
                    textAlign: TextAlign.center),
                const SizedBox(height: 12),
                SizedBox(
                  width: 80, height: 80,
                  child: Stack(alignment: Alignment.center, children: [
                    CircularProgressIndicator(
                      value: pct / 100,
                      backgroundColor: AppColors.neutral100,
                      color: AppColors.primary500,
                      strokeWidth: 8,
                    ),
                    Text('$pct%',
                        style: AppTextStyles.h3
                            .copyWith(color: AppColors.primary500)),
                  ]),
                ),
                const SizedBox(height: 8),
                Text('$achieved of $total goals achieved',
                    style: AppTextStyles.bodySm),
              ]),
            ),
            const SizedBox(height: 20),

            // Goals
            if (plan.goals.isNotEmpty) ...[
              Row(children: [
                const Icon(LucideIcons.target,
                    size: 15, color: AppColors.primary500),
                const SizedBox(width: 6),
                Text('GOALS', style: AppTextStyles.label),
              ]),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Column(
                  children: plan.goals.map((goal) {
                    final isAchieved = goal.status == 'achieved';
                    return Column(children: [
                      Padding(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 14, vertical: 12),
                        child: Row(children: [
                          Icon(
                            isAchieved
                                ? LucideIcons.checkCircle
                                : LucideIcons.circle,
                            size: 16,
                            color: isAchieved
                                ? AppColors.success
                                : AppColors.neutral400,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(goal.description,
                                style: AppTextStyles.body),
                          ),
                        ]),
                      ),
                      if (goal != plan.goals.last)
                        const Divider(height: 1, indent: 40),
                    ]);
                  }).toList(),
                ),
              ),
              const SizedBox(height: 20),
            ],

            // Interventions
            if (plan.interventions.isNotEmpty) ...[
              Row(children: [
                const Icon(LucideIcons.stethoscope,
                    size: 15, color: AppColors.primary500),
                const SizedBox(width: 6),
                Text('INTERVENTIONS', style: AppTextStyles.label),
              ]),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Column(
                  children: plan.interventions.map((iv) => Column(children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 12),
                      child: Row(children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.primary50,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(iv.type.toUpperCase(),
                              style: AppTextStyles.caption.copyWith(
                                  color: AppColors.primary500,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 9)),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                            child: Text(iv.description,
                                style: AppTextStyles.body)),
                      ]),
                    ),
                    if (iv != plan.interventions.last)
                      const Divider(height: 1, indent: 14),
                  ])).toList(),
                ),
              ),
            ],
            const SizedBox(height: 32),
          ]);
        },
      ),
    );
  }
}
```

- [ ] **Step 6: Verify**

```powershell
flutter analyze lib/features/care_plans/
```
Expected: `No issues found!`

- [ ] **Step 7: Commit**

```bash
git add lib/features/care_plans/
git commit -m "feat(mobile): Care Plans feature — models, repository, provider, list + detail screens"
```

---

## Task 5: Surveys feature

**Files:**
- Create: `lib/features/surveys/models/survey.dart`
- Create: `lib/features/surveys/data/surveys_repository.dart`
- Create: `lib/features/surveys/providers/surveys_provider.dart`
- Create: `lib/features/surveys/presentation/surveys_screen.dart`
- Create: `lib/features/surveys/presentation/survey_wizard_screen.dart`

- [ ] **Step 1: Create Survey models**

Create `lib/features/surveys/models/survey.dart`:

```dart
class SurveyQuestion {
  const SurveyQuestion({
    required this.key,
    required this.text,
    required this.type,
    this.options = const [],
  });
  final String key, text, type; // type: single_choice | multi_choice | text
  final List<String> options;

  factory SurveyQuestion.fromJson(Map<String, dynamic> json) => SurveyQuestion(
        key:     json['key']?.toString()  ?? '',
        text:    json['text']?.toString() ?? '',
        type:    json['type']?.toString() ?? 'text',
        options: (json['options'] as List? ?? [])
            .map((o) => o.toString())
            .toList(),
      );
}

class Survey {
  const Survey({
    required this.id,
    required this.title,
    required this.status,
    required this.templateKey,
    this.questions = const [],
  });
  final String id, title, status, templateKey;
  final List<SurveyQuestion> questions; // populated only in detail

  factory Survey.fromJson(Map<String, dynamic> json) {
    final template = json['template'];
    final questions = (template != null && template['questions'] is List)
        ? (template['questions'] as List)
            .map((q) => SurveyQuestion.fromJson(q as Map<String, dynamic>))
            .toList()
        : <SurveyQuestion>[];
    return Survey(
      id:          json['id']?.toString()           ?? '',
      title:       json['title']?.toString()        ?? 'Health Survey',
      status:      json['status']?.toString()       ?? 'sent',
      templateKey: json['template_key']?.toString() ?? '',
      questions:   questions,
    );
  }
}
```

- [ ] **Step 2: Create SurveysRepository**

Create `lib/features/surveys/data/surveys_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/survey.dart';

class SurveysRepository {
  const SurveysRepository(this._client);
  final ApiClient _client;

  Future<List<Survey>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.surveys);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => Survey.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<Survey> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.survey(id));
    // Response shape: { data: <survey>, template: { questions: [...] } }
    final surveyJson = (res['data'] as Map<String, dynamic>? ?? {})
      ..['template'] = res['template'];
    return Survey.fromJson(surveyJson);
  }

  Future<void> submit(String id, Map<String, dynamic> responses) =>
      _client.post(ApiEndpoints.submitSurvey(id),
          body: {'responses': responses});
}
```

- [ ] **Step 3: Create survey providers**

Create `lib/features/surveys/providers/surveys_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/surveys_repository.dart';
import '../models/survey.dart';

final surveysRepositoryProvider = Provider<SurveysRepository>(
  (ref) => SurveysRepository(ref.watch(apiClientProvider)),
);

final surveysListProvider = FutureProvider<List<Survey>>((ref) =>
    ref.watch(surveysRepositoryProvider).fetchAll());

final surveyDetailProvider =
    FutureProvider.family<Survey, String>((ref, id) =>
        ref.watch(surveysRepositoryProvider).fetchOne(id));
```

- [ ] **Step 4: Create SurveysScreen**

Create `lib/features/surveys/presentation/surveys_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/survey.dart';
import '../providers/surveys_provider.dart';

class SurveysScreen extends ConsumerWidget {
  const SurveysScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final surveysAsync = ref.watch(surveysListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Health Surveys')),
      body: surveysAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 80, borderRadius: 12),
          SizedBox(height: 10),
          LoadingSkeleton(height: 80, borderRadius: 12),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(surveysListProvider),
        ),
        data: (surveys) {
          if (surveys.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(LucideIcons.clipboardCheck,
                      size: 48, color: AppColors.neutral300),
                  const SizedBox(height: 12),
                  Text('No surveys pending', style: AppTextStyles.body),
                  const SizedBox(height: 6),
                  Text('Check back later for health surveys from your provider.',
                      style: AppTextStyles.bodySm,
                      textAlign: TextAlign.center),
                ]),
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(surveysListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: surveys.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (_, i) => _SurveyCard(survey: surveys[i]),
            ),
          );
        },
      ),
    );
  }
}

class _SurveyCard extends StatelessWidget {
  const _SurveyCard({required this.survey});
  final Survey survey;

  @override
  Widget build(BuildContext context) {
    final isPending   = survey.status == 'sent';
    final isCompleted = survey.status == 'completed';

    return GestureDetector(
      onTap: isPending
          ? () => context.push('${Routes.surveys}/${survey.id}')
          : null,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isPending ? AppColors.primarySurface : AppColors.divider,
          ),
        ),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: isPending ? AppColors.primary50 : AppColors.neutral100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              isPending ? LucideIcons.clipboardList : LucideIcons.clipboardCheck,
              size: 20,
              color: isPending ? AppColors.primary500 : AppColors.neutral400,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(survey.title,
                  style: AppTextStyles.body
                      .copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: 3),
              Text(
                isPending ? 'Pending · Tap to start' : 'Completed',
                style: AppTextStyles.bodySm.copyWith(
                    color: isPending
                        ? AppColors.primary500
                        : AppColors.textSecondary),
              ),
            ]),
          ),
          if (isPending)
            const Icon(LucideIcons.chevronRight,
                size: 16, color: AppColors.primary500),
          if (isCompleted)
            const Icon(LucideIcons.checkCircle,
                size: 18, color: AppColors.success),
        ]),
      ),
    );
  }
}
```

- [ ] **Step 5: Create SurveyWizardScreen**

Create `lib/features/surveys/presentation/survey_wizard_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../models/survey.dart';
import '../providers/surveys_provider.dart';

class SurveyWizardScreen extends ConsumerStatefulWidget {
  const SurveyWizardScreen({super.key, required this.id});
  final String id;

  @override
  ConsumerState<SurveyWizardScreen> createState() => _SurveyWizardState();
}

class _SurveyWizardState extends ConsumerState<SurveyWizardScreen> {
  int _currentIndex = 0;
  final Map<String, dynamic> _responses = {};
  bool _isSubmitting = false;
  String? _submitError;

  bool get _currentAnswered {
    // Always allow "Next" for text questions (empty string is valid)
    return true;
  }

  @override
  Widget build(BuildContext context) {
    final surveyAsync = ref.watch(surveyDetailProvider(widget.id));
    return Scaffold(
      body: surveyAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Scaffold(
          appBar: AppBar(),
          body: ErrorView(
            message: e.toString(),
            onRetry: () => ref.invalidate(surveyDetailProvider(widget.id)),
          ),
        ),
        data: (survey) => _buildWizard(context, survey),
      ),
    );
  }

  Widget _buildWizard(BuildContext context, Survey survey) {
    final questions = survey.questions;
    if (questions.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(survey.title)),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.alertCircle,
                  size: 48, color: AppColors.neutral300),
              const SizedBox(height: 12),
              Text('This survey has no questions.',
                  style: AppTextStyles.body),
            ]),
          ),
        ),
      );
    }

    final q   = questions[_currentIndex];
    final isLast = _currentIndex == questions.length - 1;
    final progress = (_currentIndex + 1) / questions.length;

    return Scaffold(
      appBar: AppBar(
        title: Text(survey.title),
        leading: IconButton(
          icon: const Icon(LucideIcons.arrowLeft),
          onPressed: () {
            if (_currentIndex == 0) {
              context.pop();
            } else {
              setState(() => _currentIndex--);
            }
          },
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(4),
          child: LinearProgressIndicator(
            value: progress,
            backgroundColor: AppColors.neutral100,
            color: AppColors.primary500,
            minHeight: 4,
          ),
        ),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(
            'Question ${_currentIndex + 1} of ${questions.length}',
            style: AppTextStyles.caption,
          ),
          const SizedBox(height: 8),
          Text(q.text, style: AppTextStyles.h4),
          const SizedBox(height: 20),
          Expanded(child: _buildQuestionInput(q)),

          if (_submitError != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.dangerLight,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_submitError!,
                  style: AppTextStyles.bodySm
                      .copyWith(color: AppColors.danger)),
            ),
            const SizedBox(height: 12),
          ],

          ElevatedButton(
            onPressed: _isSubmitting ? null : () => _handleNext(survey, isLast),
            child: _isSubmitting
                ? const SizedBox(
                    height: 20, width: 20,
                    child: CircularProgressIndicator(
                        color: Colors.white, strokeWidth: 2))
                : Text(isLast ? 'Submit' : 'Next'),
          ),
          const SizedBox(height: 8),
        ]),
      ),
    );
  }

  Widget _buildQuestionInput(SurveyQuestion q) {
    if (q.type == 'text') {
      return TextFormField(
        maxLines: 4,
        onChanged: (v) => _responses[q.key] = v,
        initialValue: _responses[q.key] as String?,
        style: AppTextStyles.body,
        decoration: const InputDecoration(
          hintText: 'Your answer...',
        ),
      );
    }

    if (q.type == 'single_choice') {
      return ListView(
        children: q.options.map((opt) {
          final selected = _responses[q.key] == opt;
          return RadioListTile<String>(
            value: opt,
            groupValue: _responses[q.key] as String?,
            onChanged: (v) => setState(() => _responses[q.key] = v),
            title: Text(opt, style: AppTextStyles.body),
            activeColor: AppColors.primary500,
            contentPadding: EdgeInsets.zero,
          );
        }).toList(),
      );
    }

    // multi_choice
    final selected = (_responses[q.key] as List<String>?) ?? [];
    return ListView(
      children: q.options.map((opt) {
        final isChecked = selected.contains(opt);
        return CheckboxListTile(
          value: isChecked,
          onChanged: (v) {
            final updated = List<String>.from(selected);
            if (v == true) updated.add(opt); else updated.remove(opt);
            setState(() => _responses[q.key] = updated);
          },
          title: Text(opt, style: AppTextStyles.body),
          activeColor: AppColors.primary500,
          contentPadding: EdgeInsets.zero,
        );
      }).toList(),
    );
  }

  Future<void> _handleNext(Survey survey, bool isLast) async {
    if (!isLast) {
      setState(() => _currentIndex++);
      return;
    }
    setState(() { _isSubmitting = true; _submitError = null; });
    try {
      await ref.read(surveysRepositoryProvider).submit(widget.id, _responses);
      ref.invalidate(surveysListProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Survey submitted — thank you!')),
        );
        context.pop();
      }
    } catch (e) {
      setState(() {
        _isSubmitting = false;
        _submitError = 'Submission failed. Please try again.';
      });
    }
  }
}
```

- [ ] **Step 6: Verify**

```powershell
flutter analyze lib/features/surveys/
```
Expected: `No issues found!`

- [ ] **Step 7: Commit**

```bash
git add lib/features/surveys/
git commit -m "feat(mobile): Surveys feature — models, repository, provider, list screen, one-per-question wizard"
```

---

## Task 6: Medical Export feature

**Files:**
- Create: `lib/features/medical_export/data/medical_export_repository.dart`
- Create: `lib/features/medical_export/presentation/medical_export_screen.dart`

- [ ] **Step 1: Create MedicalExportRepository**

Create `lib/features/medical_export/data/medical_export_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';

class MedicalExportRepository {
  const MedicalExportRepository(this._client);
  final ApiClient _client;

  /// Returns the file_path and filename for the generated PDF.
  Future<Map<String, String>> exportPdf({
    bool includeVitals        = true,
    bool includeDiagnoses     = true,
    bool includeMedications   = true,
    bool includeLabs          = true,
    bool includeImmunizations = true,
  }) async {
    final res = await _client.post(ApiEndpoints.exportRecordsPdf, body: {
      'include_vitals':        includeVitals,
      'include_diagnoses':     includeDiagnoses,
      'include_medications':   includeMedications,
      'include_labs':          includeLabs,
      'include_immunizations': includeImmunizations,
    });
    return {
      'file_path': res['file_path']?.toString() ?? '',
      'filename':  res['filename']?.toString()  ?? 'medical_records.pdf',
    };
  }

  /// Returns the raw FHIR R4 Bundle as a JSON string.
  Future<String> exportFhir() async {
    final res = await _client.post(ApiEndpoints.exportRecordsFhir);
    // res is the FHIR bundle map — serialise it for clipboard
    return res.toString();
  }
}
```

- [ ] **Step 2: Create MedicalExportScreen**

Create `lib/features/medical_export/presentation/medical_export_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/api/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../data/medical_export_repository.dart';

final _medicalExportRepositoryProvider = Provider<MedicalExportRepository>(
  (ref) => MedicalExportRepository(ref.watch(apiClientProvider)),
);

class MedicalExportScreen extends ConsumerStatefulWidget {
  const MedicalExportScreen({super.key});

  @override
  ConsumerState<MedicalExportScreen> createState() =>
      _MedicalExportScreenState();
}

class _MedicalExportScreenState extends ConsumerState<MedicalExportScreen> {
  bool _vitals        = true;
  bool _diagnoses     = true;
  bool _medications   = true;
  bool _labs          = true;
  bool _immunizations = true;

  bool _pdfLoading  = false;
  bool _fhirLoading = false;
  String? _error;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Export Medical Records')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Info banner
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.infoLight,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(children: [
              const Icon(LucideIcons.info, size: 16, color: AppColors.info),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Generate a copy of your health records for personal use or to share with another provider.',
                  style: AppTextStyles.bodySm,
                ),
              ),
            ]),
          ),
          const SizedBox(height: 20),

          Text('INCLUDE IN EXPORT', style: AppTextStyles.label),
          const SizedBox(height: 8),

          Container(
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.divider),
            ),
            child: Column(children: [
              _SectionSwitch('Diagnoses & Conditions', _diagnoses,
                  (v) => setState(() => _diagnoses = v)),
              const Divider(height: 1),
              _SectionSwitch('Medications & Prescriptions', _medications,
                  (v) => setState(() => _medications = v)),
              const Divider(height: 1),
              _SectionSwitch('Lab Results', _labs,
                  (v) => setState(() => _labs = v)),
              const Divider(height: 1),
              _SectionSwitch('Immunizations', _immunizations,
                  (v) => setState(() => _immunizations = v)),
              const Divider(height: 1),
              _SectionSwitch('Vitals', _vitals,
                  (v) => setState(() => _vitals = v)),
            ]),
          ),
          const SizedBox(height: 24),

          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.dangerLight,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_error!,
                  style: AppTextStyles.bodySm
                      .copyWith(color: AppColors.danger)),
            ),
            const SizedBox(height: 16),
          ],

          ElevatedButton.icon(
            onPressed: _pdfLoading ? null : _exportPdf,
            icon: _pdfLoading
                ? const SizedBox(
                    width: 18, height: 18,
                    child: CircularProgressIndicator(
                        color: Colors.white, strokeWidth: 2))
                : const Icon(LucideIcons.fileDown, size: 18),
            label: const Text('Export as PDF'),
          ),
          const SizedBox(height: 12),

          OutlinedButton.icon(
            onPressed: _fhirLoading ? null : _exportFhir,
            icon: _fhirLoading
                ? const SizedBox(
                    width: 18, height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(LucideIcons.code2, size: 18),
            label: const Text('Export as FHIR R4'),
          ),
          const SizedBox(height: 8),
          Text('FHIR R4 format — compatible with most health record systems.',
              style: AppTextStyles.caption,
              textAlign: TextAlign.center),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Future<void> _exportPdf() async {
    setState(() { _pdfLoading = true; _error = null; });
    try {
      final result = await ref.read(_medicalExportRepositoryProvider).exportPdf(
        includeVitals: _vitals,
        includeDiagnoses: _diagnoses,
        includeMedications: _medications,
        includeLabs: _labs,
        includeImmunizations: _immunizations,
      );
      final filePath = result['file_path']!;
      final uri = Uri.tryParse(filePath);
      if (uri != null && await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        setState(() => _error = 'Could not open PDF. File path: $filePath');
      }
    } catch (e) {
      setState(() => _error = 'PDF export failed. Please try again.');
    } finally {
      if (mounted) setState(() => _pdfLoading = false);
    }
  }

  Future<void> _exportFhir() async {
    setState(() { _fhirLoading = true; _error = null; });
    try {
      final json = await ref.read(_medicalExportRepositoryProvider).exportFhir();
      await Clipboard.setData(ClipboardData(text: json));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('FHIR bundle copied to clipboard.')),
        );
      }
    } catch (e) {
      setState(() => _error = 'FHIR export failed. Please try again.');
    } finally {
      if (mounted) setState(() => _fhirLoading = false);
    }
  }
}

class _SectionSwitch extends StatelessWidget {
  const _SectionSwitch(this.label, this.value, this.onChanged);
  final String label;
  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      child: Row(children: [
        Expanded(child: Text(label, style: AppTextStyles.body)),
        Switch(value: value, onChanged: onChanged,
            activeColor: AppColors.primary500),
      ]),
    );
  }
}
```

- [ ] **Step 3: Verify**

```powershell
flutter analyze lib/features/medical_export/
```
Expected: `No issues found!`

- [ ] **Step 4: Commit**

```bash
git add lib/features/medical_export/
git commit -m "feat(mobile): Medical Export feature — PDF + FHIR R4 export screen"
```

---

## Task 7: Wire routes and settings navigation

**Files:**
- Modify: `lib/core/router/app_router.dart`
- Modify: `lib/features/settings/presentation/settings_screen.dart`

- [ ] **Step 1: Add route constants to Routes class**

In `lib/core/router/app_router.dart`, find the `abstract final class Routes` block and add 4 new constants:

```dart
abstract final class Routes {
  static const login           = '/login';
  static const otp             = '/otp';
  static const home            = '/home';
  static const healthId        = '/health-id';
  static const consent         = '/consent';
  static const timeline        = '/timeline';
  static const labs            = '/labs';
  static const prescriptions   = '/prescriptions';
  static const appointments    = '/appointments';
  static const bookAppointment = '/appointments/book';
  static const accessLogs      = '/access-logs';
  static const documents       = '/documents';
  static const settings        = '/settings';
  // New routes — Sub-project B
  static const profile         = '/profile';
  static const carePlans       = '/care-plans';
  static const surveys         = '/surveys';
  static const medExport       = '/export-records';
}
```

- [ ] **Step 2: Add new imports to app_router.dart**

Add these imports at the top of `lib/core/router/app_router.dart`:

```dart
import '../../features/profile/presentation/profile_screen.dart';
import '../../features/care_plans/presentation/care_plans_screen.dart';
import '../../features/care_plans/presentation/care_plan_detail_screen.dart';
import '../../features/surveys/presentation/surveys_screen.dart';
import '../../features/surveys/presentation/survey_wizard_screen.dart';
import '../../features/medical_export/presentation/medical_export_screen.dart';
```

- [ ] **Step 3: Add GoRoutes to the router**

In the `GoRouter` routes list (outside the `StatefulShellRoute`), alongside the existing standalone routes (`accessLogs`, `documents`), add:

```dart
      GoRoute(
        path: Routes.profile,
        builder: (_, __) => const ProfileScreen(),
      ),

      GoRoute(
        path: Routes.carePlans,
        builder: (_, __) => const CarePlansScreen(),
        routes: [
          GoRoute(
            path: ':id',
            builder: (_, s) =>
                CarePlanDetailScreen(id: s.pathParameters['id']!),
          ),
        ],
      ),

      GoRoute(
        path: Routes.surveys,
        builder: (_, __) => const SurveysScreen(),
        routes: [
          GoRoute(
            path: ':id',
            builder: (_, s) =>
                SurveyWizardScreen(id: s.pathParameters['id']!),
          ),
        ],
      ),

      GoRoute(
        path: Routes.medExport,
        builder: (_, __) => const MedicalExportScreen(),
      ),
```

- [ ] **Step 4: Add "Your Health" section to SettingsScreen**

In `lib/features/settings/presentation/settings_screen.dart`, find the line:

```dart
        _SectionTitle(icon: LucideIcons.lock, title: 'Privacy & Data'),
```

Insert the following **before** it (after the `SizedBox(height: 20)` that follows the Notifications section):

```dart
        _SectionTitle(icon: LucideIcons.heartPulse, title: 'Your Health'),
        _Card(children: [
          _NavRow(
            icon: LucideIcons.userCircle,
            label: 'My Profile',
            onTap: () => context.push(Routes.profile),
          ),
          const Divider(height: 1),
          _NavRow(
            icon: LucideIcons.clipboardList,
            label: 'Care Plans',
            onTap: () => context.push(Routes.carePlans),
          ),
          const Divider(height: 1),
          _NavRow(
            icon: LucideIcons.clipboardCheck,
            label: 'Health Surveys',
            onTap: () => context.push(Routes.surveys),
          ),
        ]),
        const SizedBox(height: 20),
```

- [ ] **Step 5: Add "Export Medical Records" to Privacy & Data section**

In the same file, find the Privacy & Data `_Card`, find the correction request row:

```dart
          _NavRow(
            icon: LucideIcons.fileEdit,
            label: 'File a correction request',
            onTap: () => _showCorrectionSheet(context, ref),
          ),
```

After it, add:

```dart
          const Divider(height: 1),
          _NavRow(
            icon: LucideIcons.fileDown,
            label: 'Export Medical Records',
            onTap: () => context.push(Routes.medExport),
          ),
```

- [ ] **Step 6: Verify icons exist**

```powershell
cd apps/mobile-patient
grep -i "heartPulse\|userCircle\b" packages/lucide_icons/lib/lucide_icons.dart
```

Expected: Both `heartPulse` and `userCircle` are listed. If `heartPulse` is missing, use `LucideIcons.heart` instead.

- [ ] **Step 7: Run full analyze**

```powershell
flutter analyze lib/
```
Expected: 0 errors. Info-level const hints may remain — those are acceptable.

- [ ] **Step 8: Run tests**

```powershell
flutter test -v
```
Expected: `+10: All tests passed!`

- [ ] **Step 9: Commit**

```bash
git add lib/core/router/app_router.dart lib/features/settings/presentation/settings_screen.dart
git commit -m "feat(mobile): wire Profile/CarePlans/Surveys/MedExport routes + Settings navigation"
```

---

## Self-Review

### Spec coverage

| Spec requirement | Task |
|---|---|
| Fix `auth:sanctum` → `auth.mobile` on second route group | Task 1 Step 1 |
| Fix `MobileCarePlanController` patient resolver | Task 1 Step 2 |
| Fix `MobileSurveyController` patient resolver | Task 1 Step 3 |
| Fix `MedicalRecordExportController::exportPdf` resolver | Task 1 Step 4 |
| Fix `MedicalRecordExportController::exportFhir` resolver | Task 1 Step 5 |
| Add care-plan, survey, export endpoints to `api_endpoints.dart` | Task 2 Step 1 |
| Add `url_launcher` dependency | Task 2 Step 2 |
| `PatientProfile` model with all fields from `/me` response | Task 3 Step 1 |
| `ProfileRepository.fetch()` calling `ApiEndpoints.me` | Task 3 Step 2 |
| `profileProvider` FutureProvider | Task 3 Step 3 |
| `ProfileScreen` with demographics + health summary | Task 3 Step 4 |
| `CarePlanGoal`, `CarePlanIntervention`, `CarePlan` models | Task 4 Step 1 |
| `fromJson` handles both list and detail response shapes | Task 4 Step 1 |
| `CarePlansRepository.fetchAll/fetchOne` | Task 4 Step 2 |
| `carePlansListProvider`, `carePlanDetailProvider` | Task 4 Step 3 |
| `CarePlansScreen` with progress bar per plan | Task 4 Step 4 |
| `CarePlanDetailScreen` with circular progress, goals, interventions | Task 4 Step 5 |
| `SurveyQuestion`, `Survey` models | Task 5 Step 1 |
| `SurveysRepository.fetchAll/fetchOne/submit` | Task 5 Step 2 |
| `surveysListProvider`, `surveyDetailProvider` | Task 5 Step 3 |
| `SurveysScreen` with pending/completed state | Task 5 Step 4 |
| `SurveyWizardScreen` one-question-per-screen with 3 input types | Task 5 Step 5 |
| Submit sends `{responses: {...}}` to `POST surveys/{id}/submit` | Task 5 Step 5 (`_handleNext`) |
| `MedicalExportRepository.exportPdf/exportFhir` | Task 6 Step 1 |
| `MedicalExportScreen` with 5 toggles + PDF + FHIR buttons | Task 6 Step 2 |
| FHIR copies to clipboard | Task 6 Step 2 (`_exportFhir`) |
| PDF opens with `url_launcher` | Task 6 Step 2 (`_exportPdf`) |
| New route constants: profile, carePlans, surveys, medExport | Task 7 Step 1 |
| GoRoutes wired with nested detail routes | Task 7 Step 3 |
| "Your Health" settings section with 3 nav rows | Task 7 Step 4 |
| "Export Medical Records" in Privacy & Data section | Task 7 Step 5 |

**All spec requirements covered.**

### Type consistency
- `CarePlan.fromJson` defined in Task 4 Step 1; used in `CarePlansRepository` in Task 4 Step 2 ✓
- `Survey.fromJson` defined in Task 5 Step 1; `SurveysRepository.fetchOne` merges template correctly ✓
- `ApiEndpoints.carePlans` defined in Task 2; used in `CarePlansRepository` in Task 4 ✓
- `ApiEndpoints.exportRecordsPdf` defined in Task 2; used in `MedicalExportRepository` in Task 6 ✓
- `Routes.profile/carePlans/surveys/medExport` defined in Task 7 Step 1; screens reference them in Task 7 Steps 3–5 ✓
- `surveysRepositoryProvider` defined in Task 5 Step 3; used in `SurveyWizardScreen` Step 5 ✓
- `_medicalExportRepositoryProvider` defined inline in `medical_export_screen.dart` Task 6 Step 2 ✓
