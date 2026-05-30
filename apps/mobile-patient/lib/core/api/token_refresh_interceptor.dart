import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../storage/secure_storage.dart';
import 'api_endpoints.dart';

/// Intercepts 401 responses, attempts token refresh once, then retries.
/// If refresh fails, calls [onUnauthenticated] (which triggers forceLogout).
class TokenRefreshInterceptor extends Interceptor {
  TokenRefreshInterceptor({
    required this.storage,
    required this.onUnauthenticated,
  }) : _refreshDio = Dio(BaseOptions(
          connectTimeout: const Duration(seconds: 15),
          receiveTimeout: const Duration(seconds: 15),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        ));

  final SecureStorage storage;
  final VoidCallback onUnauthenticated;
  final Dio _refreshDio;
  bool _isRefreshing = false;

  @override
  Future<void> onError(
      DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode != 401 || _isRefreshing) {
      handler.next(err);
      return;
    }
    _isRefreshing = true;
    try {
      final oldToken = await storage.getToken();
      if (oldToken == null) {
        onUnauthenticated();
        handler.next(err);
        return;
      }
      final refreshRes = await _refreshDio.post(
        '${ApiEndpoints.baseUrl}/mobile/auth/refresh',
        data: {'token': oldToken},
        options: Options(
            headers: {'Authorization': 'Bearer $oldToken'}),
      );
      final newToken =
          refreshRes.data['access_token']?.toString();
      if (newToken == null) throw Exception('No token in refresh response');
      await storage.saveToken(newToken);

      // Retry original request with new token
      final opts = err.requestOptions;
      opts.headers['Authorization'] = 'Bearer $newToken';
      final retryResponse = await _refreshDio.fetch(opts);
      handler.resolve(retryResponse);
    } catch (_) {
      onUnauthenticated();
      handler.next(err);
    } finally {
      _isRefreshing = false;
    }
  }
}
