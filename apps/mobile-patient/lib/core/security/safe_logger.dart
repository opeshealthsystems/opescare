import 'package:flutter/foundation.dart';

/// PHI-safe logger for the patient app.
///
/// A health app must never leak protected health information (PHI) or secrets
/// into device logs or (later) crash reports. [SafeLogger]:
///   1. redacts sensitive values (tokens, passwords, health IDs, contact info,
///      dates of birth) from any string before it is emitted, and
///   2. only writes in debug builds — in release ([kReleaseMode]) it is a no-op,
///      so nothing sensitive is ever logged in production.
///
/// Usage:
///   SafeLogger.log('Auth response: ${SafeLogger.redact(response.body)}');
/// or simply:
///   SafeLogger.log(response.body); // redaction is applied automatically
abstract final class SafeLogger {
  /// Matches `key: value`, `key=value`, and `"key": "value"` JSON pairs for a
  /// set of sensitive field names, and replaces the value with `***`.
  static final RegExp _sensitivePair = RegExp(
    r'("?\b(?:token|access_token|refresh_token|authorization|password|pin|'
    r'health_id|healthid|otp|dob|date_of_birth|phone|email|secret|api[_-]?key)\b"?\s*[:=]\s*)'
    r'("?[^",}\s]+"?)',
    caseSensitive: false,
  );

  /// Matches bare bearer tokens anywhere in a string.
  static final RegExp _bearer =
      RegExp(r'Bearer\s+[A-Za-z0-9\-._~+/]+=*', caseSensitive: false);

  /// Returns [input] with sensitive values masked. Safe to call on any string.
  static String redact(String input) {
    return input
        .replaceAllMapped(_sensitivePair, (m) => '${m[1]}***')
        .replaceAll(_bearer, 'Bearer ***');
  }

  /// Logs [message] (after redaction) in debug builds only. No-op in release.
  static void log(String message, {String? tag}) {
    if (kReleaseMode) return;
    final prefix = tag == null ? '' : '[$tag] ';
    debugPrint('$prefix${redact(message)}');
  }
}
