import 'package:application/core/network/api_envelope.dart';

/// `meta.display_currency` of priced storefront responses.
String displayCurrencyOf(ApiEnvelope<Object?> envelope) {
  final value = envelope.meta['display_currency'];
  if (value is! String) {
    throw const FormatException('meta.display_currency is missing.');
  }
  return value;
}
