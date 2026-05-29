import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/access_log.dart';

class AccessLogsRepository {
  const AccessLogsRepository(this._client);
  final ApiClient _client;

  Future<List<AccessLog>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.accessLogs);
    final list = res['data'] as List? ?? [];
    return list.map((j) => AccessLog.fromJson(j as Map<String, dynamic>)).toList();
  }
}
