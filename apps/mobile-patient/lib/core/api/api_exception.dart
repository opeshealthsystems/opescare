import 'package:equatable/equatable.dart';

enum ApiErrorType { unauthorized, forbidden, notFound, validation, server, network, unknown }

class ApiException extends Equatable implements Exception {
  const ApiException({
    required this.type,
    required this.message,
    this.statusCode,
    this.errors,
  });

  final ApiErrorType type;
  final String message;
  final int? statusCode;
  final Map<String, List<String>>? errors;

  factory ApiException.fromStatusCode(int code, String message,
      {Map<String, List<String>>? errors}) {
    final type = switch (code) {
      401 => ApiErrorType.unauthorized,
      403 => ApiErrorType.forbidden,
      404 => ApiErrorType.notFound,
      422 => ApiErrorType.validation,
      >= 500 => ApiErrorType.server,
      _ => ApiErrorType.unknown,
    };
    return ApiException(type: type, message: message, statusCode: code, errors: errors);
  }

  factory ApiException.network() => const ApiException(
        type: ApiErrorType.network,
        message: 'No internet connection. Please check your network.',
      );

  @override
  List<Object?> get props => [type, message, statusCode, errors];
}
