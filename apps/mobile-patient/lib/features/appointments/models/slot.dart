class Slot {
  const Slot({
    required this.id,
    required this.facilityId,
    required this.startsAt,
    required this.serviceType,
    required this.providerName,
  });

  final String id, facilityId, startsAt, serviceType, providerName;

  factory Slot.fromJson(Map<String, dynamic> json) {
    final provider = json['provider'] as Map? ?? {};
    return Slot(
      id:           json['id']?.toString() ?? '',
      facilityId:   json['facility_id']?.toString() ?? '',
      startsAt:     json['starts_at']?.toString() ?? '',
      serviceType:  json['service_type']?.toString() ?? '',
      providerName: provider['name']?.toString() ?? '',
    );
  }
}
