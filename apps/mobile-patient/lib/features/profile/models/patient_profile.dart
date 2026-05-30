class PatientProfile {
  const PatientProfile({
    required this.healthId,
    required this.displayName,
    required this.firstName,
    required this.lastName,
    required this.status,
    required this.allergiesCount,
    required this.conditionsCount,
    this.phone,
    this.email,
    this.dob,
    this.sex,
    this.bloodGroup,
  });

  final String healthId, displayName, firstName, lastName, status;
  final String? phone, email, dob, sex, bloodGroup;
  final int allergiesCount, conditionsCount;

  factory PatientProfile.fromJson(Map<String, dynamic> json) => PatientProfile(
        healthId:        json['health_id']?.toString()       ?? '',
        displayName:     json['display_name']?.toString()    ?? '',
        firstName:       json['first_name']?.toString()      ?? '',
        lastName:        json['last_name']?.toString()       ?? '',
        status:          json['status']?.toString()          ?? 'active',
        phone:           json['phone']?.toString(),
        email:           json['email']?.toString(),
        dob:             json['dob']?.toString(),
        sex:             json['sex']?.toString(),
        bloodGroup:      json['blood_group']?.toString(),
        allergiesCount:  (json['allergies_count']  as num?)?.toInt() ?? 0,
        conditionsCount: (json['conditions_count'] as num?)?.toInt() ?? 0,
      );
}
