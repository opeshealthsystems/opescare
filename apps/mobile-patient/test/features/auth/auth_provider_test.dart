import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mockito/annotations.dart';
import 'package:mockito/mockito.dart';
import 'package:opescare_patient/core/api/api_client.dart';
import 'package:opescare_patient/core/storage/secure_storage.dart';
import 'package:opescare_patient/features/auth/data/auth_repository.dart';
import 'package:opescare_patient/features/auth/providers/auth_provider.dart';

@GenerateMocks([AuthRepository, SecureStorage])
import 'auth_provider_test.mocks.dart';

void main() {
  late MockAuthRepository mockRepo;
  late MockSecureStorage mockStorage;
  late ProviderContainer container;

  setUp(() {
    mockRepo = MockAuthRepository();
    mockStorage = MockSecureStorage();
    when(mockStorage.hasToken()).thenAnswer((_) async => false);
    when(mockStorage.getToken()).thenAnswer((_) async => null);
    when(mockStorage.getPushTokenId()).thenAnswer((_) async => null);

    container = ProviderContainer(
      overrides: [
        authRepositoryProvider.overrideWithValue(mockRepo),
        secureStorageProvider.overrideWithValue(mockStorage),
      ],
    );
  });

  tearDown(() => container.dispose());

  group('loginWithEmail', () {
    test('success → status becomes authenticated', () async {
      when(mockRepo.loginWithEmail(email: 'a@b.com', password: 'pass'))
          .thenAnswer((_) async => 'fake-token');

      final notifier = container.read(authProvider.notifier);
      await notifier.loginWithEmail('a@b.com', 'pass');

      final state = container.read(authProvider);
      expect(state.status, AuthStatus.authenticated);
      expect(state.errorMessage, isNull);
      expect(state.isLoading, false);
    });

    test('401 error → stays unauthenticated with errorMessage', () async {
      when(mockRepo.loginWithEmail(email: 'a@b.com', password: 'wrong'))
          .thenThrow(Exception('401 Invalid credentials'));

      final notifier = container.read(authProvider.notifier);
      await notifier.loginWithEmail('a@b.com', 'wrong');

      final state = container.read(authProvider);
      expect(state.status, AuthStatus.unauthenticated);
      expect(state.errorMessage, isNotNull);
      expect(state.isLoading, false);
    });

    test('network error → errorMessage contains network hint', () async {
      when(mockRepo.loginWithEmail(
              email: anyNamed('email'), password: anyNamed('password')))
          .thenThrow(Exception('network connection refused'));

      final notifier = container.read(authProvider.notifier);
      await notifier.loginWithEmail('a@b.com', 'any');

      final state = container.read(authProvider);
      expect(state.errorMessage, contains('internet'));
    });
  });

  group('logout', () {
    test('clears state to unauthenticated', () async {
      when(mockStorage.getPushTokenId()).thenAnswer((_) async => null);
      when(mockRepo.logout()).thenAnswer((_) async {});

      final notifier = container.read(authProvider.notifier);
      await notifier.logout();

      expect(container.read(authProvider).status, AuthStatus.unauthenticated);
    });
  });

  group('forceLogout', () {
    test('calls clearAll and sets unauthenticated without calling repo.logout',
        () async {
      when(mockStorage.clearAll()).thenAnswer((_) async {});

      final notifier = container.read(authProvider.notifier);
      await notifier.forceLogout();

      verify(mockStorage.clearAll()).called(1);
      verifyNever(mockRepo.logout());
      expect(container.read(authProvider).status, AuthStatus.unauthenticated);
    });
  });
}
