import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/account/presentation/controllers/change_password_controller.dart';
import 'package:application/features/account/presentation/widgets/account_widgets.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/features/auth/presentation/widgets/biometric_widgets.dart';
import 'package:application/features/security/presentation/controllers/devices_controller.dart';
import 'package:application/features/security/presentation/controllers/two_factor_controllers.dart';
import 'package:application/features/security/presentation/widgets/security_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_list_tile.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Security (18.1): two-step verification (turn on, turn off with the password), change
/// password, the biometric unlock switch (owner decision 6; turning it off deletes the server
/// and the local key), the signed-in devices, the recent-auth note and sign out everywhere.
class SecurityPage extends ConsumerWidget {
  const SecurityPage({super.key});

  Future<void> _turnOffTwoFactor(BuildContext context, WidgetRef ref) async {
    final l10n = context.l10n;
    final done = await showPasswordActionSheet(
      context,
      title: l10n.twoFactorDisableTitle,
      body: l10n.twoFactorDisableBody,
      actionLabel: l10n.twoFactorTurnOff,
      cancelLabel: l10n.twoFactorKeepOn,
      onSubmit: ref.read(twoFactorDisableControllerProvider.notifier).disable,
    );
    if (done && context.mounted) {
      showAppSnackbar(context, message: l10n.twoFactorDisabled);
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final header = ref.watch(accountHeaderProvider);
    final twoFactorOn = header?.twoFactorEnabled ?? false;
    // Keeps the disable controller alive while its sheet can be opened.
    ref.watch(twoFactorDisableControllerProvider);
    final devices = ref.watch(devicesControllerProvider).content.valueOrNull;
    final biometricOn = ref.watch(biometricUnlockSettingsProvider).value;
    final resetNotice =
        ref.watch(biometricResetNoticeProvider) && biometricOn == false;
    final signingOut = ref.watch(signOutControllerProvider);

    return Scaffold(
      appBar: MobileAppBar(title: l10n.accountSecurity),
      body: ListView(
        padding: EdgeInsetsDirectional.fromSTEB(
          LayoutTokens.marginFor(MediaQuery.sizeOf(context).width),
          Spacing.s2,
          LayoutTokens.marginFor(MediaQuery.sizeOf(context).width),
          Spacing.s6,
        ),
        children: [
          AccountSectionLabel(l10n.securitySignInSection),
          AppListTile(
            key: const ValueKey('security-two-factor'),
            title: l10n.twoFactorTitle,
            subtitle: twoFactorOn
                ? l10n.twoFactorOnSubtitle
                : l10n.twoFactorOffSubtitle,
            icon: Icons.verified_user_outlined,
            value: twoFactorOn ? l10n.twoFactorTurnOff : l10n.twoFactorTurnOn,
            onTap: header == null
                ? null
                : twoFactorOn
                ? () => unawaited(_turnOffTwoFactor(context, ref))
                : () => unawaited(context.push(Routes.twoFactorSetup.path)),
          ),
          const SizedBox(height: Spacing.s2),
          AppListTile(
            key: const ValueKey('security-password'),
            title: l10n.changePasswordTitle,
            subtitle: l10n.changePasswordSubtitle,
            icon: Icons.key_outlined,
            showChevron: true,
            onTap: () => unawaited(context.push(Routes.changePassword.path)),
          ),
          if (resetNotice) ...[
            const SizedBox(height: Spacing.s2),
            StatusBanner(
              tone: BannerTone.warning,
              title: l10n.passwordChangedBiometricTitle,
              body: l10n.passwordChangedBiometricBody,
            ),
          ],
          const SizedBox(height: Spacing.s2),
          const BiometricUnlockTile(),
          const SizedBox(height: Spacing.s2),
          AccountSectionLabel(l10n.securityDevicesSection),
          AppListTile(
            key: const ValueKey('security-devices'),
            title: l10n.devicesTitle,
            subtitle: devices == null
                ? null
                : l10n.devicesCount(devices.devices.length),
            icon: Icons.smartphone,
            showChevron: true,
            onTap: () => unawaited(context.push(Routes.devices.path)),
          ),
          const SizedBox(height: Spacing.s4),
          StatusBanner(
            tone: BannerTone.info,
            title: l10n.securityRevealNoticeTitle,
            body: l10n.securityRevealNoticeBody,
          ),
          const SizedBox(height: Spacing.s6),
          AppButton(
            key: const ValueKey('security-sign-out-everywhere'),
            label: l10n.signOutEverywhere,
            icon: Icons.logout,
            variant: AppButtonVariant.secondary,
            expand: true,
            loading: signingOut,
            onPressed: () => unawaited(confirmSignOutEverywhere(context, ref)),
          ),
        ],
      ),
    );
  }
}
