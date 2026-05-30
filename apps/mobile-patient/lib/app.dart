import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/notifications/notification_service.dart';
import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';

class OpesCareApp extends ConsumerStatefulWidget {
  const OpesCareApp({super.key});

  @override
  ConsumerState<OpesCareApp> createState() => _OpesCareAppState();
}

class _OpesCareAppState extends ConsumerState<OpesCareApp> {
  @override
  void initState() {
    super.initState();
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
    return MaterialApp.router(
      title: 'OpesCare',
      theme: AppTheme.light,
      routerConfig: router,
      debugShowCheckedModeBanner: false,
    );
  }
}
