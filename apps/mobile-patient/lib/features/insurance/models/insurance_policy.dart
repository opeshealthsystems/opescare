class InsurancePolicy {
  const InsurancePolicy({
    required this.id,
    required this.policyNumber,
    required this.status,
    required this.startDate,
    this.endDate,
    this.planName,
    this.providerName,
  });

  final String id;
  final String policyNumber;
  final String status;
  final String startDate;
  final String? endDate;
  final String? planName;
  final String? providerName;

  bool get isActive => status == 'active';

  factory InsurancePolicy.fromJson(Map<String, dynamic> json) {
    final plan = json['plan'] as Map?;
    final provider = plan?['provider'] as Map?;
    return InsurancePolicy(
      id:            json['id']?.toString() ?? '',
      policyNumber:  json['policy_number']?.toString() ?? '',
      status:        json['status']?.toString() ?? 'inactive',
      startDate:     json['effective_date']?.toString() ?? '',
      endDate:       json['expiry_date']?.toString(),
      planName:      plan?['name']?.toString(),
      providerName:  provider?['name']?.toString() ??
                     plan?['provider_name']?.toString(),
    );
  }
}
