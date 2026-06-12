class InsurancePlanOffer {
  const InsurancePlanOffer({
    required this.id,
    required this.name,
    this.planType,
    this.description,
    this.monthlyPremium,
    this.annualPremium,
    this.deductible,
    this.copayPercentage,
    this.cashlessAvailable = false,
    this.requiresPreauthorization = false,
    this.coveredServices,
    this.provider,
  });

  final String id;
  final String name;
  final String? planType;
  final String? description;
  final double? monthlyPremium;
  final double? annualPremium;
  final double? deductible;
  final double? copayPercentage;
  final bool cashlessAvailable;
  final bool requiresPreauthorization;
  final String? coveredServices;
  final InsurancePlanProvider? provider;

  static double? _toDouble(dynamic v) {
    if (v == null) return null;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString());
  }

  factory InsurancePlanOffer.fromJson(Map<String, dynamic> json) {
    return InsurancePlanOffer(
      id:                       json['id']?.toString() ?? '',
      name:                     json['name']?.toString() ?? '',
      planType:                 json['plan_type']?.toString(),
      description:              json['description']?.toString(),
      monthlyPremium:           _toDouble(json['monthly_premium']),
      annualPremium:            _toDouble(json['annual_premium']),
      deductible:               _toDouble(json['deductible']),
      copayPercentage:          _toDouble(json['copay_percentage']),
      cashlessAvailable:        json['cashless_available'] == true,
      requiresPreauthorization: json['requires_preauthorization'] == true,
      coveredServices:          json['covered_services']?.toString(),
      provider: json['provider'] != null
          ? InsurancePlanProvider.fromJson(json['provider'] as Map<String, dynamic>)
          : null,
    );
  }
}

class InsurancePlanProvider {
  const InsurancePlanProvider({
    required this.id,
    required this.name,
    this.contactEmail,
    this.contactPhone,
  });

  final String id;
  final String name;
  final String? contactEmail;
  final String? contactPhone;

  factory InsurancePlanProvider.fromJson(Map<String, dynamic> json) {
    return InsurancePlanProvider(
      id:           json['id']?.toString() ?? '',
      name:         json['name']?.toString() ?? '',
      contactEmail: json['contact_email']?.toString(),
      contactPhone: json['contact_phone']?.toString(),
    );
  }
}
