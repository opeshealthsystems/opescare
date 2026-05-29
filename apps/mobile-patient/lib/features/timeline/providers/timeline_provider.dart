import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/timeline_repository.dart';
import '../models/timeline_event.dart';

final timelineRepositoryProvider = Provider<TimelineRepository>(
  (ref) => TimelineRepository(ref.watch(apiClientProvider)),
);

final timelineProvider =
    FutureProvider.family<List<TimelineEvent>, String?>((ref, type) {
  return ref.watch(timelineRepositoryProvider).fetchTimeline(type: type);
});
