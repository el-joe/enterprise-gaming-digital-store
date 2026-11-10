import 'package:flutter/material.dart';

enum AppButtonVariant { primary, secondary, ghost }

/// Token-styled button with the key-cut button shape (styles come from `AppTheme`).
class AppButton extends StatelessWidget {
  const AppButton({
    required this.label,
    required this.onPressed,
    this.variant = AppButtonVariant.primary,
    this.semanticsHint,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final AppButtonVariant variant;
  final String? semanticsHint;

  @override
  Widget build(BuildContext context) {
    final text = Text(label);
    final button = switch (variant) {
      AppButtonVariant.primary => FilledButton(
        onPressed: onPressed,
        child: text,
      ),
      AppButtonVariant.secondary => OutlinedButton(
        onPressed: onPressed,
        child: text,
      ),
      AppButtonVariant.ghost => TextButton(onPressed: onPressed, child: text),
    };
    if (semanticsHint == null) {
      return button;
    }
    return Semantics(hint: semanticsHint, child: button);
  }
}
