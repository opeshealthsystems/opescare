class CareMapFacility {
  const CareMapFacility({
    required this.id,
    required this.name,
    required this.type,
    this.address,
    this.phone,
    this.distanceKm,
    this.rating,
    this.isOpen,
    this.openUntil,
    this.isConnected = false,
    this.specialties = const [],
  });

  final String id;
  final String name;
  final String type; // hospital | clinic | pharmacy | lab | emergency
  final String? address;
  final String? phone;
  final double? distanceKm;
  final double? rating;
  final bool? isOpen;
  final String? openUntil;
  final bool isConnected;
  final List<String> specialties;

  String get typeLabel {
    switch (type) {
      case 'hospital':  return 'Hospital';
      case 'clinic':    return 'Clinic';
      case 'pharmacy':  return 'Pharmacy';
      case 'lab':       return 'Laboratory';
      case 'emergency': return 'Emergency';
      default:          return 'Facility';
    }
  }

  String get distanceLabel {
    if (distanceKm == null) return '';
    if (distanceKm! < 1) return '${(distanceKm! * 1000).toInt()} m';
    return '${distanceKm!.toStringAsFixed(1)} km';
  }

  static double? _toDouble(dynamic v) {
    if (v == null) return null;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString());
  }

  factory CareMapFacility.fromJson(Map<String, dynamic> json) {
    return CareMapFacility(
      id:           json['id']?.toString() ?? '',
      name:         json['name']?.toString() ?? '',
      type:         json['type']?.toString() ?? 'clinic',
      address:      json['address']?.toString(),
      phone:        json['phone']?.toString(),
      distanceKm:   _toDouble(json['distance_km']),
      rating:       _toDouble(json['rating']),
      isOpen:       json['is_open'] as bool?,
      openUntil:    json['open_until']?.toString(),
      isConnected:  json['is_connected'] == true,
      specialties:  (json['specialties'] as List? ?? [])
                      .map((s) => s.toString())
                      .toList(),
    );
  }
}
