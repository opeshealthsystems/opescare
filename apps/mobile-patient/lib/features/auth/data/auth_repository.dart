import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/storage/secure_storage.dart';

class AuthRepository {
  const AuthRepository(this._client, this._storage);

  final ApiClient _client;
  final SecureStorage _storage;

  /// Sends OTP to phone number. Returns request_id for verification step.
  Future<String> login({required String phone}) async {
    final res = await _client.post(ApiEndpoints.login, body: {'phone': phone});
    return res['request_id'].toString();
  }

  /// Verifies OTP, saves returned Bearer token, returns it.
  Future<String> verifyOtp({
    required String phone,
    required String otp,
    required String requestId,
  }) async {
    final res = await _client.post(ApiEndpoints.verifyOtp, body: {
      'phone': phone,
      'otp': otp,
      'request_id': requestId,
    });
    final token = res['token'].toString();
    await _storage.saveToken(token);
    await _storage.savePhone(phone);
    return token;
  }

  Future<void> logout() => _storage.clearAll();
}
