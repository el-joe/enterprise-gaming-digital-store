import 'dart:math';

const _alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

/// Generates ULIDs (26 Crockford base32 chars: 48-bit millisecond time + 80 random bits from a
/// cryptographically secure source). Used for `X-Request-Id` and `Idempotency-Key`.
final class UlidGenerator {
  UlidGenerator({Random? random, DateTime Function()? clock})
    : _random = random ?? Random.secure(),
      _clock = clock ?? DateTime.now;

  final Random _random;
  final DateTime Function() _clock;

  String next() {
    final buffer = StringBuffer();
    var time = _clock().millisecondsSinceEpoch;
    final timeChars = List<String>.filled(10, '0');
    for (var i = 9; i >= 0; i--) {
      timeChars[i] = _alphabet[time % 32];
      time ~/= 32;
    }
    buffer.writeAll(timeChars);
    for (var i = 0; i < 16; i++) {
      buffer.write(_alphabet[_random.nextInt(32)]);
    }
    return buffer.toString();
  }
}

final _shared = UlidGenerator();

/// A new ULID from the shared secure generator.
String newUlid() => _shared.next();

/// Whether [value] is a syntactically valid ULID.
bool isUlid(String value) =>
    RegExp(r'^[0-7][0-9A-HJKMNP-TV-Z]{25}$').hasMatch(value);
