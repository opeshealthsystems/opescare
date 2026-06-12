import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mockito/mockito.dart';
import 'package:opescare_patient/core/api/api_client.dart';
import 'package:opescare_patient/features/auth/presentation/login_screen.dart';
import 'package:opescare_patient/features/auth/providers/auth_provider.dart';

import 'auth_provider_test.mocks.dart';

Widget _buildSubject(
    MockAuthRepository repo, MockSecureStorage storage) {
  return ProviderScope(
    overrides: [
      authRepositoryProvider.overrideWithValue(repo),
      secureStorageProvider.overrideWithValue(storage),
    ],
    child: const MaterialApp(home: LoginScreen()),
  );
}

void main() {
  late MockAuthRepository mockRepo;
  late MockSecureStorage mockStorage;

  setUp(() {
    mockRepo = MockAuthRepository();
    mockStorage = MockSecureStorage();
    when(mockStorage.hasToken()).thenAnswer((_) async => false);
    when(mockStorage.getToken()).thenAnswer((_) async => null);
    when(mockStorage.getPushTokenId()).thenAnswer((_) async => null);
  });

  testWidgets('shows email and password fields', (tester) async {
    await tester.pumpWidget(_buildSubject(mockRepo, mockStorage));
    await tester.pumpAndSettle();
    expect(find.text('EMAIL ADDRESS'), findsOneWidget);
    expect(find.text('PASSWORD'), findsOneWidget);
    expect(find.text('Sign In'), findsOneWidget);
  });

  testWidgets('validates empty form on submit', (tester) async {
    await tester.pumpWidget(_buildSubject(mockRepo, mockStorage));
    await tester.pumpAndSettle();
    await tester.tap(find.widgetWithText(ElevatedButton, 'Sign In'));
    await tester.pumpAndSettle();
    expect(find.text('Please enter your email address'), findsOneWidget);
  });

  testWidgets('validates bad email format', (tester) async {
    await tester.pumpWidget(_buildSubject(mockRepo, mockStorage));
    await tester.pumpAndSettle();
    await tester.enterText(find.byType(TextFormField).first, 'notanemail');
    await tester.tap(find.widgetWithText(ElevatedButton, 'Sign In'));
    await tester.pumpAndSettle();
    expect(find.text('Enter a valid email address'), findsOneWidget);
  });

  testWidgets('shows error banner on failed login', (tester) async {
    when(mockRepo.loginWithEmail(
            email: anyNamed('email'), password: anyNamed('password')))
        .thenThrow(Exception('401'));

    await tester.pumpWidget(_buildSubject(mockRepo, mockStorage));
    await tester.pumpAndSettle();
    await tester.enterText(find.byType(TextFormField).at(0), 'a@b.com');
    await tester.enterText(find.byType(TextFormField).at(1), 'wrongpass');
    await tester.tap(find.widgetWithText(ElevatedButton, 'Sign In'));
    await tester.pumpAndSettle();
    expect(
      find.text('Incorrect email or password. Please try again.'),
      findsOneWidget,
    );
  });
}
