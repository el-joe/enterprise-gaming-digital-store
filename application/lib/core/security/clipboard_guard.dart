import 'dart:async';

import 'package:application/core/security/security_channel.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

/// Copies sensitive text (codes, PINs) and clears it after [clearAfter] (MOBILE_APP_ROADMAP.md 6.3).
///
/// Android: the clip is marked sensitive (`ClipDescription.EXTRA_IS_SENSITIVE`, Android 13+ hides
/// it from the clipboard preview) and is cleared only while the clipboard still holds this app's
/// clip. Other platforms: plain copy, cleared only while the clipboard still holds the same text.
class ClipboardGuard {
  ClipboardGuard({
    this.clearAfter = const Duration(seconds: 60),
    this._channel = securityChannel,
    TargetPlatform Function()? platform,
  }) : _platform = platform ?? (() => defaultTargetPlatform);

  final Duration clearAfter;
  final MethodChannel _channel;
  final TargetPlatform Function() _platform;

  Timer? _timer;
  String? _copied;

  bool get _android => _platform() == TargetPlatform.android;

  /// Whether a clear is scheduled.
  bool get hasPendingClear => _timer?.isActive ?? false;

  Future<void> copy(String text) async {
    if (_android) {
      await _channel.invokeMethod<bool>('copySensitive', {'text': text});
    } else {
      await Clipboard.setData(ClipboardData(text: text));
    }
    _copied = text;
    _timer?.cancel();
    _timer = Timer(clearAfter, () => unawaited(clearNow()));
  }

  /// Clears the clipboard now if it still holds what [copy] wrote.
  Future<void> clearNow() async {
    _timer?.cancel();
    _timer = null;
    final copied = _copied;
    _copied = null;
    if (copied == null) {
      return;
    }
    if (_android) {
      await _channel.invokeMethod<bool>('clearSensitive');
      return;
    }
    final current = await Clipboard.getData(Clipboard.kTextPlain);
    if (current?.text == copied) {
      await Clipboard.setData(const ClipboardData(text: ''));
    }
  }

  /// Clears right away instead of waiting for the timer (for example when the app is closing).
  Future<void> dispose() => clearNow();
}
