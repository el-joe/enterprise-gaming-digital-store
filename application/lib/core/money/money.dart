import 'package:decimal/decimal.dart';
import 'package:flutter/foundation.dart';

/// A money value from the API (`{ amount: "12.50", currency: "USD", formatted?: "$12.50" }`).
///
/// [amount] is a [Decimal]; the original string is kept in [amountText] so values such as
/// `expected_total` are sent back exactly as received. A JSON number for `amount` is rejected,
/// mirroring the backend's 422 for numeric money. There is no `double` anywhere in this type.
@immutable
final class Money {
  const Money._({
    required this.amount,
    required this.amountText,
    required this.currency,
    this.formatted,
  });

  /// Builds a value from a decimal string, for example `Money.parse('12.50', 'USD')`.
  factory Money.parse(String amount, String currency, {String? formatted}) {
    if (!_amountPattern.hasMatch(amount)) {
      throw FormatException('Money amount "$amount" is not a decimal string.');
    }
    if (!_currencyPattern.hasMatch(currency)) {
      throw FormatException(
        'Money currency "$currency" is not an ISO 4217 code.',
      );
    }
    return Money._(
      amount: Decimal.parse(amount),
      amountText: amount,
      currency: currency,
      formatted: formatted,
    );
  }

  /// Decodes `{amount, currency, formatted?}`. Throws [FormatException] for any other shape,
  /// including a numeric `amount`.
  factory Money.fromJson(Object? json) {
    if (json is! Map<String, dynamic>) {
      throw const FormatException('Money must be a JSON object.');
    }
    final amount = json['amount'];
    final currency = json['currency'];
    final formatted = json['formatted'];
    if (amount is num) {
      throw const FormatException(
        'Money amount must be a decimal string, not a JSON number.',
      );
    }
    if (amount is! String) {
      throw const FormatException('Money amount must be a decimal string.');
    }
    if (currency is! String) {
      throw const FormatException('Money currency must be a string.');
    }
    if (formatted != null && formatted is! String) {
      throw const FormatException('Money formatted must be a string.');
    }
    return Money.parse(amount, currency, formatted: formatted as String?);
  }

  static final _amountPattern = RegExp(r'^-?\d+(\.\d+)?$');
  static final _currencyPattern = RegExp(r'^[A-Z]{3}$');

  final Decimal amount;

  /// The amount exactly as the server sent it (scale preserved, for example `"10.500000"`).
  final String amountText;

  /// ISO 4217 code.
  final String currency;

  /// Server-formatted text (`CurrencyConverter::present`), when the server sent one.
  final String? formatted;

  /// Number of fraction digits in [amountText].
  int get scale {
    final dot = amountText.indexOf('.');
    return dot < 0 ? 0 : amountText.length - dot - 1;
  }

  bool get isNegative => amount < Decimal.zero;

  Map<String, Object?> toJson() => {
    'amount': amountText,
    'currency': currency,
    if (formatted != null) 'formatted': formatted,
  };

  @override
  bool operator ==(Object other) =>
      other is Money && other.currency == currency && other.amount == amount;

  @override
  int get hashCode => Object.hash(currency, amount);

  @override
  String toString() => '$amountText $currency';
}
