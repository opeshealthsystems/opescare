class FamilyMember {
  const FamilyMember({
    required this.id,
    required this.name,
    required this.relationship,
    required this.status,
    this.healthId,
    this.dateOfBirth,
    this.age,
    this.activeRxCount = 0,
    this.upcomingAppointment,
    this.hasAlert = false,
    this.alertMessage,
    this.isPending = false,
  });

  final String id;
  final String name;
  final String relationship;
  final String status;
  final String? healthId;
  final String? dateOfBirth;
  final int? age;
  final int activeRxCount;
  final String? upcomingAppointment;
  final bool hasAlert;
  final String? alertMessage;
  final bool isPending;

  String get initials {
    final parts = name.trim().split(' ');
    if (parts.length >= 2) return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
    return name.isNotEmpty ? name[0].toUpperCase() : '?';
  }

  factory FamilyMember.fromJson(Map<String, dynamic> json) {
    final patient = json['patient'] as Map<String, dynamic>? ?? json;
    return FamilyMember(
      id:                  json['id']?.toString() ?? '',
      name:                patient['full_name']?.toString() ??
                           patient['name']?.toString() ?? 'Unknown',
      relationship:        json['relationship']?.toString() ?? '',
      status:              json['status']?.toString() ?? 'active',
      healthId:            patient['health_id']?.toString(),
      dateOfBirth:         patient['date_of_birth']?.toString(),
      age:                 (patient['age'] as num?)?.toInt(),
      activeRxCount:       (json['active_rx_count'] as num?)?.toInt() ?? 0,
      upcomingAppointment: json['upcoming_appointment']?.toString(),
      hasAlert:            json['has_alert'] == true,
      alertMessage:        json['alert_message']?.toString(),
      isPending:           json['status']?.toString() == 'pending',
    );
  }
}

class FamilyInvitation {
  const FamilyInvitation({
    required this.id,
    required this.contact,
    required this.relationship,
    required this.sentAt,
    required this.expiresAt,
    this.method = 'phone',
  });

  final String id;
  final String contact;
  final String relationship;
  final String sentAt;
  final String expiresAt;
  final String method;

  factory FamilyInvitation.fromJson(Map<String, dynamic> json) {
    return FamilyInvitation(
      id:           json['id']?.toString() ?? '',
      contact:      json['contact']?.toString() ?? '',
      relationship: json['relationship']?.toString() ?? '',
      sentAt:       json['sent_at']?.toString() ?? '',
      expiresAt:    json['expires_at']?.toString() ?? '',
      method:       json['method']?.toString() ?? 'phone',
    );
  }
}
