import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/otp_screen.dart';

abstract final class Routes {
  static const login               = '/login';
  static const otp                 = '/otp';
  static const home                = '/home';
  static const healthId            = '/health-id';
  static const consent             = '/consent';
  static const timeline            = '/timeline';
  static const labs                = '/labs';
  static const prescriptions       = '/prescriptions';
  static const appointments        = '/appointments';
  static const bookAppointment     = '/appointments/book';
  static const accessLogs          = '/access-logs';
  static const documents           = '/documents';
  static const settings            = '/settings';
}

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: Routes.home,
    redirect: (context, state) {
      final status = authState.status;
      final isAuth = status == AuthStatus.authenticated;
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
      GoRoute(path: Routes.home,  builder: (_, __) => const _PlaceholderScreen('Home')),
      GoRoute(path: Routes.healthId, builder: (_, __) => const _PlaceholderScreen('Health ID')),
      GoRoute(path: Routes.consent,  builder: (_, __) => const _PlaceholderScreen('Consent')),
      GoRoute(path: Routes.timeline, builder: (_, __) => const _PlaceholderScreen('Timeline')),
      GoRoute(path: Routes.settings, builder: (_, __) => const _PlaceholderScreen('Settings')),
    ],
  );
});

class _PlaceholderScreen extends StatelessWidget {
  const _PlaceholderScreen(this.name);
  final String name;
  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(name)),
        body: Center(child: Text('$name — coming in Phase 2+')),
      );
}
