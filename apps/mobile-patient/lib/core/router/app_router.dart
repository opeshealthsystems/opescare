import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/otp_screen.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/health_id/presentation/health_id_screen.dart';
import '../../features/shell/presentation/main_shell.dart';

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
}

class _RouterNotifier extends ChangeNotifier {
  _RouterNotifier(this._ref) {
    _ref.listen<AuthState>(authProvider, (_, __) => notifyListeners());
  }

  final Ref _ref;

  String? redirect(BuildContext context, GoRouterState state) {
    final auth = _ref.read(authProvider);
    final status = auth.status;
    final isAuth = status == AuthStatus.authenticated;
    final isLoggingIn = state.matchedLocation == Routes.login ||
        state.matchedLocation == Routes.otp;

    if (status == AuthStatus.unknown) return null;
    if (!isAuth && !isLoggingIn) return Routes.login;
    if (isAuth && isLoggingIn) return Routes.home;
    if (state.matchedLocation == Routes.otp &&
        status == AuthStatus.unauthenticated &&
        auth.pendingRequestId == null) {
      return Routes.login;
    }
    return null;
  }
}

final _routerNotifierProvider =
    ChangeNotifierProvider<_RouterNotifier>((ref) => _RouterNotifier(ref));

final appRouterProvider = Provider<GoRouter>((ref) {
  final notifier = ref.watch(_routerNotifierProvider);
  return GoRouter(
    initialLocation: Routes.home,
    refreshListenable: notifier,
    redirect: notifier.redirect,
    routes: [
      GoRoute(path: Routes.login, builder: (_, __) => const LoginScreen()),
      GoRoute(path: Routes.otp,   builder: (_, __) => const OtpScreen()),

      // Standalone routes pushed from home stats (outside shell nav)
      GoRoute(path: Routes.labs,
          builder: (_, __) => const _PlaceholderScreen('Labs', LucideIcons.flaskConical)),
      GoRoute(path: Routes.prescriptions,
          builder: (_, __) => const _PlaceholderScreen('Prescriptions', LucideIcons.pill)),
      GoRoute(path: Routes.appointments,
          builder: (_, __) => const _PlaceholderScreen('Appointments', LucideIcons.calendar)),
      GoRoute(path: Routes.accessLogs,
          builder: (_, __) => const _PlaceholderScreen('Access Logs', LucideIcons.eye)),
      GoRoute(path: Routes.documents,
          builder: (_, __) => const _PlaceholderScreen('Documents', LucideIcons.fileText)),

      // Shell — 5-tab bottom navigation
      StatefulShellRoute.indexedStack(
        builder: (context, state, shell) =>
            MainShell(navigationShell: shell),
        branches: [
          StatefulShellBranch(routes: [
            GoRoute(
              path: Routes.home,
              builder: (_, __) => const HomeScreen(),
            ),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(
              path: Routes.healthId,
              builder: (_, __) => const HealthIdScreen(),
            ),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(
              path: Routes.consent,
              builder: (_, __) =>
                  const _PlaceholderScreen('Consent', LucideIcons.shield),
            ),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(
              path: Routes.timeline,
              builder: (_, __) =>
                  const _PlaceholderScreen('Timeline', LucideIcons.activity),
            ),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(
              path: Routes.settings,
              builder: (_, __) =>
                  const _PlaceholderScreen('Settings', LucideIcons.settings),
            ),
          ]),
        ],
      ),
    ],
  );
});

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
          Text('$name — coming soon', style: AppTextStyles.bodySm),
        ]),
      ),
    );
  }
}
