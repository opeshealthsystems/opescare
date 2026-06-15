// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for French (`fr`).
class AppLocalizationsFr extends AppLocalizations {
  AppLocalizationsFr([String locale = 'fr']) : super(locale);

  @override
  String get appTitle => 'OpesCare';

  @override
  String get navHome => 'Accueil';

  @override
  String get navHealthId => 'Identifiant Santé';

  @override
  String get navAppointments => 'Rendez-vous';

  @override
  String get navRecords => 'Dossiers';

  @override
  String get navSettings => 'Paramètres';

  @override
  String get actionSignIn => 'Se connecter';

  @override
  String get actionSignOut => 'Se déconnecter';

  @override
  String get actionContinue => 'Continuer';

  @override
  String get actionCancel => 'Annuler';

  @override
  String get actionSave => 'Enregistrer';

  @override
  String get actionRetry => 'Réessayer';

  @override
  String get actionClose => 'Fermer';

  @override
  String get authEmail => 'E-mail';

  @override
  String get authPassword => 'Mot de passe';

  @override
  String get authShowPassword => 'Afficher le mot de passe';

  @override
  String get authHidePassword => 'Masquer le mot de passe';

  @override
  String get authForgotPassword => 'Mot de passe oublié ?';

  @override
  String get authLoginFailed =>
      'Échec de la connexion. Vérifiez vos informations et réessayez.';

  @override
  String homeGreeting(String name) {
    return 'Bonjour, $name';
  }

  @override
  String get homeYourHealthId => 'Votre Identifiant Santé';

  @override
  String get homeGenerateQr => 'Générer un QR temporaire';

  @override
  String get consentTitle => 'Demandes de consentement';

  @override
  String get consentApprove => 'Approuver';

  @override
  String get consentDeny => 'Refuser';

  @override
  String consentPendingCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count demandes en attente',
      one: '1 demande en attente',
      zero: 'Aucune demande en attente',
    );
    return '$_temp0';
  }

  @override
  String get labsTitle => 'Résultats de laboratoire';

  @override
  String get prescriptionsTitle => 'Ordonnances';

  @override
  String get appointmentsBook => 'Prendre rendez-vous';

  @override
  String get appointmentsUpcoming => 'À venir';

  @override
  String get errorNetwork =>
      'Aucune connexion. Vérifiez votre réseau et réessayez.';

  @override
  String get errorGeneric => 'Une erreur s\'est produite. Veuillez réessayer.';

  @override
  String get offlineBanner =>
      'Vous êtes hors ligne. Certaines données peuvent être obsolètes.';

  @override
  String get securityUpdateRequiredTitle => 'Mise à jour requise';

  @override
  String get securityUpdateRequiredBody =>
      'Une version plus récente d\'OpesCare est requise pour continuer. Veuillez mettre à jour pour protéger vos données.';

  @override
  String get securityUpdateNow => 'Mettre à jour';
}
