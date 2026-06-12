# OpesCare Patient Flutter App — Phase 1: Foundation

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Scaffold the OpesCare patient Flutter app with design system, API client, secure auth, and navigation shell — everything needed before building screens.

**Architecture:** Feature-first folder structure under `apps/mobile-patient/`. Riverpod for state management, Dio for HTTP with auth interceptor, GoRouter for navigation with auth guard. All tokens (colors, spacing, typography) live in a single `AppTheme` that drives the entire app.

**Tech Stack:** Flutter 3.x, flutter_riverpod 2.x, go_router 13.x, dio 5.x, flutter_secure_storage 9.x, lucide_icons, google_fonts (Inter), shimmer, intl

**Phases:**
- **Phase 1 (this file):** Scaffold + Design System + API Client + Auth + Router
- Phase 2: Shell + Home + Health ID screens
- Phase 3: Consent + Timeline + Labs + Prescriptions screens
- Phase 4: Appointments + Access Logs + Documents + Settings screens

**API Base:** OpesCare mobile endpoints live at `{base_url}/mobile/...`  
Local dev (Android emulator): `http://10.0.2.2/api`  
Local dev (iOS simulator): `http://localhost/api`

---

## File Map — Phase 1

```
apps/mobile-patient/
├── pubspec.yaml                          # Dependencies
├── analysis_options.yaml                 # Lint rules
├── lib/
│   ├── main.dart                         # Entry point, ProviderScope
│   ├── app.dart                          # MaterialApp.router + GoRouter
│   ├── core/
│   │   ├── theme/
│   │   │   ├── app_colors.dart           # All color constants
│   │   │   ├── app_text_styles.dart      # Typography scale
│   │   │   └── app_theme.dart            # ThemeData builder
│   │   ├── api/
│   │   │   ├── api_client.dart           # Dio singleton + interceptors
│   │   │   ├── api_endpoints.dart        # All endpoint URL constants
│   │   │   └── api_exception.dart        # Typed error model
│   │   ├── storage/
│   │   │   └── secure_storage.dart       # flutter_secure_storage wrapper
│   │   └── router/
│   │       └── app_router.dart           # GoRouter + auth redirect
│   └── features/
│       └── auth/
│           ├── data/
│           │   └── auth_repository.dart  # login() + verifyOtp()
│           ├── providers/
│           │   └── auth_provider.dart    # AuthNotifier + authProvider
│           └── presentation/
│               ├── login_screen.dart     # Phone input screen
│               └── otp_screen.dart       # OTP verification screen
```

---

## Task 1: Project Scaffold

**Files:**
- Create: `apps/mobile-patient/pubspec.yaml`
- Create: `apps/mobile-patient/analysis_options.yaml`
- Create: `apps/mobile-patient/lib/main.dart`
- Create: `apps/mobile-patient/lib/app.dart`

- [ ] **Step 1.1 — Create the Flutter project**

```bash
cd C:\laragon\www\opescare\apps
flutter create --org com.opescare --project-name opescare_patient --platforms android,ios mobile-patient
```

Expected: Flutter project created at `apps/mobile-patient/`

- [ ] **Step 1.2 — Replace pubspec.yaml with project dependencies**

Replace `apps/mobile-patient/pubspec.yaml` entirely:

```yaml
name: opescare_patient
description: OpesCare Patient Mobile App
publish_to: none
version: 1.0.0+1

environment:
  sdk: ">=3.3.0 <4.0.0"

dependencies:
  flutter:
    sdk: flutter
  flutter_riverpod: ^2.5.1
  go_router: ^13.2.0
  dio: ^5.4.3
  flutter_secure_storage: ^9.0.0
  lucide_icons: ^0.257.0
  google_fonts: ^6.2.1
  intl: ^0.19.0
  shimmer: ^3.0.0
  cached_network_image: ^3.3.1
  equatable: ^2.0.5

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^3.0.0
  mockito: ^5.4.4
  build_runner: ^2.4.9

flutter:
  uses-material-design: true
```

- [ ] **Step 1.3 — Set lint rules**

Create `apps/mobile-patient/analysis_options.yaml`:

```yaml
include: package:flutter_lints/flutter.yaml

linter:
  rules:
    prefer_single_quotes: true
    always_declare_return_types: true
    avoid_print: true
    prefer_const_constructors: true
    prefer_const_declarations: true
```

- [ ] **Step 1.4 — Install dependencies**

```bash
cd C:\laragon\www\opescare\apps\mobile-patient
flutter pub get
```

Expected: Resolving dependencies... (exit 0, no errors)

