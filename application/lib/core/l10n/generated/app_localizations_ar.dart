// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Arabic (`ar`).
class AppLocalizationsAr extends AppLocalizations {
  AppLocalizationsAr([String locale = 'ar']) : super(locale);

  @override
  String get appTitle => 'boxcards.shop';

  @override
  String get brandLogoLabel => 'boxcards.shop';

  @override
  String environmentBadge(String flavor) {
    String _temp0 = intl.Intl.selectLogic(flavor, {
      'dev': 'التطوير',
      'staging': 'التجريب',
      'other': 'الإنتاج',
    });
    return '$_temp0';
  }

  @override
  String get homeOverline => 'المتجر';

  @override
  String get homeTitle => 'مرحبًا بك في boxcards.shop';

  @override
  String get homeBody =>
      'بطاقات الهدايا وشحن الألعاب. يصل المتجر الكامل في الإصدارات القادمة من التطبيق.';

  @override
  String get switchLanguage => 'English';

  @override
  String get switchLanguageHint => 'تغيير لغة التطبيق إلى الإنجليزية';

  @override
  String get maintenanceTitle => 'سنعود قريبًا';

  @override
  String get maintenanceBody =>
      'المتجر قيد الصيانة. يُرجى المحاولة مجددًا بعد بضع دقائق.';

  @override
  String get updateRequiredTitle => 'التحديث مطلوب';

  @override
  String get updateRequiredBody =>
      'لم يعد هذا الإصدار من التطبيق مدعومًا. يُرجى تحديثه من متجر التطبيقات.';

  @override
  String get notFoundTitle => 'الصفحة غير موجودة';

  @override
  String get notFoundBody => 'الصفحة التي فتحتها غير موجودة.';

  @override
  String get backToHome => 'العودة إلى الرئيسية';
}
