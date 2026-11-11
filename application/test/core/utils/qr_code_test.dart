import 'dart:convert';
import 'dart:io';

import 'package:application/core/utils/qr_code.dart';
import 'package:flutter_test/flutter_test.dart';

/// The app's QR encoder is a port of the backend's `QrCodeSvg` (M13), whose matrices were
/// checked against bacon/bacon-qr-code. `test/fixtures/qr/reference.json` holds the backend's
/// output (forced masks and automatic mask selection); the port must match it module for module.
void main() {
  final reference = jsonDecode(
    File('test/fixtures/qr/reference.json').readAsStringSync(),
  ) as Map<String, dynamic>;
  final cases = (reference['cases'] as List).cast<Map<String, dynamic>>();

  for (final c in cases) {
    final data = c['data'] as String;
    final mask = c['mask'] as int?;
    test(
      'matches the backend encoder: ${data.length} bytes, mask ${mask ?? 'auto'}',
      () {
        final qr = QrCode.encode(data, mask: mask);
        expect(qr.size, c['size']);
        expect(qr.bits, c['bits']);
      },
    );
  }

  test('draws the three finder patterns', () {
    final qr = QrCode.encode('otpauth://totp/x');
    final size = qr.size;
    for (final (x, y) in [(0, 0), (size - 7, 0), (0, size - 7)]) {
      expect(qr.isDark(x, y), isTrue);
      expect(qr.isDark(x + 3, y + 3), isTrue);
      expect(qr.isDark(x + 1, y + 1), isFalse);
    }
  });

  test('refuses data beyond version 20', () {
    expect(() => QrCode.encode('a' * 700), throwsArgumentError);
  });
}
