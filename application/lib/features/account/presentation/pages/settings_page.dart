import 'dart:async';

import 'package:application/core/config/app_version.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/account/presentation/widgets/account_widgets.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/features/catalog/presentation/widgets/currency_sheet.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_list_tile.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Settings (17.3): language and display currency (owner decision 1; saved on the profile when
/// signed in), notifications, the legal and help pages from `GET /app/config` (only those the
/// server configures, opened in the browser), the app version and sign out. No theme setting:
/// the app is dark only, as designed.
class SettingsPage extends ConsumerWidget {
  const SettingsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    final currency = ref.watch(displayCurrencyProvider);
    final links = ref.watch(accountLinksProvider);
    final signingOut = ref.watch(signOutControllerProvider);
    final arabic = AppLocales.isRtl(Localizations.localeOf(context));

    Widget link(String key, String title, IconData icon, Uri url) => Padding(
      padding: const EdgeInsetsDirectional.only(bottom: Spacing.s2),
      child: Semantics(
        hint: l10n.opensInBrowserHint,
        child: AppListTile(
          key: ValueKey(key),
          title: title,
          icon: icon,
          showChevron: true,
          onTap: () => unawaited(openAccountLink(context, ref, url)),
        ),
      ),
    );

    return Scaffold(
      appBar: MobileAppBar(title: l10n.accountSettings),
      body: ListView(
        padding: EdgeInsetsDirectional.fromSTEB(
          LayoutTokens.marginFor(MediaQuery.sizeOf(context).width),
          Spacing.s2,
          LayoutTokens.marginFor(MediaQuery.sizeOf(context).width),
          Spacing.s6,
        ),
        children: [
          AccountSectionLabel(l10n.settingsPreferences),
          AppListTile(
            key: const ValueKey('settings-language'),
            title: l10n.languageSetting,
            icon: Icons.language,
            value: arabic ? l10n.languageArabic : l10n.languageEnglish,
            onTap: () => unawaited(showLanguageSheet(context)),
          ),
          const SizedBox(height: Spacing.s2),
          AppListTile(
            key: const ValueKey('settings-currency'),
            title: l10n.displayCurrencyLabel,
            icon: Icons.sell_outlined,
            value: currency ?? l10n.displayCurrencyAutomatic,
            onTap: () => unawaited(showCurrencySheet(context)),
          ),
          if (signedIn) ...[
            const SizedBox(height: Spacing.s2),
            AppListTile(
              key: const ValueKey('settings-notifications'),
              title: l10n.accountNotifications,
              icon: Icons.notifications_none,
              showChevron: true,
              onTap: () =>
                  unawaited(context.push(Routes.accountNotifications.path)),
            ),
          ],
          const SizedBox(height: Spacing.s2),
          AccountSectionLabel(l10n.settingsAbout),
          if (links.terms case final url?)
            link(
              'settings-terms',
              l10n.settingsTerms,
              Icons.receipt_long_outlined,
              url,
            ),
          if (links.privacy case final url?)
            link(
              'settings-privacy',
              l10n.settingsPrivacy,
              Icons.privacy_tip_outlined,
              url,
            ),
          if (links.refund case final url?)
            link(
              'settings-refund',
              l10n.settingsRefundPolicy,
              Icons.replay,
              url,
            ),
          if (links.support case final url?)
            link(
              'settings-help',
              l10n.settingsHelpCentre,
              Icons.help_outline,
              url,
            ),
          const SizedBox(height: Spacing.s2),
          Center(
            child: Text(
              l10n.appVersionLabel(appVersion),
              style: type.monoSm.copyWith(color: colors.contentMuted),
            ),
          ),
          if (signedIn) ...[
            const SizedBox(height: Spacing.s4),
            AppButton(
              key: const ValueKey('settings-sign-out'),
              label: l10n.signOutAction,
              icon: Icons.logout,
              variant: AppButtonVariant.danger,
              expand: true,
              loading: signingOut,
              onPressed: () => unawaited(_signOut(context, ref)),
            ),
          ],
        ],
      ),
    );
  }

  Future<void> _signOut(BuildContext context, WidgetRef ref) async {
    await confirmSignOut(context, ref);
  }
}
