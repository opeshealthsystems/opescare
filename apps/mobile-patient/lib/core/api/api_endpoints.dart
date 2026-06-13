abstract final class ApiEndpoints {
  // Injected at build time:
  //   --dart-define=API_BASE_URL=https://api.opescare.cm/api
  // The debug default points at the local Laragon dev host; release builds MUST
  // override it with the production HTTPS URL (enforced by [baseUrl] below).
  static const String _base =
      String.fromEnvironment('API_BASE_URL', defaultValue: 'http://opescare.test/api');

  /// All endpoint getters resolve through this so the release guard cannot be
  /// bypassed. In a release (product) build it refuses to fall back to the local
  /// HTTP dev default — a misconfigured production build fails loudly instead of
  /// silently shipping cleartext PHI traffic to a dev host.
  static String get baseUrl {
    const bool isRelease = bool.fromEnvironment('dart.vm.product');
    if (isRelease && _base.startsWith('http://')) {
      throw StateError(
        'OpesCare release build requires an HTTPS API_BASE_URL. '
        'Build with: --dart-define=API_BASE_URL=https://<prod-host>/api',
      );
    }
    return _base;
  }

  // Primary login: email + password (same credentials as patient portal)
  static String get loginEmail    => '${baseUrl}/mobile/auth/login-email';
  // Legacy phone + PIN + OTP flow
  static String get loginPhone    => '${baseUrl}/mobile/auth/login';
  static String get verifyOtp     => '${baseUrl}/mobile/auth/otp/verify';
  static String get me            => '${baseUrl}/mobile/me';
  static String get timeline      => '${baseUrl}/mobile/timeline';
  static String get healthIdCard  => '${baseUrl}/mobile/health-id-card';
  static String get allergies     => '${baseUrl}/mobile/allergies';
  static String get clinical      => '${baseUrl}/mobile/clinical';
  static String get immunizations => '${baseUrl}/mobile/immunizations';
  static String get consentRequests => '${baseUrl}/mobile/consent-requests';
  static String approveConsent(String id) => '${baseUrl}/mobile/consent-requests/$id/approve';
  static String denyConsent(String id)    => '${baseUrl}/mobile/consent-requests/$id/deny';
  static String revokeConsent(String id)  => '${baseUrl}/mobile/consents/$id/revoke';
  static String get accessLogs    => '${baseUrl}/mobile/access-logs';
  static String get correctionRequests => '${baseUrl}/mobile/correction-requests';
  static String get dataExportRequests => '${baseUrl}/mobile/data-export-requests';
  static String dataExportDownload(String id) => '${baseUrl}/mobile/data-exports/$id/download';
  static String get labs          => '${baseUrl}/mobile/labs';
  static String lab(String id)    => '${baseUrl}/mobile/labs/$id';
  static String get prescriptions    => '${baseUrl}/mobile/prescriptions';
  static String prescription(String id) => '${baseUrl}/mobile/prescriptions/$id';
  static String get appointments     => '${baseUrl}/mobile/appointments';
  static String appointment(String id) => '${baseUrl}/mobile/appointments/$id';
  static String cancelAppointment(String id) => '${baseUrl}/mobile/appointments/$id/cancel';
  static String get facilities       => '${baseUrl}/mobile/facilities';
  static String facility(String id)  => '${baseUrl}/mobile/facilities/$id';
  static String facilitySlots(String id) => '${baseUrl}/mobile/facilities/$id/slots';
  static String get documents        => '${baseUrl}/mobile/documents';
  static String document(String id)  => '${baseUrl}/mobile/documents/$id';
  static String get settings         => '${baseUrl}/mobile/settings';
  static String get pushTokens       => '${baseUrl}/mobile/push-tokens';
  static String pushToken(String id) => '${baseUrl}/mobile/push-tokens/$id';
  static String get offlinePolicies  => '${baseUrl}/mobile/offline/policies';

  // Care Plans (read-only for patient)
  static String get carePlans           => '${baseUrl}/mobile/care-plans';
  static String carePlan(String id)     => '${baseUrl}/mobile/care-plans/$id';

  // Patient Surveys
  static String get surveys                   => '${baseUrl}/mobile/surveys';
  static String survey(String id)             => '${baseUrl}/mobile/surveys/$id';
  static String submitSurvey(String id)       => '${baseUrl}/mobile/surveys/$id/submit';

  // Medical Record Export
  static String get exportRecordsPdf   => '${baseUrl}/mobile/medical-records/export/pdf';
  static String get exportRecordsFhir  => '${baseUrl}/mobile/medical-records/export/fhir';

  // Referrals
  static String get referrals          => '${baseUrl}/mobile/referrals';

  // Insurance — policies (GET /mobile/insurance)
  static String get insurance => '${baseUrl}/mobile/insurance';

  // Insurance marketplace
  static String get insuranceMarketplace       => '${baseUrl}/mobile/insurance/marketplace';
  static String insuranceMarketplacePlan(String id) => '${baseUrl}/mobile/insurance/marketplace/plans/$id';
  static String insurancePurchasePlan(String id)    => '${baseUrl}/mobile/insurance/marketplace/plans/$id/purchase';

  // QR temporary
  static String get generateTemporaryQr => '${baseUrl}/mobile/qr/temporary';

  // Family
  static String get family                    => '${baseUrl}/mobile/family';
  static String get familyInvitations         => '${baseUrl}/mobile/family/invitations';
  static String familyMember(String id)       => '${baseUrl}/mobile/family/members/$id';
  static String familyInvitation(String id)   => '${baseUrl}/mobile/family/invitations/$id';

  // Care Map (reuses facilities endpoint with type filter)
  static String get careMap => '${baseUrl}/mobile/facilities';
}
