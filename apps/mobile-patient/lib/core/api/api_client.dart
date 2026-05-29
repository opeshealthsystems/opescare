import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../storage/secure_storage.dart';
import 'api_exception.dart';

class ApiClient {
  ApiClient(this._storage) {
    _dio = Dio(BaseOptions(
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 30),
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
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

  Future<Map<String, dynamic>> get(String url, {Map<String, dynamic>? params}) async {
    try {
      final res = await _dio.get(url, queryParameters: params);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) { throw e.error as ApiException; }
  }

  Future<Map<String, dynamic>> post(String url, {Map<String, dynamic>? body}) async {
    try {
      final res = await _dio.post(url, data: body);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) { throw e.error as ApiException; }
  }

  Future<Map<String, dynamic>> patch(String url, {Map<String, dynamic>? body}) async {
    try {
      final res = await _dio.patch(url, data: body);
      return res.data as Map<String, dynamic>;
    } on DioException catch (e) { throw e.error as ApiException; }
  }

  Future<void> delete(String url) async {
    try { await _dio.delete(url); }
    on DioException catch (e) { throw e.error as ApiException; }
  }

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
final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(ref.watch(secureStorageProvider));
});
