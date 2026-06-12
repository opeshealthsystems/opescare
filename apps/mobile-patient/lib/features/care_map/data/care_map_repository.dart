import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/care_map_facility.dart';

class CareMapRepository {
  const CareMapRepository(this._client);
  final ApiClient _client;

  Future<List<CareMapFacility>> fetchFacilities({String? type}) async {
    final res = await _client.get(ApiEndpoints.careMap,
        params: type != null ? {'type': type} : null);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => CareMapFacility.fromJson(j as Map<String, dynamic>))
        .toList();
  }
}
