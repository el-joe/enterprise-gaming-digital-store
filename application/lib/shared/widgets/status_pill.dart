import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

enum StatusPillTone { success, warning, neutral }

/// Library `Badge` (`6:161`): a short label on the tone's tint with the small key-cut (the large
/// corner on the top end, so it mirrors in RTL).
class StatusPill extends StatelessWidget {
  const StatusPill({required this.label, required this.tone, super.key});

  final String label;
  final StatusPillTone tone;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final (tint, ink) = switch (tone) {
      StatusPillTone.success => (
        colors.statusSuccessTint,
        colors.statusSuccess,
      ),
      StatusPillTone.warning => (
        colors.statusWarningTint,
        colors.statusWarning,
      ),
      StatusPillTone.neutral => (
        colors.surfaceOverlay,
        colors.contentSecondary,
      ),
    };
    return Container(
      padding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s2,
        vertical: 3,
      ),
      decoration: BoxDecoration(
        color: tint,
        borderRadius: const BorderRadiusDirectional.only(
          topStart: Radius.circular(2),
          topEnd: Radius.circular(8),
          bottomStart: Radius.circular(2),
          bottomEnd: Radius.circular(2),
        ),
      ),
      child: Text(label, style: context.type.labelSm.copyWith(color: ink)),
    );
  }
}
