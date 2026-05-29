class DashboardSummary {
  const DashboardSummary({
    required this.patientName,
    required this.healthId,
    required this.isVerified,
    required this.pendingConsentCount,
    required this.unreadLabCount,
    required this.activeRxCount,
    required this.recentAccessCount,
    this.nextAppointmentDate,
    this.nextAppointmentFacility,
  });

  final String patientName;
  final String healthId;
  final bool isVerified;
  final int pendingConsentCount;
  final int unreadLabCount;
  final int activeRxCount;
  final int recentAccessCount;
  final String? nextAppointmentDate;
  final String? nextAppointmentFacility;
}
