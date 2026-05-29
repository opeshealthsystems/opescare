import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/health_id_card.dart';

class HealthIdRepository {
  const HealthIdRepository(this._client);
  final ApiClient _client;

  Future<HealthIdCard> fetchCard() async {
    final res = await _client.get(ApiEndpoints.healthIdCard);
    return HealthIdCard.fromJson(res);
  }
}
