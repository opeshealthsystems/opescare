import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/dashboard_summary.dart';

class HomeRepository {
  const HomeRepository(this._client);
  final ApiClient _client;

  Future<DashboardSummary> fetchSummary() async {
    // Parallel fetch for speed
    final results = await Future.wait([
      _client.get(ApiEndpoints.me),
      _client.get(ApiEndpoints.consentRequests,
          params: {'status': 'pending', 'per_page': '1'}),
      _client.get(ApiEndpoints.labs,
          params: {'status': 'released', 'per_page': '1'}),
      _client.get(ApiEndpoints.prescriptions,
          params: {'status': 'active', 'per_page': '1'}),
      _client.get(ApiEndpoints.appointments,
          params: {'upcoming': '1', 'per_page': '1'}),
      _client.get(ApiEndpoints.accessLogs, params: {'per_page': '1'}),
    ]);

    final me = results[0];
    final patient = me['data'] as Map<String, dynamic>? ?? me;

    final consentMeta = (results[1]['meta'] as Map<String, dynamic>?) ?? {};
    final labMeta     = (results[2]['meta'] as Map<String, dynamic>?) ?? {};
    final rxMeta      = (results[3]['meta'] as Map<String, dynamic>?) ?? {};
    final apptList    = results[4]['data'] as List? ?? [];
    final logMeta     = (results[5]['meta'] as Map<String, dynamic>?) ?? {};

    Map<String, dynamic>? nextAppt;
    if (apptList.isNotEmpty) {
      nextAppt = apptList.first as Map<String, dynamic>;
    }

    return DashboardSummary(
      patientName:             patient['display_name']?.toString() ?? 'Patient',
      healthId:                patient['health_id']?.toString() ?? '',
      isVerified:              patient['is_verified'] == true,
      pendingConsentCount:     (consentMeta['total'] as int?) ?? 0,
      unreadLabCount:          (labMeta['total'] as int?) ?? 0,
      activeRxCount:           (rxMeta['total'] as int?) ?? 0,
      nextAppointmentDate:     nextAppt?['scheduled_at']?.toString(),
      nextAppointmentFacility: (nextAppt?['facility'] as Map?)?['name']?.toString(),
      recentAccessCount:       (logMeta['total'] as int?) ?? 0,
    );
  }
}
