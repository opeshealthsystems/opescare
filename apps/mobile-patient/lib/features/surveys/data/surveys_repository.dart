import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/survey.dart';

class SurveysRepository {
  const SurveysRepository(this._client);
  final ApiClient _client;

  Future<List<Survey>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.surveys);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => Survey.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<Survey> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.survey(id));
    // Response shape: { data: <survey>, template: { questions: [...] } }
    final surveyJson = (res['data'] as Map<String, dynamic>? ?? {})
      ..['template'] = res['template'];
    return Survey.fromJson(surveyJson);
  }

  Future<void> submit(String id, Map<String, dynamic> responses) =>
      _client.post(ApiEndpoints.submitSurvey(id),
          body: {'responses': responses});
}
