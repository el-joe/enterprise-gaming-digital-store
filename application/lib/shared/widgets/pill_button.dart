import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// A chip-shaped button (same look as `Mobile / Chip` `75:694`) for actions such as "Filters · 2",
/// "Sort: …", a category link or a popular term. [highlighted] uses the selected (volt) look. A
/// plain button for screen readers, 48dp tall.
class PillButton extends StatelessWidget {
  const PillButton({
    required this.label,
    required this.onTap,
    this.highlighted = false,
    this.icon,
    this.semanticsLabel,
    super.key,
  });

  final String label;
  final VoidCallback onTap;
  final bool highlighted;
  final IconData? icon;
  final String? semanticsLabel;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final foreground = highlighted
        ? colors.contentOnAccent
        : colors.contentPrimary;
    return Semantics(
      button: true,
      label: semanticsLabel ?? label,
      excludeSemantics: true,
      onTap: onTap,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(Radii.full),
        child: ConstrainedBox(
          constraints: const BoxConstraints(minHeight: 48, minWidth: 48),
          child: Center(
            widthFactor: 1,
            child: Container(
              height: 36,
              padding: const EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s3,
              ),
              decoration: BoxDecoration(
                color: highlighted
                    ? colors.accentPrimary
                    : colors.surfaceRaised,
                border: Border.all(
                  color: highlighted
                      ? colors.accentPrimary
                      : colors.lineDefault,
                ),
                borderRadius: BorderRadius.circular(Radii.full),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (icon != null) ...[
                    Icon(icon, size: 16, color: foreground),
                    const SizedBox(width: Spacing.s1_5),
                  ],
                  Flexible(
                    child: Text(
                      label,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: context.type.labelMd.copyWith(color: foreground),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
