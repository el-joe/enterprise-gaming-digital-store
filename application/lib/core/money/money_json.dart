import 'package:application/core/money/money.dart';
import 'package:json_annotation/json_annotation.dart';

/// `json_serializable` converter for API money objects (`{amount, currency, formatted?}`).
///
/// Decoding goes through [Money.fromJson], so a JSON number for `amount` is a decode error.
final class MoneyConverter implements JsonConverter<Money, Object?> {
  const MoneyConverter();

  @override
  Money fromJson(Object? json) => Money.fromJson(json);

  @override
  Object? toJson(Money money) => money.toJson();
}

/// Nullable variant of [MoneyConverter].
final class NullableMoneyConverter implements JsonConverter<Money?, Object?> {
  const NullableMoneyConverter();

  @override
  Money? fromJson(Object? json) => json == null ? null : Money.fromJson(json);

  @override
  Object? toJson(Money? money) => money?.toJson();
}
