// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'boxcards.shop';

  @override
  String get brandLogoLabel => 'boxcards.shop';

  @override
  String environmentBadge(String flavor) {
    String _temp0 = intl.Intl.selectLogic(flavor, {
      'dev': 'Development',
      'staging': 'Staging',
      'other': 'Production',
    });
    return '$_temp0';
  }

  @override
  String get homeOverline => 'Store';

  @override
  String get homeTitle => 'Welcome to boxcards.shop';

  @override
  String get homeBody =>
      'Gift cards and game top-ups. The full store arrives in the next app releases.';

  @override
  String get switchLanguage => 'العربية';

  @override
  String get switchLanguageHint => 'Switch the app language to Arabic';

  @override
  String get maintenanceTitle => 'We\'ll be right back';

  @override
  String get maintenanceBody =>
      'The store is under maintenance. Please try again in a few minutes.';

  @override
  String get updateRequiredTitle => 'Update required';

  @override
  String get updateRequiredBody =>
      'This version of the app is no longer supported. Please update it from your app store.';

  @override
  String get notFoundTitle => 'Page not found';

  @override
  String get notFoundBody => 'The page you opened does not exist.';

  @override
  String get backToHome => 'Back to home';
}
