import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/care_plan.dart';

class CarePlansRepository {
  const CarePlansRepository(this._client);
  final ApiClient _client;

  Future<List<CarePlan>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.carePlans);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => CarePlan.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<CarePlan> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.carePlan(id));
    final data = res['data'] as Map<String, dynamic>? ?? res;
    return CarePlan.fromJson(data);
  }
}