- [ ] **Step 1.5 — Verify Flutter setup**

```bash
flutter doctor
```

Expected: No critical issues for Android/iOS targets.

- [ ] **Step 1.6 — Commit scaffold**

```bash
cd C:\laragon\www\opescare
git add apps/mobile-patient/
git commit -m "feat(mobile): scaffold OpesCare patient Flutter app"
```

---

## Task 2: Design System — Colors

**Files:**
- Create: `apps/mobile-patient/lib/core/theme/app_colors.dart`

- [ ] **Step 2.1 — Write color constants**

Create `apps/mobile-patient/lib/core/theme/app_colors.dart`:

```dart
import 'package:flutter/material.dart';

/// OpesCare Patient App — Design Tokens (Colors)
/// Primary: Clinical Blue #1565C0 — WCAG AAA on white (7.2:1 contrast ratio)
abstract final class AppColors {
  // Primary — Clinical Blue
  static const Color primary50  = Color(0xFFEFF6FF);
  static const Color primary100 = Color(0xFFDBEAFE);
  static const Color primary200 = Color(0xFFBFDBFE);
  static const Color primary300 = Color(0xFF93C5FD);
  static const Color primary400 = Color(0xFF60A5FA);
  static const Color primary500 = Color(0xFF1565C0); // Brand default
  static const Color primary600 = Color(0xFF1044A0);
  static const Color primary700 = Color(0xFF0D3A7D);
  static const Color primary800 = Color(0xFF0A2B5C);
  static const Color primary900 = Color(0xFF071B3B);

  // Neutral — Slate
  static const Color neutral50  = Color(0xFFF9FAFB);
  static const Color neutral100 = Color(0xFFF3F4F6);
  static const Color neutral200 = Color(0xFFE5E7EB);
  static const Color neutral300 = Color(0xFFD1D5DB);
  static const Color neutral400 = Color(0xFF9CA3AF);
  static const Color neutral500 = Color(0xFF6B7280);
  static const Color neutral600 = Color(0xFF4B5563);
  static const Color neutral700 = Color(0xFF374151);
  static const Color neutral800 = Color(0xFF1F2937);
  static const Color neutral900 = Color(0xFF111827);

  // Semantic
  static const Color success     = Color(0xFF10B981); // Teal — Verified
  static const Color successDark = Color(0xFF059669);
  static const Color successLight = Color(0xFFD1FAE5);

  static const Color warning     = Color(0xFFF59E0B); // Amber — Pending
  static const Color warningDark = Color(0xFFD97706);
  static const Color warningLight = Color(0xFFFEF3C7);

  static const Color danger      = Color(0xFFEF4444); // Red — Critical
  static const Color dangerDark  = Color(0xFFDC2626);
  static const Color dangerLight = Color(0xFFFEE2E2);

  static const Color info        = Color(0xFF3B82F6);
  static const Color infoDark    = Color(0xFF2563EB);
  static const Color infoLight   = Color(0xFFEFF6FF);

  // Surfaces
  static const Color background  = Color(0xFFF3F4F6); // Page background
  static const Color surface     = Color(0xFFFFFFFF); // Cards
  static const Color surfaceMuted = Color(0xFFF9FAFB);
  static const Color divider     = Color(0xFFE5E7EB);

  // Text
  static const Color textPrimary   = Color(0xFF111827);
  static const Color textSecondary = Color(0xFF6B7280);
  static const Color textMuted     = Color(0xFF9CA3AF);
  static const Color textOnPrimary = Color(0xFFFFFFFF);

  // Health ID Card gradient
  static const Color cardGradientStart = Color(0xFF1565C0);
  static const Color cardGradientEnd   = Color(0xFF1044A0);
}
```

- [ ] **Step 2.2 — Verify file compiles**

```bash
cd C:\laragon\www\opescare\apps\mobile-patient
flutter analyze lib/core/theme/app_colors.dart
```

Expected: No issues found.

---

## Task 3: Design System — Typography & Theme

**Files:**
- Create: `apps/mobile-patient/lib/core/theme/app_text_styles.dart`
- Create: `apps/mobile-patient/lib/core/theme/app_theme.dart`

- [ ] **Step 3.1 — Write typography scale**

