import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/busy_indicator.dart';
import 'package:application/shared/widgets/option_tile.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:application/shared/widgets/status_pill.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// The hub's profile header (17.1): volt avatar with the initials, name, email and the
/// verified badge. One node for screen readers.
class ProfileHeader extends StatelessWidget {
  const ProfileHeader({required this.header, super.key});

  final AccountHeader header;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final badge = header.emailVerified
        ? l10n.accountVerifiedBadge
        : l10n.accountUnverifiedBadge;
    return Semantics(
      container: true,
      label:
          '${l10n.accountHeaderSemantics(header.name, header.email)}, $badge',
      excludeSemantics: true,
      child: Padding(
        padding: const EdgeInsetsDirectional.only(
          top: Spacing.s1,
          bottom: Spacing.s3,
        ),
        child: Row(
          children: [
            Container(
              width: 56,
              height: 56,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: colors.accentPrimary,
                shape: BoxShape.circle,
              ),
              // The 56dp avatar keeps its size; large text scales the initials down to fit.
              child: Padding(
                padding: const EdgeInsetsDirectional.all(Spacing.s2),
                child: FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text(
                    header.initials,
                    style: type.headingMd.copyWith(
                      color: colors.contentOnAccent,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: Spacing.s3),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    header.name,
                    style: type.headingMd,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: Spacing.s0_5),
                  Text(
                    header.email,
                    style: type.bodySm.copyWith(color: colors.contentMuted),
                    textDirection: TextDirection.ltr,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
            const SizedBox(width: Spacing.s3),
            StatusPill(
              label: badge,
              tone: header.emailVerified
                  ? StatusPillTone.success
                  : StatusPillTone.warning,
            ),
          ],
        ),
      ),
    );
  }
}

/// A section label above a group of rows (17.3 "Preferences", 18.1 "Sign-in").
class AccountSectionLabel extends StatelessWidget {
  const AccountSectionLabel(this.text, {super.key});

  final String text;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsetsDirectional.only(
      top: Spacing.s2,
      bottom: Spacing.s1,
    ),
    child: Semantics(
      header: true,
      child: Text(
        text,
        style: context.type.labelMd.copyWith(
          color: context.colors.contentSecondary,
        ),
      ),
    ),
  );
}

/// A confirmation dialog (17.4, 18.6): title, body, the action and Cancel stacked full width.
Future<bool> showConfirmDialog(
  BuildContext context, {
  required String title,
  required String body,
  required String action,
  bool destructive = true,
}) async {
  final confirmed = await showDialog<bool>(
    context: context,
    builder: (context) {
      final l10n = context.l10n;
      return AlertDialog(
        backgroundColor: context.colors.surfaceRaised,
        title: Text(title, style: context.type.headingMd),
        content: Text(
          body,
          style: context.type.bodyMd.copyWith(
            color: context.colors.contentSecondary,
          ),
        ),
        actionsOverflowDirection: VerticalDirection.down,
        actions: [
          AppButton(
            label: action,
            expand: true,
            variant: destructive
                ? AppButtonVariant.danger
                : AppButtonVariant.primary,
            onPressed: () => Navigator.of(context).pop(true),
          ),
          AppButton(
            label: l10n.actionCancel,
            expand: true,
            variant: AppButtonVariant.ghost,
            onPressed: () => Navigator.of(context).pop(false),
          ),
        ],
      );
    },
  );
  return confirmed ?? false;
}

/// The sign-out confirmation (17.4), then sign out.
Future<void> confirmSignOut(BuildContext context, WidgetRef ref) async {
  final l10n = context.l10n;
  final confirmed = await showConfirmDialog(
    context,
    title: l10n.signOutConfirmTitle,
    body: l10n.signOutConfirmBody,
    action: l10n.signOutAction,
  );
  if (!confirmed) {
    return;
  }
  await ref.read(signOutControllerProvider.notifier).signOut();
  if (context.mounted) {
    showAppSnackbar(context, message: l10n.signedOutMessage);
  }
}

/// Sign out everywhere (18.1): confirmation, then `logout-all`.
Future<void> confirmSignOutEverywhere(
  BuildContext context,
  WidgetRef ref,
) async {
  final l10n = context.l10n;
  final confirmed = await showConfirmDialog(
    context,
    title: l10n.signOutEverywhereTitle,
    body: l10n.signOutEverywhereBody,
    action: l10n.signOutEverywhere,
  );
  if (!confirmed) {
    return;
  }
  await ref.read(signOutControllerProvider.notifier).signOutEverywhere();
  if (context.mounted) {
    showAppSnackbar(context, message: l10n.signedOutEverywhere);
  }
}

/// Opens an account link (terms, privacy, help) in the browser; a snackbar when it fails.
Future<void> openAccountLink(
  BuildContext context,
  WidgetRef ref,
  Uri? url,
) async {
  final l10n = context.l10n;
  final opened = await ref.read(accountLinkOpenerProvider.notifier).open(url);
  if (!opened && context.mounted) {
    showAppSnackbar(
      context,
      message: l10n.linkOpenFailed,
      tone: SnackbarTone.danger,
    );
  }
}

/// The language sheet (17.2, 17.3): English or Arabic, each written in its own script.
Future<void> showLanguageSheet(BuildContext context) async {
  final changed = await showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    useRootNavigator: true,
    backgroundColor: context.colors.surfaceRaised,
    builder: (context) => const LanguageSheet(),
  );
  if (changed == true && context.mounted) {
    showAppSnackbar(context, message: context.l10n.languageChanged);
  }
}

class LanguageSheet extends ConsumerWidget {
  const LanguageSheet({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final state = ref.watch(regionalSettingsControllerProvider);
    final current = Localizations.localeOf(context).languageCode;
    final failure = state.failure;

    Future<void> choose(String code) async {
      final changed = await ref
          .read(regionalSettingsControllerProvider.notifier)
          .selectLanguage(code);
      if (changed && context.mounted) {
        Navigator.of(context).pop(code != current);
      }
    }

    return SafeArea(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SheetHeader(title: l10n.languageSetting),
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              0,
              Spacing.s4,
              Spacing.s4,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (failure != null) ...[
                  StatusBanner(
                    tone: BannerTone.danger,
                    title: failureMessage(l10n, failure),
                  ),
                  const SizedBox(height: Spacing.s3),
                ],
                if (state.applying)
                  const Padding(
                    padding: EdgeInsetsDirectional.only(bottom: Spacing.s3),
                    // Still under reduced motion (M-A19).
                    child: Center(child: BusyIndicator()),
                  ),
                OptionTile(
                  title: l10n.languageEnglish,
                  titleDirection: TextDirection.ltr,
                  selected: current == 'en',
                  onTap: () => unawaited(choose('en')),
                ),
                const SizedBox(height: Spacing.s2),
                OptionTile(
                  title: l10n.languageArabic,
                  titleDirection: TextDirection.rtl,
                  selected: current == 'ar',
                  onTap: () => unawaited(choose('ar')),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
