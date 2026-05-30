import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/storage/secure_storage.dart';

class AuthRepository {
  const AuthRepository(this._client, this._storage);

  final ApiClient _client;
  final SecureStorage _storage;

  /// Primary login: email + password → direct 24-hour token.
  /// Uses the same credentials as the OpesCare patient portal.
  Future<String> loginWithEmail({
    required String email,
    required String password,
  }) async {
    final res = await _client.post(
      ApiEndpoints.loginEmail,
      body: {'email': email, 'password': password},
    );
    final token = res['access_token'].toString();
    await _storage.saveToken(token);
    await _storage.saveEmail(email);
    return token;
  }

  /// Legacy login step 1: phone_number + pin → OTP sent by SMS.
  Future<void> loginWithPhone({
    required String phoneNumber,
    required String pin,
    String? dateOfBirth,
  }) async {
    await _client.post(
      ApiEndpoints.loginPhone,
      body: {
        'phone_number': phoneNumber,
        'pin': pin,
        if (dateOfBirth != null) 'date_of_birth': dateOfBirth,
      },
    );
    await _storage.savePhone(phoneNumber);
  }

  /// Legacy login step 2: verify OTP → issues token.
  Future<String> verifyOtp({
    required String phoneNumber,
    required String otp,
  }) async {
    final res = await _client.post(
      ApiEndpoints.verifyOtp,
      body: {
        'phone_number': phoneNumber,
        'otp': otp,
      },
    );
    final token = res['access_token'].toString();
    await _storage.saveToken(token);
    return token;
  }

  Future<void> logout() => _storage.clearAll();
}
