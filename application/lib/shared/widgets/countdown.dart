import 'dart:async';

import 'package:application/core/utils/clock.dart';
import 'package:flutter/widgets.dart';

/// `m:ss` with Western digits (owner decision 2), for example `0:58` or `4:12`.
String formatCountdown(Duration remaining) {
  final seconds = remaining.isNegative ? 0 : remaining.inSeconds;
  final minutes = seconds ~/ 60;
  return '$minutes:${(seconds % 60).toString().padLeft(2, '0')}';
}

/// Rebuilds every second with the time left until [until] (never negative) and calls [onDone]
/// once when it reaches zero. Display only: the deadline itself comes from a controller.
class CountdownBuilder extends StatefulWidget {
  const CountdownBuilder({
    required this.until,
    required this.clock,
    required this.builder,
    this.onDone,
    super.key,
  });

  final DateTime until;
  final Clock clock;
  final Widget Function(BuildContext context, Duration remaining) builder;
  final VoidCallback? onDone;

  @override
  State<CountdownBuilder> createState() => _CountdownBuilderState();
}

class _CountdownBuilderState extends State<CountdownBuilder> {
  Timer? _timer;
  bool _done = false;

  Duration get _remaining {
    final left = widget.until.difference(widget.clock());
    // Round up so 58.4 s shows as 0:59 and reaches 0:00 only at the deadline.
    final seconds = (left.inMilliseconds / 1000).ceil();
    return Duration(seconds: seconds < 0 ? 0 : seconds);
  }

  @override
  void initState() {
    super.initState();
    _start();
  }

  @override
  void didUpdateWidget(CountdownBuilder oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.until != widget.until) {
      _done = false;
      _start();
    }
  }

  void _start() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
    WidgetsBinding.instance.addPostFrameCallback((_) => _checkDone());
  }

  void _tick() {
    if (!mounted) {
      return;
    }
    setState(() {});
    _checkDone();
  }

  void _checkDone() {
    if (!mounted || _done || _remaining > Duration.zero) {
      return;
    }
    _done = true;
    _timer?.cancel();
    widget.onDone?.call();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => widget.builder(context, _remaining);
}
