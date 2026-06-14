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
}
