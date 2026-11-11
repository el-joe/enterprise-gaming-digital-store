import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// Single-choice tile (library `Option Tile`): title, detail and a radio mark; the selected tile
/// gets the volt border and tint. Tiles of one group are announced as radio buttons.
class OptionTile extends StatelessWidget {
  const OptionTile({
    required this.title,
    required this.selected,
    required this.onTap,
    this.detail,
    this.titleDirection,
    super.key,
  });

  final String title;
  final String? detail;
  final bool selected;
  final VoidCallback onTap;

  /// The title's own direction (a language name is written in its own script).
  final TextDirection? titleDirection;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    return Semantics(
      inMutuallyExclusiveGroup: true,
      checked: selected,
      button: true,
      label: detail == null ? title : '$title, $detail',
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: selected ? colors.accentPrimaryTint : colors.surfaceRaised,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          side: BorderSide(
            color: selected ? colors.accentPrimary : colors.lineSubtle,
            width: selected ? 1.5 : 1,
          ),
        ),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsetsDirectional.symmetric(
              horizontal: Spacing.s4,
              vertical: Spacing.s3,
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: type.labelLg,
                        textDirection: titleDirection,
                      ),
                      if (detail != null)
                        Text(
                          detail!,
                          style: type.bodySm.copyWith(
                            color: colors.contentSecondary,
                          ),
                        ),
                    ],
                  ),
                ),
                Icon(
                  selected
                      ? Icons.radio_button_checked
                      : Icons.radio_button_unchecked,
                  color: selected ? colors.accentPrimary : colors.contentMuted,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
