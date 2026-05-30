import 'package:equatable/equatable.dart';

enum ConsentStatus { pending, approved, denied, revoked, expired }

class ConsentRequest extends Equatable {
  const ConsentRequest({
    required this.id,
    required this.requestingFacility,
    required this.requestingRole,
    required this.purpose,
    required this.scopeLabels,
    required this.expiresAt,
    required this.status,
    required this.createdAt,
  });

  final String id;
  final String requestingFacility;
  final String requestingRole;
  final String purpose;
  final List<String> scopeLabels;
  final String expiresAt;
  final ConsentStatus status;
  final String createdAt;

  factory ConsentRequest.fromJson(Map<String, dynamic> json) {
    final rawStatus = json['status']?.toString() ?? 'pending';
    final status = ConsentStatus.values.firstWhere(
      (s) => s.name == rawStatus,
      orElse: () => ConsentStatus.pending,
    );

    // Backend sends requesting_facility as a plain string or nested {name: ...} map
    final facilityRaw = json['requesting_facility'];
    final facilityName = facilityRaw is Map
        ? facilityRaw['name']?.toString() ?? 'Unknown Facility'
        : facilityRaw?.toString() ?? 'Unknown Facility';

    // Backend uses requested_scopes; scope_labels is the legacy key
    final rawScopes = (json['requested_scopes'] ?? json['scope_labels']) as List? ?? [];

    return ConsentRequest(
      id:                   (json['consent_request_id'] ?? json['id'])?.toString() ?? '',
      requestingFacility:   facilityName,
      requestingRole:       json['requesting_role']?.toString() ?? '',
      purpose:              json['purpose']?.toString() ?? '',
      scopeLabels:          rawScopes.map((s) => s.toString()).toList(),
      expiresAt:            json['expires_at']?.toString() ?? '',
      status:               status,
      createdAt:            json['created_at']?.toString() ?? '',
    );
  }

  @override
  List<Object?> get props => [id, status];
}
