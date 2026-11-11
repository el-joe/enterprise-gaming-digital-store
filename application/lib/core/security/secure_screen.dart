import 'dart:async';

import 'package:application/core/security/security_channel.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'secure_screen.g.dart';

/// Screen capture protection for code screens (MOBILE_APP_ROADMAP.md 6.3, owner decision 7),
/// through the app's own channel `shop.boxcards.app/security`.
///
/// - **Android**: `setSecure(true)` sets `FLAG_SECURE` on the activity window (screenshots,
///   recording and the recents thumbnail show nothing). It is on only while a code screen holds
///   it ([enable] / [disable], reference counted).
/// - **iOS** cannot block screenshots. `setSecure(true)` turns on the native privacy overlay that
///   covers the window when the app resigns active (the app-switcher snapshot), and
///   [captureChanges] reports `UIScreen.capturedDidChangeNotification` (recording, AirPlay
///   mirroring, QuickTime) so the screen can hide the code while capture is on. A screenshot
///   itself cannot be prevented or hidden on iOS; this limitation is documented.
/// - Other platforms: nothing (no native side).
class ScreenSecurity {
  ScreenSecurity({
    this._channel = securityChannel,
    this._captureChannel = securityCaptureChannel,
    TargetPlatform Function()? platform,
  }) : _platform = platform ?? (() => defaultTargetPlatform);

  final MethodChannel _channel;
  final EventChannel _captureChannel;
  final TargetPlatform Function() _platform;
  int _holders = 0;

  bool get _android => _platform() == TargetPlatform.android;
  bool get _ios => _platform() == TargetPlatform.iOS;

  /// Whether this platform has a native side for [enable].
  bool get isSupported => _android || _ios;

  /// Whether a screen currently holds protection.
  bool get isOn => _holders > 0;

  /// Turns protection on. Nested holders are reference counted; only the first sends
  /// `setSecure(true)`.
  Future<void> enable() async {
    if (!isSupported) {
      return;
    }
    _holders++;
    if (_holders == 1) {
      await _send(enabled: true);
    }
  }

  /// Releases one [enable]; protection turns off when the last holder leaves.
  Future<void> disable() async {
    if (!isSupported || _holders == 0) {
      return;
    }
    _holders--;
    if (_holders == 0) {
      await _send(enabled: false);
    }
  }

  Future<void> _send({required bool enabled}) async {
    try {
      await _channel.invokeMethod<bool>('setSecure', {'enabled': enabled});
    } on Object {
      // A missing native side must never break the screen; the code stays masked by default.
    }
  }

  /// iOS only: whether the screen is being recorded or mirrored right now.
  Future<bool> isCaptured() async {
    if (!_ios) {
      return false;
    }
    try {
      return await _channel.invokeMethod<bool>('isCaptured') ?? false;
    } on Object {
      return false;
    }
  }

  /// iOS only: `true` when recording or mirroring starts, `false` when it stops. Empty elsewhere.
  Stream<bool> get captureChanges {
    if (!_ios) {
      return const Stream<bool>.empty();
    }
    return _captureChannel
        .receiveBroadcastStream()
        .map((event) => event == true)
        .handleError((Object _) {});
  }
}

/// One instance for the app, so nested code screens count correctly.
@Riverpod(keepAlive: true)
ScreenSecurity screenSecurity(Ref ref) => ScreenSecurity();
