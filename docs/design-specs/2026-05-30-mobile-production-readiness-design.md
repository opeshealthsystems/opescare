# OpesCare Patient App — Production Readiness Design

**Date:** 2026-05-30  
**App:** `apps/mobile-patient` (Flutter / Riverpod / GoRouter)  
**Targets:** Android Play Store, iOS App Store, Web (hosted), Android APK sideload  
**Scope:** Take the app from Phase 4 complete (15 screens, all routes wired) to 100% production-ready across all platforms.

---

## Current State

The app has completed four build phases:
- Auth (email+password primary, phone+OTP legacy)
- Home dashboard, Health ID, Consent, Timeline
- Labs + detail, Prescriptions + detail
- Appointments + detail, Access Logs, Documents, Settings

**Known gaps before production:**
- App ID is `com.example.opescare_patient` (must change)
- Android release build uses debug signing keys
- Push notifications: API endpoints defined, Flutter side not implemented
- Appointment booking: route `/appointments/book` exists, no screen
- Settings stubs: "Request data export" and "File correction request" are empty `onTap: () {}`
- Widget test references non-existent `MyApp` class
- No app icon or splash screen configured
- No environment config for staging vs. production
- No crash reporting or analytics
- No token refresh (401 surfaces as error, no retry)
- No offline/no-network UX
- No dark mode theme
- No accessibility semantics

---

## Phase 1 — Foundation

**Goal:** Every build artifact has the correct production identity, signing, icons, and env config.

### App Identity
- `applicationId`: `com.example.opescare_patient` → `com.opescare.patient` in `android/app/build.gradle.kts`
- iOS bundle ID: `com.example.opescare-patient` → `com.opescare.patient` — change via Xcode: open `ios/Runner.xcworkspace`, select Runner target → General → Bundle Identifier (do not edit `project.pbxproj` directly to avoid corruption)
- `pubspec.yaml` version stays `1.0.0+1` for initial release; bump build number for each subsequent upload

### Android Release Signing
- Generate `opescare-release.jks` keystore (stored outside the repo)
- Add `release` signing config to `build.gradle.kts` reading from `local.properties`:
  ```
  KEYSTORE_PATH=../../../secrets/opescare-release.jks
  KEYSTORE_PASSWORD=...
  KEY_ALIAS=opescare
  KEY_PASSWORD=...
  ```
- `local.properties` is already gitignored by Flutter's default `.gitignore`

### Environment Config
- All builds pass `--dart-define=API_BASE_URL=<url>` at build time
- `api_endpoints.dart` already reads `String.fromEnvironment('API_BASE_URL')` — no code change needed
- Add build scripts:
  - `scripts/build_dev.ps1` — targets `http://opescare.test/api`
  - `scripts/build_staging.ps1` — targets staging URL (replace placeholder with actual staging host when available)
  - `scripts/build_prod.ps1` — targets `https://api.opescare.com/api`

### App Icons + Splash Screen
- Add `flutter_launcher_icons` and `flutter_native_splash` as dev dependencies
- Icon: primary color (`#1565C0`, matching `AppColors.primary500`) background, white heart icon — matches the brand mark in `LoginScreen`
- Splash: same primary color background, centered white heart, no text
- Configure both in `pubspec.yaml` under their respective keys
- Run `dart run flutter_launcher_icons` and `dart run flutter_native_splash:create`

### Fix Broken Test
- `test/widget_test.dart` currently references `MyApp` which doesn't exist
- Replace with a smoke test that wraps `OpesCareApp` in `ProviderScope` with overridden `apiClientProvider` returning a mock, verifies the login screen renders without crashing

**Deliverable:** A signed release APK and AAB that installs and runs with the correct app identity and branding.

---

## Phase 2 — Observability

**Goal:** Crash reporting, analytics, and push token registration are live.

### Firebase Project Setup
- Create Firebase project `opescare-patient` (or reuse an existing one)
- Run `flutterfire configure` to generate `lib/firebase_options.dart`
- Download `google-services.json` → `android/app/`
- Download `GoogleService-Info.plist` → `ios/Runner/`
- Packages added to `pubspec.yaml`:
  - `firebase_core`
  - `firebase_crashlytics`
  - `firebase_analytics`
  - `firebase_messaging`
  - `flutter_local_notifications`

### main.dart Integration
```dart
await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
FlutterError.onError = FirebaseCrashlytics.instance.recordFlutterFatalError;
PlatformDispatcher.instance.onError = (error, stack) {
  FirebaseCrashlytics.instance.recordError(error, stack, fatal: true);
  return true;
};
FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
```

