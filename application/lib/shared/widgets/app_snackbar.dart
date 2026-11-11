import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// Tones of the snackbar (Figma `Mobile / Snackbar` `75:881`).
enum SnackbarTone { success, danger, info }

/// Shows the token snackbar: tone icon, message and an optional accent action.
void showAppSnackbar(
  BuildContext context, {
  required String message,
  SnackbarTone tone = SnackbarTone.success,
  String? actionLabel,
  VoidCallback? onAction,
}) {
  final colors = context.colors;
  final (IconData icon, Color color) = switch (tone) {
    SnackbarTone.success => (Icons.check, colors.statusSuccess),
    SnackbarTone.danger => (Icons.warning_amber_rounded, colors.statusDanger),
    SnackbarTone.info => (Icons.info_outline, colors.statusInfo),
  };
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(
      SnackBar(
        backgroundColor: colors.surfaceOverlay,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Radii.md),
        ),
        margin: const EdgeInsetsDirectional.all(Spacing.s4),
        content: Row(
          children: [
            Icon(icon, size: 20, color: color),
            const SizedBox(width: Spacing.s3),
            Expanded(child: Text(message, style: context.type.bodyMd)),
          ],
        ),
        action: actionLabel == null
            ? null
            : SnackBarAction(
                label: actionLabel,
                textColor: colors.accentText,
                onPressed: onAction ?? () {},
              ),
      ),
    );
}
