import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../../../core/storage/secure_storage.dart';
import '../data/auth_repository.dart';

enum AuthStatus { unknown, authenticated, unauthenticated }

// Sentinel — distinguishes "caller passed null" from "caller didn't pass field"
const _keep = Object();

class AuthState {
  const AuthState({
    this.status = AuthStatus.unknown,
    this.isLoading = false,
    this.errorMessage,
    this.pendingPhone, // set during phone+OTP flow only
  });

  final AuthStatus status;
  final bool isLoading;
  final String? errorMessage;
  final String? pendingPhone;

  AuthState copyWith({
    AuthStatus? status,
    bool? isLoading,
    Object? errorMessage = _keep,
    Object? pendingPhone = _keep,
  }) =>
      AuthState(
        status:       status       ?? this.status,
        isLoading:    isLoading    ?? this.isLoading,
        errorMessage: errorMessage == _keep ? this.errorMessage : errorMessage as String?,
        pendingPhone: pendingPhone == _keep ? this.pendingPhone  : pendingPhone  as String?,
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

  // ── Primary auth: email + password ─────────────────────────────────────────

  /// Login with patient portal credentials (email + password).
  /// Issues a token directly — no OTP step required.
  Future<void> loginWithEmail(String email, String password) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      await _repo.loginWithEmail(email: email, password: password);
      state = state.copyWith(
        isLoading: false,
        status: AuthStatus.authenticated,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: _friendlyError(e.toString()),
      );
    }
  }

  // ── Legacy auth: phone + PIN + OTP ─────────────────────────────────────────

  Future<void> loginWithPhone(String phoneNumber, String pin) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      await _repo.loginWithPhone(phoneNumber: phoneNumber, pin: pin);
      state = state.copyWith(isLoading: false, pendingPhone: phoneNumber);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: _friendlyError(e.toString()),
      );
    }
  }

  Future<void> verifyOtp(String otp) async {
    if (state.pendingPhone == null) return;
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      await _repo.verifyOtp(phoneNumber: state.pendingPhone!, otp: otp);
      state = state.copyWith(
        isLoading: false,
        status: AuthStatus.authenticated,
        pendingPhone: null,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: _friendlyError(e.toString()),
      );
    }
  }

  // ── Logout ──────────────────────────────────────────────────────────────────

  Future<void> logout() async {
    await _repo.logout();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  // ── Helpers ─────────────────────────────────────────────────────────────────

  String _friendlyError(String raw) {
    if (raw.contains('401') || raw.contains('Invalid email') || raw.contains('Invalid credentials')) {
      return 'Incorrect email or password. Please try again.';
    }
    if (raw.contains('404') || raw.contains('not found')) {
      return 'No patient account found for this email. Contact your healthcare provider.';
    }
    if (raw.contains('network') || raw.contains('connection')) {
      return 'No internet connection. Check your network and try again.';
    }
    return 'Something went wrong. Please try again.';
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(
    ref.watch(authRepositoryProvider),
    ref.watch(secureStorageProvider),
  );
});
