import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/care_plans_repository.dart';
import '../models/care_plan.dart';

final carePlansRepositoryProvider = Provider<CarePlansRepository>(
  (ref) => CarePlansRepository(ref.watch(apiClientProvider)),
);

final carePlansListProvider = FutureProvider<List<CarePlan>>((ref) =>
    ref.watch(carePlansRepositoryProvider).fetchAll());

final carePlanDetailProvider =
    FutureProvider.family<CarePlan, String>((ref, id) =>
        ref.watch(carePlansRepositoryProvider).fetchOne(id));
