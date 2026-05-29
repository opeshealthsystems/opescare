import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/appointment.dart';

class AppointmentsRepository {
  const AppointmentsRepository(this._client);
  final ApiClient _client;

  Future<List<Appointment>> fetchAll() async {
    final res = await _client.get(ApiEndpoints.appointments);
    final list = res['data'] as List? ?? [];
    return list.map((j) => Appointment.fromJson(j as Map<String, dynamic>)).toList();
  }

  Future<Appointment> fetchOne(String id) async {
    final res = await _client.get(ApiEndpoints.appointment(id));
    return Appointment.fromJson(res['data'] as Map<String, dynamic>? ?? res);
  }

  Future<void> cancel(String id) =>
      _client.post(ApiEndpoints.cancelAppointment(id));
}
