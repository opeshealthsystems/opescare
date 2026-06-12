import 'insurance_plan_offer.dart';

class InsuranceProviderOffer {
  const InsuranceProviderOffer({
    required this.id,
    required this.name,
    this.code,
    this.logoUrl,
    this.contactEmail,
    this.contactPhone,
    required this.plans,
  });

  final String id;
  final String name;
  final String? code;
  final String? logoUrl;
  final String? contactEmail;
  final String? contactPhone;
  final List<InsurancePlanOffer> plans;

  factory InsuranceProviderOffer.fromJson(Map<String, dynamic> json) {
    final rawPlans = json['plans'] as List? ?? [];
    return InsuranceProviderOffer(
      id:           json['id']?.toString() ?? '',
      name:         json['name']?.toString() ?? '',
      code:         json['code']?.toString(),
      logoUrl:      json['logo_url']?.toString(),
      contactEmail: json['contact_email']?.toString(),
      contactPhone: json['contact_phone']?.toString(),
      plans: rawPlans
          .map((p) => InsurancePlanOffer.fromJson(p as Map<String, dynamic>))
          .toList(),
    );
  }
}
