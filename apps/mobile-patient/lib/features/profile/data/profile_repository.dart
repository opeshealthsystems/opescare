import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/patient_profile.dart';

class ProfileRepository {
  const ProfileRepository(this._client);
  final ApiClient _client;

  Future<PatientProfile> fetch() async {
    final res = await _client.get(ApiEndpoints.me);
    return PatientProfile.fromJson(res);
  }

  Future<PatientProfile> updateProfile(Map<String, dynamic> fields) async {
    final res = await _client.patch(ApiEndpoints.me, body: fields);
    return PatientProfile.fromJson(res);
  }
}
