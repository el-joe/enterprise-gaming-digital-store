import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'clock.g.dart';

/// Wall clock, overridden in tests (countdowns, the 5-minute app lock).
typedef Clock = DateTime Function();

@Riverpod(keepAlive: true)
Clock clock(Ref ref) => DateTime.now;
