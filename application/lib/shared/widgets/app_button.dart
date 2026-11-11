import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/shared/widgets/busy_indicator.dart';
import 'package:flutter/material.dart';

/// [danger] is the destructive filled button of the M-A8 frames (sign out, turn off two-step
/// verification, sign out a device): `status/danger-solid` with ink text.
enum AppButtonVariant { primary, secondary, ghost, danger }

/// Token-styled button with the key-cut button shape (styles come from `AppTheme`). [expand]
/// fills the width (sticky footers); [loading] keeps the label for screen readers and shows a
/// progress indicator.
class AppButton extends StatelessWidget {
  const AppButton({
    required this.label,
    required this.onPressed,
    this.variant = AppButtonVariant.primary,
    this.semanticsHint,
    this.icon,
    this.expand = false,
    this.loading = false,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final AppButtonVariant variant;
  final String? semanticsHint;
  final IconData? icon;
  final bool expand;
  final bool loading;

  @override
  Widget build(BuildContext context) {
    final pressed = loading ? null : onPressed;
    final Widget child = loading
        ? Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              // The label already says what is busy; the spinner adds nothing for screen readers.
              const BusyIndicator(size: 18, excludeSemantics: true),
              const SizedBox(width: 10),
              Flexible(child: Text(label, overflow: TextOverflow.ellipsis)),
            ],
          )
        : icon == null
        ? Text(label)
        : Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 20),
              const SizedBox(width: 8),
              Flexible(child: Text(label, overflow: TextOverflow.ellipsis)),
            ],
          );
    final style = expand
        ? const ButtonStyle(
            minimumSize: WidgetStatePropertyAll(Size(double.infinity, 52)),
          )
        : null;
    final button = switch (variant) {
      AppButtonVariant.primary => FilledButton(
        onPressed: pressed,
        style: style,
        child: child,
      ),
      AppButtonVariant.secondary => OutlinedButton(
        onPressed: pressed,
        style: style,
        child: child,
      ),
      AppButtonVariant.ghost => TextButton(
        onPressed: pressed,
        style: style,
        child: child,
      ),
      AppButtonVariant.danger => FilledButton(
        onPressed: pressed,
        style: FilledButton.styleFrom(
          backgroundColor: context.colors.statusDangerSolid,
          foregroundColor: context.colors.contentInverse,
        ).merge(style),
        child: child,
      ),
    };
    if (semanticsHint == null) {
      return button;
    }
    return Semantics(hint: semanticsHint, child: button);
  }
}
