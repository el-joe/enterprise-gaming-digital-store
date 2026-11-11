import 'package:flutter/widgets.dart';
import 'package:intl/intl.dart';

/// Locale-aware dates from server UTC times, shown in local time with Western digits in both
/// languages (owner decision 2). Arabic month names, Latin digits.
abstract final class AppDates {
  static String _locale(BuildContext context) {
    final code = Localizations.localeOf(context).languageCode;
    if (code == 'ar') {
      DateFormat.useNativeDigitsByDefaultFor('ar', false);
    }
    return code;
  }

  /// `10 Nov` / `10 نوفمبر`.
  static String day(BuildContext context, DateTime at) =>
      DateFormat('d MMM', _locale(context)).format(at.toLocal());

  /// `10 Nov, 12:00`.
  static String dayTime(BuildContext context, DateTime at) =>
      DateFormat('d MMM, HH:mm', _locale(context)).format(at.toLocal());

  /// `10 Nov 2026, 12:00`.
  static String full(BuildContext context, DateTime at) =>
      DateFormat('d MMM y, HH:mm', _locale(context)).format(at.toLocal());
}
