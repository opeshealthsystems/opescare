import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/prescription.dart';

class PrescriptionsRepository {
  const PrescriptionsRepository(this._client);
  final ApiClient _client;

  Future<List<Prescription>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.prescriptions);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => Prescription.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<Prescription> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.prescription(id));
    return Prescription.fromJson(
        res['data'] as Map<String, dynamic>? ?? res);
  }
}
