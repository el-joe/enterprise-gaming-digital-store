import 'package:application/core/theme/tokens/motion.dart';
import 'package:flutter/material.dart';

/// Wraps a platform page transition so it honours reduced motion (M-A19): when
/// [MotionTokens.reduced] is true the page cross-fades in place instead of sliding or zooming.
///
/// The wrapped builder still builds with constant animations, so its back gestures (the iOS edge
/// swipe, Android predictive back) keep working: the gesture drives the fade instead of a slide.
class ReducedMotionPageTransitionsBuilder extends PageTransitionsBuilder {
  const ReducedMotionPageTransitionsBuilder(this.delegate);

  /// The platform's own transition, used when motion is not reduced.
  final PageTransitionsBuilder delegate;

  /// [PageTransitionsTheme] with every default platform builder wrapped.
  static PageTransitionsTheme theme() => PageTransitionsTheme(
    builders: {
      for (final entry in const PageTransitionsTheme().builders.entries)
        entry.key: ReducedMotionPageTransitionsBuilder(entry.value),
    },
  );

  @override
  DelegatedTransitionBuilder? get delegatedTransition =>
      delegate.delegatedTransition;

  @override
  Duration get transitionDuration => delegate.transitionDuration;

  @override
  Duration get reverseTransitionDuration => delegate.reverseTransitionDuration;

  @override
  Widget buildTransitions<T>(
    PageRoute<T> route,
    BuildContext context,
    Animation<double> animation,
    Animation<double> secondaryAnimation,
    Widget child,
  ) {
    if (!MotionTokens.reduced(context)) {
      return delegate.buildTransitions(
        route,
        context,
        animation,
        secondaryAnimation,
        child,
      );
    }
    return delegate.buildTransitions(
      route,
      context,
      kAlwaysCompleteAnimation,
      kAlwaysDismissedAnimation,
      FadeTransition(opacity: animation, child: child),
    );
  }
}
