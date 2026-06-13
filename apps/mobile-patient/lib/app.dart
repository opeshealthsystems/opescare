import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';
import 'core/api/api_endpoints.dart';
import 'core/notifications/notification_service.dart';
import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';
import 'core/update/force_update_service.dart';
import 'features/auth/providers/auth_provider.dart';

class OpesCareApp extends ConsumerStatefulWidget {
  const OpesCareApp({super.key});

  @override
  ConsumerState<OpesCareApp> createState() => _OpesCareAppState();
}

class _OpesCareAppState extends ConsumerState<OpesCareApp> {
  @override
  void initState() {
    super.initState();
    // Wire ApiClient.onUnauthenticated → AuthNotifier.forceLogout.
    // Must be called once before any authenticated API requests.
    ref.read(authClientWiringProvider);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final router = ref.read(appRouterProvider);
      // Handle tap from terminated state
      try {
        final pending = NotificationService.instance.pendingRoute;
        if (pending != null) {
          NotificationService.instance.pendingRoute = null;
          router.go(pending);
        }
        // Handle tap while app is backgrounded
        NotificationService.instance.setRouteHandler(router.go);
      } catch (_) {
        // Silently ignore in test environment or if Firebase is unavailable
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final router = ref.watch(appRouterProvider);
    // Forced-update gate: fail-open, so a backend/network problem never locks
    // users out. Calls GET {baseUrl}/mobile/app-config at startup and blocks only
    // when the running build is below min_supported_build.
    return ForceUpdateGate(
      baseUrl: ApiEndpoints.baseUrl,
      child: MaterialApp.router(
        title: 'OpesCare',
        theme: AppTheme.light,
        darkTheme: AppTheme.dark,
        themeMode: ThemeMode.system,
        routerConfig: router,
        debugShowCheckedModeBanner: false,
        // Localization: English + French (Cameroon is bilingual EN/FR). Strings live in
        // lib/l10n/*.arb. Run `flutter gen-l10n` (or `flutter pub get`) to generate
        // AppLocalizations, then migrate screens to AppLocalizations.of(context).
        localizationsDelegates: AppLocalizations.localizationsDelegates,
        supportedLocales: AppLocalizations.supportedLocales,
      ),
    );
  }
}
