import 'package:application/core/network/api_envelope.dart';
import 'package:application/core/network/failure.dart';

/// Decodes a success envelope with [decode] for `data`. Any error thrown while decoding (a missing
/// key, a wrong type, money as a JSON number) becomes a [FormatException], which the error mapper
/// turns into a `DecodingFailure`.
ApiEnvelope<T> decodeEnvelope<T>(
  Object? body,
  T Function(Object? data) decode,
) {
  try {
    return ApiEnvelope<T>.fromJson(body, decode);
  } on FormatException {
    rethrow;
  } on Failure {
    rethrow;
  } on Object catch (error) {
    throw FormatException('Response does not match the contract: $error');
  }
}

/// `data` as a JSON object.
Map<String, dynamic> asJsonObject(Object? data) {
  if (data is Map<String, dynamic>) {
    return data;
  }
  throw const FormatException('Expected a JSON object.');
}

/// The JSON object under [key] of `data`.
Map<String, dynamic> objectAt(Object? data, String key) =>
    asJsonObject(asJsonObject(data)[key]);

/// A JSON list decoded item by item.
List<T> listOf<T>(Object? data, T Function(Map<String, dynamic> json) decode) {
  if (data is! List) {
    throw const FormatException('Expected a JSON list.');
  }
  return [for (final item in data) decode(asJsonObject(item))];
}

/// The JSON list under [key] of `data`, decoded item by item.
List<T> listAt<T>(
  Object? data,
  String key,
  T Function(Map<String, dynamic> json) decode,
) => listOf(asJsonObject(data)[key], decode);
