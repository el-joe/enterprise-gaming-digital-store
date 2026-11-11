import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/core/security/clipboard_guard.dart';
import 'package:application/core/security/secure_screen.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/code_reveal/domain/repositories/code_reveal_repository.dart';
import 'package:flutter/foundation.dart';

/// Dummy canary values only (never a real code).
const canaryCode = 'FIXTURE-CODE-0000';
const canaryPin = 'FIXTURE-PIN-0000';

/// The unit of `orders/detail_ok`.
const fixtureUnitId = '01JF0000000000000000000002';

RevealedCode canaryReveal({Uri? redemptionUrl}) => RevealedCode(
  code: canaryCode,
  pin: canaryPin,
  redemptionUrl:
      redemptionUrl ?? Uri.parse('https://redeem.example.test/orbit'),
  instructions: '1. Open Orbit.\n2. Enter the code.',
);

/// [CodeRevealRepository] with scripted answers; records every call and the integrity header.
class FakeCodeRevealRepository implements CodeRevealRepository {
  List<Result<RevealedCode>> reveals = [Ok(canaryReveal())];
  List<Result<void>> confirmations = [const Ok(null)];
  final calls = <String>[];
  final integrity = <String?>[];
  int _reveal = 0;
  int _confirm = 0;

  /// When set, reveal waits for it.
  Completer<void>? revealGate;

  static T _take<T>(List<T> results, int index) =>
      results[index < results.length ? index : results.length - 1];

  @override
  Future<Result<void>> confirmPassword(String password) async {
    calls.add('confirm:$password');
    return _take(confirmations, _confirm++);
  }

  @override
  Future<Result<RevealedCode>> reveal(
    RevealTarget target, {
    String? deviceIntegrity,
  }) async {
    calls.add(target.guest ? 'guest-reveal' : 'reveal');
    integrity.add(deviceIntegrity);
    await revealGate?.future;
    return _take(reveals, _reveal++);
  }

  int count(String prefix) => calls.where((c) => c.startsWith(prefix)).length;
}

/// [ScreenSecurity] recording `setSecure` calls, with a capture stream the test drives.
class FakeScreenSecurity extends ScreenSecurity {
  FakeScreenSecurity() : super(platform: () => TargetPlatform.iOS);

  final secureCalls = <bool>[];
  // Closed by `TestHarness.dispose`.
  // ignore: close_sinks
  final capture = StreamController<bool>.broadcast();
  bool capturedNow = false;
  int _holders = 0;

  @override
  bool get isOn => _holders > 0;

  @override
  Future<void> enable() async {
    _holders++;
    if (_holders == 1) {
      secureCalls.add(true);
    }
  }

  @override
  Future<void> disable() async {
    if (_holders == 0) {
      return;
    }
    _holders--;
    if (_holders == 0) {
      secureCalls.add(false);
    }
  }

  @override
  Future<bool> isCaptured() async => capturedNow;

  @override
  Stream<bool> get captureChanges => capture.stream;
}

/// [ClipboardGuard] that records copies and clears instead of touching the platform.
class FakeClipboardGuard extends ClipboardGuard {
  FakeClipboardGuard() : super(platform: () => TargetPlatform.android);

  int copies = 0;
  int clears = 0;

  /// A real guard to pass calls to (platform channel tests).
  ClipboardGuard? delegate;

  @override
  Future<void> copy(String text) async {
    copies++;
    await delegate?.copy(text);
  }

  @override
  Future<void> clearNow() async {
    clears++;
    await delegate?.clearNow();
  }

  @override
  Future<void> resumed() async {}
}
