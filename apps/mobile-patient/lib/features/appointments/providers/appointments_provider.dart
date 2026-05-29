import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/appointments_repository.dart';
import '../models/appointment.dart';

final appointmentsRepositoryProvider = Provider<AppointmentsRepository>(
  (ref) => AppointmentsRepository(ref.watch(apiClientProvider)),
);

final appointmentsListProvider = FutureProvider<List<Appointment>>((ref) {
  return ref.watch(appointmentsRepositoryProvider).fetchAll();
});

final appointmentDetailProvider =
    FutureProvider.family<Appointment, String>((ref, id) {
  return ref.watch(appointmentsRepositoryProvider).fetchOne(id);
});
