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
              child: Row(
                children: [
                  const Flexible(
                    child: FittedBox(
                      fit: BoxFit.scaleDown,
                      alignment: AlignmentDirectional.centerStart,
                      child: BrandLogo.full(),
                    ),
                  ),
                  if (flavor != Flavor.prod) ...[
                    const SizedBox(width: Spacing.s2),
                    Flexible(child: EnvironmentBadge(flavor: flavor)),
                  ],
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
          child: SizedBox(
            height: 48,
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
