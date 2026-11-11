import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// One tab of [AppBottomNav].
@immutable
final class AppNavItem {
  const AppNavItem({
    required this.label,
    required this.icon,
    required this.selectedIcon,
  });

  final String label;
  final IconData icon;
  final IconData selectedIcon;
}

/// Bottom navigation (Figma `Mobile / Bottom Nav` `75:568`): five tabs, the selected one with a
/// volt-tint pill behind its icon and a volt icon. Each tab fills its fifth of the bar and is at
/// least 48dp tall; the order follows the reading direction (mirrors in RTL).
class AppBottomNav extends StatelessWidget {
  const AppBottomNav({
    required this.items,
    required this.currentIndex,
    required this.onSelected,
    super.key,
  });

  final List<AppNavItem> items;
  final int currentIndex;
  final ValueChanged<int> onSelected;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceBase,
        border: Border(top: BorderSide(color: colors.lineSubtle)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(vertical: Spacing.s1),
          child: Row(
            children: [
              for (var index = 0; index < items.length; index++)
                Expanded(
                  child: _NavTab(
                    item: items[index],
                    selected: index == currentIndex,
                    onTap: () => onSelected(index),
                    colors: (
                      active: colors.accentText,
                      tint: colors.accentPrimaryTint,
                      idle: colors.contentMuted,
                      activeLabel: colors.contentPrimary,
                    ),
                    labelStyle: type.labelSm,
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NavTab extends StatelessWidget {
  const _NavTab({
    required this.item,
    required this.selected,
    required this.onTap,
    required this.colors,
    required this.labelStyle,
  });

  final AppNavItem item;
  final bool selected;
  final VoidCallback onTap;
  final ({Color active, Color tint, Color idle, Color activeLabel}) colors;
  final TextStyle labelStyle;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      selected: selected,
      label: item.label,
      excludeSemantics: true,
      onTap: onTap,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(Radii.md),
        child: ConstrainedBox(
          constraints: const BoxConstraints(minHeight: 56),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                width: 56,
                height: 32,
                decoration: BoxDecoration(
                  color: selected ? colors.tint : Colors.transparent,
                  borderRadius: BorderRadius.circular(Radii.full),
                ),
                child: Icon(
                  selected ? item.selectedIcon : item.icon,
                  size: 24,
                  color: selected ? colors.active : colors.idle,
                ),
              ),
              const SizedBox(height: Spacing.s1),
              Text(
                item.label,
                style: labelStyle.copyWith(
                  color: selected ? colors.activeLabel : colors.idle,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
