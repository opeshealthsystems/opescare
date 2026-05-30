class Facility {
  const Facility({
    required this.id,
    required this.name,
    required this.address,
    required this.phone,
    this.specialties = const [],
  });

  final String id, name, address, phone;
  final List<String> specialties;

  factory Facility.fromJson(Map<String, dynamic> json) => Facility(
        id:          json['id']?.toString() ?? '',
        name:        json['name']?.toString() ?? '',
        address:     json['address']?.toString() ?? '',
        phone:       json['phone']?.toString() ?? '',
        specialties: (json['specialties'] as List? ?? [])
            .map((s) => s.toString())
            .toList(),
      );
}
