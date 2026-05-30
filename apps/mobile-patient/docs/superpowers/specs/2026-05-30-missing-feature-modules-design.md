# Missing Feature Modules — Sub-project B

**Date:** 2026-05-30
**Scope:** Build 4 Flutter feature modules that have backend API endpoints but no screens. Fix the backend auth middleware mismatch that blocks them. Add them to the Settings screen navigation.

---

## Problem Summary

The backend has three route groups under `/api/mobile`:

| Group | Middleware | Routes |
|---|---|---|
| Primary (lines 149–225) | `auth.mobile` (custom `PatientAccessToken`) | All existing Flutter screens |
| Secondary (lines 896–909) | `auth:sanctum` ❌ wrong | care-plans, surveys, medical-records/export |

The Flutter app sends `PatientAccessToken` bearer tokens which only `auth.mobile` understands. Using `auth:sanctum` on the secondary group causes 401 errors. Additionally, the three controllers in that group call `$request->user()->patient?->id` — but `auth.mobile` doesn't set `$request->user()`, it sets `$request->attributes->get('patient_id')`.

---

## Section 1 — Backend Auth Fix

### routes/api.php (line 896)

Change:
```php
Route::prefix('mobile')->middleware('auth:sanctum')->group(function () {
```
To:
```php
Route::prefix('mobile')->middleware('auth.mobile')->group(function () {
```

### MobileCarePlanController.php

In `index()`, change:
```php
$patientId = $request->user()->patient?->id;
```
To:
```php
$patientId = $request->attributes->get('patient_id');
```

### MobileSurveyController.php

In `index()`, change:
```php
$patientId = $request->user()->patient?->id;
```
To:
```php
$patientId = $request->attributes->get('patient_id');
```

### MedicalRecordExportController.php

In both `exportPdf()` and `exportFhir()`, change:
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

---

## Section 2 — New API Endpoints (Flutter)

Add to `lib/core/api/api_endpoints.dart`:

```dart
// Care Plans
static String get carePlans       => '$_base/mobile/care-plans';
static String carePlan(String id) => '$_base/mobile/care-plans/$id';

// Surveys
static String get surveys              => '$_base/mobile/surveys';
static String survey(String id)        => '$_base/mobile/surveys/$id';
static String submitSurvey(String id)  => '$_base/mobile/surveys/$id/submit';

// Medical Record Export
static String get exportPdf   => '$_base/mobile/medical-records/export/pdf';
static String get exportFhir  => '$_base/mobile/medical-records/export/fhir';
```

Note: `ApiEndpoints.me` already exists and is used for `GET /mobile/me` — no change needed there.

---

## Section 3 — Data Models

### lib/features/profile/models/patient_profile.dart

```dart
class PatientProfile {
  const PatientProfile({
    required this.healthId,
    required this.displayName,
    required this.firstName,
    required this.lastName,
    required this.allergiesCount,
    required this.conditionsCount,
    required this.status,
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
        lastName:        json['last_name']?.toString()        ?? '',
        phone:           json['phone']?.toString(),
        email:           json['email']?.toString(),
        dob:             json['dob']?.toString(),
        sex:             json['sex']?.toString(),
        bloodGroup:      json['blood_group']?.toString(),
        status:          json['status']?.toString()           ?? 'active',
        allergiesCount:  (json['allergies_count']  as num?)?.toInt() ?? 0,
        conditionsCount: (json['conditions_count'] as num?)?.toInt() ?? 0,
      );
}
```

### lib/features/care_plans/models/care_plan.dart

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
  final int? progressPct; // only present in detail response

  factory CarePlan.fromJson(Map<String, dynamic> json) {
    // List response: top-level plan object
    // Detail response: nested under 'plan' key with 'progress_pct'
    final planJson = json.containsKey('plan')
        ? json['plan'] as Map<String, dynamic>
        : json;
    final goals = (json['goals'] as List? ??
            (planJson['goals'] as List? ?? []))
        .map((g) => CarePlanGoal.fromJson(g as Map<String, dynamic>))
        .toList();
    final interventions = (json['interventions'] as List? ??
            (planJson['interventions'] as List? ?? []))
        .map((i) => CarePlanIntervention.fromJson(i as Map<String, dynamic>))
        .toList();

    return CarePlan(
      id:            planJson['id']?.toString()       ?? '',
      title:         planJson['title']?.toString()    ?? '',
      status:        planJson['status']?.toString()   ?? 'active',
      startedAt:     planJson['started_at']?.toString(),
      goals:         goals,
      interventions: interventions,
      progressPct:   (json['progress_pct'] as num?)?.toInt(),
    );
  }
}
```

### lib/features/surveys/models/survey.dart

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
  final List<SurveyQuestion> questions; // populated in detail view

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

---

## Section 4 — Feature Module File Structure

```
lib/features/
├── profile/
│   ├── data/profile_repository.dart
│   ├── providers/profile_provider.dart
│   ├── models/patient_profile.dart
│   └── presentation/profile_screen.dart
├── care_plans/
│   ├── data/care_plans_repository.dart
│   ├── providers/care_plans_provider.dart
│   ├── models/care_plan.dart
│   └── presentation/
│       ├── care_plans_screen.dart
│       └── care_plan_detail_screen.dart
├── surveys/
│   ├── data/surveys_repository.dart
│   ├── providers/surveys_provider.dart
│   ├── models/survey.dart
│   └── presentation/
│       ├── surveys_screen.dart
│       └── survey_wizard_screen.dart
└── medical_export/
    ├── data/medical_export_repository.dart
    └── presentation/medical_export_screen.dart
