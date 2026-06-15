import 'dart:io';

import 'package:flutter_test/flutter_test.dart';

void main() {
  test('firebase options are not the generated stub', () {
    final file = File('lib/firebase_options.dart');
    final source = file.readAsStringSync();

    expect(source, isNot(contains('throw UnimplementedError')));
    expect(source, isNot(contains('STUB')));
  });

  test('auth repository does not use dart.library.html as android detection',
      () {
    final file = File('lib/features/auth/data/auth_repository.dart');
    final source = file.readAsStringSync();

    expect(
        source, isNot(contains("bool.fromEnvironment('dart.library.html')")));
  });

  test('otp resend is wired to the mobile auth API instead of navigation pop',
      () {
    final endpoints =
        File('lib/core/api/api_endpoints.dart').readAsStringSync();
    final repository =
        File('lib/features/auth/data/auth_repository.dart').readAsStringSync();
    final provider = File('lib/features/auth/providers/auth_provider.dart')
        .readAsStringSync();
    final screen = File('lib/features/auth/presentation/otp_screen.dart')
        .readAsStringSync();

    expect(endpoints, contains('otp/resend'));
    expect(repository, contains('Future<void> resendOtp'));
    expect(provider, contains('Future<void> resendOtp()'));
    expect(screen, contains('.resendOtp()'));
    expect(screen, isNot(contains('Navigator.of(context).pop();')));
  });

  test('forgot pin, register, and health id share actions are not empty stubs',
      () {
    final login = File('lib/features/auth/presentation/login_screen.dart')
        .readAsStringSync();
    final healthId =
        File('lib/features/health_id/presentation/health_id_screen.dart')
            .readAsStringSync();

    expect(login, contains('launchUrl'));
    expect(login, contains('registerPatient'));
    expect(login, isNot(contains('onPressed: () {}')));
    expect(login, isNot(contains('onTap: () {}')));
    expect(healthId, contains('Share.share'));
    expect(healthId, isNot(contains('onTap: () {}')));
  });
}
