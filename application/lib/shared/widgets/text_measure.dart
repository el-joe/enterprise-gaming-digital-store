import 'package:flutter/widgets.dart';

/// The height [text] takes in [style] at [maxWidth], laid out as a `Text` widget in [context]
/// would: the inherited default style, the customer's text scale and the text direction. For
/// boxes that must grow with large text (M-A19) but need their height up front (a page view).
double measureTextHeight(
  BuildContext context,
  String text,
  TextStyle style, {
  required double maxWidth,
  int? maxLines,
}) {
  final painter = TextPainter(
    text: TextSpan(
      text: text,
      style: DefaultTextStyle.of(context).style.merge(style),
    ),
    textDirection: Directionality.of(context),
    textScaler: MediaQuery.textScalerOf(context),
    maxLines: maxLines,
    ellipsis: maxLines == null ? null : '…',
  )..layout(maxWidth: maxWidth);
  final height = painter.height;
  painter.dispose();
  return height;
}