```

---

## Section 5 — Screen Specifications

### ProfileScreen (`/profile`)
- AppBar: "My Profile"
- Shows: Health ID, full name, DOB, sex, blood group, phone/email if present
- Bottom summary card: "X active allergies · Y conditions"
- Status badge: Verified / Active
- Read-only. No edit capability (portal manages that).

### CarePlansScreen (`/care-plans`)
- AppBar: "Care Plans"
- `FutureProvider<List<CarePlan>>` loading/error/data states
- Empty state: "No active care plans" with clipboard icon
- Each plan card: title, status badge, goal progress bar (`progressPct` shown as linear progress + `X/N goals achieved`)
- Tap → `CarePlanDetailScreen`

### CarePlanDetailScreen (`/care-plans/:id`)
- AppBar: plan title
- Progress card: `CircularProgressIndicator` + "X% goals achieved"
- Goals section: each goal as a row with status chip (pending / in_progress / achieved)
- Interventions section: list of intervention descriptions with type badge

### SurveysScreen (`/surveys`)
- AppBar: "Health Surveys"
- Pending surveys shown with a notification-style card ("Pending · tap to start")
- Completed surveys shown greyed out with "Completed" badge
- Empty state: "No surveys pending"

### SurveyWizardScreen (`/surveys/:id`)
- AppBar: survey title + `LinearProgressIndicator` showing question index/total
- Each question rendered based on `type`:
  - `single_choice` → column of `RadioListTile`
  - `multi_choice` → column of `CheckboxListTile`
  - `text` → `TextField`
- Local state: `Map<String, dynamic> _responses` keyed by question key
- "Next" button at bottom (disabled until current question answered)
- "Back" navigates to previous question (state preserved)
- Final question: "Submit" button → `POST /mobile/surveys/{id}/submit` with `{responses: _responses}`
- Success: pop to SurveysScreen + SnackBar "Survey submitted — thank you!"

### MedicalExportScreen (`/export-records`)
- AppBar: "Export Medical Records"
- Info banner: "Generate a copy of your health records for personal use or to share with another provider."
- Five `SwitchListTile` checkboxes (all on by default):
  - Diagnoses & Conditions
  - Medications & Prescriptions
  - Lab Results
  - Immunizations
  - Vitals
- Two full-width `ElevatedButton`s:
  - "Export as PDF" → `POST exportPdf` with checked section booleans → response has `file_path` → open URL with `url_launcher`
  - "Export as FHIR R4" → `POST exportFhir` → response is FHIR bundle JSON → copy to clipboard + SnackBar "FHIR bundle copied to clipboard"
- Loading state on each button independently; error banner on failure

---

## Section 6 — Settings Screen Integration

### New "YOUR HEALTH" section (between Notifications and Privacy & Data):

```dart
_SectionTitle(icon: LucideIcons.user, title: 'Your Health'),
_Card(children: [
  _NavRow(icon: LucideIcons.circleUser, label: 'My Profile',
      onTap: () => context.push(Routes.profile)),
  const Divider(height: 1),
  _NavRow(icon: LucideIcons.clipboardList, label: 'Care Plans',
      onTap: () => context.push(Routes.carePlans)),
  const Divider(height: 1),
  _NavRow(icon: LucideIcons.clipboardCheck, label: 'Health Surveys',
      onTap: () => context.push(Routes.surveys)),
]),
```

### Privacy & Data section — add one row after "Request data export":

```dart
  const Divider(height: 1),
  _NavRow(
    icon: LucideIcons.fileDown,
    label: 'Export Medical Records',
    onTap: () => context.push(Routes.medExport),
  ),
```

---

## Section 7 — Router Updates

Add to `lib/core/router/app_router.dart`:

```dart
// New routes
static const profile    = '/profile';
static const carePlans  = '/care-plans';
static const surveys    = '/surveys';
static const medExport  = '/export-records';
```

Add GoRoutes (outside the StatefulShellRoute, alongside access-logs and documents):

```dart
GoRoute(path: Routes.profile,
    builder: (_, __) => const ProfileScreen()),

GoRoute(
  path: Routes.carePlans,
  builder: (_, __) => const CarePlansScreen(),
  routes: [
    GoRoute(path: ':id',
        builder: (_, s) => CarePlanDetailScreen(id: s.pathParameters['id']!)),
  ],
),

GoRoute(
  path: Routes.surveys,
  builder: (_, __) => const SurveysScreen(),
  routes: [
    GoRoute(path: ':id',
        builder: (_, s) => SurveyWizardScreen(id: s.pathParameters['id']!)),
  ],
),

GoRoute(path: Routes.medExport,
    builder: (_, __) => const MedicalExportScreen()),
```

---

## Section 8 — New Dependency

Add `url_launcher: ^6.3.0` to `pubspec.yaml` for opening PDF file paths.

---

## Out of Scope

- Editing care plan goals from the mobile app (read-only for patients)
- FHIR viewer/renderer (just export to clipboard)
- Offline caching of any of these features
- Push notification badge count for surveys (deferred to Sub-project C)
