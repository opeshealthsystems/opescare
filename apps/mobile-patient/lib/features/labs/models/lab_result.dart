class LabResult {
  const LabResult({
    required this.id,
    required this.testName,
    required this.status,
    required this.facilityName,
    required this.orderedAt,
    this.resultSummary,
    this.resultValue,
    this.referenceRange,
    this.unit,
    this.isCritical,
    this.releasedAt,
    this.notes,
  });

  final String id;
  final String testName;
  final String status;
  final String facilityName;
  final String orderedAt;
  final String? resultSummary;
  final String? resultValue;
  final String? referenceRange;
  final String? unit;
  final bool? isCritical;
  final String? releasedAt;
  final String? notes;

  factory LabResult.fromJson(Map<String, dynamic> json) {
    final facility = json['facility'] as Map? ?? {};
    return LabResult(
      id:             json['id'].toString(),
      testName:       json['test_name']?.toString() ?? '',
      status:         json['status']?.toString() ?? 'pending',
      facilityName:   facility['name']?.toString() ?? '',
      orderedAt:      json['ordered_at']?.toString() ?? '',
      resultSummary:  json['result_summary']?.toString(),
      resultValue:    json['result_value']?.toString(),
      referenceRange: json['reference_range']?.toString(),
      unit:           json['unit']?.toString(),
      isCritical:     json['is_critical'] as bool?,
      releasedAt:     json['released_at']?.toString(),
      notes:          json['notes']?.toString(),
    );
  }
}