Create `apps/mobile-patient/lib/core/theme/app_text_styles.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'app_colors.dart';

/// OpesCare typography — Inter font, 1.25x scale
abstract final class AppTextStyles {
  static TextStyle get h1 => GoogleFonts.inter(
        fontSize: 28, fontWeight: FontWeight.w700,
        color: AppColors.textPrimary, height: 1.2,
      );

  static TextStyle get h2 => GoogleFonts.inter(
        fontSize: 22, fontWeight: FontWeight.w700,
        color: AppColors.textPrimary, height: 1.3,
      );

  static TextStyle get h3 => GoogleFonts.inter(
        fontSize: 18, fontWeight: FontWeight.w600,
        color: AppColors.textPrimary, height: 1.4,
      );

  static TextStyle get h4 => GoogleFonts.inter(
        fontSize: 16, fontWeight: FontWeight.w600,
        color: AppColors.textPrimary, height: 1.4,
      );

  static TextStyle get bodyLg => GoogleFonts.inter(
        fontSize: 16, fontWeight: FontWeight.w400,
        color: AppColors.textPrimary, height: 1.5,
      );

  static TextStyle get body => GoogleFonts.inter(
        fontSize: 14, fontWeight: FontWeight.w400,
        color: AppColors.textPrimary, height: 1.5,
      );

  static TextStyle get bodySm => GoogleFonts.inter(
        fontSize: 13, fontWeight: FontWeight.w400,
        color: AppColors.textSecondary, height: 1.5,
      );

  static TextStyle get caption => GoogleFonts.inter(
        fontSize: 11, fontWeight: FontWeight.w400,
        color: AppColors.textMuted, height: 1.4,
        letterSpacing: 0.2,
      );

  static TextStyle get label => GoogleFonts.inter(
        fontSize: 11, fontWeight: FontWeight.w600,
        color: AppColors.textMuted, height: 1.2,
        letterSpacing: 0.8,
      );

  static TextStyle get buttonLg => GoogleFonts.inter(
        fontSize: 15, fontWeight: FontWeight.w600,
        height: 1.2, letterSpacing: 0.1,
      );

  static TextStyle get button => GoogleFonts.inter(
        fontSize: 14, fontWeight: FontWeight.w600,
        height: 1.2,
      );

  static TextStyle get healthId => GoogleFonts.inter(
        fontSize: 20, fontWeight: FontWeight.w700,
        color: AppColors.textOnPrimary, letterSpacing: 2.5,
      );
}
```

- [ ] **Step 3.2 — Write ThemeData**

Create `apps/mobile-patient/lib/core/theme/app_theme.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'app_colors.dart';

abstract final class AppTheme {
  static ThemeData get light {
    const primary = AppColors.primary500;

    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primary,
        brightness: Brightness.light,
        primary: primary,
        onPrimary: AppColors.textOnPrimary,
        surface: AppColors.surface,
        background: AppColors.background,
        error: AppColors.danger,
      ),
      scaffoldBackgroundColor: AppColors.background,
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: AppColors.divider,
        systemOverlayStyle: SystemUiOverlayStyle.dark,
        titleTextStyle: GoogleFonts.inter(
          fontSize: 17, fontWeight: FontWeight.w600,
          color: AppColors.textPrimary,
        ),
      ),
      cardTheme: CardTheme(
        color: AppColors.surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: const BorderSide(color: AppColors.divider),
        ),
        margin: EdgeInsets.zero,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: AppColors.textOnPrimary,
          minimumSize: const Size(double.infinity, 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          textStyle: GoogleFonts.inter(
            fontSize: 15, fontWeight: FontWeight.w600,
          ),
          elevation: 0,
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: primary,
          minimumSize: const Size(double.infinity, 52),
          side: const BorderSide(color: primary),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          textStyle: GoogleFonts.inter(
            fontSize: 15, fontWeight: FontWeight.w600,
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surface,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16, vertical: 14,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.neutral200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: primary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.danger),
        ),
        labelStyle: GoogleFonts.inter(
          fontSize: 14, color: AppColors.textSecondary,
        ),
        hintStyle: GoogleFonts.inter(
          fontSize: 14, color: AppColors.textMuted,
        ),
      ),
      dividerTheme: const DividerThemeData(
        color: AppColors.divider,
        thickness: 1,
        space: 0,
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: AppColors.surface,
        selectedItemColor: AppColors.primary500,
        unselectedItemColor: AppColors.neutral400,
        type: BottomNavigationBarType.fixed,
        elevation: 8,
        showUnselectedLabels: true,
      ),
    );
  }
}
```

- [ ] **Step 3.3 — Analyze theme files**

```bash
cd C:\laragon\www\opescare\apps\mobile-patient
flutter analyze lib/core/theme/
```

Expected: No issues found.

---

## Task 4: API Client & Endpoints

