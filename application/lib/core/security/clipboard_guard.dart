import 'dart:async';

import 'package:application/core/security/security_channel.dart';
import 'package:application/core/utils/clock.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'clipboard_guard.g.dart';

/// Copies a code or PIN and clears the clipboard [clearAfter] later (owner decision 7: 60 s;
/// MOBILE_APP_ROADMAP.md 6.3). The guard never keeps the copied text: it only remembers that a
/// clear is due and when.
///
/// - **Android**: the clip is marked sensitive (`ClipDescription.EXTRA_IS_SENSITIVE`, so Android
///   13+ hides it from the clipboard preview) and carries the app's own label; `clearSensitive`
///   clears it only while the clipboard still holds that clip, so something the customer copied
///   later is left alone. Android 10+ cannot read the clipboard from the background: a clear that
///   falls due there runs when the app returns ([resumed]).
/// - **iOS and others**: Flutter cannot tell whose text is on the clipboard without reading it
///   (and on iOS a read shows the system "pasted from" notice), so after [clearAfter] the guard
///   writes an empty string. Limitation: if the customer copied something else within those 60
///   seconds, that is cleared too. iOS also suspends timers in the background, so an overdue
///   clear runs on [resumed].
class ClipboardGuard {
  ClipboardGuard({
    this.clearAfter = const Duration(seconds: 60),
    this._channel = securityChannel,
    TargetPlatform Function()? platform,
    Clock? clock,
  }) : _platform = platform ?? (() => defaultTargetPlatform),
       _clock = clock ?? DateTime.now;

  final Duration clearAfter;
  final MethodChannel _channel;
  final TargetPlatform Function() _platform;
  final Clock _clock;

  Timer? _timer;
  DateTime? _dueAt;

  bool get _android => _platform() == TargetPlatform.android;

  /// Whether a clear is scheduled.
  bool get hasPendingClear => _dueAt != null;

  /// When the scheduled clear runs, if one is scheduled.
  DateTime? get clearsAt => _dueAt;

  /// Copies [text] and schedules the clear (a new copy restarts the 60 s).
  Future<void> copy(String text) async {
    if (_android) {
      await _channel.invokeMethod<bool>('copySensitive', {'text': text});
    } else {
      await Clipboard.setData(ClipboardData(text: text));
    }
    _timer?.cancel();
    _dueAt = _clock().add(clearAfter);
    _timer = Timer(clearAfter, () => unawaited(clearNow()));
  }

  /// The app is in the foreground again: runs a clear that fell due in the background.
  Future<void> resumed() async {
    final due = _dueAt;
    if (due != null && !_clock().isBefore(due)) {
      await clearNow();
    }
  }

  /// Clears now (if a copy is pending).
  Future<void> clearNow() async {
    _timer?.cancel();
    _timer = null;
    if (_dueAt == null) {
      return;
    }
    _dueAt = null;
    try {
      if (_android) {
        await _channel.invokeMethod<bool>('clearSensitive');
      } else {
        await Clipboard.setData(const ClipboardData(text: ''));
      }
    } on Object {
      // Best effort: nothing else can be done about a clipboard the platform refuses.
    }
  }

  void dispose() {
    _timer?.cancel();
    _timer = null;
  }
}

/// Kept for the app's lifetime so a copy is still cleared after the code screen is left (the
/// customer typically switches to the game app to paste).
@Riverpod(keepAlive: true)
ClipboardGuard clipboardGuard(Ref ref) {
  final guard = ClipboardGuard(clock: ref.watch(clockProvider));
  ref.onDispose(guard.dispose);
  return guard;
}
