import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// Leading control of [MobileAppBar] (Figma `Mobile / App Bar` `75:137`).
enum AppBarLeading { none, back, close }

/// 56dp app bar: a 48dp leading button (the back arrow mirrors in RTL), a `heading/md` title and
/// optional 48dp actions.
class MobileAppBar extends StatelessWidget implements PreferredSizeWidget {
  const MobileAppBar({
    this.leading = AppBarLeading.back,
    this.title,
    this.titleWidget,
    this.onLeading,
    this.actions = const [],
    this.trailing,
    super.key,
  });

  final AppBarLeading leading;
  final String? title;

  /// Used instead of [title] (for example the logo on onboarding).
  final Widget? titleWidget;

  /// Defaults to popping the current route.
  final VoidCallback? onLeading;
  final List<Widget> actions;

  /// A non-button trailing widget (for example the onboarding step label).
  final Widget? trailing;

  /// Largest text scale of the title: `heading/md` (26dp line) fits the 48dp bar up to 1.8x.
  static const titleMaxTextScale = 1.8;

  @override
  Size get preferredSize => const Size.fromHeight(56);

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final leadingButton = switch (leading) {
      AppBarLeading.none => null,
      AppBarLeading.back => IconButton(
        icon: const Icon(Icons.arrow_back),
        tooltip: l10n.actionBack,
        onPressed: onLeading ?? () => Navigator.of(context).maybePop(),
      ),
      AppBarLeading.close => IconButton(
        icon: const Icon(Icons.close),
        tooltip: l10n.actionClose,
        onPressed: onLeading ?? () => Navigator.of(context).maybePop(),
      ),
    };
    return Material(
      color: colors.surfaceCanvas,
      child: SizedBox(
        height: preferredSize.height,
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s1),
          child: Row(
            children: [
              if (leadingButton != null)
                SizedBox.square(dimension: 48, child: leadingButton)
              else
                const SizedBox(width: Spacing.s3),
              Expanded(
                child: titleWidget != null
                    ? Align(
                        alignment: AlignmentDirectional.centerStart,
                        child: titleWidget,
                      )
                    : title == null
                    ? const SizedBox.shrink()
                    : Semantics(
                        header: true,
                        // The bar is 56dp (48dp inside): above 180% the one-line title would be
                        // cut vertically, so it stops growing there and ellipsizes (M-A19).
                        child: MediaQuery.withClampedTextScaling(
                          maxScaleFactor: titleMaxTextScale,
                          child: Text(
                            title!,
                            style: context.type.headingMd,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ),
              ),
              ...actions,
              if (trailing != null)
                Padding(
                  padding: const EdgeInsetsDirectional.only(end: Spacing.s3),
                  child: trailing,
                ),
            ],
          ),
        ),
      ),
    );
  }
}
