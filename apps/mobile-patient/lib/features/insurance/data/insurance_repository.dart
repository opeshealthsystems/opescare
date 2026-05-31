import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/insurance_policy.dart';

class InsuranceRepository {
  const InsuranceRepository(this._client);
  final ApiClient _client;

  Future<List<InsurancePolicy>> fetchPolicies() async {
    final res = await _client.get(ApiEndpoints.insurancePolicies);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => InsurancePolicy.fromJson(j as Map<String, dynamic>))
        .toList();
  }
}
