import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/insurance_repository.dart';
import '../models/insurance_policy.dart';

final insuranceRepositoryProvider = Provider<InsuranceRepository>(
  (ref) => InsuranceRepository(ref.watch(apiClientProvider)),
);

final insurancePoliciesProvider = FutureProvider<List<InsurancePolicy>>(
  (ref) => ref.watch(insuranceRepositoryProvider).fetchPolicies(),
);
