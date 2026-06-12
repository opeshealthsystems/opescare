import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/dashboard_summary.dart';

class HomeRepository {
  const HomeRepository(this._client);
  final ApiClient _client;

  Future<DashboardSummary> fetchSummary() async {
    final empty = <String, dynamic>{};

    // Parallel fetch — each call has its own fallback so one failure
    // never crashes the whole home screen.
    final results = await Future.wait([
      // 0: me
      _client.get(ApiEndpoints.me).catchError((_) => empty),
      // 1: consent requests (pending)
      _client.get(ApiEndpoints.consentRequests,
          params: {'status': 'pending', 'per_page': '1'}).catchError((_) => empty),
      // 2: labs (released)
      _client.get(ApiEndpoints.labs,
          params: {'status': 'released', 'per_page': '1'}).catchError((_) => empty),
      // 3: prescriptions (active)
      _client.get(ApiEndpoints.prescriptions,
          params: {'status': 'active', 'per_page': '1'}).catchError((_) => empty),
      // 4: appointments (upcoming)
      _client.get(ApiEndpoints.appointments,
          params: {'upcoming': '1', 'per_page': '1'}).catchError((_) => empty),
      // 5: access logs
      _client.get(ApiEndpoints.accessLogs,
          params: {'per_page': '1'}).catchError((_) => empty),
      // 6: care plans (active)
      _client.get(ApiEndpoints.carePlans).catchError((_) => empty),
      // 7: surveys (pending)
      _client.get(ApiEndpoints.surveys).catchError((_) => empty),
      // 8: insurance policies
      _client.get(ApiEndpoints.insurance).catchError((_) => empty),
      // 9: referrals
      _client.get(ApiEndpoints.referrals).catchError((_) => empty),
      // 10: documents
      _client.get(ApiEndpoints.documents,
          params: {'per_page': '1'}).catchError((_) => empty),
    ]);

    final me      = results[0];
    final patient = me['data'] as Map<String, dynamic>? ?? me;

    final consentMeta = (results[1]['meta'] as Map<String, dynamic>?) ?? {};
    final labMeta     = (results[2]['meta'] as Map<String, dynamic>?) ?? {};
    final rxMeta      = (results[3]['meta'] as Map<String, dynamic>?) ?? {};
    final apptList    = results[4]['data'] as List? ?? [];
    final logMeta     = (results[5]['meta'] as Map<String, dynamic>?) ?? {};

    // Care Plans
    final carePlanList = results[6]['data'] as List? ?? [];
    final activeCarePlanCount = carePlanList.length;

    // Surveys — count only 'sent' (pending) ones
    final surveyList = results[7]['data'] as List? ?? [];
    final pendingSurveyCount = surveyList
        .where((s) => (s as Map)['status'] == 'sent')
        .length;

    // Insurance — first active policy name
    final insuranceList = results[8]['data'] as List? ?? [];
    String? activeInsurancePlan;
    for (final policy in insuranceList) {
      if ((policy as Map)['is_active'] == true) {
        final plan = policy['plan'] as Map?;
        activeInsurancePlan = plan?['name']?.toString();
        break;
      }
    }

    // Referrals — count active/pending
    final referralList = results[9]['data'] as List? ?? [];
    final activeReferralCount = referralList
        .where((r) => !['completed', 'cancelled', 'rejected']
            .contains((r as Map)['status']))
        .length;

    // Documents
    final docMeta = (results[10]['meta'] as Map<String, dynamic>?) ?? {};
    final documentCount = (docMeta['total'] as num?)?.toInt() ?? 0;

    Map<String, dynamic>? nextAppt;
    if (apptList.isNotEmpty && apptList.first is Map) {
      nextAppt = apptList.first as Map<String, dynamic>;
    }

    return DashboardSummary(
      patientName:          patient['display_name']?.toString() ?? 'Patient',
      healthId:             patient['health_id']?.toString() ?? '',
      isVerified:           patient['is_verified'] == true ||
                            patient['status']?.toString() == 'active',
      pendingConsentCount:  (consentMeta['total'] as num?)?.toInt() ?? 0,
      unreadLabCount:       (labMeta['total'] as num?)?.toInt() ?? 0,
      activeRxCount:        (rxMeta['total'] as num?)?.toInt() ?? 0,
      nextAppointmentDate:  nextAppt?['scheduled_at']?.toString(),
      nextAppointmentFacility: (nextAppt?['facility'] as Map?)?['name']?.toString(),
      recentAccessCount:    (logMeta['total'] as num?)?.toInt() ?? 0,
      activeCarePlanCount:  activeCarePlanCount,
      pendingSurveyCount:   pendingSurveyCount,
      activeReferralCount:  activeReferralCount,
      documentCount:        documentCount,
      activeInsurancePlan:  activeInsurancePlan,
    );
  }
}