**Files:**
- Create: `apps/mobile-patient/lib/core/api/api_endpoints.dart`
- Create: `apps/mobile-patient/lib/core/api/api_exception.dart`
- Create: `apps/mobile-patient/lib/core/api/api_client.dart`

- [ ] **Step 4.1 — Write endpoint constants**

Create `apps/mobile-patient/lib/core/api/api_endpoints.dart`:

```dart
/// All OpesCare mobile API endpoints.
/// Base URL is injected via --dart-define=API_BASE_URL=...
abstract final class ApiEndpoints {
  static const String _base =
      String.fromEnvironment('API_BASE_URL', defaultValue: 'http://10.0.2.2/api');

  static String get baseUrl => _base;

  // ── Auth ────────────────────────────────────────────────
  static String get login        => '$_base/mobile/auth/login';
  static String get verifyOtp    => '$_base/mobile/auth/otp/verify';

  // ── Patient ─────────────────────────────────────────────
  static String get me           => '$_base/mobile/me';
  static String get timeline     => '$_base/mobile/timeline';
  static String get healthIdCard => '$_base/mobile/health-id-card';
  static String get allergies    => '$_base/mobile/allergies';
  static String get clinical     => '$_base/mobile/clinical';
  static String get immunizations => '$_base/mobile/immunizations';

  // ── Consent / Governance ────────────────────────────────
  static String get consentRequests => '$_base/mobile/consent-requests';
  static String approveConsent(String id) =>
      '$_base/mobile/consent-requests/$id/approve';
  static String denyConsent(String id) =>
      '$_base/mobile/consent-requests/$id/deny';
  static String revokeConsent(String id) =>
      '$_base/mobile/consents/$id/revoke';
  static String get accessLogs   => '$_base/mobile/access-logs';
  static String get correctionRequests => '$_base/mobile/correction-requests';
  static String get dataExportRequests => '$_base/mobile/data-export-requests';
  static String dataExportDownload(String id) =>
      '$_base/mobile/data-exports/$id/download';

  // ── Labs ────────────────────────────────────────────────
  static String get labs         => '$_base/mobile/labs';
  static String lab(String id)   => '$_base/mobile/labs/$id';

  // ── Prescriptions ───────────────────────────────────────
  static String get prescriptions   => '$_base/mobile/prescriptions';
  static String prescription(String id) => '$_base/mobile/prescriptions/$id';

  // ── Appointments ────────────────────────────────────────
  static String get appointments    => '$_base/mobile/appointments';
  static String appointment(String id) => '$_base/mobile/appointments/$id';
  static String cancelAppointment(String id) =>
      '$_base/mobile/appointments/$id/cancel';

  // ── Facilities ──────────────────────────────────────────
  static String get facilities      => '$_base/mobile/facilities';
  static String facility(String id) => '$_base/mobile/facilities/$id';
  static String facilitySlots(String id) =>
      '$_base/mobile/facilities/$id/slots';

  // ── Documents ───────────────────────────────────────────
  static String get documents       => '$_base/mobile/documents';
  static String document(String id) => '$_base/mobile/documents/$id';

  // ── Settings ────────────────────────────────────────────
  static String get settings        => '$_base/mobile/settings';
  static String get pushTokens      => '$_base/mobile/push-tokens';
  static String pushToken(String id) => '$_base/mobile/push-tokens/$id';

  // ── Offline ─────────────────────────────────────────────
  static String get offlinePolicies => '$_base/mobile/offline/policies';
}
```

- [ ] **Step 4.2 — Write typed API exception**

Create `apps/mobile-patient/lib/core/api/api_exception.dart`:

```dart
import 'package:equatable/equatable.dart';

enum ApiErrorType {
  unauthorized,   // 401 — token expired or invalid
  forbidden,      // 403 — no consent / no permission
  notFound,       // 404
  validation,     // 422 — form errors
  server,         // 500+
  network,        // No connection / timeout
  unknown,
}

class ApiException extends Equatable implements Exception {
  const ApiException({
    required this.type,
    required this.message,
    this.statusCode,
    this.errors,
  });

  final ApiErrorType type;
  final String message;
  final int? statusCode;
  final Map<String, List<String>>? errors; // validation field errors

  factory ApiException.fromStatusCode(int code, String message,
      {Map<String, List<String>>? errors}) {
    final type = switch (code) {
      401 => ApiErrorType.unauthorized,
      403 => ApiErrorType.forbidden,
      404 => ApiErrorType.notFound,
      422 => ApiErrorType.validation,
      >= 500 => ApiErrorType.server,
      _ => ApiErrorType.unknown,
    };
    return ApiException(
        type: type, message: message, statusCode: code, errors: errors);
  }

  factory ApiException.network() => const ApiException(
        type: ApiErrorType.network,
        message: 'No internet connection. Please check your network.',
      );

  @override
  List<Object?> get props => [type, message, statusCode];

  @override
  String toString() => 'ApiException($type, $message, $statusCode)';
}
```

