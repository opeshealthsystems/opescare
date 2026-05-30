abstract final class ApiEndpoints {
  static const String _base =
      String.fromEnvironment('API_BASE_URL', defaultValue: 'http://10.0.2.2/api');

  static String get baseUrl => _base;

  // Primary login: email + password (same credentials as patient portal)
  static String get loginEmail    => '$_base/mobile/auth/login-email';
  // Legacy phone + PIN + OTP flow
  static String get loginPhone    => '$_base/mobile/auth/login';
  static String get verifyOtp     => '$_base/mobile/auth/otp/verify';
  static String get me            => '$_base/mobile/me';
  static String get timeline      => '$_base/mobile/timeline';
  static String get healthIdCard  => '$_base/mobile/health-id-card';
  static String get allergies     => '$_base/mobile/allergies';
  static String get clinical      => '$_base/mobile/clinical';
  static String get immunizations => '$_base/mobile/immunizations';
  static String get consentRequests => '$_base/mobile/consent-requests';
  static String approveConsent(String id) => '$_base/mobile/consent-requests/$id/approve';
  static String denyConsent(String id)    => '$_base/mobile/consent-requests/$id/deny';
  static String revokeConsent(String id)  => '$_base/mobile/consents/$id/revoke';
  static String get accessLogs    => '$_base/mobile/access-logs';
  static String get correctionRequests => '$_base/mobile/correction-requests';
  static String get dataExportRequests => '$_base/mobile/data-export-requests';
  static String dataExportDownload(String id) => '$_base/mobile/data-exports/$id/download';
  static String get labs          => '$_base/mobile/labs';
  static String lab(String id)    => '$_base/mobile/labs/$id';
  static String get prescriptions    => '$_base/mobile/prescriptions';
  static String prescription(String id) => '$_base/mobile/prescriptions/$id';
  static String get appointments     => '$_base/mobile/appointments';
  static String appointment(String id) => '$_base/mobile/appointments/$id';
  static String cancelAppointment(String id) => '$_base/mobile/appointments/$id/cancel';
  static String get facilities       => '$_base/mobile/facilities';
  static String facility(String id)  => '$_base/mobile/facilities/$id';
  static String facilitySlots(String id) => '$_base/mobile/facilities/$id/slots';
  static String get documents        => '$_base/mobile/documents';
  static String document(String id)  => '$_base/mobile/documents/$id';
  static String get settings         => '$_base/mobile/settings';
  static String get pushTokens       => '$_base/mobile/push-tokens';
  static String pushToken(String id) => '$_base/mobile/push-tokens/$id';
  static String get offlinePolicies  => '$_base/mobile/offline/policies';
}
