import 'package:flutter/foundation.dart';

/// A currency from `GET /api/v1/storefront/currencies` (`code`, `symbol`, `exponent`).
@immutable
final class CurrencyInfo {
  const CurrencyInfo({
    required this.code,
    required this.symbol,
    required this.exponent,
  });

  factory CurrencyInfo.fromJson(Object? json) {
    if (json is! Map<String, dynamic>) {
      throw const FormatException('Currency must be a JSON object.');
    }
    final code = json['code'];
    final symbol = json['symbol'];
    final exponent = json['exponent'];
    if (code is! String || !RegExp(r'^[A-Z]{3}$').hasMatch(code)) {
      throw const FormatException('Currency code must be an ISO 4217 code.');
    }
    if (symbol is! String) {
      throw const FormatException('Currency symbol must be a string.');
    }
    if (exponent is! int || exponent < 0 || exponent > 6) {
      throw const FormatException('Currency exponent must be an integer 0..6.');
    }
    return CurrencyInfo(code: code, symbol: symbol, exponent: exponent);
  }

  final String code;
  final String symbol;

  /// Minor-unit digits (2 for USD, 0 for JPY, 3 for KWD).
  final int exponent;

  @override
  bool operator ==(Object other) =>
      other is CurrencyInfo &&
      other.code == code &&
      other.symbol == symbol &&
      other.exponent == exponent;

  @override
  int get hashCode => Object.hash(code, symbol, exponent);
}
