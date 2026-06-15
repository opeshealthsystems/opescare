// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'OpesCare';

  @override
  String get navHome => 'Home';

  @override
  String get navHealthId => 'Health ID';

  @override
  String get navAppointments => 'Appointments';

  @override
  String get navRecords => 'Records';

  @override
  String get navSettings => 'Settings';

  @override
  String get actionSignIn => 'Sign in';

  @override
  String get actionSignOut => 'Sign out';

  @override
  String get actionContinue => 'Continue';

  @override
  String get actionCancel => 'Cancel';

  @override
  String get actionSave => 'Save';

  @override
  String get actionRetry => 'Retry';

  @override
  String get actionClose => 'Close';

  @override
  String get authEmail => 'Email';

  @override
  String get authPassword => 'Password';

  @override
  String get authShowPassword => 'Show password';

  @override
  String get authHidePassword => 'Hide password';

  @override
  String get authForgotPassword => 'Forgot password?';

  @override
  String get authLoginFailed =>
      'Sign-in failed. Check your details and try again.';

  @override
  String homeGreeting(String name) {
    return 'Hello, $name';
  }

  @override
  String get homeYourHealthId => 'Your Health ID';

  @override
  String get homeGenerateQr => 'Generate temporary QR';

  @override
  String get consentTitle => 'Consent requests';

  @override
  String get consentApprove => 'Approve';

  @override
  String get consentDeny => 'Deny';

  @override
  String consentPendingCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count pending requests',
      one: '1 pending request',
      zero: 'No pending requests',
    );
    return '$_temp0';
  }

  @override
  String get labsTitle => 'Lab results';

  @override
  String get prescriptionsTitle => 'Prescriptions';

  @override
  String get appointmentsBook => 'Book appointment';

  @override
  String get appointmentsUpcoming => 'Upcoming';

  @override
  String get errorNetwork => 'No connection. Check your network and try again.';

  @override
  String get errorGeneric => 'Something went wrong. Please try again.';

  @override
  String get offlineBanner => 'You are offline. Some data may be out of date.';

  @override
  String get securityUpdateRequiredTitle => 'Update required';

  @override
  String get securityUpdateRequiredBody =>
      'A newer version of OpesCare is required to continue. Please update to keep your data secure.';

  @override
  String get securityUpdateNow => 'Update now';
}
