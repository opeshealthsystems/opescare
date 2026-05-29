class Appointment {
  const Appointment({
    required this.id,
    required this.facilityName,
    required this.facilityId,
    required this.providerName,
    required this.serviceType,
    required this.scheduledAt,
    required this.status,
    this.notes,
    this.checkInCode,
  });

  final String id, facilityName, facilityId, providerName, serviceType;
  final String scheduledAt, status;
  final String? notes, checkInCode;

  factory Appointment.fromJson(Map<String, dynamic> json) {
    final facility = json['facility'] as Map? ?? {};
    final provider = json['provider'] as Map? ?? {};
    return Appointment(
      id:           json['id']?.toString() ?? '',
      facilityId:   facility['id']?.toString() ?? '',
      facilityName: facility['name']?.toString() ?? '',
      providerName: provider['name']?.toString() ?? '',
      serviceType:  json['service_type']?.toString() ?? '',
      scheduledAt:  json['scheduled_at']?.toString() ?? '',
      status:       json['status']?.toString() ?? 'pending',
      notes:        json['notes']?.toString(),
      checkInCode:  json['check_in_code']?.toString(),
    );
  }
}
