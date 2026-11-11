import 'package:application/features/product/domain/entities/product.dart';

/// Why a delivery value is refused, mirroring `DeliveryFieldValidator` (backend, ADR-037): the
/// field definitions of `GET /storefront/catalog/products/{slug}` are exactly what
/// `POST /cart/items` validates, so the app runs the same checks before sending. The server stays
/// authoritative; its 422 `errors.delivery_values.{key}` are shown beside the field as well.
enum DeliveryFieldError {
  /// A required field is empty (after trimming).
  required,

  /// Longer than `max_length` characters (code points, as PHP `mb_strlen`).
  tooLong,

  /// `number`: digits only (`^\d+$`).
  notDigits,

  /// `email`: not an email address.
  invalidEmail,

  /// `select`: not one of `options[].value`.
  invalidOption,

  /// `pattern` does not match the whole trimmed value.
  invalidFormat,
}

/// Field types of `DeliveryFieldDefinitionInputType`.
abstract final class DeliveryFieldType {
  static const text = 'text';
  static const number = 'number';
  static const email = 'email';
  static const select = 'select';
}

/// The server's maximum when a definition carries no `max_length`.
const defaultDeliveryMaxLength = 255;

/// PHP `trim()` without a character list: space, tab, newline, carriage return, NUL and vertical
/// tab only (Dart's `trim` also strips other Unicode spaces, which the server keeps).
String phpTrim(String value) {
  const stripped = {0x20, 0x09, 0x0A, 0x0D, 0x00, 0x0B};
  var start = 0;
  var end = value.length;
  while (start < end && stripped.contains(value.codeUnitAt(start))) {
    start++;
  }
  while (end > start && stripped.contains(value.codeUnitAt(end - 1))) {
    end--;
  }
  return value.substring(start, end);
}

final _digits = RegExp(r'^\d+$');

/// A practical subset of PHP `FILTER_VALIDATE_EMAIL`: one `@`, a dot-atom local part of at most
/// 64 characters and a dotted domain of labels that do not start or end with a hyphen. Addresses
/// it accepts are accepted by the server; the server decides the rare remaining forms.
final _email = RegExp(
  r"^[A-Za-z0-9!#$%&'*+/=?^_`{|}~-]+(\.[A-Za-z0-9!#$%&'*+/=?^_`{|}~-]+)*"
  r'@([A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+'
  r'[A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?$',
);

bool _isEmail(String value) {
  if (!_email.hasMatch(value)) {
    return false;
  }
  final at = value.indexOf('@');
  return at <= 64 && value.length <= 254;
}

/// The admin pattern compiled as the server anchors it (`^(?:pattern)$`, Unicode). Null when the
/// expression is not valid in Dart's dialect: the app then leaves that check to the server
/// instead of refusing values the server may accept.
RegExp? compileDeliveryPattern(String? pattern) {
  if (pattern == null || pattern.isEmpty) {
    return null;
  }
  try {
    return RegExp('^(?:$pattern)\$', unicode: true);
  } on FormatException {
    return null;
  }
}

/// Validates one raw value against [field] exactly in the server's order: empty (required or
/// skipped), length, type, then pattern. Returns null when the value is accepted (an empty
/// optional value is accepted and not sent).
DeliveryFieldError? validateDeliveryValue(DeliveryField field, String raw) {
  final value = phpTrim(raw);
  if (value.isEmpty) {
    return field.required ? DeliveryFieldError.required : null;
  }
  if (value.runes.length > (field.maxLength ?? defaultDeliveryMaxLength)) {
    return DeliveryFieldError.tooLong;
  }
  switch (field.type) {
    case DeliveryFieldType.number when !_digits.hasMatch(value):
      return DeliveryFieldError.notDigits;
    case DeliveryFieldType.email when !_isEmail(value):
      return DeliveryFieldError.invalidEmail;
    case DeliveryFieldType.select
        when !(field.options ?? const []).any((o) => o.value == value):
      return DeliveryFieldError.invalidOption;
  }
  final pattern = compileDeliveryPattern(field.pattern);
  if (pattern != null && !pattern.hasMatch(value)) {
    return DeliveryFieldError.invalidFormat;
  }
  return null;
}

/// Errors of every field in [fields] for [values] (missing keys count as empty).
Map<String, DeliveryFieldError> validateDeliveryValues(
  List<DeliveryField> fields,
  Map<String, String> values,
) => {
  for (final field in fields)
    field.key: ?validateDeliveryValue(field, values[field.key] ?? ''),
};

/// The `delivery_values` body: trimmed, non-empty values of the defined fields only (the server
/// refuses unknown keys).
Map<String, String> deliveryValuesToSend(
  List<DeliveryField> fields,
  Map<String, String> values,
) => {
  for (final field in fields)
    if (phpTrim(values[field.key] ?? '') case final value when value.isNotEmpty)
      field.key: value,
};

/// Arabic-Indic (U+0660..0669) and Persian (U+06F0..06F9) digits as Western digits, for `number`
/// fields typed on an Arabic keyboard (the server accepts ASCII digits only; owner decision 2).
String westernDigits(String value) {
  final buffer = StringBuffer();
  for (final rune in value.runes) {
    if (rune >= 0x0660 && rune <= 0x0669) {
      buffer.writeCharCode(0x30 + rune - 0x0660);
    } else if (rune >= 0x06F0 && rune <= 0x06F9) {
      buffer.writeCharCode(0x30 + rune - 0x06F0);
    } else {
      buffer.writeCharCode(rune);
    }
  }
  return buffer.toString();
}
