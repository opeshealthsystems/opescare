import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../../../core/storage/secure_storage.dart';
import '../data/auth_repository.dart';

enum AuthStatus { unknown, authenticated, unauthenticated }

class AuthState {
  const AuthState({
    this.status = AuthStatus.unknown,
    this.isLoading = false,
    this.errorMessage,
    this.pendingPhone,
    this.pendingRequestId,
  });

  final AuthStatus status;
  final bool isLoading;
  final String? errorMessage;
  final String? pendingPhone;
  final String? pendingRequestId;

  AuthState copyWith({
    AuthStatus? status,
    bool? isLoading,
    String? errorMessage,
    String? pendingPhone,
    String? pendingRequestId,
  }) =>
      AuthState(
        status: status ?? this.status,
        isLoading: isLoading ?? this.isLoading,
        errorMessage: errorMessage,
        pendingPhone: pendingPhone ?? this.pendingPhone,
        pendingRequestId: pendingRequestId ?? this.pendingRequestId,
      );
}

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.watch(apiClientProvider),
    ref.watch(secureStorageProvider),
  );
});

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier(this._repo, this._storage) : super(const AuthState()) {
    _init();
  }

  final AuthRepository _repo;
  final SecureStorage _storage;

  Future<void> _init() async {
    final hasToken = await _storage.hasToken();
    state = state.copyWith(
      status: hasToken ? AuthStatus.authenticated : AuthStatus.unauthenticated,
    );
  }

  Future<void> login(String phone) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final requestId = await _repo.login(phone: phone);
      state = state.copyWith(
        isLoading: false,
        pendingPhone: phone,
        pendingRequestId: requestId,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  Future<void> verifyOtp(String otp) async {
    if (state.pendingPhone == null || state.pendingRequestId == null) return;
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      await _repo.verifyOtp(
        phone: state.pendingPhone!,
        otp: otp,
        requestId: state.pendingRequestId!,
      );
      state = state.copyWith(
        isLoading: false,
        status: AuthStatus.authenticated,
        pendingPhone: null,
        pendingRequestId: null,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  Future<void> logout() async {
    await _repo.logout();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(
    ref.watch(authRepositoryProvider),
    ref.watch(secureStorageProvider),
  );
});
