import 'package:application/core/config/app_env.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/elevation.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/brand_logo.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Shell around the in-app routes: header with the logo and, outside prod, the environment badge.
/// The bottom navigation is added when the first feature tabs exist (M-A11).
class AppShell extends ConsumerWidget {
  const AppShell({required this.child, super.key});

  final Widget child;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final flavor = ref.watch(appEnvProvider).flavor;
    final colors = context.colors;
    return Scaffold(
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Container(
              padding: const EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s4,
                vertical: Spacing.s3,
              ),
              decoration: BoxDecoration(
                border: Border(bottom: BorderSide(color: colors.lineSubtle)),
              ),
              child: Row(
                children: [
                  const BrandLogo.full(),
                  const Spacer(),
                  if (flavor != Flavor.prod) _EnvironmentBadge(flavor: flavor),
                ],
              ),
            ),
            Expanded(child: child),
          ],
        ),
      ),
    );
  }
}

class _EnvironmentBadge extends StatelessWidget {
  const _EnvironmentBadge({required this.flavor});

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
        ),
      ),
    );
  }
}

/// Foundation home: token-styled placeholder until the home feature ships (M-A11).
class FoundationHomePage extends ConsumerWidget {
  const FoundationHomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final width = MediaQuery.sizeOf(context).width;
    return SingleChildScrollView(
      padding: EdgeInsetsDirectional.all(LayoutTokens.marginFor(width)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          DecoratedBox(
            decoration: ShapeDecoration(
              color: colors.surfaceRaised,
              shape: KeyCut.cardShape(
                side: BorderSide(color: colors.lineSubtle),
              ),
              shadows: ElevationTokens.card,
            ),
            child: Padding(
              padding: const EdgeInsetsDirectional.all(Spacing.s6),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    type.overlineText(l10n.homeOverline),
                    style: type.overline.copyWith(color: colors.accentText),
                  ),
                  const SizedBox(height: Spacing.s2),
                  Text(l10n.homeTitle, style: type.displaySm),
                  const SizedBox(height: Spacing.s3),
                  Text(
                    l10n.homeBody,
                    style: type.bodyLg.copyWith(color: colors.contentSecondary),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: Spacing.s6),
          Align(
            alignment: AlignmentDirectional.centerStart,
            child: AppButton(
              label: l10n.switchLanguage,
              semanticsHint: l10n.switchLanguageHint,
              variant: AppButtonVariant.secondary,
              onPressed: () => ref
                  .read(appLocaleProvider.notifier)
                  .select(
                    AppLocales.isRtl(Localizations.localeOf(context))
                        ? AppLocales.english
                        : AppLocales.arabic,
                  ),
            ),
          ),
        ],
      ),
    );
  }
}

enum NoticeKind { maintenance, updateRequired, notFound }

/// Full-screen notice for maintenance, unsupported version and unknown routes.
class NoticePage extends StatelessWidget {
  const NoticePage({required this.kind, super.key});

  final NoticeKind kind;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (title, body) = switch (kind) {
      NoticeKind.maintenance => (l10n.maintenanceTitle, l10n.maintenanceBody),
      NoticeKind.updateRequired => (
        l10n.updateRequiredTitle,
        l10n.updateRequiredBody,
      ),
      NoticeKind.notFound => (l10n.notFoundTitle, l10n.notFoundBody),
    };
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s6),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const BrandLogo.mark(),
              const SizedBox(height: Spacing.s6),
              Text(title, style: type.headingLg),
              const SizedBox(height: Spacing.s2),
              Text(
                body,
                style: type.bodyMd.copyWith(color: colors.contentSecondary),
              ),
              if (kind == NoticeKind.notFound) ...[
                const SizedBox(height: Spacing.s6),
                AppButton(
                  label: l10n.backToHome,
                  onPressed: () => context.go(Routes.home.path),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
