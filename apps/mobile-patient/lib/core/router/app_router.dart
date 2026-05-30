import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/otp_screen.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/health_id/presentation/health_id_screen.dart';
import '../../features/consent/presentation/consent_screen.dart';
import '../../features/timeline/presentation/timeline_screen.dart';
import '../../features/labs/presentation/labs_screen.dart';
import '../../features/labs/presentation/lab_detail_screen.dart';
import '../../features/prescriptions/presentation/prescriptions_screen.dart';
import '../../features/prescriptions/presentation/prescription_detail_screen.dart';
import '../../features/appointments/presentation/appointments_screen.dart';
import '../../features/appointments/presentation/appointment_detail_screen.dart';
import '../../features/appointments/presentation/book_appointment_screen.dart';
import '../../features/access_logs/presentation/access_logs_screen.dart';
import '../../features/documents/presentation/documents_screen.dart';
import '../../features/settings/presentation/settings_screen.dart';
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
    // Guard: OTP screen requires a pending phone from the phone+PIN flow
    if (state.matchedLocation == Routes.otp &&
        status == AuthStatus.unauthenticated &&
        auth.pendingPhone == null) {
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
      // Auth
      GoRoute(path: Routes.login, builder: (_, __) => const LoginScreen()),
      GoRoute(path: Routes.otp,   builder: (_, __) => const OtpScreen()),

      // Labs (standalone with nested detail)
      GoRoute(
        path: Routes.labs,
        builder: (_, __) => const LabsScreen(),
        routes: [
          GoRoute(
            path: ':id',
            builder: (_, s) => LabDetailScreen(id: s.pathParameters['id']!),
          ),
        ],
      ),

      // Prescriptions (standalone with nested detail)
      GoRoute(
        path: Routes.prescriptions,
        builder: (_, __) => const PrescriptionsScreen(),
        routes: [
          GoRoute(
            path: ':id',
            builder: (_, s) =>
                PrescriptionDetailScreen(id: s.pathParameters['id']!),
          ),
        ],
      ),

      // Appointments (standalone with nested detail)
      GoRoute(
        path: Routes.appointments,
        builder: (_, __) => const AppointmentsScreen(),
        routes: [
          GoRoute(
            path: ':id',
            builder: (_, s) =>
                AppointmentDetailScreen(id: s.pathParameters['id']!),
          ),
          GoRoute(
            path: 'book',
            builder: (_, __) => const BookAppointmentScreen(),
          ),
        ],
      ),

      // Access Logs
      GoRoute(
        path: Routes.accessLogs,
        builder: (_, __) => const AccessLogsScreen(),
      ),

      // Documents
      GoRoute(
        path: Routes.documents,
        builder: (_, __) => const DocumentsScreen(),
      ),

      // Shell — 5-tab bottom navigation
      StatefulShellRoute.indexedStack(
        builder: (context, state, shell) =>
            MainShell(navigationShell: shell),
        branches: [
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.home,
                builder: (_, __) => const HomeScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.healthId,
                builder: (_, __) => const HealthIdScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.consent,
                builder: (_, __) => const ConsentScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.timeline,
                builder: (_, __) => const TimelineScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: Routes.settings,
                builder: (_, __) => const SettingsScreen()),
          ]),
        ],
      ),
    ],
  );
});
