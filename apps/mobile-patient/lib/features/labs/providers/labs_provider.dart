import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/labs_repository.dart';
import '../models/lab_result.dart';

final labsRepositoryProvider = Provider<LabsRepository>(
  (ref) => LabsRepository(ref.watch(apiClientProvider)),
);

final labsListProvider = FutureProvider<List<LabResult>>((ref) {
  return ref.watch(labsRepositoryProvider).fetchAll();
});

final labDetailProvider =
    FutureProvider.family<LabResult, String>((ref, id) {
  return ref.watch(labsRepositoryProvider).fetchOne(id);
});
