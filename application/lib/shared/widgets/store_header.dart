import 'package:application/core/config/app_env.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/brand_logo.dart';
import 'package:application/shared/widgets/notification_bell.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Root header of the Home and Browse tabs (`App Bar` Root): the logo, outside prod the
/// environment badge, the display currency button (03.5), search and, on Home, the
/// notifications bell with the unread count (M-A17). 56dp, 48dp actions.
class StoreHeader extends ConsumerWidget {
  const StoreHeader({
    required this.onSearch,
    this.currencyCode,
    this.onCurrency,
    this.onNotifications,
    this.unreadNotifications = 0,
    super.key,
  });

  final VoidCallback onSearch;

  /// The display currency shown on the currency button; hidden when null.
  final String? currencyCode;
  final VoidCallback? onCurrency;

  /// Shows the bell when set.
  final VoidCallback? onNotifications;
  final int unreadNotifications;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final flavor = ref.watch(appEnvProvider).flavor;
    final code = currencyCode;
    return SizedBox(
      height: 56,
      child: Padding(
        padding: const EdgeInsetsDirectional.only(
          start: Spacing.s4,
          end: Spacing.s1,
        ),
        child: Row(
          children: [
            Expanded(
              child: flavor == Flavor.prod
                  ? const Align(
                      alignment: AlignmentDirectional.centerStart,
                      child: FittedBox(
                        fit: BoxFit.scaleDown,
                        alignment: AlignmentDirectional.centerStart,
                        child: BrandLogo.full(),
                      ),
                    )
                  : CustomMultiChildLayout(
                      delegate: _LogoAndBadgeLayout(
                        textDirection: Directionality.of(context),
                      ),
                      children: [
                        LayoutId(
                          id: _LogoAndBadgeLayout.logo,
                          child: const FittedBox(
                            fit: BoxFit.scaleDown,
                            alignment: AlignmentDirectional.centerStart,
                            child: BrandLogo.full(),
                          ),
                        ),
                        LayoutId(
                          id: _LogoAndBadgeLayout.badge,
                          child: EnvironmentBadge(flavor: flavor),
                        ),
                      ],
                    ),
            ),
            if (code != null && onCurrency != null)
              Semantics(
                button: true,
                label: l10n.currencyButtonLabel(code),
                excludeSemantics: true,
                onTap: onCurrency,
                child: TextButton(
                  onPressed: onCurrency,
                  style: TextButton.styleFrom(
                    minimumSize: const Size(48, 48),
                    foregroundColor: colors.contentPrimary,
                    // No vertical padding: the 48dp minimum centres the code, and at 200% text
                    // it still fits the 56dp header instead of being cut (M-A19).
                    padding: const EdgeInsetsDirectional.symmetric(
                      horizontal: Spacing.s5,
                    ),
                  ),
                  child: Text(code, style: context.type.labelMd),
                ),
              ),
            SizedBox.square(
              dimension: 48,
              child: IconButton(
                icon: const Icon(Icons.search),
                tooltip: l10n.searchAction,
                onPressed: onSearch,
              ),
            ),
            if (onNotifications != null)
              NotificationBell(
                unread: unreadNotifications,
                onPressed: onNotifications!,
              ),
          ],
        ),
      ),
    );
  }
}

/// The logo and, after it, the environment badge. The badge keeps its full label while the logo
/// still has [minLogoWidth] (the logo scales down first); only when even that is not left (very
/// large text) does the badge shorten its label.
class _LogoAndBadgeLayout extends MultiChildLayoutDelegate {
  _LogoAndBadgeLayout({required this.textDirection});

  static const logo = 'logo';
  static const badge = 'badge';
  static const minLogoWidth = 72.0;

  final TextDirection textDirection;

  @override
  void performLayout(Size size) {
    final badgeMax = (size.width - Spacing.s2 - minLogoWidth).clamp(
      0.0,
      size.width,
    );
    final badgeSize = layoutChild(
      badge,
      BoxConstraints(maxWidth: badgeMax, maxHeight: size.height),
    );
    final logoMax = (size.width - Spacing.s2 - badgeSize.width).clamp(
      0.0,
      size.width,
    );
    final logoSize = layoutChild(
      logo,
      BoxConstraints(maxWidth: logoMax, maxHeight: size.height),
    );
    final ltr = textDirection == TextDirection.ltr;
    final logoX = ltr ? 0.0 : size.width - logoSize.width;
    final badgeX = ltr
        ? logoSize.width + Spacing.s2
        : size.width - logoSize.width - Spacing.s2 - badgeSize.width;
    positionChild(logo, Offset(logoX, (size.height - logoSize.height) / 2));
    positionChild(badge, Offset(badgeX, (size.height - badgeSize.height) / 2));
  }

  @override
  bool shouldRelayout(_LogoAndBadgeLayout oldDelegate) =>
      oldDelegate.textDirection != textDirection;
}

/// "Development" / "Staging" badge shown outside production.
class EnvironmentBadge extends StatelessWidget {
  const EnvironmentBadge({required this.flavor, super.key});

  final Flavor flavor;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.accentEmberTint,
        borderRadius: BorderRadius.circular(Radii.full),
      ),
      child: Padding(
        padding: const EdgeInsetsDirectional.symmetric(
          horizontal: Spacing.s2_5,
          vertical: Spacing.s0_5,
        ),
        child: Text(
          context.l10n.environmentBadge(flavor.name),
          style: context.type.labelSm.copyWith(color: colors.accentEmberText),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
      ),
    );
  }
}

/// The search field look on Home (tapping opens Search, 04.6).
class SearchFieldButton extends StatelessWidget {
  const SearchFieldButton({required this.onTap, super.key});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final hint = context.l10n.searchFieldHint;
    return Semantics(
      button: true,
      label: hint,
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceBase,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          side: BorderSide(color: colors.lineDefault),
        ),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(Radii.md),
          // 48dp at default text size; grows with large text instead of cutting the hint.
          child: ConstrainedBox(
            constraints: const BoxConstraints(minHeight: 48),
            child: Padding(
              padding: const EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s3,
              ),
              child: Row(
                children: [
                  Icon(Icons.search, size: 20, color: colors.contentMuted),
                  const SizedBox(width: Spacing.s2),
                  Expanded(
                    child: Text(
                      hint,
                      style: context.type.bodyMd.copyWith(
                        color: colors.contentMuted,
                      ),
                      overflow: TextOverflow.ellipsis,
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
