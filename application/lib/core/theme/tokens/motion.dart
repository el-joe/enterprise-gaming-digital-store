import 'package:flutter/widgets.dart';

/// Motion tokens: fast 120 ms, base 200 ms, slow 360 ms, `cubic-bezier(.2, .8, .2, 1)`.
abstract final class MotionTokens {
  static const fast = Duration(milliseconds: 120);
  static const base = Duration(milliseconds: 200);
  static const slow = Duration(milliseconds: 360);

  static const Curve easing = Cubic(0.2, 0.8, 0.2, 1);

  /// [duration], or zero when the platform asks for reduced motion.
  static Duration resolve(BuildContext context, Duration duration) =>
      MediaQuery.maybeDisableAnimationsOf(context) ?? false
      ? Duration.zero
      : duration;
}
