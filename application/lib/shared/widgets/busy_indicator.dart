import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/motion.dart';
import 'package:flutter/material.dart';

/// The app's busy spinner. Under reduced motion ([MotionTokens.reduced]) it is a still hourglass
/// of the same size instead of a spinning arc (M-A19). Screen readers hear [semanticsLabel]
/// ("Loading" by default) unless [excludeSemantics] is set, for example inside a button whose
/// label already says what is happening.
class BusyIndicator extends StatelessWidget {
  const BusyIndicator({
    this.size = 20,
    this.strokeWidth = 2,
    this.color,
    this.semanticsLabel,
    this.excludeSemantics = false,
    super.key,
  });

  final double size;
  final double strokeWidth;
  final Color? color;
  final String? semanticsLabel;
  final bool excludeSemantics;

  @override
  Widget build(BuildContext context) {
    final label = excludeSemantics
        ? null
        : semanticsLabel ?? context.l10n.loadingLabel;
    final Widget indicator = MotionTokens.reduced(context)
        ? Icon(
            Icons.hourglass_empty,
            size: size,
            color:
                color ??
                IconTheme.of(context).color ??
                context.colors.contentSecondary,
          )
        : CircularProgressIndicator(strokeWidth: strokeWidth, color: color);
    final box = SizedBox.square(dimension: size, child: indicator);
    if (label == null) {
      return ExcludeSemantics(child: box);
    }
    return Semantics(
      label: label,
      container: true,
      child: ExcludeSemantics(child: box),
    );
  }
}
