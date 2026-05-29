import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/document.dart';

class DocumentsRepository {
  const DocumentsRepository(this._client);
  final ApiClient _client;

  Future<List<PatientDocument>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.documents);
    final list = res['data'] as List? ?? [];
    return list.map((j) => PatientDocument.fromJson(j as Map<String, dynamic>)).toList();
  }
}
