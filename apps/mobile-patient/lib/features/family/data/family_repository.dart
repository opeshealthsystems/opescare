import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/family_member.dart';

class FamilyRepository {
  const FamilyRepository(this._client);
  final ApiClient _client;

  Future<List<FamilyMember>> fetchMembers() async {
    final res = await _client.get(ApiEndpoints.family);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => FamilyMember.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<List<FamilyInvitation>> fetchInvitations() async {
    final res = await _client.get(ApiEndpoints.familyInvitations);
    final list = res['data'] as List? ?? [];
    return list
        .map((j) => FamilyInvitation.fromJson(j as Map<String, dynamic>))
        .toList();
  }

  Future<void> addMember(Map<String, dynamic> data) async {
    await _client.post(ApiEndpoints.family, body: data);
  }

  Future<void> sendInvitation(Map<String, dynamic> data) async {
    await _client.post(ApiEndpoints.familyInvitations, body: data);
  }

  Future<void> cancelInvitation(String id) async {
    await _client.delete(ApiEndpoints.familyInvitation(id));
  }
}
