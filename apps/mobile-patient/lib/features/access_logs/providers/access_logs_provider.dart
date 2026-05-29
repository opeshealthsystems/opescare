import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/access_logs_repository.dart';
import '../models/access_log.dart';

final accessLogsRepositoryProvider = Provider<AccessLogsRepository>(
  (ref) => AccessLogsRepository(ref.watch(apiClientProvider)),
);

final accessLogsProvider = FutureProvider<List<AccessLog>>((ref) {
  return ref.watch(accessLogsRepositoryProvider).fetchAll();
});
