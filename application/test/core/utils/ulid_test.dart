import 'dart:math';

import 'package:application/core/utils/ulid.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('produces 26 Crockford base32 characters', () {
    for (var i = 0; i < 100; i++) {
      expect(isUlid(newUlid()), isTrue);
    }
  });

  test('encodes the timestamp in the first 10 characters', () {
    final generator = UlidGenerator(
      random: Random(1),
      clock: () =>
          DateTime.fromMillisecondsSinceEpoch(1469918176385, isUtc: true),
    );
    expect(generator.next().substring(0, 10), '01ARYZ6S41');
  });

  test('ids are unique', () {
    final ids = {for (var i = 0; i < 1000; i++) newUlid()};
    expect(ids, hasLength(1000));
  });
}
