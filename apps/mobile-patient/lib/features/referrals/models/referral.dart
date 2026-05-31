class Referral {
  const Referral({
    required this.id,
    required this.referringFacility,
    required this.receivingFacility,
    required this.reason,
    required this.urgency,
    required this.referredAt,
    this.completedAt,
  });

  final String id;
  final String referringFacility;
  final String receivingFacility;
  final String reason;
  final String urgency;
  final String referredAt;
  final String? completedAt;

  bool get isCompleted => completedAt != null && completedAt!.isNotEmpty;

  factory Referral.fromJson(Map<String, dynamic> json) {
    return Referral(
      id:                json['id']?.toString() ?? '',
      referringFacility: json['referring_facility']?.toString() ?? '',
      receivingFacility: json['receiving_facility']?.toString() ?? '',
      reason:            json['reason']?.toString() ?? '',
      urgency:           json['urgency']?.toString() ?? 'routine',
      referredAt:        json['referred_at']?.toString() ?? '',
      completedAt:       json['completed_at']?.toString(),
    );
  }
}
