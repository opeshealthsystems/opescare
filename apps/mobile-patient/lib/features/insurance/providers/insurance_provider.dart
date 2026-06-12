import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/insurance_repository.dart';
import '../models/insurance_plan_offer.dart';
import '../models/insurance_policy.dart';
import '../models/insurance_provider_offer.dart';

final insuranceRepositoryProvider = Provider<InsuranceRepository>(
  (ref) => InsuranceRepository(ref.watch(apiClientProvider)),
);

final insurancePoliciesProvider = FutureProvider<List<InsurancePolicy>>(
  (ref) => ref.watch(insuranceRepositoryProvider).fetchPolicies(),
);

final insuranceMarketplaceProvider = FutureProvider<List<InsuranceProviderOffer>>(
  (ref) => ref.watch(insuranceRepositoryProvider).fetchMarketplace(),
);

final insurancePlanDetailProvider =
    FutureProvider.family<InsurancePlanOffer, String>(
  (ref, planId) => ref.watch(insuranceRepositoryProvider).fetchPlanDetail(planId),
);
