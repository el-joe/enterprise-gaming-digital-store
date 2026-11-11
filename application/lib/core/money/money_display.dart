import 'package:application/core/money/currency_info.dart';
import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_formatter.dart';

/// Text for a server money object, never computed through `double`: the server's `formatted`
/// (`CurrencyConverter::present`) when present, else the string formatter with the currency's
/// symbol and exponent when known, else the code and the amount as sent. Western digits (owner
/// decision 2).
String moneyText(Money money, {Iterable<CurrencyInfo> currencies = const []}) {
  final formatted = money.formatted;
  if (formatted != null && formatted.isNotEmpty) {
    return formatted;
  }
  for (final info in currencies) {
    if (info.code == money.currency) {
      return MoneyFormatter.format(money, info);
    }
  }
  return '${money.currency} ${money.amountText}';
}

/// [text] as a left-to-right isolate (LRI … PDI), so an amount inside an Arabic sentence is
/// never reordered (owner decision 2: Western digits, amounts read left to right).
String ltrIsolate(String text) => '\u2066$text\u2069';
