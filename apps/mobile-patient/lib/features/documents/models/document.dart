class PatientDocument {
  const PatientDocument({
    required this.id,
    required this.title,
    required this.type,
    required this.facilityName,
    required this.issuedAt,
    required this.isVerified,
    this.fileUrl,
  });

  final String id, title, type, facilityName, issuedAt;
  final bool isVerified;
  final String? fileUrl;

  factory PatientDocument.fromJson(Map<String, dynamic> json) {
    final facility = json['facility'] as Map? ?? {};
    return PatientDocument(
      id:           json['id']?.toString() ?? '',
      title:        json['title']?.toString() ?? '',
      type:         json['document_type']?.toString() ?? '',
      facilityName: facility['name']?.toString() ?? '',
      issuedAt:     json['issued_at']?.toString() ?? '',
      isVerified:   json['is_verified'] == true,
      fileUrl:      json['file_url']?.toString(),
    );
  }
}
