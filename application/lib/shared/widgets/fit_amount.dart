import 'package:flutter/widgets.dart';

/// An amount at the end of a row: at most [maxWidth] wide and scaled down (never cut, wrapped or
/// overflowing) when large text would make it wider. At the default text size it keeps its
/// natural size, so the row looks as designed (M-A19 text scaling).
class FitAmount extends StatelessWidget {
  const FitAmount({required this.child, this.maxWidth = 200, super.key});

  final Widget child;
  final double maxWidth;

  @override
  Widget build(BuildContext context) => ConstrainedBox(
    constraints: BoxConstraints(maxWidth: maxWidth),
    child: FittedBox(
      fit: BoxFit.scaleDown,
      alignment: AlignmentDirectional.centerEnd,
      child: child,
    ),
  );
}
