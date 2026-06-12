import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/care_map_repository.dart';
import '../models/care_map_facility.dart';

final careMapRepositoryProvider = Provider<CareMapRepository>(
  (ref) => CareMapRepository(ref.watch(apiClientProvider)),
);

final careMapFacilitiesProvider = FutureProvider<List<CareMapFacility>>(
  (ref) => ref.watch(careMapRepositoryProvider).fetchFacilities(),
);

final careMapFilterProvider = StateProvider<String>((ref) => 'all');

final filteredFacilitiesProvider = Provider<AsyncValue<List<CareMapFacility>>>((ref) {
  final facilitiesAsync = ref.watch(careMapFacilitiesProvider);
  final filter = ref.watch(careMapFilterProvider);
  return facilitiesAsync.whenData((list) => filter == 'all'
      ? list
      : list.where((f) => f.type == filter).toList());
});