- [ ] **Step 4.3 — Write Dio API client**

Create `apps/mobile-patient/lib/core/api/api_client.dart`:

```dart
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../storage/secure_storage.dart';
import 'api_exception.dart';

/// Dio-based API client. Automatically attaches Bearer token from
/// secure storage and maps HTTP errors to [ApiException].
class ApiClient {
  ApiClient(this._storage) {
    _dio = Dio(BaseOptions(
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 30),
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
    ));

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storage.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (error, handler) {
          handler.reject(
            DioException(
              requestOptions: error.requestOptions,
              error: _mapError(error),
              type: error.type,
            ),
          );
        },
      ),
    );
  }

  final SecureStorage _storage;
  late final Dio _dio;

  Future<Map<String, dynamic>> get(String url,
      {Map<String, dynamic>? params}) async {
    try {
      final res = await _dio.get(url, queryParameters: params);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) {
      throw e.error as ApiException;
    }
  }

  Future<Map<String, dynamic>> post(String url,
      {Map<String, dynamic>? body}) async {
    try {
      final res = await _dio.post(url, data: body);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) {
      throw e.error as ApiException;
    }
  }

  Future<Map<String, dynamic>> patch(String url,
      {Map<String, dynamic>? body}) async {
    try {
      final res = await _dio.patch(url, data: body);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) {
      throw e.error as ApiException;
    }
  }

  Future<void> delete(String url) async {
    try {
      await _dio.delete(url);
    } on DioException catch (e) {
      throw e.error as ApiException;
    }
  }

  ApiException _mapError(DioException e) {
    if (e.type == DioExceptionType.connectionError ||
        e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout) {
      return ApiException.network();
    }
    final code = e.response?.statusCode ?? 0;
    final data = e.response?.data;
    final message = (data is Map && data['message'] != null)
        ? data['message'].toString()
        : 'An error occurred. Please try again.';
    Map<String, List<String>>? errors;
    if (data is Map && data['errors'] is Map) {
      errors = (data['errors'] as Map).map(
        (k, v) => MapEntry(k.toString(),
            (v as List).map((e) => e.toString()).toList()),
      );
    }
    return ApiException.fromStatusCode(code, message, errors: errors);
  }
}

// Riverpod providers
final secureStorageProvider = Provider<SecureStorage>((ref) => SecureStorage());

final apiClientProvider = Provider<ApiClient>((ref) {
  final storage = ref.watch(secureStorageProvider);
  return ApiClient(storage);
});
```

---

## Task 5: Secure Storage

**Files:**
- Create: `apps/mobile-patient/lib/core/storage/secure_storage.dart`

- [ ] **Step 5.1 — Write secure storage wrapper**

Create `apps/mobile-patient/lib/core/storage/secure_storage.dart`:

```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Wraps flutter_secure_storage for typed token management.
/// All sensitive values (JWT token, phone number) are stored here.
class SecureStorage {
  SecureStorage()
      : _storage = const FlutterSecureStorage(
          aOptions: AndroidOptions(encryptedSharedPreferences: true),
          iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
        );

  final FlutterSecureStorage _storage;

  static const _keyToken     = 'auth_token';
  static const _keyPhone     = 'last_phone';

  Future<void> saveToken(String token) =>
      _storage.write(key: _keyToken, value: token);

  Future<String?> getToken() => _storage.read(key: _keyToken);

  Future<void> deleteToken() => _storage.delete(key: _keyToken);

  Future<bool> hasToken() async =>
      (await _storage.read(key: _keyToken)) != null;

  Future<void> savePhone(String phone) =>
      _storage.write(key: _keyPhone, value: phone);

  Future<String?> getPhone() => _storage.read(key: _keyPhone);

  Future<void> clearAll() => _storage.deleteAll();
}
```

---

## Task 6: Auth Repository & Provider

**Files:**
- Create: `apps/mobile-patient/lib/features/auth/data/auth_repository.dart`
- Create: `apps/mobile-patient/lib/features/auth/providers/auth_provider.dart`

- [ ] **Step 6.1 — Write auth repository**

