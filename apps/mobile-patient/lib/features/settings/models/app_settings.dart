class AppSettings {
  const AppSettings({
    required this.notificationsEnabled,
    required this.language,
    required this.receiveLabAlerts,
    required this.receiveConsentAlerts,
    required this.receiveAppointmentReminders,
  });

  final bool notificationsEnabled;
  final String language;
  final bool receiveLabAlerts;
  final bool receiveConsentAlerts;
  final bool receiveAppointmentReminders;

  factory AppSettings.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;
    return AppSettings(
      notificationsEnabled:        data['notifications_enabled'] == true,
      language:                    data['language']?.toString() ?? 'en',
      receiveLabAlerts:            data['receive_lab_alerts'] == true,
      receiveConsentAlerts:        data['receive_consent_alerts'] == true,
      receiveAppointmentReminders: data['receive_appointment_reminders'] == true,
    );
  }

  Map<String, dynamic> toJson() => {
    'notifications_enabled':         notificationsEnabled,
    'language':                      language,
    'receive_lab_alerts':            receiveLabAlerts,
    'receive_consent_alerts':        receiveConsentAlerts,
    'receive_appointment_reminders': receiveAppointmentReminders,
  };

  AppSettings copyWith({
    bool? notificationsEnabled,
    String? language,
    bool? receiveLabAlerts,
    bool? receiveConsentAlerts,
    bool? receiveAppointmentReminders,
  }) =>
      AppSettings(
        notificationsEnabled:        notificationsEnabled ?? this.notificationsEnabled,
        language:                    language ?? this.language,
        receiveLabAlerts:            receiveLabAlerts ?? this.receiveLabAlerts,
        receiveConsentAlerts:        receiveConsentAlerts ?? this.receiveConsentAlerts,
        receiveAppointmentReminders: receiveAppointmentReminders ?? this.receiveAppointmentReminders,
      );
}
