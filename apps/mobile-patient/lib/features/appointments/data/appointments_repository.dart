import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../models/appointment.dart';
import '../models/facility.dart';
import '../models/slot.dart';

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

  Future<List<Facility>> fetchFacilities() async {
    final res = await _client.get(ApiEndpoints.facilities);
    final list = res['data'] as List? ?? [];
    return list.map((j) => Facility.fromJson(j as Map<String, dynamic>)).toList();
  }

  Future<List<Slot>> fetchSlots(String facilityId) async {
    final res = await _client.get(ApiEndpoints.facilitySlots(facilityId));
    final list = res['data'] as List? ?? [];
    return list.map((j) => Slot.fromJson(j as Map<String, dynamic>)).toList();
  }

  Future<Appointment> book({
    required String facilityId,
    required String slotId,
    String? notes,
  }) async {
    final res = await _client.post(
      ApiEndpoints.appointments,
      body: {
        'facility_id': facilityId,
        'slot_id': slotId,
        if (notes != null && notes.isNotEmpty) 'notes': notes,
      },
    );
    return Appointment.fromJson(res['data'] as Map<String, dynamic>? ?? res);
  }
}
