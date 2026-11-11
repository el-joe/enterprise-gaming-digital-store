import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/security/domain/entities/two_factor.dart';
import 'package:application/features/security/domain/repositories/security_repository.dart';

/// Canary values only (never real secrets).
const canarySecret = 'JBSWY3DPEHPK3PXPCANARY00';
const canaryRecoveryCodes = [
  'CANARY-RC-0001',
  'CANARY-RC-0002',
  'CANARY-RC-0003',
  'CANARY-RC-0004',
  'CANARY-RC-0005',
  'CANARY-RC-0006',
  'CANARY-RC-0007',
  'CANARY-RC-0008',
];

TwoFactorEnrollment canaryEnrollment() => TwoFactorEnrollment(
  secret: canarySecret,
  otpauthUri: Uri.parse(
    'otpauth://totp/boxcards.shop:sara%40example.com?secret=$canarySecret&issuer=boxcards.shop',
  ),
);

/// [SecurityRepository] with scripted answers (the last one repeats); records every call.
class FakeSecurityRepository implements SecurityRepository {
  List<Result<TwoFactorEnrollment>> enables = [Ok(canaryEnrollment())];
  List<Result<RecoveryCodes>> confirms = [
    const Ok(RecoveryCodes(canaryRecoveryCodes)),
  ];
  List<Result<void>> disables = [const Ok(null)];
  final calls = <String>[];
  final passwords = <String>[];
  final codes = <String>[];

  /// When set, enable waits for it.
  Completer<void>? enableGate;

  static T _take<T>(List<T> results, int index) =>
      results[index < results.length ? index : results.length - 1];

  int _count(String call) => calls.where((c) => c == call).length;

  @override
  Future<Result<TwoFactorEnrollment>> enableTwoFactor({
    required String currentPassword,
  }) async {
    final index = _count('enable');
    calls.add('enable');
    passwords.add(currentPassword);
    await enableGate?.future;
    return _take(enables, index);
  }

  @override
  Future<Result<RecoveryCodes>> confirmTwoFactor({required String code}) async {
    final index = _count('confirm');
    calls.add('confirm');
    codes.add(code);
    return _take(confirms, index);
  }

  @override
  Future<Result<void>> disableTwoFactor({
    required String currentPassword,
  }) async {
    final index = _count('disable');
    calls.add('disable');
    passwords.add(currentPassword);
    return _take(disables, index);
  }
}

/// Two signed-in devices: this phone and a tablet (as drawn in 18.5).
DeviceList fixtureDevices({bool webSessionsListed = false}) => DeviceList(
  webSessionsListed: webSessionsListed,
  devices: [
    Device(
      id: '01JF00000000000000000TABLET',
      type: 'mobile',
      current: false,
      deviceName: 'Tablet',
      ip: '127.0.0.x',
      createdAt: DateTime.utc(2026, 11, 1, 9),
      lastUsedAt: DateTime.utc(2026, 11, 10, 12),
    ),
    Device(
      id: '01JF0000000000000000000PIXEL',
      type: 'mobile',
      current: true,
      deviceName: 'Pixel 9',
      ip: '203.0.113.x',
      createdAt: DateTime.utc(2026, 11, 5, 9),
      lastUsedAt: DateTime.utc(2026, 11, 11, 12),
    ),
  ],
);
