import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/prescriptions_repository.dart';
import '../models/prescription.dart';

final prescriptionsRepositoryProvider = Provider<PrescriptionsRepository>(
  (ref) => PrescriptionsRepository(ref.watch(apiClientProvider)),
);

final prescriptionsListProvider = FutureProvider<List<Prescription>>((ref) {
  return ref.watch(prescriptionsRepositoryProvider).fetchAll();
});

final prescriptionDetailProvider =
    FutureProvider.family<Prescription, String>((ref, id) {
  return ref.watch(prescriptionsRepositoryProvider).fetchOne(id);
});
