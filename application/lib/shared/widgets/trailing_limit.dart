import 'package:flutter/widgets.dart';

/// Caps the trailing part of a row (a status pill, an amount, a delivery time) at a share of the
/// screen width. At normal text sizes it is narrower than the cap and nothing changes; with large
/// text (up to 200%, M-A19) it wraps or shrinks inside the cap instead of pushing the row past the
/// screen edge, and the leading part keeps the rest.
class TrailingLimit extends StatelessWidget {
  const TrailingLimit({required this.child, this.widthFactor = 0.4, super.key});

  final Widget child;

  /// Largest share of the screen width the trailing part may take.
  final double widthFactor;

  @override
  Widget build(BuildContext context) => ConstrainedBox(
    constraints: BoxConstraints(
      maxWidth: MediaQuery.sizeOf(context).width * widthFactor,
    ),
    child: child,
  );
}
