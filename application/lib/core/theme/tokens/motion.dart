import 'package:flutter/widgets.dart';

/// Motion tokens: fast 120 ms, base 200 ms, slow 360 ms, `cubic-bezier(.2, .8, .2, 1)`.
abstract final class MotionTokens {
  static const fast = Duration(milliseconds: 120);
  static const base = Duration(milliseconds: 200);
  static const slow = Duration(milliseconds: 360);

  static const Curve easing = Cubic(0.2, 0.8, 0.2, 1);

  /// Whether the platform asks for reduced motion: Android "Remove animations"
  /// (`MediaQuery.disableAnimations`) or iOS "Reduce Motion", which Flutter reports separately
  /// (`AccessibilityFeatures.reduceMotion`) and does not fold into `disableAnimations` (M-A19).
  static bool reduced(BuildContext context) {
    if (MediaQuery.maybeDisableAnimationsOf(context) ?? false) {
      return true;
    }
    final view = View.maybeOf(context);
    return view?.platformDispatcher.accessibilityFeatures.reduceMotion ?? false;
  }

  /// [duration], or zero when the platform asks for reduced motion.
  static Duration resolve(BuildContext context, Duration duration) =>
      reduced(context) ? Duration.zero : duration;
}