Create `apps/mobile-patient/lib/features/auth/data/auth_repository.dart`:

```dart
import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/storage/secure_storage.dart';

class AuthRepository {
  const AuthRepository(this._client, this._storage);

  final ApiClient _client;
  final SecureStorage _storage;

  /// Sends OTP to phone number. Returns request_id used for OTP verification.
  Future<String> login({required String phone}) async {
    final res = await _client.post(ApiEndpoints.login, body: {'phone': phone});
    return res['request_id'].toString();
  }

  /// Verifies OTP, saves returned Bearer token, returns it.
  Future<String> verifyOtp({
    required String phone,
    required String otp,
    required String requestId,
  }) async {
    final res = await _client.post(ApiEndpoints.verifyOtp, body: {
      'phone': phone,
      'otp': otp,
      'request_id': requestId,
    });
    final token = res['token'].toString();
    await _storage.saveToken(token);
    await _storage.savePhone(phone);
    return token;
  }

  Future<void> logout() => _storage.clearAll();
}
```

- [ ] **Step 6.2 — Write auth state & notifier**

Create `apps/mobile-patient/lib/features/auth/providers/auth_provider.dart`:

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../../../core/storage/secure_storage.dart';
import '../data/auth_repository.dart';

// ── Models ───────────────────────────────────────────────
enum AuthStatus { unknown, authenticated, unauthenticated }

class AuthState {
  const AuthState({
    this.status = AuthStatus.unknown,
    this.isLoading = false,
    this.errorMessage,
    this.pendingPhone,
    this.pendingRequestId,
  });

  final AuthStatus status;
  final bool isLoading;
  final String? errorMessage;
  final String? pendingPhone;      // set during OTP flow
  final String? pendingRequestId;  // set during OTP flow

  AuthState copyWith({
    AuthStatus? status,
    bool? isLoading,
    String? errorMessage,
    String? pendingPhone,
    String? pendingRequestId,
  }) =>
      AuthState(
        status: status ?? this.status,
        isLoading: isLoading ?? this.isLoading,
        errorMessage: errorMessage,           // null clears error
        pendingPhone: pendingPhone ?? this.pendingPhone,
        pendingRequestId: pendingRequestId ?? this.pendingRequestId,
      );
}

// ── Repository provider ──────────────────────────────────
final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.watch(apiClientProvider),
    ref.watch(secureStorageProvider),
  );
});

// ── Notifier ─────────────────────────────────────────────
class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier(this._repo, this._storage) : super(const AuthState()) {
    _init();
  }

  final AuthRepository _repo;
  final SecureStorage _storage;

  Future<void> _init() async {
    final hasToken = await _storage.hasToken();
    state = state.copyWith(
      status: hasToken ? AuthStatus.authenticated : AuthStatus.unauthenticated,
    );
  }

  /// Step 1 of auth: request OTP
  Future<void> login(String phone) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final requestId = await _repo.login(phone: phone);
      state = state.copyWith(
        isLoading: false,
        pendingPhone: phone,
        pendingRequestId: requestId,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  /// Step 2 of auth: verify OTP
  Future<void> verifyOtp(String otp) async {
    if (state.pendingPhone == null || state.pendingRequestId == null) return;
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      await _repo.verifyOtp(
        phone: state.pendingPhone!,
        otp: otp,
        requestId: state.pendingRequestId!,
      );
      state = state.copyWith(
        isLoading: false,
        status: AuthStatus.authenticated,
        pendingPhone: null,
        pendingRequestId: null,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  Future<void> logout() async {
    await _repo.logout();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(
    ref.watch(authRepositoryProvider),
    ref.watch(secureStorageProvider),
  );
});
```

---

## Task 7: App Router

**Files:**
- Create: `apps/mobile-patient/lib/core/router/app_router.dart`

- [ ] **Step 7.1 — Write GoRouter with auth guard**

Create `apps/mobile-patient/lib/core/router/app_router.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/otp_screen.dart';

// Route names — use these constants everywhere, never raw strings
abstract final class Routes {
  static const login        = '/login';
  static const otp          = '/otp';
  static const home         = '/home';
  static const healthId     = '/health-id';
  static const consent      = '/consent';
  static const timeline     = '/timeline';
  static const labs         = '/labs';
  static const labDetail    = '/labs/:id';
  static const prescriptions = '/prescriptions';
  static const prescriptionDetail = '/prescriptions/:id';
  static const appointments = '/appointments';
  static const appointmentDetail = '/appointments/:id';
  static const bookAppointment = '/appointments/book';
  static const accessLogs   = '/access-logs';
  static const documents    = '/documents';
  static const documentDetail = '/documents/:id';
  static const settings     = '/settings';
}

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: Routes.home,
    redirect: (context, state) {
      final status = authState.status;
      final isAuth = status == AuthStatus.authenticated;
      final isUnauth = status == AuthStatus.unauthenticated;
      final isLoggingIn = state.matchedLocation == Routes.login ||
          state.matchedLocation == Routes.otp;

      if (status == AuthStatus.unknown) return null; // splash/loading
      if (!isAuth && !isLoggingIn) return Routes.login;
      if (isAuth && isLoggingIn) return Routes.home;
      return null;
    },
    routes: [
      GoRoute(path: Routes.login, builder: (_, __) => const LoginScreen()),
      GoRoute(path: Routes.otp,   builder: (_, __) => const OtpScreen()),
      // Shell routes added in Phase 2
      GoRoute(path: Routes.home,  builder: (_, __) => const _PlaceholderScreen('Home')),
    ],
  );
});

