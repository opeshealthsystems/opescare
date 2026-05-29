class AccessLog {
  const AccessLog({
    required this.id,
    required this.facilityName,
    required this.accessorRole,
    required this.purpose,
    required this.dataCategory,
    required this.accessedAt,
    required this.isEmergency,
  });

  final String id, facilityName, accessorRole, purpose, dataCategory, accessedAt;
  final bool isEmergency;

  factory AccessLog.fromJson(Map<String, dynamic> json) {
    final facility = json['facility'] as Map? ?? {};
    return AccessLog(
      id:           json['id']?.toString() ?? '',
      facilityName: facility['name']?.toString() ?? '',
      accessorRole: json['accessor_role']?.toString() ?? '',
      purpose:      json['purpose']?.toString() ?? '',
      dataCategory: json['data_category']?.toString() ?? '',
      accessedAt:   json['accessed_at']?.toString() ?? '',
      isEmergency:  json['is_emergency'] == true,
    );
  }
}
