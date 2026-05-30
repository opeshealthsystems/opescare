import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../storage/secure_storage.dart';
import 'api_exception.dart';
import 'token_refresh_interceptor.dart';

class ApiClient {
  ApiClient(this._storage) {
    _dio = Dio(BaseOptions(
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 30),
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
    ));
    _dio.interceptors.add(TokenRefreshInterceptor(
      storage: _storage,
      onUnauthenticated: () => onUnauthenticated?.call(),
    ));
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _storage.getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) {
        handler.reject(DioException(
          requestOptions: error.requestOptions,
          error: _mapError(error),
          type: error.type,
        ));
      },
    ));
  }

  final SecureStorage _storage;
  late final Dio _dio;

  /// Set by the provider after construction to break the circular dependency.
  /// Called when a 401 refresh attempt fails — should trigger [AuthNotifier.forceLogout].
  VoidCallback? onUnauthenticated;

  Future<Map<String, dynamic>> get(String url, {Map<String, dynamic>? params}) async {
    try {
      final res = await _dio.get(url, queryParameters: params);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) { _rethrow(e); }
  }

  Future<Map<String, dynamic>> post(String url, {Map<String, dynamic>? body}) async {
    try {
      final res = await _dio.post(url, data: body);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) { _rethrow(e); }
  }

  Future<Map<String, dynamic>> patch(String url, {Map<String, dynamic>? body}) async {
    try {
      final res = await _dio.patch(url, data: body);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) { _rethrow(e); }
  }

  Future<void> delete(String url) async {
    try { await _dio.delete(url); }
    on DioException catch (e) { _rethrow(e); }
  }

  Never _rethrow(DioException e) =>
      throw (e.error is ApiException ? e.error as ApiException : _mapError(e));

  ApiException _mapError(DioException e) {
    if (e.type == DioExceptionType.connectionError ||
        e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout) {
      return ApiException.network();
    }
    final code = e.response?.statusCode ?? 0;
    final data = e.response?.data;
    final message = (data is Map && data['message'] != null)
        ? data['message'].toString()
        : 'An error occurred. Please try again.';
    Map<String, List<String>>? errors;
    if (data is Map && data['errors'] is Map) {
      errors = (data['errors'] as Map).map(
        (k, v) => MapEntry(k.toString(), (v as List).map((e) => e.toString()).toList()),
      );
    }
    return ApiException.fromStatusCode(code, message, errors: errors);
  }
}

final secureStorageProvider = Provider<SecureStorage>((ref) => SecureStorage());

/// Provides the [ApiClient] instance.
///
/// NOTE: [ApiClient.onUnauthenticated] is wired by [authClientWiringProvider]
/// in auth_provider.dart, which runs on first read of [authProvider].
/// This keeps api_client.dart free of any import from the auth layer,
/// preventing a circular dependency.
final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(ref.watch(secureStorageProvider));
});
