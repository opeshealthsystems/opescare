import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/insurance_plan_offer.dart';
import '../models/insurance_policy.dart';
import '../models/insurance_provider_offer.dart';

class InsuranceRepository {
  const InsuranceRepository(this._client);
  final ApiClient _client;

  Future<List<InsurancePolicy>> fetchPolicies() async {
    final res = await _client.get(ApiEndpoints.insurance);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => InsurancePolicy.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<List<InsuranceProviderOffer>> fetchMarketplace() async {
    final res = await _client.get(ApiEndpoints.insuranceMarketplace);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => InsuranceProviderOffer.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<InsurancePlanOffer> fetchPlanDetail(String planId) async {
    final res = await _client.get(ApiEndpoints.insuranceMarketplacePlan(planId));
    return InsurancePlanOffer.fromJson(res['data'] as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> purchasePlan(
    String planId, {
    required String paymentMethod,
    String? paymentReference,
  }) async {
    return await _client.post(
      ApiEndpoints.insurancePurchasePlan(planId),
      body: {
        'payment_method': paymentMethod,
        if (paymentReference != null) 'payment_reference': paymentReference,
      },
    );
  }
}
