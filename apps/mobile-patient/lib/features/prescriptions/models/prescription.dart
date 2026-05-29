class Prescription {
  const Prescription({
    required this.id,
    required this.medicationName,
    required this.dosage,
    required this.frequency,
    required this.status,
    required this.prescribedBy,
    required this.facilityName,
    required this.prescribedAt,
    this.instructions,
    this.dispensedAt,
    this.expiresAt,
  });

  final String id;
  final String medicationName;
  final String dosage;
  final String frequency;
  final String status;
  final String prescribedBy;
  final String facilityName;
  final String prescribedAt;
  final String? instructions;
  final String? dispensedAt;
  final String? expiresAt;

  factory Prescription.fromJson(Map<String, dynamic> json) {
    final facility   = json['facility']  as Map? ?? {};
    final prescriber = json['prescriber'] as Map? ?? {};
    return Prescription(
      id:             json['id'].toString(),
      medicationName: json['medication_name']?.toString() ?? '',
      dosage:         json['dosage']?.toString() ?? '',
      frequency:      json['frequency']?.toString() ?? '',
      status:         json['status']?.toString() ?? 'active',
      prescribedBy:   prescriber['name']?.toString() ?? '',
      facilityName:   facility['name']?.toString() ?? '',
      prescribedAt:   json['prescribed_at']?.toString() ?? '',
      instructions:   json['instructions']?.toString(),
      dispensedAt:    json['dispensed_at']?.toString(),
      expiresAt:      json['expires_at']?.toString(),
    );
  }
}