### FCM Push Token Registration
- After successful login in `AuthNotifier.loginWithEmail`: request notification permission, get FCM token, `POST /mobile/push-tokens`
- Store returned token ID in `SecureStorage` under key `push_token_id`
- On logout in `AuthNotifier.logout`: `DELETE /mobile/push-tokens/{id}`, then clear `push_token_id`
- On iOS, also call `FirebaseMessaging.instance.getAPNSToken()` before `getToken()`

### Foreground Notifications
- `FirebaseMessaging.onMessage` handler: use `flutter_local_notifications` to show a local notification
- No deep-linking on tap yet (handled in Phase 3)

### Analytics Events
Wrap these actions with `FirebaseAnalytics.instance.logEvent(name: '...')`:
- `login_success` (after email+password login)
- `view_health_id`
- `consent_approved` / `consent_denied`
- `lab_result_viewed`
- `appointment_booked` (Phase 3)

**Deliverable:** Crashlytics receiving test crashes, push tokens being registered with the backend on login.

---

## Phase 3 — Feature Completion

**Goal:** Every stub and route is fully implemented.

### Appointment Booking Flow
**New screen:** `lib/features/appointments/presentation/book_appointment_screen.dart`

3-step wizard:
1. **Select facility** — `GET /mobile/facilities`, show a `ListView` of facility cards; tap to select
2. **Select slot** — `GET /mobile/facilities/{id}/slots`, show available slots grouped by date; tap to select
3. **Confirm** — shows selected facility/slot summary, optional notes `TextField`; "Confirm Booking" button → `POST /mobile/appointments`

New files:
- `lib/features/appointments/models/facility.dart`
- `lib/features/appointments/models/slot.dart`
- `lib/features/appointments/data/appointments_repository.dart` (extend existing with `fetchFacilities`, `fetchSlots`, `book`)
- `lib/features/appointments/providers/appointments_provider.dart` (extend with `facilitiesProvider`, `slotsProvider(facilityId)`)

After booking: invalidate `appointmentsListProvider`, pop to appointments list, show a success snack bar.

Wire up: add a "Book Appointment" `FloatingActionButton` to `AppointmentsScreen`.

### Data Export Request
- In `SettingsScreen`, `_NavRow` for "Request data export" currently has empty `onTap`
- On tap: show `AlertDialog` confirming the request → on confirm, `POST /mobile/data-export-requests` → show a `SnackBar("Export requested. You'll be notified when it's ready.")`
- If an export is ready (`GET /mobile/data-export-requests` returns a completed record): show a "Download" button that calls `GET /mobile/data-exports/{id}/download`

### Correction Request
- `_NavRow` for "File a correction request" currently has empty `onTap`
- On tap: show a `ModalBottomSheet` with a `TextField` for description and a "Submit" button → `POST /mobile/correction-requests` with `{description}` → `SnackBar("Correction request submitted.")`

### Push Notification Tap Routing
Extend Phase 2's FCM setup:
- `FirebaseMessaging.onMessageOpenedApp` and `getInitialMessage` handlers
- Payload format expected from backend: `{ "type": "lab_result" | "consent" | "appointment" | "document", "id": "..." }`
- Route map:
  - `lab_result` → `Routes.labs` (or detail if `id` present)
  - `consent` → `Routes.consent`
  - `appointment` → `Routes.appointments`
  - `document` → `Routes.documents`
- Deep-link routing uses `appRouterProvider`'s `GoRouter.go()`

**Deliverable:** All 15+ screens are feature-complete with no stubbed actions.

---

## Phase 4 — Quality Hardening

**Goal:** The app handles edge cases gracefully, is accessible, has a dark theme, and has a meaningful test suite.

### Token Refresh / 401 Handling
- Add a second Dio interceptor to `ApiClient` that handles 401 responses:
  1. Call `POST /mobile/auth/refresh` with the stored token
  2. If successful: store new token, retry original request
  3. If refresh fails (also 401 or network error): call `authProvider.notifier.logout()`, navigate to login
- The refresh endpoint must exist on the backend; if it doesn't exist yet, coordinate with the API team or implement it in `apps/api-laravel`
- Prevents the current behavior where a session expiry crashes the user out silently

### Offline / No-Network UX
- Add `connectivity_plus` to dependencies
- Create `ConnectivityBanner` widget: a dismissible amber banner shown when `ConnectivityResult.none`
- Mount `ConnectivityBanner` at the top of `MainShell` so it appears on all authenticated screens
- In `ErrorView`: detect `ApiException.network()` specifically and show "No internet connection. Check your network and try again." with a distinct icon (`LucideIcons.wifiOff`)

### Accessibility
Add `Semantics` to:
- `_HealthIdBanner`: `label: "Health ID ${healthId}, ${isVerified ? 'verified' : 'unverified'}"`
- `_StatCard`: `label: "${label}: ${value}"`
- `_ConsentAlert`: `button: true, label: "${count} pending consent requests, tap to review"`
- All `_NavRow` items: `button: true`
- All `_SwitchRow` items: already uses `Switch` which is accessible; add `label` to outer container
- Password visibility toggle: `Semantics(label: _obscurePassword ? 'Show password' : 'Hide password')`

