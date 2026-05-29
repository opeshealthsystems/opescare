import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/timeline_event.dart';

class TimelineRepository {
  const TimelineRepository(this._client);
  final ApiClient _client;

  Future<List<TimelineEvent>> fetchTimeline({String? type}) async {
    final res = await _client.get(
      ApiEndpoints.timeline,
      params: type != null ? {'type': type} : null,
    );
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => TimelineEvent.fromJson(j as Map<String, dynamic>))
        .toList();
  }
}
