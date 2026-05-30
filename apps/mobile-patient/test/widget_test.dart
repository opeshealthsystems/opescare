import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:opescare_patient/app.dart';
import 'package:opescare_patient/core/api/api_client.dart';
import 'package:opescare_patient/core/storage/secure_storage.dart';

// Minimal fake that returns no token so the router sends us to /login
class _FakeStorage extends SecureStorage {
  @override Future<bool> hasToken() async => false;
  @override Future<String?> getToken() async => null;
}

void main() {
  testWidgets('app starts on login screen when unauthenticated',
      (WidgetTester tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          secureStorageProvider.overrideWithValue(_FakeStorage()),
        ],
        child: const OpesCareApp(),
      ),
    );
    await tester.pumpAndSettle();
    expect(find.text('Welcome back'), findsOneWidget);
  });
}
