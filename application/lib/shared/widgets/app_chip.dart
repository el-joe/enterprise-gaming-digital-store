import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// Selectable chip (Figma `Mobile / Chip` `75:694`): selected chips are volt with a check mark.
/// The touch target is 48dp tall around the 36dp chip.
class AppChip extends StatelessWidget {
  const AppChip({
    required this.label,
    required this.selected,
    required this.onTap,
    this.semanticsLabel,
    super.key,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;
  final String? semanticsLabel;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    return Semantics(
      inMutuallyExclusiveGroup: true,
      checked: selected,
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
              // 36dp at default text size; grows with large text instead of clipping (M-A19).
              constraints: const BoxConstraints(minHeight: 36),
              padding: const EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s3,
                vertical: Spacing.s1,
              ),
              decoration: BoxDecoration(
                color: selected ? colors.accentPrimary : colors.surfaceRaised,
                border: Border.all(
                  color: selected ? colors.accentPrimary : colors.lineDefault,
                ),
                borderRadius: BorderRadius.circular(Radii.full),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (selected) ...[
                    Icon(Icons.check, size: 16, color: colors.contentOnAccent),
                    const SizedBox(width: Spacing.s1_5),
                  ],
                  Flexible(
                    child: Text(
                      label,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: type.labelMd.copyWith(
                        color: selected
                            ? colors.contentOnAccent
                            : colors.contentPrimary,
                      ),
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
