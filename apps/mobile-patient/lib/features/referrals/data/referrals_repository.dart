import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/referral.dart';

class ReferralsRepository {
  const ReferralsRepository(this._client);
  final ApiClient _client;

  Future<List<Referral>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.referrals);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => Referral.fromJson(j as Map<String, dynamic>))
        .toList();
  }
}
