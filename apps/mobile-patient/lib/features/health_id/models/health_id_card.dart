import 'package:equatable/equatable.dart';

class HealthIdCard extends Equatable {
  const HealthIdCard({
    required this.healthId,
    required this.displayName,
    required this.dateOfBirth,
    required this.sex,
    required this.bloodGroup,
    required this.isVerified,
    required this.issuedAt,
    this.photoUrl,
    this.allergySummary,
    this.emergencyContact,
  });

  final String healthId;
  final String displayName;
  final String dateOfBirth;
  final String sex;
  final String bloodGroup;
  final bool isVerified;
  final String issuedAt;
  final String? photoUrl;
  final String? allergySummary;
  final String? emergencyContact;

  factory HealthIdCard.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;
    return HealthIdCard(
      healthId:         data['health_id']?.toString() ?? '',
      displayName:      data['display_name']?.toString() ?? '',
      // Backend uses 'dob'; older shape used 'date_of_birth'
      dateOfBirth:      (data['dob'] ?? data['date_of_birth'])?.toString() ?? '',
      sex:              data['sex']?.toString() ?? '',
      // Backend uses 'blood_type'; older shape used 'blood_group'
      bloodGroup:       (data['blood_type'] ?? data['blood_group'])?.toString() ?? '',
      // Backend sends 'status'; treat 'active' as verified
      isVerified:       data['is_verified'] == true || data['status']?.toString() == 'active',
      issuedAt:         data['issued_at']?.toString() ?? '',
      photoUrl:         data['photo_url']?.toString(),
      allergySummary:   data['allergy_summary']?.toString(),
      emergencyContact: data['emergency_contact']?.toString(),
    );
  }

  @override
  List<Object?> get props => [healthId, displayName, isVerified];
}
