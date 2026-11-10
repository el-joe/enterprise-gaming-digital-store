import 'package:application/core/money/currency_info.dart';
import 'package:application/core/money/money.dart';
import 'package:decimal/decimal.dart';

/// Digit shapes for formatted amounts.
enum MoneyDigits {
  /// 0-9 with `.` and `,`.
  latin,

  /// Arabic-Indic digits ٠-٩ with the Arabic decimal (٫) and thousands (٬) separators.
  arabicIndic,
}

/// String-based money formatting; values never pass through `double`.
///
/// The default output equals the backend's `CurrencyConverter::present`: the amount cut to the
/// currency exponent (as `bcadd` does, toward zero) prefixed by the symbol, for example
/// `$12.50`. A negative amount puts the sign before the symbol (`-$3.00`).
abstract final class MoneyFormatter {
  static const _arabicIndic = [
    '٠',
    '١',
    '٢',
    '٣',
    '٤',
    '٥',
    '٦',
    '٧',
    '٨',
    '٩',
  ];

  static String format(
    Money money,
    CurrencyInfo currency, {
    MoneyDigits digits = MoneyDigits.latin,
    bool grouping = false,
  }) {
    if (money.currency != currency.code) {
      throw ArgumentError.value(
        currency.code,
        'currency',
        'Does not match the money currency ${money.currency}',
      );
    }
    final number = formatAmount(
      money.amount,
      exponent: currency.exponent,
      digits: digits,
      grouping: grouping,
    );
    if (number.startsWith('-')) {
      return '-${currency.symbol}${number.substring(1)}';
    }
    return '${currency.symbol}$number';
  }

  /// Formats [amount] with exactly [exponent] fraction digits, truncating extra digits.
  static String formatAmount(
    Decimal amount, {
    required int exponent,
    MoneyDigits digits = MoneyDigits.latin,
    bool grouping = false,
  }) {
    if (exponent < 0) {
      throw ArgumentError.value(exponent, 'exponent', 'Must not be negative');
    }
    final fixed = amount.truncate(scale: exponent).toStringAsFixed(exponent);
    final negative = fixed.startsWith('-');
    final unsigned = negative ? fixed.substring(1) : fixed;
    final dot = unsigned.indexOf('.');
    var integer = dot < 0 ? unsigned : unsigned.substring(0, dot);
    final fraction = dot < 0 ? '' : unsigned.substring(dot + 1);
    if (grouping) {
      integer = _group(integer);
    }
    final isZero = !RegExp('[1-9]').hasMatch(unsigned);
    var text = fraction.isEmpty ? integer : '$integer.$fraction';
    if (negative && !isZero) {
      text = '-$text';
    }
    return digits == MoneyDigits.arabicIndic ? _toArabicIndic(text) : text;
  }

  static String _group(String integer) {
    final buffer = StringBuffer();
    for (var i = 0; i < integer.length; i++) {
      if (i > 0 && (integer.length - i) % 3 == 0) {
        buffer.write(',');
      }
      buffer.write(integer[i]);
    }
    return buffer.toString();
  }

  static String _toArabicIndic(String text) {
    final buffer = StringBuffer();
    for (final unit in text.split('')) {
      final digit = int.tryParse(unit);
      if (digit != null) {
        buffer.write(_arabicIndic[digit]);
      } else if (unit == '.') {
        buffer.write('٫');
      } else if (unit == ',') {
        buffer.write('٬');
      } else {
        buffer.write(unit);
      }
    }
    return buffer.toString();
  }
}
