import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/consent_request.dart';

class ConsentRepository {
  const ConsentRepository(this._client);
  final ApiClient _client;

  Future<List<ConsentRequest>> fetchRequests({String? status}) async {
    final res = await _client.get(
      ApiEndpoints.consentRequests,
      params: status != null ? {'status': status} : null,
    );
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => ConsentRequest.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<void> approve(String id) =>
      _client.post(ApiEndpoints.approveConsent(id));

  Future<void> deny(String id) =>
      _client.post(ApiEndpoints.denyConsent(id));

  Future<void> revoke(String id) =>
      _client.post(ApiEndpoints.revokeConsent(id));
}
