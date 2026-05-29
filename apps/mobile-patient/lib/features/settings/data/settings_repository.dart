import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/app_settings.dart';

class SettingsRepository {
  const SettingsRepository(this._client);
  final ApiClient _client;

  Future<AppSettings> fetch() async {
    final res = await _client.get(ApiEndpoints.settings);
    return AppSettings.fromJson(res);
  }

  Future<AppSettings> update(AppSettings settings) async {
    final res = await _client.patch(
        ApiEndpoints.settings, body: settings.toJson());
    return AppSettings.fromJson(res);
  }
}