Verify with TalkBack (Android emulator) and VoiceOver (iOS simulator) on Login, Home, and Consent screens.

### Dark Mode
- Add `AppTheme.dark` to `lib/core/theme/app_theme.dart` using the existing `AppColors` tokens
- Dark surfaces: `#1A1A2E` background, `#252540` surface, `#E0E0FF` text
- `OpesCareApp`: switch `theme: AppTheme.light, darkTheme: AppTheme.dark, themeMode: ThemeMode.system`

### Tests
**Fix existing test:**
- `test/widget_test.dart`: replace stale counter test with `OpesCareApp` smoke test using mocked providers

**New unit tests:**
- `test/features/auth/auth_provider_test.dart`:
  - `loginWithEmail` success → state becomes `authenticated`
  - `loginWithEmail` 401 → state has `errorMessage`, stays `unauthenticated`
  - `logout` → state becomes `unauthenticated`

**New widget tests:**
- `test/features/auth/login_screen_test.dart`: login form validates email format, shows error banner on failed login

**Deliverable:** `flutter test` passes, TalkBack/VoiceOver verified on 3 screens, dark mode renders correctly.

---

## Phase 5 — Store Submission

**Goal:** All release artifacts built and submitted.

### Android — Play Store
**Build command:**
```powershell
flutter build appbundle --release --dart-define=API_BASE_URL=https://api.opescare.com/api
```
Output: `build/app/outputs/bundle/release/app-release.aab`

**Play Store listing assets required:**
- App icon: 512×512 PNG (high-res)
- Feature graphic: 1024×500 PNG
- Screenshots: ≥2 per device category (phone minimum)
- Short description: ≤80 chars
- Full description: ≤4000 chars
- Privacy policy URL (must be live)
- Content rating: complete IARC questionnaire
- Data safety section: declare health data collection, auth credentials, device identifiers

### iOS — App Store
**Xcode Archive steps:**
1. Set scheme to Release, select "Any iOS Device"
2. Product → Archive → Distribute App → App Store Connect
3. Upload symbols for Crashlytics symbolication

**App Store Connect:**
- Screenshots: iPhone 6.5" (1284×2778) and 5.5" (1242×2208) — minimum required
- Privacy policy URL required
- Health Records entitlement disclosure if applicable
- Reviewer test credentials: create a test patient account and include in Review Notes

### Web — Hosted
**Build command:**
```powershell
flutter build web --release --dart-define=API_BASE_URL=https://api.opescare.com/api
```
Output: `build/web/`

**Deployment:** Copy `build/web/` to the web root of `app.opescare.com` (or configure as a subfolder with correct `<base href>`). Ensure CORS headers on the Laravel API allow requests from the web app origin.

### Android APK — Sideload
**Build command:**
```powershell
flutter build apk --release --dart-define=API_BASE_URL=https://api.opescare.com/api --split-per-abi
```
Produces three APKs: `app-arm64-v8a-release.apk`, `app-armeabi-v7a-release.apk`, `app-x86_64-release.apk`.

### Pre-Submission Checklist
- [ ] Privacy policy URL is live
- [ ] No debug banners (`debugShowCheckedModeBanner: false` — already set)
- [ ] No test/dev credentials hardcoded in source
- [ ] Version code (`versionCode`) incremented for each Play Store upload
- [ ] `minSdk` reviewed (Flutter default: 21, covers 99%+ of active devices)
- [ ] App tested on a physical device (Android + iOS)
- [ ] Crashlytics test crash verified in Firebase console
- [ ] Push notification received end-to-end on device

**Deliverable:** Live app on Play Store and App Store; web build deployed; APK available for sideload.

---

## Architecture Decisions

| Decision | Choice | Reason |
|---|---|---|
| Environment config | `--dart-define` at build time | Already in use; no runtime switching needed |
| Push notifications | FCM via `firebase_messaging` | Covers all 4 target platforms; Firebase already added for Crashlytics |
| Token refresh | Dio interceptor, retry once | Transparent to all callers; consistent with existing interceptor pattern |
| Offline detection | `connectivity_plus` banner in shell | Non-blocking; doesn't break navigation state |
| Dark mode | `ThemeMode.system` | Respects user OS setting without requiring in-app toggle |
| Booking flow | 3-step wizard | Matches complexity of facility → slot → confirm selection |

---

## Out of Scope (v1)

- Biometric authentication (Face ID / fingerprint)
- In-app chat with providers
- PDF viewer for documents/lab reports
- CI/CD pipeline (GitHub Actions)
- Localization / multi-language support
- Tablet / iPad layout optimizations
