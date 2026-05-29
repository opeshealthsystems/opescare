class TimelineEvent {
  const TimelineEvent({
    required this.id,
    required this.type,
    required this.title,
    required this.description,
    required this.facilityName,
    required this.occurredAt,
    required this.isVerified,
    this.sensitivityLabel,
  });

  final String id;
  final String type;
  final String title;
  final String description;
  final String facilityName;
  final String occurredAt;
  final bool isVerified;
  final String? sensitivityLabel;

  factory TimelineEvent.fromJson(Map<String, dynamic> json) {
    final facility = json['facility'] as Map? ?? {};
    return TimelineEvent(
      id:               json['id'].toString(),
      type:             json['type']?.toString() ?? 'event',
      title:            json['title']?.toString() ?? '',
      description:      json['description']?.toString() ?? '',
      facilityName:     facility['name']?.toString() ?? 'Unknown Facility',
      occurredAt:       json['occurred_at']?.toString() ?? '',
      isVerified:       json['is_verified'] == true,
      sensitivityLabel: json['sensitivity_label']?.toString(),
    );
  }
}
