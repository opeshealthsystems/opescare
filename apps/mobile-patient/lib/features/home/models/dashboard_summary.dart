class DashboardSummary {
  const DashboardSummary({
    required this.patientName,
    required this.healthId,
    required this.isVerified,
    required this.pendingConsentCount,
    required this.unreadLabCount,
    required this.activeRxCount,
    required this.recentAccessCount,
    required this.activeCarePlanCount,
    required this.pendingSurveyCount,
    required this.activeReferralCount,
    required this.documentCount,
    this.nextAppointmentDate,
    this.nextAppointmentFacility,
    this.activeInsurancePlan,
  });

  final String patientName;
  final String healthId;
  final bool isVerified;

  // Existing
  final int pendingConsentCount;
  final int unreadLabCount;
  final int activeRxCount;
  final int recentAccessCount;

  // New
  final int activeCarePlanCount;
  final int pendingSurveyCount;
  final int activeReferralCount;
  final int documentCount;
  final String? nextAppointmentDate;
  final String? nextAppointmentFacility;
  final String? activeInsurancePlan; // plan name of first active policy
}
