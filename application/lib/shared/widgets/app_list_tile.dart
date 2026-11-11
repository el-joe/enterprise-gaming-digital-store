import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// List row (Figma `Mobile / List Tile` `75:674`): leading icon, title, supporting text and a
/// trailing chevron, value or switch. At least 56dp tall; the whole row is the touch target.
class AppListTile extends StatelessWidget {
  const AppListTile({
    required this.title,
    this.subtitle,
    this.icon,
    this.value,
    this.switchValue,
    this.onSwitchChanged,
    this.onTap,
    this.showChevron = false,
    super.key,
  });

  final String title;
  final String? subtitle;
  final IconData? icon;
  final String? value;

  /// When set, the row is a switch.
  final bool? switchValue;
  final ValueChanged<bool>? onSwitchChanged;
  final VoidCallback? onTap;
  final bool showChevron;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    final isSwitch = switchValue != null;
    final content = Padding(
      padding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s3,
        vertical: Spacing.s3,
      ),
      child: Row(
        children: [
          if (icon != null) ...[
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: colors.surfaceOverlay,
                borderRadius: BorderRadius.circular(Radii.sm),
              ),
              child: Icon(icon, size: 20, color: colors.contentPrimary),
            ),
            const SizedBox(width: Spacing.s3),
          ],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: type.labelMd),
                if (subtitle != null)
                  Text(
                    subtitle!,
                    style: type.bodyCaption.copyWith(
                      color: colors.contentSecondary,
                    ),
                  ),
              ],
            ),
          ),
          if (value != null)
            Text(
              value!,
              style: type.bodySm.copyWith(color: colors.contentSecondary),
            ),
          if (isSwitch)
            ExcludeSemantics(
              child: Switch(
                value: switchValue!,
                onChanged: onSwitchChanged,
                activeTrackColor: colors.accentPrimary,
                activeThumbColor: colors.contentOnAccent,
              ),
            ),
          if (showChevron)
            Icon(Icons.chevron_right, color: colors.contentSecondary),
        ],
      ),
    );
    final tap = isSwitch
        ? (onSwitchChanged == null
              ? null
              : () => onSwitchChanged!(!switchValue!))
        : onTap;
    return Semantics(
      button: !isSwitch && tap != null,
      toggled: isSwitch ? switchValue : null,
      label: subtitle == null ? title : '$title, $subtitle',
      excludeSemantics: true,
      onTap: tap,
      child: Material(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: tap,
          child: ConstrainedBox(
            constraints: const BoxConstraints(minHeight: 56),
            child: content,
          ),
        ),
      ),
    );
  }
}
