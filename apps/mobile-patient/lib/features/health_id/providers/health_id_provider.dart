import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/health_id_repository.dart';
import '../models/health_id_card.dart';

final healthIdRepositoryProvider = Provider<HealthIdRepository>(
  (ref) => HealthIdRepository(ref.watch(apiClientProvider)),
);

final healthIdCardProvider = FutureProvider<HealthIdCard>((ref) {
  return ref.watch(healthIdRepositoryProvider).fetchCard();
});
