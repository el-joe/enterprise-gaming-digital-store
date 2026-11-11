import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/account/presentation/widgets/account_widgets.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/features/notifications/notification_badge.dart';
import 'package:application/features/reviews/reviews_providers.dart';
import 'package:application/features/wallet/wallet_providers.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_list_tile.dart';
import 'package:application/shared/widgets/directional_icons.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// The Account tab (M-A16, 17.1). Signed in: the profile header and the rows Orders, Wallet
/// (only with `features.wallet_enabled`), Notifications (the unread count), My reviews (only
/// with `features.reviews_enabled`), Refund requests, Support (M-A17), Profile, Security and
/// Settings, then sign out. Signed out: sign in or create an account,
/// guest order access (M-A14) and Settings (language, currency, legal pages).
class AccountHubPage extends ConsumerWidget {
  const AccountHubPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final signedIn = ref.watch(
      sessionProvider.select((s) => s == SessionStatus.signedIn),
    );
    return Scaffold(
      appBar: MobileAppBar(
        title: l10n.accountTitle,
        leading: AppBarLeading.none,
      ),
      body: ListView(
        padding: EdgeInsetsDirectional.fromSTEB(
          LayoutTokens.marginFor(MediaQuery.sizeOf(context).width),
          Spacing.s2,
          LayoutTokens.marginFor(MediaQuery.sizeOf(context).width),
          Spacing.s6,
        ),
        children: signedIn ? const [_SignedInHub()] : const [_SignedOutHub()],
      ),
    );
  }
}

void _push(BuildContext context, String location) =>
    unawaited(context.push(location));

class _SignedInHub extends ConsumerWidget {
  const _SignedInHub();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final header = ref.watch(accountHeaderProvider);
    final walletEnabled = ref.watch(walletEnabledProvider);
    final balance = walletEnabled
        ? ref.watch(accountWalletBalanceProvider).value
        : null;
    final signingOut = ref.watch(signOutControllerProvider);
    final unread = ref.watch(unreadNotificationsProvider);
    final reviewsEnabled = ref.watch(reviewsEnabledProvider);
    final rows = <Widget>[
      AppListTile(
        key: const ValueKey('account-orders'),
        title: l10n.ordersTitle,
        icon: Icons.receipt_long_outlined,
        showChevron: true,
        onTap: () => context.go(Routes.orders.path),
      ),
      if (walletEnabled)
        AppListTile(
          key: const ValueKey('account-wallet'),
          title: l10n.walletTitle,
          subtitle: balance == null ? l10n.walletAccountSubtitle : null,
          icon: Icons.account_balance_wallet_outlined,
          value: balance == null ? null : ltrIsolate(balance),
          showChevron: balance == null,
          onTap: () => _push(context, Routes.wallet.path),
        ),
      AppListTile(
        key: const ValueKey('account-notifications'),
        title: l10n.accountNotifications,
        icon: Icons.notifications_none,
        value: unread > 0 ? l10n.notificationsNewCount(unread) : null,
        semanticsLabel: unread > 0 ? l10n.notificationBellUnread(unread) : null,
        showChevron: true,
        onTap: () => _push(context, Routes.accountNotifications.path),
      ),
      if (reviewsEnabled)
        AppListTile(
          key: const ValueKey('account-reviews'),
          title: l10n.myReviewsTitle,
          icon: Icons.rate_review_outlined,
          showChevron: true,
          onTap: () => _push(context, Routes.myReviews.path),
        ),
      AppListTile(
        key: const ValueKey('account-refunds'),
        title: l10n.refundRequestsTitle,
        icon: Icons.currency_exchange,
        showChevron: true,
        onTap: () => _push(context, Routes.refundRequests.path),
      ),
      AppListTile(
        key: const ValueKey('account-support'),
        title: l10n.accountSupport,
        icon: Icons.help_outline,
        showChevron: true,
        onTap: () => _push(context, Routes.accountSupport.path),
      ),
      AppListTile(
        key: const ValueKey('account-profile'),
        title: l10n.accountProfile,
        icon: Icons.person_outline,
        showChevron: true,
        onTap: () => _push(context, Routes.accountProfile.path),
      ),
      AppListTile(
        key: const ValueKey('account-security'),
        title: l10n.accountSecurity,
        subtitle: header == null
            ? null
            : header.twoFactorEnabled
            ? l10n.accountTwoFactorOn
            : l10n.accountTwoFactorOff,
        icon: Icons.verified_user_outlined,
        showChevron: true,
        onTap: () => _push(context, Routes.security.path),
      ),
      AppListTile(
        key: const ValueKey('account-settings'),
        title: l10n.accountSettings,
        icon: Icons.tune,
        showChevron: true,
        onTap: () => _push(context, Routes.accountSettings.path),
      ),
    ];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (header != null) ProfileHeader(header: header),
        for (var i = 0; i < rows.length; i++) ...[
          if (i > 0) const SizedBox(height: Spacing.s2),
          rows[i],
        ],
        const SizedBox(height: Spacing.s6),
        AppButton(
          key: const ValueKey('account-sign-out'),
          label: l10n.signOutAction,
          icon: DirectionalIcons.logout,
          variant: AppButtonVariant.secondary,
          expand: true,
          loading: signingOut,
          onPressed: () => unawaited(confirmSignOut(context, ref)),
        ),
      ],
    );
  }
}

class _SignedOutHub extends StatelessWidget {
  const _SignedOutHub();

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Semantics(
          header: true,
          child: Text(l10n.accountSignedOutTitle, style: type.headingLg),
        ),
        const SizedBox(height: Spacing.s1),
        Text(
          l10n.accountSignedOutBody,
          style: type.bodyMd.copyWith(color: colors.contentSecondary),
        ),
        const SizedBox(height: Spacing.s4),
        AppButton(
          key: const ValueKey('account-sign-in'),
          label: l10n.signInAction,
          expand: true,
          onPressed: () => _push(
            context,
            Uri(
              path: Routes.login.path,
              queryParameters: {Routes.fromParameter: Routes.account.path},
            ).toString(),
          ),
        ),
        const SizedBox(height: Spacing.s2),
        AppButton(
          key: const ValueKey('account-register'),
          label: l10n.createAccountLink,
          variant: AppButtonVariant.secondary,
          expand: true,
          onPressed: () => _push(
            context,
            Uri(
              path: Routes.register.path,
              queryParameters: {Routes.fromParameter: Routes.account.path},
            ).toString(),
          ),
        ),
        const SizedBox(height: Spacing.s6),
        AccountSectionLabel(l10n.accountGuestSection),
        const SizedBox(height: Spacing.s1),
        AppListTile(
          title: l10n.guestAccessEntry,
          subtitle: l10n.guestAccessEntryBody,
          icon: Icons.mark_email_read_outlined,
          showChevron: true,
          onTap: () => _push(context, Routes.guestAccess.path),
        ),
        const SizedBox(height: Spacing.s2),
        AppListTile(
          key: const ValueKey('account-support'),
          title: l10n.accountSupport,
          icon: Icons.help_outline,
          showChevron: true,
          onTap: () => _push(context, Routes.accountSupport.path),
        ),
        const SizedBox(height: Spacing.s2),
        AppListTile(
          key: const ValueKey('account-settings'),
          title: l10n.accountSettings,
          icon: Icons.tune,
          showChevron: true,
          onTap: () => _push(context, Routes.accountSettings.path),
        ),
      ],
    );
  }
}