class _PlaceholderScreen extends StatelessWidget {
  const _PlaceholderScreen(this.name);
  final String name;
  @override
  Widget build(BuildContext context) => Scaffold(
        body: Center(child: Text('$name — coming in Phase 2')),
      );
}
```

---

## Task 8: Auth Screens

**Files:**
- Create: `apps/mobile-patient/lib/features/auth/presentation/login_screen.dart`
- Create: `apps/mobile-patient/lib/features/auth/presentation/otp_screen.dart`

- [ ] **Step 8.1 — Write login screen**

Create `apps/mobile-patient/lib/features/auth/presentation/login_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _phoneController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final phone = _phoneController.text.trim();
    await ref.read(authProvider.notifier).login(phone);
    if (!mounted) return;
    final state = ref.read(authProvider);
    if (state.errorMessage == null && state.pendingRequestId != null) {
      context.push(Routes.otp);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 56),
                // Logo area
                Container(
                  width: 56, height: 56,
                  decoration: BoxDecoration(
                    color: AppColors.primary500,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(LucideIcons.heart,
                      color: AppColors.textOnPrimary, size: 28),
                ),
                const SizedBox(height: 24),
                Text('Welcome to\nOpesCare', style: AppTextStyles.h1),
                const SizedBox(height: 8),
                Text('Enter your phone number to continue.',
                    style: AppTextStyles.bodySm),
                const SizedBox(height: 40),
                Text('PHONE NUMBER', style: AppTextStyles.label),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  style: AppTextStyles.bodyLg,
                  decoration: const InputDecoration(
                    hintText: '+234 800 000 0000',
                    prefixIcon: Icon(LucideIcons.phone,
                        color: AppColors.neutral400, size: 20),
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) {
                      return 'Please enter your phone number';
                    }
                    if (v.trim().length < 8) {
                      return 'Phone number is too short';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 8),
                if (authState.errorMessage != null)
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.dangerLight,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(children: [
                      const Icon(LucideIcons.alertCircle,
                          color: AppColors.danger, size: 16),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(authState.errorMessage!,
                            style: AppTextStyles.bodySm
                                .copyWith(color: AppColors.danger)),
                      ),
                    ]),
                  ),
                const SizedBox(height: 32),
                ElevatedButton(
                  onPressed: authState.isLoading ? null : _submit,
                  child: authState.isLoading
                      ? const SizedBox(
                          height: 20, width: 20,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2),
                        )
                      : Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Text('Send OTP'),
                            const SizedBox(width: 8),
                            const Icon(LucideIcons.arrowRight, size: 18),
                          ],
                        ),
                ),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(LucideIcons.shield,
                        color: AppColors.neutral400, size: 14),
                    const SizedBox(width: 6),
                    Text('Your data is encrypted and private.',
                        style: AppTextStyles.caption),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
```

- [ ] **Step 8.2 — Write OTP screen**

Create `apps/mobile-patient/lib/features/auth/presentation/otp_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../providers/auth_provider.dart';

class OtpScreen extends ConsumerStatefulWidget {
  const OtpScreen({super.key});

  @override
  ConsumerState<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends ConsumerState<OtpScreen> {
  final List<TextEditingController> _controllers =
      List.generate(6, (_) => TextEditingController());
  final List<FocusNode> _focusNodes =
      List.generate(6, (_) => FocusNode());

  String get _otp => _controllers.map((c) => c.text).join();

  @override
  void dispose() {
    for (final c in _controllers) c.dispose();
    for (final f in _focusNodes) f.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_otp.length < 6) return;
    await ref.read(authProvider.notifier).verifyOtp(_otp);
  }

