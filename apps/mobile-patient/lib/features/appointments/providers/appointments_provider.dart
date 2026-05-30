import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/appointments_repository.dart';
import '../models/appointment.dart';
import '../models/facility.dart';
import '../models/slot.dart';

final appointmentsRepositoryProvider = Provider<AppointmentsRepository>(
  (ref) => AppointmentsRepository(ref.watch(apiClientProvider)),
);

final appointmentsListProvider = FutureProvider<List<Appointment>>((ref) =>
    ref.watch(appointmentsRepositoryProvider).fetchAll());

final appointmentDetailProvider =
    FutureProvider.family<Appointment, String>((ref, id) =>
        ref.watch(appointmentsRepositoryProvider).fetchOne(id));

final facilitiesProvider = FutureProvider<List<Facility>>((ref) =>
    ref.watch(appointmentsRepositoryProvider).fetchFacilities());

final slotsProvider = FutureProvider.family<List<Slot>, String>(
    (ref, facilityId) =>
        ref.watch(appointmentsRepositoryProvider).fetchSlots(facilityId));
