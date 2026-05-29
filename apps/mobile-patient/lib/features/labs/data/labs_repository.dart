import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/lab_result.dart';

class LabsRepository {
  const LabsRepository(this._client);
  final ApiClient _client;

  Future<List<LabResult>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.labs);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => LabResult.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<LabResult> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.lab(id));
    return LabResult.fromJson(
        res['data'] as Map<String, dynamic>? ?? res);
  }
}
