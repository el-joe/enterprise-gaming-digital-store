import 'dart:async';

import 'package:flutter/foundation.dart';

/// Auto-advance policy of the home hero carousel (owner decision 8):
///
/// - advances every [interval] (5 s);
/// - pauses while the customer touches, swipes or focuses it, and does not resume while any of
///   those interactions lasts; a full new interval starts when the last one ends;
/// - stops while the carousel is not visible (scrolled away, another tab, a route on top, the
///   app in the background);
/// - never advances when the OS asks for reduced motion (`MediaQuery.disableAnimations`);
/// - never advances with fewer than two slides.
///
/// It owns only the timer; the widget reports facts and moves the page on [onAdvance].
class CarouselAutoplay {
  CarouselAutoplay({
    required this.onAdvance,
    this.interval = const Duration(seconds: 5),
  });

  static const defaultInterval = Duration(seconds: 5);

  final VoidCallback onAdvance;
  final Duration interval;

  Timer? _timer;
  int _slides = 0;
  bool _visible = false;
  bool _reduceMotion = false;
  bool _touching = false;
  bool _focused = false;
  bool _disposed = false;

  /// Whether the timer is running now.
  bool get isRunning => _timer?.isActive ?? false;

  bool get _shouldRun =>
      !_disposed &&
      _slides > 1 &&
      _visible &&
      !_reduceMotion &&
      !_touching &&
      !_focused;

  void update({
    required int slides,
    required bool visible,
    required bool reduceMotion,
  }) {
    _slides = slides;
    _visible = visible;
    _reduceMotion = reduceMotion;
    _sync(restart: false);
  }

  /// A finger is down on the carousel (touch or swipe in progress).
  void touchStarted() {
    _touching = true;
    _sync(restart: false);
  }

  void touchEnded() {
    _touching = false;
    _sync(restart: true);
  }

  void focusChanged({required bool focused}) {
    _focused = focused;
    _sync(restart: true);
  }

  /// The page changed for any reason: the next advance is a full interval away.
  void pageChanged() => _sync(restart: true);

  void _sync({required bool restart}) {
    if (!_shouldRun) {
      _cancel();
      return;
    }
    if (restart) {
      _cancel();
    }
    _timer ??= Timer.periodic(interval, (_) => onAdvance());
  }

  void _cancel() {
    _timer?.cancel();
    _timer = null;
  }

  void dispose() {
    _disposed = true;
    _cancel();
  }
}