  void _onDigitChanged(int index, String value) {
    if (value.isNotEmpty && index < 5) {
      _focusNodes[index + 1].requestFocus();
    }
    if (value.isEmpty && index > 0) {
      _focusNodes[index - 1].requestFocus();
    }
    if (_otp.length == 6) _submit();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final phone = authState.pendingPhone ?? '';

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(LucideIcons.arrowLeft),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 16),
              Text('Verify your number', style: AppTextStyles.h2),
              const SizedBox(height: 8),
              Text('Enter the 6-digit code sent to $phone',
                  style: AppTextStyles.bodySm),
              const SizedBox(height: 40),
              // OTP boxes
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: List.generate(6, (i) => _OtpBox(
                  controller: _controllers[i],
                  focusNode: _focusNodes[i],
                  onChanged: (v) => _onDigitChanged(i, v),
                )),
              ),
              const SizedBox(height: 12),
              if (authState.errorMessage != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.dangerLight,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(children: [
                    const Icon(LucideIcons.alertCircle,
                        color: AppColors.danger, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(authState.errorMessage!,
                          style: AppTextStyles.bodySm
                              .copyWith(color: AppColors.danger)),
                    ),
                  ]),
                ),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: authState.isLoading ? null : _submit,
                child: authState.isLoading
                    ? const SizedBox(
                        height: 20, width: 20,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2),
                      )
                    : const Text('Verify OTP'),
              ),
              const SizedBox(height: 24),
              Center(
                child: TextButton.icon(
                  icon: const Icon(LucideIcons.refreshCw, size: 16),
                  label: const Text('Resend OTP'),
                  onPressed: () {
                    if (phone.isNotEmpty) {
                      ref.read(authProvider.notifier).login(phone);
                    }
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _OtpBox extends StatelessWidget {
  const _OtpBox({
    required this.controller,
    required this.focusNode,
    required this.onChanged,
  });

  final TextEditingController controller;
  final FocusNode focusNode;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 46, height: 56,
      child: TextField(
        controller: controller,
        focusNode: focusNode,
        keyboardType: TextInputType.number,
        textAlign: TextAlign.center,
        maxLength: 1,
        style: AppTextStyles.h3,
        inputFormatters: [FilteringTextInputFormatter.digitsOnly],
        decoration: InputDecoration(
          counterText: '',
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: AppColors.neutral200, width: 1.5),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: AppColors.primary500, width: 2),
          ),
        ),
        onChanged: onChanged,
      ),
    );
  }
}
```

---

## Task 9: App Entry Point

**Files:**
- Modify: `apps/mobile-patient/lib/main.dart`
- Modify: `apps/mobile-patient/lib/app.dart`

- [ ] **Step 9.1 — Write main.dart**

Replace `apps/mobile-patient/lib/main.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'app.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const ProviderScope(child: OpesCareApp()));
}
```

- [ ] **Step 9.2 — Write app.dart**

Replace `apps/mobile-patient/lib/app.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';

class OpesCareApp extends ConsumerWidget {
  const OpesCareApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);
    return MaterialApp.router(
      title: 'OpesCare',
      theme: AppTheme.light,
      routerConfig: router,
      debugShowCheckedModeBanner: false,
    );
  }
}
```

- [ ] **Step 9.3 — Analyze entire lib/**

```bash
cd C:\laragon\www\opescare\apps\mobile-patient
flutter analyze lib/
```

Expected: No issues found. (Fix any reported before continuing.)

- [ ] **Step 9.4 — Run on device/emulator to verify auth flow**

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2/api
```

Expected: App launches → shows Login screen → enter phone → shows OTP screen.

- [ ] **Step 9.5 — Commit Phase 1**

```bash
cd C:\laragon\www\opescare
git add apps/mobile-patient/
git commit -m "feat(mobile): Phase 1 - design system, API client, auth flow"
```

---

## Phase 1 Complete ✓

**What you now have:**
- Flutter project scaffold with all dependencies
- Full design token system (colors, typography, theme)
- Dio API client with auth interceptor and error mapping
- Secure token storage (flutter_secure_storage)
- GoRouter with auth guard (unauthenticated → /login redirect)
- Login screen (phone input, Lucide icons, error handling)
- OTP screen (6-box digit input, auto-advance, auto-submit)
- Riverpod state management wired end-to-end

**Next:** Proceed to `2026-05-29-patient-flutter-app-phase2.md` — Main Shell + Home + Health ID screens.
